<?php
/**
 * Error Handing class
 *
 * @package Affiliates for WooCommerce
 */

namespace DDWCAffiliates\Helper\Error;

defined( 'ABSPATH' ) || exit();

if ( ! trait_exists( 'DDWCAF_Error_Helper' ) ) {
    /**
     * Error Handing class
     */
    trait DDWCAF_Error_Helper {
        /**
         * Print Notification function
         *
         * @param string $message
         * @return void
         */
        public function ddwcaf_print_notification( $message, $type = 'success', $dismissible = true ) {
            if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
                ?>
                <div class="notice notice-<?php echo esc_attr( $type . ' ' . ( $dismissible ? 'is-dismissible' : '' ) ); ?>">
                    <p><?php echo wp_kses_post( $message ); ?></p>
                </div>
                <?php
            } else {
                wc_print_notice( $message, $type );
            }
        }
    }
}