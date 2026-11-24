<?php
/**
 * DevDiggers MCP Hub: OAuth 2.1 sign-in.
 *
 * AI apps connect the way they connect to any remote MCP server: the app registers itself (RFC 7591), opens
 * WordPress for the person to approve, and gets a short-lived token (PKCE) that opens this MCP server and
 * nothing else. Nobody copies a secret, so none can end up in a chat, a config file or a screenshot.
 * Discovery follows the MCP spec: RFC 9728 resource metadata and RFC 8414 server metadata.
 *
 * Tokens are "user.grant.secret"; only a SHA-256 of each secret is stored, in the user's meta.
 *
 * @author DevDiggers
 * @version 1.3.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_MCP_OAuth' ) ) {
	/**
	 * OAuth 2.1 authorization server for the DevDiggers MCP server.
	 */
	class DDFW_MCP_OAuth {
		/**
		 * Registered apps (option) and approved connections (user meta).
		 */
		const CLIENTS = 'ddfw_mcp_clients';
		const GRANTS  = 'ddfw_mcp_grants';

		/**
		 * Lifetimes. A connection unused for REFRESH_TTL signs out by itself.
		 */
		const ACCESS_TTL  = 3600;
		const REFRESH_TTL = 2592000;
		const CODE_TTL    = 300;

		/**
		 * Limits that keep unauthenticated registration from filling the database.
		 */
		const MAX_CLIENTS = 100;
		const MAX_GRANTS  = 20;

		/**
		 * The approval page: admin-post.php?action=ddfw_mcp_authorize.
		 */
		const ACTION = 'ddfw_mcp_authorize';

		/**
		 * Apps whose return address belongs to a known AI app; others get a warning on the approval page.
		 */
		const KNOWN_HOSTS = [ 'claude.ai', 'claude.com', 'chatgpt.com', 'chat.openai.com', 'vscode.dev', 'insiders.vscode.dev', 'cursor.com' ];

		/**
		 * The connection whose token signed in this request: [ user ID, grant ID ].
		 *
		 * @var array|null
		 */
		private $grant = null;

		/**
		 * Whether this request sent a token that is wrong or expired.
		 *
		 * @var bool
		 */
		private $bad_token = false;

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'rest_api_init', [ $this, 'register_routes' ] );
			add_action( 'parse_request', [ $this, 'serve_well_known' ], 0 );
			add_filter( 'determine_current_user', [ $this, 'authenticate' ], 30 );
			add_filter( 'rest_authentication_errors', [ $this, 'check_route' ], 101 );
			add_filter( 'rest_post_dispatch', [ $this, 'add_challenge' ], 10, 3 );
			add_filter( 'rest_exposed_cors_headers', [ $this, 'expose_headers' ] );
			add_action( 'admin_post_' . self::ACTION, [ $this, 'consent' ] );
			add_action( 'admin_post_nopriv_' . self::ACTION, [ $this, 'require_login' ] );

			// A new password signs every AI app out too, as it does every browser.
			add_action(
				'wp_set_password',
				function ( $password, $user_id ) {
					delete_user_meta( $user_id, self::GRANTS );
				},
				10,
				2
			);
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Metadata.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * OAuth needs pretty permalinks: the issuer and the well-known paths can not carry a query string.
		 *
		 * @return bool
		 */
		public static function available() {
			return (bool) get_option( 'permalink_structure' );
		}

		/**
		 * Issuer: https://example.com/wp-json/devdiggers/v1.
		 *
		 * @return string
		 */
		public static function issuer() {
			return untrailingslashit( rest_url( DDFW_MCP::SERVER_NAMESPACE ) );
		}

		/**
		 * REST URL of an OAuth endpoint.
		 *
		 * @param string $endpoint Endpoint.
		 * @return string
		 */
		protected static function endpoint( $endpoint ) {
			return rest_url( DDFW_MCP::SERVER_NAMESPACE . '/oauth/' . $endpoint );
		}

		/**
		 * RFC 8414 authorization server metadata.
		 *
		 * @return array
		 */
		public function server_metadata() {
			return [
				'issuer'                                => self::issuer(),
				'authorization_endpoint'                => self::endpoint( 'authorize' ),
				'token_endpoint'                        => self::endpoint( 'token' ),
				'registration_endpoint'                 => self::endpoint( 'register' ),
				'response_types_supported'              => [ 'code' ],
				'grant_types_supported'                 => [ 'authorization_code', 'refresh_token' ],
				'code_challenge_methods_supported'      => [ 'S256' ],
				'token_endpoint_auth_methods_supported' => [ 'none' ],
				'scopes_supported'                      => [ 'mcp' ],
				'authorization_response_iss_parameter_supported' => true,
			];
		}

		/**
		 * RFC 9728 protected resource metadata.
		 *
		 * @return array
		 */
		public function resource_metadata() {
			return [
				'resource'                 => DDFW_MCP::server_url(),
				'authorization_servers'    => [ self::issuer() ],
				'bearer_methods_supported' => [ 'header' ],
				'scopes_supported'         => [ 'mcp' ],
				'resource_name'            => __( 'DevDiggers MCP Server', 'affiliates-for-woocommerce' ),
			];
		}

		/**
		 * Serve the well-known documents MCP clients look for, at the site root: with the issuer or server path
		 * inserted ( RFC 8414 / 9728 ), and without it for clients that only try the root.
		 *
		 * @return void
		 */
		public function serve_well_known() {
			$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';

			if ( 0 !== strpos( $path, '/.well-known/' ) || ! self::available() ) {
				return;
			}

			$path   = untrailingslashit( $path );
			$issuer = (string) wp_parse_url( self::issuer(), PHP_URL_PATH );
			$server = untrailingslashit( (string) wp_parse_url( DDFW_MCP::server_url(), PHP_URL_PATH ) );

			if ( in_array( $path, [ '/.well-known/oauth-protected-resource', '/.well-known/oauth-protected-resource' . $server ], true ) ) {
				$document = $this->resource_metadata();
			} elseif ( in_array( $path, [ '/.well-known/oauth-authorization-server', '/.well-known/oauth-authorization-server' . $issuer, '/.well-known/openid-configuration' . $issuer ], true ) ) {
				$document = $this->server_metadata();
			} else {
				return;
			}

			status_header( 200 );
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Access-Control-Allow-Origin: *' );
			echo wp_json_encode( $document );
			exit;
		}

		/**
		 * REST routes.
		 *
		 * @return void
		 */
		public function register_routes() {
			if ( ! self::available() ) {
				return;
			}

			$routes = [
				'/oauth/register'                          => [ 'POST', 'register' ],
				'/oauth/authorize'                         => [ 'GET', 'authorize' ],
				'/oauth/token'                             => [ 'POST', 'token' ],
				'/oauth/protected-resource'                => [ 'GET', 'resource_metadata' ],
				// {issuer}/.well-known/..., the last place clients look for the server metadata.
				'/\.well-known/openid-configuration'       => [ 'GET', 'server_metadata' ],
				'/\.well-known/oauth-authorization-server' => [ 'GET', 'server_metadata' ],
			];

			foreach ( $routes as $route => $route_args ) {
				register_rest_route(
					DDFW_MCP::SERVER_NAMESPACE,
					$route,
					[
						'methods'             => $route_args[0],
						'callback'            => [ $this, $route_args[1] ],
						'permission_callback' => '__return_true',
					]
				);
			}
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Apps ( dynamic client registration ).
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * Registered apps. Apps nobody approved within a day are dropped.
		 *
		 * @return array client_id => [ name, redirect_uris, created, approved ]
		 */
		public function get_clients() {
			$clients = (array) get_option( self::CLIENTS, [] );
			$kept    = array_filter(
				$clients,
				function ( $client ) {
					return ! empty( $client['approved'] ) || ( isset( $client['created'] ) && $client['created'] > time() - DAY_IN_SECONDS );
				}
			);

			if ( count( $kept ) !== count( $clients ) ) {
				update_option( self::CLIENTS, $kept, false );
			}

			return $kept;
		}

		/**
		 * A return address an app may register: https, http on this computer ( loopback ), or an app's own scheme
		 * such as cursor://. Never a scheme a browser runs.
		 *
		 * @param mixed $uri Redirect URI.
		 * @return bool
		 */
		public static function is_valid_redirect( $uri ) {
			if ( ! is_string( $uri ) || strlen( $uri ) > 2000 ) {
				return false;
			}

			$parts = wp_parse_url( $uri );

			if ( ! $parts || empty( $parts['scheme'] ) || isset( $parts['fragment'] ) ) {
				return false;
			}

			$scheme = strtolower( $parts['scheme'] );

			if ( 'https' === $scheme ) {
				return ! empty( $parts['host'] );
			}

			if ( 'http' === $scheme ) {
				return self::is_loopback( $uri );
			}

			return (bool) preg_match( '/^[a-z][a-z0-9+.-]*$/', $scheme ) && ! in_array( $scheme, [ 'javascript', 'data', 'file', 'vbscript', 'blob', 'about', 'ftp', 'ws', 'wss' ], true );
		}

		/**
		 * Whether a URI points at this computer over http.
		 *
		 * @param string $uri URI.
		 * @return bool
		 */
		protected static function is_loopback( $uri ) {
			$parts = wp_parse_url( $uri );

			return isset( $parts['scheme'], $parts['host'] ) && 'http' === strtolower( $parts['scheme'] ) && in_array( strtolower( $parts['host'] ), [ 'localhost', '127.0.0.1', '[::1]' ], true );
		}

		/**
		 * Whether an app registered this return address. Loopback addresses match on any port ( RFC 8252 ).
		 *
		 * @param array  $client Client.
		 * @param string $uri    Redirect URI.
		 * @return bool
		 */
		protected static function redirect_allowed( $client, $uri ) {
			$without_port = function ( $value ) {
				return self::is_loopback( $value ) ? preg_replace( '#^(http://[^/:]+|http://\[::1\]):\d+#i', '$1', $value ) : $value;
			};

			foreach ( (array) $client['redirect_uris'] as $registered ) {
				if ( $registered === $uri || ( self::is_loopback( $uri ) && $without_port( $registered ) === $without_port( $uri ) ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * POST /oauth/register.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function register( $request ) {
			$uris = $request->get_param( 'redirect_uris' );
			$name = $request->get_param( 'client_name' );

			if ( ! is_array( $uris ) || ! $uris || count( $uris ) > 5 || count( array_filter( $uris, [ __CLASS__, 'is_valid_redirect' ] ) ) !== count( $uris ) ) {
				return self::error( 'invalid_redirect_uri', __( 'Give one to five https, loopback or app redirect URIs.', 'affiliates-for-woocommerce' ) );
			}

			$name    = is_string( $name ) ? trim( sanitize_text_field( $name ) ) : '';
			$name    = mb_substr( '' !== $name ? $name : __( 'AI app', 'affiliates-for-woocommerce' ), 0, 60 );
			$clients = $this->get_clients();

			// Full: make room by dropping the oldest app nobody approved. Approved apps are never pushed out.
			if ( count( $clients ) >= self::MAX_CLIENTS ) {
				$pending = array_filter(
					$clients,
					function ( $client ) {
						return empty( $client['approved'] );
					}
				);

				if ( ! $pending ) {
					return self::error( 'temporarily_unavailable', __( 'Too many apps are registered. Revoke unused connections first.', 'affiliates-for-woocommerce' ), 503 );
				}

				uasort(
					$pending,
					function ( $a, $b ) {
						return $a['created'] - $b['created'];
					}
				);
				unset( $clients[ key( $pending ) ] );
			}

			$client_id             = wp_generate_uuid4();
			$clients[ $client_id ] = [
				'name'          => $name,
				'redirect_uris' => array_values( $uris ),
				'created'       => time(),
				'approved'      => 0,
			];

			update_option( self::CLIENTS, $clients, false );

			return new WP_REST_Response(
				[
					'client_id'                  => $client_id,
					'client_id_issued_at'        => time(),
					'client_name'                => $name,
					'redirect_uris'              => array_values( $uris ),
					'grant_types'                => [ 'authorization_code', 'refresh_token' ],
					'response_types'             => [ 'code' ],
					'token_endpoint_auth_method' => 'none',
				],
				201
			);
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Approval.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * The authorization request's parameters, as strings.
		 *
		 * @param array $source $_GET, $_POST or the REST params.
		 * @return array
		 */
		protected static function read_request( $source ) {
			$request = [];

			foreach ( [ 'client_id', 'redirect_uri', 'response_type', 'code_challenge', 'code_challenge_method', 'state', 'scope', 'resource' ] as $key ) {
				$value           = isset( $source[ $key ] ) && is_string( $source[ $key ] ) ? $source[ $key ] : '';
				$request[ $key ] = strlen( $value ) <= 2000 && ! preg_match( '/[\x00-\x1F\x7F]/', $value ) ? $value : '';
			}

			return $request;
		}

		/**
		 * GET /oauth/authorize: check the app and its return address, then hand over to the approval page in
		 * wp-admin, where WordPress signs the person in. Nothing is sent back to an address no app registered.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function authorize( $request ) {
			$params  = self::read_request( $request->get_query_params() );
			$clients = $this->get_clients();

			if ( ! isset( $clients[ $params['client_id'] ] ) || ! self::redirect_allowed( $clients[ $params['client_id'] ], $params['redirect_uri'] ) ) {
				return new WP_Error( 'invalid_request', __( 'Unknown app or return address. Start the connection again from your AI app.', 'affiliates-for-woocommerce' ), [ 'status' => 400 ] );
			}

			$response = new WP_REST_Response( null, 302 );
			$response->header( 'Location', add_query_arg( array_map( 'rawurlencode', array_filter( $params, 'strlen' ) + [ 'action' => self::ACTION ] ), admin_url( 'admin-post.php' ) ) );

			return $response;
		}

		/**
		 * Signed out: sign in first, then come back to the approval page.
		 *
		 * @return void
		 */
		public function require_login() {
			$params = self::read_request( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only; nothing changes before the person approves.

			wp_safe_redirect( wp_login_url( add_query_arg( array_map( 'rawurlencode', array_filter( $params, 'strlen' ) + [ 'action' => self::ACTION ] ), admin_url( 'admin-post.php' ) ) ) );
			exit;
		}

		/**
		 * Send the person back to the app.
		 *
		 * @param string $uri  Registered redirect URI.
		 * @param array  $args Query arguments.
		 * @return void
		 */
		protected static function send_back( $uri, $args ) {
			$args = array_filter( $args, 'strlen' );
			$url  = $uri . ( false === strpos( $uri, '?' ) ? '?' : '&' ) . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );

			if ( in_array( strtolower( (string) wp_parse_url( $uri, PHP_URL_SCHEME ) ), [ 'http', 'https' ], true ) ) {
				// ponytail: header() directly, as wp_redirect() refuses some loopback addresses on some setups.
				header( 'Location: ' . $url, true, 302 );
				exit;
			}

			// An app link ( claude://, cursor:// ) opens the app but leaves the browser on the approval form, which then
			// looks like it asks again. Show a page that says it is done and opens the app from there.
			$allowed     = ! isset( $args['error'] );
			$destination = self::describe_redirect( $uri );
			include DDFW_FILE . 'templates/layout/mcp-authorize-done.php';
			exit;
		}

		/**
		 * The approval page ( GET ) and the person's answer ( POST ).
		 *
		 * @return void
		 */
		public function consent() {
			nocache_headers();
			send_frame_options_header();
			header( "Content-Security-Policy: frame-ancestors 'none'" );

			$is_post = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];
			// phpcs:ignore WordPress.Security.NonceVerification -- The POST is verified below; the GET changes nothing.
			$params  = self::read_request( wp_unslash( $is_post ? $_POST : $_GET ) );
			$clients = $this->get_clients();
			$client  = isset( $clients[ $params['client_id'] ] ) ? $clients[ $params['client_id'] ] : null;

			if ( ! $client || ! self::redirect_allowed( $client, $params['redirect_uri'] ) ) {
				wp_die( esc_html__( 'Unknown app or return address. Start the connection again from your AI app.', 'affiliates-for-woocommerce' ), esc_html__( 'Connection failed', 'affiliates-for-woocommerce' ), [ 'response' => 400 ] );
			}

			$error = '';

			if ( 'code' !== $params['response_type'] ) {
				$error = 'unsupported_response_type';
			} elseif ( 'S256' !== $params['code_challenge_method'] || ! preg_match( '/^[A-Za-z0-9_-]{43}$/', $params['code_challenge'] ) ) {
				$error = 'invalid_request';
			} elseif ( '' !== $params['resource'] && untrailingslashit( $params['resource'] ) !== untrailingslashit( DDFW_MCP::server_url() ) ) {
				$error = 'invalid_target';
			}

			if ( $error ) {
				self::send_back(
					$params['redirect_uri'],
					[
						'error' => $error,
						'state' => $params['state'],
					]
				);
			}

			if ( ! DDFW_MCP::instance()->can_connect() ) {
				wp_die( esc_html__( 'Your account can not connect AI apps to this store. Ask an administrator.', 'affiliates-for-woocommerce' ), esc_html__( 'Not allowed', 'affiliates-for-woocommerce' ), [ 'response' => 403 ] );
			}

			if ( ! $is_post ) {
				$this->render_consent( $client, $params );
				exit;
			}

			check_admin_referer( self::ACTION . $params['client_id'] );

			if ( empty( $_POST['allow'] ) ) {
				self::send_back(
					$params['redirect_uri'],
					[
						'error' => 'access_denied',
						'state' => $params['state'],
					]
				);
			}

			$code = bin2hex( random_bytes( 32 ) );

			set_transient(
				'ddfw_mcp_code_' . hash( 'sha256', $code ),
				[
					'client'       => $params['client_id'],
					'redirect_uri' => $params['redirect_uri'],
					'challenge'    => $params['code_challenge'],
					'user'         => get_current_user_id(),
				],
				self::CODE_TTL
			);

			$clients[ $params['client_id'] ]['approved'] = time();
			update_option( self::CLIENTS, $clients, false );

			self::send_back(
				$params['redirect_uri'],
				[
					'code'  => $code,
					'state' => $params['state'],
					'iss'   => self::issuer(),
				]
			);
		}

		/**
		 * Where an approval sends the person, in words, and whether it is an address people know.
		 *
		 * @param string $uri Redirect URI.
		 * @return array [ text, known ]
		 */
		public static function describe_redirect( $uri ) {
			if ( self::is_loopback( $uri ) ) {
				return [ __( 'an app on this computer', 'affiliates-for-woocommerce' ), true ];
			}

			$parts = wp_parse_url( $uri );

			if ( 'https' !== strtolower( $parts['scheme'] ) ) {
				/* translators: %s: app scheme, such as cursor. */
				return [ sprintf( __( 'the %s app on this computer', 'affiliates-for-woocommerce' ), $parts['scheme'] ), true ];
			}

			$host = strtolower( $parts['host'] );

			foreach ( self::KNOWN_HOSTS as $known ) {
				if ( $host === $known || substr( $host, -strlen( '.' . $known ) ) === '.' . $known ) {
					return [ $host, true ];
				}
			}

			return [ $host, false ];
		}

		/**
		 * Approval page.
		 *
		 * @param array $client Client.
		 * @param array $params Authorization request.
		 * @return void
		 */
		protected function render_consent( $client, $params ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used by the template.
			include DDFW_FILE . 'templates/layout/mcp-authorize.php';
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Tokens.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * An OAuth error response.
		 *
		 * @param string $code        Error code.
		 * @param string $description Description.
		 * @param int    $status      HTTP status.
		 * @return WP_REST_Response
		 */
		protected static function error( $code, $description, $status = 400 ) {
			$response = new WP_REST_Response(
				[
					'error'             => $code,
					'error_description' => $description,
				],
				$status
			);
			$response->header( 'Cache-Control', 'no-store' );

			return $response;
		}

		/**
		 * A user's connections.
		 *
		 * @param int $user_id User ID.
		 * @return array grant ID => grant
		 */
		public static function get_grants( $user_id ) {
			return array_filter( (array) get_user_meta( $user_id, self::GRANTS, true ), 'is_array' );
		}

		/**
		 * Split a token into user ID, grant ID and secret.
		 *
		 * @param string $token Token.
		 * @return array|false
		 */
		protected static function parse_token( $token ) {
			return is_string( $token ) && preg_match( '/^(\d+)\.([A-Za-z0-9]{12})\.([a-f0-9]{48,64})$/', $token, $match ) ? [ (int) $match[1], $match[2], $match[3] ] : false;
		}

		/**
		 * Whether a user may connect, checked as that user.
		 *
		 * @param int $user_id User ID.
		 * @return bool
		 */
		protected static function user_can_connect( $user_id ) {
			$previous = get_current_user_id();

			wp_set_current_user( $user_id );
			$allowed = DDFW_MCP::instance()->can_connect();
			wp_set_current_user( $previous );

			return $allowed;
		}

		/**
		 * POST /oauth/token: a code for the first tokens, a refresh token for new ones.
		 *
		 * @param WP_REST_Request $request Request.
		 * @return WP_REST_Response
		 */
		public function token( $request ) {
			$param = function ( $key ) use ( $request ) {
				$value = $request->get_param( $key );

				return is_string( $value ) ? $value : '';
			};

			$clients   = $this->get_clients();
			$client_id = $param( 'client_id' );

			if ( 'authorization_code' === $param( 'grant_type' ) ) {
				$key  = 'ddfw_mcp_code_' . hash( 'sha256', $param( 'code' ) );
				$code = get_transient( $key );

				// Single use, right away, whatever happens next.
				delete_transient( $key );

				$verifier = $param( 'code_verifier' );

				if ( ! is_array( $code ) || ! hash_equals( $code['client'], $client_id ) || $code['redirect_uri'] !== $param( 'redirect_uri' ) || ! preg_match( '/^[A-Za-z0-9._~-]{43,128}$/', $verifier ) || ! hash_equals( $code['challenge'], rtrim( strtr( base64_encode( hash( 'sha256', $verifier, true ) ), '+/', '-_' ), '=' ) ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- PKCE S256.
					return self::error( 'invalid_grant', __( 'The sign-in code is wrong, used or expired. Connect again.', 'affiliates-for-woocommerce' ) );
				}

				if ( ! isset( $clients[ $client_id ] ) || ! self::user_can_connect( $code['user'] ) ) {
					return self::error( 'invalid_grant', __( 'This account can no longer connect.', 'affiliates-for-woocommerce' ) );
				}

				return $this->issue( $code['user'], '', $client_id, $clients[ $client_id ]['name'] );
			}

			if ( 'refresh_token' === $param( 'grant_type' ) ) {
				$token  = self::parse_token( $param( 'refresh_token' ) );
				$grants = $token ? self::get_grants( $token[0] ) : [];
				$grant  = $token && isset( $grants[ $token[1] ] ) ? $grants[ $token[1] ] : null;

				if ( ! $grant || ( '' !== $client_id && $client_id !== $grant['client'] ) ) {
					return self::error( 'invalid_grant', __( 'This connection was revoked or expired. Connect again.', 'affiliates-for-woocommerce' ) );
				}

				$hash = hash( 'sha256', $token[2] );

				// A refresh token is used once. Seeing an old one again means it was copied: end the connection.
				if ( ! empty( $grant['previous'] ) && hash_equals( $grant['previous'], $hash ) ) {
					$this->revoke( $token[0], $token[1] );
					return self::error( 'invalid_grant', __( 'This connection was signed out because its token was used twice. Connect again.', 'affiliates-for-woocommerce' ) );
				}

				if ( ! hash_equals( $grant['refresh'], $hash ) || $grant['refresh_expires'] < time() || ! self::user_can_connect( $token[0] ) ) {
					return self::error( 'invalid_grant', __( 'This connection was revoked or expired. Connect again.', 'affiliates-for-woocommerce' ) );
				}

				return $this->issue( $token[0], $token[1], $grant['client'], $grant['name'] );
			}

			return self::error( 'unsupported_grant_type', __( 'Use authorization_code or refresh_token.', 'affiliates-for-woocommerce' ) );
		}

		/**
		 * New access and refresh tokens for a connection; a new connection when the grant ID is empty.
		 *
		 * @param int    $user_id   User ID.
		 * @param string $grant_id  Grant ID.
		 * @param string $client_id Client ID.
		 * @param string $name      App name.
		 * @return WP_REST_Response
		 */
		protected function issue( $user_id, $grant_id, $client_id, $name ) {
			$grants   = self::get_grants( $user_id );
			$grant_id = $grant_id ? $grant_id : wp_generate_password( 12, false );
			$access   = bin2hex( random_bytes( 24 ) );
			$refresh  = bin2hex( random_bytes( 32 ) );
			$grant    = isset( $grants[ $grant_id ] ) ? $grants[ $grant_id ] : [
				'client'  => $client_id,
				'name'    => $name,
				'created' => time(),
				'used'    => 0,
			];

			$grants[ $grant_id ] = array_merge(
				$grant,
				[
					'access'          => hash( 'sha256', $access ),
					'access_expires'  => time() + self::ACCESS_TTL,
					'previous'        => isset( $grant['refresh'] ) ? $grant['refresh'] : '',
					'refresh'         => hash( 'sha256', $refresh ),
					'refresh_expires' => time() + self::REFRESH_TTL,
				]
			);

			// Keep the newest connections only.
			if ( count( $grants ) > self::MAX_GRANTS ) {
				uasort(
					$grants,
					function ( $a, $b ) {
						return max( $b['used'], $b['created'] ) - max( $a['used'], $a['created'] );
					}
				);
				$grants = array_slice( $grants, 0, self::MAX_GRANTS, true );
			}

			update_user_meta( $user_id, self::GRANTS, $grants );

			$response = new WP_REST_Response(
				[
					'access_token'  => $user_id . '.' . $grant_id . '.' . $access,
					'token_type'    => 'Bearer',
					'expires_in'    => self::ACCESS_TTL,
					'refresh_token' => $user_id . '.' . $grant_id . '.' . $refresh,
					'scope'         => 'mcp',
				]
			);
			$response->header( 'Cache-Control', 'no-store' );

			return $response;
		}

		/**
		 * End a connection.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $grant_id Grant ID.
		 * @return bool Whether it existed.
		 */
		public function revoke( $user_id, $grant_id ) {
			$grants = self::get_grants( $user_id );

			if ( ! isset( $grants[ $grant_id ] ) ) {
				return false;
			}

			unset( $grants[ $grant_id ] );
			update_user_meta( $user_id, self::GRANTS, $grants );

			return true;
		}

		/**
		 * Name of the connection whose token signed in this request ( the app's name as it registered ).
		 *
		 * @return string Empty when the request did not use a token.
		 */
		public function get_request_app() {
			if ( ! $this->grant ) {
				return '';
			}

			$grants = self::get_grants( $this->grant[0] );

			return isset( $grants[ $this->grant[1] ]['name'] ) ? (string) $grants[ $this->grant[1] ]['name'] : '';
		}

		/**
		 * Live connections, most recently used first: the current user's, or everyone's.
		 *
		 * @param bool $everyone Every user's.
		 * @return array
		 */
		public function get_connections( $everyone ) {
			$user_ids = $everyone
				? get_users(
					[
						'meta_key' => self::GRANTS, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin page, few users.
						'fields'   => 'ID',
					]
				)
				: [ get_current_user_id() ];

			$connections = [];

			foreach ( $user_ids as $user_id ) {
				foreach ( self::get_grants( $user_id ) as $grant_id => $grant ) {
					if ( $grant['refresh_expires'] > time() ) {
						$connections[] = [
							'user'    => (int) $user_id,
							'id'      => $grant_id,
							'name'    => $grant['name'],
							'created' => $grant['created'],
							'used'    => $grant['used'],
						];
					}
				}
			}

			usort(
				$connections,
				function ( $a, $b ) {
					return max( $b['used'], $b['created'] ) - max( $a['used'], $a['created'] );
				}
			);

			return $connections;
		}

		/*
		 * ---------------------------------------------------------------------------------------------
		 * Signing requests in.
		 * ---------------------------------------------------------------------------------------------
		 */

		/**
		 * Sign in a request to the MCP server that carries a valid access token. Other routes never read it.
		 *
		 * @param int|false $user_id User ID so far.
		 * @return int|false
		 */
		public function authenticate( $user_id ) {
			if ( $user_id || ! DDFW_MCP::is_mcp_request() ) {
				return $user_id;
			}

			$header = '';

			foreach ( [ 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION' ] as $key ) {
				if ( ! empty( $_SERVER[ $key ] ) ) {
					$header = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
					break;
				}
			}

			if ( ! preg_match( '/^Bearer\s+(\S+)$/i', $header, $match ) ) {
				return $user_id;
			}

			$token  = self::parse_token( $match[1] );
			$grants = $token ? self::get_grants( $token[0] ) : [];
			$grant  = $token && isset( $grants[ $token[1] ] ) ? $grants[ $token[1] ] : null;

			if ( ! $grant || $grant['access_expires'] < time() || ! hash_equals( $grant['access'], hash( 'sha256', $token[2] ) ) ) {
				$this->bad_token = true;
				return $user_id;
			}

			// Last used, at most once a minute.
			if ( time() - $grant['used'] > MINUTE_IN_SECONDS ) {
				$grants[ $token[1] ]['used'] = time();
				update_user_meta( $token[0], self::GRANTS, $grants );
			}

			$this->grant = [ $token[0], $token[1] ];

			return $token[0];
		}

		/**
		 * The MCP route asks for a sign-in ( 401 ), and a token never opens another route. Checked on the route the
		 * client asked for, after core's cookie check at 100; the tools' own internal REST calls are not affected.
		 *
		 * @param WP_Error|null|true $result Authentication result so far.
		 * @return WP_Error|null|true
		 */
		public function check_route( $result ) {
			wp_get_current_user();

			$route  = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? untrailingslashit( (string) $GLOBALS['wp']->query_vars['rest_route'] ) : '';
			$is_mcp = '/' . DDFW_MCP::SERVER_NAMESPACE . '/' . DDFW_MCP::SERVER_ROUTE === $route;

			if ( $this->grant && ! $is_mcp ) {
				return new WP_Error( 'ddfw_mcp_token_scope', __( 'This token only works for the DevDiggers MCP server.', 'affiliates-for-woocommerce' ), [ 'status' => 403 ] );
			}

			// CORS preflights carry no credentials.
			$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

			if ( $is_mcp && 'options' !== $method && ! is_wp_error( $result ) && ! is_user_logged_in() ) {
				return new WP_Error( 'ddfw_mcp_unauthorized', __( 'Sign in to use the DevDiggers MCP server.', 'affiliates-for-woocommerce' ), [ 'status' => 401 ] );
			}

			return $result;
		}

		/**
		 * A 401 from the MCP route tells the client where to sign in ( RFC 9728 ).
		 *
		 * @param WP_REST_Response $response Response.
		 * @param WP_REST_Server   $server   Server.
		 * @param WP_REST_Request  $request  Request.
		 * @return WP_REST_Response
		 */
		public function add_challenge( $response, $server, $request ) {
			if ( 401 === $response->get_status() && '/' . DDFW_MCP::SERVER_NAMESPACE . '/' . DDFW_MCP::SERVER_ROUTE === untrailingslashit( $request->get_route() ) ) {
				$response->header( 'WWW-Authenticate', 'Bearer resource_metadata="' . self::endpoint( 'protected-resource' ) . '"' . ( $this->bad_token ? ', error="invalid_token"' : '' ) );
			}

			return $response;
		}

		/**
		 * Browser-based clients need to read these headers.
		 *
		 * @param array $headers Exposed headers.
		 * @return array
		 */
		public function expose_headers( $headers ) {
			return array_merge( $headers, [ 'WWW-Authenticate', 'Mcp-Session-Id' ] );
		}
	}
}
