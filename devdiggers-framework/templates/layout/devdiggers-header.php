<?php
/**
 * Top navigation for the DevDiggers hub pages (Dashboard and Extensions).
 * Uses the same header bar markup as the plugin dashboards.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
$current_page = ! empty( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
$hub_tabs     = [
	ddfw_get_parent_menu_slug() => esc_html__( 'Dashboard', 'affiliates-for-woocommerce' ),
	'devdiggers-extensions'     => esc_html__( 'Extensions', 'affiliates-for-woocommerce' ),
];
?>
<nav class="ddfw-header-tab-wrapper ddfw-hub-header">
	<div class="ddfw-header-tabs-list-wrapper">
		<div class="ddfw-plugin-name">
			<span class="ddfw-hub-logo" style="--ddfw-hub-logo: url('<?php echo esc_url( ddfw_get_devdiggers_plugin_menu_icon_src() ); ?>');" aria-hidden="true"></span>
			<?php esc_html_e( 'DevDiggers', 'affiliates-for-woocommerce' ); ?>
		</div>
		<ul class="ddfw-header-tabs">
			<?php foreach ( $hub_tabs as $slug => $label ) : ?>
				<li class="ddfw-header-tab <?php echo esc_attr( $current_page === $slug ? 'ddfw-header-tab-active' : '' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"><?php echo esc_html( $label ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="ddfw-hub-header-actions">
			<a href="<?php echo esc_url( 'https://docs.devdiggers.com/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Docs', 'affiliates-for-woocommerce' ); ?></a>
			<a href="<?php echo esc_url( 'https://devdiggers.com/contact/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'affiliates-for-woocommerce' ); ?></a>
		</div>
	</div>
</nav>
