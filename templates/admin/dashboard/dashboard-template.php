<?php
/**
 * Dashboard Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Dashboard;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Dashboard_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Dashboard_Template' ) ) {
	/**
	 * Dashboard template class
	 */
	class DDWCAF_Dashboard_Template {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcaf_configuration;

		/**
		 * Dashboard Helper Variable
		 *
		 * @var object
		 */
		protected $dashboard_helper;

		/**
		 * Commission Helper Variable
		 *
		 * @var array
		 */
		protected $commission_helper;

		/**
		 * Dashboard Data Variable
		 *
		 * @var array
		 */
		protected $dashboard_data;

		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
			$this->ddwcaf_configuration = $ddwcaf_configuration;
			$this->dashboard_helper     = new DDWCAF_Dashboard_Helper( $ddwcaf_configuration );
			$this->commission_helper    = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
			$this->dashboard_data       = $this->dashboard_helper->get_dashboard_data();

			$this->render();
		}

		/**
		 * Render dashboard
		 *
		 * @return void
		 */
		protected function render() {
			wp_enqueue_script( 'ddwcaf-dashboard-script' );

			// Localize dashboard script with chart data
			wp_localize_script(
				'ddwcaf-dashboard-script',
				'ddwcafDashboardData',
				[
					'currencySymbol'   => get_woocommerce_currency_symbol(),
					'charts'           => $this->dashboard_data['charts'],
					'dateRange'        => $this->dashboard_data['date_range'],
					'i18n'             => [
						'earnings'          => esc_html__( 'Earnings', 'affiliates-for-woocommerce' ),
						'visits'            => esc_html__( 'Visits', 'affiliates-for-woocommerce' ),
						'affiliateRevenue'  => esc_html__( 'Affiliate Revenue', 'affiliates-for-woocommerce' ),
						'storeRevenue'      => esc_html__( 'Store Revenue', 'affiliates-for-woocommerce' ),
						'conversions'       => esc_html__( 'Conversions', 'affiliates-for-woocommerce' ),
						'noPerformanceData' => esc_html__( 'No performance data available', 'affiliates-for-woocommerce' ),
					]
				]
			);

			$current_user = wp_get_current_user();

			?>
			<div class="ddwcaf-dashboard">
				<div class="ddwcaf-dashboard-header">
					<div class="ddwcaf-header-top">
						<div class="ddwcaf-header-left">
							<div class="ddwcaf-welcome-section">
								<div class="ddwcaf-welcome-content">
									<div class="ddwcaf-admin-avatar">
										<img src="<?php echo esc_url( get_avatar_url( $current_user->ID, [ 'size' => 48 ] ) ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" class="ddwcaf-avatar-image" />
									</div>
									<div class="ddwcaf-welcome-message">
										<h1><?php printf( esc_html__( 'Welcome back, %s! 👋🏻', 'affiliates-for-woocommerce' ), $current_user->display_name ); ?></h1>
										<p class="ddwcaf-welcome-subtitle"><?php esc_html_e( 'Here\'s what\'s happening with your affiliate program', 'affiliates-for-woocommerce' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<div class="ddwcaf-header-right">
							<div class="ddwcaf-dashboard-filters">
								<form method="get" class="ddwcaf-date-filter-form">
									<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ?? 'ddwcaf-dashboard' ); ?>" />
									<input type="hidden" name="menu" value="<?php echo esc_attr( $_GET['menu'] ?? 'dashboard' ); ?>" />

									<div class="ddwcaf-date-range-container">
										<input type="text"
											id="ddwcaf-date-range-picker"
											class="ddwcaf-date-range-picker"
											value="<?php echo esc_attr( $this->dashboard_data['date_range']['label'] ); ?>"
											readonly />

										<div class="ddwcaf-date-range-dropdown" id="ddwcaf-date-range-dropdown">
											<div class="ddwcaf-dropdown-content">
												<div class="ddwcaf-date-presets">
													<div class="ddwcaf-presets-header">
														<h4><?php esc_html_e( 'Quick Select', 'affiliates-for-woocommerce' ); ?></h4>
													</div>
													<button type="button" class="ddwcaf-date-preset <?php echo 'today' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="today"><?php esc_html_e( 'Today', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo '7_days' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="7_days"><?php esc_html_e( 'Last 7 Days', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo 'last_week' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="last_week"><?php esc_html_e( 'Last Week', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo '30_days' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="30_days"><?php esc_html_e( 'Last 30 Days', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo 'last_month' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="last_month"><?php esc_html_e( 'Last Month', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo '90_days' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="90_days"><?php esc_html_e( 'Last 3 Months', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo '180_days' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="180_days"><?php esc_html_e( 'Last 6 Months', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo 'year_to_date' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="year_to_date"><?php esc_html_e( 'Year to Date', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo 'last_year' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="last_year"><?php esc_html_e( 'Last Year', 'affiliates-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcaf-date-preset <?php echo 'all_time' === $this->dashboard_data['date_range']['type'] ? 'active' : ''; ?>" data-range="all_time"><?php esc_html_e( 'All Time', 'affiliates-for-woocommerce' ); ?></button>
												</div>

												<div class="ddwcaf-custom-date-range">
													<div class="ddwcaf-custom-header">
														<h4><?php esc_html_e( 'Custom Range', 'affiliates-for-woocommerce' ); ?></h4>
														<p><?php esc_html_e( 'Select a start and end date', 'affiliates-for-woocommerce' ); ?></p>
													</div>
													<div class="ddwcaf-date-inputs">
														<div class="ddwcaf-date-input-group">
															<label for="ddwcaf-from-date"><?php esc_html_e( 'From Date', 'affiliates-for-woocommerce' ); ?></label>
															<input type="date" name="from_date" id="ddwcaf-from-date" value="<?php echo esc_attr( $_GET['from_date'] ?? $this->dashboard_data['date_range']['from'] ); ?>" />
														</div>
														<div class="ddwcaf-date-input-group">
															<label for="ddwcaf-to-date"><?php esc_html_e( 'To Date', 'affiliates-for-woocommerce' ); ?></label>
															<input type="date" name="to_date" id="ddwcaf-to-date" value="<?php echo esc_attr( $_GET['to_date'] ?? $this->dashboard_data['date_range']['to'] ); ?>" />
														</div>
													</div>
													<button type="button" class="ddwcaf-apply-custom-range button button-primary"><?php esc_html_e( 'Apply Custom Range', 'affiliates-for-woocommerce' ); ?></button>
												</div>
											</div>
										</div>

										<input type="hidden" name="date_range" id="ddwcaf-selected-range" value="<?php echo esc_attr( $_GET['date_range'] ?? '30_days' ); ?>" />
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="ddwcaf-dashboard-top-section">
					<div class="ddwcaf-summary-cards">
						<?php
						$this->render_summary_card(
							esc_html__( 'Total Earnings', 'affiliates-for-woocommerce' ),
							wc_price( $this->dashboard_data['summary']['total_earnings']['value'] ),
							$this->dashboard_data['summary']['total_earnings']['change'],
							$this->dashboard_data['summary']['total_earnings']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"></circle><path d="M18.09 10.37A6 6 0 1 1 10.34 18"></path><path d="M7 6h1v4"></path><path d="m16.71 13.88.7.71-2.82 2.82"></path></svg>',
							'html'
						);

						$this->render_summary_card(
							esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
							$this->dashboard_data['summary']['commissions']['value'],
							$this->dashboard_data['summary']['commissions']['change'],
							$this->dashboard_data['summary']['commissions']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>'
						);

						$this->render_summary_card(
							esc_html__( 'Total Sales', 'affiliates-for-woocommerce' ),
							wc_price( $this->dashboard_data['summary']['total_sales']['value'] ),
							$this->dashboard_data['summary']['total_sales']['change'],
							$this->dashboard_data['summary']['total_sales']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
							'html'
						);

						$this->render_summary_card(
							esc_html__( 'Total Visits', 'affiliates-for-woocommerce' ),
							$this->dashboard_data['summary']['visits']['value'],
							$this->dashboard_data['summary']['visits']['change'],
							$this->dashboard_data['summary']['visits']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
						);

						$this->render_summary_card(
							esc_html__( 'Conversion Rate', 'affiliates-for-woocommerce' ),
							$this->dashboard_data['summary']['conversion_rate']['value'] . '%',
							$this->dashboard_data['summary']['conversion_rate']['change'],
							$this->dashboard_data['summary']['conversion_rate']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
							'text'
						);

						$this->render_summary_card(
							esc_html__( 'New Affiliates', 'affiliates-for-woocommerce' ),
							$this->dashboard_data['summary']['new_affiliates']['value'],
							$this->dashboard_data['summary']['new_affiliates']['change'],
							$this->dashboard_data['summary']['new_affiliates']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'
						);

						$this->render_summary_card(
							esc_html__( 'Active Affiliates', 'affiliates-for-woocommerce' ),
							$this->dashboard_data['summary']['active_affiliates']['value'],
							$this->dashboard_data['summary']['active_affiliates']['change'],
							$this->dashboard_data['summary']['active_affiliates']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8l1 1 2-2"/></svg>'
						);

						$this->render_summary_card(
							esc_html__( 'Total Payouts', 'affiliates-for-woocommerce' ),
							wc_price( $this->dashboard_data['summary']['total_payouts']['value'] ),
							$this->dashboard_data['summary']['total_payouts']['change'],
							$this->dashboard_data['summary']['total_payouts']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>',
							'html'
						);

						$this->render_summary_card(
							esc_html__( 'Pending Earnings', 'affiliates-for-woocommerce' ),
							wc_price( $this->dashboard_data['summary']['pending_earnings']['value'] ),
							$this->dashboard_data['summary']['pending_earnings']['change'],
							$this->dashboard_data['summary']['pending_earnings']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
							'html'
						);

						$this->render_summary_card(
							esc_html__( 'Avg Commission', 'affiliates-for-woocommerce' ),
							wc_price( $this->dashboard_data['summary']['avg_commission_value']['value'] ),
							$this->dashboard_data['summary']['avg_commission_value']['change'],
							$this->dashboard_data['summary']['avg_commission_value']['is_positive'],
							'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>',
							'html'
						);
						?>
					</div>
				</div>

				<div class="ddwcaf-dashboard-charts-section ddwcaf-dashboard-charts-section-full-width">
					<div class="ddwcaf-chart-container">
						<h3>
							<?php esc_html_e( 'Performance Overview', 'affiliates-for-woocommerce' ); ?>
							<span class="ddwcaf-chart-date-range"><?php echo esc_html( $this->dashboard_data['date_range']['label'] ); ?></span>
						</h3>
						<div class="ddwcaf-chart">
							<canvas id="ddwcaf-performance-chart"></canvas>
						</div>
					</div>
				</div>

				<div class="ddwcaf-dashboard-charts-section">
					<div class="ddwcaf-chart-container">
						<h3><?php esc_html_e( 'Revenue Impact', 'affiliates-for-woocommerce' ); ?></h3>
						<div class="ddwcaf-chart">
							<canvas id="ddwcaf-revenue-impact-chart"></canvas>
						</div>
					</div>

					<div class="ddwcaf-chart-container">
						<h3><?php esc_html_e( 'Conversion Sources', 'affiliates-for-woocommerce' ); ?></h3>
						<div class="ddwcaf-chart">
							<canvas id="ddwcaf-conversion-sources-chart"></canvas>
						</div>
					</div>
				</div>

				<div class="ddwcaf-dashboard-tables-section">
					<div class="ddwcaf-sidebar-widget">
						<h3><?php esc_html_e( 'Recent Commissions', 'affiliates-for-woocommerce' ); ?></h3>
						<?php $this->render_recent_commissions_list(); ?>
					</div>

					<div class="ddwcaf-sidebar-widget">
						<h3><?php esc_html_e( 'Top Affiliates', 'affiliates-for-woocommerce' ); ?></h3>
						<?php $this->render_top_affiliates_list(); ?>
					</div>
				</div>
			</div>
			<?php
		}

		protected function render_summary_card( $title, $value, $change, $is_positive, $icon_svg, $value_type = 'number' ) {
			?>
			<div class="ddwcaf-summary-card">
				<div class="ddwcaf-card-header">
					<div class="ddwcaf-card-icon"><?php echo $icon_svg; ?></div>
					<?php if ( $change !== 0 ) : ?>
						<div class="ddwcaf-change-indicator <?php echo esc_attr( $is_positive ? 'positive' : 'negative' ); ?>">
							<?php if ( $is_positive ) : ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 6l-9.5 9.5-5-5L1 18M17 6h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php else : ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 18l-9.5-9.5-5 5L1 6M17 18h6v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php endif; ?>
							<span><?php echo esc_html( abs( $change ) ); ?>%</span>
						</div>
					<?php endif; ?>
				</div>
				<div class="ddwcaf-card-content">
					<h4><?php echo esc_html( $title ); ?></h4>
					<div class="ddwcaf-card-value">
						<?php if ( 'number' === $value_type ) : ?>
							<span class="ddwcaf-value-number"><?php echo esc_html( number_format( $value ) ); ?></span>
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

		protected function render_recent_commissions_list() {
			$commissions = $this->dashboard_data['recent_activities']['commissions'];
			?>
			<div class="ddwcaf-recent-items">
				<?php if ( ! empty( $commissions ) ) : ?>
					<?php foreach ( $commissions as $commission ) : ?>
						<div class="ddwcaf-recent-item">
							<div class="ddwcaf-recent-info">
								<div class="ddwcaf-recent-customer">
									<strong><?php echo esc_html( $commission['display_name'] ?: __( 'Guest', 'affiliates-for-woocommerce' ) ); ?></strong>
								</div>
								<div class="ddwcaf-recent-action">
									<?php echo esc_html( $this->commission_helper->ddwcaf_get_translation( $commission['status'] ) ); ?>
									<span class="ddwcaf-recent-date">• <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $commission['created_at'] ) ) ); ?></span>
								</div>
							</div>
							<div class="ddwcaf-recent-points">
								<span class="ddwcaf-added"><?php echo wc_price( $commission['commission'] ); ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="ddwcaf-no-data"><?php esc_html_e( 'No recent commissions', 'affiliates-for-woocommerce' ); ?></div>
				<?php endif; ?>
			</div>
			<?php
		}

		protected function render_top_affiliates_list() {
			$affiliates = $this->dashboard_data['top_affiliates'];
			?>
			<div class="ddwcaf-customers-items">
				<?php if ( ! empty( $affiliates ) ) : ?>
					<?php foreach ( $affiliates as $affiliate ) : ?>
						<div class="ddwcaf-customer-item">
							<div class="ddwcaf-customer-avatar">
								<?php echo get_avatar( $affiliate['ID'], 32 ); ?>
							</div>
							<div class="ddwcaf-customer-info">
								<div class="ddwcaf-customer-name">
									<strong><?php echo esc_html( $affiliate['display_name'] ); ?></strong>
								</div>
								<div class="ddwcaf-customer-balance">
									<?php echo wc_price( $affiliate['total_earnings'] ); ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="ddwcaf-no-data"><?php esc_html_e( 'No affiliate data', 'affiliates-for-woocommerce' ); ?></div>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
