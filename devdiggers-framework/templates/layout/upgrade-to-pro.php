<?php
/**
 * Upgrade to Pro layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

$defaults = [
	'image_url'           => '',
	'heading'             => '',
	'description'         => '',
	'list_features'       => [],
	'upgrade_url'         => 'https://devdiggers.com/woocommerce-extensions/',
	'upgrade_button_text' => esc_html__( 'Upgrade to Pro', 'devdiggers-framework' ),
];

$args = wp_parse_args( $args, $defaults );

extract( $args );
?>
<div class="ddfw-upgrade-to-pro-wrapper">
	<?php
	if ( ! empty( $image_url ) ) {
		?>
		<img src="<?php echo esc_url( $image_url ); ?>" alt="Upgrade to Pro Plugin Screenshot" title="Upgrade to Pro Plugin Screenshot" />
		<?php
	}
	?>
	<div class="ddfw-upgrade-to-pro-popup">
		<h2><?php echo esc_html( $heading ); ?></h2>
		<hr />
		<p><?php echo esc_html( $description ); ?></p>
		<?php
		if ( ! empty( $list_features ) && is_array( $list_features ) ) {
			?>
			<ul>
			<?php
			foreach ( $list_features as $feature ) {
				?>
				<li><?php echo esc_html( $feature ); ?></li>
				<?php
			}
			?>
			</ul>
			<?php
		}
		?>
		<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="button button-primary"><?php echo esc_html( $upgrade_button_text ); ?></a>
	</div>
</div>
<?php
