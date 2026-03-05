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
		 * Constructor to initialize hooks.
		 */
		public function __construct( $args = [] ) {
			if ( ! empty( $args ) ) {
				$default_args = [
					'parent_slug' => ddfw_get_parent_menu_slug(),
					'page_title'  => __( 'Plugin Dashboard', 'devdiggers-framework' ),
					'menu_title'  => __( 'Plugin', 'devdiggers-framework' ),
					'capability'  => ddfw_get_menu_capability(),
					'icon_url'    => '',
					'position'    => null,
				];

				$args = apply_filters( 'ddfw_modify_plugin_dashboard_args', wp_parse_args( $args, $default_args ) );

				$this->plugin_dashboard_slug = ! empty( $args[ 'slug' ] ) ? sanitize_title( $args[ 'slug' ] ) : 'devdiggers-plugins';

				$this->args = $args;

				add_action( 'admin_menu', [ $this, 'add_plugin_submenu' ], 20 );
				add_action( 'admin_head', [ $this, 'ddfw_admin_head' ] );

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
			$page = ! empty( $_GET[ 'page' ] ) ? sanitize_title( wp_unslash( $_GET[ 'page' ] ) ) : '';

			return $this->plugin_dashboard_slug === $page;
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
			$page         = ! empty( $_GET[ 'page' ] ) ? sanitize_title( wp_unslash( $_GET[ 'page' ] ) ) : '';
			$current_menu = ! empty( $_GET[ 'menu' ] ) ? sanitize_title( wp_unslash( $_GET[ 'menu' ] ) ) : array_key_first( $menus ); // Default to the first menu if none is set.

			?>
			<div class="wrap devdiggers-wrap">
				<?php
				include DDFW_FILE . 'templates/header/header.php';

				if ( ! empty( $this->args[ 'menus' ][ $current_menu ] ) && is_array( $this->args[ 'menus' ][ $current_menu ] ) ) {
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
	}
}
