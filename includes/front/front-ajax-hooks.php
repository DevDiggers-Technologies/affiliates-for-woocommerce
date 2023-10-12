<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all front end action ajax hooks.
 */

namespace DDWCAffiliates\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Front_Ajax_Hooks' ) ) {
    /**
     * Front end ajax hooks class
     */
    class DDWCAF_Front_Ajax_Hooks extends DDWCAF_Front_Ajax_Functions {
        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            parent::__construct( $ddwcaf_configuration );

            add_action( 'wp_ajax_ddwcaf_get_custom_referral_html', [ $this, 'ddwcaf_get_custom_referral_html' ] );
        }
    }
}
