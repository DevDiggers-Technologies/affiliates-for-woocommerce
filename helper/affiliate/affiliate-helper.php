<?php
/**
 * Affiliate helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Affiliate;

use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;

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

			foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
				if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] ) {
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

					if ( is_admin() ) {
						$args[ 'show_frontend_fields' ] = true;
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

					ddwcaf_form_field( $args );
				}
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
			$withdrawal_method_name = [
                'bacs'          => esc_html__( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ),
                'paypal_email'  => esc_html__( 'PayPal', 'affiliates-for-woocommerce' ),
                'ddwcwm_wallet' => esc_html__( 'WooCommerce Wallet Management', 'affiliates-for-woocommerce' ),
            ];

			return ! empty( $withdrawal_method_name[ $slug ] ) ? $withdrawal_method_name[ $slug ] : $slug;
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
						'type'        => 'textarea',
						'name'        => 'how_promote',
						'position'    => 7,
						'placeholder' => '',
						'css_classes' => '',
						'description' => '',
						'options'     => '',
						'required'    => false,
						'editable'    => true,
						'status'      => 'active',
					],
					[
						'modify'       => true,
						'label'       => esc_html__( 'Please read and accept our [terms]', 'affiliates-for-woocommerce' ),
						'type'        => 'checkbox',
						'name'        => 'terms',
						'position'    => 9,
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

			if ( ! empty( $label ) && ! empty( $type ) && ! empty( $name ) && ! empty( $position ) && ! empty( $status ) && $options_flag ) {
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
		 * Get social share options function
		 *
		 * @param string $url
		 * @return void
		 */
		public function ddwcaf_get_social_share_options( $url ) {
			$url = str_replace( '{referral_url}', $url, $this->ddwcaf_configuration[ 'social_share_text' ] );

			?>
			<div class="ddwcaf-social-share-wrapper">
				<ul>
					<?php
					foreach ( $this->ddwcaf_configuration[ 'referral_social_share_options' ] as $key => $social_share_option ) {
						switch ( $social_share_option ) {
							case 'twitter' :
								?>
								<li class="twitter">
									<a href="//twitter.com/share?text=<?php echo wp_strip_all_tags( rawurlencode( $this->ddwcaf_configuration[ 'social_share_title' ] ) ); ?>&amp;url=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Twitter', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'facebook' :
								?>
								<li class="facebook">
									<a href="//www.facebook.com/sharer.php?u=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Facebook', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M5.677,12.998V8.123h3.575V6.224C9.252,2.949,11.712,0,14.736,0h3.94v4.874h-3.94
												c-0.432,0-0.934,0.524-0.934,1.308v1.942h4.874v4.874h-4.874V24H9.252V12.998H5.677z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'pinterest' :
								?>
								<li class="pinterest">
									<a href="//www.pinterest.com/pin/create/button/?url=<?php echo rawurlencode( esc_url( $url ) ); ?>&amp;media=<?php echo esc_url( $this->ddwcaf_configuration[ 'pinterest_image_url' ] ); ?>&amp;description=<?php echo urlencode( wp_trim_words( strip_shortcodes( $url ), 40 ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Pinterest', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M13.757,17.343c-1.487,0-2.886-0.804-3.365-1.717c0,0-0.8,3.173-0.969,3.785
												c-0.596,2.165-2.35,4.331-2.487,4.508c-0.095,0.124-0.305,0.085-0.327-0.078c-0.038-0.276-0.485-3.007,0.041-5.235
												c0.264-1.118,1.772-7.505,1.772-7.505s-0.44-0.879-0.44-2.179c0-2.041,1.183-3.565,2.657-3.565c1.252,0,1.857,0.94,1.857,2.068
												c0,1.26-0.802,3.142-1.216,4.888c-0.345,1.461,0.734,2.653,2.174,2.653c2.609,0,4.367-3.352,4.367-7.323
												c0-3.018-2.032-5.278-5.731-5.278c-4.177,0-6.782,3.116-6.782,6.597c0,1.2,0.355,2.047,0.909,2.701
												c0.255,0.301,0.29,0.422,0.198,0.767c-0.067,0.254-0.218,0.864-0.281,1.106c-0.092,0.349-0.375,0.474-0.69,0.345
												c-1.923-0.785-2.82-2.893-2.82-5.262c0-3.912,3.3-8.604,9.844-8.604c5.259,0,8.72,3.805,8.72,7.89
												C21.188,13.307,18.185,17.343,13.757,17.343z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'linkedin' :
								?>
								<li class="linkedin">
									<a href="//www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo rawurlencode( esc_url( $url ) ); ?>&amp;title=<?php echo wp_strip_all_tags( rawurlencode( $this->ddwcaf_configuration[ 'social_share_title' ] ) ); ?>&amp;summary=<?php echo urlencode( wp_trim_words( strip_shortcodes( $url ), 40 ) ); ?>&amp;source=<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M6.52,22h-4.13V8.667h4.13V22z M4.436,6.92
												c-1.349,0-2.442-1.101-2.442-2.46C1.994,3.102,3.087,2,4.436,2s2.442,1.102,2.442,2.46C6.877,5.819,5.784,6.92,4.436,6.92z
												M21.994,22h-4.109c0,0,0-5.079,0-6.999c0-1.919-0.73-2.991-2.249-2.991c-1.652,0-2.515,1.116-2.515,2.991c0,2.054,0,6.999,0,6.999
												h-3.96V8.667h3.96v1.796c0,0,1.191-2.202,4.02-2.202c2.828,0,4.853,1.727,4.853,5.298C21.994,17.129,21.994,22,21.994,22z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'viber' :
								?>
								<li class="viber">
									<a href="viber://forward?text=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Viber', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M14.957,5.825c0.764,0.163,1.349,0.453,1.849,0.921c0.643,0.608,0.996,1.343,1.151,2.4
												c0.105,0.689,0.062,0.96-0.182,1.184c-0.229,0.209-0.651,0.217-0.907,0.019c-0.186-0.139-0.244-0.286-0.287-0.685
												c-0.05-0.53-0.143-0.902-0.302-1.246c-0.341-0.731-0.942-1.111-1.957-1.235c-0.477-0.058-0.62-0.112-0.775-0.294
												c-0.283-0.337-0.174-0.883,0.217-1.084c0.147-0.074,0.209-0.081,0.535-0.062C14.5,5.755,14.798,5.79,14.957,5.825z M14.131,2.902
												c2.353,0.344,4.175,1.436,5.369,3.209c0.671,0.999,1.089,2.171,1.233,3.429c0.05,0.461,0.05,1.3-0.004,1.44
												c-0.051,0.131-0.213,0.309-0.353,0.383c-0.151,0.078-0.473,0.07-0.651-0.023c-0.298-0.151-0.388-0.391-0.388-1.041
												c0-1.002-0.26-2.059-0.709-2.88c-0.512-0.937-1.256-1.711-2.163-2.249c-0.779-0.465-1.93-0.809-2.981-0.894
												c-0.38-0.031-0.589-0.108-0.733-0.275c-0.221-0.252-0.244-0.592-0.058-0.875C12.895,2.813,13.205,2.763,14.131,2.902z
												M5.002,0.514c0.136,0.047,0.345,0.155,0.465,0.232c0.736,0.488,2.787,3.108,3.458,4.416c0.384,0.747,0.512,1.3,0.392,1.711
												C9.193,7.314,8.988,7.547,8.069,8.286C7.701,8.584,7.356,8.89,7.301,8.971C7.162,9.172,7.049,9.567,7.049,9.846
												c0.004,0.646,0.423,1.819,0.973,2.721c0.426,0.7,1.19,1.598,1.946,2.287c0.888,0.813,1.671,1.366,2.555,1.804
												c1.136,0.565,1.83,0.708,2.337,0.472c0.128-0.058,0.264-0.135,0.306-0.17c0.039-0.035,0.337-0.399,0.663-0.801
												c0.628-0.79,0.771-0.917,1.202-1.065c0.547-0.186,1.105-0.135,1.667,0.151c0.427,0.221,1.357,0.797,1.957,1.215
												c0.791,0.553,2.481,1.931,2.71,2.206c0.403,0.495,0.473,1.13,0.202,1.831c-0.287,0.739-1.403,2.125-2.182,2.717
												c-0.705,0.534-1.206,0.739-1.865,0.77c-0.543,0.027-0.768-0.019-1.461-0.306c-5.442-2.241-9.788-5.585-13.238-10.179
												c-1.802-2.4-3.175-4.888-4.113-7.47c-0.547-1.505-0.574-2.16-0.124-2.93c0.194-0.325,1.019-1.13,1.62-1.579
												c1-0.743,1.461-1.018,1.83-1.095C4.285,0.371,4.723,0.414,5.002,0.514z M13.864,0.096c1.334,0.166,2.411,0.487,3.593,1.065
												c1.163,0.569,1.907,1.107,2.892,2.086c0.923,0.925,1.434,1.626,1.977,2.713c0.756,1.517,1.186,3.321,1.26,5.306
												c0.027,0.677,0.008,0.828-0.147,1.022c-0.294,0.375-0.942,0.313-1.163-0.108c-0.07-0.139-0.089-0.259-0.112-0.801
												c-0.039-0.832-0.097-1.37-0.213-2.013c-0.458-2.52-1.667-4.532-3.597-5.976c-1.609-1.208-3.272-1.796-5.45-1.924
												c-0.737-0.043-0.864-0.07-1.031-0.197c-0.31-0.244-0.326-0.817-0.027-1.084c0.182-0.166,0.31-0.19,0.942-0.17
												C13.116,0.027,13.6,0.065,13.864,0.096z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'vk' :
								?>
								<li class="vk">
									<a href="//vk.com/share.php?url=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on VK', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M11.701 18.771h1.437s.433-.047.654-.284c.21-.221.21-.63.21-.63s-.031-1.927.869-2.21c.887-.281 2.012 1.86 3.211 2.683.916.629 1.605.494 1.605.494l3.211-.044s1.682-.105.887-1.426c-.061-.105-.451-.975-2.371-2.76-2.012-1.861-1.742-1.561.676-4.787 1.469-1.965 2.07-3.166 1.875-3.676-.166-.48-1.26-.361-1.26-.361l-3.602.031s-.27-.031-.465.09c-.195.119-.314.391-.314.391s-.572 1.529-1.336 2.82c-1.623 2.729-2.268 2.879-2.523 2.699-.604-.391-.449-1.58-.449-2.432 0-2.641.404-3.75-.781-4.035-.39-.091-.681-.15-1.685-.166-1.29-.014-2.378.01-2.995.311-.405.203-.72.652-.539.675.24.03.779.146 1.064.537.375.506.359 1.636.359 1.636s.211 3.116-.494 3.503c-.495.262-1.155-.28-2.595-2.756-.735-1.26-1.291-2.67-1.291-2.67s-.105-.256-.299-.406c-.227-.165-.557-.225-.557-.225l-3.435.03s-.51.016-.689.24c-.166.195-.016.615-.016.615s2.686 6.287 5.732 9.453c2.79 2.902 5.956 2.715 5.956 2.715l-.05-.055z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'reddit' :
								?>
								<li class="reddit">
									<a href="//www.reddit.com/submit?url=<?php echo rawurlencode( esc_url( $url ) ); ?>&amp;title=<?php echo wp_strip_all_tags( rawurlencode( $this->ddwcaf_configuration[ 'social_share_title' ] ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Reddit', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M23.999,11.786c0-1.576-1.294-2.858-2.885-2.858c-0.689,0-1.321,0.241-1.817,0.641
												c-1.759-1.095-3.991-1.755-6.383-1.895l1.248-3.91l3.43,0.8c0.09,1.237,1.134,2.217,2.405,2.217c1.33,0,2.412-1.072,2.412-2.391
												c0-1.318-1.082-2.39-2.412-2.39c-0.93,0-1.739,0.525-2.141,1.291l-3.985-0.93c-0.334-0.078-0.671,0.112-0.775,0.436L11.547,7.65
												C8.969,7.712,6.546,8.375,4.658,9.534c-0.49-0.38-1.105-0.607-1.774-0.607C1.293,8.927,0,10.209,0,11.785
												c0,0.974,0.495,1.836,1.249,2.351c-0.031,0.227-0.048,0.455-0.048,0.686c0,1.97,1.156,3.803,3.254,5.16
												C6.468,21.283,9.13,22,11.952,22s5.485-0.716,7.496-2.018c2.099-1.357,3.254-3.19,3.254-5.16c0-0.21-0.014-0.419-0.041-0.626
												C23.464,13.689,23.999,12.798,23.999,11.786 M19.997,3.299c0.607,0,1.102,0.49,1.102,1.091c0,0.602-0.494,1.092-1.102,1.092
												s-1.102-0.49-1.102-1.092C18.896,3.789,19.389,3.299,19.997,3.299 M6.805,13.554c0-0.888,0.752-1.633,1.648-1.633
												c0.897,0,1.625,0.745,1.625,1.633c0,0.889-0.728,1.61-1.625,1.61C7.557,15.163,6.805,14.442,6.805,13.554 M15.951,18.288
												c-0.836,0.827-2.124,1.229-3.939,1.229c-0.004,0-0.008-0.001-0.013-0.001c-0.004,0-0.008,0.001-0.013,0.001
												c-1.815,0-3.103-0.402-3.938-1.229c-0.256-0.254-0.256-0.665,0-0.919c0.256-0.253,0.671-0.253,0.927,0
												c0.576,0.571,1.561,0.849,3.01,0.849c0.005,0,0.009,0.001,0.013,0.001c0.005,0,0.009-0.001,0.013-0.001
												c1.45,0,2.435-0.278,3.012-0.849c0.256-0.254,0.671-0.253,0.927,0C16.206,17.623,16.206,18.034,15.951,18.288 M15.569,15.163
												c-0.897,0-1.651-0.721-1.651-1.61s0.754-1.633,1.651-1.633s1.625,0.745,1.625,1.633C17.193,14.442,16.466,15.163,15.569,15.163"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'tumblr' :
								?>
								<li class="tumblr">
									<a href="//www.tumblr.com/widgets/share/tool?canonicalUrl=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_attr_e( 'Share on Tumblr', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
												<path d="M19.44,22.887c-1.034,0.487-1.97,0.828-2.808,1.024
												c-0.838,0.195-1.744,0.293-2.718,0.293c-1.106,0-2.083-0.14-2.933-0.418c-0.851-0.279-1.575-0.677-2.175-1.194
												c-0.6-0.518-1.017-1.067-1.248-1.649c-0.231-0.581-0.347-1.425-0.347-2.53V9.93H4.56V6.482c0.947-0.309,1.759-0.751,2.434-1.327
												C7.67,4.58,8.212,3.889,8.62,3.081C9.029,2.274,9.311,1.247,9.464,0h3.429v6.131h5.747V9.93h-5.747v6.208
												c0,1.403,0.074,2.304,0.223,2.702c0.149,0.399,0.426,0.718,0.829,0.954c0.536,0.322,1.148,0.483,1.838,0.483
												c1.225,0,2.444-0.399,3.657-1.196V22.887L19.44,22.887z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'whatsapp' :
								?>
								<li class="whatsapp">
									<a href="//api.whatsapp.com/send?text=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_html_e( 'Share on WhatsApp', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;" data-action="share/whatsapp/share">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" role="img" viewBox="0 0 90 90" aria-hidden="true" focusable="false">
												<path id="WhatsApp" d="M90,43.841c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L0,90l7.975-23.522
													c-4.023-6.606-6.34-14.354-6.34-22.637C1.635,19.628,21.416,0,45.818,0C70.223,0,90,19.628,90,43.841z M45.818,6.982
													c-20.484,0-37.146,16.535-37.146,36.859c0,8.065,2.629,15.534,7.076,21.61L11.107,79.14l14.275-4.537
													c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.857S66.301,6.982,45.818,6.982z M68.129,53.938
													c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.495c-0.993-0.358-1.717-0.538-2.438,0.537
													c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.333
													c-3.219-2.848-5.393-6.364-6.025-7.441c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882
													c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.977
													c-0.902-2.15-1.803-1.792-2.436-1.792c-0.631,0-1.354-0.09-2.076-0.09c-0.722,0-1.896,0.269-2.889,1.344
													c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.113c0.541,0.716,7.49,11.92,18.5,16.223
													C58.2,65.771,58.2,64.336,60.186,64.156c1.984-0.179,6.406-2.599,7.312-5.107C68.398,56.537,68.398,54.386,68.129,53.938z"/>
											</svg>
										</span>
									</a>
								</li>
								<?php
								break;
							case 'email' :
								?>
								<li class="email">
									<a href="mailto:?subject=<?php echo rawurlencode( $this->ddwcaf_configuration[ 'social_share_title' ] ); ?>&body=<?php echo rawurlencode( esc_url( $url ) ); ?>" aria-label="<?php esc_html_e( 'Share on WhatsApp', 'affiliates-for-woocommerce' ); ?>" onclick="ddwcafOnSocialShareClick( this.href );return false;" data-action="share/whatsapp/share">
										<span class="ddwcaf-icon-wrap">
											<svg class="ddwcaf-icon" style="enable-background:new 0 0 24 24;" version="1.1" viewBox="0 0 24 24" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="info"/><g id="icons"><path d="M20,3H4C1.8,3,0,4.8,0,7v10c0,2.2,1.8,4,4,4h16c2.2,0,4-1.8,4-4V7C24,4.8,22.2,3,20,3z M21.6,8.8l-7.9,5.3   c-0.5,0.3-1.1,0.5-1.7,0.5s-1.2-0.2-1.7-0.5L2.4,8.8C2,8.5,1.9,7.9,2.2,7.4C2.5,7,3.1,6.9,3.6,7.2l7.9,5.3c0.3,0.2,0.8,0.2,1.1,0   l7.9-5.3c0.5-0.3,1.1-0.2,1.4,0.3C22.1,7.9,22,8.5,21.6,8.8z" id="email"/></g></svg>
										</span>
									</a>
								</li>
								<?php
								break;
						}
					}
					?>
				</ul>
			</div>
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
