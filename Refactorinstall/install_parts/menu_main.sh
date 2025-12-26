if [[ -n "${__MIRZA_MENU_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_MENU_LOADED=1

# shellcheck shell=bash
# Mirza installer module: menu_main
# This file is sourced by install.sh
function show_menu() {
    show_logo
    check_ssl_status

    type_text_colored "\033[1;32m" "╔════════════════════════════════════════════════╗" 0.005
    type_text_colored "\033[1;32m" "║            MIRZA PRO - MAIN MENU               ║" 0.005
    type_text_colored "\033[1;32m" "╠════════════════════════════════════════════════╣" 0.005
    print_menu_spacer
    print_menu_option "1)" "Install Mirza Bot"
    print_menu_option "2)" "Update Mirza Bot"
    print_menu_option "3)" "Remove Mirza Bot"
    print_menu_option "4)" "Export Database"
    print_menu_option "5)" "Import Database"
    print_menu_option "6)" "Configure Automated Backup"
    print_menu_option "7)" "Renew SSL Certificates"
    print_menu_option "8)" "Change Domain"
    print_menu_option "9)" "Additional Bot Management"
    print_menu_option "10)" "Immigration (Server Migration)" "1;33"
    print_menu_option "11)" "Remove Domain" "1;31"
    print_menu_option "12)" "Delete Cron Jobs" "1;31"
    print_menu_option "13)" "Exit" "1;31"
    type_text_colored "\033[1;32m" "╚════════════════════════════════════════════════╝" 0.005
    echo ""
    read -p "$(echo -e '\033[1;33m❯\033[0m Select an option [1-13]: ')" option
    case $option in
        1) install_bot ;;
        2) update_bot ;;
        3) remove_bot ;;
        4) export_database ;;
        5) import_database ;;
        6) auto_backup ;;
        7) renew_ssl ;;
        8) change_domain ;;
        9) manage_additional_bots ;;
        10) immigration_install ;;
        11) remove_domain ;;
        12) delete_cron_jobs ;;
        13)
            type_text_colored "\033[1;32m" "✓ Exiting... Goodbye!" 0.05
            exit 0
            ;;
        *)
            type_text_colored "\033[1;31m" "✗ Invalid option. Please try again."
            sleep 2
            show_menu
            ;;
    esac
}
