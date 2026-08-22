<?php
/**
 * Dynamically loads classes
 *
 * @package Affiliates for WooCommerce
 */

defined( 'ABSPATH' ) || exit();

/*
 * Registered as a closure so more than one active plugin can autoload the same
 * namespace without a "Cannot redeclare" fatal.
 */
spl_autoload_register(
	function ( $class_name ) {
		if ( 0 !== strpos( $class_name, 'DDWCAffiliates\\' ) ) {
			return;
		}

		$file_parts = explode( '\\', $class_name );
		$class_part = array_pop( $file_parts );

		// Drop the root namespace. What is left maps to directories under the plugin root.
		array_shift( $file_parts );

		// Strip the class prefix, then pick the file prefix from the name's own suffix.
		$base_name = str_ireplace( [ '_', 'ddwcaf-' ], [ '-', '' ], strtolower( $class_part ) );

		if ( '-interface' === substr( $base_name, -10 ) ) {
			$file_name = 'interface-' . substr( $base_name, 0, -10 ) . '.php';
		} else {
			$file_name = $base_name . '.php';
		}

		$directories = array_map(
			function ( $part ) {
				return str_replace( '_', '-', strtolower( $part ) );
			},
			$file_parts
		);

		$filepath = trailingslashit( dirname( __DIR__ ) ) . implode( '/', $directories );
		$filepath = trailingslashit( $filepath ) . $file_name;

		// Miss: return quietly so another registered autoloader gets its turn.
		if ( file_exists( $filepath ) ) {
			require_once $filepath;
		}
	}
);
