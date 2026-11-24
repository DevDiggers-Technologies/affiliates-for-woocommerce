<?php
/**
 * DevDiggers MCP activity log: the observability handler of the DevDiggers MCP server.
 * Loaded only when the MCP Adapter is, since it implements the adapter's interface.
 *
 * @author DevDiggers
 * @version 1.3.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_MCP_Log' ) && interface_exists( '\WP\MCP\Infrastructure\Observability\Contracts\McpObservabilityHandlerInterface' ) ) {
	/**
	 * Records connections, tool calls and prompt requests. Never the arguments' values.
	 */
	class DDFW_MCP_Log implements \WP\MCP\Infrastructure\Observability\Contracts\McpObservabilityHandlerInterface {
		/**
		 * Record one event.
		 *
		 * @param string     $event       Event name.
		 * @param array      $tags        Tags.
		 * @param float|null $duration_ms Duration.
		 * @return void
		 */
		public function record_event( string $event, array $tags = [], ?float $duration_ms = null ): void {
			$method = isset( $tags['method'] ) ? $tags['method'] : '';

			if ( 'mcp.request' !== $event || ! in_array( $method, [ 'initialize', 'tools/call', 'prompts/get' ], true ) ) {
				return;
			}

			$params  = isset( $tags['params'] ) ? (array) $tags['params'] : [];
			$text    = function ( $value, $length ) {
				return is_scalar( $value ) ? substr( (string) $value, 0, $length ) : '';
			};
			$session = $text( ! empty( $tags['new_session_id'] ) ? $tags['new_session_id'] : ( isset( $tags['session_id'] ) ? $tags['session_id'] : '' ), 64 );
			$hub     = DDFW_MCP::instance();
			// The name the app sent when it connected, else the name its sign-in was allowed under.
			$client = isset( $params['client_name'] ) ? $text( $params['client_name'], 80 ) : $hub->get_session_client( $session );
			$client = '' !== $client ? $client : $text( $hub->oauth->get_request_app(), 80 );
			// The client sends these: capped, so a client can not grow the log option without bound.
			$keys   = isset( $params['arguments_keys'] ) ? array_slice( (array) $params['arguments_keys'], 0, 20 ) : [];
			$failed = ( isset( $tags['status'] ) && 'error' === $tags['status'] ) || ! empty( $tags['failure_reason'] );
			$error  = $failed ? $hub->last_error : '';

			$hub->last_error = '';

			$hub->add_log(
				[
					'time'      => time(),
					'user'      => get_current_user_id(),
					'method'    => $method,
					// Tool names use "-" for the ability's "/": plugin-name.
					'name'      => isset( $params['name'] ) ? $text( $params['name'], 100 ) : '',
					'plugin'    => isset( $params['name'] ) ? $hub->get_tool_plugin( $text( $params['name'], 100 ) ) : '',
					'arguments' => array_map(
						function ( $key ) use ( $text ) {
							return $text( $key, 64 );
						},
						$keys
					),
					'client'    => $client,
					'session'   => $session,
					'ok'        => ! $failed,
					'reason'    => $text( isset( $tags['failure_reason'] ) ? $tags['failure_reason'] : ( isset( $tags['error_code'] ) ? $tags['error_code'] : '' ), 40 ),
					'code'      => $text( isset( $tags['error_code'] ) ? $tags['error_code'] : '', 60 ),
					// What the tool said went wrong, as the assistant saw it.
					'error'     => $text( $error, 200 ),
					'ms'        => null === $duration_ms ? 0 : (int) round( $duration_ms ),
				]
			);
		}
	}
}
