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
				'focusable'       => true,
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
				'opacity'         => true,
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
				'opacity'         => true,
			],
			'g'        => [ 'fill' => true, 'fill-opacity' => true, 'opacity' => true ],
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
				'opacity'         => true,
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
				'opacity'         => true,
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
				'opacity'         => true,
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
				'opacity'         => true,
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
		$allowed['noscript'] = $global_attrs;

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

if ( ! function_exists( 'ddfw_kses_form_html' ) ) {
	/**
	 * Escape markup that may carry underscore row templates function
	 *
	 * wp_kses drops a script tag it does not allow but keeps everything inside it,
	 * which turns a hidden row template into live form fields floating outside the
	 * table it belongs to. The templates are pulled out first, escaped on their own,
	 * and put back inside a script tag this function builds itself, so nothing the
	 * caller supplies can ever become executable JavaScript.
	 *
	 * @param string $html Markup to escape.
	 * @return string
	 */
	function ddfw_kses_form_html( $html ) {
		$allowed   = ddfw_kses_allowed_form_html();
		$templates = [];

		$html = preg_replace_callback(
			'#<script\b[^>]*\btype=["\']text/html["\'][^>]*>(.*?)</script>#is',
			function ( $matches ) use ( &$templates, $allowed ) {
				$id = '';

				if ( preg_match( '#\bid=["\']([^"\']+)["\']#i', $matches[ 0 ], $id_match ) ) {
					$id = sanitize_key( $id_match[ 1 ] );
				}

				// A plain text token, because wp_kses strips HTML comments.
				$placeholder               = 'DDFWROWTEMPLATE' . count( $templates ) . 'ENDROWTEMPLATE';
				$templates[ $placeholder ] = '<script type="text/html"' . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . '>' . wp_kses( $matches[ 1 ], $allowed ) . '</script>';

				return $placeholder;
			},
			$html
		);

		$html = wp_kses( $html, $allowed );

		return strtr( $html, $templates );
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

if ( ! function_exists( 'ddfw_print_empty_state' ) ) {
	/**
	 * Print the shared empty state block.
	 *
	 * Written for WP_List_Table::no_items(), which prints into a single colspanned cell where
	 * the table's own column widths and text alignment still apply. The block centres itself
	 * with flex rather than inheriting text-align, so it lands the same way in every plugin.
	 *
	 * @param array $args {
	 *     Empty state arguments.
	 *
	 *     @type string $title       Required. Short heading, already translated.
	 *     @type string $description Optional. One or two sentences, already translated.
	 *     @type string $icon        Optional. Inline SVG markup. Defaults to a document mark.
	 *     @type string $button_url  Optional. Primary action link.
	 *     @type string $button_text Optional. Primary action label.
	 * }
	 * @return void
	 */
	function ddfw_print_empty_state( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'title'       => __( 'Nothing here yet', 'affiliates-for-woocommerce' ),
				'description' => '',
				'icon'        => '',
				'button_url'  => '',
				'button_text' => '',
			]
		);

		include DDFW_FILE . 'templates/global/empty-state.php';
	}
}

if ( ! function_exists( 'ddfw_get_pro_tag' ) ) {
	/**
	 * Pro tag as a string, for a label or a table cell.
	 *
	 * @return string
	 */
	function ddfw_get_pro_tag() {
		ob_start();
		ddfw_pro_tag();

		return trim( ob_get_clean() );
	}
}

if ( ! function_exists( 'ddfw_get_upgrade_to_pro_section' ) ) {
	/**
	 * Upgrade to Pro section as a string, for a layout argument such as after_header_html.
	 *
	 * @param array $args Same arguments as ddfw_upgrade_to_pro_section().
	 * @return string
	 */
	function ddfw_get_upgrade_to_pro_section( $args ) {
		ob_start();
		ddfw_upgrade_to_pro_section( $args );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'ddfw_locked_field' ) ) {
	/**
	 * A disabled copy of a field, labelled PRO.
	 *
	 * The field is renamed to a locked id, so a setting of that name is never
	 * registered and options.php discards it even if the disabled attribute is
	 * removed in the browser.
	 *
	 * @param array  $field  Field arguments as DDFW_Layout expects them.
	 * @param string $prefix Plugin prefix, for example ddwcmpa.
	 * @return array
	 */
	function ddfw_locked_field( $field, $prefix = 'ddfw' ) {
		$prefix = trim( (string) $prefix, '-_' );
		$id     = $prefix . '-locked-' . ( isset( $field['id'] ) ? $field['id'] : '' );

		$field['label']             = ( isset( $field['label'] ) ? $field['label'] : '' ) . ' ' . ddfw_get_pro_tag();
		$field['id']                = $id;
		$field['name']              = '_' . str_replace( '-', '_', $id );
		$field['field_class']       = [ 'ddfw-upgrade-to-pro-tag-wrapper' ];
		$field['custom_attributes'] = array_merge(
			isset( $field['custom_attributes'] ) ? (array) $field['custom_attributes'] : [],
			[ 'disabled' => 'disabled' ]
		);

		return $field;
	}
}

