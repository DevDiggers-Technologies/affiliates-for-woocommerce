<?php
/**
 * Edit screen layout for the DevDiggers plugins.
 *
 * A two column editor in the shape of the WordPress edit post screen: the sections that carry
 * the substance on the left, the short decisions (status, priority, schedule) in a sticky rail
 * on the right, and one primary action in the title bar that stays in reach.
 *
 * Sections are the same structures `field-section.php` renders everywhere else, so every field
 * type, description and conditional `show_fields` rule keeps working. A section opts into the
 * right hand rail with `'column' => 'side'`.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
$screen_header = wp_parse_args(
	isset( $screen_header ) ? $screen_header : [],
	[
		'heading'         => '',
		'description'     => '',
		'back_button_url' => '',
		'back_button_label' => __( 'Back', 'affiliates-for-woocommerce' ),
		'header_buttons'  => [],
	]
);

$edit_screen_sections = [
	'main' => [],
	'side' => [],
];

foreach ( (array) $args as $edit_screen_section ) {
	$edit_screen_column = ! empty( $edit_screen_section['column'] ) && 'side' === $edit_screen_section['column'] ? 'side' : 'main';

	$edit_screen_sections[ $edit_screen_column ][] = $edit_screen_section;
}

$edit_screen_submit = ! empty( $form_submit_button ) ? $form_submit_button : [];

settings_errors();
?>
<hr class="wp-header-end" />
<form <?php echo ! empty( $setting_field_name ) ? 'action="options.php"' : ''; ?> method="POST" <?php echo ! empty( $form_id ) ? 'id="' . esc_attr( $form_id ) . '"' : ''; ?> class="ddfw-edit-screen-form">
	<?php
	if ( ! empty( $setting_field_name ) ) {
		settings_fields( $setting_field_name );
	}
	?>
	<div class="ddfw-edit-screen-bar">
		<div class="ddfw-edit-screen-bar-title">
			<div class="ddfw-edit-screen-bar-heading">
				<h2><?php echo esc_html( $screen_header['heading'] ); ?></h2>

				<?php if ( ! empty( $screen_header['back_button_url'] ) ) : ?>
					<?php // Beside the heading on its baseline, not above it and not as a button: the way out of a detail screen is navigation, and it belongs with the name of the thing being edited. ?>
					<a class="ddfw-edit-screen-back" href="<?php echo esc_url( $screen_header['back_button_url'] ); ?>">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><path d="M15 19.5 7.7 12.2l7.3-7.3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
						<?php echo esc_html( $screen_header['back_button_label'] ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $screen_header['description'] ) ) : ?>
				<p><?php echo wp_kses_post( $screen_header['description'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="ddfw-edit-screen-bar-actions">
			<?php
			foreach ( $screen_header['header_buttons'] as $header_button ) {
				if ( empty( $header_button['label'] ) || empty( $header_button['url'] ) ) {
					continue;
				}
				?>
				<a href="<?php echo esc_url( $header_button['url'] ); ?>" class="<?php echo esc_attr( ! empty( $header_button['class'] ) ? $header_button['class'] : 'button' ); ?>"><?php echo esc_html( $header_button['label'] ); ?></a>
				<?php
			}

			// The one primary action, in the title bar and in reach from the first field on.
			include DDFW_FILE . 'templates/layout/edit-screen-save.php';
			?>
		</div>
	</div>

	<div class="ddfw-edit-screen<?php echo empty( $edit_screen_sections['side'] ) ? ' ddfw-edit-screen-single' : ''; ?>">
		<div class="ddfw-edit-screen-main">
			<?php
			$args = $edit_screen_sections['main'];
			include DDFW_FILE . 'templates/layout/field-section.php';
			?>
		</div>

		<?php if ( ! empty( $edit_screen_sections['side'] ) ) : ?>
			<div class="ddfw-edit-screen-side">
				<?php
				$args = $edit_screen_sections['side'];
				include DDFW_FILE . 'templates/layout/field-section.php';
				?>
			</div>
		<?php endif; ?>
	</div>
</form>
