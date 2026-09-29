<?php
/**
 * Shared empty state block.
 *
 * Included by `ddfw_print_empty_state()`. Kept as a template so a plugin can override the
 * markup through the usual template path without touching the helper.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
if ( empty( $args['icon'] ) ) {
	$args['icon'] = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M14 3v5h5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
}
?>
<div class="ddfw-empty-state">
	<span class="ddfw-empty-state-icon"><?php echo wp_kses( $args['icon'], ddfw_kses_allowed_svg_tags() ); ?></span>
	<h2><?php echo esc_html( $args['title'] ); ?></h2>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p><?php echo esc_html( $args['description'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $args['button_url'] ) && ! empty( $args['button_text'] ) ) : ?>
		<a href="<?php echo esc_url( $args['button_url'] ); ?>" class="button button-primary"><?php echo esc_html( $args['button_text'] ); ?></a>
	<?php endif; ?>
</div>
