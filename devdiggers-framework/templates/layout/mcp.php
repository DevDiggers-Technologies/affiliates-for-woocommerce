<?php
/**
 * DevDiggers AI & MCP hub page.
 *
 * Three blocks: the server ( status, URL, connected apps, Connect ), the tools of each plugin ( switches, Try )
 * and the activity log. Connecting and trying a tool open native dialogs. Apps sign in with OAuth: the page
 * only shows how to add the server, and a connection appears once someone allows it.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 * @version 1.3.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
$hub         = DDFW_MCP::instance();
$settings    = $hub->get_settings();
$can_manage  = $hub->can_manage();
$hub_plugins = $hub->get_plugins();
$grouped     = $hub->get_abilities();
$connections = $hub->oauth->get_connections( $can_manage );

/*
 * Activity: filters come from the URL, so a filtered page can be bookmarked and Back works. Only those who
 * manage the hub see everyone's calls; everyone else sees their own.
 */
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filters of an admin page.
$log_user_id = $can_manage ? 0 : get_current_user_id();
$log_apps    = $hub->get_log_apps( $log_user_id );
$log_filters = [
	'plugin' => isset( $_GET['log_plugin'] ) ? sanitize_key( wp_unslash( $_GET['log_plugin'] ) ) : '',
	'status' => isset( $_GET['log_status'] ) && 'failed' === $_GET['log_status'] ? 'failed' : '',
	'app'    => isset( $_GET['log_app'] ) ? sanitize_text_field( wp_unslash( $_GET['log_app'] ) ) : '',
	'user'   => $log_user_id,
	'page'   => isset( $_GET['log_page'] ) ? max( 1, absint( $_GET['log_page'] ) ) : 1,
];
// phpcs:enable

// Unknown values are dropped rather than matching nothing.
$log_filters['plugin'] = isset( $hub_plugins[ $log_filters['plugin'] ] ) ? $log_filters['plugin'] : '';
$log_filters['app']    = in_array( $log_filters['app'], $log_apps, true ) ? $log_filters['app'] : '';

$log         = $hub->get_log( $log_filters );
$log_pages   = max( 1, (int) ceil( $log['total'] / DDFW_MCP::LOG_PER_PAGE ) );
$is_filtered = $log_filters['plugin'] || $log_filters['status'] || $log_filters['app'];

// A page past the end ( after Clear, or an old link ) shows the last one.
if ( $log_filters['page'] > $log_pages ) {
	$log_filters['page'] = $log_pages;
	$log                 = $hub->get_log( $log_filters );
}

$log_url     = function ( $page = 1 ) use ( $log_filters ) {
	return add_query_arg(
		array_filter(
			[
				'page'       => DDFW_MCP::PAGE,
				'log_plugin' => $log_filters['plugin'],
				'log_status' => $log_filters['status'],
				'log_app'    => $log_filters['app'],
				'log_page'   => $page > 1 ? $page : '',
			]
		),
		admin_url( 'admin.php' )
	) . '#ddfw-mcp-activity';
};
$server_url  = DDFW_MCP::server_url();
$svg_open    = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
$svg_small   = str_replace( [ 'width="20"', 'height="20"' ], [ 'width="16"', 'height="16"' ], $svg_open );
$svg_tags    = ddfw_kses_allowed_svg_tags();
$date_format = get_option( 'date_format' );
$ready       = DDFW_MCP::adapter_available();
$can_connect = $ready && DDFW_MCP_OAuth::available() && $hub->can_connect();

if ( ! DDFW_MCP::abilities_available() ) {
	$status_text = esc_html__( 'Needs WordPress 6.9 or later', 'affiliates-for-woocommerce' );
	$status_note = esc_html__( 'AI tools are built on the WordPress Abilities API, added in WordPress 6.9.', 'affiliates-for-woocommerce' );
} elseif ( ! $ready ) {
	$status_text = esc_html__( 'Needs the MCP Adapter', 'affiliates-for-woocommerce' );
	$status_note = esc_html__( 'Update WooCommerce to 10.3 or later (it includes the MCP Adapter), or install the MCP Adapter plugin.', 'affiliates-for-woocommerce' );
} elseif ( ! DDFW_MCP_OAuth::available() ) {
	$status_text = esc_html__( 'Needs pretty permalinks', 'affiliates-for-woocommerce' );
	$status_note = esc_html__( 'AI apps sign in with OAuth, which needs a permalink structure other than Plain. Change it under Settings > Permalinks.', 'affiliates-for-woocommerce' );
} else {
	$status_text = esc_html__( 'Server running', 'affiliates-for-woocommerce' );
	$status_note = '';
}

// Claude and ChatGPT connect from their own servers: they reach only a site that is online over HTTPS.
$site_host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
$is_public = 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) && 'local' !== wp_get_environment_type() && ! preg_match( '/(^localhost$|^127\.|\.local$|\.test$|\.localhost$)/', $site_host );

// Live tools, and the tool labels the log shows.
$live_tools = 0;
$labels     = [];

foreach ( $grouped as $abilities ) {
	foreach ( $abilities as $ability ) {
		$labels[ str_replace( '/', '-', $ability->get_name() ) ] = $ability->get_label();
		$live_tools += $hub->is_allowed( $ability ) ? 1 : 0;
	}
}

// Why a call failed, in words.
$reasons = [
	'wp_error'                 => esc_html__( 'Failed', 'affiliates-for-woocommerce' ),
	'execution_failed'         => esc_html__( 'Crashed', 'affiliates-for-woocommerce' ),
	'permission_denied'        => esc_html__( 'Not allowed', 'affiliates-for-woocommerce' ),
	'not_found'                => esc_html__( 'Unknown tool', 'affiliates-for-woocommerce' ),
	'missing_parameter'        => esc_html__( 'Missing input', 'affiliates-for-woocommerce' ),
	'exception'                => esc_html__( 'Crashed', 'affiliates-for-woocommerce' ),
	'ability_retrieval_failed' => esc_html__( 'Unknown tool', 'affiliates-for-woocommerce' ),
];

// Brand marks from Simple Icons (CC0), shown only to name the app a setup is for.
$brand_icons = [
	'claude' => [ '#D97757', 'm4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z' ],
	'openai' => [ '#111827', 'M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z' ],
	'cursor' => [ '#111827', 'M11.503.131 1.891 5.678a.84.84 0 0 0-.42.726v11.188c0 .3.162.575.42.724l9.609 5.55a1 1 0 0 0 .998 0l9.61-5.55a.84.84 0 0 0 .42-.724V6.404a.84.84 0 0 0-.42-.726L12.497.131a1.01 1.01 0 0 0-.996 0M2.657 6.338h18.55c.263 0 .43.287.297.515L12.23 22.918c-.062.107-.229.064-.229-.06V12.335a.59.59 0 0 0-.295-.51l-9.11-5.257c-.109-.063-.064-.23.061-.23' ],
	'vscode' => [ '#007ACC', 'M23.15 2.587L18.21.21a1.494 1.494 0 0 0-1.705.29l-9.46 8.63-4.12-3.128a.999.999 0 0 0-1.276.057L.327 7.261A1 1 0 0 0 .326 8.74L3.899 12 .326 15.26a1 1 0 0 0 .001 1.479L1.65 17.94a.999.999 0 0 0 1.276.057l4.12-3.128 9.46 8.63a1.492 1.492 0 0 0 1.704.29l4.942-2.377A1.5 1.5 0 0 0 24 20.06V3.939a1.5 1.5 0 0 0-.85-1.352zm-5.146 14.861L10.826 12l7.178-5.448v10.896z' ],
];

$brand_svg = function ( $brand ) use ( $brand_icons ) {
	if ( isset( $brand_icons[ $brand ] ) ) {
		return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="' . esc_attr( $brand_icons[ $brand ][0] ) . '" d="' . esc_attr( $brand_icons[ $brand ][1] ) . '"/></svg>';
	}

	// A plug, for any other app.
	return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 22v-5M9 8V2M15 8V2M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8z"/></svg>';
};

$json = function ( $value ) {
	return wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
};

/*
 * How to add the server in each app. Nothing here is secret: the app opens WordPress to sign in. 'step' is the
 * step the snippet goes under; 'prompt' apps run on this computer, so an AI agent there can do the setup.
 */
$clients = [
	'claude'      => [
		'label' => __( 'Claude', 'affiliates-for-woocommerce' ),
		'sub'   => __( 'Web, desktop and mobile', 'affiliates-for-woocommerce' ),
		'brand' => 'claude',
		'cloud' => true,
		'code'  => $server_url,
		'file'  => __( 'Server URL', 'affiliates-for-woocommerce' ),
		'step'  => 1,
		'steps' => [
			__( 'In Claude, open Settings > Connectors and click Add custom connector.', 'affiliates-for-woocommerce' ),
			__( 'Name it DevDiggers and paste this URL:', 'affiliates-for-woocommerce' ),
			__( 'Click Connect, then Allow on the page that opens.', 'affiliates-for-woocommerce' ),
		],
	],
	'chatgpt'     => [
		'label' => __( 'ChatGPT', 'affiliates-for-woocommerce' ),
		'sub'   => __( 'Developer mode, paid plans', 'affiliates-for-woocommerce' ),
		'brand' => 'openai',
		'cloud' => true,
		'code'  => $server_url,
		'file'  => __( 'Server URL', 'affiliates-for-woocommerce' ),
		'step'  => 1,
		'steps' => [
			__( 'In ChatGPT, open Settings > Apps > Advanced settings and turn on Developer mode.', 'affiliates-for-woocommerce' ),
			__( 'Click Create app, name it DevDiggers, choose OAuth and paste this URL:', 'affiliates-for-woocommerce' ),
			__( 'Click Allow on the page that opens.', 'affiliates-for-woocommerce' ),
		],
	],
	'claude-code' => [
		'label'  => __( 'Claude Code', 'affiliates-for-woocommerce' ),
		'sub'    => __( 'Terminal and IDE', 'affiliates-for-woocommerce' ),
		'brand'  => 'claude',
		'code'   => 'claude mcp add --transport http devdiggers ' . $server_url,
		'file'   => __( 'Terminal', 'affiliates-for-woocommerce' ),
		'step'   => 0,
		'prompt' => true,
		'steps'  => [
			__( 'Run this in a terminal:', 'affiliates-for-woocommerce' ),
			__( 'In Claude Code, type /mcp, pick devdiggers and choose Authenticate.', 'affiliates-for-woocommerce' ),
			__( 'Click Allow on the page that opens.', 'affiliates-for-woocommerce' ),
		],
	],
	'codex'       => [
		'label'  => __( 'Codex', 'affiliates-for-woocommerce' ),
		'sub'    => __( 'OpenAI CLI and IDE extension', 'affiliates-for-woocommerce' ),
		'brand'  => 'openai',
		'code'   => 'codex mcp add devdiggers --url ' . $server_url,
		'file'   => __( 'Terminal', 'affiliates-for-woocommerce' ),
		'step'   => 0,
		'prompt' => true,
		'steps'  => [
			__( 'Run this in a terminal:', 'affiliates-for-woocommerce' ),
			__( 'Then run codex mcp login devdiggers and click Allow on the page that opens.', 'affiliates-for-woocommerce' ),
		],
	],
	'cursor'      => [
		'label'  => __( 'Cursor', 'affiliates-for-woocommerce' ),
		'sub'    => __( 'Code editor', 'affiliates-for-woocommerce' ),
		'brand'  => 'cursor',
		'code'   => $json( [ 'mcpServers' => [ 'devdiggers' => [ 'url' => $server_url ] ] ] ),
		'file'   => '~/.cursor/mcp.json',
		'step'   => 0,
		'prompt' => true,
		'steps'  => [
			__( 'Add this to ~/.cursor/mcp.json, or under Settings > MCP > Add new server:', 'affiliates-for-woocommerce' ),
			__( 'Cursor shows Needs login next to devdiggers. Click it, then Allow on the page that opens.', 'affiliates-for-woocommerce' ),
		],
	],
	'vscode'      => [
		'label'  => __( 'VS Code', 'affiliates-for-woocommerce' ),
		'sub'    => __( 'GitHub Copilot agent mode', 'affiliates-for-woocommerce' ),
		'brand'  => 'vscode',
		'code'   => $json(
			[
				'servers' => [
					'devdiggers' => [
						'type' => 'http',
						'url'  => $server_url,
					],
				],
			]
		),
		'file'   => 'mcp.json',
		'step'   => 0,
		'prompt' => true,
		'steps'  => [
			__( 'Run MCP: Open User Configuration from the Command Palette and add this:', 'affiliates-for-woocommerce' ),
			__( 'Click Start above the server, allow the sign-in, then click Allow on the page that opens.', 'affiliates-for-woocommerce' ),
		],
	],
	'other'       => [
		'label' => __( 'Other app', 'affiliates-for-woocommerce' ),
		'sub'   => __( 'Any MCP client', 'affiliates-for-woocommerce' ),
		'brand' => '',
		'code'  => $server_url,
		'file'  => __( 'Streamable HTTP', 'affiliates-for-woocommerce' ),
		'step'  => 0,
		'steps' => [
			__( 'Add a remote MCP server with this URL. Apps with OAuth sign-in open the approval page themselves:', 'affiliates-for-woocommerce' ),
			/* translators: %s: server URL. */
			sprintf( __( 'An app that only starts local commands can connect through mcp-remote: npx -y mcp-remote %s', 'affiliates-for-woocommerce' ), $server_url ),
		],
	],
];

$kinds = [
	'read'   => esc_html__( 'Read', 'affiliates-for-woocommerce' ),
	'write'  => esc_html__( 'Write', 'affiliates-for-woocommerce' ),
	'prompt' => esc_html__( 'Prompt', 'affiliates-for-woocommerce' ),
];

// The AI mark.
$hub_icon  = '<path d="M12 3l1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/>';
$x_icon    = '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>';
$copy_icon = '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>';

$copy_button = function ( $attribute, $value ) use ( $svg_small, $copy_icon, $svg_tags ) {
	?>
	<button type="button" class="ddfw-mcp-copy" <?php echo esc_attr( $attribute ); ?>="<?php echo esc_attr( $value ); ?>">
		<?php echo wp_kses( $svg_small . $copy_icon . '</svg>', $svg_tags ); ?>
		<span><?php esc_html_e( 'Copy', 'affiliates-for-woocommerce' ); ?></span>
	</button>
	<?php
};
?>
<div class="wrap devdiggers-wrap ddfw-hub ddfw-mcp">
	<?php require DDFW_FILE . 'templates/layout/devdiggers-header.php'; ?>

	<div class="ddfw-dashboard-container">
		<!-- Server -->
		<section class="ddfw-mcp-hero">
			<div class="ddfw-mcp-hero-top">
				<div class="ddfw-mcp-hero-icon"><?php echo wp_kses( $svg_open . $hub_icon . '</svg>', $svg_tags ); ?></div>
				<div class="ddfw-mcp-hero-text">
					<h1><?php esc_html_e( 'AI & MCP', 'affiliates-for-woocommerce' ); ?></h1>
					<p><?php esc_html_e( 'Ask Claude, ChatGPT, Cursor or VS Code about your store in plain words. They use the tools below, with your permissions.', 'affiliates-for-woocommerce' ); ?></p>
				</div>
				<button type="button" class="ddfw-button ddfw-button-primary" data-open="#ddfw-mcp-connect" <?php disabled( ! $can_connect ); ?>>
					<?php echo wp_kses( $svg_small . '<path d="M5 12h14"/><path d="M12 5v14"/></svg>', $svg_tags ); ?>
					<?php esc_html_e( 'Connect an app', 'affiliates-for-woocommerce' ); ?>
				</button>
			</div>

			<div class="ddfw-mcp-hero-meta">
				<span class="ddfw-mcp-badge <?php echo $status_note ? 'is-warn' : 'is-on'; ?>"><?php echo esc_html( $status_text ); ?></span>
				<span class="ddfw-mcp-muted">
					<?php
					/* translators: %d: number of tools. */
					echo esc_html( sprintf( _n( '%d tool live', '%d tools live', $live_tools, 'affiliates-for-woocommerce' ), $live_tools ) );
					?>
				</span>
				<?php if ( $ready ) : ?>
					<div class="ddfw-mcp-url">
						<code id="ddfw-mcp-url"><?php echo esc_html( $server_url ); ?></code>
						<?php $copy_button( 'data-copy', '#ddfw-mcp-url' ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $status_note ) : ?>
				<p class="ddfw-mcp-notice"><?php echo esc_html( $status_note ); ?></p>
			<?php endif; ?>

			<?php if ( $connections ) : ?>
				<div class="ddfw-mcp-connections">
					<h3><?php esc_html_e( 'Connected apps', 'affiliates-for-woocommerce' ); ?></h3>
					<ul>
						<?php foreach ( $connections as $connection ) : ?>
							<?php $owner = get_current_user_id() !== $connection['user'] ? get_userdata( $connection['user'] ) : false; ?>
							<li>
								<strong><?php echo esc_html( $connection['name'] ); ?></strong>
								<?php if ( $owner ) : ?>
									<span class="ddfw-mcp-muted"><?php echo esc_html( $owner->display_name ); ?></span>
								<?php endif; ?>
								<span class="ddfw-mcp-muted">
									<?php
									echo esc_html(
										$connection['used']
											/* translators: %s: time since. */
											? sprintf( __( 'Used %s ago', 'affiliates-for-woocommerce' ), human_time_diff( $connection['used'] ) )
											/* translators: %s: date. */
											: sprintf( __( 'Allowed %s', 'affiliates-for-woocommerce' ), wp_date( $date_format, $connection['created'] ) )
									);
									?>
								</span>
								<button type="button" class="ddfw-mcp-link-danger" data-revoke="<?php echo esc_attr( $connection['id'] ); ?>" data-user="<?php echo esc_attr( $connection['user'] ); ?>"><?php esc_html_e( 'Revoke', 'affiliates-for-woocommerce' ); ?></button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</section>

		<!-- Tools -->
		<div class="ddfw-dashboard-section">
			<div class="ddfw-section-header">
				<h2><?php esc_html_e( 'Tools', 'affiliates-for-woocommerce' ); ?></h2>
				<p>
					<?php
					echo esc_html(
						$can_manage
							? __( 'Read tools are on. Tools that change data stay off until you allow changes. Click a tool to try it.', 'affiliates-for-woocommerce' )
							: __( 'Only an administrator can change these switches. Click a tool to try it.', 'affiliates-for-woocommerce' )
					);
					?>
				</p>
			</div>

			<?php if ( empty( $hub_plugins ) ) : ?>
				<p class="ddfw-hub-empty"><?php esc_html_e( 'No active DevDiggers plugin offers AI tools yet.', 'affiliates-for-woocommerce' ); ?></p>
			<?php endif; ?>

			<?php foreach ( $hub_plugins as $slug => $hub_plugin ) : ?>
				<?php
				$abilities = isset( $grouped[ $slug ] ) ? $grouped[ $slug ] : [];
				$is_on     = $hub->is_plugin_enabled( $slug );
				$logo      = $hub_plugin['icon'] ? $hub_plugin['icon'] : $svg_open . '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></svg>';
				?>
				<div class="ddfw-mcp-plugin <?php echo $is_on ? '' : 'is-off'; ?>">
					<div class="ddfw-mcp-plugin-head">
						<div class="ddfw-mcp-plugin-logo"><?php echo wp_kses( $logo, $svg_tags ); ?></div>
						<div class="ddfw-mcp-plugin-title">
							<h3><?php echo esc_html( $hub_plugin['name'] ); ?></h3>
							<p><?php echo esc_html( $hub_plugin['description'] ); ?></p>
						</div>
						<?php if ( $hub_plugin['url'] ) : ?>
							<a class="ddfw-plugin-link" href="<?php echo esc_url( $hub_plugin['url'] ); ?>"><?php esc_html_e( 'Open', 'affiliates-for-woocommerce' ); ?> <span>&rarr;</span></a>
						<?php endif; ?>
						<label class="ddfw-mcp-switch" title="<?php esc_attr_e( 'Turn this plugin\'s tools on or off', 'affiliates-for-woocommerce' ); ?>">
							<input type="checkbox" data-key="plugin" data-id="<?php echo esc_attr( $slug ); ?>" <?php checked( $is_on ); ?> <?php disabled( ! $can_manage ); ?> aria-label="<?php echo esc_attr( $hub_plugin['name'] ); ?>" />
							<span></span>
						</label>
					</div>

					<ul class="ddfw-mcp-tools">
						<?php foreach ( $abilities as $ability ) : ?>
							<?php
							$tool_type = DDFW_MCP::get_type( $ability );
							$kind      = 'prompt' === $tool_type ? 'prompt' : ( DDFW_MCP::is_readonly( $ability ) ? 'read' : 'write' );
							?>
							<li>
								<button type="button" class="ddfw-mcp-tool" data-try="<?php echo esc_attr( $ability->get_name() ); ?>">
									<span class="ddfw-mcp-chip ddfw-mcp-chip-<?php echo esc_attr( $kind ); ?>"><?php echo esc_html( $kinds[ $kind ] ); ?></span>
									<span class="ddfw-mcp-tool-text">
										<strong><?php echo esc_html( $ability->get_label() ); ?></strong>
										<span><?php echo esc_html( $ability->get_description() ); ?></span>
									</span>
									<span class="ddfw-mcp-try"><?php esc_html_e( 'Try', 'affiliates-for-woocommerce' ); ?></span>
								</button>
								<?php if ( 'write' === $kind ) : ?>
									<label class="ddfw-mcp-allow">
										<span class="ddfw-mcp-switch ddfw-mcp-switch-small">
											<input type="checkbox" data-key="write" data-id="<?php echo esc_attr( $ability->get_name() ); ?>" <?php checked( ! empty( $settings['writes'][ $ability->get_name() ] ) ); ?> <?php disabled( ! $can_manage ); ?> />
											<span></span>
										</span>
										<?php esc_html_e( 'Allow changes', 'affiliates-for-woocommerce' ); ?>
									</label>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Activity -->
		<div class="ddfw-dashboard-section" id="ddfw-mcp-activity">
			<div class="ddfw-section-header ddfw-mcp-section-header">
				<div>
					<h2><?php esc_html_e( 'Activity', 'affiliates-for-woocommerce' ); ?></h2>
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: days. */
								_n( 'Every connection and tool call of the last %d day. Only which tool ran and when is stored, never what was asked.', 'Every connection and tool call of the last %d days. Only which tool ran and when is stored, never what was asked.', $log_days = max( 1, (int) apply_filters( 'ddfw_mcp_log_days', DDFW_MCP::LOG_DAYS ) ), 'affiliates-for-woocommerce' ), // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Shown once.
								$log_days
							)
						);
						echo $can_manage ? '' : ' ' . esc_html__( 'You see your own calls.', 'affiliates-for-woocommerce' );
						?>
					</p>
				</div>
				<?php if ( $log['total'] && $can_manage ) : ?>
					<button type="button" class="ddfw-button ddfw-button-secondary" id="ddfw-mcp-clear"><?php esc_html_e( 'Clear', 'affiliates-for-woocommerce' ); ?></button>
				<?php endif; ?>
			</div>

			<?php if ( $log['total'] || $is_filtered ) : ?>
				<form class="ddfw-mcp-filters" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) . '#ddfw-mcp-activity' ); ?>">
					<input type="hidden" name="page" value="<?php echo esc_attr( DDFW_MCP::PAGE ); ?>" />
					<?php if ( count( $hub_plugins ) > 1 ) : ?>
						<select name="log_plugin" class="ddfw-dash-filter-select" aria-label="<?php esc_attr_e( 'Plugin', 'affiliates-for-woocommerce' ); ?>">
							<option value=""><?php esc_html_e( 'All plugins', 'affiliates-for-woocommerce' ); ?></option>
							<?php foreach ( $hub_plugins as $slug => $hub_plugin ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $log_filters['plugin'], $slug ); ?>><?php echo esc_html( $hub_plugin['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<?php if ( count( $log_apps ) > 1 ) : ?>
						<select name="log_app" class="ddfw-dash-filter-select" aria-label="<?php esc_attr_e( 'App', 'affiliates-for-woocommerce' ); ?>">
							<option value=""><?php esc_html_e( 'All apps', 'affiliates-for-woocommerce' ); ?></option>
							<?php foreach ( $log_apps as $log_app ) : ?>
								<option value="<?php echo esc_attr( $log_app ); ?>" <?php selected( $log_filters['app'], $log_app ); ?>><?php echo esc_html( $log_app ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<select name="log_status" class="ddfw-dash-filter-select" aria-label="<?php esc_attr_e( 'Result', 'affiliates-for-woocommerce' ); ?>">
						<option value=""><?php esc_html_e( 'All results', 'affiliates-for-woocommerce' ); ?></option>
						<option value="failed" <?php selected( $log_filters['status'], 'failed' ); ?>><?php esc_html_e( 'Failed only', 'affiliates-for-woocommerce' ); ?></option>
					</select>
					<noscript><button type="submit" class="ddfw-button ddfw-button-secondary"><?php esc_html_e( 'Filter', 'affiliates-for-woocommerce' ); ?></button></noscript>
					<?php if ( $is_filtered ) : ?>
						<a class="ddfw-mcp-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . DDFW_MCP::PAGE ) . '#ddfw-mcp-activity' ); ?>"><?php esc_html_e( 'Reset', 'affiliates-for-woocommerce' ); ?></a>
					<?php endif; ?>
				</form>
			<?php endif; ?>

			<?php if ( ! $log['total'] ) : ?>
				<p class="ddfw-hub-empty">
					<?php
					echo esc_html(
						$is_filtered
							? __( 'No calls match these filters.', 'affiliates-for-woocommerce' )
							: __( 'No AI activity yet. Calls show up here as soon as an app connects.', 'affiliates-for-woocommerce' )
					);
					?>
				</p>
			<?php else : ?>
				<div class="ddfw-mcp-log">
					<table>
						<thead>
							<tr>
								<th><?php esc_html_e( 'When', 'affiliates-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'App', 'affiliates-for-woocommerce' ); ?></th>
								<?php if ( $can_manage ) : ?>
									<th><?php esc_html_e( 'User', 'affiliates-for-woocommerce' ); ?></th>
								<?php endif; ?>
								<th><?php esc_html_e( 'Action', 'affiliates-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Result', 'affiliates-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $log['entries'] as $entry ) : ?>
								<?php
								$log_user   = $entry['user'] ? get_userdata( $entry['user'] ) : false;
								$log_action = 'initialize' === $entry['method'] ? esc_html__( 'Connected', 'affiliates-for-woocommerce' ) : ( isset( $labels[ $entry['name'] ] ) ? $labels[ $entry['name'] ] : $entry['name'] );
								// A known tool the server did not list was switched off at the time.
								$log_reason = 'not_found' === $entry['reason'] && isset( $labels[ $entry['name'] ] ) ? esc_html__( 'Switched off', 'affiliates-for-woocommerce' ) : ( isset( $reasons[ $entry['reason'] ] ) ? $reasons[ $entry['reason'] ] : esc_html__( 'Error', 'affiliates-for-woocommerce' ) );
								$log_error  = $entry['error'];

								// Input the tool's schema refused: the assistant sent a wrong or missing value.
								if ( 'ability_invalid_input' === $entry['code'] ) {
									$log_reason = esc_html__( 'Wrong input', 'affiliates-for-woocommerce' );
									$log_error  = preg_match( '/Reason:\s*(.+)$/s', $log_error, $match ) ? $match[1] : $log_error;
								}

								$log_plugin = isset( $hub_plugins[ $entry['plugin'] ] ) ? $hub_plugins[ $entry['plugin'] ]['name'] : $entry['plugin'];
								?>
								<tr>
									<td title="<?php echo esc_attr( wp_date( $date_format . ' ' . get_option( 'time_format' ), $entry['time'] ) ); ?>">
										<?php
										/* translators: %s: time since. */
										echo esc_html( sprintf( __( '%s ago', 'affiliates-for-woocommerce' ), human_time_diff( $entry['time'] ) ) );
										?>
									</td>
									<td><?php echo esc_html( $entry['client'] ? $entry['client'] : '—' ); ?></td>
									<?php if ( $can_manage ) : ?>
										<td><?php echo esc_html( $log_user ? $log_user->display_name : '—' ); ?></td>
									<?php endif; ?>
									<td
									<?php
									if ( $entry['arguments'] ) {
										/* translators: %s: input names. */
										echo ' title="' . esc_attr( sprintf( __( 'Inputs: %s', 'affiliates-for-woocommerce' ), implode( ', ', $entry['arguments'] ) ) ) . '"';
									}
									?>
									>
										<?php echo esc_html( $log_action ); ?>
										<?php if ( $log_plugin && count( $hub_plugins ) > 1 ) : ?>
											<span class="ddfw-mcp-log-plugin"><?php echo esc_html( $log_plugin ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<span class="ddfw-mcp-badge <?php echo $entry['ok'] ? 'is-on' : 'is-error'; ?>"><?php echo esc_html( $entry['ok'] ? __( 'OK', 'affiliates-for-woocommerce' ) : $log_reason ); ?></span>
										<span class="ddfw-mcp-muted"><?php echo esc_html( $entry['ms'] . ' ms' ); ?></span>
										<?php if ( ! $entry['ok'] && $log_error ) : ?>
											<span class="ddfw-mcp-log-error"><?php echo esc_html( $log_error ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="tablenav ddfw-mcp-tablenav">
					<div class="tablenav-pages<?php echo 1 === $log_pages ? ' one-page' : ''; ?>">
						<span class="displaying-num">
							<?php
							$log_first = ( $log_filters['page'] - 1 ) * DDFW_MCP::LOG_PER_PAGE + 1;
							echo esc_html(
								sprintf(
									/* translators: 1: first row, 2: last row, 3: total. */
									__( '%1$d–%2$d of %3$s', 'affiliates-for-woocommerce' ),
									$log_first,
									$log_first + count( $log['entries'] ) - 1,
									number_format_i18n( $log['total'] )
								)
							);
							?>
						</span>
						<span class="pagination-links">
							<?php
							$log_nav = [
								[ 1, '«', __( 'First page', 'affiliates-for-woocommerce' ), $log_filters['page'] > 1 ],
								[ $log_filters['page'] - 1, '‹', __( 'Previous page', 'affiliates-for-woocommerce' ), $log_filters['page'] > 1 ],
								null,
								[ $log_filters['page'] + 1, '›', __( 'Next page', 'affiliates-for-woocommerce' ), $log_filters['page'] < $log_pages ],
								[ $log_pages, '»', __( 'Last page', 'affiliates-for-woocommerce' ), $log_filters['page'] < $log_pages ],
							];

							foreach ( $log_nav as $nav ) :
								if ( null === $nav ) :
									?>
									<span class="paging-input">
										<span class="tablenav-paging-text">
											<?php
											/* translators: 1: page, 2: pages. */
											echo esc_html( sprintf( __( '%1$d of %2$d', 'affiliates-for-woocommerce' ), $log_filters['page'], $log_pages ) );
											?>
										</span>
									</span>
								<?php elseif ( $nav[3] ) : ?>
									<a class="button" href="<?php echo esc_url( $log_url( $nav[0] ) ); ?>" aria-label="<?php echo esc_attr( $nav[2] ); ?>"><?php echo esc_html( $nav[1] ); ?></a>
								<?php else : ?>
									<span class="tablenav-pages-navspan button disabled" aria-hidden="true"><?php echo esc_html( $nav[1] ); ?></span>
									<?php
								endif;
							endforeach;
							?>
						</span>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Connect: pick the app, then add the server there and allow the sign-in. -->
	<dialog class="ddfw-mcp-dialog" id="ddfw-mcp-connect" aria-labelledby="ddfw-mcp-connect-title">
		<div class="ddfw-mcp-dialog-head">
			<h2 id="ddfw-mcp-connect-title"><?php esc_html_e( 'Connect an AI app', 'affiliates-for-woocommerce' ); ?></h2>
			<button type="button" class="ddfw-mcp-close" data-close aria-label="<?php esc_attr_e( 'Close', 'affiliates-for-woocommerce' ); ?>"><?php echo wp_kses( $svg_small . $x_icon . '</svg>', $svg_tags ); ?></button>
		</div>

		<div class="ddfw-mcp-step" data-step="pick">
			<p class="ddfw-mcp-muted"><?php esc_html_e( 'Which app do you use?', 'affiliates-for-woocommerce' ); ?></p>
			<div class="ddfw-mcp-clients">
				<?php foreach ( $clients as $client => $app ) : ?>
					<button type="button" class="ddfw-mcp-client" data-client="<?php echo esc_attr( $client ); ?>">
						<span class="ddfw-mcp-client-icon"><?php echo wp_kses( $brand_svg( $app['brand'] ), $svg_tags ); ?></span>
						<span class="ddfw-mcp-client-text">
							<strong><?php echo esc_html( $app['label'] ); ?></strong>
							<span><?php echo esc_html( $app['sub'] ); ?></span>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<?php foreach ( $clients as $client => $app ) : ?>
			<?php $code_id = 'ddfw-mcp-code-' . $client; ?>
			<div class="ddfw-mcp-step" data-step="<?php echo esc_attr( $client ); ?>" hidden>
				<button type="button" class="ddfw-mcp-back" data-back><?php esc_html_e( '← All apps', 'affiliates-for-woocommerce' ); ?></button>
				<div class="ddfw-mcp-setup-title">
					<span class="ddfw-mcp-client-icon"><?php echo wp_kses( $brand_svg( $app['brand'] ), $svg_tags ); ?></span>
					<strong><?php echo esc_html( $app['label'] ); ?></strong>
				</div>

				<?php if ( ! empty( $app['cloud'] ) && ! $is_public ) : ?>
					<p class="ddfw-mcp-notice">
						<?php
						/* translators: %s: app name. */
						echo esc_html( sprintf( __( '%s connects from its own servers, so it reaches only a site that is online over HTTPS. This site is not, so use an app that runs on your computer, such as Claude Code.', 'affiliates-for-woocommerce' ), $app['label'] ) );
						?>
					</p>
				<?php endif; ?>

				<ol class="ddfw-mcp-steps">
					<?php foreach ( $app['steps'] as $index => $step ) : ?>
						<li>
							<?php echo esc_html( $step ); ?>
							<?php if ( $index === $app['step'] ) : ?>
								<div class="ddfw-mcp-code">
									<div class="ddfw-mcp-code-head">
										<span><?php echo esc_html( $app['file'] ); ?></span>
										<?php $copy_button( 'data-copy', '#' . $code_id ); ?>
									</div>
									<pre><code id="<?php echo esc_attr( $code_id ); ?>"><?php echo esc_html( $app['code'] ); ?></code></pre>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>

				<?php if ( ! empty( $app['prompt'] ) ) : ?>
					<?php
					$setup_prompt = sprintf(
						/* translators: 1: app name, 2: setup snippet. */
						__( "Add an MCP server named \"devdiggers\" to %1\$s on this computer, using this:\n\n%2\$s\n\nKeep any servers that are already set up. Then tell me how to sign in to it: I will allow the sign-in in my browser myself.", 'affiliates-for-woocommerce' ),
						$app['label'],
						"```\n" . $app['code'] . "\n```"
					);
					?>
					<div class="ddfw-mcp-prompt">
						<span><?php esc_html_e( 'Or let an AI agent on this computer do it.', 'affiliates-for-woocommerce' ); ?></span>
						<button type="button" class="ddfw-mcp-link" data-copy-text="<?php echo esc_attr( $setup_prompt ); ?>"><?php esc_html_e( 'Copy setup prompt', 'affiliates-for-woocommerce' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<p class="ddfw-mcp-fineprint"><?php esc_html_e( 'No password or key to copy: you allow the app on a WordPress page, it gets a token for this server only, and it shows up here once allowed.', 'affiliates-for-woocommerce' ); ?></p>
	</dialog>

	<!-- Try: a side panel with the tool's inputs and its response. -->
	<dialog class="ddfw-mcp-dialog ddfw-mcp-drawer" id="ddfw-mcp-try" aria-labelledby="ddfw-mcp-try-title">
		<div class="ddfw-mcp-dialog-head">
			<div>
				<span class="ddfw-mcp-chip" id="ddfw-mcp-try-kind"></span>
				<h2 id="ddfw-mcp-try-title"></h2>
			</div>
			<button type="button" class="ddfw-mcp-close" data-close aria-label="<?php esc_attr_e( 'Close', 'affiliates-for-woocommerce' ); ?>"><?php echo wp_kses( $svg_small . $x_icon . '</svg>', $svg_tags ); ?></button>
		</div>
		<p class="ddfw-mcp-muted" id="ddfw-mcp-try-description"></p>
		<form id="ddfw-mcp-run">
			<div class="ddfw-mcp-inputs" id="ddfw-mcp-inputs"></div>
			<button type="submit" class="ddfw-button ddfw-button-primary" id="ddfw-mcp-run-button"><?php esc_html_e( 'Run', 'affiliates-for-woocommerce' ); ?></button>
		</form>
		<div class="ddfw-mcp-output" hidden>
			<div class="ddfw-mcp-output-head">
				<span><?php esc_html_e( 'What the assistant gets', 'affiliates-for-woocommerce' ); ?></span>
				<span id="ddfw-mcp-output-status"></span>
			</div>
			<pre><code id="ddfw-mcp-output"></code></pre>
		</div>
	</dialog>
</div>
