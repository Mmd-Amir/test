if [[ -n "${__MIRZA_SYSTEM_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_SYSTEM_LOADED=1

# shellcheck shell=bash
# Mirza installer module: system_apache_certbot_helpers
# This file is sourced by install.sh
check_ssl_status() {
    if [ -f "/var/www/html/mirzabotconfig/config.php" ]; then
        domain=$(grep '^\$domainhosts' "/var/www/html/mirzabotconfig/config.php" | cut -d"'" -f2 | cut -d'/' -f1)

        if [ -n "$domain" ] && [ -f "/etc/letsencrypt/live/$domain/cert.pem" ]; then
            expiry_date=$(openssl x509 -enddate -noout -in "/etc/letsencrypt/live/$domain/cert.pem" | cut -d= -f2)
            current_date=$(date +%s)
            expiry_timestamp=$(date -d "$expiry_date" +%s)
            days_remaining=$(( ($expiry_timestamp - $current_date) / 86400 ))
            if [ $days_remaining -gt 0 ]; then
                echo -e "\033[32m✅ SSL Certificate: $days_remaining days remaining (Domain: $domain)\033[0m"
            else
                echo -e "\033[31m❌ SSL Certificate: Expired (Domain: $domain)\033[0m"
            fi
        else
            echo -e "\033[33m⚠️ SSL Certificate: Not found for domain $domain\033[0m"
        fi
    else
        echo -e "\033[33m⚠️ Cannot check SSL: Config file not found\033[0m"
    fi
}


check_bot_status() {
    if [ -f "/var/www/html/mirzabotconfig/config.php" ]; then
        echo -e "\033[32m✅ Bot is installed\033[0m"
        check_ssl_status
    else
        echo -e "\033[31m❌ Bot is not installed\033[0m"
    fi
}


configure_apache_vhost() {
    local domain="$1"
    local docroot="${2:-/var/www/html}"
    local conf="/etc/apache2/sites-available/${domain}.conf"

    if [ -z "$domain" ]; then
        echo -e "\033[31m[ERROR] Domain name missing while configuring Apache.\033[0m"
        return 1
    fi

    echo -e "\033[33mConfiguring Apache virtual host for ${domain} (DocumentRoot: ${docroot})...\033[0m"
    sudo tee "$conf" >/dev/null <<EOF
<VirtualHost *:80>
    ServerName $domain
    DocumentRoot $docroot

    <Directory $docroot>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${domain}-error.log
    CustomLog \${APACHE_LOG_DIR}/${domain}-access.log combined
</VirtualHost>
EOF

    if ! sudo a2ensite "${domain}.conf" >/dev/null 2>&1; then
        echo -e "\033[31m[ERROR] Failed to enable Apache site for ${domain}.\033[0m"
        return 1
    fi

    if ! sudo apache2ctl configtest >/dev/null 2>&1; then
        echo -e "\033[31m[ERROR] Apache configuration test failed after adding ${domain}.\033[0m"
        return 1
    fi

    return 0
}


cleanup_apache_state() {
    if pgrep -x apache2 >/dev/null 2>&1 && ! sudo systemctl is-active --quiet apache2; then
        echo -e "\033[33m[INFO] Detected Apache running outside systemd. Stopping stale process...\033[0m"
        sudo apachectl stop >/dev/null 2>&1 || sudo pkill -TERM apache2 >/dev/null 2>&1
        sudo rm -f /var/run/apache2/apache2.pid >/dev/null 2>&1
    fi
}


restore_apache_service() {
    cleanup_apache_state
    echo -e "\033[33mRe-enabling Apache service...\033[0m"
    sudo systemctl enable apache2 >/dev/null 2>&1 || echo -e "\033[33m[INFO] Apache service was already disabled.\033[0m"
    echo -e "\033[33mStarting Apache service...\033[0m"
    if ! sudo systemctl start apache2; then
        echo -e "\033[33m[WARN] Apache failed to start cleanly, retrying after cleanup...\033[0m"
        cleanup_apache_state
        if ! sudo systemctl start apache2; then
            echo -e "\033[31m[ERROR] Failed to start Apache service!\033[0m"
        fi
    fi
}


wait_for_certbot() {
    local timeout="${1:-180}"
    local interval=5
    local waited=0
    local lock_paths=(
        "/var/log/letsencrypt/.certbot.lock"
        "/var/lib/letsencrypt/.certbot.lock"
    )

    while true; do
        local lock_found=0
        for lock_file in "${lock_paths[@]}"; do
            if [ -f "$lock_file" ]; then
                lock_found=1
                break
            fi
        done
        if ! pgrep -x certbot >/dev/null 2>&1 && [ $lock_found -eq 0 ]; then
            return 0
        fi

        if [ $waited -ge $timeout ]; then
            log_error "Certbot lock has been held for more than ${timeout}s. Please ensure no other Certbot process is running."
            return 1
        fi

        log_warn "Certbot is already running; waiting for it to finish..."
        sleep $interval
        waited=$((waited + interval))
    done
}


function check_marzban_installed() {
    if [ -f "/opt/marzban/docker-compose.yml" ]; then
        return 0 
    else
        return 1  
    fi
}


function detect_database_type() {
    COMPOSE_FILE="/opt/marzban/docker-compose.yml"
    if [ ! -f "$COMPOSE_FILE" ]; then
        echo "unknown"  
        return 1
    fi
    if grep -q "^[[:space:]]*mysql:" "$COMPOSE_FILE"; then
        echo "mysql"
        return 0
    elif grep -q "^[[:space:]]*mariadb:" "$COMPOSE_FILE"; then
        echo "mariadb"
        return 1
    else
        echo "sqlite"  
        return 1
    fi
}


function find_free_port() {
    for port in {3300..3330}; do
        if ! ss -tuln | grep -q ":$port "; then
            echo "$port"
            return 0
        fi
    done
    echo -e "\033[31m[ERROR] No free port found between 3300 and 3330.\033[0m"
    exit 1
}

function fix_update_issues() {
    echo -e "\e[33mTrying to fix update issues by changing mirrors...\033[0m"

    cp /etc/apt/sources.list /etc/apt/sources.list.backup

    if [ -f /etc/os-release ]; then
        . /etc/apt/sources.list
        VERSION_ID=$(cat /etc/os-release | grep VERSION_ID | cut -d '"' -f2)
        UBUNTU_CODENAME=$(cat /etc/os-release | grep UBUNTU_CODENAME | cut -d '=' -f2)
    else
        echo -e "\e[91mCould not detect Ubuntu version.\033[0m"
        return 1
    fi

    MIRRORS=(
        "archive.ubuntu.com"
        "us.archive.ubuntu.com"
        "fr.archive.ubuntu.com"
        "de.archive.ubuntu.com"
        "mirrors.digitalocean.com"
        "mirrors.linode.com"
    )

    for mirror in "${MIRRORS[@]}"; do
        echo -e "\e[33mTrying mirror: $mirror\033[0m"
        cat > /etc/apt/sources.list << EOF
deb http://$mirror/ubuntu/ $UBUNTU_CODENAME main restricted universe multiverse
deb http://$mirror/ubuntu/ $UBUNTU_CODENAME-updates main restricted universe multiverse
deb http://$mirror/ubuntu/ $UBUNTU_CODENAME-security main restricted universe multiverse
EOF

        if apt-get update 2>/dev/null; then
            echo -e "\e[32mSuccessfully updated using mirror: $mirror\033[0m"
            return 0
        fi
    done

    mv /etc/apt/sources.list.backup /etc/apt/sources.list
    echo -e "\e[91mAll mirrors failed. Restored original sources.list\033[0m"
    return 1
}
