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
            add_action( 'admin_init', [ $this, 'ddwcaf_register_settings' ] );

            add_action( 'user_new_form', [ $this, 'ddwcaf_add_user_form_fields' ] );

            add_action( 'user_register', [ $this, 'ddwcaf_save_user_custom_data' ], 99 );

            // handle refunds.
			add_action( 'woocommerce_refund_created', [ $this, 'ddwcaf_handle_refund_created' ] );
        }
    }
}
