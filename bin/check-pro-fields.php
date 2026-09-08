<?php
// Builds every configuration tab against WordPress stubs, with no database, and
// asserts that every field inside a pro-locked section is disabled. A pro teaser
// that is editable looks like a bug: the admin changes it and the value never saves.
// Run: php bin/check-pro-fields.php

namespace {
define( 'ABSPATH', true );
$GLOBALS['captured'] = [];

function esc_html__( $s, $d = '' ) { return $s; }
function esc_html_e( $s, $d = '' ) { echo $s; }
function esc_attr__( $s, $d = '' ) { return $s; }
function esc_attr_e( $s, $d = '' ) { echo $s; }
function esc_attr( $s ) { return $s; }
function esc_html( $s ) { return $s; }
function esc_url( $s ) { return $s; }
function __( $s, $d = '' ) { return $s; }
function sanitize_text_field( $s ) { return $s; }
function wp_unslash( $s ) { return $s; }
function flush_rewrite_rules() {}
function wp_clear_scheduled_hook( $h ) {}
function get_pages() { return []; }
function get_woocommerce_currency_symbol() { return '$'; }
function get_woocommerce_currency() { return 'USD'; }
function checked( $a, $b = true, $e = true ) {}
function selected( $a, $b = true, $e = true ) {}
function function_exists_stub() {}
function apply_filters( $t, $v ) { return $v; }
function wc_price( $p ) { return $p; }
function site_url( $p = '' ) { return 'https://example.com' . $p; }
function home_url( $p = '' ) { return 'https://example.com' . $p; }
function admin_url( $p = '' ) { return 'https://example.com/wp-admin/' . $p; }
function wp_kses_post( $s ) { return $s; }
function get_option( $k, $d = false ) { return $d; }

}

namespace DevDiggers\Framework\Includes {
    class DDFW_Layout {
        public function get_form_section_layout( $args, $group, $extra = [], $form = '' ) {
            $GLOBALS['captured'][ $group ] = $args;
        }
    }
    class DDFW_SVG { public static function get_svg_icon() {} }
    class DDFW_Form_Field { public static function display_form_field( $f ) {} }
}

namespace DDWCAffiliates\Helper\Affiliate {
    class DDWCAF_Affiliate_Helper {
        public function __construct( $c = [] ) {}
        public function ddwcaf_get_withdrawal_methods() { return []; }
        public function __call( $n, $a ) { return []; }
    }
}

namespace {
    $root = dirname( __DIR__ );
    $base = $root . '/templates/admin/configuration/';

    // Minimal configuration array: every key the free templates read.
    $config = [];
    $src = file_get_contents( $root . '/includes/common/common-functions.php' );
    preg_match_all( "/'([a-z0-9_]+)'\s*=>/", $src, $m );
    foreach ( $m[1] as $k ) { $config[ $k ] = ''; }
    $config['user_roles'] = [];
    $config['withdrawal_methods'] = [];
    $config['multi_level_rules'] = [];
    $config['excluded_products'] = [];
    $config['excluded_categories'] = [];

    $map = [
        'general'     => 'DDWCAF_General_Configuration_Template',
        'commissions' => 'DDWCAF_Commissions_Configuration_Template',
        'referrals'   => 'DDWCAF_Referrals_Configuration_Template',
        'shortcodes'  => 'DDWCAF_Shortcodes_Configuration_Template',
        'payouts'     => 'DDWCAF_Payouts_Configuration_Template',
    ];

    $fail = 0;
    foreach ( $map as $slug => $class ) {
        require_once $base . $slug . '-configuration-template.php';
        $fqcn = 'DDWCAffiliates\\Templates\\Admin\\Configuration\\' . $class;
        try {
            ob_start();
            new $fqcn( $config );
            ob_end_clean();
        } catch ( \Throwable $e ) {
            ob_end_clean();
            echo "FAIL $slug: " . $e->getMessage() . "\n";
            $fail++;
            continue;
        }
    }

    foreach ( $GLOBALS['captured'] as $group => $args ) {
        $pro = 0; $fields = 0;
        foreach ( $args as $sec ) {
            if ( ! empty( $sec['class'] ) && false !== strpos( $sec['class'], 'upgrade-to-pro' ) ) { $pro++; }
            $fields += count( $sec['fields'] ?? [] );
        }
        printf( "%-42s sections=%d pro-sections=%d fields=%d\n", $group, count( $args ), $pro, $fields );
    }

    // Every pro-locked field must be disabled, or a visitor could edit a setting that never saves.
    foreach ( $GLOBALS['captured'] as $group => $args ) {
        foreach ( $args as $sec ) {
            $section_pro = ! empty( $sec['class'] ) && false !== strpos( $sec['class'], 'upgrade-to-pro' );
            foreach ( $sec['fields'] ?? [] as $f ) {
                $field_pro = ! empty( $f['class'] ) && false !== strpos( (string) $f['class'], 'upgrade-to-pro' );
                if ( ! $section_pro && ! $field_pro ) { continue; }
                if ( empty( $f['custom_attributes']['disabled'] ) ) {
                    echo "FAIL editable pro field: {$group} / {$f['id']}\n";
                    $fail++;
                }
            }
        }
    }

    echo $fail ? "\n$fail FAILURE(S)\n" : "\nALL OK\n";
    exit( $fail ? 1 : 0 );
}
