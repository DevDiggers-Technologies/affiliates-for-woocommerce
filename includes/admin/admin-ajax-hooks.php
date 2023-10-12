<?php
/**
 * @author DevDiggers
 * @version 1.0.0
 * This file handles all admin end ajax action hooks.
 */

namespace DDWCAffiliates\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Admin_Ajax_Hooks' ) ) {
    /**
     * Admin end ajax hook handler class
     */
    class DDWCAF_Admin_Ajax_Hooks extends DDWCAF_Admin_Ajax_Functions {
        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            parent::__construct( $ddwcaf_configuration );

            add_action( 'wp_ajax_ddwcaf_get_products_list', [ $this, 'ddwcaf_get_products_list' ] );
            add_action( 'wp_ajax_ddwcaf_get_categories_list', [ $this, 'ddwcaf_get_categories_list' ] );
            add_action( 'wp_ajax_ddwcaf_get_affiliates_list', [ $this, 'ddwcaf_get_affiliates_list' ] );
        }
    }
}
