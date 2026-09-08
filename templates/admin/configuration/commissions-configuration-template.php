<?php
/**
 * Commissions Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Commissions_Configuration_Template' ) ) {
	/**
	 * Commissions Configuration template class
	 */
	class DDWCAF_Commissions_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            $args = [
                [
                    'header' => [
                        'heading'     => esc_html__( 'Commission Calculation Rules', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Define the core rules for rewarding your affiliates. Set default rates, manage product exclusions, and decide how taxes and discounts affect the final commission.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-self-refer-enabled',
                            'label'             => esc_html__( 'Allow Self-Referrals [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'value'             => 'no',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'description'       => esc_html__( 'If enabled, affiliates can earn commissions on purchases they make using their own referral links or coupons.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                        [
                            'id'                => 'ddwcaf-default-commission-rate',
                            'label'             => esc_html__( 'Default Commission (%)', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => $ddwcaf_configuration[ 'default_commission_rate' ],
                            'custom_attributes' => [ 'min' => 0, 'max' => 100 ],
                            'description'       => esc_html__( 'The default percentage awarded for successful referrals. This can be overridden by specific rules or per-affiliate settings.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-excluded-products',
                            'label'             => esc_html__( 'Excluded Products [Pro]', 'affiliates-for-woocommerce' ),
                            'name'              => '_ddwcaf_excluded_products[]',
                            'type'              => 'products',
                            'value'             => [],
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'multiple' => true, 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'Selected products will not generate any commissions.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-excluded-categories',
                            'label'             => esc_html__( 'Excluded Categories [Pro]', 'affiliates-for-woocommerce' ),
                            'name'              => '_ddwcaf_excluded_categories[]',
                            'type'              => 'categories',
                            'value'             => [],
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'multiple' => true, 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'Products from selected categories will not generate any commissions.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-exclude-taxes-enabled',
                            'label'             => esc_html__( 'Net-of-Tax Calculation', 'affiliates-for-woocommerce' ),
                            'checkbox_label'    => esc_html__( 'Calculate commission based on subtotal (excluding taxes)', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'value'             => $ddwcaf_configuration[ 'exclude_taxes_enabled' ],
                            'description'       => esc_html__( 'If enabled, the commission amount will be calculated after subtracting sales taxes from the order total.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-exclude-discounts-enabled',
                            'label'             => esc_html__( 'Exclude Discounted Value', 'affiliates-for-woocommerce' ),
                            'checkbox_label'    => esc_html__( 'Calculate commission based on final price after discounts', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'value'             => $ddwcaf_configuration[ 'exclude_discounts_enabled' ],
                            'description'       => esc_html__( 'If enabled, commissions will be calculated on the actual price paid by the customer after any coupons or discounts are applied.', 'affiliates-for-woocommerce' ),
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Tiered Reward Structure [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Incentivize top-performing affiliates by creating multiple commission levels based on their cumulative earnings within a set period.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'after_header_html' => $this->get_multi_level_rules_html( $ddwcaf_configuration ),
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-reset-multi-level-days',
                            'label'             => esc_html__( 'Earnings Reset Cycle (Days)', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => '30',
                            'description'       => esc_html__( 'The period after which an affiliate\'s cumulative earnings reset for tier calculation (e.g., 30 days for monthly tiers).', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [
                                'disabled' => 'disabled',
                            ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Lifetime Commissions [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Reward the affiliate who won a customer, not just the click that happened to be last. Once an affiliate brings in a new customer, that customer is linked to them and every later order earns commission even with no referral link and no cookie. This is what turns a one-off referral into an income stream and is the single strongest argument for a serious partner to promote you.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-lifetime-commissions-enabled',
                            'label'             => esc_html__( 'Enable Lifetime Commissions', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Link referred customers to their affiliate permanently', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'The link is written on the customer\'s first referred order and is never overwritten, so a later affiliate cannot steal a customer someone else won. Guest checkouts are skipped because there is no account to attach the link to.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-lifetime-commissions-days',
                            'label'             => esc_html__( 'Lifetime Window (Days)', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => '0',
                            'description'       => esc_html__( 'How long the customer stays attached to the affiliate. Leave at 0 for genuinely unlimited, or set something like 365 to cap your exposure to one year per customer.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'min' => 0, 'disabled' => 'disabled' ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Subscription Renewals [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'In a subscription store most of the revenue arrives after the first order. Paying affiliates on renewals lines their interests up with retention instead of just acquisition. Requires the WooCommerce Subscriptions plugin.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-recurring-commissions-enabled',
                            'label'             => esc_html__( 'Enable Renewal Commissions', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Pay commission on every subscription renewal', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'The affiliate credited for the original subscription order is credited again on each successful renewal payment.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-recurring-commission-rate',
                            'label'             => esc_html__( 'Renewal Rate (%)', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'description'       => esc_html__( 'Leave empty to pay renewals at the affiliate\'s normal rate. Set a lower number if you want acquisition rewarded more heavily than retention.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'min' => 0, 'max' => 100, 'step' => .01, 'disabled' => 'disabled' ],
                        ],
                    ],
                ],
            ];

            $layout = new DDFW_Layout();
            $layout->get_form_section_layout( $args, 'ddwcaf-commissions-configuration-fields', [], 'ddwcaf-commissions-configuration-form' );
		}

        /**
         * Get Multi Level Rules HTML
         *
         * @param array $ddwcaf_configuration
         * @return string
         */
        protected function get_multi_level_rules_html( $ddwcaf_configuration ) {
            ob_start();
            ?>
            <h3><?php esc_html_e( 'Multi Level Rules [Pro]', 'affiliates-for-woocommerce' ); ?></h3>
            <div class="ddfw-table-wrapper ddfw-upgrade-to-pro-tag-wrapper">
                <table class="widefat ddwcaf-multi-level-rules-table striped ddwcaf-rules-wrapper ddfw-table">
                    <thead>
                        <tr>
                            <th><strong><?php echo esc_html__( 'Name', 'affiliates-for-woocommerce' ); ?></strong></th>
                            <th><strong><?php echo esc_html__( 'Earning From', 'affiliates-for-woocommerce' ); ?></strong></th>
                            <th><strong><?php echo esc_html__( 'Earning To', 'affiliates-for-woocommerce' ); ?></strong></th>
                            <th><strong><?php echo esc_html__( 'Commission Rate', 'affiliates-for-woocommerce' ); ?></strong></th>
                            <th><strong><?php echo esc_html__( 'Status', 'affiliates-for-woocommerce' ); ?></strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6">
                                <a href="javascript:void(0);" class="ddwcaf-add-row button disabled"><?php esc_html_e( 'Add Row', 'affiliates-for-woocommerce' ); ?></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }
	}
}
