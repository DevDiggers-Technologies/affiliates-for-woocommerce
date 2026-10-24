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
                [
                    'header' => [
                        'heading'     => esc_html__( 'Automatic Payouts [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Let the plugin move the money instead of you. Once a payout is completed, whether you complete it yourself, an affiliate requests it, or the scheduled run creates it, it is sent through the gateway that matches that affiliate\'s own withdrawal method. Bank transfer and wallet payouts are never touched by this and stay manual.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-payout-auto-send',
                            'label'             => esc_html__( 'Send Payouts Automatically', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Pay affiliates through their gateway when a payout is completed', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Turn this off to keep recording every payout by hand. The gateways below stay available for one-off manual sends from the payout screen.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-payout-max-attempts',
                            'label'             => esc_html__( 'Maximum Send Attempts', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => '3',
                            'description'       => esc_html__( 'How many times one payout may be sent before it gives up. Protects you from a bad payout address burning through API calls. You can always retry it by hand afterwards.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'min' => 1, 'max' => 10, 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-payout-failure-email-enabled',
                            'label'             => esc_html__( 'Failure Notifications', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Email an administrator when a payout cannot be sent', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'A failed transfer is never recorded as paid, so without this alert a payout can sit unnoticed until the affiliate chases it.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-payout-failure-email-recipient',
                            'label'             => esc_html__( 'Notification Recipient', 'affiliates-for-woocommerce' ),
                            'type'              => 'email',
                            'description'       => esc_html__( 'Where failed payout alerts are sent. Defaults to the site administrator email.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'PayPal Payouts [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Pay affiliates straight to the PayPal email address on their profile, through the PayPal Payouts API. Nothing is required from the affiliate beyond saving that address.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-paypal-enabled',
                            'label'             => esc_html__( 'Enable PayPal Payouts', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Send PayPal payouts through the PayPal API', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'When this is off, PayPal payouts are still listed as a withdrawal method but you send the money yourself and record the transaction ID.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-paypal-mode',
                            'label'             => esc_html__( 'Environment', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => [
                                'sandbox' => esc_html__( 'Sandbox (test payouts, no real money)', 'affiliates-for-woocommerce' ),
                                'live' => esc_html__( 'Live (real money leaves your account)', 'affiliates-for-woocommerce' ),
                            ],
                            'value'             => 'sandbox',
                            'description'       => esc_html__( 'Sandbox credentials only work against sandbox, and live credentials only against live. Test the whole flow in sandbox before switching.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-paypal-client-id',
                            'label'             => esc_html__( 'Client ID', 'affiliates-for-woocommerce' ),
                            'type'              => 'text',
                            'placeholder'       => esc_html__( 'Paste the client ID of your PayPal REST app', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Found under your REST app in the PayPal developer dashboard. Safe to store, it is not a secret.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-paypal-client-secret',
                            'label'             => esc_html__( 'Client Secret', 'affiliates-for-woocommerce' ),
                            'type'              => 'password',
                            'description'       => esc_html__( 'Can also be defined as the DDWCAF_PAYPAL_CLIENT_SECRET constant in wp-config.php to keep it out of the database entirely.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Stripe Connect [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Stripe cannot send money to a plain email address the way PayPal can. Each affiliate connects a Stripe Express account once from their own dashboard, in a hosted flow where Stripe collects their identity and bank details directly, and payouts are then transferred to that account.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-stripe-enabled',
                            'label'             => esc_html__( 'Enable Stripe Connect Payouts', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Transfer payouts to connected Stripe accounts', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Stripe Connect must be enabled on your Stripe account first, otherwise creating affiliate accounts will fail.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-stripe-secret-key',
                            'label'             => esc_html__( 'Secret Key', 'affiliates-for-woocommerce' ),
                            'type'              => 'password',
                            'placeholder'       => 'sk_live_...',
                            'description'       => esc_html__( 'Can also be defined as the DDWCAF_STRIPE_SECRET_KEY constant in wp-config.php. A key beginning with sk_test or rk_test puts the integration in test mode automatically, so there is no separate environment switch to keep in sync.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
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
                                <td>
                                    <?php echo esc_html( $withdrawal_method_name ); ?>
                                    <?php if ( 'paypal_email' === $key ) : ?>
                                        <span class="description">(<?php esc_html_e( 'Automatic payouts available in Pro', 'affiliates-for-woocommerce' ); ?>)</span>
                                    <?php endif; ?>
                                </td>
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
                        <tr>
                            <td><?php esc_html_e( 'Stripe Connect [Pro]', 'affiliates-for-woocommerce' ); ?></td>
                            <td>
                                <p style="margin: 0; font-size: 12px;"><?php esc_html_e( 'Affiliates connect a Stripe Express account once from their dashboard, then completed payouts are transferred to it automatically.', 'affiliates-for-woocommerce' ); ?></p>
                            </td>
                            <td><input type="checkbox" disabled /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }
	}
}
