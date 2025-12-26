#!/usr/bin/env bash
# Mirza Pro Installer (Refactored)
# This file is meant to keep working with:
#   bash -c "$(curl -L https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install.sh)"

if [[ $EUID -ne 0 ]]; then
    echo -e "\033[31m[ERROR]\033[0m Please run this script as \033[1mroot\033[0m."
    exit 1
fi

LOG_FILE="${LOG_FILE:-/var/log/mirza_installer.log}"

# Where to download the refactored parts from, when running via curl|bash
MIRZA_INSTALLER_RAW_BASE="${MIRZA_INSTALLER_RAW_BASE:-https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install_parts}"
MIRZA_INSTALLER_RAW_INSTALL_URL="${MIRZA_INSTALLER_RAW_INSTALL_URL:-https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install.sh}"

MIRZA_INSTALLER_TMP_DIR="${MIRZA_INSTALLER_TMP_DIR:-/tmp/mirza_installer_parts}"
mkdir -p "$MIRZA_INSTALLER_TMP_DIR" >/dev/null 2>&1 || true

# Detect local install_parts directory (when running from a local file)
SCRIPT_DIR=""
if [[ -n "${BASH_SOURCE[0]:-}" && -f "${BASH_SOURCE[0]}" ]]; then
  SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi
LOCAL_PARTS_DIR=""
if [[ -n "$SCRIPT_DIR" && -d "$SCRIPT_DIR/install_parts" ]]; then
  LOCAL_PARTS_DIR="$SCRIPT_DIR/install_parts"
fi

fetch_to() {
  local url="$1"
  local out="$2"

  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "$url" -o "$out"
    return $?
  fi

  if command -v wget >/dev/null 2>&1; then
    wget -qO "$out" "$url"
    return $?
  fi

  echo -e "\033[31m[ERROR]\033[0m Neither curl nor wget is installed."
  return 1
}

source_part() {
  local part="$1"

  # 1) local
  if [[ -n "$LOCAL_PARTS_DIR" && -f "$LOCAL_PARTS_DIR/$part" ]]; then
    # shellcheck source=/dev/null
    source "$LOCAL_PARTS_DIR/$part"
    return 0
  fi

  # 2) download
  local cached="$MIRZA_INSTALLER_TMP_DIR/$part"
  if [[ ! -f "$cached" ]]; then
    local url="${MIRZA_INSTALLER_RAW_BASE%/}/$part"
    fetch_to "$url" "$cached" || {
      echo -e "\033[31m[ERROR]\033[0m Failed to download: $url"
      return 1
    }
  fi

  # shellcheck source=/dev/null
  source "$cached"
}

persist_installer() {
  # Best-effort: keep a local copy at /root/install.sh so 'mirza' command works later
  if [[ -f "/root/install.sh" ]]; then
    return 0
  fi

  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "$MIRZA_INSTALLER_RAW_INSTALL_URL" -o /root/install.sh || return 0
    chmod +x /root/install.sh 2>/dev/null || true
    ln -sf /root/install.sh /usr/local/bin/mirza >/dev/null 2>&1 || true
  fi
}

# Load modules (bootstrap first)
source_part "bootstrap_logging.sh" || exit 1
init_logging
log_info "Refactored installer started."

persist_installer || true

# Remaining modules
PARTS=(
  "ui_logo_menu_helpers.sh"
  "system_apache_certbot_helpers.sh"
  "immigration_install.sh"
  "install_standard_system_setup.sh"
  "install_standard_mysql_root_and_ssl.sh"
  "install_standard_bot_config.sh"
  "install_standard_wrapper.sh"
  "install_with_marzban.sh"
  "update_and_remove.sh"
  "db_backup_restore.sh"
  "ssl_domain_cron.sh"
  "additional_bots_menu.sh"
  "additional_bots_core.sh"
  "additional_bots_db.sh"
  "additional_bots_backup_domain.sh"
  "menu_main.sh"
  "arguments.sh"
)

for p in "${PARTS[@]}"; do
  source_part "$p" || exit 1
done

# Keep original argument behavior (only first 2 args are used)
process_arguments "$1" "$2"
