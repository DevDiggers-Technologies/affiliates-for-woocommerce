<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all admin end ajax action callbacks.
 */

namespace DDWCAffiliates\Includes\Admin;

use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Admin_Ajax_Functions' ) ) {
    /**
     * Admin Ajax Functions Class
     */
    class DDWCAF_Admin_Ajax_Functions {

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
		 * Fetch affiliates for creating payout function
		 *
		 * @return void
		 */
		public function ddwcaf_create_payout_for_affiliates() {
			$response = [];

			if ( check_ajax_referer( 'ddwcaf-nonce', 'nonce', false ) ) {
				if ( ! empty( $_POST[ 'ddwcaf_response' ] ) ) {
					$response = json_decode( stripslashes( $_POST[ 'ddwcaf_response' ] ), true );
				} else {
					$response = [
						'total_affiliates'      => 0,
						'current_page'          => 1,
						'percentage_completed'  => 0,
						'affiliates_count_done' => 0,
					];
				}

				// Log entry.
				if ( 1 === $response[ 'current_page' ] ) {
					$log = [
						'done'    => [],
						'failed'  => [],
						'skipped' => [],
					];
				} else {
					$log = get_user_option( 'ddwcaf_create_payout_log' );
				}

				$per_page       = 20;
				$affiliate_ids  = ! empty( $_POST[ 'ddwcaf_affiliates' ] ) ? array_map( 'sanitize_text_field', $_POST[ 'ddwcaf_affiliates' ] ) : [];
				$all_affiliates = ! empty( $_POST[ 'ddwcaf_all_affiliates' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ddwcaf_all_affiliates' ] ) ) : '';
				$offset         = ( $response[ 'current_page' ] - 1 ) * $per_page;

				$args = [
					'per_page'              => $per_page,
					'offset'                => $offset,
					'reference'             => ! empty( $_POST[ 'ddwcaf_reference' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ddwcaf_reference' ] ) ) : '',
					'from_date'             => ! empty( $_POST[ 'ddwcaf_from_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ddwcaf_from_date' ] ) ) : '',
					'end_date'              => ! empty( $_POST[ 'ddwcaf_end_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ddwcaf_end_date' ] ) ) : '',
					'include_affiliate_ids' => $affiliate_ids,
					'all_affiliates'        => $all_affiliates,
				];

				$affiliate_helper = new DDWCAF_Affiliate_Helper( $this->ddwcaf_configuration );
				$affiliates       = $affiliate_helper->ddwcaf_get_all_affiliates_having_pending_commissions( $args );

				++$response[ 'current_page' ];

				if ( ! empty( $affiliates ) ) {
					if ( empty( $response[ 'total_affiliates' ] ) ) {
						$response[ 'total_affiliates' ] = $affiliate_helper->ddwcaf_get_all_affiliates_count_having_pending_commissions( $args );
					}

					$payout_helper = new DDWCAF_Payout_Helper( $this->ddwcaf_configuration );

					foreach ( $affiliates as $affiliate ) {
						++$response[ 'affiliates_count_done' ];

						$args[ 'affiliate_id' ]            = intval( $affiliate[ 'affiliate_id' ] );
						$args[ 'total_commission_amount' ] = floatval( $affiliate[ 'total_commission_amount' ] );

						if ( $payout_helper->ddwcaf_pay_affiliate_unpaid_amount( $args ) ) {
							$log[ 'done' ][] = $affiliate[ 'user_login' ];
						} else {
							$log[ 'skipped' ][] = new \WP_Error(
								'ddwcaf_create_payout_error',
								esc_html__( 'No pending commissions to pay for the affiliate.', 'affiliates-for-woocommerce' ),
								[
									'username' => $affiliate[ 'user_login' ],
									'data'     => sprintf( esc_html__( 'Affiliate username: %s, From Date: %s, To Date: %s', 'affiliates-for-woocommerce' ), $affiliate[ 'user_login' ], $args[ 'from_date' ], $args[ 'end_date' ] ),
								]
							);
						}
					}
				}

				update_user_option( get_current_user_id(), 'ddwcaf_create_payout_log', $log );

				$response[ 'percentage_completed' ] = $response[ 'total_affiliates' ] ? floor( ( $response[ 'affiliates_count_done' ] / $response[ 'total_affiliates' ] ) * 100 ) : 100;
			} else {
				$response = [
					'error'   => true,
					'message' => esc_html__( 'Security check failed!', 'affiliates-for-woocommerce' ),
				];
			}
			wp_send_json( $response );
		}
    }
}
