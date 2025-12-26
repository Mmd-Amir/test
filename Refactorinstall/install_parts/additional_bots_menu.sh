if [[ -n "${__MIRZA_ADDITIONAL_MENU_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_ADDITIONAL_MENU_LOADED=1

# shellcheck shell=bash
# Mirza installer module: additional_bots_menu
# This file is sourced by install.sh
function manage_additional_bots() {
    if [ ! -d "/var/www/html/mirzabotconfig" ]; then
        echo -e "\033[31m[ERROR]\033[0m The main Mirza Bot is not installed (/var/www/html/mirzabotconfig not found)."
        echo -e "\033[33mYou are not allowed to use this section without the main bot installed. Exiting...\033[0m"
        sleep 2
        exit 1
    fi

    if check_marzban_installed; then
        echo -e "\033[31m[ERROR]\033[0m Additional bot management is not available when Marzban is installed."
        echo -e "\033[33mExiting script...\033[0m"
        sleep 2
        exit 1
    fi

    echo -e "\033[36m1) Install Additional Bot\033[0m"
    echo -e "\033[36m2) Update Additional Bot\033[0m"
    echo -e "\033[36m3) Remove Additional Bot\033[0m"
    echo -e "\033[36m4) Export Additional Bot Database\033[0m"
    echo -e "\033[36m5) Import Additional Bot Database\033[0m"
    echo -e "\033[36m6) Configure Automated Backup for Additional Bot\033[0m"
    echo -e "\033[36m7) Disable Automated Backup for Additional Bot\033[0m"
    echo -e "\033[36m8) Change Additional Bot Domain\033[0m"
    echo -e "\033[36m9) Back to Main Menu\033[0m"
    echo ""
    read -p "Select an option [1-9]: " sub_option
    case $sub_option in
        1) install_additional_bot ;;
        2) update_additional_bot ;;
        3) remove_additional_bot ;;
        4) export_additional_bot_database ;;
        5) import_additional_bot_database ;;
        6) configure_backup_additional_bot ;;
        7) disable_backup_additional_bot ;;
        8) change_additional_bot_domain ;;
        9) show_menu ;;
        *)
            echo -e "\033[31mInvalid option. Please try again.\033[0m"
            manage_additional_bots
            ;;
    esac
}
