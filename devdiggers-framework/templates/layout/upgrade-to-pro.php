<?php
/**
 * Upgrade to Pro layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
$defaults = [
	'image_url'           => '',
	'heading'             => '',
	'description'         => '',
	'list_features'       => [],
	'upgrade_url'         => 'https://devdiggers.com/woocommerce-extensions/',
	'upgrade_button_text' => esc_html__( 'Upgrade to Pro', 'affiliates-for-woocommerce' ),
];

$args = wp_parse_args( $args, $defaults );

extract( $args );
?>
<div class="ddfw-upgrade-to-pro-wrapper">
	<?php
	if ( ! empty( $image_url ) ) {
		?>
		<img src="<?php echo esc_url( $image_url ); ?>" alt="" aria-hidden="true" />
		<?php
	}
	?>
	<div class="ddfw-upgrade-to-pro-popup">
		<span class="ddfw-upgrade-to-pro-eyebrow"><?php esc_html_e( 'Pro', 'affiliates-for-woocommerce' ); ?></span>
		<h2><?php echo esc_html( $heading ); ?></h2>
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
		<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary">
			<?php DDFW_SVG::get_svg_icon( 'crown', false, [ 'size' => 15 ] ); ?>
			<?php echo esc_html( $upgrade_button_text ); ?>
		</a>
	</div>
</div>
<?php
