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

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
if ( ! empty( $args ) && is_array( $args ) ) {
	foreach ( $args as $key => $arg ) {
		// A group made only of after_header_html (an upgrade block, a notice, a custom table)
		// brings its own wrapper, so the section card would only double the padding.
		$html_only = empty( $arg[ 'header' ] ) && empty( $arg[ 'fields' ] ) && empty( $arg[ 'submit_button' ] ) && ! empty( $arg[ 'after_header_html' ] );

		if ( $html_only ) {
			echo ddfw_kses_form_html( $arg[ 'after_header_html' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside the helper.
			continue;
		}
		?>
		<div class="ddfw-fields-section <?php echo esc_attr( $arg[ 'class' ] ?? '' ); ?>" id="<?php echo esc_attr( $arg[ 'id' ] ?? '' ); ?>">
			<?php
			if ( ! empty( $arg[ 'header' ] ) && is_array( $arg[ 'header' ] ) ) {
				ddfw_fields_heading( $arg[ 'header' ] );
			}

			if ( ! empty( $arg[ 'after_header_html' ] ) ) {
				// after_header_html may contain form controls (inputs/selects), so allow form HTML, not just post HTML.
				echo ddfw_kses_form_html( $arg[ 'after_header_html' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside the helper.
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
					<button type="submit" id="<?php echo esc_attr( $arg[ 'id' ] ?? '' ) ?>" name="<?php echo esc_attr( $submit_button['name'] ); ?>" class="button button-primary <?php echo esc_attr( $arg[ 'class' ] ?? '' ) ?>" value="<?php echo esc_attr( ! empty( $submit_button[ 'value' ] ? $submit_button[ 'value' ] : __( 'Save', 'affiliates-for-woocommerce' ) ) ); ?>">
						<?php
						DDFW_SVG::get_svg_icon(
							'circle-check',
							false,
							[ 'size' => 15 ]
						);
						echo esc_html( ! empty( $submit_button[ 'value' ] ) ? $submit_button[ 'value' ] : __( 'Save', 'affiliates-for-woocommerce' ) ); ?>
					</button>
				</p>
				<?php
			}
			?>
		</div>
		<?php
	}
}
