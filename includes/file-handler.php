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
		protected $ddwcaf_configuration;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->ddwcaf_configuration = $this->ddwcaf_set_globals();

			new Common\DDWCAF_Common_Hooks( $this->ddwcaf_configuration );
			new Front\DDWCAF_Front_Ajax_Hooks( $this->ddwcaf_configuration );

			if ( is_admin() ) {
				new DDWCAF_Admin_Dashboard( $this->ddwcaf_configuration );
				new Admin\DDWCAF_Admin_Hooks( $this->ddwcaf_configuration );
				new Admin\DDWCAF_Admin_Ajax_Hooks( $this->ddwcaf_configuration );

				// Initialize the notifications
				new \DevDiggers_Notifications( [
					'plugin_slug' => 'affiliates-for-woocommerce',
				] );
			} else {
				if ( ! empty( $this->ddwcaf_configuration[ 'enabled' ] ) ) {
					new Front\DDWCAF_Front_Hooks( $this->ddwcaf_configuration );
				}
			}
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

			$withdrawal_methods_saved = get_option( '_ddwcaf_withdrawal_methods', [] );

			$withdrawal_methods = [
				'bacs' => [
					'name'      => esc_html__( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ),
					'available' => $withdrawal_methods_saved['bacs']['available'],
					'status'    => $withdrawal_methods_saved['bacs']['status'],
					'url'       => '',
				],
				'paypal_email' => [
					'name'      => esc_html__( 'PayPal', 'affiliates-for-woocommerce' ),
					'available' => $withdrawal_methods_saved['paypal_email']['available'],
					'status'    => $withdrawal_methods_saved['paypal_email']['status'],
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
				'user_roles'                                    => get_option( '_ddwcaf_user_roles', [] ),
				'fields_enabled_on_woocommerce_registration'    => get_option( '_ddwcaf_fields_enabled_on_woocommerce_registration' ),
				'affiliate_dashboard_page_id'                   => get_option( '_ddwcaf_affiliate_dashboard_page_id' ),
				'primary_color'                                 => get_option( '_ddwcaf_primary_color', '#0256ff' ),
				'enable_widgets_affiliate_dashboard_page'       => get_option( '_ddwcaf_enable_widgets_affiliate_dashboard_page' ),
				'default_affiliate_dashboard_page'              => get_option( '_ddwcaf_default_affiliate_dashboard_page', 'custom_page' ),
				'my_account_enabled'                            => get_option( '_ddwcaf_my_account_enabled', 'yes' ),
				'my_account_endpoint'                           => 'affiliate-dashboard',
				'my_account_endpoint_title'                     => esc_html__( 'Affiliate Dashboard', 'affiliates-for-woocommerce' ),
				'enable_widgets_my_account_endpoint'            => get_option( '_ddwcaf_enable_widgets_my_account_endpoint' ),
				'query_variable_name'                           => get_option( '_ddwcaf_query_variable_name', 'ref' ),
				'referral_cookie_name'                          => get_option( '_ddwcaf_referral_cookie_name', 'ddwcaf_referral_token' ),
				'referral_cookie_expires'                       => get_option( '_ddwcaf_referral_cookie_expires', 7 ),
				'referral_cookie_change_allowed'                => get_option( '_ddwcaf_referral_cookie_change_allowed', 'yes' ),
				'register_visits_enabled'                       => get_option( '_ddwcaf_register_visits_enabled', 'yes' ),
				'default_commission_rate'                       => get_option( '_ddwcaf_default_commission_rate', 10 ),
				'exclude_taxes_enabled'                         => get_option( '_ddwcaf_exclude_taxes_enabled', 'yes' ),
				'exclude_discounts_enabled'                     => get_option( '_ddwcaf_exclude_discounts_enabled', 'yes' ),
				'withdrawal_methods'                            => $withdrawal_methods,
				'withdrawal_type'                               => 'manually_by_admin',
				'affiliate_dashboard_shortcode'                 => get_option( '_ddwcaf_affiliate_dashboard_shortcode', '[ddwcaf_affiliate_dashboard_shortcode]' ),
				'affiliate_registration_form_shortcode_content' => get_option( '_ddwcaf_affiliate_registration_form_shortcode_content', 'both' ),
				'affiliate_registration_form_shortcode'         => get_option( '_ddwcaf_affiliate_registration_form_shortcode', '[ddwcaf_affiliate_registration_form_shortcode]' ),
				'details_icons_enabled'                         => get_option( '_ddwcaf_details_icons_enabled', 'yes' ),
				'details_icons_wrapper_enabled'                 => get_option( '_ddwcaf_details_icons_wrapper_enabled', 'yes' ),
				'details_icon_size'                             => get_option( '_ddwcaf_details_icon_size', 40 ),
				'details_icon_color'                            => get_option( '_ddwcaf_details_icon_color', '#0256ff' ),
				'details_icon_wrapper_background_color'         => get_option( '_ddwcaf_details_icon_wrapper_background_color', '#EEF3FF' ),
				'details_card_background_color'                 => get_option( '_ddwcaf_details_card_background_color', '#ffffff' ),
				'details_card_border_color'                     => get_option( '_ddwcaf_details_card_border_color', '#dce6ff' ),
				'details_card_text_color'                       => get_option( '_ddwcaf_details_card_text_color', '#121212' ),
				'details_card_value_color'                      => get_option( '_ddwcaf_details_card_value_color', '#121212' ),
				'table_header_background_color'                 => get_option( '_ddwcaf_table_header_background_color', '#f5f7f9' ),
				'table_header_text_color'                       => get_option( '_ddwcaf_table_header_text_color', '#121212' ),
			];

			$ddwcaf_configuration[ 'withdrawal_methods' ][ 'ddwcwm_wallet' ][ 'status' ] = false;

			$ddwcaf_configuration = apply_filters( 'ddwcaf_modify_global_configuration', $ddwcaf_configuration );

			return $ddwcaf_configuration;
		}
	}
}
