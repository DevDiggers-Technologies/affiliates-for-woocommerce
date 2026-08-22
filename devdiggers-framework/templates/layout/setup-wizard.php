<?php
/**
 * Setup Wizard Layout Template
 */

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template scope variables are local include variables.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only wizard step routing parameter.
$current_step = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : array_key_first( $steps );
$step_keys    = array_keys( $steps );
$step_index   = 1;
$total_steps  = count( $steps );
$current_num  = array_search( $current_step, $step_keys, true ) + 1;
$progress     = ( $current_num / $total_steps ) * 100;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page routing parameter.
$dashboard_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
?>

<div class="ddfw-setup-wizard-wrap devdiggers-wrap">
    <div class="ddfw-setup-wizard">
        <div class="ddfw-setup-wizard-top">
            <div class="ddfw-setup-wizard-header-identity">
                <div class="ddfw-setup-wizard-header-logo">
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.5 16.5C3.5 17.5 2 22 2 22C2 22 6.5 20.5 7.5 19.5C8.5 18.5 8.5 17 8.5 17L4.5 13L4.5 16.5Z" fill="#0256ff" fill-opacity="0.2"/>
                        <path d="M13.5 10.5L18.5 5.5C19.5 4.5 21 4 22 4C23 4 24 5 24 6C24 7 23.5 8.5 22.5 9.5L17.5 14.5L13.5 10.5Z" fill="#0256ff"/>
                        <path d="M13.5 10.5L10.5 13.5L4.5 13L10.5 8.5L13.5 10.5Z" fill="#0256ff" fill-opacity="0.6"/>
                        <path d="M16 19L14.5 21L12.5 19L14.5 17L16 19Z" fill="#0256ff" fill-opacity="0.4"/>
                        <path d="M19 16L17.5 18L15.5 16L17.5 14L19 16Z" fill="#0256ff" fill-opacity="0.4"/>
                        <circle cx="19" cy="7" r="1.5" fill="white"/>
                    </svg>
                </div>
                <span class="ddfw-setup-wizard-header-title"><?php esc_html_e( 'Onboarding', 'affiliates-for-woocommerce' ); ?></span>
            </div>
            <div class="ddfw-setup-wizard-step-counter">
                <?php
                printf(
                    /* translators: 1: current step number, 2: total steps */
                    esc_html__( 'Step %1$s of %2$s', 'affiliates-for-woocommerce' ),
                    '<span class="ddfw-current-step-number">' . esc_html( $current_num ) . '</span>',
                    esc_html( $total_steps )
                );
                ?>
            </div>
        </div>

        <div class="ddfw-setup-wizard-progress">
            <div class="ddfw-setup-wizard-progress-bar" style="width: <?php echo esc_attr( $progress ); ?>%"></div>
        </div>

        <div class="ddfw-setup-wizard-content">
            <div class="ddfw-template-container">
                <div class="ddfw-template-wrapper ddfw-configuration-wrapper">
                    <form id="ddfw-setup-wizard-form" method="post">
                        <?php foreach ( $steps as $id => $step ) : ?>
                            <div class="ddfw-setup-wizard-step-content <?php echo $current_step === $id ? '' : 'ddfw-hide'; ?>" data-step="<?php echo esc_attr( $id ); ?>">
                                <?php if ( 'license' === $id ) : ?>
                                    <hr class="wp-header-end" />
                                <?php endif; ?>

                                <?php if ( ! empty( $step['title'] ) || ! empty( $step['description'] ) ) : ?>
                                    <div class="ddfw-setup-wizard-step-intro">
                                        <?php if ( ! empty( $step['title'] ) ) : ?>
                                            <h2 class="ddfw-setup-wizard-step-title"><?php echo esc_html( $step['title'] ); ?></h2>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $step['description'] ) ) : ?>
                                            <p class="ddfw-setup-wizard-step-description"><?php echo esc_html( $step['description'] ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="ddfw-setup-wizard-step-fields">
                                    <?php
                                    if ( isset( $step['view_callback'] ) && is_callable( $step['view_callback'] ) ) {
                                        call_user_func( $step['view_callback'] );
                                    } elseif ( $id === 'ready' ) {
                                        $this->ready_view( $step );
                                    }
                                    ?>
                                </div>

                                <div class="ddfw-setup-wizard-actions">
                                    <div class="ddfw-setup-wizard-actions-left">
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . $dashboard_page . '&setup-wizard-skipped=true' ), 'ddfw_skip_setup_wizard_' . $plugin_slug ) ); ?>" class="ddfw-setup-wizard-skip"><?php esc_html_e( 'Skip Setup', 'affiliates-for-woocommerce' ); ?></a>
                                    </div>
                                    <div class="ddfw-setup-wizard-actions-right">
                                        <?php if ( array_key_first( $steps ) !== $id ) : ?>
                                            <button type="button" class="ddfw-setup-wizard-prev ddfw-setup-wizard-go-back" data-prev="<?php
                                                $current_pos = array_search( $id, $step_keys );
                                                echo esc_attr( $step_keys[ $current_pos - 1 ] );
                                            ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M10 9V5L3 12L10 19V14.9C15 14.9 18.5 16.5 21 20C20 15 17 10 10 9Z"/>
                                                </svg>
                                                <?php esc_html_e( 'Go Back', 'affiliates-for-woocommerce' ); ?>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ( array_key_last( $steps ) === $id ) : ?>
                                            <button type="submit" class="button button-primary ddfw-setup-wizard-finish"><?php esc_html_e( 'Finish', 'affiliates-for-woocommerce' ); ?></button>
                                        <?php else : ?>
                                            <button type="submit" class="button button-primary ddfw-setup-wizard-next" data-next="<?php
                                                $current_pos = array_search( $id, $step_keys );
                                                echo esc_attr( $step_keys[ $current_pos + 1 ] );
                                            ?>"><?php esc_html_e( 'Continue', 'affiliates-for-woocommerce' ); ?></button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="tmpl-ddfw-wizard-loader">
    <div class="ddfw-wizard-loader-overlay">
        <div class="ddfw-wizard-loader"></div>
    </div>
</script>
