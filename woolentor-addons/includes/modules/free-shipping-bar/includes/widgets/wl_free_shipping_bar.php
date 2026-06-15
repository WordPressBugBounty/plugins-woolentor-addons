<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Free Shipping Bar Elementor Widget
 */
class Woolentor_Wl_Free_Shipping_Bar_Widget extends Widget_Base {

    public function get_name() {
        return 'woolentor_free_shipping_bar';
    }

    public function get_title() {
        return __( 'WL: Free Shipping Bar', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-progress-tracker';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_help_url() {
        return 'https://woolentor.com/documentation/';
    }

    public function get_keywords() {
        return [ 'free shipping', 'shipping bar', 'progress bar', 'cart', 'woolentor', 'shoplentor' ];
    }

    public function get_script_depends() {
        $deps = [ 'woolentor-free-shipping-bar' ];
        if ( woolentor_is_pro() ) {
            $deps[] = 'woolentor-fsb-pro';
        }
        return $deps;
    }

    public function get_style_depends() {
        $deps = [ 'woolentor-free-shipping-bar' ];
        if ( woolentor_is_pro() ) {
            $deps[] = 'woolentor-fsb-pro';
        }
        return $deps;
    }

    protected function register_controls() {

        // ─── Content: General ──────────────────────────────────────────────────
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'General', 'woolentor' ),
            ]
        );

            $this->add_control(
                'dismissible',
                [
                    'label'        => __( 'Dismissible', 'woolentor' ),
                    'type'         => Controls_Manager::SWITCHER,
                    'label_on'     => __( 'Yes', 'woolentor' ),
                    'label_off'    => __( 'No', 'woolentor' ),
                    'return_value' => 'yes',
                    'default'      => 'no',
                    'description'  => __( 'Show a close button so visitors can dismiss the bar.', 'woolentor' ),
                ]
            );

            if( woolentor_is_pro() ){
                $this->add_control(
                    'show_countdown',
                    [
                        'label'        => __( 'Show Countdown', 'woolentor' ),
                        'type'         => Controls_Manager::SWITCHER,
                        'label_on'     => __( 'Show', 'woolentor' ),
                        'label_off'    => __( 'Hide', 'woolentor' ),
                        'return_value' => 'yes',
                        'default'      => 'yes',
                        'description'  => __( 'Show the countdown timer if enabled in module settings.', 'woolentor' ),
                    ]
                );
            }else{
                $this->add_control(
                    'show_countdown_pro',
                    [
                        'label'         => __( 'Show Countdown?', 'woolentor' ) .' <i class="eicon-pro-icon"></i>',
                        'type'          => Controls_Manager::SWITCHER,
                        'label_on'      => __( 'Yes', 'woolentor' ),
                        'label_off'     => __( 'No', 'woolentor' ),
                        'return_value'  => 'yes',
                        'default'       => 'no',
                        'classes' => 'woolentor-disable-control',
                    ]
                );
            }

        $this->end_controls_section();

        // ─── Content: Icon ─────────────────────────────────────────────────────
        $this->start_controls_section(
            'section_icon',
            [
                'label' => __( 'Icon', 'woolentor' ),
            ]
        );
            if(!woolentor_is_pro()){
                woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl_free_shipping_bar', 'section_icon');
            }
            else{
                $this->add_control(
                    'show_icon',
                    [
                        'label'        => __( 'Show Icon', 'woolentor' ),
                        'type'         => Controls_Manager::SWITCHER,
                        'label_on'     => __( 'Show', 'woolentor' ),
                        'label_off'    => __( 'Hide', 'woolentor' ),
                        'return_value' => 'yes',
                        'default'      => 'yes',
                    ]
                );

                $this->add_control(
                    'icon',
                    [
                        'label'       => __( 'Icon', 'woolentor' ),
                        'type'        => Controls_Manager::ICONS,
                        'default'     => [
                            'value'   => 'fas fa-shipping-fast',
                            'library' => 'font-awesome',
                        ],
                        'condition'   => [
                            'show_icon' => 'yes',
                        ],
                    ]
                );
            }

        $this->end_controls_section();

        // ─── Content: Message ──────────────────────────────────────────────────
        $this->start_controls_section(
            'section_message',
            [
                'label' => __( 'Message', 'woolentor' ),
            ]
        );

            $this->add_control(
                'custom_message',
                [
                    'label'       => __( 'Custom Message', 'woolentor' ),
                    'label_block' => true,
                    'type'        => Controls_Manager::TEXT,
                    'default'     => __( "You're {amount} away from complimentary express shipping.", 'woolentor' ),
                    'placeholder' => __( 'Spend {amount} more to get FREE shipping!', 'woolentor' ),
                    'description' => __( 'Use {amount} as placeholder. Leave empty to use the module setting.', 'woolentor' ),
                ]
            );

            $this->add_control(
                'custom_success_message',
                [
                    'label'       => __( 'Custom Success Message', 'woolentor' ),
                    'label_block' => true,
                    'type'        => Controls_Manager::TEXT,
                    'default'     => '',
                    'placeholder' => __( '🎉 You\'ve unlocked FREE shipping!', 'woolentor' ),
                    'description' => __( 'Shown when the customer has reached the free shipping threshold. Leave empty to use the module setting.', 'woolentor' ),
                ]
            );

        $this->end_controls_section();

        // ─── Style: Bar Card ───────────────────────────────────────────────────
        $this->start_controls_section(
            'style_bar_wrapper',
            [
                'label' => __( 'Bar Card', 'woolentor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'bar_bg_color',
                [
                    'label'     => __( 'Background Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-wrap' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name'     => 'bar_border',
                    'selector' => '{{WRAPPER}} .wl-fsb-wrap',
                ]
            );

            $this->add_control(
                'bar_border_radius',
                [
                    'label'      => __( 'Border Radius', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'bar_padding',
                [
                    'label'      => __( 'Padding', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                [
                    'name'     => 'bar_shadow',
                    'selector' => '{{WRAPPER}} .wl-fsb-wrap',
                ]
            );

        $this->end_controls_section();

        // ─── Style: Icon ───────────────────────────────────────────────────────
        $this->start_controls_section(
            'style_icon',
            [
                'label'     => __( 'Icon', 'woolentor' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );

            $this->add_responsive_control(
                'icon_size',
                [
                    'label'      => __( 'Size', 'woolentor' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range'      => [
                        'px' => [
                            'min'  => 10,
                            'max'  => 100,
                            'step' => 1,
                        ],
                    ],
                    'default'    => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-icon'     => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .wl-fsb-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                        '{{WRAPPER}} .wl-fsb-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'icon_color',
                [
                    'label'     => __( 'Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#2d5a3d',
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-icon'     => 'color: {{VALUE}};',
                        '{{WRAPPER}} .wl-fsb-icon svg' => 'fill: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'icon_bg_color',
                [
                    'label'     => __( 'Background Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#e8f5e9',
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-icon' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'icon_border_radius',
                [
                    'label'      => __( 'Border Radius', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'default'    => [
                        'top'      => '50',
                        'right'    => '50',
                        'bottom'   => '50',
                        'left'     => '50',
                        'unit'     => '%',
                        'isLinked' => true,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'icon_padding',
                [
                    'label'      => __( 'Padding', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em' ],
                    'default'    => [
                        'top'      => '10',
                        'right'    => '10',
                        'bottom'   => '10',
                        'left'     => '10',
                        'unit'     => 'px',
                        'isLinked' => true,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

        $this->end_controls_section();

        // ─── Style: Content Alignment ──────────────────────────────────────────
        $this->start_controls_section(
            'style_content_layout',
            [
                'label' => __( 'Content Layout', 'woolentor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_responsive_control(
                'content_alignment',
                [
                    'label'     => __( 'Content Alignment', 'woolentor' ),
                    'type'      => Controls_Manager::CHOOSE,
                    'options'   => [
                        'flex-start' => [
                            'title' => __( 'Left', 'woolentor' ),
                            'icon'  => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'woolentor' ),
                            'icon'  => 'eicon-text-align-center',
                        ],
                        'flex-end'  => [
                            'title' => __( 'Right', 'woolentor' ),
                            'icon'  => 'eicon-text-align-right',
                        ],
                    ],
                    'default'   => 'flex-start',
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-text' => 'align-items: {{VALUE}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'content_gap',
                [
                    'label'      => __( 'Icon & Text Gap', 'woolentor' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range'      => [
                        'px' => [
                            'min'  => 0,
                            'max'  => 60,
                            'step' => 1,
                        ],
                    ],
                    'default'    => [
                        'unit' => 'px',
                        'size' => 12,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-content' => 'gap: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

        $this->end_controls_section();

        // ─── Style: Message ────────────────────────────────────────────────────
        $this->start_controls_section(
            'style_message',
            [
                'label' => __( 'Message', 'woolentor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'message_color',
                [
                    'label'     => __( 'Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#333333',
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-message' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name'     => 'message_typography',
                    'label'    => __( 'Typography', 'woolentor' ),
                    'selector' => '{{WRAPPER}} .wl-fsb-message',
                ]
            );

            $this->add_control(
                'message_alignment',
                [
                    'label'     => __( 'Alignment', 'woolentor' ),
                    'type'      => Controls_Manager::CHOOSE,
                    'options'   => [
                        'left'   => [
                            'title' => __( 'Left', 'woolentor' ),
                            'icon'  => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'woolentor' ),
                            'icon'  => 'eicon-text-align-center',
                        ],
                        'right'  => [
                            'title' => __( 'Right', 'woolentor' ),
                            'icon'  => 'eicon-text-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-message' => 'text-align: {{VALUE}};',
                    ],
                ]
            );

        $this->end_controls_section();

        if( ! woolentor_is_pro() ){
            $this->start_controls_section(
                'style_countdown_pro',
                [
                    'label' => __( 'Countdown', 'woolentor' ),
                    'tab'   => Controls_Manager::TAB_STYLE,
                ]
            );
            
            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'woolentor_free_shipping_bar', 'style_countdown');
            
            $this->end_controls_section();
        }

        // ─── Style: Countdown ──────────────────────────────────────────────────
        $this->start_controls_section(
            'style_countdown',
            [
                'label'     => __( 'Countdown', 'woolentor' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_countdown' => 'yes',
                ],
            ]
        );

            $this->add_control(
                'countdown_color',
                [
                    'label'     => __( 'Text Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-countdown' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name'     => 'countdown_typography',
                    'label'    => __( 'Typography', 'woolentor' ),
                    'selector' => '{{WRAPPER}} .wl-fsb-countdown',
                ]
            );

            $this->add_control(
                'countdown_timer_color',
                [
                    'label'     => __( 'Timer Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-countdown .wl-fsb-timer' => 'color: {{VALUE}};',
                    ],
                    'separator' => 'before',
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name'     => 'countdown_timer_typography',
                    'label'    => __( 'Timer Typography', 'woolentor' ),
                    'selector' => '{{WRAPPER}} .wl-fsb-countdown .wl-fsb-timer',
                ]
            );

            $this->add_responsive_control(
                'countdown_margin',
                [
                    'label'      => __( 'Margin', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em' ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-countdown' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'separator'  => 'before',
                ]
            );

            $this->add_responsive_control(
                'countdown_alignment',
                [
                    'label'     => __( 'Alignment', 'woolentor' ),
                    'type'      => Controls_Manager::CHOOSE,
                    'options'   => [
                        'left'   => [
                            'title' => __( 'Left', 'woolentor' ),
                            'icon'  => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'woolentor' ),
                            'icon'  => 'eicon-text-align-center',
                        ],
                        'right'  => [
                            'title' => __( 'Right', 'woolentor' ),
                            'icon'  => 'eicon-text-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-countdown' => 'text-align: {{VALUE}};',
                    ],
                ]
            );

        $this->end_controls_section();

        // ─── Style: Progress Track ─────────────────────────────────────────────
        $this->start_controls_section(
            'style_progress_track',
            [
                'label' => __( 'Progress Track', 'woolentor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'track_bg_color',
                [
                    'label'     => __( 'Background Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#f0f0f0',
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-progress-track' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'track_height',
                [
                    'label'      => __( 'Height', 'woolentor' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range'      => [
                        'px' => [
                            'min'  => 2,
                            'max'  => 30,
                            'step' => 1,
                        ],
                    ],
                    'default'    => [
                        'unit' => 'px',
                        'size' => 6,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-progress-track' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'track_max_width',
                [
                    'label'      => __( 'Max Width', 'woolentor' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'range'      => [
                        'px' => [
                            'min'  => 50,
                            'max'  => 1000,
                            'step' => 1,
                        ],
                        '%'  => [
                            'min'  => 10,
                            'max'  => 100,
                            'step' => 1,
                        ],
                    ],
                    'default'    => [
                        'unit' => '%',
                        'size' => 100,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-progress-track' => 'max-width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'track_border_radius',
                [
                    'label'      => __( 'Border Radius', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-progress-track' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

        $this->end_controls_section();

        // ─── Style: Progress Fill ──────────────────────────────────────────────
        $this->start_controls_section(
            'style_progress_fill',
            [
                'label' => __( 'Progress Fill', 'woolentor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'fill_bg_color',
                [
                    'label'     => __( 'Background Color', 'woolentor' ),
                    'type'      => Controls_Manager::COLOR,
                    'default'   => '#2d5a3d',
                    'selectors' => [
                        '{{WRAPPER}} .wl-fsb-progress-fill' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'fill_border_radius',
                [
                    'label'      => __( 'Border Radius', 'woolentor' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-progress-fill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

        $this->end_controls_section();

        // ─── Style: Close Button ───────────────────────────────────────────────
        $this->start_controls_section(
            'style_close_button',
            [
                'label'     => __( 'Close Button', 'woolentor' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'dismissible' => 'yes',
                ],
            ]
        );

            $this->start_controls_tabs( 'close_btn_tabs' );

                $this->start_controls_tab( 'close_btn_normal', [ 'label' => __( 'Normal', 'woolentor' ) ] );
                    $this->add_control(
                        'close_btn_color',
                        [
                            'label'     => __( 'Color', 'woolentor' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wl-fsb-close' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_control(
                        'close_btn_bg',
                        [
                            'label'     => __( 'Background', 'woolentor' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wl-fsb-close' => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );
                $this->end_controls_tab();

                $this->start_controls_tab( 'close_btn_hover', [ 'label' => __( 'Hover', 'woolentor' ) ] );
                    $this->add_control(
                        'close_btn_hover_color',
                        [
                            'label'     => __( 'Color', 'woolentor' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wl-fsb-close:hover' => 'color: {{VALUE}};',
                            ],
                        ]
                    );
                    $this->add_control(
                        'close_btn_hover_bg',
                        [
                            'label'     => __( 'Background', 'woolentor' ),
                            'type'      => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .wl-fsb-close:hover' => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );
                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_control(
                'close_btn_size',
                [
                    'label'      => __( 'Size', 'woolentor' ),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range'      => [
                        'px' => [
                            'min'  => 10,
                            'max'  => 40,
                            'step' => 1,
                        ],
                    ],
                    'default'    => [
                        'unit' => 'px',
                        'size' => 20,
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-fsb-close' => 'font-size: {{SIZE}}{{UNIT}};',
                    ],
                    'separator'  => 'before',
                ]
            );

        $this->end_controls_section();

    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Hook the filter so the shortcode uses Elementor's Icons_Manager for
        // rendering — this handles SVG icons, custom icon libraries, etc.
        $icon_settings = $settings['icon'] ?? [];
        $icon_filter   = static function ( $default_html, $atts ) use ( $icon_settings ) {
            if ( empty( $icon_settings['value'] ) ) {
                return $default_html;
            }
            ob_start();
            \Elementor\Icons_Manager::render_icon( $icon_settings, [ 'aria-hidden' => 'true' ] );
            return ob_get_clean();
        };

        add_filter( 'woolentor_fsb_icon_html', $icon_filter, 10, 2 );

        $shortcode_params = [
            'inline'          => 'yes',
            'widget'          => 'yes',
            'dismissible'     => $settings['dismissible'],
            'show_icon'       => $settings['show_icon'],
            'icon'            => is_string( $settings['icon']['value'] ?? '' ) ? $settings['icon']['value'] : '',
            'show_countdown'  => $settings['show_countdown'] ?? 'no',
            'message'         => $settings['custom_message'] ?? '',
            'message_success' => $settings['custom_success_message'] ?? '',
        ];

        echo woolentor_do_shortcode( 'woolentor_free_shipping_bar', $shortcode_params ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        remove_filter( 'woolentor_fsb_icon_html', $icon_filter, 10 );
    }

}
