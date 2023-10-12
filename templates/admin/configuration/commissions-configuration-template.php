<?php
/**
 * Commissions Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

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
            $affiliate_helper = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
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
                <div class="ddwcaf-configuration-container ddwcaf-padding-top-bottom-0 ddwcaf-width-unset">
                    <form action="options.php" method="POST">
                        <?php settings_fields( 'ddwcaf-commissions-configuration-fields' ); ?>
                        <h2><?php esc_html_e( 'General', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable [Pro]', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Affiliate Self Refer [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the affiliate self refer functionality.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows affiliates to earn commissions on their own orders. Disabling this option will not record a commission if an affiliate uses their own referral link/coupons during orders.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-self-refer-enabled',
                                ] );

                                ddwcaf_form_field( [
                                    'type'              => 'number',
                                    'label'             => esc_html__( 'Default/Global Commission Rate (%)', 'affiliates-for-woocommerce' ),
                                    'help_tip'          => esc_html__( 'This is the default commission rate for all affiliates.', 'affiliates-for-woocommerce' ),
                                    'description'       => esc_html__( 'You can override this value in each Affiliates\' detail page.', 'affiliates-for-woocommerce' ),
                                    'id'                => 'ddwcaf-default-commission-rate',
                                    'value'             => $ddwcaf_configuration[ 'default_commission_rate' ],
                                    'custom_attributes' => [
                                        'min' => 0,
                                        'max' => 100,
                                    ]
                                ] );

                                ?>
                                <tr valign="top">
                                    <th>
                                        <label for="ddwcaf-excluded-products"><?php esc_html_e( 'Excluded Products [Pro]', 'affiliates-for-woocommerce' ); ?></label>
                                    </th>
                                    <td>
                                        <?php echo wc_help_tip( esc_html__( 'For these selected products, affiliates will not get any commissions.', 'affiliates-for-woocommerce' ) ); ?>

                                        <select id="ddwcaf-excluded-products" class="regular-text ddwcaf-products" name="_ddwcaf_excluded_products[]" multiple data-placeholder="<?php esc_attr_e( 'Search by title', 'affiliates-for-woocommerce' ); ?>"></select>

                                        <p class="description ddwcaf-margin-left-20"><i><?php esc_html_e( 'Leave empty if you want to give commissions for all products to affiliates.', 'affiliates-for-woocommerce' ); ?></i></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th>
                                        <label for="ddwcaf-excluded-categories"><?php esc_html_e( 'Excluded Categories [Pro]', 'affiliates-for-woocommerce' ); ?></label>
                                    </th>
                                    <td>
                                        <?php echo wc_help_tip( esc_html__( 'For these selected categories, affiliates will not get any commissions.', 'affiliates-for-woocommerce' ) ); ?>

                                        <select id="ddwcaf-excluded-categories" class="regular-text ddwcaf-categories" name="_ddwcaf_excluded_categories[]" multiple data-placeholder="<?php esc_attr_e( 'Search by name', 'affiliates-for-woocommerce' ); ?>"></select>

                                        <p class="description ddwcaf-margin-left-20"><i><?php esc_html_e( 'Leave empty if you want to give commissions for all products to affiliates.', 'affiliates-for-woocommerce' ); ?></i></p>
                                    </td>
                                </tr>
                                <?php

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Exclude Taxes from Commission Calculations', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the exclude taxes functionality for the affiliate commission calculation.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'Enable this option if you want to calculate commissions for the affiliate without taxes.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-exclude-taxes-enabled',
                                    'value'          => $ddwcaf_configuration[ 'exclude_taxes_enabled' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Exclude Discounts from Commission Calculations', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the exclude discounts functionality for the affiliate commission calculation.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'Enable this option if you want to calculate commissions for the affiliate without discounts.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-exclude-discounts-enabled',
                                    'value'          => $ddwcaf_configuration[ 'exclude_discounts_enabled' ],
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <h2><?php esc_html_e( 'Payouts/Withdrawals', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th>
                                        <label><?php esc_html_e( 'Payment Methods', 'affiliates-for-woocommerce' ); ?></label>
                                    </th>
                                    <td>
                                        <table class="widefat fixed ddwcaf-withdrawal-methods-table striped">
                                            <thead>
                                                <tr>
                                                    <th><?php esc_html_e( 'Name', 'affiliates-for-woocommerce' ); ?></th>
                                                    <th><?php esc_html_e( 'Available', 'affiliates-for-woocommerce' ); ?></th>
                                                    <th><?php esc_html_e( 'Status', 'affiliates-for-woocommerce' ); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ( $ddwcaf_configuration[ 'withdrawal_methods' ] as $key => $withdrawal_method ) {
                                                    $withdrawal_method_name = $affiliate_helper->ddwcaf_get_withdrawal_method_name( $key );
                                                    ?>
                                                    <tr>
                                                        <td><?php echo esc_html( $withdrawal_method_name ); ?></td>
                                                        <td>
                                                            <input type="hidden" name="_ddwcaf_withdrawal_methods[<?php echo esc_attr( $key ); ?>][name]" value="<?php echo esc_attr( $withdrawal_method_name ); ?>" />
                                                            <input type="hidden" name="_ddwcaf_withdrawal_methods[<?php echo esc_attr( $key ); ?>][available]" value="<?php echo esc_attr( $withdrawal_method[ 'available' ] ); ?>" />
                                                            <input type="hidden" name="_ddwcaf_withdrawal_methods[<?php echo esc_attr( $key ); ?>][url]" value="<?php echo esc_attr( $withdrawal_method[ 'url' ] ); ?>" />
                                                            <?php
                                                            if ( $withdrawal_method[ 'available' ] ) {
                                                                ?>
                                                                <span class="ddwcaf-required dashicons ddwcaf-required-yes dashicons-yes"></span>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <p><?php echo sprintf( esc_html__( '%s is required', 'affiliates-for-woocommerce' ), '<a href="' . esc_url( $withdrawal_method[ 'url' ] ) . '">' . esc_html( $withdrawal_method_name ) . '</a>' ); ?></p>
                                                                <?php
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" name="_ddwcaf_withdrawal_methods[<?php echo esc_attr( $key ); ?>][status]" value="1" <?php echo esc_attr( checked( ! empty( $withdrawal_method[ 'status' ] ), 1, false ) ); ?> <?php echo esc_attr( ! $withdrawal_method[ 'available' ] ? 'disabled' : '' ); ?> />
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <p class="description"><i><?php esc_html_e( 'Affiliates for WooCommerce Pro version is needed to use WooCommerce Wallet Management as withdrawal method.', 'affiliates-for-woocommerce' ); ?></i></p>
                                    </td>
                                </tr>
                                <?php
                                ddwcaf_form_field( [
                                    'type'              => 'select',
                                    'label'             => esc_html__( 'Withdrawal Type [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'          => esc_html__( 'Select the withdrawal type from the given options.', 'affiliates-for-woocommerce' ),
                                    'description'       => esc_html__( 'Choose how you want to manage the affiliate commission earnings withdrawal.', 'affiliates-for-woocommerce' ),
                                    'id'                => 'ddwcaf-withdrawal-type',
                                    'input_class'       => [ 'ddwcaf-select2' ],
                                    'options'           => [
                                        'manually_by_admin'    => esc_html__( 'Manually by admin', 'affiliates-for-woocommerce' ),
                                        'manually_affiliate'   => esc_html__( 'Manually requested by the affiliate', 'affiliates-for-woocommerce' ),
                                        'automatically_on_day' => esc_html__( 'Automatically create a payout on a specific day of the month', 'affiliates-for-woocommerce' ),
                                    ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'              => 'number',
                                    'label'             => esc_html__( 'Withdrawal Day [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'          => esc_html__( 'Select any day between (1-28) for the monthly payment.', 'affiliates-for-woocommerce' ),
                                    'description'       => esc_html__( 'Choose any day of the month to pay commissions to all affiliates.', 'affiliates-for-woocommerce' ),
                                    'id'                => 'ddwcaf-withdrawal-day',
                                    'custom_attributes' => [
                                        'min' => 1,
                                        'max' => 28,
                                    ]
                                ] );

                                ddwcaf_form_field( [
                                    'type'              => 'number',
                                    'label'             => sprintf( esc_html__( 'Payment Threshold (%s) [Pro]', 'affiliates-for-woocommerce' ), get_woocommerce_currency_symbol() ),
                                    'help_tip'          => esc_html__( 'Enter any threshold value for the affiliate earnings withdrawal.', 'affiliates-for-woocommerce' ),
                                    'description'       => esc_html__( 'Enter any minimum threshold amount for the affiliate to earn in order to allow the withdrawal.', 'affiliates-for-woocommerce' ),
                                    'id'                => 'ddwcaf-withdrawal-threshold',
                                    'custom_attributes' => [
                                        'min'  => 0,
                                        'step' => .01,
                                    ]
                                ] );

                                ddwcaf_form_field( [
                                    'type'              => 'number',
                                    'label'             => esc_html__( 'Commissions\' days old [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'          => esc_html__( 'Enter any minimum number of days old for commissions.', 'affiliates-for-woocommerce' ),
                                    'description'       => esc_html__( 'Enter the minimum number of days that should pass since the commission\'s creation to allow it to be automatically paid. Leave empty to consider all commissions.', 'affiliates-for-woocommerce' ),
                                    'id'                => 'ddwcaf-withdrawal-commission-age',
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
