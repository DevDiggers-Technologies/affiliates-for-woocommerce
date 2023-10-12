<?php
/**
 * File handler
 *
 * @author DevDiggers
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_File_Handler' ) ) {
	/**
	 * File handler class
	 */
	class DDWCAF_File_Handler {
		/**
         * Configuration Variable
         *
         * @var array
         */
        protected $ddwcbl_configuration;

		/**
		 * Constructor
		 */
		public function __construct() {
			require_once DDWCAF_PLUGIN_FILE . 'includes/global-functions.php';

			$this->ddwcaf_configuration = $this->ddwcaf_set_globals();

			if ( is_admin() ) {
				new Admin\DDWCAF_Admin_Hooks( $this->ddwcaf_configuration );
				new Admin\DDWCAF_Admin_Ajax_Hooks( $this->ddwcaf_configuration );
			} else {
				if ( ! empty( $this->ddwcaf_configuration[ 'enabled' ] ) ) {
					new Front\DDWCAF_Front_Hooks( $this->ddwcaf_configuration );
				}
			}

			new Common\DDWCAF_Common_Hooks( $this->ddwcaf_configuration );
			new Front\DDWCAF_Front_Ajax_Hooks( $this->ddwcaf_configuration );
		}

		/**
		 * Set globals function
		 *
		 * @return void
		 */
		public function ddwcaf_set_globals() {
			global $wpdb;
			$wpdb->ddwcaf_visits          = $wpdb->prefix . 'ddwcaf_visits';
			$wpdb->ddwcaf_commissions     = $wpdb->prefix . 'ddwcaf_commissions';
			$wpdb->ddwcaf_commissionsmeta = $wpdb->prefix . 'ddwcaf_commissionsmeta';
			$wpdb->ddwcaf_payouts         = $wpdb->prefix . 'ddwcaf_payouts';

			global $ddwcaf_configuration;

			$user_roles                            = get_option( '_ddwcaf_user_roles' );
			$query_variable_name                   = get_option( '_ddwcaf_query_variable_name' );
			$referral_cookie_name                  = get_option( '_ddwcaf_referral_cookie_name' );
			$referral_social_share_options         = get_option( '_ddwcaf_referral_social_share_options' );
			$social_share_title                    = get_option( '_ddwcaf_social_share_title' );
			$social_share_text                     = get_option( '_ddwcaf_social_share_text' );
			$affiliate_registration_form_shortcode = get_option( '_ddwcaf_affiliate_registration_form_shortcode' );
			$affiliate_dashboard_shortcode         = get_option( '_ddwcaf_affiliate_dashboard_shortcode' );

            $withdrawal_methods = [
                'bacs' => [
                    'name'      => esc_html__( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ),
                    'available' => true,
                    'status'    => true,
                    'url'       => '',
                ],
                'paypal_email' => [
                    'name'      => esc_html__( 'PayPal', 'affiliates-for-woocommerce' ),
                    'available' => true,
                    'status'    => true,
                    'url'       => '',
                ],
                'ddwcwm_wallet' => [
                    'name'      => esc_html__( 'WooCommerce Wallet Management [Pro]', 'affiliates-for-woocommerce' ),
                    'available' => false,
                    'status'    => false,
                    'url'       => '//devdiggers.com/product/woocommerce-wallet-management/'
                ],
            ];

			$ddwcaf_configuration = [
				'enabled'                                       => get_option( '_ddwcaf_enabled' ),
				'default_affiliate_status'                      => 'pending',
				'user_roles'                                    => ! empty( $user_roles ) ? $user_roles : [],
				'fields_enabled_on_woocommerce_registration'    => get_option( '_ddwcaf_fields_enabled_on_woocommerce_registration' ),
				'affiliate_dashboard_page_id'                   => get_option( '_ddwcaf_affiliate_dashboard_page_id' ),
				'primary_color'                                 => get_option( '_ddwcaf_primary_color', '#038bad' ),
				'enable_widgets_affiliate_dashboard_page'       => get_option( '_ddwcaf_enable_widgets_affiliate_dashboard_page' ),
				'default_affiliate_dashboard_page'              => get_option( '_ddwcaf_default_affiliate_dashboard_page', 'custom_page' ),
				'my_account_enabled'                            => get_option( '_ddwcaf_my_account_enabled', 1 ),
				'my_account_endpoint'                           => 'affiliate-dashboard',
				'my_account_endpoint_title'                     => esc_html__( 'Affiliate Dashboard', 'affiliates-for-woocommerce' ),
				'enable_widgets_my_account_endpoint'            => get_option( '_ddwcaf_enable_widgets_my_account_endpoint' ),
				'query_variable_name'                           => ! empty( $query_variable_name ) ? $query_variable_name : 'ref',
				'default_referral_token'                        => 'user_id',
				'referral_cookie_name'                          => ! empty( $referral_cookie_name ) ? $referral_cookie_name : 'ddwcaf_referral_token',
				'referral_cookie_expires'                       => get_option( '_ddwcaf_referral_cookie_expires', 7 ),
				'register_visits_enabled'                       => get_option( '_ddwcaf_register_visits_enabled', 1 ),
				'referral_social_share_options'                 => ! empty( $referral_social_share_options ) ? $referral_social_share_options : [],
				'social_share_title'                            => ! empty( $social_share_title ) ? $social_share_title : sprintf( esc_html__( 'My Referral URL on %s', 'affiliates-for-woocommerce' ), get_bloginfo( 'name' ) ),
				'social_share_text'                             => ! empty( $social_share_text ) ? $social_share_text : '{referral_url}',
				'pinterest_image_url'                           => get_option( '_ddwcaf_pinterest_image_url' ),
				'default_commission_rate'                       => get_option( '_ddwcaf_default_commission_rate', 10 ),
				'exclude_taxes_enabled'                         => get_option( '_ddwcaf_exclude_taxes_enabled', 1 ),
				'exclude_discounts_enabled'                     => get_option( '_ddwcaf_exclude_discounts_enabled', 1 ),
				'withdrawal_methods'                            => get_option( '_ddwcaf_withdrawal_methods', $withdrawal_methods ),
				'affiliate_dashboard_shortcode'                 => ! empty( $affiliate_dashboard_shortcode ) ? $affiliate_dashboard_shortcode : '[ddwcaf_affiliate_dashboard_shortcode]',
				'affiliate_registration_form_shortcode_content' => get_option( '_ddwcaf_affiliate_registration_form_shortcode_content', 'both' ),
				'affiliate_registration_form_shortcode'         => ! empty( $affiliate_registration_form_shortcode ) ? $affiliate_registration_form_shortcode : '[ddwcaf_affiliate_registration_form_shortcode]',
			];

			$ddwcaf_configuration[ 'withdrawal_methods' ][ 'ddwcwm_wallet' ][ 'status' ] = false;

			return $ddwcaf_configuration;
		}
	}
}
