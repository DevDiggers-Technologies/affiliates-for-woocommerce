<?php
/**
 * DevDiggers Extensions Page Template
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
// Get website plugins
$plugins_api      = DDFW_Plugins_API::instance();
$website_plugins  = $plugins_api->get_website_plugins();
$featured_plugins = $plugins_api->get_featured_plugins();
$plugin_stats     = $plugins_api->get_plugin_statistics();

// Basic user info
$current_user = wp_get_current_user();
?>

<div class="devdiggers-wrap">
    <div class="ddfw-extensions-page ddfw-dashboard-container">
    <!-- Dashboard Header -->
    <div class="ddfw-dashboard-header">
        <div class="ddfw-admin-avatar">
            <img src="<?php echo esc_url( get_avatar_url( $current_user->ID, [ 'size' => 48 ] ) ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" class="ddfw-avatar-image" />
        </div>
        <div class="ddfw-dashboard-welcome">
            <h1>
				<?php
				/* translators: %s: Current user's display name. */
				printf( esc_html__( 'Hello, %s!', 'affiliates-for-woocommerce' ), esc_html( $current_user->display_name ) );
				?>
			</h1>
            <p><?php esc_html_e( 'Browse and explore our premium extensions for your WooCommerce store.', 'affiliates-for-woocommerce' ); ?></p>
        </div>
        <div class="ddfw-page-actions" style="margin-left: auto;">
        </div>
    </div>

    <!-- Stats Section -->
    <div class="ddfw-dashboard-stats">
        <div class="ddfw-stat-card">
            <div class="ddfw-stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="ddfw-stat-content">
                <h3><?php echo esc_html( $plugin_stats['total_plugins'] ); ?></h3>
                <p><?php esc_html_e( 'Total Extensions', 'affiliates-for-woocommerce' ); ?></p>
            </div>
        </div>

        <div class="ddfw-stat-card">
            <div class="ddfw-stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20"></path>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="ddfw-stat-content">
                <h3><?php echo esc_html( (int) gmdate( 'Y' ) - 2018 . '+' ); ?></h3>
                <p><?php esc_html_e( 'Years Experience', 'affiliates-for-woocommerce' ); ?></p>
            </div>
        </div>

        <div class="ddfw-stat-card">
            <div class="ddfw-stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
            <div class="ddfw-stat-content">
                <h3><?php esc_html_e( '500+', 'affiliates-for-woocommerce' ); ?></h3>
                <p><?php esc_html_e( '5 Star Reviews', 'affiliates-for-woocommerce' ); ?></p>
            </div>
        </div>

        <div class="ddfw-stat-card">
            <div class="ddfw-stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
            </div>
            <div class="ddfw-stat-content">
                <h3><?php esc_html_e( 'Online', 'affiliates-for-woocommerce' ); ?></h3>
                <p><?php esc_html_e( '24/7 Support', 'affiliates-for-woocommerce' ); ?></p>
            </div>
        </div>
    </div>

    <!-- Featured Extensions -->
    <?php if ( ! empty( $featured_plugins ) ): ?>
    <div class="ddfw-dashboard-section">
        <div class="ddfw-section-header">
            <h2><?php esc_html_e( 'Featured Extensions', 'affiliates-for-woocommerce' ); ?></h2>
        </div>
        <div class="ddfw-plugins-grid">
            <?php foreach ( $featured_plugins as $plugin ): ?>
                <div class="ddfw-plugin-card">
                    <div class="ddfw-plugin-image">
                        <img src="<?php echo esc_url( $plugin['image'] ); ?>" alt="<?php echo esc_attr( $plugin['name'] ); ?>" />
                    </div>
                    <div class="ddfw-plugin-content">
                        <div class="ddfw-plugin-title-section">
                            <h3><?php echo esc_html( $plugin['name'] ); ?></h3>
                            <p class="ddfw-plugin-description"><?php echo esc_html( ! empty( $plugin['one_liner'] ) ? $plugin['one_liner'] : $plugin['description'] ); ?></p>
                        </div>
                    </div>
                    <div class="ddfw-plugin-footer">
                        <div class="ddfw-plugin-actions">
                            <a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" class="ddfw-button ddfw-button-primary">
                                <?php esc_html_e( 'Buy Now', 'affiliates-for-woocommerce' ); ?>
                                <?php DDFW_SVG::get_svg_icon('external-link', false, ['size' => 14]); ?>
                            </a>
                            <?php if ( ! empty( $plugin['demo_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['demo_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Live Demo', 'affiliates-for-woocommerce' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( ! empty( $plugin['documentation_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['documentation_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Documentation', 'affiliates-for-woocommerce' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Extensions -->
    <div class="ddfw-dashboard-section">
        <div class="ddfw-section-header">
            <h2><?php esc_html_e( 'All Extensions', 'affiliates-for-woocommerce' ); ?></h2>
        </div>
        
        <div class="ddfw-plugins-grid" id="extensions-grid">
            <?php foreach ( $website_plugins as $plugin ): ?>
                <div class="ddfw-plugin-card">
                    <div class="ddfw-plugin-image">
                        <img src="<?php echo esc_url( $plugin['image'] ); ?>" alt="<?php echo esc_attr( $plugin['name'] ); ?>" />
                    </div>
                    <div class="ddfw-plugin-content">
                        <div class="ddfw-plugin-title-section">
                            <h3><?php echo esc_html( $plugin['name'] ); ?></h3>
                            <p class="ddfw-plugin-description"><?php echo esc_html( ! empty( $plugin['one_liner'] ) ? $plugin['one_liner'] : $plugin['description'] ); ?></p>
                        </div>
                    </div>
                    <div class="ddfw-plugin-footer">
                        <div class="ddfw-plugin-actions">
                            <a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" class="ddfw-button ddfw-button-primary">
                                <?php esc_html_e( 'Buy Now', 'affiliates-for-woocommerce' ); ?>
                                <?php DDFW_SVG::get_svg_icon( 'external-link', false, [ 'size' => 14 ] ); ?>
                            </a>
                            <?php if ( ! empty( $plugin['demo_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['demo_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Live Demo', 'affiliates-for-woocommerce' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( ! empty( $plugin['documentation_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['documentation_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Documentation', 'affiliates-for-woocommerce' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

</div>
