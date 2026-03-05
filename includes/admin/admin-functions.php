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
         * Register settings function
         *
         * @return void
         */
        public function ddwcaf_register_settings() {
            // General configuration fields
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_enabled' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_user_roles' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_fields_enabled_on_woocommerce_registration' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_affiliate_dashboard_page_id' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_default_affiliate_dashboard_page' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_enable_widgets_affiliate_dashboard_page' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_my_account_enabled' );
            register_setting( 'ddwcaf-general-configuration-fields', '_ddwcaf_enable_widgets_my_account_endpoint' );

            // Referrals configuration fields
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_query_variable_name' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_referral_cookie_name' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_referral_cookie_expires' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_referral_cookie_change_allowed' );
            register_setting( 'ddwcaf-referrals-configuration-fields', '_ddwcaf_register_visits_enabled' );

            // Commissions configuration fields
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_default_commission_rate' );
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_exclude_taxes_enabled' );
            register_setting( 'ddwcaf-commissions-configuration-fields', '_ddwcaf_exclude_discounts_enabled' );

            register_setting( 'ddwcaf-payouts-configuration-fields', '_ddwcaf_withdrawal_methods' );

            // Shortcode configuration fields
            register_setting( 'ddwcaf-shortcodes-configuration-fields', '_ddwcaf_affiliate_registration_form_shortcode' );
            register_setting( 'ddwcaf-shortcodes-configuration-fields', '_ddwcaf_affiliate_registration_form_shortcode_content' );
            register_setting( 'ddwcaf-shortcodes-configuration-fields', '_ddwcaf_affiliate_dashboard_shortcode' );

            // Layout configuration fields
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_primary_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_icons_enabled' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_icons_wrapper_enabled' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_icon_size' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_icon_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_icon_wrapper_background_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_card_background_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_card_border_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_card_text_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_details_card_value_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_table_header_background_color' );
            register_setting( 'ddwcaf-layout-configuration-fields', '_ddwcaf_table_header_text_color' );
        }

        /**
         * Add user form fields function
         *
         * @return void
         */
        public function ddwcaf_add_user_form_fields() {
            ?>
            <input type="hidden" name="ddwcaf_from_affiliates_page" value="<?php echo esc_attr( ( ! empty( $_SERVER[ 'HTTP_REFERER' ] ) && strpos( $_SERVER[ 'HTTP_REFERER' ], 'affiliates-for-woocommerce' ) ) || ! empty( $_GET[ 'ddwcaf-add-affiliate' ] ) ? 1 : 0 ); ?>" />
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
                        wp_safe_redirect( admin_url( 'admin.php?page=ddwcaf-dashboard&menu=affiliates&status=saved' ) );
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

            if ( ! $order->get_meta( '_ddwcaf_referral_token' ) ) {
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
