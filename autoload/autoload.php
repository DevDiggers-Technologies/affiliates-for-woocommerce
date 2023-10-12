<?php
/**
 * Dynamically loads classes
 * 
 * @package Affiliates for WooCommerce
 */

namespace DDWCAffiliates;

defined( 'ABSPATH' ) || exit();

spl_autoload_register( 'DDWCAffiliates\ddwcaf_namespace_class_autoload' );

/**
 * @param string $class_name The name of the class to load.
 */
function ddwcaf_namespace_class_autoload( $class_name ) {
    if ( false === strpos( $class_name, 'DDWCAffiliates' ) ) {
        return;
    }

    $file_parts = explode( '\\', $class_name );

    $namespace = '';

    for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
		$current = strtolower( $file_parts[ $i ] );
		$current = str_ireplace( '_', '-', $current );
		$current = str_ireplace( 'ddwcaf-', '', $current );

		if ( count( $file_parts ) - 1 === $i ) {
			if ( strpos( strtolower( $file_parts[ count( $file_parts ) - 1 ] ), 'interface' ) ) {
				$interface_name = explode( '_', $file_parts[ count( $file_parts ) - 1 ] );
				array_pop( $interface_name );
				$interface_name = strtolower( implode( '-', $interface_name ) );
				$file_name = "interface-{$interface_name}.php";
			} else {
				$file_name = "{$current}.php";
			}
		} else {
			$namespace = '/' . $current . $namespace;
		}

		$filepath  = trailingslashit( dirname( dirname( __FILE__ ) ) . $namespace );
		$filepath .= $file_name;
	}

    // If the file exists in the specified path, then include it.
    if ( file_exists( $filepath ) ) {
        include_once( $filepath );
    } else {
        wp_die(
			/* translators: %s for the filepath */
			sprintf( esc_html__( 'The file attempting to be loaded at %s does not exist.', 'affiliates-for-woocommerce' ), $filepath )
        );
    }
}
