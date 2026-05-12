<?php
/**
 * DevDiggers Plugin Updater
 *
 * Handles WordPress-native plugin updates with license-based access control.
 * Integrates with the DevDiggers license validation API to check for updates,
 * display changelog information, and manage secure downloads.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Plugin_Updater' ) ) {
	/**
	 * Plugin Updater class for handling WordPress-native updates with license validation.
	 */
	class DDFW_Plugin_Updater {

		/**
		 * The single instance of the class.
		 *
		 * @var DDFW_Plugin_Updater
		 */
		private static $instance;

		/**
		 * Array of registered plugins.
		 *
		 * @var array
		 */
		private $plugins = [];

		/**
		 * API base URL.
		 *
		 * @var string
		 */
		private $api_url = 'https://devdiggers.com/wp-json/ddelv/v1';

		/**
		 * Cache TTL in seconds (12 hours).
		 *
		 * @var int
		 */
		private $cache_ttl = 43200;

		/**
		 * Cache key prefix.
		 *
		 * @var string
		 */
		private $cache_prefix = 'ddfw_update_';

		/**
		 * Singleton implementation.
		 *
		 * @return DDFW_Plugin_Updater
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Hook into WordPress update system.
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_updates' ] );
			add_filter( 'plugins_api', [ $this, 'plugin_info' ], 20, 3 );

			// Clear update cache when license is activated/deactivated.
			add_action( 'update_option', [ $this, 'maybe_clear_update_cache' ], 10, 3 );

			// Add custom message in plugins list for expired licenses.
			add_action( 'admin_init', [ $this, 'add_plugin_row_notices' ] );
		}

		/**
		 * Register a plugin for the update system.
		 *
		 * @param array $args {
		 *     Plugin registration arguments.
		 *
		 *     @type string $plugin_file        The plugin file path relative to plugins dir (e.g., 'plugin-dir/plugin.php').
		 *     @type string $plugin_slug         The plugin directory slug (e.g., 'dd-woocommerce-wallet-management').
		 *     @type string $version             The current version of the plugin.
		 *     @type string $license_option_key  The wp_options key storing the license key (e.g., '_ddwcwm_purchase_code').
		 *     @type string $plugin_name         Human-readable plugin name.
		 *     @type string $plugin_product_id   Optional. The WooCommerce product ID on devdiggers.com.
		 * }
		 * @return void
		 */
		public function register_plugin( $args ) {
			$defaults = [
				'plugin_file'       => '',
				'plugin_slug'       => '',
				'version'           => '',
				'license_option_key'=> '',
				'plugin_name'       => '',
				'plugin_product_id' => '',
			];

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['plugin_file'] ) || empty( $args['plugin_slug'] ) || empty( $args['version'] ) ) {
				return;
			}

			$this->plugins[ $args['plugin_slug'] ] = $args;
		}

		/**
		 * Check for plugin updates by hooking into WordPress transient filter.
		 *
		 * @param object $transient The update_plugins transient value.
		 * @return object Modified transient with DevDiggers plugin updates.
		 */
		public function check_for_updates( $transient ) {
			if ( empty( $transient ) ) {
				return $transient;
			}

			foreach ( $this->plugins as $slug => $plugin ) {
				$update_data = $this->get_update_data( $plugin );

				if ( empty( $update_data ) ) {
					continue;
				}

				$plugin_file = $plugin['plugin_file'];

				// Dynamically read version from the live file to catch upgrades immediately in the same request.
				$current_version = $plugin['version'];
				$absolute_plugin_file = WP_PLUGIN_DIR . '/' . $plugin_file;
				if ( file_exists( $absolute_plugin_file ) ) {
					$plugin_data = get_file_data( $absolute_plugin_file, [ 'Version' => 'Version' ] );
					if ( ! empty( $plugin_data['Version'] ) ) {
						$current_version = $plugin_data['Version'];
					}
				}

				// If local version is same or higher than the cached update version, it means the plugin was just updated.
				// We force a fresh API check to update the server stats (ping) and clear the update notice.
				if ( ! empty( $update_data['update'] ) && ! empty( $update_data['version'] ) && version_compare( $current_version, $update_data['version'], '>=' ) ) {
					delete_transient( $this->cache_prefix . $slug );
					$update_data = $this->fetch_update_info( $slug, $current_version, $this->get_license_key( $plugin ) );
					if ( ! empty( $update_data ) ) {
						$this->set_cached_update( $slug, $update_data );
					}
				}

				if ( empty( $update_data['update'] ) ) {
					// Ensure it's not in the response array if it's up to date.
					unset( $transient->response[ $plugin_file ] );

					// Plugin is up to date. Add to no_update so WP knows we checked it.
					if ( ! isset( $transient->no_update[ $plugin_file ] ) ) {
						$transient->no_update[ $plugin_file ] = (object) [
							'id'          => $plugin_file,
							'slug'        => $slug,
							'plugin'      => $plugin_file,
							'new_version' => $update_data['version'] ?? $plugin['version'],
							'url'         => $update_data['homepage'] ?? 'https://devdiggers.com',
						];
					}
					continue;
				}


				// If we have an update, ensure it's not in the no_update array.
				unset( $transient->no_update[ $plugin_file ] );

				// Build the update object that WordPress expects.
				$update_obj = (object) [
					'slug'        => $slug,
					'plugin'      => $plugin_file,
					'new_version' => $update_data['version'],
					'url'         => $update_data['homepage'] ?? 'https://devdiggers.com',
					'package'     => $update_data['download_url'] ?? '', // Empty string = WordPress blocks the update.
					'tested'      => $update_data['tested'] ?? '',
					'requires'    => $update_data['requires'] ?? '',
					'requires_php'=> $update_data['requires_php'] ?? '',
					'icons'       => $update_data['icons'] ?? [],
					'banners'     => $update_data['banners'] ?? [],
				];

				$transient->response[ $plugin_file ] = $update_obj;
			}

			return $transient;
		}

		/**
		 * Provide plugin information for the WordPress "View Details" modal.
		 *
		 * @param false|object|array $result The result object or array. Default false.
		 * @param string             $action The type of information being requested from the Plugin Installation API.
		 * @param object             $args   Plugin API arguments.
		 * @return false|object
		 */
		public function plugin_info( $result, $action, $args ) {
			if ( 'plugin_information' !== $action ) {
				return $result;
			}

			$slug = $args->slug ?? '';

			if ( ! isset( $this->plugins[ $slug ] ) ) {
				return $result;
			}

			$plugin      = $this->plugins[ $slug ];
			$update_data = $this->get_update_data( $plugin );

			if ( empty( $update_data ) ) {
				return $result;
			}

			$info = (object) [
				'name'          => $update_data['name'] ?? $plugin['plugin_name'],
				'slug'          => $slug,
				'version'       => $update_data['version'] ?? $plugin['version'],
				'author'        => $update_data['author'] ?? '<a href="https://devdiggers.com">DevDiggers</a>',
				'author_profile'=> 'https://devdiggers.com',
				'homepage'      => $update_data['homepage'] ?? 'https://devdiggers.com',
				'requires'      => $update_data['requires'] ?? '5.0',
				'tested'        => $update_data['tested'] ?? '',
				'requires_php'  => $update_data['requires_php'] ?? '7.4',
				'last_updated'  => $update_data['last_updated'] ?? '',
				'download_link' => $update_data['download_url'] ?? '',
				'sections'      => $update_data['sections'] ?? [],
				'banners'       => $update_data['banners'] ?? [],
				'icons'         => $update_data['icons'] ?? [],
			];

			// If no download URL (expired license), add a notice to the description.
			if ( empty( $update_data['download_url'] ) ) {
				$license_notice = '<div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px;">';
				$license_notice .= '<strong style="color: #856404;">' . esc_html__( 'License Required', 'loyaltyx-points-and-rewards-for-woocommerce' ) . '</strong><br>';
				$license_notice .= esc_html__( 'A valid license key is required to install this update. Please activate or renew your license at ', 'loyaltyx-points-and-rewards-for-woocommerce' );
				$license_notice .= '<a href="https://devdiggers.com" target="_blank">devdiggers.com</a>.';
				$license_notice .= '</div>';

				$info->sections['description'] = $license_notice . ( $info->sections['description'] ?? '' );
			}

			return $info;
		}



		/**
		 * Add plugin row notices for plugins with update issues.
		 *
		 * @return void
		 */
		public function add_plugin_row_notices() {
			foreach ( $this->plugins as $slug => $plugin ) {
				add_action(
					'in_plugin_update_message-' . $plugin['plugin_file'],
					function ( $plugin_data, $response ) use ( $slug ) {
						$this->display_plugin_row_notice( $slug, $plugin_data, $response );
					},
					10,
					2
				);
			}
		}

		/**
		 * Display inline notice on the plugins page row.
		 *
		 * @param string $slug        The plugin slug.
		 * @param array  $plugin_data Plugin data from WordPress.
		 * @param object $response    Update response data.
		 * @return void
		 */
		private function display_plugin_row_notice( $slug, $plugin_data, $response ) {
			if ( ! isset( $this->plugins[ $slug ] ) ) {
				return;
			}

			$update_data = $this->get_update_data( $this->plugins[ $slug ] );

			if ( ! empty( $update_data ) && empty( $update_data['download_url'] ) ) {
				printf(
					'&nbsp;<span style="color: #dc3232; font-weight: 600;">%s</span> <a href="%s" target="_blank">%s</a>',
					esc_html__( 'A valid license is required to install this update.', 'loyaltyx-points-and-rewards-for-woocommerce' ),
					esc_url( 'https://devdiggers.com' ),
					esc_html__( 'Renew your license', 'loyaltyx-points-and-rewards-for-woocommerce' )
				);
			}
		}

		/**
		 * Get update data for a plugin (from cache or API).
		 *
		 * @param array $plugin The registered plugin data.
		 * @return array|null The update data or null if no update or error.
		 */
		private function get_update_data( $plugin ) {
			$slug = $plugin['plugin_slug'];

			// Try cached data first.
			$cached = $this->get_cached_update( $slug );

			if ( false !== $cached ) {
				return $cached;
			}

			// Fetch from API.
			$license_key = $this->get_license_key( $plugin );
			$update_data = $this->fetch_update_info( $slug, $plugin['version'], $license_key );

			if ( ! empty( $update_data ) ) {
				$this->set_cached_update( $slug, $update_data );
			}

			return $update_data;
		}

		/**
		 * Fetch update information from the DevDiggers API.
		 *
		 * @param string $plugin_slug The plugin slug.
		 * @param string $version     The current plugin version.
		 * @param string $license_key The license key.
		 * @return array|null The update data or null on error.
		 */
		private function fetch_update_info( $plugin_slug, $version, $license_key ) {
			$url = add_query_arg(
				[
					'plugin_slug' => $plugin_slug,
					'version'     => $version,
					'license_key' => $license_key,
					'site_url'    => site_url(),
					't'           => time(),
				],
				$this->api_url . '/plugin-update'
			);

			$response = wp_remote_get( $url, [
				'timeout'   => 15,
				'sslverify' => true,
				'headers'   => [
					'User-Agent'   => 'DevDiggers Framework/' . DDFW_SCRIPT_VERSION,
					'Accept'       => 'application/json',
				],
			] );

			if ( is_wp_error( $response ) ) {
				return null;
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $response_code ) {
				return null;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE || empty( $data ) ) {
				return null;
			}

			return $data;
		}

		/**
		 * Get cached update data for a plugin.
		 *
		 * @param string $slug The plugin slug.
		 * @return array|false The cached data or false if not found/expired.
		 */
		private function get_cached_update( $slug ) {
			if ( ! empty( $_GET['force-check'] ) || ! empty( $_GET['ddfw-force-check'] ) ) {
				return false;
			}
			return get_transient( $this->cache_prefix . $slug );
		}

		/**
		 * Set cached update data for a plugin.
		 *
		 * @param string $slug The plugin slug.
		 * @param array  $data The update data to cache.
		 * @return void
		 */
		private function set_cached_update( $slug, $data ) {
			set_transient( $this->cache_prefix . $slug, $data, $this->cache_ttl );
		}

		/**
		 * Get the license key for a registered plugin.
		 *
		 * @param array $plugin The registered plugin data.
		 * @return string The license key or empty string.
		 */
		private function get_license_key( $plugin ) {
			if ( empty( $plugin['license_option_key'] ) ) {
				return '';
			}

			return get_option( $plugin['license_option_key'], '' );
		}

		/**
		 * Clear update cache when license options change.
		 *
		 * @param string $option    The option name.
		 * @param mixed  $old_value The old option value.
		 * @param mixed  $new_value The new option value.
		 * @return void
		 */
		public function maybe_clear_update_cache( $option, $old_value, $new_value ) {
			foreach ( $this->plugins as $slug => $plugin ) {
				// Clear cache if the option that changed is a license key for one of our plugins.
				if ( $option === $plugin['license_option_key'] ) {
					delete_transient( $this->cache_prefix . $slug );

					// Also delete the site transient to force WordPress to re-check.
					delete_site_transient( 'update_plugins' );
					break;
				}

				// Clear cache if the license activation status changed.
				$prefix = str_replace( '_purchase_code', '', $plugin['license_option_key'] );

				if ( $option === $prefix . '_license_activated' ) {
					delete_transient( $this->cache_prefix . $slug );
					delete_site_transient( 'update_plugins' );
					break;
				}
			}
		}

		/**
		 * Force clear all update caches.
		 *
		 * @return void
		 */
		public function clear_all_caches() {
			foreach ( $this->plugins as $slug => $plugin ) {
				delete_transient( $this->cache_prefix . $slug );
			}

			delete_site_transient( 'update_plugins' );
		}

		/**
		 * Get all registered plugins.
		 *
		 * @return array
		 */
		public function get_registered_plugins() {
			return $this->plugins;
		}
	}
}
