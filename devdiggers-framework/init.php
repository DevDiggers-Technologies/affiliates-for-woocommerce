<?php
/**
 * Framework Name - DevDiggers Framework
 * Framework Description - <code><strong>DevDiggers Framework Plugin</strong></code> is a powerful and flexible framework designed to help developers create WordPress plugins for DevDiggers with ease. It provides a set of tools and features that streamline the development process, allowing for rapid plugin creation and customization.
 * Framework URI - https://devdiggers.com/woocommerce-extensions/
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.1.1
 * DDFW Build: free
 * Text Domain: affiliates-for-woocommerce
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Tested up to: 6.x.x
 * Stable tag: 1.1.1
 * Text Domain: affiliates-for-woocommerce
 * Framework Domain Path - /i18n
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 * @version 1.1.1
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

/*
 * Framework version resolver.
 *
 * Every DevDiggers plugin bundles its own copy of the framework and loads it only when no copy is
 * loaded yet, so the plugin that loads first used to win whatever its version. Starting with
 * 1.1.0, the first copy to load looks at the copies of every active plugin and boots the newest
 * one (a pro build wins a tie, because free builds strip the license code).
 *
 * ponytail: reads the header of each bundled copy once per request (~8 KB each); cache it in a
 * transient if sites ever run dozens of DevDiggers plugins at once.
 */
if ( ! defined( 'DDFW_RESOLVED' ) ) {
	define( 'DDFW_RESOLVED', true );
	defined( 'DDFW_LOADED' ) || define( 'DDFW_LOADED', true );

	$ddfw_headers   = [
		'version' => 'Version',
		'build'   => 'DDFW Build',
	];
	$ddfw_this_file = wp_normalize_path( __FILE__ );
	$ddfw_best      = array_merge( get_file_data( __FILE__, $ddfw_headers ), [ 'file' => $ddfw_this_file ] );
	$ddfw_active    = (array) get_option( 'active_plugins', [] );

	if ( is_multisite() ) {
		$ddfw_active = array_merge( $ddfw_active, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
	}

	foreach ( array_unique( $ddfw_active ) as $ddfw_plugin ) {
		$ddfw_dir = dirname( $ddfw_plugin );

		if ( '.' === $ddfw_dir ) {
			continue;
		}

		// Folder name read from disk, not written as a string, so a plugin's text domain
		// search and replace over this copy cannot rewrite the path.
		$ddfw_folder    = basename( __DIR__ );
		$ddfw_candidate = wp_normalize_path( WP_PLUGIN_DIR . '/' . $ddfw_dir . ( $ddfw_folder === $ddfw_dir ? '' : '/' . $ddfw_folder ) . '/init.php' );

		if ( $ddfw_candidate === $ddfw_best['file'] || ! is_readable( $ddfw_candidate ) ) {
			continue;
		}

		$ddfw_data    = get_file_data( $ddfw_candidate, $ddfw_headers );
		$ddfw_compare = version_compare( (string) $ddfw_data['version'], (string) $ddfw_best['version'] );

		if ( $ddfw_compare > 0 || ( 0 === $ddfw_compare && 'pro' === $ddfw_data['build'] && 'pro' !== $ddfw_best['build'] ) ) {
			$ddfw_best = array_merge( $ddfw_data, [ 'file' => $ddfw_candidate ] );
		}
	}

	$ddfw_resolved_file = $ddfw_best['file'];

	unset( $ddfw_headers, $ddfw_best, $ddfw_active, $ddfw_plugin, $ddfw_dir, $ddfw_folder, $ddfw_candidate, $ddfw_data, $ddfw_compare );

	if ( $ddfw_resolved_file !== $ddfw_this_file ) {
		unset( $ddfw_this_file );
		require $ddfw_resolved_file;
		return;
	}

	unset( $ddfw_this_file, $ddfw_resolved_file );
}

// Define Constants.
defined( 'DDFW_LOADED' ) || define( 'DDFW_LOADED', true );
defined( 'DDFW_URL' ) || define( 'DDFW_URL', plugin_dir_url( __FILE__ ) );
defined( 'DDFW_FILE' ) || define( 'DDFW_FILE', plugin_dir_path( __FILE__ ) );
defined( 'DDFW_SCRIPT_VERSION' ) || define( 'DDFW_SCRIPT_VERSION', '1.1.1' );
defined( 'DDFW_VERSION' ) || define( 'DDFW_VERSION', '1.1.1' );

// Include the autoloader.
require_once DDFW_FILE . 'autoload/autoload.php';

// Load the framework files.
require_once DDFW_FILE . 'global-functions.php';
require_once DDFW_FILE . 'includes/class-ddfw-plugins-api.php';

if ( is_admin() ) {
	require_once DDFW_FILE . 'includes/class-ddfw-assets.php';
	require_once DDFW_FILE . 'includes/class-ddfw-admin.php';
	require_once DDFW_FILE . 'includes/class-ddfw-ajax.php';
	require_once DDFW_FILE . 'includes/class-ddfw-review-notice.php';
}

load_textdomain( 'affiliates-for-woocommerce', dirname( __FILE__ ) . '/i18n/affiliates-for-woocommerce-' . apply_filters( 'plugin_locale', determine_locale(), 'affiliates-for-woocommerce' ) . '.mo' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress hook.
