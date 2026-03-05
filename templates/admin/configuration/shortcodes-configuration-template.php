<?php
/**
 * Shortcodes Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

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
            $args = [
                [
                    'header' => [
                        'heading'     => esc_html__( 'Shortcode Configuration', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Manage and customize the shortcodes used to display registration forms and the affiliate dashboard on your site. Use these to create dedicated affiliate pages.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                        [
                            'id'    => 'ddwcaf-affiliate-registration-form-shortcode',
                            'label' => esc_html__( 'Affiliate Registration Form Shortcode', 'affiliates-for-woocommerce' ),
                            'type'  => 'text',
                            'value' => $ddwcaf_configuration[ 'affiliate_registration_form_shortcode' ],
                            'description' => esc_html__( 'Default: [ddwcaf_affiliate_registration_form_shortcode]', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'      => 'ddwcaf-affiliate-registration-form-shortcode-content',
                            'label'   => esc_html__( 'Registration Page Content', 'affiliates-for-woocommerce' ),
                            'type'    => 'select',
                            'options' => [
                                'both'              => esc_html__( 'Display Login + Registration Form', 'affiliates-for-woocommerce' ),
                                'only_registration' => esc_html__( 'Display Registration Form Only', 'affiliates-for-woocommerce' ),
                            ],
                            'value'   => $ddwcaf_configuration[ 'affiliate_registration_form_shortcode_content' ],
                            'description' => esc_html__( 'Choose whether to show both login and registration forms or just the registration form.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'    => 'ddwcaf-affiliate-dashboard-shortcode',
                            'label' => esc_html__( 'Affiliate Dashboard Shortcode', 'affiliates-for-woocommerce' ),
                            'type'  => 'text',
                            'value' => $ddwcaf_configuration[ 'affiliate_dashboard_shortcode' ],
                            'description' => esc_html__( 'Default: [ddwcaf_affiliate_dashboard_shortcode]', 'affiliates-for-woocommerce' ),
                        ],
                    ],
                ],
            ];

            $layout = new DDFW_Layout();
            $layout->get_form_section_layout( $args, 'ddwcaf-shortcodes-configuration-fields' );
		}
	}
}
