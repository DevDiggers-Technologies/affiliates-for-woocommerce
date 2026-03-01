<?php
/**
 * Affiliates Notification email
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

do_action( 'woocommerce_email_header', $email_heading, $email );

if ( ! empty( $email_message ) ) {
    if ( is_string( $email_message ) ) {
        echo wp_kses_post( $email_message );
    } elseif ( is_array( $email_message ) ) {
        if ( ! empty( $display_name ) ) {
            /* translators: %s Display name */
            ?>
            <p><?php printf( esc_html__( 'Hi %s,', 'affiliates-for-woocommerce' ), esc_html( $display_name ) ); ?></p>
            <?php 
        } else {
            /* translators: %s Customer email */
            ?>
            <p><?php printf( esc_html__( 'Hi %s,', 'affiliates-for-woocommerce' ), esc_html( $customer_email ) ); ?></p>
            <?php 
        }

        foreach ( $email_message as $key => $message ) {
            ?>
            <p><?php echo wp_kses_post( $message ); ?></p>
            <?php
        }
        ?>
        <p><?php esc_html_e( 'We are looking forward to seeing you again.', 'affiliates-for-woocommerce' ); ?></p>
        <?php
    }
}
?>


<?php
do_action( 'woocommerce_email_footer', $email );