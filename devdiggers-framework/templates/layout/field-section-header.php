<?php
/**
 * Field section header template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

$defaults = [
	'heading'             => '',
	'description'         => '',
	'back_button_enabled' => false,
	'back_button_url'     => '',
];

$args = wp_parse_args( $args, $defaults );

?>
<div class="ddfw-fields-section-header">
	<h3>
		<?php echo esc_html( $args[ 'heading' ] );
		if ( $args[ 'back_button_enabled' ] ) {
			?>
			&nbsp;
			<a href="<?php echo esc_url( $args[ 'back_button_url' ] ); ?>">← &nbsp;<?php esc_html_e( 'Back', 'devdiggers-framework' ); ?></a>
		<?php
		}
		?>
	</h3>
	<?php
	if ( ! empty( $args[ 'description' ] ) ) {
		?>
		<p><?php echo wp_kses_post( $args[ 'description' ] ); ?></p>
		<?php
	}
	?>
</div>
