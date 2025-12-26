if [[ -n "${__MIRZA_ARGUMENTS_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_ARGUMENTS_LOADED=1

# shellcheck shell=bash
# Mirza installer module: arguments
# This file is sourced by install.sh
process_arguments() {
    # Use "${N-}" so this module works with `set -u` (nounset)
    # even when the caller doesn't pass optional positional args.
    local arg1="${1-}"
    local arg2="${2-}"
    local version=""
    case "$arg1" in
        -v*)
            version="${arg1#-v}"
            if [ -n "$version" ]; then
                install_bot "-v" "$version"
            else
                if [ -n "$arg2" ]; then
                    install_bot "-v" "$arg2"
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
            update_bot "$arg2"
            ;;
        *)
            show_menu
            ;;
    esac
}

process_arguments "${1-}" "${2-}"
