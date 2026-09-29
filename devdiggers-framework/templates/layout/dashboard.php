<?php
/**
 * Dashboard layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
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

$total_active    = count( $active_devdiggers_plugins );
$total_inactive  = count( $inactive_devdiggers_plugins );
$total_installed = $total_active + $total_inactive;
$current_user    = wp_get_current_user();
$is_subscribed   = get_option( 'ddfw_newsletter_subscribed' );

$stats = [
	[
		'label' => esc_html__( 'Installed Plugins', 'affiliates-for-woocommerce' ),
		'value' => $total_installed,
		'icon'  => '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
	],
	[
		'label' => esc_html__( 'Active Plugins', 'affiliates-for-woocommerce' ),
		'value' => $total_active,
		'icon'  => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
	],
	[
		'label' => esc_html__( 'Inactive Plugins', 'affiliates-for-woocommerce' ),
		'value' => $total_inactive,
		'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M8 12h8"/>',
	],
	[
		'label' => esc_html__( 'Activation Rate', 'affiliates-for-woocommerce' ),
		'value' => ( $total_installed > 0 ? round( ( $total_active / $total_installed ) * 100 ) : 0 ) . '%',
		'icon'  => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
	],
];

$system_info = [
	esc_html__( 'WordPress', 'affiliates-for-woocommerce' )          => get_bloginfo( 'version' ),
	esc_html__( 'WooCommerce', 'affiliates-for-woocommerce' )        => defined( 'WC_VERSION' ) ? WC_VERSION : esc_html__( 'Not active', 'affiliates-for-woocommerce' ),
	esc_html__( 'PHP', 'affiliates-for-woocommerce' )                => PHP_VERSION,
	esc_html__( 'Memory Limit', 'affiliates-for-woocommerce' )       => ini_get( 'memory_limit' ),
	esc_html__( 'Max Execution Time', 'affiliates-for-woocommerce' ) => ini_get( 'max_execution_time' ) . 's',
];

$resources = [
	[
		'title' => esc_html__( 'Documentation', 'affiliates-for-woocommerce' ),
		'text'  => esc_html__( 'Guides and tutorials for every plugin', 'affiliates-for-woocommerce' ),
		'url'   => 'https://docs.devdiggers.com/',
		'icon'  => '<path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/>',
	],
	[
		'title' => esc_html__( 'Support', 'affiliates-for-woocommerce' ),
		'text'  => esc_html__( 'Get help from our support team', 'affiliates-for-woocommerce' ),
		'url'   => 'https://devdiggers.com/contact/',
		'icon'  => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.6 8.6 0 0 1-3.8-.9L3 21l1.9-5.2a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 8.5-8.5h.5a8.5 8.5 0 0 1 8 8z"/>',
	],
	[
		'title' => esc_html__( 'Custom Development', 'affiliates-for-woocommerce' ),
		'text'  => esc_html__( 'Need extra features? Hire us', 'affiliates-for-woocommerce' ),
		'url'   => 'https://devdiggers.com/contact/',
		'icon'  => '<path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/>',
	],
];

$svg_open = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
$svg_tags = ddfw_kses_allowed_svg_tags();
?>
<div class="wrap devdiggers-wrap ddfw-hub">
	<?php include DDFW_FILE . 'templates/layout/devdiggers-header.php'; ?>

	<div class="ddfw-dashboard-container">
		<div class="ddfw-dashboard-header">
			<div class="ddfw-admin-avatar">
				<img src="<?php echo esc_url( get_avatar_url( $current_user->ID, [ 'size' => 96 ] ) ); ?>" alt="" class="ddfw-avatar-image" />
			</div>
			<div class="ddfw-dashboard-welcome">
				<h1>
					<?php
					/* translators: %s: current user display name */
					printf( esc_html__( 'Welcome back, %s', 'affiliates-for-woocommerce' ), esc_html( $current_user->display_name ) );
					?>
				</h1>
				<p><?php esc_html_e( 'Manage all your DevDiggers plugins from one place.', 'affiliates-for-woocommerce' ); ?></p>
			</div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=devdiggers-extensions' ) ); ?>" class="ddfw-button ddfw-button-secondary ddfw-dashboard-header-action">
				<?php esc_html_e( 'Browse Extensions', 'affiliates-for-woocommerce' ); ?>
			</a>
		</div>

		<div class="ddfw-dashboard-stats">
			<?php foreach ( $stats as $stat ) : ?>
				<div class="ddfw-stat-card">
					<div class="ddfw-stat-icon"><?php echo wp_kses( $svg_open . $stat['icon'] . '</svg>', $svg_tags ); ?></div>
					<div class="ddfw-stat-content">
						<p><?php echo esc_html( $stat['label'] ); ?></p>
						<h3><?php echo esc_html( $stat['value'] ); ?></h3>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ddfw-dashboard-section">
			<div class="ddfw-section-header">
				<h2><?php esc_html_e( 'Active Plugins', 'affiliates-for-woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Open a plugin to manage its settings and data.', 'affiliates-for-woocommerce' ); ?></p>
			</div>

			<?php if ( empty( $active_devdiggers_plugins ) ) : ?>
				<p class="ddfw-hub-empty"><?php esc_html_e( 'No DevDiggers plugins are active yet.', 'affiliates-for-woocommerce' ); ?></p>
			<?php else : ?>
				<div class="ddfw-plugins-grid">
					<?php foreach ( $active_devdiggers_plugins as $plugin_file => $plugin_data ) : ?>
						<?php
						$plugin_prefix = $plugin_data['DevDiggersPrefix'] ?? '';
						// Only link plugins that register a dashboard page.
						$admin_url = $plugin_prefix ? menu_page_url( $plugin_prefix . '-dashboard', false ) : '';
						?>
						<div class="ddfw-plugin-card">
							<div class="ddfw-plugin-header">
								<div class="ddfw-plugin-icon">
									<?php echo wp_kses( $svg_open . '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></svg>', $svg_tags ); ?>
								</div>
								<span class="ddfw-status-badge ddfw-status-active"><?php esc_html_e( 'Active', 'affiliates-for-woocommerce' ); ?></span>
							</div>
							<div class="ddfw-plugin-content">
								<h3><?php echo esc_html( $plugin_data['Name'] ); ?></h3>
								<?php if ( ! empty( $plugin_data['Description'] ) ) : ?>
									<p class="ddfw-plugin-description"><?php echo esc_html( wp_strip_all_tags( $plugin_data['Description'] ) ); ?></p>
								<?php endif; ?>
							</div>
							<div class="ddfw-plugin-footer">
								<span class="ddfw-plugin-version">v<?php echo esc_html( $plugin_data['Version'] ); ?></span>
								<?php if ( $admin_url ) : ?>
									<a href="<?php echo esc_url( $admin_url ); ?>" class="ddfw-plugin-link">
										<?php esc_html_e( 'Open', 'affiliates-for-woocommerce' ); ?>
										<span aria-hidden="true">&rarr;</span>
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $inactive_devdiggers_plugins ) ) : ?>
				<details class="ddfw-inactive-plugins">
					<summary>
						<?php
						/* translators: %d: number of inactive plugins */
						printf( esc_html__( 'Inactive plugins (%d)', 'affiliates-for-woocommerce' ), (int) $total_inactive );
						?>
					</summary>
					<ul class="ddfw-inactive-list">
						<?php foreach ( $inactive_devdiggers_plugins as $plugin_file => $plugin_data ) : ?>
							<li>
								<span class="ddfw-inactive-name"><?php echo esc_html( $plugin_data['Name'] ); ?></span>
								<span class="ddfw-plugin-version">v<?php echo esc_html( $plugin_data['Version'] ); ?></span>
								<a href="<?php echo esc_url( admin_url( 'plugins.php?plugin_status=inactive' ) ); ?>" class="ddfw-plugin-link"><?php esc_html_e( 'Activate', 'affiliates-for-woocommerce' ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</details>
			<?php endif; ?>
		</div>

		<?php if ( ! $is_subscribed ) : ?>
			<div class="ddfw-dashboard-section ddfw-newsletter-section">
				<div class="ddfw-newsletter-text">
					<h2><?php esc_html_e( 'Stay updated with DevDiggers', 'affiliates-for-woocommerce' ); ?></h2>
					<p><?php esc_html_e( 'Product updates, WooCommerce tips and subscriber-only offers. No spam.', 'affiliates-for-woocommerce' ); ?></p>
				</div>
				<form class="ddfw-newsletter-form" method="post">
					<div class="ddfw-form-row">
						<input type="email" name="email" id="ddfw-newsletter-email" placeholder="<?php esc_attr_e( 'you@example.com', 'affiliates-for-woocommerce' ); ?>" aria-label="<?php esc_attr_e( 'Email address', 'affiliates-for-woocommerce' ); ?>" required />
						<button type="submit" class="ddfw-button ddfw-button-primary" id="ddfw-newsletter-submit"><?php esc_html_e( 'Subscribe', 'affiliates-for-woocommerce' ); ?></button>
					</div>
					<div id="ddfw-newsletter-message" class="ddfw-newsletter-message" role="status"></div>
				</form>
			</div>
		<?php endif; ?>

		<div class="ddfw-hub-columns">
			<div class="ddfw-dashboard-section">
				<div class="ddfw-section-header">
					<h2><?php esc_html_e( 'System Information', 'affiliates-for-woocommerce' ); ?></h2>
				</div>
				<dl class="ddfw-info-list">
					<?php foreach ( $system_info as $label => $value ) : ?>
						<div>
							<dt><?php echo esc_html( $label ); ?></dt>
							<dd><?php echo esc_html( $value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>

			<div class="ddfw-dashboard-section">
				<div class="ddfw-section-header">
					<h2><?php esc_html_e( 'Help & Resources', 'affiliates-for-woocommerce' ); ?></h2>
				</div>
				<ul class="ddfw-resource-list">
					<?php foreach ( $resources as $resource ) : ?>
						<li>
							<a href="<?php echo esc_url( $resource['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="ddfw-stat-icon"><?php echo wp_kses( $svg_open . $resource['icon'] . '</svg>', $svg_tags ); ?></span>
								<span class="ddfw-resource-text">
									<strong><?php echo esc_html( $resource['title'] ); ?></strong>
									<span><?php echo esc_html( $resource['text'] ); ?></span>
								</span>
								<span class="ddfw-resource-arrow" aria-hidden="true">&rarr;</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
