if [[ -n "${__MIRZA_ADDITIONAL_BACKUP_DOMAIN_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_ADDITIONAL_BACKUP_DOMAIN_LOADED=1

# shellcheck shell=bash
# Mirza installer module: additional_bots_backup_domain
# This file is sourced by install.sh
function disable_backup_additional_bot() {
    clear
    echo -e "\033[36mDisabling Automated Backup for Additional Bot...\033[0m"

    echo -e "\033[36mAvailable Bots:\033[0m"
    BOT_DIRS=$(ls -d /var/www/html/*/ 2>/dev/null | grep -v "/var/www/html/mirzabotconfig/" | xargs -r -n 1 basename)

    if [ -z "$BOT_DIRS" ]; then
        echo -e "\033[31mNo additional bots found in /var/www/html.\033[0m"
        return 1
    fi

    echo "$BOT_DIRS" | nl -w 2 -s ") "

    echo -ne "\033[36mSelect a bot by name: \033[0m"
    read SELECTED_BOT

    if [[ ! "$BOT_DIRS" =~ (^|[[:space:]])$SELECTED_BOT($|[[:space:]]) ]]; then
        echo -e "\033[31mInvalid bot name.\033[0m"
        return 1
    fi

    BACKUP_SCRIPT="/root/${SELECTED_BOT}_auto_backup.sh"

    CURRENT_CRON=$(crontab -l 2>/dev/null | grep "$BACKUP_SCRIPT")

    if [ -z "$CURRENT_CRON" ]; then
        echo -e "\033[33mNo automated backup found for $SELECTED_BOT.\033[0m"
        return 1
    fi

    crontab -l 2>/dev/null | grep -v "$BACKUP_SCRIPT" | crontab -

    if [ -f "$BACKUP_SCRIPT" ]; then
        rm "$BACKUP_SCRIPT"
    fi

    echo -e "\033[32mAutomated backup disabled successfully for $SELECTED_BOT.\033[0m"
}


function change_additional_bot_domain() {
    clear
    echo -e "\033[33mChange Additional Bot Domain\033[0m"
    log_action "Initiating additional bot domain change workflow."

    echo -e "\033[36mAvailable Bots:\033[0m"
    BOT_DIRS=$(ls -d /var/www/html/*/ 2>/dev/null | grep -v "/var/www/html/mirzabotconfig/" | xargs -r -n 1 basename)

    if [ -z "$BOT_DIRS" ]; then
        echo -e "\033[31mNo additional bots found in /var/www/html.\033[0m"
        log_warn "No additional bots detected during domain change request."
        return 1
    fi

    echo "$BOT_DIRS" | nl -w 2 -s ") "

    echo -ne "\033[36mSelect a bot by name: \033[0m"
    read SELECTED_BOT

    if [[ ! "$BOT_DIRS" =~ (^|[[:space:]])$SELECTED_BOT($|[[:space:]]) ]]; then
        echo -e "\033[31mInvalid bot name.\033[0m"
        log_error "User selected invalid bot '$SELECTED_BOT' for domain change."
        return 1
    fi

    BOT_PATH="/var/www/html/$SELECTED_BOT"
    CONFIG_PATH="$BOT_PATH/config.php"

    if [ ! -f "$CONFIG_PATH" ]; then
        echo -e "\033[31mconfig.php not found for $SELECTED_BOT.\033[0m"
        log_error "config.php missing for $SELECTED_BOT while changing domain."
        return 1
    fi

    current_domainhosts=$(grep '^\$domainhosts' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    sanitized=${current_domainhosts#http://}
    sanitized=${sanitized#https://}
    sanitized=${sanitized#/}
    current_domain=${sanitized%%/*}
    log_info "Processing domain change for bot '$SELECTED_BOT' (current domain: ${current_domain:-unknown})."

    while true; do
        echo -ne "\033[36mEnter the new domain (e.g., example.com): \033[0m"
        read NEW_DOMAIN
        if [[ "$NEW_DOMAIN" =~ ^[a-zA-Z0-9.-]+$ ]]; then
            log_info "User entered new domain '$NEW_DOMAIN' for bot '$SELECTED_BOT'."
            break
        fi
        echo -e "\033[31mInvalid domain format. Please try again.\033[0m"
    done

    log_action "Disabling Apache service before domain change..."
    sudo systemctl disable apache2 >/dev/null 2>&1 || true

    if ! configure_apache_vhost "$NEW_DOMAIN" "$BOT_PARENT_DIR"; then
        log_error "Unable to prepare Apache virtual host for ${NEW_DOMAIN} (bot ${SELECTED_BOT})."
        restore_apache_service
        return 1
    fi

    log_action "Stopping Apache to configure SSL..."
    if ! sudo systemctl stop apache2; then
        log_error "Failed to stop Apache while preparing SSL for ${NEW_DOMAIN}."
        restore_apache_service
        return 1
    fi

    log_action "Configuring SSL certificate for ${NEW_DOMAIN}..."
    if ! wait_for_certbot; then
        log_error "Certbot is already running. Please try again after the current process completes."
        restore_apache_service
        return 1
    fi

    if ! sudo certbot --apache --redirect --agree-tos --preferred-challenges http \
        --non-interactive --force-renewal --cert-name "$NEW_DOMAIN" -d "$NEW_DOMAIN"; then
        log_error "SSL configuration failed for ${NEW_DOMAIN}, rolling back certificate changes."
        if wait_for_certbot; then
            sudo certbot delete --cert-name "$NEW_DOMAIN" 2>/dev/null
        else
            log_warn "Unable to clean up certificate for ${NEW_DOMAIN} because Certbot remained busy."
        fi
        restore_apache_service
        return 1
    fi

    if [ -f "$CONFIG_PATH" ]; then
        sudo cp "$CONFIG_PATH" "$CONFIG_PATH.$(date +%s).bak"

        sanitized=${current_domainhosts#http://}
        sanitized=${sanitized#https://}
        sanitized=${sanitized#/}
        path_segment=""
        if [[ "$sanitized" == */* ]]; then
            path_segment=${sanitized#*/}
            path_segment=${path_segment%/}
        fi
        if [ -z "$path_segment" ]; then
            path_segment="$SELECTED_BOT"
        fi

        full_domain_path="${NEW_DOMAIN}/${path_segment}"

        sudo sed -i "s|\$domainhosts = '.*';|\$domainhosts = '${full_domain_path}';|" "$CONFIG_PATH"

        NEW_SECRET=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9')
        sudo sed -i "s|\$secrettoken = '.*';|\$secrettoken = '${NEW_SECRET}';|" "$CONFIG_PATH"

        BOT_TOKEN=$(awk -F"'" '/\$APIKEY/{print $2}' "$CONFIG_PATH")
        updated_domainhosts=$(awk -F"'" '/\$domainhosts/{print $2}' "$CONFIG_PATH" | head -1)
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
            log_info "Telegram webhook updated successfully for ${NEW_DOMAIN} (bot ${SELECTED_BOT})."
        else
            log_warn "Webhook update returned a warning for ${NEW_DOMAIN}: ${webhook_response}"
        fi
    else
        log_error "Config file missing at ${CONFIG_PATH}; aborting domain change."
        restore_apache_service
        return 1
    fi

    if [ -n "$current_domain" ] && [ "$current_domain" != "$NEW_DOMAIN" ] && [ -f "/etc/apache2/sites-available/${current_domain}.conf" ]; then
        sudo a2dissite "${current_domain}.conf" >/dev/null 2>&1
        log_info "Disabled old Apache site ${current_domain}.conf."
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
        log_info "Additional bot domain successfully migrated to ${full_domain_path}."
    else
        log_warn "Final verification failed for ${WEBHOOK_URL} (HTTP ${http_status:-000}). Please verify DNS, Apache vhost, and firewall."
    fi

    if [ -n "$current_domain" ] && [ "$current_domain" != "$NEW_DOMAIN" ] && [ -d "/etc/letsencrypt/live/${current_domain}" ]; then
        read -p "Delete old SSL certificate for ${current_domain}? (y/n): " delete_old_cert
        if [[ "$delete_old_cert" =~ ^[Yy]$ ]]; then
            if wait_for_certbot; then
                sudo certbot delete --cert-name "$current_domain" 2>/dev/null || echo -e "\033[33m[WARN]\033[0m Failed to delete certificate for ${current_domain}."
                log_info "Requested deletion of old certificate for ${current_domain}."
            else
                log_warn "Certbot is busy; skipping deletion of legacy certificate for ${current_domain}."
            fi
        fi
    fi

    echo -e "\033[32mDomain updated successfully for ${SELECTED_BOT}.\033[0m"
    log_info "Domain change completed for '${SELECTED_BOT}'. New domain: $NEW_DOMAIN."
    restore_apache_service
    read -p "Press Enter to return to the Additional Bot menu..."
    manage_additional_bots
}


function configure_backup_additional_bot() {
    clear
    echo -e "\033[36mConfiguring Automated Backup for Additional Bot...\033[0m"

    echo -e "\033[36mAvailable Bots:\033[0m"
    BOT_DIRS=$(ls -d /var/www/html/*/ 2>/dev/null | grep -v "/var/www/html/mirzabotconfig/" | xargs -r -n 1 basename)

    if [ -z "$BOT_DIRS" ]; then
        echo -e "\033[31mNo additional bots found in /var/www/html.\033[0m"
        return 1
    fi

    echo "$BOT_DIRS" | nl -w 2 -s ") "

    echo -ne "\033[36mSelect a bot by name: \033[0m"
    read SELECTED_BOT

    if [[ ! "$BOT_DIRS" =~ (^|[[:space:]])$SELECTED_BOT($|[[:space:]]) ]]; then
        echo -e "\033[31mInvalid bot name.\033[0m"
        return 1
    fi

    BOT_PATH="/var/www/html/$SELECTED_BOT"
    CONFIG_PATH="$BOT_PATH/config.php"

    if [ ! -f "$CONFIG_PATH" ]; then
        echo -e "\033[31mconfig.php not found for $SELECTED_BOT.\033[0m"
        return 1
    fi

    DB_NAME=$(grep '^\$dbname' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_USER=$(grep '^\$usernamedb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_PASS=$(grep '^\$passworddb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    TELEGRAM_TOKEN=$(grep '^\$APIKEY' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    TELEGRAM_CHAT_ID=$(grep '^\$adminnumber' "$CONFIG_PATH" | awk -F"'" '{print $2}')

    if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ]; then
        echo -e "\033[31m[ERROR]\033[0m Failed to extract database credentials from $CONFIG_PATH."
        return 1
    fi

    if [ -z "$TELEGRAM_TOKEN" ] || [ -z "$TELEGRAM_CHAT_ID" ]; then
        echo -e "\033[31m[ERROR]\033[0m Telegram token or chat ID not found in $CONFIG_PATH."
        return 1
    fi

    while true; do
        echo -e "\033[36mChoose backup frequency:\033[0m"
        echo -e "\033[36m1) Every minute\033[0m"
        echo -e "\033[36m2) Every hour\033[0m"
        echo -e "\033[36m3) Every day\033[0m"
        echo -e "\033[36m4) Every week\033[0m"
        read -p "Enter your choice (1-4): " frequency

        case $frequency in
            1) cron_time="* * * * *" ; break ;;
            2) cron_time="0 * * * *" ; break ;;
            3) cron_time="0 0 * * *" ; break ;;
            4) cron_time="0 0 * * 0" ; break ;;
            *)
                echo -e "\033[31mInvalid option. Please try again.\033[0m"
                ;;
        esac
    done

    BACKUP_SCRIPT="/root/${SELECTED_BOT}_auto_backup.sh"
    cat <<EOF > "$BACKUP_SCRIPT"

DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASS="$DB_PASS"
TELEGRAM_TOKEN="$TELEGRAM_TOKEN"
TELEGRAM_CHAT_ID="$TELEGRAM_CHAT_ID"

BACKUP_FILE="/root/\${DB_NAME}_\$(date +"%Y%m%d_%H%M%S").sql"
if mysqldump -u "\$DB_USER" -p"\$DB_PASS" "\$DB_NAME" > "\$BACKUP_FILE"; then
    curl -F document=@"\$BACKUP_FILE" "https://api.telegram.org/bot\$TELEGRAM_TOKEN/sendDocument" -F chat_id="\$TELEGRAM_CHAT_ID"
    if [ \$? -eq 0 ]; then
        rm "\$BACKUP_FILE"
    fi
else
    echo -e "\033[31m[ERROR]\033[0m Failed to create database backup."
fi
EOF

    chmod +x "$BACKUP_SCRIPT"

    (crontab -l 2>/dev/null; echo "$cron_time bash $BACKUP_SCRIPT") | crontab -

    echo -e "\033[32mAutomated backup configured successfully for $SELECTED_BOT.\033[0m"
}
