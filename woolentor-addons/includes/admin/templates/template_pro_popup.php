<?php
/**
 * Template Pro Popup
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<div class="woolentor-pro-modal" style="display: none;">
    <div class="woolentor-pro-modal-overlay"></div>
    <div class="woolentor-pro-modal-content">
        <div class="pro-icon">
            <span class="dashicons dashicons-lock"></span>
        </div>
        <h3><?php echo esc_html__('Pro Feature', 'woolentor'); ?></h3>
        <p><?php echo esc_html__('This feature is only available in the Pro version. Upgrade to Pro to unlock all premium features and templates.', 'woolentor'); ?></p>
        <div class="button-group">
            <a href="<?php echo esc_url('https://woolentor.com/pricing/?utm_source=admin&utm_medium=templatebuilder&utm_campaign=free'); ?>" target="_blank" class="woolentor-pro-btn woolentor-pro-upgrade-btn">
                <span class="dashicons dashicons-cart"></span>
                <?php echo esc_html__('Upgrade Now', 'woolentor'); ?>
            </a>
        </div>
        <button type="button" class="woolentor-pro-modal-dismiss">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
    </div>
</div>
