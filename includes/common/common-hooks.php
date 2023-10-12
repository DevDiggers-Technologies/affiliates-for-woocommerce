<?php
/**
 * Common hooks class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Includes\Common;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Common_Hooks' ) ) {
	/**
	 * Common hooks class
	 */
	class DDWCAF_Common_Hooks extends DDWCAF_Common_Functions {
		/**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            parent::__construct( $ddwcaf_configuration );

			add_action( 'init', [ $this, 'ddwcaf_add_endpoints' ] );

			add_filter( 'woocommerce_order_status_changed', [ $this, 'ddwcaf_handle_order_status_changed' ], 10, 3 );
        }
	}
}
