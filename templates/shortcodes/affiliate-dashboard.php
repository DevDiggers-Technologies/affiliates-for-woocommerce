<?php
/**
 * Affiliate Dashboard Shortcode Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

global $ddwcaf_configuration;

$user_id          = get_current_user_id();
$affiliate_helper = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );

$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
if ( $affiliate_helper->ddwcaf_is_user_affiliate( $user_id ) ) {
    $affiliate_status = $affiliate_helper->ddwcaf_get_affiliate_status( $user_id );
    if ( 'pending' === $affiliate_status ) {
        ?>
        <div class="ddwcaf-affiliate-notice">
            <svg width="48px" height="48px" viewBox="0 0 24 24" version="1.1"><defs><rect id="path-1" x="0" y="0" width="24" height="24"/></defs><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="basic-/-circle_checked"><mask id="mask-2" fill="white"><use xlink:href="#path-1"/></mask><g id="basic-/-circle_checked-(Background/Mask)"/><path d="M2,12 C2,17.5228481 6.47715235,22 12,22 C17.5228481,22 22,17.5228481 22,12 C22,6.47715235 17.5228481,2 12,2 C6.47715235,2 2,6.47715235 2,12 C2,15.6818988 2,15.6818988 2,12 Z M20.181818,12 C20.181818,16.518693 16.518693,20.181818 12,20.181818 C7.48130655,20.181818 3.81818187,16.518693 3.81818187,12 C3.81818187,7.48130655 7.48130655,3.81818187 12,3.81818187 C16.518693,3.81818187 20.181818,7.48130655 20.181818,12 C20.181818,15.012462 20.181818,15.012462 20.181818,12 Z M10.5,13.232233 L15.7928934,7.93933964 L17.2071066,9.3535533 L10.5,16.0606604 L6.79289341,12.3535538 L8.20710707,10.9393396 L10.5,13.232233 L10.5,13.232233 Z" fill="#A1C746" mask="url(#mask-2)"/></g></g></svg>
            <h3><?php esc_html_e( 'Thank You!!', 'affiliates-for-woocommerce' ); ?></h3>
            <p><?php esc_html_e( 'Your request has been submitted successfully for the affiliate program and it is being reviewed. You\'ll get an email update soon.', 'affiliates-for-woocommerce' ); ?></p>
        </div>
        <?php
    } else if ( 'rejected' === $affiliate_status ) {
        ?>
        <div class="ddwcaf-affiliate-notice">
            <svg width="42px" height="42px" viewBox="0 0 20 20" preserveAspectRatio="xMinYMin"><defs><rect id="path-1" x="0" y="0" width="24" height="24"/></defs><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="matrix(1, 0, 0, 1, -2, -2)"><g id="basic-/-circle_checked"><mask id="mask-2" fill="white"><rect x="0" y="0" width="24" height="24" transform="matrix(1, 0, 0, 1, 0, 0)"/></mask><g id="basic-/-circle_checked-(Background/Mask)"/><path d="M 2 12 C 2 17.523 6.477 22 12 22 C 17.523 22 22 17.523 22 12 C 22 6.477 17.523 2 12 2 C 6.477 2 2 6.477 2 12 C 2 15.682 2 15.682 2 12 Z M 20.182 12 C 20.182 16.519 16.519 20.182 12 20.182 C 7.481 20.182 3.818 16.519 3.818 12 C 3.818 7.481 7.481 3.818 12 3.818 C 16.519 3.818 20.182 7.481 20.182 12 C 20.182 15.012 20.182 15.012 20.182 12 Z" mask="url(#mask-2)" fill="rgb(244, 67, 54)" /></g></g><path d="M 11.464 10 L 15 13.5 L 13.5 15 L 10 11.5 L 6.5 15 L 5 13.5 L 8.5 10 L 5 6.5 L 6.5 5 L 10 8.5 L 13.5 5 L 15 6.5 L 11.5 10 L 11.464 10 Z" fill="rgb(244, 67, 54)" bx:origin="0.5 0.5"/><path d="M 11.517 10 L 15.707 14.189 L 14.19 15.707 L 10 11.518 L 5.81 15.706 L 4.293 14.189 L 8.483 10 L 4.293 5.811 L 5.81 4.294 L 10 8.483 L 14.19 4.293 L 15.707 5.81 Z M 10.103 10 L 14.293 5.81 L 14.19 5.707 L 10 9.897 L 5.81 5.708 L 5.707 5.811 L 9.897 10 L 5.707 14.189 L 5.81 14.292 L 10 10.104 L 14.19 14.293 L 14.293 14.189 Z" fill="none"/></svg>
            <h3><?php esc_html_e( 'We are Sorry!!', 'affiliates-for-woocommerce' ); ?></h3>
            <p><?php esc_html_e( 'Your profile is successfully reviewed and it is rejected for now so kindly submit the form again with more appropriate details.', 'affiliates-for-woocommerce' ); ?></p>
        </div>
        <?php
    } else if ( 'banned' === $affiliate_status ) {
        ?>
        <div class="ddwcaf-affiliate-notice">
            <svg width="42px" height="42px" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 122.88 122.88" xml:space="preserve"><g><path fill="#D8453E" class="st0" d="M61.44,0c8.31,0,16.24,1.66,23.49,4.66c7.53,3.12,14.3,7.68,19.96,13.34c5.66,5.66,10.22,12.42,13.34,19.96 c3,7.25,4.66,15.18,4.66,23.49c0,8.31-1.66,16.24-4.66,23.49c-3.12,7.53-7.68,14.3-13.34,19.96c-5.66,5.66-12.42,10.22-19.96,13.34 c-7.25,3-15.18,4.66-23.49,4.66s-16.24-1.66-23.49-4.66c-7.53-3.12-14.3-7.68-19.96-13.34C12.34,99.23,7.78,92.46,4.66,84.93 C1.66,77.68,0,69.75,0,61.44c0-8.31,1.66-16.24,4.66-23.49C7.78,30.42,12.34,23.65,18,18c5.66-5.66,12.42-10.22,19.96-13.34 C45.2,1.66,53.13,0,61.44,0L61.44,0z M49.13,16.62l38.18,83.42c0.93-0.63,1.84-1.28,2.72-1.97c1.46-1.14,2.89-2.41,4.26-3.78 c4.29-4.29,7.75-9.41,10.09-15.08c2.26-5.47,3.51-11.47,3.51-17.78c0-6.31-1.25-12.31-3.51-17.78c-2.35-5.67-5.8-10.79-10.09-15.08 c-4.29-4.29-9.41-7.75-15.08-10.09c-5.47-2.26-11.47-3.51-17.78-3.51c-2.54,0-5.01,0.2-7.38,0.58 C52.4,15.82,50.75,16.18,49.13,16.62L49.13,16.62z M73.75,106.26L35.57,22.83c-0.93,0.63-1.84,1.28-2.72,1.97 c-1.46,1.14-2.89,2.41-4.26,3.78c-4.29,4.29-7.75,9.41-10.09,15.08c-2.26,5.47-3.51,11.47-3.51,17.78c0,6.31,1.25,12.31,3.51,17.78 c2.35,5.67,5.8,10.79,10.09,15.08s9.41,7.75,15.08,10.09c5.47,2.26,11.47,3.51,17.78,3.51c2.54,0,5.01-0.2,7.38-0.58 C70.48,107.06,72.13,106.7,73.75,106.26L73.75,106.26z"/></g></svg>
            <h3><?php esc_html_e( 'We are Sorry!!', 'affiliates-for-woocommerce' ); ?></h3>
            <p><?php esc_html_e( 'Your profile is banned now so you can\'t earn commissions now.', 'affiliates-for-woocommerce' ); ?></p>
        </div>
        <?php
    } else if ( 'approved' === $affiliate_status ) {
        global $wp;
        $endpoints   = $affiliate_helper->ddwcaf_get_dashboard_endpoints();
        $current_tab = 'dashboard';

        foreach ( $endpoints as $key => $endpoint ) {
            if ( 'visits' === $key && empty( $ddwcaf_configuration[ 'register_visits_enabled' ] ) ) {
                continue;
            }

            if ( isset( $wp->query_vars[ $endpoint[ 'endpoint' ] ] ) || ( is_account_page() && isset( $wp->query_vars[ $ddwcaf_configuration[ 'my_account_endpoint' ] ] ) && false !== strpos( $wp->query_vars[ $ddwcaf_configuration[ 'my_account_endpoint' ] ], $endpoint[ 'endpoint' ] ) ) ) {
                $current_tab = $key;
            }
        }
        ?>
        <div class="woocommerce ddwcaf-affiliate-dashboard">
            <ul class="ddwcaf-dashboard-navigation">
                <?php
                foreach ( $endpoints as $key => $endpoint ) {
                    if ( 'visits' === $key && empty( $ddwcaf_configuration[ 'register_visits_enabled' ] ) ) {
                        continue;
                    }
                    ?>
                    <li class="ddwcaf-dashboard-navigation-item <?php echo esc_attr( $current_tab === $key ? 'ddwcaf-active' : '' ); ?>">
                        <a href="<?php echo esc_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url( $endpoint[ 'endpoint' ] ) ); ?>"><?php echo esc_html( $endpoint[ 'title' ] ); ?></a>
                    </li>
                    <?php
                }
                ?>
                <li class="ddwcaf-dashboard-navigation-item">
                    <a href="<?php echo esc_url( wc_logout_url() ); ?>"><?php esc_html_e( 'Logout', 'affiliates-for-woocommerce' ); ?></a>
                </li>
            </ul>
            <?php require DDWCAF_PLUGIN_FILE . "templates/front/affiliate-dashboard/{$current_tab}-section.php"; ?>
        </div>
        <?php
    }
} else if ( $affiliate_helper->ddwcaf_is_user_allowed_for_affiliate() ) {
    ?>
    <div class="woocommerce">
        <h4><?php esc_html_e( 'You\'re just 1 step away from becoming an affiliate so kindly provide us the following details to proceed.', 'affiliates-for-woocommerce' ); ?></h4>

        <form method="post" class="woocommerce-form woocommerce-form-register register">
            <?php
            $affiliate_helper->ddwcaf_display_affiliate_registration_fields();
            do_action( 'ddwcaf_after_registration_fields_on_become_an_affiliate' );
            ?>
            <p class="woocommerce-form-row form-row">
                <?php wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' ); ?>
                <button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="ddwcaf_affiliate_info_submit" value="<?php esc_attr_e( 'Become an Affiliate', 'affiliates-for-woocommerce' ); ?>"><?php esc_html_e( 'Become an Affiliate', 'affiliates-for-woocommerce' ); ?></button>
            </p>
        </form>
    </div>
    <?php
} else {
    wc_print_notice( sprintf( '<a href="%s" tabindex="1" class="button wc-forward%s">%s</a> %s', esc_url( wp_logout_url( $affiliate_helper->ddwcaf_get_affiliate_dashboard_url() ) ), esc_attr( $wp_button_class ), esc_html__( 'Logout', 'affiliates-for-woocommerce' ), esc_html__( 'You need to logout first in order to join our affiliate program.', 'affiliates-for-woocommerce' ) ), 'notice' );
}
