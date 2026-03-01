<?php
/**
 * Affiliates Notification email
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

echo '= ' . esc_html( $email_heading ) . " =\n\n";

if ( is_string( $email_message ) ) {
    echo esc_html( $message ) . "\n\n";
} elseif ( is_array( $email_message ) ) {
    if ( ! empty( $display_name ) ) {
        /* translators: %s Display name */
        echo sprintf( esc_html__( 'Hi %s,', 'affiliates-for-woocommerce' ), esc_html( $display_name ) ) . "\n\n";
    } else {
        /* translators: %s Customer email */
        echo sprintf( esc_html__( 'Hi %s,', 'affiliates-for-woocommerce' ), esc_html( $customer_email ) ) . "\n\n";
    }

    foreach( $email_message as $key => $message ) {
        echo esc_html( $message ) . "\n\n";
    }
    echo esc_html__( 'We are looking forward to seeing you again.', 'affiliates-for-woocommerce' ) . "\n\n";

    echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped