<?php
/**
 * Approval page an AI app opens to connect to the DevDiggers MCP server ( OAuth consent ).
 *
 * A page of its own, outside the wp-admin chrome, like any sign-in screen. Variables: $client, $params.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 * @version 1.3.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
$hub         = DDFW_MCP::instance();
$user        = wp_get_current_user();
$destination = DDFW_MCP_OAuth::describe_redirect( $params['redirect_uri'] );
$back_url    = add_query_arg( array_map( 'rawurlencode', array_filter( $params, 'strlen' ) + [ 'action' => DDFW_MCP_OAuth::ACTION ] ), admin_url( 'admin-post.php' ) );
$plugin_list = [];
$hub_plugins = $hub->get_plugins();

foreach ( $hub->get_abilities() as $slug => $abilities ) {
	if ( $hub->is_plugin_enabled( $slug ) ) {
		$plugin_list[] = $hub_plugins[ $slug ]['name'];
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php esc_html_e( 'Connect an AI app', 'affiliates-for-woocommerce' ); ?></title>
	<style>
		* { box-sizing: border-box; }
		body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; background: #f6f7f9; color: #111827; font: 14px/1.55 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
		.card { width: 100%; max-width: 440px; padding: 28px; background: #fff; border: 1px solid #ebebeb; border-radius: 12px; }
		.site { margin: 0 0 20px; color: #6b7280; font-size: 13px; }
		h1 { margin: 0 0 8px; font-size: 19px; font-weight: 600; line-height: 1.35; }
		p { margin: 0 0 14px; }
		.muted { color: #6b7280; font-size: 13px; }
		ul { margin: 0 0 16px; padding: 14px 16px; list-style: none; background: #f9fafb; border-radius: 8px; }
		li { position: relative; padding-left: 22px; margin: 6px 0; font-size: 13px; }
		li::before { content: ""; position: absolute; left: 2px; top: 6px; width: 10px; height: 5px; border-left: 2px solid #15803d; border-bottom: 2px solid #15803d; transform: rotate(-45deg); }
		.warn { padding: 10px 12px; background: #fef3c7; border-radius: 6px; color: #92400e; font-size: 13px; }
		.actions { display: flex; gap: 10px; margin-top: 20px; }
		button { flex: 1; height: 40px; border-radius: 8px; font: inherit; font-weight: 600; cursor: pointer; }
		.allow { border: 0; background: #0256ff; color: #fff; }
		.allow:hover { background: #0145d1; }
		.deny { border: 1px solid #e5e7eb; background: #fff; color: #111827; }
		.deny:hover { background: #f9fafb; }
		button:focus-visible { outline: 2px solid #0256ff; outline-offset: 2px; }
		a { color: #0256ff; }
	</style>
</head>
<body>
	<form class="card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<p class="site"><?php echo esc_html( get_bloginfo( 'name' ) ); ?> &middot; <?php esc_html_e( 'DevDiggers MCP', 'affiliates-for-woocommerce' ); ?></p>

		<h1>
			<?php
			/* translators: %s: name the AI app gave itself. */
			echo esc_html( sprintf( __( '"%s" wants to connect to your store', 'affiliates-for-woocommerce' ), $client['name'] ) );
			?>
		</h1>
		<p class="muted">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: 1: user name, 2: log out link. */
					__( 'It will act as %1$s. Not you? %2$s', 'affiliates-for-woocommerce' ),
					'<strong>' . esc_html( $user->display_name ) . '</strong>',
					'<a href="' . esc_url( wp_logout_url( $back_url ) ) . '">' . esc_html__( 'Switch account', 'affiliates-for-woocommerce' ) . '</a>'
				),
				[
					'strong' => [],
					'a'      => [ 'href' => [] ],
				]
			);
			?>
		</p>

		<ul>
			<li>
				<?php
				echo esc_html(
					$plugin_list
						/* translators: %s: plugin names. */
						? sprintf( __( 'Use the AI tools of %s, with your permissions', 'affiliates-for-woocommerce' ), implode( ', ', $plugin_list ) )
						: __( 'Use the DevDiggers AI tools you turn on, with your permissions', 'affiliates-for-woocommerce' )
				);
				?>
			</li>
			<li><?php esc_html_e( 'Change data only with tools you allowed to make changes', 'affiliates-for-woocommerce' ); ?></li>
			<li><?php esc_html_e( 'Nothing else on your site: no password, no other API', 'affiliates-for-woocommerce' ); ?></li>
		</ul>

		<p class="muted">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: where the person goes back to, such as claude.ai. */
					__( 'After you allow it, you go back to %s. Revoke it any time under DevDiggers Plugins > AI & MCP.', 'affiliates-for-woocommerce' ),
					'<strong>' . esc_html( $destination[0] ) . '</strong>'
				),
				[ 'strong' => [] ]
			);
			?>
		</p>

		<?php if ( ! $destination[1] ) : ?>
			<p class="warn"><?php esc_html_e( 'This is not an address of a known AI app. Allow it only if you started this connection yourself, just now.', 'affiliates-for-woocommerce' ); ?></p>
		<?php endif; ?>

		<?php foreach ( $params as $key => $value ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php endforeach; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( DDFW_MCP_OAuth::ACTION ); ?>" />
		<?php wp_nonce_field( DDFW_MCP_OAuth::ACTION . $params['client_id'] ); ?>

		<div class="actions">
			<button type="submit" class="deny" name="allow" value=""><?php esc_html_e( 'Cancel', 'affiliates-for-woocommerce' ); ?></button>
			<button type="submit" class="allow" name="allow" value="1"><?php esc_html_e( 'Allow', 'affiliates-for-woocommerce' ); ?></button>
		</div>
	</form>
</body>
</html>
