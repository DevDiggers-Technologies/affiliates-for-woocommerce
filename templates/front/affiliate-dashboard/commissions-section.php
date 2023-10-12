<?php
/**
 * Commissions Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

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

$commission_helper = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
$total_count       = $commission_helper->ddwcaf_get_commissions_count( $args );
$commissions       = $commission_helper->ddwcaf_get_commissions( $args );

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
                <th><?php esc_html_e( 'Product', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Total', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Refund', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Commission', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Date', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Status', 'affiliates-for-woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $commissions ) ) {
                $date_format = get_option( 'date_format' );
                $time_format = get_option( 'time_format' );

                foreach ( $commissions as $key => $commission ) {
                    $affiliate_id   = $commission[ 'affiliate_id' ];
                    $affiliate_data = get_userdata( $affiliate_id );
                    $order          = wc_get_order( $commission[ 'order_id' ] );
                    $order_currency = $order->get_currency();
                    $product        = wc_get_product( $commission[ 'product_id' ] );
                    ?>
                    <tr>
                        <td><?php echo esc_html( '#' . $commission[ 'id' ] ); ?></td>
                        <td class="column column-product"><a href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?><div><?php echo esc_html( $product->get_name() ); ?></div></a></td>
                        <td><?php echo wc_price( $commission[ 'line_total' ], [ 'currency' => $order_currency ] ); ?></td>
                        <td><?php echo wc_price( $commission[ 'refund' ], [ 'currency' => $order_currency ] ); ?></td>
                        <td><?php echo wc_price( $commission[ 'commission' ], [ 'currency' => $order_currency ] ); ?></td>
                        <td><?php echo esc_html( date_i18n( "{$date_format} {$time_format}", strtotime( $commission[ 'created_at' ] ) ) ); ?></td>
                        <td><mark class="ddwcaf-status ddwcaf-commission-status-<?php echo esc_attr( $commission[ 'status' ] ); ?>"><span><?php echo esc_html( $commission_helper->ddwcaf_get_translation( $commission[ 'status' ] ) ); ?></span></mark></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7"><center><?php esc_html_e( 'No commissions yet.', 'affiliates-for-woocommerce' ); ?></center></td>
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
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'commissions' ][ 'endpoint' ], $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        if ( ceil( $total_count / $per_page ) > $current_page ) {
            ?>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'commissions' ][ 'endpoint' ], $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        ?>
    </div>
    <?php
}
