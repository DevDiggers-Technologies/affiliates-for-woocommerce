<?php
/**
 * Autoloader for DevDiggers\Framework classes and interfaces.
 *
 * @package DevDiggers\Framework
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit();

/*
 * Registered as a closure so more than one active plugin can autoload the same
 * namespace without a "Cannot redeclare" fatal.
 */
spl_autoload_register(
	function ( $class_name ) {
		$prefix = 'DevDiggers\\Framework\\';

		if ( 0 !== strncmp( $prefix, $class_name, strlen( $prefix ) ) ) {
			return;
		}

		// Namespace separators and underscores both become path separators or hyphens.
		$relative = substr( $class_name, strlen( $prefix ) );
		$relative = strtolower( str_replace( [ '\\', '_' ], [ '/', '-' ], $relative ) );

		$path_parts = explode( '/', $relative );
		$base_name  = array_pop( $path_parts );
		$dir_path   = implode( '/', $path_parts );

		$file_prefix = 'class-';

		if ( '-interface' === substr( $base_name, -10 ) ) {
			$file_prefix = 'interface-';
			$base_name   = substr( $base_name, 0, -10 );
		}

		$filepath = trailingslashit( dirname( __DIR__ ) ) . ( $dir_path ? $dir_path . '/' : '' ) . $file_prefix . $base_name . '.php';

		// Miss: return quietly so another registered autoloader gets its turn.
		if ( file_exists( $filepath ) ) {
			require_once $filepath;
		}
	}
);
