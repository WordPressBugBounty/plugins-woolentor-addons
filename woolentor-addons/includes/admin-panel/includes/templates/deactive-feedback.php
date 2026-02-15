<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $ajaxurl = admin_url('admin-ajax.php');
    $nonce = wp_create_nonce('woolentor_deactivation_nonce');
?>

<div id="woolentor-deactivation-dialog" class="wl-deactivate-overlay" style="display: none;">
    <div class="wl-deactivate-modal">
        <!-- Header -->
        <div class="wl-deactivate-header">
            <button type="button" class="wl-close-btn woolentor-close-dialog" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="wl-header-content">
                <div class="wl-header-icon">
                    <img src="<?php echo esc_url( WOOLENTOROPT_ASSETS . '/images/logo.png' ); ?>" alt="<?php esc_attr_e('ShopLentor Logo', 'woolentor-addons'); ?>">
                </div>
                <div class="wl-header-text">
                    <h3><?php esc_html_e("We're Sorry to See You Go!", 'woolentor-addons') ?></h3>
                    <p><?php esc_html_e('Your feedback helps us improve ShopLentor for everyone.', 'woolentor-addons') ?></p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="wl-deactivate-body">
            <p class="wl-body-title"><?php esc_html_e('Please share why you\'re deactivating ShopLentor:', 'woolentor-addons') ?></p>

            <form id="woolentor-deactivation-feedback-form">
                <div class="wl-reasons-list">
                    <!-- Reason 1: Temporary -->
                    <div class="wl-reason-item">
                        <input type="radio" name="reason" id="reason_temporary" data-id="" value="<?php esc_attr_e("It's a temporary deactivation", 'woolentor-addons') ?>">
                        <label for="reason_temporary" class="wl-reason-label">
                            <span class="wl-reason-radio"></span>
                            <span class="wl-reason-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </span>
                            <span class="wl-reason-text">
                                <span><?php esc_html_e("It's a temporary deactivation", 'woolentor-addons') ?></span>
                            </span>
                        </label>
                    </div>

                    <!-- Reason 2: No longer need -->
                    <div class="wl-reason-item">
                        <input type="radio" name="reason" id="reason_no_need" data-id="" value="<?php esc_attr_e('I no longer need the plugin', 'woolentor-addons') ?>">
                        <label for="reason_no_need" class="wl-reason-label">
                            <span class="wl-reason-radio"></span>
                            <span class="wl-reason-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M18 6L6 18M6 6l12 12"/>
                                </svg>
                            </span>
                            <span class="wl-reason-text">
                                <span><?php esc_html_e('I no longer need the plugin', 'woolentor-addons') ?></span>
                            </span>
                        </label>
                    </div>

                    <!-- Reason 3: Found better -->
                    <div class="wl-reason-item">
                        <input type="radio" name="reason" id="reason_better" data-id="found_better" value="<?php esc_attr_e('I found a better plugin', 'woolentor-addons') ?>">
                        <label for="reason_better" class="wl-reason-label">
                            <span class="wl-reason-radio"></span>
                            <span class="wl-reason-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                            </span>
                            <span class="wl-reason-text">
                                <span><?php esc_html_e('I found a better plugin', 'woolentor-addons') ?></span>
                            </span>
                        </label>
                    </div>
                    <div id="woolentor-found_better-reason-text" class="wl-additional-input woolentor-deactivation-reason-input">
                        <textarea name="found_better_reason" placeholder="<?php esc_attr_e('Which plugin are you switching to? We\'d love to know...', 'woolentor-addons') ?>"></textarea>
                    </div>

                    <!-- Reason 4: Not working -->
                    <div class="wl-reason-item">
                        <input type="radio" name="reason" id="reason_not_working" data-id="stopped_working" value="<?php esc_attr_e('The plugin suddenly stopped working', 'woolentor-addons') ?>">
                        <label for="reason_not_working" class="wl-reason-label">
                            <span class="wl-reason-radio"></span>
                            <span class="wl-reason-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                            </span>
                            <span class="wl-reason-text">
                                <span><?php esc_html_e('The plugin suddenly stopped working', 'woolentor-addons') ?></span>
                            </span>
                        </label>
                    </div>
                    <div id="woolentor-stopped_working-reason-text" class="wl-additional-input woolentor-deactivation-reason-input">
                        <textarea name="stopped_working_reason" placeholder="<?php esc_attr_e('Please describe the issue you\'re experiencing...', 'woolentor-addons') ?>"></textarea>
                    </div>

                    <!-- Reason 5: Bug -->
                    <div class="wl-reason-item">
                        <input type="radio" name="reason" id="reason_bug" data-id="found_bug" value="<?php esc_attr_e('I encountered an error or bug', 'woolentor-addons') ?>">
                        <label for="reason_bug" class="wl-reason-label">
                            <span class="wl-reason-radio"></span>
                            <span class="wl-reason-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                                </svg>
                            </span>
                            <span class="wl-reason-text">
                                <span><?php esc_html_e('I encountered an error or bug', 'woolentor-addons') ?></span>
                            </span>
                        </label>
                    </div>
                    <div id="woolentor-found_bug-reason-text" class="wl-additional-input woolentor-deactivation-reason-input">
                        <textarea name="found_bug_reason" placeholder="<?php esc_attr_e('Please describe the error/bug. This will help us fix it...', 'woolentor-addons') ?>"></textarea>
                    </div>

                    <!-- Reason 6: Other -->
                    <div class="wl-reason-item">
                        <input type="radio" name="reason" id="reason_other" data-id="other" value="<?php esc_attr_e('Other', 'woolentor-addons') ?>">
                        <label for="reason_other" class="wl-reason-label">
                            <span class="wl-reason-radio"></span>
                            <span class="wl-reason-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                                </svg>
                            </span>
                            <span class="wl-reason-text">
                                <span><?php esc_html_e('Other', 'woolentor-addons') ?></span>
                            </span>
                        </label>
                    </div>
                    <div id="woolentor-other-reason-text" class="wl-additional-input woolentor-deactivation-reason-input">
                        <textarea name="other_reason" placeholder="<?php esc_attr_e('Please share the reason...', 'woolentor-addons') ?>"></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="wl-deactivate-footer">
                    <a href="#" class="wl-btn wl-btn-skip woolentor-skip-feedback"><?php esc_html_e('Skip & Deactivate', 'woolentor-addons') ?></a>
                    <button type="submit" class="wl-btn wl-btn-submit">
                        <span><?php esc_html_e('Submit & Deactivate', 'woolentor-addons') ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    ;jQuery(document).ready(function($) {
        let pluginToDeactivate = '';

        function closeDialog() {
            $('#woolentor-deactivation-dialog').animate({
                opacity: 0
            }, 'slow', function() {
                $(this).css('display', 'none');
            });
            pluginToDeactivate = '';
        }

        // Open dialog when deactivate is clicked
        $('[data-slug="<?php echo esc_attr($this->PROJECT_SLUG); ?>"] .deactivate a').on('click', function(e) {
            e.preventDefault();
            pluginToDeactivate = $(this).attr('href');
            $('#woolentor-deactivation-dialog').css({'display': 'flex', 'opacity': '1'});
        });

        // Close dialog on X button click
        $('.woolentor-close-dialog').on('click', closeDialog);

        // Close dialog on overlay click
        $('#woolentor-deactivation-dialog').on('click', function(e) {
            if (e.target === this) {
                closeDialog();
            }
        });

        // Prevent close when clicking modal content
        $('.wl-deactivate-modal').on('click', function(e) {
            e.stopPropagation();
        });

        // Handle radio button change - show/hide textarea
        $('input[name="reason"]').on('change', function() {
            $('.woolentor-deactivation-reason-input').removeClass('active').hide();

            const id = $(this).data('id');
            if (['other', 'found_better', 'stopped_working', 'found_bug'].includes(id)) {
                $(`#woolentor-${id}-reason-text`).addClass('active').show();
                $(`#woolentor-${id}-reason-text textarea`).focus();
            }
        });

        // Handle form submission
        $('#woolentor-deactivation-feedback-form').on('submit', function(e) {
            e.preventDefault();

            const $submitButton = $(this).find('button[type="submit"]');
            const $buttonText = $submitButton.find('span');
            const originalText = $buttonText.text();

            $buttonText.text('<?php esc_html_e('Submitting...', 'woolentor-addons') ?>');
            $submitButton.prop('disabled', true);

            const reason = $('input[name="reason"]:checked').val() || 'No reason selected';
            const message = $('.woolentor-deactivation-reason-input.active textarea').val() || '';

            const data = {
                action: 'woolentor_deactivation_feedback',
                reason: reason,
                message: message,
                nonce: '<?php echo esc_js($nonce); ?>'
            };

            $.post('<?php echo esc_url_raw($ajaxurl); ?>', data)
                .done(function(response) {
                    if (response.success) {
                        window.location.href = pluginToDeactivate;
                    } else {
                        console.error('Feedback submission failed:', response.data);
                        $buttonText.text(originalText);
                        $submitButton.prop('disabled', false);
                    }
                })
                .fail(function(xhr) {
                    console.error('Feedback submission failed:', xhr.responseText);
                    $buttonText.text(originalText);
                    $submitButton.prop('disabled', false);
                });
        });

        // Skip feedback and deactivate
        $('.woolentor-skip-feedback').on('click', function(e) {
            e.preventDefault();
            window.location.href = pluginToDeactivate;
        });
    });
</script>

<style>
    /* Overlay */
    #woolentor-deactivation-dialog.wl-deactivate-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        animation: wlFadeIn 0.3s ease;
    }

    @keyframes wlFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes wlFadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }

    @keyframes wlSlideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes wlSlideDown {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        to {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
    }

    /* Closing animation classes */
    #woolentor-deactivation-dialog.wl-deactivate-overlay.wl-closing {
        animation: wlFadeOut 0.25s ease forwards;
    }

    #woolentor-deactivation-dialog.wl-closing .wl-deactivate-modal {
        animation: wlSlideDown 0.25s ease forwards;
    }

    /* Modal Container */
    .wl-deactivate-modal {
        background: #ffffff;
        border-radius: 8px;
        width: 480px;
        max-width: 95%;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: wlSlideUp 0.4s ease;
    }

    /* Header */
    .wl-deactivate-header {
        background: linear-gradient(135deg, #E8573F 0%, #F05C4E 50%, #F26A52 100%);
        padding: 20px 28px;
        position: relative;
        overflow: hidden;
    }

    .wl-deactivate-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .wl-deactivate-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 10%;
        width: 120px;
        height: 120px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }

    .wl-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .wl-header-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wl-header-icon img{
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .wl-header-text h3 {
        color: #ffffff;
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 2px 0;
    }

    .wl-header-text p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 12px;
        font-weight: 400;
        margin: 0;
    }

    /* Close Button */
    .wl-close-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 28px;
        height: 28px;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        z-index: 2;
        padding: 0;
    }

    .wl-close-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg);
    }

    .wl-close-btn svg {
        width: 16px;
        height: 16px;
        stroke: #ffffff;
        stroke-width: 2;
    }

    /* Body Content */
    .wl-body-title {
        font-size: 14px;
        color: #374151;
        font-weight: 500;
        margin: 20px 0 0 0;
        padding: 0 25px;
    }

    /* Reason Options */
    .wl-reasons-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 20px 25px;
    }

    .wl-reason-item {
        position: relative;
    }

    .wl-reason-item input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .wl-reason-label {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .wl-reason-label:hover {
        border-color: #F05C4E;
        background: #FEF5F4;
    }

    .wl-reason-item input[type="radio"]:checked + .wl-reason-label {
        border-color: #F05C4E;
        background: #FEF5F4;
        box-shadow: 0 2px 8px rgba(240, 92, 78, 0.15);
    }

    .wl-reason-radio {
        width: 14px;
        height: 14px;
        min-width: 14px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
    }

    .wl-reason-item input[type="radio"]:checked + .wl-reason-label .wl-reason-radio {
        border-color: #F05C4E;
        background: #F05C4E;
    }

    .wl-reason-radio::after {
        content: '';
        width: 6px;
        height: 6px;
        background: #ffffff;
        border-radius: 50%;
        transform: scale(0);
        transition: transform 0.2s ease;
    }

    .wl-reason-item input[type="radio"]:checked + .wl-reason-label .wl-reason-radio::after {
        transform: scale(1);
    }

    .wl-reason-icon {
        width: 30px;
        height: 30px;
        min-width: 30px;
        background: #FDEAE8;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s ease;
    }

    .wl-reason-item input[type="radio"]:checked + .wl-reason-label .wl-reason-icon {
        background: #F05C4E;
    }

    .wl-reason-icon svg {
        width: 15px;
        height: 15px;
        stroke: #F05C4E;
        stroke-width: 2;
        fill: none;
        transition: all 0.25s ease;
    }

    .wl-reason-item input[type="radio"]:checked + .wl-reason-label .wl-reason-icon svg {
        stroke: #ffffff;
    }

    .wl-reason-text {
        flex: 1;
    }

    .wl-reason-text span {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        display: block;
        line-height: 1.3;
    }

    /* Additional Input Field */
    .wl-additional-input.woolentor-deactivation-reason-input {
        margin-top: 6px;
        margin-bottom: 6px;
        display: none;
        animation: wlSlideDown 0.3s ease;
    }

    .wl-additional-input.woolentor-deactivation-reason-input.active {
        display: block;
    }

    @keyframes wlSlideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .wl-additional-input textarea {
        width: 100%;
        min-height: 70px;
        padding: 12px 14px;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        resize: vertical;
        transition: all 0.25s ease;
        background: #ffffff;
        box-sizing: border-box;
    }

    .wl-additional-input textarea:focus {
        outline: none;
        border-color: #F05C4E;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(240, 92, 78, 0.1);
    }

    .wl-additional-input textarea::placeholder {
        color: #94a3b8;
    }

    /* Footer */
    .wl-deactivate-footer {
        padding: 16px 28px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-top: 1px solid #e5e5e5;
        margin-top: 10px;
    }

    .wl-btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .wl-btn-skip {
        background: transparent;
        color: #6B7280;
        padding: 10px 0;
    }

    .wl-btn-skip:hover {
        color: #F05C4E;
    }

    .wl-btn-submit {
        background: #F05C4E;
        border: 1px solid #F05C4E;
        color: #ffffff;
    }

    .wl-btn-submit:hover {
        background: #E8573F;
        border-color: #E8573F;
        color: #ffffff;
    }

    .wl-btn-submit:active {
        background: #D94D35;
    }

    .wl-btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .wl-btn-submit svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .wl-deactivate-modal {
            margin: 16px;
        }

        .wl-deactivate-header {
            padding: 16px 20px;
        }

        .wl-deactivate-body {
            padding: 16px 20px;
        }

        .wl-deactivate-footer {
            padding: 14px 20px 18px;
            flex-direction: column;
        }

        .wl-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
    