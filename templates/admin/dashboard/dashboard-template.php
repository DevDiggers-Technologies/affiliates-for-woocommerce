<?php
/**
 * Dashboard Template
 *
 * Builds the dashboard configuration and renders it through the shared devdiggers-framework
 * dashboard builder ( DDFW_Dashboard ). The plugin only supplies data + config.
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Dashboard;

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Dashboard_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DevDiggers\Framework\Includes\DDFW_Dashboard;

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
		 * @var DDWCAF_Dashboard_Helper
		 */
		protected $dashboard_helper;

		/**
		 * Commission Helper Variable
		 *
		 * @var DDWCAF_Commission_Helper
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
		 * @param array $ddwcaf_configuration Plugin configuration.
		 */
		public function __construct( $ddwcaf_configuration ) {
			$this->ddwcaf_configuration = $ddwcaf_configuration;

			if ( ! class_exists( '\\DevDiggers\\Framework\\Includes\\DDFW_Dashboard' ) ) {
				return;
			}

			$this->dashboard_helper  = new DDWCAF_Dashboard_Helper( $ddwcaf_configuration );
			$this->commission_helper = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
			$this->dashboard_data    = $this->dashboard_helper->get_dashboard_data();

			$this->render();
		}

		/**
		 * Merge separate { period, value } series lists into flat chart rows keyed by date.
		 *
		 * @param array $series_map Map of output_key => list of [ 'period' => ..., 'value' => ... ].
		 * @return array Flat rows: [ [ 'date' => ..., <key> => value, ... ], ... ].
		 */
		protected function merge_series( array $series_map ) {
			$rows = [];
			$keys = array_keys( $series_map );

			foreach ( $series_map as $key => $list ) {
				foreach ( (array) $list as $row ) {
					$period = $row['period'];
					if ( ! isset( $rows[ $period ] ) ) {
						$rows[ $period ] = [ 'date' => $period ];
						foreach ( $keys as $k ) {
							$rows[ $period ][ $k ] = 0;
						}
					}
					$rows[ $period ][ $key ] = $row['value'];
				}
			}

			ksort( $rows );

			return array_values( $rows );
		}

		/**
		 * Render dashboard via the shared framework builder.
		 *
		 * @return void
		 */
		protected function render() {
			$data       = $this->dashboard_data;
			$summary    = $data['summary'];
			$date_label = $data['date_range']['label'];

			$performance = $this->merge_series(
				[
					'earnings' => $data['charts']['performance']['earnings'] ?? [],
					'visits'   => $data['charts']['performance']['visits'] ?? [],
				]
			);
			$revenue_impact = $this->merge_series(
				[
					'affiliate_revenue' => $data['charts']['revenue_impact']['affiliate'] ?? [],
					'store_revenue'     => $data['charts']['revenue_impact']['total'] ?? [],
				]
			);

			new DDFW_Dashboard(
				[
					'columns' => 5,
					'header'  => [
						/* translators: %s: current user display name. */
						'welcome'  => esc_html__( 'Welcome back, %s! 👋🏻', 'affiliates-for-woocommerce' ),
						'subtitle' => esc_html__( 'Here\'s what\'s happening with your affiliate program', 'affiliates-for-woocommerce' ),
					],
					'summary_cards' => [
						[
							'title'       => esc_html__( 'Total Earnings', 'affiliates-for-woocommerce' ),
							'value'       => wc_price( $summary['total_earnings']['value'] ),
							'change'      => $summary['total_earnings']['change'],
							'is_positive' => $summary['total_earnings']['is_positive'],
							'value_type'  => 'html',
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"></circle><path d="M18.09 10.37A6 6 0 1 1 10.34 18"></path><path d="M7 6h1v4"></path><path d="m16.71 13.88.7.71-2.82 2.82"></path></svg>',
						],
						[
							'title'       => esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
							'value'       => $summary['commissions']['value'],
							'change'      => $summary['commissions']['change'],
							'is_positive' => $summary['commissions']['is_positive'],
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
						],
						[
							'title'       => esc_html__( 'Total Sales', 'affiliates-for-woocommerce' ),
							'value'       => wc_price( $summary['total_sales']['value'] ),
							'change'      => $summary['total_sales']['change'],
							'is_positive' => $summary['total_sales']['is_positive'],
							'value_type'  => 'html',
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
						],
						[
							'title'       => esc_html__( 'Total Visits', 'affiliates-for-woocommerce' ),
							'value'       => $summary['visits']['value'],
							'change'      => $summary['visits']['change'],
							'is_positive' => $summary['visits']['is_positive'],
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
						],
						[
							'title'       => esc_html__( 'Conversion Rate', 'affiliates-for-woocommerce' ),
							'value'       => $summary['conversion_rate']['value'] . '%',
							'change'      => $summary['conversion_rate']['change'],
							'is_positive' => $summary['conversion_rate']['is_positive'],
							'value_type'  => 'text',
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
						],
						[
							'title'       => esc_html__( 'New Affiliates', 'affiliates-for-woocommerce' ),
							'value'       => $summary['new_affiliates']['value'],
							'change'      => $summary['new_affiliates']['change'],
							'is_positive' => $summary['new_affiliates']['is_positive'],
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
						],
						[
							'title'       => esc_html__( 'Active Affiliates', 'affiliates-for-woocommerce' ),
							'value'       => $summary['active_affiliates']['value'],
							'change'      => $summary['active_affiliates']['change'],
							'is_positive' => $summary['active_affiliates']['is_positive'],
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8l1 1 2-2"/></svg>',
						],
						[
							'title'       => esc_html__( 'Total Payouts', 'affiliates-for-woocommerce' ),
							'value'       => wc_price( $summary['total_payouts']['value'] ),
							'change'      => $summary['total_payouts']['change'],
							'is_positive' => $summary['total_payouts']['is_positive'],
							'value_type'  => 'html',
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>',
						],
						[
							'title'       => esc_html__( 'Pending Earnings', 'affiliates-for-woocommerce' ),
							'value'       => wc_price( $summary['pending_earnings']['value'] ),
							'change'      => $summary['pending_earnings']['change'],
							'is_positive' => $summary['pending_earnings']['is_positive'],
							'value_type'  => 'html',
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
						],
						[
							'title'       => esc_html__( 'Avg Commission', 'affiliates-for-woocommerce' ),
							'value'       => wc_price( $summary['avg_commission_value']['value'] ),
							'change'      => $summary['avg_commission_value']['change'],
							'is_positive' => $summary['avg_commission_value']['is_positive'],
							'value_type'  => 'html',
							'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"></line><circle cx="6.5" cy="6.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>',
						],
					],
					'charts' => [
						[
							'id'         => 'performance',
							'title'      => esc_html__( 'Performance Overview', 'affiliates-for-woocommerce' ),
							'date_label' => $date_label,
							'full_width' => true,
							'type'       => 'line',
							'data'       => $performance,
							'x_key'      => 'date',
							'series'     => [
								[ 'key' => 'earnings', 'label' => esc_html__( 'Earnings', 'affiliates-for-woocommerce' ), 'color' => '#0256ff' ],
								[ 'key' => 'visits', 'label' => esc_html__( 'Visits', 'affiliates-for-woocommerce' ), 'color' => '#ec4899' ],
							],
							'empty'      => [
								'title' => esc_html__( 'No performance data available', 'affiliates-for-woocommerce' ),
								'desc'  => esc_html__( 'Earnings and visits will appear here as affiliates drive traffic.', 'affiliates-for-woocommerce' ),
							],
						],
						[
							'id'           => 'revenue-impact',
							'title'        => esc_html__( 'Revenue Impact', 'affiliates-for-woocommerce' ),
							'date_label'   => $date_label,
							'type'         => 'line',
							'value_format' => 'currency',
							'data'         => $revenue_impact,
							'x_key'        => 'date',
							'series'       => [
								[ 'key' => 'affiliate_revenue', 'label' => esc_html__( 'Affiliate Revenue', 'affiliates-for-woocommerce' ), 'color' => '#8b5cf6' ],
								[ 'key' => 'store_revenue', 'label' => esc_html__( 'Store Revenue', 'affiliates-for-woocommerce' ), 'color' => '#0256ff' ],
							],
							'empty'        => [
								'title' => esc_html__( 'No revenue data', 'affiliates-for-woocommerce' ),
								'desc'  => esc_html__( 'Affiliate vs store revenue will appear here.', 'affiliates-for-woocommerce' ),
							],
						],
						[
							'id'        => 'conversion-sources',
							'title'     => esc_html__( 'Conversion Sources', 'affiliates-for-woocommerce' ),
							'type'      => 'doughnut',
							'data'      => $data['charts']['conversion_sources'],
							'label_key' => 'source',
							'value_key' => 'value',
							'empty'     => [
								'title' => esc_html__( 'No conversion data', 'affiliates-for-woocommerce' ),
								'desc'  => esc_html__( 'Top conversion sources will appear here.', 'affiliates-for-woocommerce' ),
							],
						],
					],
					'widgets' => [
						[
							'title'  => esc_html__( 'Recent Commissions', 'affiliates-for-woocommerce' ),
							'render' => [ $this, 'render_recent_commissions' ],
						],
						[
							'title'  => esc_html__( 'Top Affiliates', 'affiliates-for-woocommerce' ),
							'render' => [ $this, 'render_top_affiliates' ],
						],
					],
				]
			);
		}

		/**
		 * Render the recent commissions widget body.
		 *
		 * @return void
		 */
		public function render_recent_commissions() {
			$commissions = $this->dashboard_data['recent_activities']['commissions'];

			if ( empty( $commissions ) ) {
				?>
				<div class="ddfw-dash-no-data"><?php esc_html_e( 'No recent commissions', 'affiliates-for-woocommerce' ); ?></div>
				<?php
				return;
			}
			?>
			<div class="ddfw-dash-list">
				<?php foreach ( $commissions as $commission ) : ?>
					<div class="ddfw-dash-list-item">
						<div class="ddfw-dash-list-info">
							<div class="ddfw-dash-list-title"><strong><?php echo esc_html( $commission['display_name'] ? $commission['display_name'] : esc_html__( 'Guest', 'affiliates-for-woocommerce' ) ); ?></strong></div>
							<div class="ddfw-dash-list-meta">
								<?php
								echo esc_html( $this->commission_helper->ddwcaf_get_translation( $commission['status'] ) );
								echo ' • ';
								echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $commission['created_at'] ) ) );
								?>
							</div>
						</div>
						<div class="ddfw-dash-list-value positive"><?php echo wp_kses_post( wc_price( $commission['commission'] ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Render the top affiliates widget body.
		 *
		 * @return void
		 */
		public function render_top_affiliates() {
			$affiliates = $this->dashboard_data['top_affiliates'];

			if ( empty( $affiliates ) ) {
				?>
				<div class="ddfw-dash-no-data"><?php esc_html_e( 'No affiliate data', 'affiliates-for-woocommerce' ); ?></div>
				<?php
				return;
			}
			?>
			<div class="ddfw-dash-list">
				<?php foreach ( $affiliates as $affiliate ) : ?>
					<div class="ddfw-dash-list-item">
						<div class="ddfw-dash-list-avatar"><?php echo get_avatar( $affiliate['ID'], 32 ); ?></div>
						<div class="ddfw-dash-list-info">
							<div class="ddfw-dash-list-title"><strong><?php echo esc_html( $affiliate['display_name'] ); ?></strong></div>
						</div>
						<div class="ddfw-dash-list-value"><?php echo wp_kses_post( wc_price( $affiliate['total_earnings'] ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
}
