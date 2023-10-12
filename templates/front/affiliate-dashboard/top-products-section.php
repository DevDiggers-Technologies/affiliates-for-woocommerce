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
<div class="ddwcaf-list-wrapper">
    <table class="shop_table_responsive ddwcaf-table ddwcaf-commissions-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Product', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Quantity', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Total Commisions', 'affiliates-for-woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $top_products ) ) {
                foreach ( $top_products as $key => $top_product ) {
                    $product = wc_get_product( $top_product[ 'product' ] );
                    ?>
                    <tr>
                        <td class="column column-product"><a href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?><div><?php echo esc_html( $product->get_name() ); ?></div></a></td>
                        <td><?php echo esc_html( $top_product[ 'quantity' ] ); ?></td>
                        <td><?php echo wc_price( $top_product[ 'commission' ] ); ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4"><center><?php esc_html_e( 'No products yet.', 'affiliates-for-woocommerce' ); ?></center></td>
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
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'top-products' ][ 'endpoint' ], $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        if ( ceil( $total_count / $per_page ) > $current_page ) {
            ?>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'top-products' ][ 'endpoint' ], $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        ?>
    </div>
    <?php
}
