<?php
/**
 * Autoloader for DevDiggers\Framework classes and interfaces.
 *
 * @package DevDiggers\Framework
 * @since 1.0.0
 */

namespace DevDiggers\Framework;

defined( 'ABSPATH' ) || exit();

spl_autoload_register( __NAMESPACE__ . '\\ddfw_namespace_class_autoload' );

/**
 * Autoload classes and interfaces within DevDiggers\Framework namespace.
 *
 * @param string $class The fully qualified class name.
 */
function ddfw_namespace_class_autoload( $class ) {
	$prefix   = __NAMESPACE__ . '\\';
	$base_dir = dirname( __DIR__ ) . '/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );

	// Convert namespace to path
	$relative_class_path = str_replace( '\\', '/', $relative_class );
	$relative_class_path = strtolower( str_replace( '_', '-', $relative_class_path ) );

	$path_parts = explode( '/', $relative_class_path );
	$class_name = array_pop( $path_parts );
	$dir_path   = implode( '/', $path_parts );

	// Default to class prefix
	$prefix = 'class-';

	// Detect interfaces based on naming (ends in -interface)
	if ( '-interface' === substr( $class_name, -10 ) ) {
		$prefix = 'interface-';
		$class_name = substr( $class_name, 0, -10 );
	}

	$file_name = $prefix . $class_name . '.php';

	$file = $base_dir . ( $dir_path ? $dir_path . '/' : '' ) . $file_name;

	if ( file_exists( $file ) ) {
		require_once $file;
	} else {
		wp_die(
			/* translators: %1$s: class name, %2$s: file path */
			sprintf( esc_html__( 'Autoloader error: The file for class %1$s was expected at path %2$s but was not found.', 'devdiggers-framework' ), esc_html( $class ), esc_html( $file ) ),
			esc_html__( 'Autoloader Error', 'devdiggers-framework' )
		);
	}
}
