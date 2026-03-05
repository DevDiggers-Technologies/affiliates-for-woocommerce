<?php
/**
 * Simple layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

$tabs = $current_menu_data[ 'tabs' ];
?>
<div class="ddfw-template-container">
	<?php
	if ( ! empty( $tabs ) && is_array( $tabs ) ) {
		$current_tab = ! empty( $_GET[ 'tab' ] ) ? sanitize_title( wp_unslash( $_GET[ 'tab' ] ) ) : array_key_first( $tabs );

		$callback = $tabs[ $current_tab ][ 'callback' ] ?? null;  // Get the callback for the current tab
		?>

		<!-- Mobile settings sidebar toggle button (FAB) -->
		<button class="ddfw-mobile-settings-toggle" type="button" aria-label="<?php esc_attr_e( 'Toggle Settings', 'devdiggers-framework' ); ?>">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
				<path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.06-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.73,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.06,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.49-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
			</svg>
		</button>

		<!-- Mobile settings sidebar overlay -->
		<div class="ddfw-mobile-settings-overlay"></div>

		<nav class="ddfw-left-tab-wrapper">
			<!-- Mobile settings sidebar close button -->
			<button class="ddfw-mobile-settings-close" type="button" aria-label="<?php esc_attr_e( 'Close Settings', 'devdiggers-framework' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
					<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
				</svg>
			</button>

			<?php
			foreach ( $tabs as $key => $tab ) {
				?>
				<a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$current_menu}&tab={$key}" ) ); ?>" class="ddfw-left-tab <?php echo esc_attr( $current_tab === $key ? 'ddfw-left-tab-active' : '' ); ?>">
					<?php
					if ( ! empty( $tab[ 'icon' ] ) ) {
						echo wp_kses( $tab[ 'icon' ], array_merge( wp_kses_allowed_html( 'post' ), ddfw_kses_allowed_svg_tags() ) );
					}
					echo esc_html( $tab[ 'label' ] ); ?>
				</a>
				<?php
			}
			?>
		</nav>
		<div class="ddfw-template-wrapper ddfw-configuration-wrapper">
			<?php
			if ( is_callable( $callback ) ) {
				call_user_func( $callback );
			}
			?>
		</div>
		<?php
	}
	?>
</div>
