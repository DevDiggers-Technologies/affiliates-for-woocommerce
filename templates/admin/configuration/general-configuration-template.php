<?php
/**
 * General Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_General_Configuration_Template' ) ) {
	/**
	 * General Configuration template class
	 */
	class DDWCAF_General_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            if ( ! empty( $_GET[ 'settings-updated' ] ) && 'true' === sanitize_text_field( wp_unslash( $_GET[ 'settings-updated' ] ) ) ) {
                flush_rewrite_rules();
            }

            $affiliate_statuses = [
                'pending'  => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
                'approved' => esc_html__( 'Approved', 'affiliates-for-woocommerce' ),
                'rejected' => esc_html__( 'Rejected', 'affiliates-for-woocommerce' ),
                'banned'   => esc_html__( 'Banned', 'affiliates-for-woocommerce' ),
            ];

            $pages_options = [];
            $pages         = get_pages();

            if ( ! empty( $pages ) ) {
                foreach ( $pages as $page ) {
                    $pages_options[ $page->ID ] = $page->post_title;
                }
            }

            $args = [
                [
                    'header' => [
                        'heading'     => esc_html__( 'General Settings', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Manage general settings of the plugin.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                        [
                            'id'          => 'ddwcaf-enabled',
                            'label'       => esc_html__( 'Enable Affiliate System', 'affiliates-for-woocommerce' ),
                            'type'        => 'checkbox',
                            'value'       => $ddwcaf_configuration[ 'enabled' ],
                            'description' => esc_html__( 'Toggle the entire affiliate system on or off.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-default-affiliate-status',
                            'label'             => esc_html__( 'Default Affiliate Status [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => $affiliate_statuses,
                            'value'             => 'pending',
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'Select the default status assigned to the user when they register as an affiliate.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-user-roles',
                            'label'       => esc_html__( 'User Roles', 'affiliates-for-woocommerce' ),
                            'name'        => '_ddwcaf_user_roles[]',
                            'type'        => 'user_roles',
                            'value'       => $ddwcaf_configuration[ 'user_roles' ],
                            'description' => esc_html__( 'Select the user roles that are allowed to become an affiliate.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-fields-enabled-on-woocommerce-registration',
                            'label'       => esc_html__( 'WooCommerce Registration Integration', 'affiliates-for-woocommerce' ),
                            'type'        => 'checkbox',
                            'value'       => $ddwcaf_configuration[ 'fields_enabled_on_woocommerce_registration' ],
                            'description' => esc_html__( 'Display affiliate registration fields on the standard WooCommerce registration page.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-affiliate-dashboard-page-id',
                            'label'       => esc_html__( 'Affiliate Dashboard Page', 'affiliates-for-woocommerce' ),
                            'type'        => 'select',
                            'options'     => $pages_options,
                            'value'       => $ddwcaf_configuration[ 'affiliate_dashboard_page_id' ],
                            'description' => esc_html__( 'Select the page that will be used as the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-default-affiliate-dashboard-page',
                            'label'       => esc_html__( 'Default Affiliate Dashboard Link', 'affiliates-for-woocommerce' ),
                            'type'        => 'select',
                            'options'     => [
                                'my_accounts_page' => esc_html__( 'My Accounts Page', 'affiliates-for-woocommerce' ),
                                'custom_page'      => esc_html__( 'Custom Page', 'affiliates-for-woocommerce' ),
                            ],
                            'value'       => $ddwcaf_configuration[ 'default_affiliate_dashboard_page' ],
                            'description' => esc_html__( 'Select the default dashboard layout for affiliates.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-enable-widgets-affiliate-dashboard-page',
                            'label'       => esc_html__( 'Show Sidebar Widgets', 'affiliates-for-woocommerce' ),
                            'type'        => 'checkbox',
                            'value'       => $ddwcaf_configuration[ 'enable_widgets_affiliate_dashboard_page' ],
                            'description' => esc_html__( 'Enable WordPress sidebar widgets on the affiliate dashboard page.', 'affiliates-for-woocommerce' ),
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'My Account Settings', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Manage affiliate settings on the WooCommerce My Account page.', 'affiliates-for-woocommerce' ),
                    ],
                    'fields' => [
                        [
                            'id'          => 'ddwcaf-my-account-enabled',
                            'label'       => esc_html__( 'Enable My Account Menu', 'affiliates-for-woocommerce' ),
                            'type'        => 'checkbox',
                            'value'       => $ddwcaf_configuration[ 'my_account_enabled' ],
                            'description' => esc_html__( 'Add an "Affiliates" tab to the standard WooCommerce My Account menu.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-my-account-endpoint',
                            'label'             => esc_html__( 'Endpoint [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'text',
                            'value'             => $ddwcaf_configuration[ 'my_account_endpoint' ],
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'Enter the endpoint slug for the affiliate menu on the My Account page.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'                => 'ddwcaf-my-account-endpoint-title',
                            'label'             => esc_html__( 'Endpoint Title [Pro]', 'affiliates-for-woocommerce' ),
                            'type'              => 'text',
                            'value'             => $ddwcaf_configuration[ 'my_account_endpoint_title' ],
                            'class'             => 'ddfw-upgrade-to-pro-tag-wrapper',
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                            'description'       => esc_html__( 'Enter the title for the affiliate menu on the My Account page.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-enable-widgets-my-account-endpoint',
                            'label'       => esc_html__( 'Show Sidebar Widgets', 'affiliates-for-woocommerce' ),
                            'type'        => 'checkbox',
                            'value'       => $ddwcaf_configuration[ 'enable_widgets_my_account_endpoint' ],
                            'description' => esc_html__( 'Enable WordPress sidebar widgets on the My Account affiliate endpoint.', 'affiliates-for-woocommerce' ),
                        ],
                    ],
                ],
                [
                    'header' => [
                        'heading'     => esc_html__( 'Affiliate Leaderboard [Pro]', 'affiliates-for-woocommerce' ),
                        'description' => esc_html__( 'Everything the leaderboard does and how it looks. The shortcode string itself lives on the Shortcodes tab. Its stylesheet only loads on pages that actually contain the shortcode, so these settings cost nothing anywhere else.', 'affiliates-for-woocommerce' ),
                    ],
                    'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
                    'fields' => [
                        [
                            'id'                => 'ddwcaf-leaderboard-enabled',
                            'label'             => esc_html__( 'Enable Leaderboard', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Allow the leaderboard shortcode to render', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'While this is off the shortcode outputs nothing, so you can place it on a page before you are ready to show it.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-limit',
                            'label'             => esc_html__( 'Affiliates Shown', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => '10',
                            'description'       => esc_html__( 'How many affiliates the board lists by default. The shortcode limit attribute overrides this per placement.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'min' => 1, 'max' => 100, 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-anonymize',
                            'label'             => esc_html__( 'Privacy', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Mask affiliate names, showing each viewer only their own in full', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Recommended when the board is public. Affiliates still see their own rank and earnings, but nobody else\'s identity or income is exposed.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-style',
                            'label'             => esc_html__( 'Layout Style', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => [
                                'card' => esc_html__( 'Card', 'affiliates-for-woocommerce' ),
                                'list' => esc_html__( 'List', 'affiliates-for-woocommerce' ),
                                'podium' => esc_html__( 'Podium', 'affiliates-for-woocommerce' ),
                            ],
                            'value'             => 'card',
                            'description'       => esc_html__( 'Card gives each affiliate its own panel. List is a compact, hairline separated ranking that suits sidebars. Podium lifts the top three above the rest.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-rank-shape',
                            'label'             => esc_html__( 'Rank Badge Shape', 'affiliates-for-woocommerce' ),
                            'type'              => 'select',
                            'options'           => [
                                'circle' => esc_html__( 'Circle', 'affiliates-for-woocommerce' ),
                                'square' => esc_html__( 'Rounded square', 'affiliates-for-woocommerce' ),
                                'plain' => esc_html__( 'No badge', 'affiliates-for-woocommerce' ),
                            ],
                            'value'             => 'circle',
                            'description'       => esc_html__( 'The shape drawn behind each position number.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-accent-color',
                            'label'             => esc_html__( 'Accent Color', 'affiliates-for-woocommerce' ),
                            'type'              => 'colorpicker',
                            'description'       => esc_html__( 'Used for the leading affiliate and for the row belonging to whoever is viewing. Leave it empty to follow the brand primary color from the Layout tab.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-highlight-top-three',
                            'label'             => esc_html__( 'Medal Colors', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Tint the top three rank badges gold, silver and bronze', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Turn this off for a flat ranking where every position looks the same.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-show-avatar',
                            'label'             => esc_html__( 'Avatars', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Show the affiliate avatar', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Avatars come from Gravatar, so they load from an external service. Turn them off if you would rather not make that request.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-show-orders',
                            'label'             => esc_html__( 'Order Count', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Show how many orders each affiliate referred', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'The order count shows the work behind the earnings, so a small affiliate with steady sales does not look idle next to one big order. Hide it if you would rather keep your sales volume private.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                        [
                            'id'                => 'ddwcaf-leaderboard-show-amount',
                            'label'             => esc_html__( 'Earned Amount', 'affiliates-for-woocommerce' ),
                            'type'              => 'checkbox',
                            'checkbox_label'    => esc_html__( 'Show the total commission each affiliate earned', 'affiliates-for-woocommerce' ),
                            'description'       => esc_html__( 'Hide this if you would rather not publish what your affiliates make.', 'affiliates-for-woocommerce' ),
                            'custom_attributes' => [ 'disabled' => 'disabled' ],
                        ],
                    ],
                ],
            ];

            $layout = new DDFW_Layout();
            $layout->get_form_section_layout( $args, 'ddwcaf-general-configuration-fields' );
		}
	}
}
