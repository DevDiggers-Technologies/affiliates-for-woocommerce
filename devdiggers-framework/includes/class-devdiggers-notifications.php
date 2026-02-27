<?php
/**
 * Generic Notification Client for DevDiggers Framework
 *
 * @package DevDiggers\Framework
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DevDiggers_Notifications' ) ) {
	/**
	 * DevDiggers_Notifications Class
	 */
	final class DevDiggers_Notifications {

		/**
		 * Configuration
		 *
		 * @var array
		 */
		private $config;

		/**
		 * Constructor
		 *
		 * @param array $config Configuration array.
		 */
		public function __construct( $config = [] ) {
			$this->config = wp_parse_args( $config, [
				'api_url'      => 'https://devdiggers.com/wp-json/ddwcs/v1/notifications',
				'plugin_slug'  => '',
			] );

			if ( empty( $this->config['api_url'] ) || empty( $this->config['plugin_slug'] ) ) {
				return;
			}

			add_action( 'admin_init', [ $this, 'init' ] );
		}

		/**
		 * Init
		 */
		public function init() {
			if ( ! is_admin() ) {
				return;
			}

			add_action( 'admin_notices', [ $this, 'render_admin_notices' ] );
			add_action( 'wp_ajax_devdiggers_dismiss_notification', [ $this, 'dismiss_notification' ] );
			
			// Enqueue minimal script for dismissal
			add_action( 'admin_footer', [ $this, 'print_dismiss_script' ] );
		}

		/**
		 * Fetch Notifications from API
		 *
		 * @return array
		 */
		private function fetch_notifications() {
			$api_url   = add_query_arg( 'slug', $this->config['plugin_slug'], $this->config['api_url'] );
			$cache_key = 'devdiggers_notifications_' . md5( $api_url );
			$data      = get_transient( $cache_key );

			if ( false === $data ) {
				$request = wp_remote_get( $api_url, [ 'timeout' => 5 ] );

				if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
					return [];
				}

				$body = wp_remote_retrieve_body( $request );
				$data = json_decode( $body, true );

				if ( ! is_array( $data ) ) {
					return [];
				}

				set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
			}

			return $data;
		}

		/**
		 * Get Active Notifications
		 *
		 * Filter notifications based on target slug, version, etc.
		 *
		 * @return array
		 */
		private function get_active_notifications() {
			$notifications = $this->fetch_notifications();
			
			if ( empty( $notifications ) ) {
				return [];
			}

			$active    = [];
			$dismissed = get_option( 'devdiggers_dismissed_notifications_' . $this->config['plugin_slug'], [] );

			foreach ( $notifications as $notification ) {
				// Check if dismissed
				if ( in_array( $notification['id'], $dismissed, true ) ) {
					continue;
				}

				// Check targeting
				$targets = isset( $notification['slugs'] ) ? $notification['slugs'] : [];
				if ( ! empty( $targets ) && ! in_array( $this->config['plugin_slug'], $targets, true ) ) {
					continue;
				}

				// Dates are filtered by API, but double check in case of cache edge cases
				$now = current_time( 'Y-m-d' );
				if ( ! empty( $notification['start_date'] ) && $now < $notification['start_date'] ) {
					continue;
				}
				if ( ! empty( $notification['end_date'] ) && $now > $notification['end_date'] ) {
					continue;
				}

				$active[] = $notification;
			}

			return $active;
		}

		/**
		 * Render Admin Notices
		 */
		public function render_admin_notices() {
			$notifications = $this->get_active_notifications();
			// Avoid duplicate display if multiple DevDiggers plugins are active
			global $devdiggers_rendered_notices;

			foreach ( $notifications as $notification ) {
				if ( ! is_array( $devdiggers_rendered_notices ) ) {
					$devdiggers_rendered_notices = [];
				}

				if ( in_array( $notification['id'], $devdiggers_rendered_notices, true ) ) {
					continue;
				}

				$devdiggers_rendered_notices[] = $notification['id'];

				$class = 'notice notice-' . esc_attr( $notification['type'] );
				if ( $notification['dismissible'] ) {
					$class .= ' is-dismissible devdiggers-notification-dismissible';
				}
				// Append UTMs
				$id = $notification['id'];
				if ( ! empty( $notification['cta']['url'] ) ) {
					$notification['cta']['url'] = $this->append_utm( $notification['cta']['url'], $id );
				}

				if ( ! empty( $notification['message'] ) ) {
					$notification['message'] = preg_replace_callback( '/href=[\'"]([^\'"]+)[\'"]/', function( $matches ) use ( $id ) {
						return 'href="' . $this->append_utm( $matches[1], $id ) . '"';
					}, $notification['message'] );
				}

				?>
				<div class="<?php echo esc_attr( $class ); ?>" data-id="<?php echo esc_attr( $notification['id'] ); ?>" data-slug="<?php echo esc_attr( $this->config['plugin_slug'] ); ?>">
					<p>
						<?php if ( ! empty( $notification['title'] ) ) : ?>
							<strong><?php echo esc_html( $notification['title'] ); ?>: </strong>
						<?php endif; ?>
						<?php echo wp_kses_post( $notification['message'] ); ?>
						
						<?php if ( ! empty( $notification['cta']['text'] ) && ! empty( $notification['cta']['url'] ) ) : ?>
							<a href="<?php echo esc_url( $notification['cta']['url'] ); ?>" class="button button-primary" target="_blank" style="margin-left: 10px;">
								<?php echo esc_html( $notification['cta']['text'] ); ?>
							</a>
						<?php endif; ?>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Dismiss Notification
		 */
		public function dismiss_notification() {
			check_ajax_referer( 'devdiggers_dismiss_notification', 'nonce' );

			$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';

			if ( ! $id || ! $slug ) {
				wp_send_json_error();
			}

			$option_name = 'devdiggers_dismissed_notifications_' . $slug;
			$dismissed   = get_option( $option_name, [] );

			if ( ! in_array( $id, $dismissed, true ) ) {
				$dismissed[] = $id;
				update_option( $option_name, $dismissed );
			}

			wp_send_json_success();
		}

		/**
		 * Print Dismissal Script
		 */
		public function print_dismiss_script() {
			?>
			<script type="text/javascript">
				jQuery(document).on('click', '.devdiggers-notification-dismissible .notice-dismiss', function() {
					var $notice = jQuery(this).closest('.devdiggers-notification-dismissible');
					var id = $notice.data('id');
					var slug = $notice.data('slug');

					var formData = new FormData();
					formData.append('action', 'devdiggers_dismiss_notification');
					formData.append('id', id);
					formData.append('slug', slug);
					formData.append('nonce', '<?php echo wp_create_nonce( "devdiggers_dismiss_notification" ); ?>');

					fetch(ajaxurl, {
						method: 'POST',
						body: formData
					});
				});
			</script>
			<?php
		}

		/**
		 * Append UTM to URL
		 *
		 * @param string $url             URL.
		 * @param int    $notification_id Notification ID.
		 * @return string
		 */
		private function append_utm( $url, $notification_id ) {
			if ( empty( $url ) || strpos( $url, 'http' ) !== 0 ) {
				return $url;
			}
			
			$host = parse_url( home_url(), PHP_URL_HOST );
			if ( ! $host ) {
				$host = home_url();
			}

			$args = [
				'utm_source'   => $host,
				'utm_medium'   => 'devdiggers_notification',
				'utm_campaign' => 'devdiggers_notification_' . $notification_id,
				'utm_content'  => $this->config['plugin_slug'],
			];

			return add_query_arg( $args, $url );
		}
	}
}
