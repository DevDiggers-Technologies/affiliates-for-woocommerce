<?php
/**
 * Framework Name - DevDiggers Framework
 * Framework Description - <code><strong>DevDiggers Framework Plugin</strong></code> is a powerful and flexible framework designed to help developers create WordPress plugins for DevDiggers with ease. It provides a set of tools and features that streamline the development process, allowing for rapid plugin creation and customization.
 * Framework URI - https://devdiggers.com/woocommerce-extensions/?utm_source=DevDiggers Plugin Framework&utm_medium=Plugins List&utm_campaign=WooCommerce Extensions
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.0.0
 * Text Domain: devdiggers-framework
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Tested up to: 6.x.x
 * Stable tag: 1.0.0
 * Text Domain: devdiggers-framework
 * Framework Domain Path - /i18n
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// Define Constants.
defined( 'DDFW_LOADED' ) || define( 'DDFW_LOADED', true );
defined( 'DDFW_URL' ) || define( 'DDFW_URL', plugin_dir_url( __FILE__ ) );
defined( 'DDFW_FILE' ) || define( 'DDFW_FILE', plugin_dir_path( __FILE__ ) );
defined( 'DDFW_SCRIPT_VERSION' ) || define( 'DDFW_SCRIPT_VERSION', '1.0.0' );

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
	require_once DDFW_FILE . 'includes/class-devdiggers-notifications.php';
}

load_textdomain( 'devdiggers-framework', dirname( __FILE__ ) . '/i18n/devdiggers-framework-' . apply_filters( 'plugin_locale', determine_locale(), 'devdiggers-framework' ) . '.mo' );
