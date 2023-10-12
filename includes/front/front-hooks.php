<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all front end action hooks.
 */

namespace DDWCAffiliates\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Front_Hooks' ) ) {
    /**
     * Front end hooks class
     */
    class DDWCAF_Front_Hooks extends DDWCAF_Front_Functions {
        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            parent::__construct( $ddwcaf_configuration );

            add_filter( 'query_vars', [ $this, 'ddwcaf_add_query_vars' ] );

            if ( ! empty( $ddwcaf_configuration[ 'my_account_enabled' ] ) ) {
                add_filter( 'woocommerce_account_menu_items', [ $this, 'ddwcaf_add_woocommerce_menu' ] );

                add_action( 'woocommerce_account_' . $ddwcaf_configuration[ 'my_account_endpoint' ] . '_endpoint', [ $this, 'ddwcaf_add_my_account_endpoint_content' ] );

                add_filter( 'the_title', [ $this, 'ddwcaf_change_endpoint_title' ] );

                add_filter( 'sidebars_widgets', [ $this, 'ddwcaf_remove_sidebar_from_custom_menu_page' ] );
            }

            add_action( 'wp_enqueue_scripts', [ $this, 'ddwcaf_front_scripts' ] );

            add_action( 'ddwcaf_add_affiliate_registration_fields', [ $this, 'ddwcaf_add_affiliate_registration_fields' ] );

            if ( ! empty( $ddwcaf_configuration[ 'fields_enabled_on_woocommerce_registration' ] ) ) {
                add_action( 'woocommerce_register_form', [ $this, 'ddwcaf_add_affiliate_registration_fields' ] );
            }

            add_filter( 'woocommerce_registration_errors', [ $this, 'ddwcaf_woocommerce_registration_errors' ] );

            add_filter( 'woocommerce_new_customer_data', [ $this, 'ddwcaf_woocommerce_new_customer_data' ] );

            add_action( 'woocommerce_created_customer', [ $this, 'ddwcaf_woocommerce_created_customer' ] );

            add_filter( 'woocommerce_registration_redirect', [ $this, 'ddwcaf_woocommerce_registration_redirect' ], 99 );

            add_filter( 'woocommerce_login_redirect', [ $this, 'ddwcaf_woocommerce_login_redirect' ], 99, 2 );

            add_action( 'wp_loaded', [ $this, 'ddwcaf_handle_wp_loaded' ] );

            add_action( 'wp', [ $this, 'ddwcaf_handle_wp' ] );

            add_action( 'woocommerce_checkout_order_processed', [ $this, 'ddwcaf_handle_checkout_order_processed' ], 10, 3 );

            add_shortcode( $this->ddwcaf_validate_shortcode( $ddwcaf_configuration[ 'affiliate_registration_form_shortcode' ] ), [ $this, 'ddwcaf_get_affiliate_registration_form_shortcode_content' ] );

            add_shortcode( $this->ddwcaf_validate_shortcode( $ddwcaf_configuration[ 'affiliate_dashboard_shortcode' ] ), [ $this, 'ddwcaf_get_affiliate_dashboard_shortcode_shortcode_content' ] );
        }

        /**
		 * Validate shortcode function
		 *
		 * @param string $shortcode
		 * @return string
		 */
		public function ddwcaf_validate_shortcode( $shortcode ) {
			$shortcode = str_replace( '[', '', $shortcode );
			$shortcode = str_replace( ']', '', $shortcode );
			$shortcode = str_replace( ' ', '', $shortcode );

			return $shortcode;
		}
    }
}
