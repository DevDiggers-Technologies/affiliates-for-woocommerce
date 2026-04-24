<?php
/**
 * Plugin Name: Affiliates for WooCommerce
 * Description: <code><strong>Affiliates for WooCommerce</strong></code> is a plugin for the eCommerce platform that allows website owners to set up an affiliate program for their online store. The plugin allows affiliates to promote the store and earn a commission on sales made through their unique affiliate links.
 * Plugin URI: https://devdiggers.com/woocommerce-extensions/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugins List&utm_campaign=WooCommerce Extensions
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 2.0.2
 * Text Domain: affiliates-for-woocommerce
 * Domain Path: /i18n
 * WC requires at least: 5.0.0
 * WC tested up to: 10.6.0
 * WP requires at least: 5.0.0
 * WP tested up to: 6.9.4
 * DevDiggersPrefix: ddwcaf
 * Requires Plugins: woocommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Affiliates for WooCommerce
 */

// ddwcaf: Affiliates for WooCommerce.

use DDWCAffiliates\Includes\DDWCAF_File_Handler;

defined( 'ABSPATH' ) || exit();

// Define Constants.
defined( 'DEVDIGGERS_FREE_PLUGIN' ) || define( 'DEVDIGGERS_FREE_PLUGIN', true );

if ( ! class_exists( 'DDWCAF_Free_Init' ) ) {
	/**
	 * Init class
	 */
	final class DDWCAF_Free_Init {
		/**
		 * Instance variable
		 *
		 * @var DDWCAF_Free_Init|null
		 */
		private static $_instance = null;

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'ddwcaf_init' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'ddwcaf_plugin_settings_link' ] );
			add_filter( 'plugin_row_meta', [ $this, 'ddwcaf_plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Create a plugin instance.
		 *
		 * @return static
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();

				/**
				 * Action hook fired when the main plugin instance is loaded.
				 *
				 * @since 1.0.0
				 */
				do_action( 'ddwcaf_loaded' );
			}

			return self::$_instance;
		}

		/**
		 * Init function
		 *
		 * @return void
		 */
		public function ddwcaf_init() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', function () {
					?>
					<div class="error">
						<p>
							<?php
							/* translators: %s for a opening tag and %s for a closing tag */
							echo sprintf( esc_html__( 'Affiliates for WooCommerce is activated but not effective. It requires %sWooCommerce Plugin%s in order to use its functionalities.', 'affiliates-for-woocommerce' ), '<a href="' . esc_url( '//wordpress.org/plugins/woocommerce/' ) . '" target="_blank">', '</a>' );
							?>
						</p>
					</div>
					<?php
				} );
			} else {
				require_once DDWCAF_PLUGIN_FILE . 'autoload/autoload.php';
				new DDWCAF_File_Handler();

				// Initialize review notice if framework is available.
				new \DevDiggers\Framework\Includes\DDFW_Review_Notice( [
					'plugin_name'   => esc_html__( 'Affiliates for WooCommerce', 'affiliates-for-woocommerce' ),
					'plugin_prefix' => 'ddwcaf',
					'review_url'    => 'https://wordpress.org/support/plugin/affiliates-for-woocommerce/reviews/#new-post',
				] );
			}
		}

		/**
		 * Plugin settings link
		 *
		 * @param array $links Links Array.
		 * @return array $links
		 */
		public function ddwcaf_plugin_settings_link( $links ) {
			ob_start();
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcaf-dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'affiliates-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcaf-dashboard&menu=configuration' ) ); ?>"><?php esc_html_e( 'Configuration', 'affiliates-for-woocommerce' ); ?></a>
			|
			<a href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=plugin_link&utm_medium=upgrade_button&utm_campaign=plugin_upgrade" style="color: #0256ff; font-weight: bold;" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'affiliates-for-woocommerce' ); ?></a>
			<?php
			$new_links = ob_get_clean();
			array_unshift( $links, $new_links );
			return $links;
		}

		/**
		 * Plugin Doc link
		 *
		 * @param array  $links Links.
		 * @param string $file File name.
		 * @return array $links
		 */
		public function ddwcaf_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$row_meta = [
					'support'       => '<a href="//devdiggers.com/contact/" aria-label="' . esc_attr__( 'Support', 'affiliates-for-woocommerce' ) . '">' . esc_html__( 'Support', 'affiliates-for-woocommerce' ) . '</a>',
					'documentation' => '<a href="//devdiggers.com/affiliates-for-woocommerce/" aria-label="' . esc_attr__( 'Documentation', 'affiliates-for-woocommerce' ) . '">' . esc_html__( 'Documentation', 'affiliates-for-woocommerce' ) . '</a>',
					'review'        => '<a href="//devdiggers.com/product/woocommerce-affiliates/" target="_blank" title="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>',
				];
				$links = array_merge( $links, $row_meta );
			}

			return $links;
		}
	}
}

if ( ! class_exists( 'DDWCAF_Init' ) ) {

}
// Load DevDiggers Framework if not loaded already.
add_action( 'plugins_loaded', function() {
	if ( ! class_exists( 'DDWCAF_Init' ) ) {
		defined( 'DDWCAF_PLUGIN_FILE' ) || define( 'DDWCAF_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
		defined( 'DDWCAF_PLUGIN_URL' ) || define( 'DDWCAF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		// Load Free version.
		DDWCAF_Free_Init::get_instance();

		// Load DevDiggers Framework if not loaded already.
		if ( ! defined( 'DDFW_LOADED' ) && file_exists( DDWCAF_PLUGIN_FILE . 'devdiggers-framework/init.php' ) ) {
			$should_load = true;

			if ( ! empty( $_GET['page'] ) ) {
				$current_page = $_GET['page'];
				$prefix       = explode( '-', $current_page )[0];

				if ( 0 === strpos( $prefix, 'ddwc' ) || 0 === strpos( $prefix, 'ddwp' ) ) {
					$pro_class  = strtoupper( $prefix ) . '_Init';
					$free_class = strtoupper( $prefix ) . '_Free_Init';

					if ( class_exists( $free_class ) && ! class_exists( $pro_class ) && 'ddwcaf' !== $prefix ) {
						$should_load = false;
					}
				}
			}

			if ( $should_load ) {
				require DDWCAF_PLUGIN_FILE . 'devdiggers-framework/init.php';
			}
		}
	}
}, 10 );


// For HPOS Compatibility
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

require_once plugin_dir_path( __FILE__ ) . 'includes/install.php';
register_activation_hook( __FILE__, [ 'DDWCAF_Install', 'ddwcaf_on_plugin_activation' ] );
