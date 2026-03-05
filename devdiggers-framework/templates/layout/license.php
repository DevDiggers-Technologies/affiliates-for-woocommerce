<?php
/**
 * License layout template for the DevDiggers plugins.
 *
 * @author  DevDiggers
 * @category Framework
 * @package DevDiggers\Framework
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

extract( $args, EXTR_SKIP );

settings_errors();
?>
<hr class="wp-header-end" />
<form action="options.php" method="POST">
	<div class="ddfw-fields-section">
		<div class="ddfw-license-input-header">
			<div class="ddfw-license-input-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"></rect>
					<circle cx="12" cy="16" r="1" fill="currentColor"></circle>
					<path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
			</div>
			<div class="ddfw-license-input-title">
				<h3><?php esc_html_e( 'License Activation', 'devdiggers-framework' ); ?></h3>
				<p><?php echo esc_html__( "Enter your license key and email to activate your plugin. You'll not be able to use the plugin until you activate the license.", 'devdiggers-framework' ); ?></p>
			</div>
		</div>
		<div class="ddfw-license-input-section <?php echo esc_attr( ! empty( $license_activated ) ? 'ddfw-hide' : '' ); ?>">
			<div class="ddfw-license-input-fields">
				<div class="ddfw-license-field-group">
					<label for="ddfw-purchase-code" class="ddfw-license-field-label">
						<?php esc_html_e( 'License Key', 'devdiggers-framework' ); ?>
						<abbr title="<?php esc_html_e( 'Required', 'devdiggers-framework' ); ?>" class="required">*</abbr>
					</label>
					<div class="ddfw-license-input-wrapper">
						<input type="text" 
							name="_<?php echo esc_attr( $prefix ); ?>_purchase_code" 
							class="regular-text ddfw-license-input" 
							id="ddfw-purchase-code" 
							value="<?php echo esc_attr( $purchase_code ); ?>" 
							placeholder="<?php esc_attr_e( 'Enter your license key', 'devdiggers-framework' ); ?>" 
							required />
						<div class="ddfw-license-input-icon-right">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M15 7a2 2 0 0 1 2 2m4 0a6 6 0 0 1-7.743 5.743L11 17H9v2H7v2H4a1 1 0 0 1-1-1v-2.586a1 1 0 0 1 .293-.707l5.964-5.964A6 6 0 1 1 21 9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
						</div>
					</div>
					<p class="ddfw-license-field-description">
						<i>
							<?php esc_html_e( 'Enter the license key you received after purchase.', 'devdiggers-framework' ); ?>
							<a href="<?php echo esc_url( '//devdiggers.com/license-activation/' ); ?>" target="__blank"><?php esc_html_e( 'How to find your license key?', 'devdiggers-framework' ); ?></a>
						</i>
					</p>
				</div>

				<div class="ddfw-license-field-group">
					<label for="ddfw-purchase-email" class="ddfw-license-field-label">
						<?php esc_html_e( 'Purchase Email', 'devdiggers-framework' ); ?>
						<abbr title="<?php esc_html_e( 'Required', 'devdiggers-framework' ); ?>" class="required">*</abbr>
					</label>
					<div class="ddfw-license-input-wrapper">
						<input type="email" 
							name="_<?php echo esc_attr( $prefix ); ?>_purchase_email" 
							class="regular-text ddfw-license-input" 
							id="ddfw-purchase-email" 
							value="<?php echo esc_attr( $purchase_email ); ?>" 
							placeholder="<?php esc_attr_e( 'Enter your purchase email', 'devdiggers-framework' ); ?>" 
							required />
						<div class="ddfw-license-input-icon-right">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" fill="none"></path>
								<polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" fill="none"></polyline>
							</svg>
						</div>
					</div>
					<p class="ddfw-license-field-description">
						<i>
							<?php esc_html_e( 'Enter the email address used for the purchase.', 'devdiggers-framework' ); ?>
						</i>
					</p>
				</div>
			</div>

			<div class="ddfw-license-input-actions">
				<button class="button button-primary ddfw-verify-license" data-action="activate" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-fetching="<?php esc_attr_e( 'Activating...', 'devdiggers-framework' ); ?>">
					<?php
					DDFW_SVG::get_svg_icon(
						'circle-check',
						false,
						[ 'size' => 15 ]
					);
					?>
					<?php esc_html_e( 'Activate License', 'devdiggers-framework' ); ?>
				</button>
			</div>
		</div>
		<div class="notice ddfw-hide">
			<p class="ddfw-license-status"></p>
		</div>

		<div class="ddfw-license-status-card ddfw-license-active <?php echo esc_attr( ! empty( $license_activated ) ? '' : 'ddfw-hide' ); ?>">
			<div class="ddfw-license-status-icon">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="12" cy="12" r="10" fill="#10b981" stroke="#10b981" stroke-width="2"></circle>
					<path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
			</div>
			<div class="ddfw-license-status-content">
				<h3><?php esc_html_e( 'License Active', 'devdiggers-framework' ); ?></h3>
				<p><?php esc_html_e( 'Your license is active and you can enjoy all premium features.', 'devdiggers-framework' ); ?></p>
			</div>
			<div class="ddfw-license-status-actions">
				<button class="button button-red ddfw-verify-license" data-action="deactivate" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-fetching="<?php esc_attr_e( 'Deactivating...', 'devdiggers-framework' ); ?>">
					<?php
					DDFW_SVG::get_svg_icon(
						'circle-x',
						false,
						[ 'size' => 15 ]
					);
					?>
					<?php esc_html_e( 'Deactivate License', 'devdiggers-framework' ); ?>
				</button>
			</div>
		</div>
	</div>
</form>
