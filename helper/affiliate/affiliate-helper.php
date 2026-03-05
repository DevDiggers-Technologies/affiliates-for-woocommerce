<?php
/**
 * Affiliate helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Affiliate;

use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DevDiggers\Framework\Includes\DDFW_Form_Field;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Affiliate_Helper' ) ) {
	/**
	 * Affiliate helper class
	 */
	class DDWCAF_Affiliate_Helper {
		/**
		 * Database object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcaf_configuration;

		/**
		 * Construct
		 */
		public function __construct( $ddwcaf_configuration ) {
			global $wpdb;
			$this->wpdb                 = $wpdb;
			$this->ddwcaf_configuration = $ddwcaf_configuration;
		}

		/**
		 * Get affiliate status function
		 *
		 * @param int $user_id
		 * @return string
		 */
		public function ddwcaf_get_affiliate_status( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$affiliate_status = get_user_meta( $user_id, '_ddwcaf_affiliate_status', true );
			return ! empty( $affiliate_status ) ? $affiliate_status : $this->ddwcaf_configuration[ 'default_affiliate_status' ];
		}

		/**
		 * Display affiliate registration fields function
		 *
		 * @param int|string $affiliate_id
		 * @param boolean $editable
		 * @return void
		 */
		public function ddwcaf_display_affiliate_registration_fields( $affiliate_id = '', $editable = false ) {
			if ( empty( $affiliate_id ) ) {
				$affiliate_data = [];
			} else {
				$affiliate_data = $this->ddwcaf_get_affiliate_info_data( $affiliate_id );
			}

			$affiliate_registration_fields = $this->ddwcaf_get_affiliate_registration_fields();

			usort( $affiliate_registration_fields, function( $first, $second ) {
				return strnatcmp( $first[ 'position' ], $second[ 'position' ] );
			} );

			$flag = false;

			foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
				if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] ) {
					if ( 'country' === $affiliate_registration_field[ 'type' ] || 'state' === $affiliate_registration_field[ 'type' ] ) {
						$flag = true;
					}

					$args = [
						'type'           => $affiliate_registration_field[ 'type' ],
						'label'          => 'checkbox' !== $affiliate_registration_field[ 'type' ] ? $affiliate_registration_field[ 'label' ] : '',
						'checkbox_label' => 'checkbox' === $affiliate_registration_field[ 'type' ] ? $affiliate_registration_field[ 'label' ] : '',
						'id'             => $affiliate_registration_field[ 'name' ],
						'name'           => $affiliate_registration_field[ 'name' ],
						'required'       => $affiliate_registration_field[ 'required' ],
						'options'        => explode( '|', str_replace( ' | ', '|', $affiliate_registration_field[ 'options' ] ) ),
						'description'    => $affiliate_registration_field[ 'description' ],
						'css_classes'    => explode( '|', str_replace( ' | ', '|', $affiliate_registration_field[ 'css_classes' ] ) ),
						'placeholder'    => $affiliate_registration_field[ 'placeholder' ],
					];

					// if ( is_admin() ) {
					// 	$args[ 'show_frontend_fields' ] = true;
					// }

					if ( 'checkbox' === $affiliate_registration_field[ 'type' ] ) {
						$args[ 'td_colspan' ] = 2;
					}

					if ( $editable ) {
						$args[ 'custom_attributes' ] = [
							'disabled' => empty( $affiliate_registration_field[ 'editable' ] ),
						];
					}

					if ( isset( $affiliate_data[ $affiliate_registration_field[ 'name' ] ] ) ) {
						$args[ 'value' ] = $affiliate_data[ $affiliate_registration_field[ 'name' ] ];
					} elseif ( isset( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ) {
						$args[ 'value' ] = sanitize_text_field( wp_unslash( $_POST[ $affiliate_registration_field[ 'name' ] ] ) );
					}

					DDFW_Form_Field::display_form_field( $args );
				}
			}

			if ( $flag ) {
				wp_enqueue_style( 'select2' );
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_script( 'wc-country-select' );
			}
		}

		/**
		 * Prepare and save affiliate info function
		 *
		 * @param int $user_id
		 * @param array $affiliate_registration_fields
		 * @return void
		 */
		public function ddwcaf_prepare_and_save_affiliate_info( $user_id, $affiliate_registration_fields = [] ) {
			if ( empty( $affiliate_registration_fields ) ) {
				$affiliate_registration_fields = $this->ddwcaf_get_affiliate_registration_fields();
			}

			$data = [];

			foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
				if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] ) {
					$data[ $affiliate_registration_field[ 'name' ] ] = isset( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ) : '';
				}
			}

			$this->ddwcaf_update_affiliate_info_data( $user_id, $data );

			if ( empty( get_user_meta( $user_id, '_ddwcaf_affiliate_status', true ) ) ) {
				$this->ddwcaf_update_affiliate_status( $user_id, $this->ddwcaf_configuration[ 'default_affiliate_status' ] );
			}
		}

		/**
		 * Update affiliate status function
		 *
		 * @param int $user_id
		 * @param string $affiliate_status
		 * @return void
		 */
		public function ddwcaf_update_affiliate_status( $user_id, $affiliate_status ) {
			update_user_meta( $user_id, '_ddwcaf_affiliate_status', $affiliate_status );
		}

		/**
		 * Get affiliate info data function
		 *
		 * @param int $user_id
		 * @return array
		 */
		public function ddwcaf_get_affiliate_info_data( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$data = get_user_meta( $user_id, '_ddwcaf_affiliate_info_data', true );
			return ! empty( $data ) ? $data : [];
		}

		/**
		 * Update affiliate info data function
		 *
		 * @param int $user_id
		 * @param array $data
		 * @return void
		 */
		public function ddwcaf_update_affiliate_info_data( $user_id, $data ) {
			update_user_meta( $user_id, '_ddwcaf_affiliate_info_data', $data );
		}

		/**
		 * Get affiliate withdrawal methods function
		 *
		 * @param int $user_id
		 * @return array
		 */
		public function ddwcaf_get_affiliate_withdrawal_methods( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$data = get_user_meta( $user_id, '_ddwcaf_affiliate_withdrawal_methods', true );

			return ! empty( $data ) ? $data : [
				'bacs'         => [],
				'paypal_email' => ''
			];
		}

		/**
		 * Get withdrawal method name function
		 *
		 * @param string $slug
		 * @return string
		 */
		public function ddwcaf_get_withdrawal_method_name( $slug ) {
			$withdrawal_method_names = [
				'bacs'          => esc_html__( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ),
				'paypal_email'  => esc_html__( 'PayPal', 'affiliates-for-woocommerce' ),
				'ddwcwm_wallet' => esc_html__( 'WooCommerce Wallet Management [Pro]', 'affiliates-for-woocommerce' ),
			];

			$withdrawal_method_names = apply_filters( 'ddwcaf_modify_withdrawal_method_names', $withdrawal_method_names, $slug );

			return ! empty( $withdrawal_method_names[ $slug ] ) ? $withdrawal_method_names[ $slug ] : $slug;
		}

		/**
		 * Get affiliate default withdrawal method function
		 *
		 * @param int $user_id
		 * @return string
		 */
		public function ddwcaf_get_affiliate_default_withdrawal_method( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$data = get_user_meta( $user_id, '_ddwcaf_affiliate_default_withdrawal_method', true );

			return ! empty( $data ) ? $data : 'bacs';
		}

		/**
		 * Update affiliate withdrawal methods function
		 *
		 * @param int $user_id
		 * @param array $data
		 * @return void
		 */
		public function ddwcaf_update_affiliate_withdrawal_methods( $user_id, $data ) {
			update_user_meta( $user_id, '_ddwcaf_affiliate_withdrawal_methods', $data );
		}

		/**
		 * Update affiliate default withdrawal method function
		 *
		 * @param int $user_id
		 * @param array $data
		 * @return void
		 */
		public function ddwcaf_update_affiliate_default_withdrawal_method( $user_id, $data ) {
			update_user_meta( $user_id, '_ddwcaf_affiliate_default_withdrawal_method', $data );
		}

		/**
		 * Is user affiliate function
		 *
		 * @param int $user_id
		 * @return boolean
		 */
		public function ddwcaf_is_user_affiliate( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user = wp_get_current_user();
			} else {
				$user = get_userdata( $user_id );
			}

			return in_array( 'ddwcaf_affiliate', $user->roles, true );
		}

		/**
		 * Is user allowed for affiliate function
		 *
		 * @param int $user_id
		 * @return boolean
		 */
		public function ddwcaf_is_user_allowed_for_affiliate( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user = wp_get_current_user();
			} else {
				$user = get_userdata( $user_id );
			}

			$flag = false;

			foreach ( $user->roles as $key => $role ) {
				if ( in_array( $role, $this->ddwcaf_configuration[ 'user_roles' ] ) ) {
					$flag = true;
					break;
				}
			}

			return $flag;
		}

		/**
		 * Get affiliate dasboard url function
		 *
		 * @param string $endpoint Optional endpoint of the page.
		 * @param string $value    Optional value to pass to the endpoint.
		 *
		 * @return string Dashboard url, or home url if no dashboard page is set
		 */
		public function ddwcaf_get_affiliate_dashboard_url( $endpoint = '', $value = '' ) {
			if ( is_account_page() ) {
				$permalink = wc_get_page_permalink( 'myaccount' ) . $this->ddwcaf_configuration[ 'my_account_endpoint' ];
			} elseif ( ! empty( $this->ddwcaf_configuration[ 'affiliate_dashboard_page_id' ] ) ) {
				if ( 'custom_page' === $this->ddwcaf_configuration[ 'default_affiliate_dashboard_page' ] ) {
					$permalink = get_permalink( $this->ddwcaf_configuration[ 'affiliate_dashboard_page_id' ] );
				} elseif ( 'my_account_page' === $this->ddwcaf_configuration[ 'default_affiliate_dashboard_page' ] ) {
					$permalink = wc_get_page_permalink( 'myaccount' ) . $this->ddwcaf_configuration[ 'my_account_endpoint' ];
				}
			}

			$rewrite = wc_sanitize_endpoint_slug( apply_filters( 'ddwcaf_endpoint_rewrite', $endpoint ) );

			if ( apply_filters( 'ddwcaf_use_dashboard_pretty_permalinks', get_option( 'permalink_structure' ) ) ) {
				$url = wc_get_endpoint_url( $rewrite, $value, $permalink );
			} else {
				$url = add_query_arg( $endpoint, $value, $permalink );
			}

			return apply_filters( 'ddwcaf_modify_affiliate_dashboard_url', $url, $endpoint, $value, $permalink );
		}

		/**
		 * Get affiliate registration fields function
		 *
		 * @return array
		 */
		public function ddwcaf_get_affiliate_registration_fields() {
			$affiliate_registration_fields = get_option( '_ddwcaf_affiliate_registration_fields' );

			if ( empty( $affiliate_registration_fields ) ) {
				$affiliate_registration_fields = [
					[
						'modify'       => false,
						'label'       => esc_html__( 'Username', 'affiliates-for-woocommerce' ),
						'type'        => 'text',
						'name'        => 'username',
						'position'    => 1,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => true,
						'editable'    => false,
						'status'      => 'no' === get_option( 'woocommerce_registration_generate_username' ) ? 'active' : 'inactive',
					],
					[
						'modify'       => false,
						'label'       => esc_html__( 'Email address', 'affiliates-for-woocommerce' ),
						'type'        => 'email',
						'name'        => 'email',
						'position'    => 2,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => true,
						'editable'    => false,
						'status'      => 'active',
					],
					[
						'modify'       => false,
						'label'       => esc_html__( 'Password', 'affiliates-for-woocommerce' ),
						'type'        => 'password',
						'name'        => 'password',
						'position'    => 3,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => true,
						'editable'    => false,
						'status'      => 'no' === get_option( 'woocommerce_registration_generate_password' ) ? 'active' : 'inactive',
					],
					[
						'modify'       => true,
						'label'       => esc_html__( 'First Name', 'affiliates-for-woocommerce' ),
						'type'        => 'text',
						'name'        => 'first_name',
						'position'    => 4,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => true,
						'editable'    => true,
						'status'      => 'active',
					],
					[
						'modify'       => true,
						'label'       => esc_html__( 'Last Name', 'affiliates-for-woocommerce' ),
						'type'        => 'text',
						'name'        => 'last_name',
						'position'    => 5,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => true,
						'editable'    => true,
						'status'      => 'active',
					],
					[
						'modify'       => true,
						'label'       => esc_html__( 'How will you promote our site?', 'affiliates-for-woocommerce' ),
						'type'        => 'select',
						'name'        => 'how_promote',
						'position'    => 6,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => 'Website/Blog | Newsletter/Email Marketing | Social Media | YouTube | Others',
						'required'    => true,
						'editable'    => true,
						'status'      => 'active',
					],
					[
						'modify'       => true,
						'label'       => sprintf( esc_html__( 'Please read and accept our %s', 'affiliates-for-woocommerce' ), '<a target="_blank" href="#">' . esc_html__( 'Terms and Conditions', 'affiliates-for-woocommerce' ) . '</a>' ),
						'type'        => 'checkbox',
						'name'        => 'terms',
						'position'    => 7,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => true,
						'editable'    => false,
						'status'      => 'active',
					]
				];
			} else {
				foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
					if ( ! $affiliate_registration_field[ 'modify' ] ) {
						if ( 'username' === $affiliate_registration_field[ 'name' ] ) {
							$affiliate_registration_fields[ $key ][ 'status' ] = 'no' === get_option( 'woocommerce_registration_generate_username' ) ? 'active' : 'inactive';
						} elseif ( 'password' === $affiliate_registration_field[ 'name' ] ) {
							$affiliate_registration_fields[ $key ][ 'status' ] = 'no' === get_option( 'woocommerce_registration_generate_password' ) ? 'active' : 'inactive';
						}
					}
				}
			}

			return $affiliate_registration_fields;
		}

		/**
         * Update affiliate registration fields function
         *
         * @param array $affiliate_registration_fields
         * @return void
         */
		public function ddwcaf_update_affiliate_registration_fields( $affiliate_registration_fields ) {
			update_option( '_ddwcaf_affiliate_registration_fields', $affiliate_registration_fields );
		}

		/**
         * Delete affiliate registration fields function
         *
         * @return void
         */
		public function ddwcaf_delete_affiliate_registration_fields() {
			delete_option( '_ddwcaf_affiliate_registration_fields' );
		}

		/**
		 * Save affiliate registration field function
		 *
		 * @return boolean
		 */
		public function ddwcaf_save_affiliate_registration_field() {
			$affiliate_registration_fields = $this->ddwcaf_get_affiliate_registration_fields();

			$field_id    = ! empty( $_POST[ '_ddwcaf_field_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_field_id' ] ) ) : '';
			$label       = ! empty( $_POST[ '_ddwcaf_label' ] ) ? wp_kses_post( wp_unslash( $_POST[ '_ddwcaf_label' ] ) ) : '';
			$type        = ! empty( $_POST[ '_ddwcaf_type' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_type' ] ) ) : '';
			$name        = ! empty( $_POST[ '_ddwcaf_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_name' ] ) ) : '';
			$options     = ! empty( $_POST[ '_ddwcaf_options' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_options' ] ) ) : '';
			$position    = ! empty( $_POST[ '_ddwcaf_position' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_position' ] ) ) : count( $affiliate_registration_fields ) + 1;
			$placeholder = ! empty( $_POST[ '_ddwcaf_placeholder' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_placeholder' ] ) ) : '';
			$css_classes = ! empty( $_POST[ '_ddwcaf_css_classes' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_css_classes' ] ) ) : '';
			$description = ! empty( $_POST[ '_ddwcaf_description' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_description' ] ) ) : '';
			$required    = ! empty( $_POST[ '_ddwcaf_required' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_required' ] ) ) : '';
			$editable    = ! empty( $_POST[ '_ddwcaf_editable' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_editable' ] ) ) : '';
			$status      = ! empty( $_POST[ '_ddwcaf_status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_status' ] ) ) : 'inactive';
			$modify      = true;

			if ( 'select' === $type || 'radio' === $type ) {
				$options_flag = ! empty( $options );
			} else {
				$options      = '';
				$options_flag = true;
			}

			if ( ! empty( $label ) && ! empty( $type ) && ! empty( $name ) && ( isset( $position ) && '' !== $position ) && ! empty( $status ) && $options_flag ) {
				$field_data = compact( 'modify', 'label', 'type', 'name', 'placeholder', 'position', 'css_classes', 'description', 'options', 'required', 'editable', 'status' );

				if ( ! empty( $field_id ) && ! empty( $affiliate_registration_fields[ $field_id ] ) ) {
					$affiliate_registration_fields[ $field_id ] = $field_data;
				} else {
					$affiliate_registration_fields[] = $field_data;
				}

				$this->ddwcaf_update_affiliate_registration_fields( $affiliate_registration_fields );

				return true;
			}

			return false;
		}

		/**
		 * Update affiliate registration field status function
		 *
		 * @param int $field_id
		 * @param string $status
		 * @return boolean
		 */
		public function ddwcaf_update_affiliate_registration_field_status( $field_id, $status ) {
			$affiliate_registration_fields = $this->ddwcaf_get_affiliate_registration_fields();

			if ( ! empty( $field_id ) && ! empty( $affiliate_registration_fields[ $field_id ] ) ) {
				$affiliate_registration_fields[ $field_id ][ 'status' ] = $status;

				$this->ddwcaf_update_affiliate_registration_fields( $affiliate_registration_fields );

				return true;
			}

			return false;
		}

		/**
		 * Get dashboard endpoints function
		 *
		 * @return array
		 */
		public function ddwcaf_get_dashboard_endpoints() {
			$endpoints = [
				'dashboard' => [
					'endpoint' => 'dashboard',
					'title'    => esc_html__( 'Dashboard', 'affiliates-for-woocommerce' ),
				],
				'commissions' => [
					'endpoint' => 'commissions',
					'title'    => esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
				],
				'payouts' => [
					'endpoint' => 'payouts',
					'title'    => esc_html__( 'Payouts', 'affiliates-for-woocommerce' ),
				],
				'visits' => [
					'endpoint' => 'visits',
					'title'    => esc_html__( 'Visits', 'affiliates-for-woocommerce' ),
				],
				'top-products' => [
					'endpoint' => 'top-products',
					'title'    => esc_html__( 'Top Products', 'affiliates-for-woocommerce' ),
				],
				'link-generator' => [
					'endpoint' => 'link-generator',
					'title'    => esc_html__( 'Link Generator', 'affiliates-for-woocommerce' ),
				],
				'settings' => [
					'endpoint' => 'settings',
					'title'    => esc_html__( 'Settings', 'affiliates-for-woocommerce' ),
				],
			];

			return apply_filters( 'ddwcaf_modify_dashboard_endpoints', $endpoints );
		}

		/**
		 * Get affiliate referral token function
		 *
		 * @param int $user_id
		 * @return string
		 */
		public function ddwcaf_get_affiliate_referral_token( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			return strval( $user_id );
		}

		/**
		 * Get affiliate id by token function
		 *
		 * @param string $token
		 * @return int
		 */
		public function ddwcaf_get_affiliate_id_by_token( $token ) {
			$affiliate = get_userdata( $token );

			if ( $affiliate && 'approved' === $this->ddwcaf_get_affiliate_status( $affiliate->ID ) && $token === $this->ddwcaf_get_affiliate_referral_token( $affiliate->ID ) ) {
				$affiliate = intval( $affiliate->ID );
			} else {
				$affiliate = false;
			}

			return $affiliate;
		}

		/**
		 * Get affiliate referral url function
		 *
		 * @param int|string $user_id
		 * @param array $args
		 * @param string $url
		 * @return string
		 */
		public function ddwcaf_get_affiliate_referral_url( $user_id = '', $args = [], $url = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			if ( empty( $url ) ) {
				$url = site_url();
			}

			$referral_token = $this->ddwcaf_get_affiliate_referral_token( $user_id );

			$args[ $this->ddwcaf_configuration[ 'query_variable_name' ] ] = $referral_token;

			foreach ( $args as $key => $value ) {
				$url = add_query_arg( $key, $value, $url );
			}

			return $url;
		}

		/**
		 * Get all affiliates count having pending commissions function
		 *
		 * @param array $args
		 * @return array
		 */
		public function ddwcaf_get_all_affiliates_count_having_pending_commissions( $args ) {
			extract( $args );

			$conditions = '';

			if ( ! empty( $from_date ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.created_at>=%s", $from_date );
			}

			if ( ! empty( $end_date ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.created_at<=%s", $end_date );
			}

			if ( empty( $all_affiliates ) && ! empty( $include_affiliate_ids ) ) {
				$include_affiliate_ids = implode( ", ", $include_affiliate_ids );
				$conditions           .= " AND commissions.affiliate_id IN ($include_affiliate_ids)";
			}

			return intval( $this->wpdb->get_var( $this->wpdb->prepare( "SELECT COUNT(DISTINCT commissions.affiliate_id) FROM {$this->wpdb->ddwcaf_commissions} as commissions JOIN {$this->wpdb->users} as users ON commissions.affiliate_id=users.ID JOIN {$this->wpdb->usermeta} as usermeta ON usermeta.user_id=commissions.affiliate_id AND usermeta.meta_key=%s WHERE 1=1 AND usermeta.meta_value=%s AND commissions.status=%s AND commissions.commission IS NOT NULL $conditions", '_ddwcaf_affiliate_status', 'approved', 'pending' ) ) );
		}

		/**
		 * Get all affiliates having pending commissions function
		 *
		 * @param array $args
		 * @return array
		 */
		public function ddwcaf_get_all_affiliates_having_pending_commissions( $args ) {
			extract( $args );

			$conditions = '';
			$limit      = '';

			if ( ! empty( $from_date ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.created_at>=%s", $from_date );
			}

			if ( ! empty( $end_date ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.created_at<=%s", $end_date );
			}

			if ( empty( $all_affiliates ) && ! empty( $include_affiliate_ids ) ) {
				$include_affiliate_ids = implode( ", ", $include_affiliate_ids );
				$conditions           .= " AND commissions.affiliate_id IN ($include_affiliate_ids)";
			}

			if ( ! empty( $per_page ) ) {
				$limit = $this->wpdb->prepare( " LIMIT %d", $per_page );
			}

			if ( ! empty( $offset ) ) {
				$limit .= $this->wpdb->prepare( " OFFSET %d", $offset );
			}

			return $this->wpdb->get_results( $this->wpdb->prepare( "SELECT DISTINCT commissions.affiliate_id as affiliate_id, users.user_login, ROUND( SUM( commissions.commission ), 2 ) as total_commission_amount FROM {$this->wpdb->ddwcaf_commissions} as commissions JOIN {$this->wpdb->users} as users ON commissions.affiliate_id=users.ID JOIN {$this->wpdb->usermeta} as usermeta ON usermeta.user_id=commissions.affiliate_id AND usermeta.meta_key=%s WHERE 1=1 AND usermeta.meta_value=%s AND commissions.status=%s $conditions GROUP BY commissions.affiliate_id ORDER BY commissions.affiliate_id DESC $limit", '_ddwcaf_affiliate_status', 'approved', 'pending' ), ARRAY_A );
		}

		/**
		 * Render link generator function
		 *
		 * @param int $user_id
		 * @return void
		 */
		public function ddwcaf_render_link_generator( $user_id = '' ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$affiliate_referral_token = $this->ddwcaf_get_affiliate_referral_token( $user_id );
			$affiliate_referral_url   = $this->ddwcaf_get_affiliate_referral_url( $user_id );

			?>
			<div class="ddwcaf-details-container">
				<h4><?php esc_html_e( 'Default Affiliate Link', 'affiliates-for-woocommerce' ); ?></h4>
				<p><?php esc_html_e( 'Affiliate Token', 'affiliates-for-woocommerce' ); ?> <span class="ddwcaf-referral-token"><?php echo esc_html( $affiliate_referral_token ); ?></span></p>
				<p>
					<?php esc_html_e( 'Referral Link', 'affiliates-for-woocommerce' ); ?>
					<span class="ddwcaf-copy-field-container">
						<input type="text" class="ddwcaf-copy-target form-control input-text" value="<?php echo esc_url( $affiliate_referral_url ); ?>" data-copy-text="<?php echo esc_url( $affiliate_referral_url ); ?>" readonly />
						<a href="#" class="ddwcaf-copy-trigger" data-tooltip="<?php esc_attr_e( 'Copy Referral Link', 'affiliates-for-woocommerce' ); ?>">
							<svg fill="none" height="20" viewBox="0 0 20 20" width="20"><path d="M8 3C7.44772 3 7 3.44772 7 4V4.5C7 4.77614 6.77614 5 6.5 5C6.22386 5 6 4.77614 6 4.5V4C6 2.89543 6.89543 2 8 2H8.5C8.77614 2 9 2.22386 9 2.5C9 2.77614 8.77614 3 8.5 3H8Z" fill="#212121"/><path d="M7 12C7 12.5523 7.44772 13 8 13H8.5C8.77614 13 9 13.2239 9 13.5C9 13.7761 8.77614 14 8.5 14H8C6.89543 14 6 13.1046 6 12V11.5C6 11.2239 6.22386 11 6.5 11C6.77614 11 7 11.2239 7 11.5V12Z" fill="#212121"/><path d="M7 6.5C7 6.22386 6.77614 6 6.5 6C6.22386 6 6 6.22386 6 6.5V9.5C6 9.77614 6.22386 10 6.5 10C6.77614 10 7 9.77614 7 9.5V6.5Z" fill="#212121"/><path d="M16 3C16.5523 3 17 3.44772 17 4V4.5C17 4.77614 17.2239 5 17.5 5C17.7761 5 18 4.77614 18 4.5V4C18 2.89543 17.1046 2 16 2H15.5C15.2239 2 15 2.22386 15 2.5C15 2.77614 15.2239 3 15.5 3H16Z" fill="#212121"/><path d="M16 13C16.5523 13 17 12.5523 17 12V11.5C17 11.2239 17.2239 11 17.5 11C17.7761 11 18 11.2239 18 11.5V12C18 13.1046 17.1046 14 16 14H15.5C15.2239 14 15 13.7761 15 13.5C15 13.2239 15.2239 13 15.5 13H16Z" fill="#212121"/><path d="M17.5 6C17.2239 6 17 6.22386 17 6.5V9.5C17 9.77614 17.2239 10 17.5 10C17.7761 10 18 9.77614 18 9.5V6.5C18 6.22386 17.7761 6 17.5 6Z" fill="#212121"/><path d="M10.5 2C10.2239 2 10 2.22386 10 2.5C10 2.77614 10.2239 3 10.5 3H13.5C13.7761 3 14 2.77614 14 2.5C14 2.22386 13.7761 2 13.5 2H10.5Z" fill="#212121"/><path d="M10 13.5C10 13.2239 10.2239 13 10.5 13H13.5C13.7761 13 14 13.2239 14 13.5C14 13.7761 13.7761 14 13.5 14H10.5C10.2239 14 10 13.7761 10 13.5Z" fill="#212121"/><path d="M4 6H5V7H4C3.44772 7 3 7.44772 3 8V14.5C3 15.8807 4.11929 17 5.5 17H12C12.5523 17 13 16.5523 13 16V15H14V16C14 17.1046 13.1046 18 12 18H5.5C3.567 18 2 16.433 2 14.5V8C2 6.89543 2.89543 6 4 6Z" fill="#212121"/></svg>
							<span class="ddwcaf-copy-tooltip"></span>
						</a>
					</span>
				</p>
				<small><?php esc_html_e( 'Use this link to refer customers to our store and earn commissions.', 'affiliates-for-woocommerce' ); ?></small>
			</div>
			<div class="ddwcaf-details-container ddwcaf-link-generator-container">
				<h4><?php esc_html_e( 'Custom Link Generator', 'affiliates-for-woocommerce' ); ?></h4>
				<p><?php esc_html_e( 'Generate a custom referral link for any destination URL on our store.', 'affiliates-for-woocommerce' ); ?></p>
				<label for="ddwcaf-custom-page-url"><?php esc_html_e( 'Enter Page URL', 'affiliates-for-woocommerce' ); ?></label>
				<div class="ddwcaf-input-with-icon">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
					<input type="text" id="ddwcaf-custom-page-url" class="ddwcaf-custom-page-url" placeholder="<?php echo esc_url( site_url() . '/product/awesome-item/' ); ?>">
				</div>
				<p><?php esc_html_e( 'Paste a link from our store to generate your referral version.', 'affiliates-for-woocommerce' ); ?></p>
				<div class="ddwcaf-generator-arrow">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
				</div>

				<div class="ddwcaf-generated-link-row">
					<?php $this->ddwcaf_render_custom_referral_link_result( $affiliate_referral_url ); ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Render custom referral link result function
		 *
		 * @param string $url
		 * @return void
		 */
		public function ddwcaf_render_custom_referral_link_result( $url ) {
			?>
			<p>
				<?php esc_html_e( 'Custom Referral Link', 'affiliates-for-woocommerce' ); ?>

				<span class="ddwcaf-copy-field-container">
					<input type="text" class="ddwcaf-copy-target form-control input-text" value="<?php echo esc_url( $url ); ?>" data-copy-text="<?php echo esc_url( $url ); ?>" readonly />
					<a href="#" class="ddwcaf-copy-trigger" data-tooltip="<?php esc_attr_e( 'Copy Custom Referral Link', 'affiliates-for-woocommerce' ); ?>">
						<svg fill="none" height="20" viewBox="0 0 20 20" width="20"><path d="M8 3C7.44772 3 7 3.44772 7 4V4.5C7 4.77614 6.77614 5 6.5 5C6.22386 5 6 4.77614 6 4.5V4C6 2.89543 6.89543 2 8 2H8.5C8.77614 2 9 2.22386 9 2.5C9 2.77614 8.77614 3 8.5 3H8Z" fill="#212121"/><path d="M7 12C7 12.5523 7.44772 13 8 13H8.5C8.77614 13 9 13.2239 9 13.5C9 13.7761 8.77614 14 8.5 14H8C6.89543 14 6 13.1046 6 12V11.5C6 11.2239 6.22386 11 6.5 11C6.77614 11 7 11.2239 7 11.5V12Z" fill="#212121"/><path d="M7 6.5C7 6.22386 6.77614 6 6.5 6C6.22386 6 6 6.22386 6 6.5V9.5C6 9.77614 6.22386 10 6.5 10C6.77614 10 7 9.77614 7 9.5V6.5Z" fill="#212121"/><path d="M16 3C16.5523 3 17 3.44772 17 4V4.5C17 4.77614 17.2239 5 17.5 5C17.7761 5 18 4.77614 18 4.5V4C18 2.89543 17.1046 2 16 2H15.5C15.2239 2 15 2.22386 15 2.5C15 2.77614 15.2239 3 15.5 3H16Z" fill="#212121"/><path d="M16 13C16.5523 13 17 12.5523 17 12V11.5C17 11.2239 17.2239 11 17.5 11C17.7761 11 18 11.2239 18 11.5V12C18 13.1046 17.1046 14 16 14H15.5C15.2239 14 15 13.7761 15 13.5C15 13.2239 15.2239 13 15.5 13H16Z" fill="#212121"/><path d="M17.5 6C17.2239 6 17 6.22386 17 6.5V9.5C17 9.77614 17.2239 10 17.5 10C17.7761 10 18 9.77614 18 9.5V6.5C18 6.22386 17.7761 6 17.5 6Z" fill="#212121"/><path d="M10.5 2C10.2239 2 10 2.22386 10 2.5C10 2.77614 10.2239 3 10.5 3H13.5C13.7761 3 14 2.77614 14 2.5C14 2.22386 13.7761 2 13.5 2H10.5Z" fill="#212121"/><path d="M10 13.5C10 13.2239 10.2239 13 10.5 13H13.5C13.7761 13 14 13.2239 14 13.5C14 13.7761 13.7761 14 13.5 14H10.5C10.2239 14 10 13.7761 10 13.5Z" fill="#212121"/><path d="M4 6H5V7H4C3.44772 7 3 7.44772 3 8V14.5C3 15.8807 4.11929 17 5.5 17H12C12.5523 17 13 16.5523 13 16V15H14V16C14 17.1046 13.1046 18 12 18H5.5C3.567 18 2 16.433 2 14.5V8C2 6.89543 2.89543 6 4 6Z" fill="#212121"/></svg>
						<span class="ddwcaf-copy-tooltip"></span>
					</a>
				</span>
			</p>

			<small><?php esc_html_e( 'Use this link to refer customers to our store and earn commissions.', 'affiliates-for-woocommerce' ); ?></small>
			<?php
		}

		/**
		 * Get translation function
		 *
		 * @param string $word
		 * @return string
		 */
		public function ddwcaf_get_translation( $word ) {
			$translation = [
				'pending'  => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
				'approved' => esc_html__( 'Approved', 'affiliates-for-woocommerce' ),
				'rejected' => esc_html__( 'Rejected', 'affiliates-for-woocommerce' ),
				'banned'   => esc_html__( 'Banned', 'affiliates-for-woocommerce' ),
				'active'   => esc_html__( 'Active', 'affiliates-for-woocommerce' ),
				'inactive' => esc_html__( 'Inactive', 'affiliates-for-woocommerce' ),
			];

			return ! empty( $translation[ $word ] ) ? $translation[ $word ] : $word;
		}
	}
}
