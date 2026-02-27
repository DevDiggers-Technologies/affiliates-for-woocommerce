<?php
/**
 * Notice template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	?>
	<div class="notice notice-<?php echo esc_attr( $type . ' ' . ( $dismissible ? 'is-dismissible' : '' ) ); ?>">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>
	<?php
} else if ( function_exists( 'wc_print_notice' ) ) {
	wc_print_notice( $message, $type );
}
