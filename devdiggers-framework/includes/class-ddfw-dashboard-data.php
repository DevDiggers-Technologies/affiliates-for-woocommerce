<?php
/**
 * File for shared DevDiggers dashboard data helpers.
 *
 * Provides plugin-agnostic helpers that every DevDiggers analytics dashboard reuses,
 * so individual plugins do not duplicate this logic. The most important is the
 * date-range resolver that powers the shared dashboard date filter.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Dashboard_Data' ) ) {
	/**
	 * Class for shared dashboard data helpers.
	 */
	class DDFW_Dashboard_Data {
		/**
		 * Resolve the active dashboard date range from the request.
		 *
		 * Reads the `date_range` (and `from_date`/`to_date` for custom) request parameters
		 * and returns a normalized range. This is the single source of truth for both the
		 * rendered date filter (handled by DDFW_Dashboard) and the SQL scoping done inside
		 * each plugin's dashboard helper, guaranteeing they always match.
		 *
		 * Routing/read-only parameters; no state change, so nonce verification is not required.
		 *
		 * @param string $default        Default range key when none is provided in the request.
		 * @param string $all_time_start Start date used for the "all_time" range.
		 * @return array{from:string,to:string,label:string,key:string}
		 */
		public static function get_date_range( $default = '30_days', $all_time_start = '2020-01-01' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only dashboard filter parameter.
			$range = ! empty( $_GET['date_range'] ) ? sanitize_text_field( wp_unslash( $_GET['date_range'] ) ) : $default;

			switch ( $range ) {
				case 'today':
					$data = [
						'from'  => current_time( 'Y-m-d' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Today', 'affiliates-for-woocommerce' ),
					];
					break;
				case '7_days':
					$data = [
						'from'  => gmdate( 'Y-m-d', strtotime( 'monday this week' ) ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'This Week', 'affiliates-for-woocommerce' ),
					];
					break;
				case 'last_week':
					$data = [
						'from'  => gmdate( 'Y-m-d', strtotime( 'monday last week' ) ),
						'to'    => gmdate( 'Y-m-d', strtotime( 'sunday last week' ) ),
						'label' => esc_html__( 'Last Week', 'affiliates-for-woocommerce' ),
					];
					break;
				case '30_days':
					$data = [
						'from'  => current_time( 'Y-m-01' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'This Month', 'affiliates-for-woocommerce' ),
					];
					break;
				case 'last_month':
					$data = [
						'from'  => gmdate( 'Y-m-01', strtotime( 'first day of last month' ) ),
						'to'    => gmdate( 'Y-m-t', strtotime( 'last day of last month' ) ),
						'label' => esc_html__( 'Last Month', 'affiliates-for-woocommerce' ),
					];
					break;
				case '90_days':
					$data = [
						'from'  => gmdate( 'Y-m-d', strtotime( '-90 days' ) ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Last 3 Months', 'affiliates-for-woocommerce' ),
					];
					break;
				case '180_days':
					$data = [
						'from'  => gmdate( 'Y-m-d', strtotime( '-180 days' ) ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Last 6 Months', 'affiliates-for-woocommerce' ),
					];
					break;
				case 'year_to_date':
					$data = [
						'from'  => current_time( 'Y-01-01' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'Year to Date', 'affiliates-for-woocommerce' ),
					];
					break;
				case 'last_year':
					$data = [
						'from'  => gmdate( 'Y-01-01', strtotime( '-1 year' ) ),
						'to'    => gmdate( 'Y-12-31', strtotime( '-1 year' ) ),
						'label' => esc_html__( 'Last Year', 'affiliates-for-woocommerce' ),
					];
					break;
				case 'all_time':
					$data = [
						'from'  => $all_time_start,
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'All Time', 'affiliates-for-woocommerce' ),
					];
					break;
				case 'custom':
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only dashboard filter parameter.
					$from = ! empty( $_GET['from_date'] ) ? sanitize_text_field( wp_unslash( $_GET['from_date'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only dashboard filter parameter.
					$to   = ! empty( $_GET['to_date'] ) ? sanitize_text_field( wp_unslash( $_GET['to_date'] ) ) : current_time( 'Y-m-d' );
					$data = [
						'from'  => $from,
						'to'    => $to,
						/* translators: 1: from date, 2: to date. */
						'label' => sprintf( esc_html__( '%1$s to %2$s', 'affiliates-for-woocommerce' ), $from, $to ),
					];
					break;
				default:
					$range = '30_days';
					$data  = [
						'from'  => current_time( 'Y-m-01' ),
						'to'    => current_time( 'Y-m-d' ),
						'label' => esc_html__( 'This Month', 'affiliates-for-woocommerce' ),
					];
					break;
			}

			$data['key'] = $range;

			return $data;
		}

		/**
		 * Resolve the chart grouping interval for a date range.
		 *
		 * Single source of truth for dashboard granularity across all plugins:
		 * up to 90 days -> daily, up to a year -> monthly, longer -> quarterly. This matches the
		 * x-axis label format produced by the JS engine ( ChartUtils.getDateFormat ).
		 *
		 * @param string $from Range start ( Y-m-d ).
		 * @param string $to   Range end ( Y-m-d ).
		 * @return string 'day' | 'month' | 'quarter'.
		 */
		public static function get_interval( $from, $to ) {
			$days = floor( ( strtotime( $to ) - strtotime( $from ) ) / DAY_IN_SECONDS );

			if ( $days <= 90 ) {
				return 'day';
			}

			if ( $days <= 365 ) {
				return 'month';
			}

			return 'quarter';
		}

		/**
		 * Normalize a date to the display key for its bucket at the given interval.
		 *
		 * Always returns a real Y-m-d date ( first of month / first of quarter ) so the value
		 * parses cleanly as a Date in the JS chart engine.
		 *
		 * @param string|int $date     Date string or timestamp.
		 * @param string     $interval 'day' | 'month' | 'quarter'.
		 * @return string Y-m-d display key.
		 */
		public static function get_period_key( $date, $interval ) {
			$ts = is_numeric( $date ) ? (int) $date : strtotime( $date );

			switch ( $interval ) {
				case 'month':
					return gmdate( 'Y-m-01', $ts );
				case 'quarter':
					$quarter      = (int) ceil( (int) gmdate( 'n', $ts ) / 3 );
					$first_month  = ( $quarter - 1 ) * 3 + 1;
					return gmdate( 'Y', $ts ) . '-' . str_pad( (string) $first_month, 2, '0', STR_PAD_LEFT ) . '-01';
				default:
					return gmdate( 'Y-m-d', $ts );
			}
		}

		/**
		 * Build the SQL GROUP BY expression for the given interval.
		 *
		 * For helpers that aggregate in SQL. The caller controls $date_column ( a trusted column
		 * name, never user input ), so the expression is safe to interpolate.
		 *
		 * @param string $interval    'day' | 'month' | 'quarter'.
		 * @param string $date_column Column name to group on. Default 'date'.
		 * @return string SQL expression.
		 */
		public static function get_sql_group_by( $interval, $date_column = 'date' ) {
			switch ( $interval ) {
				case 'month':
					return "DATE_FORMAT({$date_column}, '%Y-%m')";
				case 'quarter':
					return "CONCAT(YEAR({$date_column}), '-Q', QUARTER({$date_column}))";
				default:
					return "DATE({$date_column})";
			}
		}

		/**
		 * Bucket and zero-fill one or more date-keyed value maps into an ordered chart series.
		 *
		 * Shared replacement for the per-plugin grouping/zero-fill loops. Each entry in $series is a
		 * map of date ( Y-m-d string or timestamp ) => numeric value; values falling in the same
		 * bucket are summed. The result is one ordered row per bucket with every series key present
		 * ( missing buckets filled with 0 ), ready to pass straight into a DDFW_Dashboard chart.
		 *
		 * @param string $from   Range start ( Y-m-d ).
		 * @param string $to     Range end ( Y-m-d ).
		 * @param array  $series Map of series_key => ( map of date|timestamp => value ).
		 * @return array List of rows: [ [ 'date' => Y-m-d, <series_key> => number, ... ], ... ].
		 */
		public static function build_time_series( $from, $to, array $series ) {
			$interval    = self::get_interval( $from, $to );
			$series_keys = array_keys( $series );
			$buckets     = [];

			// Seed ordered, zero-filled buckets across the whole range.
			$cursor = strtotime( $from );
			$end    = strtotime( $to );
			while ( $cursor <= $end ) {
				$key = self::get_period_key( gmdate( 'Y-m-d', $cursor ), $interval );
				if ( ! isset( $buckets[ $key ] ) ) {
					$row = [ 'date' => $key ];
					foreach ( $series_keys as $series_key ) {
						$row[ $series_key ] = 0;
					}
					$buckets[ $key ] = $row;
				}
				$cursor = strtotime( '+1 day', $cursor );
			}

			$from_ts = strtotime( $from . ' 00:00:00' );
			$to_ts   = strtotime( $to . ' 23:59:59' );

			// Accumulate each series into its buckets.
			foreach ( $series as $series_key => $map ) {
				if ( ! is_array( $map ) ) {
					continue;
				}
				foreach ( $map as $date => $value ) {
					$ts = is_numeric( $date ) ? (int) $date : strtotime( $date );
					if ( false === $ts || $ts < $from_ts || $ts > $to_ts ) {
						continue;
					}
					$key = self::get_period_key( gmdate( 'Y-m-d', $ts ), $interval );
					if ( isset( $buckets[ $key ] ) ) {
						$buckets[ $key ][ $series_key ] += (float) $value;
					}
				}
			}

			ksort( $buckets );

			return array_values( $buckets );
		}

		/**
		 * Standard quick-select presets used by the shared dashboard date filter.
		 *
		 * @return array<string,string> Map of range key => translated label.
		 */
		public static function get_presets() {
			return [
				'today'        => esc_html__( 'Today', 'affiliates-for-woocommerce' ),
				'7_days'       => esc_html__( 'This Week', 'affiliates-for-woocommerce' ),
				'last_week'    => esc_html__( 'Last Week', 'affiliates-for-woocommerce' ),
				'30_days'      => esc_html__( 'This Month', 'affiliates-for-woocommerce' ),
				'last_month'   => esc_html__( 'Last Month', 'affiliates-for-woocommerce' ),
				'90_days'      => esc_html__( 'Last 3 Months', 'affiliates-for-woocommerce' ),
				'180_days'     => esc_html__( 'Last 6 Months', 'affiliates-for-woocommerce' ),
				'year_to_date' => esc_html__( 'Year to Date', 'affiliates-for-woocommerce' ),
				'last_year'    => esc_html__( 'Last Year', 'affiliates-for-woocommerce' ),
				'all_time'     => esc_html__( 'All Time', 'affiliates-for-woocommerce' ),
			];
		}
	}
}
