<?php
/**
 * DevDiggers MCP error handler. Loaded only when the MCP Adapter is, since it extends the adapter's handler.
 *
 * @author DevDiggers
 * @version 1.3.0
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_MCP_Errors' ) && class_exists( '\WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler' ) ) {
	/**
	 * The adapter's PHP error log, plus the message of a failed tool for the activity log: the adapter hands
	 * the observability handler only an error code.
	 */
	class DDFW_MCP_Errors extends \WP\MCP\Infrastructure\ErrorHandling\ErrorLogMcpErrorHandler {
		/**
		 * Log an error.
		 *
		 * @param string $message Message.
		 * @param array  $context Context.
		 * @param string $type    Type.
		 * @return void
		 */
		public function log( string $message, array $context = [], string $type = 'error' ): void {
			foreach ( [ 'error_message', 'exception' ] as $key ) {
				if ( ! empty( $context[ $key ] ) && is_scalar( $context[ $key ] ) ) {
					DDFW_MCP::instance()->last_error = wp_strip_all_tags( (string) $context[ $key ] );
				}
			}

			parent::log( $message, $context, $type );
		}
	}
}
