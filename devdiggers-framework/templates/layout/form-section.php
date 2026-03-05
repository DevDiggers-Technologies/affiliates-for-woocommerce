<?php
/**
 * Form section layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;
defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

settings_errors();
?>
<hr class="wp-header-end" />
<form <?php echo ! empty( $setting_field_name ) ? 'action="' . esc_attr( 'options.php' ) . '"' : ''; ?> method="POST" <?php echo ! empty( $form_id ) ? 'id="' . esc_attr( $form_id ) . '"' : ''; ?>>
	<?php
	if ( ! empty( $setting_field_name ) ) {
		settings_fields( $setting_field_name );
	}
	include DDFW_FILE . 'templates/layout/field-section.php';

	if ( ! empty( $setting_field_name ) ) {
		?>
		<p class="submit">
			<button type="submit" name="submit" id="submit" class="button button-primary">
				<?php
				DDFW_SVG::get_svg_icon(
					'circle-check',
					false,
					[ 'size' => 15 ]
				);
				?>
				<?php esc_html_e( 'Save Changes', 'devdiggers-framework' ); ?>
			</button>
		</p>
		<?php
	}

	if ( ! empty( $form_submit_button ) ) {
		?>
		<p class="submit <?php echo esc_attr( $form_submit_button[ 'button_parent_class' ] ?? '' ); ?>">
			<?php wp_nonce_field( "{$form_submit_button['name']}_nonce_action", "{$form_submit_button['name']}_nonce" ); ?>
			<button type="submit" id="<?php echo esc_attr( $arg[ 'id' ] ?? '' ) ?>" name="<?php echo esc_attr( $form_submit_button['name'] ); ?>" class="button button-primary <?php echo esc_attr( $arg[ 'class' ] ?? '' ) ?>" value="<?php echo esc_attr( ! empty( $form_submit_button[ 'value' ] ? $form_submit_button[ 'value' ] : __( 'Save', 'devdiggers-framework' ) ) ); ?>">
				<?php
				DDFW_SVG::get_svg_icon(
					'circle-check',
					false,
					[ 'size' => 15 ]
				);
				echo esc_html( ! empty( $form_submit_button[ 'value' ] ) ? $form_submit_button[ 'value' ] : __( 'Save', 'devdiggers-framework' ) );
				?>
			</button>
		</p>
		<?php
	}
	?>
</form>
