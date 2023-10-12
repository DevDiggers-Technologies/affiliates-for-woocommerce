<?php
/**
 * General Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

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

            global $wp_roles;
            $all_roles = $wp_roles->roles;

            $affiliate_statuses = [
                'pending'  => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
                'approved' => esc_html__( 'Approved', 'affiliates-for-woocommerce' ),
                'rejected' => esc_html__( 'Rejected', 'affiliates-for-woocommerce' ),
                'banned'   => esc_html__( 'Banned', 'affiliates-for-woocommerce' ),
            ];

            ?>
            <div class="wrap">
                <div class="notice notice-info">
                    <p>
                        <i>
                            <?php
                            /* translators: %s for a tag */
                            echo sprintf( esc_html__( 'If you really like our plugin, please leave us a %s rating, we\'ll really appreciate it.', 'affiliates-for-woocommerce' ), '<a href="//wordpress.org/support/plugin/woocommerce-affiliates/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>' );
                            ?>
                        </i>
                    </p>
                </div>
				<hr class="wp-header-end" />
                <?php settings_errors(); ?>
                <div class="ddwcaf-configuration-container ddwcaf-padding-top-bottom-0 ddwcaf-width-unset">
                    <form action="options.php" method="POST">
                        <?php settings_fields( 'ddwcaf-general-configuration-fields' ); ?>
                        <h2><?php esc_html_e( 'General', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Enable Affiliates for WooCommerce', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the module functionalities for the users.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows the module functionality to be used on the frontend.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-enabled',
                                    'value'          => $ddwcaf_configuration[ 'enabled' ],
                                ] );
                                ddwcaf_form_field( [
                                    'type'        => 'select',
                                    'label'       => esc_html__( 'Default Affiliate Status [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the default status used for all affiliates once they become affiliate.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'This affiliate status will get assigned to both new registered affiliates and converted affiliates.', 'affiliates-for-woocommerce' ),
                                    'options'     => $affiliate_statuses,
                                    'id'          => 'ddwcaf-default-affiliate-status',
                                    'value'       => $ddwcaf_configuration[ 'default_affiliate_status' ],
                                    'input_class' => [ 'ddwcaf-select2' ],
                                ] );
                                ?>
                                <tr valign="top">
                                    <th>
                                        <label for="ddwcaf-user-roles"><?php esc_html_e( 'User Roles', 'affiliates-for-woocommerce' ); ?></label>
                                    </th>
                                    <td>
                                        <?php echo wc_help_tip( esc_html__( 'Selected user roles will be able to apply for the affiliate functionality.', 'affiliates-for-woocommerce' ) ); ?>

                                        <select id="ddwcaf-user-roles" class="regular-text ddwcaf-select2" name="_ddwcaf_user_roles[]" multiple data-placeholder="<?php esc_attr_e( 'Search by role', 'affiliates-for-woocommerce' ); ?>">
                                            <?php
                                            if ( ! empty( $all_roles ) ) {
                                                foreach ( $all_roles as $key => $role_data ) {
                                                    if ( ! in_array( $key, [ 'ddwcaf_affiliate' ], true ) ) {
                                                        if ( in_array( $key, $ddwcaf_configuration[ 'user_roles' ], true ) ) {
                                                            ?>
                                                            <option value="<?php echo esc_attr( $key ); ?>" selected="selected"><?php echo esc_html( $role_data[ 'name' ] ); ?></option>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $role_data[ 'name' ] ); ?></option>
                                                            <?php
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>

                                        <p class="description ddwcaf-margin-left-20"><i><?php esc_html_e( 'Leave empty if you don\'t want to provide functionalities to any other user roles to convert them into affiliates.', 'affiliates-for-woocommerce' ); ?></i></p>
                                    </td>
                                </tr>
                                <?php
                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Enable Affiliate Registration Fields on WooCommerce Registration Form', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the affiliate registration fields to be appear on the WooCommerce registration form.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'You can disable this option if you want the affiliate registration to be done only from the affiliate dashboard page.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-fields-enabled-on-woocommerce-registration',
                                    'value'          => $ddwcaf_configuration[ 'fields_enabled_on_woocommerce_registration' ],
                                ] );
                                ?>
                                <tr valign="top">
                                    <th>
                                        <label for="ddwcaf-affiliate-dashboard-page-id"><?php esc_html_e( 'Affiliate Dashboard Page', 'affiliates-for-woocommerce' ); ?></label>
                                    </th>
                                    <td>
                                        <?php echo wc_help_tip( esc_html__( 'This is used for the default affiliate dashboard page where you must have placed the affiliate dashboard shortcode manually.', 'affiliates-for-woocommerce' ) ); ?>

                                        <?php
                                        wp_dropdown_pages( [
                                            'name'     => '_ddwcaf_affiliate_dashboard_page_id',
                                            'selected' => $ddwcaf_configuration[ 'affiliate_dashboard_page_id' ],
                                            'id'       => 'ddwcaf-affiliate-dashboard-page-id',
                                            'class'    => 'ddwcaf-select2',
                                        ] );
                                        ?>

                                        <p class="description ddwcaf-margin-left-20"><i><?php esc_html_e( 'An affiliate dashboard page is already created via plugin and is pre-selected here.', 'affiliates-for-woocommerce' ); ?></i></p>
                                    </td>
                                </tr>
                                <?php
                                ddwcaf_form_field( [
                                    'type'        => 'color',
                                    'label'       => esc_html__( 'Primary Color', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the primary color used for the affiliate dashboard styling.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'This color gets used in the styling of some components on the affiliate dashboard page.', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-primary-color',
                                    'value'       => $ddwcaf_configuration[ 'primary_color' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Enable Sidebar Widgets', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable sidebar widgets on the affiliate dashboard page.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows the widgets to be shown on the sidebar on the affiliate dashboard page.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-enable-widgets-affiliate-dashboard-page',
                                    'value'          => $ddwcaf_configuration[ 'enable_widgets_affiliate_dashboard_page' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'select',
                                    'label'       => esc_html__( 'Default Affiliate Dashboard', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'Select the default affiliate dashboard.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Select the default affiliate dashboard url either as affiliate dashbaord page having the affiliate dashboard shortcode or the my account page menu.', 'affiliates-for-woocommerce' ),
                                    'options'     => [
                                        'custom_page'     => esc_html__( 'Affiliate Dashboard Page', 'affiliates-for-woocommerce' ),
                                        'my_account_page' => esc_html__( 'My Account Page', 'affiliates-for-woocommerce' ),
                                    ],
                                    'id'          => 'ddwcaf-default-affiliate-dashboard-page',
                                    'value'       => $ddwcaf_configuration[ 'default_affiliate_dashboard_page' ],
                                    'input_class' => [ 'ddwcaf-select2' ],
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <h2><?php esc_html_e( 'My Account Menu', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Enable Affiliates Menu on the My Account Page', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the affiliates menu on the my account page for the allowed users so they can use the affiliate functionality.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'Disable this if you want the affiliate dashboard functionality to be used via Affiliate Dashboard page only.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-my-account-enabled',
                                    'value'          => $ddwcaf_configuration[ 'my_account_enabled' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Endpoint [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the endpoint used for the my account page menu which shows the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Default: affiliate-dashboard', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-my-account-endpoint',
                                    'value'       => $ddwcaf_configuration[ 'my_account_endpoint' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Endpoint Title [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the endpoint title used for the my account page menu which shows the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Default: Affiliate Dashboard', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-my-account-endpoint-title',
                                    'value'       => $ddwcaf_configuration[ 'my_account_endpoint_title' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Enable Sidebar Widgets', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable sidebar widgets on the affiliate dashboard menu on my accounts page.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows the widgets to be shown on the sidebar on the affiliate dashboard menu.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-enable-widgets-my-account-endpoint',
                                    'value'          => $ddwcaf_configuration[ 'enable_widgets_my_account_endpoint' ],
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <?php submit_button( esc_html__( 'Save Changes', 'affiliates-for-woocommerce' ) ); ?>
                    </form>
                </div>
            </div>
            <?php
        }
	}
}
