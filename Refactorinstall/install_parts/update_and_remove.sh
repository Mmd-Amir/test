if [[ -n "${__MIRZA_UPDATE_REMOVE_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_UPDATE_REMOVE_LOADED=1

# shellcheck shell=bash
# Mirza installer module: update_and_remove
# This file is sourced by install.sh
function update_bot() {
    echo "Updating Mirza Bot..."

    if ! sudo apt update && sudo apt upgrade -y; then
        echo -e "\e[91mError updating the server. Exiting...\033[0m"
        exit 1
    fi
    echo -e "\e[92mServer packages updated successfully...\033[0m\n"

    BOT_DIR="/var/www/html/mirzabotconfig"
    if [ ! -d "$BOT_DIR" ]; then
        echo -e "\e[91mError: Mirza Bot is not installed. Please install it first.\033[0m"
        exit 1
    fi

    if [[ "$1" == "-beta" ]] || [[ "$1" == "-v" && "$2" == "beta" ]]; then
        ZIP_URL="https://github.com/Mmd-Amir/mirza_pro/archive/refs/heads/main.zip"
    else
        ZIP_URL=$(curl -s https://api.github.com/repos/Mmd-Amir/mirza_pro/releases/latest | grep "zipball_url" | cut -d '"' -f4)
    fi

    TEMP_DIR="/tmp/mirzabot_update"
    mkdir -p "$TEMP_DIR"

    wget -O "$TEMP_DIR/bot.zip" "$ZIP_URL" || {
        echo -e "\e[91mError: Failed to download update package.\033[0m"
        exit 1
    }
    unzip "$TEMP_DIR/bot.zip" -d "$TEMP_DIR"

    EXTRACTED_DIR=$(find "$TEMP_DIR" -mindepth 1 -maxdepth 1 -type d)

    CONFIG_PATH="/var/www/html/mirzabotconfig/config.php"
    TEMP_CONFIG="/root/mirza_config_backup.php"
    if [ -f "$CONFIG_PATH" ]; then
        cp "$CONFIG_PATH" "$TEMP_CONFIG" || {
            echo -e "\e[91mConfig file backup failed!\033[0m"
            exit 1
        }
    fi

    sudo rm -rf /var/www/html/mirzabotconfig || {
        echo -e "\e[91mFailed to remove old bot files!\033[0m"
        exit 1
    }

    sudo mkdir -p /var/www/html/mirzabotconfig
    sudo mv "$EXTRACTED_DIR"/* /var/www/html/mirzabotconfig/ || {
        echo -e "\e[91mFile transfer failed!\033[0m"
        exit 1
    }

    if [ -f "$TEMP_CONFIG" ]; then
        sudo mv "$TEMP_CONFIG" "$CONFIG_PATH" || {
            echo -e "\e[91mConfig file restore failed!\033[0m"
            exit 1
        }
    fi

    INSTALL_SCRIPT_PATH=$(find /var/www/html/mirzabotconfig -maxdepth 2 -name "install.sh" -print -quit)
    if [ -n "$INSTALL_SCRIPT_PATH" ]; then
        sudo cp "$INSTALL_SCRIPT_PATH" /root/install.sh
        echo -e "\n\e[92mCopied latest install.sh to /root/install.sh.\033[0m"
    else
        RAW_INSTALL_URL="https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install.sh"
        if curl -fsSL "$RAW_INSTALL_URL" -o /root/install.sh; then
            echo -e "\n\e[92mFetched install.sh from upstream repository.\033[0m"
        else
            echo -e "\n\e[91mWarning: install.sh not found locally and download failed. Existing /root/install.sh left untouched.\033[0m"
        fi
    fi
    sudo chown -R www-data:www-data /var/www/html/mirzabotconfig/
    sudo chmod -R 755 /var/www/html/mirzabotconfig/

    URL=$(grep -oP "\$domainhosts\s*=\s*[\'\"]\K[^\'\"]+" "$CONFIG_PATH" 2>/dev/null | head -1)
    if [ -z "$URL" ]; then
        URL=$(grep "domainhosts" "$CONFIG_PATH" | sed -n "s/.*domainhosts.*=.*[\'\"]\([^\'\"]*\)[\'\"].*/\1/p" | head -1)
    fi
    if [ -n "$URL" ]; then
        CLEAN_URL=${URL#http://}
        CLEAN_URL=${CLEAN_URL#https://}
        CLEAN_URL=${CLEAN_URL%/}
        curl -s "https://${CLEAN_URL}/table.php" || {
            echo -e "\e[91mSetup script execution failed for https://${CLEAN_URL}/table.php!\033[0m"
        }
    else
        echo -e "\e[91mUnable to detect domainhosts from config.php. Skipping table setup call.\033[0m"
    fi

    echo -e "\e[33m[VERIFY] Checking if database tables are created...\033[0m"

    DB_USERNAME=$(grep '^\$usernamedb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_PASSWORD=$(grep '^\$passworddb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_NAME=$(grep '^\$dbname' "$CONFIG_PATH" | awk -F"'" '{print $2}')

    if [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ] || [ -z "$DB_NAME" ]; then
        echo -e "\e[91m[ERROR] Failed to read database credentials from config.php. Cannot verify tables.\033[0m"
    else
        TABLES=$(mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -D "$DB_NAME" -e "SHOW TABLES LIKE 'setting';" 2>&1)

        if echo "$TABLES" | grep -q "setting"; then
            echo -e "\e[92m[SUCCESS] Database table 'setting' exists!\033[0m"
        else
            echo -e "\e[91m[ERROR] Database table 'setting' NOT FOUND!\033[0m"
            echo -e "\e[91mPlease check /var/log/mirzabot_setup.log for details\033[0m"
        fi
    fi

    rm -rf "$TEMP_DIR"

    echo -e "\n\e[92mMirza Bot updated to latest version successfully!\033[0m"

    if [ -f "/root/install.sh" ]; then
        sudo chmod +x /root/install.sh
        sudo ln -sf /root/install.sh /usr/local/bin/mirza >/dev/null 2>&1
        echo -e "\e[92mEnsured /root/install.sh is executable and 'mirza' command is linked.\033[0m"
    else
        echo -e "\e[91mError: /root/install.sh not found after update attempt. Cannot make it executable or link 'mirza' command.\033[0m"
    fi
}


function remove_bot() {
    echo -e "\e[33mStarting Mirza Bot removal process...\033[0m"
    LOG_FILE="/var/log/remove_bot.log"
    echo "Log file: $LOG_FILE" > "$LOG_FILE"

    BOT_DIR="/var/www/html/mirzabotconfig"
    if [ ! -d "$BOT_DIR" ]; then
        echo -e "\e[31m[ERROR]\033[0m Mirza Bot is not installed (/var/www/html/mirzabotconfig not found)." | tee -a "$LOG_FILE"
        echo -e "\e[33mNothing to remove. Exiting...\033[0m" | tee -a "$LOG_FILE"
        sleep 2
        exit 1
    fi

    read -p "Are you sure you want to remove Mirza Bot and its dependencies? (y/n): " choice
    if [[ "$choice" != "y" ]]; then
        echo "Aborting..." | tee -a "$LOG_FILE"
        exit 0
    fi

    if check_marzban_installed; then
        echo -e "\e[41m[IMPORTANT NOTICE]\033[0m \e[33mMarzban detected. Proceeding with Marzban-compatible removal.\033[0m" | tee -a "$LOG_FILE"
        remove_bot_with_marzban
        return 0
    fi

    echo "Removing Mirza Bot..." | tee -a "$LOG_FILE"

    if [ -d "$BOT_DIR" ]; then
        sudo rm -rf "$BOT_DIR" && echo -e "\e[92mBot directory removed: $BOT_DIR\033[0m" | tee -a "$LOG_FILE" || {
            echo -e "\e[91mFailed to remove bot directory: $BOT_DIR. Exiting...\033[0m" | tee -a "$LOG_FILE"
            exit 1
        }
    fi

    CONFIG_PATH="/root/config.php"
    if [ -f "$CONFIG_PATH" ]; then
        sudo shred -u -n 5 "$CONFIG_PATH" && echo -e "\e[92mConfig file securely removed: $CONFIG_PATH\033[0m" | tee -a "$LOG_FILE" || {
            echo -e "\e[91mFailed to securely remove config file: $CONFIG_PATH\033[0m" | tee -a "$LOG_FILE"
        }
    fi

    echo -e "\e[33mRemoving MySQL and database...\033[0m" | tee -a "$LOG_FILE"
    sudo systemctl stop mysql
    sudo systemctl disable mysql
    sudo systemctl daemon-reload

    sudo apt --fix-broken install -y

    sudo apt-get purge -y mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*
    sudo rm -rf /etc/mysql /var/lib/mysql /var/log/mysql /var/log/mysql.* /usr/lib/mysql /usr/include/mysql /usr/share/mysql
    sudo rm /lib/systemd/system/mysql.service
    sudo rm /etc/init.d/mysql

    sudo dpkg --remove --force-remove-reinstreq mysql-server mysql-server-8.0

    sudo find /etc/systemd /lib/systemd /usr/lib/systemd -name "*mysql*" -exec rm -f {} \;

    sudo apt-get purge -y mysql-server mysql-server-8.0 mysql-client mysql-client-8.0
    sudo apt-get purge -y mysql-client-core-8.0 mysql-server-core-8.0 mysql-common php-mysql php8.2-mysql php8.2-mysql php-mariadb-mysql-kbs

    sudo apt-get autoremove --purge -y
    sudo apt-get clean
    sudo apt-get update

    echo -e "\e[92mMySQL has been completely removed.\033[0m" | tee -a "$LOG_FILE"

    echo -e "\e[33mRemoving PHPMyAdmin...\033[0m" | tee -a "$LOG_FILE"
    if dpkg -s phpmyadmin &>/dev/null; then
        sudo apt-get purge -y phpmyadmin && echo -e "\e[92mPHPMyAdmin removed.\033[0m" | tee -a "$LOG_FILE"
        sudo apt-get autoremove -y && sudo apt-get autoclean -y
    else
        echo -e "\e[93mPHPMyAdmin is not installed.\033[0m" | tee -a "$LOG_FILE"
    fi

    echo -e "\e[33mRemoving Apache...\033[0m" | tee -a "$LOG_FILE"
    sudo systemctl stop apache2 || {
        echo -e "\e[91mFailed to stop Apache. Continuing anyway...\033[0m" | tee -a "$LOG_FILE"
    }
    sudo systemctl disable apache2 || {
        echo -e "\e[91mFailed to disable Apache. Continuing anyway...\033[0m" | tee -a "$LOG_FILE"
    }
    sudo apt-get purge -y apache2 apache2-utils apache2-bin apache2-data libapache2-mod-php* || {
        echo -e "\e[91mFailed to purge Apache packages.\033[0m" | tee -a "$LOG_FILE"
    }
    sudo apt-get autoremove --purge -y
    sudo apt-get autoclean -y
    sudo rm -rf /etc/apache2 /var/www/html/mirzabotconfig

    echo -e "\e[33mRemoving Apache and PHP configurations...\033[0m" | tee -a "$LOG_FILE"
    sudo a2disconf phpmyadmin.conf &>/dev/null
    sudo rm -f /etc/apache2/conf-available/phpmyadmin.conf
    sudo systemctl restart apache2

    echo -e "\e[33mRemoving additional packages...\033[0m" | tee -a "$LOG_FILE"
    sudo apt-get remove -y php-soap php-ssh2 libssh2-1-dev libssh2-1 \
        && echo -e "\e[92mRemoved additional PHP packages.\033[0m" | tee -a "$LOG_FILE" || echo -e "\e[93mSome additional PHP packages may not be installed.\033[0m" | tee -a "$LOG_FILE"

    echo -e "\e[33mResetting firewall rules (except SSL)...\033[0m" | tee -a "$LOG_FILE"
    sudo ufw delete allow 'Apache'
    sudo ufw reload

    echo -e "\e[92mMirza Bot, MySQL, and their dependencies have been completely removed.\033[0m" | tee -a "$LOG_FILE"
}


function remove_bot_with_marzban() {
    echo -e "\e[33mRemoving Mirza Bot alongside Marzban...\033[0m" | tee -a "$LOG_FILE"

    BOT_DIR="/var/www/html/mirzabotconfig"

    if [ ! -d "$BOT_DIR" ]; then
        echo -e "\e[93mWarning: Bot directory $BOT_DIR not found. Assuming it was already removed.\033[0m" | tee -a "$LOG_FILE"
        DB_NAME="mirzabot"  
        DB_USER=""
    else
        CONFIG_PATH="$BOT_DIR/config.php"
        if [ -f "$CONFIG_PATH" ]; then
            DB_USER=$(grep '^\$usernamedb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
            DB_NAME=$(grep '^\$dbname' "$CONFIG_PATH" | awk -F"'" '{print $2}')
            if [ -z "$DB_USER" ] || [ -z "$DB_NAME" ]; then
                echo -e "\e[91mError: Could not extract database credentials from $CONFIG_PATH. Using defaults.\033[0m" | tee -a "$LOG_FILE"
                DB_NAME="mirzabot"  
                DB_USER=""
            else
                echo -e "\e[92mFound database credentials: User=$DB_USER, Database=$DB_NAME\033[0m" | tee -a "$LOG_FILE"
            fi
        else
            echo -e "\e[93mWarning: config.php not found at $CONFIG_PATH. Assuming default database name 'mirzabot'.\033[0m" | tee -a "$LOG_FILE"
            DB_NAME="mirzabot"
            DB_USER=""
        fi

        sudo rm -rf "$BOT_DIR" && echo -e "\e[92mBot directory removed: $BOT_DIR\033[0m" | tee -a "$LOG_FILE" || {
            echo -e "\e[91mFailed to remove bot directory: $BOT_DIR. Exiting...\033[0m" | tee -a "$LOG_FILE"
            exit 1
        }
    fi

    ENV_FILE="/opt/marzban/.env"
    if [ -f "$ENV_FILE" ]; then
        MYSQL_ROOT_PASSWORD=$(grep "MYSQL_ROOT_PASSWORD=" "$ENV_FILE" | cut -d'=' -f2 | tr -d '[:space:]' | sed 's/"//g')
        ROOT_USER="root"
    else
        echo -e "\e[91mError: Marzban .env file not found. Cannot proceed without MySQL root password.\033[0m" | tee -a "$LOG_FILE"
        exit 1
    fi

    MYSQL_CONTAINER=$(docker ps -q --filter "name=mysql" --no-trunc)
    if [ -z "$MYSQL_CONTAINER" ]; then
        echo -e "\e[91mError: Could not find a running MySQL container. Ensure Marzban is running.\033[0m" | tee -a "$LOG_FILE"
        exit 1
    fi

    if [ -n "$DB_NAME" ]; then
        echo -e "\e[33mRemoving database $DB_NAME...\033[0m" | tee -a "$LOG_FILE"
        docker exec "$MYSQL_CONTAINER" bash -c "mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD' -e \"DROP DATABASE IF EXISTS $DB_NAME;\"" && {
            echo -e "\e[92mDatabase $DB_NAME removed successfully.\033[0m" | tee -a "$LOG_FILE"
        } || {
            echo -e "\e[91mFailed to remove database $DB_NAME.\033[0m" | tee -a "$LOG_FILE"
        }
    fi

    if [ -n "$DB_USER" ]; then
        echo -e "\e[33mRemoving database user $DB_USER...\033[0m" | tee -a "$LOG_FILE"
        docker exec "$MYSQL_CONTAINER" bash -c "mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD' -e \"DROP USER IF EXISTS '$DB_USER'@'%'; FLUSH PRIVILEGES;\"" && {
            echo -e "\e[92mUser $DB_USER removed successfully.\033[0m" | tee -a "$LOG_FILE"
        } || {
            echo -e "\e[91mFailed to remove user $DB_USER.\033[0m" | tee -a "$LOG_FILE"
        }
    else
        echo -e "\e[93mWarning: No database user specified. Checking for non-default users...\033[0m" | tee -a "$LOG_FILE"
        MIRZA_USERS=$(docker exec "$MYSQL_CONTAINER" bash -c "mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD' -e \"SELECT User FROM mysql.user WHERE User NOT IN ('root', 'mysql.infoschema', 'mysql.session', 'mysql.sys', 'marzban');\"" | grep -v "User" | awk '{print $1}')
        if [ -n "$MIRZA_USERS" ]; then
            for user in $MIRZA_USERS; do
                echo -e "\e[33mRemoving detected non-default user: $user...\033[0m" | tee -a "$LOG_FILE"
                docker exec "$MYSQL_CONTAINER" bash -c "mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD' -e \"DROP USER IF EXISTS '$user'@'%'; FLUSH PRIVILEGES;\"" && {
                    echo -e "\e[92mUser $user removed successfully.\033[0m" | tee -a "$LOG_FILE"
                } || {
                    echo -e "\e[91mFailed to remove user $user.\033[0m" | tee -a "$LOG_FILE"
                }
            done
        else
            echo -e "\e[93mNo non-default users found.\033[0m" | tee -a "$LOG_FILE"
        fi
    fi

    echo -e "\e[33mRemoving Apache...\033[0m" | tee -a "$LOG_FILE"
    sudo systemctl stop apache2 || {
        echo -e "\e[91mFailed to stop Apache. Continuing anyway...\033[0m" | tee -a "$LOG_FILE"
    }
    sudo systemctl disable apache2 || {
        echo -e "\e[91mFailed to disable Apache. Continuing anyway...\033[0m" | tee -a "$LOG_FILE"
    }
    sudo apt-get purge -y apache2 apache2-utils apache2-bin apache2-data libapache2-mod-php* || {
        echo -e "\e[91mFailed to purge Apache packages.\033[0m" | tee -a "$LOG_FILE"
    }
    sudo apt-get autoremove --purge -y
    sudo apt-get autoclean -y
    sudo rm -rf /etc/apache2 /var/www/html/mirzabotconfig

    echo -e "\e[33mResetting firewall rules (keeping SSL)...\033[0m" | tee -a "$LOG_FILE"
    sudo ufw delete allow 'Apache' || {
        echo -e "\e[91mFailed to remove Apache rule from UFW.\033[0m" | tee -a "$LOG_FILE"
    }
    sudo ufw reload

    echo -e "\e[92mMirza Bot has been removed alongside Marzban. SSL certificates remain intact.\033[0m" | tee -a "$LOG_FILE"
}
