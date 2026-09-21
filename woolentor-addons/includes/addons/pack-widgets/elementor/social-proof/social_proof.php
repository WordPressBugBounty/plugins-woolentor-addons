<?php
/**
 * Social Proof Widget.
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Social_Proof_Widget extends Widget_Base {

    /**
     * Variants whose template prints a section header.
     *
     * Editorial V1 is absent on purpose: its reference opens straight onto the quote, and a
     * heading above it would be a control that never renders.
     */
    const HEADER_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** Variants that print the aggregate rating line (score, stars, review count). */
    const SUMMARY_VARIANTS = [
        'modern'    => [ 'v1' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** The five-row star histogram. Magazine V1's reference is the only one that draws it. */
    const HISTOGRAM_VARIANTS = [
        'magazine' => [ 'v1' ],
    ];

    /** The three trust figures — "4.9 Avg. Rating / 12K+ Reviews / 98% Recommend". */
    const STATS_VARIANTS = [
        'luxury' => [ 'v1' ],
    ];

    /** The press wordmark strip. */
    const PRESS_VARIANTS = [
        'editorial' => [ 'v2' ],
    ];

    /** The V1 role: every review at once, no navigation. */
    const WALL_VARIANTS = [
        'modern' => [ 'v1' ], 'editorial' => [ 'v3' ], 'luxury' => [ 'v1' ], 'magazine' => [ 'v1' ],
    ];

    /** The V2 role: a track the shopper pages through. */
    const CAROUSEL_VARIANTS = [
        'modern' => [ 'v3' ], 'editorial' => [ 'v2' ], 'luxury' => [ 'v2' ], 'magazine' => [ 'v2' ],
    ];

    /** The V3 role: one review at a time, with a picker. */
    const SPOTLIGHT_VARIANTS = [
        'modern' => [ 'v2' ], 'editorial' => [ 'v1' ], 'luxury' => [ 'v3' ],
        'magazine' => [ 'v2', 'v3' ],
    ];

    /**
     * How each spotlight lets the shopper choose: thumbs | filmstrip | arrows | tabs.
     *
     * `arrows` draws **two** arrows and a counter. There is no one-arrow mode here on purpose —
     * Product Bundle shipped one, it disabled its only control at the last item, and the shopper
     * could go forward once and then nowhere.
     */
    const PICKER_MODE = [
        'modern'    => [ 'v2' => 'thumbs' ],
        'editorial' => [ 'v1' => 'filmstrip' ],
        'luxury'    => [ 'v3' => 'arrows' ],
        'magazine'  => [ 'v3' => 'tabs' ],
    ];

    /** Magazine V2's progress bar under the track. */
    const PROGRESS_VARIANTS = [
        'magazine' => [ 'v2' ],
    ];

    /** Variants with one oversized card among the rest. */
    const FEATURED_VARIANTS = [
        'luxury' => [ 'v1', 'v2' ],
    ];

    /**
     * Variants that print the reviewer's avatar.
     *
     * Editorial V1 and V3 and Magazine V3 are absent: all three are typographic by design — rules
     * and set type, no portraits — and dropping a face into them would read as another pack.
     */
    const AVATAR_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /** Variants that name the product the review is about. */
    const PRODUCT_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v3' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Of those, the ones that also show the product's picture. */
    /** Variants whose product chip also prints the price, as Editorial V1's reference does. */
    const PRODUCT_PRICE_VARIANTS = [
        'editorial' => [ 'v1' ],
    ];

    const PRODUCT_MEDIA_VARIANTS = [
        'editorial' => [ 'v1' ],
        'luxury'    => [ 'v3' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Variants that print the verified chip. */
    const VERIFIED_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v1', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** Variants that print the review's date. */
    const DATE_VARIANTS = [
        'modern'    => [ 'v2' ],
        'editorial' => [ 'v2' ],
    ];

    /**
     * Variants that print the reviewer's role or location line.
     *
     * Magazine V1 is the only one out: its reference's card foot is an avatar and a name and
     * nothing else. The rest all carry a second line under the name.
     */
    const ROLE_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    /**
     * Variants that open the review with a large quotation glyph.
     *
     * Gated rather than always emitted and hidden by CSS: three packs would otherwise carry a
     * `display: none` for an element their design never wanted, which is a rule that exists only
     * to undo the markup.
     */
    const MARK_VARIANTS = [
        'modern'    => [ 'v2', 'v3' ],
        'editorial' => [ 'v1' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Variants whose review sits in a box — a padded, bordered or filled card.
     *
     * Editorial V1 and V3 are absent: both are set type parted by rules, with no box for a
     * background or a corner radius to act on.
     */
    const CARD_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /**
     * Variants driven by Slick, through the shared `WLPackSlider`.
     *
     * Every carousel and every spotlight: a spotlight is the same slider with one slide showing
     * and a picker wired to `slickGoTo`, not a second mechanism. The only moving layout that is
     * not a slider is Modern V1's wall, which scrolls in CSS.
     */
    const SLIDER_VARIANTS = [
        'modern'    => [ 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2' ],
        'luxury'    => [ 'v2', 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    /** The CSS auto-scrolling wall. Modern V1's reference is the only one. */
    const MARQUEE_VARIANTS = [
        'modern' => [ 'v1' ],
    ];

    /** The closing outline button. */
    const CTA_VARIANTS = [
        'modern' => [ 'v3' ],
        'luxury' => [ 'v1' ],
    ];

    /**
     * Variants whose closing bar also carries a line of copy and a cluster of reviewer faces
     * opposite the button. Modern V3 closes on a centred button alone, so it is not here.
     */
    const CTA_ASIDE_VARIANTS = [
        'luxury' => [ 'v1' ],
    ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
        $this->register_pack_script();
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-social-proof-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/social-proof/{$pack}.css",
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
                [ 'jquery', 'slick' ],
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
        return 'woolentor-social-proof';
    }

    public function get_title() {
        return esc_html__( 'Social Proof - 2026', 'woolentor' );
    }

    public function get_icon() {
        // Verified present in the bundled Elementor's eicons before being used — Product Bundle's
        // plan named an icon that did not exist.
        return 'woolentor-widget-new-icon eicon-testimonial-carousel';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'testimonial', 'testimonials', 'review', 'reviews', 'customer review', 'rating',
                 'stars', 'social proof', 'quote', 'press', 'trust', 'pack', 'style', 'woolentor' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-social-proof-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_script_depends() {
        return [ 'wl-pack-widgets' ];
    }

    public function get_style_depends_slider() {
        return [ 'slick' ];
    }

    /**
     * Style => locked variants.
     * The mirror therefore cuts nothing. A generator that deletes nothing cannot lose a method,
     * and the cut-based one ate a neighbour five times across two widgets before that was fixed.
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
     * AND several condition groups together. Elementor's Conditions::check() recurses into any
     * term that carries its own 'terms', so a group built by build_conditions() can be nested
     * inside another — which is how a control gets both "only on these variants" and "not on a
     * Pro-locked one" at the same time.
     *
     * @param  array ...$groups
     * @return array
     */
    private function all_of( ...$groups ) {
        return [ 'relation' => 'and', 'terms' => $groups ];
    }

    /**
     * Union of several style => variants maps, for a control section that holds things appearing
     * on different variant sets.
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
     * The style => variants map of every spotlight using one picker shape.
     *
     * Derived from PICKER_MODE rather than written out again, so a variant that changes its
     * picker cannot leave a Style-tab control pointing at something no longer drawn.
     *
     * @param  string ...$modes  thumbs | filmstrip | arrows | tabs
     * @return array
     */
    private function picker_map( ...$modes ) {
        $map = [];

        foreach ( self::PICKER_MODE as $style => $variants ) {
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
     * same constants the controls are gated on, so markup and controls cannot drift apart.
     *
     * @param  array  $map
     * @param  string $style
     * @param  string $variant
     * @return bool
     */
    protected function in_map( array $map, $style, $variant ) {
        return isset( $map[ $style ] ) && in_array( $variant, $map[ $style ], true );
    }

    /**
     * The picker shape this variant uses, or '' when it has none.
     *
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function picker_mode( $style, $variant ) {
        return (string) ( self::PICKER_MODE[ $style ][ $variant ] ?? '' );
    }

    protected function register_controls() {

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
                'raw'             => esc_html__( 'Variant 1 shows every review at once. Variant 2 is a carousel. Variant 3 features one review at a time.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            // Guarded rather than cut by the mirror: with Pro active get_pro_map() is empty and
            // there is nothing to upsell.
            if ( $this->get_pro_map() ) {
                woolentor_upgrade_pro_notice(
                    $this,
                    'social_proof_variant_pro_notice',
                    [ 'variant' => [ 'v2', 'v3' ] ],
                    [ 'mode' => 'alert' ]
                );
            }

        $this->end_controls_section();

        $this->register_source_controls();
        $this->register_header_controls();
        $this->register_testimonial_controls();
        $this->register_summary_controls();
        $this->register_press_controls();
        $this->register_display_controls();
        $this->register_slider_controls();
        $this->register_cta_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content: Source ───────────────────────────────────────────────────────

    private function register_source_controls() {
        $this->start_controls_section( 'section_source', [
            'label'      => esc_html__( 'Source', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'source', [
                'label'   => esc_html__( 'Reviews From', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'both',
                'options' => [
                    'both'    => esc_html__( 'Written by me, then WooCommerce reviews', 'woolentor' ),
                    'manual'  => esc_html__( 'Written by me only', 'woolentor' ),
                    'reviews' => esc_html__( 'WooCommerce reviews only', 'woolentor' ),
                ],
                'description' => esc_html__( 'Only approved reviews are ever shown.', 'woolentor' ),
            ] );

            $this->add_control( 'limit', [
                'label'   => esc_html__( 'Maximum Shown', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 9,
                'min'     => 1,
                'max'     => 50,
            ] );

            $this->add_control( 'orderby', [
                'label'     => esc_html__( 'Order Reviews By', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'comment_date_gmt',
                'options'   => [
                    'comment_date_gmt' => esc_html__( 'Newest first', 'woolentor' ),
                    'meta_value_num'   => esc_html__( 'Highest rated first', 'woolentor' ),
                    'rand'             => esc_html__( 'Random', 'woolentor' ),
                ],
                'condition' => [ 'source!' => 'manual' ],
            ] );

            $this->add_control( 'min_rating', [
                'label'       => esc_html__( 'Minimum Rating', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '1',
                'options'     => [ '1' => '1★+', '2' => '2★+', '3' => '3★+', '4' => '4★+', '5' => '5★' ],
                'description' => esc_html__( 'Hides lower-rated reviews from this section. It does not change them.', 'woolentor' ),
                'condition'   => [ 'source!' => 'manual' ],
            ] );

            $this->add_control( 'words', [
                'label'       => esc_html__( 'Trim To Words', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 40,
                'min'         => 0,
                'description' => esc_html__( '0 shows the whole review.', 'woolentor' ),
            ] );

        $this->end_controls_section();
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
                'default'     => esc_html__( 'Customer Stories', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'heading', [
                'label'       => esc_html__( 'Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Loved by the people who use it.', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Accepts <em>, <strong> and <br> for an accent word.', 'woolentor' ),
            ] );

            $this->add_control( 'description', [
                'label'   => esc_html__( 'Description', 'woolentor' ),
                'type'    => Controls_Manager::TEXTAREA,
                'rows'    => 3,
                'default' => esc_html__( 'Real reviews from verified buyers — not cherry-picked.', 'woolentor' ),
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

    // ── Content: Testimonials ─────────────────────────────────────────────────

    private function register_testimonial_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'quote', [
            'label'   => esc_html__( 'Quote', 'woolentor' ),
            'type'    => Controls_Manager::TEXTAREA,
            'rows'    => 4,
            'default' => esc_html__( 'Genuinely the best thing I have bought this year.', 'woolentor' ),
        ] );

        $repeater->add_control( 'author', [
            'label'       => esc_html__( 'Name', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
        ] );

        $repeater->add_control( 'role', [
            'label'       => esc_html__( 'Role or Place', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
            'placeholder' => esc_html__( 'Product Designer · Berlin', 'woolentor' ),
        ] );

        $repeater->add_control( 'avatar', [
            'label' => esc_html__( 'Photo', 'woolentor' ),
            'type'  => Controls_Manager::MEDIA,
        ] );

        $repeater->add_control( 'rating', [
            'label'   => esc_html__( 'Rating', 'woolentor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => '5',
            'options' => [ '5' => '5★', '4' => '4★', '3' => '3★', '2' => '2★', '1' => '1★', '0' => esc_html__( 'None', 'woolentor' ) ],
        ] );

        // Free text rather than a date picker: the references print "March 2026", not a date.
        $repeater->add_control( 'date', [
            'label'       => esc_html__( 'Date Text', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'March 2026', 'woolentor' ),
        ] );

        $repeater->add_control( 'product', [
            'label'       => esc_html__( 'About Product', 'woolentor' ),
            'type'        => 'woolentor-select',
            'ajax_search' => true,
            'post_type'   => 'product',
            'label_block' => true,
            'placeholder' => esc_html__( 'Search products…', 'woolentor' ),
        ] );

        $repeater->add_control( 'featured', [
            'label'        => esc_html__( 'Show the Badge', 'woolentor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'description'  => esc_html__( 'Your own wording, set in Display. A WooCommerce review says "Verified" only when WooCommerce confirms the purchase.', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_testimonials', [
            'label'      => esc_html__( 'Testimonials', 'woolentor' ),
            'conditions' => $this->all_of(
                [ 'relation' => 'or', 'terms' => [
                    [ 'name' => 'source', 'operator' => '==', 'value' => 'manual' ],
                    [ 'name' => 'source', 'operator' => '==', 'value' => 'both' ],
                ] ],
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'testimonials', [
                'label'       => esc_html__( 'Testimonials', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ author || quote }}}',
                'default'     => [],
                // Elementor keeps the last row by default, so a merchant who adds one by mistake
                // is stuck with it. The widget already renders an empty list (that is the default
                // state, and the WooCommerce source needs no rows at all), so nothing needs one.
                'prevent_empty' => false,
            ] );

        $this->end_controls_section();
    }

    // ── Content: Summary ──────────────────────────────────────────────────────

    private function register_summary_controls() {
        $gate = $this->merge_maps( self::SUMMARY_VARIANTS, self::HISTOGRAM_VARIANTS, self::STATS_VARIANTS );

        $this->start_controls_section( 'section_summary', [
            'label'      => esc_html__( 'Rating Summary', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( $gate ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'summary_note', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'The score and counts are read from your approved WooCommerce reviews. There is no field to type them in, so what a shopper reads is always what your shop actually scored.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            $this->add_control( 'show_summary', [
                'label'        => esc_html__( 'Rating Line', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::SUMMARY_VARIANTS ),
            ] );

            $this->add_control( 'summary_count_text', [
                'label'       => esc_html__( 'Count Wording', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( '{count} verified reviews', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Use {count}.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::SUMMARY_VARIANTS ),
            ] );

            $this->add_control( 'show_histogram', [
                'label'        => esc_html__( 'Star Breakdown', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::HISTOGRAM_VARIANTS ),
            ] );

            $this->add_control( 'stats_note', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'The three figures beside the heading: the average and the review count come from your shop. The third is yours to set.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
                'conditions'      => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

            $this->add_control( 'stat_avg_label', [
                'label'      => esc_html__( 'Average Label', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Avg. Rating', 'woolentor' ),
                'conditions' => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

            $this->add_control( 'stat_count_label', [
                'label'      => esc_html__( 'Count Label', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Reviews', 'woolentor' ),
                'conditions' => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

            $this->add_control( 'stat_extra_value', [
                'label'       => esc_html__( 'Third Figure', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( '98%', 'woolentor' ),
                'description' => esc_html__( 'Leave empty to show only the two figures your shop can prove.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

            $this->add_control( 'stat_extra_label', [
                'label'      => esc_html__( 'Third Label', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Recommend', 'woolentor' ),
                'conditions' => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Press ────────────────────────────────────────────────────────

    private function register_press_controls() {
        $repeater = new Repeater();

        $repeater->add_control( 'name', [
            'label'       => esc_html__( 'Wordmark', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'label_block' => true,
            'placeholder' => esc_html__( 'VOGUE', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_press', [
            'label'      => esc_html__( 'Press Strip', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::PRESS_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'press', [
                'label'       => esc_html__( 'Publications', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ name }}}',
                'default'     => [],
                'prevent_empty' => false,
            ] );

        $this->end_controls_section();
    }

    // ── Content: Display ──────────────────────────────────────────────────────

    private function register_display_controls() {
        $this->start_controls_section( 'section_display', [
            'label'      => esc_html__( 'Display', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'show_rating', [
                'label'        => esc_html__( 'Stars', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
            ] );

            $this->add_control( 'show_avatar', [
                'label'        => esc_html__( 'Photo', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'description'  => esc_html__( 'WooCommerce reviews use the site avatar, which may be fetched from Gravatar.', 'woolentor' ),
                'conditions'   => $this->build_conditions( self::AVATAR_VARIANTS ),
            ] );

            $this->add_control( 'show_role', [
                'label'        => esc_html__( 'Role or Place', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::ROLE_VARIANTS ),
            ] );

            $this->add_control( 'show_date', [
                'label'        => esc_html__( 'Date', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::DATE_VARIANTS ),
            ] );

            $this->add_control( 'show_product', [
                'label'        => esc_html__( 'Product', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'conditions'   => $this->build_conditions( self::PRODUCT_VARIANTS ),
            ] );

            // Both badges are earned, not typed: without this line a merchant switches Badge on,
            // sees nothing, and reads the control as broken.
            $this->add_control( 'show_verified', [
                'label'        => esc_html__( 'Badge', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'description'  => esc_html__( 'Appears beside the name. A WooCommerce review needs a confirmed purchase; a testimonial you wrote needs its own Featured switch turned on.', 'woolentor' ),
                'conditions'   => $this->build_conditions( self::VERIFIED_VARIANTS ),
            ] );

            // Two strings, deliberately. A merchant's own testimonial is not a verified purchase,
            // and one label for both would put WooCommerce's guarantee on copy the merchant wrote.
            $this->add_control( 'verified_text', [
                'label'      => esc_html__( 'WooCommerce Badge', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Verified', 'woolentor' ),
                'description' => esc_html__( 'Used on a review from a confirmed purchase.', 'woolentor' ),
                'conditions' => $this->build_conditions( self::VERIFIED_VARIANTS ),
            ] );

            $this->add_control( 'featured_text', [
                'label'      => esc_html__( 'Your Own Badge', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Featured', 'woolentor' ),
                'description' => esc_html__( 'Used on a testimonial you wrote, with Featured switched on.', 'woolentor' ),
                'conditions' => $this->build_conditions( self::VERIFIED_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Slider Options ───────────────────────────────────────────────

    /**
     * The same option set every other pack widget's slider carries.
     *
     * Deliberately the shared Slick engine (`WLPackSlider`) rather than a handler of this
     * widget's own: Slick is already bundled, already tested, already keyboard- and
     * touch-capable, and a merchant who has set up a Hero Banner slider finds the same switches
     * here under the same names. A second implementation would only be a second thing to fix.
     */
    private function register_slider_controls() {
        $spotlight = $this->build_conditions( self::SPOTLIGHT_VARIANTS );

        $this->start_controls_section( 'section_slider', [
            'label'      => esc_html__( 'Slider Options', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::SLIDER_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'slider_arrows', [
                'label'        => esc_html__( 'Show Arrows', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            // A spotlight navigates by its own picker, so dots would be a second row of the same
            // control. Hidden rather than shown and ignored.
            $this->add_control( 'slider_dots', [
                'label'        => esc_html__( 'Show Dots', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'conditions'   => $this->build_negated_conditions( self::SPOTLIGHT_VARIANTS ),
            ] );

            $this->add_control( 'slider_infinite', [
                'label'        => esc_html__( 'Infinite Loop', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'slider_fade', [
                'label'        => esc_html__( 'Fade Transition', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                // Slick can only cross-fade when it shows one slide, so this is offered on the
                // spotlight layouts and nowhere else.
                'conditions'   => $spotlight,
            ] );

            $this->add_control( 'slider_autoplay', [
                'label'        => esc_html__( 'Autoplay', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
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
                'label'   => esc_html__( 'Transition Speed (ms)', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 600,
                'min'     => 100,
                'step'    => 50,
            ] );

            $this->add_control( 'slider_pause_on_hover', [
                'label'        => esc_html__( 'Pause on Hover', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            // A spotlight shows one review by definition, so the per-device counts are hidden
            // there rather than offered and ignored.
            $this->add_control( 'heading_desktop', [
                'label'      => esc_html__( 'Desktop', 'woolentor' ),
                'type'       => Controls_Manager::HEADING,
                'separator'  => 'before',
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_items', [
                'label'      => esc_html__( 'Reviews Shown', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 3,
                'min'        => 1,
                'max'        => 6,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_scroll', [
                'label'      => esc_html__( 'Reviews to Scroll', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 1,
                'min'        => 1,
                'max'        => 6,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'heading_tablet', [
                'label'      => esc_html__( 'Tablet', 'woolentor' ),
                'type'       => Controls_Manager::HEADING,
                'separator'  => 'before',
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_tablet_items', [
                'label'      => esc_html__( 'Reviews Shown', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 2,
                'min'        => 1,
                'max'        => 6,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_tablet_scroll', [
                'label'      => esc_html__( 'Reviews to Scroll', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 1,
                'min'        => 1,
                'max'        => 6,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_tablet_width', [
                'label'      => esc_html__( 'Tablet Resolution', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 1024,
                'min'        => 480,
                'max'        => 1400,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'heading_mobile', [
                'label'      => esc_html__( 'Mobile', 'woolentor' ),
                'type'       => Controls_Manager::HEADING,
                'separator'  => 'before',
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_mobile_items', [
                'label'      => esc_html__( 'Reviews Shown', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 1,
                'min'        => 1,
                'max'        => 3,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_mobile_scroll', [
                'label'      => esc_html__( 'Reviews to Scroll', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 1,
                'min'        => 1,
                'max'        => 3,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'sl_mobile_width', [
                'label'      => esc_html__( 'Mobile Resolution', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 767,
                'min'        => 320,
                'max'        => 1024,
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_control( 'spotlight_note', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'This layout shows one review at a time and is navigated by its own picker, so the per-device counts do not apply.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
                'conditions'      => $spotlight,
            ] );

        $this->end_controls_section();
    }

    // ── Content: Button ───────────────────────────────────────────────────────

    private function register_cta_controls() {
        $this->start_controls_section( 'section_cta', [
            'label'      => esc_html__( 'Button', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::CTA_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'cta_text', [
                'label'       => esc_html__( 'Button Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Read all reviews', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Leave empty to hide the button.', 'woolentor' ),
            ] );

            $this->add_control( 'cta_note', [
                'label'       => esc_html__( 'Note', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Join thousands of customers who trust us.', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Sits opposite the button, beside a few reviewer photos. Accepts <em> and <strong>.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::CTA_ASIDE_VARIANTS ),
            ] );

            $this->add_control( 'cta_link', [
                'label'       => esc_html__( 'Button Link', 'woolentor' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Defaults to the shop page', 'woolentor' ),
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
            woolentor_upgrade_pro_notice( $this, 'social_proof_content_pro_notice', $condition );
        $this->end_controls_section();
    }

    // ── Data ──────────────────────────────────────────────────────────────────

    /**
     * The merchant's own testimonials, in the shared item shape.
     *
     * @param  array $rows
     * @param  array $settings
     * @return array
     */
    private function build_manual( array $rows, array $settings ) {
        $items = [];

        foreach ( $rows as $row ) {
            $quote = trim( (string) ( $row['quote'] ?? '' ) );

            if ( '' === $quote ) {
                continue;
            }

            $product = null;
            $id      = absint( $row['product'] ?? 0 );

            if ( $id && function_exists( 'wc_get_product' ) ) {
                $product = $this->product_shape( $id );
            }

            $items[] = [
                'kind'     => 'manual',
                'quote'    => $this->trim_quote( $quote, $settings ),
                'author'   => (string) ( $row['author'] ?? '' ),
                'role'     => (string) ( $row['role'] ?? '' ),
                'rating'   => (int) ( $row['rating'] ?? 5 ),
                'verified' => 'yes' === ( $row['featured'] ?? '' ),
                'date'     => (string) ( $row['date'] ?? '' ),
                'avatar'   => $this->manual_avatar( $row ),
                'product'  => $product,
            ];
        }

        return $items;
    }

    /**
     * Approved WooCommerce product reviews, in the shared item shape.
     *
     * `'status' => 'approve'` is hard-coded and has no control: a pending or spam review must
     * never reach the front end.
     *
     * @param  array $settings
     * @param  int   $limit
     * @param  int   $dropped  Filled with the number of reviews whose product is gone.
     * @return array
     */
    private function build_reviews( array $settings, $limit, &$dropped ) {
        $dropped = 0;

        if ( $limit < 1 || ! function_exists( 'wc_get_product' ) ) {
            return [];
        }

        $orderby = (string) ( $settings['orderby'] ?? 'comment_date_gmt' );
        $orderby = in_array( $orderby, [ 'comment_date_gmt', 'meta_value_num', 'rand' ], true ) ? $orderby : 'comment_date_gmt';
        $min     = max( 1, min( 5, (int) ( $settings['min_rating'] ?? 1 ) ) );

        $args = [
            'post_type'  => 'product',
            'status'     => 'approve',
            'type'       => 'review',
            'number'     => $limit,
            'orderby'    => $orderby,
            'order'      => 'DESC',
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [ 'key' => 'rating', 'value' => $min, 'compare' => '>=', 'type' => 'NUMERIC' ],
            ],
        ];

        if ( 'meta_value_num' === $orderby ) {
            $args['meta_key'] = 'rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        }

        $comments = get_comments( $args );

        // WooCommerce has only set comment_type = 'review' since 3.5. Reviews imported by a
        // migration tool, or written before that, are plain comments — and a shop full of them
        // would render an empty section with no explanation.
        if ( empty( $comments ) ) {
            unset( $args['type'] );
            $comments = get_comments( $args );
        }

        $items = [];

        foreach ( $comments as $comment ) {
            $product = $this->product_shape( (int) $comment->comment_post_ID );

            if ( ! $product ) {
                $dropped++;
                continue;
            }

            $items[] = [
                'kind'     => 'review',
                'quote'    => $this->trim_quote( wp_strip_all_tags( (string) $comment->comment_content ), $settings ),
                'author'   => get_comment_author( $comment ),
                'role'     => '',
                'rating'   => (int) get_comment_meta( $comment->comment_ID, 'rating', true ),
                'verified' => (bool) get_comment_meta( $comment->comment_ID, 'verified', true ),
                'date'     => get_comment_date( '', $comment ),
                'avatar'   => get_avatar( $comment, 96, '', get_comment_author( $comment ), [ 'class' => 'wl-sp-avatar-img' ] ),
                'product'  => $product,
            ];
        }

        return $items;
    }

    /**
     * The merged list every template reads. One shape, whatever the source — so no template ever
     * asks where a review came from, and the twelve cannot drift apart.
     *
     * @param  array $settings
     * @param  int   $dropped
     * @return array
     */
    private function build_items( array $settings, &$dropped ) {
        $dropped = 0;
        $limit   = max( 1, (int) ( $settings['limit'] ?? 9 ) );
        $source  = (string) ( $settings['source'] ?? 'both' );

        $manual = in_array( $source, [ 'manual', 'both' ], true )
            ? $this->build_manual( $settings['testimonials'] ?? [], $settings )
            : [];

        $reviews = [];

        if ( in_array( $source, [ 'reviews', 'both' ], true ) ) {
            $reviews = $this->build_reviews( $settings, $limit - count( $manual ), $dropped );
        }

        return array_slice( array_merge( $manual, $reviews ), 0, $limit );
    }

    /**
     * A product's name, link and thumbnail — or null when it is gone or unpublished.
     *
     * A quote about something a shopper cannot buy is noise at best, so the caller drops the
     * review and counts it.
     *
     * @param  int $id
     * @return array|null
     */
    protected function product_shape( $id ) {
        if ( ! $id || ! function_exists( 'wc_get_product' ) ) {
            return null;
        }

        $product = wc_get_product( $id );

        if ( ! $product || 'publish' !== get_post_status( $id ) ) {
            return null;
        }

        return [
            'id'    => $id,
            'name'  => $product->get_name(),
            'url'   => get_permalink( $id ),
            'thumb' => $product->get_image( 'woocommerce_gallery_thumbnail', [ 'class' => 'wl-sp-product-img', 'alt' => '' ] ),
            // get_price_html() rather than a raw number: it is what WooCommerce prints everywhere
            // else, so it already handles sale prices, ranges, tax display and the currency.
            'price' => $product->get_price_html(),
        ];
    }

    /**
     * @param  string $text
     * @param  array  $settings
     * @return string
     */
    protected function trim_quote( $text, array $settings ) {
        $words = (int) ( $settings['words'] ?? 40 );

        return $words > 0 ? wp_trim_words( $text, $words, '…' ) : $text;
    }

    /**
     * A manual testimonial's photograph. Falls back to nothing rather than a stock face — an
     * invented portrait beside a real name is its own small lie.
     *
     * @param  array $row
     * @return string
     */
    protected function manual_avatar( array $row ) {
        $id  = absint( $row['avatar']['id'] ?? 0 );
        $url = (string) ( $row['avatar']['url'] ?? '' );
        $alt = (string) ( $row['author'] ?? '' );

        if ( $id ) {
            return (string) wp_get_attachment_image( $id, 'thumbnail', false, [ 'class' => 'wl-sp-avatar-img', 'alt' => $alt ] );
        }

        if ( '' !== $url ) {
            return '<img class="wl-sp-avatar-img" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">';
        }

        return '';
    }

    private function register_pro_style_notice() {
        if ( ! $this->get_pro_map() ) {
            return;
        }

        $this->start_controls_section( 'section_pro_style_notice', [
            'label'      => esc_html__( 'Style', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_conditions( $this->get_pro_map() ),
        ] );
            woolentor_upgrade_pro_notice( $this, 'social_proof_style_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ] );
        $this->end_controls_section();
    }

    // ── Style ─────────────────────────────────────────────────────────────────

    /**
     * The Style tab.
     *
     * Two rules hold throughout, both learned the hard way:
     *
     *   1. **Every control is gated by the map of the thing it styles.** A colour offered on a
     *      variant whose template never prints that element does nothing, and the audit in §12
     *      of the plan only catches it if the gating is per control rather than per section.
     *   2. **Almost nothing carries a default.** Elementor writes a default straight into the
     *      page's generated CSS, which outranks the pack stylesheets — so a default here would
     *      silently overwrite every pack's own design.
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
                'selectors' => [ '{{WRAPPER}} .wl-sp' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'section_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp' => 'color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'section_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sp' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'selectors' => [ '{{WRAPPER}} .wl-sp-head-main' => 'text-align: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'header_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Eyebrow Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-sp-eyebrow',
            ] );

            $this->add_control( 'heading_color', [
                'label'     => esc_html__( 'Heading Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-heading' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'heading_typography',
                'selector' => '{{WRAPPER}} .wl-sp-heading',
            ] );

            $this->add_control( 'desc_color', [
                'label'     => esc_html__( 'Description Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-desc' => 'color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // ── Stars ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_stars', [
            'label'      => esc_html__( 'Stars', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'star_color', [
                'label'     => esc_html__( 'Star Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-star' => 'color: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'star_size', [
                'label'      => esc_html__( 'Star Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 32 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-star-size: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'star_off_opacity', [
                'label'       => esc_html__( 'Empty Star Opacity', 'woolentor' ),
                'type'        => Controls_Manager::SLIDER,
                'range'       => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
                'description' => esc_html__( 'Empty stars keep their outline so four out of five still reads as four out of five.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-star-off-opacity: {{SIZE}};' ],
            ] );

        $this->end_controls_section();

        // ── Rating summary ────────────────────────────────────────────────────

        $this->start_controls_section( 'style_summary', [
            'label'      => esc_html__( 'Rating Summary', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( $this->merge_maps( self::SUMMARY_VARIANTS, self::HISTOGRAM_VARIANTS, self::STATS_VARIANTS ) ),
        ] );

            $this->add_control( 'score_color', [
                'label'      => esc_html__( 'Score Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-score' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::SUMMARY_VARIANTS ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'       => 'score_typography',
                'selector'   => '{{WRAPPER}} .wl-sp-score',
                'conditions' => $this->build_conditions( self::SUMMARY_VARIANTS ),
            ] );

            $this->add_control( 'count_color', [
                'label'      => esc_html__( 'Count Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-count' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::SUMMARY_VARIANTS ),
            ] );

            $this->add_control( 'bar_color', [
                'label'      => esc_html__( 'Bar Track Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sp-bar' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::HISTOGRAM_VARIANTS ),
            ] );

            $this->add_control( 'bar_fill_color', [
                'label'      => esc_html__( 'Bar Fill Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-bar-fill' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::HISTOGRAM_VARIANTS ),
            ] );

            $this->add_responsive_control( 'bar_height', [
                'label'      => esc_html__( 'Bar Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 2, 'max' => 20 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-bar-height: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::HISTOGRAM_VARIANTS ),
            ] );

            $this->add_control( 'stat_num_color', [
                'label'      => esc_html__( 'Figure Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sp-stat-num' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

            $this->add_control( 'stat_label_color', [
                'label'      => esc_html__( 'Figure Label Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-stat-label' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::STATS_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Press ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_press', [
            'label'      => esc_html__( 'Press Strip', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::PRESS_VARIANTS ),
        ] );

            $this->add_control( 'press_color', [
                'label'     => esc_html__( 'Wordmark Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-press-item' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'press_typography',
                'selector' => '{{WRAPPER}} .wl-sp-press-item',
            ] );

            $this->add_responsive_control( 'press_gap', [
                'label'      => esc_html__( 'Spacing', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 8, 'max' => 64 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-press-gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // ── Layout ────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_layout', [
            'label'      => esc_html__( 'Layout', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( $this->merge_maps( self::WALL_VARIANTS, self::CAROUSEL_VARIANTS ) ),
        ] );

            $this->add_responsive_control( 'layout_gap', [
                'label'      => esc_html__( 'Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 64 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'slide_width', [
                'label'      => esc_html__( 'Card Width', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => 200, 'max' => 640 ], '%' => [ 'min' => 20, 'max' => 100 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-slide: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::CAROUSEL_VARIANTS ),
            ] );

            $this->add_responsive_control( 'marquee_height', [
                'label'      => esc_html__( 'Wall Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 300, 'max' => 1000 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-marquee-height: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::MARQUEE_VARIANTS ),
            ] );

            $this->add_control( 'marquee_speed', [
                'label'       => esc_html__( 'Scroll Duration', 'woolentor' ),
                'type'        => Controls_Manager::SLIDER,
                'size_units'  => [ 's' ],
                'range'       => [ 's' => [ 'min' => 10, 'max' => 160 ] ],
                'description' => esc_html__( 'Seconds for one full pass. It pauses on hover and does not run for visitors who ask for reduced motion.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-marquee-speed: {{SIZE}}{{UNIT}};' ],
                'conditions'  => $this->build_conditions( self::MARQUEE_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Card ──────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_card', [
            'label'      => esc_html__( 'Card', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::CARD_VARIANTS ),
        ] );

            $this->add_control( 'card_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-item' => 'background: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .wl-sp-item',
            ] );

            $this->add_responsive_control( 'card_radius', [
                'label'      => esc_html__( 'Corner Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp-item' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'card_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sp-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .wl-sp-item',
            ] );

        $this->end_controls_section();

        // ── Quote ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_quote', [
            'label'      => esc_html__( 'Quote', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'quote_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-text' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'quote_typography',
                'selector' => '{{WRAPPER}} .wl-sp-text',
            ] );

            $this->add_control( 'mark_color', [
                'label'      => esc_html__( 'Quote Mark Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-mark' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::MARK_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Byline ────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_byline', [
            'label'      => esc_html__( 'Byline', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $editable,
        ] );

            $this->add_control( 'name_color', [
                'label'     => esc_html__( 'Name Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-name' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'name_typography',
                'selector' => '{{WRAPPER}} .wl-sp-name',
            ] );

            $this->add_control( 'role_color', [
                'label'      => esc_html__( 'Role Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-role' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::ROLE_VARIANTS ),
            ] );

            $this->add_control( 'date_color', [
                'label'      => esc_html__( 'Date Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-date' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::DATE_VARIANTS ),
            ] );

            $this->add_responsive_control( 'avatar_size', [
                'label'      => esc_html__( 'Photo Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 24, 'max' => 220 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-avatar-size: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::AVATAR_VARIANTS ),
            ] );

            $this->add_responsive_control( 'avatar_radius', [
                'label'      => esc_html__( 'Photo Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-avatar-radius: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::AVATAR_VARIANTS ),
            ] );

            $this->add_control( 'verified_color', [
                'label'      => esc_html__( 'Badge Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sp-verified' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::VERIFIED_VARIANTS ),
            ] );

            $this->add_control( 'verified_bg', [
                'label'      => esc_html__( 'Badge Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-verified' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::VERIFIED_VARIANTS ),
            ] );

            $this->add_control( 'product_color', [
                'label'      => esc_html__( 'Product Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sp-product-name' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::PRODUCT_VARIANTS ),
            ] );

            // The three variants that show the product's picture draw it as a chip with its own
            // border, so the chip needs a fill of its own — the name's colour cannot reach it.
            $this->add_control( 'product_bg', [
                'label'      => esc_html__( 'Product Chip Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-product' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::PRODUCT_MEDIA_VARIANTS ),
            ] );

            $this->add_responsive_control( 'product_thumb', [
                'label'      => esc_html__( 'Product Thumbnail Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 24, 'max' => 200 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-product-thumb: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( self::PRODUCT_MEDIA_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Navigation ────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_nav', [
            'label'      => esc_html__( 'Navigation', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( $this->merge_maps( self::CAROUSEL_VARIANTS, self::SPOTLIGHT_VARIANTS ) ),
        ] );

            // Arrows exist on every carousel and on the one spotlight whose picker is arrows.
            // The other three spotlights pick with thumbnails, a filmstrip or tabs.
            $arrows = $this->merge_maps( self::CAROUSEL_VARIANTS, $this->picker_map( 'arrows' ) );

            $this->add_control( 'arrow_color', [
                'label'      => esc_html__( 'Arrow Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-arrow' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( $arrows ),
            ] );

            $this->add_responsive_control( 'arrow_size', [
                'label'      => esc_html__( 'Arrow Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 24, 'max' => 64 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-arrow-size: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( $arrows ),
            ] );

            $this->add_control( 'picker_color', [
                'label'      => esc_html__( 'Picker Label Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sp-picker-btn' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( $this->picker_map( 'thumbs', 'tabs' ) ),
            ] );

            $this->add_control( 'picker_color_active', [
                'label'      => esc_html__( 'Active Picker Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [
                    '{{WRAPPER}} .wl-sp-picker-btn.is-active' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
                'conditions' => $this->build_conditions( $this->picker_map( 'thumbs', 'tabs' ) ),
            ] );

            $this->add_responsive_control( 'picker_thumb', [
                'label'      => esc_html__( 'Picker Thumbnail Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 32, 'max' => 140 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp' => '--wl-sp-picker-thumb: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( $this->picker_map( 'thumbs', 'filmstrip' ) ),
            ] );

            $this->add_control( 'counter_color', [
                'label'      => esc_html__( 'Counter Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-counter' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( $this->picker_map( 'arrows' ) ),
            ] );

            $this->add_control( 'progress_color', [
                'label'      => esc_html__( 'Progress Track Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-sp-progress' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::PROGRESS_VARIANTS ),
            ] );

            $this->add_control( 'progress_fill_color', [
                'label'      => esc_html__( 'Progress Fill Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-sp-progress-fill' => 'background: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::PROGRESS_VARIANTS ),
            ] );

        $this->end_controls_section();

        // ── Featured card ─────────────────────────────────────────────────────

        $this->start_controls_section( 'style_featured', [
            'label'      => esc_html__( 'Featured Card', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::FEATURED_VARIANTS ),
        ] );

            $this->add_control( 'featured_note', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'The first review is set larger than the rest in this layout.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            $this->add_control( 'featured_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sp-item.is-featured, {{WRAPPER}} .wl-sp-featured .wl-sp-item' => 'background: {{VALUE}};',
                ],
            ] );

            $this->add_control( 'featured_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wl-sp-item.is-featured .wl-sp-text, {{WRAPPER}} .wl-sp-featured .wl-sp-item .wl-sp-text' => 'color: {{VALUE}};',
                ],
            ] );

        $this->end_controls_section();

        // ── Button ────────────────────────────────────────────────────────────

        $this->start_controls_section( 'style_cta', [
            'label'      => esc_html__( 'Button', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $gate( self::CTA_VARIANTS ),
        ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'cta_typography',
                'selector' => '{{WRAPPER}} .wl-sp-cta',
            ] );

            $this->add_control( 'cta_color', [
                'label'     => esc_html__( 'Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-cta' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'cta_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-cta' => 'background: {{VALUE}};' ],
            ] );

            $this->add_control( 'cta_color_hover', [
                'label'     => esc_html__( 'Hover Text Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-cta:hover' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'cta_bg_hover', [
                'label'     => esc_html__( 'Hover Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-sp-cta:hover' => 'background: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'cta_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-sp-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'cta_radius', [
                'label'      => esc_html__( 'Corner Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 999 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-sp-cta' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();
    }

    // ── Markup helpers ────────────────────────────────────────────────────────

    /**
     * The inline icon set. Inline because the reference packs use icon fonts this plugin does not
     * ship, and because an SVG inherits `currentColor` so every pack skins it for free.
     *
     * @param  string $name
     * @return string
     */
    protected function icon( $name ) {
        $paths = [
            'check' => '<polyline points="20 6 9 17 4 12"></polyline>',
            'left'  => '<line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>',
            'right' => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',
        ];

        if ( ! isset( $paths[ $name ] ) ) {
            return '';
        }

        return '<svg class="wl-sp-icon wl-sp-icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
    }

    /**
     * One star. Filled or hollow — never a half, because WooCommerce stores whole-number ratings
     * per review and drawing a half would imply a precision the data does not have.
     *
     * @param  bool $filled
     * @return string
     */
    protected function star( $filled ) {
        return '<svg class="wl-sp-star' . ( $filled ? ' is-on' : '' ) . '" viewBox="0 0 24 24"'
            . ' fill="' . ( $filled ? 'currentColor' : 'none' ) . '" stroke="currentColor" stroke-width="1.5"'
            . ' aria-hidden="true" focusable="false">'
            . '<polygon points="12 2 15.09 8.5 22 9.3 17 14.1 18.2 21 12 17.7 5.8 21 7 14.1 2 9.3 8.91 8.5"></polygon>'
            . '</svg>';
    }

    /**
     * A row of five stars with a real accessible name, so a screen reader hears "4 out of 5
     * stars" rather than five identical graphics.
     *
     * @param  int   $rating
     * @param  array $settings
     * @return string
     */
    protected function stars_html( $rating, array $settings ) {
        $rating = max( 0, min( 5, (int) $rating ) );

        if ( ! $rating || 'yes' !== ( $settings['show_rating'] ?? 'yes' ) ) {
            return '';
        }

        $out = '';
        for ( $i = 1; $i <= 5; $i++ ) {
            $out .= $this->star( $i <= $rating );
        }

        return '<span class="wl-sp-rating" role="img" aria-label="' . esc_attr( sprintf(
            /* translators: %d: rating out of five */
            __( '%d out of 5 stars', 'woolentor' ),
            $rating
        ) ) . '">' . $out . '</span>';
    }

    /**
     * The section header.
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

        if ( '' === $eyebrow && '' === $heading && '' === $text ) {
            return '';
        }

        $tag = in_array( $settings['heading_tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ], true )
            ? $settings['heading_tag']
            : 'h2';

        $allowed = [ 'em' => [], 'strong' => [], 'br' => [] ];

        return '<div class="wl-sp-head">'
            . '<div class="wl-sp-head-main">'
            . ( '' !== $eyebrow ? '<span class="wl-sp-eyebrow">' . esc_html( $eyebrow ) . '</span>' : '' )
            . ( '' !== $heading ? '<' . $tag . ' class="wl-sp-heading">' . wp_kses( $heading, $allowed ) . '</' . $tag . '>' : '' )
            . ( '' !== $text ? '<p class="wl-sp-desc">' . esc_html( $text ) . '</p>' : '' )
            . '</div>'
            . '</div>';
    }

    /**
     * The aggregate line — score, stars, count.
     *
     * Returns nothing when the shop has no approved reviews. A section that prints "0.0" beside
     * five hollow stars reads as a broken shop, and §3 of the plan forbids inventing the number
     * that would avoid it.
     *
     * @param  array $settings
     * @return string
     */
    protected function summary_html( array $settings ) {
        if ( 'yes' !== ( $settings['show_summary'] ?? 'yes' ) ) {
            return '';
        }

        $summary = woolentor_sp_rating_summary();

        if ( empty( $summary['count'] ) ) {
            return '';
        }

        $text  = trim( (string) ( $settings['summary_count_text'] ?? '' ) );
        $count = '';

        if ( '' !== $text ) {
            $count = '<span class="wl-sp-count">' . esc_html( str_replace(
                '{count}',
                number_format_i18n( $summary['count'] ),
                $text
            ) ) . '</span>';
        }

        return '<div class="wl-sp-summary">'
            . '<span class="wl-sp-score">' . esc_html( number_format_i18n( $summary['average'], 1 ) ) . '</span>'
            . $this->stars_html( (int) round( $summary['average'] ), $settings )
            . $count
            . '</div>';
    }

    /**
     * The five-row star histogram, drawn from the same cached aggregate.
     *
     * @param  array $settings
     * @return string
     */
    protected function histogram_html( array $settings ) {
        if ( 'yes' !== ( $settings['show_histogram'] ?? 'yes' ) ) {
            return '';
        }

        $summary = woolentor_sp_rating_summary();

        if ( empty( $summary['count'] ) ) {
            return '';
        }

        $out = '<div class="wl-sp-bars">';

        for ( $star = 5; $star >= 1; $star-- ) {
            $n   = (int) ( $summary['counts'][ $star ] ?? 0 );
            $pct = $summary['count'] ? round( $n / $summary['count'] * 100 ) : 0;

            $out .= '<div class="wl-sp-bar-row">'
                . '<span class="wl-sp-bar-star">' . esc_html( $star ) . '</span>'
                . '<span class="wl-sp-bar"><span class="wl-sp-bar-fill" style="width:' . esc_attr( $pct ) . '%;"></span></span>'
                . '<span class="wl-sp-bar-pct">' . esc_html( $pct . '%' ) . '</span>'
                . '</div>';
        }

        return $out . '</div>';
    }

    /**
     * Luxury V1's three figures. The first two are read from the shop; the third is the
     * merchant's and is simply omitted when they leave it empty.
     *
     * @param  array $settings
     * @return string
     */
    protected function stats_html( array $settings ) {
        $summary = woolentor_sp_rating_summary();

        if ( empty( $summary['count'] ) ) {
            return '';
        }

        $stats = [
            [ number_format_i18n( $summary['average'], 1 ), (string) ( $settings['stat_avg_label'] ?? '' ) ],
            [ number_format_i18n( $summary['count'] ), (string) ( $settings['stat_count_label'] ?? '' ) ],
        ];

        $extra = trim( (string) ( $settings['stat_extra_value'] ?? '' ) );

        if ( '' !== $extra ) {
            $stats[] = [ $extra, (string) ( $settings['stat_extra_label'] ?? '' ) ];
        }

        $out = '<div class="wl-sp-stats">';

        foreach ( $stats as $stat ) {
            $out .= '<div class="wl-sp-stat">'
                . '<span class="wl-sp-stat-num">' . esc_html( $stat[0] ) . '</span>'
                . ( '' !== trim( $stat[1] ) ? '<span class="wl-sp-stat-label">' . esc_html( $stat[1] ) . '</span>' : '' )
                . '</div>';
        }

        return $out . '</div>';
    }

    /**
     * Editorial V2's press wordmarks.
     *
     * @param  array $settings
     * @return string
     */
    protected function press_html( array $settings ) {
        $rows = $settings['press'] ?? [];
        $out  = '';

        foreach ( $rows as $row ) {
            $name = trim( (string) ( $row['name'] ?? '' ) );

            if ( '' === $name ) {
                continue;
            }

            if ( '' !== $out ) {
                $out .= '<span class="wl-sp-press-sep" aria-hidden="true"></span>';
            }

            $out .= '<span class="wl-sp-press-item">' . esc_html( $name ) . '</span>';
        }

        return '' !== $out ? '<div class="wl-sp-press">' . $out . '</div>' : '';
    }

    /**
     * One review. Every variant renders the same element; the maps decide which parts appear and
     * the pack stylesheet decides how they look.
     *
     * @param  array  $item
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  int    $position  0-based, for the spotlight's active state.
     * @return string
     */
    protected function item_html( array $item, array $settings, $style, $variant, $position = 0 ) {
        $spotlight = $this->in_map( self::SPOTLIGHT_VARIANTS, $style, $variant );

        $classes = [ 'wl-sp-item' ];
        if ( $spotlight && 0 === $position ) {
            $classes[] = 'is-active';
        }

        $out = '<article class="' . esc_attr( implode( ' ', $classes ) ) . '" data-wl-sp-item="' . esc_attr( $position ) . '">';

        if ( $this->in_map( self::MARK_VARIANTS, $style, $variant ) ) {
            $out .= '<span class="wl-sp-mark" aria-hidden="true">&ldquo;</span>';
        }

        $out .= $this->stars_html( $item['rating'], $settings );

        if ( '' !== $item['quote'] ) {
            $out .= '<blockquote class="wl-sp-quote"><p class="wl-sp-text">' . esc_html( $item['quote'] ) . '</p></blockquote>';
        }

        if ( $item['product'] && 'yes' === ( $settings['show_product'] ?? 'yes' )
            && $this->in_map( self::PRODUCT_VARIANTS, $style, $variant ) ) {

            // The chip clips a long title to one line, so the whole of it goes in `title` — that
            // is the only place a shopper can read a name the pill could not fit.
            $out .= '<a class="wl-sp-product" href="' . esc_url( $item['product']['url'] )
                . '" title="' . esc_attr( $item['product']['name'] ) . '">';

            if ( $this->in_map( self::PRODUCT_MEDIA_VARIANTS, $style, $variant ) ) {
                $out .= '<span class="wl-sp-product-thumb">' . $item['product']['thumb'] . '</span>';
            }

            $out .= '<span class="wl-sp-product-name">' . esc_html( $item['product']['name'] ) . '</span>';

            if ( '' !== (string) ( $item['product']['price'] ?? '' )
                && $this->in_map( self::PRODUCT_PRICE_VARIANTS, $style, $variant ) ) {
                // wp_kses_post, not esc_html: get_price_html() returns markup for a sale price.
                $out .= '<span class="wl-sp-product-price">' . wp_kses_post( $item['product']['price'] ) . '</span>';
            }

            $out .= '</a>';
        }

        $out .= '<footer class="wl-sp-author">';

        if ( '' !== $item['avatar'] && 'yes' === ( $settings['show_avatar'] ?? 'yes' )
            && $this->in_map( self::AVATAR_VARIANTS, $style, $variant ) ) {
            $out .= '<span class="wl-sp-avatar">' . $item['avatar'] . '</span>';
        }

        $out .= '<span class="wl-sp-author-text">';

        if ( '' !== $item['author'] ) {
            $out .= '<span class="wl-sp-name">' . esc_html( $item['author'] ) . '</span>';
        }

        // Role, badge and date share one quiet line under the name — the shape nine of the ten
        // references draw. A pack that wants the badge somewhere else (Modern V1 and Magazine V1
        // pin it to the card's top corner) positions it from here; DOM order does not bind an
        // absolutely positioned element.
        $meta = '';

        if ( '' !== $item['role'] && 'yes' === ( $settings['show_role'] ?? 'yes' )
            && $this->in_map( self::ROLE_VARIANTS, $style, $variant ) ) {
            $meta .= '<span class="wl-sp-role">' . esc_html( $item['role'] ) . '</span>';
        }

        if ( $item['verified'] && 'yes' === ( $settings['show_verified'] ?? 'yes' )
            && $this->in_map( self::VERIFIED_VARIANTS, $style, $variant ) ) {

            // Two different words on purpose: WooCommerce can prove a purchase, the merchant's
            // own testimonial cannot, and one label for both would lend it a guarantee.
            $label = 'review' === $item['kind']
                ? trim( (string) ( $settings['verified_text'] ?? '' ) )
                : trim( (string) ( $settings['featured_text'] ?? '' ) );

            if ( '' !== $label ) {
                $meta .= '<span class="wl-sp-verified">' . $this->icon( 'check' ) . esc_html( $label ) . '</span>';
            }
        }

        if ( '' !== $item['date'] && 'yes' === ( $settings['show_date'] ?? 'yes' )
            && $this->in_map( self::DATE_VARIANTS, $style, $variant ) ) {
            $meta .= '<span class="wl-sp-date">' . esc_html( $item['date'] ) . '</span>';
        }

        if ( '' !== $meta ) {
            $out .= '<span class="wl-sp-meta">' . $meta . '</span>';
        }

        $out .= '</span>';

        return $out . '</footer></article>';
    }

    /**
     * The spotlight's picker, in one of four shapes.
     *
     * `role="group"` with `aria-pressed`, deliberately — not `role="tab"`, which requires a
     * matching `role="tabpanel"` reached through `aria-controls`. An incomplete tab pattern is
     * worse for a screen reader than no pattern at all.
     *
     * @param  array  $items
     * @param  string $mode      thumbs | filmstrip | arrows | tabs
     * @param  array  $settings  Needed for `slider_arrows`: a picker that draws arrows answers to
     *                           the same switch as nav_html()'s pair, or the control would read
     *                           as broken on every layout whose arrows live in the picker.
     * @param  bool   $with_nav  Also draw the counter and the two arrows, as Modern V2's nav bar
     *                           does around its row of faces.
     * @return string
     */
    protected function picker_html( array $items, $mode, array $settings = [], $with_nav = false ) {
        $total = count( $items );

        if ( $total < 2 || '' === $mode ) {
            return '';
        }

        $arrows = 'yes' === ( $settings['slider_arrows'] ?? 'yes' );

        $label = esc_attr__( 'Choose a review', 'woolentor' );
        $open  = '<div class="wl-sp-picker wl-sp-picker--' . esc_attr( $mode ) . '" role="group"'
            . ' aria-label="' . $label . '" data-wl-sp-picker="' . esc_attr( $mode ) . '">';

        if ( 'arrows' === $mode ) {
            return $open
                . ( $arrows ? '<button type="button" class="wl-sp-arrow" data-wl-sp-prev aria-label="'
                    . esc_attr__( 'Previous review', 'woolentor' ) . '">' . $this->icon( 'left' ) . '</button>' : '' )
                . '<span class="wl-sp-counter" data-wl-sp-counter aria-live="polite">'
                . '<span class="wl-sp-counter-now">01</span>'
                . '<span class="wl-sp-counter-sep" aria-hidden="true"> / </span>'
                . '<span class="wl-sp-counter-total">' . esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ) . '</span>'
                . '</span>'
                . ( $arrows ? '<button type="button" class="wl-sp-arrow" data-wl-sp-next aria-label="'
                    . esc_attr__( 'Next review', 'woolentor' ) . '">' . $this->icon( 'right' ) . '</button>' : '' )
                . '</div>';
        }

        $out = $open;

        if ( $with_nav ) {
            $out .= '<span class="wl-sp-counter" data-wl-sp-counter aria-live="polite">'
                . '<span class="wl-sp-counter-now">01</span>'
                . '<span class="wl-sp-counter-sep" aria-hidden="true"> / </span>'
                . '<span class="wl-sp-counter-total">' . esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ) . '</span>'
                . '</span><span class="wl-sp-picker-faces">';
        }

        foreach ( $items as $position => $item ) {
            $active = 0 === $position;
            $name   = '' !== $item['author'] ? $item['author'] : sprintf(
                /* translators: %d: review number */
                __( 'Review %d', 'woolentor' ),
                $position + 1
            );

            $out .= '<button type="button" class="wl-sp-picker-btn' . ( $active ? ' is-active' : '' ) . '"'
                . ' data-wl-sp-go="' . esc_attr( $position ) . '"'
                . ' aria-pressed="' . ( $active ? 'true' : 'false' ) . '">';

            if ( in_array( $mode, [ 'thumbs', 'filmstrip' ], true ) ) {
                // The picture is the reviewer's on a thumbs picker and the product's on a
                // filmstrip — Editorial's reference numbers product frames, not faces.
                // A filmstrip frame with nothing in it is a hole in the row, so a review with no
                // product falls back to the reviewer's picture rather than an empty circle.
                $media = 'filmstrip' === $mode
                    ? ( ( $item['product']['thumb'] ?? '' ) ?: $item['avatar'] )
                    : $item['avatar'];

                if ( '' !== $media ) {
                    $out .= '<span class="wl-sp-picker-media">' . $media . '</span>';
                }
            }

            if ( 'filmstrip' === $mode ) {
                $out .= '<span class="wl-sp-picker-num">' . esc_html( $position + 1 ) . '</span>';
            } else {
                $out .= '<span class="wl-sp-picker-label">' . esc_html( $name ) . '</span>';
            }

            $out .= '</button>';
        }

        if ( $with_nav ) {
            // The faces row closes either way; the arrow pair is the part Show Arrows governs.
            $out .= '</span>';

            if ( $arrows ) {
                $out .= '<span class="wl-sp-nav">'
                    . '<button type="button" class="wl-sp-arrow" data-wl-sp-prev aria-label="'
                    . esc_attr__( 'Previous review', 'woolentor' ) . '">' . $this->icon( 'left' ) . '</button>'
                    . '<button type="button" class="wl-sp-arrow" data-wl-sp-next aria-label="'
                    . esc_attr__( 'Next review', 'woolentor' ) . '">' . $this->icon( 'right' ) . '</button>'
                    . '</span>';
            }
        }

        return $out . '</div>';
    }

    /**
     * Deal the reviews into N columns, round-robin.
     *
     * Round-robin rather than chunking, because chunking puts the first third of the list in
     * column one — and on Modern V1, where the columns scroll at the same speed in alternating
     * directions, that reads as one long list cut into three rather than a wall.
     *
     * @param  array $items
     * @param  int   $count
     * @return array  A list of $count lists, empty ones removed.
     */
    protected function columns( array $items, $count = 3 ) {
        $count   = max( 1, (int) $count );
        $columns = array_fill( 0, $count, [] );

        foreach ( array_values( $items ) as $i => $item ) {
            $columns[ $i % $count ][] = $item;
        }

        return array_values( array_filter( $columns ) );
    }

    /**
     * The carousel's two arrows.
     *
     * Two, never one. A single-arrow pager has to cycle or it strands the shopper at the end —
     * Product Bundle shipped one that did exactly that — and a pair makes the direction obvious
     * without needing to.
     *
     * They drive Slick through `slickPrev` / `slickNext` rather than moving anything themselves,
     * so the slider stays the single source of truth for which review is showing.
     *
     * @param  array $items
     * @param  array $settings
     * @return string
     */
    protected function nav_html( array $items, array $settings = [] ) {
        if ( count( $items ) < 2 || 'yes' !== ( $settings['slider_arrows'] ?? 'yes' ) ) {
            return '';
        }

        return '<div class="wl-sp-nav" role="group" aria-label="' . esc_attr__( 'Review navigation', 'woolentor' ) . '">'
            . '<button type="button" class="wl-sp-arrow" data-wl-sp-prev aria-label="'
            . esc_attr__( 'Previous reviews', 'woolentor' ) . '">' . $this->icon( 'left' ) . '</button>'
            . '<button type="button" class="wl-sp-arrow" data-wl-sp-next aria-label="'
            . esc_attr__( 'Next reviews', 'woolentor' ) . '">' . $this->icon( 'right' ) . '</button>'
            . '</div>';
    }

    /** Magazine V2's progress bar under the track. */
    protected function progress_html() {
        return '<div class="wl-sp-progress"><span class="wl-sp-progress-fill" data-wl-sp-progress></span></div>';
    }

    /**
     * The closing button. Falls back to the shop page so it is useful the moment the widget is
     * dropped on a page, and disappears entirely when its text is cleared.
     *
     * @param  array $settings
     * @return string
     */
    protected function cta_html( array $settings, array $items = [], $style = '', $variant = '' ) {
        $text  = trim( (string) ( $settings['cta_text'] ?? '' ) );
        $aside = $this->cta_aside_html( $settings, $items, $style, $variant );

        // The note and the faces can stand on their own: a merchant who clears the button still
        // gets the bar, rather than losing the whole thing to an empty field.
        if ( '' === $text ) {
            return '' !== $aside ? '<div class="wl-sp-foot">' . $aside . '</div>' : '';
        }

        $url = trim( (string) ( $settings['cta_link']['url'] ?? '' ) );

        if ( '' === $url && function_exists( 'wc_get_page_permalink' ) ) {
            $url = (string) wc_get_page_permalink( 'shop' );
        }

        if ( '' === $url ) {
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

        return '<div class="wl-sp-foot">' . $aside
            . '<a class="wl-sp-cta" href="' . esc_url( $url ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>'
            . esc_html( $text ) . $this->icon( 'right' ) . '</a></div>';
    }

    /**
     * The closing bar's left-hand side: a few overlapping reviewer photos and a line of copy.
     *
     * The faces are the reviewers already on the page rather than a second image field — the
     * bar says "join these people", so borrowing anyone else's photographs would be a fiction.
     * Reviews with no picture are skipped instead of leaving a hole in the cluster.
     *
     * @param  array  $settings
     * @param  array  $items
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function cta_aside_html( array $settings, array $items, $style, $variant ) {
        if ( ! $this->in_map( self::CTA_ASIDE_VARIANTS, $style, $variant ) ) {
            return '';
        }

        $note  = trim( (string) ( $settings['cta_note'] ?? '' ) );
        $faces = '';
        $shown = 0;

        foreach ( $items as $item ) {
            if ( $shown >= 4 ) {
                break;
            }

            if ( '' === (string) ( $item['avatar'] ?? '' ) ) {
                continue;
            }

            $faces .= '<span class="wl-sp-face">' . $item['avatar'] . '</span>';
            $shown++;
        }

        if ( '' === $note && '' === $faces ) {
            return '';
        }

        return '<div class="wl-sp-foot-aside">'
            . ( '' !== $faces ? '<span class="wl-sp-faces" aria-hidden="true">' . $faces . '</span>' : '' )
            . ( '' !== $note ? '<span class="wl-sp-note">' . wp_kses( $note, [ 'em' => [], 'strong' => [] ] ) . '</span>' : '' )
            . '</div>';
    }

    /**
     * The `data-wl-slider` attribute pair that hands this section to the shared Slick
     * initialiser, exactly as Hero Banner and the other pack sliders do.
     *
     * A spotlight is the same slider with one slide showing, no arrows of Slick's own (its
     * picker provides them) and fade allowed; a carousel is the multi-slide case.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function slider_attr( array $settings, $style, $variant ) {
        if ( ! $this->in_map( self::SLIDER_VARIANTS, $style, $variant ) ) {
            return '';
        }

        $spotlight = $this->in_map( self::SPOTLIGHT_VARIANTS, $style, $variant );

        $args = [
            // Slick's own arrows stay off on every variant. Each pack places its controls where
            // its reference puts them — a header row, a filmstrip, a counter — and Slick's are
            // pinned to the slider's own edges, so letting it draw a set would give the shopper
            // two controls for one job. The `slider_arrows` switch governs the template's pair,
            // which calls slickPrev/slickNext; everything else about the slider is still Slick's.
            'arrows'         => false,
            'dots'           => ! $spotlight && 'yes' === ( $settings['slider_dots'] ?? '' ),
            'infinite'       => 'yes' === ( $settings['slider_infinite'] ?? 'yes' ),
            'fade'           => $spotlight && 'yes' === ( $settings['slider_fade'] ?? '' ),
            'autoplay'       => 'yes' === ( $settings['slider_autoplay'] ?? '' ),
            'autoplay_speed' => max( 500, (int) ( $settings['slider_autoplay_speed'] ?? 5000 ) ),
            'speed'          => max( 100, (int) ( $settings['slider_speed'] ?? 600 ) ),
            'pause_on_hover' => 'yes' === ( $settings['slider_pause_on_hover'] ?? 'yes' ),
            'items'          => $spotlight ? 1 : max( 1, (int) ( $settings['sl_items'] ?? 3 ) ),
            'scroll'         => $spotlight ? 1 : max( 1, (int) ( $settings['sl_scroll'] ?? 1 ) ),
            'tablet_width'   => max( 480, (int) ( $settings['sl_tablet_width'] ?? 1024 ) ),
            'tablet_items'   => $spotlight ? 1 : max( 1, (int) ( $settings['sl_tablet_items'] ?? 2 ) ),
            'tablet_scroll'  => $spotlight ? 1 : max( 1, (int) ( $settings['sl_tablet_scroll'] ?? 1 ) ),
            'mobile_width'   => max( 320, (int) ( $settings['sl_mobile_width'] ?? 767 ) ),
            'mobile_items'   => 1,
            'mobile_scroll'  => 1,
        ];

        return ' data-wl-slider="true" data-slider-settings=\'' . esc_attr( wp_json_encode( $args ) ) . '\'';
    }

    /**
     * Everything the page builder needs to hear and a shopper must not.
     *
     * An empty section renders nothing on the front end — a shop with no reviews yet must not get
     * a row of hollow stars — so without this the editor would see a blank space and no reason
     * for it.
     *
     * @param  array $items
     * @param  int   $dropped
     * @param  array $settings
     * @return void
     */
    private function editor_notices( array $items, $dropped, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return;
        }

        if ( empty( $items ) ) {
            $source = (string) ( $settings['source'] ?? 'both' );
            $why    = 'manual' === $source
                ? esc_html__( 'Nothing to show yet — add a testimonial under Content › Testimonials.', 'woolentor' )
                : esc_html__( 'Nothing to show yet. Either no approved WooCommerce reviews match the filters, or none exist. Approve reviews under Products › Reviews, or lower the Minimum Rating.', 'woolentor' );

            echo '<p class="elementor-alert elementor-alert-info">' . esc_html( $why ) . '</p>';
            return;
        }

        if ( $dropped ) {
            echo '<p class="elementor-alert elementor-alert-warning">'
                . esc_html( sprintf(
                    /* translators: %d: number of reviews */
                    _n(
                        '%d review was skipped — the product it is about is missing or unpublished.',
                        '%d reviews were skipped — the products they are about are missing or unpublished.',
                        $dropped,
                        'woolentor'
                    ),
                    $dropped
                ) )
                . '</p>';
        }
    }

    /**
     * Render the real template so a Pro variant can be seen in the editor before upgrading. The
     * frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Social Proof' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Social Proof' );
            return;
        }

        $dropped = 0;
        $items   = $this->build_items( $settings, $dropped );

        if ( ! $items ) {
            $this->editor_notices( $items, $dropped, $settings );
            return;
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-sp wl-sp-' . esc_attr( $style ) . ' wl-sp-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-sp>';
        include $template;
        echo '</div>';
        echo '<div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.78);color:#fff;'
            . 'padding:4px 10px;border-radius:3px;font-size:11px;font-weight:700;pointer-events:none;z-index:99;">'
            . esc_html__( 'Pro — Preview Only', 'woolentor' )
            . '</div>';
        echo '</div>';
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

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Social Proof template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $dropped = 0;
        $items   = $this->build_items( $settings, $dropped );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';

        if ( $items ) {
            echo '<div class="wl-sp wl-sp-' . esc_attr( $style ) . ' wl-sp-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '"'
                . ' data-wl-sp>';
            include $template;
            echo '</div>';
        }

        $this->editor_notices( $items, $dropped, $settings );

        echo '</div>';
    }
}
