<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all front end action callbacks.
 */

namespace DDWCAffiliates\Includes\Front;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Templates\Front\MyAccount\DDWCAF_Products_List_Template;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Front_Functions' ) ) {    
    /**
     * Front functions class
     */
    class DDWCAF_Front_Functions {
        /**
         * Configuration Variable
         *
         * @var array
         */
        protected $ddwcaf_configuration;

		/**
         * Product Helper Variable
         *
         * @var object
         */
        protected $affiliate_helper;

        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            $this->ddwcaf_configuration = $ddwcaf_configuration;
            $this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $this->ddwcaf_configuration );
        }

		/**
		 * Add woocommerce menu function
		 *
		 * @param array $items
		 * @return array
		 */
		public function ddwcaf_add_woocommerce_menu( $items ) {
			$customer_logout_endpoint = get_option( 'woocommerce_logout_endpoint' );

			// Remove the logout menu item.
			$logout = $items[ $customer_logout_endpoint ];
			unset( $items[ $customer_logout_endpoint ] );

			// Insert your custom endpoint.
			$items[ $this->ddwcaf_configuration[ 'my_account_endpoint' ] ] = $this->ddwcaf_configuration[ 'my_account_endpoint_title' ];

			// Insert back the logout item.
			$items[ $customer_logout_endpoint ] = $logout;
			return $items;
		}

		/**
		 * Add Query Vars function
		 *
		 * @param array $vars
		 * @return array
		 */
		public function ddwcaf_add_query_vars( $vars ) {
			$vars[]    = $this->ddwcaf_configuration[ 'my_account_endpoint' ];
			$endpoints = $this->affiliate_helper->ddwcaf_get_dashboard_endpoints();

			foreach ( $endpoints as $key => $endpoint ) {
				$vars[] = $endpoint[ 'endpoint' ];
			}

            return $vars;
		}

		/**
		 * Add my account endpoint content function
		 *
		 * @return void
		 */
		public function ddwcaf_add_my_account_endpoint_content() {
			echo do_shortcode( $this->ddwcaf_configuration[ 'affiliate_dashboard_shortcode' ] );
		}

		/**
		 * Change Endpoint Title function
		 *
		 * @param string $title
		 * @return string
		 */
		public function ddwcaf_change_endpoint_title( $title ) {
            global $wp_query;

			// New page title.
            if ( is_main_query() && in_the_loop() && is_account_page() ) {
				if ( isset( $wp_query->query_vars[ $this->ddwcaf_configuration[ 'my_account_endpoint' ] ] ) ) {
					$title = $this->ddwcaf_configuration[ 'my_account_endpoint_title' ];
				}

				$endpoints = $this->affiliate_helper->ddwcaf_get_dashboard_endpoints();

				foreach ( $endpoints as $key => $endpoint ) {
					if ( isset( $wp_query->query_vars[ $this->ddwcaf_configuration[ 'my_account_endpoint' ] ] ) && $endpoint[ 'endpoint' ] === $wp_query->query_vars[ $this->ddwcaf_configuration[ 'my_account_endpoint' ] ] ) {
						$title = $endpoint[ 'title' ];
					}
				}
            }

            return $title;
		}

		/**
		 * Remove Sidebar from custom menu page function
		 *
		 * @param array $sidebars_widgets
		 * @return array
		 */
		public function ddwcaf_remove_sidebar_from_custom_menu_page( $sidebars_widgets ) {
            global $wp_query;

			if ( isset( $wp_query->query_vars[ $this->ddwcaf_configuration[ 'my_account_endpoint' ] ] ) && empty( $this->ddwcaf_configuration[ 'enable_widgets_my_account_endpoint' ] ) && is_account_page() ) {
				return [ false ];
			}

			if ( ! empty( $this->ddwcaf_configuration[ 'affiliate_dashboard_page_id' ] ) && is_page( $this->ddwcaf_configuration[ 'affiliate_dashboard_page_id' ] ) && empty( $this->ddwcaf_configuration[ 'enable_widgets_affiliate_dashboard_page' ] ) ) {
				return [ false ];
			}

			return $sidebars_widgets;
		}

		/**
		 * Add affiliate registration fields function
		 *
		 * @return void
		 */
		public function ddwcaf_add_affiliate_registration_fields() {
			if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) {
				wp_enqueue_script( 'wc-password-strength-meter' );
			}
			?>
			<input type="hidden" name="_ddwcaf_user_role" value="ddwcaf_affiliate" />
			<?php
            $this->affiliate_helper->ddwcaf_display_affiliate_registration_fields();
		}

		/**
		 * Get affiliate registration form shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcaf_get_affiliate_registration_form_shortcode_content( $product_id = '' ) {
			if ( ! is_user_logged_in() ) {
				ob_start();
				$this->ddwcaf_enqueue_front_scripts();
				require DDWCAF_PLUGIN_FILE . 'templates/shortcodes/affiliate-registration-form.php';
				return ob_get_clean();
			} else {
				return do_shortcode( $this->ddwcaf_configuration[ 'affiliate_dashboard_shortcode' ] );
			}
		}

		/**
		 * Get affiliate dashboard shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcaf_get_affiliate_dashboard_shortcode_shortcode_content() {
			if ( is_user_logged_in() ) {
				ob_start();
				$this->ddwcaf_enqueue_front_scripts();
				require DDWCAF_PLUGIN_FILE . 'templates/shortcodes/affiliate-dashboard.php';
				return ob_get_clean();
			} else {
				return do_shortcode( $this->ddwcaf_configuration[ 'affiliate_registration_form_shortcode' ] );
			}
		}

		/**
		 * Front scripts enqueue function
		 *
		 * @return void
		 */
		public function ddwcaf_front_scripts() {
			wp_register_style( 'ddwcaf-front-style', DDWCAF_PLUGIN_URL . 'assets/css/front.css', [], DDWCAF_SCRIPT_VERSION );
			wp_register_script( 'ddwcaf-front-script', DDWCAF_PLUGIN_URL . 'assets/js/front.js', [], DDWCAF_SCRIPT_VERSION );

			wp_localize_script(
				'ddwcaf-front-script',
				'ddwcafFrontObject',
				[
					'ajax' => [
						'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
						'ajaxNonce' => wp_create_nonce( 'ddwcaf-nonce' ),
					],
					'i18n' => [
						'copied' => esc_html__( 'Copied', 'affiliates-for-woocommerce' ),
					],
				]
			);

			?>
			<style>
				:root {
					--ddwcaf-primary-color: <?php echo esc_attr( $this->ddwcaf_configuration[ 'primary_color' ] ); ?>;
				}
			</style>
			<?php
		}

		/**
		 * Enqueue front scripts function
		 *
		 * @return void
		 */
		public function ddwcaf_enqueue_front_scripts() {
			wp_enqueue_style( 'ddwcaf-front-style' );
			wp_enqueue_script( 'ddwcaf-front-script' );
		}

		/**
		 * WooCommerce registration errors function
		 *
		 * @param object $errors
		 * @return object
		 */
		public function ddwcaf_woocommerce_registration_errors( $errors ) {
			if ( ! empty( $_POST[ '_ddwcaf_user_role' ] ) && 'ddwcaf_affiliate' === sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_user_role' ] ) ) ) {
				$affiliate_registration_fields = $this->affiliate_helper->ddwcaf_get_affiliate_registration_fields();

				usort( $affiliate_registration_fields, function( $first, $second ) {
					return strnatcmp( $first[ 'position' ], $second[ 'position' ] );
				} );

				foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
					if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] && ! empty( $affiliate_registration_field[ 'required' ] ) && ( ! isset( $_POST[ $affiliate_registration_field[ 'name' ] ] ) || '' === sanitize_text_field( wp_unslash( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ) ) ) {
						$errors->add( 'ddwcaf_error_' . $affiliate_registration_field[ 'name' ], sprintf( esc_html__( '%s is required!', 'affiliates-for-woocommerce' ), $affiliate_registration_field[ 'label' ] ) );
					}
				}
			}

			return $errors;
		}

		/**
		 * WooCommerce new customer data function
		 *
		 * @param array $customer_data
		 * @return array
		 */
		public function ddwcaf_woocommerce_new_customer_data( $customer_data ) {
			if ( ! empty( $_POST[ '_ddwcaf_user_role' ] ) && 'ddwcaf_affiliate' === sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_user_role' ] ) ) ) {
				$customer_data[ 'role' ] = 'ddwcaf_affiliate';
			}

			return $customer_data;
		}

		/**
		 * WooCommerce created customer function
		 *
		 * @param int $customer_id
		 * @return void
		 */
		public function ddwcaf_woocommerce_created_customer( $customer_id ) {
			if ( ! empty( $_POST[ '_ddwcaf_user_role' ] ) && 'ddwcaf_affiliate' === sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_user_role' ] ) ) ) {
				$this->affiliate_helper->ddwcaf_prepare_and_save_affiliate_info( $customer_id );
			}
		}

		/**
		 * WooCommerce registration redirect function
		 *
		 * @param string $redirect
		 * @return string
		 */
		public function ddwcaf_woocommerce_registration_redirect( $redirect ) {
			if ( ! empty( $_POST[ '_ddwcaf_user_role' ] ) && 'ddwcaf_affiliate' === sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_user_role' ] ) ) ) {
				$redirect = $this->affiliate_helper->ddwcaf_get_affiliate_dashboard_url();
			}

			return $redirect;
		}

		/**
		 * WooCommerce login redirect function
		 *
		 * @param string $redirect
		 * @param object $user
		 * @return string
		 */
		public function ddwcaf_woocommerce_login_redirect( $redirect, $user ) {
			if ( $this->affiliate_helper->ddwcaf_is_user_affiliate( $user->ID ) ) {
				$redirect = $this->affiliate_helper->ddwcaf_get_affiliate_dashboard_url();
			}

			return $redirect;
		}

		/**
         * Handle WP loaded function
         *
         * @return void
         */
        public function ddwcaf_handle_wp_loaded() {
			if ( ! empty( $_POST[ 'ddwcaf_nonce' ] ) && wp_verify_nonce( $_POST[ 'ddwcaf_nonce' ], 'ddwcaf_nonce_action' ) ) {
				if ( ! empty( $_POST[ 'ddwcaf_affiliate_info_submit' ] ) ) {
					$affiliate_registration_fields = $this->affiliate_helper->ddwcaf_get_affiliate_registration_fields();

					usort( $affiliate_registration_fields, function( $first, $second ) {
						return strnatcmp( $first[ 'position' ], $second[ 'position' ] );
					} );

					foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
						if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] && ! empty( $affiliate_registration_field[ 'required' ] ) && ( ! isset( $_POST[ $affiliate_registration_field[ 'name' ] ] ) || '' === sanitize_text_field( wp_unslash( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ) ) ) {
							wc_add_notice( sprintf( esc_html__( '%s is required!', 'affiliates-for-woocommerce' ), $affiliate_registration_field[ 'label' ] ), 'error' );
							return;
						}
					}

					$user = wp_get_current_user();

					$user->remove_role( 'customer' );
					$user->add_role( 'ddwcaf_affiliate' );

					$this->affiliate_helper->ddwcaf_prepare_and_save_affiliate_info( $user->ID );

					wp_safe_redirect( $this->affiliate_helper->ddwcaf_get_affiliate_dashboard_url() );
					exit();
				}

				if ( ! empty( $_POST[ 'ddwcaf_affiliate_settings_submit' ] ) ) {
					$user    = wp_get_current_user();
					$user_id = $user->ID;

					$this->affiliate_helper->ddwcaf_update_affiliate_withdrawal_methods( $user_id, $_POST[ '_ddwcaf_withdrawal_methods' ] );
					$this->affiliate_helper->ddwcaf_update_affiliate_default_withdrawal_method( $user_id, $_POST[ '_ddwcaf_default_withdrawal_method' ] );

					wc_add_notice( esc_html__( 'Settings saved successfully.', 'affiliates-for-woocommerce' ), 'success' );
					wp_safe_redirect( $_SERVER[ 'HTTP_REFERER' ] );

					exit();
				}
            }
        }

        /**
         * Handle WP function
         *
         * @return void
         */
        public function ddwcaf_handle_wp() {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();

				if ( in_array( 'administrator', $user->roles, true ) ) {
					return;
				}
			}

			$token = ! empty( $_GET[ $this->ddwcaf_configuration[ 'query_variable_name' ] ] ) ? sanitize_text_field( wp_unslash( $_GET[ $this->ddwcaf_configuration[ 'query_variable_name' ] ] ) ) : '';

			if ( ! empty( $token ) ) {
				$this->ddwcaf_set_token_in_cookie( $token );

				if ( ! empty( $this->ddwcaf_configuration[ 'register_visits_enabled' ] ) ) {
					global $wp;

					$visit_helper = new DDWCAF_Visit_Helper( $this->ddwcaf_configuration );
					$url          = add_query_arg( $_GET, home_url( $wp->request ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$ip_address   = isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REMOTE_ADDR' ] ) ) : false;
					$affiliate_id = $this->affiliate_helper->ddwcaf_get_affiliate_id_by_token( $token );

					if ( ! $affiliate_id ) {
						return;
					}

					if ( ! empty( $user ) && $affiliate_id == $user->ID ) {
						return;
					}

					$visit_exists = $visit_helper->ddwcaf_check_visit_exists( [
						'affiliate_id' => $affiliate_id,
						'url'          => $url,
						'ip'           => $ip_address,
					] );

					if ( $visit_exists ) {
						return;
					}

					$data = [
						'affiliate_id'    => $affiliate_id,
						'url'             => $url,
						'referrer_url'    => isset( $_SERVER[ 'HTTP_REFERER' ] ) ? esc_url( sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_REFERER' ] ) ) ) : false,
						'ip'              => $ip_address,
						'order_id'        => NULL,
						'date'            => current_time( 'Y-m-d H:i:s' ),
						'conversion_date' => NULL,
					];

					$visit_helper->ddwcaf_save_visit( $data );
				}
			}
        }

        /**
		 * Set token in cookie function
         * 
		 * @param string $token
		 * @return void
		 */
		public function ddwcaf_set_token_in_cookie( $token ) {
			$cookie_token = ! empty( $_COOKIE[ $this->ddwcaf_configuration[ 'referral_cookie_name' ] ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $this->ddwcaf_configuration[ 'referral_cookie_name' ] ] ) ) : '';

			if ( $cookie_token === $token ) {
				return;
			}

			if ( empty( $this->ddwcaf_configuration[ 'referral_cookie_change_allowed' ] ) && ! empty( $cookie_token ) ) {
				return;
			}

			$affiliate_id = $this->affiliate_helper->ddwcaf_get_affiliate_id_by_token( $token );

			if ( ! $affiliate_id ) {
				return;
			}

			if ( ! empty( $this->ddwcaf_configuration[ 'referral_cookie_expires' ] ) ) {
				$cookie_duration = DAY_IN_SECONDS * intval( $this->ddwcaf_configuration[ 'referral_cookie_expires' ] );
			} else {
				$cookie_duration = 15 * YEAR_IN_SECONDS;
			}

			return setcookie( $this->ddwcaf_configuration[ 'referral_cookie_name' ], $token, time() + (int) $cookie_duration, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}

		/**
		 * Handle Checkout Order Processed function
		 *
		 * @param int $order_id
		 * @param array $posted_data
		 * @param object $order
		 * @return void
		 */
		public function ddwcaf_handle_checkout_order_processed( $order_id, $posted_data, $order ) {
			$cookie_token = ! empty( $_COOKIE[ $this->ddwcaf_configuration[ 'referral_cookie_name' ] ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $this->ddwcaf_configuration[ 'referral_cookie_name' ] ] ) ) : '';
			$affiliate_id = $this->affiliate_helper->ddwcaf_get_affiliate_id_by_token( $cookie_token );

			// if no affiliate is set, return.
			if ( ! $affiliate_id ) {
				return;
			}

			$affiliate_data = get_userdata( $affiliate_id );

			if ( ( $order->get_customer_id() == $affiliate_id || $order->get_customer_id() === $affiliate_data->user_email ) ) {
				return;
			}

			$commission_helper = new DDWCAF_Commission_Helper( $this->ddwcaf_configuration );

			if ( $commission_helper->ddwcaf_save_commission_for_order( $order, $affiliate_id ) ) {
				$visit_helper = new DDWCAF_Visit_Helper( $this->ddwcaf_configuration );

				// saves current token into order metadata.
				$order->update_meta_data( '_ddwcaf_referral_token', $cookie_token );

				// Save visit in order.
				$order->update_meta_data( '_ddwcaf_visit_id', $visit_helper->ddwcaf_get_recent_visit( [
					'affiliate_id' => $affiliate_id,
					'ip'           => isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REMOTE_ADDR' ] ) ) : false,
				] ) );

				$order->save();
			}
		}
    }
}
