<?php
/**
 * Blog Posts Widget.
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Blog_Posts_Widget extends Widget_Base {

    /** Layout role: three equal cards in a row. */
    const GRID_VARIANTS = [
        'modern'    => [ 'v1', 'v2' ],
        'editorial' => [ 'v1', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Layout role: one story large, the rest as a list beside it. */
    const LEAD_VARIANTS = [
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v3' ],
        'magazine'  => [ 'v1' ],
    ];

    /** Layout role: full-width stacked rows. */
    const INDEX_VARIANTS = [
        'modern'   => [ 'v3' ],
        'magazine' => [ 'v2' ],
    ];

    /** Variants whose header carries a description line. Only Modern V1's reference has one. */
    const DESC_VARIANTS = [
        'modern' => [ 'v1' ],
    ];

    /**
     * Variants whose header carries an "All articles" link or button. Editorial V2 and Luxury V2
     * centre their header and have none.
     */
    const HEAD_CTA_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v3' ],
        'luxury'    => [ 'v1', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** The centred button under the grid. Magazine V3's reference is the only one with it. */
    const FOOT_CTA_VARIANTS = [
        'magazine' => [ 'v3' ],
    ];

    /**
     * Variants whose items are cards — a surface, a border or ring, a radius. Editorial V1/V3 and
     * Magazine V3 are bare columns; the index rows are ruled lines; Luxury V3's plate is its lead
     * alone, so a card background would also paint its type-only entries.
     */
    const CARD_VARIANTS = [
        'modern'   => [ 'v1', 'v2' ],
        'luxury'   => [ 'v1', 'v2' ],
        'magazine' => [ 'v1' ],
    ];

    /** Variants whose list rows are parted by a hairline. */
    const ROW_RULE_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v3' ],
        'magazine'  => [ 'v2' ],
    ];

    /** Variants whose list rows carry a thumbnail beside the copy. */
    const THUMB_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v2' ],
        'magazine'  => [ 'v1', 'v2' ],
    ];

    /**
     * What each variant's item draws, in order — transcribed from the references (§4.1 of the
     * plan) and the three designed variants (§6). Lead variants describe the lead story and the
     * list entries separately.
     *
     * The part maps the controls are gated on are derived from this by part_map(), the way
     * Social Proof derives its picker maps from PICKER_MODE — so a control cannot be shown on a
     * variant that does not draw its part, and markup and controls cannot drift apart.
     *
     * Tokens:
     *   media[ … ]   the image link; may hold overlays: badge, read, num
     *   body[ … ]    the text column
     *   meta[ … ]    one line of small items parted by dots: cat, date, read, by
     *   author[ … ]  a byline row: avatar, name, by, date, read
     *   foot[ … ]    a row split left/right: author[ … ] and link
     *   cat · badge · date · date_icon · read · title · excerpt · tags · link · button · num · divider
     */
    const LAYOUT = [
        'modern' => [
            'v1' => [ 'item' => [ [ 'media' => [] ], [ 'body' => [ [ 'meta' => [ 'date', 'read' ] ], 'title', 'excerpt', 'tags' ] ] ] ],
            'v2' => [ 'item' => [ [ 'media' => [ 'badge' ] ], [ 'body' => [ [ 'meta' => [ 'date', 'read' ] ], 'title', 'excerpt', [ 'foot' => [ [ 'author' => [ 'avatar', 'name' ] ], 'link' ] ] ] ] ] ],
            'v3' => [ 'item' => [ [ 'media' => [] ], [ 'body' => [ 'cat', 'title', 'excerpt' ] ], [ 'meta' => [ 'date', 'read' ] ] ] ],
        ],
        'editorial' => [
            'v1' => [ 'item' => [ [ 'media' => [] ], [ 'body' => [ 'cat', 'title', 'excerpt', [ 'meta' => [ 'by', 'read' ] ] ] ] ] ],
            'v2' => [
                'lead' => [ [ 'media' => [ 'badge' ] ], [ 'body' => [ [ 'meta' => [ 'date', 'read' ] ], 'title', 'excerpt', 'link' ] ] ],
                'item' => [ [ 'media' => [] ], [ 'body' => [ [ 'meta' => [ 'cat', 'date' ] ], 'title', 'excerpt' ] ] ],
            ],
            'v3' => [ 'item' => [ [ 'media' => [] ], [ 'body' => [ [ 'meta' => [ 'cat', 'date' ] ], 'title', 'excerpt', 'link' ] ] ] ],
        ],
        'luxury' => [
            'v1' => [ 'item' => [ [ 'media' => [ 'badge', 'read' ] ], [ 'body' => [ [ 'meta' => [ 'date' ] ], 'title', 'excerpt', [ 'foot' => [ [ 'author' => [ 'avatar', 'name' ] ], 'link' ] ] ] ] ] ],
            'v2' => [ 'item' => [ [ 'media' => [ 'badge' ] ], [ 'body' => [ [ 'meta' => [ 'read' ] ], 'title', 'excerpt', 'button' ] ] ] ],
            'v3' => [
                'lead' => [ [ 'media' => [] ], [ 'body' => [ 'cat', 'title', 'excerpt', [ 'author' => [ 'avatar', 'name' ] ] ] ] ],
                'item' => [ [ 'body' => [ 'cat', 'title', [ 'meta' => [ 'date', 'read' ] ] ] ] ],
            ],
        ],
        'magazine' => [
            'v1' => [
                'lead' => [ [ 'media' => [] ], [ 'body' => [ [ 'meta' => [ 'cat', 'read' ] ], 'title', 'excerpt', [ 'author' => [ 'avatar', 'by', 'date' ] ] ] ] ],
                'item' => [ [ 'media' => [] ], [ 'body' => [ 'cat', 'title', 'excerpt', [ 'author' => [ 'avatar', 'by', 'date', 'read' ] ] ] ] ],
            ],
            'v2' => [ 'item' => [ 'num', [ 'media' => [] ], [ 'body' => [ 'cat', 'title' ] ], [ 'meta' => [ 'date', 'read' ] ] ] ],
            'v3' => [ 'item' => [ [ 'media' => [] ], [ 'body' => [ 'date_icon', 'title', 'divider', [ 'author' => [ 'avatar', 'name' ] ] ] ] ] ],
        ],
    ];

    /** Which part tokens each Display switch governs. */
    const SWITCH_PARTS = [
        'show_image'     => [ 'media' ],
        'show_category'  => [ 'cat', 'badge' ],
        'show_date'      => [ 'date', 'date_icon' ],
        'show_read_time' => [ 'read' ],
        'show_author'    => [ 'avatar', 'name', 'by' ],
        'show_excerpt'   => [ 'excerpt' ],
        'show_tags'      => [ 'tags' ],
    ];

    /** How many pages the last query_posts() call can page through. */
    protected $max_pages = 1;

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
        $this->register_pack_script();
    }

    /**
     * The Load More script. Registered only — render() enqueues it when, and only when, a widget
     * on the page has Load More switched on, which itself needs Pro.
     */
    private function register_pack_script() {
        if ( ! wp_script_is( 'wl-pack-blog-posts', 'registered' ) ) {
            wp_register_script(
                'wl-pack-blog-posts',
                WOOLENTOR_ADDONS_PL_URL . 'assets/pack-widgets/js/blog-posts.js',
                [],
                WOOLENTOR_VERSION,
                true
            );
        }
    }

    /**
     * One stylesheet per pack, each depending on the shared base so it always loads after it and
     * wins on equal specificity. No script: every layout is static (Load More, Pro, registers its
     * own only when switched on).
     */
    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-blog-posts-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/blog-posts/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-blog-posts-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_name() {
        return 'woolentor-blog-posts';
    }

    public function get_title() {
        return esc_html__( 'Blog Posts - 2026', 'woolentor' );
    }

    public function get_icon() {
        // Verified present in the bundled Elementor's eicons.
        return 'woolentor-widget-new-icon eicon-post-list';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'blog', 'posts', 'post', 'journal', 'news', 'articles', 'article', 'stories',
                 'pack', 'style', 'woolentor' ];
    }

    /**
     * Style => locked variants. Every pack's v2 and v3 require Pro.
     *
     * The Pro mirror rewrites this body to return an empty array; everything downstream then
     * neutralises itself, exactly as in Social Proof.
     */
    private function get_pro_map() {
        $map = [];
        foreach ( \WooLentor\Style_Pack_Manager::get_pack_slugs() as $slug ) {
            $map[ $slug ] = [ 'v2', 'v3' ];
        }
        return $map;
    }

    /**
     * Whether this build offers Pagination and Load More. Free returns false; the Pro mirror
     * rewrites the body to return true.
     */
    private function has_pagination() {
        return false;
    }

    /**
     * Where this build's templates live. The Pro mirror overrides it to the free plugin's copy —
     * all twelve templates and all four stylesheets ship in free.
     *
     * @return string
     */
    protected function templates_dir() {
        return __DIR__ . '/templates';
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
     * An empty AND group — build_negated_conditions( [] ) with Pro active — is dropped. It is
     * always true, so dropping it changes nothing, but nested it would break Conditions::check():
     * that only recurses when 'terms' is non-empty, and otherwise reads the group as a leaf term
     * with no 'name' or 'value', raising "Undefined array key" warnings on CSS generation.
     *
     * @param  array ...$groups
     * @return array
     */
    private function all_of( ...$groups ) {
        $groups = array_filter( $groups, function ( $group ) {
            $is_and = ! isset( $group['relation'] ) || 'or' !== $group['relation'];
            return ! ( isset( $group['terms'] ) && empty( $group['terms'] ) && $is_and );
        } );

        return [ 'relation' => 'and', 'terms' => array_values( $groups ) ];
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
     * The style => variants map of every variant whose LAYOUT draws any of the given part tokens.
     *
     * @param  string ...$tokens
     * @return array
     */
    protected function part_map( ...$tokens ) {
        $map = [];

        foreach ( self::LAYOUT as $style => $variants ) {
            foreach ( $variants as $variant => $layout ) {
                $found = array_intersect( $tokens, $this->layout_tokens( $layout ) );
                if ( $found ) {
                    $map[ $style ][] = $variant;
                }
            }
        }

        return $map;
    }

    /**
     * Every token a layout fragment uses, containers included.
     *
     * @param  array $parts
     * @return string[]
     */
    protected function layout_tokens( array $parts ) {
        $tokens = [];

        foreach ( $parts as $key => $part ) {
            if ( is_string( $key ) && ! in_array( $key, [ 'item', 'lead' ], true ) ) {
                $tokens[] = $key;
            }
            if ( is_array( $part ) ) {
                $tokens = array_merge( $tokens, $this->layout_tokens( $part ) );
            } elseif ( is_string( $part ) ) {
                $tokens[] = $part;
            }
        }

        return array_values( array_unique( $tokens ) );
    }

    /**
     * Variants that draw a visible separator dot: those with a meta line or byline holding two or
     * more items. Editorial V2 is taken out — it renders the dots but its reference hides them,
     * setting each fact in its own outlined tab instead.
     *
     * @return array
     */
    protected function sep_map() {
        $map = [];

        $walk = function ( array $parts ) use ( &$walk ) {
            foreach ( $parts as $part ) {
                if ( ! is_array( $part ) ) {
                    continue;
                }
                $container = key( $part );
                $children  = current( $part );
                if ( in_array( $container, [ 'meta', 'author' ], true ) && count( array_diff( $children, [ 'avatar' ] ) ) > 1 ) {
                    return true;
                }
                if ( $walk( $children ) ) {
                    return true;
                }
            }
            return false;
        };

        foreach ( self::LAYOUT as $style => $variants ) {
            foreach ( $variants as $variant => $layout ) {
                if ( 'editorial' === $style && 'v2' === $variant ) {
                    continue;
                }
                if ( $walk( $layout['item'] ) || ( isset( $layout['lead'] ) && $walk( $layout['lead'] ) ) ) {
                    $map[ $style ][] = $variant;
                }
            }
        }

        return $map;
    }

    /**
     * The map a Display switch is gated on.
     *
     * @param  string $switch
     * @return array
     */
    protected function switch_map( $switch ) {
        return $this->part_map( ...self::SWITCH_PARTS[ $switch ] );
    }

    /**
     * This variant's layout: [ 'item' => [...], 'lead' => [...] ] — 'lead' only on lead variants.
     *
     * @param  string $style
     * @param  string $variant
     * @return array
     */
    protected function layout( $style, $variant ) {
        return self::LAYOUT[ $style ][ $variant ] ?? self::LAYOUT['modern']['v1'];
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

            // Guarded rather than cut by the mirror: with Pro active get_pro_map() is empty and
            // there is nothing to upsell.
            if ( $this->get_pro_map() ) {
                woolentor_upgrade_pro_notice(
                    $this,
                    'blog_posts_variant_pro_notice',
                    [ 'variant' => [ 'v2', 'v3' ] ],
                    [ 'mode' => 'alert' ]
                );
            }

        $this->end_controls_section();

        $this->register_source_controls();
        $this->register_header_controls();
        $this->register_display_controls();
        $this->register_footer_controls();
        $this->register_pagination_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content: Source ───────────────────────────────────────────────────────

    /**
     * Public post types the merchant can list. Attachments and the builders' own template types
     * (Elementor's and ours) are public but are not articles.
     *
     * @return array  slug => label
     */
    protected function post_type_options() {
        $options = [];
        $skip    = [ 'attachment', 'elementor_library', 'e-landing-page', 'e-floating-buttons', 'woolentor-template' ];

        foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $slug => $object ) {
            if ( in_array( $slug, $skip, true ) ) {
                continue;
            }
            $options[ $slug ] = $object->labels->name;
        }

        return $options;
    }

    /**
     * The filterable taxonomies of a post type — public ones with an admin screen, minus post
     * formats, which are presentation rather than subject. "Public" keeps out internal
     * taxonomies such as WPML's translation_priority, which has an admin screen but would
     * otherwise print "Optional" as a page's category.
     *
     * @param  string $post_type
     * @return \WP_Taxonomy[]  slug => object
     */
    protected function post_type_taxonomies( $post_type ) {
        $taxonomies = [];

        foreach ( get_object_taxonomies( $post_type, 'objects' ) as $slug => $taxonomy ) {
            if ( $taxonomy->public && $taxonomy->show_ui && 'post_format' !== $slug ) {
                $taxonomies[ $slug ] = $taxonomy;
            }
        }

        return $taxonomies;
    }

    /**
     * Every term of a post type's taxonomies, keyed "taxonomy:term_id" so one control can carry
     * a category and a tag — or whatever a CPT uses — side by side.
     *
     * @param  string $post_type
     * @return array
     */
    protected function term_options( $post_type ) {
        $taxonomies = $this->post_type_taxonomies( $post_type );
        $options    = [];

        foreach ( $taxonomies as $slug => $taxonomy ) {
            $terms = get_terms( [ 'taxonomy' => $slug, 'hide_empty' => true ] );

            if ( is_wp_error( $terms ) ) {
                continue;
            }

            foreach ( $terms as $term ) {
                // Name the taxonomy only when there is more than one, or "News" the category and
                // "News" the tag would be indistinguishable.
                $options[ $slug . ':' . $term->term_id ] = count( $taxonomies ) > 1
                    ? sprintf( '%1$s (%2$s)', $term->name, $taxonomy->labels->singular_name )
                    : $term->name;
            }
        }

        return $options;
    }

    private function register_source_controls() {
        $this->start_controls_section( 'section_source', [
            'label'      => esc_html__( 'Source', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $post_types = $this->post_type_options();

            $this->add_control( 'post_type', [
                'label'   => esc_html__( 'Post Type', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'post',
                'options' => $post_types,
            ] );

            // One term picker per post type, each shown only for its own type. Elementor cannot
            // repopulate a select from another control's value, so this is how the picker
            // "follows" the post type without any script. A type with no taxonomy gets none.
            foreach ( array_keys( $post_types ) as $post_type ) {
                if ( ! $this->post_type_taxonomies( $post_type ) ) {
                    continue;
                }

                $this->add_control( 'terms_' . $post_type, [
                    'label'       => esc_html__( 'Only From', 'woolentor' ),
                    'type'        => Controls_Manager::SELECT2,
                    'multiple'    => true,
                    'label_block' => true,
                    'options'     => $this->term_options( $post_type ),
                    'description' => esc_html__( 'Leave empty to show every post. Posts in any of the chosen terms are shown.', 'woolentor' ),
                    'condition'   => [ 'post_type' => $post_type ],
                ] );
            }

            $this->add_control( 'posts_count', [
                'label'   => esc_html__( 'Number of Posts', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 3,
                'min'     => 1,
                'max'     => 24,
            ] );

            $this->add_control( 'offset', [
                'label'       => esc_html__( 'Skip First', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 0,
                'min'         => 0,
                'description' => esc_html__( 'Skips this many posts from the start of the list.', 'woolentor' ),
            ] );

            $this->add_control( 'orderby', [
                'label'   => esc_html__( 'Order By', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'          => esc_html__( 'Published date', 'woolentor' ),
                    'modified'      => esc_html__( 'Last updated', 'woolentor' ),
                    'title'         => esc_html__( 'Title', 'woolentor' ),
                    'comment_count' => esc_html__( 'Most commented', 'woolentor' ),
                    'menu_order'    => esc_html__( 'Menu order', 'woolentor' ),
                    'rand'          => esc_html__( 'Random', 'woolentor' ),
                ],
            ] );

            $this->add_control( 'order', [
                'label'     => esc_html__( 'Order', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'DESC',
                'options'   => [
                    'DESC' => esc_html__( 'Descending', 'woolentor' ),
                    'ASC'  => esc_html__( 'Ascending', 'woolentor' ),
                ],
                'condition' => [ 'orderby!' => 'rand' ],
            ] );

            $this->add_control( 'exclude_current', [
                'label'        => esc_html__( 'Exclude Current Post', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'return_value' => 'yes',
                'description'  => esc_html__( 'On a single post, leaves out the post being read.', 'woolentor' ),
            ] );

            $this->add_control( 'ignore_sticky', [
                'label'        => esc_html__( 'Ignore Sticky Posts', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => '',
                'return_value' => 'yes',
                'description'  => esc_html__( 'Sticky posts are only pinned to the top on the blog home page. Elsewhere this changes nothing.', 'woolentor' ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Header ───────────────────────────────────────────────────────

    private function register_header_controls() {
        $this->start_controls_section( 'section_header', [
            'label'      => esc_html__( 'Header', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $this->add_control( 'eyebrow', [
                'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'From the Journal', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'heading', [
                'label'       => esc_html__( 'Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                // Not esc_html__(): the accent tag is the point, and header_html() runs it through
                // wp_kses on output.
                'default'     => __( 'Stories & <em>Notes</em>', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Accepts <em>, <strong> and <br> for an accent word.', 'woolentor' ),
            ] );

            $this->add_control( 'description', [
                'label'      => esc_html__( 'Description', 'woolentor' ),
                'type'       => Controls_Manager::TEXTAREA,
                'rows'       => 3,
                'default'    => esc_html__( 'Guides, stories and notes from the people behind the shop.', 'woolentor' ),
                'conditions' => $this->build_conditions( self::DESC_VARIANTS ),
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

            $this->add_control( 'head_cta_text', [
                'label'       => esc_html__( 'Link Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'All articles', 'woolentor' ),
                'label_block' => true,
                'separator'   => 'before',
                'description' => esc_html__( 'Leave empty to hide the link.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::HEAD_CTA_VARIANTS ),
            ] );

            $this->add_control( 'head_cta_link', [
                'label'       => esc_html__( 'Link', 'woolentor' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Defaults to the posts page', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::HEAD_CTA_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Display ──────────────────────────────────────────────────────

    private function register_display_controls() {
        $this->start_controls_section( 'section_display', [
            'label'      => esc_html__( 'Display', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            $switches = [
                'show_image'     => esc_html__( 'Image', 'woolentor' ),
                'show_category'  => esc_html__( 'Category', 'woolentor' ),
                'show_date'      => esc_html__( 'Date', 'woolentor' ),
                'show_read_time' => esc_html__( 'Read Time', 'woolentor' ),
                'show_author'    => esc_html__( 'Author', 'woolentor' ),
                'show_excerpt'   => esc_html__( 'Excerpt', 'woolentor' ),
                'show_tags'      => esc_html__( 'Tags', 'woolentor' ),
            ];

            foreach ( $switches as $id => $label ) {
                $this->add_control( $id, [
                    'label'        => $label,
                    'type'         => Controls_Manager::SWITCHER,
                    'default'      => 'yes',
                    'return_value' => 'yes',
                    'conditions'   => $this->build_conditions( $this->switch_map( $id ) ),
                ] );
            }

            $image = $this->build_conditions( $this->switch_map( 'show_image' ) );

            $this->add_group_control( Group_Control_Image_Size::get_type(), [
                'name'       => 'thumb',
                'default'    => 'medium_large',
                'separator'  => 'before',
                'conditions' => $this->all_of( $image, [ 'terms' => [ [ 'name' => 'show_image', 'operator' => '==', 'value' => 'yes' ] ] ] ),
            ] );

            $this->add_control( 'fallback_image', [
                'label'       => esc_html__( 'Fallback Image', 'woolentor' ),
                'type'        => Controls_Manager::MEDIA,
                'description' => esc_html__( 'Shown for posts with no featured image. Without one, the WooCommerce placeholder is used.', 'woolentor' ),
                'conditions'  => $this->all_of( $image, [ 'terms' => [ [ 'name' => 'show_image', 'operator' => '==', 'value' => 'yes' ] ] ] ),
            ] );

            $this->add_control( 'words', [
                'label'       => esc_html__( 'Excerpt Length', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 18,
                'min'         => 5,
                'max'         => 80,
                'separator'   => 'before',
                'description' => esc_html__( 'In words. A hand-written excerpt is trimmed to this too.', 'woolentor' ),
                'conditions'  => $this->all_of(
                    $this->build_conditions( $this->switch_map( 'show_excerpt' ) ),
                    [ 'terms' => [ [ 'name' => 'show_excerpt', 'operator' => '==', 'value' => 'yes' ] ] ]
                ),
            ] );

            $this->add_control( 'date_format', [
                'label'       => esc_html__( 'Date Format', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => 'F j, Y',
                'description' => esc_html__( 'A PHP date format. Leave empty to use the site\'s own, from Settings › General.', 'woolentor' ),
                'conditions'  => $this->all_of(
                    $this->build_conditions( $this->switch_map( 'show_date' ) ),
                    [ 'terms' => [ [ 'name' => 'show_date', 'operator' => '==', 'value' => 'yes' ] ] ]
                ),
            ] );

            $this->add_control( 'read_time_text', [
                'label'       => esc_html__( 'Read Time Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( '{n} min read', 'woolentor' ),
                'description' => esc_html__( '{n} is replaced by the minutes.', 'woolentor' ),
                'conditions'  => $this->all_of(
                    $this->build_conditions( $this->switch_map( 'show_read_time' ) ),
                    [ 'terms' => [ [ 'name' => 'show_read_time', 'operator' => '==', 'value' => 'yes' ] ] ]
                ),
            ] );

            $this->add_control( 'read_link_text', [
                'label'       => esc_html__( 'Read Link Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Read article', 'woolentor' ),
                'description' => esc_html__( 'Leave empty to hide the link. The title always links to the post.', 'woolentor' ),
                'conditions'  => $this->build_conditions( $this->part_map( 'link', 'button' ) ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Footer ───────────────────────────────────────────────────────

    private function register_footer_controls() {
        $this->start_controls_section( 'section_footer', [
            'label'      => esc_html__( 'Footer Button', 'woolentor' ),
            'conditions' => $this->all_of(
                $this->build_conditions( self::FOOT_CTA_VARIANTS ),
                $this->build_negated_conditions( $this->get_pro_map() )
            ),
        ] );

            $this->add_control( 'foot_cta_text', [
                'label'       => esc_html__( 'Button Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'View the Journal', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Leave empty to hide the button.', 'woolentor' ),
            ] );

            $this->add_control( 'foot_cta_link', [
                'label'       => esc_html__( 'Button Link', 'woolentor' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Defaults to the posts page', 'woolentor' ),
            ] );

        $this->end_controls_section();
    }

    // ── Content: Pagination (Pro) ─────────────────────────────────────────────

    /**
     * Pagination and Load More. In the free build the section holds only an upgrade notice —
     * nothing that could be set and then ignored. The Pro mirror's has_pagination() returns true
     * and the real controls take its place.
     */
    private function register_pagination_controls() {
        $this->start_controls_section( 'section_pagination', [
            'label'      => esc_html__( 'Pagination', 'woolentor' ),
            'conditions' => $this->build_negated_conditions( $this->get_pro_map() ),
        ] );

            if ( ! $this->has_pagination() ) {
                woolentor_upgrade_pro_notice( $this, 'blog_posts_pagination_pro_notice', [], [
                    'title'   => __( 'Pagination & Load More', 'woolentor' ),
                    'message' => __( 'Let readers page through older posts with numbered pages or a Load More button. Available in ShopLentor Pro.', 'woolentor' ),
                ] );
            } else {
                $this->add_control( 'pagination', [
                    'label'       => esc_html__( 'Pagination', 'woolentor' ),
                    'type'        => Controls_Manager::SELECT,
                    'default'     => 'none',
                    'options'     => [
                        'none'      => esc_html__( 'None', 'woolentor' ),
                        'numbers'   => esc_html__( 'Numbered pages', 'woolentor' ),
                        'load_more' => esc_html__( 'Load More button', 'woolentor' ),
                    ],
                    'description' => esc_html__( 'Each page shows "Number of Posts" posts. Nothing is added when every post already fits on the first page.', 'woolentor' ),
                ] );

                $this->add_control( 'load_more_text', [
                    'label'     => esc_html__( 'Button Text', 'woolentor' ),
                    'type'      => Controls_Manager::TEXT,
                    'default'   => esc_html__( 'Load more', 'woolentor' ),
                    'condition' => [ 'pagination' => 'load_more' ],
                ] );

                $this->add_control( 'loading_text', [
                    'label'     => esc_html__( 'Loading Text', 'woolentor' ),
                    'type'      => Controls_Manager::TEXT,
                    'default'   => esc_html__( 'Loading…', 'woolentor' ),
                    'condition' => [ 'pagination' => 'load_more' ],
                ] );

                $this->add_control( 'no_more_text', [
                    'label'       => esc_html__( 'End Text', 'woolentor' ),
                    'type'        => Controls_Manager::TEXT,
                    'default'     => esc_html__( 'No more posts', 'woolentor' ),
                    'description' => esc_html__( 'Replaces the button once the last posts have loaded.', 'woolentor' ),
                    'condition'   => [ 'pagination' => 'load_more' ],
                ] );
            }

        $this->end_controls_section();
    }

    // ── Pro notices ───────────────────────────────────────────────────────────

    private function register_pro_content_notice() {
        if ( ! $this->get_pro_map() ) {
            return;
        }

        $this->start_controls_section( 'section_pro_notice', [
            'label'      => esc_html__( 'Content', 'woolentor' ),
            'conditions' => $this->build_conditions( $this->get_pro_map() ),
        ] );
            woolentor_upgrade_pro_notice( $this, 'blog_posts_content_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ] );
        $this->end_controls_section();
    }

    // ── Style ─────────────────────────────────────────────────────────────────

    /**
     * The Style tab.
     */
    private function register_style_controls() {
        $editable = $this->build_negated_conditions( $this->get_pro_map() );

        // A section shown only on the variants in $map, only while $switch (if any) is on.
        $gate = function ( array $map, $switch = '' ) use ( $editable ) {
            $groups = [ $this->build_conditions( $map ), $editable ];
            if ( $switch ) {
                $groups[] = [ 'terms' => [ [ 'name' => $switch, 'operator' => '==', 'value' => 'yes' ] ] ];
            }
            return $this->all_of( ...$groups );
        };

        $section = function ( $id, $label, array $conditions ) {
            $this->start_controls_section( $id, [
                'label'      => $label,
                'tab'        => Controls_Manager::TAB_STYLE,
                'conditions' => $conditions,
            ] );
        };

        $end = function () {
            $section_id = $this->get_current_section()['section'] ?? '';
            $this->end_controls_section();

            $sections = $this->get_controls();
            $gate     = $sections[ $section_id ]['conditions'] ?? [];
            if ( ! $gate ) {
                return;
            }

            foreach ( $this->get_controls() as $control_id => $control ) {
                if ( ( $control['section'] ?? '' ) !== $section_id ) {
                    continue;
                }
                $own = $control['conditions'] ?? [];
                $this->update_control( $control_id, [
                    'conditions' => $own ? $this->all_of( $gate, $own ) : $gate,
                ] );
            }
        };

        $color = function ( $id, $label, $selector, $property = 'color', array $extra = [] ) {
            $this->add_control( $id, array_merge( [
                'label'     => $label,
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $selector => $property . ': {{VALUE}};' ],
            ], $extra ) );
        };

        $type = function ( $name, $selector, array $extra = [] ) {
            $this->add_group_control( Group_Control_Typography::get_type(), array_merge( [
                'name'     => $name,
                'selector' => $selector,
            ], $extra ) );
        };

        // ── Section ───────────────────────────────────────────────────────────

        $section( 'style_section', esc_html__( 'Section', 'woolentor' ), $editable );

            $color( 'section_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-bp', 'background-color' );

            $this->add_responsive_control( 'section_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-bp' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $end();

        // ── Header ────────────────────────────────────────────────────────────

        $section( 'style_header', esc_html__( 'Header', 'woolentor' ), $editable );

            $this->add_responsive_control( 'header_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-bp-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

            $color( 'eyebrow_color', esc_html__( 'Eyebrow Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-eyebrow', 'color', [ 'separator' => 'before' ] );
            $type( 'eyebrow_typography', '{{WRAPPER}} .wl-bp-eyebrow' );

            $color( 'heading_color', esc_html__( 'Heading Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-heading', 'color', [ 'separator' => 'before' ] );
            $color( 'heading_accent_color', esc_html__( 'Accent Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-heading em, {{WRAPPER}} .wl-bp-heading strong', 'color', [
                'description' => esc_html__( 'For the words wrapped in <em> or <strong> in the heading.', 'woolentor' ),
            ] );
            $type( 'heading_typography', '{{WRAPPER}} .wl-bp-heading' );

            $color( 'desc_color', esc_html__( 'Description Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-desc', 'color', [
                'separator'  => 'before',
                'conditions' => $this->build_conditions( self::DESC_VARIANTS ),
            ] );
            $type( 'desc_typography', '{{WRAPPER}} .wl-bp-desc', [ 'conditions' => $this->build_conditions( self::DESC_VARIANTS ) ] );

        $end();

        // ── Layout ────────────────────────────────────────────────────────────

        $section( 'style_layout', esc_html__( 'Layout', 'woolentor' ), $gate( $this->merge_maps( self::GRID_VARIANTS, self::LEAD_VARIANTS ) ) );

            $this->add_control( 'columns', [
                'label'       => esc_html__( 'Columns', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'options'     => [ '' => esc_html__( 'Default', 'woolentor' ), '2' => '2', '3' => '3', '4' => '4' ],
                'description' => esc_html__( 'On desktop. Tablets show two, phones one.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-bp' => '--wl-bp-cols: repeat({{VALUE}}, minmax(0, 1fr));' ],
                'conditions'  => $this->build_conditions( self::GRID_VARIANTS ),
            ] );

            $this->add_responsive_control( 'grid_gap', [
                'label'      => esc_html__( 'Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-bp' => '--wl-bp-gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $end();

        // ── Card ──────────────────────────────────────────────────────────────

        $section( 'style_card', esc_html__( 'Card', 'woolentor' ), $gate( self::CARD_VARIANTS ) );

            $color( 'card_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-bp-item', 'background-color' );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .wl-bp-item',
            ] );

            $this->add_responsive_control( 'card_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-bp-item' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .wl-bp-item',
            ] );

            $this->add_responsive_control( 'card_padding', [
                'label'      => esc_html__( 'Content Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-bp-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $end();

        // ── Image ─────────────────────────────────────────────────────────────

        $section( 'style_image', esc_html__( 'Image', 'woolentor' ), $gate( $this->switch_map( 'show_image' ), 'show_image' ) );

            $this->add_responsive_control( 'image_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ], '%' => [ 'min' => 0, 'max' => 50 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-bp-media' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'thumb_width', [
                'label'       => esc_html__( 'Thumbnail Width', 'woolentor' ),
                'type'        => Controls_Manager::SLIDER,
                'size_units'  => [ 'px' ],
                'range'       => [ 'px' => [ 'min' => 48, 'max' => 260 ] ],
                'description' => esc_html__( 'The image beside each row. Phones use a smaller one.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-bp' => '--wl-bp-thumb: {{SIZE}}{{UNIT}};' ],
                'conditions'  => $this->build_conditions( self::THUMB_VARIANTS ),
            ] );

        $end();

        // ── Row divider ───────────────────────────────────────────────────────

        $section( 'style_rows', esc_html__( 'Row Divider', 'woolentor' ), $gate( self::ROW_RULE_VARIANTS ) );

            $color( 'row_rule_color', esc_html__( 'Line Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-list > .wl-bp-item', 'border-color' );

        $end();

        // ── Badge ─────────────────────────────────────────────────────────────

        $section( 'style_badge', esc_html__( 'Image Badge', 'woolentor' ), $gate( $this->part_map( 'badge' ), 'show_category' ) );

            $color( 'badge_color', esc_html__( 'Text Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-badge' );
            $color( 'badge_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-bp-badge', 'background-color' );
            $type( 'badge_typography', '{{WRAPPER}} .wl-bp-badge' );

        $end();

        // ── Category ──────────────────────────────────────────────────────────

        $section( 'style_category', esc_html__( 'Category', 'woolentor' ), $gate( $this->part_map( 'cat' ), 'show_category' ) );

            $color( 'cat_color', esc_html__( 'Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-cat' );
            $type( 'cat_typography', '{{WRAPPER}} .wl-bp-cat' );

        $end();

        // ── Title ─────────────────────────────────────────────────────────────

        $section( 'style_title', esc_html__( 'Title', 'woolentor' ), $editable );

            $color( 'title_color', esc_html__( 'Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-title' );
            $color(
                'title_hover_color',
                esc_html__( 'Hover Color', 'woolentor' ),
                '{{WRAPPER}} .wl-bp-item:hover .wl-bp-title, {{WRAPPER}} .wl-bp-title a:hover, {{WRAPPER}} .wl-bp-title a:focus-visible'
            );
            $type( 'title_typography', '{{WRAPPER}} .wl-bp-title' );

        $end();

        // ── Excerpt ───────────────────────────────────────────────────────────

        $section( 'style_excerpt', esc_html__( 'Excerpt', 'woolentor' ), $gate( $this->switch_map( 'show_excerpt' ), 'show_excerpt' ) );

            $color( 'excerpt_color', esc_html__( 'Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-excerpt' );
            $type( 'excerpt_typography', '{{WRAPPER}} .wl-bp-excerpt' );

        $end();

        // ── Meta ──────────────────────────────────────────────────────────────
        $meta = '{{WRAPPER}} .wl-bp-meta, {{WRAPPER}} .wl-bp-meta .wl-bp-date, {{WRAPPER}} .wl-bp-meta .wl-bp-read, '
            . '{{WRAPPER}} .wl-bp-byline, {{WRAPPER}} .wl-bp-byline .wl-bp-date, {{WRAPPER}} .wl-bp-byline .wl-bp-read, '
            . '{{WRAPPER}} .wl-bp-by, {{WRAPPER}} .wl-bp-body > .wl-bp-date';

        $section( 'style_meta', esc_html__( 'Date & Read Time', 'woolentor' ), $gate( $this->part_map( 'date', 'date_icon', 'read', 'by' ) ) );

            $color( 'meta_color', esc_html__( 'Color', 'woolentor' ), $meta );
            $type( 'meta_typography', $meta );
            $color( 'meta_sep_color', esc_html__( 'Separator Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-sep', 'background-color', [
                'description' => esc_html__( 'The small dot between items on the line.', 'woolentor' ),
                'conditions'  => $this->build_conditions( $this->sep_map() ),
            ] );

        $end();

        // ── Author ────────────────────────────────────────────────────────────

        $section( 'style_author', esc_html__( 'Author', 'woolentor' ), $gate( $this->switch_map( 'show_author' ), 'show_author' ) );

            $color( 'author_color', esc_html__( 'Name Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-name' );
            $type( 'author_typography', '{{WRAPPER}} .wl-bp-name' );

            $this->add_responsive_control( 'avatar_size', [
                'label'      => esc_html__( 'Photo Size', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 16, 'max' => 80 ] ],
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-bp-author' => '--wl-bp-avatar-size: {{SIZE}}{{UNIT}};' ],
                'conditions' => $this->build_conditions( $this->part_map( 'avatar' ) ),
            ] );

            $color( 'monogram_bg', esc_html__( 'Initial Background', 'woolentor' ), '{{WRAPPER}} .wl-bp-monogram', 'background', [
                'description' => esc_html__( 'Only seen when the site has avatars switched off (Settings › Discussion): each author is then shown by their initial.', 'woolentor' ),
                'conditions'  => $this->build_conditions( $this->part_map( 'avatar' ) ),
            ] );
            $color( 'monogram_color', esc_html__( 'Initial Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-monogram', 'color', [
                'conditions' => $this->build_conditions( $this->part_map( 'avatar' ) ),
            ] );

        $end();

        // ── Tags ──────────────────────────────────────────────────────────────

        $section( 'style_tags', esc_html__( 'Tags', 'woolentor' ), $gate( $this->switch_map( 'show_tags' ), 'show_tags' ) );

            $color( 'tag_color', esc_html__( 'Text Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-tag' );
            $color( 'tag_border_color', esc_html__( 'Border Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-tag', 'border-color' );
            $type( 'tag_typography', '{{WRAPPER}} .wl-bp-tag' );

        $end();

        // ── Number ────────────────────────────────────────────────────────────

        $section( 'style_num', esc_html__( 'Number', 'woolentor' ), $gate( $this->part_map( 'num' ) ) );

            $color( 'num_color', esc_html__( 'Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-num' );
            $type( 'num_typography', '{{WRAPPER}} .wl-bp-num' );

        $end();

        // ── Read link / button ────────────────────────────────────────────────

        $section( 'style_read', esc_html__( 'Read Link', 'woolentor' ), $gate( $this->part_map( 'link', 'button' ) ) );

            $color( 'read_color', esc_html__( 'Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-link' );
            $color(
                'read_hover_color',
                esc_html__( 'Hover Color', 'woolentor' ),
                '{{WRAPPER}} .wl-bp-item:hover .wl-bp-link, {{WRAPPER}} .wl-bp-link:hover, {{WRAPPER}} .wl-bp-link:focus-visible'
            );
            $color( 'read_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-bp-button', 'background-color', [
                'conditions' => $this->build_conditions( $this->part_map( 'button' ) ),
            ] );
            $color( 'read_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), '{{WRAPPER}} .wl-bp-button:hover, {{WRAPPER}} .wl-bp-button:focus-visible', 'background-color', [
                'conditions' => $this->build_conditions( $this->part_map( 'button' ) ),
            ] );
            $type( 'read_typography', '{{WRAPPER}} .wl-bp-link' );

        $end();

        // ── Header link ───────────────────────────────────────────────────────

        $section( 'style_head_cta', esc_html__( 'Header Link', 'woolentor' ), $gate( self::HEAD_CTA_VARIANTS ) );

            $color( 'head_cta_color', esc_html__( 'Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-head .wl-bp-cta' );
            $color( 'head_cta_hover_color', esc_html__( 'Hover Color', 'woolentor' ), '{{WRAPPER}} .wl-bp-head .wl-bp-cta:hover, {{WRAPPER}} .wl-bp-head .wl-bp-cta:focus-visible' );
            $type( 'head_cta_typography', '{{WRAPPER}} .wl-bp-head .wl-bp-cta' );

        $end();

        // ── Footer button ─────────────────────────────────────────────────────

        $section( 'style_foot_cta', esc_html__( 'Footer Button', 'woolentor' ), $gate( self::FOOT_CTA_VARIANTS ) );

            $foot = '{{WRAPPER}} .wl-bp-foot .wl-bp-cta';
            $foot_hover = '{{WRAPPER}} .wl-bp-foot .wl-bp-cta:hover, {{WRAPPER}} .wl-bp-foot .wl-bp-cta:focus-visible';

            $color( 'foot_cta_color', esc_html__( 'Text Color', 'woolentor' ), $foot );
            $color( 'foot_cta_bg', esc_html__( 'Background', 'woolentor' ), $foot, 'background-color' );
            $color( 'foot_cta_border', esc_html__( 'Border Color', 'woolentor' ), $foot, 'border-color' );
            $color( 'foot_cta_hover_color', esc_html__( 'Hover Text Color', 'woolentor' ), $foot_hover, 'color', [ 'separator' => 'before' ] );
            $color( 'foot_cta_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $foot_hover, 'background-color' );
            $type( 'foot_cta_typography', $foot, [ 'separator' => 'before' ] );

        $end();

        // ── Pagination (Pro) ──────────────────────────────────────────────────

        if ( ! $this->has_pagination() ) {
            return;
        }

        $section( 'style_pagination', esc_html__( 'Pagination', 'woolentor' ), $this->all_of(
            $editable,
            [ 'terms' => [ [ 'name' => 'pagination', 'operator' => '!==', 'value' => 'none' ] ] ]
        ) );

            $color( 'pager_color', esc_html__( 'Text Color', 'woolentor' ),
                '{{WRAPPER}} .wl-bp-pager, {{WRAPPER}} .wl-bp-more' );
            $color( 'pager_accent', esc_html__( 'Active Background', 'woolentor' ), '{{WRAPPER}} .wl-bp', '--wl-bp-accent', [
                'description' => esc_html__( 'The current page, and the button on hover.', 'woolentor' ),
            ] );
            $color( 'pager_accent_ink', esc_html__( 'Active Text Color', 'woolentor' ), '{{WRAPPER}} .wl-bp', '--wl-bp-accent-ink' );

            $this->add_responsive_control( 'pager_space', [
                'label'      => esc_html__( 'Spacing Above', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-bp' => '--wl-bp-pager-space: {{SIZE}}{{UNIT}};' ],
            ] );

        $end();
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
            woolentor_upgrade_pro_notice( $this, 'blog_posts_style_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ] );
        $this->end_controls_section();
    }

    // ── Data ──────────────────────────────────────────────────────────────────

    /**
     * The pagination mode in force: 'none' unless this build offers pagination (Pro) and the
     * merchant chose a mode. A page saved with Load More under Pro and viewed after Pro is
     * deactivated therefore renders exactly as 'none'.
     *
     * @param  array $settings
     * @return string  none | numbers | load_more
     */
    protected function pagination_mode( array $settings ) {
        if ( ! $this->has_pagination() ) {
            return 'none';
        }

        $mode = (string) ( $settings['pagination'] ?? 'none' );

        return in_array( $mode, [ 'numbers', 'load_more' ], true ) ? $mode : 'none';
    }

    /**
     * The query-string key numbered pages use. Namespaced by the widget's id, so two Blog Posts
     * widgets on one page page independently.
     *
     * @return string
     */
    protected function page_param() {
        return 'wl_bp_page_' . preg_replace( '/[^a-z0-9]/', '', strtolower( (string) $this->get_id() ) );
    }

    /**
     * The post being read, when the section should leave it out: on a single post of the type
     * this section lists, with "Exclude Current Post" on. 0 otherwise — a page carrying a post
     * list has nothing to leave out of it.
     *
     * @param  array $settings
     * @return int
     */
    protected function current_post_to_exclude( array $settings ) {
        if ( 'yes' !== ( $settings['exclude_current'] ?? 'yes' ) || ! is_singular() ) {
            return 0;
        }

        $id = (int) get_the_ID();

        return get_post_type( $id ) === ( $settings['post_type'] ?? 'post' ) ? $id : 0;
    }

    /**
     * The posts this section lists, as shaped arrays — never WP_Post objects, so the templates
     * cannot reach past the shape. Sets $this->max_pages.
     *
     * @param  array    $settings
     * @param  int      $page     1-based.
     * @param  int|null $exclude  A post id to leave out; null works it out from the request. Load
     *                            More passes the first page's answer, since an AJAX request is
     *                            never singular.
     * @return array[]
     */
    protected function query_posts( array $settings, $page = 1, $exclude = null ) {
        $post_types = $this->post_type_options();
        $post_type  = $settings['post_type'] ?? 'post';
        $post_type  = isset( $post_types[ $post_type ] ) ? $post_type : 'post';

        $orderby = $settings['orderby'] ?? 'date';
        $orderby = in_array( $orderby, [ 'date', 'modified', 'title', 'comment_count', 'menu_order', 'rand' ], true ) ? $orderby : 'date';
        $order   = 'ASC' === ( $settings['order'] ?? 'DESC' ) ? 'ASC' : 'DESC';

        $per_page = max( 1, (int) ( $settings['posts_count'] ?? 3 ) );
        $offset   = max( 0, (int) ( $settings['offset'] ?? 0 ) );
        $page     = max( 1, (int) $page );
        $mode     = $this->pagination_mode( $settings );

        $args = [
            'post_type'           => $post_type,
            'post_status'         => 'publish',
            'posts_per_page'      => $per_page,
            'offset'              => $offset + ( $page - 1 ) * $per_page,
            'orderby'             => $orderby,
            'order'               => $order,
            'ignore_sticky_posts' => 'yes' === ( $settings['ignore_sticky'] ?? '' ),
            'no_found_rows'       => 'none' === $mode,
        ];

        $tax_query = $this->tax_query( $post_type, (array) ( $settings[ 'terms_' . $post_type ] ?? [] ) );
        if ( $tax_query ) {
            $args['tax_query'] = $tax_query;
        }

        $exclude = null === $exclude
            ? $this->current_post_to_exclude( $settings )
            : ( 'yes' === ( $settings['exclude_current'] ?? 'yes' ) ? absint( $exclude ) : 0 );

        if ( $exclude ) {
            $args['post__not_in'] = [ $exclude ];
        }

        $query = new \WP_Query( $args );

        // found_posts counts every match, the skipped ones included.
        $this->max_pages = 'none' === $mode
            ? 1
            : (int) ceil( max( 0, (int) $query->found_posts - $offset ) / $per_page );

        return array_map(
            function ( $post ) use ( $settings ) {
                return $this->post_shape( $post, $settings );
            },
            $query->posts
        );
    }

    /**
     * A tax_query from "taxonomy:term_id" values, or [] when nothing valid was picked — an empty
     * tax_query silently returns nothing on some setups, so it is never sent.
     *
     * @param  string $post_type
     * @param  array  $picked
     * @return array
     */
    protected function tax_query( $post_type, array $picked ) {
        $allowed = $this->post_type_taxonomies( $post_type );
        $by_tax  = [];

        foreach ( $picked as $value ) {
            $parts = explode( ':', (string) $value );
            if ( 2 !== count( $parts ) || ! isset( $allowed[ $parts[0] ] ) || ! absint( $parts[1] ) ) {
                continue;
            }
            $by_tax[ $parts[0] ][] = absint( $parts[1] );
        }

        if ( ! $by_tax ) {
            return [];
        }

        $query = [ 'relation' => 'OR' ];
        foreach ( $by_tax as $taxonomy => $ids ) {
            $query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $ids,
            ];
        }

        return $query;
    }

    /**
     * One post, in the shape every template reads.
     *
     * @param  \WP_Post $post
     * @param  array    $settings
     * @return array
     */
    protected function post_shape( \WP_Post $post, array $settings ) {
        $id      = $post->ID;
        $content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
        $author  = (string) get_the_author_meta( 'display_name', $post->post_author );
        $avatar  = get_avatar( $post->post_author, 96, '', '', [ 'class' => 'wl-bp-avatar-img' ] );

        $raw     = has_excerpt( $id ) ? get_the_excerpt( $post ) : $content;
        $excerpt = wp_trim_words( $raw, max( 5, (int) ( $settings['words'] ?? 18 ) ), '…' );

        return [
            'id'        => $id,
            'title'     => get_the_title( $post ),
            'permalink' => (string) get_permalink( $post ),
            'excerpt'   => $excerpt,
            'date'      => (string) get_the_date( (string) ( $settings['date_format'] ?? '' ), $post ),
            'datetime'  => (string) get_the_date( 'c', $post ),
            'author'    => $author,
            'avatar'    => $avatar ? $avatar : '',
            'initial'   => $this->initial( $author ),
            'thumb'     => $this->post_image( $id, $settings ),
            'terms'     => $this->post_terms( $post ),
            'read_time' => $this->read_time( $content ),
        ];
    }

    /**
     * The monogram letter for an author: the display name's first character, uppercased and
     * multibyte-safe, falling back to the site name's so the circle is never empty.
     *
     * @param  string $name
     * @return string
     */
    protected function initial( $name ) {
        $name = trim( (string) $name );
        if ( '' === $name ) {
            $name = trim( (string) get_bloginfo( 'name' ) );
        }
        return '' === $name ? '' : mb_strtoupper( mb_substr( $name, 0, 1 ) );
    }

    /**
     * Minutes to read, at least 1: Latin-script words at 200 a minute, plus every other letter
     * at 400 a minute. Counted separately and added, because a Japanese or Bengali post nearly
     * always carries a few Latin words — a brand, a URL — and str_word_count() run over the
     * whole text then counts the multibyte bytes as words and doubles the figure.
     *
     * @param  string $text  already stripped of tags and shortcodes
     * @return int
     */
    protected function read_time( $text ) {
        $text  = (string) $text;
        $words = preg_match_all( '/[A-Za-z0-9]+(?:[\'’-][A-Za-z0-9]+)*/u', $text );
        $other = preg_replace( '/[\s\p{P}\p{S}A-Za-z0-9]+/u', '', $text );

        $minutes = $words / 200 + ( null === $other ? 0 : mb_strlen( $other ) ) / 400;

        return max( 1, (int) ceil( $minutes ) );
    }

    /**
     * The post's terms in its primary taxonomy: the known category taxonomy when the type has
     * one, otherwise the first hierarchical taxonomy the post is actually filed in, otherwise
     * the first flat one. "First hierarchical" alone is not enough — on `product` that is
     * product_brand, which would print a brand where the design shows a category.
     *
     * @param  \WP_Post $post
     * @return array  [ [ 'name' => …, 'url' => … ], … ]
     */
    protected function post_terms( \WP_Post $post ) {
        $taxonomies = $this->post_type_taxonomies( $post->post_type );
        $order      = array_intersect( [ 'category', 'product_cat' ], array_keys( $taxonomies ) );

        foreach ( $taxonomies as $slug => $taxonomy ) {
            if ( $taxonomy->hierarchical ) {
                $order[] = $slug;
            }
        }
        $order = array_unique( array_merge( $order, array_keys( $taxonomies ) ) );

        $terms = [];
        foreach ( $order as $slug ) {
            $found = get_the_terms( $post, $slug );
            if ( $found && ! is_wp_error( $found ) ) {
                $terms = $found;
                break;
            }
        }

        $list = [];
        foreach ( $terms as $term ) {
            $url    = get_term_link( $term );
            $list[] = [
                'name' => $term->name,
                'url'  => is_wp_error( $url ) ? '' : $url,
            ];
        }

        return $list;
    }

    /**
     * The card image. A post with no featured image falls back to the section's own fallback
     * image, then to WooCommerce's placeholder; only if both are missing is it '' and the CSS
     * placeholder block keeps the card's shape.
     *
     * @param  int   $id
     * @param  array $settings
     * @return string  <img> markup, or ''
     */
    protected function post_image( $id, array $settings ) {
        $attachment = (int) get_post_thumbnail_id( $id );

        if ( ! $attachment ) {
            $attachment = (int) ( $settings['fallback_image']['id'] ?? 0 );
        }

        if ( $attachment ) {
            $html = $this->attachment_image( $attachment, $settings );
            if ( $html ) {
                return $html;
            }
        }

        $src = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_single' ) : '';

        return $src
            ? '<img class="wl-bp-img" src="' . esc_url( $src ) . '" alt="" loading="lazy" decoding="async">'
            : '';
    }

    /**
     * An attachment at the size chosen in the Image Size group control (`thumb`), custom size
     * included.
     *
     * @param  int   $attachment
     * @param  array $settings
     * @return string
     */
    protected function attachment_image( $attachment, array $settings ) {
        $size = $settings['thumb_size'] ?? 'medium_large';

        if ( 'custom' === $size ) {
            $src = Group_Control_Image_Size::get_attachment_image_src( $attachment, 'thumb', $settings );
            return $src
                ? '<img class="wl-bp-img" src="' . esc_url( $src ) . '" alt="" loading="lazy" decoding="async">'
                : '';
        }

        return (string) wp_get_attachment_image( $attachment, $size ?: 'medium_large', false, [
            'class'   => 'wl-bp-img',
            'alt'     => '',
            'loading' => 'lazy',
        ] );
    }

    // ── Markup helpers ────────────────────────────────────────────────────────

    /**
     * An inline SVG icon, decorative.
     *
     * @param  string $name  right | calendar
     * @return string
     */
    protected function icon( $name ) {
        $paths = [
            'right'    => '<line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline>',
            'left'     => '<line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line>'
                . '<line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
        ];

        if ( ! isset( $paths[ $name ] ) ) {
            return '';
        }

        return '<svg class="wl-bp-icon" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
    }

    /**
     * The section header: eyebrow, heading, the description where the variant has one, and the
     * "All articles" link where the variant has one.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function header_html( array $settings, $style, $variant ) {
        $eyebrow = trim( (string) ( $settings['eyebrow'] ?? '' ) );
        $heading = trim( (string) ( $settings['heading'] ?? '' ) );
        $text    = $this->in_map( self::DESC_VARIANTS, $style, $variant ) ? trim( (string) ( $settings['description'] ?? '' ) ) : '';
        $cta     = $this->in_map( self::HEAD_CTA_VARIANTS, $style, $variant ) ? $this->cta_html( $settings, 'head' ) : '';

        if ( '' === $eyebrow && '' === $heading && '' === $text && '' === $cta ) {
            return '';
        }

        $tag = in_array( $settings['heading_tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ], true )
            ? $settings['heading_tag']
            : 'h2';

        $allowed = [ 'em' => [], 'strong' => [], 'br' => [] ];

        return '<div class="wl-bp-head">'
            . '<div class="wl-bp-head-main">'
            . ( '' !== $eyebrow ? '<span class="wl-bp-eyebrow">' . esc_html( $eyebrow ) . '</span>' : '' )
            . ( '' !== $heading ? '<' . $tag . ' class="wl-bp-heading">' . wp_kses( $heading, $allowed ) . '</' . $tag . '>' : '' )
            . ( '' !== $text ? '<p class="wl-bp-desc">' . esc_html( $text ) . '</p>' : '' )
            . '</div>'
            . $cta
            . '</div>';
    }

    /**
     * Where "All articles" goes when the merchant leaves the link empty: the posts page for
     * `post`, the type's archive for a CPT that has one, the home page otherwise.
     *
     * @param  array $settings
     * @return string
     */
    protected function archive_url( array $settings ) {
        $post_type = (string) ( $settings['post_type'] ?? 'post' );

        if ( 'post' === $post_type ) {
            $page = (int) get_option( 'page_for_posts' );
            return $page ? (string) get_permalink( $page ) : home_url( '/' );
        }

        $archive = get_post_type_archive_link( $post_type );

        return $archive ? $archive : home_url( '/' );
    }

    /**
     * The header link ('head') or the centred button under the grid ('foot').
     *
     * @param  array  $settings
     * @param  string $where  head | foot
     * @return string
     */
    protected function cta_html( array $settings, $where ) {
        $text = trim( (string) ( $settings[ $where . '_cta_text' ] ?? '' ) );

        if ( '' === $text ) {
            return '';
        }

        $link = (array) ( $settings[ $where . '_cta_link' ] ?? [] );
        $url  = trim( (string) ( $link['url'] ?? '' ) );
        $url  = '' !== $url ? $url : $this->archive_url( $settings );

        $rel    = [];
        $target = '';

        if ( ! empty( $link['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }

        if ( ! empty( $link['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        $html = '<a class="wl-bp-cta" href="' . esc_url( $url ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>'
            . esc_html( $text ) . $this->icon( 'right' ) . '</a>';

        return 'foot' === $where ? '<div class="wl-bp-foot">' . $html . '</div>' : $html;
    }

    /**
     * Whether a part token survives the Display switches.
     *
     * @param  string $token
     * @param  array  $settings
     * @return bool
     */
    protected function part_on( $token, array $settings ) {
        foreach ( self::SWITCH_PARTS as $switch => $tokens ) {
            if ( in_array( $token, $tokens, true ) && 'yes' !== ( $settings[ $switch ] ?? 'yes' ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * The posts, laid out in the variant's role: a grid, a lead story beside a list, or index
     * rows. Templates call this; Load More appends through item_html() directly, so it never
     * re-renders a lead.
     *
     * @param  array  $posts
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function items_html( array $posts, array $settings, $style, $variant ) {
        $is_lead_variant = $this->in_map( self::LEAD_VARIANTS, $style, $variant );

        // Numbered page N continues the count (Magazine V2's numerals read 04, 05, … on page 2),
        // but every page still opens with its own lead story.
        $base = 'numbers' === $this->pagination_mode( $settings )
            ? ( $this->current_page() - 1 ) * max( 1, (int) ( $settings['posts_count'] ?? 3 ) )
            : 0;

        $html = [];
        foreach ( array_values( $posts ) as $i => $post ) {
            $html[] = $this->item_html( $post, $settings, $style, $variant, $base + $i, $is_lead_variant && 0 === $i );
        }

        // data-wl-bp-items marks where Load More appends. A lead variant always prints its list,
        // even empty, so later pages have somewhere to go that is not beside the lead.
        if ( $is_lead_variant ) {
            $lead  = array_shift( $html );
            $items = '<div class="wl-bp-grid">' . $lead
                . '<div class="wl-bp-list" data-wl-bp-items>' . implode( '', $html ) . '</div>'
                . '</div>';
        } else {
            $class = $this->in_map( self::INDEX_VARIANTS, $style, $variant ) ? 'wl-bp-list' : 'wl-bp-grid';
            $items = '<div class="' . $class . '" data-wl-bp-items>' . implode( '', $html ) . '</div>';
        }

        return $items . $this->pager_html( $settings );
    }

    /**
     * The numbered page being shown, 1-based.
     *
     * @return int
     */
    protected function current_page() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only page number.
        return isset( $_GET[ $this->page_param() ] ) ? max( 1, absint( $_GET[ $this->page_param() ] ) ) : 1;
    }

    /**
     * Numbered pages or the Load More button, after the items. Nothing when the mode is 'none' or
     * every post already fits on one page.
     *
     * @param  array $settings
     * @return string
     */
    protected function pager_html( array $settings ) {
        $mode = $this->pagination_mode( $settings );

        if ( 'none' === $mode || $this->max_pages < 2 ) {
            return '';
        }

        if ( 'numbers' === $mode ) {
            $param = $this->page_param();
            $links = paginate_links( [
                'base'      => str_replace( '999999999', '%#%', add_query_arg( $param, '999999999' ) ),
                'format'    => '',
                'current'   => min( $this->current_page(), $this->max_pages ),
                'total'     => $this->max_pages,
                'type'      => 'array',
                'prev_text' => $this->icon( 'left' ) . '<span class="wl-bp-sr">' . esc_html__( 'Previous page', 'woolentor' ) . '</span>',
                'next_text' => '<span class="wl-bp-sr">' . esc_html__( 'Next page', 'woolentor' ) . '</span>' . $this->icon( 'right' ),
            ] );

            if ( ! $links ) {
                return '';
            }

            return '<nav class="wl-bp-pager" aria-label="' . esc_attr__( 'Posts pages', 'woolentor' ) . '">'
                . '<ul class="wl-bp-pages"><li>' . implode( '</li><li>', $links ) . '</li></ul>'
                . '</nav>';
        }

        $document = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->documents->get_current() : null;
        $post_id  = $document ? (int) $document->get_main_id() : (int) get_the_ID();
        $label    = trim( (string) ( $settings['load_more_text'] ?? '' ) );

        return '<div class="wl-bp-more">'
            . '<button type="button" class="wl-bp-more-btn" data-wl-bp-more aria-busy="false"'
            . ' data-ajaxurl="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '"'
            . ' data-nonce="' . esc_attr( wp_create_nonce( 'woolentor_blog_posts_load_more' ) ) . '"'
            . ' data-post="' . esc_attr( $post_id ) . '"'
            . ' data-element="' . esc_attr( $this->get_id() ) . '"'
            . ' data-page="1"'
            . ' data-exclude="' . esc_attr( $this->current_post_to_exclude( $settings ) ) . '"'
            . ' data-loading="' . esc_attr( (string) ( $settings['loading_text'] ?? '' ) ) . '"'
            . ' data-end="' . esc_attr( (string) ( $settings['no_more_text'] ?? '' ) ) . '">'
            . '<span class="wl-bp-more-label">' . esc_html( '' !== $label ? $label : __( 'Load more', 'woolentor' ) ) . '</span>'
            . '</button>'
            . '<p class="wl-bp-more-status" role="status" aria-live="polite"></p>'
            . '</div>';
    }

    /**
     * The next page for Load More, rendered through the same item_html() as the first, so the
     * markup cannot drift. Called by the woolentor_blog_posts_load_more AJAX endpoint on an
     * instance built from the widget's saved settings — never from settings sent by the browser.
     *
     * @param  int $page     2 or later.
     * @param  int $exclude  The post the first page left out, 0 for none.
     * @return array|null    [ 'html' => string, 'has_more' => bool ], or null when Load More is
     *                       not on for this widget in this build.
     */
    public function ajax_page( $page, $exclude ) {
        $settings = $this->get_settings_for_display();

        if ( 'load_more' !== $this->pagination_mode( $settings ) ) {
            return null;
        }

        $style   = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            return null;
        }

        $page  = max( 2, (int) $page );
        $posts = $this->query_posts( $settings, $page, (int) $exclude );
        $base  = ( $page - 1 ) * max( 1, (int) ( $settings['posts_count'] ?? 3 ) );
        $html  = '';

        foreach ( array_values( $posts ) as $i => $post ) {
            $html .= $this->item_html( $post, $settings, $style, $variant, $base + $i, false );
        }

        return [
            'html'     => $html,
            'has_more' => $page < $this->max_pages,
        ];
    }

    /**
     * One post, drawn from the variant's LAYOUT. Position 0 on a lead variant is the lead story.
     *
     * @param  array  $post      a post_shape()
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  int       $position  0-based, across pages — drives the numeral.
     * @param  bool|null $is_lead   Whether to draw the lead; null means "position 0".
     * @return string
     */
    protected function item_html( array $post, array $settings, $style, $variant, $position = 0, $is_lead = null ) {
        $layout  = $this->layout( $style, $variant );
        $is_lead = isset( $layout['lead'] ) && ( null === $is_lead ? 0 === (int) $position : (bool) $is_lead );
        $inner   = $this->parts_html( $is_lead ? $layout['lead'] : $layout['item'], $post, $settings, (int) $position );

        return '<article class="wl-bp-item' . ( $is_lead ? ' wl-bp-lead' : '' ) . '">' . $inner . '</article>';
    }

    /**
     * Render a LAYOUT fragment.
     *
     * @param  array $parts
     * @param  array $post
     * @param  array $settings
     * @param  int   $position
     * @return string
     */
    protected function parts_html( array $parts, array $post, array $settings, $position ) {
        $out = '';

        foreach ( $parts as $part ) {
            if ( is_string( $part ) ) {
                $out .= $this->part_html( $part, $post, $settings, $position );
                continue;
            }

            $container = key( $part );
            $children  = current( $part );

            switch ( $container ) {
                case 'media':
                    if ( ! $this->part_on( 'media', $settings ) ) {
                        break;
                    }
                    $img = '' !== $post['thumb'] ? $post['thumb'] : '<span class="wl-bp-img wl-bp-img-empty"></span>';
                    $out .= '<div class="wl-bp-media">'
                        . '<a class="wl-bp-media-link" href="' . esc_url( $post['permalink'] ) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>'
                        . $this->parts_html( $children, $post, $settings, $position )
                        . '</div>';
                    break;

                case 'body':
                    $inner = $this->parts_html( $children, $post, $settings, $position );
                    $out  .= '' !== $inner ? '<div class="wl-bp-body">' . $inner . '</div>' : '';
                    break;

                case 'meta':
                    $inner = $this->joined_html( $children, $post, $settings, $position );
                    $out  .= '' !== $inner ? '<div class="wl-bp-meta">' . $inner . '</div>' : '';
                    break;

                case 'author':
                    $face  = in_array( 'avatar', $children, true ) ? $this->part_html( 'avatar', $post, $settings, $position ) : '';
                    $line  = $this->joined_html( array_diff( $children, [ 'avatar' ] ), $post, $settings, $position );
                    $out  .= '' !== $face . $line
                        ? '<div class="wl-bp-author">' . $face . ( '' !== $line ? '<span class="wl-bp-byline">' . $line . '</span>' : '' ) . '</div>'
                        : '';
                    break;

                case 'foot':
                    $inner = $this->parts_html( $children, $post, $settings, $position );
                    $out  .= '' !== $inner ? '<div class="wl-bp-item-foot">' . $inner . '</div>' : '';
                    break;
            }
        }

        return $out;
    }

    /**
     * Small items on one line, parted by a decorative dot.
     *
     * @param  array $tokens
     * @param  array $post
     * @param  array $settings
     * @param  int   $position
     * @return string
     */
    protected function joined_html( array $tokens, array $post, array $settings, $position ) {
        $items = [];
        foreach ( $tokens as $token ) {
            $html = $this->part_html( $token, $post, $settings, $position );
            if ( '' !== $html ) {
                $items[] = $html;
            }
        }
        return implode( '<span class="wl-bp-sep" aria-hidden="true"></span>', $items );
    }

    /**
     * One leaf part.
     *
     * @param  string $token
     * @param  array  $post
     * @param  array  $settings
     * @param  int    $position
     * @return string
     */
    protected function part_html( $token, array $post, array $settings, $position ) {
        if ( ! $this->part_on( $token, $settings ) ) {
            return '';
        }

        $category = $post['terms'][0]['name'] ?? '';

        switch ( $token ) {
            case 'cat':
                return '' !== $category ? '<span class="wl-bp-cat">' . esc_html( $category ) . '</span>' : '';

            case 'badge':
                return '' !== $category ? '<span class="wl-bp-badge">' . esc_html( $category ) . '</span>' : '';

            case 'date':
            case 'date_icon':
                return '<time class="wl-bp-date" datetime="' . esc_attr( $post['datetime'] ) . '">'
                    . ( 'date_icon' === $token ? $this->icon( 'calendar' ) : '' )
                    . esc_html( $post['date'] ) . '</time>';

            case 'read':
                $label = (string) ( $settings['read_time_text'] ?? '' );
                $label = '' !== trim( $label ) ? $label : __( '{n} min read', 'woolentor' );
                return '<span class="wl-bp-read">' . esc_html( str_replace( '{n}', (string) $post['read_time'], $label ) ) . '</span>';

            case 'title':
                return '<h3 class="wl-bp-title"><a href="' . esc_url( $post['permalink'] ) . '">' . esc_html( $post['title'] ) . '</a></h3>';

            case 'excerpt':
                return '' !== $post['excerpt'] ? '<p class="wl-bp-excerpt">' . esc_html( $post['excerpt'] ) . '</p>' : '';

            case 'tags':
                if ( ! $post['terms'] ) {
                    return '';
                }
                $tags = '';
                foreach ( array_slice( $post['terms'], 0, 3 ) as $term ) {
                    $tags .= '<span class="wl-bp-tag">' . esc_html( $term['name'] ) . '</span>';
                }
                return '<div class="wl-bp-tags">' . $tags . '</div>';

            case 'link':
            case 'button':
                $text = trim( (string) ( $settings['read_link_text'] ?? '' ) );
                if ( '' === $text ) {
                    return '';
                }
                return '<a class="wl-bp-link' . ( 'button' === $token ? ' wl-bp-button' : '' ) . '" href="' . esc_url( $post['permalink'] ) . '"'
                    . ' aria-label="' . esc_attr( $text . ': ' . $post['title'] ) . '">'
                    . esc_html( $text ) . $this->icon( 'right' ) . '</a>';

            case 'num':
                return '<span class="wl-bp-num" aria-hidden="true">' . esc_html( sprintf( '%02d', $position + 1 ) ) . '</span>';

            case 'divider':
                return '<span class="wl-bp-divider" aria-hidden="true"></span>';

            case 'avatar':
                return '<span class="wl-bp-avatar">'
                    . ( '' !== $post['avatar'] ? $post['avatar'] : '<span class="wl-bp-monogram" aria-hidden="true">' . esc_html( $post['initial'] ) . '</span>' )
                    . '</span>';

            case 'name':
                return '' !== $post['author'] ? '<span class="wl-bp-name">' . esc_html( $post['author'] ) . '</span>' : '';

            case 'by':
                return '' !== $post['author']
                    ? '<span class="wl-bp-by">' . sprintf(
                        /* translators: %s: author name */
                        esc_html__( 'By %s', 'woolentor' ),
                        '<span class="wl-bp-name">' . esc_html( $post['author'] ) . '</span>'
                    ) . '</span>'
                    : '';
        }

        return '';
    }

    /**
     * Editor-only explanations. The frontend prints nothing for an empty result.
     *
     * @param  array $posts
     * @param  array $settings
     * @return void
     */
    private function editor_notices( array $posts, array $settings ) {
        if ( $posts || ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return;
        }

        echo '<p class="elementor-alert elementor-alert-info">'
            . esc_html__( 'No published posts match. Choose another post type, clear the "Only From" filter, or lower "Skip First".', 'woolentor' )
            . '</p>';
    }

    /**
     * Render the real template so a Pro variant can be seen in the editor before upgrading. The
     * frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Blog Posts' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Blog Posts' );
            return;
        }

        $posts = $this->query_posts( $settings );

        if ( ! $posts ) {
            $this->editor_notices( $posts, $settings );
            return;
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-bp wl-bp-' . esc_attr( $style ) . ' wl-bp-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-bp>';
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

        // Pro variant gate — the free build locks every pack's v2 and v3.
        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            $this->render_pro_preview( $style, $variant, $settings );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Blog Posts template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $posts = $this->query_posts( $settings, $this->current_page() );

        if ( $posts && 'load_more' === $this->pagination_mode( $settings ) && $this->max_pages > 1 ) {
            wp_enqueue_script( 'wl-pack-blog-posts' );
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';

        if ( $posts ) {
            echo '<div class="wl-bp wl-bp-' . esc_attr( $style ) . ' wl-bp-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-bp>';
            include $template;
            echo '</div>';
        }

        $this->editor_notices( $posts, $settings );

        echo '</div>';
    }
}
