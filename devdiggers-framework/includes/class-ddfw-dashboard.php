<?php
/**
 * File for the shared DevDiggers analytics dashboard builder.
 *
 * Renders a config-driven analytics dashboard (header + date filter, summary stat cards,
 * Chart.js charts and a widget/list section) so every DevDiggers plugin gets an identical,
 * performance-optimized dashboard while only supplying its own data and configuration.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Dashboard' ) ) {
	/**
	 * Config-driven analytics dashboard builder.
	 */
	class DDFW_Dashboard {
		/**
		 * Normalized dashboard configuration.
		 *
		 * @var array
		 */
		protected $config;

		/**
		 * Number of summary-card columns per row.
		 *
		 * @var int
		 */
		protected $columns;

		/**
		 * Active date range ( from / to / label / key ).
		 *
		 * @var array
		 */
		protected $date_range;

		/**
		 * Declarative chart configuration collected for the JS engine.
		 *
		 * @var array
		 */
		protected $charts_js = [];

		/**
		 * Build and render the dashboard.
		 *
		 * @param array $config {
		 *     Dashboard configuration.
		 *
		 *     @type int    $columns       Summary-card columns per row. Default 5.
		 *     @type array  $header        Header config: show_avatar, welcome, subtitle,
		 *                                 show_date_filter (default true), date_range (override).
		 *     @type array  $summary_cards List of cards: title, value, change, is_positive, icon, value_type.
		 *     @type array  $charts        List of charts: id, title, date_label, full_width, type,
		 *                                 data, x_key, series, label_key, value_key, value_format, empty.
		 *     @type array  $widgets       List of bottom widgets: title, render (callable).
		 * }
		 */
		public function __construct( array $config ) {
			$this->config  = $config;
			$this->columns = ! empty( $config['columns'] ) ? absint( $config['columns'] ) : 5;

			$header = isset( $config['header'] ) && is_array( $config['header'] ) ? $config['header'] : [];

			// Date range: use the override when supplied, otherwise resolve from the request.
			$this->date_range = ! empty( $header['date_range'] ) && is_array( $header['date_range'] )
				? $header['date_range']
				: DDFW_Dashboard_Data::get_date_range();

			$this->render();
		}

		/**
		 * Render the full dashboard.
		 *
		 * @return void
		 */
		protected function render() {
			$this->enqueue_assets();
			?>
			<div class="ddfw-dash">
				<?php
				$this->render_header();
				$this->render_summary_cards();
				$this->render_charts();
				$this->render_widgets();
				?>
			</div>
			<?php
			$this->localize();
		}

		/**
		 * Enqueue the shared dashboard assets registered by DDFW_Assets.
		 *
		 * @return void
		 */
		protected function enqueue_assets() {
			wp_enqueue_style( 'ddfw-dashboard-analytics-style' );
			wp_enqueue_script( 'ddfw-dashboard-analytics-script' );
		}

		/**
		 * Render the dashboard header ( avatar + welcome + date filter ).
		 *
		 * @return void
		 */
		protected function render_header() {
			$header           = isset( $this->config['header'] ) && is_array( $this->config['header'] ) ? $this->config['header'] : [];
			$show_avatar      = ! isset( $header['show_avatar'] ) || $header['show_avatar'];
			$show_date_filter = ! isset( $header['show_date_filter'] ) || $header['show_date_filter'];
			$current_user     = wp_get_current_user();

			$welcome = isset( $header['welcome'] ) ? $header['welcome'] : esc_html__( 'Welcome back!', 'affiliates-for-woocommerce' );
			if ( false !== strpos( $welcome, '%s' ) ) {
				$welcome = sprintf( $welcome, $current_user->display_name );
			}
			$subtitle = isset( $header['subtitle'] ) ? $header['subtitle'] : '';

			// Detail screens ( e.g. a single user ) can point the header at a specific entity instead
			// of the current admin: override the avatar and add a back link.
			$avatar_url = ! empty( $header['avatar_url'] ) ? $header['avatar_url'] : get_avatar_url( $current_user->ID, [ 'size' => 48 ] );
			$back_link  = isset( $header['back_link'] ) && is_array( $header['back_link'] ) ? $header['back_link'] : [];
			?>
			<div class="ddfw-dash-header">
				<div class="ddfw-dash-header-top">
					<div class="ddfw-dash-header-left">
						<div class="ddfw-dash-welcome-content">
							<?php if ( $show_avatar ) : ?>
								<div class="ddfw-dash-admin-avatar">
									<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $welcome ); ?>" class="ddfw-dash-avatar-image" />
								</div>
							<?php endif; ?>
							<div class="ddfw-dash-welcome-message">
								<h1>
									<?php echo esc_html( $welcome ); ?>
									<?php if ( ! empty( $back_link['url'] ) ) : ?>
										<a href="<?php echo esc_url( $back_link['url'] ); ?>" class="ddfw-dash-back-button">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
											<?php echo esc_html( ! empty( $back_link['label'] ) ? $back_link['label'] : esc_html__( 'Back', 'affiliates-for-woocommerce' ) ); ?>
										</a>
									<?php endif; ?>
								</h1>
								<?php if ( $subtitle ) : ?>
									<p class="ddfw-dash-welcome-subtitle"><?php echo esc_html( $subtitle ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<?php if ( $show_date_filter ) : ?>
						<div class="ddfw-dash-header-right">
							<?php $this->render_date_filter(); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Render the shared date-range filter.
		 *
		 * Routing/read-only parameters; no state change, so nonce verification is not required.
		 *
		 * @return void
		 */
		protected function render_date_filter() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$menu = isset( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';

			// Detail screens pass extra routing args ( e.g. action + record id ) so applying a date
			// range keeps the same context instead of falling back to the list view. The date filter
			// JS submits this form, so these hidden fields ride along automatically.
			$header = isset( $this->config['header'] ) && is_array( $this->config['header'] ) ? $this->config['header'] : [];
			$extra  = isset( $header['date_filter_extra'] ) && is_array( $header['date_filter_extra'] ) ? $header['date_filter_extra'] : [];

			$presets = DDFW_Dashboard_Data::get_presets();
			?>
			<div class="ddfw-dash-filters">
				<form method="get" class="ddfw-dash-date-filter-form">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
					<input type="hidden" name="menu" value="<?php echo esc_attr( $menu ); ?>" />
					<?php foreach ( $extra as $extra_key => $extra_value ) : ?>
						<input type="hidden" name="<?php echo esc_attr( $extra_key ); ?>" value="<?php echo esc_attr( $extra_value ); ?>" />
					<?php endforeach; ?>

					<div class="ddfw-dash-date-range-container">
						<input type="text"
							id="ddfw-dash-date-range-picker"
							class="ddfw-dash-date-range-picker"
							value="<?php echo esc_attr( $this->date_range['label'] ); ?>"
							readonly />

						<div class="ddfw-dash-date-range-dropdown" id="ddfw-dash-date-range-dropdown">
							<div class="ddfw-dash-dropdown-content">
								<div class="ddfw-dash-date-presets">
									<div class="ddfw-dash-presets-header">
										<h4><?php esc_html_e( 'Quick Select', 'affiliates-for-woocommerce' ); ?></h4>
									</div>
									<?php foreach ( $presets as $key => $label ) : ?>
										<button type="button" class="ddfw-dash-date-preset" data-range="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
									<?php endforeach; ?>
								</div>

								<div class="ddfw-dash-custom-date-range">
									<div class="ddfw-dash-custom-header">
										<h4><?php esc_html_e( 'Custom Range', 'affiliates-for-woocommerce' ); ?></h4>
										<p><?php esc_html_e( 'Select specific start and end dates for your analysis', 'affiliates-for-woocommerce' ); ?></p>
									</div>
									<div class="ddfw-dash-date-inputs">
										<div class="ddfw-dash-date-input-group">
											<label for="ddfw-dash-from-date"><?php esc_html_e( 'From Date', 'affiliates-for-woocommerce' ); ?></label>
											<input type="date" name="from_date" id="ddfw-dash-from-date" value="<?php echo esc_attr( $this->date_range['from'] ); ?>" />
										</div>
										<div class="ddfw-dash-date-input-group">
											<label for="ddfw-dash-to-date"><?php esc_html_e( 'To Date', 'affiliates-for-woocommerce' ); ?></label>
											<input type="date" name="to_date" id="ddfw-dash-to-date" value="<?php echo esc_attr( $this->date_range['to'] ); ?>" />
										</div>
									</div>
									<button type="button" class="ddfw-dash-apply-custom-range button button-primary"><?php esc_html_e( 'Apply Custom Range', 'affiliates-for-woocommerce' ); ?></button>
								</div>
							</div>
						</div>

						<input type="hidden" name="date_range" id="ddfw-dash-selected-range" value="<?php echo esc_attr( $this->date_range['key'] ?? '30_days' ); ?>" />
					</div>
				</form>
			</div>
			<?php
		}

		/**
		 * Render the summary stat cards.
		 *
		 * @return void
		 */
		protected function render_summary_cards() {
			$cards = isset( $this->config['summary_cards'] ) && is_array( $this->config['summary_cards'] ) ? $this->config['summary_cards'] : [];
			if ( empty( $cards ) ) {
				return;
			}

			// Optional panel rendered beside the cards ( e.g. a user level / rank widget ) that
			// spans the full height of the card grid instead of dropping to the bottom section.
			$aside = isset( $this->config['summary_aside'] ) && is_array( $this->config['summary_aside'] ) ? $this->config['summary_aside'] : [];
			?>
			<div class="ddfw-dash-top-section<?php echo ! empty( $aside ) ? ' ddfw-dash-top-section--with-aside' : ''; ?>">
				<div class="ddfw-dash-summary-cards ddfw-dash-cols-<?php echo esc_attr( $this->columns ); ?>">
					<?php
					foreach ( $cards as $card ) {
						$this->render_summary_card( $card );
					}
					?>
				</div>
				<?php if ( ! empty( $aside ) && ! empty( $aside['render'] ) && is_callable( $aside['render'] ) ) : ?>
					<div class="ddfw-dash-widget ddfw-dash-top-aside">
						<?php if ( ! empty( $aside['title'] ) ) : ?>
							<h3><?php echo esc_html( $aside['title'] ); ?></h3>
						<?php endif; ?>
						<?php call_user_func( $aside['render'] ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Render a single summary card.
		 *
		 * @param array $card Card config: title, value, change, is_positive, icon, value_type.
		 * @return void
		 */
		protected function render_summary_card( $card ) {
			$title       = $card['title'] ?? '';
			$value       = $card['value'] ?? 0;
			$change      = round( (float) ( $card['change'] ?? 0 ), 1 ); // Float default: `0.0 !== 0` is true and showed a bogus 0%.
			$is_positive = ! isset( $card['is_positive'] ) || $card['is_positive'];
			$icon_svg    = $card['icon'] ?? '';
			$value_type  = $card['value_type'] ?? 'number';
			?>
			<div class="ddfw-dash-summary-card">
				<div class="ddfw-dash-card-header">
					<div class="ddfw-dash-card-icon"><?php echo wp_kses( $icon_svg, ddfw_kses_allowed_svg_tags() ); ?></div>
					<?php if ( 0.0 !== abs( $change ) ) : ?>
						<div class="ddfw-dash-change-indicator <?php echo esc_attr( $is_positive ? 'positive' : 'negative' ); ?>">
							<?php if ( $is_positive ) : ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 6l-9.5 9.5-5-5L1 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 6h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php else : ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 18l-9.5-9.5-5 5L1 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 18h6v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php endif; ?>
							<span><?php echo esc_html( $change ); ?>%</span>
						</div>
					<?php endif; ?>
				</div>
				<div class="ddfw-dash-card-content">
					<h4><?php echo esc_html( $title ); ?></h4>
					<div class="ddfw-dash-card-value">
						<?php if ( 'number' === $value_type ) : ?>
							<span class="ddfw-dash-value-number"><?php echo esc_html( number_format_i18n( (float) $value ) ); ?></span>
						<?php elseif ( 'html' === $value_type ) : ?>
							<span class="ddfw-dash-value-text"><?php echo wp_kses_post( $value ); ?></span>
						<?php else : ?>
							<span class="ddfw-dash-value-text"><?php echo esc_html( $value ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Render the chart canvases and collect their declarative config for JS.
		 *
		 * @return void
		 */
		protected function render_charts() {
			$charts = isset( $this->config['charts'] ) && is_array( $this->config['charts'] ) ? $this->config['charts'] : [];
			if ( empty( $charts ) ) {
				return;
			}

			// Group consecutive non-full-width charts into rows of two; full-width gets its own row.
			$full      = [];
			$half      = [];
			foreach ( $charts as $chart ) {
				if ( ! empty( $chart['full_width'] ) ) {
					$full[] = $chart;
				} else {
					$half[] = $chart;
				}
			}

			// Full-width charts each on their own row.
			foreach ( $full as $chart ) {
				?>
				<div class="ddfw-dash-charts-section ddfw-dash-charts-section-full-width">
					<?php $this->render_chart_container( $chart ); ?>
				</div>
				<?php
			}

			// Remaining charts in a two-column responsive grid.
			if ( ! empty( $half ) ) {
				?>
				<div class="ddfw-dash-charts-section">
					<?php
					foreach ( $half as $chart ) {
						$this->render_chart_container( $chart );
					}
					?>
				</div>
				<?php
			}
		}

		/**
		 * Render one chart container and register its JS config.
		 *
		 * @param array $chart Chart config.
		 * @return void
		 */
		protected function render_chart_container( $chart ) {
			$canvas_id  = $this->collect_chart( $chart );
			$title      = $chart['title'] ?? '';
			$date_label = $chart['date_label'] ?? '';
			?>
			<div class="ddfw-dash-chart-container">
				<h3>
					<?php echo esc_html( $title ); ?>
					<?php if ( $date_label ) : ?>
						<span class="ddfw-dash-chart-date-range"><?php echo esc_html( $date_label ); ?></span>
					<?php endif; ?>
				</h3>
				<?php $this->render_chart_canvas( $canvas_id ); ?>
			</div>
			<?php
		}

		/**
		 * Normalize a chart config, register it for the JS engine, and return its canvas id.
		 *
		 * @param array $chart Chart config.
		 * @return string Canvas element id.
		 */
		protected function collect_chart( $chart ) {
			$id        = isset( $chart['id'] ) ? sanitize_html_class( $chart['id'] ) : 'chart-' . wp_rand();
			$canvas_id = 'ddfw-dash-chart-' . $id;

			$this->charts_js[] = [
				'canvasId'    => $canvas_id,
				'type'        => $chart['type'] ?? 'line',
				'data'        => array_values( $chart['data'] ?? [] ),
				'xKey'        => $chart['x_key'] ?? 'date',
				'series'      => array_values( $chart['series'] ?? [] ),
				'labelKey'    => $chart['label_key'] ?? 'label',
				'valueKey'    => $chart['value_key'] ?? 'value',
				'valueFormat' => $chart['value_format'] ?? 'number',
				'empty'       => [
					'title' => $chart['empty']['title'] ?? esc_html__( 'No data available', 'affiliates-for-woocommerce' ),
					'desc'  => $chart['empty']['desc'] ?? esc_html__( 'Data will appear here once activity is recorded.', 'affiliates-for-woocommerce' ),
				],
			];

			return $canvas_id;
		}

		/**
		 * Output a chart canvas wrapper for a previously collected chart.
		 *
		 * @param string $canvas_id Canvas element id.
		 * @return void
		 */
		protected function render_chart_canvas( $canvas_id ) {
			?>
			<div class="ddfw-dash-chart" id="<?php echo esc_attr( $canvas_id . '-wrap' ); ?>">
				<canvas id="<?php echo esc_attr( $canvas_id ); ?>"></canvas>
			</div>
			<?php
		}

		/**
		 * Render the bottom widget/list section.
		 *
		 * Each widget supplies a `render` callable that echoes its body; the framework owns
		 * the section chrome so the visual treatment stays consistent across plugins.
		 *
		 * @return void
		 */
		protected function render_widgets() {
			$widgets = isset( $this->config['widgets'] ) && is_array( $this->config['widgets'] ) ? $this->config['widgets'] : [];
			if ( empty( $widgets ) ) {
				return;
			}
			?>
			<div class="ddfw-dash-tables-section">
				<?php foreach ( $widgets as $widget ) : ?>
					<?php
					// A widget that is only a render callback (an upgrade block, a custom panel) draws its
					// own card, so the widget card would only nest one box inside another.
					$bare = empty( $widget['title'] ) && empty( $widget['chart'] );
					?>
					<div class="<?php echo $bare ? 'ddfw-dash-widget-bare' : 'ddfw-dash-widget'; ?><?php echo in_array( ( $widget['width'] ?? '' ), [ 'third', 'half', 'full' ], true ) ? ' ddfw-dash-widget--' . esc_attr( $widget['width'] ) : ''; ?>">
						<?php if ( ! empty( $widget['title'] ) ) : ?>
							<h3><?php echo esc_html( $widget['title'] ); ?></h3>
						<?php endif; ?>
						<?php
						if ( ! empty( $widget['chart'] ) && is_array( $widget['chart'] ) ) {
							$this->render_chart_canvas( $this->collect_chart( $widget['chart'] ) );
						} elseif ( ! empty( $widget['render'] ) && is_callable( $widget['render'] ) ) {
							call_user_func( $widget['render'] );
						}
						?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Localize the collected chart config + shared strings for the JS engine.
		 *
		 * @return void
		 */
		protected function localize() {
			$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' )
				? html_entity_decode( get_woocommerce_currency_symbol() )
				: '$';

			wp_localize_script(
				'ddfw-dashboard-analytics-script',
				'ddfwDashboardAnalytics',
				[
					'charts'         => $this->charts_js,
					'dateRange'      => $this->date_range,
					'currencySymbol' => $currency_symbol,
					'i18n'           => [
						'noData' => esc_html__( 'No data available', 'affiliates-for-woocommerce' ),
					],
				]
			);
		}
	}
}
