<?php
/**
 * File for handling global functions in the DevDiggers Plugin Framework.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'ddfw_get_parent_menu_slug' ) ) {
	/**
	 * Get the parent menu slug for the DevDiggers Plugins menu.
	 *
	 * @return string
	 */
	function ddfw_get_parent_menu_slug() {
		return apply_filters( 'ddfw_modify_parent_menu_slug', 'devdiggers-plugins' );
	}
}

if ( ! function_exists( 'ddfw_get_menu_capability' ) ) {
	/**
	 * Get the capability required to access the dashboard menu.
	 *
	 * @return string
	 */
	function ddfw_get_menu_capability() {
		return apply_filters( 'ddfw_modify_admin_menu_capability', class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options' );
	}
}

if ( ! function_exists( 'ddfw_get_placeholder_image_src' ) ) {
	/**
	 * Get placeholder image src function
	 *
	 * @return string
	 */
	function ddfw_get_placeholder_image_src() {
		return DDFW_URL . 'assets/images/placeholder.png';
	}
}

if ( ! function_exists( 'ddfw_print_notification' ) ) {
	/**
	 * Print a notification message.
	 *
	 * @param string $message The message to display.
	 * @param string $type    The type of notification (e.g., 'success', 'error').
	 * @param bool   $dismissible Whether the notification is dismissible.
	 */
	function ddfw_print_notification( $message, $type = 'success', $dismissible = true ) {
		include DDFW_FILE . 'templates/global/notice.php';
	}
}

if ( ! function_exists( 'ddfw_kses_allowed_svg_tags' ) ) {
	/**
	 * Get allowed SVG tags for KSES filtering.
	 *
	 * @return array
	 */
	function ddfw_kses_allowed_svg_tags() {
		return [
			'svg'      => [
				'class'           => true,
				'data-*'          => true,
				'aria-*'          => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true,
				'version'         => true,
				'x'               => true,
				'y'               => true,
				'style'           => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-opacity'    => true,
			],
			'circle'   => [
				'class'           => true,
				'cx'              => true,
				'cy'              => true,
				'r'               => true,
				'fill'            => true,
				'style'           => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-opacity'    => true,
			],
			'g'        => [ 'fill' => true, 'fill-opacity' => true ],
			'polyline' => [
				'class'  => true,
				'points' => true,
				'd'               => true,
				'fill'            => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-opacity'    => true,
			],
			'polygon'  => [
				'class'  => true,
				'points' => true,
				'd'               => true,
				'fill'            => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-opacity'    => true,
			],
			'line'     => [
				'class' => true,
				'x1'    => true,
				'x2'    => true,
				'y1'    => true,
				'y2'    => true,
			],
			'title'    => [ 'title' => true ],
			'path'     => [
				'class'           => true,
				'd'               => true,
				'fill'            => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-opacity'    => true,
			],
			'rect'     => [
				'class'           => true,
				'x'               => true,
				'y'               => true,
				'rx'              => true,
				'ry'              => true,
				'fill'            => true,
				'width'           => true,
				'height'          => true,
				'clip-rule'       => true,
				'fill-rule'       => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'fill-opacity'    => true,
			],
		];
	}
}

if ( ! function_exists( 'ddfw_kses_allowed_form_html' ) ) {
	/**
	 * Allowed HTML for escaping assembled form-field markup with wp_kses().
	 *
	 * Extends the default "post" allow-list with form controls and inline SVG so
	 * that pre-built form markup can be escaped on output without stripping
	 * inputs, selects, textareas, buttons or icons.
	 *
	 * @return array
	 */
	function ddfw_kses_allowed_form_html() {
		$allowed = array_merge( wp_kses_allowed_html( 'post' ), ddfw_kses_allowed_svg_tags() );

		$global_attrs = [
			'id'           => true,
			'class'        => true,
			'style'        => true,
			'title'        => true,
			'name'         => true,
			'value'        => true,
			'data-*'       => true,
			'aria-*'       => true,
			'role'         => true,
			'tabindex'     => true,
			'placeholder'  => true,
			'autocomplete' => true,
			'spellcheck'   => true,
			'required'     => true,
			'disabled'     => true,
			'readonly'     => true,
			'checked'      => true,
			'selected'     => true,
			'multiple'     => true,
			'min'          => true,
			'max'          => true,
			'step'         => true,
			'minlength'    => true,
			'maxlength'    => true,
			'pattern'      => true,
			'rows'         => true,
			'cols'         => true,
			'size'         => true,
			'for'          => true,
			'type'         => true,
			'accept'       => true,
		];

		$allowed['input']    = $global_attrs;
		$allowed['select']   = $global_attrs;
		$allowed['option']   = $global_attrs;
		$allowed['optgroup'] = $global_attrs;
		$allowed['textarea'] = $global_attrs;
		$allowed['button']   = $global_attrs;
		$allowed['label']    = $global_attrs;
		$allowed['form']     = array_merge( $global_attrs, [ 'action' => true, 'method' => true, 'enctype' => true, 'target' => true ] );
		$allowed['fieldset'] = $global_attrs;
		$allowed['legend']   = $global_attrs;
		$allowed['datalist'] = $global_attrs;

		/**
		 * Filter the allowed HTML used to escape framework form-field markup.
		 *
		 * @since 1.0.0
		 *
		 * @param array $allowed Allowed HTML tags/attributes.
		 */
		return apply_filters( 'ddfw_kses_allowed_form_html', $allowed );
	}
}

if ( ! function_exists( 'ddfw_upgrade_to_pro_section' ) ) {
	/**
	 * Upgrade to Pro section function
	 *
	 * @param array $args
	 * @return void
	 */
	function ddfw_upgrade_to_pro_section( $args ) {
		include DDFW_FILE . 'templates/layout/upgrade-to-pro.php';
	}
}

if ( ! function_exists( 'ddfw_pro_tag' ) ) {
	/**
	 * Pro tag function
	 *
	 * @return void
	 */
	function ddfw_pro_tag() {
		?>
		<span class="ddfw-pro-tag"><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'PRO', 'affiliates-for-woocommerce' ); ?></span>
		<?php
	}
}

if ( ! function_exists( 'ddfw_fields_heading' ) ) {
	/**
	 * Fields heading function
	 *
	 * @param array $args
	 * @return void
	 */
	function ddfw_fields_heading( $args ) {
		include DDFW_FILE . 'templates/layout/field-section-header.php';
	}
}

if ( ! function_exists( 'ddfw_get_devdiggers_plugin_menu_icon_src' ) ) {
	/**
	 * Get the DevDiggers plugin menu icon src.
	 *
	 * @return string
	 */
	function ddfw_get_devdiggers_plugin_menu_icon_src() {
		return DDFW_URL . 'assets/images/devdiggers-logo.svg';
	}
}

