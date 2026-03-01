<?php
/**
 * Dashboard Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

$affiliate_id = get_current_user_id();

$affiliate_helper  = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
$visit_helper      = new DDWCAF_Visit_Helper( $ddwcaf_configuration );
$commission_helper = new DDWCAF_Commission_Helper( $ddwcaf_configuration );

$args = [
    'affiliate_id' => $affiliate_id,
];

$commission_rate           = $commission_helper->ddwcaf_get_affiliate_commission_rate( $affiliate_id );
$statistics                = $commission_helper->ddwcaf_get_affiliate_statistics( $affiliate_id );
$visits_count              = $visit_helper->ddwcaf_get_visits_count( $args );
$conversion_details        = $visit_helper->ddwcaf_get_conversion_details( $args );
$commission_endpoint       = $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'commissions' ][ 'endpoint' ] );
$visits_endpoint           = $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoints[ 'visits' ][ 'endpoint' ] );
$affiliate_data            = get_userdata( $affiliate_id );

$top_cards = [
	'total_earnings'  => [
		'url'   => $commission_endpoint,
		'title' => esc_html__( 'Total Earnings', 'affiliates-for-woocommerce' ),
		'value' => wc_price( $statistics[ 'total_earnings' ] ),
	],
	'paid_amount'     => [
		'url'   => $commission_endpoint . '?show=paid',
		'title' => esc_html__( 'Paid Amount', 'affiliates-for-woocommerce' ),
		'value' => wc_price( $statistics[ 'paid_earnings' ] ),
	],
	'unpaid_amount'   => [
		'url'   => $commission_endpoint . '?show=pending',
		'title' => esc_html__( 'Unpaid Amount', 'affiliates-for-woocommerce' ),
		'value' => wc_price( $statistics[ 'unpaid_earnings' ] ),
	],
	'visitors'        => [
		'url'   => $visits_endpoint,
		'title' => esc_html__( 'Visitors', 'affiliates-for-woocommerce' ),
		'value' => esc_html( $visits_count ),
	],
	'customers'       => [
		'url'   => $visits_endpoint . '?show=converted',
		'title' => esc_html__( 'Customers', 'affiliates-for-woocommerce' ),
		'value' => esc_html( intval( $conversion_details[ 'customers_count' ] ) ),
	],
	'conversion_rate' => [
		'url'   => $visits_endpoint . '?show=converted',
		'title' => esc_html__( 'Conversion', 'affiliates-for-woocommerce' ),
		'value' => esc_html( wc_format_decimal( $conversion_details[ 'conversion_rate' ], wc_get_price_decimals() ) . '%' ),
	],
];

?>
<div class="ddwcaf-dashboard-container">
    <div class="ddwcaf-affiliate-statistics-container">
        <?php
        foreach ( $top_cards as $key => $top_card ) {
            ?>
            <div class="ddwcaf-affiliate-statistics">
                <a href="<?php echo esc_url( $top_card[ 'url' ] ); ?>">
                    <span class="ddwcaf-statistics-value-wrapper">
                        <h4><?php echo esc_html( $top_card[ 'title' ] ); ?></h4>
                        <span class="ddwcaf-statistics-value"><?php echo $top_card[ 'value' ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    </span>
                    <?php
                    if ( ! empty( $ddwcaf_configuration[ 'details_icons_enabled' ] ) ) {
                        $svg_args = [
                            'size'         => $ddwcaf_configuration[ 'details_icon_size' ],
                            'stroke_width' => 1.5,
                            'stroke_color' => $ddwcaf_configuration[ 'details_icon_color' ],
                        ];

                        if ( ! empty( $ddwcaf_configuration[ 'details_icons_wrapper_enabled' ] ) ) {
                            $svg_args[ 'wrapper' ] = [
                                'element' => 'div',
                                'class'   => 'ddwcaf-details-icon-wrapper',
                            ];
                        }

                        DDFW_SVG::get_svg_icon(
                            $key,
                            false,
                            $svg_args
                        );
                    }
                    ?>
                </a>
            </div>
            <?php
        }
        ?>
    </div>
</div>