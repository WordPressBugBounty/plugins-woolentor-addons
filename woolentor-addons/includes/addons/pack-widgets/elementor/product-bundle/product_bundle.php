<?php
/**
 * Product Bundle Widget — Pattern B (Style + Variant dropdowns).
 *
 * Curated sets of existing products with a combined price and one-click add to cart. Every one of
 * the 12 style-variant layouts renders the same content model — two flat repeaters, `bundles` and
 * `items`, joined by an index — so switching Style or Variant never loses content.
 *
 * This is NOT the WooCommerce "Product Bundles" extension and does not integrate with it. There is
 * no bundled product type, no shared stock and no synthetic price: the widget lays out ordinary
 * products, shows what the set costs today, and adds them to the cart together.
 *
 * Nothing about a product is stored in the repeater — image, title, price and stock are read from
 * the catalogue at render, so a price change or a sold-out product is reflected without anyone
 * editing the page.
 *
 * **The saving is derived, never typed.** See bundle_totals(): `was` is the sum of regular prices,
 * `now` is the sum of effective prices, and the difference is what the shopper actually saves at
 * checkout. A merchant who wants a deeper bundle-only discount attaches a real WooCommerce coupon
 * (per-bundle `bundle_coupon`), which the endpoint applies to the cart. There is deliberately no
 * display-only discount field: it would show one number and charge another.
 *
 * Spec: blueprint/product-bundle-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Product_Bundle_Widget extends Widget_Base {

    /**
     * Style + variant combinations whose template prints a section header — an eyebrow, a heading
     * and a line of description above the bundles.
     *
     * Editorial V3 is deliberately absent: like `room-study` in Shop the Look, it opens straight
     * onto its subject, and offering a heading there would offer one that never renders.
     */
    const HEADER_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Variants where each item is a toggle and the total recalculates live — the V2 role.
     * This is the whole difference between the free tier and the first paid one, so it is exactly
     * the V2 column and nothing else.
     */
    const SELECTABLE_VARIANTS = [
        'modern'    => [ 'v2' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v2' ],
        'magazine'  => [ 'v2' ],
    ];

    /**
     * Variants that show one bundle at a time behind a switcher — the V3 role, plus Magazine V2,
     * whose reference pages between bundles with arrows rather than listing them.
     */
    const SINGLE_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v3' ],
        'luxury'    => [ 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    /**
     * Switcher shape per variant. Four modes, one helper: thumbs | tabs | arrows | counter.
     * Keys here must match SINGLE_VARIANTS exactly — a single-bundle variant with no switcher
     * would strand every bundle after the first.
     */
    const SWITCHER_MODE = [
        'modern'    => [ 'v3' => 'thumbs' ],
        'editorial' => [ 'v3' => 'counter' ],
        'luxury'    => [ 'v3' => 'tabs' ],
        'magazine'  => [ 'v2' => 'arrows', 'v3' => 'tabs' ],
    ];

    /** Variants that print a per-item price inside the list. */
    const ITEM_PRICE_VARIANTS = [
        'modern'    => [ 'v2' ],
        'editorial' => [ 'v1', 'v2' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** Variants that print the "thumb + thumb + thumb" strip above the list. */
    const THUMBSTRIP_VARIANTS = [
        'modern'   => [ 'v1' ],
        'luxury'   => [ 'v3' ],
        'magazine' => [ 'v1' ],
    ];

    /** Variants that print an "Included" badge in place of a price. */
    const BADGE_VARIANTS = [
        'modern' => [ 'v1', 'v3' ],
    ];

    /** Variants carrying the free-gift progress bar. */
    const GIFT_VARIANTS = [
        'magazine' => [ 'v2' ],
    ];

    /** Variants that print the bundle photograph. Required on V3 — there it is the subject. */
    const IMAGE_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Variants that print the header's outbound "All bundles" link. */
    const HEADLINK_VARIANTS = [
        'modern'   => [ 'v1', 'v2' ],
        'luxury'   => [ 'v1', 'v2' ],
        'magazine' => [ 'v1' ],
    ];

    /**
     * Variants that print a per-bundle ribbon ("Best Value", "Most Popular").
     *
     * Luxury only, because Luxury's reference is the only one with a slot for it — `.deal-saving`
     * beside the price. Offering the control on a pack whose layout has nowhere to put it would
     * be a control that does nothing.
     */
    const RIBBON_VARIANTS = [
        'luxury' => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Variants whose item rows carry the product thumbnail.
     *
     * Magazine V1 and V3 are absent on purpose: both are typographic lists in their references —
     * a tick or a numeral and then the name. Luxury V3 is absent because its items are the
     * thumbnail strip, not a list.
     */
    const ITEM_MEDIA_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v2' ],
    ];

    /** Variants that mark a fixed (non-toggleable) row with a tick. */
    const CHECK_VARIANTS = [
        'editorial' => [ 'v1', 'v3' ],
        'luxury'    => [ 'v1' ],
        'magazine'  => [ 'v1' ],
    ];

    /** Variants that number their item rows instead of ticking them. */
    const NUMBER_VARIANTS = [
        'magazine' => [ 'v3' ],
    ];

    /**
     * Variants that draw a boxed container — a card, a panel, or Magazine V2's two columns.
     *
     * One map rather than three, because the Style tab offers one Box section: a merchant setting
     * a background and a corner radius does not care whether the template calls its box a card or
     * a panel, and three near-identical sections would be three places to look.
     *
     * Absent: every Editorial variant, Luxury V3 and Magazine V3 — all of them sit directly on the
     * section with no box at all, so the controls would style nothing.
     */
    const BOX_VARIANTS = [
        'modern'   => [ 'v1', 'v2', 'v3' ],
        'luxury'   => [ 'v1', 'v2' ],
        'magazine' => [ 'v1', 'v2' ],
    ];

    /**
     * Variants that print the bundle's tag as `.wl-pb-tag`.
     *
     * Magazine V1 prints no tag at all — its card head is the bundle's index and name — and
     * Editorial V2 and Magazine V3 put the same text in a `.wl-pb-flag` over the photograph
     * instead. Offering Tag Color on those three would be three controls that do nothing.
     */
    const TAG_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v2' ],
    ];

    /** Variants that print the tag as a flag over the photograph instead. */
    const FLAG_VARIANTS = [
        'editorial' => [ 'v2' ],
        'magazine'  => [ 'v3' ],
    ];

    /**
     * Variants whose thumbnail strip carries the item's name and price — Luxury V3, where the
     * strip *is* the item list. Those labels are `.wl-pb-thumb-name` / `.wl-pb-thumb-price`, not
     * the `.wl-pb-item-*` the Items section styles, so without their own controls they were the
     * one place a merchant could not recolour a product title.
     */
    const LABELLED_STRIP_VARIANTS = [
        'luxury' => [ 'v3' ],
    ];

    /** Variants that recap the set as `.wl-pb-line` rows beside the total — Magazine V2 only. */
    const LINES_VARIANTS = [
        'magazine' => [ 'v2' ],
    ];

    /** Variants that print the plate's caption underneath the photograph. */
    const CAPTION_VARIANTS = [
        'editorial' => [ 'v3' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Variants that print the bundle's own line of copy. */
    const INTRO_VARIANTS = [
        'modern'    => [ 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    /**
     * Variants that lay their bundles out in `.wl-pb-grid`.
     *
     * Editorial stacks its splits and the single-bundle variants use `.wl-pb-stage`, so the
     * "Gap Between Bundles" control has nothing to act on there.
     */
    const GRID_VARIANTS = [
        'modern'   => [ 'v1', 'v2' ],
        'luxury'   => [ 'v1', 'v2' ],
        'magazine' => [ 'v1' ],
    ];

    /**
     * Variants whose template asks total_html() for its label. The rest print the figures alone,
     * so both the Total Label text control and its colour would be inert there.
     */
    const TOTAL_LABEL_VARIANTS = [
        'modern'   => [ 'v3' ],
        'magazine' => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Variants that render `.wl-pb-list`. Everything except Luxury V3, whose items are the
     * labelled thumbnail strip instead.
     */
    const LIST_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
        $this->register_pack_script();
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-product-bundle-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/product-bundle/{$pack}.css",
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
        return 'woolentor-product-bundle';
    }

    public function get_title() {
        return esc_html__( 'Product Bundle - 2026', 'woolentor' );
    }

    public function get_icon() {
        // The plan named `eicon-bundle`; no such icon exists in Elementor. This one is used
        // elsewhere in the plugin and is the closest true match.
        return 'woolentor-widget-new-icon eicon-product-upsell';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'bundle', 'product bundle', 'bundle deal', 'set', 'kit', 'combo',
                 'frequently bought together', 'bulk add to cart', 'save', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-product-bundle-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_script_depends() {
        // wc-add-to-cart is WooCommerce's own handler for the loop button. WooCommerce only enqueues
        // it on its own pages, so on an ordinary page a button carrying `ajax_add_to_cart` would
        // have nothing listening and every add would navigate away instead of adding in place.
        return [ 'wl-pack-widgets', 'wc-add-to-cart' ];
    }

    /**
     * Style => locked variants. Every pack's v2 and v3 require Pro.
     *
     * **This one method is the whole Pro gate**, and the Pro mirror's only real change is to
     * return an empty array from it. Everything downstream then neutralises itself:
     *
     *   - `is_pro_variant( …, [] )` is false, so `render()` never diverts to the preview.
     *   - `build_negated_conditions( [] )` is an AND with no terms, which Elementor reads as
     *     always true — every content and style section stays visible.
     *   - the upsell registrations below return early on an empty map, so no notice is built.
     *
     * The mirror therefore cuts nothing. That matters: the Shop the Look generator ate a
     * neighbouring method twice by cutting, and this one ate three before its assertions stopped
     * it. A mirror that rewrites two method bodies cannot lose a method.
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
     * True when the current selection is NOT any of the mapped combinations.
     * Used to hide editable controls on Pro-locked variants.
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
     * A control section often holds things that appear on different variant sets; its gate has to
     * be the union, or a merchant on one of them cannot reach a control that does render.
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

    /**
     * The style => variants map of everything using one switcher shape.
     *
     * Derived from SWITCHER_MODE rather than written out again, so a variant that changes its
     * switcher cannot leave a Style-tab control pointing at a control that is no longer drawn.
     *
     * @param  string ...$modes  thumbs | tabs | arrows | counter
     * @return array
     */
    private function switcher_map( ...$modes ) {
        $map = [];

        foreach ( self::SWITCHER_MODE as $style => $variants ) {
            foreach ( $variants as $variant => $mode ) {
                if ( in_array( $mode, $modes, true ) ) {
                    $map[ $style ][] = $variant;
                }
            }
        }

        return $map;
    }

    /**
     * Whether the current style+variant is in a map. The templates' own question, asked of the
     * same constants the controls are gated on, so markup and controls can never drift apart.
     *
     * @param  array  $map
     * @param  string $style
     * @param  string $variant
     * @return bool
     */
    protected function in_map( array $map, $style, $variant ) {
        return isset( $map[ $style ] ) && in_array( $variant, $map[ $style ], true );
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

            $this->add_control( 'variant_note', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'Variant 1 shows every item as included. Variant 2 lets the shopper switch items on and off and recalculates the total. Variant 3 features one bundle at a time.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            // Guarded rather than cut by the mirror: with Pro active get_pro_map() is empty and
            // there is nothing to upsell.
            if ( $this->get_pro_map() ) {
                woolentor_upgrade_pro_notice(
                    $this,
                    'product_bundle_variant_pro_notice',
                    [ 'variant' => [ 'v2', 'v3' ] ],
                    [ 'mode' => 'alert' ]
                );
            }

        $this->end_controls_section();

        $this->register_header_controls();
        $this->register_bundle_controls();
        $this->register_item_controls();
        $this->register_display_controls();
        $this->register_selection_controls();
        $this->register_cart_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content: Header ───────────────────────────────────────────────────────

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
                'default'     => esc_html__( 'Bundle & Save', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'heading', [
                'label'       => esc_html__( 'Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Curated sets that work together.', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Accepts <em>, <strong> and <br> for an accent word.', 'woolentor' ),
            ] );

            $this->add_control( 'description', [
                'label'   => esc_html__( 'Description', 'woolentor' ),
                'type'    => Controls_Manager::TEXTAREA,
                'rows'    => 3,
                'default' => esc_html__( 'Picked as a system, not a list — and priced as one.', 'woolentor' ),
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

            $this->add_control( 'link_text', [
                'label'       => esc_html__( 'Header Link Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'All bundles', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Leave empty to hide the link.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::HEADLINK_VARIANTS ),
            ] );

            $this->add_control( 'link_url', [
                'label'       => esc_html__( 'Header Link', 'woolentor' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Defaults to the shop page', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::HEADLINK_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Bundles ──────────────────────────────────────────────────────

    private function register_bundle_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'bundle_name', [
            'label'       => esc_html__( 'Bundle Name', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'The Complete Set', 'woolentor' ),
            'label_block' => true,
            'description' => esc_html__( 'Accepts <em> and <strong>.', 'woolentor' ),
        ] );

        $repeater->add_control( 'bundle_tag', [
            'label'       => esc_html__( 'Tag', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Bundle Deal', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'bundle_image', [
            'label'       => esc_html__( 'Image', 'woolentor' ),
            'type'        => Controls_Manager::MEDIA,
            'description' => esc_html__( 'Used by the layouts that show one; required on Variant 3, where the photograph is the subject.', 'woolentor' ),
        ] );

        $repeater->add_control( 'bundle_desc', [
            'label' => esc_html__( 'Description', 'woolentor' ),
            'type'  => Controls_Manager::TEXTAREA,
            'rows'  => 2,
        ] );

        $repeater->add_control( 'bundle_ribbon', [
            'label'       => esc_html__( 'Ribbon', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
            'description' => esc_html__( 'Best Value, Most Popular… Empty hides it.', 'woolentor' ),
        ] );

        $repeater->add_control( 'bundle_coupon', [
            'label'       => esc_html__( 'Coupon Code', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
            'description' => esc_html__( 'Optional. A real WooCommerce coupon, applied to the cart when the bundle is added. The saving shown above it always comes from the products\' own prices.', 'woolentor' ),
        ] );

        $repeater->add_control( 'bundle_gift_amount', [
            'label'       => esc_html__( 'Free Gift Threshold', 'woolentor' ),
            'type'        => Controls_Manager::NUMBER,
            'min'         => 0,
            'description' => esc_html__( 'Shows a progress bar toward a promotion you have set up elsewhere. Empty hides it.', 'woolentor' ),
        ] );

        $repeater->add_control( 'bundle_gift_label', [
            'label'       => esc_html__( 'Free Gift Label', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'a free gift', 'woolentor' ),
            'label_block' => true,
        ] );

        $repeater->add_control( 'bundle_cta_text', [
            'label'       => esc_html__( 'Button Text Override', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
        ] );

        $repeater->add_control( 'bundle_cta_link', [
            'label'       => esc_html__( 'Button Link', 'woolentor' ),
            'type'        => Controls_Manager::URL,
            'description' => esc_html__( 'Optional. With a link the button navigates; without one it adds the bundle to the cart.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_bundles', [
            'label'      => esc_html__( 'Bundles', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'bundles', [
                'label'       => esc_html__( 'Bundles', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ bundle_name }}}',
                'default'     => [
                    [ 'bundle_name' => esc_html__( 'The Complete Set', 'woolentor' ), 'bundle_tag' => esc_html__( 'Bundle Deal', 'woolentor' ) ],
                ],
            ] );

            $this->add_control( 'bundles_hint', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'Three bundles read best in a grid. Past six the section becomes a wall.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            // No default. Elementor writes a control default into the page's generated CSS, which
            // outranks the pack stylesheets — a default here would force one shape onto all twelve
            // layouts and silently override every pack's own ratio.
            $this->add_responsive_control( 'image_ratio', [
                'label'      => esc_html__( 'Image Ratio', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [ 'px' => [ 'min' => 0.4, 'max' => 2, 'step' => 0.01 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-image' => '--wl-pb-ratio: {{SIZE}};',
                ],
                'conditions' => $this->build_conditions( self::IMAGE_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Items ────────────────────────────────────────────────────────

    private function register_item_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'product', [
            'label'       => esc_html__( 'Product', 'woolentor' ),
            'type'        => 'woolentor-select',
            'ajax_search' => true,
            'post_type'   => 'product',
            'label_block' => true,
            'placeholder' => esc_html__( 'Search products…', 'woolentor' ),
        ] );

        // Elementor evaluates a repeater field's condition against its own row, never against a
        // widget-level control, so this cannot be hidden on a single-bundle layout. It is
        // described instead — and a layout showing one bundle simply reads Bundle 1.
        $repeater->add_control( 'bundle', [
            'label'       => esc_html__( 'Belongs to Bundle', 'woolentor' ),
            'type'        => Controls_Manager::NUMBER,
            'default'     => 1,
            'min'         => 1,
            'step'        => 1,
            'description' => esc_html__( 'Which bundle this item belongs to, counting from the top of the Bundles list.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_meta', [
            'label'       => esc_html__( 'Meta Line', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
            'description' => esc_html__( 'Over-ear · Active Noise Cancellation. Falls back to the product\'s first category.', 'woolentor' ),
        ] );

        $repeater->add_control( 'item_qty', [
            'label'   => esc_html__( 'Quantity', 'woolentor' ),
            'type'    => Controls_Manager::NUMBER,
            'default' => 1,
            'min'     => 1,
            'step'    => 1,
        ] );

        $repeater->add_control( 'item_required', [
            'label'        => esc_html__( 'Always Included', 'woolentor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'description'  => esc_html__( 'On Variant 2, an always-included item cannot be switched off.', 'woolentor' ),
            'return_value' => 'yes',
        ] );

        $this->start_controls_section( 'section_items', [
            'label'      => esc_html__( 'Items', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'items', [
                'label'       => esc_html__( 'Items', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '#{{{ bundle }}} — {{{ product }}}',
                'default'     => [],
            ] );

            $this->add_control( 'items_hint', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'Every item lives in this one list. The Belongs to Bundle number is what puts it in a bundle — set it to 2 for the second bundle, and so on. Variable and external products are skipped, because neither can be added to a cart without a choice.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

        $this->end_controls_section();
    }

    // ── Content: Display ──────────────────────────────────────────────────────

    private function register_display_controls() {
        $this->start_controls_section( 'section_display', [
            'label'      => esc_html__( 'Display', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'show_item_meta', [
                'label'        => esc_html__( 'Item Meta Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
            ] );

            $this->add_control( 'show_item_price', [
                'label'        => esc_html__( 'Item Price', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::ITEM_PRICE_VARIANTS ),
            ] );

            $this->add_control( 'show_thumbstrip', [
                'label'        => esc_html__( 'Thumbnail Strip', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::THUMBSTRIP_VARIANTS ),
            ] );

            $this->add_control( 'badge_text', [
                'label'       => esc_html__( 'Included Badge', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Included', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Leave empty to hide it.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::BADGE_VARIANTS ),
            ] );

            $this->add_control( 'show_saving', [
                'label'        => esc_html__( 'Saving', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'description'  => esc_html__( 'Hidden automatically when nothing in the bundle is on sale.', 'woolentor' ),
            ] );

            $this->add_control( 'saving_format', [
                'label'     => esc_html__( 'Saving Shows', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'amount',
                'options'   => [
                    'amount'  => esc_html__( 'Amount', 'woolentor' ),
                    'percent' => esc_html__( 'Percent', 'woolentor' ),
                    'both'    => esc_html__( 'Amount and percent', 'woolentor' ),
                ],
                'condition' => [ 'show_saving' => 'yes' ],
            ] );

            $this->add_control( 'show_ribbon', [
                'label'        => esc_html__( 'Ribbon', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::RIBBON_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Selection ────────────────────────────────────────────────────

    private function register_selection_controls() {
        $this->start_controls_section( 'section_selection', [
            'label'      => esc_html__( 'Selection', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::SELECTABLE_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'count_text', [
                'label'       => esc_html__( 'Count Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( '{selected} of {total} selected', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Use {selected} and {total}.', 'woolentor' ),
            ] );

            $this->add_control( 'select_all_text', [
                'label'   => esc_html__( 'Select All Text', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'All', 'woolentor' ),
            ] );

            $this->add_control( 'select_none_text', [
                'label'   => esc_html__( 'Select None Text', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'None', 'woolentor' ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Cart ─────────────────────────────────────────────────────────

    private function register_cart_controls() {
        $this->start_controls_section( 'section_cart', [
            'label'      => esc_html__( 'Cart', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'cta_text', [
                'label'       => esc_html__( 'Button Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Add bundle', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'added_text', [
                'label'   => esc_html__( 'Added Text', 'woolentor' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Added', 'woolentor' ),
            ] );

            $this->add_control( 'empty_text', [
                'label'      => esc_html__( 'Nothing Selected Text', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Select at least one item', 'woolentor' ),
                'conditions' => $this->build_conditions( self::SELECTABLE_VARIANTS ),
            ] );

            // Only some layouts print a label beside the figure; elsewhere the button says what
            // the price is for. Offering the text where it never renders is the same bug as
            // offering its colour.
            $this->add_control( 'total_label', [
                'label'      => esc_html__( 'Total Label', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Bundle Price', 'woolentor' ),
                'conditions' => $this->build_conditions( self::TOTAL_LABEL_VARIANTS ),
            ] );

            $this->add_control( 'saving_label', [
                'label'     => esc_html__( 'Saving Label', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Save', 'woolentor' ),
                'condition' => [ 'show_saving' => 'yes' ],
            ] );

        $this->end_controls_section();
    }

    private function register_pro_content_notice() {
        if ( ! $this->get_pro_map() ) {
            return;
        }

        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'      => esc_html__( 'Content', 'woolentor' ),
            'conditions' => $this->build_conditions( $this->get_pro_map() ),
        ] );
            woolentor_upgrade_pro_notice( $this, 'product_bundle_content_pro_notice', $condition );
        $this->end_controls_section();
    }

    private function register_pro_style_notice() {
        if ( ! $this->get_pro_map() ) {
            return;
        }

        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_style_notice', [
            'label'      => esc_html__( 'Style', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( $this->get_pro_map() ),
        ] );
            woolentor_upgrade_pro_notice( $this, 'product_bundle_style_pro_notice', $condition );
        $this->end_controls_section();
    }

    // ── Style ─────────────────────────────────────────────────────────────────

    /**
     * The Style tab.
     *
     * Two rules hold everywhere in here, and both were learned the hard way on Shop the Look:
     *
     *   1. **Every control is gated by the map of the thing it styles.** A colour picker offered on
     *      a variant whose template never prints that element is a control that does nothing, and
     *      there were five such sections in Shop the Look before its audit found them.
     *   2. **Almost nothing carries a default.** Elementor writes a control's default straight into
     *      the page's generated CSS, which outranks the pack stylesheets — so a default here would
     *      silently overwrite every pack's own design. Leaving them out means a control only acts
     *      once a merchant touches it.
     */
    private function register_style_controls() {
        $editable = $this->build_negated_conditions( $this->get_pro_map() );

        $gate = function ( array $map ) use ( $editable ) {
            return $this->all_of( $this->build_conditions( $map ), $editable );
        };

        // ── Section ───────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_section', [
            'label'      => esc_html__( 'Section', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'section_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'section_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb' => 'color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'section_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();

        // ── Header ────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_header', [
            'label'      => esc_html__( 'Header', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::HEADER_VARIANTS ),
        ] );

            $this->add_responsive_control( 'header_align', [
                'label'     => esc_html__( 'Alignment', 'woolentor' ),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [ 'title' => esc_html__( 'Left', 'woolentor' ),   'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => esc_html__( 'Center', 'woolentor' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => esc_html__( 'Right', 'woolentor' ),  'icon' => 'eicon-text-align-right' ],
                ],
                // The head is a flex row so the link can sit opposite the copy; text-align alone
                // would move the words and leave the block where it is.
                'selectors' => [
                    '{{WRAPPER}} .wl-pb-head-main' => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .wl-pb-head'      => 'align-items: flex-end;',
                ],
            ] );

            $this->add_responsive_control( 'header_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-pb-eyebrow',
            ] );

            $this->add_control( 'heading_color', [
                'label'     => esc_html__( 'Heading Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-heading' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'heading_typography',
                'selector' => '{{WRAPPER}} .wl-pb-heading',
            ] );

            $this->add_control( 'desc_color', [
                'label'     => esc_html__( 'Description Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .wl-pb-desc',
            ] );

            $this->add_control( 'link_color', [
                'label'      => esc_html__( 'Header Link Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-pb-link' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::HEADLINK_VARIANTS ),
            ] );

            $this->add_control( 'link_color_hover', [
                'label'      => esc_html__( 'Header Link Hover', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-link:hover' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::HEADLINK_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Bundle ────────────────────────────────────────────────────────────

        // The bundle's name is the most prominent thing a merchant writes after the section
        // heading, and until now the only way to restyle it was the pack stylesheet.
        $this->start_controls_section( 'style_bundle', [
            'label'      => esc_html__( 'Bundle', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'name_color', [
                'label'     => esc_html__( 'Name Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'name_typography',
                'selector' => '{{WRAPPER}} .wl-pb-name',
            ] );

            $this->add_control( 'intro_color', [
                'label'      => esc_html__( 'Description Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-intro' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::INTRO_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Switcher ──────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_switcher', [
            'label'      => esc_html__( 'Bundle Switcher', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::SINGLE_VARIANTS ),
        ] );

            $this->add_responsive_control( 'switcher_gap', [
                'label'      => esc_html__( 'Spacing', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-switch' => '--wl-pb-switch-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'switcher_thumb', [
                'label'      => esc_html__( 'Thumbnail Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 40, 'max' => 160 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-switch' => '--wl-pb-switch-thumb: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( $this->switcher_map( 'thumbs' ) ),
            ] );

            $this->add_control( 'switcher_color', [
                'label'      => esc_html__( 'Label Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-switch-btn' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( $this->switcher_map( 'thumbs', 'tabs' ) ),
            ] );

            $this->add_control( 'switcher_color_active', [
                'label'      => esc_html__( 'Active Label Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-switch-btn.is-active' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
                'conditions' => $this->build_conditions( $this->switcher_map( 'thumbs', 'tabs' ) ),
            ] );

            $this->add_control( 'switcher_arrow_color', [
                'label'      => esc_html__( 'Arrow Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-switch-arrow' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
                'conditions' => $this->build_conditions( $this->switcher_map( 'arrows', 'counter' ) ),
            ] );

            $this->add_control( 'switcher_counter_color', [
                'label'      => esc_html__( 'Counter Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-switch-counter' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( $this->switcher_map( 'arrows', 'counter' ) ),
            ] );

        $this->end_controls_section();

        // ── Box ───────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_box', [
            'label'      => esc_html__( 'Box', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::BOX_VARIANTS ),
        ] );

            $this->add_control( 'box_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-pb-card, {{WRAPPER}} .wl-pb-panel, {{WRAPPER}} .wl-pb-visual, {{WRAPPER}} .wl-pb-summary' => 'background: {{VALUE}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'box_border',
                'selector' => '{{WRAPPER}} .wl-pb-card, {{WRAPPER}} .wl-pb-panel, {{WRAPPER}} .wl-pb-visual, {{WRAPPER}} .wl-pb-summary',
            ] );

            $this->add_responsive_control( 'box_radius', [
                'label'      => esc_html__( 'Corner Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-card, {{WRAPPER}} .wl-pb-panel, {{WRAPPER}} .wl-pb-visual, {{WRAPPER}} .wl-pb-summary' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'box_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-card, {{WRAPPER}} .wl-pb-panel, {{WRAPPER}} .wl-pb-visual, {{WRAPPER}} .wl-pb-summary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'box_shadow',
                'selector' => '{{WRAPPER}} .wl-pb-card, {{WRAPPER}} .wl-pb-panel, {{WRAPPER}} .wl-pb-visual, {{WRAPPER}} .wl-pb-summary',
            ] );

            $this->add_responsive_control( 'box_gap', [
                'label'      => esc_html__( 'Gap Between Bundles', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-grid' => '--wl-pb-gap: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::GRID_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Image ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_image', [
            'label'      => esc_html__( 'Image', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::IMAGE_VARIANTS ),
        ] );

            $this->add_responsive_control( 'image_radius', [
                'label'      => esc_html__( 'Corner Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-image' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'image_border',
                'selector' => '{{WRAPPER}} .wl-pb-image',
            ] );

            // Its own control rather than inheriting Text Color: both packs that print a caption
            // set an explicit muted colour on it, so inheritance never reaches it.
            $this->add_control( 'caption_color', [
                'label'      => esc_html__( 'Caption Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-caption' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::CAPTION_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Labels ────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_labels', [
            'label'      => esc_html__( 'Labels', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'tag_color', [
                'label'      => esc_html__( 'Tag Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-tag' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::TAG_VARIANTS ),
            ] );

            $this->add_control( 'tag_bg', [
                'label'      => esc_html__( 'Tag Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-tag' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::TAG_VARIANTS ),
            ] );

            // Editorial V2 and Magazine V3 put the same label over the photograph instead, so
            // they get their own pair rather than controls that would style nothing.
            $this->add_control( 'flag_color', [
                'label'      => esc_html__( 'Flag Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-flag' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::FLAG_VARIANTS ),
            ] );

            $this->add_control( 'flag_bg', [
                'label'      => esc_html__( 'Flag Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-flag' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::FLAG_VARIANTS ),
            ] );

            $this->add_control( 'save_color', [
                'label'     => esc_html__( 'Saving Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-pb-save' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'save_bg', [
                'label'     => esc_html__( 'Saving Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-save' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'ribbon_color', [
                'label'      => esc_html__( 'Ribbon Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-pb-ribbon' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::RIBBON_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Thumbnail strip ───────────────────────────────────────────────────

        $this->start_controls_section( 'style_thumbstrip', [
            'label'      => esc_html__( 'Thumbnail Strip', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::THUMBSTRIP_VARIANTS ),
        ] );

            $this->add_responsive_control( 'thumbstrip_gap', [
                'label'      => esc_html__( 'Spacing', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-thumbstrip' => '--wl-pb-thumbstrip-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'thumbstrip_radius', [
                'label'      => esc_html__( 'Corner Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-thumb, {{WRAPPER}} .wl-pb-thumb img' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_control( 'plus_color', [
                'label'     => esc_html__( 'Plus Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-plus' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'plus_bg', [
                'label'     => esc_html__( 'Plus Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-plus' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'strip_name_color', [
                'label'      => esc_html__( 'Item Name Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-pb-thumb-name' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::LABELLED_STRIP_VARIANTS ),
            ] );

            $this->add_control( 'strip_price_color', [
                'label'      => esc_html__( 'Item Price Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-thumb-price' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::LABELLED_STRIP_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Items ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_items', [
            'label'      => esc_html__( 'Items', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::LIST_VARIANTS ),
        ] );

            $this->add_responsive_control( 'item_gap', [
                'label'      => esc_html__( 'Row Spacing', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-list' => '--wl-pb-row-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'item_bg', [
                'label'     => esc_html__( 'Row Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-item' => 'background: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'item_media_size', [
                'label'      => esc_html__( 'Thumbnail Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 24, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-list' => '--wl-pb-thumb-size: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::ITEM_MEDIA_VARIANTS ),
            ] );

            $this->add_responsive_control( 'item_media_radius', [
                'label'      => esc_html__( 'Thumbnail Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-item-media, {{WRAPPER}} .wl-pb-item-media img' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => $this->build_conditions( self::ITEM_MEDIA_VARIANTS ),
            ] );

            $this->add_control( 'item_name_color', [
                'label'     => esc_html__( 'Name Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-pb-item-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'item_name_typography',
                'selector' => '{{WRAPPER}} .wl-pb-item-name',
            ] );

            $this->add_control( 'item_meta_color', [
                'label'     => esc_html__( 'Meta Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-item-meta' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'item_price_color', [
                'label'      => esc_html__( 'Price Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-item-price' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::ITEM_PRICE_VARIANTS ),
            ] );

            $this->add_control( 'item_badge_color', [
                'label'      => esc_html__( 'Badge Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-item-badge' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
                'conditions' => $this->build_conditions( self::BADGE_VARIANTS ),
            ] );

            $this->add_control( 'item_num_color', [
                'label'      => esc_html__( 'Numeral Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-item-num' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::NUMBER_VARIANTS ),
            ] );

            // The tick is drawn on a fixed row and on a toggle, so its colours are offered
            // wherever either exists — the union, not one of the two.
            $this->add_control( 'check_color', [
                'label'      => esc_html__( 'Tick Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-pb-check' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( $this->merge_maps( self::CHECK_VARIANTS, self::SELECTABLE_VARIANTS ) ),
            ] );

            $this->add_control( 'check_bg', [
                'label'      => esc_html__( 'Tick Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-check' => 'background: {{VALUE}}; border-color: {{VALUE}};',
                ],
                'conditions' => $this->build_conditions( $this->merge_maps( self::CHECK_VARIANTS, self::SELECTABLE_VARIANTS ) ),
            ] );

            // Magazine V2 lists the set a second time beside the total. Those rows carry product
            // names and prices like any other, so they get a colour like any other.
            $this->add_control( 'line_color', [
                'label'      => esc_html__( 'Recap Row Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-line' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::LINES_VARIANTS ),
            ] );

            $this->add_control( 'item_off_opacity', [
                'label'      => esc_html__( 'Deselected Opacity', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [ 'px' => [ 'min' => 0.1, 'max' => 1, 'step' => 0.05 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-item.is-off' => 'opacity: {{SIZE}};' ],
                'conditions' => $this->build_conditions( self::SELECTABLE_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Selection bar ─────────────────────────────────────────────────────

        $this->start_controls_section( 'style_selection', [
            'label'      => esc_html__( 'Selection Bar', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::SELECTABLE_VARIANTS ),
        ] );

            $this->add_control( 'selectbar_color', [
                'label'     => esc_html__( 'Count Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-selectbar-count' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'selectbar_btn_color', [
                'label'     => esc_html__( 'Button Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-pb-selectbar-actions button' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ] );

            $this->add_control( 'selectbar_btn_hover', [
                'label'     => esc_html__( 'Button Hover', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-pb-selectbar-actions button:hover' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

        // ── Free-gift bar ─────────────────────────────────────────────────────

        $this->start_controls_section( 'style_gift', [
            'label'      => esc_html__( 'Free Gift Bar', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::GIFT_VARIANTS ),
        ] );

            $this->add_control( 'gift_msg_color', [
                'label'     => esc_html__( 'Message Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-gift-msg' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'gift_track_color', [
                'label'     => esc_html__( 'Track Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-gift-track' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'gift_fill_color', [
                'label'     => esc_html__( 'Fill Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-gift-fill' => 'background: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'gift_height', [
                'label'      => esc_html__( 'Bar Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 2, 'max' => 20 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-gift' => '--wl-pb-gift-height: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // ── Total ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_total', [
            'label'      => esc_html__( 'Total', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'total_label_color', [
                'label'      => esc_html__( 'Label Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-pb-total-label' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::TOTAL_LABEL_VARIANTS ),
            ] );

            $this->add_control( 'total_now_color', [
                'label'     => esc_html__( 'Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-now' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'total_now_typography',
                'selector' => '{{WRAPPER}} .wl-pb-now',
            ] );

            $this->add_control( 'total_was_color', [
                'label'     => esc_html__( 'Old Price Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pb-was' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // ── Button ────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_cta', [
            'label'      => esc_html__( 'Button', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'cta_typography',
                'selector' => '{{WRAPPER}} .wl-pb-cta',
            ] );

            $this->start_controls_tabs( 'cta_tabs' );

                $this->start_controls_tab( 'cta_tab_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                    $this->add_control( 'cta_color', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pb-cta' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pb-cta' => 'background: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'cta_border',
                        'selector' => '{{WRAPPER}} .wl-pb-cta',
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'cta_tab_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                    $this->add_control( 'cta_color_hover', [
                        'label'     => esc_html__( 'Text Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pb-cta:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_bg_hover', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pb-cta:hover' => 'background: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'cta_border_hover', [
                        'label'     => esc_html__( 'Border Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-pb-cta:hover' => 'border-color: {{VALUE}};' ],
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_responsive_control( 'cta_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'separator'  => 'before',
                'selectors'  => [
                    '{{WRAPPER}} .wl-pb-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'cta_radius', [
                'label'      => esc_html__( 'Corner Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 999 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pb-cta' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();
    }

    // ── Data ──────────────────────────────────────────────────────────────────

    /**
     * Normalise the `bundles` repeater. Always returns at least one bundle so the editor has
     * something to draw before anything is configured.
     *
     * @param  array $rows
     * @return array
     */
    private function build_bundles( array $rows ) {
        $bundles = [];

        foreach ( $rows as $index => $row ) {
            $bundles[] = [
                'index'      => $index + 1,
                'name'       => (string) ( $row['bundle_name'] ?? '' ),
                'tag'        => (string) ( $row['bundle_tag'] ?? '' ),
                'desc'       => (string) ( $row['bundle_desc'] ?? '' ),
                'ribbon'     => (string) ( $row['bundle_ribbon'] ?? '' ),
                'coupon'     => (string) ( $row['bundle_coupon'] ?? '' ),
                'gift'       => (float) ( $row['bundle_gift_amount'] ?? 0 ),
                'gift_label' => (string) ( $row['bundle_gift_label'] ?? '' ),
                'cta_text'   => (string) ( $row['bundle_cta_text'] ?? '' ),
                'cta_link'   => $row['bundle_cta_link'] ?? [],
                'id'         => absint( $row['bundle_image']['id'] ?? 0 ),
                'url'        => (string) ( $row['bundle_image']['url'] ?? '' ),
            ];
        }

        if ( empty( $bundles ) ) {
            $bundles[] = [
                'index' => 1, 'name' => '', 'tag' => '', 'desc' => '', 'ribbon' => '',
                'coupon' => '', 'gift' => 0.0, 'gift_label' => '', 'cta_text' => '',
                'cta_link' => [], 'id' => 0, 'url' => Utils::get_placeholder_image_src(),
            ];
        }

        return $bundles;
    }

    /**
     * Normalise the `items` repeater into items grouped by bundle index.
     *
     * A row whose product no longer exists or is not published is dropped here rather than in the
     * template: a deleted product must never print an empty row on a live shop. The editor is told
     * instead, in render().
     *
     * @param  array $rows
     * @return array  [ bundle index => [ item, … ] ]
     */
    private function build_items( array $rows ) {
        $items = [];

        foreach ( $rows as $row ) {
            $id = absint( $row['product'] ?? 0 );

            if ( ! $id ) {
                continue;
            }

            $product = wc_get_product( $id );

            if ( ! woolentor_bundle_item_is_addable( $product, $id ) ) {
                continue;
            }

            $bundle = max( 1, absint( $row['bundle'] ?? 1 ) );

            $items[ $bundle ][] = [
                'product'  => $product,
                'id'       => $id,
                'meta'     => (string) ( $row['item_meta'] ?? '' ),
                'qty'      => max( 1, absint( $row['item_qty'] ?? 1 ) ),
                'required' => 'yes' === ( $row['item_required'] ?? 'yes' ),
            ];
        }

        return $items;
    }

    /**
     * How many repeater rows were dropped for pointing at a product that is gone.
     *
     * @param  array $rows
     * @param  array $items
     * @return int
     */
    private function count_dropped( array $rows, array $items ) {
        $kept = 0;
        foreach ( $items as $list ) {
            $kept += count( $list );
        }
        return max( 0, count( $rows ) - $kept );
    }

    /**
     * The one place money is computed.
     *
     * `was` is the sum of regular prices and `now` the sum of what WooCommerce would actually
     * charge today, so the difference is a saving the shopper really gets — nothing here is typed
     * by a merchant. When a product has no regular price set, its current price stands in for both,
     * which makes it contribute zero saving rather than a fictional one.
     *
     * @param  array $items  Rows from build_items().
     * @return array
     */
    private function bundle_totals( array $items ) {
        $was = 0.0;
        $now = 0.0;

        foreach ( $items as $item ) {
            $product = $item['product'];
            $qty     = $item['qty'];

            $price   = (float) $product->get_price();
            $regular = (float) $product->get_regular_price();

            if ( $regular <= 0 ) {
                $regular = $price;
            }

            $now += $price * $qty;
            $was += $regular * $qty;
        }

        $saved   = max( 0.0, $was - $now );
        $percent = $was > 0 ? (int) round( $saved / $was * 100 ) : 0;

        return [
            'regular'  => $was,
            'now'      => $now,
            'saved'    => $saved,
            'percent'  => $percent,
            'count'    => count( $items ),
            'has_sale' => $saved > 0.009,
        ];
    }

    /**
     * wc_price() returns markup with HTML entities for the currency symbol. A caller that escapes
     * the result again prints `&euro;` literally, which is how the total in Shop the Look ended up
     * with a visible entity. Decode once, here, and let the caller escape plain text.
     *
     * @param  float $amount
     * @return string
     */
    protected function money( $amount ) {
        return html_entity_decode( wp_strip_all_tags( wc_price( (float) $amount ) ), ENT_QUOTES, 'UTF-8' );
    }

    /**
     * How the browser should format a number as money.
     *
     * The script has to re-total a bundle as items are toggled, and it must produce the same string
     * the server would. Handing it WooCommerce's own separators and pattern is the only way to get
     * that right in every locale — guessing `$` and two decimals breaks the moment a shop sells in
     * euros with a comma.
     *
     * @return string  JSON, for a data attribute.
     */
    protected function money_format() {
        return wp_json_encode( [
            'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
            'decimals' => wc_get_price_decimals(),
            'decimal'  => wc_get_price_decimal_separator(),
            'thousand' => wc_get_price_thousand_separator(),
            'format'   => get_woocommerce_price_format(),
        ] );
    }

    /**
     * The small inline icon set. Inline because the reference packs use icon fonts this plugin
     * does not ship, and because an SVG inherits `currentColor` so every pack skins it for free.
     *
     * @param  string $name
     * @return string
     */
    protected function icon( $name ) {
        $paths = [
            'check' => '<polyline points="20 6 9 17 4 12"></polyline>',
            'plus'  => '<line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>',
            'right' => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',
            'left'  => '<line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>',
            'cart'  => '<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>'
                     . '<path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"></path>',
            'gift'  => '<polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect>'
                     . '<line x1="12" y1="22" x2="12" y2="7"></line>'
                     . '<path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>'
                     . '<path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>',
        ];

        if ( ! isset( $paths[ $name ] ) ) {
            return '';
        }

        return '<svg class="wl-pb-icon wl-pb-icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
    }

    // ── Markup helpers ────────────────────────────────────────────────────────

    /**
     * The section header. Templates that are not in HEADER_VARIANTS never call it, so it does not
     * test the map itself — the controls are already hidden there.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function header_html( array $settings, $style = '', $variant = '' ) {
        $eyebrow = trim( (string) ( $settings['eyebrow'] ?? '' ) );
        $heading = trim( (string) ( $settings['heading'] ?? '' ) );
        $text    = trim( (string) ( $settings['description'] ?? '' ) );

        $link = '';
        if ( $style && $this->in_map( self::HEADLINK_VARIANTS, $style, $variant ) ) {
            $link = $this->headlink_html( $settings );
        }

        if ( '' === $eyebrow && '' === $heading && '' === $text && '' === $link ) {
            return '';
        }

        $tag = in_array( $settings['heading_tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ], true )
            ? $settings['heading_tag']
            : 'h2';

        // <em> and <strong> survive because several references set one word of the heading in an
        // accent face. Everything else is stripped.
        $allowed = [ 'em' => [], 'strong' => [], 'br' => [] ];

        return '<div class="wl-pb-head">'
            . '<div class="wl-pb-head-main">'
            . ( '' !== $eyebrow ? '<span class="wl-pb-eyebrow">' . esc_html( $eyebrow ) . '</span>' : '' )
            . ( '' !== $heading ? '<' . $tag . ' class="wl-pb-heading">' . wp_kses( $heading, $allowed ) . '</' . $tag . '>' : '' )
            . ( '' !== $text ? '<p class="wl-pb-desc">' . esc_html( $text ) . '</p>' : '' )
            . '</div>'
            . $link
            . '</div>';
    }

    /**
     * The header's outbound link. Falls back to the shop page so the link is useful the moment the
     * widget is dropped on a page, and disappears entirely when its text is cleared.
     *
     * @param  array $settings
     * @return string
     */
    protected function headlink_html( array $settings ) {
        $text = trim( (string) ( $settings['link_text'] ?? '' ) );

        if ( '' === $text ) {
            return '';
        }

        $url = trim( (string) ( $settings['link_url']['url'] ?? '' ) );

        if ( '' === $url && function_exists( 'wc_get_page_permalink' ) ) {
            $url = (string) wc_get_page_permalink( 'shop' );
        }

        if ( '' === $url ) {
            return '';
        }

        $rel    = [];
        $target = '';

        if ( ! empty( $settings['link_url']['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }

        if ( ! empty( $settings['link_url']['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<a class="wl-pb-link" href="' . esc_url( $url ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>'
            . esc_html( $text ) . $this->icon( 'right' ) . '</a>';
    }

    /**
     * The bundle switcher, in one of four shapes.
     *
     * `role="group"` with `aria-pressed`, deliberately — not `role="tab"`. A tab role requires a
     * matching `role="tabpanel"` reached through `aria-controls`, and an incomplete tab pattern is
     * worse for a screen reader than no pattern at all.
     *
     * @param  array  $bundles
     * @param  string $mode  thumbs | tabs | arrows | counter
     * @return string
     */
    protected function switcher_html( array $bundles, $mode = 'tabs' ) {
        if ( count( $bundles ) < 2 ) {
            return '';
        }

        $label = esc_attr__( 'Choose a bundle', 'woolentor' );

        if ( 'arrows' === $mode || 'counter' === $mode ) {
            $out = '<div class="wl-pb-switch wl-pb-switch--' . esc_attr( $mode ) . '" role="group"'
                . ' aria-label="' . $label . '" data-wl-pb-switch="' . esc_attr( $mode ) . '">';

            if ( 'arrows' === $mode ) {
                $out .= '<button type="button" class="wl-pb-switch-arrow" data-wl-pb-prev'
                    . ' aria-label="' . esc_attr__( 'Previous bundle', 'woolentor' ) . '">'
                    . $this->icon( 'left' ) . '</button>';
            }

            $out .= '<span class="wl-pb-switch-counter" data-wl-pb-counter aria-live="polite">'
                . '<span class="wl-pb-switch-now">' . esc_html( str_pad( '1', 2, '0', STR_PAD_LEFT ) ) . '</span>'
                . '<span class="wl-pb-switch-sep" aria-hidden="true"> / </span>'
                . '<span class="wl-pb-switch-total">' . esc_html( str_pad( (string) count( $bundles ), 2, '0', STR_PAD_LEFT ) ) . '</span>'
                . '</span>';

            $out .= '<button type="button" class="wl-pb-switch-arrow" data-wl-pb-next'
                . ' aria-label="' . esc_attr__( 'Next bundle', 'woolentor' ) . '">'
                . $this->icon( 'right' ) . '</button>';

            return $out . '</div>';
        }

        $out = '<div class="wl-pb-switch wl-pb-switch--' . esc_attr( $mode ) . '" role="group"'
            . ' aria-label="' . $label . '" data-wl-pb-switch="' . esc_attr( $mode ) . '">';

        foreach ( $bundles as $position => $bundle ) {
            $active = 0 === $position;
            $name   = '' !== $bundle['name']
                ? wp_strip_all_tags( $bundle['name'] )
                /* translators: %s: bundle number */
                : sprintf( esc_html__( 'Bundle %s', 'woolentor' ), str_pad( (string) $bundle['index'], 2, '0', STR_PAD_LEFT ) );

            $out .= '<button type="button" class="wl-pb-switch-btn' . ( $active ? ' is-active' : '' ) . '"'
                . ' data-wl-pb-go="' . esc_attr( $bundle['index'] ) . '"'
                . ' aria-pressed="' . ( $active ? 'true' : 'false' ) . '">';

            if ( 'thumbs' === $mode && ( $bundle['id'] || $bundle['url'] ) ) {
                $out .= $bundle['id']
                    ? wp_get_attachment_image( $bundle['id'], 'thumbnail', false, [ 'class' => 'wl-pb-switch-thumb', 'alt' => '' ] )
                    : '<img class="wl-pb-switch-thumb" src="' . esc_url( $bundle['url'] ) . '" alt="" loading="lazy">';
            }

            $out .= '<span class="wl-pb-switch-label">' . esc_html( $name ) . '</span></button>';
        }

        return $out . '</div>';
    }

    /**
     * The "thumb + thumb + thumb" strip.
     *
     * In **labelled** mode each thumbnail also carries the item's name, its price and the data
     * attributes the script totals from. That is for Luxury V3, where the strip *is* the item list
     * — there is no `.wl-pb-list` beside it. Everywhere else the strip is decoration above a real
     * list, and it must stay silent: two elements carrying the same price would make the script
     * count that product twice and double the bundle.
     *
     * @param  array $items
     * @param  array $settings
     * @param  bool  $labelled
     * @return string
     */
    protected function thumbstrip_html( array $items, array $settings, $labelled = false ) {
        if ( empty( $items ) || 'yes' !== ( $settings['show_thumbstrip'] ?? 'yes' ) ) {
            return '';
        }

        $out   = '<div class="wl-pb-thumbstrip' . ( $labelled ? ' is-labelled' : '' ) . '">';
        $first = true;

        foreach ( $items as $item ) {
            if ( ! $first ) {
                $out .= '<span class="wl-pb-plus" aria-hidden="true">' . $this->icon( 'plus' ) . '</span>';
            }
            $first = false;

            $data = '';

            if ( $labelled ) {
                $product = $item['product'];
                $price   = (float) $product->get_price();
                $regular = (float) $product->get_regular_price();

                if ( $regular <= 0 ) {
                    $regular = $price;
                }

                $data = ' data-wl-pb-item'
                    . ' data-wl-pb-id="' . esc_attr( $item['id'] ) . '"'
                    . ' data-wl-pb-qty="' . esc_attr( $item['qty'] ) . '"'
                    . ' data-wl-pb-price="' . esc_attr( $price * $item['qty'] ) . '"'
                    . ' data-wl-pb-regular="' . esc_attr( $regular * $item['qty'] ) . '"';
            }

            $out .= '<div class="wl-pb-thumb"' . $data . '>'
                . $item['product']->get_image( 'woocommerce_thumbnail', [ 'alt' => esc_attr( $item['product']->get_name() ) ] );

            if ( $labelled ) {
                $out .= '<span class="wl-pb-thumb-name">' . esc_html( $item['product']->get_name() ) . '</span>'
                    . '<span class="wl-pb-thumb-price">' . esc_html( $this->money( (float) $item['product']->get_price() ) ) . '</span>';
            }

            $out .= '</div>';
        }

        return $out . '</div>';
    }

    /**
     * The printed caption the two quiet V3s share — "04 / Bundle & Save".
     *
     * Built from the bundle's own number and the section eyebrow rather than a control of its own,
     * so the three V3s read as one family without adding a field nine other layouts would ignore.
     *
     * Editorial V3 has no section header at all — it opens straight onto its subject — so there is
     * no eyebrow to borrow there and the bundle's own tag stands in. Without either, there is
     * nothing true to print and the caption does not appear.
     *
     * @param  array $bundle
     * @param  array $settings
     * @return string
     */
    protected function caption_html( array $bundle, array $settings ) {
        $eyebrow = trim( (string) ( $settings['eyebrow'] ?? '' ) );

        if ( '' === $eyebrow ) {
            $eyebrow = trim( (string) $bundle['tag'] );
        }

        if ( '' === $eyebrow ) {
            return '';
        }

        return '<span class="wl-pb-caption">' . esc_html( sprintf(
            /* translators: 1: bundle number, 2: section eyebrow */
            __( '%1$s / %2$s', 'woolentor' ),
            str_pad( (string) $bundle['index'], 2, '0', STR_PAD_LEFT ),
            $eyebrow
        ) ) . '</span>';
    }

    /**
     * One item row.
     *
     * On a selectable variant the tick becomes a real `<button role="checkbox">` so the keyboard
     * reaches it; the surrounding row is a mouse convenience the script layers on top, never the
     * only way in. An always-included item keeps the static tick and gets `is-locked`.
     *
     * @param  array  $item
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  int    $number    1-based position, for the variants that number their rows.
     * @return string
     */
    protected function item_html( array $item, array $settings, $style, $variant, $number = 0 ) {
        $product = $item['product'];

        $price   = (float) $product->get_price();
        $regular = (float) $product->get_regular_price();
        if ( $regular <= 0 ) {
            $regular = $price;
        }

        $selectable = $this->in_map( self::SELECTABLE_VARIANTS, $style, $variant );
        $toggleable = $selectable && ! $item['required'];

        $classes = [ 'wl-pb-item' ];
        if ( $selectable ) {
            $classes[] = $toggleable ? 'is-toggle' : 'is-locked';
        }

        $out = '<li class="' . esc_attr( implode( ' ', $classes ) ) . '"'
            . ' data-wl-pb-item'
            . ' data-wl-pb-id="' . esc_attr( $item['id'] ) . '"'
            . ' data-wl-pb-qty="' . esc_attr( $item['qty'] ) . '"'
            . ' data-wl-pb-price="' . esc_attr( $price * $item['qty'] ) . '"'
            . ' data-wl-pb-regular="' . esc_attr( $regular * $item['qty'] ) . '">';

        // Leading mark: a toggle, a static tick, or a numeral — one of the three, never two.
        if ( $toggleable ) {
            $out .= '<button type="button" class="wl-pb-check" role="checkbox" aria-checked="true"'
                . ' data-wl-pb-toggle aria-label="' . esc_attr( $product->get_name() ) . '">'
                . $this->icon( 'check' ) . '</button>';
        } elseif ( $selectable || $this->in_map( self::CHECK_VARIANTS, $style, $variant ) ) {
            $out .= '<span class="wl-pb-check" aria-hidden="true">' . $this->icon( 'check' ) . '</span>';
        } elseif ( $number && $this->in_map( self::NUMBER_VARIANTS, $style, $variant ) ) {
            $out .= '<span class="wl-pb-item-num" aria-hidden="true">'
                . esc_html( str_pad( (string) $number, 2, '0', STR_PAD_LEFT ) ) . '</span>';
        }

        if ( $this->in_map( self::ITEM_MEDIA_VARIANTS, $style, $variant ) ) {
            $out .= '<span class="wl-pb-item-media">'
                . $product->get_image( 'woocommerce_gallery_thumbnail', [ 'alt' => esc_attr( $product->get_name() ) ] )
                . '</span>';
        }

        $meta = trim( (string) $item['meta'] );
        if ( '' === $meta ) {
            $meta = $this->first_category( $product );
        }

        $out .= '<span class="wl-pb-item-info">'
            . '<span class="wl-pb-item-name">' . esc_html( $product->get_name() ) . '</span>';

        if ( '' !== $meta && 'yes' === ( $settings['show_item_meta'] ?? 'yes' ) ) {
            $out .= '<span class="wl-pb-item-meta">' . esc_html( $meta ) . '</span>';
        }

        $out .= '</span>';

        $badge = trim( (string) ( $settings['badge_text'] ?? '' ) );

        if ( $this->in_map( self::ITEM_PRICE_VARIANTS, $style, $variant )
            && 'yes' === ( $settings['show_item_price'] ?? 'yes' ) ) {

            $out .= '<span class="wl-pb-item-price">' . esc_html( $this->money( $price ) );

            if ( $regular > $price ) {
                $out .= ' <s class="wl-pb-item-was">' . esc_html( $this->money( $regular ) ) . '</s>';
            }

            $out .= '</span>';
        } elseif ( '' !== $badge && $this->in_map( self::BADGE_VARIANTS, $style, $variant ) ) {
            $out .= '<span class="wl-pb-item-badge">' . esc_html( $badge ) . '</span>';
        }

        return $out . '</li>';
    }

    /**
     * The whole item list.
     *
     * @param  array  $items
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function list_html( array $items, array $settings, $style, $variant ) {
        if ( empty( $items ) ) {
            return '';
        }

        $out = '<ul class="wl-pb-list">';

        foreach ( array_values( $items ) as $i => $item ) {
            $out .= $this->item_html( $item, $settings, $style, $variant, $i + 1 );
        }

        return $out . '</ul>';
    }

    /**
     * A recap line per item — Magazine V2's `#bsLines`, beside the total rather than in the
     * picker.
     *
     * These deliberately do **not** carry `data-wl-pb-item`: that attribute is what the script
     * sums, and an item counted twice would double the bundle's price. They carry
     * `data-wl-pb-line` instead, keyed by product id, and the script only shows and hides them as
     * the matching tile is toggled.
     *
     * @param  array $items
     * @return string
     */
    protected function lines_html( array $items ) {
        if ( empty( $items ) ) {
            return '';
        }

        $out = '<ul class="wl-pb-lines">';

        foreach ( $items as $item ) {
            $out .= '<li class="wl-pb-line" data-wl-pb-line="' . esc_attr( $item['id'] ) . '">'
                . '<span class="wl-pb-line-name">' . esc_html( $item['product']->get_name() ) . '</span>'
                . '<span class="wl-pb-line-price">'
                . esc_html( $this->money( (float) $item['product']->get_price() * $item['qty'] ) )
                . '</span></li>';
        }

        return $out . '</ul>';
    }

    /**
     * A product's first category, used when the merchant leaves the meta line empty.
     *
     * @param  \WC_Product $product
     * @return string
     */
    protected function first_category( $product ) {
        $terms = get_the_terms( $product->get_id(), 'product_cat' );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        return (string) $terms[0]->name;
    }

    /**
     * The "3 of 3 selected · All · None" bar.
     *
     * @param  array $items
     * @param  array $settings
     * @return string
     */
    protected function selectbar_html( array $items, array $settings ) {
        if ( empty( $items ) ) {
            return '';
        }

        $total = count( $items );
        $text  = trim( (string) ( $settings['count_text'] ?? '' ) );

        if ( '' === $text ) {
            $text = esc_html__( '{selected} of {total} selected', 'woolentor' );
        }

        $rendered = str_replace(
            [ '{selected}', '{total}' ],
            [ '<strong data-wl-pb-selected>' . esc_html( number_format_i18n( $total ) ) . '</strong>', esc_html( number_format_i18n( $total ) ) ],
            esc_html( $text )
        );

        $all  = trim( (string) ( $settings['select_all_text'] ?? '' ) );
        $none = trim( (string) ( $settings['select_none_text'] ?? '' ) );

        $out = '<div class="wl-pb-selectbar">'
            . '<span class="wl-pb-selectbar-count" aria-live="polite">' . $rendered . '</span>';

        if ( '' !== $all || '' !== $none ) {
            $out .= '<span class="wl-pb-selectbar-actions">'
                . ( '' !== $all ? '<button type="button" data-wl-pb-all>' . esc_html( $all ) . '</button>' : '' )
                . ( '' !== $none ? '<button type="button" data-wl-pb-none>' . esc_html( $none ) . '</button>' : '' )
                . '</span>';
        }

        return $out . '</div>';
    }

    /**
     * The progress bar toward a free gift.
     *
     * It reports progress toward a promotion the merchant has configured elsewhere and does not
     * add anything to the cart itself. Claiming to give a gift the cart never receives would be
     * worse than not showing the bar.
     *
     * @param  array $bundle
     * @param  array $totals
     * @return string
     */
    protected function gift_html( array $bundle, array $totals ) {
        $threshold = (float) $bundle['gift'];

        if ( $threshold <= 0 ) {
            return '';
        }

        $label     = trim( (string) $bundle['gift_label'] );
        $label     = '' !== $label ? $label : esc_html__( 'a free gift', 'woolentor' );
        $remaining = max( 0.0, $threshold - $totals['now'] );
        $percent   = min( 100, $threshold > 0 ? ( $totals['now'] / $threshold ) * 100 : 0 );

        $message = $remaining > 0
            ? sprintf(
                /* translators: 1: amount still needed, 2: what the shopper unlocks */
                esc_html__( 'Add %1$s more for %2$s', 'woolentor' ),
                '<b data-wl-pb-gift-left>' . esc_html( $this->money( $remaining ) ) . '</b>',
                esc_html( $label )
            )
            : sprintf(
                /* translators: %s: what the shopper unlocked */
                esc_html__( 'Unlocked — %s', 'woolentor' ),
                esc_html( $label )
            );

        return '<div class="wl-pb-gift" data-wl-pb-gift="' . esc_attr( $threshold ) . '"'
            . ' data-wl-pb-gift-label="' . esc_attr( $label ) . '">'
            . '<p class="wl-pb-gift-msg" data-wl-pb-gift-msg>' . $this->icon( 'gift' ) . ' ' . $message . '</p>'
            . '<span class="wl-pb-gift-track"><span class="wl-pb-gift-fill" data-wl-pb-gift-fill'
            . ' style="width:' . esc_attr( round( $percent, 2 ) ) . '%;"></span></span>'
            . '</div>';
    }

    /**
     * The saving label. Returns nothing when nothing is on sale — "Save 0" is noise, and printing
     * a saving the shopper does not get is worse than noise.
     *
     * @param  array $totals
     * @param  array $settings
     * @param  bool  $with_label  Luxury writes its own wording beside the figure, so it asks for
     *                            the figure alone rather than getting "Save" twice.
     * @return string
     */
    protected function saving_html( array $totals, array $settings, $with_label = true ) {
        if ( 'yes' !== ( $settings['show_saving'] ?? 'yes' ) || ! $totals['has_sale'] ) {
            return '';
        }

        $label  = $with_label ? trim( (string) ( $settings['saving_label'] ?? '' ) ) : '';
        $format = (string) ( $settings['saving_format'] ?? 'amount' );

        $amount  = $this->money( $totals['saved'] );
        $percent = $totals['percent'] . '%';

        if ( 'percent' === $format ) {
            $value = $percent;
        } elseif ( 'both' === $format ) {
            /* translators: 1: amount saved, 2: percent saved */
            $value = sprintf( esc_html__( '%1$s — %2$s off', 'woolentor' ), $amount, $percent );
        } else {
            $value = $amount;
        }

        // The mode travels with the figure so the script can rebuild it in the merchant's chosen
        // shape when a V2 toggle changes the total, instead of guessing at "amount".
        return '<span class="wl-pb-save" data-wl-pb-save>'
            . ( '' !== $label ? '<span class="wl-pb-save-label">' . esc_html( $label ) . '</span> ' : '' )
            . '<span class="wl-pb-save-value" data-wl-pb-save-mode="' . esc_attr( $format ) . '">'
            . esc_html( $value ) . '</span>'
            . '</span>';
    }

    /**
     * The pricing block: the label, the current total and, where something is on sale, the
     * original struck through.
     *
     * @param  array $totals
     * @param  array $settings
     * @param  bool  $with_label
     * @return string
     */
    protected function total_html( array $totals, array $settings, $with_label = true ) {
        $label = trim( (string) ( $settings['total_label'] ?? '' ) );

        $out = '<div class="wl-pb-total">';

        if ( $with_label && '' !== $label ) {
            $out .= '<span class="wl-pb-total-label">' . esc_html( $label ) . '</span>';
        }

        $out .= '<span class="wl-pb-now" data-wl-pb-now>' . esc_html( $this->money( $totals['now'] ) ) . '</span>';

        if ( $totals['has_sale'] ) {
            $out .= '<span class="wl-pb-was" data-wl-pb-was>' . esc_html( $this->money( $totals['regular'] ) ) . '</span>';
        }

        return $out . '</div>';
    }

    /**
     * A bundle's ribbon — "Best Value", "Most Popular".
     *
     * @param  array  $bundle
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function ribbon_html( array $bundle, array $settings, $style, $variant ) {
        $text = trim( (string) $bundle['ribbon'] );

        if ( '' === $text
            || 'yes' !== ( $settings['show_ribbon'] ?? 'yes' )
            || ! $this->in_map( self::RIBBON_VARIANTS, $style, $variant ) ) {
            return '';
        }

        return '<span class="wl-pb-ribbon">' . esc_html( $text ) . '</span>';
    }

    /**
     * The add button.
     *
     * With a link on the bundle it is an anchor that navigates; without one it is a button that
     * adds the whole set to the cart through the endpoint. Nothing here carries WooCommerce's own
     * `ajax_add_to_cart` class — this button adds several products at once, which that handler
     * cannot do.
     *
     * @param  array  $bundle
     * @param  array  $items
     * @param  array  $settings
     * @return string
     */
    protected function cta_html( array $bundle, array $items, array $settings ) {
        $text = trim( (string) $bundle['cta_text'] );

        if ( '' === $text ) {
            $text = trim( (string) ( $settings['cta_text'] ?? '' ) );
        }

        if ( '' === $text ) {
            return '';
        }

        $url = trim( (string) ( $bundle['cta_link']['url'] ?? '' ) );

        if ( '' !== $url ) {
            $rel    = [];
            $target = '';

            if ( ! empty( $bundle['cta_link']['is_external'] ) ) {
                $target = ' target="_blank"';
                $rel[]  = 'noopener';
                $rel[]  = 'noreferrer';
            }

            if ( ! empty( $bundle['cta_link']['nofollow'] ) ) {
                $rel[] = 'nofollow';
            }

            return '<a class="wl-pb-cta" href="' . esc_url( $url ) . '"' . $target
                . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>'
                . esc_html( $text ) . $this->icon( 'right' ) . '</a>';
        }

        if ( empty( $items ) ) {
            return '';
        }

        return '<button type="button" class="wl-pb-cta" data-wl-pb-add'
            . ' data-wl-pb-nonce="' . esc_attr( wp_create_nonce( 'woolentor_psa_nonce' ) ) . '"'
            . ' data-wl-pb-label="' . esc_attr( $text ) . '"'
            . ' data-wl-pb-added="' . esc_attr( trim( (string) ( $settings['added_text'] ?? '' ) ) ) . '"'
            . ' data-wl-pb-empty="' . esc_attr( trim( (string) ( $settings['empty_text'] ?? '' ) ) ) . '"'
            . ( '' !== $bundle['coupon'] ? ' data-wl-pb-coupon="' . esc_attr( $bundle['coupon'] ) . '"' : '' )
            . '><span class="wl-pb-cta-text">' . esc_html( $text ) . '</span>' . $this->icon( 'cart' ) . '</button>';
    }

    /**
     * The attributes every bundle box needs for the script to re-total it.
     *
     * @param  array $bundle
     * @return string
     */
    protected function bundle_attrs( array $bundle ) {
        return ' data-wl-pb-bundle="' . esc_attr( $bundle['index'] ) . '"'
            . ( '' !== $bundle['coupon'] ? ' data-wl-pb-coupon="' . esc_attr( $bundle['coupon'] ) . '"' : '' );
    }

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
     * Render the real template with the merchant's own content so a Pro variant can be seen in the
     * editor before upgrading. The frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Product Bundle' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Product Bundle' );
            return;
        }

        $bundles = $this->build_bundles( $settings['bundles'] ?? [] );
        $items   = $this->build_items( $settings['items'] ?? [] );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-pb wl-pb-' . esc_attr( $style ) . ' wl-pb-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '"'
            . ' data-wl-pb data-wl-pb-money="' . esc_attr( $this->money_format() ) . '">';
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
            echo '<p>' . esc_html__( 'Product Bundle requires WooCommerce.', 'woolentor' ) . '</p>';
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
            echo '<p>' . esc_html__( 'Product Bundle template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $rows    = $settings['items'] ?? [];
        $bundles = $this->build_bundles( $settings['bundles'] ?? [] );
        $items   = $this->build_items( $rows );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-pb wl-pb-' . esc_attr( $style ) . ' wl-pb-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '"'
            . ' data-wl-pb data-wl-pb-money="' . esc_attr( $this->money_format() ) . '">';
        include $template;
        echo '</div>';

        // Editor-only: a row pointing at a deleted or unpublished product is dropped silently on
        // the frontend, so this is the only place the page builder would ever hear about it.
        $dropped = $this->count_dropped( $rows, $items );

        if ( $dropped && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<p class="elementor-alert elementor-alert-warning">'
                . esc_html( sprintf(
                    /* translators: %d: number of items */
                    _n(
                        '%d item was skipped — its product is missing, unpublished, out of stock, or needs a variation chosen.',
                        '%d items were skipped — their products are missing, unpublished, out of stock, or need a variation chosen.',
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
