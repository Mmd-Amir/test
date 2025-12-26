if [[ -n "${__MIRZA_INSTALL_MARZBAN_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_INSTALL_MARZBAN_LOADED=1

# shellcheck shell=bash
# Mirza installer module: install_bot_with_marzban
# This file is sourced by install.sh
function install_bot_with_marzban() {
    echo -e "\033[41m[IMPORTANT WARNING]\033[0m \033[1;33mMarzban panel is detected on your server. Please make sure to backup the Marzban database before installing Mirza Bot.\033[0m"
    read -p "Are you sure you want to install Mirza Bot alongside Marzban? (y/n): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        echo -e "\e[91mInstallation aborted by user.\033[0m"
        exit 0
    fi

    echo -e "\e[32mChecking Marzban database type...\033[0m"
    DB_TYPE=$(detect_database_type)
    if [ "$DB_TYPE" != "mysql" ]; then
        echo -e "\e[91mError: Your database is $DB_TYPE. To install Mirza Bot, you must use MySQL.\033[0m"
        echo -e "\e[93mPlease configure Marzban to use MySQL and try again.\033[0m"
        exit 1
    fi
    echo -e "\e[92mMySQL detected. Proceeding with installation...\033[0m"

    echo -e "\e[32mChecking port availability...\033[0m"
    if sudo ss -tuln | grep -q ":80 "; then
        echo -e "\e[91mError: Port 80 is already in use. Please free port 80 and run the script again.\033[0m"
        exit 1
    fi
    if sudo ss -tuln | grep -q ":88 "; then
        echo -e "\e[91mError: Port 88 is already in use. Please free port 88 and run the script again.\033[0m"
        exit 1
    fi
    echo -e "\e[92mPorts 80 and 88 are free. Proceeding with installation...\033[0m"

    if ! (sudo apt update && sudo apt upgrade -y); then
        echo -e "\e[93mUpdate/upgrade failed. Attempting to fix using alternative mirrors...\033[0m"
        if fix_update_issues; then
            if sudo apt update && sudo apt upgrade -y; then
                echo -e "\e[92mSystem updated successfully after fixing mirrors...\033[0m\n"
            else
                echo -e "\e[91mError: Failed to update even after trying alternative mirrors.\033[0m"
                exit 1
            fi
        else
            echo -e "\e[91mError: Failed to update/upgrade system and mirror fix failed.\033[0m"
            exit 1
        fi
    else
        echo -e "\e[92mSystem updated successfully...\033[0m\n"
    fi

    sudo apt-get install software-properties-common || {
        echo -e "\e[91mError: Failed to install software-properties-common.\033[0m"
        exit 1
    }

    echo -e "\e[32mChecking and installing MySQL client...\033[0m"
    if ! command -v mysql &>/dev/null; then
        sudo apt install -y mysql-client || {
            echo -e "\e[91mError: Failed to install MySQL client. Please install it manually and try again.\033[0m"
            exit 1
        }
        echo -e "\e[92mMySQL client installed successfully.\033[0m"
    else
        echo -e "\e[92mMySQL client is already installed.\033[0m"
    fi

    sudo apt install -y software-properties-common || {
        echo -e "\e[91mError: Failed to install software-properties-common.\033[0m"
        exit 1
    }
    sudo add-apt-repository -y ppa:ondrej/php || {
        echo -e "\e[91mError: Failed to add PPA ondrej/php. Trying with locale override...\033[0m"
        sudo LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php || {
            echo -e "\e[91mError: Failed to add PPA even with locale override.\033[0m"
            exit 1
        }
    }
    sudo apt update || {
        echo -e "\e[91mError: Failed to update package list after adding PPA.\033[0m"
        exit 1
    }

    sudo apt install -y git unzip curl wget jq || {
        echo -e "\e[91mError: Failed to install basic tools.\033[0m"
        exit 1
    }

    if ! dpkg -s apache2 &>/dev/null; then
        sudo apt install -y apache2 || {
            echo -e "\e[91mError: Failed to install Apache2.\033[0m"
            exit 1
        }
    fi

    DEBIAN_FRONTEND=noninteractive sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-zip php8.2-gd php8.2-curl php8.2-soap php8.2-ssh2 libssh2-1-dev libssh2-1 php8.2-pdo || {
        echo -e "\e[91mError: Failed to install PHP 8.2 and modules.\033[0m"
        exit 1
    }

    sudo apt install -y libapache2-mod-php8.2 || {
        echo -e "\e[91mError: Failed to install libapache2-mod-php8.2.\033[0m"
        exit 1
    }

    sudo apt install -y python3-certbot-apache || {
        echo -e "\e[91mError: Failed to install Certbot for Apache.\033[0m"
        exit 1
    }
    sudo systemctl enable certbot.timer || {
        echo -e "\e[91mError: Failed to enable certbot timer.\033[0m"
        exit 1
    }

    if ! dpkg -s ufw &>/dev/null; then
        sudo apt install -y ufw || {
            echo -e "\e[91mError: Failed to install UFW.\033[0m"
            exit 1
        }
    fi

    ENV_FILE="/opt/marzban/.env"
    if [ ! -f "$ENV_FILE" ]; then
        echo -e "\e[91mError: Marzban .env file not found. Cannot proceed without Marzban configuration.\033[0m"
        exit 1
    fi

    MYSQL_ROOT_PASSWORD=$(grep "MYSQL_ROOT_PASSWORD=" "$ENV_FILE" | cut -d'=' -f2 | tr -d '[:space:]' | sed 's/"//g')
    ROOT_USER="root"

    if [ -z "$MYSQL_ROOT_PASSWORD" ]; then
        echo -e "\e[93mWarning: Could not retrieve MySQL root password from Marzban .env file.\033[0m"
        read -s -p "Please enter the MySQL root password manually: " MYSQL_ROOT_PASSWORD
        echo
    fi

    MYSQL_CONTAINER=$(docker ps -q --filter "name=mysql" --no-trunc)
    if [ -z "$MYSQL_CONTAINER" ]; then
        echo -e "\e[91mError: Could not find a running MySQL container. Ensure Marzban is running with Docker.\033[0m"
        echo -e "\e[93mRunning containers:\033[0m"
        docker ps
        exit 1
    fi

    echo "Testing MySQL connection..."

    if [ -f "/opt/marzban/.env" ]; then
        MYSQL_ROOT_PASSWORD=$(grep -E '^MYSQL_ROOT_PASSWORD=' /opt/marzban/.env | cut -d '=' -f2- | tr -d '" \n\r')
        if [ -z "$MYSQL_ROOT_PASSWORD" ]; then
            echo -e "\e[93mWarning: MYSQL_ROOT_PASSWORD not found in .env. Please enter it manually.\033[0m"
            read -s -p "Enter MySQL root password: " MYSQL_ROOT_PASSWORD
            echo
        fi
    else
        echo -e "\e[93mWarning: .env file not found. Please enter MySQL root password manually.\033[0m"
        read -s -p "Enter MySQL root password: " MYSQL_ROOT_PASSWORD
        echo
    fi

    ROOT_USER="root"
    echo -e "\e[32mUsing MySQL container: $(docker inspect -f '{{.Name}}' "$MYSQL_CONTAINER" | cut -c2-)\033[0m"

    mysql -u "$ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" -h 127.0.0.1 -P 3306 -e "SELECT 1;" 2>/tmp/mysql_error.log
    if [ $? -eq 0 ]; then
        echo -e "\e[92mMySQL connection successful (direct host method).\033[0m"
    else
        if [ -n "$MYSQL_CONTAINER" ]; then
            echo -e "\e[93mDirect connection failed, trying inside container...\033[0m"
            docker exec "$MYSQL_CONTAINER" bash -c "echo 'SELECT 1;' | mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD'" 2>/tmp/mysql_error.log
            if [ $? -eq 0 ]; then
                echo -e "\e[92mMySQL connection successful (container method).\033[0m"
            else
                echo -e "\e[91mError: Failed to connect to MySQL using both methods.\033[0m"
                echo -e "\e[93mPassword used: '$MYSQL_ROOT_PASSWORD'\033[0m"
                echo -e "\e[93mError details:\033[0m"
                cat /tmp/mysql_error.log
                echo -e "\e[93mPlease ensure MySQL is running and the root password is correct.\033[0m"
                read -s -p "Enter the correct MySQL root password: " NEW_PASSWORD
                echo
                MYSQL_ROOT_PASSWORD="$NEW_PASSWORD"
                mysql -u "$ROOT_USER" -p"$MYSQL_ROOT_PASSWORD" -h 127.0.0.1 -P 3306 -e "SELECT 1;" 2>/tmp/mysql_error.log || {
                    docker exec "$MYSQL_CONTAINER" bash -c "echo 'SELECT 1;' | mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD'" 2>/tmp/mysql_error.log || {
                        echo -e "\e[91mError: Still can't connect with new password.\033[0m"
                        echo -e "\e[93mError details:\033[0m"
                        cat /tmp/mysql_error.log
                        exit 1
                    }
                }
                echo -e "\e[92mMySQL connection successful with new password.\033[0m"
            fi
        else
            echo -e "\e[91mError: No MySQL container found and direct connection failed.\033[0m"
            echo -e "\e[93mPassword used: '$MYSQL_ROOT_PASSWORD'\033[0m"
            echo -e "\e[93mError details:\033[0m"
            cat /tmp/mysql_error.log
            exit 1
        fi
    fi

    clear
    echo -e "\e[33mConfiguring Mirza Bot database credentials...\033[0m"
    default_dbuser=$(openssl rand -base64 12 | tr -dc 'a-zA-Z' | head -c8)
    printf "\e[33m[+] \e[36mDatabase username (default: $default_dbuser): \033[0m"
    read dbuser
    if [ -z "$dbuser" ]; then
        dbuser="$default_dbuser"
    fi

    default_dbpass=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c12)
    printf "\e[33m[+] \e[36mDatabase password (default: $default_dbpass): \033[0m"
    read -s dbpass
    echo
    if [ -z "$dbpass" ]; then
        dbpass="$default_dbpass"
    fi
    dbname="mirzabot"

    docker exec "$MYSQL_CONTAINER" bash -c "mysql -u '$ROOT_USER' -p'$MYSQL_ROOT_PASSWORD' -e \"CREATE DATABASE IF NOT EXISTS $dbname; CREATE USER IF NOT EXISTS '$dbuser'@'%' IDENTIFIED BY '$dbpass'; GRANT ALL PRIVILEGES ON $dbname.* TO '$dbuser'@'%'; FLUSH PRIVILEGES;\"" || {
        echo -e "\e[91mError: Failed to create database or user in Marzban MySQL container.\033[0m"
        exit 1
    }
    echo -e "\e[92mDatabase '$dbname' created successfully.\033[0m"

    BOT_DIR="/var/www/html/mirzabotconfig"
    if [ -d "$BOT_DIR" ]; then
        echo -e "\e[93mDirectory $BOT_DIR already exists. Removing...\033[0m"
        sudo rm -rf "$BOT_DIR" || {
            echo -e "\e[91mError: Failed to remove existing directory $BOT_DIR.\033[0m"
            exit 1
        }
    fi
    sudo mkdir -p "$BOT_DIR" || {
        echo -e "\e[91mError: Failed to create directory $BOT_DIR.\033[0m"
        exit 1
    }

    ZIP_URL=$(curl -s https://api.github.com/repos/Mmd-Amir/mirza_pro/releases/latest | grep "zipball_url" | cut -d '"' -f 4)
    if [[ "$1" == "-v" && "$2" == "beta" ]] || [[ "$1" == "-beta" ]] || [[ "$1" == "-" && "$2" == "beta" ]]; then
        ZIP_URL="https://github.com/Mmd-Amir/mirza_pro/archive/refs/heads/main.zip"
    elif [[ "$1" == "-v" && -n "$2" ]]; then
        ZIP_URL="https://github.com/Mmd-Amir/mirza_pro/archive/refs/tags/$2.zip"
    fi

    TEMP_DIR="/tmp/mirzabot"
    mkdir -p "$TEMP_DIR"
    wget -O "$TEMP_DIR/bot.zip" "$ZIP_URL" || {
        echo -e "\e[91mError: Failed to download bot files.\033[0m"
        exit 1
    }
    unzip "$TEMP_DIR/bot.zip" -d "$TEMP_DIR" || {
        echo -e "\e[91mError: Failed to unzip bot files.\033[0m"
        exit 1
    }
    EXTRACTED_DIR=$(find "$TEMP_DIR" -mindepth 1 -maxdepth 1 -type d)
    mv "$EXTRACTED_DIR"/* "$BOT_DIR" || {
        echo -e "\e[91mError: Failed to move bot files.\033[0m"
        exit 1
    }
    rm -rf "$TEMP_DIR"

    sudo chown -R www-data:www-data "$BOT_DIR"
    sudo chmod -R 755 "$BOT_DIR"
    echo -e "\e[92mBot files installed in $BOT_DIR.\033[0m"
    sleep 3
    clear

    echo -e "\e[32mConfiguring Apache ports...\033[0m"
    sudo bash -c "echo -n > /etc/apache2/ports.conf"  
    cat <<EOF | sudo tee /etc/apache2/ports.conf

Listen 80
Listen 88

EOF
    if [ $? -ne 0 ]; then
        echo -e "\e[91mError: Failed to configure ports.conf.\033[0m"
        exit 1
    fi

    sudo bash -c "echo -n > /etc/apache2/sites-available/000-default.conf"  
    cat <<EOF | sudo tee /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

EOF
    if [ $? -ne 0 ]; then
        echo -e "\e[91mError: Failed to configure 000-default.conf.\033[0m"
        exit 1
    fi

    sudo systemctl enable apache2 || {
        echo -e "\e[91mError: Failed to enable Apache2.\033[0m"
        exit 1
    }
    sudo systemctl restart apache2 || {
        echo -e "\e[91mError: Failed to restart Apache2.\033[0m"
        exit 1
    }

    echo -e "\e[32mConfiguring SSL on port 88...\033[0m\n"
    sudo ufw allow 80 || {
        echo -e "\e[91mError: Failed to configure firewall for port 80.\033[0m"
        exit 1
    }
    sudo ufw allow 88 || {
        echo -e "\e[91mError: Failed to configure firewall for port 88.\033[0m"
        exit 1
    }
    clear
    printf "\e[33m[+] \e[36mEnter the domain (e.g., example.com): \033[0m"
    read domainname
    while [[ ! "$domainname" =~ ^[a-zA-Z0-9.-]+$ ]]; do
        echo -e "\e[91mInvalid domain format. Must be like 'example.com'. Please try again.\033[0m"
        printf "\e[33m[+] \e[36mEnter the domain (e.g., example.com): \033[0m"
        read domainname
    done
    DOMAIN_NAME="$domainname"
    echo -e "\e[92mDomain set to: $DOMAIN_NAME\033[0m"

    sudo systemctl restart apache2 || {
        echo -e "\e[91mError: Failed to restart Apache2 before Certbot.\033[0m"
        exit 1
    }
    if ! wait_for_certbot; then
        echo -e "\e[91mError: Certbot is busy. Please try again shortly.\033[0m"
        exit 1
    fi
    sudo certbot --apache --agree-tos --preferred-challenges http -d "$DOMAIN_NAME" --https-port 88 --no-redirect || {
        echo -e "\e[91mError: Failed to configure SSL with Certbot on port 88.\033[0m"
        exit 1
    }

    sudo bash -c "echo -n > /etc/apache2/sites-available/000-default-le-ssl.conf"  
    cat <<EOF | sudo tee /etc/apache2/sites-available/000-default-le-ssl.conf
<IfModule mod_ssl.c>
<VirtualHost *:88>
    ServerAdmin webmaster@localhost
    ServerName $DOMAIN_NAME
    DocumentRoot /var/www/html
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/$DOMAIN_NAME/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/$DOMAIN_NAME/privkey.pem
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
</VirtualHost>
</IfModule>
EOF
    if [ $? -ne 0 ]; then
        echo -e "\e[91mError: Failed to create SSL VirtualHost configuration.\033[0m"
        exit 1
    fi
    sudo a2enmod ssl || {
        echo -e "\e[91mError: Failed to enable SSL module.\033[0m"
        exit 1
    }
    sudo a2ensite 000-default-le-ssl.conf || {
        echo -e "\e[91mError: Failed to enable SSL site.\033[0m"
        exit 1
    }
    sudo bash -c "echo -n > /etc/apache2/ports.conf"
    cat <<EOF | sudo tee /etc/apache2/ports.conf
Listen 88
EOF
    sudo apache2ctl configtest || {
        echo -e "\e[91mError: Apache configuration test failed after Certbot.\033[0m"
        exit 1
    }
    sudo systemctl restart apache2 || {
        echo -e "\e[91mError: Failed to restart Apache2 after SSL configuration.\033[0m"
        systemctl status apache2.service
        exit 1
    }

    echo -e "\e[32mDisabling port 80 as it's no longer needed...\033[0m"
    sudo a2dissite 000-default.conf || {
        echo -e "\e[91mError: Failed to disable port 80 VirtualHost.\033[0m"
        exit 1
    }
    sudo ufw delete allow 80 || {
        echo -e "\e[91mError: Failed to remove port 80 from firewall.\033[0m"
        exit 1
    }
    sudo apache2ctl configtest || {
        echo -e "\e[91mError: Apache configuration test failed.\033[0m"
        exit 1
    }
    sudo systemctl restart apache2 || {
        echo -e "\e[91mError: Failed to restart Apache2 after disabling port 80.\033[0m"
        systemctl status apache2.service
        exit 1
    }
    echo -e "\e[92mSSL configured successfully on port 88. Port 80 disabled.\033[0m"
    sleep 3
    clear

    printf "\e[33m[+] \e[36mBot Token: \033[0m"
    read YOUR_BOT_TOKEN
    while [[ ! "$YOUR_BOT_TOKEN" =~ ^[0-9]{8,10}:[a-zA-Z0-9_-]{35}$ ]]; do
        echo -e "\e[91mInvalid bot token format. Please try again.\033[0m"
        printf "\e[33m[+] \e[36mBot Token: \033[0m"
        read YOUR_BOT_TOKEN
    done

    printf "\e[33m[+] \e[36mChat id: \033[0m"
    read YOUR_CHAT_ID
    while [[ ! "$YOUR_CHAT_ID" =~ ^-?[0-9]+$ ]]; do
        echo -e "\e[91mInvalid chat ID format. Please try again.\033[0m"
        printf "\e[33m[+] \e[36mChat id: \033[0m"
        read YOUR_CHAT_ID
    done

    YOUR_DOMAIN="$DOMAIN_NAME:88"  
    printf "\e[33m[+] \e[36mUsernamebot: \033[0m"
    read YOUR_BOTNAME
    while [ -z "$YOUR_BOTNAME" ]; do
        echo -e "\e[91mError: Bot username cannot be empty.\033[0m"
        printf "\e[33m[+] \e[36mUsernamebot: \033[0m"
        read YOUR_BOTNAME
    done

    ASAS="$"
    secrettoken=$(openssl rand -base64 10 | tr -dc 'a-zA-Z0-9' | cut -c1-8)
    cat <<EOF > "$BOT_DIR/config.php"
<?php
${ASAS}APIKEY = '$YOUR_BOT_TOKEN';
${ASAS}usernamedb = '$dbuser';
${ASAS}passworddb = '$dbpass';
${ASAS}dbname = '$dbname';
${ASAS}domainhosts = '$YOUR_DOMAIN';
${ASAS}adminnumber = '$YOUR_CHAT_ID';
${ASAS}usernamebot = '$YOUR_BOTNAME';
${ASAS}secrettoken = '$secrettoken';

${ASAS}connect = mysqli_connect('127.0.0.1', \$usernamedb, \$passworddb, \$dbname);
if (${ASAS}connect->connect_error) {
    die('Database connection failed: ' . ${ASAS}connect->connect_error);
}
mysqli_set_charset(${ASAS}connect, 'utf8mb4');

${ASAS}options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
${ASAS}dsn = "mysql:host=127.0.0.1;port=3306;dbname=\$dbname;charset=utf8mb4";
try {
    ${ASAS}pdo = new PDO(\$dsn, \$usernamedb, \$passworddb, \$options);
} catch (\PDOException \$e) {
    die('PDO Connection failed: ' . \$e->getMessage());
}
?>
EOF

    curl -F "url=https://${YOUR_DOMAIN}/mirzabotconfig/index.php" \
         -F "secret_token=${secrettoken}" \
         "https://api.telegram.org/bot${YOUR_BOT_TOKEN}/setWebhook" || {
        echo -e "\e[91mError: Failed to set webhook.\033[0m"
        exit 1
    }

    MESSAGE="✅ The bot is installed! for start bot send comment /start"
    curl -s -X POST "https://api.telegram.org/bot${BOT_TOKEN}/sendMessage" -d chat_id="${CHAT_ID}" -d text="$MESSAGE" || {
        echo -e "\033[31mError: Failed to send message to Telegram.\033[0m"
        return 1
    }

    TABLE_SETUP_URL="https://${YOUR_DOMAIN}/mirzabotconfig/table.php"
    echo -e "\033[33mSetting up database tables...\033[0m"
    curl $TABLE_SETUP_URL || {
        echo -e "\033[31mError: Failed to execute table creation script at $TABLE_SETUP_URL.\033[0m"
        return 1
    }

    echo -e "\033[32mBot installed successfully!\033[0m"
    echo -e "\033[102mDomain Bot: https://$DOMAIN_NAME\033[0m"
    echo -e "\033[104mDatabase address: https://$DOMAIN_NAME/phpmyadmin\033[0m"
    echo -e "\033[33mDatabase name: \033[36m$DB_NAME\033[0m"
    echo -e "\033[33mDatabase username: \033[36m$DB_USERNAME\033[0m"
    echo -e "\033[33mDatabase password: \033[36m$DB_PASSWORD\033[0m"

    chmod +x /root/install.sh
    ln -sf /root/install.sh /usr/local/bin/mirza >/dev/null 2>&1
}
