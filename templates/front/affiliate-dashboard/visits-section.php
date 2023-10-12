<?php
/**
 * Visits Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

if ( is_account_page() ) {
    $current_page = preg_replace( "/[^0-9]/", '', $wp->query_vars[ $ddwcaf_configuration[ 'my_account_endpoint' ] ] );
    $current_page = ! empty( $current_page ) ? intval( $current_page ) : 1;
} else {
    $current_page = ! empty( $wp->query_vars[ $endpoints[ 'visits' ][ 'endpoint' ] ] ) ? intval( $wp->query_vars[ $endpoints[ 'visits' ][ 'endpoint' ] ] ) : 1;
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

$visit_helper = new DDWCAF_Visit_Helper( $ddwcaf_configuration );
$total_count  = $visit_helper->ddwcaf_get_visits_count( $args );
$visits       = $visit_helper->ddwcaf_get_visits( $args );
?>
<div class="ddwcaf-list-wrapper">
    <div class="ddwcaf-bulk-actions">
        <form method="get">
            <select name="show" class="form-control">
                <option value=""><?php esc_html_e( 'All', 'affiliates-for-woocommerce' ); ?></option>
                <option value="converted" <?php echo esc_attr( 'converted' === $args[ 'show' ] ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Converted', 'affiliates-for-woocommerce' ); ?></option>
                <option value="not_converted" <?php echo esc_attr( 'not_converted' === $args[ 'show' ] ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Not Converted', 'affiliates-for-woocommerce' ); ?></option>
            </select>

            <label for="from-date"><?php esc_html_e( 'From:', 'affiliates-for-woocommerce' ); ?></label>
            <input type="date" class="form-control" value="<?php echo esc_attr( $args[ 'from_date' ] ); ?>" name="from-date" id="from-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

            <label for="end-date"><?php esc_html_e( 'To:', 'affiliates-for-woocommerce' ); ?></label>
            <input type="date" class="form-control" value="<?php echo esc_attr( $args[ 'end_date' ] ); ?>" name="end-date" id="end-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

            <input type="submit" value="<?php esc_attr_e( 'Filter', 'affiliates-for-woocommerce' ); ?>" name="ddwcaf_filter_submit" class="button" />
        </form>
    </div>

    <table class="shop_table_responsive ddwcaf-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'ID', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Referral URL', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Referrer/Origin URL', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Date', 'affiliates-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Conversion Date', 'affiliates-for-woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $visits ) ) {
                $date_format = get_option( 'date_format' );
                $time_format = get_option( 'time_format' );

                foreach ( $visits as $key => $visit ) {
                    $affiliate_id   = $visit[ 'affiliate_id' ];
                    $affiliate_data = get_userdata( $affiliate_id );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $visit[ 'id' ] ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( $visit[ 'url' ] ); ?>" target="_blank"><?php echo esc_url( $visit[ 'url' ] ); ?></a>
                        </td>
                        <td><?php echo wp_kses_post( ! empty( $visit[ 'referrer_url' ] ) ? '<a href="' . esc_url( $visit[ 'referrer_url' ] ) . '">' . esc_url( $visit[ 'referrer_url' ] ) . '</a>' : 'N/A' ); ?></td>
                        <td><?php echo esc_html( date_i18n( "{$date_format} {$time_format}", strtotime( $visit[ 'date' ] ) ) ); ?></td>
                        <td><?php echo esc_html( ! empty( $visit[ 'conversion_date' ] ) ? date_i18n( "{$date_format} {$time_format}", strtotime( $visit[ 'conversion_date' ] ) ) : 'N/A' ); ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="5"><center><?php esc_html_e( 'No visits yet.', 'affiliates-for-woocommerce' ); ?></center></td>
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
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'visits' ][ 'endpoint' ], $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        if ( ceil( $total_count / $per_page ) > $current_page ) {
            ?>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'visits' ][ 'endpoint' ], $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'affiliates-for-woocommerce' ); ?></a>
            <?php
        }
        ?>
    </div>
    <?php
}
