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
		 *                                 show_date_filter (default true), date_range (override),
		 *                                 filters (selects shown beside the date picker: name, label,
		 *                                 options as value => label, value).
		 *     @type array  $summary_cards List of cards: title, value, value_type, icon, change, is_positive,
		 *                                 previous, note, no_previous_label.
		 *     @type array  $charts        List of charts: id, title, date_label, full_width, type,
		 *                                 data, x_key, series ( key, label, color, style, aggregate:
		 *                                 'average' for a series that must not add up by week or
		 *                                 month ), label_key, value_key, value_format, empty.
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
			<?php // Admin notices are moved after this marker; without it WordPress put them after the first heading, inside the welcome line. ?>
			<hr class="wp-header-end" />
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

					<?php if ( $show_date_filter || ! empty( $header['actions'] ) ) : ?>
						<div class="ddfw-dash-header-right">
							<?php
							if ( $show_date_filter ) {
								$this->render_date_filter();
							}

							$this->render_header_actions( isset( $header['actions'] ) && is_array( $header['actions'] ) ? $header['actions'] : [] );
							?>
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

			// Extra selects (an outlet, a store, a vendor) live in the same form as the dates, so
			// changing one keeps the other. A filter's own name is never also sent as a hidden field.
			$filters = isset( $header['filters'] ) && is_array( $header['filters'] ) ? $header['filters'] : [];
			$extra   = array_diff_key( $extra, array_flip( wp_list_pluck( $filters, 'name' ) ) );

			$presets = DDFW_Dashboard_Data::get_presets();
			$groups  = DDFW_Dashboard_Data::get_preset_groups();
			$active  = $this->date_range['key'] ?? '30_days';
			$check = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>';
			?>
			<div class="ddfw-dash-filters">
				<form method="get" class="ddfw-dash-date-filter-form">
					<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
					<input type="hidden" name="menu" value="<?php echo esc_attr( $menu ); ?>" />
					<?php foreach ( $extra as $extra_key => $extra_value ) : ?>
						<input type="hidden" name="<?php echo esc_attr( $extra_key ); ?>" value="<?php echo esc_attr( $extra_value ); ?>" />
					<?php endforeach; ?>

					<div class="ddfw-dash-dropdown ddfw-dash-date-range-container">
						<button type="button" id="ddfw-dash-date-range-picker" class="ddfw-dash-trigger ddfw-dash-date-range-picker" aria-haspopup="dialog" aria-expanded="false">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
							<span class="ddfw-dash-trigger-label"><?php echo esc_html( $this->date_range['label'] ); ?></span>
						</button>

						<div class="ddfw-dash-menu ddfw-dash-date-range-dropdown" id="ddfw-dash-date-range-dropdown" role="dialog" aria-label="<?php esc_attr_e( 'Date range', 'affiliates-for-woocommerce' ); ?>" hidden>
							<ul class="ddfw-dash-date-presets">
								<?php
								$previous_group = '';
								foreach ( $presets as $key => $label ) :
									$range = DDFW_Dashboard_Data::resolve_range( $key );
									$group = $groups[ $key ] ?? $key;
									?>
									<li class="<?php echo esc_attr( $previous_group && $previous_group !== $group ? 'has-divider' : '' ); ?>">
										<button type="button" class="ddfw-dash-date-preset<?php echo esc_attr( $active === $key ? ' is-active' : '' ); ?>" data-range="<?php echo esc_attr( $key ); ?>" data-from="<?php echo esc_attr( 'all_time' === $key ? '' : $range['from'] ); ?>" data-to="<?php echo esc_attr( $range['to'] ); ?>">
											<span><?php echo esc_html( $label ); ?></span>
											<small class="ddfw-dash-preset-dates"></small>
										</button>
									</li>
									<?php
									$previous_group = $group;
								endforeach;
								?>
							</ul>

							<div class="ddfw-dash-custom-date-range">
								<div class="ddfw-dash-calendar"></div>
								<div class="ddfw-dash-range-footer">
									<span class="ddfw-dash-range-selection"></span>
									<span class="ddfw-dash-range-buttons">
										<button type="button" class="button ddfw-dash-cancel-custom-range"><?php esc_html_e( 'Cancel', 'affiliates-for-woocommerce' ); ?></button>
										<button type="button" class="button button-primary ddfw-dash-apply-custom-range"><?php esc_html_e( 'Apply', 'affiliates-for-woocommerce' ); ?></button>
									</span>
								</div>
								<input type="hidden" name="from_date" id="ddfw-dash-from-date" value="<?php echo esc_attr( $this->date_range['from'] ); ?>" />
								<input type="hidden" name="to_date" id="ddfw-dash-to-date" value="<?php echo esc_attr( $this->date_range['to'] ); ?>" />
							</div>
						</div>

						<input type="hidden" name="date_range" id="ddfw-dash-selected-range" value="<?php echo esc_attr( $active ); ?>" />
					</div>

					<?php
					// Extra filters as dropdowns in the same bar. The first option is the unfiltered one;
					// any other choice turns the control blue, so a narrowed view is never missed.
					foreach ( $filters as $filter ) {
						if ( empty( $filter['name'] ) || empty( $filter['options'] ) || ! is_array( $filter['options'] ) ) {
							continue;
						}

						$filter_value = isset( $filter['value'] ) ? (string) $filter['value'] : '';
						$values       = array_map( 'strval', array_keys( $filter['options'] ) );
						$filter_value = in_array( $filter_value, $values, true ) ? $filter_value : $values[0];
						$filter_label = ! empty( $filter['label'] ) ? $filter['label'] : $filter['name'];
						$is_filtered  = $filter_value !== $values[0];
						?>
						<div class="ddfw-dash-dropdown ddfw-dash-filter">
							<input type="hidden" name="<?php echo esc_attr( $filter['name'] ); ?>" value="<?php echo esc_attr( $filter_value ); ?>" />
							<button type="button" class="ddfw-dash-trigger<?php echo esc_attr( $is_filtered ? ' is-filtered' : '' ); ?>" aria-haspopup="listbox" aria-expanded="false" aria-label="<?php echo esc_attr( $filter_label . ': ' . $filter['options'][ $filter_value ] ); ?>">
								<?php echo ! empty( $filter['icon'] ) ? wp_kses( $filter['icon'], ddfw_kses_allowed_svg_tags() ) : ''; ?>
								<span class="ddfw-dash-trigger-label"><?php echo esc_html( $filter['options'][ $filter_value ] ); ?></span>
							</button>
							<ul class="ddfw-dash-menu" role="listbox" aria-label="<?php echo esc_attr( $filter_label ); ?>" hidden>
								<?php foreach ( $filter['options'] as $option_value => $option_label ) : ?>
									<li role="none">
										<button type="button" role="option" class="ddfw-dash-option<?php echo esc_attr( (string) $option_value === $filter_value ? ' is-active' : '' ); ?>" data-value="<?php echo esc_attr( $option_value ); ?>" aria-selected="<?php echo esc_attr( (string) $option_value === $filter_value ? 'true' : 'false' ); ?>">
											<span><?php echo esc_html( $option_label ); ?></span>
											<?php echo (string) $option_value === $filter_value ? $check : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
										</button>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php
					}
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Render links on the right of the header ( e.g. "View reports" on a today overview ).
		 *
		 * @param array $actions List of actions: label, url, primary, new_tab.
		 * @return void
		 */
		protected function render_header_actions( $actions ) {
			foreach ( $actions as $action ) {
				if ( empty( $action['url'] ) || empty( $action['label'] ) ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $action['url'] ); ?>" class="button<?php echo ! empty( $action['primary'] ) ? ' button-primary' : ''; ?> ddfw-dash-header-action"<?php echo ! empty( $action['new_tab'] ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $action['label'] ); ?></a>
				<?php
			}
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
		 * Render a single summary card: label ( with its icon ), the figure, and how it compares
		 * with the previous period. No sparkline: only two or three cards per dashboard have a
		 * matching daily series, and a row where some cards have one and others don't looked broken.
		 *
		 * @param array $card Card config: title, value, value_type, icon, change ( percent; null when
		 *                    the previous period had nothing ), is_positive, previous ( formatted
		 *                    previous figure ), note, no_previous_label.
		 * @return void
		 */
		protected function render_summary_card( $card ) {
			$value      = $card['value'] ?? 0;
			$value_type = $card['value_type'] ?? 'number';
			$no_compare = array_key_exists( 'change', $card ) && null === $card['change'];
			$change     = round( (float) ( $card['change'] ?? 0 ), 1 ); // Float default: `0.0 !== 0` is true and showed a bogus 0%.
			$good       = ! isset( $card['is_positive'] ) || $card['is_positive'];

			// Zero now and zero before ( "$0.00" from "$0.00" ): one line for every plugin, instead of
			// "No change", "None in the previous period" or a 0% depending on how each one counted.
			$zero = static function ( $figure ) {
				$text = html_entity_decode( wp_strip_all_tags( (string) $figure ), ENT_QUOTES, 'UTF-8' );
				return (bool) preg_match( '/\d/', $text ) && ! preg_match( '/[1-9]/', $text );
			};
			if ( isset( $card['previous'] ) && '' !== $card['previous'] && $zero( $value ) && $zero( $card['previous'] ) ) {
				$no_compare                = false;
				$change                    = 0.0;
				$card['note'] = __( 'No activity in either period', 'affiliates-for-woocommerce' );
			}
			?>
			<div class="ddfw-dash-summary-card">
				<div class="ddfw-dash-card-content">
					<h4>
						<?php if ( ! empty( $card['icon'] ) ) : ?>
							<span class="ddfw-dash-card-icon" aria-hidden="true"><?php echo wp_kses( $card['icon'], ddfw_kses_allowed_svg_tags() ); ?></span>
						<?php endif; ?>
						<span><?php echo esc_html( $card['title'] ?? '' ); ?></span>
					</h4>
					<?php
					// The comparison is a line of its own under the figure: beside it, it crowded the
					// number in a narrow card and read as part of it.
					$has_change = ! $no_compare && 0.0 !== abs( $change );
					?>
					<div class="ddfw-dash-card-value">
						<?php if ( 'number' === $value_type ) : ?>
							<span class="ddfw-dash-value-number"><?php echo esc_html( number_format_i18n( (float) $value ) ); ?></span>
						<?php elseif ( 'html' === $value_type ) : ?>
							<span class="ddfw-dash-value-text"><?php echo wp_kses_post( $value ); ?></span>
						<?php elseif ( is_numeric( $value ) || preg_match( '/^[\d.,%+\-\s]+$/', (string) $value ) ) : ?>
							<?php // A figure passed as text ( "3", "-12%" ) keeps the figure size; only a name gets the label size. ?>
							<span class="ddfw-dash-value-text"><?php echo esc_html( $value ); ?></span>
						<?php else : ?>
							<span class="ddfw-dash-value-text is-label" title="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( $no_compare ) : ?>
						<p class="ddfw-dash-card-note"><?php echo esc_html( $card['no_previous_label'] ?? __( 'None in the previous period', 'affiliates-for-woocommerce' ) ); ?></p>
					<?php elseif ( $has_change ) : ?>
						<p class="ddfw-dash-card-note">
							<span class="ddfw-dash-change-indicator <?php echo esc_attr( $good ? 'positive' : 'negative' ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $change > 0 ? '<path d="M22 7 13.5 15.5 8.5 10.5 2 17"/><path d="M16 7h6v6"/>' : '<path d="M22 17 13.5 8.5 8.5 13.5 2 7"/><path d="M16 17h6v-6"/>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup. ?></svg>
								<?php // Past 1000% the exact figure only says "from almost nothing". ?>
								<?php echo esc_html( ( $change < 0 ? '-' : '' ) . ( abs( $change ) >= 1000 ? '999+' : number_format_i18n( abs( $change ), fmod( $change, 1 ) ? 1 : 0 ) ) . '%' ); ?>
							</span>
							<span>
								<?php
								if ( ! empty( $card['note'] ) ) {
									echo wp_kses_post( $card['note'] );
								} elseif ( isset( $card['previous'] ) && '' !== $card['previous'] ) {
									/* translators: %s: the figure in the previous period */
									echo esc_html( sprintf( __( 'from %s', 'affiliates-for-woocommerce' ), wp_strip_all_tags( (string) $card['previous'] ) ) );
								} else {
									esc_html_e( 'vs previous period', 'affiliates-for-woocommerce' );
								}
								?>
							</span>
						</p>
					<?php elseif ( ! empty( $card['note'] ) ) : ?>
						<p class="ddfw-dash-card-note"><?php echo wp_kses_post( $card['note'] ); ?></p>
					<?php endif; ?>
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
		 * One widget list in the shared style, so every plugin's lists line up: a thumbnail ( a
		 * square tile for products, a round avatar for people, a placeholder tile when there is no
		 * image ), a title with a meta line, and a value on the right. Call it from a widget's
		 * `render` callback.
		 *
		 * @param array  $items {
		 *     List rows.
		 *
		 *     @type string $title       Title ( plain text ).
		 *     @type string $meta        Meta line under the title ( plain text ).
		 *     @type string $url         Optional link for the row.
		 *     @type string $image       Optional image: an <img> tag ( `$product->get_image()` ) or an image URL.
		 *     @type string $shape       'square' ( products, the default ) or 'round' ( people ).
		 *     @type string $value       Optional value on the right ( HTML: `wc_price()`, a status badge ).
		 *     @type string $value_class Optional 'positive' or 'negative' for a tinted signed value.
		 * }
		 * @param string $empty Message shown when there are no rows.
		 * @param bool   $image Whether rows carry an image column. Rows without one get the placeholder tile.
		 * @return void
		 */
		public static function render_list( $items, $empty = '', $image = true ) {
			if ( empty( $items ) ) {
				?>
				<div class="ddfw-dash-no-data"><?php echo esc_html( $empty ? $empty : __( 'Nothing to show yet.', 'affiliates-for-woocommerce' ) ); ?></div>
				<?php
				return;
			}
			?>
			<div class="ddfw-dash-list">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$tag    = empty( $item['url'] ) ? 'div' : 'a';
					$round  = 'round' === ( $item['shape'] ?? 'square' );
					$source = $item['image'] ?? '';
					// WooCommerce's grey placeholder for a product without a photo reads as a broken
					// image at this size: the shared placeholder tile says "no image" instead.
					if ( $source && false !== strpos( $source, 'woocommerce-placeholder' ) ) {
						$source = '';
					}
					?>
					<<?php echo esc_attr( $tag ); ?> class="ddfw-dash-list-item"<?php echo 'a' === $tag ? ' href="' . esc_url( $item['url'] ) . '"' : ''; ?>>
						<?php if ( $image ) : ?>
							<?php if ( $source ) : ?>
								<span class="ddfw-dash-list-avatar<?php echo $round ? '' : ' is-square'; ?>">
									<?php echo false === strpos( $source, '<img' ) ? '<img src="' . esc_url( $source ) . '" alt="" loading="lazy" />' : wp_kses_post( $source ); ?>
								</span>
							<?php else : ?>
								<span class="ddfw-dash-list-placeholder<?php echo $round ? ' is-round' : ''; ?>" aria-hidden="true">
									<?php if ( $round ) : ?>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
									<?php else : ?>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/></svg>
									<?php endif; ?>
								</span>
							<?php endif; ?>
						<?php endif; ?>
						<span class="ddfw-dash-list-info">
							<span class="ddfw-dash-list-title"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
							<?php if ( ! empty( $item['meta'] ) ) : ?>
								<span class="ddfw-dash-list-meta"><?php echo esc_html( $item['meta'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php if ( isset( $item['value'] ) && '' !== $item['value'] ) : ?>
							<span class="ddfw-dash-list-value<?php echo ! empty( $item['value_class'] ) ? ' ' . esc_attr( $item['value_class'] ) : ''; ?>"><?php echo wp_kses_post( $item['value'] ); ?></span>
						<?php endif; ?>
					</<?php echo esc_attr( $tag ); ?>>
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
			// Charts write money the way the store does: its currency, symbol position, decimals
			// and separators. A plugin on another store ( FluentCart ) passes its own through the filter.
			$currency = function_exists( 'get_woocommerce_currency_symbol' )
				? [
					'symbol'            => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
					'position'          => get_option( 'woocommerce_currency_pos', 'left' ),
					'decimals'          => wc_get_price_decimals(),
					'decimalSeparator'  => wc_get_price_decimal_separator(),
					'thousandSeparator' => wc_get_price_thousand_separator(),
				]
				: [ 'symbol' => '$' ];
			$currency = apply_filters( 'ddfw_dashboard_currency', $currency );

			wp_localize_script(
				'ddfw-dashboard-analytics-script',
				'ddfwDashboardAnalytics',
				[
					'charts'         => $this->charts_js,
					'dateRange'      => $this->date_range,
					'currency'       => $currency,
					'currencySymbol' => $currency['symbol'],
					'today'          => current_time( 'Y-m-d' ),
					'startOfWeek'    => (int) get_option( 'start_of_week', 1 ),
					'i18n'           => [
						'noData'        => esc_html__( 'No data available', 'affiliates-for-woocommerce' ),
						'total'         => esc_html__( 'Total', 'affiliates-for-woocommerce' ),
						'pickStart'     => esc_html__( 'Pick a start date', 'affiliates-for-woocommerce' ),
						'pickEnd'       => esc_html__( 'Now pick the end date', 'affiliates-for-woocommerce' ),
						'previousMonth' => esc_html__( 'Previous month', 'affiliates-for-woocommerce' ),
						'nextMonth'     => esc_html__( 'Next month', 'affiliates-for-woocommerce' ),
						/* translators: %s: the period still being counted, e.g. "Oct 19 – 25, 2026". */
						'soFar'         => esc_html__( '%s (so far)', 'affiliates-for-woocommerce' ),
						'busiestDay'    => esc_html__( 'Busiest day', 'affiliates-for-woocommerce' ),
						'busiestHour'   => esc_html__( 'Busiest hour', 'affiliates-for-woocommerce' ),
						'less'          => esc_html__( 'Less', 'affiliates-for-woocommerce' ),
						'more'          => esc_html__( 'More', 'affiliates-for-woocommerce' ),
						'day'           => esc_html__( 'Day', 'affiliates-for-woocommerce' ),
						'week'          => esc_html__( 'Week', 'affiliates-for-woocommerce' ),
						'month'         => esc_html__( 'Month', 'affiliates-for-woocommerce' ),
						'line'          => esc_html__( 'Line', 'affiliates-for-woocommerce' ),
						'bar'           => esc_html__( 'Bar', 'affiliates-for-woocommerce' ),
					],
				]
			);
		}
	}
}
