<?php
/**
 * Commissions Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

if ( is_account_page() ) {
	$current_page = preg_replace( "/[^0-9]/", '', $wp->query_vars[ $ddwcaf_configuration[ 'my_account_endpoint' ] ] );
	$current_page = ! empty( $current_page ) ? intval( $current_page ) : 1;
} else {
	$current_page = ! empty( $wp->query_vars[ $endpoints[ 'commissions' ][ 'endpoint' ] ] ) ? intval( $wp->query_vars[ $endpoints[ 'commissions' ][ 'endpoint' ] ] ) : 1;
}

$per_page = 10;
$offset   = 1 === $current_page ? 0 : ( $current_page - 1 ) * $per_page;

$args = [
	'show'         => ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '',
	'affiliate_id' => get_current_user_id(),
	'from_date'    => ! empty( $_GET[ 'from-date' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'from-date' ] ) ) : '',
	'end_date'     => ! empty( $_GET[ 'end-date' ] ) ? sanitize_text_field( wp_unslash ( $_GET[ 'end-date' ] ) ) : '',
	'per_page'     => $per_page,
	'offset'       => $offset,
];

$commission_helper       = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
$total_count             = $commission_helper->ddwcaf_get_commissions_count( $args );
$commissions             = $commission_helper->ddwcaf_get_commissions( $args );
$pending_commission_args = $args;
$total_commission_amount = $commission_helper->ddwcaf_get_affiliate_total_pending_commissions_amount( $pending_commission_args );

$shows = [
	''                => esc_html__( 'All', 'affiliates-for-woocommerce' ),
	'pending'         => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
	'pending_payment' => esc_html__( 'Pending Payment', 'affiliates-for-woocommerce' ),
	'paid'            => esc_html__( 'Paid', 'affiliates-for-woocommerce' ),
	'not_confirmed'   => esc_html__( 'Not Confirmed', 'affiliates-for-woocommerce' ),
	'cancelled'       => esc_html__( 'Cancelled', 'affiliates-for-woocommerce' ),
	'refunded'        => esc_html__( 'Refunded', 'affiliates-for-woocommerce' ),
];
?>
<div class="ddwcaf-table-container">
	<div class="ddwcaf-bulk-actions">
		<form method="get">
			<select name="show" class="form-control input-text">
				<?php
				foreach ( $shows as $key => $value ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key === $args[ 'show' ] ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $value ); ?></option>
					<?php
				}
				?>
			</select>

			<label for="from-date"><?php esc_html_e( 'From:', 'affiliates-for-woocommerce' ); ?></label>
			<input type="date" class="form-control input-text" value="<?php echo esc_attr( $args[ 'from_date' ] ); ?>" name="from-date" id="from-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

			<label for="end-date"><?php esc_html_e( 'To:', 'affiliates-for-woocommerce' ); ?></label>
			<input type="date" class="form-control input-text" value="<?php echo esc_attr( $args[ 'end_date' ] ); ?>" name="end-date" id="end-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

			<input type="submit" value="<?php esc_attr_e( 'Filter', 'affiliates-for-woocommerce' ); ?>" name="ddwcaf_filter_submit" class="button" />
		</form>
	</div>

	<div class="ddwcaf-table-loader-overlay">
		<span class="ddwcaf-table-spinner"></span>
	</div>

	<div class="ddwcaf-table-wrapper">
		<table class="my_account_orders shop_table_responsive ddwcaf-table ddwcaf-commissions-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'affiliates-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Product', 'affiliates-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Total', 'affiliates-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Refund', 'affiliates-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Commission', 'affiliates-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Date', 'affiliates-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Status', 'affiliates-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $commission_helper->ddwcaf_render_commissions_table_rows( $commissions ); ?>
			</tbody>
		</table>
		<input type="hidden" class="ddwcaf-current-page" value="<?php echo esc_attr( $current_page ); ?>" />
		<input type="hidden" class="ddwcaf-total-count" value="<?php echo esc_attr( $total_count ); ?>" />
	</div>

	<?php if ( ceil( $total_count / $per_page ) > 1 ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination ddwcaf-pagination">
			<button class="ddwcaf-pagination-button button woocommerce-Button--previous" data-table="commissions" data-perform="previous" <?php echo 1 === $current_page ? 'disabled' : ''; ?>><?php esc_html_e( 'Previous', 'affiliates-for-woocommerce' ); ?></button>
			<button class="ddwcaf-pagination-button button woocommerce-Button--next" data-table="commissions" data-perform="next" <?php echo ceil( $total_count / $per_page ) <= $current_page ? 'disabled' : ''; ?>><?php esc_html_e( 'Next', 'affiliates-for-woocommerce' ); ?></button>
		</div>
	<?php endif; ?>
</div>
