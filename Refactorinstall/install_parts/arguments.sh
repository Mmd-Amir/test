if [[ -n "${__MIRZA_ARGUMENTS_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_ARGUMENTS_LOADED=1

# shellcheck shell=bash
# Mirza installer module: arguments
# This file is sourced by install.sh
process_arguments() {
    local version=""
    case "$1" in
        -v*)
            version="${1#-v}"
            if [ -n "$version" ]; then
                install_bot "-v" "$version"
            else
                if [ -n "$2" ]; then
                    install_bot "-v" "$2"
                else
                    echo -e "\033[31m[ERROR]\033[0m Please specify a version with -v (e.g., -v 4.11.1)"
                    exit 1
                fi
            fi
            ;;
        -beta)
            install_bot "-beta"
            ;;
        --beta)
            install_bot "-beta"
            ;;
        -update)
            update_bot "$2"
            ;;
        *)
            show_menu
            ;;
    esac
}

process_arguments "$1" "$2"
