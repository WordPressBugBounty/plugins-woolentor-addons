<?php
/**
 * Shop by Category Widget
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Shop_By_Category_Widget extends Widget_Base {

    /** The taxonomy every query in this widget reads. */
    const TAXONOMY = 'product_cat';

    /**
     * Style + variant combinations whose template draws the header's "view all" link.
     * Grows as templates are built — keep in sync with the templates themselves.
     */
    const VIEW_ALL_VARIANTS = [
        'modern'    => [ 'v1' ],
        'editorial' => [ 'v1' ],
        'magazine'  => [ 'v1' ],
    ];

    /**
     * Style + variant combinations whose header renders a description line. Modern v3's header is
     * eyebrow + headline only, so the control is hidden rather than silently ignored.
     */
    const DESC_VARIANTS = [
        'modern'    => [ 'v1', 'v2' ],
        'editorial' => [ 'v1' ],
    ];

    /**
     * Style + variant combinations whose card draws the child-term sub-line.
     */
    const CHILDREN_VARIANTS = [
        'modern' => [ 'v2' ],
    ];

    /**
     * Style + variant combinations whose card draws the category's own description line. The copy
     * comes from the term description, which is a real taxonomy field — unlike the icon.
     */
    const ROW_DESC_VARIANTS = [
        'editorial' => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1' ],
    ];

    /**
     * Style + variant combinations whose card draws an icon. WooCommerce has no icon field for a
     * category, so these render the slot only when the Overrides repeater supplies one.
     */
    const ICON_VARIANTS = [
        'modern' => [ 'v3' ],
    ];

    /**
     * Style + variant combinations whose card carries a call-to-action button. Modern v2's only
     * appears on hover, which is why it is absent from the reference screenshot.
     */
    const CARD_BUTTON_VARIANTS = [
        'modern'   => [ 'v2' ],
        'luxury'   => [ 'v2' ],
        'magazine' => [ 'v2' ],
    ];

    /**
     * Style + variant combinations whose grid is a carousel rather than a static grid. These reuse
     * the plugin's existing WLPackSlider (Slick) via pack-widgets.js rather than shipping a second
     * slider implementation.
     */
    const SLIDER_VARIANTS = [
        'luxury' => [ 'v1', 'v3' ],
    ];

    /**
     * Style + variant combinations whose grid actually reads --wl-sbc-cols, and so are the only
     * ones the Columns control can move. The rest are laid out some other way and would ignore it:
     * the carousels size by slides-per-view, magazine v3 and editorial v2 are flex tracks,
     * magazine v1 is a fixed twelve-column mosaic, and the remaining variants are single-column
     * lists whose design depends on staying that way.
     */
    const COLUMN_VARIANTS = [
        'modern'   => [ 'v1', 'v2', 'v3' ],
        'magazine' => [ 'v2' ],
    ];

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
            $handle = "wl-pack-shop-by-category-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/shop-by-category/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-shop-by-category';
    }

    public function get_title() {
        return esc_html__( 'Shop by Category - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-product-categories';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'shop', 'category', 'categories', 'collection', 'product', 'taxonomy', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-shop-by-category-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_script_depends() {
        return [ 'wl-pack-widgets' ];
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
     * Negated form of build_conditions() — true when the current selection is NOT any of the
     * mapped combinations. Used to hide editable controls on Pro-locked variants.
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
     * AND several condition groups together. Elementor's Conditions::check() recurses into any
     * term that carries its own 'terms', so a group built by build_conditions() can be nested
     * inside another — which is how a control gets both "only on these variants" and
     * "not on a Pro-locked one" at the same time.
     *
     * @param  array ...$groups
     * @return array
     */
    private function all_of( ...$groups ) {
        return [ 'relation' => 'and', 'terms' => $groups ];
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
                'shop_by_category_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_header_controls();
        $this->register_source_controls();
        $this->register_overrides_controls();
        $this->register_layout_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    /** The header block above the grid. Every confirmed variant has one, so it lives here. */
    private function register_header_controls() {
        $this->start_controls_section( 'section_header', [
            'label'      => esc_html__( 'Section Header', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'eyebrow', [
                'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Shop by category', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'headline', [
                'label'       => esc_html__( 'Headline', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'default'     => esc_html__( 'Find the perfect tech for every moment.', 'woolentor' ),
                'description' => esc_html__( 'Wrap words in <em> to render them in the accent colour.', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'description', [
                'label'       => esc_html__( 'Description', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__( 'Six lifestyle-led categories, hand-picked from over 240 brands and engineered to fit how you actually live.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::DESC_VARIANTS ),
            ] );

            $this->add_control( 'view_all_text', [
                'label'       => esc_html__( 'View All Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'View all categories', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::VIEW_ALL_VARIANTS ),
            ] );

            $this->add_control( 'view_all_link', [
                'label'         => esc_html__( 'View All Link', 'woolentor' ),
                'type'          => Controls_Manager::URL,
                'placeholder'   => 'https://example.com/shop',
                'show_external' => true,
                'conditions'    => $this->build_conditions( self::VIEW_ALL_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    /** Which categories the grid shows, and how they are ordered. */
    private function register_source_controls() {
        $this->start_controls_section( 'section_source', [
            'label'      => esc_html__( 'Categories', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'source_mode', [
                'label'       => esc_html__( 'Source', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'all',
                'options'     => [
                    'all'         => esc_html__( 'All top-level categories', 'woolentor' ),
                    'selected'    => esc_html__( 'Selected categories', 'woolentor' ),
                    'children_of' => esc_html__( 'Children of a category', 'woolentor' ),
                ],
                'description' => esc_html__( 'All top-level shows the categories that sit directly under the catalogue root.', 'woolentor' ),
            ] );

            $this->add_control( 'selected_cats', [
                'label'       => esc_html__( 'Select Categories', 'woolentor' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => woolentor_taxonomy_list( self::TAXONOMY ),
                'condition'   => [ 'source_mode' => 'selected' ],
            ] );

            $this->add_control( 'parent_cat', [
                'label'       => esc_html__( 'Parent Category', 'woolentor' ),
                'type'        => Controls_Manager::SELECT2,
                'label_block' => true,
                'options'     => woolentor_taxonomy_list( self::TAXONOMY ),
                'condition'   => [ 'source_mode' => 'children_of' ],
            ] );

            $this->add_control( 'orderby', [
                'label'   => esc_html__( 'Order By', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name'       => esc_html__( 'Name', 'woolentor' ),
                    'count'      => esc_html__( 'Product Count', 'woolentor' ),
                    'menu_order' => esc_html__( 'Category Order', 'woolentor' ),
                ],
                'condition' => [ 'source_mode!' => 'selected' ],
            ] );

            // "As picked" only means something when there is a pick to follow.
            $this->add_control( 'orderby_selected', [
                'label'   => esc_html__( 'Order By', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'picked',
                'options' => [
                    'picked'     => esc_html__( 'As picked above', 'woolentor' ),
                    'name'       => esc_html__( 'Name', 'woolentor' ),
                    'count'      => esc_html__( 'Product Count', 'woolentor' ),
                    'menu_order' => esc_html__( 'Category Order', 'woolentor' ),
                ],
                'condition' => [ 'source_mode' => 'selected' ],
            ] );

            $this->add_control( 'order', [
                'label'   => esc_html__( 'Order', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC'  => esc_html__( 'Ascending', 'woolentor' ),
                    'DESC' => esc_html__( 'Descending', 'woolentor' ),
                ],
            ] );

            $this->add_control( 'limit', [
                'label'       => esc_html__( 'Number of Categories', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 6,
                'min'         => -1,
                'description' => esc_html__( 'Use -1 to show every matching category.', 'woolentor' ),
            ] );

            $this->add_control( 'hide_empty', [
                'label'        => esc_html__( 'Hide Empty Categories', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'woolentor' ),
                'label_off'    => esc_html__( 'No', 'woolentor' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'show_count', [
                'label'        => esc_html__( 'Show Product Count', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'woolentor' ),
                'label_off'    => esc_html__( 'No', 'woolentor' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'count_suffix', [
                'label'       => esc_html__( 'Count Suffix', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'products', 'woolentor' ),
                'description' => esc_html__( 'Leave empty for the bare number.', 'woolentor' ),
                'condition'   => [ 'show_count' => 'yes' ],
            ] );

            $this->add_control( 'card_button_text', [
                'label'       => esc_html__( 'Card Button Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Shop Now', 'woolentor' ),
                'description' => esc_html__( 'Revealed on hover. Leave empty to drop the button.', 'woolentor' ),
                'label_block' => true,
                'separator'   => 'before',
                'conditions'  => $this->build_conditions( self::CARD_BUTTON_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    /**
     * Per-term overrides for the things the taxonomy genuinely cannot supply.
     * Keyed by term, so reordering or changing the limit never misaligns a row.
     */
    private function register_overrides_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'override_term', [
            'label'       => esc_html__( 'Category', 'woolentor' ),
            'type'        => Controls_Manager::SELECT2,
            'label_block' => true,
            'options'     => woolentor_taxonomy_list( self::TAXONOMY ),
        ] );

        $repeater->add_control( 'override_icon', [
            'label' => esc_html__( 'Icon', 'woolentor' ),
            'type'  => Controls_Manager::ICONS,
        ] );

        $repeater->add_control( 'override_image', [
            'label'       => esc_html__( 'Image', 'woolentor' ),
            'type'        => Controls_Manager::MEDIA,
            'description' => esc_html__( 'Replaces the category thumbnail.', 'woolentor' ),
        ] );

        $repeater->add_control( 'override_sub', [
            'label'       => esc_html__( 'Sub Line', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
            'description' => esc_html__( 'Comma-separated. Replaces the automatic child-category line.', 'woolentor' ),
        ] );

        $repeater->add_control( 'override_desc', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'rows'        => 2,
            'label_block' => true,
            'description' => esc_html__( 'Replaces the category description from WooCommerce.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_overrides', [
            'label'      => esc_html__( 'Overrides', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'overrides_note', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'Leave this empty and the section is fully automatic. WooCommerce stores a thumbnail per category but has no icon field, so icon-led layouts draw their icon only when one is set here.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            $this->add_control( 'overrides', [
                'label'       => esc_html__( 'Category Overrides', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ override_term }}}',
                'default'     => [],
                'prevent_empty' => false,
            ] );

        $this->end_controls_section();
    }

    private function register_layout_controls() {
        // Shown only where the grid actually reads --wl-sbc-cols. Everywhere else the control
        // would sit in the panel doing nothing, which is worse than not offering it.
        $this->start_controls_section( 'section_layout', [
            'label'      => esc_html__( 'Layout', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::COLUMN_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            // Left empty by default so each variant keeps the column count its reference used.
            // Picking a value writes a custom property that overrides the stylesheet.
            $this->add_responsive_control( 'columns', [
                'label'     => esc_html__( 'Columns', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => '',
                'options'   => [
                    ''  => esc_html__( 'Default', 'woolentor' ),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .wl-sbc-grid' => '--wl-sbc-cols: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

        // Carousel variants size themselves by slides-per-view, not by a CSS column count, so
        // they get their own section instead of the Columns control above.
        $this->start_controls_section( 'section_slider', [
            'label'      => esc_html__( 'Slider', 'woolentor' ),
            'conditions' => $this->build_conditions( self::SLIDER_VARIANTS ),
        ] );

            $this->add_control( 'slides_per_view', [
                'label'   => esc_html__( 'Cards Per View', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 1,
                'max'     => 8,
            ] );

            $this->add_control( 'slides_per_view_tablet', [
                'label'   => esc_html__( 'Cards Per View (Tablet)', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 3,
                'min'     => 1,
                'max'     => 8,
            ] );

            $this->add_control( 'slides_per_view_mobile', [
                'label'   => esc_html__( 'Cards Per View (Mobile)', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 2,
                'min'     => 1,
                'max'     => 8,
            ] );

            $this->add_control( 'slider_autoplay', [
                'label'        => esc_html__( 'Autoplay', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'woolentor' ),
                'label_off'    => esc_html__( 'No', 'woolentor' ),
                'return_value' => 'yes',
                'default'      => '',
                'separator'    => 'before',
            ] );

            $this->add_control( 'slider_autoplay_speed', [
                'label'     => esc_html__( 'Autoplay Speed (ms)', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 5000,
                'min'       => 1000,
                'step'      => 500,
                'condition' => [ 'slider_autoplay' => 'yes' ],
            ] );

            $this->add_control( 'slider_loop', [
                'label'        => esc_html__( 'Loop', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'woolentor' ),
                'label_off'    => esc_html__( 'No', 'woolentor' ),
                'return_value' => 'yes',
                'default'      => '',
                'description'  => esc_html__( 'Off keeps the progress bar meaningful — it fills as the track reaches its end.', 'woolentor' ),
            ] );

        $this->end_controls_section();
    }

    /** Content-tab notice shown in place of the editable sections on a Pro variant. */
    private function register_pro_content_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Categories', 'woolentor' ),
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_upgrade_notice', $condition );
        $this->end_controls_section();
    }

    /** Style-tab notice shown when a Pro variant is selected. */
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
     * All 12 templates share the same semantic classes, so every selector here is a single
     * short string rather than a per-variant selector list.
     */
    private function register_style_controls() {
        $unlocked = $this->build_negated_conditions( $this->get_pro_map() );

        // — Header —
        $this->start_controls_section( 'style_header', [
            'label'      => esc_html__( 'Section Header', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-eyebrow',
            ] );

            $this->add_control( 'headline_color', [
                'label'     => esc_html__( 'Headline Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-sbc-headline' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'headline_accent_color', [
                'label'       => esc_html__( 'Headline Accent Color', 'woolentor' ),
                'type'        => Controls_Manager::COLOR,
                'description' => esc_html__( 'Applies to the <em> part of the headline.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-sbc-headline em' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'headline_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-headline',
            ] );

            $this->add_control( 'desc_color', [
                'label'      => esc_html__( 'Description Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sbc-desc' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::DESC_VARIANTS ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'       => 'desc_typography',
                'selector'   => '{{WRAPPER}} .wl-sbc-desc',
                'conditions' => $this->build_conditions( self::DESC_VARIANTS ),
            ] );

            $this->add_responsive_control( 'header_spacing', [
                'label'      => esc_html__( 'Spacing Below Header', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'separator'  => 'before',
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sbc-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — View All link —
        $this->start_controls_section( 'style_view_all', [
            'label'      => esc_html__( 'View All Link', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::VIEW_ALL_VARIANTS ),
        ] );

            $this->add_control( 'view_all_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-viewall' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'view_all_color_hover', [
                'label'     => esc_html__( 'Hover Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-viewall:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'view_all_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-viewall',
            ] );

        $this->end_controls_section();

        // — Card —
        $this->start_controls_section( 'style_card', [
            'label'      => esc_html__( 'Card', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            // Two declarations because the carousel variants cannot use `gap` — Slick sizes each
            // slide with an inline width inside its own track, so their spacing comes from the
            // --wl-sbc-slide-gap custom property instead. Writing both keeps one control correct
            // for all twelve layouts.
            $this->add_responsive_control( 'grid_gap', [
                'label'      => esc_html__( 'Gap Between Cards', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sbc-grid' => 'gap: {{SIZE}}{{UNIT}}; --wl-sbc-slide-gap: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->start_controls_tabs( 'card_tabs' );

                $this->start_controls_tab( 'card_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'card_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-sbc-card' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'card_border',
                        'selector' => '{{WRAPPER}} .wl-sbc-card',
                    ] );

                    $this->add_responsive_control( 'card_radius', [
                        'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                        'type'       => Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', '%' ],
                        'selectors'  => [
                            '{{WRAPPER}} .wl-sbc-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'card_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'card_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-sbc-card:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'card_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-sbc-card:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Image —
        $this->start_controls_section( 'style_image', [
            'label'      => esc_html__( 'Image', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'image_ratio', [
                'label'      => esc_html__( 'Aspect Ratio', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [ 'px' => [ 'min' => 0.4, 'max' => 2, 'step' => 0.05 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sbc-media' => 'aspect-ratio: {{SIZE}};' ],
            ] );

            $this->add_responsive_control( 'image_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sbc-media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();

        // — Category Name —
        $this->start_controls_section( 'style_name', [
            'label'      => esc_html__( 'Category Name', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'name_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'name_color_hover', [
                'label'     => esc_html__( 'Hover Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-card:hover .wl-sbc-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'name_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-name',
            ] );

        $this->end_controls_section();

        // — Count —
        $this->start_controls_section( 'style_count', [
            'label'      => esc_html__( 'Product Count', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
            'condition'  => [ 'show_count' => 'yes' ],
        ] );

            $this->add_control( 'count_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-count' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'count_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-count',
            ] );

        $this->end_controls_section();

        // — Sub Line —
        $this->start_controls_section( 'style_children', [
            'label'      => esc_html__( 'Sub Line', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::CHILDREN_VARIANTS ),
        ] );

            $this->add_control( 'children_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-sub' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'children_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-sub',
            ] );

        $this->end_controls_section();

        // — Category Description —
        $this->start_controls_section( 'style_row_desc', [
            'label'      => esc_html__( 'Category Description', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::ROW_DESC_VARIANTS ),
        ] );

            $this->add_control( 'row_desc_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-row-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'row_desc_typography',
                'selector' => '{{WRAPPER}} .wl-sbc-row-desc',
            ] );

        $this->end_controls_section();

        // — Icon —
        $this->start_controls_section( 'style_icon', [
            'label'      => esc_html__( 'Icon', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::ICON_VARIANTS ),
        ] );

            $this->add_control( 'icon_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sbc-icon'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-sbc-icon svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->add_responsive_control( 'icon_size', [
                'label'      => esc_html__( 'Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 96 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sbc-icon'     => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-sbc-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();

        // — Arrow button —
        $this->start_controls_section( 'style_arrow', [
            'label'      => esc_html__( 'Arrow Button', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'arrow_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-go' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'arrow_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-go' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'arrow_color_hover', [
                'label'     => esc_html__( 'Hover Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-sbc-card:hover .wl-sbc-go' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'arrow_bg_hover', [
                'label'     => esc_html__( 'Hover Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sbc-card:hover .wl-sbc-go' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

        // — Card Button —
        $this->start_controls_section( 'style_card_button', [
            'label'      => esc_html__( 'Card Button', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::CARD_BUTTON_VARIANTS ),
        ] );

            $this->add_control( 'card_button_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-btn' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'card_button_border_color', [
                'label'     => esc_html__( 'Border Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-btn' => 'border-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'card_button_color_hover', [
                'label'     => esc_html__( 'Hover Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-sbc-btn:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'card_button_bg_hover', [
                'label'     => esc_html__( 'Hover Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sbc-btn:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'      => 'card_button_typography',
                'separator' => 'before',
                'selector'  => '{{WRAPPER}} .wl-sbc-btn',
            ] );

        $this->end_controls_section();

        // — Count Badge —
        $this->start_controls_section( 'style_badge', [
            'label'      => esc_html__( 'Count Badge', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( self::ICON_VARIANTS ),
            'condition'  => [ 'show_count' => 'yes' ],
        ] );

            $this->add_control( 'badge_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-badge' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'badge_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-badge' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'badge_bg_hover', [
                'label'     => esc_html__( 'Hover Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sbc-card:hover .wl-sbc-badge' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'      => 'badge_typography',
                'separator' => 'before',
                'selector'  => '{{WRAPPER}} .wl-sbc-badge',
            ] );

        $this->end_controls_section();
    }

    // ── Data layer ────────────────────────────────────────────────────────────

    /**
     * The one place in this widget that touches the taxonomy.
     *
     * Makes a single get_terms() call for the whole taxonomy, groups the result by parent in PHP,
     * and then walks the selected parents. Filling `children` from that grouping costs nothing —
     * the naive alternative, a get_terms( child_of ) inside the row loop, would cost one extra
     * query per card and grow with the limit.
     *
     * `hide_empty` is applied here rather than in the query, because the query has to return
     * everything for the parent/child grouping to be complete.
     *
     * @param  array $settings
     * @return array  Rows shaped [ id, name, url, count, image, icon, desc, children ].
     */
    private function resolve_categories( array $settings ) {
        $hide_empty = 'yes' === ( $settings['hide_empty'] ?? 'yes' );
        $mode       = sanitize_key( $settings['source_mode'] ?? 'all' );
        $order      = 'DESC' === strtoupper( $settings['order'] ?? 'ASC' ) ? 'DESC' : 'ASC';
        $limit      = (int) ( $settings['limit'] ?? 6 );

        $orderby = 'selected' === $mode
            ? sanitize_key( $settings['orderby_selected'] ?? 'picked' )
            : sanitize_key( $settings['orderby'] ?? 'name' );

        // One unfiltered call. Ordering is handed to the query where WordPress and WooCommerce
        // support it; "as picked" is resolved in PHP afterwards because it follows the control's
        // own order, not the database's.
        $terms = get_terms( [
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
            'orderby'    => in_array( $orderby, [ 'name', 'count', 'menu_order' ], true ) ? $orderby : 'name',
            'order'      => $order,
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return [];
        }

        // Group once; both the parent selection and every card's child line read from this.
        $by_parent = [];
        $by_slug   = [];
        foreach ( $terms as $term ) {
            $by_parent[ (int) $term->parent ][] = $term;
            $by_slug[ $term->slug ]             = $term;
        }

        switch ( $mode ) {
            case 'selected':
                $picked   = array_filter( (array) ( $settings['selected_cats'] ?? [] ) );
                $selected = [];
                foreach ( $picked as $slug ) {
                    if ( isset( $by_slug[ $slug ] ) ) {
                        $selected[] = $by_slug[ $slug ];
                    }
                }
                // Anything other than "as picked" falls back to the order the query returned.
                if ( 'picked' !== $orderby ) {
                    $order_map = [];
                    foreach ( $terms as $i => $term ) {
                        $order_map[ $term->term_id ] = $i;
                    }
                    usort( $selected, fn( $a, $b ) => $order_map[ $a->term_id ] <=> $order_map[ $b->term_id ] );
                } elseif ( 'DESC' === $order ) {
                    $selected = array_reverse( $selected );
                }
                break;

            case 'children_of':
                $parent_slug = (string) ( $settings['parent_cat'] ?? '' );
                $parent      = $by_slug[ $parent_slug ] ?? null;
                $selected    = $parent ? ( $by_parent[ (int) $parent->term_id ] ?? [] ) : [];
                break;

            case 'all':
            default:
                $selected = $by_parent[0] ?? [];
                break;
        }

        if ( $hide_empty ) {
            $selected = array_filter( $selected, fn( $term ) => (int) $term->count > 0 );
        }

        $selected = array_values( $selected );

        if ( $limit > 0 ) {
            $selected = array_slice( $selected, 0, $limit );
        }

        $overrides = $this->build_overrides( $settings );

        $rows = [];
        foreach ( $selected as $term ) {
            $override = $overrides[ $term->slug ] ?? [];

            $children = [];
            foreach ( $by_parent[ (int) $term->term_id ] ?? [] as $child ) {
                if ( $hide_empty && (int) $child->count < 1 ) {
                    continue;
                }
                $children[] = $child->name;
            }

            if ( '' !== ( $override['sub'] ?? '' ) ) {
                $children = array_values( array_filter( array_map( 'trim', explode( ',', $override['sub'] ) ) ) );
            }

            $link = get_term_link( $term );

            $rows[] = [
                'id'       => (int) $term->term_id,
                'name'     => $term->name,
                'url'      => is_wp_error( $link ) ? '' : $link,
                'count'    => (int) $term->count,
                'image'    => ! empty( $override['image'] ) ? $override['image'] : $this->term_image( $term->term_id ),
                'icon'     => $override['icon'] ?? '',
                'desc'     => '' !== ( $override['desc'] ?? '' ) ? $override['desc'] : wp_strip_all_tags( $term->description ),
                'children' => $children,
            ];
        }

        return $rows;
    }

    /**
     * Flatten the Overrides repeater into slug => override map. Keying by term is what keeps an
     * override attached to its category when the order or the limit changes.
     *
     * @param  array $settings
     * @return array
     */
    private function build_overrides( array $settings ) {
        $map = [];
        foreach ( (array) ( $settings['overrides'] ?? [] ) as $row ) {
            $slug = (string) ( $row['override_term'] ?? '' );
            if ( '' === $slug ) {
                continue;
            }
            $map[ $slug ] = [
                'icon'  => ! empty( $row['override_icon']['value'] ) ? $row['override_icon'] : '',
                'image' => $row['override_image']['url'] ?? '',
                'sub'   => $row['override_sub'] ?? '',
                'desc'  => $row['override_desc'] ?? '',
            ];
        }
        return $map;
    }

    /**
     * A category's thumbnail, falling back to WooCommerce's own placeholder so a card is never a
     * broken box.
     *
     * @param  int $term_id
     * @return string
     */
    private function term_image( $term_id ) {
        $attachment_id = (int) get_term_meta( $term_id, 'thumbnail_id', true );

        if ( $attachment_id ) {
            $url = wp_get_attachment_image_url( $attachment_id, 'medium_large' );
            if ( $url ) {
                return $url;
            }
        }

        return function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '';
    }

    /**
     * Demo rows so the design is visible while building a page on a store that has no categories
     * yet. Editor only — the frontend renders nothing instead.
     *
     * @return array
     */
    private function placeholder_rows() {
        $image = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '';

        $names = [
            [ __( 'Dresses', 'woolentor' ),    128, [ __( 'Midi', 'woolentor' ), __( 'Maxi', 'woolentor' ), __( 'Slip', 'woolentor' ) ], __( 'Fluid shapes made to move with you.', 'woolentor' ) ],
            [ __( 'Outerwear', 'woolentor' ),   96, [ __( 'Coats', 'woolentor' ), __( 'Jackets', 'woolentor' ) ], __( 'Layers built for the long season.', 'woolentor' ) ],
            [ __( 'Footwear', 'woolentor' ),    74, [ __( 'Boots', 'woolentor' ), __( 'Sneakers', 'woolentor' ) ], __( 'Everyday soles, considered finish.', 'woolentor' ) ],
            [ __( 'Accessories', 'woolentor' ), 212, [ __( 'Bags', 'woolentor' ), __( 'Belts', 'woolentor' ) ], __( 'The details that finish a look.', 'woolentor' ) ],
            [ __( 'Knitwear', 'woolentor' ),    58, [ __( 'Sweaters', 'woolentor' ), __( 'Cardigans', 'woolentor' ) ], __( 'Soft weights for cooler rooms.', 'woolentor' ) ],
            [ __( 'Denim', 'woolentor' ),      140, [ __( 'Straight', 'woolentor' ), __( 'Wide Leg', 'woolentor' ) ], __( 'Honest cloth, cut every which way.', 'woolentor' ) ],
        ];

        $rows = [];
        foreach ( $names as $i => $entry ) {
            $rows[] = [
                'id'       => -( $i + 1 ),
                'name'     => $entry[0],
                'url'      => '',
                'count'    => $entry[1],
                'image'    => $image,
                'icon'     => '',
                'desc'     => $entry[3],
                'children' => $entry[2],
            ];
        }
        return $rows;
    }

    /**
     * The header block, cleared of anything the current variant's template does not draw. Hiding a
     * control does not clear what is stored under it, so a value typed on one variant would keep
     * rendering after switching to another.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return array
     */
    private function build_header( array $settings, $style, $variant ) {
        $draws_desc     = in_array( $variant, self::DESC_VARIANTS[ $style ] ?? [], true );
        $draws_view_all = in_array( $variant, self::VIEW_ALL_VARIANTS[ $style ] ?? [], true );
        $draws_button   = in_array( $variant, self::CARD_BUTTON_VARIANTS[ $style ] ?? [], true );

        return [
            'eyebrow'     => $settings['eyebrow'] ?? '',
            'headline'    => $settings['headline'] ?? '',
            'description' => $draws_desc ? ( $settings['description'] ?? '' ) : '',
            'card_button' => $draws_button ? ( $settings['card_button_text'] ?? '' ) : '',
            'view_all'    => [
                'text'        => $draws_view_all ? ( $settings['view_all_text'] ?? '' ) : '',
                'url'         => $draws_view_all ? ( $settings['view_all_link']['url'] ?? '' ) : '',
                'is_external' => ! empty( $settings['view_all_link']['is_external'] ),
                'nofollow'    => ! empty( $settings['view_all_link']['nofollow'] ),
            ],
        ];
    }

    // ── Template helpers ──────────────────────────────────────────────────────

    /**
     * Headline with the accent markup kept. Templates call $this->headline( $header['headline'] ).
     *
     * @param  string $text
     * @return string  Safe HTML.
     */
    protected function headline( $text ) {
        $allowed = [ 'br' => [], 'em' => [], 'strong' => [], 'b' => [], 'i' => [], 'span' => [ 'class' => [] ] ];
        return nl2br( wp_kses( $text, $allowed ) );
    }

    /**
     * The count line for one row — "312 products", or the bare number when the suffix is empty.
     * Returns an empty string when the count is switched off, so layouts collapse cleanly.
     *
     * @param  array $row
     * @param  array $settings
     * @return string  Escaped.
     */
    protected function count_text( array $row, array $settings ) {
        if ( 'yes' !== ( $settings['show_count'] ?? 'yes' ) ) {
            return '';
        }

        $suffix = trim( (string) ( $settings['count_suffix'] ?? '' ) );
        $number = number_format_i18n( $row['count'] );

        return esc_html( '' === $suffix ? $number : $number . ' ' . $suffix );
    }

    /**
     * Opening tag for one card. A row with a resolvable term link becomes an anchor, otherwise a
     * div — so placeholder and preview rows do not render dead links.
     *
     * @param  array  $row
     * @param  string $classes
     * @return string
     */
    protected function card_open( array $row, $classes = '' ) {
        $class = 'wl-sbc-card' . ( $classes ? ' ' . $classes : '' );

        if ( empty( $row['url'] ) ) {
            return '<div class="' . esc_attr( $class ) . '">';
        }

        return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $row['url'] ) . '">';
    }

    /**
     * Matching closing tag for card_open().
     *
     * @param  array $row
     * @return string
     */
    protected function card_close( array $row ) {
        return empty( $row['url'] ) ? '</div>' : '</a>';
    }

    /**
     * The header's "view all" link, or an empty string when the variant or the settings omit it.
     *
     * @param  array  $view_all
     * @param  string $icon  Optional trailing markup, already safe.
     * @return string
     */
    protected function view_all_link( array $view_all, $icon = '' ) {
        if ( '' === trim( (string) $view_all['text'] ) ) {
            return '';
        }

        $inner = '<span>' . esc_html( $view_all['text'] ) . '</span>' . $icon;

        if ( '' === $view_all['url'] ) {
            return '<span class="wl-sbc-viewall">' . $inner . '</span>';
        }

        $rel    = [];
        $target = '';
        if ( ! empty( $view_all['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }
        if ( ! empty( $view_all['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<a class="wl-sbc-viewall" href="' . esc_url( $view_all['url'] ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>' . $inner . '</a>';
    }

    /**
     * One row's override icon inside the shared wrapper. Empty when no override supplies one, so
     * icon-led layouts collapse the slot rather than drawing an empty box.
     *
     * @param  array $row
     * @return string
     */
    protected function row_icon( array $row ) {
        if ( empty( $row['icon']['value'] ) ) {
            return '';
        }

        ob_start();
        echo '<span class="wl-sbc-icon">';
        \Elementor\Icons_Manager::render_icon( $row['icon'], [ 'aria-hidden' => 'true' ] );
        echo '</span>';
        return ob_get_clean();
    }

    /**
     * The attributes that turn the grid into a Slick carousel, or an empty string on a static
     * variant. WLPackSlider in pack-widgets.js reads data-slider-settings and does the rest —
     * no second slider implementation ships with this widget.
     *
     * Templates call it as $this->slider_attrs( $settings, $style, $variant ) on the grid element.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function slider_attrs( array $settings, $style, $variant ) {
        if ( ! in_array( $variant, self::SLIDER_VARIANTS[ $style ] ?? [], true ) ) {
            return '';
        }

        $config = wp_json_encode( [
            'arrows'         => true,
            'dots'           => false,
            'infinite'       => 'yes' === ( $settings['slider_loop'] ?? '' ),
            'autoplay'       => 'yes' === ( $settings['slider_autoplay'] ?? '' ),
            'autoplay_speed' => max( 1000, absint( $settings['slider_autoplay_speed'] ?? 5000 ) ),
            'items'          => max( 1, absint( $settings['slides_per_view'] ?? 6 ) ),
            'scroll'         => 1,
            'tablet_width'   => 1024,
            'tablet_items'   => max( 1, absint( $settings['slides_per_view_tablet'] ?? 3 ) ),
            'tablet_scroll'  => 1,
            'mobile_width'   => 640,
            'mobile_items'   => max( 1, absint( $settings['slides_per_view_mobile'] ?? 2 ) ),
            'mobile_scroll'  => 1,
        ] );

        return ' data-wl-slider="true" data-slider-settings=\'' . esc_attr( $config ) . '\'';
    }

    /**
     * The arrow glyph the card and header links share.
     *
     * @param  int $size
     * @return string
     */
    protected function arrow( $size = 13 ) {
        return '<svg width="' . absint( $size ) . '" height="' . absint( $size ) . '" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Render the real template with demo rows so the user can see a Pro variant in the editor
     * before upgrading. Frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Shop by Category' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Shop by Category' );
            return;
        }

        $rows = $this->resolve_categories( $settings );
        if ( empty( $rows ) ) {
            $rows = $this->placeholder_rows();
        }

        $header = $this->build_header( $settings, $style, $variant );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-sbc wl-sbc-' . esc_attr( $style ) . ' wl-sbc-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
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

        $rows = $this->resolve_categories( $settings );

        // A store with no matching categories renders nothing on the frontend rather than an
        // empty grid shell. The editor gets demo rows so the section stays visible and editable.
        if ( empty( $rows ) ) {
            if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                return;
            }
            $rows = $this->placeholder_rows();
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Shop by Category template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $header = $this->build_header( $settings, $style, $variant );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-sbc wl-sbc-' . esc_attr( $style ) . ' wl-sbc-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
        echo '</div>';
    }
}
