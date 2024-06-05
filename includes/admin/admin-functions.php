<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all admin end action callbacks.
 */

namespace DDWCAffiliates\Includes\Admin;

use DDWCAffiliates\Templates\Admin;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Admin_Functions' ) ) {
    /**
     * Admin Functions Class
     */
    class DDWCAF_Admin_Functions {
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
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            $this->ddwcaf_configuration = $ddwcaf_configuration;
            $this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
        }

        /**
         * Add admin notices function
         *
         * @return void
         */
        public function ddwcaf_add_admin_notices() {
            ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    /* translators: %s for a tag */
                    echo sprintf( esc_html__( 'Want more advanced functionalities in your Affiliates for WooCommerce plugin, upgrade it by purchasing the %s.', 'affiliates-for-woocommerce' ), '<a href="//devdiggers.com/product/woocommerce-affiliates/" target="_blank">' . esc_html__( 'pro version', 'affiliates-for-woocommerce' ) . '</a>' );
                    ?>
                </p>
            </div>
            <?php
        }

        /**
         * Add Admin menu function
         *
         * @return void
         */
        public function ddwcaf_add_dashboard_menu() {
            $menu_capability = apply_filters( 'ddwcaf_modify_admin_menu_capability', 'manage_woocommerce' );

            add_menu_page( esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ), $menu_capability, 'affiliates-for-woocommerce-management', [ $this, 'ddwcaf_get_analytics' ], 'dashicons-networking', 56 );

            add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Analytics | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Analytics', 'affiliates-for-woocommerce' ), $menu_capability, 'affiliates-for-woocommerce-management', [ $this, 'ddwcaf_get_analytics' ] );

            $hook1 = add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Affiliates | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ), $menu_capability, 'affiliates-for-woocommerce', [ $this, 'ddwcaf_get_affiliates_list' ] );

            $hook2 = add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Commissions | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Commissions', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-commissions', [ $this, 'ddwcaf_get_commissions_list' ] );

            $hook3 = add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Payouts | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Payouts', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-payouts', [ $this, 'ddwcaf_get_payouts_list' ] );

            $hook4 = add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Top Products | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Top Products', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-top-products', [ $this, 'ddwcaf_get_top_products_list' ] );

            $hook5 = add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Visits | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Visits', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-visits', [ $this, 'ddwcaf_get_visits_list' ] );

            add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Rules | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Rules', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-rules', [ $this, 'ddwcaf_get_rules_template' ] );

            $hook6 = add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Registration Fields | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Registration Fields', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-affiliate-registration-fields', [ $this, 'ddwcaf_get_affiliate_registration_fields_list' ] );

            add_submenu_page( 'affiliates-for-woocommerce-management', esc_html__( 'Configuration | Affiliates', 'affiliates-for-woocommerce' ), esc_html__( 'Configuration', 'affiliates-for-woocommerce' ), $menu_capability, 'ddwcaf-configuration', [ $this, 'ddwcaf_get_configuration' ] );

            do_action( 'ddwcaf_admin_menu_action' );

            add_action( "load-{$hook1}", [ $this, 'ddwcaf_add_screen_options_in_affiliates_list' ] );
            add_action( "load-{$hook2}", [ $this, 'ddwcaf_add_screen_options_in_commissions_list' ] );
            add_action( "load-{$hook3}", [ $this, 'ddwcaf_add_screen_options_in_payouts_list' ] );
            add_action( "load-{$hook4}", [ $this, 'ddwcaf_add_screen_options_in_top_products_list' ] );
            add_action( "load-{$hook5}", [ $this, 'ddwcaf_add_screen_options_in_visits_list' ] );
            add_action( "load-{$hook6}", [ $this, 'ddwcaf_add_screen_options_in_affiliates_registration_fields_list' ] );

            global $submenu;

            if ( ! empty( $submenu[ 'affiliates-for-woocommerce-management' ] ) ) {
                $submenu[ 'affiliates-for-woocommerce-management' ][] = [ esc_html__( 'Extensions', 'affiliates-for-woocommerce' ) . '<i class="dashicons dashicons-external ddwcaf-dashicon-external"></i>', 'manage_woocommerce', '//devdiggers.com/woocommerce-extensions/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Admin Menu&utm_campaign=WooCommerce Extensions' ];
                $submenu[ 'affiliates-for-woocommerce-management' ][] = [ '<span class="ddwcaf-upgrade-submenu">' . esc_html__( 'Upgrade to Pro', 'affiliates-for-woocommerce' ) .  '</span>', 'manage_woocommerce', '//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Free Plugin&utm_medium=Affiliates for WooCommerce Free Plugin Admin Menu&utm_campaign=Go Pro', esc_html__( 'Upgrade to Pro | Affiliates', 'affiliates-for-woocommerce' ), 'ddwcaf-upgrade-submenu-li' ];
            }
        }

        /**
         * Admin head function
         *
         * @return void
         */
        public function ddwcaf_admin_head() {
            ?>
            <script type="text/javascript">
                // Open extensions menu link in the new tab.
                jQuery( document ).ready( function( $ ) {
                    $( "ul#adminmenu a[href*='devdiggers.com']" ).attr( 'target', '_blank' );
                } );
            </script>
            <style>
                .ddwcaf-dashicon-external {
                    font-size: 14px;
                    vertical-align: -2px;
                    height: 10px;
                }
            </style>
            <?php
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
		 * Analytics Submenu
		 *
		 * @return void
		 */
        public function ddwcaf_get_analytics() {
            ?>
            <div class="ddwcaf-pro-container">
                <img src="<?php echo esc_url( DDWCAF_PLUGIN_URL . 'assets/images/analytics.jpg' ); ?>" alt="<?php esc_attr_e( 'Affiliates for WooCommerce Go Pro', 'affiliates-for-woocommerce' ); ?>" />
                <div class="ddwcaf-pro-details">
                    <h2><?php esc_html_e( 'Analytics Report', 'affiliates-for-woocommerce' ); ?></h2>
                    <hr />
                    <h3><?php esc_html_e( 'Want to check the advanced reports of how your affiliates are performing?', 'affiliates-for-woocommerce' ); ?></h3>
                    <ul>
                        <li><?php esc_html_e( 'How much earnings generated by affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much commissions generated for affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much commissions are paid to affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much commissions are unpaid to affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much commissions are refunded from affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much visits are registered from affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much visits are converted from affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'How much is the conversion rate from affiliates and when?', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Compare reports from previous years.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Check all or individual affiliate reports.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Bar and line charts.', 'affiliates-for-woocommerce' ); ?></li>
                    </ul>
                    <a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Analytics Page&utm_campaign=Affiliates for WooCommerce Pro" target="_blank" class="ddwcaf-go-pro-button"><?php esc_html_e( 'Go Pro', 'affiliates-for-woocommerce' ); ?></a>&ensp;
                    <a href="//devdiggers.com/affiliates-for-woocommerce/?utm_source=Affiliates for WooCommerce Doc&utm_medium=Plugin Analytics Page&utm_campaign=Affiliates for WooCommerce Doc" target="_blank"><?php esc_html_e( 'Learn more about Pro', 'affiliates-for-woocommerce' ); ?></php></a>
                </div>
            </div>
            <?php
        }

        /**
		 * Affiliates List Submenu
		 *
		 * @return void
		 */
		public function ddwcaf_get_affiliates_list() {
			?>
			<div class="wrap ddwcaf-affiliates-list-wrap">
            <?php
                if ( ! empty( $_GET[ 'action' ] ) && 'view' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) ) {
                    new Admin\Affiliates\DDWCAF_Manage_Affiliate_Template( $this->ddwcaf_configuration );
				} else {
                    if ( ! empty( $_GET[ 'status' ] ) && 'saved' === sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) ) ) {
                        $this->ddwcaf_print_notification( esc_html__( 'Affiliate saved successfully.', 'affiliates-for-woocommerce' ) );
                    }

                    $obj = new Admin\Affiliates\DDWCAF_Affiliates_List_Template( $this->ddwcaf_configuration );
                    ?>
                    <h1 class="wp-heading-inline"><?php esc_html_e( 'Affiliates', 'affiliates-for-woocommerce' ); ?></h1>
                    <a href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>" class="page-title-action button-primary"><?php esc_html_e( 'Add New', 'affiliates-for-woocommerce' ); ?></a>

                    <hr class="wp-header-end" />
                    <form method="get">
                        <input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( $_REQUEST[ 'page' ] ) : ''; ?>" />
                        <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( $_REQUEST[ 'paged' ] ) : ''; ?>" />
                        <?php
                        wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                        $obj->prepare_items();
                        $obj->search_box( esc_html__( 'Search', 'affiliates-for-woocommerce' ), 'search-id' );
                        $obj->display();
                        ?>
                    </form>
                    <?php
                }
                ?>
			</div>
			<?php
		}

        /**
		 * Commissions List Submenu
		 *
		 * @return void
		 */
		public function ddwcaf_get_commissions_list() {
            wp_enqueue_script( 'wc-orders' );
            wp_enqueue_script( 'wc-backbone-modal' );

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
            <div class="wrap post-type-shop_order ddwcaf-commissions-list-wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Commissions', 'affiliates-for-woocommerce' ); ?></h1>
                <hr class="wp-header-end" />
                <ul class="subsubsub">
                    <?php
                    foreach ( $shows as $key => $value ) {
                        ?>
                        <li class="<?php echo esc_attr( $key ); ?>">
                            <a class="<?php echo esc_attr( $current_show === $key ? 'current' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $_REQUEST[ 'page' ] . '&show=' . $key ) ); ?>"><?php echo esc_html( $value ); ?></a>
                        </li>
                        <?php
                        if ( 'refunded' !== $key ) {
                            echo esc_html( ' | ' );
                        }
                    }
                    ?>
                </ul>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( $_REQUEST[ 'page' ] ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( $_REQUEST[ 'paged' ] ) : ''; ?>" />
                    <input type="hidden" name="payout-id" value="<?php echo isset( $_REQUEST[ 'payout-id' ] ) ? esc_attr( $_REQUEST[ 'payout-id' ] ) : ''; ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_REQUEST[ 'affiliate-id' ] ) ? esc_attr( $_REQUEST[ 'affiliate-id' ] ) : ''; ?>" />
                    <input type="hidden" name="product-id" value="<?php echo isset( $_REQUEST[ 'product-id' ] ) ? esc_attr( $_REQUEST[ 'product-id' ] ) : ''; ?>" />
                    <input type="hidden" name="from-date" value="<?php echo isset( $_REQUEST[ 'from-date' ] ) ? esc_attr( $_REQUEST[ 'from-date' ] ) : ''; ?>" />
                    <input type="hidden" name="end-date" value="<?php echo isset( $_REQUEST[ 'end-date' ] ) ? esc_attr( $_REQUEST[ 'end-date' ] ) : ''; ?>" />
                    <input type="hidden" name="show" value="<?php echo isset( $_REQUEST[ 'show' ] ) ? esc_attr( $_REQUEST[ 'show' ] ) : ''; ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->search_box( esc_html__( 'Search by ID', 'affiliates-for-woocommerce' ), 'search-id' );
                    $obj->display();
                    $order_edit_url_placeholder =
                        wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
                        ? esc_url( admin_url( 'admin.php?page=wc-orders&action=edit' ) ) . '&id={{ data.data.id }}'
                        : esc_url( admin_url( 'post.php?action=edit' ) ) . '&post={{ data.data.id }}';

                    ?>
                    <script type="text/template" id="tmpl-wc-modal-view-order">
                        <div class="wc-backbone-modal wc-order-preview">
                            <div class="wc-backbone-modal-content">
                                <section class="wc-backbone-modal-main" role="main">
                                    <header class="wc-backbone-modal-header">
                                        <mark class="order-status status-{{ data.status }}"><span>{{ data.status_name }}</span></mark>
                                        <?php /* translators: %s: order ID */ ?>
                                        <h1><?php echo esc_html( sprintf( __( 'Order #%s', 'affiliates-for-woocommerce' ), '{{ data.order_number }}' ) ); ?></h1>
                                        <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                                            <span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'affiliates-for-woocommerce' ); ?></span>
                                        </button>
                                    </header>
                                    <article>
                                        <?php do_action( 'woocommerce_admin_order_preview_start' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

                                        <div class="wc-order-preview-addresses">
                                            <div class="wc-order-preview-address">
                                                <h2><?php esc_html_e( 'Billing details', 'affiliates-for-woocommerce' ); ?></h2>
                                                {{{ data.formatted_billing_address }}}

                                                <# if ( data.data.billing.email ) { #>
                                                    <strong><?php esc_html_e( 'Email', 'affiliates-for-woocommerce' ); ?></strong>
                                                    <a href="mailto:{{ data.data.billing.email }}">{{ data.data.billing.email }}</a>
                                                <# } #>

                                                <# if ( data.data.billing.phone ) { #>
                                                    <strong><?php esc_html_e( 'Phone', 'affiliates-for-woocommerce' ); ?></strong>
                                                    <a href="tel:{{ data.data.billing.phone }}">{{ data.data.billing.phone }}</a>
                                                <# } #>

                                                <# if ( data.payment_via ) { #>
                                                    <strong><?php esc_html_e( 'Payment via', 'affiliates-for-woocommerce' ); ?></strong>
                                                    {{{ data.payment_via }}}
                                                <# } #>
                                            </div>
                                            <# if ( data.needs_shipping ) { #>
                                                <div class="wc-order-preview-address">
                                                    <h2><?php esc_html_e( 'Shipping details', 'affiliates-for-woocommerce' ); ?></h2>
                                                    <# if ( data.ship_to_billing ) { #>
                                                        {{{ data.formatted_billing_address }}}
                                                    <# } else { #>
                                                        <a href="{{ data.shipping_address_map_url }}" target="_blank">{{{ data.formatted_shipping_address }}}</a>
                                                    <# } #>

                                                    <# if ( data.shipping_via ) { #>
                                                        <strong><?php esc_html_e( 'Shipping method', 'affiliates-for-woocommerce' ); ?></strong>
                                                        {{ data.shipping_via }}
                                                    <# } #>
                                                </div>
                                            <# } #>

                                            <# if ( data.data.customer_note ) { #>
                                                <div class="wc-order-preview-note">
                                                    <strong><?php esc_html_e( 'Note', 'affiliates-for-woocommerce' ); ?></strong>
                                                    {{ data.data.customer_note }}
                                                </div>
                                            <# } #>
                                        </div>

                                        {{{ data.item_html }}}

                                        <?php do_action( 'woocommerce_admin_order_preview_end' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>
                                    </article>
                                    <footer>
                                        <div class="inner">
                                            {{{ data.actions_html }}}

                                            <a class="button button-primary button-large" aria-label="<?php esc_attr_e( 'Edit this order', 'affiliates-for-woocommerce' ); ?>" href="<?php echo $order_edit_url_placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Edit', 'affiliates-for-woocommerce' ); ?></a>
                                        </div>
                                    </footer>
                                </section>
                            </div>
                        </div>
                        <div class="wc-backbone-modal-backdrop modal-close"></div>
                    </script>
                </form>
            </div>
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
                    ?>
                    <div class="wrap">
                        <hr class="wp-header-end" />

                        <div class="ddwcaf-pro-container">
                            <img src="<?php echo esc_url( DDWCAF_PLUGIN_URL . 'assets/images/payout-details.jpg' ); ?>" alt="<?php esc_attr_e( 'Affiliates for WooCommerce Go Pro', 'affiliates-for-woocommerce' ); ?>" />
                            <div class="ddwcaf-pro-details">
                                <h2><?php esc_html_e( 'Payout Details', 'affiliates-for-woocommerce' ); ?></h2>
                                <hr />
                                <h3><?php esc_html_e( 'Want to check payout details?', 'affiliates-for-woocommerce' ); ?></h3>
                                <ul>
                                    <li><?php esc_html_e( 'Check withdrawal details.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Check payout created and completed dates.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Check payout status.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Update payout status.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Add and update payout transaction ID.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Automatic transaction ID generation in case of wallet withdrawal.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Check payout affiliate details.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Check commission details linked with the payout.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Check order details linked with the payout.', 'affiliates-for-woocommerce' ); ?></li>
                                    <li><?php esc_html_e( 'Check all affiliate earnings.', 'affiliates-for-woocommerce' ); ?></li>
                                </ul>
                                <a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Payout Details Page&utm_campaign=Affiliates for WooCommerce Pro" target="_blank" class="ddwcaf-go-pro-button"><?php esc_html_e( 'Go Pro', 'affiliates-for-woocommerce' ); ?></a>&ensp;
                                <a href="//devdiggers.com/affiliates-for-woocommerce/?utm_source=Affiliates for WooCommerce Doc&utm_medium=Plugin Payout Details Page&utm_campaign=Affiliates for WooCommerce Doc" target="_blank"><?php esc_html_e( 'Learn more about Pro', 'affiliates-for-woocommerce' ); ?></php></a>
                            </div>
                        </div>
                    </div>
                    <?php
                } elseif ( 'create' === sanitize_text_field( wp_unslash( $_GET[ 'tab' ] ) ) ) {
                    ?>
                    <div class="ddwcaf-pro-container">
                        <img src="<?php echo esc_url( DDWCAF_PLUGIN_URL . 'assets/images/create-payout.jpg' ); ?>" alt="<?php esc_attr_e( 'WooCommerce Analytics Go Pro', 'affiliates-for-woocommerce' ); ?>" />
                        <div class="ddwcaf-pro-details">
                            <h2><?php esc_html_e( 'Create Payout', 'affiliates-for-woocommerce' ); ?></h2>
                            <hr />
                            <h3><?php esc_html_e( 'Want to create affiliate payouts for pending commission?', 'affiliates-for-woocommerce' ); ?></h3>
                            <ul>
                                <li><?php esc_html_e( 'Select specific affiliates for payout.', 'affiliates-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Select all affiliates for payout.', 'affiliates-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Select payout reference as "Manually by Admin", "Requested by Affiliate" or "Automatic Monthly Payout".', 'affiliates-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Create payout for any specific date or for all pending commissions.', 'affiliates-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Batch processing for load balancing in order to create payouts.', 'affiliates-for-woocommerce' ); ?></li>
                                <li><?php esc_html_e( 'Tested with 10 lakhs affiliates and worked smoothly.', 'affiliates-for-woocommerce' ); ?></li>
                            </ul>
                            <a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Create Payout Page&utm_campaign=Affiliates for WooCommerce Pro" target="_blank" class="ddwcaf-go-pro-button"><?php esc_html_e( 'Go Pro', 'affiliates-for-woocommerce' ); ?></a>&ensp;
                            <a href="//devdiggers.com/affiliates-for-woocommerce/?utm_source=Affiliates for WooCommerce Doc&utm_medium=Plugin Create Payout Page&utm_campaign=Affiliates for WooCommerce Doc" target="_blank"><?php esc_html_e( 'Learn more about Pro', 'affiliates-for-woocommerce' ); ?></php></a>
                        </div>
                    </div>
                    <?php
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
                <div class="wrap post-type-shop_order ddwcaf-commissions-list-wrap ddwcaf-payouts-list-wrap">
                    <h1 class="wp-heading-inline"><?php esc_html_e( 'Payouts', 'affiliates-for-woocommerce' ); ?></h1>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $_REQUEST[ 'page' ] . '&tab=create' ) ); ?>" class="page-title-action button-primary"><?php esc_html_e( 'Create', 'affiliates-for-woocommerce' ); ?></a>
                    <hr class="wp-header-end" />
                    <ul class="subsubsub">
                        <?php
                        foreach ( $shows as $key => $value ) {
                            ?>
                            <li class="<?php echo esc_attr( $key ); ?>">
                                <a class="<?php echo esc_attr( $current_show === $key ? 'current' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $_REQUEST[ 'page' ] . '&show=' . $key ) ); ?>"><?php echo esc_html( $value ); ?></a>
                            </li>
                            <?php
                            if ( 'cancelled' !== $key ) {
                                echo esc_html( ' | ' );
                            }
                        }
                        ?>
                    </ul>
                    <form method="get">
                        <input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( $_REQUEST[ 'page' ] ) : ''; ?>" />
                        <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( $_REQUEST[ 'paged' ] ) : ''; ?>" />
                        <input type="hidden" name="payout-id" value="<?php echo isset( $_REQUEST[ 'payout-id' ] ) ? esc_attr( $_REQUEST[ 'payout-id' ] ) : ''; ?>" />
                        <input type="hidden" name="affiliate-id" value="<?php echo isset( $_REQUEST[ 'affiliate-id' ] ) ? esc_attr( $_REQUEST[ 'affiliate-id' ] ) : ''; ?>" />
                        <input type="hidden" name="from-date" value="<?php echo isset( $_REQUEST[ 'from-date' ] ) ? esc_attr( $_REQUEST[ 'from-date' ] ) : ''; ?>" />
                        <input type="hidden" name="end-date" value="<?php echo isset( $_REQUEST[ 'end-date' ] ) ? esc_attr( $_REQUEST[ 'end-date' ] ) : ''; ?>" />
                        <input type="hidden" name="show" value="<?php echo isset( $_REQUEST[ 'show' ] ) ? esc_attr( $_REQUEST[ 'show' ] ) : ''; ?>" />
                        <?php
                        wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                        $obj->prepare_items();
                        $obj->search_box( esc_html__( 'Search by ID', 'affiliates-for-woocommerce' ), 'search-id' );
                        $obj->display();
                        ?>
                    </form>
                </div>
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
            <div class="wrap post-type-shop_order ddwcaf-commissions-list-wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Top Products', 'affiliates-for-woocommerce' ); ?></h1>
                <hr class="wp-header-end" />
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( $_REQUEST[ 'page' ] ) : ''; ?>" />
                    <input type="hidden" name="order" value="<?php echo isset( $_REQUEST[ 'order' ] ) ? esc_attr( $_REQUEST[ 'order' ] ) : ''; ?>" />
                    <input type="hidden" name="orderby" value="<?php echo isset( $_REQUEST[ 'orderby' ] ) ? esc_attr( $_REQUEST[ 'orderby' ] ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( $_REQUEST[ 'paged' ] ) : ''; ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_REQUEST[ 'affiliate-id' ] ) ? esc_attr( $_REQUEST[ 'affiliate-id' ] ) : ''; ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->display();
                    ?>
                </form>
            </div>
			<?php
		}

        /**
		 * Visits List Submenu
		 *
		 * @return void
		 */
		public function ddwcaf_get_visits_list() {
            wp_enqueue_script( 'wc-orders' );
            wp_enqueue_script( 'wc-backbone-modal' );

            $obj = new Admin\Visits\DDWCAF_Visits_List_Template( $this->ddwcaf_configuration );

            $shows = [
                ''              => esc_html__( 'All', 'affiliates-for-woocommerce' ),
                'converted'     => esc_html__( 'Converted', 'affiliates-for-woocommerce' ),
                'not_converted' => esc_html__( 'Not Converted', 'affiliates-for-woocommerce' ),
            ];

            $current_show = ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '';
			?>
            <div class="wrap post-type-shop_order ddwcaf-commissions-list-wrap ddwcaf-visits-list-wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Visits', 'affiliates-for-woocommerce' ); ?></h1>
                <hr class="wp-header-end" />
                <ul class="subsubsub">
                    <?php
                    foreach ( $shows as $key => $value ) {
                        ?>
                        <li class="<?php echo esc_attr( $key ); ?>">
                            <a class="<?php echo esc_attr( $current_show === $key ? 'current' : '' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $_REQUEST[ 'page' ] . '&show=' . $key ) ); ?>"><?php echo esc_html( $value ); ?></a>
                        </li>
                        <?php
                        if ( 'not_converted' !== $key ) {
                            echo esc_html( ' | ' );
                        }
                    }
                    ?>
                </ul>
                <form method="get" id="plugin-fw-wc">
                    <input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( $_REQUEST[ 'page' ] ) : ''; ?>" />
                    <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( $_REQUEST[ 'paged' ] ) : ''; ?>" />
                    <input type="hidden" name="payout-id" value="<?php echo isset( $_REQUEST[ 'payout-id' ] ) ? esc_attr( $_REQUEST[ 'payout-id' ] ) : ''; ?>" />
                    <input type="hidden" name="affiliate-id" value="<?php echo isset( $_REQUEST[ 'affiliate-id' ] ) ? esc_attr( $_REQUEST[ 'affiliate-id' ] ) : ''; ?>" />
                    <input type="hidden" name="from-date" value="<?php echo isset( $_REQUEST[ 'from-date' ] ) ? esc_attr( $_REQUEST[ 'from-date' ] ) : ''; ?>" />
                    <input type="hidden" name="end-date" value="<?php echo isset( $_REQUEST[ 'end-date' ] ) ? esc_attr( $_REQUEST[ 'end-date' ] ) : ''; ?>" />
                    <input type="hidden" name="show" value="<?php echo isset( $_REQUEST[ 'show' ] ) ? esc_attr( $_REQUEST[ 'show' ] ) : ''; ?>" />
                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    $obj->prepare_items();
                    $obj->search_box( esc_html__( 'Search by ID', 'affiliates-for-woocommerce' ), 'search-id' );
                    $obj->display();
                    $order_edit_url_placeholder =
                        wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
                        ? esc_url( admin_url( 'admin.php?page=wc-orders&action=edit' ) ) . '&id={{ data.data.id }}'
                        : esc_url( admin_url( 'post.php?action=edit' ) ) . '&post={{ data.data.id }}';

                    ?>
                    <script type="text/template" id="tmpl-wc-modal-view-order">
                        <div class="wc-backbone-modal wc-order-preview">
                            <div class="wc-backbone-modal-content">
                                <section class="wc-backbone-modal-main" role="main">
                                    <header class="wc-backbone-modal-header">
                                        <mark class="order-status status-{{ data.status }}"><span>{{ data.status_name }}</span></mark>
                                        <?php /* translators: %s: order ID */ ?>
                                        <h1><?php echo esc_html( sprintf( __( 'Order #%s', 'affiliates-for-woocommerce' ), '{{ data.order_number }}' ) ); ?></h1>
                                        <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                                            <span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'affiliates-for-woocommerce' ); ?></span>
                                        </button>
                                    </header>
                                    <article>
                                        <?php do_action( 'woocommerce_admin_order_preview_start' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

                                        <div class="wc-order-preview-addresses">
                                            <div class="wc-order-preview-address">
                                                <h2><?php esc_html_e( 'Billing details', 'affiliates-for-woocommerce' ); ?></h2>
                                                {{{ data.formatted_billing_address }}}

                                                <# if ( data.data.billing.email ) { #>
                                                    <strong><?php esc_html_e( 'Email', 'affiliates-for-woocommerce' ); ?></strong>
                                                    <a href="mailto:{{ data.data.billing.email }}">{{ data.data.billing.email }}</a>
                                                <# } #>

                                                <# if ( data.data.billing.phone ) { #>
                                                    <strong><?php esc_html_e( 'Phone', 'affiliates-for-woocommerce' ); ?></strong>
                                                    <a href="tel:{{ data.data.billing.phone }}">{{ data.data.billing.phone }}</a>
                                                <# } #>

                                                <# if ( data.payment_via ) { #>
                                                    <strong><?php esc_html_e( 'Payment via', 'affiliates-for-woocommerce' ); ?></strong>
                                                    {{{ data.payment_via }}}
                                                <# } #>
                                            </div>
                                            <# if ( data.needs_shipping ) { #>
                                                <div class="wc-order-preview-address">
                                                    <h2><?php esc_html_e( 'Shipping details', 'affiliates-for-woocommerce' ); ?></h2>
                                                    <# if ( data.ship_to_billing ) { #>
                                                        {{{ data.formatted_billing_address }}}
                                                    <# } else { #>
                                                        <a href="{{ data.shipping_address_map_url }}" target="_blank">{{{ data.formatted_shipping_address }}}</a>
                                                    <# } #>

                                                    <# if ( data.shipping_via ) { #>
                                                        <strong><?php esc_html_e( 'Shipping method', 'affiliates-for-woocommerce' ); ?></strong>
                                                        {{ data.shipping_via }}
                                                    <# } #>
                                                </div>
                                            <# } #>

                                            <# if ( data.data.customer_note ) { #>
                                                <div class="wc-order-preview-note">
                                                    <strong><?php esc_html_e( 'Note', 'affiliates-for-woocommerce' ); ?></strong>
                                                    {{ data.data.customer_note }}
                                                </div>
                                            <# } #>
                                        </div>

                                        {{{ data.item_html }}}

                                        <?php do_action( 'woocommerce_admin_order_preview_end' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>
                                    </article>
                                    <footer>
                                        <div class="inner">
                                            {{{ data.actions_html }}}

                                            <a class="button button-primary button-large" aria-label="<?php esc_attr_e( 'Edit this order', 'affiliates-for-woocommerce' ); ?>" href="<?php echo $order_edit_url_placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Edit', 'affiliates-for-woocommerce' ); ?></a>
                                        </div>
                                    </footer>
                                </section>
                            </div>
                        </div>
                        <div class="wc-backbone-modal-backdrop modal-close"></div>
                    </script>
                </form>
            </div>
			<?php
		}

        /**
		 * Rules Template Submenu function
		 *
		 * @return void
		 */
		public function ddwcaf_get_rules_template() {
            ?>
            <div class="ddwcaf-pro-container">
                <img src="<?php echo esc_url( DDWCAF_PLUGIN_URL . 'assets/images/rules.jpg' ); ?>" alt="<?php esc_attr_e( 'Affiliates for WooCommerce Go Pro', 'affiliates-for-woocommerce' ); ?>" />
                <div class="ddwcaf-pro-details">
                    <h2><?php esc_html_e( 'Commission Rules', 'affiliates-for-woocommerce' ); ?></h2>
                    <hr />
                    <h3><?php esc_html_e( 'Want to create advanced commission rules?', 'affiliates-for-woocommerce' ); ?></h3>
                    <ul>
                        <li><?php esc_html_e( 'Configure rules to override the global commission rate.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Configure commission rules for specific products.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Configure commission rules for specific product categories.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Configure commission rules for specific user roles.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Fixed or percentage commission calculation.', 'affiliates-for-woocommerce' ); ?></li>
                        <li><?php esc_html_e( 'Prioritize commission rules.', 'affiliates-for-woocommerce' ); ?></li>
                    </ul>
                    <a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Commission Rules Page&utm_campaign=Affiliates for WooCommerce Pro" target="_blank" class="ddwcaf-go-pro-button"><?php esc_html_e( 'Go Pro', 'affiliates-for-woocommerce' ); ?></a>&ensp;
                    <a href="//devdiggers.com/affiliates-for-woocommerce/?utm_source=Affiliates for WooCommerce Doc&utm_medium=Plugin Commission Rules Page&utm_campaign=Affiliates for WooCommerce Doc" target="_blank"><?php esc_html_e( 'Learn more about Pro', 'affiliates-for-woocommerce' ); ?></php></a>
                </div>
            </div>
            <?php
		}

        /**
         * Get affiliate registration fields list function
         *
         * @return void
         */
        public function ddwcaf_get_affiliate_registration_fields_list() {
            if ( ! empty( $_GET[ 'action' ] ) && ( 'add' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) || 'edit' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) ) ) {
                ?>
                <div class="ddwcaf-pro-container">
                    <img src="<?php echo esc_url( DDWCAF_PLUGIN_URL . 'assets/images/add-registration-field.jpg' ); ?>" alt="<?php esc_attr_e( 'Affiliates for WooCommerce Go Pro', 'affiliates-for-woocommerce' ); ?>" />
                    <div class="ddwcaf-pro-details">
                        <h2><?php esc_html_e( 'Add/Edit Registration Field', 'affiliates-for-woocommerce' ); ?></h2>
                        <hr />
                        <h3><?php esc_html_e( 'Want to add more fields on the affiliate registration form or want to edit it?', 'affiliates-for-woocommerce' ); ?></h3>
                        <ul>
                            <li><?php esc_html_e( 'Add unlimited fields to the registration with fully customization fields.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Label of the field.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Type of the field such as text, number, email, password, select, checkbox, textarea, radio, date, country and state.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Name of the field.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Placeholder of the field.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Placeholder of the field.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Options of the field for select and radio type fields.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Postiion of the field in the affiliate registration form.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Extra CSS classes for the field.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Description for the affiliate to show below the field.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Field can be mandatory/required or not.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Field can be editable from the affiliate dashboard.', 'affiliates-for-woocommerce' ); ?></li>
                            <li><?php esc_html_e( 'Field can be active or inactive.', 'affiliates-for-woocommerce' ); ?></li>
                        </ul>
                        <a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugin Add/Edit Registration Field Page&utm_campaign=Affiliates for WooCommerce Pro" target="_blank" class="ddwcaf-go-pro-button"><?php esc_html_e( 'Go Pro', 'affiliates-for-woocommerce' ); ?></a>&ensp;
                        <a href="//devdiggers.com/affiliates-for-woocommerce/?utm_source=Affiliates for WooCommerce Doc&utm_medium=Plugin Add/Edit Registration Field Page&utm_campaign=Affiliates for WooCommerce Doc" target="_blank"><?php esc_html_e( 'Learn more about Pro', 'affiliates-for-woocommerce' ); ?></php></a>
                    </div>
                </div>
                <?php
            } else {
                if ( ! empty( $_GET[ 'status' ] ) && 'saved' === sanitize_text_field( wp_unslash(  $_GET[ 'status' ] ) ) ) {
                    $this->ddwcaf_print_notification( esc_html__( 'Field saved successfully.', 'affiliates-for-woocommerce' ) );
                }
                if ( ! empty( $_POST[ 'ddwcaf_reset_affiliate_registration_fields' ] ) && ! empty( $_POST[ 'ddwcaf_nonce' ] ) && wp_verify_nonce( $_POST[ 'ddwcaf_nonce' ], 'ddwcaf_nonce_action' ) ) {
                    $this->affiliate_helper->ddwcaf_delete_affiliate_registration_fields();
                    $this->ddwcaf_print_notification( esc_html__( 'Registration fields are reset to default now.', 'affiliates-for-woocommerce' ) );
                }

                $this->ddwcaf_print_notification( sprintf( esc_html__( 'To show this form anywhere on the front end, use this shortcode %s. Also, registration form will get displayed on the affiliate dashboard page as well.', 'affiliates-for-woocommerce' ), '<strong>' . esc_html( $this->ddwcaf_configuration[ 'affiliate_registration_form_shortcode' ] ) . '</strong>' ), 'info' );

                $obj = new Admin\Registration\DDWCAF_Affiliate_Registration_Fields_Template( $this->ddwcaf_configuration );
                ?>
                <div class="wrap">

                    <h1 class="wp-heading-inline"><?php esc_html_e( 'Affiliates Registration Fields', 'affiliates-for-woocommerce' ); ?></h1>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $_REQUEST[ 'page' ] . '&action=add' ) ); ?>" class="page-title-action button-primary"><?php esc_html_e( 'Add New', 'affiliates-for-woocommerce' ); ?></a>

                    <form method="post" class="ddwcaf-inline-block">
                        <?php wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' ); ?>
                        <input type="submit" class="page-title-action button-secondary ddwcaf-vertical-align-unset" name="ddwcaf_reset_affiliate_registration_fields" value="<?php esc_attr_e( 'Restore Defaults', 'affiliates-for-woocommerce' ); ?>" />
                    </form>

                    <hr class="wp-header-end" />
                    <form method="get">
                        <input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( $_REQUEST[ 'page' ] ) : ''; ?>" />
                        <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( $_REQUEST[ 'paged' ] ) : ''; ?>" />
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
         * Configuration Template function
         *
         * @return void
         */
        public function ddwcaf_get_configuration() {
            new Admin\Configuration\DDWCAF_Configuration_Template( $this->ddwcaf_configuration );
        }

        /**
		 * Set Options.
		 *
		 * @param string $status Status.
		 * @param string $option Option.
		 * @param string $value Option Value.
		 * 
		 * @return string
		 */
		function ddwcaf_set_options( $status, $option, $value ) {
			$options = [ 'ddwcaf_affiliates_per_page', 'ddwcaf_commissions_per_page', 'ddwcaf_payouts_per_page', 'ddwcaf_top_products_per_page', 'ddwcaf_visits_per_page', 'ddwcaf_affiliates_registration_fields_per_page' ];

			return in_array( $option, $options, true ) ? $value : $status;
		}

        /**
		 * Set screen ids
		 *
		 * @param array $ids
		 * @return array
		 */
		public function ddwcaf_set_wc_screen_ids( $ids ) {
            $screen_id  = sanitize_title( esc_html__( 'Affiliates', 'affiliates-for-woocommerce' ) );
			$screen_ids = [
                'toplevel_page_' . $screen_id,
                'toplevel_page_affiliates-for-woocommerce-management',
				$screen_id . '_page_affiliates-for-woocommerce',
				$screen_id . '_page_ddwcaf-commissions',
				$screen_id . '_page_ddwcaf-payouts',
				$screen_id . '_page_ddwcaf-top-products',
				$screen_id . '_page_ddwcaf-visits',
				$screen_id . '_page_ddwcaf-rules',
				$screen_id . '_page_ddwcaf-affiliate-registration-fields',
				$screen_id . '_page_ddwcaf-configuration',
			];
			array_push( $ids, ...$screen_ids );
			return $ids;
		}

        /**
         * Change the admin footer text function.
         *
         * @param  string $footer_text text to be rendered in the footer.
         * @return string
         */
        public function ddwcaf_set_admin_footer_text( $footer_text ) {
            if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_get_screen_ids' ) ) {
                return $footer_text;
            }
            $current_screen = get_current_screen();
            $wc_pages       = wc_get_screen_ids();

            // Set only WC pages.
            $wc_pages = array_diff( $wc_pages, array( 'profile', 'user-edit' ) );

            // Check to make sure we're on a plugin page.
            if ( isset( $current_screen->id ) && apply_filters( 'woocommerce_display_admin_footer_text', in_array( $current_screen->id, $wc_pages, true ) ) ) {
                // Change the footer text.
                $footer_text = sprintf(
                    /* translators: %s for a tag */
                    esc_html__( 'If you really like our plugin, please leave us a %s rating, we\'ll really appreciate it.', 'affiliates-for-woocommerce' ), '<a href="//wordpress.org/support/plugin/woocommerce-affiliates/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>'
                );
            }

            return $footer_text;
        }

        /**
         * Register settings function
         *
         * @return settings
         */
        public function ddwcaf_register_settings() {
            // General configuration fields
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_enabled' );
            // register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_default_affiliate_status' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_user_roles' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_fields_enabled_on_woocommerce_registration' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_affiliate_dashboard_page_id' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_enable_widgets_affiliate_dashboard_page' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_primary_color' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_default_affiliate_dashboard_page' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_my_account_enabled' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_enable_widgets_my_account_endpoint' );

            // Referrals configuration fields
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_query_variable_name' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_referral_cookie_name' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_referral_cookie_expires' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_register_visits_enabled' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_referral_social_share_options' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_social_share_title' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_social_share_text' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_pinterest_image_url' );

            // Commissions configuration fields
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_default_commission_rate' );
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_exclude_taxes_enabled' );
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_exclude_discounts_enabled' );
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_withdrawal_methods' );

            // Shortcode configuration fields
            register_setting( 'ddwcaf-shortcodes-configuration-fields', '_ddwcaf_affiliate_registration_form_shortcode' );
            register_setting( 'ddwcaf-shortcodes-configuration-fields', '_ddwcaf_affiliate_registration_form_shortcode_content' );
            register_setting( 'ddwcaf-shortcodes-configuration-fields', '_ddwcaf_affiliate_dashboard_shortcode' );
        }

        /**
         * Enqueue admin scripts function
         *
         * @return scripts
         */
        public function ddwcaf_enqueue_admin_scripts() {
            global $post_type, $pagenow;

			$pages = apply_filters( 'ddwcaf_modify_pages_to_enqueue_script_at_admin_end', [ 'affiliates-for-woocommerce-management', 'affiliates-for-woocommerce', 'ddwcaf-commissions', 'ddwcaf-visits', 'ddwcaf-payouts', 'ddwcaf-top-products', 'ddwcaf-rules', 'ddwcaf-affiliate-registration-fields', 'ddwcaf-configuration' ] );

            if ( ( ! empty( $_REQUEST[ 'page' ] ) && in_array( $_REQUEST[ 'page' ], $pages ) ) ) {
                wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', [], DDWCAF_SCRIPT_VERSION );

                wp_enqueue_style( 'ddwcaf-go-pro-style', DDWCAF_PLUGIN_URL . 'assets/css/go-pro.css', [], DDWCAF_SCRIPT_VERSION );

                wp_enqueue_style( 'ddwcaf-admin-style', DDWCAF_PLUGIN_URL . 'assets/css/admin.css', [], DDWCAF_SCRIPT_VERSION );

                wp_enqueue_script( 'ddwcaf-admin-script', DDWCAF_PLUGIN_URL . 'assets/js/admin.js', [ 'select2' ], DDWCAF_SCRIPT_VERSION );

                wp_localize_script(
                    'ddwcaf-admin-script',
                    'ddwcafAdminObject',
                    [
                        'ajax' => [
                            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                            'ajaxNonce' => wp_create_nonce( 'ddwcaf-nonce' ),
                        ],
                        'i18n' => [
                            'pleaseEnter'   => esc_html__( 'Please enter', 'affiliates-for-woocommerce' ),
                            'moreCharacter' => esc_html__( 'or more character', 'affiliates-for-woocommerce' ),
                            'noResult'      => esc_html__( 'No result Found', 'affiliates-for-woocommerce' ),
                        ],
                    ]
                );
            }
            wp_enqueue_style( 'ddwcaf-onboarding-style', DDWCAF_PLUGIN_URL . 'assets/css/onboarding.css', [], DDWCAF_SCRIPT_VERSION );
        }

        /**
         * Add user form fields function
         *
         * @return void
         */
        public function ddwcaf_add_user_form_fields() {
            ?>
            <input type="hidden" name="ddwcaf_from_affiliates_page" value="<?php echo esc_attr( ! empty( $_SERVER[ 'HTTP_REFERER' ] ) && strpos( $_SERVER[ 'HTTP_REFERER' ], 'affiliates-for-woocommerce' ) ?: 0 ); ?>" />
            <?php
        }

        /**
		 * Save user custom data function.
		 *
		 * @param int $user_id
         * @return void
		 */
		public function ddwcaf_save_user_custom_data( $user_id ) {
			if ( ! empty( $_POST[ 'createuser' ] ) && ! empty( $_POST[ 'role' ] ) ) {
                if ( ( is_array( $_POST[ 'role' ] ) && in_array( 'ddwcaf_affiliate', $_POST[ 'role' ], true ) ) || 'ddwcaf_affiliate' === sanitize_text_field( wp_unslash( $_POST[ 'role' ] ) ) ) {
                    $this->affiliate_helper->ddwcaf_update_affiliate_status( $user_id, $this->ddwcaf_configuration[ 'default_affiliate_status' ] );

                    if ( ! empty( $_POST[ 'ddwcaf_from_affiliates_page' ] ) ) {
                        wp_safe_redirect( admin_url( 'admin.php?page=affiliates-for-woocommerce&status=saved' ) );
                        exit();
                    }
                }
			}
		}

        /**
		 * Handle order refund creation
		 *
		 * @param int $refund_id Refund id.
		 * @return void
		 */
        public function ddwcaf_handle_refund_created( $refund_id ) {
            $refund = wc_get_order( $refund_id );

			if ( ! $refund ) {
				return;
			}

			$order = wc_get_order( $refund->get_parent_id() );

			if ( ! $order ) {
				return;
			}

			if ( $order->has_status( 'refunded' ) ) {
				return;
			}

            if ( ! $order->get_meta_data( '_ddwcaf_referral_token' ) ) {
                return;
            }

			$refund_partials   = [];
			$commission_helper = new DDWCAF_Commission_Helper( $this->ddwcaf_configuration );

			foreach ( $refund->get_items() as $item_id => $item ) {
				// retrieve amount refunded.
				$refunded_item = $item->get_meta( '_refunded_item_id' );

				// retrieve commission id for current item.
				try {
					$commission_id = wc_get_order_item_meta( $refunded_item, '_ddwcaf_commission_id' );
				} catch ( Exception $e ) {
					continue;
				}

				// if no commission id is found, continue.
				if ( ! $commission_id ) {
					continue;
				}

				// retrieve commission data for found commission id.
                $commission = $commission_helper->ddwcaf_get_commission_by_id( $commission_id );

				// if no commission is found, continue.
				if ( ! $commission ) {
					continue;
				}

                $product_id   = $item->get_product_id();
                $variation_id = $item->get_variation_id();

                // retrieves current product id.
                $product_id = $variation_id ? $variation_id : $product_id;

                // choose method to retrieve item total.
                $get_item_amount = ! empty( $this->ddwcaf_configuration[ 'exclude_discounts_enabled' ] ) ? 'get_line_total' : 'get_line_subtotal';
                $item_amount     = (float) $refund->$get_item_amount( $item, empty( $this->ddwcaf_configuration[ 'exclude_taxes_enabled' ] ), false );
                $line_total      = abs( $item_amount );

				// calculate amount of the item's refund that affects current commission.
				$refunded_amount = $commission_helper->ddwcaf_calculate_commission_amount( $line_total, $commission[ 'affiliate_id' ] );

				// decrease commission amount and increase total refunds.
                $commission[ 'commission' ]  = floatval( $commission[ 'commission' ] ) - $refunded_amount;
                $commission[ 'refund' ]      = $refunded_amount;
                $commission[ 'updated_at' ]  = current_time( 'Y-m-d H:i:s' );

                $commission_helper->ddwcaf_save_commission( $commission );

				// save amount refunded.
				$refund_partials[ $commission_id ] = -1 * $refunded_amount;
			}

			// save list of refunded commissions for this refund.
			$refund->update_meta_data( '_ddwcaf_refunded_commissions', $refund_partials );
			$refund->save();
        }
    }
}
