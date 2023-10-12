#!/usr/bin/env bash

GREEN_BOLD='\033[1;32m';

success () {
	echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}

# Substitute JS source references with build references
for T in `find i18n -name "*.pot"`
	do
		sed \
			-e 's/#: src\/reports[^:]*:/#: assets\/js\/reports.js:/gp' \
			$T | uniq > $T-build
		rm $T
		mv $T-build $T
	done

success "Successfully replaced development mappings with production files in affiliates-for-woocommerce.pot 🎉 ";

# Check for required version
WPCLI_VERSION=`wp cli version | cut -f2 -d' '`
if [ ${WPCLI_VERSION:0:1} -lt "2" -o ${WPCLI_VERSION:0:1} -eq "2" -a ${WPCLI_VERSION:2:1} -lt "1" ]; then
	echo WP-CLI version 2.1.0 or greater is required to make JSON translation files
	exit
fi

# Make the JSON files
# wp i18n make-json i18n --no-purge
