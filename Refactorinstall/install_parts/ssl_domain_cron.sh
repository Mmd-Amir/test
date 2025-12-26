if [[ -n "${__MIRZA_SSL_DOMAIN_CRON_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_SSL_DOMAIN_CRON_LOADED=1

# shellcheck shell=bash
# Mirza installer module: ssl_domain_cron
# This file is sourced by install.sh
function renew_ssl() {
    echo -e "\033[33mStarting SSL renewal process...\033[0m"

    if ! command -v certbot &>/dev/null; then
        echo -e "\033[31m[ERROR]\033[0m Certbot is not installed. Please install Certbot to proceed."
        return 1
    fi

    echo -e "\033[33mStopping Apache...\033[0m"
    sudo systemctl stop apache2 || {
        echo -e "\033[31m[ERROR]\033[0m Failed to stop Apache. Exiting..."
        return 1
    }

    if ! wait_for_certbot; then
        echo -e "\033[31m[ERROR]\033[0m Certbot is busy. Please try again later."
        sudo systemctl start apache2 >/dev/null 2>&1
        return 1
    fi

    if sudo certbot renew; then
        echo -e "\033[32mSSL certificates successfully renewed.\033[0m"
    else
        echo -e "\033[31m[ERROR]\033[0m SSL renewal failed. Please check Certbot logs for more details."
        sudo systemctl start apache2
        return 1
    fi

    echo -e "\033[33mRestarting Apache...\033[0m"
    sudo systemctl restart apache2 || {
        echo -e "\033[31m[WARNING]\033[0m Failed to restart Apache. Please check manually."
    }
}

function change_domain() {
    local new_domain current_domainhosts sanitized_value path_segment full_domain_path WEBHOOK_URL webhook_response http_status updated_domainhosts
    full_domain_path=""
    WEBHOOK_URL=""
    while [[ ! "$new_domain" =~ ^[a-zA-Z0-9.-]+$ ]]; do
        read -p "Enter new domain: " new_domain
        [[ ! "$new_domain" =~ ^[a-zA-Z0-9.-]+$ ]] && echo -e "\033[31mInvalid domain format\033[0m"
    done

    log_action "Disabling Apache service before domain change..."
    sudo systemctl disable apache2 >/dev/null 2>&1 || true

    if ! configure_apache_vhost "$new_domain"; then
        log_error "Unable to prepare Apache virtual host for ${new_domain}."
        restore_apache_service
        return 1
    fi

    log_action "Stopping Apache to configure SSL..."
    if ! sudo systemctl stop apache2; then
        log_error "Failed to stop Apache while preparing SSL for ${new_domain}."
        restore_apache_service
        return 1
    fi

    log_action "Configuring SSL certificate for ${new_domain}..."
    if ! wait_for_certbot; then
        log_error "Certbot is already running. Please try again after the current process completes."
        restore_apache_service
        return 1
    fi

    if ! sudo certbot --apache --redirect --agree-tos --preferred-challenges http \
        --non-interactive --force-renewal --cert-name "$new_domain" -d "$new_domain"; then
        log_error "SSL configuration failed for ${new_domain}, rolling back certificate changes."
        if wait_for_certbot; then
            sudo certbot delete --cert-name "$new_domain" 2>/dev/null
        else
            log_warn "Unable to clean up certificate for ${new_domain} because Certbot remained busy."
        fi
        restore_apache_service
        return 1
    fi

    CONFIG_FILE="/var/www/html/mirzabotconfig/config.php"
    if [ -f "$CONFIG_FILE" ]; then
        sudo cp "$CONFIG_FILE" "$CONFIG_FILE.$(date +%s).bak"

        current_domainhosts=$(awk -F"'" '/\$domainhosts/{print $2}' "$CONFIG_FILE" | head -1)
        sanitized_value=${current_domainhosts#http://}
        sanitized_value=${sanitized_value#https://}
        sanitized_value=${sanitized_value#/}
        path_segment=""
        if [[ "$sanitized_value" == */* ]]; then
            path_segment=${sanitized_value#*/}
            path_segment=${path_segment%/}
        fi

        if [ -z "$path_segment" ] && [ -d "/var/www/html/mirzabotconfig" ]; then
            path_segment="mirzabotconfig"
            log_info "No path segment detected in existing domain. Using default path '/mirzabotconfig'."
        fi

        if [ -n "$path_segment" ]; then
            full_domain_path="${new_domain}/${path_segment}"
        else
            full_domain_path="${new_domain}"
        fi

        sudo sed -i "s|\$domainhosts = '.*';|\$domainhosts = '${full_domain_path}';|" "$CONFIG_FILE"

        NEW_SECRET=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9')
        sudo sed -i "s|\$secrettoken = '.*';|\$secrettoken = '${NEW_SECRET}';|" "$CONFIG_FILE"

        BOT_TOKEN=$(awk -F"'" '/\$APIKEY/{print $2}' "$CONFIG_FILE")
        updated_domainhosts=$(awk -F"'" '/\$domainhosts/{print $2}' "$CONFIG_FILE" | head -1)
        updated_domainhosts=${updated_domainhosts%/}
        if [[ "$updated_domainhosts" =~ ^https?:// ]]; then
            WEBHOOK_URL="${updated_domainhosts}/index.php"
        else
            WEBHOOK_URL="https://${updated_domainhosts}/index.php"
        fi

        webhook_response=$(curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/setWebhook" \
            -F "url=${WEBHOOK_URL}" \
            -F "secret_token=${NEW_SECRET}")

        if echo "$webhook_response" | grep -q '"ok":true'; then
            log_info "Telegram webhook updated successfully for ${new_domain}."
        else
            log_warn "Webhook update returned a warning: ${webhook_response}"
        fi
    else
        log_error "Config file missing at ${CONFIG_FILE}; aborting domain change."
        restore_apache_service
        return 1
    fi

    local attempt http_status=""
    for attempt in {1..5}; do
        http_status=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$WEBHOOK_URL")
        if [[ "$http_status" =~ ^(200|301|302)$ ]]; then
            break
        fi
        log_warn "Endpoint ${WEBHOOK_URL} not ready yet (HTTP ${http_status:-000}). Retrying in 3 seconds..."
        sleep 3
    done

    if [[ "$http_status" =~ ^(200|301|302)$ ]]; then
        log_info "Domain successfully migrated to ${full_domain_path}. Old configuration cleaned up."
    else
        log_warn "Final verification failed for ${WEBHOOK_URL} (HTTP ${http_status:-000}). Please verify DNS, Apache vhost, and firewall."
    fi

    restore_apache_service
}

function remove_domain() {
    local conf_dir="/etc/apache2/sites-available"
    local domain_list=()
    local domain selection
    local -a conf_files=()

    if [ ! -d "$conf_dir" ]; then
        echo -e "\033[31m[ERROR]\033[0m Apache configuration directory not found."
        read -p "Press Enter to return to main menu..."
        show_menu
        return 1
    fi

    mapfile -t conf_files < <(find "$conf_dir" -maxdepth 1 -type f -name '*.conf' -printf '%f\n' 2>/dev/null | sort)
    for conf in "${conf_files[@]}"; do
        [ -z "$conf" ] && continue
        domain="${conf%.conf}"
        case "$domain" in
            000-default|default-ssl|000-default-le-ssl)
                continue
                ;;
        esac
        domain_list+=("$domain")
    done

    if [ ${#domain_list[@]} -eq 0 ]; then
        echo -e "\033[33m[INFO]\033[0m No custom domains found to remove."
        read -p "Press Enter to return to main menu..."
        show_menu
        return 0
    fi

    echo -e "\033[36mConfigured domains:\033[0m"
    for idx in "${!domain_list[@]}"; do
        printf "%d) %s\n" $((idx + 1)) "${domain_list[$idx]}"
    done

    read -p "Select the domain you want to remove [1-${#domain_list[@]}]: " selection
    if ! [[ "$selection" =~ ^[0-9]+$ ]] || [ "$selection" -lt 1 ] || [ "$selection" -gt ${#domain_list[@]} ]; then
        echo -e "\033[31m[ERROR]\033[0m Invalid selection."
        sleep 2
        show_menu
        return 1
    fi

    domain="${domain_list[$((selection - 1))]}"
    read -p "Are you sure you want to remove ${domain}? (y/n): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo -e "\033[33m[INFO]\033[0m Operation cancelled."
        sleep 1
        show_menu
        return 0
    fi

    mapfile -t conf_files < <(find "$conf_dir" -maxdepth 1 -type f -name "${domain}*.conf" -printf '%f\n' 2>/dev/null)
    if [ ${#conf_files[@]} -eq 0 ]; then
        conf_files=("${domain}.conf")
    fi

    for conf in "${conf_files[@]}"; do
        [ -z "$conf" ] && continue
        sudo a2dissite "$conf" >/dev/null 2>&1
        sudo rm -f "${conf_dir}/${conf}" "/etc/apache2/sites-enabled/${conf}"
    done

    if ! sudo apache2ctl configtest >/dev/null 2>&1; then
        echo -e "\033[31m[ERROR]\033[0m Apache configuration test failed after removing ${domain}. Please inspect manually."
        show_menu
        return 1
    fi

    if ! sudo systemctl reload apache2 >/dev/null 2>&1; then
        echo -e "\033[33m[WARN]\033[0m Reload failed. Attempting restart..."
        sudo systemctl restart apache2 >/dev/null 2>&1 || echo -e "\033[31m[ERROR]\033[0m Apache restart failed."
    fi

    if [ -d "/etc/letsencrypt/live/${domain}" ]; then
        read -p "Delete existing SSL certificate for ${domain}? (y/n): " delete_cert
        if [[ "$delete_cert" =~ ^[Yy]$ ]]; then
            if wait_for_certbot; then
                sudo certbot delete --cert-name "$domain" 2>/dev/null || echo -e "\033[33m[WARN]\033[0m Failed to delete certificate for ${domain}."
            else
                echo -e "\033[33m[WARN]\033[0m Certbot is busy. Skipping certificate deletion for ${domain}."
            fi
        fi
    fi

    echo -e "\033[32m[SUCCESS]\033[0m Domain ${domain} removed."
    read -p "Press Enter to return to main menu..."
    show_menu
}


delete_cron_jobs() {
    local CRON_FILE="/var/spool/cron/crontabs/www-data"
    while true; do
        local delete_all selection tmp
        clear
        echo -e "\033[33mReading cron jobs for www-data...\033[0m"

        if [ ! -f "$CRON_FILE" ]; then
            echo -e "\033[31m[ERROR]\033[0m Cron file not found at $CRON_FILE."
            read -p "Press Enter to return to main menu..."
            show_menu
            return 1
        fi

        if ! sudo cat "$CRON_FILE" >/dev/null 2>&1; then
            echo -e "\033[31m[ERROR]\033[0m Cannot read $CRON_FILE (permission denied)."
            read -p "Press Enter to return to main menu..."
            show_menu
            return 1
        fi

        mapfile -t CRON_LINES < <(sudo awk '
            /^[[:space:]]*#/ {next}
            /^[[:space:]]*$/ {next}
            {print}
        ' "$CRON_FILE")

        if [ ${#CRON_LINES[@]} -eq 0 ]; then
            echo -e "\033[33m[INFO]\033[0m No cron entries found for www-data."
            read -p "Press Enter to return to main menu..."
            show_menu
            return 0
        fi

        echo -e "\033[36mExisting cron entries:\033[0m"
        for idx in "${!CRON_LINES[@]}"; do
            printf "%d) %s\n" $((idx + 1)) "${CRON_LINES[$idx]}"
        done

        read -p "Delete all detected cron jobs? (y/n): " delete_all
        if [[ "$delete_all" =~ ^[Yy]$ ]]; then
            tmp=$(mktemp)
            if ! sudo awk '
                /^[[:space:]]*#/ {print; next}
                /^[[:space:]]*$/ {print; next}
            ' "$CRON_FILE" > "$tmp"; then
                echo -e "\033[31m[ERROR]\033[0m Failed to clean cron file."
                rm -f "$tmp"
                read -p "Press Enter to return to main menu..."
                show_menu
                return 1
            fi
            if ! sudo mv "$tmp" "$CRON_FILE"; then
                echo -e "\033[31m[ERROR]\033[0m Failed to overwrite cron file."
                rm -f "$tmp"
                read -p "Press Enter to return to main menu..."
                show_menu
                return 1
            fi
            sudo chown www-data:crontab "$CRON_FILE" 2>/dev/null || true
            sudo chmod 600 "$CRON_FILE" 2>/dev/null || true
            echo -e "\033[32m[SUCCESS]\033[0m All detected cron jobs were deleted."
            sleep 1.5
            show_menu
            return 0
        fi

        echo -e "\033[1;31m0) Exit to Main Menu\033[0m"
        echo ""

        read -p "Select a cron entry to delete [0-${#CRON_LINES[@]}]: " selection
        if [[ "$selection" == "0" ]]; then
            echo -e "\033[33mReturning to main menu...\033[0m"
            sleep 1
            show_menu
            return 0
        fi

        if ! [[ "$selection" =~ ^[0-9]+$ ]] || [ "$selection" -lt 1 ] || [ "$selection" -gt ${#CRON_LINES[@]} ]; then
            echo -e "\033[31m[ERROR]\033[0m Invalid selection."
            sleep 1.5
            continue
        fi

        if ! sudo awk -v target="$selection" 'BEGIN{idx=0}
        {
            line=$0
            if (line ~ /^[[:space:]]*$/) {print; next}
            if (line ~ /^[[:space:]]*#/) {print; next}
            idx++
            if (idx==target) next
            print
        }' "$CRON_FILE" > "$tmp"; then
            echo -e "\033[31m[ERROR]\033[0m Failed to update cron file."
            rm -f "$tmp"
            read -p "Press Enter to return to main menu..."
            show_menu
            return 1
        fi

        if ! sudo mv "$tmp" "$CRON_FILE"; then
            echo -e "\033[31m[ERROR]\033[0m Failed to overwrite cron file."
            rm -f "$tmp"
            read -p "Press Enter to return to main menu..."
            show_menu
            return 1
        fi

        sudo chown www-data:crontab "$CRON_FILE" 2>/dev/null || true
        sudo chmod 600 "$CRON_FILE" 2>/dev/null || true

        echo -e "\033[32m[SUCCESS]\033[0m Cron entry #$selection deleted."
        sleep 1.5
    done
}
