<?php
/**
 * File for handling the DevDiggers Plugin Dashboard.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Plugin_Dashboard' ) ) {
	/**
	 * Class for handling the DevDiggers Plugin Dashboard.
	 */
	class DDFW_Plugin_Dashboard {
		/**
		 * List of parameters.
		 *
		 * @var array
		 */
		public $args = [];

		/**
		 * Are the actions initialized?
		 *
		 * @var bool
		 */
		protected static $actions_initialized = false;

		/**
		 * The slug for the plugin dashboard.
		 *
		 * @var bool
		 */
		protected $plugin_dashboard_slug = false;

		/**
		 * Slugs of every registered plugin dashboard.
		 *
		 * @var string[]
		 */
		protected static $dashboard_slugs = [];

		/**
		 * Constructor to initialize hooks.
		 */
		public function __construct( $args = [] ) {
			if ( ! empty( $args ) ) {
				$default_args = [
					'parent_slug' => ddfw_get_parent_menu_slug(),
					'page_title'  => __( 'Plugin Dashboard', 'affiliates-for-woocommerce' ),
					'menu_title'  => __( 'Plugin', 'affiliates-for-woocommerce' ),
					'capability'  => ddfw_get_menu_capability(),
					'icon_url'    => '',
					'position'    => null,
				];

				$args = apply_filters( 'ddfw_modify_plugin_dashboard_args', wp_parse_args( $args, $default_args ) );

				$this->plugin_dashboard_slug = ! empty( $args[ 'slug' ] ) ? sanitize_title( $args[ 'slug' ] ) : 'devdiggers-plugins';

				$this->args = $args;

				static::$dashboard_slugs[] = $this->plugin_dashboard_slug;

				add_action( 'admin_menu', [ $this, 'add_plugin_submenu' ], 20 );
				add_action( 'admin_head', [ $this, 'ddfw_admin_head' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'add_turbo_config' ], 20 );
				add_action( 'admin_print_footer_scripts', [ $this, 'print_speculation_rules' ] );

				static::init_actions();
			}
		}

		/**
		 * Admin head function
		 *
		 * @return void
		 */
		public function ddfw_admin_head() {
			$screen = get_current_screen();

			// Match the correct plugin menu page
			if ( $this->is_a_plugin_page() ) {
				$screen->remove_help_tabs(); // Remove default tabs
			}
		}

		/**
		 * Check if the current page is a plugin dashboard page.
		 *
		 * @return bool
		 */
		public function is_a_plugin_page() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
			$page = ! empty( $_GET[ 'page' ] ) ? sanitize_title( wp_unslash( $_GET[ 'page' ] ) ) : '';

			return $this->plugin_dashboard_slug === $page;
		}


		/**
		 * Whether in-place navigation (DDFW Turbo) is enabled for this dashboard.
		 *
		 * @return bool
		 */
		public function is_turbo_enabled() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			if ( ! empty( $_GET['setup-wizard'] ) || ( isset( $this->args['turbo'] ) && false === $this->args['turbo'] ) ) {
				return false;
			}

			/**
			 * Filters whether DDFW Turbo in-place navigation is enabled for all DevDiggers dashboards.
			 *
			 * @since 1.1.0
			 *
			 * @param bool $enabled Enabled.
			 */
			if ( ! apply_filters( 'ddfw_turbo_enabled', true ) ) {
				return false;
			}

			/**
			 * Filters whether DDFW Turbo in-place navigation is enabled for one dashboard.
			 *
			 * @since 1.1.0
			 *
			 * @param bool   $enabled Enabled.
			 * @param string $slug    Dashboard page slug.
			 */
			return (bool) apply_filters( 'ddfw_turbo_enabled_for_page', true, $this->plugin_dashboard_slug );
		}

		/**
		 * Pass the DDFW Turbo configuration to the framework script.
		 *
		 * @return void
		 */
		public function add_turbo_config() {
			if ( ! $this->is_a_plugin_page() ) {
				return;
			}

			// The shell (layout, list tables, Turbo) needs these even when a plugin enqueues nothing of its own.
			wp_enqueue_style( DDFW_Assets::$framework_css_handle );
			wp_enqueue_script( DDFW_Assets::$framework_js_handle );

			if ( ! $this->is_turbo_enabled() ) {
				return;
			}

			$slug   = $this->plugin_dashboard_slug;
			$config = [
				'enabled'         => true,
				'page'            => $slug,
				'prefix'          => str_replace( '-dashboard', '', $slug ),
				// Framework handles that belong to a view and re-run on every in-place visit.
				'viewHandles'     => [ 'ddfw-dashboard-analytics-script', 'ddfw-dashboard-analytics-style' ],
				// Registered scripts a view may add to the live page, in page order, like a full load would ('*' = any; narrow via the filter).
				'loadableHandles' => [ '*' ],
				'hardParams'      => [ 'setup-wizard', 'setup-wizard-skipped', 'ddfw-hard' ],
				'prefetchParams'  => [ 'page', 'menu', 'tab', 'paged', 's', 'orderby', 'order', 'status', 'date_range', 'from_date', 'to_date' ],
				'prefetch'        => true,
				'transitions'     => false, // View Transitions snapshot the whole admin page (~100 ms); opt in via the ddfw_turbo_config filter.
				'dirtyGuard'      => true,
				'licenseInterval' => 30,
				'i18n'            => [
					'unsavedChanges' => esc_html__( 'You have unsaved changes. Do you want to leave this page?', 'affiliates-for-woocommerce' ),
					/* translators: %s: screen title. */
					'loaded'         => esc_html__( '%s loaded', 'affiliates-for-woocommerce' ),
				],
			];

			/**
			 * Filters the DDFW Turbo configuration of a dashboard.
			 *
			 * @since 1.1.0
			 *
			 * @param array  $config Configuration.
			 * @param string $slug   Dashboard page slug.
			 */
			$config = apply_filters( 'ddfw_turbo_config', $config, $slug );

			wp_add_inline_script( DDFW_Assets::$framework_js_handle, 'window.ddfwTurboConfig = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP ) . ';', 'before' );
		}

		/**
		 * Let Chromium browsers prerender the other DevDiggers dashboards on hover, so switching
		 * between plugins is instant too. Links handled in place by DDFW Turbo are excluded.
		 *
		 * @return void
		 */
		public function print_speculation_rules() {
			if ( ! $this->is_a_plugin_page() ) {
				return;
			}

			$slugs = array_unique( static::$dashboard_slugs );

			if ( $this->is_turbo_enabled() ) {
				$slugs = array_diff( $slugs, [ $this->plugin_dashboard_slug ] );
			}

			if ( empty( $slugs ) ) {
				return;
			}

			$admin_path = wp_parse_url( admin_url( 'admin.php' ), PHP_URL_PATH );
			$rules      = [
				'prerender' => [
					[
						'where'     => [
							'and' => [
								[ 'or' => array_values( array_map( function ( $slug ) use ( $admin_path ) {
									return [ 'href_matches' => $admin_path . '?page=' . $slug . '*' ];
								}, $slugs ) ) ],
								[ 'not' => [ 'href_matches' => '*?*_wpnonce*' ] ],
								[ 'not' => [ 'href_matches' => '*?*action=*' ] ],
								[ 'not' => [ 'href_matches' => '*?*setup-wizard*' ] ],
								[ 'not' => [ 'selector_matches' => '[data-ddfw-hard], [target="_blank"]' ] ],
							],
						],
						'eagerness' => 'moderate',
					],
				],
			];

			/**
			 * Filters the speculation rules printed on DevDiggers dashboards. Return false to disable.
			 *
			 * @since 1.1.0
			 *
			 * @param array|false $rules Speculation rules.
			 * @param string      $slug  Dashboard page slug.
			 */
			$rules = apply_filters( 'ddfw_speculation_rules', $rules, $this->plugin_dashboard_slug );

			if ( empty( $rules ) ) {
				return;
			}

			echo '<script type="speculationrules">' . wp_json_encode( $rules, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}

		/**
		 * Init actions.
		 *
		 * @return void
		 */
		protected static function init_actions() {
			if ( ! static::$actions_initialized ) {
				// Sort plugins by name in DevDiggers Plugins menu.
				add_action( 'admin_menu', array( __CLASS__, 'sort_plugins' ), 90 );
				static::$actions_initialized = true;
			}
		}

		/**
		 * Sort the plugins in the dashboard submenu.
		 *
		 * @return void
		 */
		public static function sort_plugins() {
			global $submenu;
			$parent_slug = ddfw_get_parent_menu_slug();

			if ( ! empty( $submenu[ $parent_slug ] ) ) {
				$dashboard_item  = null;
				$extensions_item = null;
				$other_items     = [];

				foreach ( $submenu[ $parent_slug ] as $item ) {
					$slug = $item[2] ?? '';
					if ( $parent_slug === $slug ) {
						$dashboard_item = $item;
					} elseif ( 'devdiggers-extensions' === $slug ) {
						$extensions_item = $item;
					} else {
						$other_items[] = $item;
					}
				}

				usort(
					$other_items,
					function ( $a, $b ) {
						return strcmp( current( $a ), current( $b ) );
					}
				);

				$sorted_submenu = [];
				if ( $dashboard_item ) {
					$sorted_submenu[] = $dashboard_item;
				}
				$sorted_submenu = array_merge( $sorted_submenu, $other_items );
				if ( $extensions_item ) {
					$sorted_submenu[] = $extensions_item;
				}

				$submenu[ $parent_slug ] = $sorted_submenu; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}

		/**
		 * Add the plugin submenu to the dashboard.
		 *
		 * @return void
		 */
		public function add_plugin_submenu() {
			$hook = add_submenu_page(
				ddfw_get_parent_menu_slug(),
				$this->args[ 'page_title' ],
				$this->args[ 'menu_title' ],
				$this->args[ 'capability' ],
				$this->args[ 'slug' ],
				[ $this, 'ddfw_plugin_dashboard' ]
			);

			if ( ! empty( $this->args[ 'screen_options_callback' ] ) && is_callable( $this->args[ 'screen_options_callback' ] ) ) {
				add_action( "load-{$hook}", $this->args[ 'screen_options_callback' ] );
			}

			// Duplicate Items Hack.
			do_action( 'ddfw_after_adding_plugin_submenu' );
		}

		/**
		 * Render the plugin dashboard.
		 *
		 * @return void
		 */
		public function ddfw_plugin_dashboard() {
			$menus        = $this->args[ 'menus' ];
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$page         = ! empty( $_GET[ 'page' ] ) ? sanitize_title( wp_unslash( $_GET[ 'page' ] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$current_menu = ! empty( $_GET[ 'menu' ] ) ? sanitize_title( wp_unslash( $_GET[ 'menu' ] ) ) : array_key_first( $menus ); // Default to the first menu if none is set.

			// Check if setup wizard is requested via URL parameter.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
			$is_setup_wizard = ! empty( $_GET['setup-wizard'] );

			?>
			<div class="wrap devdiggers-wrap">
				<?php
				include DDFW_FILE . 'templates/header/header.php';

				if ( $is_setup_wizard ) {
					$this->render_setup_wizard( $page );
				} elseif ( ! empty( $this->args[ 'menus' ][ $current_menu ] ) && is_array( $this->args[ 'menus' ][ $current_menu ] ) ) {
					$current_menu_data = $this->args[ 'menus' ][ $current_menu ];
					$layout            = $current_menu_data[ 'layout' ] ?? 'default';  // Load the template for the current menu.

					if ( file_exists( DDFW_FILE . "templates/layout/{$layout}.php" ) ) {
						include DDFW_FILE . "templates/layout/{$layout}.php";
					} else {
						include DDFW_FILE . 'templates/layout/default.php';
					}
				} else {
					// Fallback to a default template if the specific one does not exist.
					include DDFW_FILE . 'templates/layout/default.php';
				}
				?>
			</div>
			<?php
		}

		/**
		 * Render the setup wizard within the dashboard layout.
		 *
		 * @param string $page The current page slug.
		 * @return void
		 */
		private function render_setup_wizard( $page ) {
			if ( has_action( 'ddfw_render_setup_wizard' ) ) {
				do_action( 'ddfw_render_setup_wizard', $page );
			} else {
				// Fallback if no wizard matches this dashboard page.
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Setup wizard not found for this plugin.', 'affiliates-for-woocommerce' ) . '</p></div>';
			}
		}
	}
}
