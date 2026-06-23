<?php
/**
 * Setup Wizard for Affiliates for WooCommerce (Free)
 */

namespace DDWCAffiliates\Includes\Admin;

use DevDiggers\Framework\Includes\DDFW_Form_Field;
use DevDiggers\Framework\Includes\DDFW_Setup_Wizard;

defined( 'ABSPATH' ) || exit();

/**
 * DDWCAF_Setup_Wizard class
 */
class DDWCAF_Setup_Wizard {

    /**
     * Constructor
     */
    public function __construct() {
        $slug = 'affiliates-for-woocommerce';
        if ( ! get_option( 'ddfw_setup_wizard_completed_' . $slug ) ) {
            // Check if plugin is already configured by checking for existing options.
            if ( get_option( '_ddwcaf_enabled' ) ) {
                update_option( 'ddfw_setup_wizard_completed_' . $slug, true );
            }
        }
        new DDFW_Setup_Wizard( $this->get_wizard_config() );
    }

    /**
     * Get the wizard configuration
     *
     * @return array
     */
    public function get_wizard_config() {
        return [
            'plugin_slug'    => 'affiliates-for-woocommerce',
            'plugin_file'    => 'affiliates-for-woocommerce/functions.php',
            'dashboard_page' => 'ddwcaf-dashboard',
            'redirect_url'   => admin_url( 'admin.php?page=ddwcaf-dashboard' ),
            'brand'        => [
                'name' => esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ),
                'description' => esc_html__( "Welcome to Affiliates for WooCommerce! Let's quickly set up your affiliate network so you can start growing your sales.", 'affiliates-for-woocommerce' ),
                'logo' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="15" fill="var(--ddfw-tab-background-color)"/>
                    <circle cx="16" cy="11" r="3.5" fill="var(--ddfw-primary-color)"/>
                    <path d="M9 22C9 19.7909 10.7909 18 13 18H19C21.2091 18 23 19.7909 23 22V24H9V22Z" fill="var(--ddfw-primary-color)"/>
                    <path d="M16 18V14M13 18L11 16M19 18L21 16" stroke="var(--ddfw-primary-color)" stroke-width="1.5" stroke-linecap="round"/>
                    <circle cx="10" cy="15" r="2.5" fill="var(--ddfw-primary-color)" fill-opacity="0.5"/>
                    <circle cx="22" cy="15" r="2.5" fill="var(--ddfw-primary-color)" fill-opacity="0.5"/>
                </svg>',
            ],
            'steps'        => [
                'welcome'   => [
                    'label'         => esc_html__( 'Welcome', 'affiliates-for-woocommerce' ),
                    'view_callback' => [ $this, 'welcome_view' ],
                ],
                'general'   => [
                    'label'         => esc_html__( 'General Settings', 'affiliates-for-woocommerce' ),
                    'title'         => esc_html__( 'Basic Configuration', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Start by enabling the affiliate system and defining your base settings.', 'affiliates-for-woocommerce' ),
                    'view_callback' => [ $this, 'general_settings_view' ],
                    'save_callback' => [ $this, 'save_fields' ],
                ],
                'referrals'   => [
                    'label'         => esc_html__( 'Referral Settings', 'affiliates-for-woocommerce' ),
                    'title'         => esc_html__( 'Referral Tracking', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Define how referrals are tracked and how long the cookies should last.', 'affiliates-for-woocommerce' ),
                    'view_callback' => [ $this, 'referral_settings_view' ],
                    'save_callback' => [ $this, 'save_fields' ],
                ],
                'commissions'   => [
                    'label'         => esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
                    'title'         => esc_html__( 'Commission Rates', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Set the default commission structure for your affiliates.', 'affiliates-for-woocommerce' ),
                    'view_callback' => [ $this, 'commissions_settings_view' ],
                    'save_callback' => [ $this, 'save_fields' ],
                ],
                'ready'     => [
                    'label'             => esc_html__( 'Ready!', 'affiliates-for-woocommerce' ),
                    'ready_title'       => esc_html__( 'Congratulations! Your affiliate network is ready.', 'affiliates-for-woocommerce' ),
                    'ready_description' => esc_html__( 'You can always adjust these settings and configure more advanced rules from the plugin dashboard.', 'affiliates-for-woocommerce' ),
                ],
            ],
        ];
    }

    /**
     * Welcome view
     * 
     * @return void
     */
    public function welcome_view() {
        ?>
        <div class="ddfw-setup-wizard-ready ddfw-setup-wizard-onboarding">
            <div class="ddfw-success-icon-wrap">
                <svg class="ddfw-success-svg" width="100" height="100" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="15" fill="var(--ddfw-tab-background-color)"/>
                    <circle cx="16" cy="11" r="3.5" fill="var(--ddfw-primary-color)"/>
                    <path d="M9 22C9 19.7909 10.7909 18 13 18H19C21.2091 18 23 19.7909 23 22V24H9V22Z" fill="var(--ddfw-primary-color)"/>
                    <path d="M16 18V14M13 18L11 16M19 18L21 16" stroke="var(--ddfw-primary-color)" stroke-width="1.5" stroke-linecap="round"/>
                    <circle cx="10" cy="15" r="2.5" fill="var(--ddfw-primary-color)" fill-opacity="0.5"/>
                    <circle cx="22" cy="15" r="2.5" fill="var(--ddfw-primary-color)" fill-opacity="0.5"/>
                </svg>
            </div>
            <h2 class="ddfw-setup-wizard-ready-title"><?php esc_html_e( 'Welcome to Affiliates for WooCommerce!', 'affiliates-for-woocommerce' ); ?></h2>
            <p class="ddfw-setup-wizard-ready-desc">
                <?php esc_html_e( 'Supercharge your sales with a professional affiliate network. Let\'s quickly set up the core features so you can start recruiting partners and growing your sales.', 'affiliates-for-woocommerce' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * General settings view
     * 
     * @return void
     */
    public function general_settings_view() {
        $pages_options = [];
        $pages         = get_pages();

        if ( ! empty( $pages ) ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }

        ?>
        <div class="ddfw-fields-section">
            <table class="form-table">
                <tbody>
                    <?php
                    $fields = [
                        [
                            'id'          => 'ddwcaf-enabled',
                            'label'       => esc_html__( 'Enable Affiliate System', 'affiliates-for-woocommerce' ),
                            'type'        => 'checkbox',
                            'value'       => get_option( '_ddwcaf_enabled', 'yes' ),
                            'name'        => '_ddwcaf_enabled',
                            'description' => esc_html__( 'Toggle the entire affiliate system on or off.', 'affiliates-for-woocommerce' ),
                        ],
                        [
                            'id'          => 'ddwcaf-affiliate-dashboard-page-id',
                            'label'       => esc_html__( 'Affiliate Dashboard Page', 'affiliates-for-woocommerce' ),
                            'type'        => 'select',
                            'options'     => $pages_options,
                            'value'       => get_option( '_ddwcaf_affiliate_dashboard_page_id' ),
                            'name'        => '_ddwcaf_affiliate_dashboard_page_id',
                            'description' => esc_html__( 'Select the page that will be used as the affiliate dashboard.', 'affiliates-for-woocommerce' ),
                        ],
                    ];

                    foreach ( $fields as $field ) {
                        DDFW_Form_Field::display_form_field( $field );
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Referral settings view
     * 
     * @return void
     */
    public function referral_settings_view() {
        ?>
        <div class="ddfw-fields-section">
            <table class="form-table">
                <tbody>
                    <?php
                    $fields = [
                        [
                            'id'          => 'ddwcaf-query-variable-name',
                            'label'       => esc_html__( 'URL Parameter', 'affiliates-for-woocommerce' ),
                            'type'        => 'text',
                            'value'       => get_option( '_ddwcaf_query_variable_name', 'ref' ),
                            'name'        => '_ddwcaf_query_variable_name',
                            'placeholder' => esc_html__( 'Default: ref', 'affiliates-for-woocommerce' ),
                            'description' => sprintf( esc_html__( 'The query string used to identify a referral (e.g., %s?ref=123).', 'affiliates-for-woocommerce' ), site_url() ),
                        ],
                        [
                            'id'          => 'ddwcaf-referral-cookie-expires',
                            'label'       => esc_html__( 'Cookie Life (Days)', 'affiliates-for-woocommerce' ),
                            'type'        => 'text',
                            'value'       => get_option( '_ddwcaf_referral_cookie_expires', 7 ),
                            'name'        => '_ddwcaf_referral_cookie_expires',
                            'description' => esc_html__( 'How long the tracking cookie remains active. Leave empty for session-only tracking.', 'affiliates-for-woocommerce' ),
                        ],
                    ];

                    foreach ( $fields as $field ) {
                        DDFW_Form_Field::display_form_field( $field );
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Commissions settings view
     * 
     * @return void
     */
    public function commissions_settings_view() {
        ?>
        <div class="ddfw-fields-section">
            <table class="form-table">
                <tbody>
                    <?php
                    $fields = [
                        [
                            'id'                => 'ddwcaf-default-commission-rate',
                            'label'             => esc_html__( 'Default Commission (%)', 'affiliates-for-woocommerce' ),
                            'type'              => 'number',
                            'value'             => get_option( '_ddwcaf_default_commission_rate', 10 ),
                            'name'              => '_ddwcaf_default_commission_rate',
                            'custom_attributes' => [ 'min' => 0, 'max' => 100 ],
                            'description'       => esc_html__( 'The default percentage awarded for successful referrals. This can be overridden by specific rules or per-affiliate settings.', 'affiliates-for-woocommerce' ),
                        ],
                    ];

                    foreach ( $fields as $field ) {
                        DDFW_Form_Field::display_form_field( $field );
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Generic save helper
     * 
     * @param array $form_data Form data array.
     * @return bool True if save successful, false otherwise.
     */
    public function save_fields( $form_data ) {
        foreach ( $form_data as $field ) {
            if ( strpos( $field['name'], '_ddwcaf_' ) === 0 ) {
                update_option( $field['name'], sanitize_text_field( $field['value'] ) );
            }
        }

        return true;
    }
}
