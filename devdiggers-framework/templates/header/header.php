<?php
/**
 * File for handling the header of the DevDiggers Plugin Framework.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

?>
<nav class="ddfw-header-tab-wrapper">
	<div class="ddfw-header-tabs-list-wrapper">
		<div class="ddfw-plugin-name">
			<?php echo wp_kses( $this->args[ 'plugin_name' ], array_merge( wp_kses_allowed_html( 'post' ), ddfw_kses_allowed_svg_tags() ) ); ?>
		</div>
		<?php

		$count       = 1;
		$show_menus  = 8;
		$total_menus = count( $menus );

		?>

		<!-- Mobile hamburger toggle button (visible below 1023px) -->
		<button type="button" class="ddfw-mobile-header-toggle" aria-label="<?php esc_attr_e( 'Open navigation menu', 'devdiggers-framework' ); ?>">
			<span class="dashicons dashicons-menu"></span>
		</button>

		<ul class="ddfw-header-tabs">
			<?php
			foreach ( $menus as $slug => $menu ) {
				if ( $count < $show_menus ) {
					?>
					<li class="ddfw-header-tab <?php echo esc_attr( $current_menu === $slug ? 'ddfw-header-tab-active' : '' ); ?>">
						<a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$slug}" ) ); ?>"><?php echo esc_html( $menu[ 'label' ] ); ?></a>
					</li>
					<?php
				} else {
					if ( $show_menus === $count ) {
						?>
						<li class="ddfw-header-tab"><a href="#"><?php esc_html_e( 'More', 'devdiggers-framework' ); ?><span class="dashicons dashicons-arrow-down-alt2"></span></a>
						<ul class="ddfw-header-dropdown">
							<li class="ddfw-header-tab <?php echo esc_attr( $current_menu === $slug ? 'ddfw-header-tab-active' : '' ); ?>"><a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$slug}" ) ); ?>"><?php echo esc_html( $menu[ 'label' ] ); ?></a></li>
						<?php
					} else {
						?>
						<li class="ddfw-header-tab <?php echo esc_attr( $current_menu === $slug ? 'ddfw-header-tab-active' : '' ); ?>"><a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$slug}" ) ); ?>"><?php echo esc_html( $menu[ 'label' ] ); ?></a></li>
						<?php
					}

					if ( $total_menus === $count ) {
						?>
						</ul></li>
						<?php
					}
				}
				++$count;
			}
			?>
		</ul>
		<div class="ddfw-upgrade-to-pro-button-wrapper">
			<?php
			if ( ! empty( $this->args[ 'upgrade_url' ] ) ) {
				?>
				<a href="<?php echo esc_url( $this->args[ 'upgrade_url' ] ); ?>" target="_blank" class="ddfw-upgrade-to-pro-button">
					<?php
					DDFW_SVG::get_svg_icon(
						'crown',
						false,
						[ 'size' => 15 ]
					);
					?>
					<?php esc_html_e( 'Upgrade to Pro', 'devdiggers-framework' ); ?>
				</a>
				<?php
			}
			?>
		</div>
	</div>
</nav>

<!-- Mobile sidebar overlay (click to close) -->
<div class="ddfw-mobile-header-overlay"></div>

<!-- Mobile sidebar drawer -->
<div class="ddfw-mobile-header-drawer">
	<div class="ddfw-mobile-header-header">
		<div class="ddfw-mobile-header-plugin-name">
			<?php echo wp_kses( $this->args[ 'plugin_name' ], array_merge( wp_kses_allowed_html( 'post' ), ddfw_kses_allowed_svg_tags() ) ); ?>
		</div>
		<button type="button" class="ddfw-mobile-header-close" aria-label="<?php esc_attr_e( 'Close navigation menu', 'devdiggers-framework' ); ?>">
			<span class="dashicons dashicons-no-alt"></span>
		</button>
	</div>
	<ul class="ddfw-mobile-header-menu">
		<?php foreach ( $menus as $slug => $menu ) : ?>
		<li>
			<a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$slug}" ) ); ?>"
			   class="ddfw-left-tab <?php echo esc_attr( $current_menu === $slug ? 'ddfw-left-tab-active' : '' ); ?>">
				<?php echo esc_html( $menu[ 'label' ] ); ?>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
