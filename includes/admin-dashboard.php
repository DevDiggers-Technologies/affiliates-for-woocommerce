<?php
/**
 * admin-dashboard.php
 * 
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all admin end dashboard management.
 */

namespace DDWCAffiliates\Includes;

use DevDiggers\Framework\Includes\DDFW_Plugin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_SVG;
use DDWCAffiliates\Templates\Admin;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Admin_Dashboard' ) ) {
    /**
     * Admin Dashboard Class
     */
    class DDWCAF_Admin_Dashboard {
        /**
         * Error Helper Trait
         */
        use DDWCAF_Error_Helper;
        /**
         * Configuration Variable
         *
         * @var array
         */
        protected $ddwcaf_configuration;

        /**
         * Affiliate Variable
         *
         * @var object
         */
        protected $affiliate_helper;

        /**
         * Dashboard Variable
         *
         * @var DDFW_Plugin_Dashboard
         */
        protected $dashboard;

        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            $this->ddwcaf_configuration = $ddwcaf_configuration;
            $this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
            $this->ddwcaf_add_dashboard_menu();
            add_action( 'admin_enqueue_scripts', [ $this, 'ddwcaf_enqueue_admin_scripts' ] );
        }

        /**
         * Add Admin menu function
         *
         * @return void
         */
        public function ddwcaf_add_dashboard_menu() {
            ob_start();
            ?>
            <svg width="30" zoomAndPan="magnify" viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet" version="1.0"><path fill="#000000" d="M11 22c-1.1 0-2-.9-2-2v-3H6c-1.1 0-2-.9-2-2v-4a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2h4c1.1 0 2 .9 2 2v4a2 2 0 0 1-2 2v4c0 1.1-.9 2-2 2h-3v3c0 1.1-.9 2-2 2h-4zM6 5v4h12V5H6zm0 6v4h4v-4H6zm12 0h-4v4h4v-4zM11 17v3h3v-3h-3z" fill-opacity="1" fill-rule="nonzero"/></svg>
            <?php esc_html_e( 'Affiliates', 'affiliates-for-woocommerce' ); ?>
            <?php
            $plugin_name = ob_get_clean();

            $args = [
                'page_title'              => esc_html__( 'Affiliates for WooCommerce', 'affiliates-for-woocommerce' ),
                'menu_title'              => esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ),
                'slug'                    => 'ddwcaf-dashboard',
                'plugin_name'             => $plugin_name,
                'screen_options_callback' => [ $this, 'add_screen_options' ],
                'upgrade_url'             => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=plugin-dashboard&utm_medium=upgrade-link&utm_campaign=affiliates-for-woocommerce',
                'menus'                   => [
                    'dashboard'      => [
                        'label'    => esc_html__( 'Dashboard', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_dashboard' ],
                        'layout'   => 'full-width',
                    ],
                    'affiliates'     => [
                        'label'    => esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_affiliates_list' ],
                        'layout'   => 'full-width',
                    ],
                    'commissions'    => [
                        'label'    => esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_commissions_list' ],
                        'layout'   => 'full-width',
                    ],
                    'payouts'        => [
                        'label'    => esc_html__( 'Payouts', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_payouts_list' ],
                        'layout'   => 'full-width',
                    ],
                    'top-products'   => [
                        'label'    => esc_html__( 'Top Products', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_top_products_list' ],
                        'layout'   => 'full-width',
                    ],
                    'visits'         => [
                        'label'    => esc_html__( 'Visits', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_visits_list' ],
                        'layout'   => 'full-width',
                    ],
                    'reports'         => [
                        'label'    => esc_html__( 'Reports', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_reports' ],
                        'layout'   => 'full-width',
                    ],
                    'rules'          => [
                        'label'    => esc_html__( 'Rules', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_rules_template' ],
                        'layout'   => 'full-width',
                    ],
                    'registration-fields' => [
                        'label'    => esc_html__( 'Registration Fields', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_affiliate_registration_fields_list' ],
                        'layout'   => 'full-width',
                    ],
                    'creatives'      => [
                        'label'    => esc_html__( 'Creatives', 'affiliates-for-woocommerce' ),
                        'callback' => [ $this, 'ddwcaf_get_creatives_list' ],
                        'layout'   => 'full-width',
                    ],
                    'configuration'  => [
                        'label'    => esc_html__( 'Configuration', 'affiliates-for-woocommerce' ),
                        'layout'   => 'sidebar',
                        'tabs'     => [
                            'general'     => [
                                'label'    => esc_html__( 'General', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'general',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_general_configuration_template' ],
                            ],
                            'referrals'   => [
                                'label'    => esc_html__( 'Referrals', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'referrals',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_referrals_configuration_template' ],
                            ],
                            'commissions' => [
                                'label'    => esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'commissions',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_commissions_configuration_template' ],
                            ],
                            'payouts'     => [
                                'label'    => esc_html__( 'Payouts', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'payouts',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_payouts_configuration_template' ],
                            ],
                            'shortcodes'  => [
                                'label'    => esc_html__( 'Shortcodes', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'shortcodes',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_shortcodes_configuration_template' ],
                            ],
                            'social-promotion' => [
                                'label'    => esc_html__( 'Social Promotion', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'social',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_social_promotion_configuration_template' ],
                            ],
                            'emails'      => [
                                'label'    => esc_html__( 'Emails', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'emails',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_emails_configuration_template' ],
                            ],
                            'endpoints'   => [
                                'label'    => esc_html__( 'Endpoints', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'endpoints',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_endpoints_configuration_template' ],
                            ],
                            'layout'      => [
                                'label'    => esc_html__( 'Layout', 'affiliates-for-woocommerce' ),
                                'icon'     => DDFW_SVG::get_svg_icon(
                                    'layout',
                                    true,
                                    [ 'size' => 18 ]
                                ),
                                'callback' => [ $this, 'ddwcaf_get_layout_configuration_template' ],
                            ],
                        ],
                    ],
                ],
            ];
            $this->dashboard = new DDFW_Plugin_Dashboard( $args );

            do_action( 'ddwcaf_admin_menu_action' );
        }

        /**
         * Add screen options function
         *
         * @return void
         */
        public function add_screen_options() {
            $menu = ! empty( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : 'dashboard';

            switch ( $menu ) {
                case 'affiliates':
                    $this->ddwcaf_add_screen_options_in_affiliates_list();
                    break;
                case 'commissions':
                    $this->ddwcaf_add_screen_options_in_commissions_list();
                    break;
                case 'payouts':
                    $this->ddwcaf_add_screen_options_in_payouts_list();
                    break;
                case 'top-products':
                    $this->ddwcaf_add_screen_options_in_top_products_list();
                    break;
                case 'visits':
                    $this->ddwcaf_add_screen_options_in_visits_list();
                    break;
                case 'registration-fields':
                    $this->ddwcaf_add_screen_options_in_affiliates_registration_fields_list();
                    break;
                case 'creatives':
                    $this->ddwcaf_add_screen_options_in_creatives_list();
                    break;
            }
        }

        /**
		 * Affiliates List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_affiliates_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Affiliates Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_affiliates_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Affiliates\DDWCAF_Affiliates_List_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Commissions List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_commissions_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Commissions Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_commissions_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Commissions\DDWCAF_Commissions_List_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Payouts List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_payouts_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Payouts Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_payouts_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Payouts\DDWCAF_Payouts_List_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Top Products List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_top_products_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Top Products Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_top_products_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Top_Products\DDWCAF_Top_Products_List_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Visits List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_visits_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Visits Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_visits_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Visits\DDWCAF_Visits_List_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Affiliate Registration Fields List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_affiliates_registration_fields_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Registration Fields Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_affiliates_registration_fields_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Registration\DDWCAF_Affiliate_Registration_Fields_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Creatives List Screen Options.
		 *
		 * @return void
		 */
		public function ddwcaf_add_screen_options_in_creatives_list() {
			global $myListTable;

            $args = [
                'label'   => esc_html__( 'Creatives Per Page', 'affiliates-for-woocommerce' ),
                'default' => 20,
                'option'  => 'ddwcaf_creatives_per_page',
                'hidden' => 'id'
            ];

            $myListTable = new Admin\Creatives\DDWCAF_Creatives_List_Template( $this->ddwcaf_configuration );

			add_screen_option( 'per_page', $args );
		}

        /**
		 * Dashboard Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_dashboard() {
            new Admin\Dashboard\DDWCAF_Dashboard_Template( $this->ddwcaf_configuration );
        }

        /**
		 * Reports Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_reports() {
            ddfw_upgrade_to_pro_section(
                [
                    'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/reports.webp',
                    'heading'       => esc_html__( 'Advanced Affiliate Analytics', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Get deep insights into your affiliate program performance with comprehensive reports and real-time data visualization.', 'affiliates-for-woocommerce' ),
                    'list_features' => [
                        esc_html__( 'Detailed Revenue and Commission Overviews', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Top Performing Affiliates and Products Tracking', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Visit and Conversion Rate Analysis', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Custom Date Range Filtering', 'affiliates-for-woocommerce' ),
                    ],
                    'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Reports Page&utm_campaign=Affiliates for WooCommerce Pro',
                ]
            );
        }

        /**
		 * Affiliates List Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_affiliates_list() {
            if ( ! empty( $_GET[ 'action' ] ) && 'view' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) ) {
                new Admin\Affiliates\DDWCAF_Manage_Affiliate_Template( $this->ddwcaf_configuration );
            } else {
                if ( ! empty( $_GET[ 'status' ] ) && 'saved' === sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) ) ) {
                    $this->ddwcaf_print_notification( esc_html__( 'Affiliate saved successfully.', 'affiliates-for-woocommerce' ) );
                }

                $obj = new Admin\Affiliates\DDWCAF_Affiliates_List_Template( $this->ddwcaf_configuration );
                ?>
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Affiliates', 'affiliates-for-woocommerce' ); ?></h1>
                <a href="<?php echo esc_url( admin_url( 'user-new.php?ddwcaf-add-affiliate=1' ) ); ?>" class="page-title-action button button-primary">
                    <?php
                        DDFW_SVG::get_svg_icon(
                            'plus',
                            false,
                            [ 'size' => 15 ]
                        );
                        esc_html_e( 'Add New', 'affiliates-for-woocommerce' );
                    ?>
                </a>

                <div class="ddwcaf-affiliates-list-wrap">
                    <form method="get">
                        <input type="hidden" name="page" value="<?php echo isset( $_GET[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                        <input type="hidden" name="paged" value="<?php echo isset( $_GET[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'paged' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                        <input type="hidden" name="menu" value="<?php echo isset( $_GET[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) ) : ''; ?>" />
                        <?php
                        wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                        $obj->prepare_items();
                        $obj->search_box( esc_html__( 'Search', 'affiliates-for-woocommerce' ), 'search-id' );
                        $obj->display();
                        ?>
                    </form>
                </div>
                <?php
            }
        }

        /**
		 * Commissions List Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_commissions_list() {
            $obj = new Admin\Commissions\DDWCAF_Commissions_List_Template( $this->ddwcaf_configuration );

            $shows = [
                ''                => esc_html__( 'All', 'affiliates-for-woocommerce' ),
                'pending'         => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
                'pending_payment' => esc_html__( 'Pending Payment', 'affiliates-for-woocommerce' ),
                'paid'            => esc_html__( 'Paid', 'affiliates-for-woocommerce' ),
                'not_confirmed'   => esc_html__( 'Not Confirmed', 'affiliates-for-woocommerce' ),
                'cancelled'       => esc_html__( 'Cancelled', 'affiliates-for-woocommerce' ),
                'refunded'        => esc_html__( 'Refunded', 'affiliates-for-woocommerce' ),
            ];

            $current_show = ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '';
            ?>
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Commissions', 'affiliates-for-woocommerce' ); ?></h1>
                <ul class="subsubsub">
                    <?php
                    foreach ( $shows as $key => $value ) {
                        ?>
                        <li class="<?php echo esc_attr( $key ); ?>">
                            <a class="<?php echo esc_attr( $current_show === $key ? 'current' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) . '&menu=' . sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) . '&show=' . $key ) ); ?>"><?php echo esc_html( $value ); ?></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_GET[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="menu" value="<?php echo isset( $_GET[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_GET[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'paged' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="payout-id" value="<?php echo isset( $_GET[ 'payout-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'payout-id' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_GET[ 'affiliate-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="from-date" value="<?php echo isset( $_GET[ 'from-date' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'from-date' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="end-date" value="<?php echo isset( $_GET[ 'end-date' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'end-date' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="show" value="<?php echo isset( $_GET[ 'show' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->search_box( esc_html__( 'Search by ID', 'affiliates-for-woocommerce' ), 'search-id' );
                    $obj->display();
                    ?>
                </form>
            <?php
        }

        /**
		 * Payouts List Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_payouts_list() {
            if ( ! empty( $_GET[ 'tab' ] ) ) {
                if ( 'manage' === sanitize_text_field( wp_unslash( $_GET[ 'tab' ] ) ) ) {
                    new Admin\Payouts\DDWCAF_Manage_Payout_Template( $this->ddwcaf_configuration );
                } elseif ( 'create' === sanitize_text_field( wp_unslash( $_GET[ 'tab' ] ) ) ) {
                    ddfw_upgrade_to_pro_section(
                        [
                            'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/payout-wizard.webp',
                            'heading'       => esc_html__( 'Advanced Payout Wizard', 'affiliates-for-woocommerce' ),
                            'description'   => esc_html__( 'Streamline your payout process with a powerful, step-by-step wizard designed for efficiency and accuracy.', 'affiliates-for-woocommerce' ),
                            'list_features' => [
                                esc_html__( 'Fast and Efficient Bulk Payout Creation', 'affiliates-for-woocommerce' ),
                                esc_html__( 'Filter Commissions by Custom Date Ranges', 'affiliates-for-woocommerce' ),
                                esc_html__( 'Support for Multiple Payment References', 'affiliates-for-woocommerce' ),
                                esc_html__( 'Real-time Payout Processing with Progress Bar', 'affiliates-for-woocommerce' ),
                                esc_html__( 'Automated Affiliate Selection for Pending Commissions', 'affiliates-for-woocommerce' ),
                                esc_html__( 'Detailed Payout Success and Error Reports', 'affiliates-for-woocommerce' ),
                            ],
                            'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Payout Wizard Page&utm_campaign=Affiliates for WooCommerce Pro',
                        ]
                    );
                }
            } else {
                $obj = new Admin\Payouts\DDWCAF_Payouts_List_Template( $this->ddwcaf_configuration );

                $shows = [
                    ''          => esc_html__( 'All', 'affiliates-for-woocommerce' ),
                    'pending'   => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
                    'completed' => esc_html__( 'Completed', 'affiliates-for-woocommerce' ),
                    'cancelled' => esc_html__( 'Cancelled', 'affiliates-for-woocommerce' ),
                ];

                $current_show = ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '';
                ?>
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Payouts', 'affiliates-for-woocommerce' ); ?></h1>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) . '&menu=' . sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) . '&tab=create' ) ); ?>" class="page-title-action button button-primary">
                    <?php
                        DDFW_SVG::get_svg_icon(
                            'plus',
                            false,
                            [ 'size' => 15 ]
                        );
                        esc_html_e( 'Create', 'affiliates-for-woocommerce' );
                    ?>
                </a>
                <ul class="subsubsub">
                    <?php
                    foreach ( $shows as $key => $value ) {
                        ?>
                        <li class="<?php echo esc_attr( $key ); ?>">
                            <a class="<?php echo esc_attr( $current_show === $key ? 'current' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) . '&menu=' . sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) . '&show=' . $key ) ); ?>"><?php echo esc_html( $value ); ?></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_GET[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="menu" value="<?php echo isset( $_GET[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_GET[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'paged' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="payout-id" value="<?php echo isset( $_GET[ 'payout-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'payout-id' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_GET[ 'affiliate-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="from-date" value="<?php echo isset( $_GET[ 'from-date' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'from-date' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="end-date" value="<?php echo isset( $_GET[ 'end-date' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'end-date' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="show" value="<?php echo isset( $_GET[ 'show' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->search_box( esc_html__( 'Search by ID', 'affiliates-for-woocommerce' ), 'search-id' );
                    $obj->display();
                    ?>
                </form>
                <?php
            }
        }

        /**
		 * Top Products List Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_top_products_list() {
            $obj = new Admin\Top_Products\DDWCAF_Top_Products_List_Template( $this->ddwcaf_configuration );
            ?>
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Top Products', 'affiliates-for-woocommerce' ); ?></h1>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_GET[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="menu" value="<?php echo isset( $_GET[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="order" value="<?php echo isset( $_GET[ 'order' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="orderby" value="<?php echo isset( $_GET[ 'orderby' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_GET[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'paged' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_GET[ 'affiliate-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) ) : ''; ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->display();
                    ?>
                </form>
            <?php
        }

        /**
		 * Visits List Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_visits_list() {
            $obj = new Admin\Visits\DDWCAF_Visits_List_Template( $this->ddwcaf_configuration );

            $shows = [
                ''              => esc_html__( 'All', 'affiliates-for-woocommerce' ),
                'converted'     => esc_html__( 'Converted', 'affiliates-for-woocommerce' ),
                'not_converted' => esc_html__( 'Not Converted', 'affiliates-for-woocommerce' ),
            ];

            $current_show = ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '';
            ?>
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Visits', 'affiliates-for-woocommerce' ); ?></h1>
                <ul class="subsubsub">
                    <?php
                    foreach ( $shows as $key => $value ) {
                        ?>
                        <li class="<?php echo esc_attr( $key ); ?>">
                            <a class="<?php echo esc_attr( $current_show === $key ? 'current' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) . '&menu=' . sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) . '&show=' . $key ) ); ?>"><?php echo esc_html( $value ); ?></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_GET[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="menu" value="<?php echo isset( $_GET[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_GET[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'paged' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="payout-id" value="<?php echo isset( $_GET[ 'payout-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'payout-id' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_GET[ 'affiliate-id' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="from-date" value="<?php echo isset( $_GET[ 'from-date' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'from-date' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="end-date" value="<?php echo isset( $_GET[ 'end-date' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'end-date' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="show" value="<?php echo isset( $_GET[ 'show' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) ) : ''; ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->search_box( esc_html__( 'Search by ID', 'affiliates-for-woocommerce' ), 'search-id' );
                    $obj->display();
                    ?>
                </form>
            <?php
        }

        /**
		 * Rules Template Submenu function
		 *
		 * @return void
		 */
		public function ddwcaf_get_rules_template() {
            ddfw_upgrade_to_pro_section(
                [
                    'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/rules.webp',
                    'heading'       => esc_html__( 'Smart Commission Rules', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Automate your commission structures with advanced rules based on products, categories, or user roles.', 'affiliates-for-woocommerce' ),
                    'list_features' => [
                        esc_html__( 'Product-Specific Commission Rates', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Category-Based Commission Logic', 'affiliates-for-woocommerce' ),
                        esc_html__( 'User Role Based Commission Overrides', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Tiered & Multi-Level Commission Support', 'affiliates-for-woocommerce' ),
                    ],
                    'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Rules Page&utm_campaign=Affiliates for WooCommerce Pro',
                ]
            );
		}

        /**
         * Get affiliate registration fields list function
         *
         * @return void
         */
        public function ddwcaf_get_affiliate_registration_fields_list() {
            if ( ! empty( $_GET[ 'action' ] ) && ( 'add' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) || 'edit' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) ) ) {
                ddfw_upgrade_to_pro_section(
                    [
                        'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/registration-fields.webp',
                        'heading'       => esc_html__( 'Custom Registration Fields', 'affiliates-for-woocommerce' ),
                        'description'   => esc_html__( 'Gather specific data from your affiliates with a fully customizable registration form.', 'affiliates-for-woocommerce' ),
                        'list_features' => [
                            esc_html__( 'Multiple Field Types (Text, Select, Checkbox, etc.)', 'affiliates-for-woocommerce' ),
                            esc_html__( 'Mandatory & Optional Field Controls', 'affiliates-for-woocommerce' ),
                            esc_html__( 'User Meta Integration', 'affiliates-for-woocommerce' ),
                            esc_html__( 'Custom Labeling and Tooltips', 'affiliates-for-woocommerce' ),
                        ],
                        'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Add/Edit Registration Field Page&utm_campaign=Affiliates for WooCommerce Pro',
                    ]
                );
            } else {
                if ( ! empty( $_GET[ 'status' ] ) && 'saved' === sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) ) ) {
                    $this->ddwcaf_print_notification( esc_html__( 'Field saved successfully.', 'affiliates-for-woocommerce' ) );
                }
                if ( ! empty( $_POST[ 'ddwcaf_reset_affiliate_registration_fields' ] ) && ! empty( $_POST[ 'ddwcaf_nonce' ] ) && wp_verify_nonce( $_POST[ 'ddwcaf_nonce' ], 'ddwcaf_nonce_action' ) ) {
                    $this->affiliate_helper->ddwcaf_delete_affiliate_registration_fields();
                    $this->ddwcaf_print_notification( esc_html__( 'Registration fields are reset to default now.', 'affiliates-for-woocommerce' ) );
                }

                ?>
                <div class="ddwcaf-info-notice">
                    <div class="ddwcaf-info-notice-icon">
                        <?php echo DDFW_SVG::get_svg_icon( 'info', true, [ 'size' => 20 ] ); ?>
                    </div>
                    <div class="ddwcaf-info-notice-content">
                        <p>
                            <strong><?php esc_html_e( 'How to Show the Form?', 'affiliates-for-woocommerce' ); ?></strong><br>
                            <?php echo sprintf( wp_kses_post( __( 'To show this form anywhere on the front end, use this shortcode %s. Also, registration form will get displayed on the affiliate dashboard page as well.', 'affiliates-for-woocommerce' ) ), '<strong>' . esc_html( $this->ddwcaf_configuration[ 'affiliate_registration_form_shortcode' ] ) . '</strong>' ); ?>
                        </p>
                    </div>
                </div>
                <?php

                $obj = new Admin\Registration\DDWCAF_Affiliate_Registration_Fields_Template( $this->ddwcaf_configuration );
                ?>
                <hr class="wp-header-end" />
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Affiliates Registration Fields', 'affiliates-for-woocommerce' ); ?></h1>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) . '&menu=' . sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) . '&action=add' ) ); ?>" class="page-title-action button button-primary">
                    <?php
						DDFW_SVG::get_svg_icon(
							'plus',
							false,
							[ 'size' => 15 ]
						);
						esc_html_e( 'Add New', 'affiliates-for-woocommerce' );
					?>
                </a>

                <form method="post" class="ddwcaf-inline-block">
                    <?php wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' ); ?>
                    <input type="submit" class="page-title-action button button-secondary" name="ddwcaf_reset_affiliate_registration_fields" value="<?php esc_attr_e( 'Restore Defaults', 'affiliates-for-woocommerce' ); ?>" />
                </form>

                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_GET[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <input type="hidden" name="menu" value="<?php echo isset( $_GET[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_GET[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'paged' ] ) ) ) : ''; // WPCS: CSRF ok. // WPCS: input var ok. ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->search_box( esc_html__( 'Search', 'affiliates-for-woocommerce' ), 'search-id' );
                    $obj->display();
                    ?>
                </form>
                <?php
            }
        }

		/**
		 * Get creatives list function
		 */
		public function ddwcaf_get_creatives_list() {
            ddfw_upgrade_to_pro_section(
                [
                    'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/creatives.webp',
                    'heading'       => esc_html__( 'Marketing Assets for Affiliates', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Provide your affiliates with professional banners and text links to improve their marketing efforts.', 'affiliates-for-woocommerce' ),
                    'list_features' => [
                        esc_html__( 'Visual Banner Ads Management', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Custom Text Link Distribution', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Easy Copy-to-Clipboard Links', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Organized Asset Categories', 'affiliates-for-woocommerce' ),
                    ],
                    'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Creatives Page&utm_campaign=Affiliates for WooCommerce Pro',
                ]
            );
		}

		/**
		 * Get General Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_general_configuration_template() {
			new Admin\Configuration\DDWCAF_General_Configuration_Template( $this->ddwcaf_configuration );
		}

		/**
		 * Get Referrals Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_referrals_configuration_template() {
			new Admin\Configuration\DDWCAF_Referrals_Configuration_Template( $this->ddwcaf_configuration );
		}

		/**
		 * Get Social Promotion Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_social_promotion_configuration_template() {
            ddfw_upgrade_to_pro_section(
                [
                    'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/social-promotion.webp',
                    'heading'       => esc_html__( 'Social Media Sharing', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Enable affiliates to share their referral links across major social networks with a single click.', 'affiliates-for-woocommerce' ),
                    'list_features' => [
                        esc_html__( 'Facebook, Twitter/X, WhatsApp and Various Social Networks Support', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Custom Share Titles and Content', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Social Media Preview Optimization', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Pinterest Image Integration', 'affiliates-for-woocommerce' ),
                    ],
                    'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Social Promotion Page&utm_campaign=Affiliates for WooCommerce Pro',
                ]
            );
		}

		/**
		 * Get Commissions Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_commissions_configuration_template() {
			new Admin\Configuration\DDWCAF_Commissions_Configuration_Template( $this->ddwcaf_configuration );
		}

		/**
		 * Get Payouts Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_payouts_configuration_template() {
			new Admin\Configuration\DDWCAF_Payouts_Configuration_Template( $this->ddwcaf_configuration );
		}

		/**
		 * Get Shortcodes Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_shortcodes_configuration_template() {
			new Admin\Configuration\DDWCAF_Shortcodes_Configuration_Template( $this->ddwcaf_configuration );
		}

		/**
		 * Get Emails Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_emails_configuration_template() {
            ddfw_upgrade_to_pro_section(
                [
                    'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/emails.webp',
                    'heading'       => esc_html__( 'Affiliate Email Notifications', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Keep your affiliates informed with automated emails for registration, approvals, and commission updates.', 'affiliates-for-woocommerce' ),
                    'list_features' => [
                        esc_html__( 'Fully Customizable Email Templates', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Support for Multiple Event Triggers', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Dynamic Placeholders for Personalization', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Admin and Affiliate Specific Notifications', 'affiliates-for-woocommerce' ),
                    ],
                    'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Emails Page&utm_campaign=Affiliates for WooCommerce Pro',
                ]
            );
		}

		/**
		 * Get Endpoints Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_endpoints_configuration_template() {
            ddfw_upgrade_to_pro_section(
                [
                    'image_url'     => DDWCAF_PLUGIN_URL . 'assets/images/pro-pages/endpoints.webp',
                    'heading'       => esc_html__( 'Customizable Affiliate Dashboard Endpoints', 'affiliates-for-woocommerce' ),
                    'description'   => esc_html__( 'Customize your affiliate dashboard structure by defining unique URL endpoints for each section.', 'affiliates-for-woocommerce' ),
                    'list_features' => [
                        esc_html__( 'Custom Slug Names for All Dashboard Tabs', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Enable/Disable Specific Dashboard Sections', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Unique Page Title Customization', 'affiliates-for-woocommerce' ),
                        esc_html__( 'Seamless Integration with WooCommerce Endpoints', 'affiliates-for-woocommerce' ),
                        esc_html__( 'SEO-Friendly Affiliate Link Structure', 'affiliates-for-woocommerce' ),
                    ],
                    'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Endpoints Page&utm_campaign=Affiliates for WooCommerce Pro',
                ]
            );
		}

		/**
		 * Get License Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_license_configuration_template() {
			new Admin\Configuration\DDWCAF_License_Configuration_Template( $this->ddwcaf_configuration );
		}

		/**
		 * Get Layout Configuration Template
		 *
		 * @return void
		 */
		public function ddwcaf_get_layout_configuration_template() {
			new Admin\Configuration\DDWCAF_Layout_Configuration_Template( $this->ddwcaf_configuration );
		}

        /**
         * Enqueue admin scripts function
         *
         * @return scripts
         */
        public function ddwcaf_enqueue_admin_scripts() {
            global $post_type;

            if ( $this->dashboard->is_a_plugin_page() ) {
                $menu   = ! empty( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
                $action = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

                if ( empty( $menu ) || 'dashboard' === $menu ) {
                    wp_enqueue_style( 'ddwcaf-dashboard-style', DDWCAF_PLUGIN_URL . 'assets/css/dashboard.css', [ \DevDiggers\Framework\Includes\DDFW_Assets::$framework_css_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/css/dashboard.css' ) );
                    
                    wp_enqueue_script( 'chart-js', DDWCAF_PLUGIN_URL . 'assets/js/chart.js', [ 'jquery' ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/chart.js' ) );
                    
                    wp_register_script( 'ddwcaf-dashboard-script', DDWCAF_PLUGIN_URL . 'assets/js/dashboard.js', [ 'chart-js' ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/dashboard.js' ) );
                } elseif ( 'affiliates' === $menu && 'view' === $action ) {
                    // Enqueue Manage Affiliate assets
                    wp_enqueue_style( 'ddwcaf-manage-affiliate-style', DDWCAF_PLUGIN_URL . 'assets/css/manage-affiliate.css', [ \DevDiggers\Framework\Includes\DDFW_Assets::$framework_css_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/css/manage-affiliate.css' ) );

                    wp_enqueue_script( 'chart-js', DDWCAF_PLUGIN_URL . 'assets/js/chart.js', [ 'jquery' ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/chart.js' ) );
                    
                    wp_enqueue_script( 'ddwcaf-admin-script', DDWCAF_PLUGIN_URL . 'assets/js/admin.js', [ 'wp-util', \DevDiggers\Framework\Includes\DDFW_Assets::$framework_js_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/admin.js' ) );

                    wp_register_script( 'ddwcaf-manage-affiliate-script', DDWCAF_PLUGIN_URL . 'assets/js/manage-affiliate.js', [ \DevDiggers\Framework\Includes\DDFW_Assets::$framework_js_handle, 'chart-js' ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/manage-affiliate.js' ) );
                } elseif ( 'payouts' === $menu && ! empty( $_GET['tab'] ) && 'manage' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
                    // Enqueue Manage Payout assets (reuses manage-affiliate styles)
                    wp_enqueue_style( 'ddwcaf-manage-affiliate-style', DDWCAF_PLUGIN_URL . 'assets/css/manage-affiliate.css', [], filemtime( DDWCAF_PLUGIN_FILE . 'assets/css/manage-affiliate.css' ) );

                    wp_enqueue_style( 'ddwcaf-admin-style', DDWCAF_PLUGIN_URL . 'assets/css/admin.css', [ \DevDiggers\Framework\Includes\DDFW_Assets::$framework_css_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/css/admin.css' ) );

                    wp_enqueue_script( 'ddwcaf-admin-script', DDWCAF_PLUGIN_URL . 'assets/js/admin.js', [ 'wp-util', \DevDiggers\Framework\Includes\DDFW_Assets::$framework_js_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/admin.js' ) );

                    wp_localize_script(
                        'ddwcaf-admin-script',
                        'ddwcafAdminObject',
                        [
                            'ajax' => [
                                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                                'ajaxNonce' => wp_create_nonce( 'ddwcaf-nonce' ),
                            ],
                            'i18n' => [
                                'createPayoutError' => esc_html__( 'You need to select affiliates first!!', 'affiliates-for-woocommerce' ),
                            ],
                            'ddwcaf_configuration' => $this->ddwcaf_configuration,
                            'site_url'             => site_url(),
                        ]
                    );
                } else {
                    wp_enqueue_style( 'ddwcaf-admin-style', DDWCAF_PLUGIN_URL . 'assets/css/admin.css', [ \DevDiggers\Framework\Includes\DDFW_Assets::$framework_css_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/css/admin.css' ) );

                    wp_enqueue_script( 'ddwcaf-admin-script', DDWCAF_PLUGIN_URL . 'assets/js/admin.js', [ 'wp-util', \DevDiggers\Framework\Includes\DDFW_Assets::$framework_js_handle ], filemtime( DDWCAF_PLUGIN_FILE . 'assets/js/admin.js' ) );

                    wp_localize_script(
                        'ddwcaf-admin-script',
                        'ddwcafAdminObject',
                        [
                            'ajax' => [
                                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                                'ajaxNonce' => wp_create_nonce( 'ddwcaf-nonce' ),
                            ],
                            'i18n' => [
                                'createPayoutError' => esc_html__( 'You need to select affiliates first!!', 'affiliates-for-woocommerce' ),
                            ],
                            'ddwcaf_configuration' => $this->ddwcaf_configuration,
                            'site_url'             => site_url(),
                        ]
                    );
                }
            }
        }
    }
}
