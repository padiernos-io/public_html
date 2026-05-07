#!/bin/bash
# Wrapper to run PHP via DDEV for the Twiggy language server.
# The Twiggy extension can't find `php` on the host since it only runs in the
# DDEV container, so this script:
#   1. Syncs the Twiggy extension's phpUtils files into the project where DDEV
#      can access them (.vscode/twiggy-cache/).
#   2. Translates host-absolute paths to DDEV container paths (/var/www/html).
#   3. Delegates to `ddev exec php` with the translated arguments.
set -e

DDEV_ROOT="/home/mikepadiernos/Library/Projects/personal/padiernos.me"
HOST_ROOT="$DDEV_ROOT"
CONTAINER_ROOT="/var/www/html"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TWIGGY_CACHE="$SCRIPT_DIR/twiggy-cache"

# Sync the latest phpUtils files from the active Twiggy extension into the
# project directory so DDEV can reach them.
TWIGGY_PHP_UTILS=$(ls -d /home/mikepadiernos/.vscode-insiders/extensions/moetelo.twiggy-*/dist/phpUtils 2>/dev/null | sort -V | tail -1)
if [ -n "$TWIGGY_PHP_UTILS" ]; then
    mkdir -p "$TWIGGY_CACHE"
    cp -f "$TWIGGY_PHP_UTILS"/*.php "$TWIGGY_CACHE/"
    # Patch printTwigEnvironment.php to output unescaped slashes so the
    # container→host path translation via sed works correctly.
    sed -i 's/JSON_PRETTY_PRINT)/JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)/' \
        "$TWIGGY_CACHE/printTwigEnvironment.php"
fi

CONTAINER_CACHE="$CONTAINER_ROOT/web/themes/custom/matter/.vscode/twiggy-cache"

# Build the translated argument list.
ARGS=()
for arg in "$@"; do
    case "$arg" in
        # Replace extension phpUtils scripts with cached copies inside the project.
        */phpUtils/*.php)
            FILENAME=$(basename "$arg")
            ARGS+=("$CONTAINER_CACHE/$FILENAME")
            ;;
        # Translate host project paths to DDEV container paths.
        "$HOST_ROOT"/*)
            ARGS+=("${CONTAINER_ROOT}${arg#$HOST_ROOT}")
            ;;
        *)
            ARGS+=("$arg")
            ;;
    esac
done

# Run PHP in DDEV and translate container paths back to host paths so the
# Twiggy language server (running on the host) can resolve template files.
cd "$DDEV_ROOT" && ddev exec php "${ARGS[@]}" | sed "s|$CONTAINER_ROOT|$HOST_ROOT|g"
