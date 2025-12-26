if [[ -n "${__MIRZA_ADDITIONAL_DB_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_ADDITIONAL_DB_LOADED=1

# shellcheck shell=bash
# Mirza installer module: additional_bots_db
# This file is sourced by install.sh
function export_additional_bot_database() {
    clear
    echo -e "\033[36mAvailable Bots:\033[0m"

    BOT_DIRS=$(ls -d /var/www/html/*/ 2>/dev/null | grep -v "/var/www/html/mirzabotconfig/" | xargs -r -n 1 basename)

    if [ -z "$BOT_DIRS" ]; then
        echo -e "\033[31mNo additional bots found in /var/www/html.\033[0m"
        return 1
    fi

    echo "$BOT_DIRS" | nl -w 2 -s ") "

    echo -ne "\033[36mEnter the bot name: \033[0m"
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

    ROOT_CREDENTIALS_FILE="/root/confmirza/dbrootmirza.txt"
    if [ -f "$ROOT_CREDENTIALS_FILE" ]; then
        ROOT_USER=$(grep '\$user =' "$ROOT_CREDENTIALS_FILE" | awk -F"'" '{print $2}')
        ROOT_PASS=$(grep '\$pass =' "$ROOT_CREDENTIALS_FILE" | awk -F"'" '{print $2}')
    else
        echo -e "\033[31mRoot credentials file not found.\033[0m"
        echo -ne "\033[36mEnter MySQL root password: \033[0m"
        read -s ROOT_PASS
        echo

        if [ -z "$ROOT_PASS" ]; then
            echo -e "\033[31mPassword cannot be empty. Exiting...\033[0m"
            return 1
        fi

        ROOT_USER="root"

        echo "SELECT 1" | mysql -u "$ROOT_USER" -p"$ROOT_PASS" 2>/dev/null
        if [ $? -ne 0 ]; then
            echo -e "\033[31mInvalid root credentials. Exiting...\033[0m"
            return 1
        fi
    fi

    DB_USER=$(grep '^\$usernamedb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_PASS=$(grep '^\$passworddb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_NAME=$(grep '^\$dbname' "$CONFIG_PATH" | awk -F"'" '{print $2}')

    if [ -z "$DB_USER" ] || [ -z "$DB_PASS" ] || [ -z "$DB_NAME" ]; then
        echo -e "\033[31m[ERROR]\033[0m Failed to extract database credentials from $CONFIG_PATH."
        return 1
    fi

    echo -e "\033[33mVerifying database existence...\033[0m"
    if ! mysql -u "$ROOT_USER" -p"$ROOT_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
        echo -e "\033[31m[ERROR]\033[0m Database $DB_NAME does not exist or credentials are incorrect."
        return 1
    fi

    BACKUP_FILE="/root/${DB_NAME}_backup.sql"
    echo -e "\033[33mCreating backup at $BACKUP_FILE...\033[0m"
    if ! mysqldump -u "$ROOT_USER" -p"$ROOT_PASS" "$DB_NAME" > "$BACKUP_FILE"; then
        echo -e "\033[31m[ERROR]\033[0m Failed to create database backup."
        return 1
    fi

    echo -e "\033[32mBackup successfully created at $BACKUP_FILE.\033[0m"
}


function import_additional_bot_database() {
    clear
    echo -e "\033[36mStarting Import Database Process...\033[0m"

    ROOT_CREDENTIALS_FILE="/root/confmirza/dbrootmirza.txt"
    if [ -f "$ROOT_CREDENTIALS_FILE" ]; then
        ROOT_USER=$(grep '\$user =' "$ROOT_CREDENTIALS_FILE" | awk -F"'" '{print $2}')
        ROOT_PASS=$(grep '\$pass =' "$ROOT_CREDENTIALS_FILE" | awk -F"'" '{print $2}')
    else
        echo -e "\033[31mRoot credentials file not found.\033[0m"
        echo -ne "\033[36mEnter MySQL root password: \033[0m"
        read -s ROOT_PASS
        echo

        if [ -z "$ROOT_PASS" ]; then
            echo -e "\033[31mPassword cannot be empty. Exiting...\033[0m"
            return 1
        fi

        ROOT_USER="root"

        echo "SELECT 1" | mysql -u "$ROOT_USER" -p"$ROOT_PASS" 2>/dev/null
        if [ $? -ne 0 ]; then
            echo -e "\033[31mInvalid root credentials. Exiting...\033[0m"
            return 1
        fi
    fi

    SQL_FILES=$(find /root -maxdepth 1 -type f -name "*.sql")
    if [ -z "$SQL_FILES" ]; then
        echo -e "\033[31mNo .sql files found in /root. Please provide a valid .sql file.\033[0m"
        return 1
    fi

    echo -e "\033[36mAvailable .sql files:\033[0m"
    echo "$SQL_FILES" | nl -w 2 -s ") "

    echo -ne "\033[36mEnter the number of the file or provide a full path: \033[0m"
    read FILE_SELECTION

    if [[ "$FILE_SELECTION" =~ ^[0-9]+$ ]]; then
        SELECTED_FILE=$(echo "$SQL_FILES" | sed -n "${FILE_SELECTION}p")
    else
        SELECTED_FILE="$FILE_SELECTION"
    fi

    if [ ! -f "$SELECTED_FILE" ]; then
        echo -e "\033[31mSelected file does not exist. Exiting...\033[0m"
        return 1
    fi

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

    DB_USER=$(grep '^\$usernamedb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_PASS=$(grep '^\$passworddb' "$CONFIG_PATH" | awk -F"'" '{print $2}')
    DB_NAME=$(grep '^\$dbname' "$CONFIG_PATH" | awk -F"'" '{print $2}')

    if [ -z "$DB_USER" ] || [ -z "$DB_PASS" ] || [ -z "$DB_NAME" ]; then
        echo -e "\033[31m[ERROR]\033[0m Failed to extract database credentials from $CONFIG_PATH."
        return 1
    fi

    echo -e "\033[33mVerifying database existence...\033[0m"
    if ! mysql -u "$ROOT_USER" -p"$ROOT_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
        echo -e "\033[31m[ERROR]\033[0m Database $DB_NAME does not exist or credentials are incorrect."
        return 1
    fi

    echo -e "\033[33mImporting database from $SELECTED_FILE into $DB_NAME...\033[0m"
    if ! mysql -u "$ROOT_USER" -p"$ROOT_PASS" "$DB_NAME" < "$SELECTED_FILE"; then
        echo -e "\033[31m[ERROR]\033[0m Failed to import database."
        return 1
    fi

    echo -e "\033[32mDatabase successfully imported from $SELECTED_FILE into $DB_NAME.\033[0m"
}
