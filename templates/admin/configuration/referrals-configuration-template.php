<?php
/**
 * Referrals Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Referrals_Configuration_Template' ) ) {
	/**
	 * Referrals Configuration template class
	 */
	class DDWCAF_Referrals_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            $args = [
                [
                    'header' => [
                        'heading'     => esc_html__( 'Referral Link Settings', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Configure the structure and behavior of referral links. Define your query variables and set the default token format for your affiliates.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                         [
                            'id'          => 'ddwcaf-query-variable-name',
                            'label'       => esc_html__( 'URL Parameter', 'affiliates-for-woocommerce' ),
                            'type'        => 'text',
                            'value'       => $ddwcaf_configuration[ 'query_variable_name' ],
                            'placeholder' => esc_html__( 'Default: ref', 'affiliates-for-woocommerce' ),
                            'description' => sprintf( esc_html__( 'The query string used to identify a referral (e.g., %s?ref=123).', 'affiliates-for-woocommerce' ), site_url() ),
                        ],
                        [
                            'id'                => 'ddwcaf-default-referral-token',
                            'label'             => esc_html__( 'Default Referral Token [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => [
                                'user_id'  => esc_html__( 'User ID', 'affiliates-for-woocommerce' ),
                                'username' => esc_html__( 'Username [Pro]', 'affiliates-for-woocommerce' ),
                            ],
                            'description'       => esc_html__( 'Select the default identifier used for referral tokens.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                        [
                            'id'                => 'ddwcaf-referral-token-change-allowed',
                            'label'             => esc_html__( 'Custom Referral Tokens [Pro]', 'affiliates-for-woocommerce' ),
                            'checkbox_label'    => esc_html__( 'Allow affiliates to customize their referral token', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'description'       => esc_html__( 'Enables affiliates to create personalized, branded referral links from their dashboard.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Tracking Cookie Configuration', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Manage how referral data is stored and persisted on the customer\'s browser. Set expiration policies and define if subsequent referrals should override existing cookies.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                        [
                            'id'          => 'ddwcaf-referral-cookie-name',
                            'label'       => esc_html__( 'Cookie Name', 'affiliates-for-woocommerce' ),
                            'type'        => 'text',
                            'value'       => $ddwcaf_configuration[ 'referral_cookie_name' ],
                            'placeholder' => esc_html__( 'Default: ddwcaf_referral_token', 'affiliates-for-woocommerce' ),
                            'description' => esc_html__( 'Warning: if you change this setting, all cookies created previously will no longer be valid.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-referral-cookie-expires',
                            'label'       => esc_html__( 'Cookie Life (Days)', 'affiliates-for-woocommerce' ),
                            'type'        => 'text',
                            'value'       => $ddwcaf_configuration[ 'referral_cookie_expires' ],
                            'description' => esc_html__( 'How long the tracking cookie remains active. Leave empty for session-only tracking.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-referral-cookie-change-allowed',
                            'label'             => esc_html__( 'Overwrite Existing Cookie [Pro]', 'affiliates-for-woocommerce' ),
                            'checkbox_label'    => esc_html__( 'Update tracking cookie if a visitor clicks another affiliate\'s link', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'description'       => esc_html__( 'Determines if the "Last Click" wins. If disabled, the first affiliate earns the commission.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                        [
                            'id'                => 'ddwcaf-referral-cookie-checkout-delete-allowed',
                            'label'             => esc_html__( 'Clear Cookie After Purchase [Pro]', 'affiliates-for-woocommerce' ),
                            'checkbox_label'    => esc_html__( 'Delete the tracking cookie after a successful order', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'description'       => esc_html__( 'If enabled, subsequent purchases by the same customer will not be tracked unless they click a referral link again.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Coupon Referral Integration [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Enable and configure coupon-based referrals, allowing affiliates to earn commissions by sharing exclusive discount codes.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-assign-coupons-enabled',
                            'label'             => esc_html__( 'Enable Coupon Tracking', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'description'       => esc_html__( 'Allow administrators to link WooCommerce coupons directly to specific affiliates.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                        [
                            'id'                => 'ddwcaf-display-coupons-section',
                            'label'             => esc_html__( 'Dashboard Coupons Tab', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => [
                                'all'  => esc_html__( 'Show to all affiliates', 'affiliates-for-woocommerce' ),
                                'some' => esc_html__( 'Only show if they have coupons assigned', 'affiliates-for-woocommerce' ),
                            ],
                            'description'       => esc_html__( 'Control the visibility of the "Coupons" section in the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Visit Analytics Settings', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Fine-tune how visitor interactions are recorded and how often repeat visits are registered for analytics purposes.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-register-visits-enabled',
                            'label'             => esc_html__( 'Track All Clicks', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'value'             => $ddwcaf_configuration[ 'register_visits_enabled' ],
                            'description'       => esc_html__( 'Log every click on a referral link for detailed visit analytics.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-register-visit-again-after',
                            'label'             => esc_html__( 'Debounce Time (Seconds) [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'text',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'description'       => esc_html__( 'Minimum time before registering another visit from the same visitor/IP. Prevents analytics bloat.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                    ],
                ],
            ];

            $layout = new DDFW_Layout();
            $layout->get_form_section_layout( $args, 'ddwcaf-referrals-configuration-fields' );
		}
	}
}
