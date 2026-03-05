<?php
/**
 * File for handling review notices in the DevDiggers Framework.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Review_Notice' ) ) {
	/**
	 * Class for handling review notices.
	 */
	class DDFW_Review_Notice {
		/**
		 * The plugin arguments.
		 *
		 * @var array
		 */
		protected $args = [];

		/**
		 * Constructor to initialize hooks.
		 * 
		 * @param array $args Plugin specific arguments.
		 */
		public function __construct( $args = [] ) {
			$this->args = wp_parse_args( $args, [
				'plugin_name'    => '',
				'plugin_prefix'  => '',
				'review_url'     => '',
				'threshold_days' => 14,
				'remind_days'    => 7,
			] );

			if ( empty( $this->args['plugin_prefix'] ) || empty( $this->args['review_url'] ) ) {
				return;
			}

			add_action( 'admin_notices', [ $this, 'display_review_notice' ] );
			add_action( 'wp_ajax_ddfw_dismiss_review_notice', [ $this, 'dismiss_review_notice' ] );
		}

		/**
		 * Check if the notice should be displayed.
		 * 
		 * @return bool
		 */
		protected function should_display() {
			$prefix = $this->args['plugin_prefix'];

			// Check if dismissed permanently.
			if ( 'yes' === get_option( "_{$prefix}_review_notice_dismissed" ) ) {
				return false;
			}

			// Check install time.
			$installed_at = get_option( "_{$prefix}_installed_at" );
			if ( ! $installed_at ) {
				return false;
			}

			$current_time = time();
			$threshold_time = $this->args['threshold_days'] * DAY_IN_SECONDS;

			if ( ( $current_time - $installed_at ) < $threshold_time ) {
				return false;
			}

			// Check "Maybe Later" delay.
			$remind_at = get_option( "_{$prefix}_review_notice_remind_at" );
			if ( $remind_at && $current_time < $remind_at ) {
				return false;
			}

			return true;
		}

		/**
		 * Display the review notice.
		 * 
		 * @return void
		 */
		public function display_review_notice() {
			if ( ! $this->should_display() ) {
				return;
			}

			$prefix      = $this->args['plugin_prefix'];
			$plugin_name = $this->args['plugin_name'];
			$review_url  = $this->args['review_url'];

			?>
			<div class="notice notice-info ddfw-review-notice" data-prefix="<?php echo esc_attr( $prefix ); ?>">
				<p>
					<?php
					/* translators: %s: Plugin Name */
					echo sprintf( esc_html__( 'Enjoying %s? We would love to hear your feedback! Could you take a moment to leave a review?', 'devdiggers-framework' ), '<strong>' . esc_html( $plugin_name ) . '</strong>' );
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary ddfw-review-notice-action" data-action="already-did" target="_blank"><?php esc_html_e( 'Leave a Review', 'devdiggers-framework' ); ?></a>
					<button class="button button-secondary ddfw-review-notice-action" data-action="maybe-later"><?php esc_html_e( 'Maybe Later', 'devdiggers-framework' ); ?></button>
					<button class="button button-link ddfw-review-notice-action" data-action="already-did"><?php esc_html_e( 'Already Did', 'devdiggers-framework' ); ?></button>
				</p>
				<script>
					( function() {
						document.addEventListener( 'DOMContentLoaded', function() {
							var notice = document.querySelector( '.ddfw-review-notice[data-prefix="<?php echo esc_js( $prefix ); ?>"]' );
							if ( ! notice ) {
								return;
							}

							var buttons = notice.querySelectorAll( '.ddfw-review-notice-action' );
							buttons.forEach( function( btn ) {
								btn.addEventListener( 'click', function( e ) {
									var action = this.getAttribute( 'data-action' );
									var prefix = notice.getAttribute( 'data-prefix' );

									if ( action !== 'already-did' || this.tagName.toLowerCase() === 'button' ) {
										e.preventDefault();
									}

									var formData = new FormData();
									formData.append( 'action', 'ddfw_dismiss_review_notice' );
									formData.append( 'dismiss_action', action );
									formData.append( 'prefix', prefix );
									formData.append( 'nonce', '<?php echo esc_js( wp_create_nonce( 'ddfw-review-notice-nonce' ) ); ?>' );

									fetch( ajaxurl, {
										method: 'POST',
										body: formData
									} )
									.then( function( response ) {
										return response.json();
									} )
									.then( function( data ) {
										if ( data.success ) {
											notice.style.transition = 'opacity 0.5s';
											notice.style.opacity = '0';
											setTimeout( function() {
												notice.remove();
											}, 500 );
										}
									} );
								} );
							} );
						} );
					} )();
				</script>
			</div>
			<?php
		}

		/**
		 * Dismiss the review notice via AJAX.
		 * 
		 * @return void
		 */
		public function dismiss_review_notice() {
			check_ajax_referer( 'ddfw-review-notice-nonce', 'nonce' );

			$prefix = ! empty( $_POST['prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['prefix'] ) ) : '';
			$dismiss_action = ! empty( $_POST['dismiss_action'] ) ? sanitize_text_field( wp_unslash( $_POST['dismiss_action'] ) ) : '';

			if ( empty( $prefix ) ) {
				wp_send_json_error();
			}

			if ( 'maybe-later' === $dismiss_action ) {
				$remind_at = time() + ( $this->args['remind_days'] * DAY_IN_SECONDS );
				update_option( "_{$prefix}_review_notice_remind_at", $remind_at );
			} else {
				update_option( "_{$prefix}_review_notice_dismissed", 'yes' );
			}

			wp_send_json_success();
		}
	}
}
