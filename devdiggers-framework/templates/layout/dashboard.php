<?php
/**
 * Dashboard layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// Get installed DevDiggers plugins
$installed_plugins = get_plugins();
$active_plugins    = get_option( 'active_plugins', [] );

// Filter for DevDiggers plugins only and separate active/inactive
$active_devdiggers_plugins   = [];
$inactive_devdiggers_plugins = [];

foreach ( $installed_plugins as $plugin_file => $plugin_data ) {
	if ( strpos( $plugin_data['Name'], 'DevDiggers' ) !== false || 
		strpos( $plugin_data['Name'], 'DD' ) !== false ||
		strpos( $plugin_data['Author'], 'DevDiggers' ) !== false ) {

		if ( in_array( $plugin_file, $active_plugins, true ) ) {
			$active_devdiggers_plugins[ $plugin_file ] = $plugin_data;
		} else {
			$inactive_devdiggers_plugins[ $plugin_file ] = $plugin_data;
		}
	}
}

// Combine arrays: active plugins first, then inactive plugins
$devdiggers_plugins = $active_devdiggers_plugins + $inactive_devdiggers_plugins;

// Get plugin statistics
$total_installed = count( $devdiggers_plugins );
$total_plugins   = count( $installed_plugins );
$total_active    = 0;
foreach ( $devdiggers_plugins as $plugin_file => $plugin_data ) {
	if ( in_array( $plugin_file, $active_plugins, true ) ) {
		$total_active++;
	}
}

// Get system info
$wp_version         = get_bloginfo( 'version' );
$php_version        = PHP_VERSION;
$memory_limit       = ini_get( 'memory_limit' );
$max_execution_time = ini_get( 'max_execution_time' );

$current_user = wp_get_current_user();

?>
<div class="devdiggers-wrap">
	<div class="ddfw-dashboard-container">
		<!-- Dashboard Header -->
		<div class="ddfw-dashboard-header">
			<div class="ddfw-admin-avatar">
				<img src="<?php echo esc_url( get_avatar_url( $current_user->ID, [ 'size' => 48 ] ) ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" class="ddfw-avatar-image" />
			</div>
			<div class="ddfw-dashboard-welcome">
				<h1>
					<?php
					/* translators: %s: current user display name */
					printf( esc_html__( 'Welcome to DevDiggers, %s! 👋🏻', 'devdiggers-framework' ), esc_html( $current_user->display_name ) );
					?>
				</h1>
				<p><?php esc_html_e( 'Manage all your DevDiggers plugins from one central dashboard', 'devdiggers-framework' ); ?></p>
			</div>
		</div>

		<?php
		$is_subscribed = get_option( 'ddfw_newsletter_subscribed' );
		if ( ! $is_subscribed ) {
			?>
			<!-- Newsletter Signup Section -->
			<div class="ddfw-dashboard-section ddfw-newsletter-section">
				<div class="ddfw-newsletter-content">
					<div class="ddfw-newsletter-text">
						<h2><?php esc_html_e( 'Stay Updated with DevDiggers', 'devdiggers-framework' ); ?></h2>
						<p><?php esc_html_e( 'Subscribe to our newsletter for the latest updates, tips and exclusive offers on WooCommerce plugins.', 'devdiggers-framework' ); ?></p>
					</div>
					<form class="ddfw-newsletter-form" method="post">
						<div class="ddfw-form-row">
							<input type="email" name="email" id="ddfw-newsletter-email" placeholder="<?php esc_attr_e( 'Enter your email address', 'devdiggers-framework' ); ?>" required />
							<button type="submit" class="ddfw-button ddfw-button-primary" id="ddfw-newsletter-submit">
								<?php esc_html_e( 'Subscribe', 'devdiggers-framework' ); ?>
							</button>
						</div>
						<div id="ddfw-newsletter-message" class="ddfw-newsletter-message"></div>
					</form>
				</div>
				<div class="ddfw-newsletter-features">
					<div class="ddfw-feature-item">
						<div class="ddfw-feature-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="ddfw-feature-text">
							<h4><?php esc_html_e( 'Latest Updates', 'devdiggers-framework' ); ?></h4>
							<p><?php esc_html_e( 'Get notified about new features and improvements', 'devdiggers-framework' ); ?></p>
						</div>
					</div>
					<div class="ddfw-feature-item">
						<div class="ddfw-feature-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="ddfw-feature-text">
							<h4><?php esc_html_e( 'Expert Tips', 'devdiggers-framework' ); ?></h4>
							<p><?php esc_html_e( 'Learn best practices and optimization techniques', 'devdiggers-framework' ); ?></p>
						</div>
					</div>
					<div class="ddfw-feature-item">
						<div class="ddfw-feature-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<text x="12" y="18" font-size="17" font-weight="bold" text-anchor="middle" fill="currentColor">%</text>
							</svg>
						</div>
						<div class="ddfw-feature-text">
							<h4><?php esc_html_e( 'Special Offers', 'devdiggers-framework' ); ?></h4>
							<p><?php esc_html_e( 'Exclusive discounts and promotional deals', 'devdiggers-framework' ); ?></p>
						</div>
					</div>
					<div class="ddfw-feature-item">
						<div class="ddfw-feature-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="ddfw-feature-text">
							<h4><?php esc_html_e( 'Community', 'devdiggers-framework' ); ?></h4>
							<p><?php esc_html_e( 'Connect with other WooCommerce developers', 'devdiggers-framework' ); ?></p>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>

		<!-- Dashboard Stats -->
		<div class="ddfw-dashboard-stats">
			<div class="ddfw-stat-card">
				<div class="ddfw-stat-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor"/>
						<path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
						<path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
					</svg>
				</div>
				<div class="ddfw-stat-content">
					<h3><?php echo esc_html( $total_installed ); ?></h3>
					<p><?php esc_html_e( 'Installed Plugins', 'devdiggers-framework' ); ?></p>
				</div>
			</div>
			<div class="ddfw-stat-card">
				<div class="ddfw-stat-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
						<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
					</svg>
				</div>
				<div class="ddfw-stat-content">
					<h3><?php echo esc_html( $total_active ); ?></h3>
					<p><?php esc_html_e( 'Active Plugins', 'devdiggers-framework' ); ?></p>
				</div>
			</div>
			<div class="ddfw-stat-card">
				<div class="ddfw-stat-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
						<path d="M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</div>
				<div class="ddfw-stat-content">
					<h3><?php echo esc_html( $total_installed - $total_active ); ?></h3>
					<p><?php esc_html_e( 'Inactive Plugins', 'devdiggers-framework' ); ?></p>
				</div>
			</div>
			<div class="ddfw-stat-card">
				<div class="ddfw-stat-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
					</svg>
				</div>
				<div class="ddfw-stat-content">
					<h3><?php echo esc_html( $total_installed > 0 ? round( ( $total_active / $total_installed ) * 100 ) : 0 ); ?>%</h3>
					<p><?php esc_html_e( 'Activation Rate', 'devdiggers-framework' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Installed DevDiggers Plugins Section -->
		<div class="ddfw-dashboard-section">
			<div class="ddfw-section-header">
				<h2><?php esc_html_e( 'Installed DevDiggers Plugins', 'devdiggers-framework' ); ?></h2>
				<p><?php esc_html_e( 'Manage your installed DevDiggers WooCommerce extensions', 'devdiggers-framework' ); ?></p>
			</div>
			<div class="ddfw-plugins-grid">
				<?php foreach ( $devdiggers_plugins as $plugin_file => $plugin_data ) : ?>
					<?php
					$is_active     = in_array( $plugin_file, $active_plugins, true );
					$plugin_slug   = dirname( $plugin_file );
					$plugin_prefix = $plugin_data['DevDiggersPrefix'];
					$admin_url     = admin_url( 'admin.php?page=' . $plugin_prefix . '-dashboard' );
					?>
					<div class="ddfw-plugin-card <?php echo esc_attr( $is_active ? 'ddfw-plugin-active' : 'ddfw-plugin-inactive' ); ?>">
						<!-- Plugin Header -->
						<div class="ddfw-plugin-header">
							<div class="ddfw-plugin-icon">
								<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
									<path d="M19.5 7.5c-.828 0-1.5-.672-1.5-1.5V4.5A1.5 1.5 0 0 0 16.5 3h-2.25c-.828 0-1.5.672-1.5 1.5 0 .828-.672 1.5-1.5 1.5s-1.5-.672-1.5-1.5A1.5 1.5 0 0 0 6.75 3H4.5A1.5 1.5 0 0 0 3 4.5v2.25c0 .828.672 1.5 1.5 1.5.828 0 1.5.672 1.5 1.5s-.672 1.5-1.5 1.5A1.5 1.5 0 0 0 3 13.5v2.25A1.5 1.5 0 0 0 4.5 17h2.25c.828 0 1.5-.672 1.5-1.5 0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5c0 .828.672 1.5 1.5 1.5h2.25a1.5 1.5 0 0 0 1.5-1.5v-2.25c0-.828-.672-1.5-1.5-1.5-.828 0-1.5-.672-1.5-1.5s.672-1.5 1.5-1.5c.828 0 1.5-.672 1.5-1.5V6c0-.828-.672-1.5-1.5-1.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</div>
							<div class="ddfw-plugin-status">
								<?php if ( $is_active ) { ?>
									<span class="ddfw-status-badge ddfw-status-active">
										<?php esc_html_e( 'Active', 'devdiggers-framework' ); ?>
									</span>
								<?php } else { ?>
									<span class="ddfw-status-badge ddfw-status-inactive">
										<?php esc_html_e( 'Inactive', 'devdiggers-framework' ); ?>
									</span>
								<?php } ?>
							</div>
						</div>

						<!-- Plugin Content -->
						<div class="ddfw-plugin-content">
							<div class="ddfw-plugin-title-section">
								<h3><?php echo esc_html( $plugin_data['Name'] ); ?></h3>
								<?php if ( ! empty( $plugin_data['Description'] ) ) : ?>
									<p class="ddfw-plugin-description"><?php echo wp_kses_post( $plugin_data['Description'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>

						<!-- Plugin Footer -->
						<div class="ddfw-plugin-footer">
							<div class="ddfw-plugin-meta">
								<span class="ddfw-plugin-version">v<?php echo esc_html( $plugin_data['Version'] ); ?></span>
							</div>
							<div class="ddfw-plugin-actions">
								<?php if ( $is_active ) { ?>
									<a href="<?php echo esc_url( $admin_url ); ?>" class="ddfw-button ddfw-button-primary">
										<?php esc_html_e( 'Visit', 'devdiggers-framework' ); ?>
									</a>
								<?php } else { ?>
									<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="ddfw-button ddfw-button-secondary">
										<?php esc_html_e( 'Activate', 'devdiggers-framework' ); ?>
									</a>
								<?php } ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- System Information Section -->
		<div class="ddfw-dashboard-section">
			<div class="ddfw-section-header">
				<h2><?php esc_html_e( 'System Information', 'devdiggers-framework' ); ?></h2>
				<p><?php esc_html_e( 'Your WordPress environment details', 'devdiggers-framework' ); ?></p>
			</div>
			<div class="ddfw-system-info">
				<div class="ddfw-info-grid">
					<div class="ddfw-info-item">
						<div class="ddfw-info-label"><?php esc_html_e( 'WordPress Version', 'devdiggers-framework' ); ?></div>
						<div class="ddfw-info-value"><?php echo esc_html( $wp_version ); ?></div>
					</div>
					<div class="ddfw-info-item">
						<div class="ddfw-info-label"><?php esc_html_e( 'PHP Version', 'devdiggers-framework' ); ?></div>
						<div class="ddfw-info-value"><?php echo esc_html( $php_version ); ?></div>
					</div>
					<div class="ddfw-info-item">
						<div class="ddfw-info-label"><?php esc_html_e( 'Memory Limit', 'devdiggers-framework' ); ?></div>
						<div class="ddfw-info-value"><?php echo esc_html( $memory_limit ); ?></div>
					</div>
					<div class="ddfw-info-item">
						<div class="ddfw-info-label"><?php esc_html_e( 'Max Execution Time', 'devdiggers-framework' ); ?></div>
						<div class="ddfw-info-value"><?php echo esc_html( $max_execution_time ); ?>s</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Support Section -->
		<div class="ddfw-dashboard-section">
			<div class="ddfw-section-header">
				<h2><?php esc_html_e( 'Need Help?', 'devdiggers-framework' ); ?></h2>
				<p><?php esc_html_e( 'Get support and resources for your DevDiggers plugins', 'devdiggers-framework' ); ?></p>
			</div>
			<div class="ddfw-support-grid">
				<div class="ddfw-support-card">
					<div class="ddfw-support-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
							<path d="M2 19.5V6.75C2 5.23122 3.23122 4 4.75 4H9.25C10.7688 4 12 5.23122 12 6.75V19.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M12 19.5V6.75C12 5.23122 13.2312 4 14.75 4H19.25C20.7688 4 22 5.23122 22 6.75V19.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M2 19.5C2 18.1193 3.11929 17 4.5 17H9.5C10.8807 17 12 18.1193 12 19.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M12 19.5C12 18.1193 13.1193 17 14.5 17H19.5C20.8807 17 22 18.1193 22 19.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<h3><?php esc_html_e( 'Documentation', 'devdiggers-framework' ); ?></h3>
					<p><?php esc_html_e( 'Comprehensive guides and tutorials', 'devdiggers-framework' ); ?></p>
					<a href="<?php echo esc_url( '//devdiggers.com/knowledge-base/' ); ?>" target="_blank" class="ddfw-button ddfw-button-secondary">
						<?php esc_html_e( 'View Docs', 'devdiggers-framework' ); ?>
					</a>
				</div>
				<div class="ddfw-support-card">
					<div class="ddfw-support-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
							<rect width="24" height="24" fill="none"/>
							<path d="M3 6.75C3 5.7835 3.7835 5 4.75 5H19.25C20.2165 5 21 5.7835 21 6.75V17.25C21 18.2165 20.2165 19 19.25 19H4.75C3.7835 19 3 18.2165 3 17.25V6.75Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M21 7L12 13L3 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
					<h3><?php esc_html_e( 'Support', 'devdiggers-framework' ); ?></h3>
					<p><?php esc_html_e( 'Get help from our support team', 'devdiggers-framework' ); ?></p>
					<a href="<?php echo esc_url( '//devdiggers.com/contact/' ); ?>" class="ddfw-button ddfw-button-secondary">
						<?php esc_html_e( 'Contact Us', 'devdiggers-framework' ); ?>
					</a>
				</div>
				<div class="ddfw-support-card">
					<div class="ddfw-support-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
							<path d="M12 2C6.48 2 2 6.48 2 12c0 5.52 4.48 10 10 10s10-4.48 10-10c0-5.52-4.48-10-10-10zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm1-13h-2v6h6v-2h-4V7zm-1 8h2v2h-2v-2z" fill="currentColor"/>
						</svg>
					</div>
					<h3><?php esc_html_e( 'Want Extra Features?', 'devdiggers-framework' ); ?></h3>
					<p><?php esc_html_e( 'Contact us for custom development', 'devdiggers-framework' ); ?></p>
					<a href="<?php echo esc_url( '//devdiggers.com/contact/' ); ?>" target="_blank" class="ddfw-button ddfw-button-secondary">
						<?php esc_html_e( 'Hire Us', 'devdiggers-framework' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
