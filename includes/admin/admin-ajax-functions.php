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
		 * Get Products List
		 *
		 * @return void
		 */
		public function ddwcaf_get_products_list() {
			$response = [];
			if ( check_ajax_referer( 'ddwcaf-nonce', 'nonce', false ) ) {

                $search_results = new \WP_Query( [
                    's'                   => sanitize_text_field( wp_unslash( $_POST[ 'query' ] ) ),
                    'post_type'           => [ 'product', 'product_variation' ],
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => 1,
                    'posts_per_page'      => 10,
                    'search_columns'      => [ 'post_title' ],
					'fields'              => [ 'ID', 'post_title' ],
                ] );

                if ( $search_results->have_posts() ) {
					while ( $search_results->have_posts() ) {
						$search_results->the_post();

						$product_id = $search_results->post->ID;
						$product    = wc_get_product( $product_id );

						if ( 'variable' !== $product->get_type() ) {
							$response[] = [
								'ID'    => $product_id,
								'title' => rawurldecode( wp_strip_all_tags( $product->get_formatted_name() ) ),
							];
						}
					}
				}

				wp_reset_postdata();

			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'affiliates-for-woocommerce' ),
				];
			}
			wp_send_json( $response );
        }

		/**
		 * Get categories list function
		 *
		 * @return void
		 */
		public function ddwcaf_get_categories_list() {
			$response = [];
			if ( check_ajax_referer( 'ddwcaf-nonce', 'nonce', false ) ) {
				$query = isset( $_POST[ 'query' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'query' ] ) ) : ''; // wpcs: input var okay.

				$categories = get_terms( [
					'taxonomy' => 'product_cat',
					'search'   => esc_attr( $query ),
				] );

				$response = [
					'success'    => true,
					'message'    => '',
					'categories' => $categories,
				];
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'affiliates-for-woocommerce' ),
				];
			}
			wp_send_json( $response );
		}

		/**
		 * Get Users
		 *
		 * @return void
		 */
		public function ddwcaf_get_affiliates_list() {
			$response = [];
			if ( check_ajax_referer( 'ddwcaf-nonce', 'nonce', false ) ) {
				$query = isset( $_POST[ 'query' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'query' ] ) ) : ''; // wpcs: input var okay.

				$query = new \WP_User_Query( [
					'role'           => 'ddwcaf_affiliate',
					'search'         => '*' . esc_attr( $query ) . '*',
					'search_columns' => [ 'user_login', 'user_email', 'display_name', 'ID' ],
					'fields'         => [ 'user_email', 'user_login', 'ID' ],
					'number'         => 20,
				] );

				$response = [
					'success' => true,
					'message' => esc_html__( 'Successfully fetched!', 'affiliates-for-woocommerce' ),
					'users'   => $query->get_results(),
				];
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'affiliates-for-woocommerce' ),
				];
			}
			wp_send_json( $response );
		}
    }
}
