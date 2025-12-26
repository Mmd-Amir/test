#!/usr/bin/env bash
# Mirza Pro Installer (Refactored - multi-part)
#
# Works with:
#   bash -c "$(curl -L https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install.sh)"
#
# Repo layout supported (either one):
#   A) /install.sh + /install_parts/*.sh
#   B) /install.sh + /Refactorinstall/install_parts/*.sh   (your current layout)
#
# You can override where parts are fetched from:
#   export MIRZA_INSTALLER_RAW_BASE="https://raw.githubusercontent.com/<USER>/<REPO>/<BRANCH>/Refactorinstall/install_parts"
#
set -u

if [[ ${EUID:-999} -ne 0 ]]; then
  echo -e "\033[31m[ERROR]\033[0m Please run as \033[1mroot\033[0m."
  exit 1
fi

LOG_FILE="${LOG_FILE:-/var/log/mirza_installer.log}"

# When running via curl|bash, we download parts from GitHub raw.
# We try multiple base paths to avoid 404 if you keep parts under Refactorinstall/.
MIRZA_INSTALLER_RAW_BASE="${MIRZA_INSTALLER_RAW_BASE:-}"
MIRZA_INSTALLER_RAW_INSTALL_URL="${MIRZA_INSTALLER_RAW_INSTALL_URL:-https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install.sh}"

# ---------- logging ----------
_log() {
  local level="$1"; shift || true
  local msg="$*"
  printf '[%s] [%s] %s\n' "$(date '+%F %T')" "$level" "$msg" | tee -a "$LOG_FILE" >/dev/null
}

# ---------- fetch helpers ----------
fetch_to() {
  local url="$1"
  local out="$2"
  command -v curl >/dev/null 2>&1 || { _log ERROR "curl not found"; return 1; }
  # -f: fail on 4xx/5xx, -sS: silent but show errors, -L: follow redirects
  curl -fsSL "$url" -o "$out"
}

# Try to locate parts locally if this script is executed from a file on disk.
_local_parts_dir=""
if [[ -n "${BASH_SOURCE[0]:-}" && -f "${BASH_SOURCE[0]}" ]]; then
  _script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
  if [[ -d "$_script_dir/install_parts" ]]; then
    _local_parts_dir="$_script_dir/install_parts"
  elif [[ -d "$_script_dir/Refactorinstall/install_parts" ]]; then
    _local_parts_dir="$_script_dir/Refactorinstall/install_parts"
  fi
fi

# Candidate raw bases (used only if local parts are not available and MIRZA_INSTALLER_RAW_BASE isn't set)
_default_bases=(
  "https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/install_parts"
  "https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/Refactorinstall/install_parts"
)

source_part() {
  local part="$1"
  local cache_dir="/tmp/mirza_install_parts"
  mkdir -p "$cache_dir"
  local cached="$cache_dir/$part"

  # 1) local
  if [[ -n "$_local_parts_dir" && -f "$_local_parts_dir/$part" ]]; then
    # shellcheck source=/dev/null
    source "$_local_parts_dir/$part"
    return 0
  fi

  # 2) cache
  if [[ -f "$cached" ]]; then
    # shellcheck source=/dev/null
    source "$cached"
    return 0
  fi

  # 3) download
  local bases=()
  if [[ -n "$MIRZA_INSTALLER_RAW_BASE" ]]; then
    bases=("$MIRZA_INSTALLER_RAW_BASE")
  else
    bases=("${_default_bases[@]}")
  fi

  local ok=0
  local last_url=""
  for b in "${bases[@]}"; do
    last_url="${b%/}/$part"
    if fetch_to "$last_url" "$cached"; then
      ok=1
      break
    fi
  done

  if [[ $ok -ne 1 ]]; then
    echo -e "\033[31m[ERROR]\033[0m Failed to download: $last_url"
    echo -e "\033[33m[HINT]\033[0m If your repo uses Refactorinstall/, set:"
    echo "  export MIRZA_INSTALLER_RAW_BASE="https://raw.githubusercontent.com/Mmd-Amir/mirza_pro/main/Refactorinstall/install_parts""
    return 1
  fi

  # shellcheck source=/dev/null
  source "$cached"
}

persist_installer() {
  # Best-effort: keep a local copy at /root/install.sh so 'mirza' command works later
  [[ -f "/root/install.sh" ]] && return 0
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "$MIRZA_INSTALLER_RAW_INSTALL_URL" -o /root/install.sh || return 0
    chmod +x /root/install.sh 2>/dev/null || true
    ln -sf /root/install.sh /usr/local/bin/mirza >/dev/null 2>&1 || true
  fi
}

# ---------- load parts ----------
PARTS=(
  "bootstrap_logging.sh"
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

persist_installer || true

# main() is provided by menu_main.sh
if declare -F main >/dev/null 2>&1; then
  main "$@"
else
  _log ERROR "main() not found after sourcing parts."
  exit 1
fi
