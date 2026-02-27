<?php
/**
 * File for handling the DevDiggers Admin functionalities.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Admin' ) ) {
	/**
	 * Class for handling the DevDiggers Admin functionalities.
	 */
	class DDFW_Admin {
		/**
		 * The single instance of the class.
		 *
		 * @var DDFW_Admin
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return DDFW_Admin
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor to initialize hooks.
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'add_main_menu' ] );
			add_action( 'admin_head', [ $this, 'remove_admin_notices' ] );
			// Add custom plugin headers.
			add_filter( 'extra_plugin_headers', [ $this, 'add_custom_plugin_headers' ] );
		}

		/**
		 * Add the main menu for the DevDiggers Plugin Framework.
		 * 
		 * @return void
		 */
		public function add_main_menu() {
			/**
			 * Modify admin menu capability.
			 * 
			 * @since 1.0.0
			 */
			$menu_capability = ddfw_get_menu_capability();
			$parent_slug     = ddfw_get_parent_menu_slug();

			add_menu_page( esc_html__( 'DevDiggers Plugins', 'devdiggers-framework' ), esc_html__( 'DevDiggers Plugins', 'devdiggers-framework' ), $menu_capability, $parent_slug, [ $this, 'ddfw_get_main_dashboard' ], ddfw_get_devdiggers_plugin_menu_icon_src(), 56 );

			add_submenu_page( $parent_slug, esc_html__( 'Dashboard', 'devdiggers-framework' ), esc_html__( 'Dashboard', 'devdiggers-framework' ), $menu_capability, $parent_slug, [ $this, 'ddfw_get_main_dashboard' ] );
			add_submenu_page( $parent_slug, esc_html__( 'Extensions', 'devdiggers-framework' ), esc_html__( 'Extensions', 'devdiggers-framework' ), $menu_capability, 'devdiggers-extensions', [ $this, 'ddfw_get_extensions_page' ] );
		}

		/**
		 * Get the main dashboard
		 * 
		 * @return void
		 */
		public function ddfw_get_main_dashboard() {
			include DDFW_FILE . 'templates/layout/dashboard.php';
		}

		/**
		 * Get the extensions page
		 * 
		 * @return void
		 */
		public function ddfw_get_extensions_page() {
			include DDFW_FILE . 'templates/layout/extensions.php';
		}

		/**
		 * Remove admin notices
		 * 
		 * @return void
		 */
		public function remove_admin_notices() {
			$screen = get_current_screen();
			if ( $screen && ( 
				( isset( $screen->id ) && strpos( $screen->id, 'devdiggers-plugins' ) !== false ) ||
				( isset( $screen->base ) && strpos( $screen->base, 'devdiggers-plugins' ) !== false )
			) ) {
				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );
			}
		}

		/**
		 * Add custom plugin headers.
		 * 
		 * @param array $headers The existing plugin headers.
		 * @return array
		 */
		public function add_custom_plugin_headers( $headers ) {
			$headers[] = 'DevDiggersPrefix';
			return $headers;
		}
	}
}

DDFW_Admin::instance();
