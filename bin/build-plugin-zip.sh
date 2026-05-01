#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory (plugin root)
cd "$(dirname "$0")"
cd ..

PLUGIN_SLUG=$(basename "$(pwd)")
PROJECT_PATH=$(pwd)
BUILD_PATH="${PROJECT_PATH}/build"
DESTINATION_PATH="$BUILD_PATH/$PLUGIN_SLUG"

# Get the version from the main plugin file
MAIN_FILE=$(grep -l "Plugin Name:" *.php | head -n 1)
if [ -z "$MAIN_FILE" ]; then
    VERSION="1.0.0"
    PLUGIN_NAME="$PLUGIN_SLUG"
else
    VERSION=$(grep -m 1 "Version:" "$MAIN_FILE" | awk '{print $NF}' | tr -d '\r')
    PLUGIN_NAME=$(grep -m 1 "Plugin Name:" "$MAIN_FILE" | cut -d: -f2 | sed 's/^ //')
fi

if [ -z "$VERSION" ]; then
    VERSION="1.0.0"
fi

ZIP_FILE="${PLUGIN_SLUG}-${VERSION}.zip"

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

status "💃 Time to release ${PLUGIN_NAME:-$PLUGIN_SLUG} 🙂"

warning "Ready to proceed to create a zip? [Y/N]: "
read -r PROCEED

if [ "$(echo "${PROCEED:-n}" | tr "[:upper:]" "[:lower:]")" != "y" ]; then
	error "Release cancelled!"
	exit 1
fi

status "Removing previously generated zip (if exists)..."
rm -f "$PROJECT_PATH/${PLUGIN_SLUG}"*.zip

status "Generating build directory..."
rm -rf "$BUILD_PATH"
mkdir -p "$DESTINATION_PATH"

status "Syncing files..."

rsync -rc --exclude-from="$PROJECT_PATH/.distignore" "$PROJECT_PATH/" "$DESTINATION_PATH" --delete --delete-excluded

# Remove plugin header from devdiggers-framework/init.php if exists
FRAMEWORK_INIT_FILE="$DESTINATION_PATH/devdiggers-framework/init.php"
if [ -f "$FRAMEWORK_INIT_FILE" ]; then
	TEMP_FILE="${FRAMEWORK_INIT_FILE}.tmp"
	sed -e 's/^ \* Plugin Name:/ * Framework Name -/' \
		-e 's/^ \* Description:/ * Framework Description -/' \
		-e 's/^ \* Plugin URI:/ * Framework URI -/' \
		-e 's/^ \* Domain Path:/ * Framework Domain Path -/' \
		"$FRAMEWORK_INIT_FILE" > "$TEMP_FILE"
	mv "$TEMP_FILE" "$FRAMEWORK_INIT_FILE"
	success "Plugin header removed from devdiggers-framework/init.php"
fi

status "Generating zip file..."

cd "$BUILD_PATH" || exit

zip -q -r "$ZIP_FILE" "$PLUGIN_SLUG/"

cd "$PROJECT_PATH" || exit
mv "$BUILD_PATH/$ZIP_FILE" "$PROJECT_PATH"
status "$ZIP_FILE file generated!"

rm -rf "$BUILD_PATH"

success "Done. You've built ${PLUGIN_SLUG} zip! 🎉 "
