<?php
/**
 * Shop the Look Widget — Pattern B (Style + Variant dropdowns).
 *
 * A shoppable image: real WooCommerce products pinned to a lifestyle photograph by coordinate.
 * Every one of the 12 style-variant layouts renders the same content model — two flat repeaters,
 * `looks` and `hotspots`, joined by an index — so switching Style or Variant never loses content.
 *
 * The positioning technique is deliberately the one `wb_image_marker.php` uses: an X and a Y slider
 * per repeater row written straight into CSS through {{CURRENT_ITEM}}. No JavaScript positions
 * anything, the editor shows a pin move as the slider moves, and there is no layout thrash on load.
 *
 * Nothing about a product is stored in the repeater — image, title, price, rating and stock are read
 * from the catalogue at render, so a price change or a sold-out product is reflected without anyone
 * editing the page. That is the whole reason this is a product widget and not Image Marker.
 *
 * Spec: blueprint/shop-the-look-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Shop_The_Look_Widget extends Widget_Base {

    /**
     * Style + variant combinations whose template prints a section header — an eyebrow, a heading
     * and a line of description above the image.
     *
     * Modern V1 and Editorial V1 are deliberately absent: neither reference has a header at all —
     * Editorial V1's section opens straight onto its scene and closes on the tab strip — and
     * showing the controls there would offer a heading that never renders.
     */
    const HEADER_VARIANTS = [
        'modern'    => [ 'v2', 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Style + variant combinations that close with one outbound link — the V3 role's
     * *Shop the Full Room* / *Shop the Room*, and the two designed V1s.
     */
    const CTA_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v3' ],
        'luxury'    => [ 'v1', 'v3' ],
        'magazine'  => [ 'v1', 'v3' ],
    ];

    /**
     * Style + variant combinations that draw a bottom bar — the `.wl-stl-foot` strip carrying the
     * item count and, where there is one, the total.
     *
     * Kept separate from CTA_VARIANTS because the two barely overlap: a V3 has a link and no bar,
     * a V2 has a bar and no link, and only the two designed V1s have both. Gating the bar's style
     * controls on the link's map left every V2's total unstylable and every V3 offered a background
     * for a strip it does not draw.
     */
    const FOOT_VARIANTS = [
        'modern'    => [ 'v2' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v1' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /**
     * Style + variant combinations whose card carries a star rating.
     *
     * Modern V1 alone. Its reference is the only one with a star row — Editorial V1 shows a name,
     * a price and a button; Magazine V1 a spec line. Offering the rating controls anywhere else
     * would be offering controls that do nothing.
     */
    const RATING_VARIANTS = [
        'modern' => [ 'v1' ],
    ];

    /**
     * Style + variant combinations that sum the look and offer a single bulk add.
     *
     * All four V2s, and every V2 is Pro — which is why no free variant ever needs the bulk
     * endpoint. Magazine V2 was missing from the plan's first reading of the references: its
     * section is built in JavaScript, and its `Add All` button and `reduce`d total only show up in
     * the script, not in the markup.
     */
    const TOTAL_VARIANTS = [
        'modern'    => [ 'v2' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v2' ],
        'magazine'  => [ 'v2' ],
    ];

    /**
     * Style + variant combinations that list every pinned product beside the image.
     *
     * Two row shapes serve them: the V2s draw the rich panel row (`.wl-stl-item`), the V3s the
     * quiet list row (`.wl-stl-row`). One style section covers both, so its selectors name both.
     */
    /**
     * Style + variant combinations whose card closes with something clickable — a cart button or a
     * link. Every layout but Magazine V3, whose card is a tooltip carrying neither.
     */
    const CARD_BUTTON_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /**
     * Style + variant combinations that print a category line — Modern V1's uppercase brand row,
     * and the panel rows of every V2. The quiet V3 rows and the compact cards do not.
     */
    const BRAND_VARIANTS = [
        'modern'    => [ 'v1', 'v2' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /**
     * Style + variant combinations that print the attribute line under a name.
     *
     * Every V3's list row, plus the two V2 panel rows that ask for it. Modern V2 and Magazine V2
     * put swatches there instead, so the line never renders for them.
     */
    const META_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v3' ],
    ];

    /**
     * Style + variant combinations whose bottom bar prints a summed total.
     *
     * Not the same as TOTAL_VARIANTS: Luxury V2 sums its look into the bulk button's own label and
     * draws no total line, while Magazine V1 draws the line and has no bulk button at all.
     */
    const TOTAL_LINE_VARIANTS = [
        'modern'    => [ 'v2' ],
        'editorial' => [ 'v2' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /**
     * Style + variant combinations whose look switcher shows labelled thumbnails rather than plain
     * tabs or a counter. Modern alone — the other packs list their looks by name.
     */
    const THUMB_SWITCHER_VARIANTS = [
        'modern' => [ 'v1', 'v2' ],
    ];

    const LIST_VARIANTS = [
        'modern'    => [ 'v2', 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v2', 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
        $this->register_pack_script();
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-shop-the-look-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/shop-the-look/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    private function register_pack_script() {
        if ( ! wp_script_is( 'wl-pack-widgets', 'registered' ) ) {
            wp_register_script(
                'wl-pack-widgets',
                WOOLENTOR_ADDONS_PL_URL . 'assets/pack-widgets/js/pack-widgets.js',
                [ 'jquery' ],
                WOOLENTOR_VERSION,
                true
            );
        }

        if ( ! wp_script_is( 'wl-pack-widgets', 'localized' ) ) {
            wp_localize_script( 'wl-pack-widgets', 'wlPackWidgets', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            ] );
        }
    }

    public function get_name() {
        return 'woolentor-shop-the-look';
    }

    public function get_title() {
        return esc_html__( 'Shop the Look - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-image-hotspot';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'shop the look', 'lookbook', 'hotspot', 'shoppable image', 'pin', 'room', 'outfit', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-shop-the-look-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_script_depends() {
        // wc-add-to-cart is WooCommerce's own handler for the loop button. WooCommerce only enqueues
        // it on its own pages, so on an ordinary page the button this widget prints would carry the
        // `ajax_add_to_cart` class with nothing listening for it — and every Add to Cart would
        // navigate away instead of adding in place. Declaring it here is what makes the button
        // behave the way the reference shows it.
        return [ 'wl-pack-widgets', 'wc-add-to-cart' ];
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
     * True when the current selection is NOT any of the
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
     * AND several condition groups together. Elementor's Conditions::check() recurses into any term
     * that carries its own 'terms', so a group built by build_conditions() can be nested inside
     * another — which is how a control gets both "only on these variants" and "not on a Pro-locked
     * one" at the same time.
     *
     * @param  array ...$groups
     * @return array
     */
    private function all_of( ...$groups ) {
        return [ 'relation' => 'and', 'terms' => $groups ];
    }

    /**
     * Union of several style => variants maps.
     *
     * The Bottom Bar's content section holds three different things — a link, a bar, a bulk button
     * — and no two of them appear on the same set of variants. Its gate is the union, or a merchant
     * on a V2 cannot reach the bulk button's own label.
     *
     * @param  array ...$maps
     * @return array
     */
    private function merge_maps( array ...$maps ) {
        $merged = [];

        foreach ( $maps as $map ) {
            foreach ( $map as $style => $variants ) {
                $merged[ $style ] = array_values( array_unique( array_merge( $merged[ $style ] ?? [], $variants ) ) );
            }
        }

        return $merged;
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
                'shop_the_look_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_header_controls();
        $this->register_looks_controls();
        $this->register_hotspot_controls();
        $this->register_card_controls();
        $this->register_cta_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    /**
     * The section header — an eyebrow, a heading and a line of description.
     *
     * Hidden on Modern V1, whose reference has no header at all: the image is the whole widget, and
     * offering a heading that never renders is worse than offering none.
     */
    private function register_header_controls() {
        $this->start_controls_section( 'section_header', [
            'label'      => esc_html__( 'Header', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::HEADER_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'eyebrow', [
                'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Inspiration', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'heading', [
                'label'       => esc_html__( 'Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Shop This Room', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'description', [
                'label'       => esc_html__( 'Description', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'default'     => esc_html__( 'A composed living room built around the pieces below.', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'heading_tag', [
                'label'   => esc_html__( 'Heading Tag', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'h2',
                'options' => [
                    'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3',
                    'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'div' => 'div',
                ],
            ] );

        $this->end_controls_section();
    }

    /**
     * The `looks` repeater — one row per lifestyle photograph.
     *
     * Elementor has no nested repeater, so a look cannot own its pins directly. The pins live in
     * their own repeater and name their look by index. One model serves all twelve layouts; the
     * alternative — a single image control for the single-look designs — would mean two models and
     * a migration the day a variant changes its mind.
     */
    private function register_looks_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'look_label', [
            'label'       => esc_html__( 'Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Look 01', 'woolentor' ),
            'description' => esc_html__( 'Shown on the switcher and, in some layouts, as a badge on the image.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'look_image', [
            'label'   => esc_html__( 'Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => Utils::get_placeholder_image_src() ],
        ] );

        // Editorial V2 changes its whole panel story per look — a different title and a different
        // line of copy for each. Both fall back to the section's own Header when left empty, so a
        // merchant who does not want three stories sets one and stops.
        $repeater->add_control( 'look_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => esc_html__( 'Falls back to the section heading', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'look_desc', [
            'label'       => esc_html__( 'Description', 'woolentor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'rows'        => 3,
            'default'     => '',
            'placeholder' => esc_html__( 'Falls back to the section description', 'woolentor' ),
            'label_block' => true,
        ] );

        $this->start_controls_section( 'section_looks', [
            'label'      => esc_html__( 'Looks', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'looks', [
                'label'       => esc_html__( 'Looks', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ look_label }}}',
                'description' => esc_html__( 'Layouts without a switcher show the first look only.', 'woolentor' ),
                'default'     => [
                    [
                        'look_label' => esc_html__( 'Look 01', 'woolentor' ),
                        'look_image' => [ 'url' => Utils::get_placeholder_image_src() ],
                    ],
                ],
            ] );

            // No default on purpose. A default here would be written into the page CSS at a
            // specificity no pack stylesheet can beat, so every variant would be forced to one
            // shape — and each reference has its own: Editorial V1 is a wide scene, Editorial V2
            // is nearly square. Left empty, the pack's own ratio stands and this control overrides
            // it only when a merchant actually reaches for it.
            $this->add_responsive_control( 'image_ratio', [
                'label'      => esc_html__( 'Image Ratio', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [ 'px' => [ 'min' => 0.4, 'max' => 1.6, 'step' => 0.01 ] ],
                'size_units' => [ 'px' ],
                'description' => esc_html__( 'Height as a fraction of width. Left empty each layout keeps its own shape. Pin coordinates are percentages, so they hold as this changes.', 'woolentor' ),
                // Written on the root, not on the image. A pack that cross-fades its looks has to
                // put the ratio on the stage instead — Editorial V1 does — and a custom property
                // set on the image would never reach it, since properties inherit downward only.
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl' => '--wl-stl-ratio: {{SIZE}};',
                ],
            ] );

        $this->end_controls_section();
    }

    /**
     * The `hotspots` repeater — one row per pinned product.
     *
     * `x` and `y` are written straight to CSS through {{CURRENT_ITEM}}, which resolves to the row's
     * own `.elementor-repeater-item-{id}` class. That is the whole positioning mechanism.
     */
    private function register_hotspot_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'product', [
            'label'       => esc_html__( 'Product', 'woolentor' ),
            'type'        => 'woolentor-select',
            'ajax_search' => true,
            'post_type'   => 'product',
            'label_block' => true,
            'placeholder' => esc_html__( 'Search products…', 'woolentor' ),
        ] );

        // Percentages, not pixels: a pin has to hold its place on the photograph at every width.
        $repeater->add_responsive_control( 'x', [
            'label'      => esc_html__( 'Horizontal Position', 'woolentor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'default'    => [ 'unit' => '%', 'size' => 50 ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 0.5 ] ],
            'selectors'  => [
                '{{WRAPPER}} .wl-stl-pin{{CURRENT_ITEM}}' => 'left: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $repeater->add_responsive_control( 'y', [
            'label'      => esc_html__( 'Vertical Position', 'woolentor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'default'    => [ 'unit' => '%', 'size' => 50 ],
            'range'      => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 0.5 ] ],
            'selectors'  => [
                '{{WRAPPER}} .wl-stl-pin{{CURRENT_ITEM}}' => 'top: {{SIZE}}{{UNIT}};',
            ],
        ] );

        // Elementor evaluates a repeater field's condition against its own row, never against a
        // widget-level control, so this cannot be hidden on the single-look layouts. It is
        // described instead — and a layout that shows one look simply ignores it.
        $repeater->add_control( 'look', [
            'label'       => esc_html__( 'Belongs to Look', 'woolentor' ),
            'type'        => Controls_Manager::NUMBER,
            'default'     => 1,
            'min'         => 1,
            'step'        => 1,
            'description' => esc_html__( 'Which look this pin sits on. Layouts that show one look use Look 1.', 'woolentor' ),
        ] );

        $repeater->add_control( 'label', [
            'label'       => esc_html__( 'Label Override', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'description' => esc_html__( 'Optional. Replaces the product title in the panel list only, never on the card.', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'side', [
            'label'       => esc_html__( 'Card Opens', 'woolentor' ),
            'type'        => Controls_Manager::SELECT,
            'default'     => 'auto',
            'options'     => [
                'auto'  => esc_html__( 'Auto', 'woolentor' ),
                'right' => esc_html__( 'Right', 'woolentor' ),
                'left'  => esc_html__( 'Left', 'woolentor' ),
            ],
            'description' => esc_html__( 'Auto flips the card away from the nearest edge.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_hotspots', [
            'label'      => esc_html__( 'Pins', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'hotspots', [
                'label'       => esc_html__( 'Pins', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ label || product }}}',
                'default'     => [],
            ] );

            $this->add_control( 'hotspots_hint', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'A look reads best with three to ten pins. Past that the image gets harder to use than the product list beside it.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

        $this->end_controls_section();
    }

    /** What a pin's card shows. Names match Product Showcase so the card helpers read the same keys. */
    private function register_card_controls() {
        $this->start_controls_section( 'section_card', [
            'label'      => esc_html__( 'Product Card', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'show_brand', [
                'label'        => esc_html__( 'Show Category Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'conditions' => $this->all_of(
                    $this->build_conditions( self::BRAND_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'brand_source', [
                'label'     => esc_html__( 'Line Source', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'category',
                'options'   => [
                    'category' => esc_html__( 'Category', 'woolentor' ),
                    'brand'    => esc_html__( 'Brand', 'woolentor' ),
                ],
                'condition' => [ 'show_brand' => 'yes' ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::BRAND_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'show_rating', [
                'label'        => esc_html__( 'Show Rating', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'conditions'   => $this->all_of(
                    $this->build_conditions( self::RATING_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'show_price', [
                'label'        => esc_html__( 'Show Price', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'show_meta', [
                'label'        => esc_html__( 'Show Attribute Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'The product’s own visible attributes, e.g. “Performance Fabric · Sand”. Shown in the product list beside the image.', 'woolentor' ),
                'conditions' => $this->all_of(
                    $this->build_conditions( self::META_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'card_link_text', [
                'label'       => esc_html__( 'Card Link Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'View Product', 'woolentor' ),
                'description' => esc_html__( 'The link inside a pin card, on the layouts that show one instead of a cart button. Left empty, each layout uses its own wording.', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'show_swatches', [
                'label'        => esc_html__( 'Show Colour Swatches', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'conditions'   => $this->all_of(
                    $this->build_conditions( self::LIST_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'swatch_attribute', [
                'label'       => esc_html__( 'Swatch Attribute', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'color',
                'description' => esc_html__( 'Attribute slug to read colours from, with or without the pa_ prefix.', 'woolentor' ),
                'condition'   => [ 'show_swatches' => 'yes' ],
                'conditions'  => $this->all_of(
                    $this->build_conditions( self::LIST_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'show_add_to_cart', [
                'label'        => esc_html__( 'Show Add to Cart', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'add_to_cart_text', [
                'label'       => esc_html__( 'Add to Cart Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Leave empty for WooCommerce’s own label', 'woolentor' ),
                'condition'   => [ 'show_add_to_cart' => 'yes' ],
                'label_block' => true,
            ] );

        $this->end_controls_section();
    }

    /**
     * The footer under the image: one outbound link, and on the two designed V1s a line counting
     * the look's items. Not a cart action — no V1 or V3 carries a bulk add, which is what keeps the
     * bulk endpoint out of the free path entirely.
     */
    private function register_cta_controls() {
        $this->start_controls_section( 'section_cta', [
            'label'      => esc_html__( 'Bottom Bar', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( $this->merge_maps( self::CTA_VARIANTS, self::FOOT_VARIANTS, self::TOTAL_VARIANTS ) ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'cta_text', [
                'label'       => esc_html__( 'Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Shop the Full Room', 'woolentor' ),
                'description' => esc_html__( 'Left empty, each layout uses its own wording — Shop the Room, Shop the Composition, and so on.', 'woolentor' ),
                'label_block' => true,
                'conditions'    => $this->build_conditions( self::CTA_VARIANTS ),
            ] );

            $this->add_control( 'cta_link', [
                'label'         => esc_html__( 'Link', 'woolentor' ),
                'type'          => Controls_Manager::URL,
                'placeholder'   => 'https://example.com/shop',
                'description'   => esc_html__( 'Leave empty to link to the shop page.', 'woolentor' ),
                'show_external' => true,
                'conditions'    => $this->build_conditions( self::CTA_VARIANTS ),
            ] );

            $this->add_control( 'bulk_text', [
                'label'       => esc_html__( 'Add Whole Look Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Add Complete Look to Cart', 'woolentor' ),
                'description' => esc_html__( 'Use {total} to print the look’s price inside the label. Left empty, each layout uses its own wording.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->all_of(
                    $this->build_conditions( self::TOTAL_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'ship_note', [
                'label'       => esc_html__( 'Note Under the Total', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( 'Free shipping included', 'woolentor' ),
                'description' => esc_html__( 'Static text. The section cannot know a shipping threshold, so this is a line you write, not one it calculates.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->all_of(
                    $this->build_conditions( self::TOTAL_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'count_text', [
                'label'       => esc_html__( 'Item Count Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => esc_html__( '{count} items in this look', 'woolentor' ),
                'description' => esc_html__( 'Use {count} where the number should go. Left empty, each layout uses its own wording.', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->all_of(
                    $this->build_conditions( self::FOOT_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

        $this->end_controls_section();
    }

    /** Content-tab notice shown in place of the editable sections when a Pro variant is selected. */
    private function register_pro_content_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Looks', 'woolentor' ),
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

    private function register_style_controls() {
        $unlocked = $this->build_negated_conditions( $this->get_pro_map() );

        // — Section —
        // Three variants paint their own ground — Editorial V2 is dark, Luxury V1 is obsidian,
        // Magazine V3 is warm paper — and a pack stylesheet cannot be reached from Elementor's
        // Container background, which sits behind the widget rather than on it. These write onto
        // the widget's own root, at a specificity the pack CSS cannot win, so a merchant can
        // repaint the section the design ships with.
        $this->start_controls_section( 'style_section', [
            'label'      => esc_html__( 'Section', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'section_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'section_color', [
                'label'       => esc_html__( 'Text Color', 'woolentor' ),
                'type'        => Controls_Manager::COLOR,
                'description' => esc_html__( 'The base colour everything in the section inherits. The controls below still override it piece by piece.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-stl' => 'color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'section_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'section_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                // No `overflow: hidden` alongside it. A background honours a radius without
                // clipping, and clipping the root is how an open card near an edge gets cut off —
                // the same mistake the image box had to be cured of.
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_control( 'panel_heading', [
                'label'      => esc_html__( 'Panel', 'woolentor' ),
                'type'       => Controls_Manager::HEADING,
                'separator'  => 'before',
                'conditions' => $this->all_of(
                    $this->build_conditions( self::LIST_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'panel_bg', [
                'label'      => esc_html__( 'Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-stl-panel' => 'background-color: {{VALUE}};' ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::LIST_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_responsive_control( 'panel_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::LIST_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

        $this->end_controls_section();

        // — Header —
        $this->start_controls_section( 'style_header', [
            'label'      => esc_html__( 'Header', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                $this->build_conditions( self::HEADER_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_responsive_control( 'header_align', [
                'label'     => esc_html__( 'Alignment', 'woolentor' ),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [ 'title' => esc_html__( 'Left', 'woolentor' ),   'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => esc_html__( 'Center', 'woolentor' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => esc_html__( 'Right', 'woolentor' ),  'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [ '{{WRAPPER}} .wl-stl-head' => 'text-align: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'header_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 140 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-stl-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-stl-eyebrow',
            ] );

            $this->add_control( 'heading_color', [
                'label'     => esc_html__( 'Heading Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-heading' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'heading_typography',
                'selector' => '{{WRAPPER}} .wl-stl-heading',
            ] );

            $this->add_control( 'desc_color', [
                'label'     => esc_html__( 'Description Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .wl-stl-desc',
            ] );

        $this->end_controls_section();

        // — Look Switcher —
        // Not gated on a variant map: any layout draws one once a section has more than one look,
        // and with a single look there is nothing on the page for these to reach.
        $this->start_controls_section( 'style_switcher', [
            'label'      => esc_html__( 'Look Switcher', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'switcher_gap', [
                'label'      => esc_html__( 'Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 90 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-stl-switch'  => '--wl-stl-switch-gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-stl-counter' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'switcher_thumb_size', [
                'label'      => esc_html__( 'Thumbnail Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 24, 'max' => 120 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-switch'       => '--wl-stl-switch-thumb: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-stl-switch-thumb' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::THUMB_SWITCHER_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'switcher_typography',
                'selector' => '{{WRAPPER}} .wl-stl-switch-btn, {{WRAPPER}} .wl-stl-counter-text',
            ] );

            $this->start_controls_tabs( 'switcher_tabs' );

                $this->start_controls_tab( 'switcher_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'switcher_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-switch-btn, {{WRAPPER}} .wl-stl-counter-text, {{WRAPPER}} .wl-stl-counter-btn' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'switcher_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-switch-btn, {{WRAPPER}} .wl-stl-counter-btn' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'switcher_border_color', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-switch-btn, {{WRAPPER}} .wl-stl-counter-btn' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'switcher_active', [ 'label' => esc_html__( 'Active', 'woolentor' ) ] );

                    $this->add_control( 'switcher_color_active', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-switch-btn.is-active, {{WRAPPER}} .wl-stl-counter-now' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'switcher_bg_active', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-switch-btn.is-active' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'switcher_border_color_active', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                            '{{WRAPPER}} .wl-stl-switch-btn.is-active' => 'border-color: {{VALUE}}; border-bottom-color: {{VALUE}};',
                        ],
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

            $this->add_responsive_control( 'image_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'image_border',
                'selector' => '{{WRAPPER}} .wl-stl-image',
            ] );

        $this->end_controls_section();

        // — Pin —
        $this->start_controls_section( 'style_pin', [
            'label'      => esc_html__( 'Pin', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'pin_size', [
                'label'      => esc_html__( 'Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 12, 'max' => 64 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-stl-pin-btn' => '--wl-stl-pin-size: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->start_controls_tabs( 'pin_tabs' );

                $this->start_controls_tab( 'pin_closed', [ 'label' => esc_html__( 'Closed', 'woolentor' ) ] );

                    $this->add_control( 'pin_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-pin-btn' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'pin_color', [
                        'label'     => esc_html__( 'Glyph Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-pin-btn' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'pin_border_color', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-pin-btn' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'pin_open', [ 'label' => esc_html__( 'Open', 'woolentor' ) ] );

                    $this->add_control( 'pin_bg_open', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-pin.is-open .wl-stl-pin-btn' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'pin_color_open', [
                        'label'     => esc_html__( 'Glyph Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-pin.is-open .wl-stl-pin-btn' => 'color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Card —
        $this->start_controls_section( 'style_card', [
            'label'      => esc_html__( 'Card', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_responsive_control( 'card_width', [
                'label'      => esc_html__( 'Width', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 180, 'max' => 480 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-stl-card' => 'width: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'card_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-card' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .wl-stl-card',
            ] );

            $this->add_responsive_control( 'card_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'card_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .wl-stl-card',
            ] );

        $this->end_controls_section();

        // — Card Text —
        $this->start_controls_section( 'style_card_text', [
            'label'      => esc_html__( 'Card Text', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'card_brand_color', [
                'label'     => esc_html__( 'Category Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-brand' => 'color: {{VALUE}};' ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::BRAND_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'card_brand_typography',
                'selector' => '{{WRAPPER}} .wl-stl-brand',
                'conditions' => $this->all_of(
                    $this->build_conditions( self::BRAND_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'card_title_color', [
                'label'     => esc_html__( 'Title Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-title, {{WRAPPER}} .wl-stl-title a' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'card_title_typography',
                'selector' => '{{WRAPPER}} .wl-stl-title, {{WRAPPER}} .wl-stl-title a',
            ] );

            $this->add_control( 'card_price_color', [
                'label'     => esc_html__( 'Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-price' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'card_price_typography',
                'selector' => '{{WRAPPER}} .wl-stl-price',
            ] );

        $this->end_controls_section();

        // — Rating —
        $this->start_controls_section( 'style_rating', [
            'label'      => esc_html__( 'Rating', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                $this->build_conditions( self::RATING_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'rating_color', [
                'label'     => esc_html__( 'Star Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-rating svg' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'rating_empty_color', [
                'label'     => esc_html__( 'Empty Star Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-rating svg.star.empty' => 'color: {{VALUE}}; opacity: 1;' ],
            ] );

            $this->add_responsive_control( 'rating_size', [
                'label'      => esc_html__( 'Star Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 32 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-rating svg.star' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_control( 'rating_text_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-stl-rating-avg, {{WRAPPER}} .wl-stl-rating-count' => 'color: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

        // — Product List —
        $this->start_controls_section( 'style_list', [
            'label'      => esc_html__( 'Product List', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                $this->build_conditions( self::LIST_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_responsive_control( 'list_gap', [
                'label'      => esc_html__( 'Gap Between Rows', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-stl-list'  => '--wl-stl-row-gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-stl-items' => '--wl-stl-item-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'row_thumb_size', [
                'label'      => esc_html__( 'Thumbnail Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 32, 'max' => 140 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-list'                  => '--wl-stl-row-thumb: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-stl-items'                 => '--wl-stl-item-thumb: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .wl-stl-row-media .wl-stl-img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'row_padding', [
                'label'      => esc_html__( 'Row Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-row, {{WRAPPER}} .wl-stl-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'row_radius', [
                'label'      => esc_html__( 'Row Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-row, {{WRAPPER}} .wl-stl-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->start_controls_tabs( 'row_tabs' );

                $this->start_controls_tab( 'row_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'row_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-row, {{WRAPPER}} .wl-stl-item' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'row_border',
                        'selector' => '{{WRAPPER}} .wl-stl-row, {{WRAPPER}} .wl-stl-item',
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'row_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'row_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-row:hover, {{WRAPPER}} .wl-stl-item:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'row_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-row:hover, {{WRAPPER}} .wl-stl-item:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_control( 'row_text_heading', [
                'label'     => esc_html__( 'Text', 'woolentor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'row_name_color', [
                'label'     => esc_html__( 'Name Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-row-name, {{WRAPPER}} .wl-stl-item-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'row_name_typography',
                'selector' => '{{WRAPPER}} .wl-stl-row-name, {{WRAPPER}} .wl-stl-item-name',
            ] );

            $this->add_control( 'row_meta_color', [
                'label'     => esc_html__( 'Attribute Line Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-meta' => 'color: {{VALUE}};' ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::META_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'row_meta_typography',
                'selector' => '{{WRAPPER}} .wl-stl-meta',
                'conditions' => $this->all_of(
                    $this->build_conditions( self::META_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_control( 'row_price_color', [
                'label'     => esc_html__( 'Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-row .wl-stl-price, {{WRAPPER}} .wl-stl-item .wl-stl-price' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // — Footer —
        $this->start_controls_section( 'style_footer', [
            'label'      => esc_html__( 'Bottom Bar', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                $this->build_conditions( self::FOOT_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_responsive_control( 'footer_spacing', [
                'label'      => esc_html__( 'Spacing Above', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 90 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-stl-foot' => 'margin-top: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'footer_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-foot' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'footer_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-foot' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_control( 'count_color', [
                'label'     => esc_html__( 'Item Count Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-count' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'count_typography',
                'selector' => '{{WRAPPER}} .wl-stl-count',
            ] );

            $this->add_control( 'total_color', [
                'label'     => esc_html__( 'Total Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-stl-total ins' => 'color: {{VALUE}};' ],
                'conditions' => $this->all_of(
                    $this->build_conditions( self::TOTAL_LINE_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'total_typography',
                'selector' => '{{WRAPPER}} .wl-stl-total ins',
                'conditions' => $this->all_of(
                    $this->build_conditions( self::TOTAL_LINE_VARIANTS ),
                    $this->build_negated_conditions( $this->get_pro_map() )
                ),
            ] );

        $this->end_controls_section();

        // — Bottom Link —
        $this->start_controls_section( 'style_cta', [
            'label'      => esc_html__( 'Bottom Link', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->all_of(
                $this->build_conditions( self::CTA_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'cta_typography',
                'selector' => '{{WRAPPER}} .wl-stl-cta a',
            ] );

            $this->add_responsive_control( 'cta_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-cta a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'cta_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-cta a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->start_controls_tabs( 'cta_tabs' );

                $this->start_controls_tab( 'cta_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'cta_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cta a' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cta a' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_border_color', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cta a' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'cta_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'cta_color_hover', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cta a:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cta a:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cta a:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();

        // — Card Button —
        // One section for both closing actions: a cart button on the layouts that sell from the
        // card, a text link on the ones that send the visitor to the product. They occupy the same
        // slot, so splitting them would send a merchant hunting for a second panel.
        $this->start_controls_section( 'style_button', [
            'label'      => esc_html__( 'Card Button', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            // Magazine V3 is absent from the map: its card is a tooltip with neither a cart
            // button nor a link, so there is nothing here for it to reach.
            'conditions' => $this->all_of(
                $this->build_conditions( self::CARD_BUTTON_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .wl-stl-cart a, {{WRAPPER}} .wl-stl-link',
            ] );

            $this->add_responsive_control( 'button_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-cart a, {{WRAPPER}} .wl-stl-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'button_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-stl-cart a, {{WRAPPER}} .wl-stl-link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->start_controls_tabs( 'button_tabs' );

                $this->start_controls_tab( 'button_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'button_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cart a, {{WRAPPER}} .wl-stl-link' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'button_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cart a, {{WRAPPER}} .wl-stl-link' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'button_border_color', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cart a, {{WRAPPER}} .wl-stl-link' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'button_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'button_color_hover', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cart a:hover, {{WRAPPER}} .wl-stl-link:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'button_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cart a:hover, {{WRAPPER}} .wl-stl-link:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'button_border_color_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-stl-cart a:hover, {{WRAPPER}} .wl-stl-link:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

        $this->end_controls_section();
    }

    // ── Data ──────────────────────────────────────────────────────────────────

    /**
     * Normalise the `looks` repeater. Always returns at least one entry so a template can render
     * without checking — a widget with no look row still shows its placeholder image.
     *
     * @param  array $rows
     * @return array
     */
    private function build_looks( array $rows ) {
        $looks = [];

        foreach ( $rows as $index => $row ) {
            $looks[] = [
                'index' => $index + 1,
                'label' => (string) ( $row['look_label'] ?? '' ),
                'title' => (string) ( $row['look_title'] ?? '' ),
                'desc'  => (string) ( $row['look_desc'] ?? '' ),
                'id'    => absint( $row['look_image']['id'] ?? 0 ),
                'url'   => (string) ( $row['look_image']['url'] ?? '' ),
            ];
        }

        if ( empty( $looks ) ) {
            $looks[] = [
                'index' => 1,
                'label' => '',
                'title' => '',
                'desc'  => '',
                'id'    => 0,
                'url'   => Utils::get_placeholder_image_src(),
            ];
        }

        return $looks;
    }

    /**
     * Normalise the `hotspots` repeater into pins grouped by look index.
     *
     * A row whose product no longer exists, is not published, or is not a product at all is dropped
     * here rather than in the template: a deleted product must never print an empty card on a live
     * shop. The editor is told instead, in render().
     *
     * @param  array $rows
     * @return array  [ look index => [ pin, … ] ]
     */
    private function build_pins( array $rows ) {
        $pins = [];

        foreach ( $rows as $row ) {
            $id = absint( $row['product'] ?? 0 );

            if ( ! $id ) {
                continue;
            }

            $product = wc_get_product( $id );

            if ( ! $product || 'publish' !== get_post_status( $id ) ) {
                continue;
            }

            $look = max( 1, absint( $row['look'] ?? 1 ) );

            $pins[ $look ][] = [
                'id'      => $id,
                'row_id'  => (string) ( $row['_id'] ?? '' ),
                'product' => $product,
                'label'   => (string) ( $row['label'] ?? '' ),
                'side'    => in_array( $row['side'] ?? 'auto', [ 'auto', 'left', 'right' ], true ) ? $row['side'] : 'auto',
            ];
        }

        return $pins;
    }

    /**
     * How many rows were configured but could not be drawn. Shown in the editor only, so a page
     * builder finds out about a deleted product without a shopper ever seeing a gap.
     *
     * @param  array $rows
     * @param  array $pins
     * @return int
     */
    private function count_dropped( array $rows, array $pins ) {
        $drawn = 0;
        foreach ( $pins as $group ) {
            $drawn += count( $group );
        }
        return max( 0, count( $rows ) - $drawn );
    }

    // ── Card pieces ───────────────────────────────────────────────────────────
    //
    // Local for now, deliberately. Product Showcase has the same pieces as `card_*` methods, but
    // they carry showcase-specific switches and live on that widget. A shared Product_Card belongs
    // in the kit once two widgets have proved the shape — extracting mid-flight would put a shipped
    // widget at risk to save a second consumer some duplication. See §5 of the plan.

    /**
     * A pin's product image, at thumbnail size. Falls back to WooCommerce's placeholder so a card
     * never collapses to text.
     *
     * @param  \WC_Product $product
     * @param  string      $size
     * @return string
     */
    protected function card_media( $product, $size = 'woocommerce_thumbnail' ) {
        $image = $product->get_image( $size, [ 'class' => 'wl-stl-img', 'loading' => 'lazy' ] );

        if ( '' === $image ) {
            $image = '<img class="wl-stl-img" src="' . esc_url( wc_placeholder_img_src( $size ) ) . '" alt="'
                . esc_attr( $product->get_name() ) . '" loading="lazy">';
        }

        return '<span class="wl-stl-media">' . $image . '</span>';
    }

    /**
     * The line above the title — a category name by default, a brand where the store has a brand
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

        $taxonomy = 'product_cat';

        if ( 'brand' === ( $settings['brand_source'] ?? 'category' ) ) {
            foreach ( [ 'product_brand', 'pwb-brand', 'yith_product_brand' ] as $candidate ) {
                if ( taxonomy_exists( $candidate ) ) {
                    $taxonomy = $candidate;
                    break;
                }
            }
        }

        $terms = get_the_terms( $product->get_id(), $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        return '<span class="wl-stl-brand">' . esc_html( $terms[0]->name ) . '</span>';
    }

    /**
     * The product title, linked to the product.
     *
     * The label override is deliberately not used here — it renames a row in a panel list, never
     * the card, which has to name the thing the visitor is about to buy.
     *
     * @param  \WC_Product $product
     * @return string
     */
    protected function card_title( $product ) {
        return '<span class="wl-stl-title"><a href="' . esc_url( $product->get_permalink() ) . '">'
            . esc_html( $product->get_name() ) . '</a></span>';
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

        return '<span class="wl-stl-rating">' . $stars
            . '<span class="wl-stl-rating-avg">' . esc_html( number_format_i18n( (float) $product->get_average_rating(), 1 ) ) . '</span>'
            . '<span class="wl-stl-rating-count">(' . esc_html( number_format_i18n( $count ) ) . ')</span>'
            . '</span>';
    }

    /**
     * WooCommerce's own price HTML — it already carries <del>/<ins> for a sale, the right currency
     * position and the right decimal separator, and it is what theme CSS expects.
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

        return $price ? '<span class="wl-stl-price">' . $price . '</span>' : '';
    }

    /**
     * WooCommerce's own loop add-to-cart, so a variable product still goes to the product page and
     * an ajax simple product still adds in place. Only the label is ours.
     *
     * The card's class is appended, never passed as `class` in the args: WooCommerce merges those
     * with wp_parse_args, so supplying one replaces its whole default string — and with it `button`,
     * `add_to_cart_button`, `ajax_add_to_cart` and `product_type_*`. Losing those silently turns off
     * ajax add-to-cart and every bit of theme styling on the button.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function card_cart( $product, array $settings, $icon = '', $icon_only = false ) {
        if ( 'yes' !== ( $settings['show_add_to_cart'] ?? 'yes' ) ) {
            return '';
        }

        // woocommerce_template_loop_add_to_cart() reads the global, not an argument.
        $previous = $GLOBALS['product'] ?? null;
        $GLOBALS['product'] = $product;

        // An icon-only button empties WooCommerce's label rather than hiding it in CSS, so the
        // anchor carries no stray text for a screen reader to read out beside its own aria-label.
        $label       = $icon_only ? '' : trim( (string) ( $settings['add_to_cart_text'] ?? '' ) );
        $text_filter = null;

        if ( $icon_only || '' !== $label ) {
            $text_filter = function () use ( $label ) {
                return $label;
            };
            add_filter( 'woocommerce_product_add_to_cart_text', $text_filter, 99 );
        }

        $extra       = $icon_only ? ' wl-stl-cart-btn wl-stl-cart-icon' : ' wl-stl-cart-btn';
        $args_filter = function ( $args ) use ( $extra, $icon_only ) {
            $class = trim( ( $args['class'] ?? '' ) . $extra );

            // `button` is WooCommerce's cosmetic class, and themes hang their whole button style on
            // it — padding, min-width, background. On a 36px square icon control that styling wins
            // on specificity and blows the control up into a full-size themed button. It is dropped
            // here rather than fought with selectors, because nothing functional needs it: the ajax
            // handler binds to `add_to_cart_button`, which stays.
            if ( $icon_only ) {
                $class = trim( preg_replace( '/(^|\s)button(\s|$)/', ' ', $class ) );
            }

            $args['class'] = $class;

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

        if ( null === $previous ) {
            unset( $GLOBALS['product'] );
        } else {
            $GLOBALS['product'] = $previous;
        }

        if ( '' === trim( $html ) ) {
            return '';
        }

        // The glyph belongs inside WooCommerce's own anchor, next to the label — putting it beside
        // the button would leave it floating outside. WooCommerce owns the anchor, so the only way
        // in is straight after its opening tag.
        if ( '' !== $icon ) {
            $html = preg_replace( '/(<a\b[^>]*>)/', '$1' . $icon, $html, 1 );
        }

        return '<span class="wl-stl-cart">' . $html . '</span>';
    }

    /**
     * Colour dots for a product's swatch attribute.
     *
     * get_the_terms(), not wc_get_product_terms(): the WooCommerce wrapper goes through
     * wp_get_post_terms(), which queries once per product. This one reads the object term cache.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  int         $limit
     * @return string
     */
    protected function card_swatches( $product, array $settings, $limit = 4 ) {
        if ( 'yes' !== ( $settings['show_swatches'] ?? 'yes' ) ) {
            return '';
        }

        $slug     = sanitize_title( $settings['swatch_attribute'] ?? 'color' );
        $taxonomy = 0 === strpos( $slug, 'pa_' ) ? $slug : 'pa_' . $slug;

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return '';
        }

        $terms = get_the_terms( $product->get_id(), $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        $terms = array_values( $terms );
        $dots  = '';

        foreach ( array_slice( $terms, 0, $limit ) as $term ) {
            $colour = get_term_meta( $term->term_id, 'product_attribute_color', true );

            if ( '' === $colour ) {
                $colour = get_term_meta( $term->term_id, 'wl_swatch_color', true );
            }

            if ( '' === $colour ) {
                $colour = $this->named_colour( $term->slug );
            }

            $dots .= '<span class="wl-stl-swatch" title="' . esc_attr( $term->name ) . '"'
                . ( $colour ? ' style="background:' . esc_attr( $colour ) . ';"' : '' ) . '></span>';
        }

        if ( count( $terms ) > $limit ) {
            $dots .= '<span class="wl-stl-swatch-more">+' . esc_html( number_format_i18n( count( $terms ) - $limit ) ) . '</span>';
        }

        return '<span class="wl-stl-swatches">' . $dots . '</span>';
    }

    /**
     * A CSS colour for an attribute term that carries no swatch meta. Only the plain names a browser
     * already knows are trusted, so a term called "Ocean Mist" draws an empty ring rather than a
     * wrong colour.
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

        $slug = sanitize_title( $slug );

        return in_array( $slug, $known, true ) ? $slug : '';
    }

    /**
     * The attribute line under a title — Modern V3's "Performance Fabric · Sand".
     *
     * Built from the product's own visible attributes, values rather than labels, because that is
     * what the reference prints. A product with no visible attributes collapses the slot instead of
     * drawing an empty line.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  int         $limit
     * @return string
     */
    protected function card_meta( $product, array $settings, $limit = 2 ) {
        if ( 'yes' !== ( $settings['show_meta'] ?? 'yes' ) ) {
            return '';
        }

        $parts = [];

        foreach ( $product->get_attributes() as $attribute ) {
            if ( count( $parts ) >= $limit ) {
                break;
            }

            if ( ! is_a( $attribute, 'WC_Product_Attribute' ) || ! $attribute->get_visible() ) {
                continue;
            }

            $value = $product->get_attribute( $attribute->get_name() );

            if ( '' === $value ) {
                continue;
            }

            // WooCommerce joins multiple terms with ", ". The reference prints a slash.
            $parts[] = str_replace( ', ', ' / ', $value );
        }

        return $parts ? '<span class="wl-stl-meta">' . esc_html( implode( ' · ', $parts ) ) . '</span>' : '';
    }

    /**
     * The link a card closes with where it does not close with a cart button.
     *
     * One control, and each layout passes its own reference's wording as the fallback — Modern V3
     * says *Quick Shop*, Editorial V1 says *View Product*. A per-pack default is not something a
     * single Elementor control can carry, and a second control would be one more thing to set for
     * a string most merchants never touch.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  string      $fallback  This layout's own wording.
     * @param  bool        $arrow
     * @return string
     */
    protected function card_link( $product, array $settings, $fallback, $arrow = true ) {
        $label = trim( (string) ( $settings['card_link_text'] ?? '' ) );

        if ( '' === $label ) {
            $label = $fallback;
        }

        if ( '' === $label ) {
            return '';
        }

        return '<a class="wl-stl-link" href="' . esc_url( $product->get_permalink() ) . '">'
            . esc_html( $label )
            . ( $arrow ? ' <span aria-hidden="true">&rarr;</span>' : '' )
            . '</a>';
    }

    /**
     * The compact card a quiet layout opens instead of a full one — a thumbnail, the name, the
     * price and a link onward. No cart button: on these layouts the pin is a pointer into the list
     * beside the image, and the list is where the detail lives.
     *
     * @param  array  $pin
     * @param  array  $settings
     * @param  string $link_text  This layout's own wording for the link.
     * @return string
     */
    protected function card_compact( array $pin, array $settings, $link_text = '' ) {
        $product = $pin['product'];

        return '<div class="wl-stl-card-inner">'
            . '<span class="wl-stl-card-media">' . $this->card_media( $product, 'woocommerce_gallery_thumbnail' ) . '</span>'
            . '<div class="wl-stl-card-body">'
            . $this->card_title( $product )
            . $this->card_price( $product, $settings )
            . '</div></div>'
            . $this->card_link( $product, $settings, $link_text );
    }

    /**
     * One row in the product list beside the image.
     *
     * The whole row is an anchor. Modern V3's reference gives its rows `cursor: pointer` and a hover
     * border but wires no behaviour to them, which is a dead pointer rather than a design intent —
     * linking the row to its product is the reading that makes the cursor honest.
     *
     * The row carries its pin's id so the two can address each other without a lookup, which is
     * what Luxury V2's hover dimming needs.
     *
     * @param  array $pin
     * @param  array $settings
     * @param  int   $index  1-based position, printed by the layouts that number their rows.
     * @return string
     */
    protected function row_html( array $pin, array $settings, $index = 0 ) {
        $product = $pin['product'];
        $name    = '' !== $pin['label'] ? $pin['label'] : $product->get_name();

        return '<a class="wl-stl-row" href="' . esc_url( $product->get_permalink() ) . '"'
            . ' data-wl-stl-row="' . esc_attr( $pin['row_id'] ) . '">'
            . ( $index ? '<span class="wl-stl-row-index">' . esc_html( str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) ) . '</span>' : '' )
            . '<span class="wl-stl-row-media">' . $this->card_media( $product, 'woocommerce_gallery_thumbnail' ) . '</span>'
            . '<span class="wl-stl-row-body">'
            . '<span class="wl-stl-row-name">' . esc_html( $name ) . '</span>'
            . $this->card_meta( $product, $settings )
            . '</span>'
            . $this->card_price( $product, $settings )
            . '</a>';
    }

    /**
     * One row in the panel beside a split layout's image — the richer of the two row shapes.
     *
     * Index, thumbnail, category, name, price, colour swatches and an add button. The quiet list
     * rows of a V3 use row_html() instead; these are the only two row shapes the twelve layouts
     * need. $args covers what differs **within** this shape — where the numeral sits, whether the
     * button carries a label or a glyph, and whether the price moves to a column of its own — which
     * is what the V2 references actually differ by.
     *
     * @param  array $pin
     * @param  array $settings
     * @param  int   $index  1-based position.
     * @param  array $args
     * @return string
     */
    protected function panel_row_html( array $pin, array $settings, $index = 0, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'index_in_thumb' => false,   // Editorial V2 burns the numeral into the thumbnail.
            'cart'           => 'icon',  // 'icon' for a bag glyph, 'text' for a labelled button.
            'meta'           => false,   // A second line under the name, from the product's attributes.
            'cart_in_body'   => false,   // Luxury V2 sets its Add to Bag under the name, not beside the price.
        ] );

        $product = $pin['product'];
        $name    = '' !== $pin['label'] ? $pin['label'] : $product->get_name();
        $num     = $index ? '<span class="wl-stl-item-num">' . esc_html( str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) ) . '</span>' : '';

        // Both glyphs ship, and CSS swaps them on WooCommerce's own `added` class. The panel row
        // hides the "View cart" link WooCommerce appends — a 36px control has nowhere to put it —
        // so without this the button would say nothing at all after an add, and a shopper would not
        // know whether the click had worked.
        $bag = '<span class="wl-stl-icon-bag">'
            . '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            . ' stroke-width="2" stroke-linecap="round" aria-hidden="true">'
            . '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>'
            . '<line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>'
            . '</span>'
            . '<span class="wl-stl-icon-check">'
            . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            . ' stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . '<path d="M20 6 9 17l-5-5"/></svg>'
            . '</span>';

        $icon_only = 'icon' === $args['cart'];
        $button    = $this->card_cart( $product, $settings, $icon_only ? $bag : '', $icon_only );

        return '<div class="wl-stl-item" data-wl-stl-row="' . esc_attr( $pin['row_id'] ) . '">'
            . ( $args['index_in_thumb'] ? '' : $num )
            . '<a class="wl-stl-item-media" href="' . esc_url( $product->get_permalink() ) . '" tabindex="-1" aria-hidden="true">'
            . $this->card_media( $product, 'woocommerce_gallery_thumbnail' )
            . ( $args['index_in_thumb'] ? $num : '' )
            . '</a>'
            . '<div class="wl-stl-item-info">'
            . $this->card_brand( $product, $settings )
            . '<a class="wl-stl-item-name" href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $name ) . '</a>'
            . '<div class="wl-stl-item-bottom">'
            . ( $args['meta'] ? '' : $this->card_price( $product, $settings ) )
            . $this->card_swatches( $product, $settings )
            . ( $args['meta'] ? $this->card_meta( $product, $settings, 1 ) : '' )
            . '</div>'
            . ( $args['cart_in_body'] ? $button : '' )
            . '</div>'
            . ( $args['meta']
                ? '<div class="wl-stl-item-right">' . $this->card_price( $product, $settings )
                    . ( $args['cart_in_body'] ? '' : $button ) . '</div>'
                : ( $args['cart_in_body'] ? '' : $button ) )
            . '</div>';
    }

    /**
     * The products in a look that a single click can actually add.
     *
     * A variable product needs a variation chosen, an external one lives on someone else's site,
     * and one out of stock cannot be bought at all. None of the three can be added blind, so they
     * are counted out here and the button says what it will really do.
     *
     * @param  array $pins
     * @return array
     */
    protected function addable_pins( array $pins ) {
        return array_values( array_filter( $pins, function ( $pin ) {
            $product = $pin['product'];

            return $product->is_purchasable()
                && $product->is_in_stock()
                && ! $product->is_type( 'variable' )
                && ! $product->is_type( 'external' );
        } ) );
    }

    /**
     * The button that adds a whole look in one action.
     *
     * `{total}` is a placeholder because Luxury V2's reference puts the price inside the label —
     * *Add All to Bag — €1,060* — while Modern V2's keeps it out.
     *
     * When some of the look cannot be added the button says how many it will add, so a shopper is
     * never told "add the complete look" and then given two thirds of it. When none of it can, the
     * button becomes a plain link to the first product rather than a control that does nothing.
     *
     * @param  array  $pins
     * @param  array  $settings
     * @param  string $fallback  This layout's own wording.
     * @return string
     */
    protected function bulk_html( array $pins, array $settings, $fallback ) {
        if ( empty( $pins ) ) {
            return '';
        }

        $addable = $this->addable_pins( $pins );
        $label   = trim( (string) ( $settings['bulk_text'] ?? '' ) );

        if ( '' === $label ) {
            $label = $fallback;
        }

        if ( '' === $label ) {
            return '';
        }

        if ( empty( $addable ) ) {
            $first = $pins[0]['product'];

            return '<div class="wl-stl-bulk"><a class="wl-stl-bulk-btn" href="' . esc_url( $first->get_permalink() ) . '">'
                . esc_html__( 'View the look', 'woolentor' ) . '</a></div>';
        }

        $label = str_replace( '{total}', $this->total_html( $addable, true ), $label );

        if ( count( $addable ) < count( $pins ) ) {
            /* translators: %d: number of products that can be added */
            $label = sprintf( _n( 'Add %d item to cart', 'Add %d items to cart', count( $addable ), 'woolentor' ), count( $addable ) );
        }

        $ids = implode( ',', array_map( function ( $pin ) {
            return $pin['id'];
        }, $addable ) );

        return '<div class="wl-stl-bulk">'
            . '<button type="button" class="wl-stl-bulk-btn" data-wl-stl-bulk="' . esc_attr( $ids ) . '"'
            . ' data-wl-stl-nonce="' . esc_attr( wp_create_nonce( 'woolentor_psa_nonce' ) ) . '"'
            . ' data-wl-stl-added="' . esc_attr__( 'Added to cart', 'woolentor' ) . '">'
            . esc_html( $label ) . '</button>'
            . '</div>';
    }

    /**
     * The look switcher.
     *
     * Four references switch looks and each draws it differently — Modern V2 uses labelled
     * thumbnails, Editorial V1 a tab strip, Magazine V2 tabs above the image. Same data, three
     * skins, so the markup is one shape and $mode only decides whether a thumbnail is printed.
     * Everything else is the pack stylesheet's business.
     *
     * Returns an empty string for a single look: a switcher with one button is furniture.
     *
     * @param  array  $looks
     * @param  string $mode  'tabs', 'thumbs' or 'counter'.
     * @return string
     */
    protected function switcher_html( array $looks, $mode = 'tabs' ) {
        if ( count( $looks ) < 2 ) {
            return '';
        }

        if ( 'counter' === $mode ) {
            return $this->counter_html( $looks );
        }

        // A group of toggle buttons, not a tab list. `role="tab"` is a promise that the button
        // controls a `role="tabpanel"` named by `aria-controls` — and a look here is rendered
        // twice on a split layout, once in the stage and once in the panel, so there is no single
        // panel to point at. An incomplete tab pattern reads worse to a screen reader than an
        // honest pressed button: it announces a tab that controls nothing.
        $out = '<div class="wl-stl-switch wl-stl-switch-' . esc_attr( 'thumbs' === $mode ? 'thumbs' : 'tabs' ) . '"'
            . ' role="group" aria-label="' . esc_attr__( 'Choose a look', 'woolentor' ) . '">';

        foreach ( $looks as $position => $look ) {
            $active = 0 === $position;
            $label  = '' !== $look['label'] ? $look['label'] : sprintf(
                /* translators: %d: look number */
                esc_html__( 'Look %d', 'woolentor' ),
                $look['index']
            );

            $thumb = '';
            if ( 'thumbs' === $mode && $look['id'] ) {
                // The same image at a smaller size, never a second upload.
                $thumb = '<span class="wl-stl-switch-thumb">'
                    . wp_get_attachment_image( $look['id'], 'thumbnail', false, [ 'alt' => '', 'loading' => 'lazy' ] )
                    . '</span>';
            }

            $out .= '<button type="button"'
                . ' class="wl-stl-switch-btn' . ( $active ? ' is-active' : '' ) . '"'
                . ' aria-pressed="' . ( $active ? 'true' : 'false' ) . '"'
                . ' data-wl-stl-switch="' . esc_attr( $look['index'] ) . '">'
                . $thumb
                // Printed for every skin and hidden by the packs that do not show it — Magazine V2
                // numbers its tabs, the others do not, and that is a CSS decision not a mode.
                . '<span class="wl-stl-switch-num" aria-hidden="true">'
                . esc_html( str_pad( (string) $look['index'], 2, '0', STR_PAD_LEFT ) ) . '</span>'
                . '<span class="wl-stl-switch-label">' . esc_html( $label ) . '</span>'
                . '</button>';
        }

        return $out . '</div>';
    }

    /**
     * The counter shape of the switcher — a previous arrow, `01 / 03`, a next arrow.
     *
     * Editorial V2's reference draws it this way instead of listing the looks. The buttons step
     * rather than name a look, so the script wraps at either end and the reader never reaches a
     * dead arrow.
     *
     * @param  array $looks
     * @return string
     */
    protected function counter_html( array $looks ) {
        $arrow = function ( $back ) {
            return '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"'
                . ' viewBox="0 0 24 24" aria-hidden="true"><path d="'
                . ( $back ? 'M19 12H5M12 19l-7-7 7-7' : 'M5 12h14M12 5l7 7-7 7' )
                . '"/></svg>';
        };

        return '<div class="wl-stl-counter">'
            . '<button type="button" class="wl-stl-counter-btn" data-wl-stl-step="-1"'
            . ' aria-label="' . esc_attr__( 'Previous look', 'woolentor' ) . '">' . $arrow( true ) . '</button>'
            // The counter is the only thing that says a step worked, and the script rewrites it in
            // place — announced politely so a screen reader hears "02 / 03" after pressing Next
            // rather than nothing at all.
            . '<span class="wl-stl-counter-text" aria-live="polite" aria-atomic="true">'
            . '<span class="wl-stl-counter-now">01</span> / '
            . '<span class="wl-stl-counter-total">' . esc_html( str_pad( (string) count( $looks ), 2, '0', STR_PAD_LEFT ) ) . '</span>'
            . '</span>'
            . '<button type="button" class="wl-stl-counter-btn" data-wl-stl-step="1"'
            . ' aria-label="' . esc_attr__( 'Next look', 'woolentor' ) . '">' . $arrow( false ) . '</button>'
            . '</div>';
    }

    /**
     * The section header. Empty when nothing was typed, so a layout that offers one but is left
     * blank collapses rather than printing an empty block.
     *
     * @param  array $settings
     * @param  array $look  Optional. A look whose own title and copy replace the section's.
     * @return string
     */
    protected function header_html( array $settings, array $look = [] ) {
        $eyebrow = trim( (string) ( $look['label'] ?? '' ) );
        $eyebrow = '' !== $eyebrow ? $eyebrow : trim( (string) ( $settings['eyebrow'] ?? '' ) );

        $heading = trim( (string) ( $look['title'] ?? '' ) );
        $heading = '' !== $heading ? $heading : trim( (string) ( $settings['heading'] ?? '' ) );

        $text = trim( (string) ( $look['desc'] ?? '' ) );
        $text = '' !== $text ? $text : trim( (string) ( $settings['description'] ?? '' ) );

        if ( '' === $eyebrow && '' === $heading && '' === $text ) {
            return '';
        }

        $tag = in_array( $settings['heading_tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ], true )
            ? $settings['heading_tag']
            : 'h2';

        return '<div class="wl-stl-head">'
            . ( '' !== $eyebrow ? '<span class="wl-stl-eyebrow">' . esc_html( $eyebrow ) . '</span>' : '' )
            . ( '' !== $heading ? '<' . $tag . ' class="wl-stl-heading">' . esc_html( $heading ) . '</' . $tag . '>' : '' )
            . ( '' !== $text ? '<p class="wl-stl-desc">' . esc_html( $text ) . '</p>' : '' )
            . '</div>';
    }

    /**
     * The line counting a look's items — Luxury V1's "04 items in this composition", Magazine V1's
     * "3 ITEMS IN THIS LOOK".
     *
     * `{count}` is a placeholder rather than a sprintf token so a merchant cannot break the string
     * by typing the wrong one, and each layout passes its own reference's wording as the fallback.
     *
     * @param  array  $pins      This look's pins.
     * @param  array  $settings
     * @param  string $fallback  This layout's own wording, with {count} in it.
     * @return string
     */
    protected function count_html( array $pins, array $settings, $fallback ) {
        if ( empty( $pins ) ) {
            return '';
        }

        $text = trim( (string) ( $settings['count_text'] ?? '' ) );

        if ( '' === $text ) {
            $text = $fallback;
        }

        if ( '' === $text ) {
            return '';
        }

        return '<span class="wl-stl-count">'
            . esc_html( str_replace( '{count}', number_format_i18n( count( $pins ) ), $text ) )
            . '</span>';
    }

    /**
     * A look's summed price, as WooCommerce would print it.
     *
     * A **display** total and nothing more: no tax, no shipping and no coupon logic, because a
     * section cannot know any of those. Where every product in the look is on sale the original is
     * summed too and returned struck through beside it, which is the pair Magazine V1's footer
     * shows. A look with a single unpriced product still totals the rest rather than printing
     * nothing.
     *
     * @param  array $pins
     * @param  bool  $raw  Return the payable figure as plain text — what {total} substitutes in.
     * @return string
     */
    protected function total_html( array $pins, $raw = false ) {
        if ( empty( $pins ) || ! function_exists( 'wc_price' ) ) {
            return '';
        }

        $now  = 0.0;
        $was  = 0.0;
        $sale = false;

        foreach ( $pins as $pin ) {
            $price   = (float) $pin['product']->get_price();
            $regular = (float) $pin['product']->get_regular_price();

            $now += $price;
            $was += $regular > 0 ? $regular : $price;

            if ( $regular > $price ) {
                $sale = true;
            }
        }

        if ( $now <= 0 ) {
            return '';
        }

        if ( $raw ) {
            // Entities are decoded, not just stripped of tags. wc_price() writes the currency as
            // `&euro;`, and the caller escapes what it gets back — so leaving it encoded would put
            // a literal "&euro;" in the label instead of the symbol.
            return html_entity_decode( wp_strip_all_tags( wc_price( $now ) ), ENT_QUOTES, get_bloginfo( 'charset' ) );
        }

        return '<span class="wl-stl-total">'
            . ( $sale ? '<del>' . wc_price( $was ) . '</del> ' : '' )
            . '<ins>' . wc_price( $now ) . '</ins>'
            . '</span>';
    }

    /**
     * The one outbound link the footer closes with.
     *
     * **A merchant who types nothing still gets the button**, because that is what the design is
     * built around: an empty Link falls back to the shop page — which is where *Shop the Full Room*
     * was always going — and an empty Text falls back to this layout's own wording. Before, an
     * empty Link meant no button at all, so the section shipped looking broken until someone found
     * the field.
     *
     * @param  array  $settings
     * @param  string $fallback  This layout's own wording.
     * @return string
     */
    protected function cta_html( array $settings, $fallback = '' ) {
        $text = trim( (string) ( $settings['cta_text'] ?? '' ) );
        $url  = trim( (string) ( $settings['cta_link']['url'] ?? '' ) );

        if ( '' === $text ) {
            $text = $fallback;
        }

        if ( '' === $url && function_exists( 'wc_get_page_permalink' ) ) {
            $url = (string) wc_get_page_permalink( 'shop' );
        }

        // A store with no shop page set has nowhere to send anyone; a link to nothing is worse
        // than no link.
        if ( '' === $text || '' === $url ) {
            return '';
        }

        $rel    = [];
        $target = '';

        if ( ! empty( $settings['cta_link']['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }

        if ( ! empty( $settings['cta_link']['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<div class="wl-stl-cta"><a href="' . esc_url( $url ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>'
            . esc_html( $text ) . ' <span aria-hidden="true">&rarr;</span></a></div>';
    }

    /**
     * One pin — the button and the card it controls.
     *
     * The button is a real <button aria-expanded aria-controls>, not a hover target: the card holds
     * an Add to Cart, and a hover-only card cannot be reached by touch or by keyboard. Hover is
     * added as an enhancement in CSS, never as the only way in.
     *
     * Templates are included inside this class, so they call it as $this->pin_html( $pin, … ).
     *
     * @param  array  $pin       One entry from build_pins().
     * @param  array  $settings
     * @param  string $card      Card body markup, already safe.
     * @param  int    $number    Print this as the pin's face instead of the pack's glyph.
     * @return string
     */
    protected function pin_html( array $pin, array $settings, $card, $number = 0 ) {
        $product = $pin['product'];
        $uid     = 'wl-stl-card-' . $this->get_id() . '-' . $pin['row_id'];

        $classes = [ 'wl-stl-pin' ];

        if ( $pin['row_id'] ) {
            $classes[] = 'elementor-repeater-item-' . $pin['row_id'];
        }

        if ( 'auto' !== $pin['side'] ) {
            $classes[] = 'is-card-' . $pin['side'];
        }

        /* translators: %s: product name */
        $aria = sprintf( esc_html__( 'Show details for %s', 'woolentor' ), $product->get_name() );

        return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-wl-stl-pin="' . esc_attr( $pin['row_id'] ) . '">'
            . '<button type="button" class="wl-stl-pin-btn" aria-expanded="false" aria-controls="' . esc_attr( $uid ) . '"'
            . ' aria-label="' . esc_attr( $aria ) . '">'
            . ( $number
                ? '<span class="wl-stl-pin-num" aria-hidden="true">' . esc_html( str_pad( (string) $number, 2, '0', STR_PAD_LEFT ) ) . '</span>'
                : '<span class="wl-stl-pin-glyph" aria-hidden="true"></span>' )
            . '</button>'
            . '<div class="wl-stl-card" id="' . esc_attr( $uid ) . '" hidden>' . $card . '</div>'
            . '</div>';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Where this build's templates live. The Pro mirror overrides it to the free plugin's copy —
     * all twelve templates and all four stylesheets ship in free, and Pro removes the gate rather
     * than shipping the designs a second time.
     *
     * @return string
     */
    protected function templates_dir() {
        return __DIR__ . '/templates';
    }

    /**
     * Render the real template with demo content so the user can see a Pro variant in the editor
     * before upgrading. The frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Shop the Look' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Shop the Look' );
            return;
        }

        $looks = $this->build_looks( $settings['looks'] ?? [] );
        $pins  = $this->build_pins( $settings['hotspots'] ?? [] );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-stl wl-stl-' . esc_attr( $style ) . ' wl-stl-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-stl>';
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

        if ( ! function_exists( 'wc_get_product' ) ) {
            echo '<p>' . esc_html__( 'Shop the Look requires WooCommerce.', 'woolentor' ) . '</p>';
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

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Shop the Look template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $rows  = $settings['hotspots'] ?? [];
        $looks = $this->build_looks( $settings['looks'] ?? [] );
        $pins  = $this->build_pins( $rows );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-stl wl-stl-' . esc_attr( $style ) . ' wl-stl-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-stl>';
        include $template;
        echo '</div>';

        // Editor-only: a row pointing at a deleted or unpublished product is dropped silently on
        // the frontend, so this is the only place the page builder would ever hear about it.
        $dropped = $this->count_dropped( $rows, $pins );

        if ( $dropped && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<p class="elementor-alert elementor-alert-warning">'
                . esc_html( sprintf(
                    /* translators: %d: number of pins */
                    _n(
                        '%d pin was skipped — its product no longer exists or is not published.',
                        '%d pins were skipped — their products no longer exist or are not published.',
                        $dropped,
                        'woolentor'
                    ),
                    $dropped
                ) )
                . '</p>';
        }

        echo '</div>';
    }
}
