<?php
/**
 * Flash Sale Widget.
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Flash_Sale_Widget extends Widget_Base {

    /** Layouts whose products sit in a Slick rail. Everything else is a static grid. */
    const RAIL_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'magazine'  => [ 'v2' ],
    ];

    /** Layouts with a column count of their own. */
    const GRID_VARIANTS = [
        'modern'    => [ 'v1', 'v2' ],
        'editorial' => [ 'v1' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v3' ],
    ];

    /** Editorial V3 opens straight on its headline. */
    const EYEBROW_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2' ],
        'luxury'    => [ 'v1', 'v2', 'v3' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** Luxury V3's banner carries a label, not a heading. */
    const HEADING_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** The block of colour that holds the header or the products. */
    const SURFACE_VARIANTS = [
        'modern'    => [ 'v2', 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v2', 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    /** The flag on the photograph. */
    const FLAG_VARIANTS = [ 'editorial' => [ 'v2' ] ];

    /** The white "Shop" pill on an overlay card. */
    const SHOP_VARIANTS = [ 'luxury' => [ 'v3' ] ];

    /** Timers whose every unit is a box. */
    const UNIT_BOX_VARIANTS = [
        'modern'   => [ 'v1', 'v2', 'v3' ],
        'magazine' => [ 'v2' ],
    ];

    /** The timer whose numeral alone is boxed, inside a box of its own. */
    const NUM_BOX_VARIANTS = [ 'magazine' => [ 'v1' ] ];

    /** Layouts that carry the merchant's own photograph beside the products. */
    const MEDIA_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
    ];

    const DESC_VARIANTS = [
        'modern'   => [ 'v1', 'v2', 'v3' ],
        'luxury'   => [ 'v1', 'v2', 'v3' ],
        'magazine' => [ 'v3' ],
    ];

    /** The oversized "30 %" of Modern V3. */
    const PERCENT_VARIANTS = [ 'modern' => [ 'v3' ] ];

    const FINE_VARIANTS = [ 'modern' => [ 'v3' ] ];

    const CTA_VARIANTS = [
        'modern'   => [ 'v2', 'v3' ],
        'luxury'   => [ 'v3' ],
        'magazine' => [ 'v3' ],
    ];

    /** The small heading above a rail. */
    const PANEL_HEAD_VARIANTS = [
        'modern'    => [ 'v3' ],
        'editorial' => [ 'v2', 'v3' ],
    ];

    const STOCK_BAR_VARIANTS = [
        'modern'   => [ 'v1', 'v2' ],
        'luxury'   => [ 'v1' ],
        'magazine' => [ 'v1', 'v2' ],
    ];

    /** One line of words and no bar. */
    const STOCK_TEXT_VARIANTS = [
        'editorial' => [ 'v1' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Print the low-stock words in place of the count once a product runs low. */
    const LOW_WORD_VARIANTS = [ 'magazine' => [ 'v1' ] ];

    /** The "Selling fast" chip over the image. */
    const LOW_TAG_VARIANTS = [ 'magazine' => [ 'v2' ] ];

    const BRAND_VARIANTS = [
        'modern'   => [ 'v1' ],
        'magazine' => [ 'v1' ],
    ];

    const CATEGORY_VARIANTS = [
        'editorial' => [ 'v1', 'v2' ],
        'luxury'    => [ 'v1', 'v3' ],
        'magazine'  => [ 'v2', 'v3' ],
    ];

    const RATING_VARIANTS = [
        'modern'   => [ 'v1' ],
        'magazine' => [ 'v1' ],
    ];

    const SWATCH_VARIANTS = [ 'editorial' => [ 'v2', 'v3' ] ];

    const ICONS_VARIANTS = [
        'modern'    => [ 'v1', 'v2' ],
        'editorial' => [ 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1' ],
    ];

    const CART_VARIANTS = [
        'modern'    => [ 'v1', 'v2' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** The timers that run without separators. */
    const NO_SEPARATOR_VARIANTS = [ 'modern' => [ 'v1' ] ];

    /** The timers that print numerals only. */
    const NO_LABEL_VARIANTS = [ 'luxury' => [ 'v3' ] ];

    /** The references that count hours, minutes and seconds with no days cell. */
    const HMS_VARIANTS = [
        'luxury'   => [ 'v3' ],
        'magazine' => [ 'v1' ],
    ];

    const UNITS = [ 'days', 'hours', 'minutes', 'seconds' ];

    /**
     * What resolve() decided for the current render.
     *
     * @var array
     */
    protected $sale = [];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_scripts();
        $this->register_pack_styles();
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
            $handle = "wl-pack-flash-sale-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/flash-sale/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-flash-sale-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_script_depends() {
        return [ 'countdown-min', 'wl-pack-widgets' ];
    }

    public function get_name() {
        return 'woolentor-flash-sale';
    }

    public function get_title() {
        return esc_html__( 'Flash Sale - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-countdown';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'flash', 'sale', 'deal', 'deals', 'countdown', 'timer', 'discount', 'offer',
                 'pack', 'style', 'woolentor' ];
    }

    /**
     * Style => locked variants. Every pack's v2 and v3 require Pro.
     */
    private function get_pro_map() {
        $map = [];
        foreach ( \WooLentor\Style_Pack_Manager::get_pack_slugs() as $slug ) {
            $map[ $slug ] = [ 'v2', 'v3' ];
        }
        return $map;
    }

    /**
     * Where this build's templates live. The Pro mirror overrides it to the free plugin's copy.
     *
     * @return string
     */
    protected function templates_dir() {
        return __DIR__ . '/templates';
    }

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

    private function merge_maps( array ...$maps ) {
        $merged = [];

        foreach ( $maps as $map ) {
            foreach ( $map as $style => $variants ) {
                $merged[ $style ] = array_values( array_unique( array_merge( $merged[ $style ] ?? [], $variants ) ) );
            }
        }

        return $merged;
    }

    private function all_of( ...$groups ) {
        $groups = array_filter( $groups, function ( $group ) {
            return ! empty( $group['terms'] );
        } );
        return [ 'relation' => 'and', 'terms' => array_values( $groups ) ];
    }

    protected function in_map( array $map, $style, $variant ) {
        return in_array( $variant, $map[ $style ] ?? [], true );
    }

    /**
     * Only on these variants, and never on a locked one.
     *
     * @param  array $map
     * @param  array $extra  Further flat terms.
     * @return array
     */
    private function gate( array $map = [], array $extra = [] ) {
        return $this->all_of(
            $map ? $this->build_conditions( $map ) : [],
            $this->build_negated_conditions( $this->get_pro_map() ),
            $extra ? [ 'relation' => 'and', 'terms' => $extra ] : []
        );
    }

    // ── Deals ─────────────────────────────────────────────────────────────────

    /**
     * The module's deals, or nothing when the module is off.
     *
     * @return array
     */
    protected function deals() {
        if ( 'on' !== woolentor_get_option( 'enable', 'woolentor_flash_sale_settings' )
            || ! class_exists( '\Woolentor\Modules\FlashSale\Woolentor_Flash_Sale' ) ) {
            return [];
        }

        $deals = woolentor_get_option( 'deals', 'woolentor_flash_sale_settings' );

        return is_array( $deals ) ? $deals : [];
    }

    /**
     * The deal that prices a product, whichever source put it in this section. The module's own
     * lookup, which is also what prices the cart.
     *
     * @param  int $id
     * @return array
     */
    protected function product_deal( $id ) {
        if ( ! $this->deals() ) {
            return [];
        }

        return \Woolentor\Modules\FlashSale\Woolentor_Flash_Sale::get_deal( $id );
    }

    /**
     * The instant a deal stops discounting, as a UTC timestamp.
     *
     * end_date is saved as ISO, or as a plain date on older sites. This is datetime_validity()'s
     * own sum, so the timer and the discount end together. That sum works in the site's wall-clock
     * time, and the offset converts it to a real instant.
     *
     * @param  array $deal
     * @return int  Zero when the deal has no end date.
     */
    protected function deal_end( array $deal ) {
        $raw = (string) ( $deal['end_date'] ?? '' );
        $end = '' !== $raw ? (int) strtotime( $raw ) : 0;

        if ( ! $end ) {
            return 0;
        }

        return $end + 86399 - (int) ( (float) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
    }

    /**
     * Decide what this render is showing: which source, which deal, when it ends, and whether a
     * shopper may see it at all.
     *
     * A saved deal that is gone, or a module that has since been switched off, falls back to
     * WooCommerce's on-sale list and this widget's own end date rather than to an empty section.
     *
     * @param  array $settings
     * @return array {
     *     @type string $source  deal | sale | manual
     *     @type array  $deal
     *     @type int    $end     UTC timestamp, zero for no timer.
     *     @type bool   $live    False when a shopper must not see this section.
     *     @type string $note    Why, for the editor.
     * }
     */
    protected function resolve( array $settings ) {
        $deals  = $this->deals();
        $source = (string) ( $settings['source'] ?? '' );
        $source = in_array( $source, [ 'deal', 'sale', 'manual' ], true ) ? $source : ( $deals ? 'deal' : 'sale' );
        $key    = $settings['deal'] ?? '';
        $key    = '' === $key || null === $key ? array_key_first( $deals ) : $key;
        $sale   = [ 'source' => $source, 'deal' => [], 'end' => 0, 'live' => true, 'note' => '' ];

        if ( 'deal' === $source && ! isset( $deals[ $key ] ) ) {
            $sale['source'] = 'sale';
            $sale['note']   = $deals
                ? __( 'The selected deal no longer exists, so this section is showing on-sale products and its own end date.', 'woolentor' )
                : __( 'The Flash Sale module has no active deals here, so this section is showing on-sale products and its own end date.', 'woolentor' );
        }

        if ( 'deal' !== $sale['source'] ) {
            $raw         = trim( (string) ( $settings['countdown_end'] ?? '' ) );
            $sale['end'] = '' !== $raw ? (int) strtotime( get_gmt_from_date( $raw ) . ' UTC' ) : 0;

            if ( $sale['end'] && $sale['end'] <= time()
                && 'hide-section' === ( $settings['countdown_expired_action'] ?? 'hide-section' ) ) {
                $sale['live'] = false;
                $sale['note'] = __( 'The end date has passed, so shoppers do not see this section.', 'woolentor' );
            }

            return $sale;
        }

        $module       = '\Woolentor\Modules\FlashSale\Woolentor_Flash_Sale';
        $deal         = $deals[ $key ];
        $sale['deal'] = $deal;
        $sale['end']  = $this->deal_end( $deal );

        if ( 'on' !== ( $deal['status'] ?? 'off' ) ) {
            $sale['live'] = false;
            $sale['note'] = __( 'This deal is switched off in the Flash Sale module, so shoppers do not see this section.', 'woolentor' );
        } elseif ( ! $module::datetime_validity( $deal ) ) {
            $sale['live'] = false;
            $sale['note'] = __( 'This deal is outside its start and end dates, so shoppers do not see this section.', 'woolentor' );
        } elseif ( ! $module::user_validity( $deal ) ) {
            // A shopper the deal does not cover would see a sale timer over full prices.
            $sale['live'] = false;
            $sale['note'] = __( 'This deal is limited to certain customers, and only they see this section.', 'woolentor' );
        }

        return $sale;
    }

    // ── Query ─────────────────────────────────────────────────────────────────

    /**
     * The products, as WC_Product objects.
     *
     * Ids are resolved here and the kit is always asked for `manual` or `recent`. Its `sale` source
     * is Pro-locked and falls back to `recent` on a free site, which would put full-price products
     * under a sale timer.
     *
     * @param  array $settings
     * @param  array $sale  From resolve().
     * @return \WC_Product[]
     */
    protected function sale_products( array $settings, array $sale ) {
        $limit = max( 1, (int) ( $settings['posts_per_page'] ?? 4 ) );
        $args  = [
            'query_type'           => 'manual',
            'posts_per_page'       => $limit,
            'orderby'              => $settings['orderby'] ?? 'date',
            'order'                => $settings['order'] ?? 'DESC',
            'exclude_products'     => $settings['exclude_products'] ?? [],
            'exclude_out_of_stock' => $settings['exclude_out_of_stock'] ?? 'yes',
            'enable_filters'       => false,
        ];

        $picked = [];

        if ( 'deal' === $sale['source'] ) {
            $args = array_merge( $args, $this->deal_args( $sale['deal'], $limit ) );
        } elseif ( 'manual' === $sale['source'] ) {
            $picked                   = array_map( 'absint', (array) ( $settings['include_products'] ?? [] ) );
            $args['include_products'] = array_slice( $picked, 0, $limit );
        } else {
            $args['include_products'] = array_map( 'absint', wc_get_product_ids_on_sale() );
            $args['categories']       = $settings['categories'] ?? [];
        }

        if ( 'manual' === $args['query_type'] && empty( $args['include_products'] ) ) {
            return [];
        }

        $query = \WooLentor\Product_Kit\Product_Query::get( $args );

        if ( ! $query ) {
            return [];
        }

        $items = [];

        foreach ( $query->posts as $post ) {
            $id      = is_object( $post ) ? $post->ID : (int) $post;
            $product = wc_get_product( $id );

            if ( $product ) {
                $items[ $id ] = $product;
            }
        }

        // A manual pick keeps the order the merchant set.
        if ( $picked ) {
            $items = array_replace( array_flip( array_intersect( $picked, array_keys( $items ) ) ), $items );
        }

        // Overlapping deals: a product priced by another deal must not sit under this one's timer.
        // Editor is exempt, so a merchant can still design around a deal that is not running.
        if ( 'deal' === $sale['source'] && ! $this->is_editor() ) {
            $module = '\Woolentor\Modules\FlashSale\Woolentor_Flash_Sale';
            $items  = array_filter( $items, function ( $product ) use ( $sale, $module ) {
                return $module::get_deal( $product->get_id() ) === $sale['deal'];
            } );
        }

        return array_slice( array_values( $items ), 0, $limit );
    }

    /**
     * Turn a deal's product rules into query arguments. Over-fetches, because overlapping deals
     * can remove some of what comes back.
     *
     * @param  array $deal
     * @param  int   $limit
     * @return array
     */
    protected function deal_args( array $deal, $limit ) {
        $exclude = array_map( 'absint', (array) ( $deal['exclude_products'] ?? [] ) );

        if ( 'on' === ( $deal['apply_on_all_products'] ?? '' ) ) {
            return [
                'query_type'       => 'recent',
                'posts_per_page'   => $limit * 2,
                'exclude_products' => $exclude,
            ];
        }

        $ids = array_map( 'absint', (array) ( $deal['products'] ?? [] ) );

        if ( ! empty( $deal['categories'] ) ) {
            $ids = array_merge( $ids, get_posts( [
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'fields'         => 'ids',
                'posts_per_page' => 200,
                'no_found_rows'  => true,
                'tax_query'      => [ [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => array_map( 'absint', (array) $deal['categories'] ),
                ] ],
            ] ) );
        }

        return [
            'posts_per_page'   => $limit * 2,
            'include_products' => array_values( array_diff( array_unique( $ids ), $exclude ) ),
        ];
    }

    protected function is_editor() {
        return \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    // ── Controls ──────────────────────────────────────────────────────────────

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

            // Guarded rather than cut by the mirror: with Pro active get_pro_map() is empty.
            if ( $this->get_pro_map() ) {
                woolentor_upgrade_pro_notice( $this, 'flash_sale_variant_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ], [ 'mode' => 'alert' ] );
            }

        $this->end_controls_section();

        $this->register_sale_controls();
        $this->register_countdown_controls();
        $this->register_header_controls();
        $this->register_card_controls();
        $this->register_layout_controls();
        $this->register_slider_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    private function register_sale_controls() {
        $deals = $this->deals();

        $this->start_controls_section( 'section_sale', [
            'label'      => esc_html__( 'Sale', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $sources = [
                'sale'   => esc_html__( 'WooCommerce on-sale products', 'woolentor' ),
                'manual' => esc_html__( 'Choose products', 'woolentor' ),
            ];

            if ( $deals ) {
                $sources = [ 'deal' => esc_html__( 'Flash Sale module deal', 'woolentor' ) ] + $sources;
            }

            $this->add_control( 'source', [
                'label'   => esc_html__( 'Products From', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => $deals ? 'deal' : 'sale',
                'options' => $sources,
            ] );

            if ( $deals ) {
                $this->add_control( 'deal', [
                    'label'       => esc_html__( 'Deal', 'woolentor' ),
                    'type'        => Controls_Manager::SELECT,
                    'default'     => (string) array_key_first( $deals ),
                    'options'     => array_map( function ( $deal ) {
                        return ! empty( $deal['title'] ) ? $deal['title'] : esc_html__( 'Unnamed Deal', 'woolentor' );
                    }, $deals ),
                    'description' => esc_html__( 'The deal sets the products, prices and end date. Deleting an earlier deal in the module can change which one is selected here.', 'woolentor' ),
                    'condition'   => [ 'source' => 'deal' ],
                ] );
            }

            \WooLentor\Product_Kit\Elementor_Controls::add_fields( $this, 'query', [
                'skip'      => [ 'query_type', 'tags', 'exclude_no_image', 'enable_filters' ],
                'overrides' => [
                    'include_products'     => [ 'condition' => [ 'source' => 'manual' ] ],
                    'categories'           => [ 'condition' => [ 'source' => 'sale' ] ],
                    'posts_per_page'       => [ 'default' => 4, 'condition' => [] ],
                    'orderby'              => [ 'condition' => [ 'source!' => 'manual' ] ],
                    'order'                => [ 'condition' => [ 'source!' => 'manual' ] ],
                    'exclude_products'     => [ 'condition' => [ 'source!' => 'manual' ] ],
                    'exclude_out_of_stock' => [ 'default' => 'yes', 'condition' => [] ],
                ],
            ] );

        $this->end_controls_section();
    }

    private function register_countdown_controls() {
        $this->start_controls_section( 'section_countdown', [
            'label'      => esc_html__( 'Countdown', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $this->add_control( 'countdown_show', [
                'label'        => esc_html__( 'Show Countdown', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            // Offered whenever no deal sets the date. With no deals on the site the condition is
            // left off, because a value saved as `deal` could otherwise hide this for good.
            $end_condition = [ 'countdown_show' => 'yes' ];

            if ( $this->deals() ) {
                $end_condition['source!'] = 'deal';
            }

            $this->add_control( 'countdown_end', [
                'label'       => esc_html__( 'Ends On', 'woolentor' ),
                'type'        => Controls_Manager::DATE_TIME,
                'default'     => gmdate( 'Y-m-d H:i', strtotime( '+3 days' ) ),
                'description' => esc_html__( 'Your site\'s timezone.', 'woolentor' ),
                'condition'   => $end_condition,
            ] );

            $this->add_control( 'countdown_label', [
                'label'     => esc_html__( 'Lead-in', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Ends in', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes', 'style' => 'magazine', 'variant' => 'v1' ],
            ] );

            $this->add_control( 'countdown_units', [
                'label'       => esc_html__( 'Units', 'woolentor' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'default'     => [],
                'options'     => [
                    'days'    => esc_html__( 'Days', 'woolentor' ),
                    'hours'   => esc_html__( 'Hours', 'woolentor' ),
                    'minutes' => esc_html__( 'Minutes', 'woolentor' ),
                    'seconds' => esc_html__( 'Seconds', 'woolentor' ),
                ],
                'description' => esc_html__( 'Empty uses this layout\'s own units. Drop a larger unit and the next one absorbs it.', 'woolentor' ),
                'condition'   => [ 'countdown_show' => 'yes' ],
            ] );

            foreach ( [
                'days'    => [ esc_html__( 'Days Label', 'woolentor' ), esc_html__( 'Days', 'woolentor' ) ],
                'hours'   => [ esc_html__( 'Hours Label', 'woolentor' ), esc_html__( 'Hours', 'woolentor' ) ],
                'minutes' => [ esc_html__( 'Minutes Label', 'woolentor' ), esc_html__( 'Mins', 'woolentor' ) ],
                'seconds' => [ esc_html__( 'Seconds Label', 'woolentor' ), esc_html__( 'Secs', 'woolentor' ) ],
            ] as $unit => $strings ) {
                // Hours-first layouts draw no days cell unless the merchant asks for one.
                $days_gate = 'days' === $unit
                    ? [ 'relation' => 'or', 'terms' => [
                        $this->build_negated_conditions( self::HMS_VARIANTS ),
                        [ 'name' => 'countdown_units', 'operator' => 'contains', 'value' => 'days' ],
                    ] ]
                    : [];

                $this->add_control( 'label_' . $unit, [
                    'label'      => $strings[0],
                    'type'       => Controls_Manager::TEXT,
                    'default'    => $strings[1],
                    'conditions' => $this->all_of(
                        $this->build_negated_conditions( self::NO_LABEL_VARIANTS ),
                        [ 'relation' => 'and', 'terms' => [
                            [ 'name' => 'countdown_show', 'operator' => '==', 'value' => 'yes' ],
                        ] ],
                        $days_gate
                    ),
                ] );
            }

            $this->add_control( 'countdown_expired_action', [
                'label'     => esc_html__( 'When It Ends', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'hide-section',
                'options'   => [
                    'hide-section' => esc_html__( 'Hide the whole section', 'woolentor' ),
                    'hide'         => esc_html__( 'Hide the countdown', 'woolentor' ),
                    'message'      => esc_html__( 'Show a message', 'woolentor' ),
                ],
                'description' => esc_html__( 'A deal that has ended always hides the section, because its prices have ended too.', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes' ],
            ] );

            $this->add_control( 'countdown_expired_text', [
                'label'     => esc_html__( 'Message', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'This sale has ended.', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes', 'countdown_expired_action' => 'message' ],
            ] );

        $this->end_controls_section();
    }

    private function register_header_controls() {
        $this->start_controls_section( 'section_header', [
            'label'      => esc_html__( 'Header', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $this->add_control( 'eyebrow', [
                'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Flash Sale', 'woolentor' ),
                'label_block' => true,
                'conditions'  => $this->build_conditions( self::EYEBROW_VARIANTS ),
            ] );

            $this->add_control( 'heading', [
                'label'       => esc_html__( 'Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => wp_kses( __( 'Today\'s <em>best deals.</em>', 'woolentor' ), [ 'em' => [] ] ),
                'label_block' => true,
                'description' => esc_html__( 'Accepts <em>, <strong> and <br>.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::HEADING_VARIANTS ),
            ] );

            $this->add_control( 'heading_tag', [
                'label'      => esc_html__( 'Heading Tag', 'woolentor' ),
                'type'       => Controls_Manager::SELECT,
                'default'    => 'h2',
                'options'    => [ 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ],
                'conditions' => $this->build_conditions( self::HEADING_VARIANTS ),
            ] );

            $this->add_control( 'description', [
                'label'      => esc_html__( 'Description', 'woolentor' ),
                'type'       => Controls_Manager::TEXTAREA,
                'rows'       => 2,
                'default'    => esc_html__( 'Limited-time offers on selected products, while stocks last.', 'woolentor' ),
                'conditions' => $this->build_conditions( self::DESC_VARIANTS ),
            ] );

            $this->add_control( 'percent_value', [
                'label'      => esc_html__( 'Big Number', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => '30',
                'conditions' => $this->build_conditions( self::PERCENT_VARIANTS ),
            ] );

            $this->add_control( 'percent_suffix', [
                'label'      => esc_html__( 'Beside the Number', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( '% Off', 'woolentor' ),
                'conditions' => $this->build_conditions( self::PERCENT_VARIANTS ),
            ] );

            $this->add_control( 'panel_heading', [
                'label'      => esc_html__( 'Products Heading', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'On Sale Now', 'woolentor' ),
                'conditions' => $this->build_conditions( self::PANEL_HEAD_VARIANTS ),
            ] );

            $this->add_control( 'media_image', [
                'label'      => esc_html__( 'Photo', 'woolentor' ),
                'type'       => Controls_Manager::MEDIA,
                'default'    => [ 'url' => Utils::get_placeholder_image_src() ],
                'conditions' => $this->build_conditions( self::MEDIA_VARIANTS ),
            ] );

            $this->add_control( 'media_flag', [
                'label'      => esc_html__( 'Photo Flag', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Final Reductions', 'woolentor' ),
                'conditions' => [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'style', 'operator' => '==', 'value' => 'editorial' ],
                    [ 'name' => 'variant', 'operator' => '==', 'value' => 'v2' ],
                ] ],
            ] );

            $this->add_control( 'cta_text', [
                'label'      => esc_html__( 'Button Text', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Shop the Sale', 'woolentor' ),
                'conditions' => $this->build_conditions( self::CTA_VARIANTS ),
            ] );

            $this->add_control( 'cta_link', [
                'label'       => esc_html__( 'Button Link', 'woolentor' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Leave empty for the shop page', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::CTA_VARIANTS ),
            ] );

            $this->add_control( 'fine_print', [
                'label'      => esc_html__( 'Fine Print', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'While stocks last.', 'woolentor' ),
                'conditions' => $this->build_conditions( self::FINE_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    private function register_card_controls() {
        $this->start_controls_section( 'section_card', [
            'label'      => esc_html__( 'Product Card', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $switch = function ( $id, $label, array $map, array $extra = [] ) {
                $this->add_control( $id, array_merge( [
                    'label'        => $label,
                    'type'         => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default'      => 'yes',
                ], $map ? [ 'conditions' => $this->build_conditions( $map ) ] : [], $extra ) );
            };

            $stock_map = [];
            foreach ( [ self::STOCK_BAR_VARIANTS, self::STOCK_TEXT_VARIANTS ] as $map ) {
                foreach ( $map as $pack => $variants ) {
                    $stock_map[ $pack ] = array_merge( $stock_map[ $pack ] ?? [], $variants );
                }
            }

            $switch( 'show_badge', esc_html__( 'Discount Badge', 'woolentor' ), [] );
            $switch( 'show_wishlist', esc_html__( 'Wishlist', 'woolentor' ), self::ICONS_VARIANTS );
            $switch( 'show_compare', esc_html__( 'Compare', 'woolentor' ), self::ICONS_VARIANTS );
            $switch( 'show_quick_view', esc_html__( 'Quick View', 'woolentor' ), self::ICONS_VARIANTS );
            $switch( 'show_cart', esc_html__( 'Add to Cart', 'woolentor' ), self::CART_VARIANTS );

            $this->add_control( 'cart_text', [
                'label'       => esc_html__( 'Add to Cart Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'description' => esc_html__( 'Empty keeps WooCommerce\'s own wording.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::CART_VARIANTS ),
            ] );

            $this->add_control( 'shop_text', [
                'label'      => esc_html__( 'Link Text', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Shop', 'woolentor' ),
                'conditions' => [ 'relation' => 'and', 'terms' => [
                    [ 'name' => 'style', 'operator' => '==', 'value' => 'luxury' ],
                    [ 'name' => 'variant', 'operator' => '==', 'value' => 'v3' ],
                ] ],
            ] );

            $switch( 'show_brand', esc_html__( 'Brand', 'woolentor' ), self::BRAND_VARIANTS );

            $this->add_control( 'brand_source', [
                'label'      => esc_html__( 'Brand Comes From', 'woolentor' ),
                'type'       => Controls_Manager::SELECT,
                'default'    => 'category',
                'options'    => [
                    'category' => esc_html__( 'Product category', 'woolentor' ),
                    'brand'    => esc_html__( 'Brand taxonomy', 'woolentor' ),
                ],
                'conditions' => $this->build_conditions( self::BRAND_VARIANTS ),
            ] );

            $switch( 'show_category', esc_html__( 'Category', 'woolentor' ), self::CATEGORY_VARIANTS );
            $switch( 'show_rating', esc_html__( 'Rating', 'woolentor' ), self::RATING_VARIANTS );
            $switch( 'show_swatches', esc_html__( 'Colour Swatches', 'woolentor' ), self::SWATCH_VARIANTS );

            $this->add_control( 'swatch_attribute', [
                'label'      => esc_html__( 'Swatch Attribute', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => 'color',
                'conditions' => $this->build_conditions( self::SWATCH_VARIANTS ),
            ] );

            $this->add_control( 'swatch_limit', [
                'label'      => esc_html__( 'Swatches Shown', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 4,
                'min'        => 1,
                'max'        => 12,
                'conditions' => $this->build_conditions( self::SWATCH_VARIANTS ),
            ] );

            $switch( 'show_stock', esc_html__( 'Stock Progress', 'woolentor' ), $stock_map, [
                'description' => esc_html__( 'Shows only for products with Manage Stock on. Sold counts are the product\'s lifetime sales.', 'woolentor' ),
            ] );

            $this->add_control( 'low_stock_threshold', [
                'label'      => esc_html__( 'Low Stock Below', 'woolentor' ),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 10,
                'min'        => 1,
                'conditions' => $this->build_conditions( $this->merge_maps( self::LOW_WORD_VARIANTS, self::LOW_TAG_VARIANTS ) ),
            ] );

            $this->add_control( 'stock_sold_text', [
                'label'       => esc_html__( 'Sold Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Sold {sold} / {total}', 'woolentor' ),
                'description' => esc_html__( 'Use {sold}, {total}, {left} and {percent}.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::STOCK_BAR_VARIANTS ),
            ] );

            $this->add_control( 'stock_left_text', [
                'label'       => esc_html__( 'Remaining Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Only {left} left', 'woolentor' ),
                'conditions'  => $this->build_conditions( $stock_map ),
            ] );

            $this->add_control( 'stock_low_text', [
                'label'      => esc_html__( 'Low Stock Text', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Almost gone', 'woolentor' ),
                'conditions' => $this->build_conditions( $this->merge_maps( self::LOW_WORD_VARIANTS, self::LOW_TAG_VARIANTS ) ),
            ] );

        $this->end_controls_section();
    }

    private function register_layout_controls() {
        $this->start_controls_section( 'section_layout', [
            'label'      => esc_html__( 'Layout', 'woolentor' ),
            'conditions' => $this->gate( self::GRID_VARIANTS ),
        ] );

            // A separate custom property per device, because each pack sets its own column count
            // at each breakpoint and a desktop choice must not reach the phone.
            $this->add_responsive_control( 'columns', [
                'label'       => esc_html__( 'Columns', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => [
                    ''  => esc_html__( 'Default', 'woolentor' ),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                ],
                'selectors'   => [ '{{WRAPPER}} .wl-fs-grid' => '--wl-fs-cols: {{VALUE}};' ],
                'device_args' => [
                    Controls_Stack::RESPONSIVE_TABLET => [
                        'selectors' => [ '{{WRAPPER}} .wl-fs-grid' => '--wl-fs-cols-md: {{VALUE}};' ],
                    ],
                    Controls_Stack::RESPONSIVE_MOBILE => [
                        'selectors' => [ '{{WRAPPER}} .wl-fs-grid' => '--wl-fs-cols-sm: {{VALUE}};' ],
                    ],
                ],
            ] );

        $this->end_controls_section();
    }

    /**
     * On these variants the rail is the design rather than an option, so there is no switch and the
     * kit's Pro-locked slider section is not used. Same control names as Hero Banner.
     */
    private function register_slider_controls() {
        $this->start_controls_section( 'section_slider', [
            'label'      => esc_html__( 'Slider Options', 'woolentor' ),
            'conditions' => $this->gate( self::RAIL_VARIANTS ),
        ] );

            foreach ( [
                'slider_arrows'         => [ esc_html__( 'Show Arrows', 'woolentor' ), 'yes' ],
                'slider_infinite'       => [ esc_html__( 'Infinite Loop', 'woolentor' ), '' ],
                'slider_autoplay'       => [ esc_html__( 'Autoplay', 'woolentor' ), '' ],
                'slider_pause_on_hover' => [ esc_html__( 'Pause on Hover', 'woolentor' ), 'yes' ],
            ] as $id => $strings ) {
                $this->add_control( $id, [
                    'label'        => $strings[0],
                    'type'         => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default'      => $strings[1],
                ] );
            }

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

            $this->add_control( 'sl_items', [
                'label'       => esc_html__( 'Products Shown', 'woolentor' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 1,
                'max'         => 6,
                'description' => esc_html__( 'Empty uses this layout\'s own count.', 'woolentor' ),
            ] );

            foreach ( [
                'sl_scroll'        => [ esc_html__( 'Products to Scroll', 'woolentor' ), 1 ],
                'sl_tablet_items'  => [ esc_html__( 'Tablet: Products Shown', 'woolentor' ), 2 ],
                'sl_tablet_scroll' => [ esc_html__( 'Tablet: Products to Scroll', 'woolentor' ), 1 ],
                'sl_tablet_width'  => [ esc_html__( 'Tablet Resolution', 'woolentor' ), 1024 ],
                'sl_mobile_items'  => [ esc_html__( 'Mobile: Products Shown', 'woolentor' ), 1 ],
                'sl_mobile_scroll' => [ esc_html__( 'Mobile: Products to Scroll', 'woolentor' ), 1 ],
                'sl_mobile_width'  => [ esc_html__( 'Mobile Resolution', 'woolentor' ), 767 ],
            ] as $id => $strings ) {
                $this->add_control( $id, [
                    'label'   => $strings[0],
                    'type'    => Controls_Manager::NUMBER,
                    'default' => $strings[1],
                    'min'     => 1,
                ] );
            }

        $this->end_controls_section();
    }

    private function register_pro_content_notice() {
        if ( ! $this->get_pro_map() ) {
            return;
        }

        $this->start_controls_section( 'section_pro_notice', [
            'label'      => esc_html__( 'Content', 'woolentor' ),
            'conditions' => $this->build_conditions( $this->get_pro_map() ),
        ] );

            woolentor_upgrade_pro_notice( $this, 'flash_sale_content_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ]  );

        $this->end_controls_section();
    }

    private function register_style_controls() {
        $editable = $this->build_negated_conditions( $this->get_pro_map() );

        // A section shown only on the variants in $map, only while $switch (if any) is on.
        $gate = function ( array $map, $switch = '' ) use ( $editable ) {
            $groups = [ $map ? $this->build_conditions( $map ) : [], $editable ];
            if ( $switch ) {
                $groups[] = [ 'terms' => [ [ 'name' => $switch, 'operator' => '==', 'value' => 'yes' ] ] ];
            }
            return $this->all_of( ...array_filter( $groups ) );
        };

        $section = function ( $id, $label, array $conditions ) {
            $this->start_controls_section( $id, [
                'label'      => $label,
                'tab'        => Controls_Manager::TAB_STYLE,
                'conditions' => $conditions,
            ] );
        };

        // Elementor does not hand a section's conditions down to its controls, and it writes the
        // CSS of a control whose section is hidden. Each control takes the section's gate here.
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

        $size = function ( $id, $label, $selector, $property, $max, array $extra = [] ) {
            $this->add_responsive_control( $id, array_merge( [
                'label'      => $label,
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => $max ] ],
                'selectors'  => [ $selector => $property . ': {{SIZE}}{{UNIT}};' ],
            ], $extra ) );
        };

        $pad = function ( $id, $label, $selector ) {
            $this->add_responsive_control( $id, [
                'label'      => $label,
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );
        };

        $on = function ( array $map ) {
            return [ 'conditions' => $this->build_conditions( $map ) ];
        };

        $before = [ 'separator' => 'before' ];

        // ── Section ───────────────────────────────────────────────────────────

        $section( 'style_section', esc_html__( 'Section', 'woolentor' ), $editable );

            $color( 'section_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-fs', 'background-color' );
            $pad( 'section_padding', esc_html__( 'Padding', 'woolentor' ), '{{WRAPPER}} .wl-fs' );

        $end();

        // ── Panel ─────────────────────────────────────────────────────────────

        $section( 'style_surface', esc_html__( 'Panel', 'woolentor' ), $gate( self::SURFACE_VARIANTS ) );

            $color( 'surface_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-surface', 'background-color' );
            $size( 'surface_radius', esc_html__( 'Border Radius', 'woolentor' ), '{{WRAPPER}} .wl-fs-surface', 'border-radius', 60 );
            $pad( 'surface_padding', esc_html__( 'Padding', 'woolentor' ), '{{WRAPPER}} .wl-fs-surface' );

        $end();

        // ── Header ────────────────────────────────────────────────────────────

        $section( 'style_header', esc_html__( 'Header', 'woolentor' ), $editable );

            $color( 'eyebrow_color', esc_html__( 'Eyebrow Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-eyebrow', 'color', $on( self::EYEBROW_VARIANTS ) );
            $type( 'eyebrow_typography', '{{WRAPPER}} .wl-fs-eyebrow', $on( self::EYEBROW_VARIANTS ) );

            $color( 'heading_color', esc_html__( 'Heading Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-heading', 'color', $before + $on( self::HEADING_VARIANTS ) );
            $color( 'heading_accent_color', esc_html__( 'Accent Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-heading em, {{WRAPPER}} .wl-fs-heading strong', 'color', [
                'description' => esc_html__( 'For the words wrapped in <em> or <strong> in the heading.', 'woolentor' ),
            ] + $on( self::HEADING_VARIANTS ) );
            $type( 'heading_typography', '{{WRAPPER}} .wl-fs-heading', $on( self::HEADING_VARIANTS ) );

            $color( 'desc_color', esc_html__( 'Description Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-desc', 'color', $before + $on( self::DESC_VARIANTS ) );
            $type( 'desc_typography', '{{WRAPPER}} .wl-fs-desc', $on( self::DESC_VARIANTS ) );

            $color( 'percent_color', esc_html__( 'Percent Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-percent-num', 'color', $before + $on( self::PERCENT_VARIANTS ) );
            $color( 'percent_suffix_color', esc_html__( 'Percent Suffix Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-percent-suffix', 'color', $on( self::PERCENT_VARIANTS ) );
            $type( 'percent_typography', '{{WRAPPER}} .wl-fs-percent-num', $on( self::PERCENT_VARIANTS ) );

            $color( 'fine_color', esc_html__( 'Fine Print Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-fine', 'color', $before + $on( self::FINE_VARIANTS ) );

            $color( 'panel_head_color', esc_html__( 'Panel Heading Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-panel-head', 'color', $before + $on( self::PANEL_HEAD_VARIANTS ) );
            $type( 'panel_head_typography', '{{WRAPPER}} .wl-fs-panel-head', $on( self::PANEL_HEAD_VARIANTS ) );

            $color( 'flag_color', esc_html__( 'Flag Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-flag', 'color', $before + $on( self::FLAG_VARIANTS ) );
            $color( 'flag_bg', esc_html__( 'Flag Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-flag', 'background-color', $on( self::FLAG_VARIANTS ) );

        $end();

        // ── Countdown ─────────────────────────────────────────────────────────

        $section( 'style_countdown', esc_html__( 'Countdown', 'woolentor' ), $gate( [], 'countdown_show' ) );

            $color( 'cd_num_color', esc_html__( 'Number Color', 'woolentor' ), '{{WRAPPER}} .wl-pack-countdown-num' );
            $type( 'cd_num_typography', '{{WRAPPER}} .wl-pack-countdown-num' );

            $color( 'cd_label_color', esc_html__( 'Label Color', 'woolentor' ), '{{WRAPPER}} .wl-pack-countdown-label', 'color', $before + [
                'conditions' => $this->build_negated_conditions( self::NO_LABEL_VARIANTS ),
            ] );
            $type( 'cd_label_typography', '{{WRAPPER}} .wl-pack-countdown-label', [
                'conditions' => $this->build_negated_conditions( self::NO_LABEL_VARIANTS ),
            ] );

            $color( 'cd_sep_color', esc_html__( 'Separator Color', 'woolentor' ), '{{WRAPPER}} .wl-pack-countdown-sep', 'color', [
                'conditions' => $this->build_negated_conditions( self::NO_SEPARATOR_VARIANTS ),
            ] );

            $size( 'cd_gap', esc_html__( 'Gap', 'woolentor' ), '{{WRAPPER}} .wl-fs-countdown', '--wl-pack-countdown-gap', 60 );

            $cell = '{{WRAPPER}} .wl-pack-countdown-unit';

            $color( 'cd_cell_bg', esc_html__( 'Box Background', 'woolentor' ), $cell, 'background-color', $before + $on( self::UNIT_BOX_VARIANTS ) );
            $color( 'cd_cell_border', esc_html__( 'Box Border Color', 'woolentor' ), $cell, 'border-color', $on( self::UNIT_BOX_VARIANTS ) );
            $size( 'cd_cell_radius', esc_html__( 'Box Radius', 'woolentor' ), $cell, 'border-radius', 40, $on( self::UNIT_BOX_VARIANTS ) );

            $color( 'cd_num_bg', esc_html__( 'Number Background', 'woolentor' ), '{{WRAPPER}} .wl-pack-countdown-num', 'background-color', $before + $on( self::NUM_BOX_VARIANTS ) );
            $size( 'cd_num_radius', esc_html__( 'Number Radius', 'woolentor' ), '{{WRAPPER}} .wl-pack-countdown-num', 'border-radius', 40, $on( self::NUM_BOX_VARIANTS ) );

        $end();

        // ── Card ──────────────────────────────────────────────────────────────

        $section( 'style_card', esc_html__( 'Card', 'woolentor' ), $editable );

            $color( 'card_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-item', 'background-color' );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .wl-fs-item',
            ] );

            $size( 'card_radius', esc_html__( 'Border Radius', 'woolentor' ), '{{WRAPPER}} .wl-fs-item', 'border-radius', 40, [
                'selectors' => [ '{{WRAPPER}} .wl-fs-item' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;' ],
            ] );

            $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
                'name'     => 'card_hover_shadow',
                'label'    => esc_html__( 'Hover Shadow', 'woolentor' ),
                'selector' => '{{WRAPPER}} .wl-fs-item:hover',
            ] );

            $pad( 'card_padding', esc_html__( 'Content Padding', 'woolentor' ), '{{WRAPPER}} .wl-fs-body' );
            $size( 'card_gap', esc_html__( 'Gap Between Cards', 'woolentor' ), '{{WRAPPER}} .wl-fs', '--wl-fs-gap', 80 );

        $end();

        // ── Image ─────────────────────────────────────────────────────────────

        $section( 'style_image', esc_html__( 'Image', 'woolentor' ), $editable );

            $this->add_control( 'image_ratio', [
                'label'     => esc_html__( 'Aspect Ratio', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    ''      => esc_html__( 'Default', 'woolentor' ),
                    '1 / 1' => '1:1',
                    '4 / 5' => '4:5',
                    '3 / 4' => '3:4',
                    '4 / 3' => '4:3',
                    '3 / 2' => '3:2',
                ],
                'selectors' => [ '{{WRAPPER}} .wl-fs' => '--wl-fs-ratio: {{VALUE}};' ],
            ] );

            $color( 'image_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-media', 'background-color' );
            $size( 'image_radius', esc_html__( 'Border Radius', 'woolentor' ), '{{WRAPPER}} .wl-fs-media', 'border-radius', 40 );

            $this->add_control( 'image_no_zoom', [
                'label'        => esc_html__( 'Turn Off Zoom on Hover', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'selectors'    => [ '{{WRAPPER}} .wl-fs' => '--wl-fs-zoom: 1;' ],
            ] );

        $end();

        // ── Badge ─────────────────────────────────────────────────────────────

        $section( 'style_badge', esc_html__( 'Badge', 'woolentor' ), $gate( [], 'show_badge' ) );

            $color( 'badge_color', esc_html__( 'Text Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-off' );
            $color( 'badge_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-off', 'background-color' );
            $size( 'badge_radius', esc_html__( 'Border Radius', 'woolentor' ), '{{WRAPPER}} .wl-fs-off', 'border-radius', 40 );
            $type( 'badge_typography', '{{WRAPPER}} .wl-fs-off' );

            $color( 'low_tag_color', esc_html__( 'Low Stock Chip Text', 'woolentor' ), '{{WRAPPER}} .wl-fs-tag', 'color', $before + $on( self::LOW_TAG_VARIANTS ) );
            $color( 'low_tag_bg', esc_html__( 'Low Stock Chip Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-tag', 'background-color', $on( self::LOW_TAG_VARIANTS ) );

        $end();

        // ── Text ──────────────────────────────────────────────────────────────

        $section( 'style_text', esc_html__( 'Product Text', 'woolentor' ), $editable );

            $color( 'title_color', esc_html__( 'Title Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-title, {{WRAPPER}} .wl-fs-title a' );
            $color( 'title_hover_color', esc_html__( 'Title Hover Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-title a:hover, {{WRAPPER}} a.wl-fs-item:hover .wl-fs-title, {{WRAPPER}} .wl-fs-item:hover .wl-fs-title a' );
            $type( 'title_typography', '{{WRAPPER}} .wl-fs-title' );

            $color( 'cat_color', esc_html__( 'Category Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-cat', 'color', $before + $on( self::CATEGORY_VARIANTS ) );
            $type( 'cat_typography', '{{WRAPPER}} .wl-fs-cat', $on( self::CATEGORY_VARIANTS ) );

            $color( 'brand_color', esc_html__( 'Brand Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-brand', 'color', $before + $on( self::BRAND_VARIANTS ) );
            $type( 'brand_typography', '{{WRAPPER}} .wl-fs-brand', $on( self::BRAND_VARIANTS ) );

            $color( 'rating_color', esc_html__( 'Rating Star Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-rating', 'color', $before + $on( self::RATING_VARIANTS ) );

        $end();

        // ── Price ─────────────────────────────────────────────────────────────

        $section( 'style_price', esc_html__( 'Price', 'woolentor' ), $editable );

            $color( 'price_color', esc_html__( 'Sale Price Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-price, {{WRAPPER}} .wl-fs-price ins' );
            $color( 'price_was_color', esc_html__( 'Old Price Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-price del' );
            $type( 'price_typography', '{{WRAPPER}} .wl-fs-price' );

        $end();

        // ── Stock ─────────────────────────────────────────────────────────────

        $section( 'style_stock', esc_html__( 'Stock', 'woolentor' ), $gate( $this->merge_maps( self::STOCK_BAR_VARIANTS, self::STOCK_TEXT_VARIANTS ), 'show_stock' ) );

            $color( 'stock_track', esc_html__( 'Bar Track', 'woolentor' ), '{{WRAPPER}} .wl-fs-stock-bar', 'background-color', $on( self::STOCK_BAR_VARIANTS ) );
            $color( 'stock_fill', esc_html__( 'Bar Fill', 'woolentor' ), '{{WRAPPER}} .wl-fs-stock-fill', 'background', $on( self::STOCK_BAR_VARIANTS ) );
            $size( 'stock_height', esc_html__( 'Bar Height', 'woolentor' ), '{{WRAPPER}} .wl-fs', '--wl-fs-bar-height', 20, $on( self::STOCK_BAR_VARIANTS ) );
            $size( 'stock_radius', esc_html__( 'Bar Radius', 'woolentor' ), '{{WRAPPER}} .wl-fs-stock-bar, {{WRAPPER}} .wl-fs-stock-fill', 'border-radius', 20, $on( self::STOCK_BAR_VARIANTS ) );

            $color( 'stock_text_color', esc_html__( 'Sold Text Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-stock-meta', 'color', $before + $on( self::STOCK_BAR_VARIANTS ) );
            $color( 'stock_left_color', esc_html__( 'Stock Left Color', 'woolentor' ), '{{WRAPPER}} .wl-fs-stock-left' );
            $type( 'stock_typography', '{{WRAPPER}} .wl-fs-stock-meta, {{WRAPPER}} .wl-fs-stock-left' );

        $end();

        // ── Buttons ───────────────────────────────────────────────────────────

        $cart = '{{WRAPPER}} .wl-fs-cart-wrap a.button';

        $section( 'style_cart', esc_html__( 'Add to Cart', 'woolentor' ), $gate( self::CART_VARIANTS, 'show_cart' ) );

            $color( 'cart_color', esc_html__( 'Text Color', 'woolentor' ), $cart );
            $color( 'cart_bg', esc_html__( 'Background', 'woolentor' ), $cart, 'background-color' );
            $color( 'cart_hover_color', esc_html__( 'Hover Text Color', 'woolentor' ), $cart . ':hover' );
            $color( 'cart_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $cart . ':hover', 'background-color' );
            $size( 'cart_radius', esc_html__( 'Border Radius', 'woolentor' ), $cart, 'border-radius', 40 );
            $type( 'cart_typography', $cart );

        $end();

        $section( 'style_icons', esc_html__( 'Icon Buttons', 'woolentor' ), $gate( self::ICONS_VARIANTS ) );

            $icon = '{{WRAPPER}} .wl-fs-iconbtn';

            $color( 'icon_color', esc_html__( 'Icon Color', 'woolentor' ), $icon );
            $color( 'icon_bg', esc_html__( 'Background', 'woolentor' ), $icon, 'background-color' );
            $color( 'icon_hover_color', esc_html__( 'Hover Icon Color', 'woolentor' ), $icon . ':hover' );
            $color( 'icon_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $icon . ':hover', 'background-color' );

        $end();

        $section( 'style_cta', esc_html__( 'Button', 'woolentor' ), $gate( self::CTA_VARIANTS ) );

            $cta = '{{WRAPPER}} .wl-fs-cta';

            $color( 'cta_color', esc_html__( 'Text Color', 'woolentor' ), $cta );
            $color( 'cta_bg', esc_html__( 'Background', 'woolentor' ), $cta, 'background-color' );
            $color( 'cta_hover_color', esc_html__( 'Hover Text Color', 'woolentor' ), $cta . ':hover' );
            $color( 'cta_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $cta . ':hover', 'background-color' );
            $size( 'cta_radius', esc_html__( 'Border Radius', 'woolentor' ), $cta, 'border-radius', 100 );
            $type( 'cta_typography', $cta );

        $end();

        $section( 'style_shop', esc_html__( 'Shop Link', 'woolentor' ), $gate( self::SHOP_VARIANTS ) );

            $shop = '{{WRAPPER}} .wl-fs-shop';

            $color( 'shop_color', esc_html__( 'Text Color', 'woolentor' ), $shop );
            $color( 'shop_bg', esc_html__( 'Background', 'woolentor' ), $shop, 'background-color' );
            $color( 'shop_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), '{{WRAPPER}} .wl-fs-item:hover .wl-fs-shop', 'background-color' );

        $end();

        // ── Arrows ────────────────────────────────────────────────────────────

        $section( 'style_arrows', esc_html__( 'Arrows', 'woolentor' ), $gate( self::RAIL_VARIANTS, 'slider_arrows' ) );

            $arrow = '{{WRAPPER}} .wl-fs-arrow';

            $size( 'arrow_size', esc_html__( 'Size', 'woolentor' ), '{{WRAPPER}} .wl-fs', '--wl-fs-arrow-size', 80 );
            $color( 'arrow_color', esc_html__( 'Icon Color', 'woolentor' ), $arrow );
            $color( 'arrow_bg', esc_html__( 'Background', 'woolentor' ), $arrow, 'background-color' );
            $color( 'arrow_hover_color', esc_html__( 'Hover Icon Color', 'woolentor' ), $arrow . ':hover:not(:disabled)' );
            $color( 'arrow_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $arrow . ':hover:not(:disabled)', 'background-color' );

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

            woolentor_upgrade_pro_notice( $this, 'flash_sale_style_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ]  );

        $this->end_controls_section();
    }

    // ── Header parts ──────────────────────────────────────────────────────────

    protected function eyebrow( array $settings ) {
        $text = trim( (string) ( $settings['eyebrow'] ?? '' ) );

        return '' !== $text ? '<span class="wl-fs-eyebrow">' . esc_html( $text ) . '</span>' : '';
    }

    protected function heading( array $settings ) {
        $text = trim( (string) ( $settings['heading'] ?? '' ) );

        if ( '' === $text ) {
            return '';
        }

        $tag = in_array( $settings['heading_tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4', 'div' ], true )
            ? $settings['heading_tag']
            : 'h2';

        return '<' . $tag . ' class="wl-fs-heading">'
            . wp_kses( $text, [ 'em' => [], 'strong' => [], 'br' => [] ] )
            . '</' . $tag . '>';
    }

    protected function description( array $settings ) {
        $text = trim( (string) ( $settings['description'] ?? '' ) );

        return '' !== $text ? '<p class="wl-fs-desc">' . esc_html( $text ) . '</p>' : '';
    }

    /**
     * The section's button. An empty link means the shop page.
     *
     * @param  array $settings
     * @return string
     */
    protected function cta( array $settings ) {
        $text = trim( (string) ( $settings['cta_text'] ?? '' ) );

        if ( '' === $text ) {
            return '';
        }

        $link = $settings['cta_link'] ?? [];
        $url  = ! empty( $link['url'] ) ? $link['url'] : wc_get_page_permalink( 'shop' );
        $rel  = [];

        if ( ! empty( $link['is_external'] ) ) {
            $rel[] = 'noopener';
        }

        if ( ! empty( $link['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<a class="wl-fs-cta" href="' . esc_url( $url ) . '"'
            . ( ! empty( $link['is_external'] ) ? ' target="_blank"' : '' )
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' )
            . '>' . esc_html( $text ) . '</a>';
    }

    /**
     * The timer. Numerals render here first, so a cached or script-less page is never 00:00:00.
     * WLPackCountdown takes over in the browser.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function countdown( array $settings, $style, $variant ) {
        $end = (int) ( $this->sale['end'] ?? 0 );

        if ( 'yes' !== ( $settings['countdown_show'] ?? 'yes' ) || ! $end ) {
            return '';
        }

        $units = array_values( array_intersect(
            self::UNITS,
            array_map( 'sanitize_key', (array) ( $settings['countdown_units'] ?? [] ) )
        ) );

        if ( ! $units ) {
            $units = $this->in_map( self::HMS_VARIANTS, $style, $variant )
                ? [ 'hours', 'minutes', 'seconds' ]
                : self::UNITS;
        }

        $action = sanitize_key( $settings['countdown_expired_action'] ?? 'hide-section' );
        $action = in_array( $action, [ 'hide', 'hide-section', 'message' ], true ) ? $action : 'hide-section';

        // The editor always keeps the section on screen so it can still be designed.
        if ( 'hide-section' === $action && $this->is_editor() ) {
            $action = 'hide';
        }

        $remaining = max( 0, $end - time() );
        $divisors  = [ 'days' => 86400, 'hours' => 3600, 'minutes' => 60, 'seconds' => 1 ];
        $separator = $this->in_map( self::NO_SEPARATOR_VARIANTS, $style, $variant ) ? '' : ':';
        $labels    = ! $this->in_map( self::NO_LABEL_VARIANTS, $style, $variant );
        $lead      = trim( (string) ( $settings['countdown_label'] ?? '' ) );

        $out = '<div class="wl-pack-countdown wl-fs-countdown" role="timer"'
            . ' data-wl-end="' . esc_attr( $end ) . '"'
            . ' data-wl-expired="' . esc_attr( $action ) . '"'
            . ' data-wl-expired-text="' . esc_attr( $settings['countdown_expired_text'] ?? '' ) . '"'
            . ' aria-label="' . esc_attr__( 'Time remaining in this sale', 'woolentor' ) . '">';

        if ( '' !== $lead && 'magazine' === $style && 'v1' === $variant ) {
            $out .= '<span class="wl-pack-countdown-lead">' . esc_html( $lead ) . '</span>';
        }

        $first = true;

        foreach ( $units as $unit ) {
            if ( ! $first && '' !== $separator ) {
                $out .= '<span class="wl-pack-countdown-sep" aria-hidden="true">' . esc_html( $separator ) . '</span>';
            }

            $value      = intdiv( $remaining, $divisors[ $unit ] );
            $remaining -= $value * $divisors[ $unit ];
            $first      = false;

            $out .= '<span class="wl-pack-countdown-unit">'
                . '<span class="wl-pack-countdown-num" data-unit="' . esc_attr( $unit ) . '">'
                . esc_html( str_pad( (string) $value, 2, '0', STR_PAD_LEFT ) ) . '</span>';

            if ( $labels && '' !== (string) ( $settings[ 'label_' . $unit ] ?? '' ) ) {
                $out .= '<span class="wl-pack-countdown-label">' . esc_html( $settings[ 'label_' . $unit ] ) . '</span>';
            }

            $out .= '</span>';
        }

        return $out . '</div>';
    }

    /**
     * The attribute pair that hands a rail to the shared Slick initialiser. Slick's own arrows stay
     * off: each template places its pair where its reference does.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  int    $items  What this layout shows on a desktop.
     * @return string
     */
    protected function slider_attr( array $settings, $style, $variant, $items = 3 ) {
        if ( ! $this->in_map( self::RAIL_VARIANTS, $style, $variant ) ) {
            return '';
        }

        $args = [
            'arrows'         => false,
            'dots'           => false,
            'infinite'       => 'yes' === ( $settings['slider_infinite'] ?? '' ),
            'autoplay'       => 'yes' === ( $settings['slider_autoplay'] ?? '' ),
            'autoplay_speed' => max( 500, (int) ( $settings['slider_autoplay_speed'] ?? 5000 ) ),
            'speed'          => max( 100, (int) ( $settings['slider_speed'] ?? 600 ) ),
            'pause_on_hover' => 'yes' === ( $settings['slider_pause_on_hover'] ?? 'yes' ),
            'items'          => max( 1, (int) $items ),
            'scroll'         => max( 1, (int) ( $settings['sl_scroll'] ?? 1 ) ),
            'tablet_width'   => max( 480, (int) ( $settings['sl_tablet_width'] ?? 1024 ) ),
            'tablet_items'   => max( 1, (int) ( $settings['sl_tablet_items'] ?? 2 ) ),
            'tablet_scroll'  => max( 1, (int) ( $settings['sl_tablet_scroll'] ?? 1 ) ),
            'mobile_width'   => max( 320, (int) ( $settings['sl_mobile_width'] ?? 767 ) ),
            'mobile_items'   => max( 1, (int) ( $settings['sl_mobile_items'] ?? 1 ) ),
            'mobile_scroll'  => max( 1, (int) ( $settings['sl_mobile_scroll'] ?? 1 ) ),
        ];

        return ' data-wl-slider="true" data-slider-settings=\'' . esc_attr( wp_json_encode( $args ) ) . '\'';
    }

    /**
     * Previous and next, for a rail. Hidden when every product is already showing.
     *
     * @param  array $items
     * @param  array $settings
     * @param  int   $shown
     * @return string
     */
    protected function arrows( array $items, array $settings, $shown ) {
        if ( count( $items ) <= $shown || 'yes' !== ( $settings['slider_arrows'] ?? 'yes' ) ) {
            return '';
        }

        return '<button type="button" class="wl-fs-arrow" data-wl-fs-prev aria-label="' . esc_attr__( 'Previous products', 'woolentor' ) . '">' . $this->icon( 'left' ) . '</button>'
            . '<button type="button" class="wl-fs-arrow" data-wl-fs-next aria-label="' . esc_attr__( 'Next products', 'woolentor' ) . '">' . $this->icon( 'right' ) . '</button>';
    }

    protected function icon( $name ) {
        $paths = [
            'left'  => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
            'right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
            'bag'   => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
            'flame' => '<path d="M12 2c1 4 6 6 6 12a6 6 0 0 1-12 0c0-2 1-4 2-5 0 2 1 3 2 3 1-3 0-6 2-10z"/>',
        ];

        return '<svg class="wl-fs-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ( $paths[ $name ] ?? '' ) . '</svg>';
    }

    // ── Card parts ────────────────────────────────────────────────────────────

    /**
     * Point WooCommerce's globals at a product, so its loop button and any plugin hooked to it see
     * the right one. render() restores them afterwards.
     *
     * @param \WC_Product $product
     */
    protected function set_product( $product ) {
        $GLOBALS['product'] = $product;
        $GLOBALS['post']    = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        setup_postdata( $GLOBALS['post'] );
    }

    /**
     * How many products a rail shows on a desktop.
     *
     * @param  array $settings
     * @param  int   $default  This layout's own count.
     * @return int
     */
    protected function shown( array $settings, $default ) {
        $items = (int) ( $settings['sl_items'] ?? 0 );

        return $items > 0 ? $items : $default;
    }

    /**
     * The merchant's own photograph.
     *
     * @param  array $settings
     * @return string
     */
    protected function photo( array $settings ) {
        $media = $settings['media_image'] ?? [];

        if ( ! empty( $media['id'] ) ) {
            $image = wp_get_attachment_image( $media['id'], 'large', false, [ 'class' => 'wl-fs-photo-img', 'loading' => 'lazy' ] );

            if ( $image ) {
                return $image;
            }
        }

        return ! empty( $media['url'] )
            ? '<img class="wl-fs-photo-img" src="' . esc_url( $media['url'] ) . '" alt="" loading="lazy">'
            : '';
    }

    protected function flag( array $settings ) {
        $text = trim( (string) ( $settings['media_flag'] ?? '' ) );

        return '' !== $text ? '<span class="wl-fs-flag">' . esc_html( $text ) . '</span>' : '';
    }

    protected function panel_head( array $settings ) {
        $text = trim( (string) ( $settings['panel_heading'] ?? '' ) );

        return '' !== $text ? '<span class="wl-fs-panel-head">' . esc_html( $text ) . '</span>' : '';
    }

    protected function fine( array $settings ) {
        $text = trim( (string) ( $settings['fine_print'] ?? '' ) );

        return '' !== $text ? '<p class="wl-fs-fine">' . esc_html( $text ) . '</p>' : '';
    }

    protected function percent( array $settings ) {
        $value  = trim( (string) ( $settings['percent_value'] ?? '' ) );
        $suffix = trim( (string) ( $settings['percent_suffix'] ?? '' ) );

        if ( '' === $value ) {
            return '';
        }

        return '<div class="wl-fs-percent"><span class="wl-fs-percent-num">' . esc_html( $value ) . '</span>'
            . ( '' !== $suffix ? '<span class="wl-fs-percent-suffix">' . esc_html( $suffix ) . '</span>' : '' )
            . '</div>';
    }

    /**
     * The product name without a link, for a card that is itself one link.
     *
     * @param  \WC_Product $product
     * @return string
     */
    protected function name( $product ) {
        return '<span class="wl-fs-title">' . esc_html( $product->get_name() ) . '</span>';
    }

    protected function shop_link( array $settings ) {
        $text = trim( (string) ( $settings['shop_text'] ?? '' ) );

        return '' !== $text ? '<span class="wl-fs-shop">' . esc_html( $text ) . '</span>' : '';
    }

    protected function thumb( $product, $size = 'woocommerce_thumbnail' ) {
        return '<a class="wl-fs-thumb" href="' . esc_url( $product->get_permalink() ) . '" tabindex="-1" aria-hidden="true">' . $this->image( $product, $size ) . '</a>';
    }

    /**
     * The image alone.
     *
     * @param  \WC_Product $product
     * @param  string      $size
     * @return string
     */
    protected function image( $product, $size = 'woocommerce_thumbnail' ) {
        // Read straight rather than through get_image(): the Badges module adds its own badge on that
        // filter, and its rule can print a different discount beside this card's real one.
        $image = wp_get_attachment_image( $product->get_image_id(), $size, false, [
            'class'   => 'wl-fs-img',
            'loading' => 'lazy',
            'alt'     => $product->get_name(),
        ] );

        if ( ! $image ) {
            $image = '<img class="wl-fs-img" src="' . esc_url( wc_placeholder_img_src( $size ) ) . '" alt="'
                . esc_attr( $product->get_name() ) . '" loading="lazy">';
        }

        return $image;
    }

    protected function title( $product, $tag = 'h3' ) {
        return '<' . $tag . ' class="wl-fs-title"><a href="' . esc_url( $product->get_permalink() ) . '">'
            . esc_html( $product->get_name() ) . '</a></' . $tag . '>';
    }

    /**
     * "-24%", or nothing when no honest figure exists.
     *
     * The module only changes the stored price at cart and checkout, so is_on_sale() and
     * get_sale_price() cannot see a deal on the homepage. A fixed amount off a variable product has
     * no single percent across its range, so it prints none.
     *
     * @param  \WC_Product $product
     * @return string
     */
    protected function discount_percent( $product ) {
        $deal = $this->product_deal( $product->get_id() );

        if ( $deal && ! empty( $deal['discount_value'] ) ) {
            if ( $product->is_type( 'variable' ) ) {
                return 'percentage_discount' === ( $deal['discount_type'] ?? '' )
                    ? '-' . round( (float) $deal['discount_value'] ) . '%'
                    : '';
            }

            // Measured from the two prices the card prints. The module can take its discount off
            // the sale price, so the deal's own figure may not be what the shopper sees.
            $regular = (float) $product->get_regular_price();
            $now     = (float) \Woolentor\Modules\FlashSale\Woolentor_Flash_Sale::get_calculated_price( $product->get_id(), $deal );

            return $regular > $now && $regular > 0 ? '-' . round( ( $regular - $now ) / $regular * 100 ) . '%' : '';
        }

        $regular = (float) $product->get_regular_price();
        $sale    = (float) $product->get_sale_price();

        if ( $product->is_type( 'variable' ) ) {
            $regular = (float) $product->get_variation_regular_price( 'max' );
            $sale    = (float) $product->get_variation_sale_price( 'min' );
        }

        if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
            return '';
        }

        return '-' . round( ( $regular - $sale ) / $regular * 100 ) . '%';
    }

    protected function badge( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_badge'] ?? 'yes' ) ) {
            return '';
        }

        $off = $this->discount_percent( $product );

        return '' !== $off ? '<span class="wl-fs-off">' . esc_html( $off ) . '</span>' : '';
    }

    /**
     * Now and was. A simple product on a deal gets its own pair, from the module's number and
     * formatted as the module formats it, because the module's price HTML has no old price.
     * Everything else is WooCommerce's own price HTML.
     *
     * @param  \WC_Product $product
     * @return string
     */
    protected function price( $product ) {
        $deal = $this->product_deal( $product->get_id() );

        if ( $deal && ! empty( $deal['discount_value'] ) && $product->is_type( [ 'simple', 'external' ] ) ) {
            $was = (float) $product->get_regular_price();
            $now = (float) \Woolentor\Modules\FlashSale\Woolentor_Flash_Sale::get_calculated_price( $product->get_id(), $deal );

            if ( $was > $now ) {
                return '<span class="wl-fs-price"><del>' . wc_price( $was ) . '</del><ins>' . wc_price( $now ) . '</ins></span>';
            }
        }

        // A variable product at one price reads like a simple one. A real price range stays a range.
        if ( $deal && ! empty( $deal['discount_value'] ) && $product->is_type( 'variable' ) ) {
            $was = (float) $product->get_variation_regular_price( 'min' );
            $now = (float) $product->get_variation_price( 'min' );

            if ( $was > $now
                && $was === (float) $product->get_variation_regular_price( 'max' )
                && $now === (float) $product->get_variation_price( 'max' ) ) {
                return '<span class="wl-fs-price"><del>' . wc_price( $was ) . '</del><ins>' . wc_price( $now ) . '</ins></span>';
            }
        }

        $html = $product->get_price_html();

        return $html ? '<span class="wl-fs-price">' . $html . '</span>' : '';
    }

    /**
     * Stock figures, only ever real. No managed stock means no figures at all.
     *
     * @param  \WC_Product $product
     * @return array|null
     */
    protected function stock_data( $product ) {
        if ( ! $product->managing_stock() ) {
            return null;
        }

        $left = (int) $product->get_stock_quantity();

        if ( $left <= 0 ) {
            return null;
        }

        $sold  = (int) $product->get_total_sales();
        $total = $sold + $left;

        return [
            'sold'    => $sold,
            'left'    => $left,
            'total'   => $total,
            'percent' => (int) round( $sold / $total * 100 ),
        ];
    }

    protected function stock_words( $text, array $data ) {
        return str_replace(
            [ '{sold}', '{total}', '{left}', '{percent}' ],
            [
                number_format_i18n( $data['sold'] ),
                number_format_i18n( $data['total'] ),
                number_format_i18n( $data['left'] ),
                number_format_i18n( $data['percent'] ),
            ],
            (string) $text
        );
    }

    protected function is_low( array $data, array $settings ) {
        return $data['left'] <= max( 1, (int) ( $settings['low_stock_threshold'] ?? 10 ) );
    }

    /**
     * The bar with its two lines of words, or one line of words on the layouts that have no bar.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  string      $style
     * @param  string      $variant
     * @return string
     */
    protected function stock( $product, array $settings, $style, $variant ) {
        if ( 'yes' !== ( $settings['show_stock'] ?? 'yes' ) ) {
            return '';
        }

        $data = $this->stock_data( $product );

        if ( ! $data ) {
            return '';
        }

        $left = $this->stock_words( $settings['stock_left_text'] ?? '', $data );

        if ( $this->in_map( self::STOCK_TEXT_VARIANTS, $style, $variant ) ) {
            return '' !== trim( $left ) ? '<span class="wl-fs-stock-left">' . esc_html( $left ) . '</span>' : '';
        }

        if ( ! $this->in_map( self::STOCK_BAR_VARIANTS, $style, $variant ) ) {
            return '';
        }

        $low = '';

        if ( $this->in_map( self::LOW_WORD_VARIANTS, $style, $variant ) && $this->is_low( $data, $settings ) ) {
            $low = trim( (string) ( $settings['stock_low_text'] ?? '' ) );
        }

        $right = '' !== $low
            ? '<span class="wl-fs-stock-left is-low">' . $this->icon( 'flame' ) . esc_html( $low ) . '</span>'
            : ( '' !== trim( $left ) ? '<span class="wl-fs-stock-left">' . esc_html( $left ) . '</span>' : '' );

        return '<div class="wl-fs-stock">'
            . '<div class="wl-fs-stock-bar" aria-hidden="true"><div class="wl-fs-stock-fill" style="width:' . esc_attr( $data['percent'] ) . '%"></div></div>'
            . '<div class="wl-fs-stock-meta"><span class="wl-fs-stock-sold">' . esc_html( $this->stock_words( $settings['stock_sold_text'] ?? '', $data ) ) . '</span>' . $right . '</div>'
            . '</div>';
    }

    /**
     * The chip over a card's image on a product that is running low.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @return string
     */
    protected function low_tag( $product, array $settings ) {
        $data = 'yes' === ( $settings['show_stock'] ?? 'yes' ) ? $this->stock_data( $product ) : null;
        $text = trim( (string) ( $settings['stock_low_text'] ?? '' ) );

        if ( ! $data || '' === $text || ! $this->is_low( $data, $settings ) ) {
            return '';
        }

        return '<span class="wl-fs-tag">' . $this->icon( 'flame' ) . esc_html( $text ) . '</span>';
    }

    /**
     * A category, or a brand when the store has a brand taxonomy and the control asks for one.
     *
     * @param  \WC_Product $product
     * @param  string      $taxonomy
     * @param  string      $class
     * @return string
     */
    protected function term( $product, $taxonomy, $class ) {
        $terms = get_the_terms( $product->get_id(), $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        return '<span class="' . esc_attr( $class ) . '">' . esc_html( $terms[0]->name ) . '</span>';
    }

    protected function category( $product, array $settings ) {
        return 'yes' === ( $settings['show_category'] ?? 'yes' )
            ? $this->term( $product, 'product_cat', 'wl-fs-cat' )
            : '';
    }

    protected function brand( $product, array $settings ) {
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

        return $this->term( $product, $taxonomy, 'wl-fs-brand' );
    }

    protected function rating( $product, array $settings ) {
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

        return '<span class="wl-fs-rating">' . $stars
            . '<span class="wl-fs-rating-avg">' . esc_html( number_format_i18n( (float) $product->get_average_rating(), 1 ) ) . '</span>'
            . '<span class="wl-fs-rating-count">(' . esc_html( number_format_i18n( $count ) ) . ')</span>'
            . '</span>';
    }

    /**
     * Wishlist, compare and quick view. Each is drawn only when its own plugin or module is active.
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  array       $parts  Any of 'wishlist', 'compare', 'quickview', in the order wanted.
     * @return string
     */
    protected function icons( $product, array $settings, array $parts = [ 'wishlist', 'compare', 'quickview' ] ) {
        $built = [];

        if ( 'yes' === ( $settings['show_wishlist'] ?? 'yes' )
            && function_exists( 'woolentor_add_to_wishlist_button' )
            && function_exists( 'woolentor_has_wishlist_plugin' )
            && true === woolentor_has_wishlist_plugin() ) {

            $normal = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.3l7.8-7.8 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>';
            $added  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.3l7.8-7.8 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>';

            $built['wishlist'] = '<span class="wl-fs-iconbtn wl-fs-wish">' . woolentor_add_to_wishlist_button( $normal, $added ) . '</span>';
        }

        if ( 'yes' === ( $settings['show_compare'] ?? 'yes' )
            && function_exists( 'woolentor_compare_button' )
            && function_exists( 'woolentor_exist_compare_plugin' )
            && true === woolentor_exist_compare_plugin() ) {

            $icon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="16 3 21 8 16 13"/><line x1="21" y1="8" x2="9" y2="8"/><polyline points="8 21 3 16 8 11"/><line x1="3" y1="16" x2="15" y2="16"/></svg>';

            ob_start();
            echo '<span class="wl-fs-iconbtn wl-fs-compare">';
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

            $built['quickview'] = '<button type="button" class="wl-fs-iconbtn wl-fs-qview woolentorquickview"'
                . ' data-product_id="' . esc_attr( $product->get_id() ) . '"'
                . ' aria-label="' . esc_attr( sprintf( /* translators: %s: product name */ __( 'Quick view of %s', 'woolentor' ), $product->get_name() ) ) . '">'
                . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
                . '</button>';
        }

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
    protected function cart( $product, array $settings, $icon = '' ) {
        if ( 'yes' !== ( $settings['show_cart'] ?? 'yes' ) ) {
            return '';
        }

        $label       = trim( (string) ( $settings['cart_text'] ?? '' ) );
        $text_filter = null;

        if ( '' !== $label ) {
            $text_filter = function () use ( $label ) {
                return $label;
            };
            add_filter( 'woocommerce_product_add_to_cart_text', $text_filter, 99 );
        }

        // Appended rather than passed in: WooCommerce's own class string carries the ajax hooks.
        $args_filter = function ( $args ) {
            $args['class'] = trim( ( $args['class'] ?? '' ) . ' wl-fs-cart' );
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

        if ( '' !== $icon ) {
            $html = preg_replace( '/(<a\b[^>]*>)/', '$1' . $icon, $html, 1 );
        }

        return '<span class="wl-fs-cart-wrap">' . $html . '</span>';
    }

    protected function swatches( $product, array $settings ) {
        if ( 'yes' !== ( $settings['show_swatches'] ?? 'yes' ) ) {
            return '';
        }

        $slug     = sanitize_title( $settings['swatch_attribute'] ?? 'color' );
        $taxonomy = 0 === strpos( $slug, 'pa_' ) ? $slug : 'pa_' . $slug;
        $limit    = max( 1, absint( $settings['swatch_limit'] ?? 4 ) );

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return '';
        }

        $terms = get_the_terms( $product->get_id(), $taxonomy );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        $dots = '';

        foreach ( array_slice( array_values( $terms ), 0, $limit ) as $term ) {
            $colour = get_term_meta( $term->term_id, 'product_attribute_color', true );

            if ( '' === $colour ) {
                $colour = get_term_meta( $term->term_id, 'wl_swatch_color', true );
            }

            // A term with no colour saved has nothing to draw; a blank dot would say nothing.
            if ( ! $colour ) {
                continue;
            }

            $dots .= '<span class="wl-fs-swatch" title="' . esc_attr( $term->name ) . '" style="background:' . esc_attr( $colour ) . ';"></span>';
        }

        return '' !== $dots ? '<span class="wl-fs-swatches">' . $dots . '</span>' : '';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    private function editor_notices( array $items, array $settings, array $sale ) {
        if ( ! $this->is_editor() ) {
            return;
        }

        $notes = [];

        if ( '' !== $sale['note'] ) {
            $notes[] = $sale['note'];
        }

        if ( ! $items ) {
            $notes[] = 'manual' === $sale['source']
                ? __( 'Nothing to show yet: choose some products under Content > Sale.', 'woolentor' )
                : __( 'No products match right now: nothing is on sale in stock for this source.', 'woolentor' );
        }

        foreach ( $notes as $note ) {
            echo '<p class="elementor-alert elementor-alert-info">' . esc_html( $note ) . '</p>';
        }
    }

    /**
     * Run a template with WooCommerce's globals pointed at each product in turn, then put them back.
     *
     * @param string $template
     * @param array  $items
     * @param array  $settings
     * @param string $style
     * @param string $variant
     */
    protected function render_template( $template, array $items, array $settings, $style, $variant ) {
        $previous = $GLOBALS['product'] ?? null;

        echo '<div class="wl-fs wl-fs-' . esc_attr( $style ) . ' wl-fs-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-fs>';
        include $template;
        echo '</div>';

        wp_reset_postdata();
        $GLOBALS['product'] = $previous;
    }

    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! $this->is_editor() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Flash Sale' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Flash Sale' );
            return;
        }

        $this->sale = $this->resolve( $settings );
        $items      = $this->sale_products( $settings, $this->sale );

        if ( ! $items ) {
            $this->editor_notices( $items, $settings, $this->sale );
            return;
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        $this->render_template( $template, $items, $settings, $style, $variant );
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

        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            $this->render_pro_preview( $style, $variant, $settings );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Flash Sale template not found.', 'woolentor' ) . '</p>';
            return;
        }

        $this->sale = $this->resolve( $settings );

        // A sale a shopper must not see prints nothing at all. The editor keeps it on screen, with
        // a note, so the section can still be designed.
        if ( ! $this->sale['live'] && ! $this->is_editor() ) {
            return;
        }

        $items = $this->sale_products( $settings, $this->sale );

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';

        if ( $items ) {
            $this->render_template( $template, $items, $settings, $style, $variant );
        }

        $this->editor_notices( $items, $settings, $this->sale );

        echo '</div>';
    }
}
