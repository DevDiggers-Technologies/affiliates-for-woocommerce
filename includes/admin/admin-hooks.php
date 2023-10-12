<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all admin end action hooks.
 */

namespace DDWCAffiliates\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Admin_Hooks' ) ) {
    /**
     * Admin end hook handler class
     */
    class DDWCAF_Admin_Hooks extends DDWCAF_Admin_Functions {
        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            parent::__construct( $ddwcaf_configuration );

            add_action( 'admin_notices', [ $this, 'ddwcaf_add_admin_notices' ] );

            add_action( 'admin_menu', [ $this, 'ddwcaf_add_dashboard_menu' ] );

            add_action( 'admin_head', [ $this, 'ddwcaf_admin_head' ] );

			add_filter( 'set-screen-option', [ $this, 'ddwcaf_set_options' ], 10, 3 );

			add_filter( 'woocommerce_screen_ids', [ $this, 'ddwcaf_set_wc_screen_ids' ] );

            add_filter( 'admin_footer_text', [ $this, 'ddwcaf_set_admin_footer_text' ], 99 );

            add_action( 'admin_init', [ $this, 'ddwcaf_register_settings' ] );

            add_action( 'admin_enqueue_scripts', [ $this, 'ddwcaf_enqueue_admin_scripts' ] );

            add_action( 'user_new_form', [ $this, 'ddwcaf_add_user_form_fields' ] );

            add_action( 'user_register', [ $this, 'ddwcaf_save_user_custom_data' ], 99 );

            // handle refunds.
			add_action( 'woocommerce_refund_created', [ $this, 'ddwcaf_handle_refund_created' ] );
        }
    }
}
