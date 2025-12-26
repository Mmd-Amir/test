# shellcheck shell=bash
# Mirza installer module: install_standard_mysql_root_and_ssl
# This file is sourced by install.sh

if [[ -n "${__MIRZA_INSTALL_STANDARD_MYSQL_SSL_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_INSTALL_STANDARD_MYSQL_SSL_LOADED=1

install_bot_standard_mysql_root_and_ssl() {
    if [ ! -d "/root/confmirza" ]; then
    sudo mkdir /root/confmirza || {
        echo -e "\e[91mError: Failed to create /root/confmirza directory.\033[0m"
        exit 1
    }

    sleep 1

    touch /root/confmirza/dbrootmirza.txt || {
        echo -e "\e[91mError: Failed to create dbrootmirza.txt.\033[0m"
        exit 1
    }
    sudo chmod -R 777 /root/confmirza/dbrootmirza.txt || {
        echo -e "\e[91mError: Failed to set permissions for dbrootmirza.txt.\033[0m"
        exit 1
    }
    sleep 1

    randomdbpasstxt=$(openssl rand -base64 10 | tr -dc 'a-zA-Z0-9' | cut -c1-8)

    ASAS="$"

    echo "${ASAS}user = 'root';" >> /root/confmirza/dbrootmirza.txt
    echo "${ASAS}pass = '${randomdbpasstxt}';" >> /root/confmirza/dbrootmirza.txt
    echo "${ASAS}path = '${RANDOM_NUMBER}';" >> /root/confmirza/dbrootmirza.txt

    sleep 1

    passs=$(cat /root/confmirza/dbrootmirza.txt | grep '$pass' | cut -d"'" -f2)
    userrr=$(cat /root/confmirza/dbrootmirza.txt | grep '$user' | cut -d"'" -f2)

    sudo mysql -u $userrr -p$passs -e "alter user '$userrr'@'localhost' identified with mysql_native_password by '$passs';FLUSH PRIVILEGES;" || {
        echo -e "\e[91mError: Failed to alter MySQL user. Attempting recovery...\033[0m"

        sudo sed -i '$ a skip-grant-tables' /etc/mysql/mysql.conf.d/mysqld.cnf
        sudo systemctl restart mysql

        sudo mysql <<EOF
    DROP USER IF EXISTS 'root'@'localhost';
    CREATE USER 'root'@'localhost' IDENTIFIED BY '${passs}';
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
EOF

        sudo sed -i '/skip-grant-tables/d' /etc/mysql/mysql.conf.d/mysqld.cnf
        sudo systemctl restart mysql

        echo "SELECT 1" | mysql -u$userrr -p$passs 2>/dev/null || {
            echo -e "\e[91mError: Recovery failed. MySQL login still not working.\033[0m"
            exit 1
        }
    }

    echo "Folder created successfully!"
    else
    echo "Folder already exists."
    fi


    clear

    echo " "
    echo -e "\e[32m SSL \033[0m\n"

    read -p "Enter the domain: " domainname
    while [[ ! "$domainname" =~ ^[a-zA-Z0-9.-]+$ ]]; do
    echo -e "\e[91mInvalid domain format. Please try again.\033[0m"
    read -p "Enter the domain: " domainname
    done
    DOMAIN_NAME="$domainname"
    PATHS=$(cat /root/confmirza/dbrootmirza.txt | grep '$path' | cut -d"'" -f2)
    sudo ufw allow 80 || {
        echo -e "\e[91mError: Failed to allow port 80 in UFW.\033[0m"
        exit 1
    }
    sudo ufw allow 443 || {
        echo -e "\e[91mError: Failed to allow port 443 in UFW.\033[0m"
        exit 1
    }

    echo -e "\033[33mDisable apache2\033[0m"
    wait

    sudo systemctl stop apache2 || {
        echo -e "\e[91mError: Failed to stop Apache2.\033[0m"
        exit 1
    }
    sudo systemctl disable apache2 || {
        echo -e "\e[91mError: Failed to disable Apache2.\033[0m"
        exit 1
    }
    sudo apt install letsencrypt -y || {
        echo -e "\e[91mError: Failed to install letsencrypt.\033[0m"
        exit 1
    }
    sudo systemctl enable certbot.timer || {
        echo -e "\e[91mError: Failed to enable certbot timer.\033[0m"
        exit 1
    }
    if ! wait_for_certbot; then
        echo -e "\e[91mError: Certbot is busy. Please try again shortly.\033[0m"
        exit 1
    fi
    sudo certbot certonly --standalone --agree-tos --preferred-challenges http -d $DOMAIN_NAME || {
        echo -e "\e[91mError: Failed to generate SSL certificate.\033[0m"
        exit 1
    }
    sudo apt install python3-certbot-apache -y || {
        echo -e "\e[91mError: Failed to install python3-certbot-apache.\033[0m"
        exit 1
    }
    if ! wait_for_certbot; then
        echo -e "\e[91mError: Certbot is busy. Please try again shortly.\033[0m"
        exit 1
    fi
    sudo certbot --apache --agree-tos --preferred-challenges http -d $DOMAIN_NAME || {
        echo -e "\e[91mError: Failed to configure SSL with Certbot.\033[0m"
        exit 1
    }

    echo " "
    echo -e "\033[33mEnable apache2\033[0m"
    wait
    sudo systemctl enable apache2 || {
        echo -e "\e[91mError: Failed to enable Apache2.\033[0m"
        exit 1
    }
    sudo systemctl start apache2 || {
        echo -e "\e[91mError: Failed to start Apache2.\033[0m"
        exit 1
    }
            clear
}
