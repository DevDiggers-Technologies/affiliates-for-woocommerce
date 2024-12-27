<?php
/**
 * Global Functions
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! function_exists( 'ddwcaf_is_wallet_active' ) ) {
	/**
	 * Is Wallet Active function
	 *
	 * @return void
	 */
	function ddwcaf_is_wallet_active() {
		return class_exists( 'DDWCWM_Init' );
	}
}

if ( ! function_exists( 'ddwcaf_form_field' ) ) {
	/**
	 * Display Form field function
	 *
	 * @return void
	 */
	function ddwcaf_form_field( $args ) {
		$defaults = [
			'type'                  => 'text',
			'name'                  => '',
			'value'                 => '',
			'label'                 => '',
			'checkbox_label'        => '',
			'checkbox_value'        => 1,
			'description'           => '',
			'no_description_margin' => false,
			'help_tip'              => '',
			'placeholder'           => '',
			'maxlength'             => false,
			'required'              => false,
			'autocomplete'          => false,
			'id'                    => '',
			'class'                 => [],
			'label_class'           => [],
			'input_class'           => [],
			'return'                => false,
			'options'               => [],
			'custom_attributes'     => [],
			'validate'              => [],
			'default'               => '',
			'autofocus'             => '',
			'priority'              => '',
			'show_frontend_fields'  => false,
		];

		$args  = wp_parse_args( $args, $defaults );
		$value = $args[ 'value' ];

		if ( ! empty( $args[ 'name' ] ) ) {
			$name = $args[ 'name' ];
		} else {
			$name = '_' . str_replace( '-', '_', $args[ 'id' ] );
		}

		if ( empty( $args[ 'checkbox_label' ] ) ) {
			$args[ 'checkbox_label' ] = $args[ 'label' ];
		}

		$args = apply_filters( 'ddwcaf_form_field_args', $args, $name, $value );

		if ( is_string( $args['class'] ) ) {
			$args['class'] = [ $args['class'] ];
		}

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'affiliates-for-woocommerce' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = [ $args['label_class'] ];
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling.
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

		if ( $is_admin ) {
			$field_container = '<tr valign="top">%1$s</tr>';
		} else {
			$field_container = '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">%1$s</p>';
		}

		$default_input_class = $is_admin ? 'regular-text' : 'form-control woocommerce-Input';

		switch ( $args['type'] ) {
			case 'country':
				$countries = 'shipping_country' === $name ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

				if ( 1 === count( $countries ) ) {
					$field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';

					$field .= '<input type="hidden" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ', $custom_attributes ) . ' class="country_to_state" readonly="readonly" />';
				} else {
					$data_label = ! empty( $args['label'] ) ? 'data-label="' . esc_attr( $args['label'] ) . '"' : '';

					$field = '<select id="billing_country" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state country_select ' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_attr__( 'Select a country / region&hellip;', 'affiliates-for-woocommerce' ) ) . '" ' . $data_label . '><option value="">' . esc_html__( 'Select a country / region&hellip;', 'affiliates-for-woocommerce' ) . '</option>';

					foreach ( $countries as $ckey => $cvalue ) {
						$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
					}

					$field .= '</select>';

					$field .= '<noscript><button type="submit" name="woocommerce_checkout_update_totals" value="' . esc_attr__( 'Update country / region', 'affiliates-for-woocommerce' ) . '">' . esc_html__( 'Update country / region', 'affiliates-for-woocommerce' ) . '</button></noscript>';
				}

				break;
			case 'state':
				/* Get country this state field is representing */
				$for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $name ? 'billing_country' : 'shipping_country' );
				$states      = WC()->countries->get_states( $for_country );

				if ( is_array( $states ) && empty( $states ) ) {
					$field .= '<input type="hidden" class="hidden" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';
				} elseif ( ! is_null( $for_country ) && is_array( $states ) ) {
					$data_label = ! empty( $args['label'] ) ? 'data-label="' . esc_attr( $args['label'] ) . '"' : '';

					$field .= '<select id="billing_state" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;', 'affiliates-for-woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . $data_label . '>
						<option value="">' . esc_html__( 'Select an option&hellip;', 'affiliates-for-woocommerce' ) . '</option>';

					foreach ( $states as $ckey => $cvalue ) {
						$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
					}

					$field .= '</select>';
				} else {
					$field .= '<input type="text" class="' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';
				}

				break;
			case 'textarea':
				$field .= '<textarea name="' . esc_attr( $name ) . '" class="' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

				break;
			case 'checkbox':
				$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . '><input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $args[ 'checkbox_value' ] ) . '" ' . checked( $value, $args[ 'checkbox_value' ], false ) . implode( ' ', $custom_attributes ) . ' /> ' . $args['checkbox_label'] . $required . '</label>';

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
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

				break;
			case 'hidden':
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-hidden ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

				break;
			case 'select':
				$field   = '';
				$options = '';

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder.
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : esc_html__( 'Choose an option', 'affiliates-for-woocommerce' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}

						if ( is_array( $value ) ) {
							$options .= '<option value="' . esc_attr( $option_key ) . '" ' . ( in_array( $option_key, $value, true ) ? 'selected="selected"' : '' ) . '>' . esc_html( $option_text ) . '</option>';
						} else {
							$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_text ) . '</option>';
						}
					}

					$field .= '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'radio':
				$label_id .= '_' . current( array_keys( $args['options'] ) );

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<input type="radio" class="' . esc_attr( $default_input_class ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $name ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . esc_html( $option_text ) . '</label>';
					}
				}

				break;
		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] ) {
				if ( $is_admin ) {
					$field_html .= '<th><label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . wp_kses_post( $args['label'] ) . $required . '</label></th>';
				} else {
					$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . wp_kses_post( $args['label'] ) . $required . '</label>';
				}
			}

			if ( $is_admin ) {
				$field_html .= '<td>';
			}

			if ( $args['help_tip'] ) {
				$field_html .= wc_help_tip( wp_kses_post( $args['help_tip'] ) ) . ' ';
			}

			$field_html .= $field;

			if ( $args['description'] ) {
				if ( $is_admin ) {
					$field_html .= '<p class="description '. ( $args[ 'no_description_margin' ] ? '' : 'ddwcaf-margin-left-20' ) .'" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true"><i>' . wp_kses_post( $args['description'] ) . '</i></p>';
				} else {
					$field_html .= '<i class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</i>';
				}
			}

			if ( $is_admin ) {
				$field_html .= '</td>';
			}

			$field = sprintf( $field_container, $field_html );
		}

		/**
		 * Filter by type.
		 */
		$field = apply_filters( 'ddwcaf_form_field_' . $args['type'], $field, $name, $args, $value );

		/**
		 * General filter on form fields.
		 */
		$field = apply_filters( 'ddwcaf_form_field', $field, $name, $args, $value );

		if ( $args['return'] ) {
			return $field;
		} else {
			echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
