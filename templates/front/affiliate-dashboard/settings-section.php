<?php
/**
 * Settings Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

$affiliate_id              = get_current_user_id();
$affiliate_helper          = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
$affiliate_referral_token  = $affiliate_helper->ddwcaf_get_affiliate_referral_token( $affiliate_id );
$withdrawal_methods        = $affiliate_helper->ddwcaf_get_affiliate_withdrawal_methods( $affiliate_id );
$default_withdrawal_method = $affiliate_helper->ddwcaf_get_affiliate_default_withdrawal_method( $affiliate_id );

?>
<form method="post" class="ddwcaf-settings-container">
    <div class="ddwcaf-settings-details-wrapper">
        <h3><?php esc_html_e( 'Withdrawal Info', 'affiliates-for-woocommerce' ); ?></h3>
        <p><?php esc_html_e( 'Please fill out the below withdrawal information for the commission payouts.', 'affiliates-for-woocommerce' ); ?></p>

        <h4><?php esc_html_e( 'Withdrawal Method', 'affiliates-for-woocommerce' ); ?></h4>
        <hr />

        <?php
        $available_withdrawal_methods = [];

        foreach ( $ddwcaf_configuration[ 'withdrawal_methods' ] as $key => $withdrawal_method ) {
            if ( ! empty( $withdrawal_method[ 'available' ] ) && ! empty( $withdrawal_method[ 'status' ] ) ) {
                $available_withdrawal_methods[ $key ] = $affiliate_helper->ddwcaf_get_withdrawal_method_name( $key );
            }
        }

        ddwcaf_form_field( [
            'type'    => 'select',
            'label'   => esc_html__( 'Default Withdrawal Method', 'affiliates-for-woocommerce' ),
            'id'      => 'ddwcaf-default-withdrawal-method',
            'name'    => '_ddwcaf_default_withdrawal_method',
            'options' => $available_withdrawal_methods,
            'value'   => ! empty( $default_withdrawal_method ) ? $default_withdrawal_method : '',
        ] );

        if ( array_key_exists( 'bacs', $available_withdrawal_methods ) ) {
            ?>
            <h4><?php esc_html_e( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ); ?></h4>
            <hr />

            <?php
            ddwcaf_form_field( [
                'type'        => 'text',
                'label'       => esc_html__( 'Account Name', 'affiliates-for-woocommerce' ),
                'id'          => 'ddwcaf-withdrawal-account-name',
                'name'        => '_ddwcaf_withdrawal_methods[bacs][account_name]',
                'placeholder' => esc_html__( 'Enter your account name', 'affiliates-for-woocommerce' ),
                'value'       => ! empty( $withdrawal_methods[ 'bacs' ][ 'account_name' ] ) ? $withdrawal_methods[ 'bacs' ][ 'account_name' ] : '',
            ] );

            ddwcaf_form_field( [
                'type'        => 'text',
                'label'       => esc_html__( 'IBAN', 'affiliates-for-woocommerce' ),
                'id'          => 'ddwcaf-withdrawal-iban',
                'name'        => '_ddwcaf_withdrawal_methods[bacs][iban]',
                'placeholder' => esc_html__( 'Enter your account iban', 'affiliates-for-woocommerce' ),
                'value'       => ! empty( $withdrawal_methods[ 'bacs' ][ 'iban' ] ) ? $withdrawal_methods[ 'bacs' ][ 'iban' ] : '',
            ] );

            ddwcaf_form_field( [
                'type'        => 'text',
                'label'       => esc_html__( 'Swift Code', 'affiliates-for-woocommerce' ),
                'id'          => 'ddwcaf-withdrawal-swift-code',
                'name'        => '_ddwcaf_withdrawal_methods[bacs][swift_code]',
                'placeholder' => esc_html__( 'Enter your account swift code', 'affiliates-for-woocommerce' ),
                'value'       => ! empty( $withdrawal_methods[ 'bacs' ][ 'swift_code' ] ) ? $withdrawal_methods[ 'bacs' ][ 'swift_code' ] : '',
            ] );
        }

        if ( array_key_exists( 'paypal_email', $available_withdrawal_methods ) ) {
            ?>
            <h4><?php esc_html_e( 'PayPal Email', 'affiliates-for-woocommerce' ); ?></h4>
            <hr />

            <?php
            ddwcaf_form_field( [
                'type'        => 'text',
                'label'       => esc_html__( 'PayPal Email', 'affiliates-for-woocommerce' ),
                'id'          => 'ddwcaf-withdrawal-paypal-email',
                'name'        => '_ddwcaf_withdrawal_methods[paypal_email]',
                'placeholder' => esc_html__( 'Enter your PayPal email', 'affiliates-for-woocommerce' ),
                'value'       => ! empty( $withdrawal_methods[ 'paypal_email' ] ) ? $withdrawal_methods[ 'paypal_email' ] : '',
            ] );
        }
        ?>
    </div>

    <p class="woocommerce-form-row form-row">
        <?php wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' ); ?>
        <button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="ddwcaf_affiliate_settings_submit" value="<?php esc_attr_e( 'Become an Affiliate', 'affiliates-for-woocommerce' ); ?>"><?php esc_html_e( 'Save Changes', 'affiliates-for-woocommerce' ); ?></button>
    </p>
</form>