<?php
/**
 * File for handling AJAX requests in the DevDiggers Framework.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Ajax' ) ) {
	/**
	 * Class for handling AJAX requests in the DevDiggers Framework.
	 */
	class DDFW_Ajax {
		/**
		 * The single instance of the class.
		 *
		 * @var DDFW_Ajax
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @return DDFW_Ajax
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor to initialize hooks.
		 */
		public function __construct() {
			add_action( 'wp_ajax_ddfw_verify_license', [ $this, 'ddfw_verify_license' ] );
			add_action( 'wp_ajax_ddfw_license_heartbeat_check', [ $this, 'ddfw_license_heartbeat_check' ] );
			add_action( 'wp_ajax_ddfw_get_products_list', [ $this, 'ddfw_get_products_list' ] );
			add_action( 'wp_ajax_ddfw_get_categories_list', [ $this, 'ddfw_get_categories_list' ] );
			add_action( 'wp_ajax_ddfw_get_users_list', [ $this, 'ddfw_get_users_list' ] );
			add_action( 'wp_ajax_ddfw_refresh_plugins_cache', [ $this, 'ddfw_refresh_plugins_cache' ] );
			add_action( 'wp_ajax_ddfw_newsletter_subscribe', [ $this, 'ddfw_newsletter_subscribe' ] );
		}

		/**
		 * Verify License function
		 *
		 * @return void
		 */
		public function ddfw_verify_license() {
			if ( check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {
				$purchase_code  = ! empty( $_POST[ 'purchase_code' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'purchase_code' ] ) ) : '';
				$purchase_email = ! empty( $_POST[ 'purchase_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'purchase_email' ] ) ) : '';
				$status         = ! empty( $_POST[ 'status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'status' ] ) ) : '';
				$prefix         = ! empty( $_POST[ 'prefix' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'prefix' ] ) ) : '';
				$product_id     = ! empty( $_POST[ 'product_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'product_id' ] ) ) : '';

				$args = [
					'purchase_code'     => $purchase_code,
					'purchase_email'    => $purchase_email,
					'action'            => $status,
					'plugin_prefix'     => $prefix,
					'plugin_product_id' => $product_id,
					'user_agent'        => site_url(),
					'multisite'         => is_multisite(),
					'network_site_url'  => network_site_url(),
				];

				$response = wp_remote_post( 'https://devdiggers.com/wp-json/ddelv/v1/verify-purchase', [
					'body' => json_encode( $args )
				] );

				if ( is_wp_error( $response ) ) {
					wp_send_json_error( 'API request failed: ' . $response->get_error_message() );
				}

				$response = json_decode( wp_remote_retrieve_body( $response ) );

				if ( $response->success ) {
					update_option( "_{$prefix}_purchase_code", $purchase_code );
					update_option( "_{$prefix}_purchase_email", $purchase_email );

					if ( 'activate' === $status ) {
						// Store a hashed activation token instead of plain 'yes'.
						$token = ddfw_generate_activation_token( $purchase_code, $prefix );
						update_option( "_{$prefix}_license_activated", $token );
					} else {
						delete_option( "_{$prefix}_license_activated" );
					}
				}

				wp_send_json( $response );
			} else {
				wp_send_json_error( esc_html__( 'Security check failed!', 'devdiggers-framework' ) );
			}
		}

		/**
		 * License Heartbeat Check via AJAX.
		 * Routes the license check through WP-AJAX so the JS
		 * doesn't need to call devdiggers.com directly.
		 * This defeats the JavaScript fetch monkey-patching attack.
		 *
		 * @return void
		 */
		public function ddfw_license_heartbeat_check() {
			if ( ! check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {
				wp_send_json_error( esc_html__( 'Security check failed!', 'devdiggers-framework' ) );
			}

			$purchase_code = ! empty( $_POST['purchase_code'] ) ? sanitize_text_field( wp_unslash( $_POST['purchase_code'] ) ) : '';
			$prefix        = ! empty( $_POST['prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['prefix'] ) ) : '';

			if ( empty( $purchase_code ) || empty( $prefix ) ) {
				wp_send_json_error( 'Missing parameters.' );
			}

			// Use direct cURL call to bypass pre_http_request filters.
			$response = ddfw_direct_license_check( $purchase_code, site_url(), $prefix );

			if ( false === $response ) {
				// Network issue - don't deactivate, just report error.
				wp_send_json_error( 'Unable to verify license. Please try again.' );
			}

			// Verify the HMAC signature to ensure authenticity.
			if ( ! ddfw_verify_license_signature( $response ) ) {
				// Forged response detected - deactivate.
				delete_option( "_{$prefix}_license_activated" );
				wp_send_json( [
					'success'  => true,
					'status'   => 'deactivated',
					'verified' => false,
				] );
			}

			// If license is deactivated/deleted/expired, clean up local options.
			if ( ! empty( $response['status'] ) && in_array( $response['status'], [ 'deactivated', 'deleted', 'expired' ], true ) ) {
				delete_option( "_{$prefix}_license_activated" );
			}

			wp_send_json( [
				'success'  => ! empty( $response['success'] ),
				'status'   => $response['status'] ?? 'unknown',
				'verified' => true,
			] );
		}

		/**
		 * Get products list for AJAX requests.
		 * 
		 * @return void
		 */
		public function ddfw_get_products_list() {
			$response = [];
			if ( check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {

				$search_results = new \WP_Query( [
					's'                   => ! empty( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '',
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
					'message' => esc_html__( 'Security check failed!', 'devdiggers-framework' ),
				];
			}
			wp_send_json( $response );
		}

		/**
		 * Get categories list for AJAX requests.
		 * 
		 * @return void
		 */
		public function ddfw_get_categories_list() {
			$response = [];
			if ( check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {
				$query = isset( $_POST[ 'query' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'query' ] ) ) : ''; // wpcs: input var okay.

				$categories = get_terms( [
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'search'     => esc_attr( $query ),
				] );

				$response = [
					'success'    => true,
					'message'    => '',
					'categories' => $categories,
				];
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'devdiggers-framework' ),
				];
			}
			wp_send_json( $response );
		}

		/**
		 * Get users list for AJAX requests.
		 *
		 * @return void
		 */
		public function ddfw_get_users_list() {
			$response = [];
			if ( check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {
				$query = isset( $_POST[ 'query' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'query' ] ) ) : ''; // wpcs: input var okay.
				$role  = isset( $_POST[ 'role' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'role' ] ) ) : ''; // wpcs: input var okay.

				$query = new \WP_User_Query( [
					'role'           => $role,
					'search'         => '*' . esc_attr( $query ) . '*',
					'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
					'fields'         => [ 'user_email', 'user_login', 'ID' ],
				] );

				$response = [
					'error'   => false,
					'message' => '',
					'users'   => $query->get_results(),
				];
			} else {
				$response = [
					'error'   => true,
					'message' => esc_html__( 'Security check failed!', 'devdiggers-framework' ),
				];
			}
			wp_send_json( $response );
		}

		/**
		 * Refresh plugins cache
		 *
		 * @return void
		 */
		public function ddfw_refresh_plugins_cache() {
			if ( check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					$plugins_api = DDFW_Plugins_API::instance();
					$result = $plugins_api->refresh_plugins_cache();
					
					wp_send_json_success( $result );
				} else {
					wp_send_json_error( [
						'message' => esc_html__( 'Insufficient permissions', 'devdiggers-framework' ),
					] );
				}
			} else {
				wp_send_json_error( [
					'message' => esc_html__( 'Security check failed!', 'devdiggers-framework' ),
				] );
			}
		}

		/**
		 * Handle newsletter subscription
		 *
		 * @return void
		 */
		public function ddfw_newsletter_subscribe() {
			if ( ! check_ajax_referer( 'ddfw-nonce', 'nonce', false ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'devdiggers-framework' ) ] );
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

			if ( ! is_email( $email ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Please enter a valid email address.', 'devdiggers-framework' ) ] );
			}

			$user_id = get_current_user_id();
			if ( ! $user_id ) {
				wp_send_json_error( [ 'message' => esc_html__( 'You must be logged in to subscribe.', 'devdiggers-framework' ) ] );
			}

			// Check if already subscribed
			$already_subscribed = get_option( 'ddfw_newsletter_subscribed' );
			if ( $already_subscribed ) {
				wp_send_json_error( [ 'message' => esc_html__( 'You are already subscribed to our newsletter.', 'devdiggers-framework' ) ] );
			}

			// Prepare data for FluentCRM webhook
			$contact_data = [
				'email'  => $email,
				'tags'   => [ 'newsletter' ],
				'source' => site_url(),
			];

			// Send to FluentCRM webhook
			$response = wp_remote_post( 'https://devdiggers.com/?fluentcrm=1&route=contact&hash=28b2644f-9c13-4518-bd76-3666456d20b3', [
				'method'  => 'POST',
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode( $contact_data ),
				'timeout' => 15,
			] );

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Failed to connect to newsletter service. Please try again.', 'devdiggers-framework' ) ] );
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( $data && isset( $data['success'] ) && $data['success'] ) {
				// Mark as subscribed
				update_option( 'ddfw_newsletter_subscribed', true );
				update_option( 'ddfw_newsletter_email', $email );

				wp_send_json_success( [ 'message' => esc_html__( 'Thank you for subscribing!', 'devdiggers-framework' ) ] );
			} else {
				$error_message = $data['message'] ?? esc_html__( 'Subscription failed. Please try again.', 'devdiggers-framework' );
				wp_send_json_error( [ 'message' => $error_message ] );
			}
		}
	}
}

DDFW_Ajax::instance();
