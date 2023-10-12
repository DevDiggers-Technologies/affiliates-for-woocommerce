<?php
/**
 * Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Configuration_Template' ) ) {
	/**
	 * Configuration template class
	 */
	class DDWCAF_Configuration_Template {
		/**
		 * Construct
         * 
         * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
            $tabs = [
                'general'     => esc_html__( 'General', 'affiliates-for-woocommerce' ),
                'referrals'   => esc_html__( 'Referrals', 'affiliates-for-woocommerce' ),
                'commissions' => esc_html__( 'Commissions', 'affiliates-for-woocommerce' ),
                'shortcodes'  => esc_html__( 'Shortcodes', 'affiliates-for-woocommerce' ),
                'emails'      => esc_html__( 'Emails', 'affiliates-for-woocommerce' ),
                'endpoints'   => esc_html__( 'Endpoints', 'affiliates-for-woocommerce' ),
            ];

            $page        = sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) );
            $current_tab = ! empty( $_GET[ 'tab' ] ) ? sanitize_title( $_GET[ 'tab' ] ) : 'general';
            ?>
            <div class="wrap">
                <nav class="nav-tab-wrapper">
                    <?php
                    foreach ( $tabs as $key => $label ) {
                        ?>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=$page&tab=$key" ) ); ?>" class="nav-tab <?php echo esc_attr( $current_tab == $key ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( $label ); ?></a>
                        <?php
                    }
                    ?>
                </nav>
                <?php
                $current_tab = ucfirst( $current_tab );
                $class_name = "DDWCAffiliates\Templates\Admin\Configuration\DDWCAF_{$current_tab}_Configuration_Template";
                new $class_name( $ddwcaf_configuration );
                ?>
            </div>
            <?php
        }
	}
}
