<?php
/**
 * Store Highlights Widget — Pattern B (Style + Variant dropdowns).
 *
 * A trust / service highlight strip for home page sections. Every one of the 12
 * style-variant layouts renders the same content model, so a single repeater is
 * shared across all of them and switching Style or Variant never loses content.
 *
 * Spec: blueprint/store-highlights-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Store_Highlights_Widget extends Widget_Base {

    /**
     * Style + variant combinations whose template renders $section_label.
     * Grows as templates are built — keep in sync with the templates themselves.
     */
    const LABEL_VARIANTS = [
        'editorial' => [ 'v2' ],
        'magazine'  => [ 'v2' ],
    ];

    /**
     * Style + variant combinations that lay items out as one centred row sized to their
     * content, rather than as a column grid. The Columns control is meaningless for these.
     */
    const ROW_VARIANTS = [
        'editorial' => [ 'v3' ],
        'magazine'  => [ 'v3' ],
    ];

    /**
     * Style + variant combinations that separate items with a rule rather than a box,
     * and so expose the Divider style section. Keep in sync with the pack stylesheets.
     */
    const DIVIDER_VARIANTS = [
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1' ],
    ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-store-highlights-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/store-highlights/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-store-highlights';
    }

    public function get_title() {
        return esc_html__( 'Store Highlights - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-icon-box';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'store', 'highlights', 'trust', 'feature', 'badge', 'shipping', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-store-highlights-{$pack}",
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
     * @param  array $map  [ 'modern' => [ 'v3' ], ... ]
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
     * the mapped combinations. Used to hide grid-only controls on centred-row layouts and
     * to hide editable controls on Pro-locked variants.
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
                'store_highlights_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_items_controls();
        $this->register_layout_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    private function register_items_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'feature_icon', [
            'label'   => esc_html__( 'Icon', 'woolentor' ),
            'type'    => Controls_Manager::ICONS,
            'default' => [
                'value'   => 'fas fa-truck',
                'library' => 'fa-solid',
            ],
        ] );

        $repeater->add_control( 'feature_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Free Shipping', 'woolentor' ),
            'description' => esc_html__( 'Also used for stat-style values, e.g. "4.8 / 5".', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'feature_subtitle', [
            'label'       => esc_html__( 'Subtitle', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'rows'        => 2,
            'default'     => esc_html__( 'On orders over $99', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'feature_link', [
            'label'         => esc_html__( 'Link', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com/shipping',
            'show_external' => true,
            'description'   => esc_html__( 'Optional. When set, the whole item becomes clickable.', 'woolentor' ),
        ] );

        $repeater->add_control( 'is_highlighted', [
            'label'        => esc_html__( 'Highlight This Item', 'woolentor' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'woolentor' ),
            'label_off'    => esc_html__( 'No', 'woolentor' ),
            'return_value' => 'yes',
            'default'      => '',
            'description'  => esc_html__( 'Applies an accent treatment. Layouts without a highlight state ignore this.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_items', [
            'label'      => esc_html__( 'Items', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'section_label', [
                'label'       => esc_html__( 'Section Label', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Why Shop With Us', 'woolentor' ),
                'description' => esc_html__( 'Small label shown above the group.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::LABEL_VARIANTS ),
            ] );

            $this->add_control( 'feature_items', [
                'label'       => esc_html__( 'Highlights', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ feature_title }}}',
                'default'     => [
                    [
                        'feature_icon'     => [ 'value' => 'fas fa-truck', 'library' => 'fa-solid' ],
                        'feature_title'    => esc_html__( 'Free Shipping', 'woolentor' ),
                        'feature_subtitle' => esc_html__( 'On orders over $99', 'woolentor' ),
                    ],
                    [
                        'feature_icon'     => [ 'value' => 'fas fa-undo', 'library' => 'fa-solid' ],
                        'feature_title'    => esc_html__( '30-Day Returns', 'woolentor' ),
                        'feature_subtitle' => esc_html__( 'No-questions promise', 'woolentor' ),
                    ],
                    [
                        'feature_icon'     => [ 'value' => 'fas fa-shield-alt', 'library' => 'fa-solid' ],
                        'feature_title'    => esc_html__( '2-Year Warranty', 'woolentor' ),
                        'feature_subtitle' => esc_html__( 'Genuine product cover', 'woolentor' ),
                    ],
                    [
                        'feature_icon'     => [ 'value' => 'fas fa-comment-dots', 'library' => 'fa-solid' ],
                        'feature_title'    => esc_html__( '24/7 Support', 'woolentor' ),
                        'feature_subtitle' => esc_html__( 'Chat with experts', 'woolentor' ),
                    ],
                ],
            ] );

        $this->end_controls_section();
    }

    private function register_layout_controls() {
        // Centred-row layouts size themselves to their content, so a column count
        // would have no effect there.
        $this->start_controls_section( 'section_layout', [
            'label'      => esc_html__( 'Layout', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( self::ROW_VARIANTS ),
        ] );

            // Written straight to CSS as a custom property so one control covers all three
            // breakpoints without threading three values through the template.
            $this->add_responsive_control( 'columns', [
                'label'          => esc_html__( 'Columns', 'woolentor' ),
                'type'           => Controls_Manager::SELECT,
                'default'        => '4',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors'      => [
                    '{{WRAPPER}} .wl-sh-list' => '--wl-sh-cols: {{VALUE}};',
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

        // — Section Label —
        $this->start_controls_section( 'style_label', [
            'label'      => esc_html__( 'Section Label', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::LABEL_VARIANTS ),
        ] );

            $this->add_control( 'label_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sh-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .wl-sh-label',
            ] );

        $this->end_controls_section();

        // — Icon —
        $this->start_controls_section( 'style_icon', [
            'label'      => esc_html__( 'Icon', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'icon_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sh-icon'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-sh-icon svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->add_responsive_control( 'icon_size', [
                'label'      => esc_html__( 'Icon Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 96 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sh-icon'     => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-sh-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_control( 'icon_box_bg', [
                'label'     => esc_html__( 'Box Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sh-icon' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'icon_box_size', [
                'label'      => esc_html__( 'Box Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 16, 'max' => 160 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sh-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'icon_box_border',
                'selector' => '{{WRAPPER}} .wl-sh-icon',
            ] );

            $this->add_responsive_control( 'icon_box_radius', [
                'label'      => esc_html__( 'Box Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sh-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();

        // — Title —
        $this->start_controls_section( 'style_title', [
            'label'      => esc_html__( 'Title', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'title_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sh-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .wl-sh-title',
            ] );

            $this->add_responsive_control( 'title_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sh-title' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Subtitle —
        $this->start_controls_section( 'style_subtitle', [
            'label'      => esc_html__( 'Subtitle', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'subtitle_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sh-sub' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .wl-sh-sub',
            ] );

        $this->end_controls_section();

        // — Item Box —
        $this->start_controls_section( 'style_item', [
            'label'      => esc_html__( 'Item Box', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'item_gap', [
                'label'      => esc_html__( 'Gap Between Items', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sh-list' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'items_area_bg', [
                'label'      => esc_html__( 'Item area Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sh-list' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'item_area_padding', [
                'label'      => esc_html__( 'Item Area Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sh-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'item_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sh-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->start_controls_tabs( 'item_tabs' );

                $this->start_controls_tab( 'item_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'item_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-sh-item' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'item_border',
                        'selector' => '{{WRAPPER}} .wl-sh-item',
                    ] );

                    $this->add_responsive_control( 'item_radius', [
                        'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                        'type'       => Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', '%' ],
                        'selectors'  => [
                            '{{WRAPPER}} .wl-sh-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'item_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'item_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-sh-item:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'item_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-sh-item:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Highlighted Item —
        $this->start_controls_section( 'style_highlighted', [
            'label'      => esc_html__( 'Highlighted Item', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'highlighted_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sh-item.is-highlighted' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'highlighted_border_color', [
                'label'     => esc_html__( 'Border Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sh-item.is-highlighted' => 'border-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'highlighted_icon_color', [
                'label'     => esc_html__( 'Icon Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sh-item.is-highlighted .wl-sh-icon'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-sh-item.is-highlighted .wl-sh-icon svg' => 'fill: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

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
                'icon'           => $row['feature_icon'] ?? [],
                'title'          => $row['feature_title'] ?? '',
                'subtitle'       => $row['feature_subtitle'] ?? '',
                'url'            => $row['feature_link']['url'] ?? '',
                'is_external'    => ! empty( $row['feature_link']['is_external'] ),
                'nofollow'       => ! empty( $row['feature_link']['nofollow'] ),
                'is_highlighted' => 'yes' === ( $row['is_highlighted'] ?? '' ),
            ];
        }
        return $items;
    }

    /**
     * Opening tag for one highlight item. An item with a link becomes an anchor, otherwise a
     * div — so every template gets link support without repeating the branch 12 times.
     *
     * Templates are included inside this class, so they call it as $this->item_open( $item ).
     *
     * @param  array  $item     One entry from build_items().
     * @param  string $classes  Extra layout classes for this template.
     * @return string
     */
    protected function item_open( array $item, $classes = '' ) {
        $class = 'wl-sh-item';
        if ( ! empty( $item['is_highlighted'] ) ) {
            $class .= ' is-highlighted';
        }
        if ( $classes ) {
            $class .= ' ' . $classes;
        }

        if ( empty( $item['url'] ) ) {
            return '<div class="' . esc_attr( $class ) . '">';
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
        return empty( $item['url'] ) ? '</div>' : '</a>';
    }

    /**
     * Render one item's icon inside the shared .wl-sh-icon wrapper. Returns an empty string
     * when no icon is set, so layouts collapse cleanly instead of showing an empty box.
     *
     * @param  array $item
     * @return string
     */
    protected function item_icon( array $item ) {
        if ( empty( $item['icon']['value'] ) ) {
            return '';
        }

        ob_start();
        echo '<span class="wl-sh-icon">';
        \Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] );
        echo '</span>';
        return ob_get_clean();
    }

    /**
     * Render the real template with demo items so the user can see a Pro variant in the
     * editor before upgrading. Frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Store Highlights' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Store Highlights' );
            return;
        }

        $preview_sub = __( 'Preview only — upgrade to edit', 'woolentor' );

        $items = [
            [
                'icon'     => [ 'value' => 'fas fa-truck', 'library' => 'fa-solid' ],
                'title'    => __( 'Free Shipping', 'woolentor' ),
                'subtitle' => $preview_sub,
            ],
            [
                'icon'     => [ 'value' => 'fas fa-undo', 'library' => 'fa-solid' ],
                'title'    => __( '30-Day Returns', 'woolentor' ),
                'subtitle' => $preview_sub,
            ],
            [
                'icon'           => [ 'value' => 'fas fa-shield-alt', 'library' => 'fa-solid' ],
                'title'          => __( '2-Year Warranty', 'woolentor' ),
                'subtitle'       => $preview_sub,
                'is_highlighted' => true,
            ],
            [
                'icon'     => [ 'value' => 'fas fa-comment-dots', 'library' => 'fa-solid' ],
                'title'    => __( '24/7 Support', 'woolentor' ),
                'subtitle' => $preview_sub,
            ],
        ];

        // Fill in the keys build_items() would have provided so templates can read them blindly.
        $items = array_map( function( $item ) {
            return array_merge( [
                'url'            => '',
                'is_external'    => false,
                'nofollow'       => false,
                'is_highlighted' => false,
            ], $item );
        }, $items );

        $section_label = __( 'Pro Variant Preview', 'woolentor' );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-sh wl-sh-' . esc_attr( $style ) . ' wl-sh-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
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

        $items = $this->build_items( $settings['feature_items'] ?? [] );

        if ( empty( $items ) ) {
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Store Highlights template not found.', 'woolentor' ) . '</p>';
            return;
        }

        // Template-facing variables. Templates own the item loop and all escaping.
        $section_label = $settings['section_label'] ?? '';

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-sh wl-sh-' . esc_attr( $style ) . ' wl-sh-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
        echo '</div>';
    }
}
