<?php
/**
 * Plugin Name: Affiliates for WooCommerce
 * Description: <code><strong>Affiliates for WooCommerce</strong></code> is a plugin for the e-commerce platform that allows website owners to set up an affiliate program for their online store. The plugin allows affiliates to promote the store and earn a commission on sales made through their unique affiliate links.
 * Plugin URI: https://devdiggers.com/woocommerce-extensions/?utm_source=Affiliates for WooCommerce Plugin&utm_medium=Plugins List&utm_campaign=WooCommerce Extensions
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.1.0
 * Text Domain: affiliates-for-woocommerce
 * Domain Path: /i18n
 * WC requires at least: 5.0.0
 * WC tested up to: 8.8.2
 * WP requires at least: 5.0.0
 * WP tested up to: 6.5.2
 * Requires Plugins: woocommerce
 *
 * @package Affiliates for WooCommerce
 */

// ddwcaf: Affiliates for WooCommerce.

use DDWCAffiliates\Includes\DDWCAF_File_Handler;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Free_Init' ) ) {
	/**
	 * Init class
	 */
	class DDWCAF_Free_Init {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'plugins_loaded', [ $this, 'ddwcaf_loaded' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'ddwcaf_plugin_settings_link' ] );
			add_filter( 'plugin_row_meta',[ $this, 'ddwcaf_plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Plugin loaded function
		 *
		 * @return void
		 */
		public function ddwcaf_loaded() {
			load_plugin_textdomain( 'affiliates-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/i18n' );

			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', function () {
					?>
					<div class="error">
						<p>
							<?php
							/* translators: %1$s for a opening tag and %2$s for a closing tag */
							echo sprintf( esc_html__( 'Affiliates for WooCommerce is activated but not effective. It requires %sWooCommerce Plugin%s in order to use its functionalities.', 'affiliates-for-woocommerce' ), '<a href="' . esc_url( '//wordpress.org/plugins/woocommerce/' ) . '" target="_blank">', '</a>' );
							?>
						</p>
					</div>
					<?php
				} );
			} else if ( ! class_exists( 'DDWCAF_Init' ) ) {
				// Define Constants.
				defined( 'DDWCAF_PLUGIN_FILE' ) || define( 'DDWCAF_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
				defined( 'DDWCAF_PLUGIN_URL' ) || define( 'DDWCAF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
				defined( 'DDWCAF_SCRIPT_VERSION' ) || define( 'DDWCAF_SCRIPT_VERSION', '1.0.0' );

				require_once DDWCAF_PLUGIN_FILE . 'autoload/autoload.php';
				new DDWCAF_File_Handler();
			}

			// For HPOS Compatibility
			add_action( 'before_woocommerce_init', function() {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			} );
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
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcaf-configuration' ) ); ?>"><?php esc_html_e( 'Configuration', 'affiliates-for-woocommerce' ); ?></a> | 
			<a class="ddwcaf-go-pro" href="//devdiggers.com/product/woocommerce-affiliates/?utm_source=Affiliates for WooCommerce Free Plugin&utm_medium=Plugins List&utm_campaign=Go Pro" target="_blank"><?php esc_html_e( 'GO PRO', 'affiliates-for-woocommerce' ); ?></a>
			<?php
			array_unshift( $links, ob_get_clean() );
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
					'review'        => '<a href="//wordpress.org/support/plugin/woocommerce-affiliates/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'affiliates-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>',
				];
				$links = array_merge( $links, $row_meta );
			}

			return $links;
		}
	}
}

new DDWCAF_Free_Init();

require_once plugin_dir_path( __FILE__ ) . 'includes/install.php';
register_activation_hook( __FILE__, [ 'DDWCAF_Install', 'ddwcaf_on_plugin_activation' ] );