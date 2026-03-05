<?php
/**
 * Top Products Section Template
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
    $current_page = ! empty( $wp->query_vars[ $endpoints[ 'top-products' ][ 'endpoint' ] ] ) ? intval( $wp->query_vars[ $endpoints[ 'top-products' ][ 'endpoint' ] ] ) : 1;
}

$per_page = 10;
$offset   = 1 === $current_page ? 0 : ( $current_page - 1 ) * $per_page;

$args = [
    'affiliate_id' => get_current_user_id(),
    'per_page'     => $per_page,
    'offset'       => $offset,
    'order'        => 'desc',
    'orderby'      => 'quantity',
];

$commission_helper = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
$total_count       = $commission_helper->ddwcaf_get_top_products_count( $args );
$top_products      = $commission_helper->ddwcaf_get_top_products( $args );

?>
<div class="ddwcaf-table-container">
    <div class="ddwcaf-table-loader-overlay">
        <span class="ddwcaf-table-spinner"></span>
    </div>

    <div class="ddwcaf-table-wrapper">
        <table class="my_account_orders shop_table_responsive ddwcaf-table ddwcaf-commissions-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'affiliates-for-woocommerce' ); ?></th>
                    <th><?php esc_html_e( 'Quantity', 'affiliates-for-woocommerce' ); ?></th>
                    <th><?php esc_html_e( 'Total Commisions', 'affiliates-for-woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $commission_helper->ddwcaf_render_top_products_table_rows( $top_products ); ?>
            </tbody>
        </table>
        <input type="hidden" class="ddwcaf-current-page" value="<?php echo esc_attr( $current_page ); ?>" />
        <input type="hidden" class="ddwcaf-total-count" value="<?php echo esc_attr( $total_count ); ?>" />
    </div>

    <?php if ( ceil( $total_count / $per_page ) > 1 ) : ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination ddwcaf-pagination">
            <button class="ddwcaf-pagination-button button woocommerce-Button--previous" data-table="top_products" data-perform="previous" <?php echo 1 === $current_page ? 'disabled' : ''; ?>><?php esc_html_e( 'Previous', 'affiliates-for-woocommerce' ); ?></button>
            <button class="ddwcaf-pagination-button button woocommerce-Button--next" data-table="top_products" data-perform="next" <?php echo ceil( $total_count / $per_page ) <= $current_page ? 'disabled' : ''; ?>><?php esc_html_e( 'Next', 'affiliates-for-woocommerce' ); ?></button>
        </div>
    <?php endif; ?>
</div>
