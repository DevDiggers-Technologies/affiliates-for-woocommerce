<?php
/**
 * File for handling global functions in the DevDiggers Plugin Framework.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'ddfw_get_parent_menu_slug' ) ) {
	/**
	 * Get the parent menu slug for the DevDiggers Plugins menu.
	 *
	 * @return string
	 */
	function ddfw_get_parent_menu_slug() {
		return apply_filters( 'ddfw_modify_parent_menu_slug', 'devdiggers-plugins' );
	}
}

if ( ! function_exists( 'ddfw_get_menu_capability' ) ) {
	/**
	 * Get the capability required to access the dashboard menu.
	 *
	 * @return string
	 */
	function ddfw_get_menu_capability() {
		return apply_filters( 'ddfw_modify_admin_menu_capability', class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options' );
	}
}

if ( ! function_exists( 'ddfw_get_placeholder_image_src' ) ) {
	/**
	 * Get placeholder image src function
	 *
	 * @return string
	 */
	function ddfw_get_placeholder_image_src() {
		return DDFW_URL . 'assets/images/placeholder.png';
	}
}

if ( ! function_exists( 'ddfw_print_notification' ) ) {
	/**
	 * Print a notification message.
	 *
	 * @param string $message The message to display.
	 * @param string $type    The type of notification (e.g., 'success', 'error').
	 * @param bool   $dismissible Whether the notification is dismissible.
	 */
	function ddfw_print_notification( $message, $type = 'success', $dismissible = true ) {
		include DDFW_FILE . 'templates/global/notice.php';
	}
}

if ( ! function_exists( 'ddfw_kses_allowed_svg_tags' ) ) {
	/**
	 * Get allowed SVG tags for KSES filtering.
	 *
	 * @return array
	 */
	function ddfw_kses_allowed_svg_tags() {
		return [
			'svg'      => [
				'class'           => true,
				'data-*'          => true,
				'aria-*'          => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true,
				'version'         => true,
				'x'               => true,
				'y'               => true,
				'style'           => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			],
			'circle'   => [
				'class'           => true,
				'cx'              => true,
				'cy'              => true,
				'r'               => true,
				'fill'            => true,
				'style'           => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			],
			'g'        => [ 'fill' => true ],
			'polyline' => [
				'class'  => true,
				'points' => true,
				'd'               => true,
				'fill'            => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			],
			'polygon'  => [
				'class'  => true,
				'points' => true,
				'd'               => true,
				'fill'            => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			],
			'line'     => [
				'class' => true,
				'x1'    => true,
				'x2'    => true,
				'y1'    => true,
				'y2'    => true,
			],
			'title'    => [ 'title' => true ],
			'path'     => [
				'class'           => true,
				'd'               => true,
				'fill'            => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			],
			'rect'     => [
				'class'           => true,
				'x'               => true,
				'y'               => true,
				'rx'              => true,
				'ry'              => true,
				'fill'            => true,
				'width'           => true,
				'height'          => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			],
		];
	}
}

if ( ! function_exists( 'ddfw_upgrade_to_pro_section' ) ) {
	/**
	 * Upgrade to Pro section function
	 *
	 * @param array $args
	 * @return void
	 */
	function ddfw_upgrade_to_pro_section( $args ) {
		include DDFW_FILE . 'templates/layout/upgrade-to-pro.php';
	}
}

if ( ! function_exists( 'ddfw_pro_tag' ) ) {
	/**
	 * Pro tag function
	 *
	 * @return void
	 */
	function ddfw_pro_tag() {
		?>
		<span class="ddfw-pro-tag"><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'PRO', 'devdiggers-framework' ); ?></span>
		<?php
	}
}

if ( ! function_exists( 'ddfw_fields_heading' ) ) {
	/**
	 * Fields heading function
	 *
	 * @param array $args
	 * @return void
	 */
	function ddfw_fields_heading( $args ) {
		include DDFW_FILE . 'templates/layout/field-section-header.php';
	}
}

if ( ! function_exists( 'ddfw_get_devdiggers_plugin_menu_icon_src' ) ) {
	/**
	 * Get the DevDiggers plugin menu icon src.
	 *
	 * @return string
	 */
	function ddfw_get_devdiggers_plugin_menu_icon_src() {
		return DDFW_URL . 'assets/images/devdiggers-logo.svg';
	}
}

if ( ! function_exists( 'ddfw_handle_license_deactivation' ) ) {
	/**
	 * Handle generic license deactivation for all DevDiggers plugins
	 * This function should be called via URL parameter: ?ddfw-deactivate-license=LICENSE_KEY
	 *
	 * @return void
	 */
	function ddfw_handle_license_deactivation() {
		// Check if this is a license deactivation request
		if ( ! isset( $_GET['ddfw-deactivate-license'] ) || empty( $_GET['ddfw-deactivate-license'] ) ) {
			return;
		}

		$license_key = sanitize_text_field( wp_unslash( $_GET['ddfw-deactivate-license'] ) );

		// Query database to find the option key that contains this purchase code
		global $wpdb;

		// Look for options that contain the license key (purchase code)
		// DevDiggers plugins typically use pattern: _PREFIX_purchase_code
		$purchase_code_option = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value = %s",
				'%_purchase_code',
				$license_key
			),
			ARRAY_A
		);

		if ( empty( $purchase_code_option ) ) {
			wp_die( 'License not found in any plugin', 'License Not Found', [ 'response' => 404 ] );
		}

		$purchase_code_option_name = $purchase_code_option['option_name'];

		// Extract prefix from option name (e.g., _ddwcpsi_purchase_code -> ddwcpsi)
		$prefix = str_replace( [ '_purchase_code', '_' ], '', $purchase_code_option_name );

		// Construct the license activated option name
		$license_activated_option = '_' . $prefix . '_license_activated';

		// Check if this plugin has the license activated
		$license_activated = get_option( $license_activated_option );

		if ( ! empty( $license_activated ) ) {
			// This is the plugin with the matching license
			// Call the license validation API to check current status
			$args = [
				'purchase_code' => $license_key,
				'user_agent'    => site_url(),
			];

			$response = wp_remote_post( 'https://devdiggers.com/wp-json/ddelv/v1/check-license', [
				'body' => json_encode( $args ),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 30,
			] );

			if ( ! is_wp_error( $response ) ) {
				$response_body = wp_remote_retrieve_body( $response );
				$response_data = json_decode( $response_body, true );

				if ( ! empty( $response_data ) && 
					isset( $response_data['success'] ) && 
					$response_data['success'] && 
					isset( $response_data['status'] ) && 
					in_array( $response_data['status'], [ 'deactivated', 'deleted', 'expired' ] ) ) {

					// License is deactivated on server, remove local activation
					delete_option( $license_activated_option );

					/* translators: %s: Plugin Prefix */
					wp_die( sprintf( esc_html__( "License Deactivated for %s", 'devdiggers-framework' ), esc_html( $prefix ) ), 'License Deactivated', [ 'response' => 200 ] );
				}
			}
		}

		// If we reach here, no matching license was found or deactivation failed
		wp_die( esc_html__( 'License not found or deactivation failed', 'devdiggers-framework' ), esc_html__( 'Deactivation Failed', 'devdiggers-framework' ), [ 'response' => 404 ] );
	}
}

// Hook the license deactivation handler to init
add_action( 'init', 'ddfw_handle_license_deactivation' );

if ( ! function_exists( 'ddfw_get_license_signing_secret' ) ) {
	/**
	 * Get the shared HMAC signing secret for license response verification.
	 * This must match the secret used in the dd-envato-license-validator API.
	 *
	 * @return string
	 */
	function ddfw_get_license_signing_secret() {
		return 'ddfw_lk_s3cr3t_7x9Qm2Kp4R8vW1nB6jY0';
	}
}

if ( ! function_exists( 'ddfw_generate_activation_token' ) ) {
	/**
	 * Generate a hashed activation token for license storage.
	 * This replaces the plain 'yes' value to prevent simple update_option spoofing.
	 *
	 * @param string $purchase_code The license purchase code.
	 * @param string $prefix The plugin prefix (e.g., 'ddwcpr').
	 * @return string The hashed activation token.
	 */
	function ddfw_generate_activation_token( $purchase_code, $prefix ) {
		$salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'ddfw-fallback-salt';
		return hash_hmac( 'sha256', $purchase_code . '|' . $prefix . '|activated', $salt );
	}
}

if ( ! function_exists( 'ddfw_validate_activation_token' ) ) {
	/**
	 * Validate a stored activation token against the purchase code and prefix.
	 *
	 * @param string $stored_value The stored option value.
	 * @param string $purchase_code The license purchase code.
	 * @param string $prefix The plugin prefix (e.g., 'ddwcpr').
	 * @return bool True if valid, false otherwise.
	 */
	function ddfw_validate_activation_token( $stored_value, $purchase_code, $prefix ) {
		if ( empty( $stored_value ) || empty( $purchase_code ) ) {
			return false;
		}

		$expected = ddfw_generate_activation_token( $purchase_code, $prefix );
		return hash_equals( $expected, $stored_value );
	}
}

if ( ! function_exists( 'ddfw_is_license_activated' ) ) {
	/**
	 * Check if a plugin license is properly activated.
	 * Validates the activation token hash rather than just checking for 'yes'.
	 * Falls back to accepting 'yes' for backward compatibility with older activations.
	 *
	 * @param string $prefix The plugin prefix (e.g., 'ddwcpr').
	 * @return bool True if license is activated and valid.
	 */
	function ddfw_is_license_activated( $prefix ) {
		$stored_value  = get_option( "_{$prefix}_license_activated" );
		$purchase_code = get_option( "_{$prefix}_purchase_code" );

		if ( empty( $stored_value ) ) {
			return false;
		}

		// Validate the hash token.
		if ( ddfw_validate_activation_token( $stored_value, $purchase_code, $prefix ) ) {
			return true;
		}

		// Backward compatibility: Accept 'yes' ONLY if it was legitimately stored before the update.
		// Once the cron heartbeat runs and verifies, it will upgrade the token.
		if ( 'yes' === $stored_value && ! empty( $purchase_code ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'ddfw_verify_license_signature' ) ) {
	/**
	 * Verify the HMAC signature on a license check API response.
	 * This ensures the response actually came from devdiggers.com.
	 *
	 * @param array $response_data Decoded JSON response.
	 * @return bool True if signature is valid.
	 */
	function ddfw_verify_license_signature( $response_data ) {
		if ( empty( $response_data['sig'] ) || empty( $response_data['ts'] ) ) {
			// Response is not signed (older API version or forged).
			return false;
		}

		$secret  = ddfw_get_license_signing_secret();
		$payload = ( ! empty( $response_data['success'] ) ? '1' : '0' ) . '|' . $response_data['status'] . '|' . $response_data['ts'];
		$expected_sig = hash_hmac( 'sha256', $payload, $secret );

		if ( ! hash_equals( $expected_sig, $response_data['sig'] ) ) {
			return false;
		}

		// Reject responses older than 5 minutes to prevent replay attacks.
		if ( abs( time() - intval( $response_data['ts'] ) ) > 300 ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'ddfw_direct_license_check' ) ) {
	/**
	 * Perform a direct license check that bypasses WordPress HTTP API.
	 * This defeats the pre_http_request filter used by nullification scripts.
	 * Uses PHP's native cURL directly instead of wp_remote_post().
	 *
	 * @param string $purchase_code The license purchase code.
	 * @param string $site_url The site URL.
	 * @param string $plugin_prefix The plugin prefix.
	 * @return array|false The decoded response data or false on failure.
	 */
	function ddfw_direct_license_check( $purchase_code, $site_url, $plugin_prefix = '' ) {
		$url  = 'https://devdiggers.com/wp-json/ddelv/v1/check-license';
		$body = wp_json_encode( [
			'purchase_code' => $purchase_code,
			'user_agent'    => $site_url,
			'plugin_prefix' => $plugin_prefix,
		] );

		// Use cURL directly to bypass WordPress HTTP API filters.
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt_array( $ch, [
				CURLOPT_URL            => $url,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $body,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTPHEADER     => [
					'Content-Type: application/json',
					'Accept: application/json',
				],
				CURLOPT_SSL_VERIFYPEER => true,
			] );

			$response = curl_exec( $ch );
			$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );

			if ( false !== $response && 200 === $http_code ) {
				$data = json_decode( $response, true );
				if ( is_array( $data ) ) {
					return $data;
				}
			}
		}

		// Fallback: try file_get_contents with stream context.
		$context = stream_context_create( [
			'http' => [
				'method'  => 'POST',
				'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
				'content' => $body,
				'timeout' => 30,
			],
			'ssl' => [
				'verify_peer' => true,
			],
		] );

		$response = @file_get_contents( $url, false, $context );
		if ( false !== $response ) {
			$data = json_decode( $response, true );
			if ( is_array( $data ) ) {
				return $data;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'ddfw_license_heartbeat' ) ) {
	/**
	 * License heartbeat callback for WP Cron.
	 * Periodically verifies all active DevDiggers plugin licenses
	 * using direct HTTP calls that bypass pre_http_request filters.
	 *
	 * @return void
	 */
	function ddfw_license_heartbeat() {
		global $wpdb;

		// Find all active DevDiggers license options.
		$license_options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options} 
			WHERE option_name LIKE '%_license_activated' 
			AND option_name LIKE '\_%' 
			AND option_value != ''",
			ARRAY_A
		);

		if ( empty( $license_options ) ) {
			return;
		}

		$site_url = site_url();

		foreach ( $license_options as $option ) {
			$option_name = $option['option_name'];
			$prefix = str_replace( [ '_license_activated' ], '', ltrim( $option_name, '_' ) );

			$purchase_code = get_option( "_{$prefix}_purchase_code" );
			if ( empty( $purchase_code ) ) {
				continue;
			}

			// Do a direct license check bypassing WP HTTP API.
			$response = ddfw_direct_license_check( $purchase_code, $site_url, $prefix );

			if ( false === $response ) {
				// Could not reach server. Don't deactivate (network issue tolerance).
				continue;
			}

			// Verify the signature to ensure it's from devdiggers.com.
			if ( ! ddfw_verify_license_signature( $response ) ) {
				// Unsigned or forged response. Deactivate the license.
				delete_option( $option_name );
				continue;
			}

			// Check if license is still valid.
			if ( ! empty( $response['success'] ) && ! empty( $response['status'] ) ) {
				if ( in_array( $response['status'], [ 'deactivated', 'deleted', 'expired' ], true ) ) {
					delete_option( $option_name );
				} else if ( 'activated' === $response['status'] ) {
					// Upgrade plain 'yes' to hashed token if needed.
					if ( 'yes' === $option['option_value'] ) {
						$token = ddfw_generate_activation_token( $purchase_code, $prefix );
						update_option( $option_name, $token );
					}
				}
			}
		}
	}
}

// Schedule the license heartbeat cron (runs twice daily).
add_action( 'ddfw_license_heartbeat_cron', 'ddfw_license_heartbeat' );
if ( ! wp_next_scheduled( 'ddfw_license_heartbeat_cron' ) ) {
	wp_schedule_event( time(), 'twicedaily', 'ddfw_license_heartbeat_cron' );
}

if ( ! function_exists( 'ddfw_register_plugin_for_updates' ) ) {
	/**
	 * Register a plugin for the DevDiggers update system.
	 *
	 * Individual plugins call this function to opt-in to the
	 * license-based plugin update system. The function should
	 * be called during 'plugins_loaded' or later.
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
	function ddfw_register_plugin_for_updates( $args ) {
		if ( ! class_exists( 'DDFW_Plugin_Updater' ) ) {
			return;
		}

		$updater = DDFW_Plugin_Updater::instance();
		$updater->register_plugin( $args );
	}
}

if ( ! function_exists( 'ddfw_get_plugin_updater' ) ) {
	/**
	 * Get the plugin updater instance.
	 *
	 * @return DDFW_Plugin_Updater|null The updater instance or null if not available.
	 */
	function ddfw_get_plugin_updater() {
		if ( ! class_exists( 'DDFW_Plugin_Updater' ) ) {
			return null;
		}

		return DDFW_Plugin_Updater::instance();
	}
}
