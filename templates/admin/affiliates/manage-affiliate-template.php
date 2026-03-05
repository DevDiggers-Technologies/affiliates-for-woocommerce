<?php
/**
 * Manage Affiliates Template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Affiliates;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Dashboard_Helper;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Manage_Affiliate_Template' ) ) {
	/**
	 * Manage Affiliates Template class
	 */
	class DDWCAF_Manage_Affiliate_Template {
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
            $page                          = ! empty( $_GET[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : '';
            $menu                          = ! empty( $_GET[ 'menu' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) : '';
            $affiliate_helper              = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
            $visit_helper                  = new DDWCAF_Visit_Helper( $ddwcaf_configuration );
            $commission_helper             = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
            $affiliate_id                  = ! empty( $_GET[ 'id' ] ) ? intval( sanitize_text_field( wp_unslash( $_GET[ 'id' ] ) ) ) : '';
            $affiliate_registration_fields = $affiliate_helper->ddwcaf_get_affiliate_registration_fields();
            
            $dashboard_helper              = new DDWCAF_Dashboard_Helper( $ddwcaf_configuration );
            $dashboard_data                = $dashboard_helper->get_dashboard_data( $affiliate_id );

            $flag                          = false;

            usort( $affiliate_registration_fields, function( $first, $second ) {
                return strnatcmp( $first[ 'position' ], $second[ 'position' ] );
            } );

            if ( ! empty( $_POST[ 'ddwcaf_save_affiliate_info' ] ) && ! empty( $_POST[ 'ddwcaf_save_affiliate_info_nonce' ] ) && wp_verify_nonce( $_POST[ 'ddwcaf_save_affiliate_info_nonce' ], 'ddwcaf_save_affiliate_info_nonce_action' ) ) {
                $error = false;

                foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
                    if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] && ! empty( $affiliate_registration_field[ 'required' ] ) && ( ! isset( $_POST[ $affiliate_registration_field[ 'name' ] ] ) || '' === sanitize_text_field( wp_unslash( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ) ) ) {
                        $this->ddwcaf_print_notification( sprintf( esc_html__( '%s is required!', 'affiliates-for-woocommerce' ), $affiliate_registration_field[ 'label' ] ), 'error' );
                        $error = true;
                        break;
                    }
                }

                $data = [
                    'new_commission'           => ! empty( $_POST[ '_ddwcaf_notifications' ][ 'new_commission' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_notifications' ][ 'new_commission' ] ) ) : '',
                    'paid_commission'          => ! empty( $_POST[ '_ddwcaf_notifications' ][ 'paid_commission' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_notifications' ][ 'paid_commission' ] ) ) : '',
                    'commission_status_change' => ! empty( $_POST[ '_ddwcaf_notifications' ][ 'commission_status_change' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_notifications' ][ 'commission_status_change' ] ) ) : '',
                ];

                $commission_helper->ddwcaf_update_affiliate_commission_rate( $affiliate_id, ! empty( $_POST[ '_ddwcaf_commission_rate' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_commission_rate' ] ) ) : '' );
                $affiliate_helper->ddwcaf_prepare_and_save_affiliate_info( $affiliate_id );
                $affiliate_helper->ddwcaf_update_affiliate_withdrawal_methods( $affiliate_id, $_POST[ '_ddwcaf_withdrawal_methods' ] );
                $affiliate_helper->ddwcaf_update_affiliate_default_withdrawal_method( $affiliate_id, $_POST[ '_ddwcaf_default_withdrawal_method' ] );

                $token = ! empty( $_POST[ '_ddwcaf_referral_token' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_ddwcaf_referral_token' ] ) ) : '';

                $token_exists = $affiliate_helper->ddwcaf_get_affiliate_id_by_token( $token );

                if ( ! $token_exists || $token_exists === $affiliate_id ) {
                    $affiliate_helper->ddwcaf_update_affiliate_referral_token( $affiliate_id, $token );
                } else {
                    $this->ddwcaf_print_notification( esc_html__( 'Referral token already exists.', 'affiliates-for-woocommerce' ), 'error' );
                }

                if ( ! $error ) {
                    $affiliate_helper->ddwcaf_prepare_and_save_affiliate_info( $affiliate_id, $affiliate_registration_fields );

                    $this->ddwcaf_print_notification( esc_html__( 'Details are saved successfully.', 'affiliates-for-woocommerce' ), 'success' );
                } else {
                    $this->ddwcaf_print_notification( esc_html__( 'Kindly fill all the mandatory fields.', 'affiliates-for-woocommerce' ), 'error' );
                }
            }

            $args = [
                'affiliate_id' => $affiliate_id,
            ];

            $affiliate_referral_token  = $affiliate_helper->ddwcaf_get_affiliate_referral_token( $affiliate_id );
            $commission_rate           = $commission_helper->ddwcaf_get_affiliate_commission_rate( $affiliate_id );
            $withdrawal_methods        = $affiliate_helper->ddwcaf_get_affiliate_withdrawal_methods( $affiliate_id );
            $default_withdrawal_method = $affiliate_helper->ddwcaf_get_affiliate_default_withdrawal_method( $affiliate_id );
            $statistics                = $commission_helper->ddwcaf_get_affiliate_statistics( $affiliate_id );
            $visits_count              = $visit_helper->ddwcaf_get_visits_count( $args );
            $conversion_details        = $visit_helper->ddwcaf_get_conversion_details( $args );
            $affiliate_referral_url    = $affiliate_helper->ddwcaf_get_affiliate_referral_url( $affiliate_id );
            $affiliate                 = get_userdata( $affiliate_id );

            wp_enqueue_script( 'ddwcaf-manage-affiliate-script' );
            // Localize manage affiliate script with chart data
            wp_localize_script(
                'ddwcaf-manage-affiliate-script',
                'ddwcafDashboardData',
                [
                    'currencySymbol'   => get_woocommerce_currency_symbol(),
                    'charts'           => $dashboard_data['charts'],
                    'dateRange'        => $dashboard_data['date_range'],
                    'i18n'             => [
                        'earnings'          => esc_html__( 'Earnings', 'affiliates-for-woocommerce' ),
                        'visits'            => esc_html__( 'Visits', 'affiliates-for-woocommerce' ),
                        'noPerformanceData' => esc_html__( 'No performance data available', 'affiliates-for-woocommerce' ),
                    ]
                ]
            );
            ?>
            <div class="ddwcaf-manage-affiliate-container">
                <div class="ddwcaf-page-header">
                    <div class="ddwcaf-header-left">
                        <h1><?php echo esc_html__( 'View Affiliate', 'affiliates-for-woocommerce' ); ?></h1>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page . '&menu=' . $menu ) ); ?>" class="button"><?php esc_html_e( '← Back', 'affiliates-for-woocommerce' ); ?></a>
                    </div>
                    <div class="ddwcaf-header-right">
                        <div class="ddwcaf-dashboard-filters">
                            <form method="get" class="ddwcaf-date-filter-form">
                                <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
                                <input type="hidden" name="menu" value="<?php echo esc_attr( $menu ); ?>" />
                                <input type="hidden" name="action" value="<?php echo esc_attr( ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'view' ); ?>" />
                                <input type="hidden" name="id" value="<?php echo esc_attr( $affiliate_id ); ?>" />

                                <div class="ddwcaf-date-range-container">
                                    <input type="text"
                                        id="ddwcaf-date-range-picker"
                                        class="ddwcaf-date-range-picker"
                                        value="<?php echo esc_attr( $dashboard_data['date_range']['label'] ); ?>"
                                        readonly />

                                    <div class="ddwcaf-date-range-dropdown" id="ddwcaf-date-range-dropdown">
                                        <div class="ddwcaf-dropdown-content">
                                            <div class="ddwcaf-date-presets">
                                                <div class="ddwcaf-presets-header">
                                                    <h4><?php esc_html_e( 'Quick Select', 'affiliates-for-woocommerce' ); ?></h4>
                                                </div>
                                                <button type="button" class="ddwcaf-date-preset <?php echo 'today' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="today"><?php esc_html_e( 'Today', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo '7_days' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="7_days"><?php esc_html_e( 'Last 7 Days', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo 'last_week' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="last_week"><?php esc_html_e( 'Last Week', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo '30_days' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="30_days"><?php esc_html_e( 'Last 30 Days', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo 'last_month' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="last_month"><?php esc_html_e( 'Last Month', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo '90_days' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="90_days"><?php esc_html_e( 'Last 3 Months', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo '180_days' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="180_days"><?php esc_html_e( 'Last 6 Months', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo 'year_to_date' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="year_to_date"><?php esc_html_e( 'Year to Date', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo 'last_year' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="last_year"><?php esc_html_e( 'Last Year', 'affiliates-for-woocommerce' ); ?></button>
                                                <button type="button" class="ddwcaf-date-preset <?php echo 'all_time' === $dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="all_time"><?php esc_html_e( 'All Time', 'affiliates-for-woocommerce' ); ?></button>
                                            </div>

                                            <div class="ddwcaf-custom-date-range">
                                                <div class="ddwcaf-custom-header">
                                                    <h4><?php esc_html_e( 'Custom Range', 'affiliates-for-woocommerce' ); ?></h4>
                                                    <p><?php esc_html_e( 'Select a start and end date', 'affiliates-for-woocommerce' ); ?></p>
                                                </div>
                                                <div class="ddwcaf-date-inputs">
                                                    <div class="ddwcaf-date-input-group">
                                                        <label for="ddwcaf-from-date"><?php esc_html_e( 'From Date', 'affiliates-for-woocommerce' ); ?></label>
                                                        <input type="date" name="from_date" id="ddwcaf-from-date" value="<?php echo esc_attr( $_GET['from_date'] ?? $dashboard_data['date_range']['from'] ); ?>" />
                                                    </div>
                                                    <div class="ddwcaf-date-input-group">
                                                        <label for="ddwcaf-to-date"><?php esc_html_e( 'To Date', 'affiliates-for-woocommerce' ); ?></label>
                                                        <input type="date" name="to_date" id="ddwcaf-to-date" value="<?php echo esc_attr( $_GET['to_date'] ?? $dashboard_data['date_range']['to'] ); ?>" />
                                                    </div>
                                                </div>
                                                <div class="ddwcaf-custom-range-actions">
                                                    <button type="button" class="button button-primary ddwcaf-apply-custom-range"><?php esc_html_e( 'Apply Range', 'affiliates-for-woocommerce' ); ?></button>
                                                </div>
                                            </div>

                                            <input type="hidden" name="date_range" id="ddwcaf-selected-range" value="<?php echo esc_attr( $_GET['date_range'] ?? '30_days' ); ?>" />
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="ddwcaf-affiliate-profile-card">
                    <div class="ddwcaf-profile-avatar">
                        <img src="<?php echo esc_url( get_avatar_url( $affiliate_id, [ 'size' => 100 ] ) ); ?>" alt="<?php echo esc_attr( $affiliate->display_name ); ?>" />
                    </div>
                    <div class="ddwcaf-profile-content">
                        <div class="ddwcaf-profile-name-row">
                            <h3><?php echo esc_html( $affiliate->display_name ); ?></h3>
                            <span class="ddwcaf-user-id-badge">#<?php echo esc_html( $affiliate_id ); ?></span>
                            <span class="ddwcaf-status <?php echo esc_attr( 'ddwcaf-affiliate-status-' . esc_html( $affiliate_helper->ddwcaf_get_affiliate_status( $affiliate_id ) ) ); ?>">
                                <?php echo esc_html( ucfirst( $affiliate_helper->ddwcaf_get_affiliate_status( $affiliate_id ) ) ); ?>
                            </span>
                        </div>
                        
                        <div class="ddwcaf-profile-meta-grid">
                            <div class="ddwcaf-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                                <span><?php echo esc_attr( $affiliate->user_email ); ?></span>
                            </div>
                            <div class="ddwcaf-meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <span><?php printf( esc_html__( 'Joined: %s', 'affiliates-for-woocommerce' ), date_i18n( get_option( 'date_format' ), strtotime( $affiliate->user_registered ) ) ); ?></span>
                            </div>
                        </div>

                        <div class="ddwcaf-profile-referral-box">
                            <div class="ddwcaf-referral-input-group">
                                <span class="ddwcaf-referral-url-text" id="ddwcaf-referral-url-input"><?php echo esc_url( $affiliate_referral_url ); ?></span>
                                <button type="button" class="ddwcaf-copy-url-btn" title="<?php esc_attr_e( 'Copy URL', 'affiliates-for-woocommerce' ); ?>" data-target="ddwcaf-referral-url-input">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

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
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path></svg>',
                        'html'
                    );

                    $this->render_summary_card(
                        esc_html__( 'Unpaid Amount', 'affiliates-for-woocommerce' ),
                        wc_price( $statistics[ 'unpaid_earnings' ] ?? 0 ),
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>',
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

                <div class="ddwcaf-manage-affiliate-charts-section">
                    <div class="ddwcaf-chart-container">
                        <div class="ddwcaf-chart-header">
                            <h3><?php esc_html_e( 'Performance Overview', 'affiliates-for-woocommerce' ); ?></h3>
                            <span><?php echo esc_html( $dashboard_data['date_range']['label'] ); ?></span>
                        </div>
                        <div class="ddwcaf-chart-body">
                            <canvas id="ddwcaf-performance-chart"></canvas>
                        </div>
                    </div>
                </div>

                <?php
                // Prepare withdrawal methods options
                $available_withdrawal_methods = [];
                foreach ( $ddwcaf_configuration[ 'withdrawal_methods' ] as $key => $withdrawal_method ) {
                    if ( ! empty( $withdrawal_method[ 'available' ] ) && ! empty( $withdrawal_method[ 'status' ] ) ) {
                        $available_withdrawal_methods[ $key ] = $affiliate_helper->ddwcaf_get_withdrawal_method_name( $key );
                    }
                }

                // Build DDFW Layout args
                $layout_args = [
                    [
                        'id'     => 'ddwcaf-affiliate-details-section',
                        'header' => [
                            'heading'     => esc_html__( 'Details', 'affiliates-for-woocommerce' ),
                            'description' => esc_html__( 'Manage the affiliate\'s core details including referral tokens and commission rates.', 'affiliates-for-woocommerce' ),
                        ],
                        'fields' => [
                            [
                                'type'        => 'text',
                                'id'          => 'ddwcaf-referral-token',
                                'name'        => '_ddwcaf_referral_token',
                                'label'       => esc_html__( 'Referral Token', 'affiliates-for-woocommerce' ),
                                'placeholder' => esc_html__( 'Enter your referral token', 'affiliates-for-woocommerce' ),
                                'description' => esc_html__( 'You can use the brand name as a referral token which allows "friendly" looking referral links.', 'affiliates-for-woocommerce' ),
                                'value'       => $affiliate_referral_token,
                            ],
                            [
                                'type'              => 'number',
                                'id'                => 'ddwcaf-commission-rate',
                                'name'              => '_ddwcaf_commission_rate',
                                'label'             => esc_html__( 'Commission Rate', 'affiliates-for-woocommerce' ),
                                'placeholder'       => esc_html__( 'Enter your commission rate', 'affiliates-for-woocommerce' ),
                                'description'       => esc_html__( 'You can enter any specific commission rate for the affiliate and if not entered then global rate is used.', 'affiliates-for-woocommerce' ),
                                'value'             => $commission_rate,
                                'custom_attributes' => [
                                    'min'  => 0,
                                    'step' => .01,
                                ],
                            ],
                        ],
                    ],
                    [
                        'id'     => 'ddwcaf-email-notifications-section',
                        'header' => [
                            'heading'     => esc_html__( 'Email Notifications', 'affiliates-for-woocommerce' ),
                            'description' => esc_html__( 'Configure which email notifications this affiliate should receive.', 'affiliates-for-woocommerce' ),
                        ],
                        'class' => 'ddfw-upgrade-to-pro-tag-wrapper',
                        'fields' => [
                            [
                                'type'           => 'checkbox',
                                'checkbox_label' => esc_html__( 'Notify on New Commissions', 'affiliates-for-woocommerce' ),
                                'id'             => 'ddwcaf-notifications-new-commission',
                                'name'           => '_ddwcaf_notifications[new_commission]',
                                'description'    => esc_html__( 'Get an email when a new commission is made and its status switches to pending.', 'affiliates-for-woocommerce' ),
                            ],
                            [
                                'type'           => 'checkbox',
                                'checkbox_label' => esc_html__( 'Notify on Paid Commissions', 'affiliates-for-woocommerce' ),
                                'id'             => 'ddwcaf-notifications-paid-commission',
                                'name'           => '_ddwcaf_notifications[paid_commission]',
                                'description'    => esc_html__( 'Get an email when a commission status changes to paid.', 'affiliates-for-woocommerce' ),
                            ],
                            [
                                'type'           => 'checkbox',
                                'checkbox_label' => esc_html__( 'Notify on Commissions Status Change', 'affiliates-for-woocommerce' ),
                                'id'             => 'ddwcaf-notifications-commission-status-change',
                                'name'           => '_ddwcaf_notifications[commission_status_change]',
                                'description'    => esc_html__( 'Get an email when a commission status changes.', 'affiliates-for-woocommerce' ),
                            ],
                        ],
                    ],
                    [
                        'id'                => 'ddwcaf-account-info-section',
                        'header'            => [
                            'heading'     => esc_html__( 'Account Info', 'affiliates-for-woocommerce' ),
                            'description' => esc_html__( 'Update the affiliate\'s personal and account information.', 'affiliates-for-woocommerce' ),
                        ],
                        'after_header_html' => $this->get_affiliate_registration_fields_html( $affiliate_helper, $affiliate_id ),
                    ],
                    [
                        'id'                => 'ddwcaf-withdrawal-info-section',
                        'header'            => [
                            'heading'     => esc_html__( 'Withdrawal Info', 'affiliates-for-woocommerce' ),
                            'description' => esc_html__( 'Manage withdrawal methods and payment details for commission payouts.', 'affiliates-for-woocommerce' ),
                        ],
                        'fields'            => [
                            [
                                'type'    => 'select',
                                'label'   => esc_html__( 'Default Withdrawal Method', 'affiliates-for-woocommerce' ),
                                'id'      => 'ddwcaf-default-withdrawal-method',
                                'name'    => '_ddwcaf_default_withdrawal_method',
                                'options'     => $available_withdrawal_methods,
                                'description' => esc_html__( 'Select the withdrawal method which will be used for payouts.', 'affiliates-for-woocommerce' ),
                                'value'       => ! empty( $default_withdrawal_method ) ? $default_withdrawal_method : '',
                            ],
                            [
                                'type'  => 'heading',
                                'label' => esc_html__( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ),
                            ],
                            [
                                'type'        => 'text',
                                'label'       => esc_html__( 'Account Name', 'affiliates-for-woocommerce' ),
                                'id'          => 'ddwcaf-withdrawal-account-name',
                                'name'        => '_ddwcaf_withdrawal_methods[bacs][account_name]',
                                'placeholder' => esc_html__( 'Enter your account name', 'affiliates-for-woocommerce' ),
                                'description' => esc_html__( 'Enter the name of the bank account holder.', 'affiliates-for-woocommerce' ),
                                'value'       => ! empty( $withdrawal_methods[ 'bacs' ][ 'account_name' ] ) ? $withdrawal_methods[ 'bacs' ][ 'account_name' ] : '',
                            ],
                            [
                                'type'        => 'text',
                                'label'       => esc_html__( 'IBAN', 'affiliates-for-woocommerce' ),
                                'id'          => 'ddwcaf-withdrawal-iban',
                                'name'        => '_ddwcaf_withdrawal_methods[bacs][iban]',
                                'placeholder' => esc_html__( 'Enter your account iban', 'affiliates-for-woocommerce' ),
                                'description' => esc_html__( 'Enter the International Bank Account Number (IBAN).', 'affiliates-for-woocommerce' ),
                                'value'       => ! empty( $withdrawal_methods[ 'bacs' ][ 'iban' ] ) ? $withdrawal_methods[ 'bacs' ][ 'iban' ] : '',
                            ],
                            [
                                'type'        => 'text',
                                'label'       => esc_html__( 'Swift Code', 'affiliates-for-woocommerce' ),
                                'id'          => 'ddwcaf-withdrawal-swift-code',
                                'name'        => '_ddwcaf_withdrawal_methods[bacs][swift_code]',
                                'placeholder' => esc_html__( 'Enter your account swift code', 'affiliates-for-woocommerce' ),
                                'description' => esc_html__( 'Enter the Bank Identifier Code (BIC) or SWIFT code.', 'affiliates-for-woocommerce' ),
                                'value'       => ! empty( $withdrawal_methods[ 'bacs' ][ 'swift_code' ] ) ? $withdrawal_methods[ 'bacs' ][ 'swift_code' ] : '',
                            ],
                            [
                                'type'  => 'heading',
                                'label' => esc_html__( 'PayPal Email', 'affiliates-for-woocommerce' ),
                            ],
                            [
                                'type'        => 'text',
                                'label'       => esc_html__( 'PayPal Email', 'affiliates-for-woocommerce' ),
                                'id'          => 'ddwcaf-withdrawal-paypal-email',
                                'name'        => '_ddwcaf_withdrawal_methods[paypal_email]',
                                'placeholder' => esc_html__( 'Enter your PayPal email', 'affiliates-for-woocommerce' ),
                                'description' => esc_html__( 'Enter the email address associated with your PayPal account.', 'affiliates-for-woocommerce' ),
                                'value'       => ! empty( $withdrawal_methods[ 'paypal_email' ] ) ? $withdrawal_methods[ 'paypal_email' ] : '',
                            ],
                        ],
                    ],
                ];

                // Render using DDFW_Layout
                $layout = new DDFW_Layout();
                $layout->get_form_section_layout(
                    $layout_args,
                    '',
                    [
                        'name'  => 'ddwcaf_save_affiliate_info',
                        'value' => esc_html__( 'Save Changes', 'affiliates-for-woocommerce' ),
                    ]
                );
                ?>
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
         * Get Affiliate Registration Fields HTML
         * 
         * @param DDWCAF_Affiliate_Helper $affiliate_helper
         * @param int $affiliate_id
         * @return string
         */
        protected function get_affiliate_registration_fields_html( $affiliate_helper, $affiliate_id ) {
            ob_start();
            ?>
            <table class="form-table">
                <tbody>
                    <?php $affiliate_helper->ddwcaf_display_affiliate_registration_fields( $affiliate_id ); ?>
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
