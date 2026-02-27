<?php
/**
 * DevDiggers Plugins API Class
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Plugins_API' ) ) {
	/**
	 * Class for handling DevDiggers plugins API
	 */
	class DDFW_Plugins_API {
		/**
		 * The single instance of the class.
		 *
		 * @var DDFW_Plugins_API
		 */
		private static $instance;
		
		/**
		 * API base URL
		 */
		private $api_base_url = 'https://devdiggers.com/wp-json/ddwcs/v1';
		
		/**
		 * Cache key prefix
		 */
		private $cache_prefix = 'ddfw_plugins_';
		
		/**
		 * Cache expiry time (1 day)
		 */
		private $cache_expiry = 86400;
		
		/**
		 * Singleton implementation.
		 *
		 * @return DDFW_Plugins_API
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : new self();
		}

		/**
		 * Runtime cache for plugin data
		 *
		 * @var array
		 */
		private $runtime_cache = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			// Initialize hooks
		}

		/**
		 * Get consolidated plugin data (Single Source of Truth)
		 * 
		 * @return array
		 */
		private function get_data() {
			// Check runtime cache first (fastest)
			if ( ! is_null( $this->runtime_cache ) ) {
				return $this->runtime_cache;
			}

			$cache_key   = $this->cache_prefix . 'master_data';
			$cached_data = get_transient( $cache_key );

			if ( $cached_data !== false ) {
				$this->runtime_cache = $cached_data;
				return $cached_data;
			}

			// Fetch from API
			$plugins_data = $this->fetch_plugins_from_api();

			if ( $plugins_data && isset( $plugins_data['plugins'] ) ) {
				// Cache valid data
				$this->runtime_cache = $plugins_data;
				set_transient( $cache_key, $plugins_data, $this->cache_expiry );
				return $plugins_data;
			}

			// Fallback (Empty data structure) if API fails
			$empty_data = [
				'plugins' => [],
				'stats'   => [
					'total_plugins'     => 0,
					'active_plugins'    => 0,
					'available_plugins' => 0,
					'average_rating'    => 0
				]
			];
			
			$this->runtime_cache = $empty_data;
			return $empty_data;
		}

		/**
		 * Get all plugins from website
		 * 
		 * @return array
		 */
		public function get_website_plugins() {
			$data = $this->get_data();
			return $data['plugins'] ?? [];
		}

		/**
		 * Get featured plugins
		 * 
		 * @return array
		 */
		public function get_featured_plugins() {
			$all_plugins = $this->get_website_plugins();
			
			if ( empty( $all_plugins ) ) {
				return [];
			}

			$featured_plugins = array_filter( $all_plugins, function( $plugin ) {
				return isset( $plugin['is_featured'] ) && $plugin['is_featured'];
			} );
			
			// If no featured plugins found, fallback to first 4
			if ( empty( $featured_plugins ) ) {
				$featured_plugins = array_slice( $all_plugins, 0, 4 );
			} else {
				$featured_plugins = array_values( $featured_plugins );
			}
			
			return $featured_plugins;
		}

		/**
		 * Get plugin statistics
		 * 
		 * @return array
		 */
		public function get_plugin_statistics() {
			$data = $this->get_data();
			
			// If stats already exist in API response, use them
			if ( ! empty( $data['stats'] ) ) {
				return $data['stats'];
			}

			// Calculate stats locally if missing (fallback)
			$all_plugins = $this->get_website_plugins();
			
			return [
				'total_plugins'     => count( $all_plugins ),
				'active_plugins'    => count( array_filter( $all_plugins, function( $plugin ) {
					return isset( $plugin['status'] ) && $plugin['status'] === 'active';
				} ) ),
				'available_plugins' => count( array_filter( $all_plugins, function( $plugin ) {
					return isset( $plugin['status'] ) && $plugin['status'] === 'available';
				} ) ),
				'average_rating'    => 4.9
			];
		}

		/**
		 * Fetch plugins from API
		 * 
		 * @return array|false
		 */
		private function fetch_plugins_from_api() {
			$response = wp_remote_get( $this->api_base_url . '/plugins', [
				'timeout' => 30,
				'headers' => [
					'User-Agent' => 'DevDiggers Framework/' . DDFW_SCRIPT_VERSION
				]
			] );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code !== 200 ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			return json_decode( $body, true );
		}

		/**
		 * Check if plugin is installed locally
		 * 
		 * @param string $plugin_slug
		 * @return array|false
		 */
		public function is_plugin_installed( $plugin_slug ) {
			$installed_plugins = get_plugins();
			$active_plugins = get_option( 'active_plugins', [] );

			foreach ( $installed_plugins as $plugin_file => $plugin_data ) {
				if ( strpos( $plugin_file, $plugin_slug ) !== false || 
					strpos( $plugin_data['Name'], 'DevDiggers' ) !== false ) {
					return [
						'installed'   => true,
						'active'      => in_array( $plugin_file, $active_plugins ),
						'plugin_file' => $plugin_file,
						'plugin_data' => $plugin_data
					];
				}
			}

			return false;
		}

	}
}

// Initialize the class
DDFW_Plugins_API::instance();
