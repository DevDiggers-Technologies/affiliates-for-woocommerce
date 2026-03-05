<?php
/**
 * File for handling the DevDiggers plugins form field functionalities.
 *
 * @author DevDiggers
 * @version 1.0.0
 * @package DevDiggers\Framework
 */

namespace DevDiggers\Framework\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDFW_Form_Field' ) ) {
	/**
	 * Class for handling the DevDiggers plugins form field functionalities.
	 */
	class DDFW_Form_Field {
		/**
		 * Display Form field function
		 *
		 * @param array $args
		 * @return void
		 */
		public static function display_form_field( $args ) {
			$defaults = [
				'type'                 => 'text',
				'name'                 => '',
				'value'                => '',
				'label'                => '',
				'checkbox_label'       => '',
				'checkbox_value'       => 'yes',
				'description'          => '',
				'placeholder'          => '',
				'maxlength'            => false,
				'required'             => false,
				'autocomplete'         => false,
				'id'                   => '',
				'default_class'        => true,
				'class'                => [],
				'td_class'             => [],
				'label_class'          => [],
				'input_class'          => [],
				'field_class'          => [],
				'return'               => false,
				'options'              => [],
				'show_fields'          => [],
				'only_hide_fields'     => [],
				'custom_attributes'    => [],
				'validate'             => [],
				'default'              => '',
				'autofocus'            => '',
				'priority'             => '',
				'after_field_text'     => '',
				'show_frontend_fields' => false,
				'radio_single_line'    => false,
				'default_image'        => '',
				'show_condition'       => true,
				'td_colspan'           => '',
			];

			$args  = wp_parse_args( $args, $defaults );

			if ( ! $args[ 'show_condition' ] ) {
				return;
			}

			$value = $args[ 'value' ];

			if ( ! empty( $args[ 'after_field_text' ] ) ) {
				$args[ 'after_field_text' ] = '&emsp;' . $args[ 'after_field_text' ];
			}

			if ( ! empty( $args[ 'name' ] ) ) {
				$name = $args[ 'name' ];
			} else {
				$name = '_' . str_replace( '-', '_', $args[ 'id' ] );
			}

			if ( empty( $args[ 'checkbox_label' ] ) ) {
				$args[ 'checkbox_label' ] = $args[ 'label' ];
			}

			$args = apply_filters( 'ddfw_form_field_args', $args, $name, $value );

			if ( is_string( $args['class'] ) ) {
				$args['class'] = [ $args['class'] ];
			}

			if ( is_string( $args['td_class'] ) ) {
				$args['td_class'] = [ $args['td_class'] ];
			}

			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'devdiggers-framework' ) . '">*</abbr>';
			} else {
				$required = '';
			}

			if ( is_string( $args['label_class'] ) ) {
				$args['label_class'] = [ $args['label_class'] ];
			}

			if ( is_null( $value ) ) {
				$value = $args['default'];
			}

			$custom_attributes         = [];
			$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

			if ( $args['maxlength'] ) {
				$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
			}

			if ( ! empty( $args['autocomplete'] ) ) {
				$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
			}

			if ( true === $args['autofocus'] ) {
				$args['custom_attributes']['autofocus'] = 'autofocus';
			}

			if ( $args['description'] ) {
				$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
			}

			if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			if ( ! empty( $args['validate'] ) ) {
				foreach ( $args['validate'] as $validate ) {
					$args['class'][] = 'validate-' . $validate;
				}
			}

			$field    = '';
			$label_id = $args['id'];
			$sort     = $args['priority'] ? $args['priority'] : '';
			$is_admin = $args['show_frontend_fields'] ? false : is_admin();

			if ( ! empty( $args['show_adminend_fields'] ) && $args['show_adminend_fields'] ) {
				$is_admin = true;
			}

			if ( $is_admin ) {
				$field_container = '<tr valign="top" class="' . esc_attr( implode( ' ', $args['field_class'] ) ) . '">%1$s</tr>';
			} else {
				$field_container = '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide ' . esc_attr( implode( ' ', $args['field_class'] ) ) . '">%1$s</p>';
			}

			$default_input_class = $is_admin ? 'regular-text' : 'form-control woocommerce-Input woocommerce-Input--text input-text';

			switch ( $args['type'] ) {
				case 'country':
					ob_start();
					$countries = 'shipping_country' === $name ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

					if ( 1 === count( $countries ) ) {
						?>
						<strong><?php echo esc_html( current( array_values( $countries ) ) ); ?></strong>
						<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( current( array_keys( $countries ) ) ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> class="country_to_state" readonly="readonly" />
						<?php
					} else {
						$data_label = ! empty( $args['label'] ) ? 'data-label="' . esc_attr( $args['label'] ) . '"' : '';
						?>
						<select id="billing_country" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="country_to_state country_select <?php echo esc_attr( $default_input_class ); ?> <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> data-placeholder="<?php echo esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_attr__( 'Select a country / region&hellip;', 'devdiggers-framework' ) ); ?>" <?php echo wp_kses_post( $data_label ); ?>>
							<option value=""><?php esc_html_e( 'Select a country / region&hellip;', 'devdiggers-framework' ); ?></option>
							<?php foreach ( $countries as $ckey => $cvalue ) : ?>
								<option value="<?php echo esc_attr( $ckey ); ?>" <?php selected( $value, $ckey ); ?>><?php echo esc_html( $cvalue ); ?></option>
							<?php endforeach; ?>
						</select>
						<noscript>
							<button type="submit" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update country / region', 'devdiggers-framework' ); ?>">
								<?php esc_html_e( 'Update country / region', 'devdiggers-framework' ); ?>
							</button>
						</noscript>
						<?php
					}
					$field .= ob_get_clean();
					break;

				case 'state':
					ob_start();
					?>
					<input type="text" class="<?php echo esc_attr( $default_input_class ); ?> <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" value="<?php echo esc_attr( $value ); ?>"  placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> data-input-classes="<?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>"/>
					<?php
					$field .= ob_get_clean();
					break;

				case 'textarea':
					ob_start();
					?>
					<textarea name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $default_input_class ); ?> <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" <?php echo ( empty( $args['custom_attributes']['rows'] ) ? ' rows="4"' : '' ); ?> <?php echo ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ); ?> <?php echo implode( ' ', $custom_attributes ); ?>><?php echo esc_textarea( $value ); ?></textarea>
					<?php
					$field .= ob_get_clean();
					break;

				case 'checkbox':
					ob_start();
					?>
					<label class="checkbox <?php echo esc_attr( implode( ' ', $args['label_class'] ) ); ?>">
						<input type="<?php echo esc_attr( $args['type'] ); ?>" class="input-checkbox <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $args['checkbox_value'] ); ?>" <?php checked( $value, $args['checkbox_value'] ); ?> <?php echo implode( ' ', $custom_attributes ); ?> />
						<?php echo wp_kses_post( $args['checkbox_label'] . $required ); ?>
					</label>
					<?php
					$field .= ob_get_clean();
					break;

				case 'text':
				case 'password':
				case 'datetime-local':
				case 'date':
				case 'color':
				case 'month':
				case 'time':
				case 'week':
				case 'number':
				case 'email':
				case 'url':
				case 'tel':
					ob_start();
					?>
					<input type="<?php echo esc_attr( $args['type'] ); ?>" class="<?php echo esc_attr( $default_input_class ); ?> <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> />
					<?php
					$field .= ob_get_clean();
					break;

				case 'hidden':
					ob_start();
					?>
					<input type="<?php echo esc_attr( $args['type'] ); ?>" class="input-hidden <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> />
					<?php
					$field .= ob_get_clean();
					break;

				case 'select':
					ob_start();
					?>
					<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> data-placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" data-show-fields='<?php echo esc_attr( ! empty( $args['show_fields'] ) ? json_encode( $args['show_fields'] ) : 'false' ); ?>' data-only-hide-fields='<?php echo esc_attr( ! empty( $args['only_hide_fields'] ) ? json_encode( $args['only_hide_fields'] ) : 'false' ); ?>'>
						<?php
						if ( ! empty( $args['options'] ) ) {
							foreach ( $args['options'] as $option_key => $option_text ) {
								if ( '' === $option_key ) {
									if ( empty( $args['placeholder'] ) ) {
										$args['placeholder'] = $option_text ? $option_text : esc_html__( 'Choose an option', 'devdiggers-framework' );
									}
									$custom_attributes[] = 'data-allow_clear="true"';
								}
								if ( is_array( $value ) ) {
									?>
									<option value="<?php echo esc_attr( $option_key ); ?>" <?php echo in_array( $option_key, $value, true ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $option_text ); ?></option>
									<?php
								} else {
									?>
									<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $value, $option_key ); ?>><?php echo esc_html( $option_text ); ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<?php
					$field .= ob_get_clean();
					break;

				case 'radio':
					ob_start();
					$label_id .= '_' . current( array_keys( $args['options'] ) );
					if ( ! empty( $args['options'] ) ) {
						foreach ( $args['options'] as $option_key => $option_text ) {
							if ( ! $args['radio_single_line'] ) {
								?><p><?php
							}
							?>
							<input type="radio" class="<?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" value="<?php echo esc_attr( $option_key ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> id="<?php echo esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ); ?>" <?php checked( $value, $option_key ); ?> data-show-fields='<?php echo esc_attr( ! empty( $args['show_fields'] ) ? json_encode( $args['show_fields'] ) : 'false' ); ?>' data-only-hide-fields='<?php echo esc_attr( ! empty( $args['only_hide_fields'] ) ? json_encode( $args['only_hide_fields'] ) : 'false' ); ?>' />
							<label for="<?php echo esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ); ?>" class="radio <?php echo esc_attr( implode( ' ', $args['label_class'] ) ); ?>"><?php echo esc_html( $option_text ); ?></label>
							<?php
							if ( ! $args['radio_single_line'] ) {
								?></p><?php
							}
						}
					}
					$field .= ob_get_clean();
					break;

				case 'editor':
					ob_start();
					$editor_args = wp_parse_args(
						$args,
						[
							'wpautop'       => true,    // Choose if you want to use wpautop.
							'media_buttons' => true,    // Choose if showing media button(s).
							'textarea_name' => $name,   // Set the textarea name to something different, square brackets [] can be used here.
							'textarea_rows' => 10,      // Set the number of rows.
							'tabindex'      => '',
							'editor_css'    => '',      // Extra CSS styles to include in the editor iframe.
							'editor_class'  => '',      // Add extra class(es) to the editor textarea.
							'teeny'         => false,   // Output the minimal editor config used in Press This.
							'dfw'           => false,   // Replace the default fullscreen with DFW (needs specific DOM elements and css).
							'quicktags'     => true,    // Load Quicktags, can be used to pass settings directly to Quicktags using an array().
							'tinymce'       => [ 
								'content_css' => ''
							],
						]
					);
					wp_editor( $value, $args['id'], $editor_args );
					$field .= ob_get_clean();
					break;

				case 'image':
					ob_start();
					$image_src  = wp_get_attachment_image_src( $value, 'thumbnail' );
					$image_url  = ! empty( $image_src ) ? $image_src[0] : ( $args['default_image'] ? $args['default_image'] : '' );
					?>
					<div>
						<div class="ddfw-image-upload regular-text">
							<div class="ddfw-image-preview-container <?php echo esc_attr( $value || $args['default_image'] ? '' : 'ddfw-hide' ); ?>">
								<div class="ddfw-image-preview-wrapper">
									<img src="<?php echo esc_url( $image_url ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>-preview" />
									<div class="ddfw-image-actions">
										<span class="ddfw-upload-image-button" data-id="<?php echo esc_attr( $args['id'] ); ?>" title="<?php esc_attr_e( 'Replace Image', 'devdiggers-framework' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</span>
										<span class="ddfw-remove-image-button <?php echo esc_attr( $args['default_image'] && ! $value ? 'ddfw-hide' : '' ); ?>" data-id="<?php echo esc_attr( $args['id'] ); ?>" title="<?php esc_attr_e( 'Remove Image', 'devdiggers-framework' ); ?>">
											<span class="dashicons dashicons-trash"></span>
										</span>
									</div>
								</div>
								<p><?php esc_html_e( 'Hover on the image to replace or remove', 'devdiggers-framework' ); ?></p>
							</div>
							<div class="ddfw-image-upload-wrapper <?php echo esc_attr( $value || $args['default_image'] ? 'ddfw-hide' : '' ); ?>">
								<div class="ddfw-upload-icon-wrapper">
									<?php DDFW_SVG::get_svg_icon( 'upload', false ); ?>
								</div>
								<h3><?php esc_html_e( 'Upload Image', 'devdiggers-framework' ); ?></h3>
								<p><?php esc_html_e( 'Click the upload button to select an image from your media library or upload a new one.', 'devdiggers-framework' ); ?></p>
								<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
								<input type="hidden" id="<?php echo esc_attr( $args['id'] ); ?>-default-image" value="<?php echo esc_attr( $args['default_image'] ? $args['default_image'] : '' ); ?>" />
								<button type="button" class="button ddfw-upload-image-button" data-id="<?php echo esc_attr( $args['id'] ); ?>" <?php echo implode( ' ', $custom_attributes ); ?>>
									<?php
									DDFW_SVG::get_svg_icon(
										'file',
										false,
										[ 'size' => 15 ]
									);
									esc_html_e( 'Choose Image', 'devdiggers-framework' );
									?>
								</button>
							</div>
						</div>
					</div>
					<?php
					$field .= ob_get_clean();
					break;

				case 'colorpicker':
					ob_start();
					?>
					<input type="<?php echo esc_attr( $args['type'] ); ?>" class="ddfw-color-picker <?php echo esc_attr( $default_input_class ); ?> <?php echo esc_attr( implode( ' ', $args['input_class'] ) ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo implode( ' ', $custom_attributes ); ?> data-default-value="<?php echo esc_attr( $value ); ?>" />
					<?php
					$field .= ob_get_clean();
					break;

				case 'products':
					ob_start();
					?>
					<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $default_input_class ); ?> ddfw-products" <?php echo implode( ' ', $custom_attributes ); ?> data-placeholder="<?php esc_attr_e( 'Search by name', 'devdiggers-framework' ); ?>">
						<?php
						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								foreach ( $value as $product_id ) {
									$product = wc_get_product( $product_id );
									if ( $product ) {
										?>
										<option value="<?php echo esc_attr( $product_id ); ?>" selected="selected"><?php echo esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ); ?></option>
										<?php
									}
								}
							} else {
								$product = wc_get_product( $value );
								if ( $product ) {
									?>
									<option value="<?php echo esc_attr( $product->get_id() ); ?>" selected="selected"><?php echo esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ); ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<?php
					$field .= ob_get_clean();
					break;

				case 'categories':
					ob_start();
					?>
					<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $default_input_class ); ?> ddfw-categories" <?php echo implode( ' ', $custom_attributes ); ?> data-placeholder="<?php esc_attr_e( 'Search by name', 'devdiggers-framework' ); ?>">
						<?php
						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								foreach ( $value as $category_id ) {
									$category = get_term( $category_id, 'product_cat' );
									if ( $category ) {
										?>
										<option value="<?php echo esc_attr( $category_id ); ?>" selected="selected"><?php echo esc_html( $category->name ); ?></option>
										<?php
									}
								}
							} else {
								$category = get_term( $value, 'product_cat' );
								if ( $category ) {
									?>
									<option value="<?php echo esc_attr( $category->term_id ); ?>" selected="selected"><?php echo esc_html( $category->name ); ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<?php
					$field .= ob_get_clean();
					break;
				case 'users':
					ob_start();
					?>
					<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" class="<?php echo esc_attr( $default_input_class ); ?> ddfw-users" <?php echo implode( ' ', $custom_attributes ); ?> data-placeholder="<?php esc_attr_e( 'Search Users', 'devdiggers-framework' ); ?>">
						<?php
						if ( ! empty( $value ) ) {
							if ( is_array( $value ) ) {
								foreach ( $value as $user_id ) {
									$user = get_user_by( 'ID', $user_id );
									if ( $user ) {
										?>
										<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected"><?php echo esc_html( '(#' . $user->ID . ') ' . $user->user_login . ' <' . $user->user_email . '>' ); ?></option>
										<?php
									}
								}
							} else {
								$user = get_user_by( 'ID', $value );
								if ( $user ) {
									?>
									<option value="<?php echo esc_attr( $user->ID ); ?>" selected="selected"><?php echo esc_html( '(#' . $user->ID . ') ' . $user->user_login . ' <' . $user->user_email . '>' ); ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<?php
					$field .= ob_get_clean();
					break;
				case 'two_input_columns':
					ob_start();
					?>
					<div class="<?php echo esc_attr( ( isset( $args['default_class'] ) && ! $args['default_class'] ? '' : 'ddfw-two-input-columns' ) . ( ! empty( $args['class'] ) ? ' ' . implode( ' ', $args['class'] ) : '' ) ); ?>">
						<?php
						if ( ! empty( $args['columns'] ) ) {
							foreach ( $args['columns'] as $column ) {
								if ( empty( $column[ 'name' ] ) ) {
									$column[ 'name' ] = '_' . str_replace( '-', '_', $column[ 'id' ] );
								}
								$wrapper = ! empty( $column['wrapper'] ) ? $column['wrapper'] : 'span'; ?>
								<<?php echo esc_attr( $wrapper ); ?>>
									<?php echo wp_kses_post( isset( $column['prefix'] ) ? $column['prefix'] : '' ); ?>
									<input
										type="<?php echo esc_attr( $column['type'] ); ?>"
										name="<?php echo esc_attr( $column['name'] ); ?>"
										id="<?php echo esc_attr( $column['id'] ); ?>"
										value="<?php echo esc_attr( $column['value'] ); ?>"
										<?php echo ! empty( $column['class'] ) ? 'class="' . esc_attr( implode( ' ', (array) $column['class'] ) ) . '"' : ''; ?>
										<?php echo ! empty( $column['placeholder'] ) ? 'placeholder="' . esc_attr( $column['placeholder'] ) . '"' : ''; ?>
										<?php echo isset( $column['min'] ) ? 'min="' . esc_attr( $column['min'] ) . '"' : ''; ?>
										<?php echo esc_attr( ! empty( $column['custom_attributes'] ) ? implode( ' ', array_map( function( $k, $v ) { return esc_attr( $k ) . '="' . esc_attr( $v ) . '"'; }, array_keys( $column['custom_attributes'] ), $column['custom_attributes'] ) ) : '' ); ?>
									/>
									<?php echo wp_kses_post( isset( $column['suffix'] ) ? $column['suffix'] : '' ); ?>
								</<?php echo esc_attr( $wrapper ); ?>>
								<?php
							}
						}
						?>
					</div>
					<?php
					$field .= ob_get_clean();
					break;
				case 'user_roles':
					ob_start();
					global $wp_roles;
					$all_roles      = $wp_roles->roles;
					$selected_roles = is_array( $value ) ? $value : [];
					?>
					<select
						id="<?php echo esc_attr( $args['id'] ); ?>"
						class="regular-text"
						name="<?php echo esc_attr( $args['name'] ); ?>"
						multiple
						data-placeholder="<?php esc_attr_e( 'Search by role', 'devdiggers-framework' ); ?>"
					>
						<?php
						if ( ! empty( $all_roles ) ) {
							foreach ( $all_roles as $key => $role_data ) {
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array( $key, $selected_roles, true ) ); ?>>
									<?php echo esc_html( $role_data['name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
					<?php
					$field .= ob_get_clean();
					break;
				case 'field_html':
					$field = ! empty( $args['html'] ) ? $args['html'] : '';
					break;
			}

			if ( ! empty( $field ) ) {
				$field_html = '';

				if ( $args['label'] ) {
					if ( $is_admin ) {
						ob_start();
						?>
						<th>
							<label for="<?php echo esc_attr( $label_id ); ?>" class="<?php echo esc_attr( implode( ' ', $args['label_class'] ) ); ?>">
								<?php echo wp_kses_post( $args['label'] . $required ); ?>
							</label>
						</th>
						<?php
						$field_html .= ob_get_clean();
					} else {
						ob_start();
						?>
						<label for="<?php echo esc_attr( $label_id ); ?>" class="<?php echo esc_attr( implode( ' ', $args['label_class'] ) ); ?>">
							<?php echo wp_kses_post( $args['label'] . $required ); ?>
						</label>
						<?php
						$field_html .= ob_get_clean();
					}
				}

				if ( $is_admin ) {
					$field_html .= '<td ' . ( ! empty( $args['td_class'] ) ? 'class="' . esc_attr( implode( ' ', $args['td_class'] ) ) . '"' : '' ) . ( ! empty( $args['td_colspan'] ) ? ' colspan="' . esc_attr( $args['td_colspan'] ) . '"' : '' ) . '>'; // phpcs:ignore
				}

				$field_html .= $field . $args[ 'after_field_text' ];

				if ( $args['description'] ) {
					if ( $is_admin ) {
						ob_start();
						?>
						<p class="description" id="<?php echo esc_attr( $args['id'] ); ?>-description" aria-hidden="true">
							<i><?php echo wp_kses_post( $args['description'] ); ?></i>
						</p>
						<?php
						$field_html .= ob_get_clean();
					} else {
						ob_start();
						?>
						<i class="description" id="<?php echo esc_attr( $args['id'] ); ?>-description" aria-hidden="true">
							<?php echo wp_kses_post( $args['description'] ); ?>
						</i>
						<?php
						$field_html .= ob_get_clean();
					}
				}

				if ( $is_admin ) {
					$field_html .= '</td>';
				}

				$field = sprintf( $field_container, $field_html );
			}

			$field = apply_filters( 'ddfw_form_field_' . $args[ 'type' ], $field, $name, $args, $value );
			$field = apply_filters( 'ddfw_form_field', $field, $name, $args, $value );

			if ( $args[ 'return' ] ) {
				return $field;
			} else {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $field;
			}
		}
	}
}
