<?php
/**
 * Simple layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

$callback = $current_menu_data[ 'callback' ] ?? null;     // Get the callback for the current menu
?>
<div class="ddfw-template-container">
	<div class="ddfw-template-wrapper ddfw-width-max-content">
		<?php
		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		}
		?>
	</div>
</div>
