<?php
/**
 * Layout Configuration template class
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Layout_Configuration_Template' ) ) {
	/**
	 * Layout Configuration template class
	 */
	class DDWCAF_Layout_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Icons Configuration', 'affiliates-for-woocommerce' ),
						'description' => esc_html__( 'This section allows you to enable and configure the basic settings for the icons displayed alongside statistics details on the dashboard layout.', 'affiliates-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Show Icons', 'affiliates-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable Icons for Statistics Details', 'affiliates-for-woocommerce' ),
							'description'    => esc_html__( 'Enable this option to display icons next to statistic details such as total earnings, paid amount, etc.', 'affiliates-for-woocommerce' ),
							'id'             => 'ddwcaf-details-icons-enabled',
							'value'          => $ddwcaf_configuration['details_icons_enabled'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Show Wrapper', 'affiliates-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable Wrapper for Details Icons', 'affiliates-for-woocommerce' ),
							'description'    => esc_html__( 'Enable this option to display a wrapper around the icons next to statistic details such as total earnings, paid amount, etc.', 'affiliates-for-woocommerce' ),
							'id'             => 'ddwcaf-details-icons-wrapper-enabled',
							'value'          => $ddwcaf_configuration['details_icons_wrapper_enabled'],
						],
						[
							'type'           => 'number',
							'label'          => esc_html__( 'Icon Size', 'affiliates-for-woocommerce' ),
							'description'    => esc_html__( 'Set the size for the icons displayed next to statistic details such as total earnings, paid amount, etc. (in pixels)', 'affiliates-for-woocommerce' ),
							'id'             => 'ddwcaf-details-icon-size',
							'value'          => $ddwcaf_configuration['details_icon_size'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Icon Images', 'affiliates-for-woocommerce' ),
						'description' => esc_html__( 'This section allows you to upload and customize the specific image icons displayed for each statistic on the dashboard layout.', 'affiliates-for-woocommerce' ),
					],
					'class'  => 'ddfw-upgrade-to-pro-tag-wrapper',
					'fields' => [
						[
							'type'              => 'image',
							'label'             => esc_html__( 'Icon for Total Earnings', 'affiliates-for-woocommerce' ),
							'description'       => esc_html__( 'Choose an icon (100x100px) to represent total earnings.', 'affiliates-for-woocommerce' ),
							'id'                => 'ddwcaf-details-icon-total-earnings',
							'custom_attributes' => [
								'disabled' => 'disabled',
							]
						],
						[
							'type'              => 'image',
							'label'             => esc_html__( 'Icon for Paid Amount', 'affiliates-for-woocommerce' ),
							'description'       => esc_html__( 'Choose an icon (100x100px) that represents the paid commission amount.', 'affiliates-for-woocommerce' ),
							'id'                => 'ddwcaf-details-icon-paid-amount',
							'custom_attributes' => [
								'disabled' => 'disabled',
							]
						],
						[
							'type'              => 'image',
							'label'             => esc_html__( 'Icon for Unpaid Amount', 'affiliates-for-woocommerce' ),
							'description'       => esc_html__( 'Choose an icon (100x100px) to visually represent unpaid commission amount.', 'affiliates-for-woocommerce' ),
							'id'                => 'ddwcaf-details-icon-unpaid-amount',
							'custom_attributes' => [
								'disabled' => 'disabled',
							]
						],
						[
							'type'              => 'image',
							'label'             => esc_html__( 'Icon for Visitors', 'affiliates-for-woocommerce' ),
							'description'       => esc_html__( 'Choose an icon (100x100px) to represent total visitors.', 'affiliates-for-woocommerce' ),
							'id'                => 'ddwcaf-details-icon-visitors',
							'custom_attributes' => [
								'disabled' => 'disabled',
							]
						],
						[
							'type'              => 'image',
							'label'             => esc_html__( 'Icon for Customers', 'affiliates-for-woocommerce' ),
							'description'       => esc_html__( 'Choose an icon (100x100px) to represent converted customers.', 'affiliates-for-woocommerce' ),
							'id'                => 'ddwcaf-details-icon-customers',
							'custom_attributes' => [
								'disabled' => 'disabled',
							]
						],
						[
							'type'              => 'image',
							'label'             => esc_html__( 'Icon for Conversion Rate', 'affiliates-for-woocommerce' ),
							'description'       => esc_html__( 'Choose an icon (100x100px) to represent the conversion rate percentage.', 'affiliates-for-woocommerce' ),
							'id'                => 'ddwcaf-details-icon-conversion-rate',
							'custom_attributes' => [
								'disabled' => 'disabled',
							]
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Color', 'affiliates-for-woocommerce' ),
						'description' => esc_html__( 'This section allows you to customize the colors displayed alongside statistic details on the Affiliate Dashboard layout. Add visual appeal by customizing the text or background colors for cards displayed to affiliates.', 'affiliates-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Brand Primary Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose the main brand color for your affiliate dashboard. This color will be applied to primary buttons, active states, and highlights throughout the dashboard.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-primary-color',
							'value'       => $ddwcaf_configuration['primary_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Details Icon Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a color for the icons displayed next to statistic details such as total earnings, paid amount, etc.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-details-icon-color',
							'value'       => $ddwcaf_configuration['details_icon_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Details Icon Wrapper Background Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a background color for the icon wrappers displayed next to statistic details.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-details-icon-wrapper-background-color',
							'value'       => $ddwcaf_configuration['details_icon_wrapper_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Background Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a background color for the statistic cards.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-details-card-background-color',
							'value'       => $ddwcaf_configuration['details_card_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Border Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a border color for the statistic cards.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-details-card-border-color',
							'value'       => $ddwcaf_configuration['details_card_border_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Text Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a text color for the statistic card titles.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-details-card-text-color',
							'value'       => $ddwcaf_configuration['details_card_text_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Value Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a value color for the statistic card amounts.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-details-card-value-color',
							'value'       => $ddwcaf_configuration['details_card_value_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Table Header Background Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a background color for the table headers.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-table-header-background-color',
							'value'       => $ddwcaf_configuration['table_header_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Table Header Text Color', 'affiliates-for-woocommerce' ),
							'description' => esc_html__( 'Choose a text color for the table headers.', 'affiliates-for-woocommerce' ),
							'id'          => 'ddwcaf-table-header-text-color',
							'value'       => $ddwcaf_configuration['table_header_text_color'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcaf-layout-configuration-fields' );
		}
	}
}
