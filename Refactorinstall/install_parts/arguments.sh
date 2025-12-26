\
    #!/usr/bin/env bash
    # Mirza installer module: arguments
    # Safe under `set -u` (nounset)

    if [[ -n "${__MIRZA_ARGUMENTS_LOADED:-}" ]]; then
      return 0
    fi
    __MIRZA_ARGUMENTS_LOADED=1

    process_arguments() {
        local arg1="${1-}"
        local arg2="${2-}"
        local version=""

        case "$arg1" in
            -v*)
                version="${arg1#-v}"
                if [[ -n "$version" ]]; then
                    install_bot "-v" "$version"
                else
                    if [[ -n "$arg2" ]]; then
                        install_bot "-v" "$arg2"
                    else
                        echo -e "\033[31m[ERROR]\033[0m Please specify a version with -v (e.g., -v 4.11.1)"
                        exit 1
                    fi
                fi
                ;;
            -beta|--beta)
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

    # Call immediately if this module is sourced as last step
    # (Entry script also calls process_arguments, but this keeps backward-compat if module is used alone)
    if [[ "${MIRZA_ARGUMENTS_AUTORUN:-1}" == "1" ]]; then
      process_arguments "${1-}" "${2-}"
    fi
