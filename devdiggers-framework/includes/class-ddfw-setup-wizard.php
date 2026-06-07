<?php
/**
 * Setup Wizard for DevDiggers Framework.
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Setup_Wizard' ) ) {
	/**
	 * Setup Wizard Class.
	 */
	class DDFW_Setup_Wizard {
		/**
		 * List of parameters.
		 *
		 * @var array
		 */
		public $args = [];

		/**
		 * Constructor
		 * 
		 * @param array $args Wizard configuration arguments.
		 */
		public function __construct( $args = [] ) {
			$this->args = wp_parse_args( $args, [
				'plugin_slug'    => '',
				'dashboard_page' => '',
				'redirect_url'   => '',
				'brand'          => [],
				'steps'          => []
			] );

			add_action( 'admin_init', [ $this, 'redirect_to_wizard' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
			add_action( 'wp_ajax_ddfw_save_wizard_step', [ $this, 'ajax_save_wizard_step' ] );
			add_action( 'ddfw_render_setup_wizard', [ $this, 'render_wizard' ] );
		}

		/**
		 * Redirect to wizard after plugin activation or manual visit
		 *
		 * @return void
		 */
		public function redirect_to_wizard() {
			$config = $this->args;
			$slug   = $config['plugin_slug'];

			if ( empty( $slug ) || empty( $config['dashboard_page'] ) ) {
				return;
			}

			// Handle manual skip action.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is sanitized and verified before updating the option.
			if ( isset( $_GET['page'] ) && $_GET['page'] === $config['dashboard_page'] && ! empty( $_GET['setup-wizard-skipped'] ) ) {
				$nonce = ! empty( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
				if ( ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) || ! wp_verify_nonce( $nonce, 'ddfw_skip_setup_wizard_' . $slug ) ) {
					wp_die( esc_html__( 'Security check failed.', 'affiliates-for-woocommerce' ) );
				}

				update_option( 'ddfw_setup_wizard_completed_' . $slug, true );
				wp_safe_redirect( admin_url( 'admin.php?page=' . $config['dashboard_page'] ) );
				exit;
			}

			// Handle activation redirect
			if ( get_transient( 'ddfw_activation_redirect_' . $slug ) ) {
				delete_transient( 'ddfw_activation_redirect_' . $slug );
				
				// Only redirect if not already completed
				if ( ! get_option( 'ddfw_setup_wizard_completed_' . $slug, false ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=' . $config['dashboard_page'] . '&setup-wizard=true' ) );
					exit;
				}
			}

			// Force wizard on first visit to dashboard if not completed.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing parameter; no form processing here.
			if ( isset( $_GET['page'] ) && $_GET['page'] === $config['dashboard_page'] && empty( $_GET['setup-wizard'] ) && empty( $_GET['setup-wizard-skipped'] ) ) {
				if ( ! get_option( 'ddfw_setup_wizard_completed_' . $slug, false ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=' . $config['dashboard_page'] . '&setup-wizard=true' ) );
					exit;
				}
			}
		}

		/**
		 * Enqueue scripts for setup wizard
		 *
		 * @param mixed $hook
		 * @return void
		 */
		public function enqueue_scripts( $hook ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing parameters used to decide whether assets load.
			$is_dashboard_wizard = ( ! empty( $_GET['setup-wizard'] ) && ! empty( $_GET['page'] ) );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing parameter used to match current admin page.
			if ( ! $is_dashboard_wizard || $_GET['page'] !== $this->args['dashboard_page'] ) {
				return;
			}

			// Enqueue style and script. Using priority 20 ensures it runs after framework handles are registered.
			wp_enqueue_style( 'ddfw-setup-wizard', DDFW_URL . 'assets/css/setup-wizard.css', [ DDFW_Assets::$framework_css_handle ], filemtime( DDFW_FILE . 'assets/css/setup-wizard.css' ) );
			wp_enqueue_script( 'ddfw-setup-wizard', DDFW_URL . 'assets/js/setup-wizard.js', [ DDFW_Assets::$framework_js_handle ], filemtime( DDFW_FILE . 'assets/js/setup-wizard.js' ), true );

			wp_localize_script( 'ddfw-setup-wizard', 'ddfw_wizard_params', [
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'ddfw-wizard-nonce' ),
				'plugin_slug' => $this->args['plugin_slug'],
				'steps'       => array_keys( $this->args['steps'] ?? [] ),
				'redirect'    => $this->args['redirect_url'] ?? admin_url( 'admin.php?page=' . $this->args['dashboard_page'] ),
			] );
		}

		/**
		 * AJAX handler to save wizard step
		 */
		public function ajax_save_wizard_step() {
			check_ajax_referer( 'ddfw-wizard-nonce', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Insufficient permissions.', 'affiliates-for-woocommerce' ) ] );
			}

			$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';

			// Early exit if the action doesn't belong to this wizard instance
			if ( $plugin_slug !== $this->args['plugin_slug'] ) {
				return; 
			}

			$step_id     = isset( $_POST['step_id'] ) ? sanitize_text_field( wp_unslash( $_POST['step_id'] ) ) : '';
			$form_data   = isset( $_POST['form_data'] ) ? map_deep( wp_unslash( $_POST['form_data'] ), 'sanitize_text_field' ) : [];

			if ( empty( $plugin_slug ) || empty( $step_id ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid request.', 'affiliates-for-woocommerce' ) ] );
			}

			if ( ! isset( $this->args['steps'][ $step_id ] ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Step not found.', 'affiliates-for-woocommerce' ) ] );
			}

			$step_config = $this->args['steps'][ $step_id ];

			// Call the save callback if provided
			if ( isset( $step_config['save_callback'] ) && is_callable( $step_config['save_callback'] ) ) {
				$result = call_user_func( $step_config['save_callback'], $form_data );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				}
			}

			// If it's the last step, mark as completed
			$step_keys = array_keys( $this->args['steps'] );
			if ( end( $step_keys ) === $step_id ) {
				update_option( 'ddfw_setup_wizard_completed_' . $plugin_slug, true );
			}

			wp_send_json_success();
		}

		/**
		 * Render the setup wizard within the dashboard layout.
		 *
		 * @param string $page The current page slug.
		 * @return void
		 */
		public function render_wizard( $page ) {
			if ( $page === $this->args['dashboard_page'] ) {
				$config         = $this->args;
				$steps          = $config['steps'];
				$brand          = $config['brand'];
				$plugin_slug    = $config['plugin_slug'];
				$dashboard_page = $config['dashboard_page'];

				include DDFW_FILE . 'templates/layout/setup-wizard.php';
			}
		}

		/**
		 * Default ready view for the setup wizard
		 *
		 * @param array $step Step configuration.
		 * @return void
		 */
		public function ready_view( $step = [] ) {
			$title = $step['ready_title'] ?? esc_html__( 'Congratulations! You are all set.', 'affiliates-for-woocommerce' );
			$desc  = $step['ready_description'] ?? esc_html__( 'You can now start using the plugin and configure more advanced settings from the dashboard.', 'affiliates-for-woocommerce' );
			?>
			<div class="ddfw-setup-wizard-ready ddwcpr-onboarding-welcome">
				<div class="ddfw-success-icon-wrap">
					<div style="background:#eef2ff; border-radius:50%; width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
						<svg class="ddfw-success-svg" width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path class="ddfw-check-path" d="M5 13L9 17L19 7" stroke="#0256ff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</div>
				</div>
				<p class="ddfw-setup-wizard-ready-title"><?php echo esc_html( $title ); ?></p>
				<p class="ddfw-setup-wizard-ready-desc"><?php echo esc_html( $desc ); ?></p>
			</div>
			<?php
		}
	}
}
