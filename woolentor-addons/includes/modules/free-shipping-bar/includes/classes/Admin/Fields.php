<?php
namespace Woolentor\Modules\FreeShippingBar\Admin;
use WooLentor\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

class Fields{
    use Singleton;

    private function __construct(){
        add_filter('woolentor_admin_fields_vue', [$this, 'admin_fields'], 99, 1);
    }

    /**
     * Admin Field Register
     *
     * @param  mixed $fields
     * @return mixed
     */
    public function admin_fields($fields){
        if (woolentor_is_pro() && method_exists('\WoolentorPro\Modules\FreeShippingBar\Admin\Fields', 'setting_fields')) {
            array_splice($fields['woolentor_others_tabs'], 20, 0, \WoolentorPro\Modules\FreeShippingBar\Admin\Fields::instance()->setting_fields());
        } else {
            array_splice($fields['woolentor_others_tabs'], 20, 0, $this->setting_fields());
        }

        $fields['woolentor_elements_tabs'][] = [
            'id'    => 'wl_free_shipping_bar',
            'name'   => esc_html__( 'Free Shipping Bar', 'woolentor' ),
            'type'    => 'element',
            'default' => 'on',
            'badge'   => [
                'is_active' => true,
                'type'      => 'new',
                'label'     => esc_html__('New','woolentor')
            ]
        ];

        // Block
        $fields['woolentor_gutenberg_tabs'][] = [
            'id'  => 'free_shipping_bar',
            'name' => esc_html__( 'Free Shipping Bar', 'woolentor' ),
            'type'  => 'element',
            'default' => 'on',
            'badge'   => [
                'is_active' => true,
                'type'      => 'new',
                'label'     => esc_html__('New','woolentor')
            ]
        ];
        
        return $fields;
    }

    /**
     * Settings fields for the Free Shipping Bar module.
     *
     * @return array
     */
    public function setting_fields(){
        $fields = [
            [
                'id'               => 'woolentor_free_shipping_bar_settings',
                'name'             => esc_html__('Free Shipping Bar', 'woolentor'),
                'type'             => 'moduledrawer',
                'default'          => 'off',
                'section'          => 'woolentor_free_shipping_bar_settings',
                'documentation'    => esc_url('https://woolentor.com/doc/how-to-set-up-the-free-shipping-bar-in-shoplentor-for-woocommerce/'),
                'option_id'        => 'enable',
                'require_settings' => true,
                'setting_tabs'     => [

                    // ── Tab 1: General ────────────────────────────────────────
                    [
                        'id'     => 'general',
                        'name'   => esc_html__('General', 'woolentor'),
                        'fields' => [

                            [
                                'id'      => 'enable',
                                'name'    => esc_html__('Enable / Disable', 'woolentor'),
                                'desc'    => esc_html__('Enable or disable the Free Shipping Bar.', 'woolentor'),
                                'type'    => 'checkbox',
                                'default' => 'off',
                                'class'   => 'woolentor-action-field-left',
                            ],

                            [
                                'id'      => 'display_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Display Settings', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'bar_position',
                                'name'    => esc_html__('Bar Position', 'woolentor'),
                                'desc'    => esc_html__('Choose where the bar appears on the page.', 'woolentor'),
                                'type'    => 'select',
                                'default' => 'top',
                                'options' => [
                                    'top'    => esc_html__('Top', 'woolentor'),
                                    'bottom' => esc_html__('Bottom', 'woolentor'),
                                ],
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'          => 'display_pages',
                                'name'        => esc_html__('Display On Pages', 'woolentor'),
                                'type'        => 'ajaxselect',
                                'post_type'   => 'page',
                                'multiple'    => true,
                                'placeholder' => esc_html__('Search pages...', 'woolentor'),
                                'desc'        => esc_html__('Select the pages where the bar should appear.', 'woolentor'),
                                'class'       => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'show_on_device',
                                'name'    => esc_html__('Show On Device', 'woolentor'),
                                'desc'    => esc_html__('Choose which devices should display the bar.', 'woolentor'),
                                'type'    => 'select',
                                'default' => 'all',
                                'options' => [
                                    'all'     => esc_html__('All Devices', 'woolentor'),
                                    'desktop' => esc_html__('Desktop Only', 'woolentor'),
                                    'mobile'  => esc_html__('Mobile Only', 'woolentor'),
                                ],
                                'class'   => 'woolentor-action-field-left',
                            ],

                            [
                                'id'      => 'threshold_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Shipping Threshold', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'manual_threshold',
                                'name'    => esc_html__('Manual Threshold Amount', 'woolentor'),
                                'desc'    => esc_html__('Override the free-shipping minimum amount detected from your WooCommerce shipping zones. Leave at 0 to auto-detect.', 'woolentor'),
                                'type'    => 'number',
                                'default' => '0',
                                'min'     => '0',
                                'step'    => '0.01',
                                'class'   => 'woolentor-action-field-left',
                            ],

                            [
                                'id'      => 'shipping_mode_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Shipping Application', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'shipping_mode',
                                'name'    => esc_html__('Shipping Mode', 'woolentor'),
                                'desc'    => esc_html__('WooCommerce: rely on your WC shipping zone configuration to apply free shipping. Module: the bar automatically applies free shipping when the threshold is reached — no WooCommerce free shipping method required. Requires Manual Threshold Amount to be set.', 'woolentor'),
                                'type'    => 'select',
                                'default' => 'module',
                                'options' => [
                                    'woocommerce' => esc_html__('WooCommerce (use WC shipping methods)', 'woolentor'),
                                    'module'      => esc_html__('Module (apply automatically)', 'woolentor'),
                                ],
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'        => 'module_hide_other_rates',
                                'name'      => esc_html__('Hide Other Shipping Methods', 'woolentor'),
                                'desc'      => esc_html__('When free shipping is applied by the module, hide all other shipping methods (flat rate, etc.).', 'woolentor'),
                                'type'      => 'checkbox',
                                'default'   => 'on',
                                'class'     => 'woolentor-action-field-left',
                                'condition' => [
                                    'key'      => 'shipping_mode',
                                    'operator' => '==',
                                    'value'    => 'module',
                                ],
                            ],

                        ],
                    ],

                    // ── Tab 2: Messages ───────────────────────────────────────
                    [
                        'id'     => 'messages',
                        'name'   => esc_html__('Messages', 'woolentor'),
                        'fields' => [

                            [
                                'id'      => 'msg_initial',
                                'name'    => esc_html__('Progress Message', 'woolentor'),
                                'desc'    => esc_html__('Message shown while the customer has not yet reached the threshold. Use {amount} as a placeholder for the remaining amount.', 'woolentor'),
                                'type'    => 'text',
                                'default' => esc_html__('Spend {amount} more to get FREE shipping!', 'woolentor'),
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'msg_success',
                                'name'    => esc_html__('Success Message', 'woolentor'),
                                'desc'    => esc_html__('Message shown when the customer has unlocked free shipping.', 'woolentor'),
                                'type'    => 'text',
                                'default' => esc_html__("🎉 You have unlocked FREE shipping!", 'woolentor'),
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // A/B Testing (Pro preview)
                            [
                                'id'      => 'ab_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('A/B Testing (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'enable_ab_test',
                                'name'    => esc_html__('Enable A/B Testing', 'woolentor'),
                                'desc'    => esc_html__('Randomly show visitors one of two message variants and track which one drives more free-shipping conversions.', 'woolentor'),
                                'type'    => 'checkbox',
                                'default' => 'off',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'ab_variant_a_msg',
                                'name'    => esc_html__('Variant A — Progress Message', 'woolentor'),
                                'desc'    => esc_html__('Shown to ~50% of visitors. Use {amount} as a placeholder.', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'ab_variant_b_msg',
                                'name'    => esc_html__('Variant B — Progress Message', 'woolentor'),
                                'desc'    => esc_html__('Shown to the other ~50% of visitors. Use {amount} as a placeholder.', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                        ],
                    ],

                    // ── Tab 3: Appearance ─────────────────────────────────────
                    [
                        'id'     => 'appearance',
                        'name'   => esc_html__('Appearance', 'woolentor'),
                        'fields' => [

                            [
                                'id'      => 'bar_bg_color',
                                'name'    => esc_html__('Bar Background Color', 'woolentor'),
                                'desc'    => esc_html__('Background colour for the shipping bar.', 'woolentor'),
                                'type'    => 'color',
                                'default' => '',
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'bar_text_color',
                                'name'    => esc_html__('Text Color', 'woolentor'),
                                'desc'    => esc_html__('Colour for the message text inside the bar.', 'woolentor'),
                                'type'    => 'color',
                                'default' => '',
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'bar_fill_color',
                                'name'    => esc_html__('Progress Fill Color', 'woolentor'),
                                'desc'    => esc_html__('Colour of the progress bar fill.', 'woolentor'),
                                'type'    => 'color',
                                'default' => '',
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'bar_font_size',
                                'name'    => esc_html__('Font Size (px)', 'woolentor'),
                                'desc'    => esc_html__('Font size in pixels for the bar message. Leave empty to use the theme default.', 'woolentor'),
                                'type'    => 'number',
                                'default' => '',
                                'min'     => '10',
                                'max'     => '48',
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // Advanced Styles & Skins (Pro preview)
                            [
                                'id'      => 'styles_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Advanced Styles & Skins (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'bar_skin',
                                'name'    => esc_html__('Bar Skin', 'woolentor'),
                                'desc'    => esc_html__('Choose a pre-built visual skin: Minimal, Gradient, Announcement, or Bold.', 'woolentor'),
                                'type'    => 'select',
                                'default' => 'default',
                                'options' => ['default' => esc_html__('Default', 'woolentor')],
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'bar_animation',
                                'name'    => esc_html__('Progress Bar Animation', 'woolentor'),
                                'desc'    => esc_html__('Add a Slide, Pulse, or Glow animation to the progress fill.', 'woolentor'),
                                'type'    => 'select',
                                'default' => 'none',
                                'options' => ['none' => esc_html__('None', 'woolentor')],
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'bar_custom_css',
                                'name'    => esc_html__('Custom CSS', 'woolentor'),
                                'desc'    => esc_html__('Add custom CSS rules for the bar.', 'woolentor'),
                                'type'    => 'textarea',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // Countdown Timer (Pro preview)
                            [
                                'id'      => 'countdown_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Countdown Timer (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'enable_countdown',
                                'name'    => esc_html__('Enable Countdown Timer', 'woolentor'),
                                'desc'    => esc_html__('Show a live countdown clock inside the bar to create urgency around your free-shipping offer.', 'woolentor'),
                                'type'    => 'checkbox',
                                'default' => 'off',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'countdown_end_date',
                                'name'    => esc_html__('Countdown End Date & Time', 'woolentor'),
                                'desc'    => esc_html__('Format: YYYY-MM-DD HH:MM', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'countdown_message',
                                'name'    => esc_html__('Countdown Message', 'woolentor'),
                                'desc'    => esc_html__('Use {timer} as a placeholder for the clock (e.g. "Offer ends in {timer}!").', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                        ],
                    ],

                    // ── Tab 4: Targeting & Advanced ───────────────────────────
                    [
                        'id'     => 'targeting',
                        'name'   => esc_html__('Targeting', 'woolentor'),
                        'fields' => [

                            // Geo-Targeting (Pro preview)
                            [
                                'id'      => 'geo_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Geo-Targeting (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'enable_geo_targeting',
                                'name'    => esc_html__('Enable Geo-Targeting', 'woolentor'),
                                'desc'    => esc_html__('Automatically show the threshold that matches the customer\'s WooCommerce shipping zone — no manual configuration needed.', 'woolentor'),
                                'type'    => 'checkbox',
                                'default' => 'off',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'geo_fallback_threshold',
                                'name'    => esc_html__('Geo Fallback Threshold', 'woolentor'),
                                'desc'    => esc_html__('Threshold used when no matching shipping zone is found.', 'woolentor'),
                                'type'    => 'number',
                                'default' => '0',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // Advanced Display Targeting (Pro preview)
                            [
                                'id'      => 'adv_target_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Advanced Display Targeting (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'target_categories',
                                'name'    => esc_html__('Product Category Targeting', 'woolentor'),
                                'desc'    => esc_html__('Show the bar only on pages that belong to specific product categories.', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'target_user_role',
                                'name'    => esc_html__('User Role Targeting', 'woolentor'),
                                'desc'    => esc_html__('Restrict the bar to specific user roles — all visitors, guests only, logged-in users, or customers who have made a purchase.', 'woolentor'),
                                'type'    => 'select',
                                'default' => 'all',
                                'options' => ['all' => esc_html__('All Visitors', 'woolentor')],
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'target_include_pages',
                                'name'    => esc_html__('Include Specific Page IDs', 'woolentor'),
                                'desc'    => esc_html__('Show the bar only on these specific pages (comma-separated IDs).', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'target_exclude_pages',
                                'name'    => esc_html__('Exclude Specific Page IDs', 'woolentor'),
                                'desc'    => esc_html__('Hide the bar on these specific pages (comma-separated IDs).', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // Scheduled Display (Pro preview)
                            [
                                'id'      => 'schedule_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Scheduled Display (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'enable_schedule',
                                'name'    => esc_html__('Enable Schedule', 'woolentor'),
                                'desc'    => esc_html__('Show the bar only within a defined date/time window — perfect for weekend promotions or flash sales.', 'woolentor'),
                                'type'    => 'checkbox',
                                'default' => 'off',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'schedule_start',
                                'name'    => esc_html__('Start Date & Time', 'woolentor'),
                                'desc'    => esc_html__('Format: YYYY-MM-DD HH:MM', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],
                            [
                                'id'      => 'schedule_end',
                                'name'    => esc_html__('End Date & Time', 'woolentor'),
                                'desc'    => esc_html__('Format: YYYY-MM-DD HH:MM', 'woolentor'),
                                'type'    => 'text',
                                'default' => '',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // Performance Tracking (Pro preview)
                            [
                                'id'      => 'analytics_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Performance Tracking (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'      => 'enable_analytics',
                                'name'    => esc_html__('Enable Analytics', 'woolentor'),
                                'desc'    => esc_html__('Track bar impressions (views) and conversions (threshold reached). View per-bar stats directly in the admin panel.', 'woolentor'),
                                'type'    => 'checkbox',
                                'default' => 'off',
                                'is_pro'  => true,
                                'class'   => 'woolentor-action-field-left',
                            ],

                            // Additional Bars (Pro preview)
                            [
                                'id'      => 'multi_bar_heading',
                                'type'    => 'title',
                                'heading' => esc_html__('Additional Bars (Pro)', 'woolentor'),
                                'size'    => 'woolentor_style_seperator',
                            ],
                            [
                                'id'          => 'additional_bars',
                                'name'        => esc_html__('Additional Bars', 'woolentor'),
                                'desc'        => esc_html__('Create multiple independent bars each with its own threshold, targeting, schedule, and style. The highest-priority matching bar is shown.', 'woolentor'),
                                'type'        => 'repeater',
                                'title_field' => 'bar_title',
                                'max_items'   => '0',
                                'message'     => [
                                    'title'    => esc_html__('Upgrade to Premium Version', 'woolentor'),
                                    'desc'     => esc_html__('Multiple bars is a Pro feature. Upgrade to create additional bars with advanced targeting, scheduling, and custom styles.', 'woolentor'),
                                    'pro_link' => esc_url('https://woolentor.com/pricing/?utm_source=admin&utm_medium=lockfeatures&utm_campaign=free'),
                                ],
                                'options'     => [
                                    'button_label' => esc_html__('Add New Bar', 'woolentor'),
                                ],
                                'fields'      => [
                                    [
                                        'id'    => 'bar_title',
                                        'name'  => esc_html__('Bar Name', 'woolentor'),
                                        'type'  => 'text',
                                        'class' => 'woolentor-action-field-left',
                                    ],
                                ],
                            ],

                        ],
                    ],
                    [
                        'id'     => 'analytics',
                        'name'   => esc_html__('Analytics', 'woolentor'),
                        'fields' => [
                            [
                                'id'        => 'analytics_data_render',
                                'type'      => 'html',
                                'html'      => '',
                                'component' => 'FsbAnalytics',
                            ],
                        ],
                    ],

                ],
            ],
        ];

        return $fields;
    }
}
