if [[ -n "${__MIRZA_UI_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_UI_LOADED=1

# shellcheck shell=bash
# Mirza installer module: ui_logo_menu_helpers
# This file is sourced by install.sh
type_text() {
    local text="$1"
    local delay="${2:-0.03}"
    local i=0
    while [ $i -lt ${#text} ]; do
        echo -n "${text:$i:1}"
        sleep "$delay"
        ((i++))
    done
    echo ""
}


type_text_colored() {
    local color="$1"
    local text="$2"
    local delay="${3:-0.03}"
    echo -ne "$color"
    type_text "$text" "$delay"
    echo -ne "\033[0m"
}


function show_animated_logo() {
    clear
    echo ""
    sleep 0.05
    type_text_colored "\033[1;32m" "███╗░░░███╗ ██╗ ██████╗░ ███████╗ ░█████╗░ ██████╗░ ██████╗░ ░█████╗░" 0.003
    type_text_colored "\033[1;32m" "████╗░████║ ██║ ██╔══██╗ ╚════██║ ██╔══██╗ ██╔══██╗ ██╔══██╗ ██╔══██╗" 0.003
    type_text_colored "\033[1;37m" "██╔████╔██║ ██║ ██████╔╝ ░░███╔═╝ ███████║ ██████╔╝ ██████╔╝ ██║░░██║" 0.003
    type_text_colored "\033[1;37m" "██║╚██╔╝██║ ██║ ██╔══██╗ ██╔══╝░░ ██╔══██║ ██╔═══╝░ ██╔══██╗ ██║░░██║" 0.003
    type_text_colored "\033[1;31m" "██║░╚═╝░██║ ██║ ██║░░██║ ███████╗ ██║░░██║ ██║░░░░░ ██║░░██║ ╚█████╔╝" 0.003
    type_text_colored "\033[1;31m" "╚═╝░░░░░╚═╝ ╚═╝ ╚═╝░░╚═╝ ╚══════╝ ╚═╝░░╚═╝ ╚═╝░░░░░ ╚═╝░░╚═╝ ░╚════╝░" 0.003
    echo ""
    type_text_colored "\033[1;33m" "                    Mirza Pro Bot Installer v3.8" 0.015
    type_text_colored "\033[1;36m" "                    Developer: mahdiMGF2" 0.015
    type_text_colored "\033[1;36m" "                    debugger:  github.com/Mmd-Amir/mirza_pro" 0.015
    echo ""
}




function show_logo() {
    show_animated_logo
    return
}


print_menu_spacer() {
    local width=${1:-46}
    printf '\033[1;32m║\033[0m %*s \033[1;32m║\033[0m\n' "$width" ""
}


print_menu_option() {
    local option_number="$1"
    local option_label="$2"
    local color_code="${3:-1;37}"
    local width=${4:-46}

    printf -v padded_number '%-3s' "$option_number"
    local plain_text="$padded_number $option_label"
    local padding=$((width - ${#plain_text}))
    ((padding < 0)) && padding=0

    local colored_number=$'\033['"$color_code"'m'"$padded_number"$'\033[0m'
    printf '\033[1;32m║\033[0m %s %s%*s \033[1;32m║\033[0m\n' \
        "$colored_number" "$option_label" "$padding" ""
}
