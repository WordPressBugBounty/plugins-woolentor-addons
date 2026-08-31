<?php
/**
 * Product Showcase Widget — Pattern B (Style + Variant dropdowns).
 *
 * The product-listing section every pack homepage carries: eyebrow, headline, an optional tab
 * row, a set of product cards, and a link out to the shop. It is not the archive grid — that is
 * `includes/addons/product-grid/`, which keeps its grid/list toggle, its pagination and its
 * filter surface. This one is the section, and it can sit on a page, a shop archive or a
 * category archive without changing.
 *
 * No query is built here. Settings are handed to WooLentor_Product_Grid_Base, which is already
 * the adapter onto WooLentor_WooCommerce_Query_Manager — that is where query types, taxonomy
 * filters, the Product Filter module and archive awareness already live.
 *
 * All 12 style-variant templates consume one card contract. A template with no rating ignores
 * `rating`; one with no swatches ignores `swatches`. That is what makes switching Style or
 * Variant lossless — the data never changes shape when the design does.
 *
 * Spec: blueprint/product-showcase-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Product_Showcase_Widget extends Widget_Base implements \WooLentor\Product_Kit\Renders_Section {

    /**
     * Style + variant combinations whose header renders a description line. Only modern v1's
     * reference prints one, so everywhere else the control is hidden rather than silently ignored.
     */
    const DESC_VARIANTS = [
        'modern' => [ 'v1' ],
    ];

    /**
     * Style + variant combinations whose template draws a "view all" link. Editorial v1 is the
     * one adopted section with no link out at all.
     */
    const VIEW_ALL_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Style + variant combinations that print a result-count line under the header — modern v2's
     * "Showing 8 of 214 new styles".
     */
    const COUNT_LINE_VARIANTS = [
        'modern' => [ 'v2' ],
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

        // The tab handler needs an endpoint URL. It falls back to `woolentor_addons` and then to
        // the standard admin-ajax path, but neither is guaranteed on a page that carries only pack
        // widgets — so the handle states its own. The flag keeps a page with several pack widgets
        // on it from printing the same var once per widget.
        static $localized = false;

        if ( ! $localized ) {
            $localized = true;
            wp_localize_script( 'wl-pack-widgets', 'wlPackWidgets', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            ] );
        }
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-product-showcase-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/product-showcase/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-product-showcase';
    }

    public function get_title() {
        return esc_html__( 'Product Showcase - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-woocommerce';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'product', 'showcase', 'featured', 'new arrivals', 'best sellers', 'shop', 'woocommerce', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        $packs = array_map(
            fn( $pack ) => "wl-pack-product-showcase-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );

        // The kit's stylesheet carries the one state the shared script owns — how a section looks
        // while it is waiting for the server — and nothing this widget could style itself.
        $packs[] = \WooLentor\Product_Kit\Kit::asset_handles()['style'];

        return $packs;
    }

    public function get_script_depends() {
        return [ 'wl-pack-widgets', \WooLentor\Product_Kit\Kit::asset_handles()['script'] ];
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
     * AND several condition groups together.
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
                'product_showcase_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_header_controls();
        $this->register_query_controls();
        $this->register_pagination_controls();
        $this->register_tabs_controls();
        $this->register_card_controls();
        $this->register_layout_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    /** The header block above the cards. Every adopted variant has one. */
    private function register_header_controls() {
        $this->start_controls_section( 'section_header', [
            'label'      => esc_html__( 'Section Header', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'eyebrow', [
                'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Featured products', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'headline', [
                'label'       => esc_html__( 'Headline', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'default'     => esc_html__( 'Hand-picked, lab-tested, customer-loved.', 'woolentor' ),
                'description' => esc_html__( 'Wrap words in <em> to render them in the accent colour.', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'description', [
                'label'       => esc_html__( 'Description', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__( 'Our editors test every product for at least 30 days before it ranks on this grid — these are the ones that survived.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::DESC_VARIANTS ),
            ] );

            $this->add_control( 'result_count_text', [
                'label'       => esc_html__( 'Result Count Line', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'description' => esc_html__( 'Use {shown} for the number of cards and {total} for the number of matching products.', 'woolentor' ),
                'placeholder' => esc_html__( 'Showing {shown} of {total} new styles', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::COUNT_LINE_VARIANTS ),
            ] );

            $this->add_control( 'view_all_text', [
                'label'       => esc_html__( 'View All Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'View all products', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::VIEW_ALL_VARIANTS ),
            ] );

            $this->add_control( 'view_all_link', [
                'label'         => esc_html__( 'View All Link', 'woolentor' ),
                'type'          => Controls_Manager::URL,
                'placeholder'   => 'https://example.com/shop',
                'show_external' => true,
                'description'   => esc_html__( 'Leave empty to link to the WooCommerce shop page.', 'woolentor' ),
                'conditions'    => $this->build_conditions( self::VIEW_ALL_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    /**
     * Which products the section shows.
     *
     * Every control here is generated from the Product Kit's schema, so this widget and a
     * Gutenberg block reading the same file offer the same options under the same keys — and the
     * settings array both produce is the one Product_Query already understands.
     */
    private function register_query_controls() {
        \WooLentor\Product_Kit\Elementor_Controls::add_section( $this, 'query', [
            'section_id' => 'section_query',
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );
    }

    /**
     * Pagination. Its own section rather than an "Advanced" one, because on this widget it is a
     * content decision — how much of the catalogue the section hands over — not a technical detail.
     *
     * Off by default: a section is a section, and the eleven variants that draw a "View all
     * products" link already have a way out to the shop. Turning it on does not take that link
     * away — a store can offer both.
     *
     * Hidden on the one carousel variant, where a rail already scrolls through its own products
     * and a second pager underneath it would be two answers to one question.
     */
    private function register_pagination_controls() {
        \WooLentor\Product_Kit\Elementor_Controls::add_section( $this, 'pagination', [
            'section_id' => 'section_pagination',
            'conditions' => $this->all_of(
                [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'enable_slider', 'operator' => '!=', 'value' => 'yes' ],
                ] ],
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );
    }

    /**
     * The tab row. Six of the twelve references carry one, and they are of two kinds: query tabs
     * (New Arrivals / Best Sellers / Trending / Deals) and taxonomy tabs (Women / Men /
     * Accessories). One repeater covers both — a row says where its products come from, and
     * anything it leaves alone falls through to the section's own Products settings.
     *
     * Empty by default, which is what the other six references do: no tab row at all, one query.
     */
    private function register_tabs_controls() {
        \WooLentor\Product_Kit\Elementor_Controls::add_section( $this, 'tabs', [
            'section_id' => 'section_tabs',
            'conditions' => $this->all_of(
                [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'enable_slider', 'operator' => '!=', 'value' => 'yes' ],
                ] ],
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );
    }

    /**
     * Which parts of the card are drawn. Every one of the twelve designs uses a different subset,
     * so these are switches rather than variant-gated controls — a store that wants ratings off
     * on a variant whose reference has them should be able to say so.
     */
    private function register_card_controls() {
        $this->start_controls_section( 'section_card', [
            'label'      => esc_html__( 'Product Card', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'show_badges', [
                'label'        => esc_html__( 'Badges', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Sale, New and Featured. The Badges module takes over when it is enabled and has a badge for the product.', 'woolentor' ),
            ] );

            $this->add_control( 'new_badge_days', [
                'label'     => esc_html__( 'Count As New For (days)', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 7,
                'min'       => 1,
                'max'       => 365,
                'condition' => [ 'show_badges' => 'yes' ],
            ] );

            $this->add_control( 'show_discount_badge', [
                'label'        => esc_html__( 'Discount Percent Badge', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [ 'show_badges' => 'yes' ],
            ] );

            $this->add_control( 'show_secondary_image', [
                'label'        => esc_html__( 'Second Image On Hover', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ] );

            $this->add_control( 'show_wishlist', [
                'label'        => esc_html__( 'Wishlist Button', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Needs a supported wishlist plugin. Hidden when none is active.', 'woolentor' ),
            ] );

            $this->add_control( 'show_compare', [
                'label'        => esc_html__( 'Compare Button', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Needs a supported compare plugin. Hidden when none is active.', 'woolentor' ),
            ] );

            $this->add_control( 'show_quick_view', [
                'label'        => esc_html__( 'Quick View Button', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Needs the Quick View module. Hidden when it is off.', 'woolentor' ),
            ] );

            $this->add_control( 'quick_view_text', [
                'label'       => esc_html__( 'Quick View Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Quick view', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Most variants show the icon alone and keep this for screen readers. Magazine v3 prints it beside the icon.', 'woolentor' ),
                'condition'   => [ 'show_quick_view' => 'yes' ],
            ] );

            $this->add_control( 'show_add_to_cart', [
                'label'        => esc_html__( 'Add To Cart Button', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'add_to_cart_text', [
                'label'       => esc_html__( 'Add To Cart Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Quick Add', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Leave empty to use WooCommerce\'s own wording per product type.', 'woolentor' ),
                'condition'   => [ 'show_add_to_cart' => 'yes' ],
            ] );

            $this->add_control( 'show_brand', [
                'label'        => esc_html__( 'Brand / Category Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ] );

            $this->add_control( 'brand_source', [
                'label'     => esc_html__( 'Line Shows', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'category',
                'options'   => [
                    'category' => esc_html__( 'Product Category', 'woolentor' ),
                    'brand'    => esc_html__( 'Product Brand', 'woolentor' ),
                ],
                'condition' => [ 'show_brand' => 'yes' ],
            ] );

            $this->add_control( 'show_meta', [
                'label'        => esc_html__( 'Attribute Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'The small line under the title — "Performance Fabric · Sand / Graphite". Built from the product\'s visible attributes, and skipped on a product that has none.', 'woolentor' ),
            ] );

            $this->add_control( 'meta_attributes', [
                'label'     => esc_html__( 'Attributes To Show', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 2,
                'min'       => 1,
                'max'       => 5,
                'condition' => [ 'show_meta' => 'yes' ],
            ] );

            $this->add_control( 'show_tagline', [
                'label'        => esc_html__( 'Tagline', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'One italic line under the title, taken from the product\'s short description. A product without one collapses the slot.', 'woolentor' ),
            ] );

            $this->add_control( 'tagline_words', [
                'label'     => esc_html__( 'Tagline Length (words)', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 12,
                'min'       => 3,
                'max'       => 40,
                'condition' => [ 'show_tagline' => 'yes' ],
            ] );

            $this->add_control( 'show_rating', [
                'label'        => esc_html__( 'Star Rating', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'show_price', [
                'label'        => esc_html__( 'Price', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'show_stock', [
                'label'        => esc_html__( 'Stock Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'The "In stock" note magazine v2 prints beside the price. Reads WooCommerce\'s own stock status.', 'woolentor' ),
            ] );

            $this->add_control( 'show_swatches', [
                'label'        => esc_html__( 'Colour Swatches', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Read from a variable product\'s colour attribute. Simple products collapse the slot.', 'woolentor' ),
            ] );

            $this->add_control( 'swatch_attribute', [
                'label'       => esc_html__( 'Swatch Attribute', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'color',
                'description' => esc_html__( 'Attribute slug without the pa_ prefix. "color" matches pa_color.', 'woolentor' ),
                'condition'   => [ 'show_swatches' => 'yes' ],
            ] );

            $this->add_control( 'swatch_limit', [
                'label'     => esc_html__( 'Swatches To Show', 'woolentor' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 4,
                'min'       => 1,
                'max'       => 10,
                'condition' => [ 'show_swatches' => 'yes' ],
            ] );

        $this->end_controls_section();
    }

    private function register_layout_controls() {
        $this->start_controls_section( 'section_layout', [
            'label'      => esc_html__( 'Layout', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            // Left empty by default so each variant keeps the column count its reference used.
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
                    '{{WRAPPER}} .wl-ps-grid' => '--wl-ps-cols: {{VALUE}};',
                ],
                // A carousel sizes by cards-per-view instead; Slider Options has that control.
                'condition' => [ 'enable_slider!' => 'yes' ],
            ] );

        $this->end_controls_section();

        // A carousel sizes by cards-per-view, not by a CSS column count. The controls come from
        // the kit's schema, so a block carousel is configured by the same keys.
        \WooLentor\Product_Kit\Elementor_Controls::add_section( $this, 'slider', [
            'section_id' => 'section_slider',
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
            // Slick can only cross-fade one slide at a time, and a product rail shows several, so
            // the option would be a switch that either does nothing or breaks the layout.
            'skip'       => [ 'slider_fade' ],
        ] );
    }

    /** Content-tab notice shown in place of the editable sections on a Pro variant. */
    private function register_pro_content_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Products', 'woolentor' ),
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

        // Header ------------------------------------------------------------
        $this->start_controls_section( 'style_header', [
            'label'      => esc_html__( 'Header', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-ps-eyebrow',
            ] );

            $this->add_control( 'headline_color', [
                'label'     => esc_html__( 'Headline Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-ps-headline' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'headline_accent_color', [
                'label'     => esc_html__( 'Headline Accent Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-headline em' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'headline_typography',
                'selector' => '{{WRAPPER}} .wl-ps-headline',
            ] );

            $this->add_control( 'desc_color', [
                'label'     => esc_html__( 'Description Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-ps-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .wl-ps-desc',
            ] );

            $this->add_responsive_control( 'header_spacing', [
                'label'      => esc_html__( 'Space Below Header', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-ps-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // Card --------------------------------------------------------------
        $this->start_controls_section( 'style_card', [
            'label'      => esc_html__( 'Card', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'card_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-card' => 'background: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .wl-ps-card',
            ] );

            $this->add_responsive_control( 'card_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ps-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .wl-ps-card',
            ] );

            $this->add_responsive_control( 'card_body_padding', [
                'label'      => esc_html__( 'Body Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-ps-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'grid_gap', [
                'label'      => esc_html__( 'Gap Between Cards', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-grid' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // Image -------------------------------------------------------------
        $this->start_controls_section( 'style_image', [
            'label'      => esc_html__( 'Image', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'image_ratio', [
                'label'      => esc_html__( 'Aspect Ratio', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [ 'px' => [ 'min' => 0.5, 'max' => 2, 'step' => 0.05 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-media' => 'aspect-ratio: {{SIZE}};' ],
            ] );

            $this->add_control( 'image_bg', [
                'label'     => esc_html__( 'Backdrop', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-media' => 'background: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // Title & meta ------------------------------------------------------
        $this->start_controls_section( 'style_text', [
            'label'      => esc_html__( 'Title & Meta', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'title_color', [
                'label'     => esc_html__( 'Title Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-title, {{WRAPPER}} .wl-ps-title a' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .wl-ps-title, {{WRAPPER}} .wl-ps-title a',
            ] );

            $this->add_control( 'brand_color', [
                'label'     => esc_html__( 'Brand Line Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-ps-brand' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'brand_typography',
                'selector' => '{{WRAPPER}} .wl-ps-brand',
            ] );

        $this->end_controls_section();

        // Rating ------------------------------------------------------------
        //
        // Its own section rather than one colour buried under Title & Meta: the stars are the one
        // card part with two states to colour, and a store that shows them usually wants to size
        // them too.
        $this->start_controls_section( 'style_rating', [
            'label'      => esc_html__( 'Rating', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'rating_color', [
                'label'     => esc_html__( 'Star Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-rating svg' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'rating_empty_color', [
                'label'       => esc_html__( 'Empty Star Color', 'woolentor' ),
                'type'        => Controls_Manager::COLOR,
                'description' => esc_html__( 'Unrated stars are the star colour faded out until this is set.', 'woolentor' ),
                // The opacity goes with it: without that reset the chosen colour would still be
                // drawn at the 28% the stylesheet fades an empty star to, and never match.
                'selectors'   => [ '{{WRAPPER}} .wl-ps-rating svg.star.empty' => 'color: {{VALUE}}; opacity: 1;' ],
            ] );

            $this->add_responsive_control( 'rating_size', [
                'label'      => esc_html__( 'Star Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'separator'  => 'before',
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 36 ] ],
                // `.star` rather than `svg` alone: the stylesheet sizes these at 1em, and a bare
                // element selector would tie with it rather than win.
                'selectors'  => [ '{{WRAPPER}} .wl-ps-rating svg.star' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'rating_gap', [
                'label'      => esc_html__( 'Spacing', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 20 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-rating' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'rating_text_color', [
                'label'     => esc_html__( 'Average & Count Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-ps-rating-avg, {{WRAPPER}} .wl-ps-rating-count' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'rating_text_typography',
                'selector' => '{{WRAPPER}} .wl-ps-rating-avg, {{WRAPPER}} .wl-ps-rating-count',
            ] );

        $this->end_controls_section();

        // Attribute Strip ---------------------------------------------------
        //
        // Luxury V1 only — it is the one card that carries the scrolling attribute strip across the
        // foot of its image, so the section is conditioned on that combination rather than offered
        // everywhere and doing nothing.
        //
        // Every selector names `.wl-ps-luxury-v1` as well. The pack stylesheet already styles these
        // chips at two classes deep, and a selector that merely ties with it would leave the
        // control looking broken.
        $this->start_controls_section( 'style_tagstrip', [
            'label'      => esc_html__( 'Attribute Strip', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( [ 'luxury' => [ 'v1' ] ] ),
        ] );

            $this->add_control( 'tagstrip_bg', [
                'label'     => esc_html__( 'Strip Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tagstrip' => 'background: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'tagstrip_height', [
                'label'      => esc_html__( 'Strip Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 20, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tagstrip' => 'height: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'tagstrip_speed', [
                'label'       => esc_html__( 'Scroll Duration (s)', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 4,
                'max'         => 120,
                'description' => esc_html__( 'How long one full loop takes. A larger number scrolls more slowly.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tagtrack' => 'animation-duration: {{VALUE}}s;' ],
            ] );

            $this->add_control( 'tagstrip_chip_heading', [
                'label'     => esc_html__( 'Chips', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'tag_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'tag_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'tag_border_color', [
                'label'     => esc_html__( 'Border Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag' => 'border-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'tag_typography',
                'selector' => '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag',
            ] );

            $this->add_responsive_control( 'tag_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'tag_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'tag_gap', [
                'label'      => esc_html__( 'Space Between', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-luxury-v1 .wl-ps-tag' => 'margin-left: calc({{SIZE}}{{UNIT}} / 2); margin-right: calc({{SIZE}}{{UNIT}} / 2);' ],
            ] );

        $this->end_controls_section();

        // Price -------------------------------------------------------------
        $this->start_controls_section( 'style_price', [
            'label'      => esc_html__( 'Price', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'price_color', [
                'label'     => esc_html__( 'Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-price' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'price_was_color', [
                'label'     => esc_html__( 'Old Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-price del' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'price_typography',
                'selector' => '{{WRAPPER}} .wl-ps-price',
            ] );

        $this->end_controls_section();

        // Buttons -----------------------------------------------------------
        $this->start_controls_section( 'style_buttons', [
            'label'      => esc_html__( 'Buttons', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->start_controls_tabs( 'button_tabs' );

                $this->start_controls_tab( 'button_tab_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'cart_color', [
                        'label'     => esc_html__( 'Cart Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ps-cart' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cart_bg', [
                        'label'     => esc_html__( 'Cart Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ps-cart' => 'background: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'icon_btn_color', [
                        'label'     => esc_html__( 'Icon Button Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'separator' => 'before',
                        'selectors' => [ '{{WRAPPER}} .wl-ps-iconbtn' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'icon_btn_bg', [
                        'label'     => esc_html__( 'Icon Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ps-iconbtn' => 'background: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'button_tab_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'cart_color_hover', [
                        'label'     => esc_html__( 'Cart Button Text', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ps-cart:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cart_bg_hover', [
                        'label'     => esc_html__( 'Cart Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ps-cart:hover' => 'background: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'icon_btn_color_hover', [
                        'label'     => esc_html__( 'Icon Button Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'separator' => 'before',
                        'selectors' => [ '{{WRAPPER}} .wl-ps-iconbtn:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'icon_btn_bg_hover', [
                        'label'     => esc_html__( 'Icon Button Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-ps-iconbtn:hover' => 'background: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // Badges ------------------------------------------------------------
        $this->start_controls_section( 'style_badge', [
            'label'      => esc_html__( 'Badges', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'badge_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-badge' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'badge_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-badge' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'badge_sale_bg', [
                'label'     => esc_html__( 'Sale Badge Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-badge--sale' => 'background: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'badge_typography',
                'selector' => '{{WRAPPER}} .wl-ps-badge',
            ] );

        $this->end_controls_section();

        // View all ----------------------------------------------------------
        $this->start_controls_section( 'style_view_all', [
            'label'      => esc_html__( 'View All Link', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                $this->build_conditions( self::VIEW_ALL_VARIANTS ),
                $unlocked
            ),
        ] );

            $this->add_control( 'view_all_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-viewall' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'view_all_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-viewall' => 'background: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'view_all_typography',
                'selector' => '{{WRAPPER}} .wl-ps-viewall',
            ] );

        $this->end_controls_section();

        // Pagination --------------------------------------------------------
        // One set of controls for all three types: the number links, the Load More button and the
        // infinite-scroll spinner share .wl-ps-page as their skin, so a store that switches type
        // does not have to re-style the section.
        $this->start_controls_section( 'style_pagination', [
            'label'      => esc_html__( 'Pagination', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'enable_slider', 'operator' => '!=', 'value' => 'yes' ],
                ] ],
                $unlocked,
                [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'enable_pagination', 'operator' => '==', 'value' => 'yes' ],
                ] ]
            ),
        ] );

            $this->add_control( 'pagination_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-page' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'pagination_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-ps-page' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'pagination_active_color', [
                'label'     => esc_html__( 'Active / Hover Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-ps-page:hover'    => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wl-ps-page.current'  => 'color: {{VALUE}};',
                ],
            ] );

            $this->add_control( 'pagination_active_bg', [
                'label'     => esc_html__( 'Active / Hover Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-ps-page:hover'   => 'background: {{VALUE}};',
                    '{{WRAPPER}} .wl-ps-page.current' => 'background: {{VALUE}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'pagination_typography',
                'selector' => '{{WRAPPER}} .wl-ps-page',
            ] );

            $this->add_responsive_control( 'pagination_gap', [
                'label'      => esc_html__( 'Space Above', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-ps-pagination' => 'margin-top: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // Slider controller -------------------------------------------------
        // The one carousel variant's arrows and dots. The section comes from the kit, so this
        // widget and Hero Banner style a carousel through the same controls under the same ids.
        \WooLentor\Product_Kit\Elementor_Controls::add_slider_style_section( $this, [
            'conditions'    => $this->all_of(
                [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'enable_slider', 'operator' => '==', 'value' => 'yes' ],
                ] ],
                $unlocked
            ),
            // A product rail's dots sit under the track, not over a photograph, so the control that
            // moves them is the gap above — `left` would do nothing here.
            'dots_position' => 'stack',
        ] );
    }

    // ── Product Kit ───────────────────────────────────────────────────────────
    //
    // The query, the pagination, the tab row and the carousel all live in
    // includes/product-kit/, which knows nothing about Elementor. What is left here is the part
    // that is genuinely this widget's: which of its twelve layouts draws which of those things.

    /**
     * Every variant can carry a tab row. Eleven of the twelve references had one drawn or a skin
     * already written for one; luxury v1, the rail, was the only exception and now has both.
     */
    private function has_tabs( array $settings ) {
        return ! $this->is_slider( $settings );
    }

    /**
     * Is this section a carousel? Every variant can be — it is a Display choice now, not a property
     * of the design — and the choice is Pro, so a free site always answers no.
     */
    private function is_slider( array $settings ) {
        return \WooLentor\Product_Kit\Slider::enabled( $settings );
    }

    /**
     * A carousel already scrolls through its own products; a pager underneath it would be two
     * answers to one question.
     */
    private function has_pager( array $settings ) {
        return ! $this->is_slider( $settings );
    }

    /**
     * The products for this section.
     *
     * @param  array $settings
     * @param  int   $paged  Zero when the section is not paginated, which is not page one.
     * @return \WP_Query|null
     */
    private function get_showcase_products( array $settings, $paged = 0 ) {
        return \WooLentor\Product_Kit\Product_Query::get( $settings, $paged );
    }

    /**
     * The tab rows for this variant, or an empty array when the variant has no tab row or the user
     * has not added any. `$active` is which one is showing.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  int    $active
     * @return array
     */
    private function build_tabs( array $settings, $style, $variant, $active = 0 ) {
        return \WooLentor\Product_Kit\Tabs::build( $settings, $active, $this->has_tabs( $settings ) );
    }

    /**
     * The attributes the section endpoint needs to call back, or an empty string when nothing on
     * the page can call back — no tab row and no AJAX pager means no endpoint surface at all.
     *
     * @param  array  $tabs
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function endpoint_attrs( array $tabs, array $settings ) {
        $pagination = \WooLentor\Product_Kit\Pagination::settings( $settings, $this->has_pager( $settings ) );
        $ajax_pager = $pagination['enabled'] && in_array( $pagination['type'], [ 'load_more', 'infinite' ], true );

        return \WooLentor\Product_Kit\Section_Ajax::attrs( [
            'provider'   => \WooLentor\Product_Kit\Elementor_Provider::NAME,
            'post_id'    => \WooLentor\Product_Kit\Elementor_Provider::current_post_id(),
            'section_id' => $this->get_id(),
            'enabled'    => ! empty( $tabs ) || $ajax_pager,
            'filters'    => \WooLentor\Product_Kit\Filter::responds( $settings ),
        ] );
    }

    /**
     * The pager under the cards.
     *
     * @param  \WP_Query|null $products
     * @param  array          $settings
     * @param  string         $style
     * @param  string         $variant
     * @param  int            $active_tab
     * @param  int            $base_post
     * @return string
     */
    protected function pagination_html( $products, array $settings, $active_tab = -1, $base_post = 0 ) {
        return \WooLentor\Product_Kit\Pagination::html( $products, $settings, [
            'prefix'     => 'wl-ps',
            'section_id' => $this->get_id(),
            'active_tab' => $active_tab,
            'base_post'  => $base_post,
            'available'  => $this->has_pager( $settings ),
        ] );
    }

    /**
     * The header block, cleared of anything the current variant's template does not draw. Hiding
     * a control does not clear what is stored under it, so a value typed on one variant would
     * keep rendering after switching to another.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  int    $shown
     * @param  int    $total
     * @return array
     */
    private function build_header( array $settings, $style, $variant, $shown = 0, $total = 0 ) {
        $draws_desc     = in_array( $variant, self::DESC_VARIANTS[ $style ] ?? [], true );
        $draws_view_all = in_array( $variant, self::VIEW_ALL_VARIANTS[ $style ] ?? [], true );
        $draws_count    = in_array( $variant, self::COUNT_LINE_VARIANTS[ $style ] ?? [], true );

        $count_line = '';
        if ( $draws_count && '' !== trim( (string) ( $settings['result_count_text'] ?? '' ) ) ) {
            $count_line = strtr( $settings['result_count_text'], [
                '{shown}' => number_format_i18n( $shown ),
                '{total}' => number_format_i18n( $total ),
            ] );
        }

        $url = $settings['view_all_link']['url'] ?? '';
        if ( '' === $url && function_exists( 'wc_get_page_permalink' ) ) {
            $url = (string) wc_get_page_permalink( 'shop' );
        }

        return [
            'eyebrow'     => $settings['eyebrow'] ?? '',
            'headline'    => $settings['headline'] ?? '',
            'description' => $draws_desc ? ( $settings['description'] ?? '' ) : '',
            'count_line'  => $count_line,
            'view_all'    => [
                'text'        => $draws_view_all ? ( $settings['view_all_text'] ?? '' ) : '',
                'url'         => $draws_view_all ? $url : '',
                'is_external' => ! empty( $settings['view_all_link']['is_external'] ),
                'nofollow'    => ! empty( $settings['view_all_link']['nofollow'] ),
            ],
        ];
    }

    /**
     * Path to the variant's single-card partial. The section template includes it in its loop and
     * the tab endpoint includes it in its own, which is what keeps a tab switch byte-identical to
     * the first page load.
     *
     * @param  string $style
     * @param  string $variant
     * @return string  Empty when the variant has no partial.
     */
    protected function card_template( $style, $variant ) {
        $style   = \WooLentor\Style_Pack_Manager::sanitize_pack( $style, 'modern' );
        $variant = \WooLentor\Style_Pack_Manager::sanitize_variant( $variant );

        $base      = trailingslashit( wp_normalize_path( $this->templates_dir() ) );
        $candidate = wp_normalize_path( $base . $style . '/' . $variant . '-card.php' );

        if ( ! file_exists( $candidate ) ) {
            return '';
        }

        $real_base   = wp_normalize_path( realpath( $base ) );
        $real_target = wp_normalize_path( realpath( $candidate ) );

        if ( ! $real_target || strpos( $real_target, $real_base ) !== 0 ) {
            return '';
        }

        return $real_target;
    }

    /**
     * Where this build's templates live. The Pro mirror overrides it to the free plugin's copy.
     *
     * @return string
     */
    protected function templates_dir() {
        return __DIR__ . '/templates';
    }

    // ── Template helpers: section ─────────────────────────────────────────────

    /**
     * Headline with the accent markup kept.
     *
     * @param  string $text
     * @return string  Safe HTML.
     */
    protected function headline( $text ) {
        $allowed = [ 'br' => [], 'em' => [], 'strong' => [], 'b' => [], 'i' => [], 'span' => [ 'class' => [] ] ];
        return nl2br( wp_kses( $text, $allowed ) );
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
            return '<span class="wl-ps-viewall">' . $inner . '</span>';
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

        return '<a class="wl-ps-viewall" href="' . esc_url( $view_all['url'] ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>' . $inner . '</a>';
    }

    /**
     * The tab row, or an empty string when the section has none.
     *
     * Each button carries its own index and nothing else — the query behind a tab is never sent to
     * the browser, so it cannot be edited there. The endpoint reads the saved settings back from
     * the page it belongs to.
     *
     * @param  array $tabs
     * @return string
     */
    protected function tab_row( array $tabs ) {
        return \WooLentor\Product_Kit\Tabs::row_html( $tabs, [ 'prefix' => 'wl-ps' ] );
    }

    /**
     * What an empty tab says. One string, used by both the first page load and the tab endpoint,
     * so a visitor never sees two different wordings for the same situation.
     *
     * @param  string $message  A different wording for a different empty — a filter that matched
     *                          nothing is not an empty tab.
     * @return string
     */
    public function empty_notice_html( $message = '' ) {
        $message = '' !== $message ? $message : __( 'No products in this tab yet.', 'woolentor' );

        return '<p class="wl-ps-empty">' . esc_html( $message ) . '</p>';
    }

    /**
     * The empty message when the section rendered no cards, or nothing when it did. Templates call
     * it at the end of the grid — it only ever fires on a tabbed section, because a section
     * without tabs does not render at all when its query comes back empty.
     *
     * @param  \WP_Query|null $products
     * @return string
     */
    protected function empty_notice( $products ) {
        return ( $products && $products->post_count ) ? '' : $this->empty_notice_html();
    }

    /**
     * Render the card list for one tab and one page — the Product Kit's Renders_Section contract.
     *
     * The section endpoint calls this, and it goes through the same card partial the first page
     * load used, so a switched tab or an appended page is not a second rendering of the card, it is
     * the same one. The settings arrive from the saved document, never from the browser.
     *
     * @param  array $settings
     * @param  array $context
     * @return array
     */
    public function render_section( array $settings, array $context ) {
        $empty   = [ 'html' => '', 'pagination' => '' ];
        $style   = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        // A locked variant has no cards to hand out.
        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            return $empty;
        }

        $tab   = (int) ( $context['tab'] ?? -1 );
        $paged = max( 1, (int) ( $context['paged'] ?? 1 ) );

        // A tab index is honoured only on a variant that draws a tab row, and only when that tab
        // exists — so a request naming one cannot reach a query the page never offered.
        if ( $tab >= 0 ) {
            $tab_settings = \WooLentor\Product_Kit\Tabs::settings_for( $settings, $tab, $this->has_tabs( $settings ) );

            if ( null === $tab_settings ) {
                return $empty;
            }

            $settings = $tab_settings;
        }

        $card = $this->card_template( $style, $variant );

        if ( '' === $card ) {
            return $empty;
        }

        $paginated = \WooLentor\Product_Kit\Pagination::settings( $settings, $this->has_pager( $settings ) );
        $products  = $this->get_showcase_products( $settings, $paginated['enabled'] ? $paged : 0 );

        $html = '';

        if ( $products && $products->have_posts() ) {
            // A rail never reaches this endpoint — a carousel has neither a tab row nor an AJAX
            // pager — but the shell is emitted anyway so the two paths cannot drift if it ever does.
            $rail = $this->is_rail( $settings );

            ob_start();

            while ( $products->have_posts() ) {
                $products->the_post();

                global $product;

                if ( ! is_a( $product, 'WC_Product' ) ) {
                    continue;
                }

                if ( $rail ) {
                    echo '<div class="wl-ps-slide">';
                }

                include $card;

                if ( $rail ) {
                    echo '</div>';
                }
            }

            $html = ob_get_clean();
        }

        wp_reset_postdata();

        // An empty *first* page of a tabbed section says so. An empty page two is the end of the
        // list, not an empty tab, and appending "no products" under a full grid would be a lie
        // about the store.
        if ( '' === trim( $html ) && 1 === $paged ) {
            if ( ! empty( $settings[ \WooLentor\Product_Kit\Filter::ARG_KEY ] ) ) {
                // A filter that matched nothing has to say so. Clearing the list and leaving the
                // space blank reads as a section that broke.
                $html = $this->empty_notice_html( __( 'No products match these filters.', 'woolentor' ) );
            } elseif ( $tab >= 0 ) {
                $html = $this->empty_notice_html();
            }
        }

        return [
            'html'       => $html,
            'pagination' => $this->pagination_html( $products, $settings, $tab, (int) ( $context['post_id'] ?? 0 ) ),
        ];
    }

    /**
     * The card list's own attributes: the marker the shared script binds to, plus the carousel
     * configuration on the one variant that is a rail.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function grid_attrs( array $settings, $style = '', $variant = '' ) {
        return ' data-wl-section-list'
            . \WooLentor\Product_Kit\Slider::attrs( $settings, [ 'enabled' => $this->is_slider( $settings ) ] );
    }

    /**
     * Is this render a carousel? Templates ask so they can put the rail wrapper and the per-card
     * shell around what they already draw — Slick promotes a track's direct children as they are,
     * so the gap has to live on a shell rather than on the card.
     *
     * @param  array $settings
     * @return bool
     */
    public function is_rail( array $settings ) {
        return $this->is_slider( $settings );
    }

    /**
     * The arrow glyph the section links share.
     *
     * @param  int $size
     * @return string
     */
    protected function arrow( $size = 16 ) {
        return '<svg width="' . absint( $size ) . '" height="' . absint( $size ) . '" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
    }

    // ── Template helpers: card ────────────────────────────────────────────────

    /**
     * The badge stack. The Badges module wins when it is enabled and has something to say about
     * this product — the same precedence the product grid templates already use, so a store does
     * not see two badge systems disagree on one page.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_badges( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_badges'] ?? 'yes' ) ) {
            return '';
        }

        if ( class_exists( '\Woolentor\Modules\Badges\Frontend\Badge_Manager' ) ) {
            $module_badges = \Woolentor\Modules\Badges\Frontend\Badge_Manager::instance()->product_badges();
            if ( ! empty( $module_badges ) ) {
                return '<div class="wl-ps-badges wl-ps-badges--module">' . $module_badges . '</div>';
            }
        }

        $badges = [];

        if ( $product->is_featured() ) {
            $badges[] = '<span class="wl-ps-badge wl-ps-badge--hot">' . esc_html__( 'Best Seller', 'woolentor' ) . '</span>';
        }

        $days = absint( $settings['new_badge_days'] ?? 7 );
        if ( $days ) {
            $published = (int) get_the_date( 'U', $product->get_id() );
            if ( $published && ( time() - $published ) <= ( $days * DAY_IN_SECONDS ) ) {
                $badges[] = '<span class="wl-ps-badge wl-ps-badge--new">' . esc_html__( 'New', 'woolentor' ) . '</span>';
            }
        }

        if ( $product->is_on_sale() ) {
            $off = $this->discount_percent( $product );
            if ( '' !== $off && 'yes' === ( $settings['show_discount_badge'] ?? 'yes' ) ) {
                $badges[] = '<span class="wl-ps-badge wl-ps-badge--sale">' . esc_html( $off ) . '</span>';
            } else {
                $badges[] = '<span class="wl-ps-badge wl-ps-badge--sale">' . esc_html__( 'Sale', 'woolentor' ) . '</span>';
            }
        }

        return $badges ? '<div class="wl-ps-badges">' . implode( '', $badges ) . '</div>' : '';
    }

    /**
     * "-24%", or an empty string when the product has no usable regular price to measure against.
     * Variable products are measured on the range's own regular and sale prices.
     *
     * @param  \WC_Product $product
     * @return string
     */
    protected function discount_percent( $product ) {
        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();

        if ( $product->is_type( 'variable' ) ) {
            $regular = (float) $product->get_variation_regular_price( 'max' );
            $sale    = (float) $product->get_variation_sale_price( 'min' );
        }

        if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
            return '';
        }

        return '-' . round( ( ( $regular - $sale ) / $regular ) * 100 ) . '%';
    }

    /**
     * The product image, with the gallery's first image stacked behind it for the hover swap.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  string      $size
     * @return string
     */
    protected function card_media( $product, array $settings, $size = 'woocommerce_thumbnail' ) {
        $main = $product->get_image( $size, [ 'class' => 'wl-ps-img', 'loading' => 'lazy' ] );

        if ( '' === $main ) {
            $main = '<img class="wl-ps-img" src="' . esc_url( wc_placeholder_img_src( $size ) ) . '" alt="'
                . esc_attr( $product->get_name() ) . '" loading="lazy">';
        }

        // woolentor_product_secondary_image() is deliberately not used here: it echoes rather than
        // returning, and it wraps the image in its own <a> — which would nest an anchor inside the
        // card's image link. The gallery's first image is read straight instead.
        $second  = '';
        $gallery = $product->get_gallery_image_ids();
        if ( 'yes' === ( $settings['show_secondary_image'] ?? 'yes' ) && ! empty( $gallery[0] ) ) {
            $hover = wp_get_attachment_image( $gallery[0], $size, false, [
                'class'   => 'wl-ps-img',
                'loading' => 'lazy',
                'alt'     => '',
            ] );
            if ( $hover ) {
                $second = $hover;
            }
        }

        return '<span class="wl-ps-media">' . $main
            . ( $second ? '<span class="wl-ps-img-second">' . $second . '</span>' : '' )
            . '</span>';
    }

    /**
     * Wishlist, compare and quick view. Each is drawn only when its own plugin or module is
     * actually active — the widget asks, it does not assume, and it does not ship a second
     * implementation of any of the three.
     *
     * $parts lets a template take only the buttons its reference puts in that corner: modern v1
     * stacks wishlist and compare over the image but sits quick view next to Quick Add along the
     * bottom, so it asks for them in two calls.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  array       $parts     Any of 'wishlist', 'compare', 'quickview'.
     * @return string
     */
    protected function card_icon_actions( $product, array $settings, array $parts = [ 'wishlist', 'compare', 'quickview' ] ) {
        $built = [];

        if ( 'yes' === ( $settings['show_wishlist'] ?? 'yes' )
            && function_exists( 'woolentor_add_to_wishlist_button' )
            && function_exists( 'woolentor_has_wishlist_plugin' )
            && true === woolentor_has_wishlist_plugin() ) {

            $normal = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $added  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';

            $built['wishlist'] = '<span class="wl-ps-iconbtn wl-ps-wish">' . woolentor_add_to_wishlist_button( $normal, $added, 'yes' ) . '</span>';
        }

        if ( 'yes' === ( $settings['show_compare'] ?? 'yes' )
            && function_exists( 'woolentor_compare_button' )
            && function_exists( 'woolentor_exist_compare_plugin' )
            && true === woolentor_exist_compare_plugin() ) {

            $icon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3L4 7l4 4"/><line x1="4" y1="7" x2="20" y2="7"/><path d="M16 21l4-4-4-4"/><line x1="20" y1="17" x2="4" y2="17"/></svg>';

            ob_start();
            echo '<span class="wl-ps-iconbtn wl-ps-compare">';
            woolentor_compare_button( [
                'style'         => 2,
                'btn_text'      => $icon,
                'btn_added_txt' => $icon,
            ] );
            echo '</span>';
            $built['compare'] = ob_get_clean();
        }

        if ( 'yes' === ( $settings['show_quick_view'] ?? 'yes' )
            && function_exists( 'woolentor_has_quickview' )
            && true === woolentor_has_quickview() ) {

            // The label is always in the markup and hidden by default. Magazine v3 is the one
            // variant that prints it beside the icon, and a class switch is cheaper than a second
            // button; everywhere else it remains the button's accessible name.
            $qv_label = trim( (string) ( $settings['quick_view_text'] ?? '' ) );
            $qv_label = '' !== $qv_label ? $qv_label : __( 'Quick view', 'woolentor' );

            $built['quickview'] = '<button type="button" class="wl-ps-iconbtn wl-ps-qview woolentorquickview"'
                . ' data-product_id="' . esc_attr( $product->get_id() ) . '"'
                . ' aria-label="' . esc_attr( sprintf( /* translators: 1: quick view label, 2: product name */ __( '%1$s: %2$s', 'woolentor' ), $qv_label, $product->get_name() ) ) . '">'
                . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
                . '<span class="wl-ps-btn-label">' . esc_html( $qv_label ) . '</span>'
                . '</button>';
        }

        // Emitted in the order the template asked for, not the order they were built. Modern v3
        // puts quick view first in its bar; v1 and v2 stack wishlist above compare.
        $out = '';
        foreach ( $parts as $part ) {
            $out .= $built[ $part ] ?? '';
        }

        return $out;
    }

    /**
     * WooCommerce's own loop add-to-cart, so variable products still go to the product page and
     * ajax simple products still add in place. Only the label is ours.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  string      $icon  Optional leading markup, already safe.
     * @return string
     */
    protected function card_cart( $product, array $settings, $icon = '' ) {
        if ( 'yes' !== ( $settings['show_add_to_cart'] ?? 'yes' ) ) {
            return '';
        }

        $label = trim( (string) ( $settings['add_to_cart_text'] ?? '' ) );

        $text_filter = null;
        if ( '' !== $label ) {
            $text_filter = function () use ( $label ) {
                return $label;
            };
            add_filter( 'woocommerce_product_add_to_cart_text', $text_filter, 99 );
        }

        // The card's class is appended, never passed as `class` in the args: WooCommerce merges
        // those with wp_parse_args, so supplying one replaces its whole default string — and with
        // it `button`, `add_to_cart_button`, `ajax_add_to_cart` and `product_type_*`. Losing those
        // silently turns off ajax add-to-cart and every bit of theme styling on the button.
        $args_filter = function ( $args ) {
            $args['class'] = trim( ( $args['class'] ?? '' ) . ' wl-ps-cart' );
            return $args;
        };
        add_filter( 'woocommerce_loop_add_to_cart_args', $args_filter, 99 );

        ob_start();
        woocommerce_template_loop_add_to_cart();
        $html = ob_get_clean();

        remove_filter( 'woocommerce_loop_add_to_cart_args', $args_filter, 99 );

        if ( $text_filter ) {
            remove_filter( 'woocommerce_product_add_to_cart_text', $text_filter, 99 );
        }

        if ( '' === trim( $html ) ) {
            return '';
        }

        // The glyph belongs inside WooCommerce's own anchor, next to the label — placing it beside
        // the button instead leaves it floating outside the pill. WooCommerce owns the anchor, so
        // the only way in is straight after its opening tag.
        if ( '' !== $icon ) {
            $html = preg_replace( '/(<a\b[^>]*>)/', '$1' . $icon, $html, 1 );
        }

        return '<span class="wl-ps-cart-wrap">' . $html . '</span>';
    }

    /**
     * The line above the title — a category name by default, a brand when the store has a brand
     * taxonomy and the control asks for one.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_brand( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_brand'] ?? 'yes' ) ) {
            return '';
        }

        $taxonomy = 'category';
        if ( 'brand' === ( $settings['brand_source'] ?? 'category' ) ) {
            foreach ( [ 'product_brand', 'pwb-brand', 'yith_product_brand' ] as $candidate ) {
                if ( taxonomy_exists( $candidate ) ) {
                    $taxonomy = $candidate;
                    break;
                }
            }
        }

        if ( 'category' === $taxonomy ) {
            $taxonomy = 'product_cat';
        }

        $terms = get_the_terms( $product->get_id(), $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        return '<span class="wl-ps-brand">' . esc_html( $terms[0]->name ) . '</span>';
    }

    /**
     * The attribute line under the title — modern v3's "Performance Fabric · Sand / Graphite".
     *
     * Built from the product's own visible attributes, values rather than labels, because that is
     * what the reference prints. A product with no visible attributes collapses the slot instead
     * of drawing an empty line, and the swatch attribute is skipped: the dots already say it.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_meta( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_meta'] ?? 'yes' ) ) {
            return '';
        }

        $limit  = max( 1, absint( $settings['meta_attributes'] ?? 2 ) );
        $swatch = sanitize_title( $settings['swatch_attribute'] ?? 'color' );
        $swatch = 0 === strpos( $swatch, 'pa_' ) ? $swatch : 'pa_' . $swatch;

        $parts = [];

        foreach ( $product->get_attributes() as $attribute ) {
            if ( count( $parts ) >= $limit ) {
                break;
            }

            if ( ! is_a( $attribute, 'WC_Product_Attribute' ) || ! $attribute->get_visible() ) {
                continue;
            }

            if ( 'yes' === ( $settings['show_swatches'] ?? 'yes' ) && $attribute->get_name() === $swatch ) {
                continue;
            }

            $value = $product->get_attribute( $attribute->get_name() );

            if ( '' === $value ) {
                continue;
            }

            // WooCommerce joins multiple terms with ", ". The reference prints a slash.
            $parts[] = str_replace( ', ', ' / ', $value );
        }

        if ( empty( $parts ) ) {
            return '';
        }

        return '<p class="wl-ps-meta-line">' . esc_html( implode( ' · ', $parts ) ) . '</p>';
    }

    /**
     * The same attributes as card_meta(), but as separate chips — luxury v1 runs them along a dark
     * strip at the foot of its image. Returns the chips only; the marquee track is the template's,
     * because a seamless loop needs the set printed twice and only the template knows that.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  int         $limit  0 for every visible attribute.
     * @return string
     */
    protected function card_tags( $product, array $settings, $limit = 0 ) {
        if ( 'yes' !== ( $settings['show_meta'] ?? 'yes' ) ) {
            return '';
        }

        $swatch = sanitize_title( $settings['swatch_attribute'] ?? 'color' );
        $swatch = 0 === strpos( $swatch, 'pa_' ) ? $swatch : 'pa_' . $swatch;

        $chips = '';
        $count = 0;

        foreach ( $product->get_attributes() as $attribute ) {
            if ( $limit && $count >= $limit ) {
                break;
            }

            if ( ! is_a( $attribute, 'WC_Product_Attribute' ) || ! $attribute->get_visible() ) {
                continue;
            }

            if ( 'yes' === ( $settings['show_swatches'] ?? 'yes' ) && $attribute->get_name() === $swatch ) {
                continue;
            }

            $value = $product->get_attribute( $attribute->get_name() );

            if ( '' === $value ) {
                continue;
            }

            // Each term is its own chip, so "Small, Medium, Large" becomes three.
            foreach ( array_filter( array_map( 'trim', explode( ',', $value ) ) ) as $term ) {
                $chips .= '<span class="wl-ps-tag">' . esc_html( $term ) . '</span>';
            }

            $count++;
        }

        return $chips;
    }

    /**
     * The italic one-liner editorial v1 prints between the title and the price — the product's
     * short description, trimmed to a line. The full description is not used as a fallback: a
     * paragraph or two of body copy in a card slot is worse than no line at all.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_tagline( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_tagline'] ?? 'yes' ) ) {
            return '';
        }

        $text = trim( wp_strip_all_tags( $product->get_short_description() ) );

        if ( '' === $text ) {
            return '';
        }

        $words = max( 3, absint( $settings['tagline_words'] ?? 12 ) );

        return '<p class="wl-ps-tagline"><em>' . esc_html( wp_trim_words( $text, $words ) ) . '</em></p>';
    }

    /**
     * Stars plus the average and the review count, using the plugin's own star renderer so the
     * glyphs match every other WooLentor widget.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_rating( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_rating'] ?? 'yes' ) || ! function_exists( 'woolentor_wc_product_rating_generate' ) ) {
            return '';
        }

        $count = (int) $product->get_rating_count();
        if ( $count < 1 ) {
            return '';
        }

        $stars = woolentor_wc_product_rating_generate( $product );
        if ( empty( $stars ) ) {
            return '';
        }

        return '<span class="wl-ps-rating">' . $stars
            . '<span class="wl-ps-rating-avg">' . esc_html( number_format_i18n( (float) $product->get_average_rating(), 1 ) ) . '</span>'
            . '<span class="wl-ps-rating-count">(' . esc_html( number_format_i18n( $count ) ) . ')</span>'
            . '</span>';
    }

    /**
     * WooCommerce's own price HTML — it already carries <del>/<ins> for a sale, the right
     * currency position and the right decimal separator, and it is what theme CSS expects.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_price( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_price'] ?? 'yes' ) ) {
            return '';
        }

        $price = $product->get_price_html();

        return $price ? '<span class="wl-ps-price">' . $price . '</span>' : '';
    }

    /**
     * The stock note magazine v2 prints beside the price. WooCommerce's own status is the source,
     * so a store using backorders or "only 2 left" plugins keeps whatever those set.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_stock( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_stock'] ?? 'yes' ) ) {
            return '';
        }

        $in    = $product->is_in_stock();
        $class = $in ? 'wl-ps-stock wl-ps-stock--in' : 'wl-ps-stock wl-ps-stock--out';
        $text  = $in ? __( 'In stock', 'woolentor' ) : __( 'Out of stock', 'woolentor' );

        return '<span class="' . esc_attr( $class ) . '">'
            . '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . ( $in ? '<polyline points="20 6 9 17 4 12"/>' : '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' )
            . '</svg>' . esc_html( $text ) . '</span>';
    }

    /**
     * Colour dots read from the product's own attribute. The variation-swatch module renders on
     * the single product page and has no loop renderer, so this is the widget's own small reader
     * rather than a second swatch system — if that module ever grows one, this is the single
     * place to swap.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_swatches( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_swatches'] ?? 'yes' ) ) {
            return '';
        }

        $slug      = sanitize_title( $settings['swatch_attribute'] ?? 'color' );
        $taxonomy  = 0 === strpos( $slug, 'pa_' ) ? $slug : 'pa_' . $slug;
        $limit     = max( 1, absint( $settings['swatch_limit'] ?? 4 ) );

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return '';
        }

        // get_the_terms(), not wc_get_product_terms(): the WooCommerce wrapper goes through
        // wp_get_post_terms(), which queries per product and cost one query per card. This one
        // reads the object term cache the section primes for the whole result set at once.
        $terms = get_the_terms( $product->get_id(), $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        $terms = array_values( $terms );

        $dots  = '';
        $total = count( $terms );

        foreach ( array_slice( $terms, 0, $limit ) as $term ) {
            $colour = get_term_meta( $term->term_id, 'product_attribute_color', true );
            if ( '' === $colour ) {
                $colour = get_term_meta( $term->term_id, 'wl_swatch_color', true );
            }
            if ( '' === $colour ) {
                $colour = $this->named_colour( $term->slug );
            }

            $dots .= '<span class="wl-ps-swatch" title="' . esc_attr( $term->name ) . '"'
                . ( $colour ? ' style="background:' . esc_attr( $colour ) . ';"' : '' ) . '></span>';
        }

        if ( $total > $limit ) {
            $dots .= '<span class="wl-ps-swatch-more">+' . esc_html( number_format_i18n( $total - $limit ) ) . '</span>';
        }

        return '<span class="wl-ps-swatches">' . $dots . '</span>';
    }

    /**
     * A CSS colour for an attribute term that carries no swatch meta. Only the plain names a
     * browser already knows are trusted, so a term called "Ocean Mist" draws an empty ring rather
     * than a wrong colour.
     *
     * @param  string $slug
     * @return string
     */
    protected function named_colour( $slug ) {
        $known = [
            'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange', 'purple', 'pink',
            'brown', 'grey', 'gray', 'beige', 'navy', 'teal', 'olive', 'maroon', 'silver',
            'gold', 'ivory', 'cream', 'tan', 'charcoal',
        ];

        $slug = strtolower( $slug );

        if ( ! in_array( $slug, $known, true ) ) {
            return '';
        }

        $aliases = [ 'charcoal' => '#36454F', 'cream' => '#FFFDD0' ];

        return $aliases[ $slug ] ?? $slug;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Render the real template with the store's own products so the user can see a Pro variant in
     * the editor before upgrading. Frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Product Showcase' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Product Showcase' );
            return;
        }

        $tabs     = $this->build_tabs( $settings, $style, $variant );
        $settings = \WooLentor\Product_Kit\Tabs::apply_active( $settings, $tabs );
        $products = $this->get_showcase_products( $settings );

        if ( ! $products || ! $products->have_posts() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Product Showcase' );
            return;
        }

        $header = $this->build_header( $settings, $style, $variant, $products->post_count, (int) $products->found_posts );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-ps wl-ps-' . esc_attr( $style ) . ' wl-ps-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '"'
            . $this->endpoint_attrs( $tabs, $settings ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside endpoint_attrs()
            . '>';
        include $template;
        echo '</div>';
        echo '<div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.78);color:#fff;'
            . 'padding:4px 10px;border-radius:3px;font-size:11px;font-weight:700;pointer-events:none;z-index:99;">'
            . esc_html__( 'Pro — Preview Only', 'woolentor' )
            . '</div>';
        echo '</div>';

        wp_reset_postdata();
    }

    protected function render() {
        if ( ! class_exists( '\WooLentor\Style_Pack_Manager' ) ) {
            echo '<p>' . esc_html__( 'Style Pack Manager not found.', 'woolentor' ) . '</p>';
            return;
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'Product Showcase needs WooCommerce to be active.', 'woolentor' ) . '</p>';
            }
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

        // The tab row is built before the query, because the open tab decides what the query is.
        // Which tab is open normally means the first one; a numbers link carries its own.
        $pager    = $this->has_pager( $settings );
        $tabs     = $this->build_tabs( $settings, $style, $variant, \WooLentor\Product_Kit\Pagination::current_tab( $settings, $this->get_id(), $pager ) );
        $settings = \WooLentor\Product_Kit\Tabs::apply_active( $settings, $tabs );
        $paged    = \WooLentor\Product_Kit\Pagination::settings( $settings, $pager )['enabled']
            ? \WooLentor\Product_Kit\Pagination::current_page( $settings, $pager )
            : 0;
        $products = $this->get_showcase_products( $settings, $paged );

        // A query that matches nothing renders nothing rather than an empty grid shell. The
        // editor says why, so a page builder is not left staring at a blank space.
        //
        // A section with tabs is the exception: dropping it would take the tab row with it, and a
        // visitor whose first tab happens to be empty could never reach the others.
        if ( ( ! $products || ! $products->have_posts() ) && empty( $tabs ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'No products match this section\'s settings yet.', 'woolentor' ) . '</p>';
            }
            wp_reset_postdata();
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Product Showcase template not found.', 'woolentor' ) . '</p>';
            wp_reset_postdata();
            return;
        }

        // Reaching here with nothing means the section has tabs and this one is empty. The
        // templates loop over $products, so they need a real object to loop over zero of. An
        // argument-less WP_Query runs no query at all.
        if ( ! $products ) {
            $products = new \WP_Query();
        }

        $header = $this->build_header( $settings, $style, $variant, $products->post_count, (int) $products->found_posts );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-ps wl-ps-' . esc_attr( $style ) . ' wl-ps-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '"'
            . $this->endpoint_attrs( $tabs, $settings ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside endpoint_attrs()
            . '>';
        include $template;
        echo $this->pagination_html( $products, $settings, \WooLentor\Product_Kit\Tabs::active_index( $tabs ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pagination_html()
        echo '</div>';
        echo '</div>';

        wp_reset_postdata();
    }
}
