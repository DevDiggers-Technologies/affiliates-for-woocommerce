<?php
/**
 * Dashboard self-test: rebuilds every Dashboard card row by row from the plugin's tables ( no SQL
 * sums ) and compares. Also checks the cache, the list status counts and the date filters.
 * Changes nothing it does not put back.
 *
 * Run: wp eval-file wp-content/plugins/affiliates-for-woocommerce/bin/dashboard-test.php --user=<admin login>
 *
 * @package Affiliates for WooCommerce
 */

defined( 'ABSPATH' ) || exit();

global $ddwcaf_fails, $ddwcaf_checks; // eval-file runs inside a function.
$ddwcaf_fails  = 0;
$ddwcaf_checks = 0;

/**
 * Compare two values ( numbers within half a cent ).
 *
 * @param string $label    Label.
 * @param mixed  $actual   Plugin value.
 * @param mixed  $expected Rebuilt value.
 * @return void
 */
function ddwcaf_dt_check( $label, $actual, $expected ) {
	global $ddwcaf_fails, $ddwcaf_checks;
	++$ddwcaf_checks;

	$ok = is_numeric( $actual ) && is_numeric( $expected ) ? abs( (float) $actual - (float) $expected ) < 0.006 : $actual === $expected;

	if ( ! $ok ) {
		++$ddwcaf_fails;
		WP_CLI::warning( sprintf( '%s: plugin %s, rebuilt %s', $label, wp_json_encode( $actual ), wp_json_encode( $expected ) ) );
	}
}

global $wpdb;
$commissions = $wpdb->get_results( "SELECT * FROM {$wpdb->ddwcaf_commissions}", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$visits      = $wpdb->get_results( "SELECT * FROM {$wpdb->ddwcaf_visits}", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$payouts     = $wpdb->get_results( "SELECT * FROM {$wpdb->ddwcaf_payouts}", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

if ( ! $commissions ) {
	WP_CLI::error( 'No commissions to check against.' );
}

$day     = fn( $date ) => substr( (string) $date, 0, 10 );
$earning = fn( $row ) => ! in_array( $row['status'], [ 'cancelled', 'refunded', 'flagged', 'rejected' ], true );

/**
 * Cards of one period for one affiliate ( 0 for all ), from the rows.
 */
$rebuild = function ( $from, $to, $affiliate ) use ( $commissions, $visits, $payouts, $day, $earning ) {
	$keep = fn( $row, $date ) => ( ! $affiliate || (int) $row['affiliate_id'] === $affiliate ) && $day( $date ) >= $from && $day( $date ) <= $to;
	$c    = array_filter( $commissions, fn( $row ) => $keep( $row, $row['created_at'] ) );
	$e    = array_filter( $c, $earning );
	$v    = array_filter( $visits, fn( $row ) => $keep( $row, $row['date'] ) );
	$p    = array_filter( $payouts, fn( $row ) => 'completed' === $row['status'] && $keep( $row, $row['completed_at'] ?: $row['created_at'] ) );
	$conv = count( array_filter( $v, fn( $row ) => (int) $row['order_id'] > 0 ) );

	return [
		'total_earnings'       => array_sum( array_column( $e, 'commission' ) ),
		'commissions'          => count( $e ),
		'total_sales'          => array_sum( array_column( $e, 'line_total' ) ),
		'visits'               => count( $v ),
		'pending_earnings'     => array_sum( array_map( fn( $row ) => in_array( $row['status'], [ 'pending', 'pending_payment' ], true ) ? $row['commission'] : 0, $c ) ),
		'total_payouts'        => array_sum( array_column( $p, 'amount' ) ),
		'conversion_rate'      => $v ? round( $conv / count( $v ) * 100, 1 ) : 0,
		'avg_commission_value' => $e ? array_sum( array_column( $e, 'commission' ) ) / count( $e ) : 0,
	];
};

$helper = new DDWCAffiliates\Helper\Affiliate\DDWCAF_Dashboard_Helper( [] );
$range  = [ 'from' => $day( min( array_column( $commissions, 'created_at' ) ) ), 'to' => $day( max( array_column( $commissions, 'created_at' ) ) ) ];
$top    = (int) $commissions[0]['affiliate_id'];

foreach ( [ 0, $top ] as $affiliate ) {
	$data     = $helper->get_dashboard_data( $affiliate, $range );
	$current  = $rebuild( $range['from'], $range['to'], $affiliate );
	$previous = $rebuild( $data['previous_from'], gmdate( 'Y-m-d', strtotime( $range['from'] . ' -1 day' ) ), $affiliate );

	foreach ( $current as $card => $value ) {
		ddwcaf_dt_check( "affiliate {$affiliate} {$card}", $data['summary'][ $card ]['value'], $value );
		ddwcaf_dt_check( "affiliate {$affiliate} previous {$card}", $data['summary'][ $card ]['previous'], $previous[ $card ] );
	}
	ddwcaf_dt_check( "affiliate {$affiliate} earnings chart adds up", array_sum( array_column( $data['charts']['performance']['earnings'], 'value' ) ), $current['total_earnings'] );
	ddwcaf_dt_check( "affiliate {$affiliate} visits chart adds up", array_sum( array_column( $data['charts']['performance']['visits'], 'value' ) ), $current['visits'] );
}
ddwcaf_dt_check( 'previous period is as long as the range', ( strtotime( $range['from'] ) - strtotime( $data['previous_from'] ) ) / DAY_IN_SECONDS, ( strtotime( $range['to'] ) - strtotime( $range['from'] ) ) / DAY_IN_SECONDS + 1 );
ddwcaf_dt_check( 'recent commissions', array_column( $helper->get_dashboard_data( 0, $range )['recent_commissions'], 'id' ), $wpdb->get_col( "SELECT id FROM {$wpdb->ddwcaf_commissions} ORDER BY created_at DESC, id DESC LIMIT 5" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
ddwcaf_dt_check( 'active affiliates', $helper->get_dashboard_data( 0, $range )['summary']['active_affiliates']['value'], count( get_users( [ 'role' => 'ddwcaf_affiliate', 'meta_key' => '_ddwcaf_affiliate_status', 'meta_value' => 'approved', 'fields' => 'ID' ] ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery

// Store revenue by the site's day, as WooCommerce dates each order.
$expected = [];
foreach ( wc_get_orders( [ 'type' => 'shop_order', 'status' => [ 'wc-completed', 'wc-processing' ], 'limit' => -1, 'date_created' => $range['from'] . '...' . $range['to'] ] ) as $order ) {
	$key              = wp_date( 'Y-m-d', $order->get_date_created()->getTimestamp() );
	$expected[ $key ] = ( $expected[ $key ] ?? 0 ) + $order->get_total();
}
ksort( $expected );
$actual = array_filter( array_column( $helper->get_dashboard_data( 0, $range )['charts']['revenue_impact']['total'], 'value', 'period' ) );
ddwcaf_dt_check( 'store revenue by day', array_map( fn( $value ) => round( $value, 2 ), $actual ), array_map( fn( $value ) => round( $value, 2 ), $expected ) );

// A commission write drops the cache; a new visit changes the key.
$today = [ 'from' => current_time( 'Y-m-d' ), 'to' => current_time( 'Y-m-d' ) ];
$first = $helper->get_dashboard_data( 0, $today );
$wpdb->insert( $wpdb->ddwcaf_commissions, [ 'affiliate_id' => $top, 'order_id' => 0, 'line_item_id' => 0, 'product_id' => 0, 'quantity' => 1, 'line_total' => 1000, 'commission' => 100, 'refund' => 0, 'status' => 'not_confirmed', 'created_at' => current_time( 'mysql' ), 'updated_at' => current_time( 'mysql' ) ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$test_id = $wpdb->insert_id;
do_action( 'shutdown' ); // The flush runs at the end of the request that wrote.
ddwcaf_dt_check( 'cache dropped on a commission write', $helper->get_dashboard_data( 0, $today )['summary']['total_earnings']['value'] - $first['summary']['total_earnings']['value'], 100 );
$wpdb->delete( $wpdb->ddwcaf_commissions, [ 'id' => $test_id ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->insert( $wpdb->ddwcaf_visits, [ 'affiliate_id' => $top, 'url' => home_url( '/' ), 'ip' => '192.0.2.1', 'date' => current_time( 'mysql' ) ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$test_id = $wpdb->insert_id;
ddwcaf_dt_check( 'new visit shows at once', $helper->get_dashboard_data( 0, $today )['summary']['visits']['value'] - $first['summary']['visits']['value'], 1 );
$wpdb->delete( $wpdb->ddwcaf_visits, [ 'id' => $test_id ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
DDWCAffiliates\Helper\Affiliate\DDWCAF_Dashboard_Helper::ddwcaf_flush_cache(); // Nothing cached here outlives the test rows.

// Status tabs count what their list shows; a picked end day runs to its last second.
$commission_helper = new DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper( [] );
foreach ( array_unique( array_column( $commissions, 'status' ) ) as $status ) {
	ddwcaf_dt_check( "{$status} tab count", (int) $commission_helper->ddwcaf_get_commissions_count( [ 'show' => $status ] ), count( array_filter( $commissions, fn( $row ) => $status === $row['status'] ) ) );
}
ddwcaf_dt_check( 'All tab count', (int) $commission_helper->ddwcaf_get_commissions_count( [ 'show' => '' ] ), count( $commissions ) );
ddwcaf_dt_check( 'end date includes its last day', (int) $commission_helper->ddwcaf_get_commissions_count( [ 'from_date' => $range['to'], 'end_date' => $range['to'] ] ), count( array_filter( $commissions, fn( $row ) => $day( $row['created_at'] ) === $range['to'] ) ) );
$visit_helper = new DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper( [] );
$last_visit   = $day( max( array_column( $visits, 'date' ) ) );
ddwcaf_dt_check( 'visit end date includes its last day', (int) $visit_helper->ddwcaf_get_visits_count( [ 'from_date' => $last_visit, 'end_date' => $last_visit ] ), count( array_filter( $visits, fn( $row ) => $day( $row['date'] ) === $last_visit ) ) );

// Report indexes are in place.
ddwcaf_dt_check( 'visit report index', (bool) $wpdb->get_var( "SHOW INDEX FROM {$wpdb->ddwcaf_visits} WHERE Key_name = 'report_date'" ), true ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
ddwcaf_dt_check( 'payout affiliate index', (bool) $wpdb->get_var( "SHOW INDEX FROM {$wpdb->ddwcaf_payouts} WHERE Key_name = 'affiliate_id'" ), true ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

if ( $ddwcaf_fails ) {
	WP_CLI::error( sprintf( '%d of %d checks failed.', $ddwcaf_fails, $ddwcaf_checks ) );
}

WP_CLI::success( sprintf( 'All %d checks passed.', $ddwcaf_checks ) );
