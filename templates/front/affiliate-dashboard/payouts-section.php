<?php
/**
 * Payouts Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

if ( is_account_page() ) {
    $current_page = preg_replace( "/[^0-9]/", '', $wp->query_vars[ $ddwcaf_configuration[ 'my_account_endpoint' ] ] );
    $current_page = ! empty( $current_page ) ? intval( $current_page ) : 1;
} else {
    $current_page = ! empty( $wp->query_vars[ $endpoints[ 'payouts' ][ 'endpoint' ] ] ) ? intval( $wp->query_vars[ $endpoints[ 'payouts' ][ 'endpoint' ] ] ) : 1;
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

$payout_helper    = new DDWCAF_Payout_Helper( $ddwcaf_configuration );
$affiliate_helper = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
$total_count      = $payout_helper->ddwcaf_get_payouts_count( $args );
$payouts          = $payout_helper->ddwcaf_get_payouts( $args );

$shows = [
    ''          => esc_html__( 'All', 'affiliates-for-woocommerce' ),
    'pending'   => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
    'completed' => esc_html__( 'Completed', 'affiliates-for-woocommerce' ),
    'cancelled' => esc_html__( 'Cancelled', 'affiliates-for-woocommerce' ),
];

?>
<div class="ddwcaf-list-wrapper">
    <div class="ddwcaf-bulk-actions">
        <form method="get">
            <select name="show" class="form-control">
                <?php
                foreach ( $shows as $key => $value ) {
                    ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key === $args[ 'show' ] ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $value ); ?></option>
                    <?php
                }
                ?>
            </select>

            <label for="from-date"><?php esc_html_e( 'From:', 'affiliates-for-woocommerce' ); ?></label>
            <input type="date" class="form-control" value="<?php echo esc_attr( $args[ 'from_date' ] ); ?>" name="from-date" id="from-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

            <label for="end-date"><?php esc_html_e( 'To:', 'affiliates-for-woocommerce' ); ?></label>
            <input type="date" class="form-control" value="<?php echo esc_attr( $args[ 'end_date' ] ); ?>" name="end-date" id="end-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

            <input type="submit" value="<?php esc_attr_e( 'Filter', 'affiliates-for-woocommerce' ); ?>" name="ddwcaf_filter_submit" class="button" />
        </form>
    </div>

    <table class="shop_table_responsive ddwcaf-table ddwcaf-commissions-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Payment Method', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Reference', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Created On', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Completed On', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Status', 'affiliates-for-woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $payouts ) ) {
                foreach ( $payouts as $key => $payout ) {
                    $affiliate_id   = $payout[ 'affiliate_id' ];
                    $affiliate_data = get_userdata( $affiliate_id );
                    $date_format    = get_option( 'date_format' );
                    $time_format    = get_option( 'time_format' );
                    ?>
                    <tr>
                        <td><?php echo esc_html( '#' . $payout[ 'id' ] ); ?></td>
                        <td>
                            <p><strong><?php echo esc_html( $affiliate_helper->ddwcaf_get_withdrawal_method_name( $payout[ 'payment_method' ] ) ); ?></strong></p>
                            <small>
                                <?php
                                if ( 'bacs' === $payout[ 'payment_method' ] ) {
                                    $payment_info = maybe_unserialize( $payout[ 'payment_info' ] );
                                    echo sprintf( esc_html__( 'Account Name: %s', 'affiliates-for-woocommerce' ), $payment_info[ 'account_name' ] ) . '<br />';
                                    echo sprintf( esc_html__( 'IBAN: %s', 'affiliates-for-woocommerce' ), $payment_info[ 'iban' ] ) . '<br />';
                                    echo sprintf( esc_html__( 'Swift Code: %s', 'affiliates-for-woocommerce' ), $payment_info[ 'swift_code' ] ) . '<br />';
                                } else {
                                    echo esc_html( ! empty( $payment_info ) ? $payment_info : 'N/A' );
                                }
                                ?>
                            </small>
                        </td>
                        <td><?php echo wc_price( $payout[ 'amount' ] ); ?></td>
                        <td><?php echo esc_html( $payout_helper->ddwcaf_get_references( $payout[ 'reference' ] ) ); ?></td>
                        <td><?php echo esc_html( date_i18n( "{$date_format} {$time_format}", strtotime( $payout[ 'created_at' ] ) ) ); ?></td>
                        <td><?php echo esc_html( ! empty( $payout[ 'completed_at' ] ) ? date_i18n( "{$date_format} {$time_format}", strtotime( $payout[ 'completed_at' ] ) ) : 'N/A' ); ?></td>
                        <td>
                            <mark class="ddwcaf-status ddwcaf-commission-status-<?php echo esc_attr( $payout[ 'status' ] ); ?>"><span><?php echo esc_html( $payout_helper->ddwcaf_get_translation( $payout[ 'status' ] ) ); ?></span></mark>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="8"><center><?php esc_html_e( 'No payouts yet.', 'affiliates-for-woocommerce' ); ?></center></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>
<?php
if ( 1 < $total_count ) {
    ?>
    <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination ddwcaf-pagination">
        <?php
        if ( 1 !== $current_page && $current_page > 1 ) {
            ?>
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'payouts' ][ 'endpoint' ], $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        if ( ceil( $total_count / $per_page ) > $current_page ) {
            ?>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'payouts' ][ 'endpoint' ], $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        ?>
    </div>
    <?php
}
