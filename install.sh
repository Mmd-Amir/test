\
    #!/usr/bin/env bash
    # Mirza Installer (Refactored - multi-part)
    #
    # This entrypoint supports running via:
    #   bash -c "$(curl -L https://raw.githubusercontent.com/Mmd-Amir/test/main/install.sh)"
    #
    # Repo layouts supported:
    #   A) /install.sh + /install_parts/*.sh
    #   B) /install.sh + /Refactorinstall/install_parts/*.sh
    #
    # Override parts base (optional):
    #   export MIRZA_INSTALLER_RAW_BASE="https://raw.githubusercontent.com/<USER>/<REPO>/<BRANCH>/Refactorinstall/install_parts"
    #
    set -euo pipefail

    # Defaults for THIS repo (change these defaults if you copy the script to another repo)
    MIRZA_GH_USER="${MIRZA_GH_USER:-Mmd-Amir}"
    MIRZA_GH_REPO="${MIRZA_GH_REPO:-test}"
    MIRZA_GH_BRANCH="${MIRZA_GH_BRANCH:-main}"

    MIRZA_INSTALLER_RAW_BASE="${MIRZA_INSTALLER_RAW_BASE:-}"

    TMP_DIR="/tmp/mirza_install_parts"
    mkdir -p "$TMP_DIR"

    _log() { echo -e "$*"; }
    _err() { echo -e "\033[31m[ERROR]\033[0m $*" >&2; }

    curl_download() {
      local url="$1" out="$2"
      curl -fsSL "$url" -o "$out"
    }

    # Detect local parts dir (when executed from disk, not via curl|bash)
    _local_parts_dir=""
    if [[ -n "${BASH_SOURCE[0]:-}" && -f "${BASH_SOURCE[0]}" ]]; then
      _script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
      if [[ -d "$_script_dir/install_parts" ]]; then
        _local_parts_dir="$_script_dir/install_parts"
      elif [[ -d "$_script_dir/Refactorinstall/install_parts" ]]; then
        _local_parts_dir="$_script_dir/Refactorinstall/install_parts"
      fi
    fi

    # Candidate raw bases (used only when local parts are not available)
    _bases=()
    if [[ -n "$MIRZA_INSTALLER_RAW_BASE" ]]; then
      _bases+=("$MIRZA_INSTALLER_RAW_BASE")
    else
      _bases+=("https://raw.githubusercontent.com/${MIRZA_GH_USER}/${MIRZA_GH_REPO}/${MIRZA_GH_BRANCH}/install_parts")
      _bases+=("https://raw.githubusercontent.com/${MIRZA_GH_USER}/${MIRZA_GH_REPO}/${MIRZA_GH_BRANCH}/Refactorinstall/install_parts")
    fi

    PARTS=(
      "bootstrap_logging.sh"
      "ui_logo_menu_helpers.sh"
      "system_apache_certbot_helpers.sh"
      "ssl_domain_cron.sh"
      "install_standard_system_setup.sh"
      "install_standard_mysql_root_and_ssl.sh"
      "install_standard_bot_config.sh"
      "install_with_marzban.sh"
      "additional_bots_core.sh"
      "additional_bots_db.sh"
      "additional_bots_backup_domain.sh"
      "additional_bots_menu.sh"
      "db_backup_restore.sh"
      "update_and_remove.sh"
      "menu_main.sh"
      "install_standard_wrapper.sh"
      "arguments.sh"
      "immigration_install.sh"
    )

    fetch_part() {
      local name="$1" out="$TMP_DIR/$name"

      # Local mode
      if [[ -n "$_local_parts_dir" && -f "$_local_parts_dir/$name" ]]; then
        cp -f "$_local_parts_dir/$name" "$out"
        return 0
      fi

      # Remote mode
      local base url ok=1
      for base in "${_bases[@]}"; do
        url="${base%/}/$name"
        if curl_download "$url" "$out" 2>/dev/null; then
          ok=0
          break
        fi
      done

      if [[ $ok -ne 0 ]]; then
        _err "Failed to download: ${_bases[0]%/}/$name"
        if [[ -n "$MIRZA_INSTALLER_RAW_BASE" ]]; then
          _log "\033[33m[HINT]\033[0m Check that MIRZA_INSTALLER_RAW_BASE points to the folder containing *.sh parts."
        else
          _log "\033[33m[HINT]\033[0m If your repo uses Refactorinstall/, set:"
          _log "  export MIRZA_INSTALLER_RAW_BASE=\"https://raw.githubusercontent.com/${MIRZA_GH_USER}/${MIRZA_GH_REPO}/${MIRZA_GH_BRANCH}/Refactorinstall/install_parts\""
        fi
        exit 1
      fi
    }

    # Download all parts (bootstrap first for consistent logging)
    fetch_part "bootstrap_logging.sh"
    # shellcheck disable=SC1090
    source "$TMP_DIR/bootstrap_logging.sh"

    log_info "Mirza installer initialized (PID $$)"

    for p in "${PARTS[@]}"; do
      [[ "$p" == "bootstrap_logging.sh" ]] && continue
      fetch_part "$p"
    done

    # Source all parts (order-safe: source everything, then process args/menu)
    for p in "${PARTS[@]}"; do
      # shellcheck disable=SC1090
      source "$TMP_DIR/$p"
    done

    # Prefer argument processing module if available
    if declare -F process_arguments >/dev/null 2>&1; then
      process_arguments "${1-}" "${2-}"
    elif declare -F show_menu >/dev/null 2>&1; then
      show_menu
    else
      _err "No entry function found (process_arguments/show_menu). Parts may be missing or not loaded."
      exit 1
    fi
