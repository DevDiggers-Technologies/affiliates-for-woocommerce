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

$affiliate_helper = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
$affiliate_helper->ddwcaf_render_link_generator();
