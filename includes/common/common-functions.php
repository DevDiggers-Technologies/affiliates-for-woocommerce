<?php
/**
 * Common functions class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Includes\Common;

use DDWCAffiliates\Includes\DDWCAF_Email_Notification_Handler;
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
		 * Add SVG icons
		 *
		 * @param array $default_svg_icons
		 * @param array $args
		 * @return array
		 */
		public function ddwcaf_add_svg_icons( $default_svg_icons, $args ) {
			$size         = ! empty( $args['size'] ) ? $args['size'] : '24';
			$size_attr    = 'width="' . $size . '" height="' . $size . '"';
			$stroke_color = ! empty( $args['stroke_color'] ) ? $args['stroke_color'] : 'currentColor';
			$stroke_width = isset( $args['stroke_width'] ) ? $args['stroke_width'] : '2';
			$fill         = ! empty( $args['fill'] ) ? $args['fill'] : 'none';

			$svg_icons = [
				'referrals'   => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
				'commissions' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
				'shortcodes'  => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
				'emails'      => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>',
				'endpoints'   => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7A5 5 0 0 1 7 7h2"></path><path d="M15 7h2a5 5 0 0 1 0 10h-2"></path><line x1="8" y1="12" x2="16" y2="12"></line></svg>',
				'payouts'     => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>',
				'social'      => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"></line><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"></line></svg>',
				'total_earnings' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>',
				'paid_amount' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line><path d="M17 14h.01"></path><path d="M13 14h.01"></path></svg>',
				'unpaid_amount' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
				'visitors' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
				'customers' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>',
				'conversion_rate' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>',
				'layout'      => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>',
				'info'        => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>',
			];

			return array_merge( $default_svg_icons, $svg_icons );
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

			$visit_helper = new DDWCAF_Visit_Helper( $this->ddwcaf_configuration );

			$visit_helper->ddwcaf_register_visit_conversion( $order_id );

			$items = $order->get_items();

			if ( empty( $items ) ) {
				return;
			}

			$commission_helper = new DDWCAF_Commission_Helper( $this->ddwcaf_configuration );

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
