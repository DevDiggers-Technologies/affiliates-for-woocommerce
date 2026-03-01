<?php
/**
 * Manage Payout Template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Payouts;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Manage_Payout_Template' ) ) {
	/**
	 * Manage Payouts Template class
	 */
	class DDWCAF_Manage_Payout_Template {
        /**
		 * Error Helper Trait
		 */
		use DDWCAF_Error_Helper;

		/**
		 * Construct
         * 
         * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            $page              = ! empty( $_GET[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : '';
            $affiliate_helper  = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
            $payout_helper     = new DDWCAF_Payout_Helper( $ddwcaf_configuration );
            $visit_helper      = new DDWCAF_Visit_Helper( $ddwcaf_configuration );
            $commission_helper = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
            $payout_id         = ! empty( $_GET[ 'id' ] ) ? intval( sanitize_text_field( wp_unslash( $_GET[ 'id' ] ) ) ) : '';
            $payout            = $payout_helper->ddwcaf_get_payout_by_id( $payout_id );
            $affiliate_id      = $payout[ 'affiliate_id' ];
            $date_format       = get_option( 'date_format' );
            $time_format       = get_option( 'time_format' );

            if ( ! empty( $_POST[ 'ddwcaf_save_payout_info' ] ) && ! empty( $_POST[ 'ddwcaf_save_payout_info_nonce' ] ) && wp_verify_nonce( $_POST[ 'ddwcaf_save_payout_info_nonce' ], 'ddwcaf_save_payout_info_nonce_action' ) ) {
                $transaction_id = ! empty( $_POST[ '_ddwcaf_transaction_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_transaction_id' ] ) ) : '';

                if ( ! empty( $_POST[ '_ddwcaf_status' ] ) ) {
                    $status = sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_status' ] ) );

                    if ( apply_filters( 'ddwcaf_custom_check_for_payout_transaction_id', true, $payout ) ) {
                        $payout[ 'transaction_id' ] = $transaction_id;
                    }

                    $response = $payout_helper->ddwcaf_update_payout_status( $payout_id, $status, false, $payout );

                    if ( $response ) {
                        $payout = $response;
                        $this->ddwcaf_print_notification( sprintf( esc_html__( 'Payout status is changed to %s successfully', 'affiliates-for-woocommerce' ), $payout_helper->ddwcaf_get_translation( $status ) ), 'success' );
                    } else {
                        $this->ddwcaf_print_notification( esc_html__( 'There is some error in processing the payout.', 'affiliates-for-woocommerce' ), 'error' );
                    }

                    if ( apply_filters( 'ddwcaf_custom_check_for_payout_transaction_id', true, $payout ) ) {
                        $this->ddwcaf_print_notification( esc_html__( 'Transaction ID is saved successfully to the payout.', 'affiliates-for-woocommerce' ), 'success' );
                    }
                }
            }

            $args = [
                'affiliate_id' => $affiliate_id,
            ];

            $per_page = 10;

            $payout_args = [
                'payout_id' => $payout_id,
                'per_page'  => $per_page,
                'offset'    => 0,
            ];

            $commissions_count      = $payout_helper->ddwcaf_get_payout_commissions_count( $payout_args );
            $commissions            = $payout_helper->ddwcaf_get_payout_commissions( $payout_args );
            $statistics             = $commission_helper->ddwcaf_get_affiliate_statistics( $affiliate_id );
            $visits_count           = $visit_helper->ddwcaf_get_visits_count( $args );
            $conversion_details     = $visit_helper->ddwcaf_get_conversion_details( $args );
            $affiliate_referral_url = $affiliate_helper->ddwcaf_get_affiliate_referral_url( $affiliate_id );
            $affiliate_status       = $affiliate_helper->ddwcaf_get_affiliate_status( $affiliate_id );
            $affiliate_data         = get_userdata( $affiliate_id );
            $payment_info           = maybe_unserialize( $payout[ 'payment_info' ] );
            ?>
            <div class="ddwcaf-manage-affiliate-container">
                <div class="ddwcaf-page-header">
                    <div class="ddwcaf-header-left">
                        <h1><?php echo esc_html( sprintf( __( 'Payout #%d', 'affiliates-for-woocommerce' ), $payout_id ) ); ?></h1>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page ) ); ?>" class="button"><?php esc_html_e( '← Back', 'affiliates-for-woocommerce' ); ?></a>
                    </div>
                </div>

                <hr class="wp-header-end" />

                <form method="POST">
                    <?php wp_nonce_field( 'ddwcaf_save_payout_info_nonce_action', 'ddwcaf_save_payout_info_nonce' ); ?>
                    <div class="ddwcaf-management-card">
                        <div class="ddwcaf-card-header">
                            <h3><?php esc_html_e( 'Payout Management', 'affiliates-for-woocommerce' ); ?></h3>
                        </div>
                        <div class="ddwcaf-card-body">
                            <div class="ddwcaf-management-grid">
                                <!-- Left Column: Payout Actions -->
                                <div class="ddwcaf-management-section">
                                    <h4><?php esc_html_e( 'Payout Actions', 'affiliates-for-woocommerce' ); ?></h4>
                                    
                                    <div class="ddwcaf-field-group">
                                        <label for="ddwcaf-status"><?php esc_html_e( 'Payout Status', 'affiliates-for-woocommerce' ); ?></label>
                                        <select id="ddwcaf-status" name="_ddwcaf_status" class="regular-text">
                                            <?php foreach ( $payout_status_options as $value => $label ) : ?>
                                                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $payout[ 'status' ], $value ); ?>><?php echo esc_html( $label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="ddwcaf-field-description"><?php esc_html_e( 'Change the current status of this payout.', 'affiliates-for-woocommerce' ); ?></span>
                                    </div>

                                    <div class="ddwcaf-field-group">
                                        <label for="ddwcaf-transaction-id"><?php esc_html_e( 'Transaction ID', 'affiliates-for-woocommerce' ); ?></label>
                                        <input 
                                            type="text" 
                                            id="ddwcaf-transaction-id" 
                                            class="regular-text" 
                                            name="_ddwcaf_transaction_id" 
                                            value="<?php echo esc_attr( $payout[ 'transaction_id' ] ); ?>" 
                                            placeholder="<?php esc_attr_e( 'Enter the transaction ID', 'affiliates-for-woocommerce' ); ?>"
                                        />
                                        <span class="ddwcaf-field-description">
                                            <?php echo apply_filters( 'ddwcaf_payout_transaction_input_description', esc_html__( 'It gets auto generated for the Wallet [Pro] payout.', 'affiliates-for-woocommerce' ), $payout ); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Right Column: Withdrawal Details & Payout Info -->
                                <div class="ddwcaf-management-section">
                                    <div class="ddwcaf-management-sub-section" style="margin-bottom: 32px;">
                                        <h4><?php esc_html_e( 'Withdrawal Details', 'affiliates-for-woocommerce' ); ?></h4>
                                        <?php echo $this->get_withdrawal_details_html( $affiliate_helper, $payout_helper, $payout, $affiliate_id, $payment_info ); ?>
                                    </div>

                                    <div class="ddwcaf-management-sub-section">
                                        <h4><?php esc_html_e( 'Payout Information', 'affiliates-for-woocommerce' ); ?></h4>
                                        <table class="ddwcaf-withdrawal-info-table">
                                            <tbody>
                                                <tr>
                                                    <th><?php esc_html_e( 'Payout Amount', 'affiliates-for-woocommerce' ); ?></th>
                                                    <td><strong><?php echo wc_price( $payout[ 'amount' ] ); ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <th><?php esc_html_e( 'Created Date', 'affiliates-for-woocommerce' ); ?></th>
                                                    <td><?php echo esc_html( date_i18n( "{$date_format} {$time_format}", strtotime( $payout[ 'created_at' ] ) ) ); ?></td>
                                                </tr>
                                                <?php if ( ! empty( $payout[ 'completed_at' ] ) ) : ?>
                                                <tr>
                                                    <th><?php esc_html_e( 'Completed Date', 'affiliates-for-woocommerce' ); ?></th>
                                                    <td><?php echo esc_html( date_i18n( "{$date_format} {$time_format}", strtotime( $payout[ 'completed_at' ] ) ) ); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <th><?php esc_html_e( 'Status', 'affiliates-for-woocommerce' ); ?></th>
                                                    <td><mark class="ddwcaf-status ddwcaf-payout-status-<?php echo esc_attr( $payout[ 'status' ] ); ?>"><?php echo esc_html( $payout_helper->ddwcaf_get_translation( $payout[ 'status' ] ) ); ?></mark></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ddwcaf-card-footer">
                            <input type="submit" name="ddwcaf_save_payout_info" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'affiliates-for-woocommerce' ); ?>" />
                        </div>
                    </div>
                </form>

                <!-- Affiliate Profile Card -->
                <div class="ddwcaf-affiliate-profile-card">
                    <div class="ddwcaf-profile-avatar">
                        <img src="<?php echo esc_url( get_avatar_url( $affiliate_id, [ 'size' => 100 ] ) ); ?>" alt="<?php echo esc_attr( $affiliate_data->display_name ); ?>" />
                    </div>
                    <div class="ddwcaf-profile-content">
                        <div class="ddwcaf-profile-name-row">
                            <h3><?php echo esc_html( $affiliate_data->display_name ); ?></h3>
                            <span class="ddwcaf-user-id-badge">#<?php echo esc_html( $affiliate_id ); ?></span>
                            <span class="ddwcaf-status ddwcaf-affiliate-status-<?php echo esc_attr( $affiliate_status ); ?>">
                                <?php echo esc_html( ucfirst( $affiliate_status ) ); ?>
                            </span>
                        </div>

                        <div class="ddwcaf-profile-meta-grid">
                            <div class="ddwcaf-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                                <span><?php echo esc_html( $affiliate_data->user_email ); ?></span>
                            </div>
                        </div>

                        <div class="ddwcaf-profile-referral-box">
                            <div class="ddwcaf-referral-input-group">
                                <span class="ddwcaf-referral-url-text" id="ddwcaf-payout-referral-url"><?php echo esc_url( $affiliate_referral_url ); ?></span>
                                <button type="button" class="ddwcaf-copy-url-btn" title="<?php esc_attr_e( 'Copy URL', 'affiliates-for-woocommerce' ); ?>" data-target="ddwcaf-payout-referral-url">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="ddwcaf-summary-grid">
                    <?php
                    $this->render_summary_card(
                        esc_html__( 'Total Earnings', 'affiliates-for-woocommerce' ),
                        wc_price( $statistics[ 'total_earnings' ] ?? 0 ),
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
                        'html'
                    );

                    $this->render_summary_card(
                        esc_html__( 'Paid Amount', 'affiliates-for-woocommerce' ),
                        wc_price( $statistics[ 'paid_earnings' ] ?? 0 ),
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>',
                        'html'
                    );

                    $this->render_summary_card(
                        esc_html__( 'Unpaid Amount', 'affiliates-for-woocommerce' ),
                        wc_price( $statistics[ 'unpaid_earnings' ] ?? 0 ),
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
                        'html'
                    );

                    $this->render_summary_card(
                        esc_html__( 'Visitors', 'affiliates-for-woocommerce' ),
                        $visits_count ?? 0,
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
                        'number'
                    );

                    $this->render_summary_card(
                        esc_html__( 'Customers', 'affiliates-for-woocommerce' ),
                        $conversion_details[ 'customers_count' ] ?? 0,
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
                        'number'
                    );

                    $this->render_summary_card(
                        esc_html__( 'Conversion', 'affiliates-for-woocommerce' ),
                        wc_format_decimal( $conversion_details[ 'conversion_rate' ] ?? 0, wc_get_price_decimals() ) . '%',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
                        'text'
                    );
                    ?>
                </div>

                <?php
                $payout_status_options = [
                    ''          => esc_html__( 'Select an action', 'affiliates-for-woocommerce' ),
                    'pending'   => esc_html__( 'Change Status to Pending', 'affiliates-for-woocommerce' ),
                    'cancelled' => esc_html__( 'Change Status to Cancelled', 'affiliates-for-woocommerce' ),
                    'completed' => esc_html__( 'Change Status to Completed', 'affiliates-for-woocommerce' ),
                ];
                ?>

                <!-- Commissions Table -->
                <?php if ( ! empty( $commissions ) ) : ?>
                <div class="ddwcaf-manage-affiliate-charts-section">
                    <div class="ddwcaf-chart-container">
                        <div class="ddwcaf-chart-header">
                            <h3><?php esc_html_e( 'Commissions in this Payout', 'affiliates-for-woocommerce' ); ?></h3>
                            <?php if ( $commissions_count > $per_page ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcaf-commissions&payout-id=' . $payout_id ) ); ?>" class="button button-primary" target="_blank">
                                    <?php printf( esc_html__( 'View All %d', 'affiliates-for-woocommerce' ), $commissions_count ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="ddwcaf-chart-body" style="height: auto; min-height: unset;">
                            <table class="widefat fixed striped ddfw-table">
                                <thead>
                                    <tr>
                                        <th class="column-id"><?php esc_html_e( 'ID', 'affiliates-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Order', 'affiliates-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Product', 'affiliates-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Commission', 'affiliates-for-woocommerce' ); ?></th>
                                        <th><?php esc_html_e( 'Status & Date', 'affiliates-for-woocommerce' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $date_format = get_option( 'date_format' );
                                    $time_format = get_option( 'time_format' );
                                    foreach ( $commissions as $commission ) {
                                        $order = wc_get_order( $commission[ 'order_id' ] );
                                        if ( ! $order ) {
                                            continue;
                                        }
                                        $order_currency = $order->get_currency();
                                        $product        = wc_get_product( $commission[ 'product_id' ] );
                                        if ( ! $product ) {
                                            continue;
                                        }

                                        $buyer = '';
                                        if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
                                            $buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'affiliates-for-woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
                                        } elseif ( $order->get_billing_company() ) {
                                            $buyer = trim( $order->get_billing_company() );
                                        } elseif ( $order->get_customer_id() ) {
                                            $user  = get_user_by( 'id', $order->get_customer_id() );
                                            $buyer = ucwords( $user->display_name );
                                        }
                                        $buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $order );

                                        ob_start();
                                        ?>
                                        <mark class="ddwcaf-status ddwcaf-commission-status-<?php echo esc_attr( $commission[ 'status' ] ); ?>"><?php echo esc_html( $commission_helper->ddwcaf_get_translation( $commission[ 'status' ] ) ); ?></mark>
                                        <?php
                                        $status_html = ob_get_clean();

                                        if ( ! empty( $commission[ 'updated_at' ] ) && strtotime( $commission[ 'updated_at' ] ) > strtotime( $commission[ 'created_at' ] ) ) {
                                            $date = sprintf( esc_html__( 'Last Updated %s', 'affiliates-for-woocommerce' ), date_i18n( "{$date_format} {$time_format}", strtotime( $commission[ 'updated_at' ] ) ) );
                                        } else {
                                            $date = sprintf( esc_html__( 'Created %s', 'affiliates-for-woocommerce' ), date_i18n( "{$date_format} {$time_format}", strtotime( $commission[ 'created_at' ] ) ) );
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html( $commission[ 'id' ] ); ?></td>
                                            <td>
                                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) . '&action=edit' ) ); ?>">
                                                    <strong>#<?php echo esc_attr( $order->get_order_number() ); ?> <?php echo esc_html( $buyer ); ?></strong>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank" class="ddwcaf-product-name-wrapper">
                                                    <?php echo wp_kses_post( $product->get_image( 'thumbnail', [ 'class' => 'ddwcaf-product-thumbnail' ] ) ); ?>
                                                    <div><?php echo esc_html( $product->get_name() ); ?><span class="ddwcaf-product-quantity">x <?php echo esc_html( $commission[ 'quantity' ] ); ?></span></div>
                                                </a>
                                            </td>
                                            <td>
                                                <?php
                                                echo sprintf(
                                                    '<div class="ddwcaf-amount-column">
                                                        <div class="ddwcaf-commission-amount">
                                                            %1$s
                                                        </div>
                                                        <div class="ddwcaf-order-total">
                                                            %2$s %3$s
                                                        </div>
                                                        %4$s
                                                    </div>',
                                                    '<strong>' . wc_price( $commission[ 'commission' ], [ 'currency' => $order_currency ] ) . '</strong>',
                                                     esc_html__( 'Order:', 'affiliates-for-woocommerce' ),
                                                     wc_price( $commission[ 'line_total' ], [ 'currency' => $order_currency ] ),
                                                     $commission['refund'] > 0 ? '<div class="ddwcaf-refund-amount">' . esc_html__( 'Refund:', 'affiliates-for-woocommerce' ) . ' ' . wc_price( $commission['refund'], [ 'currency' => $order_currency ] ) . '</div>' : ''
                                                );
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                echo sprintf(
                                                    '<div class="ddwcaf-status-timeline-column">
                                                        <div class="ddwcaf-status-tier">
                                                            %1$s
                                                        </div>
                                                        <div class="ddwcaf-date-tier">
                                                            %2$s
                                                        </div>
                                                    </div>',
                                                    $status_html,
                                                    $date
                                                );
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }

                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php
        }

        /**
         * Render Summary Card
         *
         * @param string $title
         * @param mixed  $value
         * @param string $icon_svg
         * @param string $value_type
         */
        protected function render_summary_card( $title, $value, $icon_svg, $value_type = 'number' ) {
            ?>
            <div class="ddwcaf-summary-card">
                <div class="ddwcaf-card-header">
                    <div class="ddwcaf-card-icon"><?php echo $icon_svg; ?></div>
                </div>
                <div class="ddwcaf-card-content">
                    <h4><?php echo esc_html( $title ); ?></h4>
                    <div class="ddwcaf-card-value">
                        <?php if ( 'number' === $value_type ) : ?>
                            <span class="ddwcaf-value-number"><?php echo esc_html( number_format( (float) ( $value ?? 0 ) ) ); ?></span>
                        <?php elseif ( 'html' === $value_type ) : ?>
                            <span class="ddwcaf-value-text"><?php echo wp_kses_post( $value ); ?></span>
                        <?php else : ?>
                            <span class="ddwcaf-value-text"><?php echo esc_html( $value ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Get Withdrawal Details HTML
         *
         * @param DDWCAF_Affiliate_Helper $affiliate_helper
         * @param DDWCAF_Payout_Helper    $payout_helper
         * @param array                  $payout
         * @param int                    $affiliate_id
         * @param array                  $payment_info
         * @return string
         */
        protected function get_withdrawal_details_html( $affiliate_helper, $payout_helper, $payout, $affiliate_id, $payment_info ) {
            ob_start();
            ?>
            <table class="ddwcaf-withdrawal-info-table">
                <tbody>
                    <tr>
                        <th><?php esc_html_e( 'Payment Method', 'affiliates-for-woocommerce' ); ?></th>
                        <td><strong><?php echo esc_html( $affiliate_helper->ddwcaf_get_withdrawal_method_name( $payout[ 'payment_method' ] ) ); ?></strong></td>
                    </tr>
                    <?php if ( 'bacs' === $payout[ 'payment_method' ] ) : ?>
                        <?php if ( ! empty( $payment_info[ 'account_name' ] ) ) : ?>
                        <tr>
                            <th><?php esc_html_e( 'Account Name', 'affiliates-for-woocommerce' ); ?></th>
                            <td><?php echo esc_html( $payment_info[ 'account_name' ] ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( ! empty( $payment_info[ 'iban' ] ) ) : ?>
                        <tr>
                            <th><?php esc_html_e( 'IBAN', 'affiliates-for-woocommerce' ); ?></th>
                            <td><?php echo esc_html( $payment_info[ 'iban' ] ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( ! empty( $payment_info[ 'swift_code' ] ) ) : ?>
                        <tr>
                            <th><?php esc_html_e( 'Swift Code', 'affiliates-for-woocommerce' ); ?></th>
                            <td><?php echo esc_html( $payment_info[ 'swift_code' ] ); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th><?php esc_html_e( 'Payment Info', 'affiliates-for-woocommerce' ); ?></th>
                            <td><?php echo apply_filters( 'ddwcaf_modify_display_payment_info', esc_html( ! empty( $payout[ 'payment_info' ] ) ? $payout[ 'payment_info' ] : 'N/A' ), $payout ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php
            return ob_get_clean();
        }

        /**
         * Print Notification
         * 
         * @param string $message
         * @param string $type
         */
        protected function ddwcaf_print_notification( $message, $type = 'success' ) {
            ?>
            <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
            <?php
        }
	}
}
