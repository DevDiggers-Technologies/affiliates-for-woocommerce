<?php
/**
 * Create Schema on Activation
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Install' ) ) {
	/**
	 * Activation class
	 */
	final class DDWCAF_Install {
		/**
		 * On plugin activation function
		 * 
		 * @return void
		 */
		public static function ddwcaf_on_plugin_activation() {
            if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			global $wpdb;
			$wpdb->ddwcaf_visits          = $wpdb->prefix . 'ddwcaf_visits';
			$wpdb->ddwcaf_commissions     = $wpdb->prefix . 'ddwcaf_commissions';
			$wpdb->ddwcaf_commissionsmeta = $wpdb->prefix . 'ddwcaf_commissionsmeta';
			$wpdb->ddwcaf_payouts         = $wpdb->prefix . 'ddwcaf_payouts';
			$charset_collate              = $wpdb->get_charset_collate();

			// Visits table.
            dbDelta( "CREATE TABLE {$wpdb->ddwcaf_visits} (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`affiliate_id` bigint(20) NOT NULL,
				`url` varchar(255) NOT NULL,
				`referrer_url` varchar(255) DEFAULT NULL,
				`ip` varchar(15) NOT NULL,
				`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				`order_id` bigint(20) DEFAULT NULL,
				`conversion_date` datetime DEFAULT NULL,
				PRIMARY KEY (id)
			) $charset_collate;" );

			// Commissions table.
            dbDelta( "CREATE TABLE {$wpdb->ddwcaf_commissions} (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`affiliate_id` bigint(20) NOT NULL,
				`order_id` bigint(20) NOT NULL,
				`line_item_id` bigint(20) NOT NULL,
				`product_id` bigint(20) NOT NULL,
				`quantity` bigint(20) NOT NULL,
				`line_total` double(15,4) NOT NULL DEFAULT '0',
				`commission` double(15,4) NOT NULL DEFAULT '0',
				`refund` double(15,4) NOT NULL DEFAULT '0',
				`status` varchar(100) NOT NULL,
				`created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				`updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				PRIMARY KEY (id)
			) $charset_collate;" );

			// Commissions meta table.
			$sql = "CREATE TABLE $wpdb->ddwcaf_commissionsmeta (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`commission_id` bigint(20) NOT NULL,
				`meta_key` varchar(255) NOT NULL DEFAULT '',
				`meta_value` longtext NOT NULL DEFAULT '',
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $sql );

			// Payouts table.
			$sql = "CREATE TABLE $wpdb->ddwcaf_payouts (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`affiliate_id` bigint(20) NOT NULL,
				`payment_method` varchar(255) NOT NULL DEFAULT '',
				`amount` double(15,4) NOT NULL DEFAULT 0,
				`transaction_id` varchar(255) DEFAULT NULL,
				`reference` varchar(255) NOT NULL,
				`status` varchar(255) NOT NULL,
				`payment_info` longtext DEFAULT NULL,
				`created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				`completed_at` datetime DEFAULT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $sql );

			self::ddwcaf_add_role();
			self::ddwcaf_add_pages();
		}

		/**
		 * Add role function
		 *
		 * @return void
		 */
		protected static function ddwcaf_add_role() {
			if ( empty( get_role( 'ddwcaf_affiliate' ) ) ) {
				add_role(
					'ddwcaf_affiliate',
					'Affiliate',
					[
						'read'    => true,
						'level_0' => true,
					]
				);
			}
		}

		/**
		 * Add pages function
		 *
		 * @return void
		 */
		protected static function ddwcaf_add_pages() {
			include_once WC()->plugin_path() . '/includes/admin/wc-admin-functions.php';

			if ( ! function_exists( 'wc_create_page' ) ) {
				return false;
			}

			$page = [
				'name'    => 'affiliate-dashboard',
				'title'   => esc_html_x( 'Affiliate Dashboard', '[GLOBAL] Dashboard page title', 'affiliates-for-woocommerce' ),
				'content' => '<!-- wp:shortcode -->[ddwcaf_affiliate_dashboard_shortcode]<!-- /wp:shortcode -->',
			];

			return wc_create_page( esc_sql( $page[ 'name' ] ), '_ddwcaf_affiliate_dashboard_page_id', $page[ 'title' ], $page[ 'content' ] );
		}
	}
}
