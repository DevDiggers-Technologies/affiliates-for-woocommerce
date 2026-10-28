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
		 * Schema revision. Raise it with any table change that has to reach sites already on
		 * this plugin version ( 2: report indexes on dates, affiliates and orders; the visit ones cover the
		 * report's sums, so they never read the rows ).
		 */
		const SCHEMA = 2;

		/**
		 * On plugin activation function
		 * 
		 * @return void
		 */
		public static function ddwcaf_on_plugin_activation() {
			if ( ! get_option( '_ddwcpr_installed_at' ) ) {
				update_option( '_ddwcpr_installed_at', time() );
			}

			// Trigger setup wizard for new installations.
			set_transient( 'ddfw_activation_redirect_affiliates-for-woocommerce', true, 30 );

			self::ddwcaf_create_tables();
			self::ddwcaf_add_role();
			self::ddwcaf_add_pages();
		}

		/**
		 * Create or update the plugin tables. dbDelta only adds what is missing, so this is safe
		 * to run again on an update.
		 *
		 * @return void
		 */
		protected static function ddwcaf_create_tables() {
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
				`ip` varchar(50) NOT NULL,
				`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				`order_id` bigint(20) DEFAULT NULL,
				`conversion_date` datetime DEFAULT NULL,
				PRIMARY KEY (id),
				KEY report_date (date,affiliate_id,order_id),
				KEY report_affiliate (affiliate_id,date,order_id),
				KEY order_id (order_id)
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
				PRIMARY KEY (id),
				KEY created_at (created_at),
				KEY affiliate_created (affiliate_id,created_at),
				KEY order_id (order_id)
			) $charset_collate;" );

			// Commissions meta table.
			$sql = "CREATE TABLE $wpdb->ddwcaf_commissionsmeta (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`commission_id` bigint(20) NOT NULL,
				`meta_key` varchar(255) NOT NULL DEFAULT '',
				`meta_value` longtext NOT NULL,
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
				PRIMARY KEY (id),
				KEY status (status),
				KEY affiliate_id (affiliate_id)
			) $charset_collate;";

			dbDelta( $sql );

			// Only once the report indexes are really there: a failed ALTER is tried again next time.
			if ( $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM {$wpdb->ddwcaf_visits} WHERE Key_name = %s", 'report_date' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed table.
				update_option( '_ddwcaf_db_schema', self::SCHEMA );
			}
		}

		/**
		 * Bring the tables up to date on sites that updated in place: activation hooks do not
		 * fire on a plugin update, so new indexes would otherwise never reach them.
		 *
		 * @return void
		 */
		public static function ddwcaf_maybe_upgrade() {
			if ( self::SCHEMA <= (int) get_option( '_ddwcaf_db_schema' ) ) {
				return;
			}

			// One request upgrades; the rest carry on. Adding indexes to a large visits table takes a
			// while, and requests racing to ALTER it would queue behind each other's locks.
			$lock = (int) get_option( '_ddwcaf_upgrading' );

			if ( $lock && $lock > time() - 10 * MINUTE_IN_SECONDS ) {
				return;
			}

			if ( $lock ) {
				update_option( '_ddwcaf_upgrading', time(), false ); // A run that died; take it over.
			} elseif ( ! add_option( '_ddwcaf_upgrading', time(), '', false ) ) {
				return;
			}

			self::ddwcaf_create_tables();
			delete_option( '_ddwcaf_upgrading' );
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
				'name'    => 'affiliate-dashboard-page',
				'title'   => esc_html_x( 'Affiliate Dashboard', '[GLOBAL] Dashboard page title', 'affiliates-for-woocommerce' ),
				'content' => '<!-- wp:shortcode -->[ddwcaf_affiliate_dashboard_shortcode]<!-- /wp:shortcode -->',
			];

			return wc_create_page( esc_sql( $page[ 'name' ] ), '_ddwcaf_affiliate_dashboard_page_id', $page[ 'title' ], $page[ 'content' ] );
		}
	}
}
