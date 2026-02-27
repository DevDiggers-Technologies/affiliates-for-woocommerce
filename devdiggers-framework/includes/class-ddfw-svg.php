<?php
/**
 * File for handling the DevDiggers plugins svg functionalities.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_SVG' ) ) {
	/**
	 * Class for handling the DevDiggers plugins svg functionalities.
	 */
	class DDFW_SVG {
		/**
		 * Get the SVG icon based on the type and arguments.
		 * 
		 * @param string $icon_type The type of the SVG icon.
		 * @param bool $return Whether to return the SVG as a string or echo it directly.
		 * @param array $args Optional arguments for the SVG icon.
		 * @return string|void The SVG icon as a string if $return is true, otherwise echoes the SVG directly.
		 */
		public static function get_svg_icon( $icon_type, $return = true, $args = [] ) {
			$size         = ! empty( $args[ 'size' ] ) ? $args[ 'size' ] : '24';
			$size_attr    = 'width="' . $size . '" height="' . $size . '"';
			$stroke_color = ! empty( $args[ 'stroke_color' ] ) ? $args[ 'stroke_color' ] : 'currentColor';
			$stroke_width = isset( $args[ 'stroke_width' ] ) ? $args[ 'stroke_width' ] : '2';
			$fill         = ! empty( $args[ 'fill' ] ) ? $args[ 'fill' ] : 'none';

			$svg_icons = [
				'general'      => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . '  viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
				'upload'       => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" x2="12" y1="3" y2="15"></line></svg>',
				'file'         => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path></svg>',
				'crown'        => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z"></path><path d="M5 21h14"></path></svg>',
				'circle-check' => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"></path><path d="m9 11 3 3L22 4"></path></svg>',
				'plus'         => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>',
				'circle-x'     => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
				'license'      => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"></path><path d="m21 2-9.6 9.6"></path><circle cx="7.5" cy="15.5" r="5.5"></circle></svg>',
			];

			$svg_icons = apply_filters( 'ddfw_modify_svg_icons', $svg_icons, $args );

			if ( $return ) {
				if ( ! empty( $args[ 'wrapper' ] ) ) {
					$element = $args[ 'wrapper' ][ 'element' ] ?? 'span';
					$classes = $args[ 'wrapper' ][ 'class' ] ?? '';
					$id      = $args[ 'wrapper' ][ 'id' ] ?? '';
					$svg     = '<' . esc_attr( $element ) . ' class="' . esc_attr( $classes ) . '" id="' . esc_attr( $id ) . '">';
					$svg    .= ! empty( $svg_icons[ $icon_type ] ) ? $svg_icons[ $icon_type ] : '';
					$svg    .= '</' . esc_attr( $element ) . '>';

					return $svg;
				} else {
					// If returning, return the SVG as a string.
					return ! empty( $svg_icons[ $icon_type ] ) ? $svg_icons[ $icon_type ] : '';
				}
			} else {
				if ( ! empty( $args[ 'wrapper' ] ) ) {
					$element = $args[ 'wrapper' ][ 'element' ] ?? 'span';
					$classes = $args[ 'wrapper' ][ 'class' ] ?? '';
					$id      = $args[ 'wrapper' ][ 'id' ] ?? '';
					?>
					<<?php echo esc_attr( $element ); ?> class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>">
					<?php
					echo wp_kses( ! empty( $svg_icons[ $icon_type ] ) ? $svg_icons[ $icon_type ] : '', ddfw_kses_allowed_svg_tags() );
					?>
					</<?php echo esc_attr( $element ); ?>>
					<?php

				} else {
					// If not returning, echo the SVG directly.
					echo wp_kses( ! empty( $svg_icons[ $icon_type ] ) ? $svg_icons[ $icon_type ] : '', ddfw_kses_allowed_svg_tags() );
				}
			}
		}
	}
}
