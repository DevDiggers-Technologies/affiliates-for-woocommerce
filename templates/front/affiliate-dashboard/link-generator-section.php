<?php
/**
 * Link Generator Section Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

$user_id                  = get_current_user_id();
$affiliate_helper         = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
$affiliate_referral_token = $affiliate_helper->ddwcaf_get_affiliate_referral_token( $user_id );
$affiliate_referral_url   = $affiliate_helper->ddwcaf_get_affiliate_referral_url( $user_id );

?>
<div class="ddwcaf-details-container">
    <p><?php esc_html_e( 'Your Referral Token', 'affiliates-for-woocommerce' ); ?> <span class="ddwcaf-referral-token"><?php echo esc_html( $affiliate_referral_token ); ?></span></p>
    <p>
        <?php esc_html_e( 'Your Referral URL', 'affiliates-for-woocommerce' ); ?>
        <span class="ddwcaf-copy-field-container">
            <input type="text" class="ddwcaf-copy-target form-control" value="<?php echo esc_url( $affiliate_referral_url ); ?>" data-copy-text="<?php echo esc_url( $affiliate_referral_url ); ?>" readonly />
            <a href="#" class="ddwcaf-copy-trigger" data-tooltip="<?php esc_attr_e( 'Copy Referral URL', 'affiliates-for-woocommerce' ); ?>">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20"><path d="M8 3C7.44772 3 7 3.44772 7 4V4.5C7 4.77614 6.77614 5 6.5 5C6.22386 5 6 4.77614 6 4.5V4C6 2.89543 6.89543 2 8 2H8.5C8.77614 2 9 2.22386 9 2.5C9 2.77614 8.77614 3 8.5 3H8Z" fill="#212121"/><path d="M7 12C7 12.5523 7.44772 13 8 13H8.5C8.77614 13 9 13.2239 9 13.5C9 13.7761 8.77614 14 8.5 14H8C6.89543 14 6 13.1046 6 12V11.5C6 11.2239 6.22386 11 6.5 11C6.77614 11 7 11.2239 7 11.5V12Z" fill="#212121"/><path d="M7 6.5C7 6.22386 6.77614 6 6.5 6C6.22386 6 6 6.22386 6 6.5V9.5C6 9.77614 6.22386 10 6.5 10C6.77614 10 7 9.77614 7 9.5V6.5Z" fill="#212121"/><path d="M16 3C16.5523 3 17 3.44772 17 4V4.5C17 4.77614 17.2239 5 17.5 5C17.7761 5 18 4.77614 18 4.5V4C18 2.89543 17.1046 2 16 2H15.5C15.2239 2 15 2.22386 15 2.5C15 2.77614 15.2239 3 15.5 3H16Z" fill="#212121"/><path d="M16 13C16.5523 13 17 12.5523 17 12V11.5C17 11.2239 17.2239 11 17.5 11C17.7761 11 18 11.2239 18 11.5V12C18 13.1046 17.1046 14 16 14H15.5C15.2239 14 15 13.7761 15 13.5C15 13.2239 15.2239 13 15.5 13H16Z" fill="#212121"/><path d="M17.5 6C17.2239 6 17 6.22386 17 6.5V9.5C17 9.77614 17.2239 10 17.5 10C17.7761 10 18 9.77614 18 9.5V6.5C18 6.22386 17.7761 6 17.5 6Z" fill="#212121"/><path d="M10.5 2C10.2239 2 10 2.22386 10 2.5C10 2.77614 10.2239 3 10.5 3H13.5C13.7761 3 14 2.77614 14 2.5C14 2.22386 13.7761 2 13.5 2H10.5Z" fill="#212121"/><path d="M10 13.5C10 13.2239 10.2239 13 10.5 13H13.5C13.7761 13 14 13.2239 14 13.5C14 13.7761 13.7761 14 13.5 14H10.5C10.2239 14 10 13.7761 10 13.5Z" fill="#212121"/><path d="M4 6H5V7H4C3.44772 7 3 7.44772 3 8V14.5C3 15.8807 4.11929 17 5.5 17H12C12.5523 17 13 16.5523 13 16V15H14V16C14 17.1046 13.1046 18 12 18H5.5C3.567 18 2 16.433 2 14.5V8C2 6.89543 2.89543 6 4 6Z" fill="#212121"/></svg>
                <span class="ddwcaf-copy-tooltip"></span>
            </a>
        </span>
    </p>
    <small><?php esc_html_e( 'Copy this URL and use it to redirect users to our site with your affiliate token.', 'affiliates-for-woocommerce' ); ?></small>

    <?php
    if ( ! empty( $ddwcaf_configuration[ 'referral_social_share_options' ] ) ) {
        ?>
        <p><?php esc_html_e( 'Share your referral URL on:', 'affiliates-for-woocommerce' ); ?></p>
        <?php
        $affiliate_helper->ddwcaf_get_social_share_options( $affiliate_referral_url );
    }
    ?>
</div>
<div class="ddwcaf-details-container">
    <h4><?php esc_html_e( 'Generate a Custom URL', 'affiliates-for-woocommerce' ); ?></h4>
    <p><?php esc_html_e( 'If you want to redirect users to any specific page (for example: a product page) use this link generator.', 'affiliates-for-woocommerce' ); ?></p>
    <p>
        <label>
            <?php esc_html_e( 'Page URL', 'affiliates-for-woocommerce' ); ?>
            <input type="text" class="ddwcaf-custom-page-url form-control" />
        </label>
    </p>
    <p>
        <?php esc_html_e( 'Referral URL', 'affiliates-for-woocommerce' ); ?>

        <span class="ddwcaf-copy-field-container">
            <input type="text" class="ddwcaf-copy-target form-control" value="<?php echo esc_url( $affiliate_referral_url ); ?>" data-copy-text="<?php echo esc_url( $affiliate_referral_url ); ?>" readonly />
            <a href="#" class="ddwcaf-copy-trigger" data-tooltip="<?php esc_attr_e( 'Copy Referral URL', 'affiliates-for-woocommerce' ); ?>">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20"><path d="M8 3C7.44772 3 7 3.44772 7 4V4.5C7 4.77614 6.77614 5 6.5 5C6.22386 5 6 4.77614 6 4.5V4C6 2.89543 6.89543 2 8 2H8.5C8.77614 2 9 2.22386 9 2.5C9 2.77614 8.77614 3 8.5 3H8Z" fill="#212121"/><path d="M7 12C7 12.5523 7.44772 13 8 13H8.5C8.77614 13 9 13.2239 9 13.5C9 13.7761 8.77614 14 8.5 14H8C6.89543 14 6 13.1046 6 12V11.5C6 11.2239 6.22386 11 6.5 11C6.77614 11 7 11.2239 7 11.5V12Z" fill="#212121"/><path d="M7 6.5C7 6.22386 6.77614 6 6.5 6C6.22386 6 6 6.22386 6 6.5V9.5C6 9.77614 6.22386 10 6.5 10C6.77614 10 7 9.77614 7 9.5V6.5Z" fill="#212121"/><path d="M16 3C16.5523 3 17 3.44772 17 4V4.5C17 4.77614 17.2239 5 17.5 5C17.7761 5 18 4.77614 18 4.5V4C18 2.89543 17.1046 2 16 2H15.5C15.2239 2 15 2.22386 15 2.5C15 2.77614 15.2239 3 15.5 3H16Z" fill="#212121"/><path d="M16 13C16.5523 13 17 12.5523 17 12V11.5C17 11.2239 17.2239 11 17.5 11C17.7761 11 18 11.2239 18 11.5V12C18 13.1046 17.1046 14 16 14H15.5C15.2239 14 15 13.7761 15 13.5C15 13.2239 15.2239 13 15.5 13H16Z" fill="#212121"/><path d="M17.5 6C17.2239 6 17 6.22386 17 6.5V9.5C17 9.77614 17.2239 10 17.5 10C17.7761 10 18 9.77614 18 9.5V6.5C18 6.22386 17.7761 6 17.5 6Z" fill="#212121"/><path d="M10.5 2C10.2239 2 10 2.22386 10 2.5C10 2.77614 10.2239 3 10.5 3H13.5C13.7761 3 14 2.77614 14 2.5C14 2.22386 13.7761 2 13.5 2H10.5Z" fill="#212121"/><path d="M10 13.5C10 13.2239 10.2239 13 10.5 13H13.5C13.7761 13 14 13.2239 14 13.5C14 13.7761 13.7761 14 13.5 14H10.5C10.2239 14 10 13.7761 10 13.5Z" fill="#212121"/><path d="M4 6H5V7H4C3.44772 7 3 7.44772 3 8V14.5C3 15.8807 4.11929 17 5.5 17H12C12.5523 17 13 16.5523 13 16V15H14V16C14 17.1046 13.1046 18 12 18H5.5C3.567 18 2 16.433 2 14.5V8C2 6.89543 2.89543 6 4 6Z" fill="#212121"/></svg>
                <span class="ddwcaf-copy-tooltip"></span>
            </a>
        </span>
    </p>

    <small><?php esc_html_e( 'Copy this URL and use it to redirect users to our site with your affiliate token.', 'affiliates-for-woocommerce' ); ?></small>

    <?php
    if ( ! empty( $ddwcaf_configuration[ 'referral_social_share_options' ] ) ) {
        ?>
        <p><?php esc_html_e( 'Share your referral URL on:', 'affiliates-for-woocommerce' ); ?></p>
        <?php
        $affiliate_helper->ddwcaf_get_social_share_options( $affiliate_referral_url );
    }
    ?>
</div>
