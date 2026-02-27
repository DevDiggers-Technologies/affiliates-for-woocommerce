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
		 * Get the license layout.
		 *
		 * @return void
		 */
		public function get_license_layout( $args ) {
			include DDFW_FILE . 'templates/layout/license.php';
		}

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
	}
}
