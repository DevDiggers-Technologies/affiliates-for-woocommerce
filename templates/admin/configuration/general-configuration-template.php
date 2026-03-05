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
            ];

            $layout = new DDFW_Layout();
            $layout->get_form_section_layout( $args, 'ddwcaf-general-configuration-fields' );
		}
	}
}
