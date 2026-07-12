<?php
/**
 * Hero Banner Widget — Pattern B (Style + Variant dropdowns)
 * Supports multiple slides via Repeater for future slider functionality.
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Hero_Banner_Widget extends Widget_Base {

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
        $this->register_pack_scripts();
    }

    private function register_pack_scripts() {
        if ( ! wp_script_is( 'wl-pack-widgets', 'registered' ) ) {
            wp_register_script(
                'wl-pack-widgets',
                WOOLENTOR_ADDONS_PL_URL . 'assets/pack-widgets/js/pack-widgets.js',
                [ 'jquery', 'slick' ],
                WOOLENTOR_VERSION,
                true
            );
        }
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-hero-banner-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/hero-banner/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-hero-banner';
    }

    public function get_title() {
        return esc_html__( 'Hero Banner - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-slider-device';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'hero', 'banner', 'slider', 'pack', 'style', 'modern', 'luxury', 'editorial', 'magazine', 'woolentor' ];
    }

    public function get_script_depends() {
        return [ 'wl-pack-widgets' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-hero-banner-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    protected function register_controls() {

        // ── Style Pack ────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_pack', [
            'label' => esc_html__( 'Style Pack', 'woolentor' ),
        ] );

            $this->add_control( 'style', [
                'label'   => esc_html__( 'Style', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'modern',
                'options' => \WooLentor\Style_Pack_Manager::get_pack_labels(),
            ] );

            $this->add_control( 'variant', [
                'label'   => esc_html__( 'Variant', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'v1',
                'options' => \WooLentor\Style_Pack_Manager::get_variant_options(),
            ] );

            $this->add_control( 'enable_slider', [
                'label'        => esc_html__( 'Enable Slider', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'woolentor' ),
                'label_off'    => esc_html__( 'No', 'woolentor' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [ 'style!' => 'magazine' ],
            ] );

            woolentor_upgrade_pro_notice( $this, 'style_pack_variant_pro_notice',
                [
                    'relation' => 'or',
                    'terms'    => [
                        [
                            'relation' => 'and',
                            'terms'    => [
                                [ 'name' => 'style',   'operator' => '==', 'value' => 'editorial' ],
                                [ 'name' => 'variant', 'operator' => '!=', 'value' => 'v1' ],
                            ],
                        ],
                        [
                            'relation' => 'and',
                            'terms'    => [
                                [ 'name' => 'style',   'operator' => '==', 'value' => 'luxury' ],
                                [ 'name' => 'variant', 'operator' => '!=', 'value' => 'v1' ],
                            ],
                        ],
                        [
                            'relation' => 'and',
                            'terms'    => [
                                [ 'name' => 'style',   'operator' => '==', 'value' => 'magazine' ],
                                [ 'name' => 'variant', 'operator' => '!=', 'value' => 'v1' ],
                            ],
                        ],
                    ],
                ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        // ── Slide Items — one section per style ──────────────────────────────
        $this->register_modern_slides_controls();
        $this->register_editorial_slides_controls();
        $this->register_luxury_slides_controls();
        $this->register_magazine_slides_controls();
        $this->register_magazine_v1_sidebar_controls();
        // Pro variant notice — replaces content controls for locked variants.
        $this->register_pro_content_notice();

        // ── Slider ────────────────────────────────────────────────────────────
        $this->start_controls_section(
            'section_slider',
            [
                'label'     => esc_html__( 'Slider Options', 'woolentor' ),
                'condition' => [
                    'enable_slider' => 'yes',
                    'style!'        => 'magazine',
                ],
            ]
        );

            // Compact pro notice at the top of Slider Options for pro variants.
            woolentor_upgrade_pro_notice( $this, 'slider_pro_notice', [
                'style'   => [ 'editorial', 'luxury' ],
                'variant' => [ 'v2', 'v3' ],
            ], [ 'compact' => true ] );

            $this->add_control( 'slider_arrows', [
                'label'        => esc_html__( 'Show Arrows', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes'
            ] );

            $this->add_control( 'slider_dots', [
                'label'        => esc_html__( 'Show Dots', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes'
            ] );

            $this->add_control( 'slider_infinite', [
                'label'        => esc_html__( 'Infinite Loop', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes'
            ] );

            $this->add_control( 'slider_fade', [
                'label'        => esc_html__( 'Fade Transition', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => ''
            ] );

            $this->add_control( 'slider_autoplay', [
                'label'        => esc_html__( 'Autoplay', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes'
            ] );

            $this->add_control( 'slider_autoplay_speed', [
                'label'     => esc_html__( 'Autoplay Speed (ms)', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 5000,
                'min'       => 500,
                'step'      => 100,
                'condition' => [ 'slider_autoplay' => 'yes' ],
            ] );

            $this->add_control( 'slider_speed', [
                'label'     => esc_html__( 'Transition Speed (ms)', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 600,
                'min'       => 100,
                'step'      => 50
            ] );

            $this->add_control( 'slider_pause_on_hover', [
                'label'        => esc_html__( 'Pause on Hover', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes'
            ] );

            // — Device-wise items —
            $this->add_control( 'heading_desktop', [
                'label'     => esc_html__( 'Desktop', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ] );

            $this->add_control( 'sl_items', [
                'label'     => esc_html__( 'Slider Items', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 5,
                'step'      => 1,
                'default'   => 1
            ] );

            $this->add_control( 'sl_scroll', [
                'label'     => esc_html__( 'Items to Scroll', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 5,
                'step'      => 1,
                'default'   => 1
            ] );

            $this->add_control( 'heading_tablet', [
                'label'     => esc_html__( 'Tablet', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ] );

            $this->add_control( 'sl_tablet_items', [
                'label'     => esc_html__( 'Slider Items', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 4,
                'step'      => 1,
                'default'   => 1
            ] );

            $this->add_control( 'sl_tablet_scroll', [
                'label'     => esc_html__( 'Items to Scroll', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 4,
                'step'      => 1,
                'default'   => 1
            ] );

            $this->add_control( 'sl_tablet_width', [
                'label'     => esc_html__( 'Tablet Resolution', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 768
            ] );

            $this->add_control( 'heading_mobile', [
                'label'     => esc_html__( 'Mobile', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            ] );

            $this->add_control( 'sl_mobile_items', [
                'label'     => esc_html__( 'Slider Items', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 3,
                'step'      => 1,
                'default'   => 1
            ] );

            $this->add_control( 'sl_mobile_scroll', [
                'label'     => esc_html__( 'Items to Scroll', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 3,
                'step'      => 1,
                'default'   => 1
            ] );

            $this->add_control( 'sl_mobile_width', [
                'label'     => esc_html__( 'Mobile Resolution', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 480
            ] );

        $this->end_controls_section();

        // ── Style Tab ─────────────────────────────────────────────────────────

        // Pro variant style notice — replaces style controls for locked variants.
        $this->register_pro_style_notice();

        // — Heading —
        $this->start_controls_section( 'style_heading', [
            'label' => esc_html__( 'Heading', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_control( 'heading_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hc-heading, {{WRAPPER}} .wl-hero-heading, {{WRAPPER}} .wl-hv2-title span:not(.wl-hl-outline), {{WRAPPER}} .wl-hv3-heading, {{WRAPPER}} [class^="wl-hev"][class$="-title"],{{WRAPPER}} [class*="wl-hlv"][class*="-title"]' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'heading_typography',
                'selector' => '{{WRAPPER}} .wl-hc-heading, {{WRAPPER}} .wl-hero-heading, {{WRAPPER}} .wl-hv2-title, {{WRAPPER}} .wl-hv3-heading, {{WRAPPER}} [class^="wl-hev"][class$="-title"],{{WRAPPER}} [class*="wl-hlv"][class*="-title"]',
            ] );

        $this->end_controls_section();

        // — Eyebrow —
        $this->start_controls_section( 'style_eyebrow', [
            'label' => esc_html__( 'Eyebrow', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-hv2-eyebrow, {{WRAPPER}} .wl-hc-eyebrow, {{WRAPPER}} .wl-hv3-eyebrow, {{WRAPPER}} .wl-hev1-kicker, {{WRAPPER}} [class^="wl-hev"][class$="-eyebrow"], {{WRAPPER}} .wl-hev3-badge, {{WRAPPER}} [class*="wl-hlv"][class*="-eyebrow"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-hv2-eyebrow::before, {{WRAPPER}} .wl-hc-eyebrow::before, {{WRAPPER}} .wl-hv3-eyebrow::before, {{WRAPPER}} .wl-hev1-kicker::before, {{WRAPPER}} [class^="wl-hev"][class$="-eyebrow"]::before, {{WRAPPER}} .wl-hev3-badge-dot, {{WRAPPER}} [class*="wl-hlv"][class*="-eyebrow"]::before, {{WRAPPER}} .wl-hlv1-eyebrow-dash' => 'background: {{VALUE}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-hv2-eyebrow, {{WRAPPER}} .wl-hc-eyebrow, {{WRAPPER}} .wl-hv3-eyebrow, {{WRAPPER}} [class^="wl-hev"][class$="-eyebrow"], {{WRAPPER}} .wl-hev3-badge, {{WRAPPER}} [class*="wl-hlv"][class*="-eyebrow"]',
            ] );

        $this->end_controls_section();

        // — Highlight Text —
        $this->start_controls_section( 'style_highlight', [
            'label' => esc_html__( 'Highlight Text', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_control( 'highlight_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-hl' => 'background: {{VALUE}}; -webkit-background-clip: text; background-clip: text; color: transparent;',
                    '{{WRAPPER}} .wl-hv2-title span.wl-hl-outline' => 'background: {{VALUE}}; -webkit-background-clip: text; background-clip: text; color: transparent;',
                ],
            ] );

        $this->end_controls_section();

        // — Description —
        $this->start_controls_section( 'style_description', [
            'label' => esc_html__( 'Description', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_control( 'description_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hc-sub, {{WRAPPER}} .wl-hero-description, {{WRAPPER}} .wl-hv2-desc, {{WRAPPER}} .wl-hv3-subtext, {{WRAPPER}} .wl-hev1-intro,{{WRAPPER}} .wl-hev3-body, {{WRAPPER}} [class^="wl-hlv"][class$="-sub"], {{WRAPPER}} [class^="wl-hlv"][class$="-desc"]' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'description_typography',
                'selector' => '{{WRAPPER}} .wl-hc-sub, {{WRAPPER}} .wl-hero-description, {{WRAPPER}} .wl-hv2-desc, {{WRAPPER}} .wl-hv3-subtext, {{WRAPPER}} .wl-hev1-intro, {{WRAPPER}} .wl-hev3-body, {{WRAPPER}} [class^="wl-hlv"][class$="-sub"], {{WRAPPER}} [class^="wl-hlv"][class$="-desc"]',
            ] );

        $this->end_controls_section();

        // — Showcase Card —
        $this->start_controls_section( 'style_showcase', [
            'label' => esc_html__( 'Showcase Card', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_control( 'divider_showcase_eyebrow', [
                'label'     => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'showcase_eyebrow_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hc-sm-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'showcase_eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-hc-sm-eyebrow',
            ] );

            $this->add_control( 'divider_showcase_subtitle', [
                'label'     => esc_html__( 'Subtitle', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'showcase_subtitle_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hc-sm-subtitle, {{WRAPPER}} .wl-hv3-product-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'showcase_subtitle_typography',
                'selector' => '{{WRAPPER}} .wl-hc-sm-subtitle, {{WRAPPER}} .wl-hv3-product-name',
            ] );

            $this->add_control( 'divider_showcase_desc', [
                'label'     => esc_html__( 'Description', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'showcase_desc_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hc-sm-para, {{WRAPPER}} .wl-hv3-product-price' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'showcase_desc_typography',
                'selector' => '{{WRAPPER}} .wl-hc-sm-para, {{WRAPPER}} .wl-hv3-product-price',
            ] );

        $this->end_controls_section();

        // — Primary Button —
        $this->start_controls_section( 'style_btn_primary', [
            'label' => esc_html__( 'Primary Button', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'btn_primary_typography',
                'selector' => '{{WRAPPER}} .wl-hc-btn-light, {{WRAPPER}} .wl-btn-primary, {{WRAPPER}} .wl-hv3-btn, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"], {{WRAPPER}} .wl-hev2-btn, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]',
            ] );

            $this->add_responsive_control( 'btn_primary_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [ '{{WRAPPER}} .wl-hc-btn-light, {{WRAPPER}} .wl-btn-primary, {{WRAPPER}} .wl-hv3-btn, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"], {{WRAPPER}} .wl-hev2-btn, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );

            $this->add_control( 'btn_primary_border_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [ '{{WRAPPER}} .wl-hc-btn-light, {{WRAPPER}} .wl-btn-primary, {{WRAPPER}} .wl-hv3-btn, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"], {{WRAPPER}} .wl-hev2-btn, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );

            $this->start_controls_tabs( 'btn_primary_tabs' );

                $this->start_controls_tab( 'btn_primary_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'btn_primary_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-light, {{WRAPPER}} .wl-btn-primary, {{WRAPPER}} .wl-hv3-btn--primary, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"], {{WRAPPER}} .wl-hev2-btn, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_primary_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-light, {{WRAPPER}} .wl-btn-primary, {{WRAPPER}} .wl-hv3-btn--primary, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"], {{WRAPPER}} .wl-hev2-btn, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_primary_border_color', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-light, {{WRAPPER}} .wl-btn-primary, {{WRAPPER}} .wl-hv3-btn--primary, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"], {{WRAPPER}} .wl-hev2-btn, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'btn_primary_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'btn_primary_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-light:hover, {{WRAPPER}} .wl-btn-primary:hover, {{WRAPPER}} .wl-hv3-btn--primary:hover, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"]:hover, {{WRAPPER}} .wl-hev2-btn:hover, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_primary_color_hover', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-light:hover, {{WRAPPER}} .wl-btn-primary:hover, {{WRAPPER}} .wl-hv3-btn--primary:hover, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"]:hover, {{WRAPPER}} .wl-hev2-btn:hover, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_primary_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-light:hover, {{WRAPPER}} .wl-btn-primary:hover, {{WRAPPER}} .wl-hv3-btn--primary:hover, {{WRAPPER}} [class^="wl-hev"][class$="-btn-primary"]:hover, {{WRAPPER}} .wl-hev2-btn:hover, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-primary"]:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Secondary Button —
        $this->start_controls_section( 'style_btn_secondary', [
            'label' => esc_html__( 'Secondary Button', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [
                'style!' => 'magazine'
            ]
        ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'btn_secondary_typography',
                'selector' => '{{WRAPPER}} .wl-hc-btn-dark, {{WRAPPER}} .wl-btn-ghost, {{WRAPPER}} .wl-hev1-btn-link, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"], {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]',
            ] );

            $this->add_responsive_control( 'btn_secondary_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [ '{{WRAPPER}} .wl-hc-btn-dark, {{WRAPPER}} .wl-btn-ghost, {{WRAPPER}} .wl-hev1-btn-link, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"], {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );

            $this->add_control( 'btn_secondary_border_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [ '{{WRAPPER}} .wl-hc-btn-dark, {{WRAPPER}} .wl-btn-ghost, {{WRAPPER}} .wl-hev1-btn-link, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"], {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );

            $this->start_controls_tabs( 'btn_secondary_tabs' );

                $this->start_controls_tab( 'btn_secondary_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'btn_secondary_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-dark, {{WRAPPER}} .wl-btn-ghost, {{WRAPPER}} .wl-hv3-btn--outline, {{WRAPPER}} .wl-hev1-btn-link, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"], {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_secondary_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-dark, {{WRAPPER}} .wl-btn-ghost, {{WRAPPER}} .wl-hv3-btn--outline, {{WRAPPER}} .wl-hev1-btn-link, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"], {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_secondary_border_color', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-dark, {{WRAPPER}} .wl-btn-ghost, {{WRAPPER}} .wl-hv3-btn--outline, {{WRAPPER}} .wl-hev1-btn-link, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"], {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'btn_secondary_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'btn_secondary_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-dark:hover, {{WRAPPER}} .wl-btn-ghost:hover, {{WRAPPER}} .wl-hv3-btn--outline:hover, {{WRAPPER}} .wl-hev1-btn-link:hover, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"]:hover, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_secondary_color_hover', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-dark:hover, {{WRAPPER}} .wl-btn-ghost:hover, {{WRAPPER}} .wl-hv3-btn--outline:hover, {{WRAPPER}} .wl-hev1-btn-link:hover, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"]:hover, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_secondary_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hc-btn-dark:hover, {{WRAPPER}} .wl-btn-ghost:hover, {{WRAPPER}} .wl-hv3-btn--outline:hover, {{WRAPPER}} .wl-hev1-btn-link:hover, {{WRAPPER}} [class^="wl-hev"][class$="-btn-secondary"]:hover, {{WRAPPER}} [class^="wl-hlv"][class$="-btn-ghost"]:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Others Info Style —
        $this->start_controls_section( 'style_others_info', [
            'label' => esc_html__( 'Others Info', 'woolentor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
            'condition' => [ 'style' => 'editorial' ],
        ] );

            $this->add_control( 'issue_text_heading', [
                'label' => esc_html__( 'Issue Text', 'woolentor' ),
                'type'  => Controls_Manager::HEADING,
            ] );

            $this->add_control( 'issue_text_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hev1-vtext, {{WRAPPER}} .wl-hev3-media-label' => 'color: {{VALUE}};'],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'issue_text_typography',
                'selector' => '{{WRAPPER}} .wl-hev1-vtext, {{WRAPPER}} .wl-hev3-media-label',
            ] );

            $this->add_control( 'meta_text_heading', [
                'label' => esc_html__( 'Meta Text', 'woolentor' ),
                'type'  => Controls_Manager::HEADING,
            ] );

            $this->add_control( 'meta_text_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hev1-meta, {{WRAPPER}} .wl-hev3-edit-ref' => 'color: {{VALUE}};'],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'meta_text_typography',
                'selector' => '{{WRAPPER}} .wl-hev1-meta, {{WRAPPER}} .wl-hev3-edit-ref',
            ] );

            $this->add_control( 'photo_caption_heading', [
                'label' => esc_html__( 'Photo Caption', 'woolentor' ),
                'type'  => Controls_Manager::HEADING,
            ] );

            $this->add_control( 'photo_caption_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hev1-caption, {{WRAPPER}} .wl-hev3-caption-text' => 'color: {{VALUE}};'],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'photo_caption_typography',
                'selector' => '{{WRAPPER}} .wl-hev1-caption, {{WRAPPER}} .wl-hev3-caption-text',
            ] );

            $this->add_control( 'photo_caption_bg_color', [
                'label'     => esc_html__( 'Background Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hev1-caption, {{WRAPPER}} .wl-hev3-caption-text' => 'background-color: {{VALUE}};'],
            ] );

        $this->end_controls_section();

        // — Slider Controller Style —
        $this->start_controls_section( 'style_slider_controller', [
            'label'     => esc_html__( 'Slider Controller Style', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 
                'enable_slider' => 'yes',
                'style!' => 'magazine'
            ],
        ] );

            $this->start_controls_tabs( 'slider_controller_tabs' );

                // Normal tab
                $this->start_controls_tab( 'slider_controller_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'nav_arrow_heading', [
                        'label' => esc_html__( 'Navigation Arrow', 'woolentor' ),
                        'type'  => Controls_Manager::HEADING,
                    ] );

                    $this->add_responsive_control( 'nav_arrow_position', [
                        'label'      => esc_html__( 'Position (Top)', 'woolentor' ),
                        'type'       => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'range'      => [
                            'px' => [ 'min' => 0, 'max' => 1000, 'step' => 1 ],
                            '%'  => [ 'min' => 0, 'max' => 100 ],
                        ],
                        'selectors'  => [ '{{WRAPPER}} .wl-pack-nav' => 'top: {{SIZE}}{{UNIT}};' ],
                    ] );

                    $this->add_control( 'nav_arrow_color', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-nav' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'nav_arrow_bg', [
                        'label'     => esc_html__( 'Background Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-nav' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'nav_arrow_border',
                        'selector' => '{{WRAPPER}} .wl-pack-nav',
                    ] );

                    $this->add_responsive_control( 'nav_arrow_border_radius', [
                        'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                        'type'      => Controls_Manager::DIMENSIONS,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-nav' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                    ] );

                    $this->add_responsive_control( 'nav_arrow_padding', [
                        'label'      => esc_html__( 'Padding', 'woolentor' ),
                        'type'       => Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', '%', 'em' ],
                        'selectors'  => [ '{{WRAPPER}} .wl-pack-nav' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
                    ] );

                    $this->add_control( 'nav_dots_heading', [
                        'label'     => esc_html__( 'Navigation Dots', 'woolentor' ),
                        'type'      => Controls_Manager::HEADING,
                        'separator' => 'before',
                    ] );

                    $this->add_responsive_control( 'nav_dots_position', [
                        'label'      => esc_html__( 'Position (Left)', 'woolentor' ),
                        'type'       => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'range'      => [
                            'px' => [ 'min' => 0, 'max' => 1000, 'step' => 5 ],
                            '%'  => [ 'min' => 0, 'max' => 100 ],
                        ],
                        'default'    => [ 'unit' => '%', 'size' => 50 ],
                        'selectors'  => [ '{{WRAPPER}} .wl-pack-dots' => 'left: {{SIZE}}{{UNIT}};' ],
                    ] );

                    $this->add_control( 'nav_dots_bg', [
                        'label'     => esc_html__( 'Background Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-dots li button' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'nav_dots_border',
                        'selector' => '{{WRAPPER}} .wl-pack-dots li button',
                    ] );

                    $this->add_responsive_control( 'nav_dots_border_radius', [
                        'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                        'type'      => Controls_Manager::DIMENSIONS,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-dots li button' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                    ] );

                $this->end_controls_tab();

                // Hover tab
                $this->start_controls_tab( 'slider_controller_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'nav_arrow_hover_heading', [
                        'label' => esc_html__( 'Navigation Arrow', 'woolentor' ),
                        'type'  => Controls_Manager::HEADING,
                    ] );

                    $this->add_control( 'nav_arrow_color_hover', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-nav:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'nav_arrow_bg_hover', [
                        'label'     => esc_html__( 'Background Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-nav:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'nav_arrow_border_hover',
                        'selector' => '{{WRAPPER}} .wl-pack-nav:hover',
                    ] );

                    $this->add_responsive_control( 'nav_arrow_border_radius_hover', [
                        'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                        'type'      => Controls_Manager::DIMENSIONS,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-nav:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                    ] );

                    $this->add_control( 'nav_dots_hover_heading', [
                        'label'     => esc_html__( 'Navigation Dots', 'woolentor' ),
                        'type'      => Controls_Manager::HEADING,
                        'separator' => 'before',
                    ] );

                    $this->add_control( 'nav_dots_bg_hover', [
                        'label'     => esc_html__( 'Background Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .wl-pack-dots li button:hover'          => 'background-color: {{VALUE}};',
                            '{{WRAPPER}} .wl-pack-dots li.slick-active button'   => 'background-color: {{VALUE}};',
                        ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'nav_dots_border_hover',
                        'selector' => '{{WRAPPER}} .wl-pack-dots li button:hover',
                    ] );

                    $this->add_responsive_control( 'nav_dots_border_radius_hover', [
                        'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                        'type'      => Controls_Manager::DIMENSIONS,
                        'selectors' => [ '{{WRAPPER}} .wl-pack-dots li button:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // ── Magazine Style Sections ───────────────────────────────────────────
        $this->register_magazine_v1_style_controls();
    }

    // ── Per-style slide repeaters ─────────────────────────────────────────────

    private function register_modern_slides_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'divider_main', [
            'label'     => esc_html__( 'Main Card', 'woolentor' ),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'after',
        ] );

        $repeater->add_control( 'eyebrow', [
            'label'       => esc_html__( 'Eyebrow Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'description' => esc_html__( 'Small label above the heading, e.g. "Spring Summer 2026 Collection".', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading', [
            'label'       => esc_html__( 'Heading', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Power Built for<br>Every Task',
            'description' => esc_html__( 'Use <br> for a line break.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading_highlight', [
            'label'       => esc_html__( 'Heading Highlight Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Built for', 'woolentor' ),
            'description' => esc_html__( 'Comma-separated phrases to highlight. Must match text in the Heading field exactly.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'description', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => esc_html__( 'Ultra-thin laptops with all-day battery, blazing performance, and the display you deserve.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'btn_primary_text', [
            'label'       => esc_html__( 'Primary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Shop Now', 'woolentor' ),
        ] );

        $repeater->add_control( 'btn_primary_url', [
            'label'         => esc_html__( 'Primary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/shop',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'btn_secondary_text', [
            'label'       => esc_html__( 'Secondary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Explore All', 'woolentor' ),
        ] );

        $repeater->add_control( 'btn_secondary_url', [
            'label'         => esc_html__( 'Secondary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/collection',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'hero_image', [
            'label'   => esc_html__( 'Background Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => '' ],
        ] );

        $repeater->add_control( 'hero_image_alt', [
            'label'       => esc_html__( 'Background Image Alt Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'label_block' => true,
        ] );

        // — Showcase Card —
        $repeater->add_control( 'divider_showcase', [
            'label'     => esc_html__( 'Showcase Card', 'woolentor' ),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'after',
        ] );

        $repeater->add_control( 'card2_eyebrow', [
            'label'       => esc_html__( 'Product Name', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Xenbuds Ultra', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'card2_subtitle', [
            'label'       => esc_html__( 'Subtitle / Price', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '14″ 120Hz OLED · 18-hr Battery · Starts at $1,299',
            'label_block' => true,
        ] );

        $repeater->add_control( 'card2_image', [
            'label'   => esc_html__( 'Product Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => '' ],
        ] );

        $repeater->add_control( 'card2_image_alt', [
            'label'       => esc_html__( 'Product Image Alt Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'label_block' => true,
        ] );

        $repeater->add_control( 'card2_watermark', [
            'label'       => esc_html__( 'Watermark Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'BUDS', 'woolentor' ),
            'description' => esc_html__( 'Large faint text shown behind the product image.', 'woolentor' ),
        ] );

        $repeater->add_control( 'card2_description', [
            'label'       => esc_html__( 'Product Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => esc_html__( 'M3-class chip, fanless design, and a ProMotion OLED display that adapts to every scene. Zero compromise for creators on the move.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'card2_btn_text', [
            'label'   => esc_html__( 'Button Text', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'Shop now', 'woolentor' ),
        ] );

        $repeater->add_control( 'card2_btn_url', [
            'label'         => esc_html__( 'Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/shop',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $this->start_controls_section( 'section_modern_slides', [
            'label'     => esc_html__( 'Items', 'woolentor' ),
            'condition' => [ 'style' => 'modern' ],
        ] );

        $this->add_control( 'modern_slides', [
            'label'       => esc_html__( 'Slides', 'woolentor' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => [
                [
                    'heading'            => 'Power Built for<br>Every Task',
                    'heading_highlight'  => 'Built for',
                    'description'        => 'Ultra-thin laptops with all-day battery, blazing performance, and the display you deserve.',
                    'btn_primary_text'   => 'Shop Now',
                    'btn_primary_url'    => [ 'url' => '#' ],
                    'btn_secondary_text' => 'Explore All',
                    'btn_secondary_url'  => [ 'url' => '#' ],
                    'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'     => 'NovaBook Ultra — power built for every task',
                    'card2_eyebrow'      => 'Xenbuds Ultra',
                    'card2_subtitle'     => '14″ 120Hz OLED · 18-hr Battery · Starts at $1,299',
                    'card2_image'        => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'card2_image_alt'    => 'Xenbuds Ultra earbuds',
                    'card2_watermark'    => 'BUDS',
                    'card2_description'  => 'M3-class chip, fanless design, and a ProMotion OLED display that adapts to every scene. Zero compromise for creators on the move.',
                    'card2_btn_text'     => 'Shop now',
                    'card2_btn_url'      => [ 'url' => '#' ],
                ],
                [
                    'heading'            => 'Capture Designed for<br>Every Shot',
                    'heading_highlight'  => 'Designed for',
                    'description'        => 'Professional-grade cameras and lenses for photographers who demand precision, clarity, and speed.',
                    'btn_primary_text'   => 'Shop Now',
                    'btn_primary_url'    => [ 'url' => '#' ],
                    'btn_secondary_text' => 'Explore All',
                    'btn_secondary_url'  => [ 'url' => '#' ],
                    'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'     => 'ProLens X1 — designed for every shot',
                    'card2_eyebrow'      => 'Omnix Pro 2',
                    'card2_subtitle'     => '45MP Full-Frame · 4K/60fps · Starts at $1,499',
                    'card2_image'        => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'card2_image_alt'    => 'ProLens X1 camera',
                    'card2_watermark'    => 'SOUND',
                    'card2_description'  => 'Dual-card slots, in-body stabilisation, and a weather-sealed magnesium body. Engineered for professionals who never miss a shot.',
                    'card2_btn_text'     => 'Shop now',
                    'card2_btn_url'      => [ 'url' => '#' ],
                ],
                [
                    'heading'            => 'Precision Crafted for<br>Every Moment',
                    'heading_highlight'  => 'Crafted for',
                    'description'        => 'Smartwatches built for performance, deep health insights, and bold design that moves with you.',
                    'btn_primary_text'   => 'Shop Now',
                    'btn_primary_url'    => [ 'url' => '#' ],
                    'btn_secondary_text' => 'Explore All',
                    'btn_secondary_url'  => [ 'url' => '#' ],
                    'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'     => 'Omnix Watch Ultra tracking active lifestyle',
                    'card2_eyebrow'      => 'Watch Ultra',
                    'card2_subtitle'     => 'Titanium Edition · 18-Day Battery · Starts at $799',
                    'card2_image'        => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'card2_image_alt'    => 'Omnix Watch Ultra Titanium',
                    'card2_watermark'    => 'WATCH',
                    'card2_description'  => 'ECG, blood oxygen, and advanced sleep tracking in a premium titanium case. The watch that keeps up with every ambition.',
                    'card2_btn_text'     => 'Shop now',
                    'card2_btn_url'      => [ 'url' => '#' ],
                ],
            ],
            'title_field' => '{{{ heading }}}',
        ] );

        $this->end_controls_section();

        // ── Variant 2 Additional Info ────────────────────────────────────────────────
        $this->start_controls_section( 'section_v2_settings', [
            'label'     => esc_html__( 'Additional Info', 'woolentor' ),
            'condition' => [ 'variant' => 'v2', 'style' => 'modern' ],
        ] );

            $this->add_control( 'v2_scroll_text', [
                'label'   => esc_html__( 'Scroll Indicator Text', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Scroll to Explore', 'woolentor' ),
            ] );

            $this->add_control( 'divider_v2_stats', [
                'label'     => esc_html__( 'Stats Panel', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'v2_stat_1_n', [
                'label'   => esc_html__( 'Stat 1 — Value', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '100%',
            ] );

            $this->add_control( 'v2_stat_1_l', [
                'label'   => esc_html__( 'Stat 1 — Label', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Organic Linen', 'woolentor' ),
            ] );

            $this->add_control( 'v2_stat_2_n', [
                'label'   => esc_html__( 'Stat 2 — Value', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '12+',
            ] );

            $this->add_control( 'v2_stat_2_l', [
                'label'   => esc_html__( 'Stat 2 — Label', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'New Silhouettes', 'woolentor' ),
            ] );

            $this->add_control( 'v2_stat_3_n', [
                'label'   => esc_html__( 'Stat 3 — Value', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => 'SS26',
            ] );

            $this->add_control( 'v2_stat_3_l', [
                'label'   => esc_html__( 'Stat 3 — Label', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Live Now', 'woolentor' ),
            ] );

        $this->end_controls_section();
    }

    private function register_editorial_slides_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'divider_main', [
            'label'     => esc_html__( 'Content', 'woolentor' ),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'after',
        ] );

        $repeater->add_control( 'eyebrow', [
            'label'       => esc_html__( 'Kicker / Issue Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Issue 03 · The Sound Edit',
            'description' => esc_html__( 'Uppercase label above the title, e.g. "Issue 03 · The Sound Edit".', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'The quiet revolution in personal audio.',
            'description' => esc_html__( 'Serif headline. Use the Italic Highlight field to italicise key words in the accent colour.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading_highlight', [
            'label'       => esc_html__( 'Italic Highlight Words', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'revolution',
            'description' => esc_html__( 'Comma-separated words/phrases to render in italic accent colour.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'description', [
            'label'       => esc_html__( 'Intro Paragraph', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => "Five wireless headphones that prove silence is the new luxury. We tested them so you don't have to.",
            'label_block' => true,
        ] );

        $repeater->add_control( 'btn_primary_text', [
            'label'       => esc_html__( 'Primary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Read the edit', 'woolentor' ),
        ] );

        $repeater->add_control( 'btn_primary_url', [
            'label'         => esc_html__( 'Primary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/article',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'btn_secondary_text', [
            'label'       => esc_html__( 'Link Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Shop the issue', 'woolentor' ),
        ] );

        $repeater->add_control( 'btn_secondary_url', [
            'label'         => esc_html__( 'Link Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/shop',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'hero_image', [
            'label'   => esc_html__( 'Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => '' ],
        ] );

        $repeater->add_control( 'hero_image_alt', [
            'label'       => esc_html__( 'Image Alt Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'label_block' => true,
        ] );

        $repeater->add_control( 'hero_video_url', [
            'label'       => esc_html__( 'Video (v2)', 'woolentor' ),
            'type'        => Controls_Manager::MEDIA,
            'default'     => [ 'url' => '' ],
            'media_types' => ['video'],
            'description' => esc_html__( 'Video shown in the left panel of Variant 2.', 'woolentor' ),
        ] );

        // — Editorial Details (v1) —
        $repeater->add_control( 'divider_editorial', [
            'label'     => esc_html__( 'Editorial Details', 'woolentor' ),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'after',
        ] );

        $repeater->add_control( 'card2_eyebrow', [
            'label'       => esc_html__( 'Vertical Issue Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Spring · Vol III · MMXXVI',
            'description' => esc_html__( 'Shown rotated vertically along the left edge of the section.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'card2_subtitle', [
            'label'       => esc_html__( 'Photo Caption', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Photographed by A. Vasquez · Issue 03',
            'description' => esc_html__( 'Small text overlaid at the bottom-left of the photograph.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'card2_description', [
            'label'       => esc_html__( 'Meta Row', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Published 12 May 2026 · 9 min read · By Anna Vasquez',
            'description' => esc_html__( 'Article metadata shown below the CTA buttons, e.g. "Published 12 May 2026 · 9 min read · By Anna Vasquez".', 'woolentor' ),
            'label_block' => true,
        ] );

        $this->start_controls_section( 'section_editorial_slides', [
            'label'     => esc_html__( 'Items', 'woolentor' ),
            'condition' => [ 'style' => 'editorial', 'variant' => 'v1' ],
        ] );

            $this->add_control( 'editorial_slides', [
                'label'       => esc_html__( 'Slides', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'eyebrow'            => 'Issue 03 · The Sound Edit',
                        'heading'            => 'The quiet revolution in personal audio.',
                        'heading_highlight'  => 'revolution',
                        'description'        => "Five wireless headphones that prove silence is the new luxury. We tested them so you don't have to.",
                        'btn_primary_text'   => 'Read the edit',
                        'btn_primary_url'    => [ 'url' => '#' ],
                        'btn_secondary_text' => 'Shop the issue',
                        'btn_secondary_url'  => [ 'url' => '#' ],
                        'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                        'hero_image_alt'     => 'Wireless headphones editorial photograph',
                        'card2_eyebrow'      => 'Spring · Vol III · MMXXVI',
                        'card2_subtitle'     => 'Photographed by A. Vasquez · Issue 03',
                        'card2_description'  => 'Published 12 May 2026 · 9 min read · By Anna Vasquez',
                    ],
                ],
                'title_field' => '{{{ heading }}}',
            ] );

        $this->end_controls_section();
    }

    private function register_luxury_slides_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'eyebrow', [
            'label'       => esc_html__( 'Eyebrow Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading', [
            'label'       => esc_html__( 'Heading', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Timeless Elegance,<br>Redefined.',
            'description' => esc_html__( 'Use <br> for a line break.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading_highlight', [
            'label'       => esc_html__( 'Heading Highlight Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'description' => esc_html__( 'Comma-separated phrases to highlight. Must match text in the Heading field exactly.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'description', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => esc_html__( 'Curated collections crafted for those who appreciate the finest in life.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'btn_primary_text', [
            'label'       => esc_html__( 'Primary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Explore Collection', 'woolentor' ),
        ] );

        $repeater->add_control( 'btn_primary_url', [
            'label'         => esc_html__( 'Primary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/collection',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'btn_secondary_text', [
            'label'       => esc_html__( 'Secondary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
        ] );

        $repeater->add_control( 'btn_secondary_url', [
            'label'         => esc_html__( 'Secondary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'hero_image', [
            'label'   => esc_html__( 'Background Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => '' ],
        ] );

        $repeater->add_control( 'hero_image_alt', [
            'label'       => esc_html__( 'Background Image Alt Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'label_block' => true,
        ] );

        $this->start_controls_section( 'section_luxury_slides', [
            'label'     => esc_html__( 'Items', 'woolentor' ),
            'condition' => [ 'style' => 'luxury', 'variant' => 'v1' ],
        ] );

        $this->add_control( 'luxury_slides', [
            'label'       => esc_html__( 'Slides', 'woolentor' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => [
                [
                    'eyebrow'            => 'Spring Drop 2025',
                    'heading'            => 'Premium Sound,<br>Redefined Experience',
                    'heading_highlight'  => 'Experience',
                    'description'        => 'Wireless ANC headphones engineered for audiophiles who demand nothing less than perfection in every note.',
                    'btn_primary_text'   => 'Shop Audio',
                    'btn_primary_url'    => [ 'url' => '#' ],
                    'btn_secondary_text' => 'View All Products',
                    'btn_secondary_url'  => [ 'url' => '#' ],
                    'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'     => '',
                ],
                [
                    'eyebrow'            => 'Best Seller 2025',
                    'heading'            => 'Power Meets<br>Elegant Design',
                    'heading_highlight'  => 'Design',
                    'description'        => 'The ProBook Ultra X1 redefines what a laptop can be — OLED display, all-day battery, and an obsidian chassis built to impress.',
                    'btn_primary_text'   => 'Shop Laptops',
                    'btn_primary_url'    => [ 'url' => '#' ],
                    'btn_secondary_text' => 'View All Products',
                    'btn_secondary_url'  => [ 'url' => '#' ],
                    'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'     => '',
                ],
                [
                    'eyebrow'            => 'New Arrival',
                    'heading'            => 'Time Worn<br>With Purpose',
                    'heading_highlight'  => 'Purpose',
                    'description'        => 'The ChronoX Elite bridges Swiss craftsmanship with cutting-edge health and performance technology on your wrist.',
                    'btn_primary_text'   => 'Shop Wearables',
                    'btn_primary_url'    => [ 'url' => '#' ],
                    'btn_secondary_text' => 'View All Products',
                    'btn_secondary_url'  => [ 'url' => '#' ],
                    'hero_image'         => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'     => '',
                ],
            ],
            'title_field' => '{{{ heading }}}',
        ] );

        $this->end_controls_section();
    }

    private function register_luxury_v3_social_controls() {
        $this->start_controls_section( 'section_luxury_v3_social', [
            'label'     => esc_html__( 'Social Media Strip', 'woolentor' ),
            'condition' => [
                'style'   => 'luxury',
                'variant' => 'v3',
            ],
        ] );

        $this->add_control( 'v3_side_label', [
            'label'   => esc_html__( 'Side Label', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'New 2025', 'woolentor' ),
        ] );

        $this->add_control( 'v3_social_facebook', [
            'label'       => esc_html__( 'Facebook URL', 'woolentor' ),
            'type'        => Controls_Manager::URL,
            'placeholder' => 'https://facebook.com/',
            'default'     => [ 'url' => '#' ],
        ] );

        $this->add_control( 'v3_social_twitter', [
            'label'       => esc_html__( 'X / Twitter URL', 'woolentor' ),
            'type'        => Controls_Manager::URL,
            'placeholder' => 'https://x.com/',
            'default'     => [ 'url' => '#' ],
        ] );

        $this->add_control( 'v3_social_instagram', [
            'label'       => esc_html__( 'Instagram URL', 'woolentor' ),
            'type'        => Controls_Manager::URL,
            'placeholder' => 'https://instagram.com/',
            'default'     => [ 'url' => '#' ],
        ] );

        $this->add_control( 'v3_social_youtube', [
            'label'       => esc_html__( 'YouTube URL', 'woolentor' ),
            'type'        => Controls_Manager::URL,
            'placeholder' => 'https://youtube.com/',
            'default'     => [ 'url' => '#' ],
        ] );

        $this->end_controls_section();
    }

    private function register_magazine_slides_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'eyebrow', [
            'label'       => esc_html__( 'Category / Tag', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'FEATURED', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading', [
            'label'       => esc_html__( 'Headline', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Bold Stories,<br>Bigger Impact.',
            'description' => esc_html__( 'Use <br> for a line break.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'heading_highlight', [
            'label'       => esc_html__( 'Heading Highlight Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'description' => esc_html__( 'Comma-separated phrases to highlight. Must match text in the Headline field exactly.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'description', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => esc_html__( 'The stories that shape culture, curated for the curious.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'btn_primary_text', [
            'label'       => esc_html__( 'Primary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Read Now', 'woolentor' ),
        ] );

        $repeater->add_control( 'btn_primary_url', [
            'label'         => esc_html__( 'Primary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/article',
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'btn_secondary_text', [
            'label'       => esc_html__( 'Secondary Button Text', 'woolentor' ),
            'label_block' => true,
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
        ] );

        $repeater->add_control( 'btn_secondary_url', [
            'label'         => esc_html__( 'Secondary Button URL', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'show_external' => true,
            'default'       => [ 'url' => '#' ],
        ] );

        $repeater->add_control( 'hero_image', [
            'label'   => esc_html__( 'Feature Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => '' ],
        ] );

        $repeater->add_control( 'hero_image_alt', [
            'label'       => esc_html__( 'Feature Image Alt Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'label_block' => true,
        ] );

        $repeater->add_control( 'discount_badge', [
            'label'       => esc_html__( 'Discount Badge (v1 only)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '-32% TODAY',
            'label_block' => true,
        ] );

        $repeater->add_control( 'rating_text', [
            'label'       => esc_html__( 'Rating Text (v1 only)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '4.9 · 2,847 reviews',
            'label_block' => true,
        ] );

        $repeater->add_control( 'eyebrow_date', [
            'label'       => esc_html__( 'Eyebrow Date / Issue (v1 only)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Issue 247 · May 17',
            'label_block' => true,
        ] );

        $repeater->add_control( 'pill_tag', [
            'label'       => esc_html__( 'Pill Tag (v2 only)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Cover Story · N°02',
            'label_block' => true,
        ] );

        $repeater->add_control( 'spin_badge_text', [
            'label'       => esc_html__( 'Spin Badge Text (v2 only)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'description' => esc_html__( 'Rotating circular text. Leave blank to hide.', 'woolentor' ),
            'label_block' => true,
        ] );

        $this->start_controls_section( 'section_magazine_slides', [
            'label'     => esc_html__( 'Items', 'woolentor' ),
            'condition' => [ 'style' => 'magazine', 'variant' => 'v1' ],
        ] );

        $this->add_control( 'magazine_slides', [
            'label'       => esc_html__( 'Slides', 'woolentor' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => [
                [
                    'eyebrow'          => 'FEATURED',
                    'heading'          => 'Bold Stories,<br>Bigger Impact.',
                    'description'      => 'The stories that shape culture, curated for the curious.',
                    'btn_primary_text' => 'Read Now',
                    'btn_primary_url'  => [ 'url' => '#' ],
                    'hero_image'       => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                    'hero_image_alt'   => 'Magazine feature story image',
                ],
            ],
            'title_field' => '{{{ heading }}}',
        ] );

        $this->end_controls_section();
    }

    private function register_magazine_v1_sidebar_controls() {
        $this->start_controls_section( 'section_magazine_v1_sidebar', [
            'label'     => esc_html__( 'Sidebar Cards', 'woolentor' ),
            'condition' => [ 'style' => 'magazine', 'variant' => 'v1' ],
        ] );

        $this->add_control( 'mag_v1_c1_heading_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Card 1', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v1_c1_badge', [
            'label'       => esc_html__( 'Badge Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '⚡ Today\'s Deal',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c1_badge_style', [
            'label'   => esc_html__( 'Badge Style', 'woolentor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'light',
            'options' => [
                'light' => esc_html__( 'Light (yellow bg, card)', 'woolentor' ),
                'dark'  => esc_html__( 'Dark', 'woolentor' ),
                'red'   => esc_html__( 'Red', 'woolentor' ),
            ],
        ] );
        $this->add_control( 'mag_v1_c1_image', [
            'label'   => esc_html__( 'Card Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v1_c1_heading', [
            'label'       => esc_html__( 'Heading', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'SAVE 40%<br>ON GAMING',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c1_desc', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Curated bundle: keyboard, mouse, headset, and mousepad — all RGB, all ready.',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c1_cta_text', [
            'label'       => esc_html__( 'CTA Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Shop the bundle',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c1_cta_url', [
            'label'   => esc_html__( 'CTA URL', 'woolentor' ),
            'type'    => Controls_Manager::URL,
            'default' => [ 'url' => '#' ],
        ] );

        $this->add_control( 'mag_v1_c2_heading_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Card 2', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v1_c2_badge', [
            'label'       => esc_html__( 'Badge Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '🔥 Hot This Week',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c2_badge_style', [
            'label'   => esc_html__( 'Badge Style', 'woolentor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'red',
            'options' => [
                'light' => esc_html__( 'Light (yellow bg, card)', 'woolentor' ),
                'dark'  => esc_html__( 'Dark', 'woolentor' ),
                'red'   => esc_html__( 'Red', 'woolentor' ),
            ],
        ] );
        $this->add_control( 'mag_v1_c2_image', [
            'label'   => esc_html__( 'Card Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v1_c2_heading', [
            'label'       => esc_html__( 'Heading', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'LAPTOPS<br>FROM $799',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c2_desc', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Back-to-work picks: thin, light, all-day battery. Hand-tested by our editors.',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c2_cta_text', [
            'label'       => esc_html__( 'CTA Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Shop laptops',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v1_c2_cta_url', [
            'label'   => esc_html__( 'CTA URL', 'woolentor' ),
            'type'    => Controls_Manager::URL,
            'default' => [ 'url' => '#' ],
        ] );

        $this->end_controls_section();
    }

    private function register_magazine_v2_side_controls() {
        $this->start_controls_section( 'section_magazine_v2_side', [
            'label'     => esc_html__( 'Side Panel', 'woolentor' ),
            'condition' => [ 'style' => 'magazine', 'variant' => 'v2' ],
        ] );

        $this->add_control( 'mag_v2_prod_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Product Card', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v2_prod_image', [
            'label'   => esc_html__( 'Product Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v2_prod_chip1', [
            'label'       => esc_html__( 'Chip 1 (coral)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Trending',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_prod_chip2', [
            'label'       => esc_html__( 'Chip 2 (glass)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'New In',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_prod_cat', [
            'label'       => esc_html__( 'Category', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Ready-to-Wear',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_prod_name', [
            'label'       => esc_html__( 'Product Name', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Relaxed Linen Blazer',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_prod_price', [
            'label'       => esc_html__( 'Price', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '$245',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_prod_orig_price', [
            'label'       => esc_html__( 'Original Price (crossed out)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '$295',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_prod_btn_url', [
            'label'       => esc_html__( 'Action Button URL (+ button)', 'woolentor' ),
            'type'        => Controls_Manager::URL,
            'description' => esc_html__( 'Leave blank to hide the + button.', 'woolentor' ),
            'default'     => [ 'url' => '#' ],
        ] );

        $this->add_control( 'mag_v2_stat_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Stat Card', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v2_stat_eyebrow', [
            'label'       => esc_html__( 'Eyebrow Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Inside the Issue',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_stat_badge', [
            'label'       => esc_html__( 'Badge', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'N°02',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_stat_num', [
            'label'       => esc_html__( 'Main Number', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '48',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_stat_unit', [
            'label'       => esc_html__( 'Unit Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Curated looks',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_stat_1_val', [
            'label'   => esc_html__( 'Sub-stat 1 Value', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => '12',
        ] );
        $this->add_control( 'mag_v2_stat_1_label', [
            'label'   => esc_html__( 'Sub-stat 1 Label', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => 'Designers',
        ] );
        $this->add_control( 'mag_v2_stat_2_val', [
            'label'   => esc_html__( 'Sub-stat 2 Value', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => '06',
        ] );
        $this->add_control( 'mag_v2_stat_2_label', [
            'label'   => esc_html__( 'Sub-stat 2 Label', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => 'Stories',
        ] );

        $this->add_control( 'mag_v2_look_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Lookbook Card', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v2_look_image', [
            'label'   => esc_html__( 'Lookbook Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v2_look_cat', [
            'label'       => esc_html__( 'Category Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Lookbook · Resort 26',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_look_idx', [
            'label'   => esc_html__( 'Index Number', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => '01',
        ] );
        $this->add_control( 'mag_v2_look_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Coastal Hours',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_look_meta', [
            'label'       => esc_html__( 'Meta (dot-separated)', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '8 Looks · Resort Film',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_look_cta_text', [
            'label'       => esc_html__( 'CTA Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'View the edit',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v2_look_cta_url', [
            'label'   => esc_html__( 'CTA URL', 'woolentor' ),
            'type'    => Controls_Manager::URL,
            'default' => [ 'url' => '#' ],
        ] );

        $this->end_controls_section();
    }

    private function register_magazine_v3_panels_controls() {
        $this->start_controls_section( 'section_magazine_v3_panels', [
            'label'     => esc_html__( 'Side Panels', 'woolentor' ),
            'condition' => [ 'style' => 'magazine', 'variant' => 'v3' ],
        ] );

        $this->add_control( 'mag_v3_promo_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Promo Card (middle)', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v3_promo_image', [
            'label'   => esc_html__( 'Promo Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v3_promo_label', [
            'label'       => esc_html__( 'Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Shop by Room · Bedroom',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_promo_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Bedroom Set<br>Starting at $290',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_promo_btn_text', [
            'label'       => esc_html__( 'Button Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Shop the Room',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_promo_btn_url', [
            'label'   => esc_html__( 'Button URL', 'woolentor' ),
            'type'    => Controls_Manager::URL,
            'default' => [ 'url' => '#' ],
        ] );

        $this->add_control( 'mag_v3_spot1_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Spot 1 (top right)', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v3_spot1_url', [
            'label'   => esc_html__( 'Link URL', 'woolentor' ),
            'type'    => Controls_Manager::URL,
            'default' => [ 'url' => '#' ],
        ] );
        $this->add_control( 'mag_v3_spot1_image', [
            'label'   => esc_html__( 'Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v3_spot1_label', [
            'label'       => esc_html__( 'Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => "Editor's List",
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_spot1_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Linen Lounge<br>Chair',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_spot1_price', [
            'label'       => esc_html__( 'Price Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Starting at $890',
            'label_block' => true,
        ] );

        $this->add_control( 'mag_v3_spot2_note', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => '<strong>' . esc_html__( 'Spot 2 (bottom right)', 'woolentor' ) . '</strong>',
            'content_classes' => 'elementor-control-raw-html elementor-panel-alert',
        ] );
        $this->add_control( 'mag_v3_spot2_url', [
            'label'   => esc_html__( 'Link URL', 'woolentor' ),
            'type'    => Controls_Manager::URL,
            'default' => [ 'url' => '#' ],
        ] );
        $this->add_control( 'mag_v3_spot2_image', [
            'label'   => esc_html__( 'Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
        ] );
        $this->add_control( 'mag_v3_spot2_label', [
            'label'       => esc_html__( 'Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'New Arrival',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_spot2_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => 'Solid Oak<br>Dining Table',
            'label_block' => true,
        ] );
        $this->add_control( 'mag_v3_spot2_price', [
            'label'       => esc_html__( 'Price Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => 'Starting at $1,450',
            'label_block' => true,
        ] );

        $this->end_controls_section();
    }

    // ── Magazine Style Controls ───────────────────────────────────────────────

    private function register_magazine_v1_style_controls() {
        $cond_v1 = [ 'style' => 'magazine', 'variant' => 'v1' ];

        // ── Main Hero ────────────────────────────────────────────────────────
        $this->start_controls_section( 'mag_v1_style_main', [
            'label'     => esc_html__( 'Magazine V1 — Main Hero', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v1,
        ] );

            $this->add_control( 'mag_v1_overlay_color', [
                'label'     => esc_html__( 'Overlay Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-overlay' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_eyebrow_heading', [
                'label'     => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v1_ping_color', [
                'label'     => esc_html__( 'Ping Dot Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-ping' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_eyebrow_color', [
                'label'     => esc_html__( 'Label & Date Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-eyebrow-label, {{WRAPPER}} .wl-hm1-eyebrow-date' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_title_heading', [
                'label'     => esc_html__( 'Heading', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v1_title_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'mag_v1_title_typography',
                'selector' => '{{WRAPPER}} .wl-hm1-title',
            ] );

            $this->add_control( 'mag_v1_highlight_color', [
                'label'     => esc_html__( 'Highlight Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-title .wl-hl' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_highlight_underline', [
                'label'     => esc_html__( 'Highlight Underline Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-title .wl-hl::after' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_desc_heading', [
                'label'     => esc_html__( 'Description', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v1_desc_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-deck' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_badge_heading', [
                'label'     => esc_html__( 'Discount Badge', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v1_badge_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-discount' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_badge_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-discount' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_rating_heading', [
                'label'     => esc_html__( 'Rating', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v1_stars_color', [
                'label'     => esc_html__( 'Stars Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-stars' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_rating_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-rating span:not(.wl-hm1-stars)' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_btn_heading', [
                'label'     => esc_html__( 'Buttons', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->start_controls_tabs( 'mag_v1_btn_tabs' );

                $this->start_controls_tab( 'mag_v1_btn_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'mag_v1_btn_deal_bg', [
                        'label'     => esc_html__( 'Deal Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-deal' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v1_btn_deal_color', [
                        'label'     => esc_html__( 'Deal Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-deal' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v1_btn_ghost_color', [
                        'label'     => esc_html__( 'Ghost Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-ghost' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v1_btn_ghost_border', [
                        'label'     => esc_html__( 'Ghost Button Border', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-ghost' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'mag_v1_btn_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'mag_v1_btn_deal_bg_hover', [
                        'label'     => esc_html__( 'Deal Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-deal:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v1_btn_deal_color_hover', [
                        'label'     => esc_html__( 'Deal Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-deal:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v1_btn_ghost_color_hover', [
                        'label'     => esc_html__( 'Ghost Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-ghost:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v1_btn_ghost_bg_hover', [
                        'label'     => esc_html__( 'Ghost Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm1-btn-ghost:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // ── Sidebar Cards ────────────────────────────────────────────────────
        $this->start_controls_section( 'mag_v1_style_cards', [
            'label'     => esc_html__( 'Magazine V1 — Sidebar Cards', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v1,
        ] );

            $this->add_control( 'mag_v1_card_heading_color', [
                'label'     => esc_html__( 'Card Heading Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-card-heading' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'mag_v1_card_heading_typography',
                'selector' => '{{WRAPPER}} .wl-hm1-card-heading',
            ] );

            $this->add_control( 'mag_v1_card_desc_color', [
                'label'     => esc_html__( 'Card Description Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-card-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_card_cta_color', [
                'label'     => esc_html__( 'Card CTA Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-card-cta' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_chip_heading', [
                'label'     => esc_html__( 'Chips', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v1_chip_light_bg', [
                'label'     => esc_html__( 'Light Chip Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-chip--light' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_chip_light_color', [
                'label'     => esc_html__( 'Light Chip Text', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-chip--light' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_chip_dark_bg', [
                'label'     => esc_html__( 'Dark Chip Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-chip--dark' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_chip_dark_color', [
                'label'     => esc_html__( 'Dark Chip Text', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-chip--dark' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_chip_red_bg', [
                'label'     => esc_html__( 'Red Chip Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-chip--red' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v1_chip_red_color', [
                'label'     => esc_html__( 'Red Chip Text', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm1-chip--red' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();
    }

    private function register_magazine_v2_style_controls() {
        $cond_v2 = [ 'style' => 'magazine', 'variant' => 'v2' ];

        // ── Cover Panel ──────────────────────────────────────────────────────
        $this->start_controls_section( 'mag_v2_style_cover', [
            'label'     => esc_html__( 'Magazine V2 — Cover Panel', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v2,
        ] );

            $this->add_control( 'mag_v2_overlay_color', [
                'label'     => esc_html__( 'Overlay Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-cover-overlay' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pill_heading', [
                'label'     => esc_html__( 'Pill Tag', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v2_pill_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pill-tag' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pill_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pill-tag' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pill_dot_color', [
                'label'     => esc_html__( 'Dot Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pill-dot' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_cover_text_heading', [
                'label'     => esc_html__( 'Cover Text', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v2_eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_title_color', [
                'label'     => esc_html__( 'Title Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'mag_v2_title_typography',
                'selector' => '{{WRAPPER}} .wl-hm2-title',
            ] );

            $this->add_control( 'mag_v2_sub_color', [
                'label'     => esc_html__( 'Subtitle Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-sub' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_cover_btn_heading', [
                'label'     => esc_html__( 'Cover Buttons', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->start_controls_tabs( 'mag_v2_cover_btn_tabs' );

                $this->start_controls_tab( 'mag_v2_cover_btn_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'mag_v2_btn_coral_bg', [
                        'label'     => esc_html__( 'Primary Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-coral' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_btn_coral_color', [
                        'label'     => esc_html__( 'Primary Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-coral' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_btn_ghost_color', [
                        'label'     => esc_html__( 'Ghost Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-ghost' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_btn_ghost_border', [
                        'label'     => esc_html__( 'Ghost Button Border', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-ghost' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'mag_v2_cover_btn_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'mag_v2_btn_coral_bg_hover', [
                        'label'     => esc_html__( 'Primary Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-coral:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_btn_coral_color_hover', [
                        'label'     => esc_html__( 'Primary Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-coral:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_btn_ghost_color_hover', [
                        'label'     => esc_html__( 'Ghost Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-ghost:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_btn_ghost_bg_hover', [
                        'label'     => esc_html__( 'Ghost Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-btn-ghost:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_control( 'mag_v2_spin_heading', [
                'label'     => esc_html__( 'Spin Badge', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v2_spin_text_color', [
                'label'     => esc_html__( 'Ring Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-spin-ring text' => 'fill: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_spin_core_bg', [
                'label'     => esc_html__( 'Core Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-spin-core' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_spin_core_color', [
                'label'     => esc_html__( 'Core Icon Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-spin-core' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // ── Product Card ─────────────────────────────────────────────────────
        $this->start_controls_section( 'mag_v2_style_product', [
            'label'     => esc_html__( 'Magazine V2 — Product Card', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v2,
        ] );

            $this->add_control( 'mag_v2_chip_coral_bg', [
                'label'     => esc_html__( 'Coral Chip Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-chip-coral' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_chip_coral_color', [
                'label'     => esc_html__( 'Coral Chip Text', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-chip-coral' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_chip_light_bg', [
                'label'     => esc_html__( 'Light Chip Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-chip-light' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_chip_light_color', [
                'label'     => esc_html__( 'Light Chip Text', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-chip-light' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pbody_heading', [
                'label'     => esc_html__( 'Card Body', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v2_pbody_bg', [
                'label'     => esc_html__( 'Body Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pbody' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pcat_color', [
                'label'     => esc_html__( 'Category Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pcat' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pname_color', [
                'label'     => esc_html__( 'Product Name Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pname' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_pprice_color', [
                'label'     => esc_html__( 'Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-pprice' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_padd_heading', [
                'label'     => esc_html__( 'Action Button (+)', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->start_controls_tabs( 'mag_v2_padd_tabs' );

                $this->start_controls_tab( 'mag_v2_padd_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'mag_v2_padd_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-padd' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_padd_color', [
                        'label'     => esc_html__( 'Icon Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-padd' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'mag_v2_padd_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'mag_v2_padd_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-padd:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v2_padd_color_hover', [
                        'label'     => esc_html__( 'Icon Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm2-padd:hover' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // ── Stat & Lookbook Cards ────────────────────────────────────────────
        $this->start_controls_section( 'mag_v2_style_stat_look', [
            'label'     => esc_html__( 'Magazine V2 — Stat & Lookbook', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v2,
        ] );

            $this->add_control( 'mag_v2_stat_heading', [
                'label' => esc_html__( 'Stat Card', 'woolentor' ),
                'type'  => Controls_Manager::HEADING,
            ] );

            $this->add_control( 'mag_v2_ms_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-mini-stat' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ms_eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ms-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ms_badge_color', [
                'label'     => esc_html__( 'Badge Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ms-badge' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ms_badge_bg', [
                'label'     => esc_html__( 'Badge Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ms-badge' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ms_num_color', [
                'label'     => esc_html__( 'Big Number Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-num' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ms_unit_color', [
                'label'     => esc_html__( 'Unit Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ms-unit' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ms_label_color', [
                'label'     => esc_html__( 'Footer Labels Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-hm2-ms-cell b'    => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-hm2-ms-cell span' => 'color: {{VALUE}};',
                ],
            ] );

            $this->add_control( 'mag_v2_look_heading', [
                'label'     => esc_html__( 'Lookbook Card', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v2_ml_cat_color', [
                'label'     => esc_html__( 'Category Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-cat' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ml_idx_color', [
                'label'     => esc_html__( 'Index Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-idx' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ml_title_color', [
                'label'     => esc_html__( 'Title Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ml_meta_color', [
                'label'     => esc_html__( 'Meta Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-meta' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ml_cta_color', [
                'label'     => esc_html__( 'CTA Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-cta span:first-child' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ml_arrow_bg', [
                'label'     => esc_html__( 'Arrow Button Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-arrow' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v2_ml_arrow_color', [
                'label'     => esc_html__( 'Arrow Button Icon Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm2-ml-arrow' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();
    }

    private function register_magazine_v3_style_controls() {
        $cond_v3 = [ 'style' => 'magazine', 'variant' => 'v3' ];

        // ── Feature Panel ────────────────────────────────────────────────────
        $this->start_controls_section( 'mag_v3_style_feature', [
            'label'     => esc_html__( 'Magazine V3 — Feature Panel', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v3,
        ] );

            $this->add_control( 'mag_v3_overlay_color', [
                'label'     => esc_html__( 'Overlay Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-shade' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_title_color', [
                'label'     => esc_html__( 'Heading Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'mag_v3_title_typography',
                'selector' => '{{WRAPPER}} .wl-hm3-title',
            ] );

            $this->add_control( 'mag_v3_desc_color', [
                'label'     => esc_html__( 'Description Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_btn_heading', [
                'label'     => esc_html__( 'Feature Button', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->start_controls_tabs( 'mag_v3_btn_tabs' );

                $this->start_controls_tab( 'mag_v3_btn_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'mag_v3_btn_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm3-btn' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v3_btn_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm3-btn' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'mag_v3_btn_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'mag_v3_btn_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm3-btn:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'mag_v3_btn_color_hover', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-hm3-btn:hover' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // ── Promo & Spot Cards ───────────────────────────────────────────────
        $this->start_controls_section( 'mag_v3_style_promo_spots', [
            'label'     => esc_html__( 'Magazine V3 — Promo & Spots', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $cond_v3,
        ] );

            $this->add_control( 'mag_v3_promo_heading', [
                'label' => esc_html__( 'Promo Card', 'woolentor' ),
                'type'  => Controls_Manager::HEADING,
            ] );

            $this->add_control( 'mag_v3_promo_label_color', [
                'label'     => esc_html__( 'Label Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-promo-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_promo_title_color', [
                'label'     => esc_html__( 'Title Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-promo-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_promo_btn_bg', [
                'label'     => esc_html__( 'Button Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-promo-btn' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_promo_btn_color', [
                'label'     => esc_html__( 'Button Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-promo-btn' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_spots_heading', [
                'label'     => esc_html__( 'Spot Cards', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'mag_v3_spot_overlay_color', [
                'label'     => esc_html__( 'Overlay Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-spot-shade' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_spot_label_color', [
                'label'     => esc_html__( 'Label Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-spot-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_spot_title_color', [
                'label'     => esc_html__( 'Title Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-spot-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_spot_title_hover', [
                'label'     => esc_html__( 'Title Hover Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-spot:hover .wl-hm3-spot-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'mag_v3_spot_price_color', [
                'label'     => esc_html__( 'Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-hm3-spot-price' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();
    }

    // ── Pro variant gating ────────────────────────────────────────────────────

    /**
     * Returns true when the style+variant combo is a Pro-only feature.
     * This file only loads when Pro is NOT active, so no is_pro_active() check needed.
     */
    /** Pro variant map for this widget — passed to Style_Pack_Manager helpers. */
    private function get_pro_map() {
        return [
            'editorial' => [ 'v2', 'v3' ],
            'luxury'    => [ 'v2', 'v3' ],
            'magazine'  => [ 'v2', 'v3' ],
        ];
    }

    /** Content-tab notice section shown when a pro variant is selected. */
    private function register_pro_content_notice() {
        $condition = [
            'style'   => [ 'editorial', 'luxury', 'magazine' ],
            'variant' => [ 'v2', 'v3' ],
        ];
        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Items', 'woolentor' ),
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_upgrade_notice', $condition );
        $this->end_controls_section();
    }

    /** Style-tab notice section shown when a pro variant is selected. */
    private function register_pro_style_notice() {
        $condition = [
            'style'   => [ 'editorial', 'luxury', 'magazine' ],
            'variant' => [ 'v2', 'v3' ],
        ];
        $this->start_controls_section( 'section_pro_style_notice', [
            'label'     => esc_html__( 'Pro Feature', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_style_upgrade_notice', $condition, [
                'message' => __( 'Style controls are only available in ShopLentor Pro. Upgrade to customize colors, typography, and more for this variant.', 'woolentor' ),
            ] );
        $this->end_controls_section();
    }

    /**
     * Renders the template with demo content in Elementor editor mode so the
     * user can see what the pro variant looks like before upgrading.
     * Non-magazine styles get 3 demo slides with slider enabled.
     * Magazine styles render a single static grid (no slider).
     */
    private function render_pro_preview( $style, $variant, $settings = [] ) {
        $base_dir = __DIR__ . '/templates';
        $template = \WooLentor\Style_Pack_Manager::resolve_template( $base_dir, $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Hero Banner' );
            return;
        }

        $placeholder = \Elementor\Utils::get_placeholder_image_src();

        // Magazine variants are static grids — slider never applies.
        // For editorial/luxury pro variants, read the real panel settings so the user
        // can explore slider on/off, arrows, dots, fade, autoplay etc. in editor mode.
        $supports_slider = ( 'magazine' !== $style );
        $slider_enabled  = $supports_slider && isset( $settings['enable_slider'] ) && 'yes' === $settings['enable_slider'];
        $v2_no_slider    = false;

        // Luxury V2 is a dual-panel (left + right) layout — 2 slides fills it perfectly.
        // All other slider styles use 3 slides. Magazine uses 1 (static grid).
        $is_dual_panel    = ( 'luxury' === $style && 'v2' === $variant );
        $all_demo_slides  = [
            [
                'eyebrow' => __( 'Pro Feature Preview', 'woolentor' ),
                'heading' => __( 'Upgrade to Unlock This Variant', 'woolentor' ),
                'btn_p'   => __( 'Upgrade to Pro', 'woolentor' ),
                'btn_s'   => __( 'Learn More', 'woolentor' ),
            ],
            [
                'eyebrow' => __( 'Exclusive Layout', 'woolentor' ),
                'heading' => __( 'Premium Design Ready to Use', 'woolentor' ),
                'btn_p'   => __( 'Get Pro', 'woolentor' ),
                'btn_s'   => __( 'Explore More', 'woolentor' ),
            ],
            [
                'eyebrow' => __( 'Pro Variant', 'woolentor' ),
                'heading' => __( 'Fully Customizable with Pro', 'woolentor' ),
                'btn_p'   => __( 'Unlock Now', 'woolentor' ),
                'btn_s'   => __( 'See Details', 'woolentor' ),
            ],
        ];
        $demo_slides_data = $supports_slider
            ? array_slice( $all_demo_slides, 0, $is_dual_panel ? 2 : 3 )
            : [ $all_demo_slides[0] ];

        $slide_total = count( $demo_slides_data );

        if ( $slider_enabled ) {
            // Use real panel values so the user can explore all slider options in editor mode.
            $slider_settings = wp_json_encode( [
                'arrows'         => 'yes' === ( $settings['slider_arrows']         ?? 'yes' ),
                'dots'           => 'yes' === ( $settings['slider_dots']           ?? 'yes' ),
                'infinite'       => 'yes' === ( $settings['slider_infinite']       ?? 'yes' ),
                'fade'           => 'yes' === ( $settings['slider_fade']           ?? '' ),
                'autoplay'       => 'yes' === ( $settings['slider_autoplay']       ?? 'yes' ),
                'autoplay_speed' => intval( $settings['slider_autoplay_speed']     ?? 5000 ),
                'speed'          => intval( $settings['slider_speed']              ?? 600 ),
                'pause_on_hover' => 'yes' === ( $settings['slider_pause_on_hover'] ?? 'yes' ),
                'items'          => intval( $settings['sl_items']                  ?? 1 ),
                'scroll'         => intval( $settings['sl_scroll']                 ?? 1 ),
                'tablet_width'   => intval( $settings['sl_tablet_width']           ?? 768 ),
                'tablet_items'   => intval( $settings['sl_tablet_items']           ?? 1 ),
                'tablet_scroll'  => intval( $settings['sl_tablet_scroll']          ?? 1 ),
                'mobile_width'   => intval( $settings['sl_mobile_width']           ?? 480 ),
                'mobile_items'   => intval( $settings['sl_mobile_items']           ?? 1 ),
                'mobile_scroll'  => intval( $settings['sl_mobile_scroll']          ?? 1 ),
            ] );
            $slider_attr = ' data-wl-slider="true" data-slider-settings=\'' . esc_attr( $slider_settings ) . '\'';
        } else {
            $slider_attr = '';
        }

        // Magazine v2 demo data.
        $mag_v2_prod = [
            'image' => $placeholder, 'chip1' => 'NEW', 'chip2' => 'LIMITED',
            'cat'   => esc_html__( 'Fashion', 'woolentor' ),
            'name'  => esc_html__( 'Premium Product', 'woolentor' ),
            'price' => '$199', 'orig_price' => '$299', 'btn_url' => '#',
        ];
        $mag_v2_stat = [
            'eyebrow' => 'STATS', 'badge' => '+50%', 'num' => '10K',
            'unit'    => esc_html__( 'Sales', 'woolentor' ),
            '1_val'   => '4.8', '1_label' => esc_html__( 'Rating', 'woolentor' ),
            '2_val'   => '98%', '2_label' => esc_html__( 'Satisfaction', 'woolentor' ),
        ];
        $mag_v2_look = [
            'image'    => $placeholder, 'cat' => 'LOOKBOOK', 'idx' => '01',
            'title'    => esc_html__( 'Spring Collection', 'woolentor' ),
            'meta'     => esc_html__( '12 Items', 'woolentor' ),
            'cta_text' => esc_html__( 'Explore', 'woolentor' ), 'cta_url' => '#',
        ];

        // Magazine v3 demo data.
        $mag_v3_promo = [
            'image'    => $placeholder, 'label' => 'FEATURED',
            'title'    => esc_html__( 'Pro Collection', 'woolentor' ),
            'btn_text' => esc_html__( 'Shop Now', 'woolentor' ), 'btn_url' => '#',
        ];
        $mag_v3_spot1 = [
            'image' => $placeholder, 'label' => 'NEW',
            'title' => esc_html__( 'Product One', 'woolentor' ), 'price' => '$89', 'url' => '#',
        ];
        $mag_v3_spot2 = [
            'image' => $placeholder, 'label' => 'SALE',
            'title' => esc_html__( 'Product Two', 'woolentor' ), 'price' => '$129', 'url' => '#',
        ];

        // Luxury v3 social strip — empty for demo.
        $v3_side_label = $v3_social_facebook = $v3_social_twitter = $v3_social_instagram = $v3_social_youtube = '';

        $banner_class = 'wl-hero-banner wl-hero-' . esc_attr( $style ) . ' wl-hero-' . esc_attr( $style ) . '-' . esc_attr( $variant );

        echo '<div style="position:relative;" data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="' . esc_attr( $banner_class ) . '"' . $slider_attr . '>';

        foreach ( $demo_slides_data as $index => $demo ) {
            $eyebrow            = esc_html( $demo['eyebrow'] );
            $heading            = esc_html( $demo['heading'] );
            $heading_lines      = array_values( array_filter( array_map( 'trim', explode( ' ', $demo['heading'], 3 ) ) ) );
            $description        = esc_html__( 'Get ShopLentor Pro to use this premium design with your own content.', 'woolentor' );
            $btn_primary_text   = esc_html( $demo['btn_p'] );
            $btn_primary_url    = '#';
            $btn_secondary_text = esc_html( $demo['btn_s'] );
            $btn_secondary_url  = '#';
            $hero_image_url     = $placeholder;
            $hero_image_alt     = esc_attr__( 'Pro Variant Preview', 'woolentor' );
            $hero_video_url     = '';
            $card2_eyebrow      = '';
            $card2_subtitle     = '';
            $card2_image_url    = $placeholder;
            $card2_image_alt    = '';
            $card2_video_url    = '';
            $card2_watermark    = '';
            $card2_description  = '';
            $card2_btn_text     = '';
            $card2_btn_url      = '#';
            $discount_badge     = '';
            $rating_text        = '';
            $eyebrow_date       = '';
            $pill_tag           = esc_html__( 'Pro', 'woolentor' );
            $spin_badge_text    = '';
            $slide_class        = 'wl-hero-slide' . ( $index > 0 ? ' wl-hero-slide--hidden' : '' );

            echo '<div class="' . esc_attr( $slide_class ) . '">';
            include $template;
            echo '</div>';
        }

        echo '</div>'; // .wl-hero-banner
        echo '<div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.78);color:#fff;'
            . 'padding:4px 10px;border-radius:3px;font-size:11px;font-weight:700;pointer-events:none;z-index:99;">'
            . esc_html__( 'Pro — Preview Only', 'woolentor' )
            . '</div>';
        echo '</div>';
    }

    protected function render() {
        if ( ! class_exists( '\WooLentor\Style_Pack_Manager' ) ) {
            echo '<p>' . esc_html__( 'Style Pack Manager not found.', 'woolentor' ) . '</p>';
            return;
        }

        $settings       = $this->get_settings_for_display();
        $style          = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant        = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        // Pro variant gate — this file is only loaded when Pro is NOT active.
        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            $this->render_pro_preview( $style, $variant, $settings );
            return;
        }

        $slider_enabled = isset( $settings['enable_slider'] ) && 'yes' === $settings['enable_slider'];
        $slides         = $settings["{$style}_slides"] ?? [];

        if ( empty( $slides ) ) {
            return;
        }

        // Magazine layouts are static full-page grids — only the first slide drives the main panel.
        if ( 'magazine' === $style ) {
            $slides         = array_slice( $slides, 0, 1 );
            $slider_enabled = false;
        }

        $base_dir = __DIR__ . '/templates';
        $template = \WooLentor\Style_Pack_Manager::resolve_template( $base_dir, $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Hero banner template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $banner_class = 'wl-hero-banner wl-hero-' . esc_attr( $style ) . ' wl-hero-' . esc_attr( $style ) . '-' . esc_attr( $variant );

        // v2 without slider: all slides visible as stacked full-viewport sections.
        $v2_no_slider = ( 'v2' === $variant && ! $slider_enabled );
        if ( $v2_no_slider && count( $slides ) > 1 ) {
            $banner_class .= ' wl-hero-v2-stacked';
        }

        if ( $slider_enabled ) {
            $slider_settings = wp_json_encode( [
                'arrows'         => 'yes' === ( $settings['slider_arrows']         ?? 'yes' ),
                'dots'           => 'yes' === ( $settings['slider_dots']           ?? 'yes' ),
                'infinite'       => 'yes' === ( $settings['slider_infinite']       ?? 'yes' ),
                'fade'           => 'yes' === ( $settings['slider_fade']           ?? '' ),
                'autoplay'       => 'yes' === ( $settings['slider_autoplay']       ?? 'yes' ),
                'autoplay_speed' => intval( $settings['slider_autoplay_speed']     ?? 5000 ),
                'speed'          => intval( $settings['slider_speed']              ?? 600 ),
                'pause_on_hover' => 'yes' === ( $settings['slider_pause_on_hover'] ?? 'yes' ),
                'items'          => intval( $settings['sl_items']                  ?? 1 ),
                'scroll'         => intval( $settings['sl_scroll']                 ?? 1 ),
                'tablet_width'   => intval( $settings['sl_tablet_width']           ?? 768 ),
                'tablet_items'   => intval( $settings['sl_tablet_items']           ?? 1 ),
                'tablet_scroll'  => intval( $settings['sl_tablet_scroll']          ?? 1 ),
                'mobile_width'   => intval( $settings['sl_mobile_width']           ?? 480 ),
                'mobile_items'   => intval( $settings['sl_mobile_items']           ?? 1 ),
                'mobile_scroll'  => intval( $settings['sl_mobile_scroll']          ?? 1 ),
            ] );
            $slider_attr = ' data-wl-slider="true" data-slider-settings=\'' . esc_attr( $slider_settings ) . '\'';
        } else {
            $slider_attr = '';
        }

        // Total slide count — used by v3 template for counter and controls visibility.
        $slide_total = count( $slides );

        // v2 widget-level settings — same value for every slide, read once before the loop.
        $scroll_text = esc_html( $settings['v2_scroll_text'] ?? __( 'Scroll to Explore', 'woolentor' ) );
        $v2_stat_1_n = esc_html( $settings['v2_stat_1_n'] ?? '' );
        $v2_stat_1_l = esc_html( $settings['v2_stat_1_l'] ?? '' );
        $v2_stat_2_n = esc_html( $settings['v2_stat_2_n'] ?? '' );
        $v2_stat_2_l = esc_html( $settings['v2_stat_2_l'] ?? '' );
        $v2_stat_3_n = esc_html( $settings['v2_stat_3_n'] ?? '' );
        $v2_stat_3_l = esc_html( $settings['v2_stat_3_l'] ?? '' );

        // Magazine v1: sidebar cards.
        $mag_v1_c1 = $mag_v1_c2 = [];
        if ( 'magazine' === $style && 'v1' === $variant ) {
            $mag_v1_c1 = [
                'badge'       => esc_html( $settings['mag_v1_c1_badge'] ?? '' ),
                'badge_style' => sanitize_key( $settings['mag_v1_c1_badge_style'] ?? 'light' ),
                'image'       => esc_url( $settings['mag_v1_c1_image']['url'] ?? '' ),
                'heading'     => wp_kses_post( $settings['mag_v1_c1_heading'] ?? '' ),
                'desc'        => wp_kses_post( $settings['mag_v1_c1_desc'] ?? '' ),
                'cta_text'    => esc_html( $settings['mag_v1_c1_cta_text'] ?? '' ),
                'cta_url'     => esc_url( $settings['mag_v1_c1_cta_url']['url'] ?? '#' ),
            ];
            $mag_v1_c2 = [
                'badge'       => esc_html( $settings['mag_v1_c2_badge'] ?? '' ),
                'badge_style' => sanitize_key( $settings['mag_v1_c2_badge_style'] ?? 'red' ),
                'image'       => esc_url( $settings['mag_v1_c2_image']['url'] ?? '' ),
                'heading'     => wp_kses_post( $settings['mag_v1_c2_heading'] ?? '' ),
                'desc'        => wp_kses_post( $settings['mag_v1_c2_desc'] ?? '' ),
                'cta_text'    => esc_html( $settings['mag_v1_c2_cta_text'] ?? '' ),
                'cta_url'     => esc_url( $settings['mag_v1_c2_cta_url']['url'] ?? '#' ),
            ];
        }

        // Magazine v2: side panel (product card + stat card + lookbook card).
        $mag_v2_prod = $mag_v2_stat = $mag_v2_look = [];
        if ( 'magazine' === $style && 'v2' === $variant ) {
            $mag_v2_prod = [
                'image'      => esc_url( $settings['mag_v2_prod_image']['url'] ?? '' ),
                'chip1'      => esc_html( $settings['mag_v2_prod_chip1'] ?? '' ),
                'chip2'      => esc_html( $settings['mag_v2_prod_chip2'] ?? '' ),
                'cat'        => esc_html( $settings['mag_v2_prod_cat'] ?? '' ),
                'name'       => esc_html( $settings['mag_v2_prod_name'] ?? '' ),
                'price'      => esc_html( $settings['mag_v2_prod_price'] ?? '' ),
                'orig_price' => esc_html( $settings['mag_v2_prod_orig_price'] ?? '' ),
                'btn_url'    => esc_url( $settings['mag_v2_prod_btn_url']['url'] ?? '' ),
            ];
            $mag_v2_stat = [
                'eyebrow' => esc_html( $settings['mag_v2_stat_eyebrow'] ?? '' ),
                'badge'   => esc_html( $settings['mag_v2_stat_badge'] ?? '' ),
                'num'     => esc_html( $settings['mag_v2_stat_num'] ?? '' ),
                'unit'    => esc_html( $settings['mag_v2_stat_unit'] ?? '' ),
                '1_val'   => esc_html( $settings['mag_v2_stat_1_val'] ?? '' ),
                '1_label' => esc_html( $settings['mag_v2_stat_1_label'] ?? '' ),
                '2_val'   => esc_html( $settings['mag_v2_stat_2_val'] ?? '' ),
                '2_label' => esc_html( $settings['mag_v2_stat_2_label'] ?? '' ),
            ];
            $mag_v2_look = [
                'image'    => esc_url( $settings['mag_v2_look_image']['url'] ?? '' ),
                'cat'      => esc_html( $settings['mag_v2_look_cat'] ?? '' ),
                'idx'      => esc_html( $settings['mag_v2_look_idx'] ?? '' ),
                'title'    => esc_html( $settings['mag_v2_look_title'] ?? '' ),
                'meta'     => esc_html( $settings['mag_v2_look_meta'] ?? '' ),
                'cta_text' => esc_html( $settings['mag_v2_look_cta_text'] ?? '' ),
                'cta_url'  => esc_url( $settings['mag_v2_look_cta_url']['url'] ?? '#' ),
            ];
        }

        // Magazine v3: promo panel + two spot cards.
        $mag_v3_promo = $mag_v3_spot1 = $mag_v3_spot2 = [];
        if ( 'magazine' === $style && 'v3' === $variant ) {
            $mag_v3_promo = [
                'image'    => esc_url( $settings['mag_v3_promo_image']['url'] ?? '' ),
                'label'    => esc_html( $settings['mag_v3_promo_label'] ?? '' ),
                'title'    => wp_kses_post( $settings['mag_v3_promo_title'] ?? '' ),
                'btn_text' => esc_html( $settings['mag_v3_promo_btn_text'] ?? '' ),
                'btn_url'  => esc_url( $settings['mag_v3_promo_btn_url']['url'] ?? '#' ),
            ];
            $mag_v3_spot1 = [
                'image' => esc_url( $settings['mag_v3_spot1_image']['url'] ?? '' ),
                'label' => esc_html( $settings['mag_v3_spot1_label'] ?? '' ),
                'title' => wp_kses_post( $settings['mag_v3_spot1_title'] ?? '' ),
                'price' => esc_html( $settings['mag_v3_spot1_price'] ?? '' ),
                'url'   => esc_url( $settings['mag_v3_spot1_url']['url'] ?? '#' ),
            ];
            $mag_v3_spot2 = [
                'image' => esc_url( $settings['mag_v3_spot2_image']['url'] ?? '' ),
                'label' => esc_html( $settings['mag_v3_spot2_label'] ?? '' ),
                'title' => wp_kses_post( $settings['mag_v3_spot2_title'] ?? '' ),
                'price' => esc_html( $settings['mag_v3_spot2_price'] ?? '' ),
                'url'   => esc_url( $settings['mag_v3_spot2_url']['url'] ?? '#' ),
            ];
        }

        // Luxury v3: social strip — widget-level, rendered once outside slides.
        $v3_side_label       = '';
        $v3_social_facebook  = '';
        $v3_social_twitter   = '';
        $v3_social_instagram = '';
        $v3_social_youtube   = '';
        if ( 'luxury' === $style && 'v3' === $variant ) {
            $v3_side_label       = esc_html( $settings['v3_side_label'] ?? '' );
            $v3_social_facebook  = esc_url( $settings['v3_social_facebook']['url']  ?? '' );
            $v3_social_twitter   = esc_url( $settings['v3_social_twitter']['url']   ?? '' );
            $v3_social_instagram = esc_url( $settings['v3_social_instagram']['url'] ?? '' );
            $v3_social_youtube   = esc_url( $settings['v3_social_youtube']['url']   ?? '' );
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="' . $banner_class . '"' . $slider_attr . '>';

        // Luxury v3: social strip rendered as a non-slide sibling inside .wl-hero-banner.
        // Slick uses slide: '.wl-hero-slide' so this div is excluded from the carousel.
        if ( 'luxury' === $style && 'v3' === $variant && ( $v3_social_facebook || $v3_social_twitter || $v3_social_instagram || $v3_social_youtube || $v3_side_label ) ) {
            echo '<div class="wl-hlv3-social-strip" aria-label="' . esc_attr__( 'Social links', 'woolentor' ) . '">';
            if ( $v3_social_facebook ) {
                echo '<a href="' . $v3_social_facebook . '" class="wl-hlv3-social-link" aria-label="' . esc_attr__( 'Facebook', 'woolentor' ) . '" target="_blank" rel="noopener noreferrer">'
                    . '<svg width="10" height="18" viewBox="0 0 10 18" fill="currentColor" aria-hidden="true"><path d="M6.5 10.5H8.75L9.5 7.5H6.5V6C6.5 5.205 6.5 4.5 8 4.5H9.5V1.98C9.23 1.944 8.208 1.875 7.125 1.875C4.9 1.875 3.5 3.15 3.5 5.7V7.5H0.5V10.5H3.5V18H6.5V10.5Z"/></svg>'
                    . '</a>';
            }
            if ( $v3_social_twitter ) {
                echo '<a href="' . $v3_social_twitter . '" class="wl-hlv3-social-link" aria-label="' . esc_attr__( 'X / Twitter', 'woolentor' ) . '" target="_blank" rel="noopener noreferrer">'
                    . '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.213 5.567zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'
                    . '</a>';
            }
            if ( $v3_social_instagram ) {
                echo '<a href="' . $v3_social_instagram . '" class="wl-hlv3-social-link" aria-label="' . esc_attr__( 'Instagram', 'woolentor' ) . '" target="_blank" rel="noopener noreferrer">'
                    . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>'
                    . '</a>';
            }
            if ( $v3_social_youtube ) {
                echo '<a href="' . $v3_social_youtube . '" class="wl-hlv3-social-link" aria-label="' . esc_attr__( 'YouTube', 'woolentor' ) . '" target="_blank" rel="noopener noreferrer">'
                    . '<svg width="14" height="12" viewBox="0 0 24 17" fill="currentColor" aria-hidden="true"><path d="M23.495 2.654a3.013 3.013 0 0 0-2.116-2.131C19.505 0 12 0 12 0S4.495 0 2.621.523A3.013 3.013 0 0 0 .505 2.654C0 4.545 0 8.5 0 8.5s0 3.955.505 5.846a3.013 3.013 0 0 0 2.116 2.131C4.495 17 12 17 12 17s7.505 0 9.379-.523a3.013 3.013 0 0 0 2.116-2.131C24 12.455 24 8.5 24 8.5s0-3.955-.505-5.846zM9.545 12.068V4.932L15.818 8.5l-6.273 3.568z"/></svg>'
                    . '</a>';
            }
            if ( $v3_side_label ) {
                echo '<span class="wl-hlv3-side-label" aria-hidden="true">' . $v3_side_label . '</span>';
            }
            echo '</div>';
        }

        foreach ( $slides as $index => $slide ) {

            // Build heading and compute highlight/outline phrases from the same field.
            $heading_raw              = $slide['heading'] ?? '';
            $heading_highlight_raw    = sanitize_text_field( $slide['heading_highlight'] ?? '' );
            $heading_outline_phrases  = $heading_highlight_raw
                ? array_values( array_filter( array_map( 'trim', explode( ',', $heading_highlight_raw ) ) ) )
                : [];

            // Modern v2 renders heading line-by-line in its own template; skip .wl-hl injection.
            // All other style/variant combos (including editorial v2) use inline .wl-hl spans.
            if ( ! ( 'modern' === $style && 'v2' === $variant ) ) {
                foreach ( $heading_outline_phrases as $phrase ) {
                    if ( $phrase !== '' && strpos( $heading_raw, $phrase ) !== false ) {
                        $heading_raw = str_replace(
                            $phrase,
                            '<span class="wl-hl">' . esc_html( $phrase ) . '</span>',
                            $heading_raw
                        );
                    }
                }
            }

            // v2 — split raw heading into per-line array (before any span injection).
            $heading_lines = array_values( array_filter( array_map( 'trim', explode( '<br>', $slide['heading'] ?? '' ) ) ) );

            // Sanitised variables available inside the template.
            $eyebrow            = wp_kses_post( $slide['eyebrow'] ?? '' );
            $heading            = wp_kses_post( $heading_raw );
            $description        = wp_kses_post( $slide['description'] ?? '' );
            $btn_primary_text   = esc_html( $slide['btn_primary_text'] ?? '' );
            $btn_primary_url    = esc_url( $slide['btn_primary_url']['url'] ?? '#' );
            $btn_secondary_text = esc_html( $slide['btn_secondary_text'] ?? '' );
            $btn_secondary_url  = esc_url( $slide['btn_secondary_url']['url'] ?? '#' );
            $hero_image_url     = esc_url( $slide['hero_image']['url'] ?? '' );
            $hero_image_alt     = esc_attr( $slide['hero_image_alt'] ?? '' );
            $hero_video_url     = esc_url( $slide['hero_video_url']['url'] ?? '' );

            $card2_eyebrow     = esc_html( $slide['card2_eyebrow'] ?? '' );
            $card2_subtitle    = esc_html( $slide['card2_subtitle'] ?? '' );
            $card2_image_url   = esc_url( $slide['card2_image']['url'] ?? '' );
            $card2_image_alt   = esc_attr( $slide['card2_image_alt'] ?? '' );
            $card2_video_url   = esc_url( $slide['card2_video_url'] ?? '' );
            $card2_watermark   = esc_html( $slide['card2_watermark'] ?? '' );
            $card2_description = esc_html( $slide['card2_description'] ?? '' );
            $card2_btn_text    = esc_html( $slide['card2_btn_text'] ?? '' );
            $card2_btn_url     = esc_url( $slide['card2_btn_url']['url'] ?? '#' );

            // Magazine v1-specific per-slide fields.
            $discount_badge  = esc_html( $slide['discount_badge'] ?? '' );
            $rating_text     = esc_html( $slide['rating_text'] ?? '' );
            $eyebrow_date    = esc_html( $slide['eyebrow_date'] ?? '' );
            // Magazine v2-specific per-slide fields.
            $pill_tag        = esc_html( $slide['pill_tag'] ?? '' );
            $spin_badge_text = esc_html( $slide['spin_badge_text'] ?? '' );

            // v2 without slider: all slides are visible (stacked sections). Otherwise hide non-first slides until slider JS activates.
            $slide_class = 'wl-hero-slide' . ( ( $index > 0 && ! $v2_no_slider ) ? ' wl-hero-slide--hidden' : '' );

            echo '<div class="' . esc_attr( $slide_class ) . '">';
            include $template;
            echo '</div>';
        }

        echo '</div>'; // .wl-hero-banner
        echo '</div>'; // data-wl-pack
    }
}
