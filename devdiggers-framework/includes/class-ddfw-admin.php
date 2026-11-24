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

			add_menu_page( esc_html__( 'DevDiggers Plugins', 'affiliates-for-woocommerce' ), esc_html__( 'DevDiggers Plugins', 'affiliates-for-woocommerce' ), $menu_capability, $parent_slug, [ $this, 'ddfw_get_main_dashboard' ], ddfw_get_devdiggers_plugin_menu_icon_src(), 56 );

			add_submenu_page( $parent_slug, esc_html__( 'Dashboard', 'affiliates-for-woocommerce' ), esc_html__( 'Dashboard', 'affiliates-for-woocommerce' ), $menu_capability, $parent_slug, [ $this, 'ddfw_get_main_dashboard' ] );
			add_submenu_page( $parent_slug, esc_html__( 'Extensions', 'affiliates-for-woocommerce' ), esc_html__( 'Extensions', 'affiliates-for-woocommerce' ), $menu_capability, 'devdiggers-extensions', [ $this, 'ddfw_get_extensions_page' ] );
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
			// The list comes from devdiggers.com, so it is only loaded after an administrator opts in on this page.
			if ( isset( $_POST['ddfw_extensions_consent'] ) ) {
				if ( empty( $_POST['ddfw_extensions_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ddfw_extensions_nonce'] ) ), 'ddfw_extensions_consent_action' ) ) {
					wp_die( esc_html__( 'Security check failed.', 'affiliates-for-woocommerce' ) );
				}

				if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
					wp_die( esc_html__( 'You are not allowed to change this setting.', 'affiliates-for-woocommerce' ) );
				}

				if ( 'yes' === sanitize_key( wp_unslash( $_POST['ddfw_extensions_consent'] ) ) ) {
					update_option( 'ddfw_extensions_consent', 'yes', false );
				} else {
					delete_option( 'ddfw_extensions_consent' );
					DDFW_Plugins_API::instance()->clear_cache();
				}
			}

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
