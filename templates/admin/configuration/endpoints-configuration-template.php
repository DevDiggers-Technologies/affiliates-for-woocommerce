<?php
/**
 * Endpoints Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Endpoints_Configuration_Template' ) ) {
	/**
	 * Endpoints Configuration template class
	 */
	class DDWCAF_Endpoints_Configuration_Template {
		/**
		 * Construct
         * 
         * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            ?>
            <div class="wrap">
                <div class="notice notice-info">
                    <p>
                        <i>
                            <?php
                            /* translators: %s for a tag */
                            echo sprintf( esc_html__( 'If you really like our plugin, please leave us a %s rating, we\'ll really appreciate it.', 'affiliates-for-woocommerce' ), '<a href="//wordpress.org/support/plugin/woocommerce-affiliates/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>' );
                            ?>
                        </i>
                    </p>
                </div>
				<hr class="wp-header-end" />
                <div class="ddwcaf-pro-container">
                    <img src="<?php echo esc_url( DDWCAF_PLUGIN_URL . 'assets/images/endpoints-configuration.jpg' ); ?>" alt="<?php esc_attr_e( 'Affiliates for WooCommerce Go Pro', 'affiliates-for-woocommerce' ); ?>" />
                    <div class="ddwcaf-pro-details">
                        <h2><?php esc_html_e( 'Endpoints Configuration', 'affiliates-for-woocommerce' ); ?></h2>
                        <hr />
                        <h3><?php esc_html_e( 'Want to customize the endpoints and section title of the affiliate dashboard page?', 'affiliates-for-woocommerce' ); ?></h3>
                        <ul>
                            <li><?php esc_html_e( 'All sections\' endpoints and titles are completely customizable.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Custom or dynamic endpoints.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Custom or dynamic endpoints title.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Create multilingual URLs.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Completely Reliabile.', 'affiliates-for-woocommerce' ); ?></li>
                        </ul>
                        <a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Endpoints Configuration Page&utm_campaign=Affiliates for WooCommerce Pro" target="_blank" class="ddwcaf-go-pro-button"><?php esc_html_e( 'Go Pro', 'affiliates-for-woocommerce' ); ?></a>&ensp;
                        <a href="//devdiggers.com/affiliates-for-woocommerce/?utm_source=Affiliates for WooCommerce Doc&utm_medium=Plugin Endpoints Configuration Page&utm_campaign=Affiliates for WooCommerce Doc" target="_blank"><?php esc_html_e( 'Learn more about Pro', 'affiliates-for-woocommerce' ); ?></php></a>
                    </div>
                </div>
            </div>
            <?php
        }
	}
}
