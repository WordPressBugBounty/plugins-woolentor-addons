<?php
/**
 * Compare Table - 2026 Widget.
 *
 * Two to four products side by side: a header per product, rows of specs, a button per column.
 * The products are either picked here or read from the visitor's own compare list (the Compare
 * module or the Ever Compare plugin).
 *
 * The visitor's list lives in a cookie, so with that source the page carries only an empty shell
 * and the table is fetched through the Product Kit's section endpoint. A cached page therefore
 * never shows one visitor's list to another. Known limit, shared with Product Showcase's tabs: the
 * nonce in a cached page goes stale after about a day, and a failed request leaves the section
 * hidden, which is also what an empty list does.
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Compare_Table_Widget extends Widget_Base implements \WooLentor\Product_Kit\Renders_Section {

    /** One column per product, with the rows inside each column. */
    const CARD_VARIANTS = [
        'modern'   => [ 'v1', 'v2' ],
        'magazine' => [ 'v3' ],
    ];

    /** The copy block beside the table instead of above it. */
    const SPLIT_VARIANTS = [ 'editorial' => [ 'v3' ] ];

    /** A CSS grid table with a label column. */
    const GRID_VARIANTS = [ 'magazine' => [ 'v2' ] ];

    /** "Full compare tool →" beside the heading. */
    const LINK_VARIANTS = [ 'modern' => [ 'v1', 'v2', 'v3' ] ];

    /** Copy in the first header cell, above the labels. */
    const CAPTION_VARIANTS = [ 'magazine' => [ 'v2' ] ];

    /** "Editor's Pick" on a highlighted product. */
    const BADGE_VARIANTS = [
        'modern'   => [ 'v1', 'v2' ],
        'magazine' => [ 'v2', 'v3' ],
    ];

    /** The product's category in small capitals above its name. */
    const KICKER_VARIANTS = [ 'editorial' => [ 'v1' ] ];

    /** The price under the name in the header. Luxury V3 prices only through a row. */
    const HEAD_PRICE_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    /** An attribute row whose terms carry a colour draws dots. */
    const SWATCH_VARIANTS = [ 'magazine' => [ 'v2' ] ];

    /** Rating as "4.8 ★" rather than a row of stars. */
    const RATING_TEXT_VARIANTS = [ 'magazine' => [ 'v2' ] ];

    /** Yes / no marks drawn as circled icons. */
    const CIRCLE_MARK_VARIANTS = [ 'magazine' => [ 'v2' ] ];

    /** "Add to Cart — $449". */
    const CART_PRICE_VARIANTS = [ 'magazine' => [ 'v1' ] ];

    /** The foot is a link to the product rather than a cart button. */
    const VIEW_LINK_VARIANTS = [ 'luxury' => [ 'v3' ] ];

    /** The layouts whose table sits inside a ruled frame. */
    const FRAME_BORDER_VARIANTS = [
        'modern' => [ 'v1' ],
        'luxury' => [ 'v2' ],
    ];

    /** Everything that ends in a cart button. */
    const CART_VARIANTS = [
        'modern'    => [ 'v1', 'v2', 'v3' ],
        'editorial' => [ 'v1', 'v2', 'v3' ],
        'luxury'    => [ 'v1', 'v2' ],
        'magazine'  => [ 'v1', 'v2', 'v3' ],
    ];

    const MAX_PRODUCTS = 4;

    const DATA_TYPES = [ 'price', 'rating', 'stock', 'sku', 'weight', 'dimensions', 'short_description', 'attribute', 'meta', 'manual' ];

    const REMOVE_NONCE = 'woolentor_compare_table_remove';

    /**
     * Facts about the current render that the helpers need.
     *
     * @var array
     */
    protected $state = [];

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

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-compare-table-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/compare-table/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-compare-table-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    public function get_script_depends() {
        return [ 'wl-pack-widgets' ];
    }

    public function get_name() {
        return 'woolentor-compare-table';
    }

    public function get_title() {
        return esc_html__( 'Compare Table - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-table';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'compare', 'comparison', 'table', 'specs', 'versus', 'side by side', 'pack', 'style', 'woolentor' ];
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
     * Never on a locked variant, and only on the variants in $map when one is given.
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

    protected function is_editor() {
        return \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    // ── Compare list ──────────────────────────────────────────────────────────

    /**
     * The Compare module and the Ever Compare plugin both declare this class, with the same
     * methods, and the module switches the plugin off. One path covers both.
     *
     * @return bool
     */
    protected function has_compare() {
        return class_exists( '\EverCompare\Frontend\Manage_Compare' )
            && method_exists( '\EverCompare\Frontend\Manage_Compare', 'instance' );
    }

    /**
     * The ids in the visitor's cookie, oldest first. The module's own reader does not sanitise
     * them, so this does.
     *
     * @return int[]
     */
    protected function compared_ids() {
        if ( ! $this->has_compare() ) {
            return [];
        }

        $ids = \EverCompare\Frontend\Manage_Compare::instance()->get_compared_products();

        return array_values( array_unique( array_filter( array_map( 'absint', is_array( $ids ) ? $ids : [] ) ) ) );
    }

    /**
     * The compare page, when the module or the plugin has one set.
     *
     * @return string
     */
    protected function compare_page_url() {
        if ( ! $this->has_compare() ) {
            return '';
        }

        // get_permalink() of an unset page is the current post's address, so the option is read first.
        $page = function_exists( 'woolentor_get_option' ) ? woolentor_get_option( 'compare_page', 'ever_compare_table_settings_tabs' ) : '';

        if ( ! $page && function_exists( 'ever_compare_get_option' ) ) {
            $page = ever_compare_get_option( 'compare_page', 'ever_compare_table_settings_tabs' );
        }

        if ( ! $page ) {
            return '';
        }

        $url = \EverCompare\Frontend\Manage_Compare::instance()->get_compare_page_url();

        return $url ? (string) $url : '';
    }

    // ── Products ──────────────────────────────────────────────────────────────

    /**
     * A product a shopper may see.
     *
     * @param  int $id
     * @return \WC_Product|null
     */
    protected function visible_product( $id ) {
        $product = $id ? wc_get_product( $id ) : null;

        if ( ! $product || 'publish' !== get_post_status( $product->get_id() ) || ! $product->is_visible() ) {
            return null;
        }

        return $product;
    }

    /**
     * The products for this render.
     *
     * Each item keeps `pos`, its 1-based place in the Products list, because the manual values of a
     * row are typed in that order. A skipped product takes its values with it, and the next one
     * keeps its own.
     *
     * @param  array $settings
     * @param  bool  $visitor  Read the visitor's cookie rather than the Products list.
     * @return array[] { product, pos, highlight }
     */
    protected function items( array $settings, $visitor = false ) {
        if ( $visitor ) {
            $limit = min( self::MAX_PRODUCTS, max( 2, (int) ( $settings['limit'] ?? 3 ) ) );
            $items = [];

            // The products added last win.
            foreach ( array_reverse( $this->compared_ids() ) as $id ) {
                $product = $this->visible_product( $id );

                if ( $product ) {
                    $items[] = [ 'product' => $product, 'pos' => 0, 'highlight' => false ];
                }

                if ( count( $items ) >= $limit ) {
                    break;
                }
            }

            return array_reverse( $items );
        }

        $items = [];
        $rows  = array_slice( array_values( (array) ( $settings['products'] ?? [] ) ), 0, self::MAX_PRODUCTS );

        foreach ( $rows as $index => $row ) {
            $product = $this->visible_product( absint( $row['product'] ?? 0 ) );

            if ( $product ) {
                $items[] = [
                    'product'   => $product,
                    'pos'       => $index + 1,
                    'highlight' => 'yes' === ( $row['highlight'] ?? '' ),
                ];
            }
        }

        return $items;
    }

    /**
     * What the editor shows for the visitor source: the picked products, or the latest three.
     *
     * @param  array $settings
     * @return array[]
     */
    protected function preview_items( array $settings ) {
        $items = $this->items( $settings );

        if ( $items ) {
            return $items;
        }

        $ids = wc_get_products( [
            'status'     => 'publish',
            'visibility' => 'visible',
            'limit'      => 3,
            'orderby'    => 'date',
            'order'      => 'DESC',
            'return'     => 'ids',
        ] );

        foreach ( $ids as $id ) {
            $product = $this->visible_product( $id );

            if ( $product ) {
                $items[] = [ 'product' => $product, 'pos' => 0, 'highlight' => false ];
            }
        }

        return $items;
    }

    // ── Rows ──────────────────────────────────────────────────────────────────

    /**
     * The rows that have something to say, each with one cell per product.
     *
     * Manual rows belong to the Products list, so the visitor's list skips them. A row that is
     * empty for every product is not drawn: with the visitor's list the merchant cannot know which
     * products will be in it.
     *
     * @param  array   $settings
     * @param  array[] $items
     * @param  bool    $visitor
     * @param  string  $style
     * @param  string  $variant
     * @return array[] { label, data, cells[] }
     */
    protected function rows( array $settings, array $items, $visitor, $style, $variant ) {
        $rows = [];

        $this->state['skipped_manual'] = [];

        foreach ( (array) ( $settings['rows'] ?? [] ) as $row ) {
            $data  = in_array( $row['data'] ?? '', self::DATA_TYPES, true ) ? $row['data'] : 'manual';
            $label = trim( (string) ( $row['label'] ?? '' ) );

            if ( 'manual' === $data && $visitor ) {
                $this->state['skipped_manual'][] = '' !== $label ? $label : __( '(no label)', 'woolentor' );
                continue;
            }

            $cells = [];
            $any   = false;

            foreach ( $items as $item ) {
                $value   = $this->cell_value( $row, $data, $item, $style, $variant );
                $cells[] = $value;
                $any     = $any || '' !== $value;
            }

            if ( $any ) {
                $rows[] = [ 'label' => $label, 'data' => $data, 'cells' => $cells ];
            }
        }

        return $rows;
    }

    /**
     * One cell, as safe HTML, or an empty string.
     *
     * @param  array  $row
     * @param  string $data
     * @param  array  $item
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function cell_value( array $row, $data, array $item, $style, $variant ) {
        /** @var \WC_Product $product */
        $product = $item['product'];

        switch ( $data ) {
            case 'price':
                return (string) $product->get_price_html();

            case 'rating':
                return $this->rating( $product, $style, $variant );

            case 'stock':
                return esc_html( $this->stock_text( $product ) );

            case 'sku':
                return $this->text( $product->get_sku(), $style, $variant );

            case 'weight':
                $weight = $product->get_weight();
                return '' !== (string) $weight ? esc_html( wp_strip_all_tags( wc_format_weight( $weight ) ) ) : '';

            case 'dimensions':
                return $product->has_dimensions()
                    ? esc_html( wp_strip_all_tags( wc_format_dimensions( $product->get_dimensions( false ) ) ) )
                    : '';

            case 'short_description':
                $parent = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : null;
                $text   = $product->get_short_description() ?: ( $parent ? $parent->get_short_description() : '' );
                return esc_html( wp_trim_words( wp_strip_all_tags( (string) $text ), 20 ) );

            case 'attribute':
                return $this->attribute( $product, (string) ( $row['attribute'] ?? '' ), $style, $variant );

            case 'meta':
                $key = trim( (string) ( $row['meta_key'] ?? '' ) );

                if ( '' === $key ) {
                    return '';
                }

                $value = get_post_meta( $product->get_id(), $key, true );

                return is_scalar( $value ) ? $this->text( (string) $value, $style, $variant ) : '';

            case 'manual':
            default:
                $pos = (int) $item['pos'];
                return $pos >= 1 && $pos <= self::MAX_PRODUCTS
                    ? $this->text( (string) ( $row[ 'value_' . $pos ] ?? '' ), $style, $variant )
                    : '';
        }
    }

    /**
     * Plain text, or a yes / no mark when the text says only that.
     *
     * @param  string $text
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function text( $text, $style, $variant ) {
        $text = trim( (string) $text );

        if ( '' === $text ) {
            return '';
        }

        $word = $this->lower( $text );
        $yes  = [ 'yes', 'true', '✓', '✔', $this->lower( __( 'Yes', 'woolentor' ) ) ];
        $no   = [ 'no', 'false', '✗', '✕', 'x', $this->lower( __( 'No', 'woolentor' ) ) ];

        if ( in_array( $word, $yes, true ) ) {
            return $this->mark( true, $style, $variant );
        }

        if ( in_array( $word, $no, true ) ) {
            return $this->mark( false, $style, $variant );
        }

        return esc_html( $text );
    }

    protected function lower( $text ) {
        return function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $text, 'UTF-8' ) : strtolower( (string) $text );
    }

    protected function mark( $yes, $style, $variant ) {
        $circle = $this->in_map( self::CIRCLE_MARK_VARIANTS, $style, $variant );

        if ( $yes ) {
            $icon  = $circle ? 'check-circle' : 'check';
            $label = __( 'Yes', 'woolentor' );
        } else {
            $icon  = $circle ? 'x-circle' : 'dash';
            $label = __( 'No', 'woolentor' );
        }

        return '<span class="wl-ct-' . ( $yes ? 'yes' : 'no' ) . '">' . $this->icon( $icon )
            . '<span class="wl-ct-sr">' . esc_html( $label ) . '</span></span>';
    }

    protected function stock_text( $product ) {
        if ( ! $product->is_in_stock() ) {
            return __( 'Out of stock', 'woolentor' );
        }

        $availability = $product->get_availability();
        $text         = wp_strip_all_tags( (string) ( $availability['availability'] ?? '' ) );

        if ( '' !== $text ) {
            return $text;
        }

        return $product->is_on_backorder() ? __( 'Available on backorder', 'woolentor' ) : __( 'In stock', 'woolentor' );
    }

    protected function rating( $product, $style, $variant ) {
        $parent = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
        $count  = $parent ? (int) $parent->get_rating_count() : 0;

        if ( $count < 1 || 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
            return '';
        }

        $average = number_format_i18n( (float) $parent->get_average_rating(), 1 );

        if ( $this->in_map( self::RATING_TEXT_VARIANTS, $style, $variant ) ) {
            return '<span class="wl-ct-rating">' . esc_html( $average ) . ' <span aria-hidden="true">★</span>'
                . '<span class="wl-ct-sr">' . esc_html__( 'out of 5', 'woolentor' ) . '</span></span>';
        }

        $stars = function_exists( 'woolentor_wc_product_rating_generate' ) ? woolentor_wc_product_rating_generate( $parent ) : '';

        return '<span class="wl-ct-rating">' . $stars
            . '<span class="wl-ct-rating-avg">' . esc_html( $average ) . '</span></span>';
    }

    /**
     * A global attribute's term names, or a custom attribute's value. A variation reads its parent.
     *
     * @param  \WC_Product $product
     * @param  string      $name  A name or slug: Color, color or pa_color.
     * @param  string      $style
     * @param  string      $variant
     * @return string
     */
    protected function attribute( $product, $name, $style, $variant ) {
        $name = trim( $name );

        if ( '' === $name ) {
            return '';
        }

        if ( $product->get_parent_id() ) {
            $product = wc_get_product( $product->get_parent_id() );

            if ( ! $product ) {
                return '';
            }
        }

        $slug     = sanitize_title( $name );
        $taxonomy = 0 === strpos( $slug, 'pa_' ) ? $slug : wc_attribute_taxonomy_name( $slug );

        if ( taxonomy_exists( $taxonomy ) ) {
            $terms = wc_get_product_terms( $product->get_id(), $taxonomy, [ 'fields' => 'all' ] );

            if ( is_wp_error( $terms ) || ! $terms ) {
                return '';
            }

            if ( $this->in_map( self::SWATCH_VARIANTS, $style, $variant ) ) {
                $dots = $this->swatches( $terms );

                if ( '' !== $dots ) {
                    return $dots;
                }
            }

            return $this->text( implode( ', ', wp_list_pluck( $terms, 'name' ) ), $style, $variant );
        }

        $wanted = $this->lower( $name );

        foreach ( $product->get_attributes() as $attribute ) {
            if ( ! is_object( $attribute ) || $attribute->is_taxonomy() ) {
                continue;
            }

            $own = $this->lower( $attribute->get_name() );

            if ( $own === $wanted || sanitize_title( $attribute->get_name() ) === $slug ) {
                return $this->text( implode( ', ', $attribute->get_options() ), $style, $variant );
            }
        }

        return '';
    }

    /**
     * Colour dots for terms that have a colour. The Variation Swatch module saves swatchly_color;
     * the other two keys match the other pack widgets. Terms with no colour keep their names.
     *
     * @param  \WP_Term[] $terms
     * @return string
     */
    protected function swatches( array $terms ) {
        $out   = '';
        $drawn = 0;

        foreach ( $terms as $term ) {
            $colour = '';

            foreach ( [ 'swatchly_color', 'product_attribute_color', 'wl_swatch_color' ] as $key ) {
                $colour = (string) get_term_meta( $term->term_id, $key, true );

                if ( '' !== $colour ) {
                    break;
                }
            }

            $colour = sanitize_hex_color( $colour ) ?: ( preg_match( '/^[a-z]+$/i', $colour ) ? $colour : '' );

            if ( '' === $colour ) {
                $out .= '<span class="wl-ct-swatch-name">' . esc_html( $term->name ) . '</span>';
                continue;
            }

            $drawn++;
            $out .= '<span class="wl-ct-swatch" title="' . esc_attr( $term->name ) . '" style="background:' . esc_attr( $colour ) . ';">'
                . '<span class="wl-ct-sr">' . esc_html( $term->name ) . '</span></span>';
        }

        return $drawn ? '<span class="wl-ct-swatches">' . $out . '</span>' : '';
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
                woolentor_upgrade_pro_notice( $this, 'compare_table_variant_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ], [ 'mode' => 'alert' ] );
            }

        $this->end_controls_section();

        $this->register_product_controls();
        $this->register_row_controls();
        $this->register_header_controls();
        $this->register_column_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    private function register_product_controls() {
        $this->start_controls_section( 'section_products', [
            'label'      => esc_html__( 'Products', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $sources = [ 'handpick' => esc_html__( 'Choose products', 'woolentor' ) ];

            if ( $this->has_compare() ) {
                $sources['compare_list'] = esc_html__( 'Visitor\'s compare list', 'woolentor' );
            }

            $this->add_control( 'source', [
                'label'       => esc_html__( 'Products From', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'handpick',
                'options'     => $sources,
                'description' => $this->has_compare()
                    ? esc_html__( 'The visitor\'s compare list shows what each visitor added to compare. When it is empty, the section is hidden.', 'woolentor' )
                    : esc_html__( 'Turn on the Compare module to show each visitor\'s own compare list.', 'woolentor' ),
            ] );

            $repeater = new Repeater();

            $repeater->add_control( 'product', [
                'label'       => esc_html__( 'Product', 'woolentor' ),
                'type'        => 'woolentor-select',
                'ajax_search' => true,
                'post_type'   => 'product',
                'label_block' => true,
                'placeholder' => esc_html__( 'Search products…', 'woolentor' ),
            ] );

            $repeater->add_control( 'highlight', [
                'label'        => esc_html__( 'Highlight', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
                'description'  => esc_html__( 'Sets this column apart as the one you recommend.', 'woolentor' ),
            ] );

            $this->add_control( 'products', [
                'label'         => esc_html__( 'Products', 'woolentor' ),
                'type'          => Controls_Manager::REPEATER,
                'fields'        => $repeater->get_controls(),
                'default'       => [ [ 'product' => '' ], [ 'product' => '', 'highlight' => 'yes' ], [ 'product' => '' ] ],
                'title_field'   => esc_html__( 'Product', 'woolentor' ) . ' <# if ( "yes" === highlight ) { #>★<# } #>',
                'max_items'     => self::MAX_PRODUCTS,
                'prevent_empty' => false,
                'description'   => esc_html__( 'Up to 4. Their order here is the order of the Product 1 to Product 4 values in Rows.', 'woolentor' ),
                'condition'     => [ 'source!' => 'compare_list' ],
            ] );

            if ( $this->has_compare() ) {
                $this->add_control( 'limit', [
                    'label'       => esc_html__( 'Products Shown', 'woolentor' ),
                    'type'        => Controls_Manager::NUMBER,
                    'default'     => 3,
                    'min'         => 2,
                    'max'         => self::MAX_PRODUCTS,
                    'description' => esc_html__( 'The products the visitor added last.', 'woolentor' ),
                    'condition'   => [ 'source' => 'compare_list' ],
                ] );
            }

        $this->end_controls_section();
    }

    private function register_row_controls() {
        $this->start_controls_section( 'section_rows', [
            'label'      => esc_html__( 'Rows', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $repeater = new Repeater();

            $repeater->add_control( 'label', [
                'label'       => esc_html__( 'Label', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
            ] );

            $repeater->add_control( 'data', [
                'label'   => esc_html__( 'Value', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'manual',
                'options' => [
                    'manual'            => esc_html__( 'Type it per product', 'woolentor' ),
                    'price'             => esc_html__( 'Price', 'woolentor' ),
                    'rating'            => esc_html__( 'Rating', 'woolentor' ),
                    'stock'             => esc_html__( 'Availability', 'woolentor' ),
                    'sku'               => esc_html__( 'SKU', 'woolentor' ),
                    'weight'            => esc_html__( 'Weight', 'woolentor' ),
                    'dimensions'        => esc_html__( 'Dimensions', 'woolentor' ),
                    'short_description' => esc_html__( 'Short description', 'woolentor' ),
                    'attribute'         => esc_html__( 'Product attribute', 'woolentor' ),
                    'meta'              => esc_html__( 'Custom field', 'woolentor' ),
                ],
            ] );

            $repeater->add_control( 'attribute', [
                'label'       => esc_html__( 'Attribute Name', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Color', 'woolentor' ),
                'description' => esc_html__( 'As it is named under Products > Attributes, or in the product.', 'woolentor' ),
                'condition'   => [ 'data' => 'attribute' ],
            ] );

            $repeater->add_control( 'meta_key', [
                'label'     => esc_html__( 'Custom Field Key', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'condition' => [ 'data' => 'meta' ],
            ] );

            for ( $i = 1; $i <= self::MAX_PRODUCTS; $i++ ) {
                $repeater->add_control( 'value_' . $i, [
                    /* translators: %d: position of the product in the Products list */
                    'label'       => sprintf( esc_html__( 'Product %d', 'woolentor' ), $i ),
                    'type'        => Controls_Manager::TEXT,
                    'label_block' => true,
                    'description' => 1 === $i ? esc_html__( 'In the order of the Products list. Type Yes or No for a mark. Only used with chosen products.', 'woolentor' ) : '',
                    'condition'   => [ 'data' => 'manual' ],
                ] );
            }

            $this->add_control( 'rows', [
                'label'       => esc_html__( 'Rows', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [ 'label' => esc_html__( 'Rating', 'woolentor' ), 'data' => 'rating' ],
                    [ 'label' => esc_html__( 'Availability', 'woolentor' ), 'data' => 'stock' ],
                    [ 'label' => esc_html__( 'Weight', 'woolentor' ), 'data' => 'weight' ],
                    [ 'label' => esc_html__( 'Dimensions', 'woolentor' ), 'data' => 'dimensions' ],
                ],
                'title_field' => '{{{ label }}}',
                'description' => esc_html__( 'A row that is empty for every product is not shown. An empty cell shows a dash.', 'woolentor' ),
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
                'default'     => esc_html__( 'Compare top models', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'heading', [
                'label'       => esc_html__( 'Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Pick the one that fits you.', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Accepts <em>, <strong> and <br>.', 'woolentor' ),
            ] );

            $this->add_control( 'heading_tag', [
                'label'   => esc_html__( 'Heading Tag', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'h2',
                'options' => [ 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ],
            ] );

            $this->add_control( 'description', [
                'label'   => esc_html__( 'Description', 'woolentor' ),
                'type'    => Controls_Manager::TEXTAREA,
                'rows'    => 2,
                'default' => esc_html__( 'Our best sellers side by side, so the differences are easy to see.', 'woolentor' ),
            ] );

            $this->add_control( 'caption_title', [
                'label'      => esc_html__( 'Table Caption', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'The line-up', 'woolentor' ),
                'conditions' => $this->build_conditions( self::CAPTION_VARIANTS ),
            ] );

            $this->add_control( 'caption_text', [
                'label'      => esc_html__( 'Caption Text', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Compare materials, fit & finish', 'woolentor' ),
                'conditions' => $this->build_conditions( self::CAPTION_VARIANTS ),
            ] );

            $this->add_control( 'link_text', [
                'label'      => esc_html__( 'Link Text', 'woolentor' ),
                'type'       => Controls_Manager::TEXT,
                'default'    => esc_html__( 'Full compare tool', 'woolentor' ),
                'separator'  => 'before',
                'conditions' => $this->build_conditions( self::LINK_VARIANTS ),
            ] );

            $this->add_control( 'link_url', [
                'label'       => esc_html__( 'Link', 'woolentor' ),
                'type'        => Controls_Manager::URL,
                'placeholder' => esc_html__( 'Leave empty for the compare page', 'woolentor' ),
                'description' => esc_html__( 'Empty uses the Compare module\'s compare page. With neither, no link is shown.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::LINK_VARIANTS ),
            ] );

        $this->end_controls_section();
    }

    private function register_column_controls() {
        $this->start_controls_section( 'section_columns', [
            'label'      => esc_html__( 'Product Columns', 'woolentor' ),
            'conditions' => $this->gate(),
        ] );

            $switch = function ( $id, $label, array $extra = [] ) {
                $this->add_control( $id, array_merge( [
                    'label'        => $label,
                    'type'         => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default'      => 'yes',
                ], $extra ) );
            };

            $switch( 'show_image', esc_html__( 'Image', 'woolentor' ) );
            $switch( 'show_price', esc_html__( 'Price Under the Name', 'woolentor' ), [
                'conditions' => $this->build_conditions( self::HEAD_PRICE_VARIANTS ),
            ] );
            $switch( 'show_kicker', esc_html__( 'Category Above the Name', 'woolentor' ), [
                'conditions' => $this->build_conditions( self::KICKER_VARIANTS ),
            ] );

            $this->add_control( 'badge_text', [
                'label'       => esc_html__( 'Highlight Badge', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Editor\'s Pick', 'woolentor' ),
                'description' => esc_html__( 'On the highlighted products. Empty shows no badge.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::BADGE_VARIANTS ),
            ] );

            $switch( 'show_cart', esc_html__( 'Button', 'woolentor' ), [ 'separator' => 'before' ] );

            $this->add_control( 'cart_text', [
                'label'       => esc_html__( 'Add to Cart Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'description' => esc_html__( 'Empty keeps WooCommerce\'s own wording. Products that need options keep theirs.', 'woolentor' ),
                'conditions'  => $this->all_of(
                    $this->build_conditions( self::CART_VARIANTS ),
                    [ 'relation' => 'and', 'terms' => [ [ 'name' => 'show_cart', 'operator' => '==', 'value' => 'yes' ] ] ]
                ),
            ] );

            $this->add_control( 'view_text', [
                'label'       => esc_html__( 'Link Text', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                /* translators: %s: stays as is; it is replaced by the product name */
                'default'     => esc_html__( 'View %s', 'woolentor' ),
                'description' => esc_html__( '%s is replaced by the product name.', 'woolentor' ),
                'conditions'  => $this->all_of(
                    $this->build_conditions( self::VIEW_LINK_VARIANTS ),
                    [ 'relation' => 'and', 'terms' => [ [ 'name' => 'show_cart', 'operator' => '==', 'value' => 'yes' ] ] ]
                ),
            ] );

            if ( $this->has_compare() ) {
                $switch( 'show_remove', esc_html__( 'Remove Button', 'woolentor' ), [
                    'separator'   => 'before',
                    'description' => esc_html__( 'Lets visitors take a product off their compare list.', 'woolentor' ),
                    'condition'   => [ 'source' => 'compare_list' ],
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

            woolentor_upgrade_pro_notice( $this, 'compare_table_content_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ] );

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

            $color( 'section_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-ct', 'background-color' );
            $pad( 'section_padding', esc_html__( 'Padding', 'woolentor' ), '{{WRAPPER}} .wl-ct' );

        $end();

        // ── Header ────────────────────────────────────────────────────────────

        $section( 'style_header', esc_html__( 'Header', 'woolentor' ), $editable );

            $color( 'eyebrow_color', esc_html__( 'Eyebrow Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-eyebrow' );
            $type( 'eyebrow_typography', '{{WRAPPER}} .wl-ct-eyebrow' );

            $color( 'heading_color', esc_html__( 'Heading Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-heading', 'color', $before );
            $color( 'heading_accent_color', esc_html__( 'Accent Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-heading em, {{WRAPPER}} .wl-ct-heading strong', 'color', [
                'description' => esc_html__( 'For the words wrapped in <em> or <strong> in the heading.', 'woolentor' ),
            ] );
            $type( 'heading_typography', '{{WRAPPER}} .wl-ct-heading' );

            $color( 'desc_color', esc_html__( 'Description Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-desc', 'color', $before );
            $type( 'desc_typography', '{{WRAPPER}} .wl-ct-desc' );

            $color( 'link_color', esc_html__( 'Link Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-link', 'color', $before + $on( self::LINK_VARIANTS ) );
            $color( 'link_hover_color', esc_html__( 'Link Hover Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-link:hover', 'color', $on( self::LINK_VARIANTS ) );

            $color( 'caption_title_color', esc_html__( 'Caption Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-caption-title', 'color', $before + $on( self::CAPTION_VARIANTS ) );
            $color( 'caption_text_color', esc_html__( 'Caption Text Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-caption-text', 'color', $on( self::CAPTION_VARIANTS ) );

        $end();

        // ── Frame ─────────────────────────────────────────────────────────────

        $section( 'style_frame', esc_html__( 'Table', 'woolentor' ), $editable );

            $color( 'frame_bg', esc_html__( 'Background', 'woolentor' ), '{{WRAPPER}} .wl-ct-frame', 'background' );
            $color( 'frame_border', esc_html__( 'Border Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-frame', 'border-color', $on( self::FRAME_BORDER_VARIANTS ) );
            $size( 'frame_radius', esc_html__( 'Border Radius', 'woolentor' ), '{{WRAPPER}} .wl-ct-frame', 'border-radius', 60 );
            $color( 'line_color', esc_html__( 'Divider Color', 'woolentor' ), '{{WRAPPER}} .wl-ct', '--wl-ct-line', [
                'description' => esc_html__( 'The lines between columns and rows.', 'woolentor' ),
            ] );

        $end();

        // ── Product header ────────────────────────────────────────────────────

        $section( 'style_cell', esc_html__( 'Product Header', 'woolentor' ), $editable );

            $size( 'thumb_size', esc_html__( 'Image Size', 'woolentor' ), '{{WRAPPER}} .wl-ct', '--wl-ct-thumb', 320, [
                'condition' => [ 'show_image' => 'yes' ],
            ] );
            $size( 'thumb_radius', esc_html__( 'Image Radius', 'woolentor' ), '{{WRAPPER}} .wl-ct-thumb, {{WRAPPER}} .wl-ct-thumb img', 'border-radius', 200, [
                'condition' => [ 'show_image' => 'yes' ],
            ] );
            $color( 'thumb_bg', esc_html__( 'Image Background', 'woolentor' ), '{{WRAPPER}} .wl-ct-thumb', 'background-color', [
                'condition' => [ 'show_image' => 'yes' ],
            ] );

            $color( 'kicker_color', esc_html__( 'Category Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-kicker', 'color', $before + $on( self::KICKER_VARIANTS ) + [ 'condition' => [ 'show_kicker' => 'yes' ] ] );

            $color( 'name_color', esc_html__( 'Name Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-name, {{WRAPPER}} .wl-ct-name a', 'color', $before );
            $color( 'name_hover_color', esc_html__( 'Name Hover Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-name a:hover' );
            $type( 'name_typography', '{{WRAPPER}} .wl-ct-name' );

            $color( 'price_color', esc_html__( 'Price Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-price, {{WRAPPER}} .wl-ct-price ins', 'color', $before + $on( self::HEAD_PRICE_VARIANTS ) + [ 'condition' => [ 'show_price' => 'yes' ] ] );
            $color( 'price_was_color', esc_html__( 'Old Price Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-price del', 'color', $on( self::HEAD_PRICE_VARIANTS ) + [ 'condition' => [ 'show_price' => 'yes' ] ] );
            $type( 'price_typography', '{{WRAPPER}} .wl-ct-price', $on( self::HEAD_PRICE_VARIANTS ) + [ 'condition' => [ 'show_price' => 'yes' ] ] );

        $end();

        // ── Highlight ─────────────────────────────────────────────────────────

        $section( 'style_highlight', esc_html__( 'Highlighted Product', 'woolentor' ), $editable );

            $color( 'highlight_bg', esc_html__( 'Column Background', 'woolentor' ), '{{WRAPPER}} .wl-ct', '--wl-ct-hl-bg' );

            $color( 'badge_color', esc_html__( 'Badge Text', 'woolentor' ), '{{WRAPPER}} .wl-ct-badge', 'color', $before + $on( self::BADGE_VARIANTS ) );
            $color( 'badge_bg', esc_html__( 'Badge Background', 'woolentor' ), '{{WRAPPER}} .wl-ct-badge', 'background-color', $on( self::BADGE_VARIANTS ) );
            $size( 'badge_radius', esc_html__( 'Badge Radius', 'woolentor' ), '{{WRAPPER}} .wl-ct-badge', 'border-radius', 40, $on( self::BADGE_VARIANTS ) );
            $type( 'badge_typography', '{{WRAPPER}} .wl-ct-badge', $on( self::BADGE_VARIANTS ) );

        $end();

        // ── Rows ──────────────────────────────────────────────────────────────

        $section( 'style_rows', esc_html__( 'Rows', 'woolentor' ), $editable );

            $color( 'label_color', esc_html__( 'Label Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-label' );
            $type( 'label_typography', '{{WRAPPER}} .wl-ct-label' );

            $color( 'value_color', esc_html__( 'Value Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-value', 'color', $before );
            $type( 'value_typography', '{{WRAPPER}} .wl-ct-value' );

            $size( 'row_padding', esc_html__( 'Row Height', 'woolentor' ), '{{WRAPPER}} .wl-ct', '--wl-ct-row-pad', 40, $before + [
                'description' => esc_html__( 'Space above and below each value.', 'woolentor' ),
            ] );

            $color( 'yes_color', esc_html__( 'Yes Mark Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-yes', 'color', $before );
            $color( 'no_color', esc_html__( 'No Mark Color', 'woolentor' ), '{{WRAPPER}} .wl-ct-no' );

        $end();

        // ── Buttons ───────────────────────────────────────────────────────────

        $cart = '{{WRAPPER}} .wl-ct-cart-wrap a.button';

        $section( 'style_cart', esc_html__( 'Add to Cart', 'woolentor' ), $gate( self::CART_VARIANTS, 'show_cart' ) );

            $color( 'cart_color', esc_html__( 'Text Color', 'woolentor' ), $cart );
            $color( 'cart_bg', esc_html__( 'Background', 'woolentor' ), $cart, 'background-color' );
            $color( 'cart_border', esc_html__( 'Border Color', 'woolentor' ), $cart, 'border-color' );
            $color( 'cart_hover_color', esc_html__( 'Hover Text Color', 'woolentor' ), $cart . ':hover', 'color', $before );
            $color( 'cart_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $cart . ':hover', 'background-color' );
            $color( 'cart_hover_border', esc_html__( 'Hover Border Color', 'woolentor' ), $cart . ':hover', 'border-color' );

            $hl = '{{WRAPPER}} .is-highlight .wl-ct-cart-wrap a.button';

            $color( 'cart_hl_color', esc_html__( 'Highlighted: Text Color', 'woolentor' ), $hl, 'color', $before );
            $color( 'cart_hl_bg', esc_html__( 'Highlighted: Background', 'woolentor' ), $hl, 'background-color' );

            $size( 'cart_radius', esc_html__( 'Border Radius', 'woolentor' ), $cart, 'border-radius', 40, $before );
            $type( 'cart_typography', $cart );

        $end();

        $section( 'style_view', esc_html__( 'Product Link', 'woolentor' ), $gate( self::VIEW_LINK_VARIANTS, 'show_cart' ) );

            $view = '{{WRAPPER}} .wl-ct-view';

            $color( 'view_color', esc_html__( 'Text Color', 'woolentor' ), $view );
            $color( 'view_border', esc_html__( 'Border Color', 'woolentor' ), $view, 'border-color' );
            $color( 'view_hover_color', esc_html__( 'Hover Text Color', 'woolentor' ), $view . ':hover' );
            $color( 'view_hover_bg', esc_html__( 'Hover Background', 'woolentor' ), $view . ':hover', 'background-color' );

            $color( 'view_hl_color', esc_html__( 'Highlighted: Text Color', 'woolentor' ), '{{WRAPPER}} .is-highlight .wl-ct-view', 'color', $before );
            $color( 'view_hl_bg', esc_html__( 'Highlighted: Background', 'woolentor' ), '{{WRAPPER}} .is-highlight .wl-ct-view', 'background-color' );

            $type( 'view_typography', $view, $before );

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

            woolentor_upgrade_pro_notice( $this, 'compare_table_style_pro_notice', [ 'variant' => [ 'v2', 'v3' ] ] );

        $this->end_controls_section();
    }

    // ── Header parts ──────────────────────────────────────────────────────────

    protected function eyebrow( array $settings ) {
        $text = trim( (string) ( $settings['eyebrow'] ?? '' ) );

        return '' !== $text ? '<span class="wl-ct-eyebrow">' . esc_html( $text ) . '</span>' : '';
    }

    protected function heading_text( array $settings ) {
        return trim( (string) ( $settings['heading'] ?? '' ) );
    }

    protected function heading( array $settings ) {
        $text = $this->heading_text( $settings );

        if ( '' === $text ) {
            return '';
        }

        $tag = in_array( $settings['heading_tag'] ?? 'h2', [ 'h1', 'h2', 'h3', 'h4', 'div' ], true )
            ? $settings['heading_tag']
            : 'h2';

        return '<' . $tag . ' class="wl-ct-heading">'
            . wp_kses( $text, [ 'em' => [], 'strong' => [], 'br' => [] ] )
            . '</' . $tag . '>';
    }

    protected function description( array $settings ) {
        $text = trim( (string) ( $settings['description'] ?? '' ) );

        return '' !== $text ? '<p class="wl-ct-desc">' . esc_html( $text ) . '</p>' : '';
    }

    /**
     * The link beside the heading. An empty URL means the compare page, and with no compare page
     * there is no link.
     *
     * @param  array $settings
     * @return string
     */
    protected function link( array $settings ) {
        $text = trim( (string) ( $settings['link_text'] ?? '' ) );

        if ( '' === $text ) {
            return '';
        }

        $link = $settings['link_url'] ?? [];
        $url  = ! empty( $link['url'] ) ? $link['url'] : $this->compare_page_url();

        if ( '' === $url ) {
            return '';
        }

        $rel = [];

        if ( ! empty( $link['is_external'] ) ) {
            $rel[] = 'noopener';
        }

        if ( ! empty( $link['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<a class="wl-ct-link" href="' . esc_url( $url ) . '"'
            . ( ! empty( $link['is_external'] ) ? ' target="_blank"' : '' )
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' )
            . '>' . esc_html( $text ) . $this->icon( 'arrow' ) . '</a>';
    }

    /**
     * The section head: eyebrow, heading and description, then the link when the variant has one.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function head( array $settings, $style, $variant ) {
        $copy = $this->eyebrow( $settings ) . $this->heading( $settings ) . $this->description( $settings );
        $link = $this->in_map( self::LINK_VARIANTS, $style, $variant ) ? $this->link( $settings ) : '';

        if ( '' === $copy && '' === $link ) {
            return '';
        }

        return '<div class="wl-ct-head"><div class="wl-ct-head-copy">' . $copy . '</div>' . $link . '</div>';
    }

    /**
     * The table's own name, for screen readers: the heading without its markup.
     *
     * @param  array $settings
     * @return string
     */
    protected function table_caption( array $settings ) {
        $text = trim( wp_strip_all_tags( str_replace( '<br>', ' ', $this->heading_text( $settings ) ) ) );

        if ( '' === $text ) {
            $text = __( 'Product comparison', 'woolentor' );
        }

        return '<caption class="wl-ct-sr">' . esc_html( $text ) . '</caption>';
    }

    protected function caption( array $settings ) {
        $title = trim( (string) ( $settings['caption_title'] ?? '' ) );
        $text  = trim( (string) ( $settings['caption_text'] ?? '' ) );

        return ( '' !== $title ? '<span class="wl-ct-caption-title">' . esc_html( $title ) . '</span>' : '' )
            . ( '' !== $text ? '<span class="wl-ct-caption-text">' . esc_html( $text ) . '</span>' : '' );
    }

    protected function icon( $name ) {
        $paths = [
            'arrow'        => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
            'check'        => '<polyline points="20 6 9 17 4 12"/>',
            'dash'         => '<line x1="6" y1="12" x2="18" y2="12"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9"/><polyline points="8.5 12.5 11 15 15.5 9.5"/>',
            'x-circle'     => '<circle cx="12" cy="12" r="9"/><line x1="9.5" y1="9.5" x2="14.5" y2="14.5"/><line x1="14.5" y1="9.5" x2="9.5" y2="14.5"/>',
            'close'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'crown'        => '<path d="M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5z"/>',
        ];

        return '<svg class="wl-ct-icon wl-ct-icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ( $paths[ $name ] ?? '' ) . '</svg>';
    }

    // ── Product header parts ──────────────────────────────────────────────────

    /**
     * Point WooCommerce's globals at a product, so its loop button and any plugin hooked to it see
     * the right one. render_template() restores them afterwards.
     *
     * @param \WC_Product $product
     */
    protected function set_product( $product ) {
        $GLOBALS['product'] = $product;
        $GLOBALS['post']    = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        setup_postdata( $GLOBALS['post'] );
    }

    protected function thumb( array $item, array $settings ) {
        if ( 'yes' !== ( $settings['show_image'] ?? 'yes' ) ) {
            return '';
        }

        $product = $item['product'];

        // Read straight rather than through get_image(): the Badges module adds its own badge there.
        $image = wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, [
            'class'   => 'wl-ct-img',
            'loading' => 'lazy',
            'alt'     => '',
        ] );

        if ( ! $image ) {
            $image = '<img class="wl-ct-img" src="' . esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ) . '" alt="" loading="lazy">';
        }

        return '<a class="wl-ct-thumb" href="' . esc_url( $product->get_permalink() ) . '" tabindex="-1" aria-hidden="true">' . $image . '</a>';
    }

    protected function name( array $item, $tag = 'h3' ) {
        $product = $item['product'];

        return '<' . $tag . ' class="wl-ct-name"><a href="' . esc_url( $product->get_permalink() ) . '">'
            . esc_html( $product->get_name() ) . '</a>'
            . ( $item['highlight'] && '' === $this->badge_label() ? '<span class="wl-ct-sr"> ' . esc_html__( '(recommended)', 'woolentor' ) . '</span>' : '' )
            . '</' . $tag . '>';
    }

    protected function kicker( array $item, array $settings, $style, $variant ) {
        if ( ! $this->in_map( self::KICKER_VARIANTS, $style, $variant ) || 'yes' !== ( $settings['show_kicker'] ?? 'yes' ) ) {
            return '';
        }

        $product = $item['product'];
        $terms   = get_the_terms( $product->get_parent_id() ?: $product->get_id(), 'product_cat' );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        return '<span class="wl-ct-kicker">' . esc_html( $terms[0]->name ) . '</span>';
    }

    protected function price( array $item, array $settings, $style, $variant ) {
        if ( ! $this->in_map( self::HEAD_PRICE_VARIANTS, $style, $variant ) || 'yes' !== ( $settings['show_price'] ?? 'yes' ) ) {
            return '';
        }

        $html = $item['product']->get_price_html();

        return $html ? '<div class="wl-ct-price">' . $html . '</div>' : '';
    }

    /**
     * The badge text for this render, or empty when this variant draws none.
     *
     * @return string
     */
    protected function badge_label() {
        return (string) ( $this->state['badge'] ?? '' );
    }

    protected function badge( array $item ) {
        $text = $this->badge_label();

        if ( ! $item['highlight'] || '' === $text ) {
            return '';
        }

        $crown = $this->in_map( self::CIRCLE_MARK_VARIANTS, $this->state['style'] ?? '', $this->state['variant'] ?? '' )
            ? $this->icon( 'crown' )
            : '';

        return '<span class="wl-ct-badge">' . $crown . esc_html( $text ) . '</span>';
    }

    /**
     * Take a product off the visitor's list. Drawn only for that source, and inert in the editor.
     *
     * @param  array $item
     * @return string
     */
    protected function remove( array $item ) {
        if ( empty( $this->state['remove'] ) ) {
            return '';
        }

        $product = $item['product'];

        return '<button type="button" class="wl-ct-remove" data-wl-ct-remove="' . esc_attr( $product->get_id() ) . '"'
            . ' aria-label="' . esc_attr( sprintf( /* translators: %s: product name */ __( 'Remove %s from compare', 'woolentor' ), $product->get_name() ) ) . '">'
            . $this->icon( 'close' ) . '</button>';
    }

    /**
     * Everything at the top of one product's column.
     *
     * @param  array  $item
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function cell( array $item, array $settings, $style, $variant ) {
        return '<div class="wl-ct-cell">'
            . $this->remove( $item )
            . $this->thumb( $item, $settings )
            . $this->kicker( $item, $settings, $style, $variant )
            . $this->name( $item )
            . $this->price( $item, $settings, $style, $variant )
            . '</div>';
    }

    // ── Foot ──────────────────────────────────────────────────────────────────

    /**
     * The button at the end of a column: WooCommerce's own loop button, or a link to the product.
     *
     * @param  array  $item
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    protected function foot( array $item, array $settings, $style, $variant ) {
        if ( 'yes' !== ( $settings['show_cart'] ?? 'yes' ) ) {
            return '';
        }

        $product = $item['product'];

        if ( $this->in_map( self::VIEW_LINK_VARIANTS, $style, $variant ) ) {
            $text = trim( (string) ( $settings['view_text'] ?? '' ) );
            $text = '' !== $text ? str_replace( '%s', $product->get_name(), $text ) : $product->get_name();

            return '<a class="wl-ct-view" href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $text ) . '</a>';
        }

        // WooCommerce's loop button reads the global product.
        $this->set_product( $product );

        return $this->cart( $product, $settings, $this->in_map( self::CART_PRICE_VARIANTS, $style, $variant ) );
    }

    /**
     * WooCommerce's own loop add-to-cart, so variable products still go to the product page and
     * ajax simple products still add in place. Only the label is ours, and only where WooCommerce
     * would have said "Add to cart".
     *
     * @param  \WC_Product $product
     * @param  array       $settings
     * @param  bool        $with_price  Append " — $449" to a simple product's label.
     * @return string
     */
    protected function cart( $product, array $settings, $with_price = false ) {
        $label = trim( (string) ( $settings['cart_text'] ?? '' ) );

        $text_filter = function ( $text, $for = null ) use ( $label, $with_price, $product ) {
            $for = $for ?: $product;

            if ( ! $for->is_type( 'simple' ) || ! $for->is_purchasable() || ! $for->is_in_stock() ) {
                return $text;
            }

            $text = '' !== $label ? $label : $text;

            if ( $with_price ) {
                $price = html_entity_decode( wp_strip_all_tags( wc_price( wc_get_price_to_display( $for ) ) ), ENT_QUOTES, 'UTF-8' );
                $text .= ' — ' . $price;
            }

            return $text;
        };
        add_filter( 'woocommerce_product_add_to_cart_text', $text_filter, 99, 2 );

        // Appended rather than passed in: WooCommerce's own class string carries the ajax hooks.
        $args_filter = function ( $args ) {
            $args['class'] = trim( ( $args['class'] ?? '' ) . ' wl-ct-cart' );
            return $args;
        };
        add_filter( 'woocommerce_loop_add_to_cart_args', $args_filter, 99 );

        ob_start();
        woocommerce_template_loop_add_to_cart();
        $html = ob_get_clean();

        remove_filter( 'woocommerce_loop_add_to_cart_args', $args_filter, 99 );
        remove_filter( 'woocommerce_product_add_to_cart_text', $text_filter, 99 );

        return '' !== trim( $html ) ? '<span class="wl-ct-cart-wrap">' . $html . '</span>' : '';
    }

    // ── Shared layouts ────────────────────────────────────────────────────────

    /**
     * The value of one row for one product, or a dash.
     *
     * @param  array $row
     * @param  int   $index
     * @return string
     */
    protected function value( array $row, $index ) {
        $value = $row['cells'][ $index ] ?? '';

        return '' !== $value
            ? $value
            : '<span class="wl-ct-empty" aria-hidden="true">–</span><span class="wl-ct-sr">' . esc_html__( 'Not specified', 'woolentor' ) . '</span>';
    }

    protected function hl( array $item ) {
        return $item['highlight'] ? ' is-highlight' : '';
    }

    /**
     * A real table: one column per product, one row per spec, the buttons in the foot.
     *
     * @param  array[] $items
     * @param  array[] $rows
     * @param  array   $settings
     * @param  string  $style
     * @param  string  $variant
     * @param  string  $corner  Markup for the empty cell above the labels.
     * @return string
     */
    protected function table( array $items, array $rows, array $settings, $style, $variant, $corner = '' ) {
        $out = '<div class="wl-ct-frame"><table class="wl-ct-table wl-ct-count-' . count( $items ) . '">'
            . $this->table_caption( $settings )
            . '<thead><tr><th class="wl-ct-corner" scope="col">'
            . ( '' !== $corner ? $corner : '<span class="wl-ct-sr">' . esc_html__( 'Feature', 'woolentor' ) . '</span>' )
            . '</th>';

        foreach ( $items as $item ) {
            $out .= '<th class="wl-ct-col-head' . $this->hl( $item ) . '" scope="col">' . $this->badge( $item ) . $this->cell( $item, $settings, $style, $variant ) . '</th>';
        }

        $out .= '</tr></thead><tbody>';

        foreach ( $rows as $row ) {
            $out .= '<tr class="wl-ct-row wl-ct-row-' . esc_attr( $row['data'] ) . '"><th class="wl-ct-label" scope="row">' . esc_html( $row['label'] ) . '</th>';

            foreach ( $items as $index => $item ) {
                $out .= '<td class="wl-ct-value' . $this->hl( $item ) . '">' . $this->value( $row, $index ) . '</td>';
            }

            $out .= '</tr>';
        }

        $out .= '</tbody>';

        $foot = '';

        foreach ( $items as $item ) {
            $foot .= '<td class="wl-ct-foot' . $this->hl( $item ) . '">' . $this->foot( $item, $settings, $style, $variant ) . '</td>';
        }

        if ( 'yes' === ( $settings['show_cart'] ?? 'yes' ) ) {
            $out .= '<tfoot><tr><td class="wl-ct-corner"></td>' . $foot . '</tr></tfoot>';
        }

        return $out . '</table></div>';
    }

    /**
     * One card per product, each carrying its own labels.
     *
     * @param  array[] $items
     * @param  array[] $rows
     * @param  array   $settings
     * @param  string  $style
     * @param  string  $variant
     * @return string
     */
    protected function cards( array $items, array $rows, array $settings, $style, $variant ) {
        $out = '<div class="wl-ct-frame"><div class="wl-ct-cards wl-ct-count-' . count( $items ) . '" role="list">';

        foreach ( $items as $index => $item ) {
            $out .= '<div class="wl-ct-col' . $this->hl( $item ) . '" role="listitem">'
                . $this->badge( $item )
                . $this->cell( $item, $settings, $style, $variant );

            if ( $rows ) {
                $out .= '<dl class="wl-ct-specs">';

                foreach ( $rows as $row ) {
                    $out .= '<div class="wl-ct-row wl-ct-row-' . esc_attr( $row['data'] ) . '"><dt class="wl-ct-label">' . esc_html( $row['label'] ) . '</dt>'
                        . '<dd class="wl-ct-value">' . $this->value( $row, $index ) . '</dd></div>';
                }

                $out .= '</dl>';
            }

            $foot = $this->foot( $item, $settings, $style, $variant );

            $out .= ( '' !== $foot ? '<div class="wl-ct-foot">' . $foot . '</div>' : '' ) . '</div>';
        }

        return $out . '</div></div>';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Run a template with WooCommerce's globals in place, then put them back.
     *
     * @param  string  $template
     * @param  array[] $items
     * @param  array[] $rows
     * @param  array   $settings
     * @param  string  $style
     * @param  string  $variant
     * @return string
     */
    protected function render_template( $template, array $items, array $rows, array $settings, $style, $variant ) {
        $previous = $GLOBALS['product'] ?? null;
        $post     = $GLOBALS['post'] ?? null;

        ob_start();
        echo '<div class="wl-ct wl-ct-' . esc_attr( $style ) . ' wl-ct-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '" data-wl-ct>';
        include $template;
        echo '</div>';
        $html = ob_get_clean();

        $GLOBALS['product'] = $previous;
        $GLOBALS['post']    = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        if ( $post instanceof \WP_Post ) {
            setup_postdata( $post );
        }

        return $html;
    }

    /**
     * What the template needs to know beyond its arguments.
     *
     * @param array  $settings
     * @param string $style
     * @param string $variant
     * @param bool   $remove
     */
    protected function prepare( array $settings, $style, $variant, $remove ) {
        $badge = $this->in_map( self::BADGE_VARIANTS, $style, $variant ) ? trim( (string) ( $settings['badge_text'] ?? '' ) ) : '';

        $this->state = array_merge( $this->state, [
            'style'   => $style,
            'variant' => $variant,
            'badge'   => $badge,
            'remove'  => $remove,
        ] );
    }

    /**
     * The whole section for a set of products, or an empty string when there is nothing to show.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @param  array  $items
     * @param  bool   $visitor
     * @param  bool   $remove
     * @return string
     */
    protected function build( array $settings, $style, $variant, array $items, $visitor, $remove ) {
        $template = \WooLentor\Style_Pack_Manager::resolve_template( $this->templates_dir(), $style, $variant );

        if ( ! $template || ! $items ) {
            return '';
        }

        $this->prepare( $settings, $style, $variant, $remove );

        $rows = $this->rows( $settings, $items, $visitor, $style, $variant );

        return $this->render_template( $template, $items, $rows, $settings, $style, $variant );
    }

    /**
     * The table for the visitor's list — the Product Kit's Renders_Section contract.
     *
     * The settings come from the saved document, never from the browser, and the product ids come
     * from the visitor's cookie on the server.
     *
     * @param  array $settings
     * @param  array $context
     * @return array
     */
    public function render_section( array $settings, array $context ) {
        $empty   = [ 'html' => '', 'pagination' => '' ];
        $style   = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() )
            || 'compare_list' !== ( $settings['source'] ?? '' )
            || ! $this->has_compare() ) {
            return $empty;
        }

        $html = $this->build( $settings, $style, $variant, $this->items( $settings, true ), true, 'yes' === ( $settings['show_remove'] ?? 'yes' ) );

        return [ 'html' => $html, 'pagination' => '' ];
    }

    /**
     * The empty shell a page carries for the visitor's list. The script fills it, or hides the
     * element when the list is empty.
     *
     * @return string
     */
    protected function shell() {
        $attrs = \WooLentor\Product_Kit\Section_Ajax::attrs( [
            'provider'   => \WooLentor\Product_Kit\Elementor_Provider::NAME,
            'post_id'    => \WooLentor\Product_Kit\Elementor_Provider::current_post_id(),
            'section_id' => $this->get_id(),
            'enabled'    => true,
        ] );

        if ( '' === $attrs ) {
            return '';
        }

        return '<div class="wl-ct-shell" data-wl-ct-shell'
            . $attrs // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside Section_Ajax::attrs()
            . ' data-wl-ct-nonce="' . esc_attr( wp_create_nonce( self::REMOVE_NONCE ) ) . '"></div>';
    }

    private function editor_notes( array $notes ) {
        if ( ! $this->is_editor() ) {
            return;
        }

        foreach ( array_filter( $notes ) as $note ) {
            echo '<p class="elementor-alert elementor-alert-info">' . esc_html( $note ) . '</p>';
        }
    }

    /**
     * Notes about what the editor is showing.
     *
     * @param  array $settings
     * @param  bool  $visitor
     * @param  array $items
     * @return array
     */
    protected function notes( array $settings, $visitor, array $items ) {
        $notes = [];

        if ( $visitor ) {
            $notes[] = __( 'Preview: visitors see their own compare list here, and nothing when it is empty.', 'woolentor' );
        } elseif ( ! $items ) {
            $notes[] = __( 'Choose up to four products under Content > Products.', 'woolentor' );
        } else {
            $picked = count( array_filter( (array) ( $settings['products'] ?? [] ), function ( $row ) {
                return ! empty( $row['product'] );
            } ) );

            if ( $picked > count( $items ) ) {
                $notes[] = __( 'Some chosen products are not published or are hidden from the catalogue, so they are left out.', 'woolentor' );
            }
        }

        if ( ! empty( $this->state['skipped_manual'] ) ) {
            $notes[] = sprintf(
                /* translators: %s: comma-separated row labels */
                __( 'Typed values belong to chosen products, so these rows are not shown with the visitor\'s compare list: %s', 'woolentor' ),
                implode( ', ', $this->state['skipped_manual'] )
            );
        }

        return $notes;
    }

    private function render_pro_preview( $style, $variant, array $settings ) {
        if ( ! $this->is_editor() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Compare Table' );
            return;
        }

        $visitor = 'compare_list' === ( $settings['source'] ?? '' );
        $items   = $visitor ? $this->preview_items( $settings ) : $this->items( $settings );
        $html    = $this->build( $settings, $style, $variant, $items, $visitor, false );

        if ( '' === $html ) {
            $this->editor_notes( $this->notes( $settings, $visitor, $items ) );
            return;
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
        echo '<div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.78);color:#fff;'
            . 'padding:4px 10px;border-radius:3px;font-size:11px;font-weight:700;pointer-events:none;z-index:99;">'
            . esc_html__( 'Pro — Preview Only', 'woolentor' )
            . '</div>';
        echo '</div>';
    }

    protected function render() {
        if ( ! class_exists( '\WooLentor\Style_Pack_Manager' ) || ! function_exists( 'wc_get_product' ) ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $style    = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant  = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            $this->render_pro_preview( $style, $variant, $settings );
            return;
        }

        $visitor = 'compare_list' === ( $settings['source'] ?? '' );

        if ( $visitor && ! $this->has_compare() ) {
            $this->editor_notes( [ __( 'This section shows the visitor\'s compare list, but the Compare module is off, so visitors see nothing. Turn the module on, or choose products instead.', 'woolentor' ) ] );
            return;
        }

        // The visitor's list is fetched in the browser, so a cached page never carries anyone's list.
        if ( $visitor && ! $this->is_editor() ) {
            $shell = $this->shell();

            if ( '' !== $shell ) {
                echo '<div data-wl-pack="' . esc_attr( $style ) . '">' . $shell . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
            }

            return;
        }

        $items = $visitor ? $this->preview_items( $settings ) : $this->items( $settings );
        $html  = $this->build( $settings, $style, $variant, $items, $visitor, $visitor && 'yes' === ( $settings['show_remove'] ?? 'yes' ) );

        if ( '' !== $html ) {
            echo '<div data-wl-pack="' . esc_attr( $style ) . '">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
        }

        $this->editor_notes( $this->notes( $settings, $visitor, $items ) );
    }
}
