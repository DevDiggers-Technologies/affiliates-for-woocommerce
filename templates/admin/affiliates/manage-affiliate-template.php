<?php
/**
 * Manage Affiliates Template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Affiliates;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Manage_Affiliate_Template' ) ) {
	/**
	 * Manage Affiliates Template class
	 */
	class DDWCAF_Manage_Affiliate_Template {
        /**
		 * Error Helper Trait
		 */
		use DDWCAF_Error_Helper;

		/**
		 * Construct
         * 
         * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            $affiliate_helper              = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
            $visit_helper                  = new DDWCAF_Visit_Helper( $ddwcaf_configuration );
            $commission_helper             = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
            $affiliate_id                  = ! empty( $_GET[ 'ddwcaf-id' ] ) ? intval( sanitize_text_field( wp_unslash( $_GET[ 'ddwcaf-id' ] ) ) ) : '';
            $affiliate_registration_fields = $affiliate_helper->ddwcaf_get_affiliate_registration_fields();
            $flag                          = false;

            usort( $affiliate_registration_fields, function( $first, $second ) {
                return strnatcmp( $first[ 'position' ], $second[ 'position' ] );
            } );

            if ( ! empty( $_POST[ 'ddwcaf_save_affiliate_info' ] ) && ! empty( $_POST[ 'ddwcaf_nonce' ] ) && wp_verify_nonce( $_POST[ 'ddwcaf_nonce' ], 'ddwcaf_nonce_action' ) ) {
                $error = false;

                foreach ( $affiliate_registration_fields as $key => $affiliate_registration_field ) {
                    if ( $affiliate_registration_field[ 'modify' ] && 'active' === $affiliate_registration_field[ 'status' ] && ! empty( $affiliate_registration_field[ 'required' ] ) && ( ! isset( $_POST[ $affiliate_registration_field[ 'name' ] ] ) || '' === sanitize_text_field( wp_unslash( $_POST[ $affiliate_registration_field[ 'name' ] ] ) ) ) ) {
                        $this->ddwcaf_print_notification( sprintf( esc_html__( '%s is required!', 'affiliates-for-woocommerce' ), $affiliate_registration_field[ 'label' ] ), 'error' );
                        $error = true;
                        break;
                    }
                }

                $affiliate_helper->ddwcaf_prepare_and_save_affiliate_info( $affiliate_id );
                $affiliate_helper->ddwcaf_update_affiliate_withdrawal_methods( $affiliate_id, $_POST[ '_ddwcaf_withdrawal_methods' ] );
                $affiliate_helper->ddwcaf_update_affiliate_default_withdrawal_method( $affiliate_id, $_POST[ '_ddwcaf_default_withdrawal_method' ] );

                if ( ! $error ) {
                    $affiliate_helper->ddwcaf_prepare_and_save_affiliate_info( $affiliate_id, $affiliate_registration_fields );

                    $this->ddwcaf_print_notification( esc_html__( 'Details are saved successfully.', 'affiliates-for-woocommerce' ), 'success' );
                } else {
                    $this->ddwcaf_print_notification( esc_html__( 'Kindly fill all the mandatory fields.', 'affiliates-for-woocommerce' ), 'error' );
                }
            }

            $args = [
                'affiliate_id' => $affiliate_id,
            ];

            $affiliate_referral_token  = $affiliate_helper->ddwcaf_get_affiliate_referral_token( $affiliate_id );
            $commission_rate           = $commission_helper->ddwcaf_get_affiliate_commission_rate( $affiliate_id );
            $withdrawal_methods        = $affiliate_helper->ddwcaf_get_affiliate_withdrawal_methods( $affiliate_id );
            $default_withdrawal_method = $affiliate_helper->ddwcaf_get_affiliate_default_withdrawal_method( $affiliate_id );
            $statistics                = $commission_helper->ddwcaf_get_affiliate_statistics( $affiliate_id );
            $visits_count              = $visit_helper->ddwcaf_get_visits_count( $args );
            $conversion_details        = $visit_helper->ddwcaf_get_conversion_details( $args );
            $affiliate_referral_url    = $affiliate_helper->ddwcaf_get_affiliate_referral_url( $affiliate_id );
            $affiliate_status          = $affiliate_helper->ddwcaf_get_affiliate_status( $affiliate_id );
            $affiliate_data            = get_userdata( $affiliate_id );

            ?>
            <div class="ddwcaf-manage-affiliate-container">

                <h1 class="wp-heading-inline"><?php echo sprintf( esc_html__( 'Affiliate #%s Details', 'affiliates-for-woocommerce' ), $affiliate_id ); ?></h1>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $_GET[ 'page' ] ) ); ?>" class="page-title-action"><?php esc_html_e( 'Back', 'affiliates-for-woocommerce' ); ?></a>

                <hr class="wp-header-end" />

                <div class="ddwcaf-affiliate-details-container">
                    <div class="ddwcaf-affiliate-details-wrapper">
                        <img src="<?php echo esc_url( get_avatar_url( $affiliate_id ) ); ?>" />

                        <div>
                            <h3><?php echo esc_html( $affiliate_data->display_name ); ?></h3>
                            <p><?php echo sprintf( esc_html__( 'Referral URL: %s', 'affiliates-for-woocommerce' ), '<code>' . esc_url( $affiliate_referral_url ) . '</code>' ); ?></p>
                            <p><?php echo sprintf( esc_html__( 'Email: %s', 'affiliates-for-woocommerce' ), '<a href="mailto:' . esc_attr( $affiliate_data->user_email ) . '" target="_blank">' . esc_html( $affiliate_data->user_email ) . '</a>' ); ?></p>
                        </div>

                        <div>
                            <mark class="ddwcaf-status ddwcaf-affiliate-status-<?php echo esc_attr( $affiliate_status ); ?>"><span><?php echo esc_html( $affiliate_helper->ddwcaf_get_translation( $affiliate_status ) ); ?></span></mark>
                        </div>
                    </div>

                    <div class="ddwcaf-affiliate-statistics-container">
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=ddwcaf-commissions&affiliate-id={$affiliate_id}" ) ); ?>" target="_blank">
                            <svg fill="none" viewBox="0 0 149 153" width="30"><g clip-path="url(#clip0)"><path d="M58.5772 57.7152C55.833 57.1967 53.0763 56.7308 50.3446 56.1466C47.1443 55.4946 44.1214 54.1622 41.4808 52.24C39.1836 50.5493 37.5533 48.3807 37.5684 45.506C37.5947 40.4699 37.8723 35.4339 38.1289 30.4017C38.2261 28.4983 38.6251 26.6153 38.7668 24.7139C38.8955 22.5188 39.4791 20.3743 40.4806 18.4168C41.4821 16.4593 42.8797 14.7315 44.5846 13.3428C46.5484 11.6961 48.6251 10.0027 50.9387 8.98739C60.5081 4.78683 70.2273 1.11721 80.9801 1.36596C88.1657 1.53267 95.3625 1.35749 102.45 2.80603C107.931 3.9264 112.915 6.13686 117.06 9.98102C120.665 13.3231 122.808 17.3543 122.95 22.3897C123.055 26.0757 123.379 29.7552 123.601 33.3644C127.382 34.2445 131.018 34.874 134.504 35.9793C136.844 36.7299 139.048 37.8529 141.03 39.3049C144.029 41.5141 145.024 44.8871 145.171 48.5055C145.442 55.2323 145.63 61.9637 145.965 68.6876C146.407 77.5416 147.117 86.3851 147.421 95.243C147.767 105.339 147.896 115.446 147.894 125.547C147.894 128.536 147.073 131.52 146.73 134.515C146.402 137.385 144.522 138.762 142.088 139.717C136.561 141.919 130.797 143.471 124.913 144.345C110.767 146.319 96.4028 146.13 82.3138 143.783C79.2369 143.29 76.2394 142.299 72.9459 141.471C72.4641 141.865 71.7645 142.409 71.095 142.988C64.1231 149.009 55.9681 152.018 46.8339 152.616C36.3929 153.299 26.4815 151.454 17.2646 146.414C9.17388 141.989 3.64035 135.465 1.70941 126.309C-2.93287 104.301 11.1606 81.8485 32.9379 76.3116C34.6345 75.8804 36.175 74.8428 37.8703 74.4004C40.0015 73.8451 42.2639 73.1435 44.3858 73.3502C47.4706 73.6541 50.4898 74.6918 53.5253 75.4617C54.7921 75.7826 56.0286 76.2191 57.487 76.6667C57.8512 70.3048 58.2044 64.1867 58.5772 57.7152ZM43.5943 145.074C45.1826 144.889 47.7803 144.78 50.2934 144.262C61.9631 141.855 69.9838 135.446 72.707 123.395C73.7624 118.789 73.8766 114.017 73.0417 109.365C71.2171 99.0925 65.9701 91.3037 56.5798 86.5518C53.5876 85.0376 50.4589 83.7762 47.3308 82.5567C43.943 81.2598 40.2177 81.1403 36.7539 82.2174C24.2691 85.8148 15.3573 93.3095 11.3294 105.92C10.2577 109.11 9.55048 112.411 9.22114 115.759C8.24713 127.791 12.6013 135.624 23.0634 140.593C29.2448 143.529 35.7891 144.856 43.5943 145.073V145.074ZM74.9202 33.3035C82.8382 32.5204 90.3526 32.0445 97.7725 30.9366C101.863 30.2964 105.847 29.0927 109.608 27.3596C114.105 25.3053 114.588 21.5379 111.408 17.7383C110.811 16.9209 110.068 16.2212 109.216 15.6748C106.565 14.2565 103.952 12.5244 101.098 11.7775C90.7576 9.05446 79.9831 8.39117 69.3879 9.82546C63.5634 10.6078 57.9674 12.2002 52.9215 15.346C50.1301 17.0873 47.5809 19.1219 46.1757 22.2093C44.8879 25.0381 45.5193 27.3484 48.2812 28.7609C50.3524 29.8782 52.5933 30.6472 54.9141 31.0371C61.6797 31.9789 68.49 32.5997 74.9202 33.3027V33.3035ZM115.538 34.5609C111.378 35.7889 107.686 37.1174 103.885 37.9542C93.9677 40.1372 83.8798 40.4758 73.7683 40.1969C63.7779 39.9212 53.8259 39.3523 44.561 34.815V46.526C47.3452 48.457 49.4586 49.423 53.6644 49.9055C60.2317 50.6589 66.8183 51.3612 73.4165 51.711C85.0842 52.3462 96.7782 51.0659 108.032 47.9208C110.799 47.1482 113.561 46.23 115.54 44.7415L115.538 34.5609ZM139.007 79.2159C133.685 81.5998 129.117 84.0282 124.292 85.732C112.514 89.8913 100.28 89.9129 88.0233 88.8424C83.1368 88.4152 78.2885 87.5508 73.5182 86.8984C74.9287 89.1627 76.5452 91.4474 77.8284 93.9061C79.1115 96.3647 80.0711 99.013 81.2807 101.831C82.5626 102.024 83.9816 102.349 85.4157 102.435C98.6553 103.252 111.939 101.89 124.738 98.4053C128.923 97.2685 132.905 95.3697 136.953 93.7512C137.975 93.3416 139.017 92.8015 139.012 91.3884C138.997 87.3795 139.007 83.3712 139.007 79.2153V79.2159ZM66.3156 77.879C68.7532 78.6489 71.2611 79.6852 73.8713 80.2234C85.8876 82.6906 98.2372 83.0864 110.387 81.3943C119.459 80.1597 127.611 76.4915 135.006 71.1489C135.947 70.4689 137.172 69.508 137.316 68.5314C137.657 66.239 137.425 63.8618 137.425 61.4701C137.126 61.5175 136.836 61.6135 136.568 61.7543C130.469 66.3001 123.458 68.4704 116.078 69.7561C105.207 71.5683 94.1463 71.9575 83.1756 70.9146C77.6958 70.4394 72.2508 69.5802 67.1097 67.4314C66.4534 67.1583 65.7791 66.9404 64.7913 66.5788C65.3144 70.4354 65.7738 73.8497 66.3156 77.8783V77.879ZM77.2941 136.003C88.022 139.778 126.426 139.865 140.463 130.555C140.156 127.489 139.84 124.342 139.516 121.115C120.45 128.32 101.014 129.126 81.2755 127.041C79.9116 130.108 78.6029 133.055 77.2941 136.002V136.003ZM139.021 102.337C121.035 108.857 101.747 110.962 82.7785 108.475V119.56C83.0863 119.77 83.1756 119.883 83.2721 119.889C85.1761 120.008 87.0795 120.12 88.9822 120.223C101.669 120.914 114.388 119.72 126.726 116.679C131.037 115.665 135.178 114.029 139.019 111.822L139.021 102.337ZM124.039 41.8581C123.828 42.1817 123.579 42.3924 123.607 42.5486C124.363 46.4997 122.042 48.8081 119.262 50.9536C115.603 53.7759 111.315 55.08 106.938 56.0828C94.2086 58.9141 81.1121 59.7211 68.131 58.4739C67.1951 58.3919 66.2453 58.4635 65.3027 58.4635C65.964 59.5102 66.9443 60.3166 68.0988 60.7639C70.2923 61.6105 72.556 62.2648 74.8631 62.7191C88.5215 64.9283 102.465 64.6626 116.03 61.9348C121.107 60.9457 126.097 59.3915 130.179 56.0383C131.968 54.5365 133.487 52.7382 134.667 50.7219C135.897 48.6393 135.253 46.828 133.269 45.4936C130.494 43.6224 127.344 42.3815 124.039 41.8581Z" fill="black"/><path d="M42.2467 99.8165V112.156C44.045 112.834 45.7253 113.393 47.3497 114.092C53.7306 116.839 55.3006 122.833 51.1171 128.347C49.3942 130.618 48.0323 131.537 42.9509 133.871C42.4718 136.308 41.9887 138.71 41.5299 141.117C41.2746 142.457 40.6346 143.495 39.171 143.529C37.6266 143.565 37.0839 142.306 36.8083 141.096C36.2917 138.821 35.9399 136.506 35.4713 133.941C33.6027 133.443 31.5687 132.915 29.5439 132.352C28.5634 132.121 27.6036 131.81 26.6744 131.422C24.8229 130.559 24.0779 129.114 24.4323 127.373C24.7605 125.759 26.148 124.738 28.2844 124.879C30.6118 125.031 32.924 125.426 35.1838 125.708C36.1276 123.106 36.2339 121.557 35.8243 117.126C34.2491 116.535 32.546 116.014 30.9432 115.275C24.4586 112.284 23.0298 105.807 27.6248 100.33C29.5216 97.9446 32.0208 96.1088 34.8642 95.0127C36.5982 94.384 37.2926 93.3279 37.3583 91.5296C37.374 90.3002 37.6421 89.0873 38.1459 87.9663C38.3622 87.6046 38.6623 87.3008 39.021 87.0802C39.3796 86.8597 39.7861 86.7284 40.2061 86.6982C40.6126 86.8138 40.9859 87.0244 41.2948 87.3126C41.6037 87.6014 41.8393 87.9597 41.9822 88.3575C42.2635 89.7916 42.4172 91.248 42.4416 92.7096C43.987 93.0614 45.5133 93.492 47.0143 94.0007C49.4093 94.9432 50.5428 96.8013 50.2507 98.9186C49.9921 100.793 48.9728 101.426 47.2243 100.657C45.6937 99.9845 44.2682 98.9882 42.2467 99.8165ZM36.3344 102.263C33.0527 104.21 31.3725 106.281 31.9789 107.824C32.7009 109.667 34.3844 110.132 36.3344 110.385V102.263ZM42.4672 125.387C44.6482 124.582 46.6468 123.619 46.5811 121.409C46.526 119.556 44.4841 119.178 42.8242 118.926C42.7291 118.911 42.6187 118.996 42.1993 119.153C42.1993 120.443 42.1764 121.856 42.2098 123.267C42.2249 123.884 42.355 124.499 42.4672 125.387Z" fill="black"/></g><defs><clipPath id="clip0"><rect fill="white" height="152.27" transform="translate(0.534424 0.703125)" width="147.676"/></clipPath></defs></svg>
                            <h4><?php esc_html_e( 'Total Earnings', 'affiliates-for-woocommerce' ); ?></h4>
                            <span><?php echo wc_price( $statistics[ 'total_earnings' ] ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=ddwcaf-commissions&show=paid&affiliate-id={$affiliate_id}" ) ); ?>" target="_blank">
                            <svg width="30" viewBox="0 0 21.54 23.94"><g data-name="Camada 2"><g data-name="Camada 1" id="Camada_1-2"><path d="M21.5,23.26c-.19-.48-.7-1.85-.81-2.35a32.44,32.44,0,0,1,0-5.63,9.91,9.91,0,0,0-9-9.86L14.93.79A.5.5,0,0,0,14.52,0H7a.5.5,0,0,0-.41.79L9.84,5.43A9.92,9.92,0,0,0,.9,15.31a32.12,32.12,0,0,1,0,5.6A21.83,21.83,0,0,1,0,23.25a.5.5,0,0,0,.46.7H21a.5.5,0,0,0,.47-.68ZM13.56,1,10.77,5,8,1ZM1.25,22.94a15.11,15.11,0,0,0,.64-1.82,33.63,33.63,0,0,0,0-5.85,8.9,8.9,0,0,1,17.8,0,33.87,33.87,0,0,0,0,5.88,18.43,18.43,0,0,0,.6,1.82Z"/><path d="M12.6,15.22a6.94,6.94,0,0,0-1.42-.45,7.76,7.76,0,0,1-1-.3,1.29,1.29,0,0,1-.5-.32.66.66,0,0,1-.16-.45.69.69,0,0,1,.31-.61,1.54,1.54,0,0,1,.87-.21,1.52,1.52,0,0,1,.83.21.81.81,0,0,1,.4.54.29.29,0,0,0,.29.18h.95a.2.2,0,0,0,.21-.21,1.59,1.59,0,0,0-.26-.76,2.22,2.22,0,0,0-.71-.69,2.76,2.76,0,0,0-1.12-.39v-.71a.22.22,0,0,0-.24-.24h-.62a.24.24,0,0,0-.17.06.23.23,0,0,0-.07.17v.69a2.63,2.63,0,0,0-1.54.66,1.76,1.76,0,0,0-.56,1.32A1.63,1.63,0,0,0,8.65,15a4.26,4.26,0,0,0,1.73.74q.71.19,1.07.33a1.5,1.5,0,0,1,.54.31.61.61,0,0,1,.18.45.73.73,0,0,1-.35.64,1.9,1.9,0,0,1-1,.24,1.78,1.78,0,0,1-1-.23A1.07,1.07,0,0,1,9.34,17a.47.47,0,0,0-.12-.15.32.32,0,0,0-.19,0h-.9a.21.21,0,0,0-.16.06.2.2,0,0,0-.06.15,1.69,1.69,0,0,0,.29.87,2.1,2.1,0,0,0,.78.69,3.22,3.22,0,0,0,1.23.35v.7a.23.23,0,0,0,.07.18.24.24,0,0,0,.17.06h.62a.22.22,0,0,0,.24-.24v-.7A3,3,0,0,0,13,18.23a1.77,1.77,0,0,0,.63-1.41,1.73,1.73,0,0,0-.25-1A1.84,1.84,0,0,0,12.6,15.22Z"/></g></g></svg>
                            <h4><?php esc_html_e( 'Paid Amount', 'affiliates-for-woocommerce' ); ?></h4>
                            <span><?php echo wc_price( $statistics[ 'paid_earnings' ] ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=ddwcaf-commissions&show=pending&affiliate-id={$affiliate_id}" ) ); ?>" target="_blank">
                            <svg width="34" viewBox="0 0 64 64"><g><path d="M23.17578,39.35168l-5.21875-15.24365c-0.16113-0.48584-0.6582-0.79932-1.16895-0.73926   c-1.58203,0.19189-3.0127,1.05127-3.92676,2.35791c-0.91406,1.30566-1.23047,2.94531-0.88379,4.41797l0.33691,2.25488   l-5.12305-6.87549V14.38c0-3.41406-2.77734-6.19189-6.19141-6.19189H0v25.37451l8.88086,12.02246v5.36481h-3.2356v8.19708h19.87799   v-8.19708h-2.34747V39.35168z M2,32.90442v-22.5957c1.83008,0.44971,3.19141,2.10449,3.19141,4.07129v11.80713l7.79297,10.4585   l0.55469,0.63965l1.41602-0.71777l-1.01465-6.79688c-0.23633-1.01514-0.03711-2.04395,0.55957-2.89746   c0.4502-0.64355,1.09473-1.11865,1.82422-1.36133l4.85156,14.17188v11.26617H10.88086v-6.02301L2,32.90442z M21.15302,56.11292   c-0.70001,0-1.28003-0.57001-1.28003-1.27002c0-0.69995,0.58002-1.26996,1.28003-1.26996s1.27002,0.57001,1.27002,1.26996   C22.42303,55.54291,21.85303,56.11292,21.15302,56.11292z"/><path d="M64,33.56262V8.18811h-1c-3.41406,0-6.19141,2.77783-6.19141,6.19189v11.14404l-5.12305,6.87842l0.32227-2.17871   c0.36133-1.55273,0.04492-3.19238-0.86914-4.49805c-0.91406-1.30615-2.3457-2.16504-3.93359-2.35791   c-0.52051-0.06201-0.9834,0.23926-1.16016,0.73291l-5.2207,15.25098v11.59821h-2.34698v8.19708h19.87799v-8.19708h-3.23511   v-5.36481L64,33.56262z M42.85303,56.11292c-0.71002,0-1.28003-0.57001-1.28003-1.27002c0-0.69995,0.57001-1.26996,1.28003-1.26996   c0.69995,0,1.26996,0.57001,1.26996,1.26996C44.12299,55.54291,43.55298,56.11292,42.85303,56.11292z M42.82422,50.94989V39.68372   l4.85156-14.17139c0.73145,0.24365,1.375,0.71826,1.82422,1.36035c0.59766,0.85352,0.7959,1.88281,0.54492,2.97705   l-0.87402,5.89844l-0.16016,0.83301l1.52539,0.71094l8.27246-11.10498V14.38c0-1.9668,1.36133-3.62158,3.19141-4.07129v22.5957   l-8.87988,12.02246v6.02301H42.82422z"/><path d="M43.54419,16.39697c0-6.37555-5.1684-11.54395-11.54395-11.54395S20.4563,10.02142,20.4563,16.39697   s5.1684,11.54395,11.54395,11.54395S43.54419,22.77252,43.54419,16.39697z M32,15.39709c2.10156,0,3.81055,1.70898,3.81055,3.81006   c0,1.75287-1.19604,3.21796-2.81055,3.66052v1.21985h-2v-1.21997c-1.61383-0.44263-2.80957-1.90765-2.80957-3.6604h2   c0,0.99854,0.81152,1.81055,1.80957,1.81055s1.81055-0.81201,1.81055-1.81055c0-0.99805-0.8125-1.81006-1.81055-1.81006   c-2.10059,0-3.80957-1.70947-3.80957-3.81055c0-1.75275,1.19574-3.21747,2.80957-3.65997V8.70667h2v1.21979   c1.6145,0.44238,2.81055,1.90723,2.81055,3.6601h-2c0-0.99805-0.8125-1.81006-1.81055-1.81006s-1.80957,0.81201-1.80957,1.81006   C30.19043,14.58508,31.00195,15.39709,32,15.39709z"/></g></svg>
                            <h4><?php esc_html_e( 'Unpaid Amount', 'affiliates-for-woocommerce' ); ?></h4>
                            <span><?php echo wc_price( $statistics[ 'unpaid_earnings' ] ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=ddwcaf-visits&affiliate-id={$affiliate_id}" ) ); ?>" target="_blank">
                            <svg viewBox="0 0 32 32" width="35"><defs><style>.cls-1{fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:2px;}</style></defs><title/><g data-name="79-users" id="_79-users"><circle class="cls-1" cx="16" cy="13" r="5"/><path class="cls-1" d="M23,28A7,7,0,0,0,9,28Z"/><path class="cls-1" d="M24,14a5,5,0,1,0-4-8"/><path class="cls-1" d="M25,24h6a7,7,0,0,0-7-7"/><path class="cls-1" d="M12,6a5,5,0,1,0-4,8"/><path class="cls-1" d="M8,17a7,7,0,0,0-7,7H7"/></g></svg>
                            <h4><?php esc_html_e( 'Visitors', 'affiliates-for-woocommerce' ); ?></h4>
                            <span><?php echo esc_html( $visits_count ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=ddwcaf-visits&show=converted&affiliate-id={$affiliate_id}" ) ); ?>" target="_blank">
                            <svg width="35" viewBox="0 0 64 64"><g><path d="M60.3496094,17.6196289C59.9726563,17.2241211,59.4492188,17,58.9023438,17H47v-2c0-7.7197266-6.2802734-14-14-14h-2   c-7.7197266,0-14,6.2802734-14,14v2H5.0976563c-0.546875,0-1.0703125,0.2241211-1.4472656,0.6196289   c-0.3779297,0.3959961-0.5761719,0.9291992-0.5507813,1.4755859l1.8183594,38.1899414   C5.0712891,60.4902344,7.703125,63,10.9111328,63h42.1777344c3.2080078,0,5.8398438-2.5097656,5.9931641-5.7148438   l1.8183594-38.1899414C60.9257813,18.5488281,60.7275391,18.015625,60.3496094,17.6196289z M21,15c0-5.5141602,4.4863281-10,10-10   h2c5.5136719,0,10,4.4858398,10,10v2H21V15z M55.0859375,57.0957031C55.0351563,58.1630859,54.1582031,59,53.0888672,59H10.9111328   c-1.0693359,0-1.9462891-0.8369141-1.9970703-1.9042969L7.1953125,21H17v7c0,1.1044922,0.8955078,2,2,2s2-0.8955078,2-2v-7h22v7   c0,1.1044922,0.8955078,2,2,2s2-0.8955078,2-2v-7h9.8046875L55.0859375,57.0957031z"/><path d="M20.109375,35.3398438C20,35.2695313,19.8896484,35.2001953,19.7695313,35.1494141   c-0.1298828-0.0498047-0.25-0.0898438-0.3798828-0.109375c-0.6494141-0.1298828-1.3398438,0.0800781-1.7998047,0.5498047   C17.2099609,35.9599609,17,36.4697266,17,37c0,0.2695313,0.0498047,0.5195313,0.1494141,0.7695313   c0.1005859,0.2402344,0.25,0.4599609,0.4404297,0.640625c0.1796875,0.1894531,0.4003906,0.3398438,0.6396484,0.4394531   C18.4794922,38.9501953,18.7294922,39,19,39c0.5302734,0,1.0400391-0.2099609,1.4101563-0.5898438   c0.1894531-0.1806641,0.3398438-0.4003906,0.4394531-0.640625C20.9501953,37.5195313,21,37.2695313,21,37   c0-0.5302734-0.2099609-1.0400391-0.5898438-1.4101563C20.3193359,35.4902344,20.2197266,35.4101563,20.109375,35.3398438z"/><path d="M46.8496094,36.2294922C46.7998047,36.109375,46.7294922,36,46.6601563,35.8896484   c-0.0703125-0.109375-0.1503906-0.2099609-0.25-0.2998047c-0.4599609-0.4697266-1.1503906-0.6796875-1.8007813-0.5498047   c-0.1298828,0.0195313-0.2597656,0.0595703-0.3798828,0.109375C44.109375,35.2001953,44,35.2695313,43.8896484,35.3398438   c-0.109375,0.0703125-0.2099609,0.1503906-0.2998047,0.25c-0.0996094,0.0898438-0.1796875,0.1904297-0.25,0.2998047   C43.2597656,36,43.2001953,36.109375,43.1494141,36.2294922c-0.0498047,0.1298828-0.0898438,0.25-0.109375,0.3798828   C43.0097656,36.7402344,43,36.8701172,43,37c0,0.2695313,0.0498047,0.5195313,0.1494141,0.7695313   c0.1005859,0.2402344,0.25,0.4599609,0.4404297,0.640625c0.0898438,0.0996094,0.1904297,0.1796875,0.2998047,0.25   C44,38.7402344,44.109375,38.7998047,44.2294922,38.8496094s0.25,0.0898438,0.3798828,0.1103516   C44.7402344,38.9902344,44.8701172,39,45,39s0.2597656-0.0097656,0.3896484-0.0400391   c0.1298828-0.0205078,0.2597656-0.0605469,0.3798828-0.1103516C45.8798828,38.7998047,46,38.7402344,46.109375,38.6601563   c0.1103516-0.0703125,0.2099609-0.1503906,0.3007813-0.25c0.1894531-0.1806641,0.3398438-0.4003906,0.4394531-0.640625   C46.9501953,37.5195313,47,37.2695313,47,37c0-0.1298828-0.0205078-0.2597656-0.0400391-0.390625   C46.9296875,36.4794922,46.8994141,36.359375,46.8496094,36.2294922z"/><path d="M39,44h-7c-1.1044922,0-2,0.8955078-2,2s0.8955078,2,2,2h4.5820313C35.8095703,49.7646484,34.0458984,51,32,51   c-2.7568359,0-5-2.2431641-5-5c0-1.1044922-0.8955078-2-2-2s-2,0.8955078-2,2c0,4.9628906,4.0371094,9,9,9s9-4.0371094,9-9   C41,44.8955078,40.1044922,44,39,44z"/></g></svg>
                            <h4><?php esc_html_e( 'Customers', 'affiliates-for-woocommerce' ); ?></h4>
                            <span><?php echo esc_html( intval( $conversion_details[ 'customers_count' ] ) ); ?></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=ddwcaf-visits&show=converted&affiliate-id={$affiliate_id}" ) ); ?>" target="_blank">
                            <svg viewBox="0 0 24 24" width="35"><defs id="defs2"><rect height="7.0346723" id="rect2504" width="7.9207187" x="-1.1008456" y="289.81766"/></defs><g id="g2963" style="display:inline;stroke-width:0.262528;stroke-miterlimit:4;stroke-dasharray:none" transform="matrix(3.7984262,0,0,3.819826,-0.05887475,-1110.3615)"><path d="m 20.267578,48.617188 v 0.498046 11.064454 h -2.144531 v -9.921876 -0.5 h -0.994141 v 0.5 9.921876 H 14.992188 V 51.75 51.242188 h -1 V 51.75 60.179688 H 11.638672 11.142578 V 61.1875 h 0.496094 10.410156 0.496094 V 60.179688 H 22.048828 21.261719 V 49.115234 48.617188 Z" id="path2866" style="color:#000000;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:medium;line-height:normal;font-family:sans-serif;font-variant-ligatures:normal;font-variant-position:normal;font-variant-caps:normal;font-variant-numeric:normal;font-variant-alternates:normal;font-variant-east-asian:normal;font-feature-settings:normal;font-variation-settings:normal;text-indent:0;text-align:start;text-decoration:none;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000000;letter-spacing:normal;word-spacing:normal;text-transform:none;writing-mode:lr-tb;direction:ltr;text-orientation:mixed;dominant-baseline:auto;baseline-shift:baseline;text-anchor:start;white-space:normal;shape-padding:0;shape-margin:0;inline-size:0;clip-rule:nonzero;display:inline;overflow:visible;visibility:visible;opacity:1;isolation:auto;mix-blend-mode:normal;color-interpolation:sRGB;color-interpolation-filters:linearRGB;solid-color:#000000;solid-opacity:1;vector-effect:none;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.999998;stroke-linecap:square;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;color-rendering:auto;image-rendering:auto;shape-rendering:auto;text-rendering:auto;enable-background:accumulate;stop-color:#000000;stop-opacity:1" transform="matrix(0.26326693,0,0,0.26179203,0.01575684,280.21211)"/><path d="m 17.195312,42.8125 v 1.005859 h 0.50586 1.341797 l -3.033203,3.082032 -1.707032,-1.730469 -3.835937,4.132812 -0.210938,0.226563 C 9.3647547,48.953899 8.3082544,48.617188 7.1738281,48.617188 c -3.1558392,0 -5.7207031,2.583422 -5.7207031,5.757812 0,3.174352 2.5648639,5.759766 5.7207031,5.759766 3.1558389,0 5.7265629,-2.585414 5.7265629,-5.759766 0,-1.669956 -0.715598,-3.171367 -1.84961,-4.224609 l 0.150391,-0.16211 3.130859,-3.371093 1.677735,1.701171 3.724609,-3.785156 v 1.359375 0.5 h 1 v -0.5 V 42.8125 H 17.701172 Z M 7.1738281,49.623047 c 2.6167423,0 4.7324219,2.119367 4.7324219,4.751953 0,2.632548 -2.1156796,4.753906 -4.7324219,4.753906 -2.6167422,0 -4.7265625,-2.121358 -4.7265625,-4.753906 0,-2.632586 2.1098203,-4.751953 4.7265625,-4.751953 z" id="ellipse2880" style="color:#000000;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:medium;line-height:normal;font-family:sans-serif;font-variant-ligatures:normal;font-variant-position:normal;font-variant-caps:normal;font-variant-numeric:normal;font-variant-alternates:normal;font-variant-east-asian:normal;font-feature-settings:normal;font-variation-settings:normal;text-indent:0;text-align:start;text-decoration:none;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000000;letter-spacing:normal;word-spacing:normal;text-transform:none;writing-mode:lr-tb;direction:ltr;text-orientation:mixed;dominant-baseline:auto;baseline-shift:baseline;text-anchor:start;white-space:normal;shape-padding:0;shape-margin:0;inline-size:0;clip-rule:nonzero;display:inline;overflow:visible;visibility:visible;opacity:1;isolation:auto;mix-blend-mode:normal;color-interpolation:sRGB;color-interpolation-filters:linearRGB;solid-color:#000000;solid-opacity:1;vector-effect:none;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.999998;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;paint-order:fill markers stroke;color-rendering:auto;image-rendering:auto;shape-rendering:auto;text-rendering:auto;enable-background:accumulate;stop-color:#000000;stop-opacity:1" transform="matrix(0.26326693,0,0,0.26179203,0.01575684,280.21211)"/><path d="m 6.6738281,51.251953 v 0.503906 0.292969 H 5.421875 V 54.875 h 2.5078125 v 0.822266 H 5.9277344 5.421875 v 1.005859 H 5.9277344 6.6738281 V 56.994141 57.5 h 1.0039063 v -0.505859 -0.291016 h 1.2460937 v -2.833984 h -2.5 v -0.814453 h 2.0039063 0.4960937 v -1.00586 h -0.4960937 -0.75 v -0.292969 -0.503906 z" id="path2882" style="color:#000000;font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:medium;line-height:normal;font-family:sans-serif;font-variant-ligatures:normal;font-variant-position:normal;font-variant-caps:normal;font-variant-numeric:normal;font-variant-alternates:normal;font-variant-east-asian:normal;font-feature-settings:normal;font-variation-settings:normal;text-indent:0;text-align:start;text-decoration:none;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000000;letter-spacing:normal;word-spacing:normal;text-transform:none;writing-mode:lr-tb;direction:ltr;text-orientation:mixed;dominant-baseline:auto;baseline-shift:baseline;text-anchor:start;white-space:normal;shape-padding:0;shape-margin:0;inline-size:0;clip-rule:nonzero;display:inline;overflow:visible;visibility:visible;opacity:1;isolation:auto;mix-blend-mode:normal;color-interpolation:sRGB;color-interpolation-filters:linearRGB;solid-color:#000000;solid-opacity:1;vector-effect:none;fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.999998;stroke-linecap:square;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;color-rendering:auto;image-rendering:auto;shape-rendering:auto;text-rendering:auto;enable-background:accumulate;stop-color:#000000;stop-opacity:1" transform="matrix(0.26326693,0,0,0.26179203,0.01575684,280.21211)"/></g></svg>
                            <h4><?php esc_html_e( 'Conversion', 'affiliates-for-woocommerce' ); ?></h4>
                            <span><?php echo esc_html( wc_format_decimal( $conversion_details[ 'conversion_rate' ], wc_get_price_decimals() ) . '%' ); ?></span>
                        </a>
                    </div>
                </div>

                <form method="POST">
                    <div class="ddwcaf-affiliate-info-container">
                        <div class="ddwcaf-configuration-container ddwcaf-padding-top-bottom-0">
                            <h2><?php esc_html_e( 'Details', 'affiliates-for-woocommerce' ); ?></h2>
                            <table class="form-table">
                                <tbody>
                                    <?php
                                    ddwcaf_form_field( [
                                        'type'                 => 'text',
                                        'id'                   => 'ddwcaf-referral-token',
                                        'label'                => esc_html__( 'Referral Token [Pro]', 'affiliates-for-woocommerce' ),
                                        'placeholder'          => esc_html__( 'Enter your referral token', 'affiliates-for-woocommerce' ),
                                        'description'          => esc_html__( 'You can use the brand name as a referral token which allows "friendly" looking referral links.', 'affiliates-for-woocommerce' ),
                                        'value'                => $affiliate_referral_token,
                                        'show_frontend_fields' => 'true',
                                    ] );

                                    ddwcaf_form_field( [
                                        'type'                 => 'number',
                                        'id'                   => 'ddwcaf-commission-rate',
                                        'label'                => esc_html__( 'Commission Rate [Pro]', 'affiliates-for-woocommerce' ),
                                        'placeholder'          => esc_html__( 'Enter your commission rate', 'affiliates-for-woocommerce' ),
                                        'description'          => esc_html__( 'You can enter any specific commission rate for the affiliate and if not entered then global rate is used.', 'affiliates-for-woocommerce' ),
                                        'value'                => $commission_rate,
                                        'show_frontend_fields' => 'true',
                                        'custom_attributes'    => [
                                            'min'  => 0,
                                            'step' => .01,
                                        ],
                                    ] );
                                    ?>
                                </tbody>
                            </table>
                            <h3><?php esc_html_e( 'Email Notifications [Pro]', 'affiliates-for-woocommerce' ); ?></h3>
                            <hr />
                            <table class="form-table">
                                <tbody>
                                    <?php
                                    ddwcaf_form_field( [
                                        'type'                 => 'checkbox',
                                        'checkbox_label'       => esc_html__( 'Notify on New Commissions [Pro]', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-notifications-new-commission',
                                        'name'                 => '_ddwcaf_notifications[new_commission]',
                                        'description'          => esc_html__( 'Get an email when a new commission is made and its status switches to pending.', 'affiliates-for-woocommerce' ),
                                        'show_frontend_fields' => 'true',
                                    ] );

                                    ddwcaf_form_field( [
                                        'type'                 => 'checkbox',
                                        'checkbox_label'       => esc_html__( 'Notify on Paid Commissions [Pro]', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-notifications-paid-commission',
                                        'name'                 => '_ddwcaf_notifications[paid_commission]',
                                        'description'          => esc_html__( 'Get an email when a commission status changes to paid.', 'affiliates-for-woocommerce' ),
                                        'show_frontend_fields' => 'true',
                                    ] );

                                    ddwcaf_form_field( [
                                        'type'                 => 'checkbox',
                                        'checkbox_label'       => esc_html__( 'Notify on Commissions Status Change [Pro]', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-notifications-commission-status-change',
                                        'name'                 => '_ddwcaf_notifications[commission_status_change]',
                                        'description'          => esc_html__( 'Get an email when a commission status changes.', 'affiliates-for-woocommerce' ),
                                        'show_frontend_fields' => 'true',
                                    ] );
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="ddwcaf-configuration-container ddwcaf-padding-top-bottom-0">
                            <h2><?php esc_html_e( 'Account Info', 'affiliates-for-woocommerce' ); ?></h2>
                            <table class="form-table">
                                <tbody>
                                    <?php $affiliate_helper->ddwcaf_display_affiliate_registration_fields( $affiliate_id ); ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="ddwcaf-configuration-container ddwcaf-padding-top-bottom-0">
                            <h2><?php esc_html_e( 'Withdrawal Info', 'affiliates-for-woocommerce' ); ?></h2>
                            <p><?php esc_html_e( 'Please fill out the below withdrawal information for the commission payouts.', 'affiliates-for-woocommerce' ); ?></p>

                            <h3><?php esc_html_e( 'Withdrawal Method', 'affiliates-for-woocommerce' ); ?></h3>
                            <hr />

                            <?php
                            $available_withdrawal_methods = [];

                            foreach ( $ddwcaf_configuration[ 'withdrawal_methods' ] as $key => $withdrawal_method ) {
                                if ( ! empty( $withdrawal_method[ 'available' ] ) && ! empty( $withdrawal_method[ 'status' ] ) ) {
                                    $available_withdrawal_methods[ $key ] = $affiliate_helper->ddwcaf_get_withdrawal_method_name( $key );
                                }
                            }

                            ddwcaf_form_field( [
                                'type'                 => 'select',
                                'label'                => esc_html__( 'Default Withdrawal Method', 'affiliates-for-woocommerce' ),
                                'id'                   => 'ddwcaf-default-withdrawal-method',
                                'name'                 => '_ddwcaf_default_withdrawal_method',
                                'options'              => $available_withdrawal_methods,
                                'value'                => ! empty( $default_withdrawal_method ) ? $default_withdrawal_method : '',
                                'show_frontend_fields' => 'true',
                            ] );
                            ?>
                            <h3><?php esc_html_e( 'Direct Bank/Wire Transfer', 'affiliates-for-woocommerce' ); ?></h3>
                            <hr />
                            <table class="form-table">
                                <tbody>
                                    <?php
                                    ddwcaf_form_field( [
                                        'type'                 => 'text',
                                        'label'                => esc_html__( 'Account Name', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-withdrawal-account-name',
                                        'name'                 => '_ddwcaf_withdrawal_methods[bacs][account_name]',
                                        'placeholder'          => esc_html__( 'Enter your account name', 'affiliates-for-woocommerce' ),
                                        'value'                => ! empty( $withdrawal_methods[ 'bacs' ][ 'account_name' ] ) ? $withdrawal_methods[ 'bacs' ][ 'account_name' ] : '',
                                        'show_frontend_fields' => 'true',
                                    ] );

                                    ddwcaf_form_field( [
                                        'type'                 => 'text',
                                        'label'                => esc_html__( 'IBAN', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-withdrawal-iban',
                                        'name'                 => '_ddwcaf_withdrawal_methods[bacs][iban]',
                                        'placeholder'          => esc_html__( 'Enter your account iban', 'affiliates-for-woocommerce' ),
                                        'value'                => ! empty( $withdrawal_methods[ 'bacs' ][ 'iban' ] ) ? $withdrawal_methods[ 'bacs' ][ 'iban' ] : '',
                                        'show_frontend_fields' => 'true',
                                    ] );

                                    ddwcaf_form_field( [
                                        'type'                 => 'text',
                                        'label'                => esc_html__( 'Swift Code', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-withdrawal-swift-code',
                                        'name'                 => '_ddwcaf_withdrawal_methods[bacs][swift_code]',
                                        'placeholder'          => esc_html__( 'Enter your account swift code', 'affiliates-for-woocommerce' ),
                                        'value'                => ! empty( $withdrawal_methods[ 'bacs' ][ 'swift_code' ] ) ? $withdrawal_methods[ 'bacs' ][ 'swift_code' ] : '',
                                        'show_frontend_fields' => 'true',
                                    ] );
                                    ?>
                                </tbody>
                            </table>

                            <h3><?php esc_html_e( 'PayPal Email', 'affiliates-for-woocommerce' ); ?></h3>
                            <hr />

                            <table class="form-table">
                                <tbody>
                                    <?php
                                    ddwcaf_form_field( [
                                        'type'                 => 'text',
                                        'label'                => esc_html__( 'PayPal Email', 'affiliates-for-woocommerce' ),
                                        'id'                   => 'ddwcaf-withdrawal-paypal-email',
                                        'name'                 => '_ddwcaf_withdrawal_methods[paypal_email]',
                                        'placeholder'          => esc_html__( 'Enter your PayPal email', 'affiliates-for-woocommerce' ),
                                        'value'                => ! empty( $withdrawal_methods[ 'paypal_email' ] ) ? $withdrawal_methods[ 'paypal_email' ] : '',
                                        'show_frontend_fields' => 'true',
                                    ] );
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php
                    wp_nonce_field( 'ddwcaf_nonce_action', 'ddwcaf_nonce' );
                    submit_button( esc_html__( 'Save Changes', 'affiliates-for-woocommerce' ), 'primary', 'ddwcaf_save_affiliate_info' );
                    ?>
                </form>
            </div>
            <?php
        }
	}
}
