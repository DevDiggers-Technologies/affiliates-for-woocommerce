#!/bin/bash
PLUGIN_SLUG="affiliates-for-woocommerce"
PROJECT_PATH=$(pwd)
BUILD_PATH="${PROJECT_PATH}/build"
DESTINATION_PATH="$BUILD_PATH/$PLUGIN_SLUG"

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
GREEN_BOLD='\033[1;32m';
RED_BOLD='\033[1;31m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
error () {
	echo -e "\n${RED_BOLD}$1${COLOR_RESET}\n"
}
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
success () {
	echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}
warning () {
	echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}

status "💃 Time to release Affiliates for WooCommerce Free 🙂"

warning "Ready to proceed to create a zip? [Y/N]: "
read -r PROCEED

if [ "$(echo "${PROCEED:-n}" | tr "[:upper:]" "[:lower:]")" != "y" ]; then
	error "Release cancelled!"
	exit 1
fi

status "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DESTINATION_PATH"

status "Syncing files..."

rsync -rc --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DESTINATION_PATH" --delete --delete-excluded

status "Generating zip file..."

cd "$BUILD_PATH" || exit

zip -q -r "${PLUGIN_SLUG}.zip" "$PLUGIN_SLUG/"

cd "$PROJECT_PATH" || exit
mv "$BUILD_PATH/${PLUGIN_SLUG}.zip" "$PROJECT_PATH"
status "${PLUGIN_SLUG}.zip file generated!"

rm -rf "$BUILD_PATH"

status "Deleting if development zip exists..."
rm -rf "${PLUGIN_SLUG}-development.zip"

status "Generating development directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DESTINATION_PATH"

status "Syncing files..."

rsync -rc --exclude "node_modules" "$PROJECT_PATH/" "$DESTINATION_PATH" --delete --delete-excluded
# rsync -rc --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DESTINATION_PATH" --delete --delete-excluded

status "Generating zip file..."

cd "$BUILD_PATH" || exit

zip -q -r "${PLUGIN_SLUG}-development.zip" "$PLUGIN_SLUG/"

cd "$PROJECT_PATH" || exit
mv "$BUILD_PATH/${PLUGIN_SLUG}-development.zip" "$PROJECT_PATH"
status "${PLUGIN_SLUG}-development.zip file generated!"

rm -rf "$BUILD_PATH"

success "Done. You've built Affiliates for WooCommerce zip! 🎉 "
