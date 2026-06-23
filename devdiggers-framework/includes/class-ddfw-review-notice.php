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
		 * Registry of plugin prefixes that have registered a review notice.
		 *
		 * Used to validate the prefix received over AJAX so the option name can
		 * never be built from an arbitrary, user-supplied value.
		 *
		 * @var array
		 */
		protected static $registered_prefixes = [];

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

			// Record this prefix so the AJAX handler can validate against known prefixes.
			self::$registered_prefixes[ $this->args['plugin_prefix'] ] = true;

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
			if ( 'yes' === get_option( "ddfw_review_notice_dismissed_{$prefix}" ) ) {
				return false;
			}

			// Check install time.
			$installed_at = get_option( "{$prefix}_installed_at" );
			if ( ! $installed_at ) {
				return false;
			}

			$current_time = time();
			$threshold_time = $this->args['threshold_days'] * DAY_IN_SECONDS;

			if ( ( $current_time - $installed_at ) < $threshold_time ) {
				return false;
			}

			// Check "Maybe Later" delay.
			$remind_at = get_option( "ddfw_review_notice_remind_at_{$prefix}" );
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
			$script_path = DDFW_FILE . 'assets/js/review-notice.js';

			wp_enqueue_script( 'ddfw-review-notice', DDFW_URL . 'assets/js/review-notice.js', [], filemtime( $script_path ), true );

			?>
			<div class="notice notice-info ddfw-review-notice" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ddfw-review-notice-nonce' ) ); ?>">
				<p>
					<?php
					/* translators: %s: Plugin Name */
					echo sprintf( esc_html__( 'Enjoying %s? We would love to hear your feedback! Could you take a moment to leave a review?', 'affiliates-for-woocommerce' ), '<strong>' . esc_html( $plugin_name ) . '</strong>' );
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary ddfw-review-notice-action" data-action="already-did" data-open-review="true" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a Review', 'affiliates-for-woocommerce' ); ?></a>
					&nbsp;&nbsp;
					<button type="button" class="button button-secondary ddfw-review-notice-action" data-action="maybe-later"><?php esc_html_e( 'Maybe Later', 'affiliates-for-woocommerce' ); ?></button>
					&nbsp;&nbsp;
					<a href="#" class="ddfw-review-notice-action" data-action="already-did"><?php esc_html_e( 'I Already Did', 'affiliates-for-woocommerce' ); ?></a>
				</p>
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

			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error();
			}

			$prefix = ! empty( $_POST['prefix'] ) ? sanitize_key( wp_unslash( $_POST['prefix'] ) ) : '';
			$dismiss_action = ! empty( $_POST['dismiss_action'] ) ? sanitize_text_field( wp_unslash( $_POST['dismiss_action'] ) ) : '';

			// Only accept a prefix that an active review-notice instance actually registered.
			// This prevents the option name from being built out of arbitrary user input.
			if ( empty( $prefix ) || empty( self::$registered_prefixes[ $prefix ] ) ) {
				wp_send_json_error();
			}

			if ( 'maybe-later' === $dismiss_action ) {
				$remind_at = time() + ( $this->args['remind_days'] * DAY_IN_SECONDS );
				update_option( "ddfw_review_notice_remind_at_{$prefix}", $remind_at );
			} else {
				update_option( "ddfw_review_notice_dismissed_{$prefix}", 'yes' );
			}

			wp_send_json_success();
		}
	}
}
