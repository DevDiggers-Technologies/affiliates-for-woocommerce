<?php
/**
 * Field section template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_Form_Field;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! empty( $args ) && is_array( $args ) ) {
	foreach ( $args as $key => $arg ) {
		?>
		<div class="ddfw-fields-section <?php echo esc_attr( $arg[ 'class' ] ?? '' ); ?>" id="<?php echo esc_attr( $arg[ 'id' ] ?? '' ); ?>">
			<?php
			if ( ! empty( $arg[ 'header' ] ) && is_array( $arg[ 'header' ] ) ) {
				ddfw_fields_heading( $arg[ 'header' ] );
			}

			if ( ! empty( $arg[ 'after_header_html' ] ) ) {
				echo ( $arg[ 'after_header_html' ] );
			}

			if ( ! empty( $arg[ 'fields' ] ) && is_array( $arg[ 'fields' ] ) ) {
				?>
				<table class="form-table">
					<tbody>
						<?php
						foreach ( $arg[ 'fields' ] as $field ) {
							DDFW_Form_Field::display_form_field( $field );
						}
						?>
					</tbody>
				</table>
				<?php
			}

			if ( ! empty( $arg[ 'submit_button' ] ) ) {
				$submit_button = $arg[ 'submit_button' ];
				?>
				<p class="submit <?php echo esc_attr( $submit_button[ 'button_parent_class' ] ?? '' ); ?>">
					<?php wp_nonce_field( "{$submit_button['name']}_nonce_action", "{$submit_button['name']}_nonce" ); ?>
					<button type="submit" id="<?php echo esc_attr( $arg[ 'id' ] ?? '' ) ?>" name="<?php echo esc_attr( $submit_button['name'] ); ?>" class="button button-primary <?php echo esc_attr( $arg[ 'class' ] ?? '' ) ?>" value="<?php echo esc_attr( ! empty( $submit_button[ 'value' ] ? $submit_button[ 'value' ] : __( 'Save', 'devdiggers-framework' ) ) ); ?>">
						<?php
						DDFW_SVG::get_svg_icon(
							'circle-check',
							false,
							[ 'size' => 15 ]
						);
						echo esc_html( ! empty( $submit_button[ 'value' ] ) ? $submit_button[ 'value' ] : __( 'Save', 'devdiggers-framework' ) ); ?>
					</button>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	}
}
