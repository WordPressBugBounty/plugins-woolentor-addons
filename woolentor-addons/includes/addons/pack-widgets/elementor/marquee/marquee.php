<?php
/**
 * Marquee Widget — Pattern B (Style + Variant dropdowns).
 *
 * The scrolling bands each style pack ships: brand strips, promise tickers, press rows,
 * campaign phrases. Split out of Store Highlights because the content differs — a marquee
 * item is one label, optionally with a logo or thumbnail, not icon + claim + supporting line.
 *
 * The animation itself is not defined here. pack-widgets-base.css carries it under
 * .wl-pack-marquee*, named the same way .wl-pack-nav and .wl-pack-dots are shared by slider
 * widgets, so this widget only supplies each pack's colours, type and spacing.
 *
 * Spec: blueprint/marquee-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Marquee_Widget extends Widget_Base {

    /**
     * Style + variant combinations whose template renders $section_label.
     * Keep in sync with the templates themselves.
     */
    const LABEL_VARIANTS = [
        'modern'    => [ 'v1' ],
        'editorial' => [ 'v1' ],
    ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-marquee-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/marquee/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-marquee';
    }

    public function get_title() {
        return esc_html__( 'Marquee - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-animation-text';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'marquee', 'ticker', 'scroll', 'brands', 'press', 'strip', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-marquee-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    /**
     * Style => locked variants. Every pack's v2 and v3 require Pro.
     * This file is only loaded when Pro is NOT active.
     */
    private function get_pro_map() {
        $map = [];
        foreach ( \WooLentor\Style_Pack_Manager::get_pack_slugs() as $slug ) {
            $map[ $slug ] = [ 'v2', 'v3' ];
        }
        return $map;
    }

    /**
     * Build a nested Elementor 'conditions' array from a style => variants map.
     * OR across the mapped combinations, AND between style and variant within each.
     *
     * @param  array $map  [ 'modern' => [ 'v1' ], ... ]
     * @return array
     */
    private function build_conditions( array $map ) {
        $terms = [];
        foreach ( $map as $style => $variants ) {
            foreach ( $variants as $variant ) {
                $terms[] = [
                    'relation' => 'and',
                    'terms'    => [
                        [ 'name' => 'style',   'operator' => '==', 'value' => $style ],
                        [ 'name' => 'variant', 'operator' => '==', 'value' => $variant ],
                    ],
                ];
            }
        }
        return [ 'relation' => 'or', 'terms' => $terms ];
    }

    /**
     * Negated form of build_conditions() — true when the current selection is NOT any of
     * the mapped combinations. Used to hide editable controls on Pro-locked variants.
     *
     * @param  array $map
     * @return array
     */
    private function build_negated_conditions( array $map ) {
        $terms = [];
        foreach ( $map as $style => $variants ) {
            foreach ( $variants as $variant ) {
                $terms[] = [
                    'relation' => 'or',
                    'terms'    => [
                        [ 'name' => 'style',   'operator' => '!=', 'value' => $style ],
                        [ 'name' => 'variant', 'operator' => '!=', 'value' => $variant ],
                    ],
                ];
            }
        }
        return [ 'relation' => 'and', 'terms' => $terms ];
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

            woolentor_upgrade_pro_notice(
                $this,
                'marquee_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_items_controls();
        $this->register_marquee_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    private function register_items_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'item_text', [
            'label'       => esc_html__( 'Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Free Worldwide Shipping', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'item_image', [
            'label'       => esc_html__( 'Image', 'woolentor' ),
            'type'        => Controls_Manager::MEDIA,
            'description' => esc_html__( 'Optional — a logo or product thumbnail. Takes the place of the icon when set.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_icon', [
            'label'       => esc_html__( 'Icon', 'woolentor' ),
            'type'        => Controls_Manager::ICONS,
            'description' => esc_html__( 'Optional. Ignored when an image is set.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_link', [
            'label'         => esc_html__( 'Link', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com',
            'show_external' => true,
            'description'   => esc_html__( 'Optional. When set, the item becomes clickable.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_highlight', [
            'label'        => esc_html__( 'Highlight This Item', 'woolentor' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'woolentor' ),
            'label_off'    => esc_html__( 'No', 'woolentor' ),
            'return_value' => 'yes',
            'default'      => '',
            'description'  => esc_html__( 'Accent treatment — the alternating look some packs use.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_items', [
            'label'      => esc_html__( 'Items', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'section_label', [
                'label'       => esc_html__( 'Section Label', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Trusted brands we partner with', 'woolentor' ),
                'description' => esc_html__( 'Small label shown above the band.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::LABEL_VARIANTS ),
            ] );

            $this->add_control( 'marquee_items', [
                'label'       => esc_html__( 'Items', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ item_text }}}',
                'default'     => [
                    [ 'item_text' => esc_html__( 'Free Worldwide Shipping', 'woolentor' ) ],
                    [ 'item_text' => esc_html__( 'Lifetime Repairs', 'woolentor' ) ],
                    [ 'item_text' => esc_html__( 'Ethically Sourced', 'woolentor' ) ],
                    [ 'item_text' => esc_html__( '30-Day Easy Returns', 'woolentor' ) ],
                    [ 'item_text' => esc_html__( 'Certified Quality', 'woolentor' ) ],
                    [ 'item_text' => esc_html__( '2-Year Warranty', 'woolentor' ) ],
                ],
            ] );

        $this->end_controls_section();
    }

    private function register_marquee_controls() {
        $this->start_controls_section( 'section_marquee', [
            'label' => esc_html__( 'Marquee Options', 'woolentor' ),
        ] );

            woolentor_upgrade_pro_notice(
                $this,
                'marquee_options_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'compact' => true ]
            );

            $this->add_control( 'marquee_speed', [
                'label'       => esc_html__( 'Loop Duration (seconds)', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 30,
                'min'         => 5,
                'max'         => 200,
                'step'        => 1,
                'description' => esc_html__( 'Time for one full pass. Higher is slower.', 'woolentor' ),
            ] );

            $this->add_control( 'marquee_direction', [
                'label'   => esc_html__( 'Direction', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left'  => esc_html__( 'Left', 'woolentor' ),
                    'right' => esc_html__( 'Right', 'woolentor' ),
                ],
            ] );

            $this->add_control( 'marquee_pause_on_hover', [
                'label'        => esc_html__( 'Pause on Hover', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'marquee_separator', [
                'label'   => esc_html__( 'Separator', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'dot',
                'options' => [
                    'dot'     => esc_html__( 'Dot', 'woolentor' ),
                    'dash'    => esc_html__( 'Dash', 'woolentor' ),
                    'slash'   => esc_html__( 'Slash', 'woolentor' ),
                    'star'    => esc_html__( 'Star', 'woolentor' ),
                    'diamond' => esc_html__( 'Diamond', 'woolentor' ),
                    'none'    => esc_html__( 'None', 'woolentor' ),
                ],
            ] );

            $this->add_control( 'marquee_edge_fade', [
                'label'        => esc_html__( 'Fade Edges', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Softens both ends so items enter and leave instead of being cut off.', 'woolentor' ),
            ] );

            $this->add_responsive_control( 'marquee_fade_width', [
                'label'      => esc_html__( 'Fade Width', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ '%', 'px' ],
                'range'      => [
                    '%'  => [ 'min' => 1, 'max' => 30 ],
                    'px' => [ 'min' => 8, 'max' => 300 ],
                ],
                'condition'  => [ 'marquee_edge_fade' => 'yes' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pack-marquee--faded' => '--wl-pack-marquee-fade: {{SIZE}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();
    }

    /** Content-tab notice section shown in place of Items when a Pro variant is selected. */
    private function register_pro_content_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Items', 'woolentor' ),
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_upgrade_notice', $condition );
        $this->end_controls_section();
    }

    /** Style-tab notice section shown when a Pro variant is selected. */
    private function register_pro_style_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_style_notice', [
            'label'     => esc_html__( 'Pro Feature', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_style_upgrade_notice', $condition, [
                'message' => __( 'Style controls are only available in ShopLentor Pro. Upgrade to customize colors, typography, and spacing for this variant.', 'woolentor' ),
            ] );
        $this->end_controls_section();
    }

    // ── Style tab ─────────────────────────────────────────────────────────────

    /**
     * All 12 templates share the same semantic classes, so every selector here is a
     * single short string rather than a per-variant selector list.
     */
    private function register_style_controls() {
        $unlocked = $this->build_negated_conditions( $this->get_pro_map() );

        // — Band —
        $this->start_controls_section( 'style_band', [
            'label'      => esc_html__( 'Band', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'band_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-mq' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'band_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-mq' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'band_border',
                'selector' => '{{WRAPPER}} .wl-mq',
            ] );

            $this->add_responsive_control( 'band_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-mq' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ] );

        $this->end_controls_section();

        // — Section Label —
        $this->start_controls_section( 'style_label', [
            'label'      => esc_html__( 'Section Label', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::LABEL_VARIANTS ),
        ] );

            $this->add_control( 'label_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-mq-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .wl-mq-label',
            ] );

            $this->add_responsive_control( 'label_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-mq-label' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Item Text —
        $this->start_controls_section( 'style_text', [
            'label'      => esc_html__( 'Item Text', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->start_controls_tabs( 'text_tabs' );

                $this->start_controls_tab( 'text_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'text_color', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-mq-text' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'text_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'text_color_hover', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-mq-item:hover .wl-mq-text' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'      => 'text_typography',
                'selector'  => '{{WRAPPER}} .wl-mq-text',
                'separator' => 'before',
            ] );

        $this->end_controls_section();

        // — Media —
        $this->start_controls_section( 'style_media', [
            'label'      => esc_html__( 'Icon & Image', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'icon_color', [
                'label'     => esc_html__( 'Icon Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-mq-icon'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-mq-icon svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->add_responsive_control( 'icon_size', [
                'label'      => esc_html__( 'Icon Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 64 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-mq-icon'     => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-mq-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'image_height', [
                'label'      => esc_html__( 'Image Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 12, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-mq-image' => 'height: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'image_radius', [
                'label'      => esc_html__( 'Image Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-mq-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();

        // — Item —
        $this->start_controls_section( 'style_item', [
            'label'      => esc_html__( 'Item', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'item_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-mq-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'item_gap', [
                'label'      => esc_html__( 'Gap Inside Item', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-mq-item' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Highlighted Item —
        $this->start_controls_section( 'style_highlighted', [
            'label'      => esc_html__( 'Highlighted Item', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'highlighted_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-mq-item.is-highlighted .wl-mq-text' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'highlighted_icon_color', [
                'label'     => esc_html__( 'Icon Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-mq-item.is-highlighted .wl-mq-icon'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-mq-item.is-highlighted .wl-mq-icon svg' => 'fill: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

        // — Separator —
        $this->start_controls_section( 'style_separator', [
            'label'      => esc_html__( 'Separator', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
            'condition'  => [ 'marquee_separator!' => 'none' ],
        ] );

            $this->add_control( 'separator_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pack-marquee-sep' => 'color: {{VALUE}}; background-color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'separator_size', [
                'label'      => esc_html__( 'Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 1, 'max' => 30 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pack-marquee-sep' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();
    }

    // ── Render helpers ────────────────────────────────────────────────────────

    /**
     * Opening tag for one marquee item. An item with a link becomes an anchor, otherwise a
     * span — so every template gets link support without repeating the branch 12 times.
     *
     * Templates are included inside this class, so they call it as $this->item_open( $item ).
     *
     * @param  array  $item     One entry from build_items().
     * @param  string $classes  Extra layout classes for this template, e.g. an alternating
     *                          modifier a variant applies by index.
     * @return string
     */
    protected function item_open( array $item, $classes = '' ) {
        $class = 'wl-mq-item wl-pack-marquee-item';
        if ( ! empty( $item['is_highlighted'] ) ) {
            $class .= ' is-highlighted';
        }
        if ( $classes ) {
            $class .= ' ' . $classes;
        }

        if ( empty( $item['url'] ) ) {
            return '<span class="' . esc_attr( $class ) . '">';
        }

        $rel    = [];
        $target = '';
        if ( ! empty( $item['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }
        if ( ! empty( $item['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $item['url'] ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>';
    }

    /**
     * Matching closing tag for item_open().
     *
     * @param  array $item
     * @return string
     */
    protected function item_close( array $item ) {
        return empty( $item['url'] ) ? '</span>' : '</a>';
    }

    /**
     * Render one item's media. An image wins over an icon so a row never shows both, and an
     * item with neither renders nothing rather than an empty box.
     *
     * @param  array $item
     * @return string
     */
    protected function item_media( array $item ) {
        if ( ! empty( $item['image'] ) ) {
            return '<img class="wl-mq-image" src="' . esc_url( $item['image'] ) . '" alt="'
                . esc_attr( $item['text'] ) . '" loading="lazy" decoding="async">';
        }

        if ( empty( $item['icon']['value'] ) ) {
            return '';
        }

        ob_start();
        echo '<span class="wl-mq-icon">';
        \Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] );
        echo '</span>';
        return ob_get_clean();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Normalize the repeater into the flat $items contract the templates expect.
     * Values stay raw here — templates are the single escaping point.
     *
     * @param  array $rows  Raw repeater rows.
     * @return array
     */
    private function build_items( array $rows ) {
        $items = [];
        foreach ( $rows as $row ) {
            $items[] = [
                'text'           => $row['item_text'] ?? '',
                'icon'           => $row['item_icon'] ?? [],
                'image'          => $row['item_image']['url'] ?? '',
                'url'            => $row['item_link']['url'] ?? '',
                'is_external'    => ! empty( $row['item_link']['is_external'] ),
                'nofollow'       => ! empty( $row['item_link']['nofollow'] ),
                'is_highlighted' => 'yes' === ( $row['item_highlight'] ?? '' ),
            ];
        }
        return $items;
    }

    /**
     * Marquee settings passed to the template. Speed is clamped to the control range so a
     * hand-edited value cannot produce a zero-duration animation.
     *
     * @param  array $settings
     * @return array
     */
    private function build_marquee_settings( array $settings ) {
        $direction = sanitize_key( $settings['marquee_direction'] ?? 'left' );
        $separator = sanitize_key( $settings['marquee_separator'] ?? 'dot' );

        return [
            'speed'          => max( 5, min( 200, absint( $settings['marquee_speed'] ?? 30 ) ) ),
            'direction'      => in_array( $direction, [ 'left', 'right' ], true ) ? $direction : 'left',
            'separator'      => in_array( $separator, [ 'dot', 'dash', 'slash', 'star', 'diamond', 'none' ], true ) ? $separator : 'dot',
            'pause_on_hover' => 'yes' === ( $settings['marquee_pause_on_hover'] ?? 'yes' ),
            'edge_fade'      => 'yes' === ( $settings['marquee_edge_fade'] ?? 'yes' ),
        ];
    }

    /**
     * Class list for the band element, assembled once so every template stays declarative.
     *
     * @param  array $marquee
     * @return string
     */
    protected function band_classes( array $marquee ) {
        $classes = [ 'wl-pack-marquee', 'wl-pack-marquee--' . $marquee['direction'] ];
        if ( $marquee['pause_on_hover'] ) {
            $classes[] = 'wl-pack-marquee--pausable';
        }
        if ( $marquee['edge_fade'] ) {
            $classes[] = 'wl-pack-marquee--faded';
        }
        return implode( ' ', $classes );
    }

    /**
     * Render the real template with demo items so the user can see a Pro variant in the
     * editor before upgrading. Frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Marquee' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Marquee' );
            return;
        }

        $labels = [
            __( 'Free Worldwide Shipping', 'woolentor' ),
            __( 'Lifetime Repairs', 'woolentor' ),
            __( 'Ethically Sourced', 'woolentor' ),
            __( '30-Day Easy Returns', 'woolentor' ),
            __( 'Certified Quality', 'woolentor' ),
            __( '2-Year Warranty', 'woolentor' ),
        ];

        $items = [];
        foreach ( $labels as $i => $label ) {
            $items[] = [
                'text'           => $label,
                'icon'           => [],
                'image'          => '',
                'url'            => '',
                'is_external'    => false,
                'nofollow'       => false,
                'is_highlighted' => 1 === $i % 3,
            ];
        }

        $section_label = __( 'Pro Variant Preview', 'woolentor' );
        $marquee       = $this->build_marquee_settings( $settings );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-mq wl-mq-' . esc_attr( $style ) . ' wl-mq-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
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

        $settings = $this->get_settings_for_display();
        $style    = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant  = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        // Pro variant gate — this file is only loaded when Pro is NOT active.
        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            $this->render_pro_preview( $style, $variant, $settings );
            return;
        }

        $items = $this->build_items( $settings['marquee_items'] ?? [] );

        if ( empty( $items ) ) {
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Marquee template not found.', 'woolentor' ) . '</p>';
            return;
        }

        // Template-facing variables. Templates own the item loop and all escaping.
        $section_label = $settings['section_label'] ?? '';
        $marquee       = $this->build_marquee_settings( $settings );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-mq wl-mq-' . esc_attr( $style ) . ' wl-mq-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
        echo '</div>';
    }
}
