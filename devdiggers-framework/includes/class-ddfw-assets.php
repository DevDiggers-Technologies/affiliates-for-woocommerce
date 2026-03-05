<?php
/**
 * File for handling assets in the DevDiggers Framework Plugin.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Assets' ) ) {
	/**
	 * Class for handling assets related to form fields.
	 */
	class DDFW_Assets {
		/**
		 * The single instance of the class.
		 *
		 * @var DDFW_Assets
		 */
		private static $instance;

		/**
		 * Handle for the framework CSS.
		 *
		 * @var string
		 */
		public static $framework_css_handle = 'ddfw-framework-style';

		/**
		 * Handle for the framework JS.
		 *
		 * @var string
		 */
		public static $framework_js_handle = 'ddfw-framework-script';

		/**
		 * Singleton implementation.
		 *
		 * @return DDFW_Assets
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor to initialize hooks.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', [ $this, 'register_styles_and_scripts' ] );
		}

		/**
		 * Register styles and scripts for the fields.
		 * 
		 * @return void
		 */
		public function register_styles_and_scripts() {
			// Enqueue inline style
			wp_enqueue_style( 'ddfw-admin-inline', true );
			wp_add_inline_style( 'ddfw-admin-inline', '
				.ddfw-dashicon-external {
					font-size: 14px;
					vertical-align: -2px;
					height: 10px;
				}
			' );

			// Enqueue inline script
			wp_add_inline_script( 'jquery', '
				jQuery( document ).ready( function( $ ) {
					$( "ul#adminmenu a[href*=\'devdiggers.com\']" ).attr( \'target\', \'_blank\' );
				} );
			' );

			wp_register_style( 'select2', DDFW_URL . 'assets/css/select2.css', [], filemtime( DDFW_FILE . 'assets/css/select2.css' ) );
			wp_register_script( 'select2', DDFW_URL . 'assets/js/select2.js', [], filemtime( DDFW_FILE . 'assets/js/select2.js' ) );

			wp_register_style( self::$framework_css_handle, DDFW_URL . 'assets/css/framework.css', [ 'select2' ], filemtime( DDFW_FILE . 'assets/css/framework.css' ) );
			wp_register_script( self::$framework_js_handle, DDFW_URL . 'assets/js/framework.js', [ 'select2', 'wp-color-picker' ], filemtime( DDFW_FILE . 'assets/js/framework.js' ) );

			if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], [ 'devdiggers-plugins', 'devdiggers-extensions' ], true ) ) {
				wp_enqueue_style( 'ddfw-dashboard-style', DDFW_URL . 'assets/css/dashboard.css', [], filemtime( DDFW_FILE . 'assets/css/dashboard.css' ) );
				wp_enqueue_script( 'ddfw-dashboard-script', DDFW_URL . 'assets/js/dashboard.js', [], filemtime( DDFW_FILE . 'assets/js/dashboard.js' ) );

				wp_localize_script(
					'ddfw-dashboard-script',
					'ddfwDashboardObject',
					[
						'ajax' => [
							'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
							'ajaxNonce' => wp_create_nonce( 'ddfw-nonce' ),
						],
						'i18n' => [
							'subscribing'         => esc_html__( 'Subscribing...', 'devdiggers-framework' ),
							'subscribe'           => esc_html__( 'Subscribe', 'devdiggers-framework' ),
							'subscriptionSuccess' => esc_html__( 'Thank you for subscribing!', 'devdiggers-framework' ),
							'subscriptionError'   => esc_html__( 'An error occurred. Please try again.', 'devdiggers-framework' ),
						],
					]
				);
			}

			// Get current DevDiggers plugin dynamically
			$devdiggers_plugin = $this->get_current_devdiggers_plugin();

			wp_localize_script(
				self::$framework_js_handle,
				'ddfwFrameworkObject',
				[
					'ajax' => [
						'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
						'ajaxNonce' => wp_create_nonce( 'ddfw-nonce' ),
					],
					'i18n' => [
						'selectImage'         => esc_html__( 'Select Image', 'devdiggers-framework' ),
						'useImage'            => esc_html__( 'Use Image', 'devdiggers-framework' ),
						'pleaseEnter'         => esc_html__( 'Please enter', 'devdiggers-framework' ),
						'moreCharacter'       => esc_html__( 'or more character', 'devdiggers-framework' ),
						'noResult'            => esc_html__( 'No result Found', 'devdiggers-framework' ),
						'deleteConfirm'       => esc_html__( 'Are you sure you want to delete?', 'devdiggers-framework' ),
					],
					'site_url'          => site_url(),
					'devdiggers_plugin' => $devdiggers_plugin,
				]
			);
		}

		/**
		 * Get current DevDiggers plugin dynamically
		 * 
		 * @return array
		 */
		private function get_current_devdiggers_plugin() {
			$plugin = [];
			$current_page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			if ( strpos( $current_page, 'dd' ) !== false ) {
				$prefix = str_replace( '-dashboard', '', $current_page );
				$plugin = [
					'page_slug'          => $current_page,
					'purchase_code'      => get_option( '_' . $prefix . '_purchase_code' ),
					'configuration_menu' => 'configuration',
					'plugin_prefix'      => $prefix,
				];
			}

			return $plugin;
		}
	}
}

DDFW_Assets::instance();
