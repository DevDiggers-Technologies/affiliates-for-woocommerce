<?php
/**
 * Shortcodes Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Shortcodes_Configuration_Template' ) ) {
	/**
	 * Shortcodes Configuration template class
	 */
	class DDWCAF_Shortcodes_Configuration_Template {
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
                <?php settings_errors(); ?>
                <div class="ddwcaf-configuration-container ddwcaf-padding-top-bottom-0">
                    <form action="options.php" method="POST">
                        <?php settings_fields( 'ddwcaf-shortcodes-configuration-fields' ); ?>
                        <h2><?php esc_html_e( 'Shortcodes Configuration', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Affiliate Registration Form', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the shortcode used to display the affiliate registration form.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Default: [ddwcaf_affiliate_registration_form_shortcode]', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-affiliate-registration-form-shortcode',
                                    'value'       => $ddwcaf_configuration[ 'affiliate_registration_form_shortcode' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'     => 'select',
                                    'label'    => esc_html__( 'Affiliate Registration Form Content', 'affiliates-for-woocommerce' ),
                                    'help_tip' => esc_html__( 'Select the content to be displayed on the affiliate registration shortcode.', 'affiliates-for-woocommerce' ),
                                    'options'  => [
                                        'both'              => esc_html__( 'Login + Registration Form', 'affiliates-for-woocommerce' ),
                                        'only_registration' => esc_html__( 'Only Registration Form', 'affiliates-for-woocommerce' ),
                                    ],
                                    'id'          => 'ddwcaf-affiliate-registration-form-shortcode-content',
                                    'value'       => $ddwcaf_configuration[ 'affiliate_registration_form_shortcode_content' ],
                                    'input_class' => [ 'ddwcaf-select2' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Affiliate Dashboard', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the shortcode used to display the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Default: [ddwcaf_affiliate_dashboard_shortcode]', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-affiliate-dashboard-shortcode',
                                    'value'       => $ddwcaf_configuration[ 'affiliate_dashboard_shortcode' ],
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <?php submit_button( esc_html__( 'Save Changes', 'affiliates-for-woocommerce' ) ); ?>
                    </form>
                </div>
            </div>
            <?php
        }
	}
}
