<?php
/**
 * Common functions class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Includes\Common;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Common_Functions' ) ) {
	/**
	 * Common functions
	 */
	class DDWCAF_Common_Functions {
		/**
         * Configuration Variable
         *
         * @var array
         */
        protected $ddwcaf_configuration;

        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            $this->ddwcaf_configuration = $ddwcaf_configuration;
        }

		/**
         * Register new endpoint to use inside My Account page.
		 *
		 * @return void
		 */
        public function ddwcaf_add_endpoints() {
            add_rewrite_endpoint( $this->ddwcaf_configuration[ 'my_account_endpoint' ], EP_ROOT | EP_PAGES );

            $affiliate_helper = new DDWCAF_Affiliate_Helper( $this->ddwcaf_configuration );
            $endpoints        = $affiliate_helper->ddwcaf_get_dashboard_endpoints();

			foreach ( $endpoints as $key => $endpoint ) {
				add_rewrite_endpoint( $endpoint[ 'endpoint' ], EP_ROOT | EP_PAGES );
			}
		}

		/**
		 * Changes status of commissions related to an order, after of a status change for the order function
		 *
		 * @param int    $order_id   Order id.
		 * @param string $old_status Old order status.
		 * @param string $new_status New order status.
		 *
		 * @return void
		 */
		public function ddwcaf_handle_order_status_changed( $order_id, $old_status, $new_status ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return;
			}

			if ( $order->has_status( 'trash' ) ) {
				return;
			}

			$visit_helper      = new DDWCAF_Visit_Helper( $this->ddwcaf_configuration );
			$commission_helper = new DDWCAF_Commission_Helper( $this->ddwcaf_configuration );

			$visit_helper->ddwcaf_register_visit_conversion( $order_id );

			$items = $order->get_items();

			if ( empty( $items ) ) {
				return;
			}

			$commission_status = $commission_helper->ddwcaf_get_commission_status_by_order_status( $new_status );
			$commissions       = $commission_helper->ddwcaf_get_commissions(
				[
					'order_id' => $order_id,
					'per_page' => 1000,
					'offset'   => 0,
				]
			);

			if ( ! empty( $commissions ) ) {
				foreach ( $commissions as $commission ) {
					// if we're paying commission, please skip any user total change.
					if ( 'pending_payment' === $commission[ 'status' ] || 'paid' === $commission[ 'status' ] ) {
						continue;
					}

					$commission_helper->ddwcaf_update_commission_status( $commission[ 'id' ], $commission_status );
				}
			}
		}
	}
}
