<?php
/**
 * Referrals Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Referrals_Configuration_Template' ) ) {
	/**
	 * Referrals Configuration template class
	 */
	class DDWCAF_Referrals_Configuration_Template {
		/**
		 * Construct
         * 
         * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
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
                        <?php settings_fields( 'ddwcaf-referrals-configuration-fields' ); ?>
                        <h2><?php esc_html_e( 'General', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Query Variable Name', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This query variable name is used to store referral tokens in the URL.', 'affiliates-for-woocommerce' ),
                                    'placeholder' => esc_html__( 'Default: ref', 'affiliates-for-woocommerce' ),
                                    'description'    => sprintf( esc_html__( '%s/%s={%s}.', 'affiliates-for-woocommerce' ), site_url(), $ddwcaf_configuration[ 'query_variable_name' ], $ddwcaf_configuration[ 'default_referral_token' ] ),
                                    'id'          => 'ddwcaf-query-variable-name',
                                    'value'       => $ddwcaf_configuration[ 'query_variable_name' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'select',
                                    'label'       => esc_html__( 'Default Referral Token [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the default referral token used in the query variable for affiliates to earn commissions.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'This will be the default referral token for all affiliates.', 'affiliates-for-woocommerce' ),
                                    'options'     => [
                                        'user_id'  => esc_html__( 'User ID', 'affiliates-for-woocommerce' ),
                                        'username' => esc_html__( 'Username', 'affiliates-for-woocommerce' ),
                                    ],
                                    'id'          => 'ddwcaf-default-referral-token',
                                    'value'       => $ddwcaf_configuration[ 'default_referral_token' ],
                                    'input_class' => [ 'ddwcaf-select2' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable [Pro]', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Allow Affiliates to Change their Referral Token [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the referral token change functionality from the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                                    'description'    => sprintf( esc_html__( 'This allows "friendly" looking links - because affiliates can use their brand name instead of {%s}.', 'affiliates-for-woocommerce' ), $ddwcaf_configuration[ 'default_referral_token' ] ),
                                    'id'             => 'ddwcaf-referral-token-change-allowed',
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <h2><?php esc_html_e( 'Cookies', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Referral Cookie Name', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This cookie name used to store referral tokens.', 'affiliates-for-woocommerce' ),
                                    'placeholder' => esc_html__( 'Default: ddwcaf_referral_token', 'affiliates-for-woocommerce' ),
                                    'description'    => sprintf( esc_html__( 'This name should be as unique as possible to avoid conflict with other plugins. %s: if you change this setting, all cookies created previously will no longer be valid.', 'affiliates-for-woocommerce' ), '<br /><strong>' . esc_html__( 'Warning', 'affiliates-for-woocommerce' ) . '</strong>' ),
                                    'id'          => 'ddwcaf-referral-cookie-name',
                                    'value'       => $ddwcaf_configuration[ 'referral_cookie_name' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Referral Cookie Expires After (in days)', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'After the entered time, referral cookie gets expired.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Leave the field empty for no expiration.', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-referral-cookie-expires',
                                    'value'       => $ddwcaf_configuration[ 'referral_cookie_expires' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable [Pro]', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Change referral cookie if another referral link is visited [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the referral token change functionality in the referral cookie if another referral link gets visited.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows to overrides the new referral token in the cookie if user accesses the site using another referral link.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-referral-cookie-change-allowed',
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable [Pro]', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Delete the Cookies after Checkout [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the functionality to delete the referral cookie from the browser once the user places the order.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows to delete the referral cookie once the customer places the order. Disable this option to allow the same affiliate to earn commission for all future orders as well if there isn\'t any new affiliate refers.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-referral-cookie-checkout-delete-allowed',
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <h2><?php esc_html_e( 'Coupons [Pro]', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable [Pro]', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Assign Coupons to Affiliates [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the functionality to assign the coupons to affiliate on creating or modifying it.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows affiliates to promote your site via coupons so each order that contains the coupon will generate a commission for the affiliate.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-assign-coupons-enabled',
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'select',
                                    'label'       => esc_html__( 'Display Coupon Section on Affiliate Dashboard [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'Select the appropriate option to whom you want to show the coupon section.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Select whether to display the "Coupons" section to all affiliates or only to those affiliates that have any coupon assigned to them.', 'affiliates-for-woocommerce' ),
                                    'options'     => [
                                        'all'  => esc_html__( 'To all affiliates', 'affiliates-for-woocommerce' ),
                                        'some' => esc_html__( 'Only to those affiliates that have any coupon assigned to them', 'affiliates-for-woocommerce' ),
                                    ],
                                    'id'          => 'ddwcaf-display-coupons-section',
                                    'input_class' => [ 'ddwcaf-select2' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable [Pro]', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Send an email to affiliate when a coupon gets assigned [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the send email functionality to affiliates when a coupon gets assigned.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'Enable to notify affiliates when any coupon gets assigned to their account.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-assigned-coupon-email-enabled',
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <h2><?php esc_html_e( 'Visits', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'           => 'checkbox',
                                    'label'          => esc_html__( 'Enable/Disable', 'affiliates-for-woocommerce' ),
                                    'checkbox_label' => esc_html__( 'Register Visits', 'affiliates-for-woocommerce' ),
                                    'help_tip'       => esc_html__( 'Enable/Disable the register visits functionality.', 'affiliates-for-woocommerce' ),
                                    'description'    => esc_html__( 'This allows registering the visits of the affiliate referred URLs by users with their IP addresses in your database.', 'affiliates-for-woocommerce' ),
                                    'id'             => 'ddwcaf-register-visits-enabled',
                                    'value'          => $ddwcaf_configuration[ 'register_visits_enabled' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Re-register the same visit after (in seconds) [Pro]', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'After the entered time, same visit get registered again.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'After the above entered time, if the same user visits the same referred url, re-register the visit. Leave empty to disable re-register.', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-register-visit-again-after',
                                ] );
                                ?>
                            </tbody>
                        </table>
                        <h2><?php esc_html_e( 'Social Share', 'affiliates-for-woocommerce' ); ?></h2>
                        <table class="form-table">
                            <tbody>
                                <?php
                                ddwcaf_form_field( [
                                    'type'              => 'select',
                                    'label'             => esc_html__( 'Allowed Options', 'affiliates-for-woocommerce' ),
                                    'help_tip'          => esc_html__( 'Selected social media share options will get displayed on the Link Generator section.', 'affiliates-for-woocommerce' ),
                                    'description'       => esc_html__( 'Select which social media share options you want to enable in the "Link Generator" section on the affiliate dashboard. Leave empty for no social shares.', 'affiliates-for-woocommerce' ),
                                    'id'                => 'ddwcaf-referral-social-share-options',
                                    'name'              => '_ddwcaf_referral_social_share_options[]',
                                    'value'             => $ddwcaf_configuration[ 'referral_social_share_options' ],
                                    'input_class'       => [ 'ddwcaf-select2' ],
                                    'custom_attributes' => [
                                        'multiple'         => true,
                                        'data-placeholder' => esc_attr__( 'Search by social platforms', 'affiliates-for-woocommerce' ),
                                    ],
                                    'options'           => [
                                        'facebook'  => esc_html__( 'Facebook', 'affiliates-for-woocommerce' ),
                                        'twitter'   => esc_html__( 'Twitter', 'affiliates-for-woocommerce' ),
                                        'pinterest' => esc_html__( 'Pinterest', 'affiliates-for-woocommerce' ),
                                        'linkedin'  => esc_html__( 'LinkedIn', 'affiliates-for-woocommerce' ),
                                        'viber'     => esc_html__( 'Viber', 'affiliates-for-woocommerce' ),
                                        'vk'        => esc_html__( 'VK', 'affiliates-for-woocommerce' ),
                                        'reddit'    => esc_html__( 'Reddit', 'affiliates-for-woocommerce' ),
                                        'whatsapp'  => esc_html__( 'WhatsApp', 'affiliates-for-woocommerce' ),
                                        'tumblr'    => esc_html__( 'Tumblr', 'affiliates-for-woocommerce' ),
                                        'email'     => esc_html__( 'Email', 'affiliates-for-woocommerce' ),
                                    ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Title', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the title used in the social share.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'This is the title to be used in the Twitter and Pinterest social share.', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-social-share-title',
                                    'value'       => $ddwcaf_configuration[ 'social_share_title' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'textarea',
                                    'label'       => esc_html__( 'Text', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'Enter the text to be used in the Twitter, Pinterest and WhatsApp social share. Use {referral_url} placeholder to display the affiliate url.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Use {referral_url} placeholder to display the affiliate url.', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-social-share-text',
                                    'value'       => $ddwcaf_configuration[ 'social_share_text' ],
                                ] );

                                ddwcaf_form_field( [
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Pinterest Image URL', 'affiliates-for-woocommerce' ),
                                    'help_tip'    => esc_html__( 'This is the image url used in the Pinterest social share.', 'affiliates-for-woocommerce' ),
                                    'description' => esc_html__( 'Enter the URL of the image to use in Pinterest social sharing.', 'affiliates-for-woocommerce' ),
                                    'id'          => 'ddwcaf-pinterest-image-url',
                                    'value'       => $ddwcaf_configuration[ 'pinterest_image_url' ],
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
