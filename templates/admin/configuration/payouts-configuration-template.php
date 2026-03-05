<?php
/**
 * Payouts Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Payouts_Configuration_Template' ) ) {
	/**
	 * Payouts Configuration template class
	 */
	class DDWCAF_Payouts_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
			if ( ! empty( $_GET[ 'settings-updated' ] ) && 'true' === sanitize_text_field( wp_unslash( $_GET[ 'settings-updated' ] ) ) ) {
				wp_clear_scheduled_hook( 'ddwcaf_create_payout_schedule' );
			}

            $affiliate_helper = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );

            $args = [
                [
                    'header' => [
                        'heading'     => esc_html__( 'Payout & Withdrawal Configuration', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Set up your payment ecosystem. Choose supported withdrawal methods, define minimum payment thresholds, and schedule automatic payout cycles.', 'affiliates-for-woocommerce' ),
                    ],
                    'after_header_html' => $this->get_withdrawal_methods_html( $ddwcaf_configuration, $affiliate_helper ),
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-withdrawal-type',
                            'label'             => esc_html__( 'Payout Initiation', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => [
                                'manually_by_admin'    => esc_html__( 'Administrator-led (Manual processing)', 'affiliates-for-woocommerce' ),
                                'manually_affiliate'   => esc_html__( 'Affiliate-led (Request-based payouts) [Pro]', 'affiliates-for-woocommerce' ),
                                'automatically_on_day' => esc_html__( 'System-led (Automated monthly schedule) [Pro]', 'affiliates-for-woocommerce' ),
                            ],
                            'value'             => 'manually_by_admin',
                            'description'       => esc_html__( 'Decide how payouts are triggered: manually by you, requested by affiliates, or automatically by the system.', 'affiliates-for-woocommerce' ),
                            'show_fields'       => [
                                'automatically_on_day' => [ 'ddwcaf-withdrawal-day' ]
                            ]
                        ],
                        [
                            'id'                => 'ddwcaf-withdrawal-day',
                            'label'             => esc_html__( 'Scheduled Payout Day [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => '15',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'min' => 1, 'max' => 28, 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'The specific day of the month (1-28) when automatic payouts are generated.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-withdrawal-threshold',
                            'label'             => sprintf( esc_html__( 'Minimum Withdrawal (%s) [Pro]', 'affiliates-for-woocommerce' ), get_woocommerce_currency_symbol() ),
                            'type'              => 'number',
                            'value'             => '0',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'min' => 0, 'step' => .01, 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'The minimum balance an affiliate must earn before they can receive a payout.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-withdrawal-commission-age',
                            'label'             => esc_html__( 'Holding Period (Days) [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => '15',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'The number of days a commission must "mature" before becoming available for payout. Helps manage refunds.', 'affiliates-for-woocommerce' ),
                        ],
                    ],
                ],
            ];

            $layout = new DDFW_Layout();
            $layout->get_form_section_layout( $args, 'ddwcaf-payouts-configuration-fields', [], 'ddwcaf-payouts-configuration-form' );
		}

        /**
         * Get Withdrawal Methods HTML
         *
         * @param array $ddwcaf_configuration
         * @param object $affiliate_helper
         * @return string
         */
        protected function get_withdrawal_methods_html( $ddwcaf_configuration, $affiliate_helper ) {
            ob_start();
            ?>
            <h3><?php esc_html_e( 'Withdrawal Methods', 'affiliates-for-woocommerce' ); ?></h3>
            <div class="ddfw-table-wrapper">
                <table class="widefat fixed ddwcaf-withdrawal-methods-table striped ddfw-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'affiliates-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Available', 'affiliates-for-woocommerce' ); ?></th>
                            <th style="width: 80px;"><?php esc_html_e( 'Status', 'affiliates-for-woocommerce' ); ?></th>
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
                                    <?php if ( $withdrawal_method[ 'available' ] ) : ?>
                                        <span class="ddwcaf-required dashicons ddwcaf-required-yes dashicons-yes"></span>
                                    <?php else : ?>
                                        <p style="margin: 0; font-size: 12px;"><?php echo sprintf( esc_html__( '%s is required', 'affiliates-for-woocommerce' ), '<a href="' . esc_url( $withdrawal_method[ 'url' ] ) . '">' . esc_html( $withdrawal_method_name ) . '</a>' ); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="checkbox" name="_ddwcaf_withdrawal_methods[<?php echo esc_attr( $key ); ?>][status]" value="1" <?php checked( ! empty( $withdrawal_method[ 'status' ] ), 1 ); ?> <?php echo ! $withdrawal_method[ 'available' ] ? 'disabled' : ''; ?> />
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }
	}
}
