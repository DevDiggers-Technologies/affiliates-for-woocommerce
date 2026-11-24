<?php
/**
 * After the approval page, for apps that take the answer through their own link ( claude://, cursor:// ).
 *
 * The browser opens the app but stays on the page it was on, so this page replaces the approval form.
 * Variables: $url ( the link back to the app ), $allowed, $destination.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 * @version 1.3.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo $allowed ? esc_html__( 'Connected', 'affiliates-for-woocommerce' ) : esc_html__( 'Connection cancelled', 'affiliates-for-woocommerce' ); ?></title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; background: #f6f7f9; color: #111827; font: 14px/1.55 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
		.card { width: 100%; max-width: 440px; padding: 28px; background: #fff; border: 1px solid #ebebeb; border-radius: 12px; }
		.site { margin: 0 0 20px; color: #6b7280; font-size: 13px; }
		h1 { margin: 0 0 8px; font-size: 19px; font-weight: 600; line-height: 1.35; }
		p { margin: 0 0 14px; }
		.muted { color: #6b7280; font-size: 13px; }
		.open { display: flex; align-items: center; justify-content: center; height: 40px; margin-top: 20px; border-radius: 8px; background: #0256ff; color: #fff; font-weight: 600; text-decoration: none; }
		.open:hover { background: #0145d1; }
		.open:focus-visible { outline: 2px solid #0256ff; outline-offset: 2px; }
	</style>
</head>
<body>
	<div class="card">
		<p class="site"><?php echo esc_html( get_bloginfo( 'name' ) ); ?> &middot; <?php esc_html_e( 'DevDiggers MCP', 'affiliates-for-woocommerce' ); ?></p>
		<h1><?php echo $allowed ? esc_html__( 'Connected. You can close this tab.', 'affiliates-for-woocommerce' ) : esc_html__( 'Connection cancelled', 'affiliates-for-woocommerce' ); ?></h1>
		<p class="muted">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: where the person goes back to, such as the claude app on this computer. */
					__( 'Your answer was sent to %s. If it did not open, use the button below.', 'affiliates-for-woocommerce' ),
					'<strong>' . esc_html( $destination[0] ) . '</strong>'
				),
				[ 'strong' => [] ]
			);
			?>
		</p>
		<a class="open" href="<?php echo esc_url( $url, [ $scheme ] ); ?>"><?php esc_html_e( 'Open the app', 'affiliates-for-woocommerce' ); ?></a>
	</div>
	<script>window.location.href = <?php echo wp_json_encode( esc_url_raw( $url, [ $scheme ] ) ); ?>;</script>
</body>
</html>
