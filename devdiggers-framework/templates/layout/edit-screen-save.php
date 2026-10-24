<?php
/**
 * Primary action card for the edit screen layout.
 *
 * Included by `edit-screen.php`, once, either at the top of the side rail or below a single
 * column form. Kept in its own file so the two placements cannot drift apart.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
if ( empty( $edit_screen_submit['name'] ) && empty( $setting_field_name ) ) {
	return;
}
?>
<div class="ddfw-edit-screen-save">
	<?php
	if ( ! empty( $edit_screen_submit['name'] ) ) {
		wp_nonce_field( "{$edit_screen_submit['name']}_nonce_action", "{$edit_screen_submit['name']}_nonce" );
		$edit_screen_submit_label = ! empty( $edit_screen_submit['value'] ) ? $edit_screen_submit['value'] : __( 'Save', 'affiliates-for-woocommerce' );
		?>
		<button type="submit" name="<?php echo esc_attr( $edit_screen_submit['name'] ); ?>" class="button button-primary" value="<?php echo esc_attr( $edit_screen_submit_label ); ?>">
			<?php
			DDFW_SVG::get_svg_icon( 'circle-check', false, [ 'size' => 15 ] );
			echo esc_html( $edit_screen_submit_label );
			?>
		</button>
		<?php
	} else {
		?>
		<button type="submit" name="submit" id="submit" class="button button-primary">
			<?php
			DDFW_SVG::get_svg_icon( 'circle-check', false, [ 'size' => 15 ] );
			esc_html_e( 'Save Changes', 'affiliates-for-woocommerce' );
			?>
		</button>
		<?php
	}
	?>
</div>
