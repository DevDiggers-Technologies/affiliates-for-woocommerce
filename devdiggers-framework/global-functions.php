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
