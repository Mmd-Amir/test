# shellcheck shell=bash
# Mirza installer module: install_standard_wrapper
# This file is sourced by install.sh

if [[ -n "${__MIRZA_INSTALL_STANDARD_WRAPPER_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_INSTALL_STANDARD_WRAPPER_LOADED=1

function install_bot() {
    echo -e "\e[32mInstalling mirza script ... \033[0m\n"

    if check_marzban_installed; then
        echo -e "\033[41m[IMPORTANT WARNING]\033[0m \033[1;33mMarzban detected. Proceeding with Marzban-compatible installation.\033[0m"
        install_bot_with_marzban "$@"
        return 0
    fi

    install_bot_standard_system_setup "$@"
    install_bot_standard_mysql_root_and_ssl
    install_bot_standard_bot_config

    # Keep original behavior: try to ensure /root/install.sh is executable and linked as 'mirza'
    chmod +x /root/install.sh 2>/dev/null || true
    ln -sf /root/install.sh /usr/local/bin/mirza >/dev/null 2>&1 || true
}
