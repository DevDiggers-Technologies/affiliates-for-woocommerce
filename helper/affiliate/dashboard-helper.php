<?php
/**
 * Dashboard helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Affiliate;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Dashboard_Helper' ) ) {
	/**
	 * Dashboard helper class
	 */
	class DDWCAF_Dashboard_Helper {
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
		 *
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
			global $wpdb;
			$this->wpdb                 = $wpdb;
			$this->ddwcaf_configuration = $ddwcaf_configuration;
		}

		/**
		 * Get dashboard data function
		 *
		 * @return array
		 */
		public function get_dashboard_data( $affiliate_id = null ) {
			$date_range = $this->get_date_range();
			$from_date  = $date_range['from'];
			$to_date    = $date_range['to'];

			return [
				'summary'            => $this->get_summary_metrics( $from_date, $to_date, $affiliate_id ),
				'charts'             => [
					'performance'        => $this->get_performance_chart_data( $from_date, $to_date, $affiliate_id ),
					'revenue_impact'     => $this->get_revenue_impact_data( $from_date, $to_date, $affiliate_id ),
					'conversion_sources' => $this->get_conversion_sources_data( $from_date, $to_date, $affiliate_id ),
				],
				'recent_activities'  => $this->get_recent_activities( 10, $affiliate_id ),
				'top_affiliates'     => $this->get_top_affiliates(),
				'date_range'         => $date_range,
			];
		}

		/**
		 * Get summary metrics
		 *
		 * @param string $from_date
		 * @param string $to_date
		 * @return array
		 */
		public function get_summary_metrics( $from_date, $to_date, $affiliate_id = null ) {
			$affiliate_where = $affiliate_id ? $this->wpdb->prepare( " AND affiliate_id = %d", $affiliate_id ) : "";

			// Current period data
			$current_earnings = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(commission) FROM {$this->wpdb->ddwcaf_commissions} WHERE status != 'cancelled' AND created_at BETWEEN %s AND %s $affiliate_where",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;

			$current_commissions = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(id) FROM {$this->wpdb->ddwcaf_commissions} WHERE created_at BETWEEN %s AND %s $affiliate_where",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;

			$current_visits = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(id) FROM {$this->wpdb->ddwcaf_visits} WHERE date BETWEEN %s AND %s $affiliate_where",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;

			$current_affiliates = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(u.ID) FROM {$this->wpdb->users} u 
				JOIN {$this->wpdb->usermeta} um ON u.ID = um.user_id 
				WHERE um.meta_key = 'user_registered' AND u.user_registered BETWEEN %s AND %s",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;
			// user_registered is in users table, not usermeta usually, but let's check WP query.
			$current_affiliates = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(ID) FROM {$this->wpdb->users} WHERE user_registered BETWEEN %s AND %s",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;
			// Filter by role ddwcaf_affiliate
			$current_affiliates = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(u.ID) FROM {$this->wpdb->users} u 
				INNER JOIN {$this->wpdb->usermeta} um ON (u.ID = um.user_id) 
				WHERE u.user_registered BETWEEN %s AND %s 
				AND um.meta_key = '{$this->wpdb->prefix}capabilities' AND um.meta_value LIKE %s",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59',
				'%ddwcaf_affiliate%'
			) ) ?: 0;

			$current_sales = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(line_total) FROM {$this->wpdb->ddwcaf_commissions} WHERE status != 'cancelled' AND created_at BETWEEN %s AND %s $affiliate_where",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;

			$current_pending = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(commission) FROM {$this->wpdb->ddwcaf_commissions} WHERE status = 'pending' AND created_at BETWEEN %s AND %s $affiliate_where",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;

			// Previous period for comparison
			$days_diff = ( strtotime( $to_date ) - strtotime( $from_date ) ) / ( 60 * 60 * 24 );
			$prev_from = date( 'Y-m-d', strtotime( $from_date . ' -' . ceil( $days_diff + 1 ) . ' days' ) );
			$prev_to   = date( 'Y-m-d', strtotime( $from_date . ' -1 day' ) );

			$prev_earnings = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(commission) FROM {$this->wpdb->ddwcaf_commissions} WHERE status != 'cancelled' AND created_at BETWEEN %s AND %s $affiliate_where",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) ) ?: 0;

			$prev_commissions = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(id) FROM {$this->wpdb->ddwcaf_commissions} WHERE created_at BETWEEN %s AND %s $affiliate_where",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) ) ?: 0;

			$prev_sales = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(line_total) FROM {$this->wpdb->ddwcaf_commissions} WHERE status != 'cancelled' AND created_at BETWEEN %s AND %s $affiliate_where",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) ) ?: 0;

			$prev_pending = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(commission) FROM {$this->wpdb->ddwcaf_commissions} WHERE status = 'pending' AND created_at BETWEEN %s AND %s $affiliate_where",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) ) ?: 0;

			$prev_visits = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(id) FROM {$this->wpdb->ddwcaf_visits} WHERE date BETWEEN %s AND %s $affiliate_where",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) ) ?: 0;

			$prev_affiliates = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(u.ID) FROM {$this->wpdb->users} u 
				INNER JOIN {$this->wpdb->usermeta} um ON (u.ID = um.user_id) 
				WHERE u.user_registered BETWEEN %s AND %s 
				AND um.meta_key = '{$this->wpdb->prefix}capabilities' AND um.meta_value LIKE %s",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59',
				'%ddwcaf_affiliate%'
			) ) ?: 0;

			// Total Payouts (Current)
			$current_payouts = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(amount) FROM {$this->wpdb->prefix}ddwcaf_payouts WHERE created_at BETWEEN %s AND %s AND status = 'completed' $affiliate_where",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) ) ?: 0;

			$prev_payouts = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT SUM(amount) FROM {$this->wpdb->prefix}ddwcaf_payouts WHERE created_at BETWEEN %s AND %s AND status = 'completed' $affiliate_where",
				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) ) ?: 0;

			// Active Affiliates (Total)
			$active_affiliates = $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(u.ID) FROM {$this->wpdb->users} u 
				INNER JOIN {$this->wpdb->usermeta} um1 ON (u.ID = um1.user_id) 
				INNER JOIN {$this->wpdb->usermeta} um2 ON (u.ID = um2.user_id) 
				WHERE um1.meta_key = '{$this->wpdb->prefix}capabilities' AND um1.meta_value LIKE %s
				AND um2.meta_key = '_ddwcaf_affiliate_status' AND um2.meta_value = %s",
				'%ddwcaf_affiliate%',
				'approved'
			) ) ?: 0;

			return [
				'total_earnings' => [
					'value'       => (float) $current_earnings,
					'change'      => $this->calculate_change( $current_earnings, $prev_earnings ),
					'is_positive' => $current_earnings >= $prev_earnings,
				],
				'commissions' => [
					'value'       => (int) $current_commissions,
					'change'      => $this->calculate_change( $current_commissions, $prev_commissions ),
					'is_positive' => $current_commissions >= $prev_commissions,
				],
				'total_sales' => [
					'value'       => (float) $current_sales,
					'change'      => $this->calculate_change( $current_sales, $prev_sales ),
					'is_positive' => $current_sales >= $prev_sales,
				],
				'visits' => [
					'value'       => (int) $current_visits,
					'change'      => $this->calculate_change( $current_visits, $prev_visits ),
					'is_positive' => $current_visits >= $prev_visits,
				],
				'new_affiliates' => [
					'value'       => (int) $current_affiliates,
					'change'      => $this->calculate_change( $current_affiliates, $prev_affiliates ),
					'is_positive' => $current_affiliates >= $prev_affiliates,
				],
				'pending_earnings' => [
					'value'       => (float) $current_pending,
					'change'      => $this->calculate_change( $current_pending, $prev_pending ),
					'is_positive' => $current_pending >= $prev_pending,
				],
				'total_payouts' => [
					'value'       => (float) $current_payouts,
					'change'      => $this->calculate_change( $current_payouts, $prev_payouts ),
					'is_positive' => $current_payouts >= $prev_payouts,
				],
				'active_affiliates' => [
					'value'       => (int) $active_affiliates,
					'change'      => 0,
					'is_positive' => true,
				],
				'conversion_rate' => [
					'value'       => $current_visits > 0 ? round( ( $current_commissions / $current_visits ) * 100, 2 ) : 0,
					'change'      => 0,
					'is_positive' => true,
				],
				'avg_commission_value' => [
					'value'       => $current_commissions > 0 ? (float) ($current_earnings / $current_commissions) : 0,
					'change'      => 0,
					'is_positive' => true,
				]
			];
		}

		/**
		 * Calculate percentage change
		 */
		protected function calculate_change( $current, $prev ) {
			if ( $prev == 0 ) {
				return $current > 0 ? 100 : 0;
			}
			return round( ( ( $current - $prev ) / $prev ) * 100, 1 );
		}

		/**
		 * Get chart data with intelligent grouping
		 *
		 * @param string $from_date
		 * @param string $to_date
		 * @param string $table
		 * @param string $date_column
		 * @param string $select_clause
		 * @param string $value_key
		 * @return array
		 */
		protected function get_chart_data_with_grouping( $from_date, $to_date, $table, $date_column, $select_clause, $value_key = 'value', $affiliate_id = null ) {
			$days_diff = ( strtotime( $to_date ) - strtotime( $from_date ) ) / ( 60 * 60 * 24 );

			$affiliate_where = $affiliate_id ? $this->wpdb->prepare( " AND affiliate_id = %d", $affiliate_id ) : "";


			if ( $days_diff <= 60 ) {
				$group_by = "DATE({$date_column})";
			} elseif ( $days_diff <= 365 ) {
				$group_by = "DATE_FORMAT({$date_column}, '%Y-%m')";
			} else {
				$group_by = "CONCAT(YEAR({$date_column}), '-Q', QUARTER({$date_column}))";
			}

			$data = $this->wpdb->get_results( $this->wpdb->prepare(
				"SELECT {$group_by} as period, {$select_clause} 
				FROM {$table} 
				WHERE {$date_column} BETWEEN %s AND %s $affiliate_where 
				GROUP BY period ORDER BY period ASC",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			), ARRAY_A );

			$processed_data = [];

			// Organize data by date
			foreach ( $data as $row ) {
				$date_value = $row['period'];

				if ( $days_diff > 365 ) {
					// Quarterly: convert to first month of quarter for frontend parsing
					$year    = substr( $date_value, 0, 4 );
					$quarter = substr( $date_value, -1 );
					$month   = ( $quarter - 1 ) * 3 + 1;
					$date    = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT ) . '-01';
				} else {
					$date = $date_value;
				}

				$processed_data[$date] = [
					'period' => $date,
					'value'  => (float) $row[$value_key]
				];
			}

			// Fill gaps for daily data
			if ( $days_diff <= 90 ) {
				$curr = $from_date;
				while ( $curr <= $to_date ) {
					if ( ! isset( $processed_data[$curr] ) ) {
						$processed_data[$curr] = [
							'period' => $curr,
							'value'  => 0
						];
					}
					$curr = date( 'Y-m-d', strtotime( $curr . ' +1 day' ) );
				}
			}

			ksort( $processed_data );
			return array_values( $processed_data );
		}

		/**
		 * Get performance chart data
		 */
		public function get_performance_chart_data( $from_date, $to_date, $affiliate_id = null ) {
			return [
				'earnings' => $this->get_chart_data_with_grouping(
					$from_date,
					$to_date,
					$this->wpdb->ddwcaf_commissions,
					'created_at',
					"SUM(commission) as value",
					'value',
					$affiliate_id
				),
				'visits' => $this->get_chart_data_with_grouping(
					$from_date,
					$to_date,
					$this->wpdb->ddwcaf_visits,
					'date',
					"COUNT(id) as value",
					'value',
					$affiliate_id
				),
			];
		}

		/**
		 * Get revenue impact data
		 * 
		 * @param string $from_date
		 * @param string $to_date
		 * @return array
		 */
		public function get_revenue_impact_data( $from_date, $to_date, $affiliate_id = null ) {
			$days_diff = ( strtotime( $to_date ) - strtotime( $from_date ) ) / ( 60 * 60 * 24 );

			$affiliate_revenue_data = $this->get_chart_data_with_grouping(
				$from_date,
				$to_date,
				$this->wpdb->ddwcaf_commissions,
				'created_at',
				"SUM(line_total) as value",
				'value',
				$affiliate_id
			);

			// Store revenue (using wc_get_orders for parity)
			$store_revenue_raw = [];
			$orders = wc_get_orders( [
				'status'       => [ 'wc-completed', 'wc-processing' ],
				'date_created' => $from_date . '...' . $to_date,
				'limit'        => -1,
			] );

			if ( ! empty( $orders ) ) {
				foreach ( $orders as $order ) {
					$date = $order->get_date_created();
					if ( ! $date ) continue;
					
					$period = '';
					if ( $days_diff > 365 ) {
						// Quarterly
						$q = ceil( $date->date( 'n' ) / 3 );
						$m = ( $q - 1 ) * 3 + 1;
						$period = $date->date( 'Y-' ) . str_pad( $m, 2, '0', STR_PAD_LEFT ) . '-01';
					} elseif ( $days_diff > 90 ) {
						// Monthly
						$period = $date->date( 'Y-m' );
					} else {
						// Daily
						$period = $date->date( 'Y-m-d' );
					}

					if ( ! isset( $store_revenue_raw[ $period ] ) ) {
						$store_revenue_raw[ $period ] = 0;
					}
					$store_revenue_raw[ $period ] += (float) $order->get_total();
				}
			}

			// Consolidate and fill gaps
			$all_periods = array_unique( array_merge( 
				array_column( $affiliate_revenue_data, 'period' ), 
				array_keys( $store_revenue_raw ) 
			) );

			if ( $days_diff <= 90 ) {
				$curr = $from_date;
				while ( $curr <= $to_date ) {
					$all_periods[] = $curr;
					$curr = date( 'Y-m-d', strtotime( $curr . ' +1 day' ) );
				}
			}
			$all_periods = array_unique( $all_periods );
			sort( $all_periods );

			$affiliate = [];
			$total     = [];

			foreach ( $all_periods as $period ) {
				$aff_val = 0;
				foreach ( $affiliate_revenue_data as $row ) {
					if ( $row['period'] === $period ) {
						$aff_val = $row['value'];
						break;
					}
				}
				$affiliate[] = [ 'period' => $period, 'value' => $aff_val ];
				$total[]     = [ 'period' => $period, 'value' => $store_revenue_raw[$period] ?? 0 ];
			}

			return [
				'affiliate' => $affiliate,
				'total'     => $total,
			];
		}

		/**
		 * Get conversion sources data
		 */
		public function get_conversion_sources_data( $from_date, $to_date, $affiliate_id = null ) {
			$affiliate_where = $affiliate_id ? $this->wpdb->prepare( " AND c.affiliate_id = %d", $affiliate_id ) : "";

			$results = $this->wpdb->get_results( $this->wpdb->prepare(
				"SELECT v.referrer_url as source, COUNT(c.id) as count 
				FROM {$this->wpdb->ddwcaf_commissions} c
				LEFT JOIN {$this->wpdb->ddwcaf_visits} v ON c.order_id = v.order_id
				WHERE c.created_at BETWEEN %s AND %s $affiliate_where 
				GROUP BY v.referrer_url ORDER BY count DESC",
				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			), ARRAY_A );

			$consolidated = [];
			$default_label = esc_html__( 'Direct / Unknown', 'affiliates-for-woocommerce' );

			if ( ! empty( $results ) ) {
				foreach ( $results as $row ) {
					$source_host = ! empty( $row['source'] ) ? parse_url( $row['source'], PHP_URL_HOST ) : $default_label;
					
					// Final fallback if host extraction fails
					$source = $source_host ?: $default_label;

					if ( ! isset( $consolidated[$source] ) ) {
						$consolidated[$source] = 0;
					}
					$consolidated[$source] += (int) $row['count'];
				}
			}

			$formatted = [];
			foreach ( $consolidated as $source => $count ) {
				$formatted[] = [
					'source' => $source,
					'count'  => $count,
				];
			}

			// Re-sort by count and limit
			usort( $formatted, function( $a, $b ) {
				return $b['count'] - $a['count'];
			} );

			return array_slice( $formatted, 0, 10 );
		}

		/**
		 * Get recent activities
		 */
		public function get_recent_activities( $limit = 10, $affiliate_id = null ) {
			$affiliate_where_c = $affiliate_id ? $this->wpdb->prepare( " WHERE c.affiliate_id = %d", $affiliate_id ) : "";
			$affiliate_where_v = $affiliate_id ? $this->wpdb->prepare( " WHERE v.affiliate_id = %d", $affiliate_id ) : "";

			$commissions = $this->wpdb->get_results( $this->wpdb->prepare(
				"SELECT c.*, u.display_name, u.user_email 
				FROM {$this->wpdb->ddwcaf_commissions} c 
				LEFT JOIN {$this->wpdb->users} u ON c.affiliate_id = u.ID 
				$affiliate_where_c 
				ORDER BY c.created_at DESC LIMIT %d",
				$limit
			), ARRAY_A );

			$visits = $this->wpdb->get_results( $this->wpdb->prepare(
				"SELECT v.*, u.display_name, u.user_email 
				FROM {$this->wpdb->ddwcaf_visits} v 
				LEFT JOIN {$this->wpdb->users} u ON v.affiliate_id = u.ID 
				$affiliate_where_v 
				ORDER BY v.date DESC LIMIT %d",
				$limit
			), ARRAY_A );

			return [
				'commissions' => $commissions,
				'visits'      => $visits,
			];
		}

		/**
		 * Get top affiliates
		 */
		public function get_top_affiliates( $limit = 5 ) {
			return $this->wpdb->get_results( $this->wpdb->prepare(
				"SELECT u.ID, u.display_name, u.user_email, SUM(c.commission) as total_earnings 
				FROM {$this->wpdb->users} u 
				JOIN {$this->wpdb->ddwcaf_commissions} c ON u.ID = c.affiliate_id 
				WHERE c.status != 'cancelled' 
				GROUP BY u.ID ORDER BY total_earnings DESC LIMIT %d",
				$limit
			), ARRAY_A );
		}

		/**
		 * Get date range
		 */
		public function get_date_range() {
			$range = ! empty( $_GET['date_range'] ) ? sanitize_text_field( wp_unslash( $_GET['date_range'] ) ) : '30_days';

			switch ( $range ) {
				case 'today':
					return [
						'type'  => 'today',
						'from'  => current_time( 'Y-m-d' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Today', 'affiliates-for-woocommerce' ),
					];
				case '7_days':
					return [
						'type'  => '7_days',
						'from'  => date( 'Y-m-d', strtotime( 'monday this week' ) ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'This Week', 'affiliates-for-woocommerce' ),
					];
				case 'last_week':
					return [
						'type'  => 'last_week',
						'from'  => date( 'Y-m-d', strtotime( 'monday last week' ) ),
						'to'    => date( 'Y-m-d', strtotime( 'sunday last week' ) ),
						'label' => esc_html__( 'Last Week', 'affiliates-for-woocommerce' ),
					];
				case '30_days':
					return [
						'type'  => '30_days',
						'from'  => current_time( 'Y-m-01' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'This Month', 'affiliates-for-woocommerce' ),
					];
				case 'last_month':
					return [
						'type'  => 'last_month',
						'from'  => date( 'Y-m-01', strtotime( 'first day of last month' ) ),
						'to'    => date( 'Y-m-t', strtotime( 'last day of last month' ) ),
						'label' => esc_html__( 'Last Month', 'affiliates-for-woocommerce' ),
					];
				case '90_days':
					return [
						'type'  => '90_days',
						'from'  => date( 'Y-m-d', strtotime( '-90 days' ) ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Last 3 Months', 'affiliates-for-woocommerce' ),
					];
				case '180_days':
					return [
						'type'  => '180_days',
						'from'  => date( 'Y-m-d', strtotime( '-180 days' ) ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Last 6 Months', 'affiliates-for-woocommerce' ),
					];
				case 'year_to_date':
					return [
						'type'  => 'year_to_date',
						'from'  => current_time( 'Y-01-01' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Year to Date', 'affiliates-for-woocommerce' ),
					];
				case 'last_year':
					return [
						'type'  => 'last_year',
						'from'  => date( 'Y-01-01', strtotime( '-1 year' ) ),
						'to'    => date( 'Y-12-31', strtotime( '-1 year' ) ),
						'label' => esc_html__( 'Last Year', 'affiliates-for-woocommerce' ),
					];
				case 'all_time':
					return [
						'type'  => 'all_time',
						'from'  => '2020-01-01', // Synced start date
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'All Time', 'affiliates-for-woocommerce' ),
					];
				case 'custom':
					$from = ! empty( $_GET['from_date'] ) ? sanitize_text_field( wp_unslash( $_GET['from_date'] ) ) : current_time( 'Y-m-d', false, strtotime( '-30 days' ) );
					$to   = ! empty( $_GET['to_date'] ) ? sanitize_text_field( wp_unslash( $_GET['to_date'] ) ) : current_time( 'Y-m-d' );
					return [
						'type'  => 'custom',
						'from'  => $from,
						'to'    => $to,
						'label' => sprintf( esc_html__( '%s to %s', 'affiliates-for-woocommerce' ), $from, $to ),
					];
				default:
					return [
						'type'  => '30_days',
						'from'  => current_time( 'Y-m-01' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'This Month', 'affiliates-for-woocommerce' ),
					];
			}
		}
	}
}
