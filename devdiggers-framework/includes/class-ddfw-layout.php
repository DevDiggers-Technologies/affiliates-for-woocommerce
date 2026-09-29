<?php
/**
 * File for handling the DevDiggers plugins layout functionalities.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Layout' ) ) {
	/**
	 * Class for handling the DevDiggers plugins layout functionalities.
	 */
	class DDFW_Layout {
		/**
		 * Get the form section layout.
		 *
		 * #param array $args The arguments for the form section.
		 * @param string $setting_field_name The name of the setting field.
		 * @param array $form_submit_button The form submit button configuration.
		 * @param string $form_id The id of the form.
		 * @return void
		 */
		public function get_form_section_layout( $args, $setting_field_name = '', $form_submit_button = [], $form_id = '' ) {
			include DDFW_FILE . 'templates/layout/form-section.php';
		}

		/**
		 * Get the edit screen layout: a two column editor with a sticky side rail, in the shape
		 * of the WordPress edit post screen. A section goes to the rail with 'column' => 'side'.
		 *
		 * @param array  $args               The sections, same shape as the form section layout.
		 * @param string $setting_field_name The settings group, when the form posts to options.php.
		 * @param array  $form_submit_button The primary action rendered in the title bar.
		 * @param string $form_id            The id of the form.
		 * @param array  $screen_header      heading, description, back_button_url, header_buttons.
		 * @return void
		 */
		public function get_edit_screen_layout( $args, $setting_field_name = '', $form_submit_button = [], $form_id = '', $screen_header = [] ) {
			include DDFW_FILE . 'templates/layout/edit-screen.php';
		}
	}
}
