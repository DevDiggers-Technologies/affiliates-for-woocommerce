<?php
/**
 * Default layout template for the DevDiggers Plugin Framework.
 *
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

?>
<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing parameter.
$current_page = ! empty( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
?>
<div class="ddfw-template-container">
	<div class="ddfw-template-wrapper">
		<div class="ddfw-empty-state">
			<div class="ddfw-empty-state-icon" aria-hidden="true">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/><path d="M8.5 11h5"/></svg>
			</div>
			<h2><?php esc_html_e( 'This section could not be found', 'affiliates-for-woocommerce' ); ?></h2>
			<p><?php esc_html_e( 'The link may be outdated or the feature is not available in this version. Pick a section from the menu above.', 'affiliates-for-woocommerce' ); ?></p>
			<?php if ( $current_page ) : ?>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $current_page ) ); ?>"><?php esc_html_e( 'Back to dashboard', 'affiliates-for-woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
