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
            <h1><?php printf( esc_html__( 'Hello, %s!', 'devdiggers-framework' ), esc_html( $current_user->display_name ) ); ?></h1>
            <p><?php esc_html_e( 'Browse and explore our premium extensions for your WooCommerce store.', 'devdiggers-framework' ); ?></p>
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
                <p><?php esc_html_e( 'Total Extensions', 'devdiggers-framework' ); ?></p>
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
                <h3><?php echo esc_html( (int) date( 'Y' ) - 2018 . '+' ); ?></h3>
                <p><?php esc_html_e( 'Years Experience', 'devdiggers-framework' ); ?></p>
            </div>
        </div>

        <a href="https://www.trustpilot.com/review/devdiggers.com" target="_blank" class="ddfw-stat-card ddfw-stat-card-link">
            <div class="ddfw-stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
            <div class="ddfw-stat-content">
                <h3><?php esc_html_e( 'Excellent', 'devdiggers-framework' ); ?></h3>
                <p style="margin-top: 4px;line-height: 0;">
                    <svg width="85" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1133 279"><path fill-rule="evenodd" d="M412.4 98.6V120h-45.1v120.3h-24.8V120h-44.9V98.6zm16.3 39.1v19.8h.4q1-4.2 3.9-8.1c1.9-2.6 4.2-5.1 6.9-7.2 2.7-2.2 5.7-3.9 9-5.3 3.3-1.3 6.7-2 10.1-2 2.6 0 4.5.1 5.5.2s2 .3 3.1.4v21.8c-1.6-.3-3.2-.5-4.9-.7-1.7-.2-3.3-.3-4.9-.3-3.8 0-7.4.8-10.8 2.3-3.4 1.5-6.3 3.8-8.8 6.7-2.5 3-4.5 6.6-6 11s-2.2 9.4-2.2 15.1v48.8h-22.6V137.7zm142.8 102.6h-22.2V226h-.4c-2.8 5.2-6.9 9.3-12.4 12.4-5.5 3.1-11.1 4.7-16.8 4.7-13.5 0-23.3-3.3-29.3-10q-9-10-9-30.3v-65.1H504v62.9c0 9 1.7 15.4 5.2 19.1 3.4 3.7 8.3 5.6 14.5 5.6 4.8 0 8.7-.7 11.9-2.2 3.2-1.5 5.8-3.4 7.7-5.9 2-2.4 3.4-5.4 4.3-8.8.9-3.4 1.3-7.1 1.3-11.1v-59.5h22.6zm38.5-32.9c.7 6.6 3.2 11.2 7.5 13.9 4.4 2.6 9.6 4 15.7 4 2.1 0 4.5-.2 7.2-.5s5.3-1 7.6-1.9c2.4-.9 4.3-2.3 5.9-4.1 1.5-1.8 2.2-4.1 2.1-7-.1-2.9-1.2-5.3-3.2-7.1-2-1.9-4.5-3.3-7.6-4.5-3.1-1.1-6.6-2.1-10.6-2.9-4-.8-8-1.7-12.1-2.6-4.2-.9-8.3-2.1-12.2-3.4q-5.9-2-10.5-5.4c-3.1-2.2-5.6-5.1-7.4-8.6-1.9-3.5-2.8-7.8-2.8-13 0-5.6 1.4-10.2 4.1-14 2.7-3.8 6.2-6.8 10.3-9.1 4.2-2.3 8.8-3.9 13.9-4.9 5.1-.9 10-1.4 14.6-1.4 5.3 0 10.4.6 15.2 1.7 4.8 1.1 9.2 2.9 13.1 5.5 3.9 2.5 7.1 5.8 9.7 9.8 2.6 4 4.2 8.9 4.9 14.6h-23.6c-1.1-5.4-3.5-9.1-7.4-10.9-3.9-1.9-8.4-2.8-13.4-2.8-1.6 0-3.5.1-5.7.4-2.2.3-4.2.8-6.2 1.5-1.9.7-3.5 1.8-4.9 3.2-1.3 1.4-2 3.2-2 5.5 0 2.8 1 5 2.9 6.7 1.9 1.7 4.4 3.1 7.5 4.3 3.1 1.1 6.6 2.1 10.6 2.9 4 .8 8.1 1.7 12.3 2.6 4.1.9 8.1 2.1 12.1 3.4 4 1.3 7.5 3.1 10.6 5.4 3.1 2.3 5.6 5.1 7.5 8.5 1.9 3.4 2.9 7.7 2.9 12.7 0 6.1-1.4 11.2-4.2 15.5-2.8 4.2-6.4 7.7-10.8 10.3-4.4 2.6-9.4 4.6-14.8 5.8-5.4 1.2-10.8 1.8-16.1 1.8-6.5 0-12.5-.7-18-2.2-5.5-1.5-10.3-3.7-14.3-6.6-4-3-7.2-6.7-9.5-11.1-2.3-4.4-3.5-9.7-3.7-15.8H610zm91.7-69.7v-30.8h22.6v30.8h20.4v16.9h-20.4v54.8c0 2.4.1 4.4.3 6.2.2 1.7.7 3.2 1.4 4.4q1 1.8 3.3 2.7c1.5.6 3.4.9 6 .9 1.6 0 3.2 0 4.8-.1 1.6-.1 3.2-.3 4.8-.7v17.5c-2.5.3-5 .5-7.3.8-2.4.3-4.8.4-7.3.4-6 0-10.8-.6-14.4-1.7-3.6-1.1-6.5-2.8-8.5-5-2.1-2.2-3.4-4.9-4.2-8.2-.7-3.3-1.2-7.1-1.3-11.3v-60.5h-17.1v-17.1zm59 0h21.4v13.9h.4c3.2-6 7.6-10.2 13.3-12.8 5.7-2.6 11.8-3.9 18.5-3.9 8.1 0 15.1 1.4 21.1 4.3q9 4.2 15 11.7c4 5 6.9 10.8 8.9 17.4 2 6.6 3 13.7 3 21.2 0 6.9-.9 13.6-2.7 20-1.8 6.5-4.5 12.2-8.1 17.2-3.6 5-8.2 8.9-13.8 11.9-5.6 3-12.1 4.5-19.7 4.5q-5 0-9.9-.9c-3.3-.6-6.5-1.6-9.5-2.9-3-1.3-5.9-3-8.4-5.1-2.6-2.1-4.7-4.5-6.5-7.2h-.4v51.2h-22.6zm79 51.4q0-6.9-1.8-13.5c-1.2-4.4-3-8.2-5.4-11.6-2.4-3.4-5.4-6.1-8.9-8.1-3.6-2-7.7-3.1-12.3-3.1-9.5 0-16.7 3.3-21.5 9.9q-7.2 9.9-7.2 26.4c0 5.2.6 10 1.9 14.4 1.3 4.4 3.1 8.2 5.7 11.4q3.7 4.8 9 7.5c3.5 1.9 7.6 2.8 12.2 2.8 5.2 0 9.5-1.1 13.1-3.2 3.6-2.1 6.5-4.9 8.8-8.2 2.3-3.4 4-7.2 5-11.5.9-4.3 1.4-8.7 1.4-13.2zm39.9-90.5h22.6V120h-22.6zm0 39.1h22.6v102.6h-22.6zm42.8-39.1H945v141.7h-22.6zm91.9 144.5c-8.2 0-15.5-1.4-21.9-4.1-6.4-2.7-11.8-6.5-16.3-11.2-4.4-4.8-7.8-10.5-10.1-17.1-2.3-6.6-3.5-13.9-3.5-21.8 0-7.8 1.2-15 3.5-21.6 2.3-6.6 5.7-12.3 10.1-17.1 4.4-4.8 9.9-8.5 16.3-11.2 6.4-2.7 13.7-4.1 21.9-4.1s15.5 1.4 21.9 4.1c6.4 2.7 11.8 6.5 16.3 11.2 4.4 4.8 7.8 10.5 10.1 17.1 2.3 6.6 3.5 13.8 3.5 21.6 0 7.9-1.2 15.2-3.5 21.8-2.3 6.6-5.7 12.3-10.1 17.1-4.4 4.8-9.9 8.5-16.3 11.2-6.4 2.7-13.7 4.1-21.9 4.1zm0-17.9c5 0 9.4-1.1 13.1-3.2 3.7-2.1 6.7-4.9 9.1-8.3 2.4-3.4 4.1-7.3 5.3-11.6 1.1-4.3 1.7-8.7 1.7-13.2 0-4.4-.6-8.7-1.7-13.1s-2.9-8.2-5.3-11.6c-2.4-3.4-5.4-6.1-9.1-8.2-3.7-2.1-8.1-3.2-13.1-3.2s-9.4 1.1-13.1 3.2c-3.7 2.1-6.7 4.9-9.1 8.2-2.4 3.4-4.1 7.2-5.3 11.6-1.1 4.4-1.7 8.7-1.7 13.1 0 4.5.6 8.9 1.7 13.2 1.1 4.3 2.9 8.2 5.3 11.6 2.4 3.4 5.4 6.2 9.1 8.3 3.7 2.2 8.1 3.2 13.1 3.2zm75.5-87.5v-30.8h22.6v30.8h20.4v16.9h-20.4v54.8c0 2.4.1 4.4.3 6.2.2 1.7.7 3.2 1.4 4.4q1 1.8 3.3 2.7c1.5.6 3.4.9 6 .9 1.6 0 3.2 0 4.8-.1 1.6-.1 3.2-.3 4.8-.7v17.5c-2.5.3-5 .5-7.3.8-2.4.3-4.8.4-7.3.4-6 0-10.8-.6-14.4-1.7-3.6-1.1-6.5-2.8-8.5-5-2.1-2.2-3.4-4.9-4.2-8.2-.7-3.3-1.2-7.1-1.3-11.3v-60.5h-17.1v-17.1z" style="fill:#231c4c"></path><path d="M271.3 98.6H167.7L135.7 0l-32.1 98.6L0 98.5l83.9 61L51.8 258l83.9-60.9 83.8 60.9-32-98.5z" style="fill:#00b67a"></path><path d="m194.7 181.8-7.2-22.3-51.8 37.6z" style="fill:#005128"></path></svg>
                </p>
            </div>
        </a>

        <div class="ddfw-stat-card">
            <div class="ddfw-stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
            </div>
            <div class="ddfw-stat-content">
                <h3><?php esc_html_e( 'Online', 'devdiggers-framework' ); ?></h3>
                <p><?php esc_html_e( '24/7 Support', 'devdiggers-framework' ); ?></p>
            </div>
        </div>
    </div>

    <!-- Featured Extensions -->
    <?php if ( ! empty( $featured_plugins ) ): ?>
    <div class="ddfw-dashboard-section">
        <div class="ddfw-section-header">
            <h2><?php esc_html_e( 'Featured Extensions', 'devdiggers-framework' ); ?></h2>
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
                            <span class="ddfw-plugin-price"><?php echo wp_kses_post( $plugin['price'] ); ?></span>
                        </div>
                    </div>
                    <div class="ddfw-plugin-footer">
                        <div class="ddfw-plugin-actions">
                            <a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" class="ddfw-button ddfw-button-primary">
                                <?php esc_html_e( 'Buy Now', 'devdiggers-framework' ); ?>
                                <?php DDFW_SVG::get_svg_icon('external-link', false, ['size' => 14]); ?>
                            </a>
                            <?php if ( ! empty( $plugin['demo_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['demo_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Live Demo', 'devdiggers-framework' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( ! empty( $plugin['documentation_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['documentation_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Documentation', 'devdiggers-framework' ); ?>
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
            <h2><?php esc_html_e( 'All Extensions', 'devdiggers-framework' ); ?></h2>
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
                            <span class="ddfw-plugin-price"><?php echo wp_kses_post( $plugin['price'] ); ?></span>
                        </div>
                    </div>
                    <div class="ddfw-plugin-footer">
                        <div class="ddfw-plugin-actions">
                            <a href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" class="ddfw-button ddfw-button-primary">
                                <?php esc_html_e( 'Buy Now', 'devdiggers-framework' ); ?>
                                <?php DDFW_SVG::get_svg_icon( 'external-link', false, [ 'size' => 14 ] ); ?>
                            </a>
                            <?php if ( ! empty( $plugin['demo_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['demo_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Live Demo', 'devdiggers-framework' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( ! empty( $plugin['documentation_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $plugin['documentation_url'] ); ?>" class="ddfw-button ddfw-button-secondary" target="_blank">
                                    <?php esc_html_e( 'Documentation', 'devdiggers-framework' ); ?>
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
