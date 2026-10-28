<?php
/**
 * Dashboard helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Affiliate;

use Automattic\WooCommerce\Utilities\OrderUtil;
use DevDiggers\Framework\Includes\DDFW_Dashboard_Data;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Dashboard_Helper' ) ) {
	/**
	 * Dashboard helper class
	 */
	class DDWCAF_Dashboard_Helper {
		/**
		 * Commissions that count as earned, as on the Pro Reports page.
		 */
		const EARNING = "status NOT IN ('cancelled','refunded','flagged','rejected')";

		/**
		 * Option whose value is part of every cache key; moving it on drops every cached dashboard.
		 * Same option as the Pro Reports cache, so either plugin's writes drop both.
		 */
		const CACHE_VERSION = '_ddwcaf_reports_cache_version';

		/**
		 * The site a visit came from: the referrer's host, without "www.", or '' when there was none.
		 */
		const REFERRER_HOST = "TRIM(LEADING 'www.' FROM LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(COALESCE(referrer_url, ''), '://', -1), '/', 1), '?', 1), ':', 1)))";

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
		 * Dashboard figures of a date range, with the same number of days before it for the cards' change.
		 * Every figure is defined as on Affiliates > Reports, so the two pages agree.
		 *
		 * @param int|null   $affiliate_id One affiliate ( their admin page ), or the whole program.
		 * @param array|null $date_range   [ 'from' => Y-m-d, 'to' => Y-m-d ]; the date filter's range by default.
		 * @return array
		 */
		public function get_dashboard_data( $affiliate_id = null, $date_range = null ) {
			$date_range   = $date_range ?: $this->get_date_range();
			$affiliate_id = absint( $affiliate_id );

			// ponytail: the plugin-table figures share the Reports cache ( dropped on any commission or payout write,
			// checked against the newest visit ). Orders and users are not watched, so those figures are read fresh.
			$latest_visit = (int) $this->wpdb->get_var( "SELECT MAX(id) FROM {$this->wpdb->ddwcaf_visits}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Primary key lookup.
			$cache_key    = 'ddwcaf_dashboard_' . md5( wp_json_encode( [ $affiliate_id, $date_range['from'], $date_range['to'], get_option( self::CACHE_VERSION ), get_locale() ] ) );
			$cached       = get_transient( $cache_key );

			if ( is_array( $cached ) && isset( $cached['visit'], $cached['data'] ) && $latest_visit === $cached['visit'] ) {
				$data = $cached['data'];
			} else {
				$data = $this->get_program_data( $date_range['from'], $date_range['to'], $affiliate_id );
				set_transient(
					$cache_key,
					[
						'visit' => $latest_visit,
						'data'  => $data,
					],
					HOUR_IN_SECONDS
				);
			}

			// Store-wide figures, only the main Dashboard shows them.
			if ( ! $affiliate_id ) {
				$store = DDFW_Dashboard_Data::build_time_series( $date_range['from'], $date_range['to'], [ 'value' => $this->get_store_revenue( $date_range['from'], $date_range['to'] ) ] );

				$data['charts']['revenue_impact']['total'] = array_map(
					function ( $row ) {
						return [
							'period' => $row['date'],
							'value'  => $row['value'],
						];
					},
					$store
				);
				$data['summary'] += $this->get_affiliate_counts( $data['previous_from'], $date_range['from'], $date_range['to'] );
			}

			$data['date_range'] = $date_range;

			return $data;
		}

		/**
		 * The figures read from the plugin's own tables: one query per table covers both periods by day,
		 * for the cards and the charts.
		 *
		 * @param string $from         Range start ( Y-m-d ).
		 * @param string $to           Range end ( Y-m-d ).
		 * @param int    $affiliate_id Affiliate, or 0.
		 * @return array
		 */
		protected function get_program_data( $from, $to, $affiliate_id ) {
			// Plain calendar days in UTC, like Reports: stepping a day never skips one.
			$utc       = new \DateTimeZone( 'UTC' );
			$after     = new \DateTimeImmutable( $from, $utc );
			$before    = new \DateTimeImmutable( $to, $utc );
			$prev_to   = $after->modify( '-1 day' );
			$prev_from = $prev_to->modify( '-' . (int) $after->diff( $before )->days . ' days' );
			$both      = [ $prev_from->format( 'Y-m-d 00:00:00' ), $before->format( 'Y-m-d 23:59:59' ) ];
			$current   = [ $after->format( 'Y-m-d 00:00:00' ), $before->format( 'Y-m-d 23:59:59' ) ];
			$earning   = self::EARNING;
			$where     = function ( $column, $range ) use ( $affiliate_id ) {
				$sql = $this->wpdb->prepare( "{$column} BETWEEN %s AND %s", $range[0], $range[1] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed column names.

				return $affiliate_id ? $sql . $this->wpdb->prepare( ' AND affiliate_id = %d', $affiliate_id ) : $sql;
			};

			$commissions = $this->results(
				"SELECT DATE(created_at) AS day,
					SUM(CASE WHEN {$earning} THEN commission ELSE 0 END) AS earnings,
					SUM(CASE WHEN {$earning} THEN line_total ELSE 0 END) AS sales,
					SUM({$earning}) AS commissions,
					SUM(CASE WHEN status IN ('pending','pending_payment') THEN commission ELSE 0 END) AS pending
				FROM {$this->wpdb->ddwcaf_commissions}
				WHERE {$where( 'created_at', $both )}
				GROUP BY day"
			);
			$visits      = $this->results(
				"SELECT DATE(date) AS day, COUNT(*) AS visits, SUM(order_id > 0) AS conversions
				FROM {$this->wpdb->ddwcaf_visits}
				WHERE {$where( 'date', $both )}
				GROUP BY day"
			);
			$payouts     = $this->results(
				"SELECT DATE(COALESCE(completed_at, created_at)) AS day, SUM(amount) AS payouts
				FROM {$this->wpdb->ddwcaf_payouts}
				WHERE status = 'completed' AND {$where( 'COALESCE(completed_at, created_at)', $both )}
				GROUP BY day"
			);

			$totals = [
				'current'  => [],
				'previous' => [],
			];

			foreach ( array_merge( $commissions, $visits, $payouts ) as $row ) {
				$period = $row['day'] >= $after->format( 'Y-m-d' ) ? 'current' : 'previous';

				foreach ( $row as $field => $value ) {
					if ( 'day' !== $field ) {
						$totals[ $period ][ $field ] = ( $totals[ $period ][ $field ] ?? 0 ) + (float) $value;
					}
				}
			}

			$figure = function ( $period, $field ) use ( $totals ) {
				$value = $totals[ $period ][ $field ] ?? 0;

				return in_array( $field, [ 'earnings', 'sales', 'pending', 'payouts' ], true ) ? round( $value, wc_get_price_decimals() ) : (int) $value;
			};
			$metric = function ( $current, $previous ) {
				return [
					'value'       => $current,
					'change'      => $this->calculate_change( $current, $previous ),
					'is_positive' => $current >= $previous,
					'previous'    => $previous,
				];
			};
			$rate   = function ( $period ) use ( $figure ) {
				return $figure( $period, 'visits' ) ? round( $figure( $period, 'conversions' ) / $figure( $period, 'visits' ) * 100, 1 ) : 0;
			};
			$avg    = function ( $period ) use ( $figure ) {
				return $figure( $period, 'commissions' ) ? $figure( $period, 'earnings' ) / $figure( $period, 'commissions' ) : 0;
			};
			$series = DDFW_Dashboard_Data::build_time_series(
				$from,
				$to,
				[
					'earnings' => array_column( $commissions, 'earnings', 'day' ),
					'sales'    => array_column( $commissions, 'sales', 'day' ),
					'visits'   => array_column( $visits, 'visits', 'day' ),
				]
			);
			$list   = function ( $key ) use ( $series ) {
				return array_map(
					function ( $row ) use ( $key ) {
						return [
							'period' => $row['date'],
							'value'  => $row[ $key ],
						];
					},
					$series
				);
			};

			$summary = [];
			foreach ( [ 'total_earnings' => 'earnings', 'commissions' => 'commissions', 'total_sales' => 'sales', 'visits' => 'visits', 'pending_earnings' => 'pending', 'total_payouts' => 'payouts' ] as $key => $field ) {
				$summary[ $key ] = $metric( $figure( 'current', $field ), $figure( 'previous', $field ) );
			}
			$summary['conversion_rate']      = $metric( $rate( 'current' ), $rate( 'previous' ) );
			$summary['avg_commission_value'] = $metric( $avg( 'current' ), $avg( 'previous' ) );

			return [
				'summary'            => $summary,
				'previous_from'      => $prev_from->format( 'Y-m-d' ),
				'charts'             => [
					'performance'        => [
						'earnings' => $list( 'earnings' ),
						'visits'   => $list( 'visits' ),
					],
					'revenue_impact'     => [
						'affiliate' => $list( 'sales' ),
						'total'     => [],
					],
					'conversion_sources' => $this->get_conversion_sources( $where( 'date', $current ) ),
				],
				'recent_commissions' => $this->results(
					"SELECT c.*, u.display_name, u.user_email
					FROM {$this->wpdb->ddwcaf_commissions} c
					LEFT JOIN {$this->wpdb->users} u ON c.affiliate_id = u.ID" .
					( $affiliate_id ? $this->wpdb->prepare( ' WHERE c.affiliate_id = %d', $affiliate_id ) : '' ) . '
					ORDER BY c.created_at DESC, c.id DESC LIMIT 5'
				),
				'top_affiliates'     => $this->results(
					"SELECT u.ID, u.display_name, u.user_email, SUM(c.commission) AS total_earnings
					FROM {$this->wpdb->ddwcaf_commissions} c
					JOIN {$this->wpdb->users} u ON u.ID = c.affiliate_id
					WHERE {$earning} AND {$where( 'c.created_at', $current )}
					GROUP BY u.ID ORDER BY total_earnings DESC LIMIT 5"
				),
			];
		}

		/**
		 * Orders that came through a referral link, by the site the visitor came from.
		 *
		 * @param string $where Prepared range and affiliate condition on the visits table.
		 * @return array [ [ 'source' => host, 'count' => orders ], ... ], ten at most.
		 */
		protected function get_conversion_sources( $where ) {
			$host = self::REFERRER_HOST;

			return array_map(
				function ( $row ) {
					return [
						'source' => '' !== $row['host'] ? $row['host'] : esc_html__( 'Direct / Unknown', 'affiliates-for-woocommerce' ),
						'count'  => (int) $row['count'],
					];
				},
				$this->results(
					"SELECT {$host} AS host, COUNT(*) AS count
					FROM {$this->wpdb->ddwcaf_visits}
					WHERE order_id > 0 AND {$where}
					GROUP BY host ORDER BY count DESC LIMIT 10"
				)
			);
		}

		/**
		 * Completed and processing order totals by the site's local day.
		 *
		 * @param string $from Range start ( Y-m-d ).
		 * @param string $to   Range end ( Y-m-d ).
		 * @return array Map of Y-m-d => total.
		 */
		protected function get_store_revenue( $from, $to ) {
			$timezone = wp_timezone();
			$start    = new \DateTimeImmutable( $from . ' 00:00:00', $timezone );
			$end      = new \DateTimeImmutable( $to . ' 23:59:59', $timezone );

			if ( class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// Order tables keep only the GMT time: each order moves by the site's offset at that moment,
				// which daylight saving changes ( a fixed-offset site has no transitions ).
				$shift = (string) $start->getOffset();
				foreach ( array_slice( (array) $timezone->getTransitions( $start->getTimestamp(), $end->getTimestamp() ), 1 ) as $transition ) {
					$shift = $this->wpdb->prepare( 'IF(date_created_gmt < %s, ', gmdate( 'Y-m-d H:i:s', $transition['ts'] ) ) . $shift . ', ' . (int) $transition['offset'] . ')';
				}

				$rows = $this->results(
					$this->wpdb->prepare(
						"SELECT DATE(DATE_ADD(date_created_gmt, INTERVAL {$shift} SECOND)) AS day, SUM(total_amount) AS total
						FROM {$this->wpdb->prefix}wc_orders
						WHERE type = 'shop_order' AND status IN ('wc-completed','wc-processing') AND date_created_gmt BETWEEN %s AND %s
						GROUP BY day",
						gmdate( 'Y-m-d H:i:s', $start->getTimestamp() ),
						gmdate( 'Y-m-d H:i:s', $end->getTimestamp() )
					)
				);
			} else {
				$rows = $this->results(
					$this->wpdb->prepare(
						"SELECT DATE(p.post_date) AS day, SUM(m.meta_value) AS total
						FROM {$this->wpdb->posts} p
						JOIN {$this->wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_order_total'
						WHERE p.post_type = 'shop_order' AND p.post_status IN ('wc-completed','wc-processing') AND p.post_date BETWEEN %s AND %s
						GROUP BY day",
						$start->format( 'Y-m-d H:i:s' ),
						$end->format( 'Y-m-d H:i:s' )
					)
				);
			}

			return array_column( $rows, 'total', 'day' );
		}

		/**
		 * Affiliates who joined in each period, and those approved right now.
		 *
		 * @param string $prev_from Previous period start ( Y-m-d ).
		 * @param string $from      Range start ( Y-m-d ).
		 * @param string $to        Range end ( Y-m-d ).
		 * @return array Summary cards 'new_affiliates' and 'active_affiliates'.
		 */
		protected function get_affiliate_counts( $prev_from, $from, $to ) {
			// Registration times are stored in GMT; each period runs up to the next one's first second.
			$start = get_gmt_from_date( $from . ' 00:00:00' );
			$row   = $this->wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$this->wpdb->prepare(
					"SELECT SUM(u.user_registered >= %s AND u.user_registered < %s) AS current,
						SUM(u.user_registered >= %s AND u.user_registered < %s) AS previous,
						SUM(s.meta_value = 'approved') AS active
					FROM {$this->wpdb->users} u
					JOIN {$this->wpdb->usermeta} r ON r.user_id = u.ID AND r.meta_key = %s AND r.meta_value LIKE %s
					LEFT JOIN {$this->wpdb->usermeta} s ON s.user_id = u.ID AND s.meta_key = '_ddwcaf_affiliate_status'",
					$start,
					get_gmt_from_date( gmdate( 'Y-m-d', strtotime( $to . ' +1 day' ) ) . ' 00:00:00' ),
					get_gmt_from_date( $prev_from . ' 00:00:00' ),
					$start,
					$this->wpdb->get_blog_prefix() . 'capabilities',
					'%' . $this->wpdb->esc_like( 'ddwcaf_affiliate' ) . '%'
				),
				ARRAY_A
			);

			$current  = (int) ( $row['current'] ?? 0 );
			$previous = (int) ( $row['previous'] ?? 0 );

			return [
				'new_affiliates'    => [
					'value'       => $current,
					'change'      => $this->calculate_change( $current, $previous ),
					'is_positive' => $current >= $previous,
					'previous'    => $previous,
				],
				'active_affiliates' => [
					'value' => (int) ( $row['active'] ?? 0 ),
				],
			];
		}

		/**
		 * Note a write to the commissions or payouts tables, or a visit changed or deleted, so cached
		 * figures are dropped at the end of the request. The plugin writes these tables from many
		 * places with no action of its own, so the writes themselves are watched. New visits are
		 * caught by the cache key instead, so a busy site does not flush on every click.
		 *
		 * @param string $query SQL.
		 * @return string
		 */
		public static function ddwcaf_watch_writes( $query ) {
			static $flushing = false;

			if ( $flushing || false === stripos( $query, 'ddwcaf_' ) ) {
				return $query;
			}

			// Built per call, not kept: the prefix changes after switch_to_blog() on multisite.
			global $wpdb;
			$table   = preg_quote( $wpdb->prefix, '/' ) . 'ddwcaf_';
			$pattern = "/^\\s*(?:(?:INSERT|UPDATE|DELETE|REPLACE)\\b.*?\\b{$table}(?:commissions|payouts)|(?:UPDATE|DELETE)\\b.*?\\b{$table}visits)(?![a-z_])/is";

			if ( preg_match( $pattern, $query ) ) {
				$flushing = true;
				add_action( 'shutdown', [ __CLASS__, 'ddwcaf_flush_cache' ] );
			}

			return $query;
		}

		/**
		 * Drop every cached dashboard.
		 *
		 * @return void
		 */
		public static function ddwcaf_flush_cache() {
			update_option( self::CACHE_VERSION, (string) microtime( true ), false );
		}

		/**
		 * Results of a query as arrays.
		 *
		 * @param string $sql Prepared SQL.
		 * @return array
		 */
		protected function results( $sql ) {
			return (array) $this->wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Built from prepared parts.
		}

		/**
		 * Calculate percentage change
		 */
		protected function calculate_change( $current, $prev ) {
			// A jump from nothing is not a percentage: null makes the card say "None in the previous period".
			if ( $prev == 0 ) {
				return $current > 0 ? null : 0;
			}
			return round( ( ( $current - $prev ) / $prev ) * 100, 1 );
		}

		/**
		 * Get date range
		 */
		public function get_date_range() {
			$range = DDFW_Dashboard_Data::get_date_range();

			// Preserve the legacy 'type' alias used internally by this helper.
			$range['type'] = $range['key'];

			return $range;
		}
	}
}
