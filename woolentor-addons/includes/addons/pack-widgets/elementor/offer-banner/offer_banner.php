<?php
/**
 * Offer Banner Widget — Pattern B (Style + Variant dropdowns).
 *
 * The image-card banner grids each style pack ships: a photograph, an eyebrow tag, a headline,
 * an optional line of copy and a call to action — two or three of them side by side.
 *
 * Split from Campaign Banner because the content differs: this widget is a repeater of small
 * self-contained cards, while Campaign Banner is one wide section. Anything that needs a real
 * WooCommerce product — gallery, price, Add to Cart — belongs to Feature Product instead.
 *
 * The grid, card stacking and overlay mechanics are not defined here. pack-widgets-base.css
 * carries them under .wl-ob-*, so this widget only supplies each pack's colours, type and spacing.
 *
 * Spec: blueprint/offer-banner-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Offer_Banner_Widget extends Widget_Base {

    /**
     * Style + variant combinations whose template renders the corner badge.
     * Editorial v1 and v2 show it as a single label; magazine v2 pairs the value with the
     * label beneath it to make a discount stamp. Keep in sync with the templates themselves.
     */
    const BADGE_VARIANTS = [
        'editorial' => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /** Combinations whose template renders the per-card feature list. */
    const FEATURES_VARIANTS = [
        'magazine' => [ 'v2' ],
    ];

    /** Combinations whose template renders the coupon chip beside the button. */
    const META_VARIANTS = [
        'magazine' => [ 'v2' ],
    ];

    /** Combinations whose template renders the fine-print line. */
    const NOTE_VARIANTS = [
        'magazine' => [ 'v2' ],
    ];

    /** Combinations whose layout is asymmetric and honours item_size. */
    const SIZE_VARIANTS = [
        'modern' => [ 'v3' ],
    ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-offer-banner-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/offer-banner/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-offer-banner';
    }

    public function get_title() {
        return esc_html__( 'Offer Banner - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-image-box';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'banner', 'offer', 'promo', 'promotion', 'card', 'grid', 'sale', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-offer-banner-{$pack}",
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

    /**
     * Intersection of "not Pro-locked" and "this map" — a Style-tab section that is both
     * variant-specific and hidden behind the Pro gate.
     *
     * @param  array $map
     * @return array
     */
    private function build_unlocked_conditions( array $map ) {
        return [
            'relation' => 'and',
            'terms'    => [
                $this->build_negated_conditions( $this->get_pro_map() ),
                $this->build_conditions( $map ),
            ],
        ];
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
                'offer_banner_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_items_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    /**
     * One repeater shared by all 12 variants, so switching Style or Variant never loses
     * content.
     *
     * Repeater field conditions can only reference other fields in the same row — they cannot
     * see the widget-level style/variant settings. So every field is always visible and its
     * description says which packs use it; the templates render only what they support. The
     * Style tab, which is not a repeater, does hide its variant-specific sections.
     */
    private function register_items_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'item_image', [
            'label'   => esc_html__( 'Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => Utils::get_placeholder_image_src() ],
        ] );

        $repeater->add_control( 'item_eyebrow', [
            'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Limited Time', 'woolentor' ),
            'label_block' => true,
            'description' => esc_html__( 'The small tag above the title.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'rows'        => 2,
            'default'     => esc_html__( '30% Off Summer Sale', 'woolentor' ),
            'description' => esc_html__( 'Line breaks and <br>, <em>, <strong> are allowed.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_subtitle', [
            'label'   => esc_html__( 'Subtitle', 'woolentor' ),
            'type'    => Controls_Manager::TEXTAREA,
            'rows'    => 3,
            'default' => esc_html__( 'Refresh your space with our biggest sale of the season.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_cta_text', [
            'label'       => esc_html__( 'Button Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Shop Now', 'woolentor' ),
            'label_block' => true,
            'description' => esc_html__( 'Leave empty to hide the button.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_link', [
            'label'         => esc_html__( 'Link', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => 'https://example.com',
            'show_external' => true,
        ] );

        $repeater->add_control( 'item_size', [
            'label'       => esc_html__( 'Card Size', 'woolentor' ),
            'type'        => Controls_Manager::SELECT,
            'default'     => 'default',
            'options'     => [
                'default' => esc_html__( 'Default', 'woolentor' ),
                'large'   => esc_html__( 'Large', 'woolentor' ),
                'small'   => esc_html__( 'Small', 'woolentor' ),
            ],
            'description' => esc_html__( 'Used by layouts with an asymmetric grid, such as Modern v3.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_tone', [
            'label'       => esc_html__( 'Card Tone', 'woolentor' ),
            'type'        => Controls_Manager::SELECT,
            'default'     => 'default',
            'options'     => [
                'default' => esc_html__( 'Default', 'woolentor' ),
                'alt'     => esc_html__( 'Alternate', 'woolentor' ),
            ],
            'description' => esc_html__( 'Used by layouts that colour-code their cards, such as Magazine v2.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_badge_value', [
            'label'       => esc_html__( 'Badge Value', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => '40%',
            'separator'   => 'before',
            'description' => esc_html__( 'The corner badge. Editorial v1 and v2 use it on its own; Magazine v2 pairs it with the label below.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_badge_label', [
            'label'       => esc_html__( 'Badge Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'Off', 'woolentor' ),
            'description' => esc_html__( 'The small word under the badge value. Magazine v2 only.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_features', [
            'label'       => esc_html__( 'Feature List', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'rows'        => 3,
            'placeholder' => esc_html__( "Extra 10% for members\nFree shipping & easy returns", 'woolentor' ),
            'description' => esc_html__( 'One per line. Magazine v2 only.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_meta', [
            'label'       => esc_html__( 'Coupon Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => 'EDIT40',
            'label_block' => true,
            'description' => esc_html__( 'The chip beside the button. Magazine v2 only.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_note', [
            'label'       => esc_html__( 'Fine Print', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( '*Ends Sunday at midnight.', 'woolentor' ),
            'label_block' => true,
            'description' => esc_html__( 'Magazine v2 only.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_items', [
            'label'      => esc_html__( 'Banners', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'link_whole_card', [
                'label'        => esc_html__( 'Link the Whole Card', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'woolentor' ),
                'label_off'    => esc_html__( 'No', 'woolentor' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'On, the whole card is clickable and the button is decorative. Off, only the button links.', 'woolentor' ),
            ] );

            $this->add_control( 'banner_items', [
                'label'       => esc_html__( 'Banners', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ item_title || item_eyebrow }}}',
                // Elementor does not fold a repeater field's own default into the rows given here,
                // so each row has to name the placeholder itself — otherwise a freshly dropped
                // widget shows flat colour where the photograph should be.
                'default'     => [
                    [
                        'item_image'    => [ 'url' => Utils::get_placeholder_image_src() ],
                        'item_eyebrow'  => esc_html__( 'Trade-In', 'woolentor' ),
                        'item_title'    => esc_html__( "Trade your old gear,\nsave up to 40%.", 'woolentor' ),
                        'item_subtitle' => esc_html__( 'Send us your old phone, laptop or wearable and get instant credit toward your next order.', 'woolentor' ),
                        'item_cta_text' => esc_html__( 'Estimate trade-in value', 'woolentor' ),
                    ],
                    [
                        'item_image'    => [ 'url' => Utils::get_placeholder_image_src() ],
                        'item_eyebrow'  => esc_html__( 'Gaming Drop', 'woolentor' ),
                        'item_title'    => esc_html__( "Built for the\nnext 1000 hours.", 'woolentor' ),
                        'item_subtitle' => esc_html__( 'Tournament-grade headsets, mech keys and pro mice, engineered for low-latency play.', 'woolentor' ),
                        'item_cta_text' => esc_html__( 'Shop the gaming edit', 'woolentor' ),
                    ],
                ],
            ] );

        $this->end_controls_section();
    }

    /** Content-tab notice section shown in place of Banners when a Pro variant is selected. */
    private function register_pro_content_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Banners', 'woolentor' ),
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
     *
     * Every default lives in the pack stylesheet, not in the control — selecting a Style and
     * a Variant must reproduce the reference design with nothing touched here.
     */
    private function register_style_controls() {
        $unlocked = $this->build_negated_conditions( $this->get_pro_map() );

        // — Grid —
        $this->start_controls_section( 'style_grid', [
            'label'      => esc_html__( 'Grid', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'grid_columns', [
                'label'       => esc_html__( 'Columns', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => [
                    ''  => esc_html__( 'Default (from variant)', 'woolentor' ),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'description' => esc_html__( 'Leave on Default to keep the layout the variant was designed with.', 'woolentor' ),
                'selectors'   => [
                    '{{WRAPPER}} .wl-ob-grid' => '--wl-ob-cols: {{VALUE}}; --wl-ob-template: repeat({{VALUE}}, minmax(0, 1fr));',
                ],
            ] );

            $this->add_responsive_control( 'grid_column_gap', [
                'label'      => esc_html__( 'Column Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-grid' => 'column-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'grid_row_gap', [
                'label'      => esc_html__( 'Row Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-grid' => 'row-gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Card —
        $this->start_controls_section( 'style_card', [
            'label'      => esc_html__( 'Card', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'card_min_height', [
                'label'      => esc_html__( 'Minimum Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => 120, 'max' => 900 ],
                    'vh' => [ 'min' => 10,  'max' => 100 ],
                ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-card' => 'min-height: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'card_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-card' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .wl-ob-card',
            ] );

            $this->add_responsive_control( 'card_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ob-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .wl-ob-card',
            ] );

            $this->add_responsive_control( 'card_hover_lift', [
                'label'       => esc_html__( 'Hover Lift', 'woolentor' ),
                'type'        => Controls_Manager::SLIDER,
                'size_units'  => [ 'px' ],
                'range'       => [ 'px' => [ 'min' => -30, 'max' => 0 ] ],
                'description' => esc_html__( 'How far the card rises on hover. Negative values move it up.', 'woolentor' ),
                'selectors'   => [
                    '{{WRAPPER}} .wl-ob-card'       => 'transition: transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s cubic-bezier(.2,.8,.2,1);',
                    '{{WRAPPER}} .wl-ob-card:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
                ],
            ] );

        $this->end_controls_section();

        // — Image —
        $this->start_controls_section( 'style_image', [
            'label'      => esc_html__( 'Image', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'image_position', [
                'label'   => esc_html__( 'Focal Point', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    ''              => esc_html__( 'Default (center)', 'woolentor' ),
                    'top'           => esc_html__( 'Top', 'woolentor' ),
                    'bottom'        => esc_html__( 'Bottom', 'woolentor' ),
                    'left'          => esc_html__( 'Left', 'woolentor' ),
                    'right'         => esc_html__( 'Right', 'woolentor' ),
                    'top left'      => esc_html__( 'Top Left', 'woolentor' ),
                    'top right'     => esc_html__( 'Top Right', 'woolentor' ),
                    'bottom left'   => esc_html__( 'Bottom Left', 'woolentor' ),
                    'bottom right'  => esc_html__( 'Bottom Right', 'woolentor' ),
                ],
                'selectors' => [ '{{WRAPPER}} .wl-ob-img' => 'object-position: {{VALUE}};' ],
            ] );

            $this->add_control( 'image_zoom', [
                'label'       => esc_html__( 'Hover Zoom', 'woolentor' ),
                'type'        => Controls_Manager::SLIDER,
                'range'       => [ 'px' => [ 'min' => 1, 'max' => 1.3, 'step' => 0.01 ] ],
                'description' => esc_html__( 'Scale applied to the photo when the card is hovered. 1 disables it.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-ob-card' => '--wl-ob-img-zoom: {{SIZE}};' ],
            ] );

            $this->add_responsive_control( 'card_ratio', [
                'label'       => esc_html__( 'Card Ratio', 'woolentor' ),
                'type'        => Controls_Manager::SLIDER,
                'range'       => [ 'px' => [ 'min' => 0.4, 'max' => 2.5, 'step' => 0.01 ] ],
                'description' => esc_html__( 'Width divided by height. The card still grows past it if the copy needs more room.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-ob-card' => '--wl-ob-ratio: calc(100% / {{SIZE}});' ],
            ] );

        $this->end_controls_section();

        // — Overlay —
        $this->start_controls_section( 'style_overlay', [
            'label'      => esc_html__( 'Overlay', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->start_controls_tabs( 'overlay_tabs' );

                $this->start_controls_tab( 'overlay_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_group_control( Group_Control_Background::get_type(), [
                        'name'     => 'overlay_bg',
                        'types'    => [ 'classic', 'gradient' ],
                        'selector' => '{{WRAPPER}} .wl-ob-overlay',
                        'exclude'  => [ 'image' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'overlay_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_group_control( Group_Control_Background::get_type(), [
                        'name'     => 'overlay_bg_hover',
                        'types'    => [ 'classic', 'gradient' ],
                        'selector' => '{{WRAPPER}} .wl-ob-card:hover .wl-ob-overlay',
                        'exclude'  => [ 'image' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Content Box —
        $this->start_controls_section( 'style_body', [
            'label'      => esc_html__( 'Content Box', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'body_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ob-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            // One control drives both the text and the flex cross-axis, so the eyebrow pill and
            // the button follow the copy instead of needing a second control of their own.
            $this->add_responsive_control( 'body_align', [
                'label'   => esc_html__( 'Alignment', 'woolentor' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => esc_html__( 'Left', 'woolentor' ),   'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => esc_html__( 'Center', 'woolentor' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => esc_html__( 'Right', 'woolentor' ),  'icon' => 'eicon-text-align-right' ],
                ],
                'selectors_dictionary' => [
                    'left'   => 'text-align: left; --wl-ob-body-align: flex-start;',
                    'center' => 'text-align: center; --wl-ob-body-align: center;',
                    'right'  => 'text-align: right; --wl-ob-body-align: flex-end;',
                ],
                'selectors' => [ '{{WRAPPER}} .wl-ob-body' => '{{VALUE}}' ],
            ] );

            $this->add_responsive_control( 'body_justify', [
                'label'       => esc_html__( 'Vertical Position', 'woolentor' ),
                'type'        => Controls_Manager::CHOOSE,
                'options'     => [
                    'flex-start' => [ 'title' => esc_html__( 'Top', 'woolentor' ),    'icon' => 'eicon-v-align-top' ],
                    'center'     => [ 'title' => esc_html__( 'Middle', 'woolentor' ), 'icon' => 'eicon-v-align-middle' ],
                    'flex-end'   => [ 'title' => esc_html__( 'Bottom', 'woolentor' ), 'icon' => 'eicon-v-align-bottom' ],
                ],
                'description' => esc_html__( 'Applies to layouts where the content sits over the image.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-ob-body' => '--wl-ob-body-justify: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // — Eyebrow —
        $this->start_controls_section( 'style_eyebrow', [
            'label'      => esc_html__( 'Eyebrow', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'eyebrow_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-eyebrow' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-ob-eyebrow',
            ] );

            $this->add_responsive_control( 'eyebrow_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ob-eyebrow' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'eyebrow_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ob-eyebrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'eyebrow_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
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
                'selectors' => [ '{{WRAPPER}} .wl-ob-title' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .wl-ob-title',
            ] );

            $this->add_responsive_control( 'title_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-title' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Subtitle —
        $this->start_controls_section( 'style_sub', [
            'label'      => esc_html__( 'Subtitle', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'sub_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-sub' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'sub_typography',
                'selector' => '{{WRAPPER}} .wl-ob-sub',
            ] );

            $this->add_responsive_control( 'sub_max_width', [
                'label'      => esc_html__( 'Max Width', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => 120, 'max' => 900 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-sub' => 'max-width: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'sub_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-sub' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Button —
        $this->start_controls_section( 'style_button', [
            'label'      => esc_html__( 'Button', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->start_controls_tabs( 'button_tabs' );

                $this->start_controls_tab( 'button_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'button_color', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .wl-ob-cta'     => 'color: {{VALUE}};',
                            '{{WRAPPER}} .wl-ob-cta svg' => 'stroke: {{VALUE}};',
                        ],
                    ] );

                    $this->add_control( 'button_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ob-cta' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'button_border',
                        'selector' => '{{WRAPPER}} .wl-ob-cta',
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'button_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'button_color_hover', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .wl-ob-card:hover .wl-ob-cta'     => 'color: {{VALUE}};',
                            '{{WRAPPER}} .wl-ob-card:hover .wl-ob-cta svg' => 'stroke: {{VALUE}};',
                        ],
                    ] );

                    $this->add_control( 'button_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ob-card:hover .wl-ob-cta' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'button_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ob-card:hover .wl-ob-cta' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'      => 'button_typography',
                'selector'  => '{{WRAPPER}} .wl-ob-cta',
                'separator' => 'before',
            ] );

            $this->add_responsive_control( 'button_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ob-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'button_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ob-cta' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'button_icon_gap', [
                'label'      => esc_html__( 'Icon Spacing', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-cta' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Badge (variant-specific) —
        $this->start_controls_section( 'style_badge', [
            'label'      => esc_html__( 'Badge', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_unlocked_conditions( self::BADGE_VARIANTS ),
        ] );

            $this->add_control( 'badge_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-badge' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'badge_value_color', [
                'label'     => esc_html__( 'Value Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-badge-value' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'badge_value_typography',
                'selector' => '{{WRAPPER}} .wl-ob-badge-value',
            ] );

            $this->add_control( 'badge_label_color', [
                'label'     => esc_html__( 'Label Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-badge-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'badge_label_typography',
                'selector' => '{{WRAPPER}} .wl-ob-badge-label',
            ] );

        $this->end_controls_section();

        // — Features (variant-specific) —
        $this->start_controls_section( 'style_features', [
            'label'      => esc_html__( 'Feature List', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_unlocked_conditions( self::FEATURES_VARIANTS ),
        ] );

            $this->add_control( 'features_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-feature' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'features_icon_color', [
                'label'     => esc_html__( 'Icon Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-feature svg' => 'stroke: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'features_typography',
                'selector' => '{{WRAPPER}} .wl-ob-feature',
            ] );

            $this->add_responsive_control( 'features_gap', [
                'label'      => esc_html__( 'Row Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ob-features' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Coupon chip (variant-specific) —
        $this->start_controls_section( 'style_meta', [
            'label'      => esc_html__( 'Coupon Chip', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_unlocked_conditions( self::META_VARIANTS ),
        ] );

            $this->add_control( 'meta_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-meta' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'meta_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-meta' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'meta_typography',
                'selector' => '{{WRAPPER}} .wl-ob-meta',
            ] );

        $this->end_controls_section();

        // — Fine print (variant-specific) —
        $this->start_controls_section( 'style_note', [
            'label'      => esc_html__( 'Fine Print', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_unlocked_conditions( self::NOTE_VARIANTS ),
        ] );

            $this->add_control( 'note_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ob-note' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'note_typography',
                'selector' => '{{WRAPPER}} .wl-ob-note',
            ] );

        $this->end_controls_section();
    }

    // ── Render helpers ────────────────────────────────────────────────────────

    /**
     * Opening tag for one card. With "Link the Whole Card" on and a URL present the card is an
     * anchor; otherwise a div. Either way exactly one anchor is produced per card — nested
     * anchors are invalid HTML and break keyboard navigation.
     *
     * Templates are included inside this class, so they call it as $this->card_open( $card ).
     *
     * @param  array  $card     One entry from build_items().
     * @param  string $classes  Extra layout classes for this template.
     * @return string
     */
    protected function card_open( array $card, $classes = '' ) {
        $class = 'wl-ob-card';
        if ( $classes ) {
            $class .= ' ' . $classes;
        }
        if ( 'default' !== $card['size'] ) {
            $class .= ' wl-ob-card--' . $card['size'];
        }
        if ( 'default' !== $card['tone'] ) {
            $class .= ' wl-ob-card--' . $card['tone'];
        }

        if ( ! $card['card_is_link'] ) {
            return '<div class="' . esc_attr( $class ) . '">';
        }

        return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $card['url'] ) . '"'
            . $this->link_attrs( $card ) . '>';
    }

    /**
     * Matching closing tag for card_open().
     *
     * @param  array $card
     * @return string
     */
    protected function card_close( array $card ) {
        return $card['card_is_link'] ? '</a>' : '</div>';
    }

    /**
     * target and rel for a linked element, shared by the card and the button.
     *
     * @param  array $card
     * @return string
     */
    private function link_attrs( array $card ) {
        $rel    = [];
        $target = '';
        if ( ! empty( $card['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }
        if ( ! empty( $card['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }
        return $target . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' );
    }

    /**
     * The call to action. When the whole card is already a link this renders as a span so the
     * markup stays valid; otherwise it is the anchor.
     *
     * @param  array  $card
     * @param  string $icon     Raw inline SVG for the arrow, or empty.
     * @param  string $classes  Extra classes.
     * @return string
     */
    protected function card_cta( array $card, $icon = '', $classes = '' ) {
        if ( '' === $card['cta_text'] ) {
            return '';
        }

        $class = 'wl-ob-cta' . ( $classes ? ' ' . $classes : '' );
        $inner = esc_html( $card['cta_text'] ) . $icon;

        if ( $card['card_is_link'] || '' === $card['url'] ) {
            return '<span class="' . esc_attr( $class ) . '">' . $inner . '</span>';
        }

        return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $card['url'] ) . '"'
            . $this->link_attrs( $card ) . '>' . $inner . '</a>';
    }

    /**
     * The photograph plus its overlay. Renders nothing when no image is set, so a card without
     * one collapses to plain content rather than leaving an empty box.
     *
     * @param  array $card
     * @return string
     */
    protected function card_media( array $card ) {
        if ( '' === $card['image'] ) {
            return '<span class="wl-ob-overlay" aria-hidden="true"></span>';
        }

        return '<span class="wl-ob-media"><img class="wl-ob-img" src="' . esc_url( $card['image'] )
            . '" alt="' . esc_attr( $card['image_alt'] ) . '" loading="lazy" decoding="async"></span>'
            . '<span class="wl-ob-overlay" aria-hidden="true"></span>';
    }

    /**
     * The corner badge. Editorial v1/v2 and magazine v1 set only the value and get a single pill;
     * magazine v2 sets both and gets a stacked discount disc. Nothing renders when the value is
     * empty, so a variant that supports a badge does not force one.
     *
     * Templates place this as a sibling of .wl-ob-body, not inside it: magazine v2 narrows the
     * body to 74% so the photograph shows down one side, and a badge pinned from in there would
     * land 26% short of the card's edge.
     *
     * @param  array $card
     * @return string
     */
    protected function card_badge( array $card ) {
        if ( '' === $card['badge_value'] ) {
            return '';
        }

        $out = '<span class="wl-ob-badge"><span class="wl-ob-badge-value">'
            . esc_html( $card['badge_value'] ) . '</span>';

        if ( '' !== $card['badge_label'] ) {
            $out .= '<span class="wl-ob-badge-label">' . esc_html( $card['badge_label'] ) . '</span>';
        }

        return $out . '</span>';
    }

    /**
     * The per-card feature list. Elementor has no nested repeaters, so the field is a textarea
     * and each non-empty line becomes a row. The tick is inline SVG rather than an icon font so
     * it inherits currentColor and needs no extra asset.
     *
     * @param  array $card
     * @return string
     */
    protected function card_features( array $card ) {
        if ( empty( $card['features'] ) ) {
            return '';
        }

        $tick = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            . ' stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . '<polyline points="20 6 9 17 4 12" /></svg>';

        $out = '<ul class="wl-ob-features">';
        foreach ( $card['features'] as $feature ) {
            $out .= '<li class="wl-ob-feature">' . $tick . '<span>' . esc_html( $feature ) . '</span></li>';
        }
        return $out . '</ul>';
    }

    /**
     * Title with the small set of inline tags the reference headlines use. Line breaks typed in
     * the textarea become <br> so the two-line headlines in the references are reproducible
     * without the user writing HTML.
     *
     * @param  string $title
     * @return string
     */
    protected function card_title( $title ) {
        $allowed = [ 'br' => [], 'em' => [], 'strong' => [], 'b' => [], 'i' => [], 'span' => [ 'class' => [] ] ];
        return nl2br( wp_kses( $title, $allowed ) );
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Normalize the repeater into the flat $items contract the templates expect.
     * Values stay raw here — templates and the helpers above are the escaping point.
     *
     * @param  array $rows            Raw repeater rows.
     * @param  bool  $whole_card_link Widget-level "Link the Whole Card" setting.
     * @return array
     */
    private function build_items( array $rows, $whole_card_link ) {
        $items = [];

        foreach ( $rows as $row ) {
            $url = $row['item_link']['url'] ?? '';

            $features = [];
            if ( ! empty( $row['item_features'] ) ) {
                foreach ( preg_split( '/\R/', $row['item_features'] ) as $line ) {
                    $line = trim( $line );
                    if ( '' !== $line ) {
                        $features[] = $line;
                    }
                }
            }

            $items[] = [
                'image'        => $row['item_image']['url'] ?? '',
                'image_alt'    => $row['item_image']['alt'] ?? ( $row['item_title'] ?? '' ),
                'eyebrow'      => $row['item_eyebrow'] ?? '',
                'title'        => $row['item_title'] ?? '',
                'subtitle'     => $row['item_subtitle'] ?? '',
                'cta_text'     => $row['item_cta_text'] ?? '',
                'url'          => $url,
                'is_external'  => ! empty( $row['item_link']['is_external'] ),
                'nofollow'     => ! empty( $row['item_link']['nofollow'] ),
                'card_is_link' => $whole_card_link && '' !== $url,
                'size'         => $row['item_size'] ?? 'default',
                'tone'         => $row['item_tone'] ?? 'default',
                'badge_value'  => $row['item_badge_value'] ?? '',
                'badge_label'  => $row['item_badge_label'] ?? '',
                'features'     => $features,
                'meta'         => $row['item_meta'] ?? '',
                'note'         => $row['item_note'] ?? '',
            ];
        }

        return $items;
    }

    /**
     * Render the real template with demo cards so the user can see a Pro variant in the
     * editor before upgrading. Frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Offer Banner' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Offer Banner' );
            return;
        }

        $demo = [
            [
                'eyebrow'  => __( 'Limited Time', 'woolentor' ),
                'title'    => __( "30% Off\nSummer Sale", 'woolentor' ),
                'subtitle' => __( 'Refresh your space with our biggest sale of the season.', 'woolentor' ),
                'cta'      => __( 'Shop Now', 'woolentor' ),
            ],
            [
                'eyebrow'  => __( 'This Week Only', 'woolentor' ),
                'title'    => __( "Free\nDelivery", 'woolentor' ),
                'subtitle' => __( 'On all orders over $299. White-glove service included.', 'woolentor' ),
                'cta'      => __( 'Shop Now', 'woolentor' ),
            ],
            [
                'eyebrow'  => __( 'New Season', 'woolentor' ),
                'title'    => __( "The New\nEssentials", 'woolentor' ),
                'subtitle' => __( 'Light layers, natural tones, effortless silhouettes.', 'woolentor' ),
                'cta'      => __( 'Shop Now', 'woolentor' ),
            ],
        ];

        $items = [];
        foreach ( $demo as $i => $row ) {
            $items[] = [
                'image'        => Utils::get_placeholder_image_src(),
                'image_alt'    => $row['title'],
                'eyebrow'      => $row['eyebrow'],
                'title'        => $row['title'],
                'subtitle'     => $row['subtitle'],
                'cta_text'     => $row['cta'],
                'url'          => '',
                'is_external'  => false,
                'nofollow'     => false,
                'card_is_link' => false,
                'size'         => 0 === $i ? 'large' : 'small',
                'tone'         => 1 === $i ? 'alt' : 'default',
                'badge_value'  => '40%',
                'badge_label'  => __( 'Off', 'woolentor' ),
                'features'     => [ __( 'Free shipping & easy returns', 'woolentor' ) ],
                'meta'         => 'DEMO40',
                'note'         => __( '*Preview content.', 'woolentor' ),
            ];
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-ob wl-ob-' . esc_attr( $style ) . ' wl-ob-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
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
            $this->render_pro_preview( $style, $variant );
            return;
        }

        $items = $this->build_items(
            $settings['banner_items'] ?? [],
            'yes' === ( $settings['link_whole_card'] ?? 'yes' )
        );

        if ( empty( $items ) ) {
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Offer Banner template not found.', 'woolentor' ) . '</p>';
            return;
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-ob wl-ob-' . esc_attr( $style ) . ' wl-ob-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
        echo '</div>';
    }
}
