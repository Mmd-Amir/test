if [[ -n "${__MIRZA_IMMIGRATION_LOADED:-}" ]]; then
  return 0
fi
__MIRZA_IMMIGRATION_LOADED=1

# shellcheck shell=bash
# Mirza installer module: immigration_install
# This file is sourced by install.sh
function immigration_install() {
    show_animated_logo

    echo ""
    type_text_colored "\033[1;32m" "╔════════════════════════════════════════════════╗" 0.01
    type_text_colored "\033[1;32m" "║      🚀 IMMIGRATION - WEB INSTALLER            ║" 0.01
    type_text_colored "\033[1;32m" "╚════════════════════════════════════════════════╝" 0.01
    echo ""

    type_text_colored "\033[1;33m" "Scanning for installed bots in /var/www/html/..."
    echo ""

    mapfile -t BOT_CONFIGS < <(find /var/www/html -maxdepth 4 -type f -name config.php -print 2>/dev/null | sort)

    VALID_CONFIGS=()
    for CONFIG_CHECK in "${BOT_CONFIGS[@]}"; do
        [ -z "$CONFIG_CHECK" ] && continue
        if grep -q '\$domainhosts' "$CONFIG_CHECK" 2>/dev/null; then
            VALID_CONFIGS+=("$CONFIG_CHECK")
        fi
    done

    if [ ${#VALID_CONFIGS[@]} -eq 0 ]; then
        type_text_colored "\033[1;31m" "✗ No bots found in /var/www/html/"
        echo ""
        read -p "Press Enter to return to main menu..."
        show_menu
        return 1
    fi

    BOT_COUNT=${#VALID_CONFIGS[@]}
    type_text_colored "\033[1;32m" "✓ Found $BOT_COUNT bot(s) installed"
    echo ""
    echo ""

    PROCESSED=0
    ERRORS=0

    for CONFIG_FILE in "${VALID_CONFIGS[@]}"; do
        [ -z "$CONFIG_FILE" ] && continue
        if [ ! -f "$CONFIG_FILE" ]; then
            continue
        fi
        BOT_PATH="$(dirname "$CONFIG_FILE")"
        RELATIVE_PATH="${BOT_PATH#/var/www/html/}"
        if [ "$BOT_PATH" = "/var/www/html" ]; then
            BOT_LABEL="/var/www/html"
        elif [ -z "$RELATIVE_PATH" ] || [ "$RELATIVE_PATH" = "$BOT_PATH" ]; then
            BOT_LABEL="$(basename "$BOT_PATH")"
        else
            BOT_LABEL="$RELATIVE_PATH"
        fi

        DOMAIN=$(grep -oP "\$domainhosts\s*=\s*[\'\"]\K[^\'\"]+" "$CONFIG_FILE" 2>/dev/null | head -1)

        if [ -z "$DOMAIN" ]; then
            DOMAIN=$(grep "domainhosts" "$CONFIG_FILE" | sed -n "s/.*domainhosts.*=.*[\'\"]\([^\'\"]*\)[\'\"].*/\1/p" | head -1)
        fi

        if [ -z "$DOMAIN" ]; then
            continue
        fi

        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        type_text_colored "\033[1;36m" "Processing bot: $BOT_LABEL" 0.03
        echo ""

        type_text_colored "\033[1;32m" "  ✓ Domain detected: $DOMAIN" 0.03
        echo ""

        type_text_colored "\033[1;33m" "  Setting file permissions..." 0.03

        if [ -f "$CONFIG_FILE" ]; then
            sudo chmod 666 "$CONFIG_FILE" 2>/dev/null || {
                type_text_colored "\033[1;31m" "  ✗ Failed to set permissions for config.php"
                echo ""
                ((ERRORS++))
                continue
            }
        fi

        TABLE_FILE="$BOT_PATH/table.php"
        if [ -f "$TABLE_FILE" ]; then
            sudo chmod 644 "$TABLE_FILE" 2>/dev/null
        fi

        sudo chmod 755 "$BOT_PATH" 2>/dev/null || {
            type_text_colored "\033[1;31m" "  ✗ Failed to set directory permissions"
            echo ""
            ((ERRORS++))
            continue
        }

        sudo chown -R www-data:www-data "$BOT_PATH" 2>/dev/null || {
            type_text_colored "\033[1;31m" "  ✗ Failed to set ownership"
            echo ""
            ((ERRORS++))
            continue
        }

        APP_DIR=""
        if [[ "$BOT_PATH" == */app ]]; then
            APP_DIR="$BOT_PATH"
        elif [[ "$BOT_PATH" == */app/* ]]; then
            APP_DIR="${BOT_PATH%%/app/*}/app"
        fi

        if [ -n "$APP_DIR" ] && [ -d "$APP_DIR" ]; then
            type_text_colored "\033[1;33m" "  Applying permissions to app directory: $APP_DIR" 0.02
            sudo chmod 755 "$APP_DIR" 2>/dev/null || {
                type_text_colored "\033[1;31m" "  ✗ Failed to set directory permissions for app"
                echo ""
                ((ERRORS++))
                continue
            }
            sudo chown -R www-data:www-data "$APP_DIR" 2>/dev/null || {
                type_text_colored "\033[1;31m" "  ✗ Failed to set ownership for app directory"
                echo ""
                ((ERRORS++))
                continue
            }
        fi

        type_text_colored "\033[1;32m" "  ✓ Permissions set successfully" 0.03
        echo ""

        DOMAIN_TRIMMED="${DOMAIN%/}"
        if [[ "$DOMAIN_TRIMMED" =~ ^https?:// ]]; then
            INSTALLER_URL="${DOMAIN_TRIMMED}/installer/"
        else
            INSTALLER_URL="https://${DOMAIN_TRIMMED}/installer/"
        fi

        type_text_colored "\033[1;36m" "  📎 Installer URL:" 0.03
        type_text_colored "\033[1;33m" "     $INSTALLER_URL" 0.02
        echo ""

        ((PROCESSED++))
    done

    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""

    type_text_colored "\033[1;32m" "╔════════════════════════════════════════════════╗" 0.01
    type_text_colored "\033[1;32m" "║               SUMMARY                         ║" 0.01
    type_text_colored "\033[1;32m" "╚════════════════════════════════════════════════╝" 0.01
    echo ""

    type_text_colored "\033[1;37m" "Total bots found:       $BOT_COUNT"
    type_text_colored "\033[1;32m" "Successfully processed: $PROCESSED"
    if [ $ERRORS -gt 0 ]; then
        type_text_colored "\033[1;31m" "Errors encountered:     $ERRORS"
    fi
    echo ""
    echo ""

    type_text_colored "\033[1;33m" "Open the installer URLs in your browser to complete the migration."
    echo ""

    read -p "Press Enter to return to main menu..."
    show_menu
}
