if [[ -n "${__MIRZA_BOOTSTRAP_LOGGING_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_BOOTSTRAP_LOGGING_LOADED=1

# shellcheck shell=bash
# Mirza installer module: bootstrap_logging
# This file is sourced by install.sh
init_logging() {
    local log_dir
    log_dir="$(dirname "$LOG_FILE")"
    [ -d "$log_dir" ] || mkdir -p "$log_dir"
    [ -f "$LOG_FILE" ] || touch "$LOG_FILE"
    chmod 600 "$LOG_FILE"
}


log_message() {
    local level="$1"; shift
    local message="$*"
    local timestamp
    local color
    timestamp="$(date '+%Y-%m-%d %H:%M:%S')"
    case "$level" in
        INFO) color="\033[1;34m" ;;
        WARN) color="\033[1;33m" ;;
        ERROR) color="\033[1;31m" ;;
        ACTION) color="\033[1;36m" ;;
        *) color="\033[0m" ;;
    esac
    echo -e "${color}[${level}]\033[0m $message"
    printf '%s [%s] %s\n' "$timestamp" "$level" "$message" >>"$LOG_FILE"
}


log_action() { log_message "ACTION" "$@"; }

log_info() { log_message "INFO" "$@"; }

log_warn() { log_message "WARN" "$@"; }

log_error() { log_message "ERROR" "$@"; }

init_logging
log_info "Mirza installer initialized (PID $$)"
