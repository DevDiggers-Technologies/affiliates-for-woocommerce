<?php
/**
 * This file handles all front end action ajax callbacks.
 *
 * @package Affiliates for WooCommerce
 * @author DevDiggers
 * @version 1.0.0
 */

namespace DDWCAffiliates\Includes\Front;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Front_Ajax_Functions' ) ) {    
	/**
	 * Front functions ajax class
	 */
	class DDWCAF_Front_Ajax_Functions {
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
		 * Get custom referral html function
		 *
		 * @return void
		 */
		public function ddwcaf_get_custom_referral_html() {
			if ( check_ajax_referer( 'ddwcaf-nonce', 'nonce', false ) ) {
				if ( ! empty( $_POST[ 'custom_page_url' ] ) ) {
					$custom_url = urldecode( sanitize_text_field( wp_unslash( $_POST[ 'custom_page_url' ] ) ) );

					// check if base is a valid url.
					// $url       = filter_var( $custom_url, FILTER_VALIDATE_URL );
					$url       = $custom_url;
					$base_host = wp_parse_url( $url, PHP_URL_HOST );
					$site_host = wp_parse_url( site_url(), PHP_URL_HOST );

					if ( $base_host !== $site_host ) {
						$url = '';
					}

					$user_id                = get_current_user_id();
					$affiliate_helper       = new DDWCAF_Affiliate_Helper( $this->ddwcaf_configuration );
					$affiliate_referral_url = $affiliate_helper->ddwcaf_get_affiliate_referral_url( $user_id, [], $url );

					ob_start();
					$affiliate_helper->ddwcaf_render_custom_referral_link_result( $affiliate_referral_url );
					$html = ob_get_clean();

					$response = [
						'success' => true,
						'html'    => $html,
					];
				} else {
					$response = [
						'success' => false,
						'message' => esc_html__( 'Page url not provided.', 'affiliates-for-woocommerce' ),
					];
				}
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'affiliates-for-woocommerce' ),
				];
			}

			wp_send_json( $response );
		}

		/**
		 * Get table rows function
		 *
		 * @return void
		 */
		public function ddwcaf_get_table_rows() {
			$response = [];
			if ( check_ajax_referer( 'ddwcaf-nonce', 'nonce', false ) ) {
				if ( ! empty( $_POST['table'] ) && ! empty( $_POST['perform'] ) && ! empty( $_POST['current_page'] ) ) {
					$table        = sanitize_text_field( wp_unslash( $_POST['table'] ) );
					$perform      = sanitize_text_field( wp_unslash( $_POST['perform'] ) );
					$current_page = sanitize_text_field( wp_unslash( $_POST['current_page'] ) );
					$user_id      = get_current_user_id();
					$per_page     = 10;
					$offset       = 1 === $current_page ? 0 : ( $current_page - 1 ) * $per_page;
					$html         = '';

					if ( 'commissions' === $table ) {
						$commission_helper = new DDWCAF_Commission_Helper( $this->ddwcaf_configuration );
						$args              = [ 'user_id' => $user_id, 'per_page' => $per_page, 'offset' => $offset ];
						$commissions       = $commission_helper->ddwcaf_get_commissions( $args );
						ob_start();
						$commission_helper->ddwcaf_render_commissions_table_rows( $commissions );
						$html = ob_get_clean();
					} elseif ( 'top_products' === $table ) {
						$commission_helper = new DDWCAF_Commission_Helper( $this->ddwcaf_configuration );
						$args              = [
							'affiliate_id' => $user_id,
							'per_page'     => $per_page,
							'offset'       => $offset,
							'order'        => 'desc',
							'orderby'      => 'quantity',
						];
						$top_products = $commission_helper->ddwcaf_get_top_products( $args );
						ob_start();
						$commission_helper->ddwcaf_render_top_products_table_rows( $top_products );
						$html = ob_get_clean();
					} elseif ( 'visits' === $table ) {
						$visit_helper = new DDWCAF_Visit_Helper( $this->ddwcaf_configuration );
						$args         = [ 'affiliate_id' => $user_id, 'per_page' => $per_page, 'offset' => $offset ];
						$visits       = $visit_helper->ddwcaf_get_visits( $args );
						ob_start();
						$visit_helper->ddwcaf_render_visits_table_rows( $visits );
						$html = ob_get_clean();
					}

					$response = [
						'success' => true,
						'message' => esc_html__( 'Table rows fetched!', 'affiliates-for-woocommerce' ),
						'html'    => $html,
					];
				} else {
					$response = [
						'success' => false,
						'message' => esc_html__( 'Arguments are missing.', 'affiliates-for-woocommerce' ),
					];
				}
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
