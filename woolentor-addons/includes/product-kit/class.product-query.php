<?php
namespace WooLentor\Product_Kit;

/**
 * The kit's query layer.
 *
 * This builds no query. It normalises a settings array — from an Elementor widget, a Gutenberg
 * block, or anything else — into the keys `WooLentor_Product_Grid_Base` already understands, and
 * hands it over. The base is in turn the adapter onto `WooLentor_WooCommerce_Query_Manager`, which
 * is where query types, taxonomy filters, the Product Filter module and archive awareness live.
 *
 * Everything a product section needs from the catalogue goes through here, so a widget and a block
 * with the same settings produce the same query — that is the whole contract.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Product_Query {

    /**
     * Sources the query manager's apply_query_type() understands.
     */
    const SOURCES = [
        'recent',
        'featured',
        'best_selling',
        'top_rated',
        'sale',
        'recently_viewed',
        'products',
        'manual',
        'current_query',
    ];

    const ORDER_BY = [ 'date', 'title', 'price', 'popularity', 'rating', 'menu_order', 'rand' ];

    /**
     * Settings in, grid-base settings out.
     *
     * @param  array $settings
     * @param  int   $paged  Page to fetch. Zero means the section is not paginated at all, which is
     *                       not the same as page one: with pagination off the base must not fold
     *                       the page's own `paged` var into the query and blank the section on
     *                       page two of whatever else is on that page.
     * @return array
     */
    public static function build_args( array $settings, $paged = 0 ) {
        $source     = self::source( $settings, 'query_type' );
        $paginated  = $paged > 0;

        $args = [
            'query_type'           => $source,
            'orderby'              => Settings::choice( $settings, 'orderby', self::ORDER_BY, 'date' ),
            'order'                => 'ASC' === strtoupper( (string) ( $settings['order'] ?? 'DESC' ) ) ? 'ASC' : 'DESC',
            'posts_per_page'       => Settings::number( $settings, 'posts_per_page', 8, 1, 100 ),
            'categories'           => Settings::slugs( $settings, 'categories' ),
            'tags'                 => Settings::slugs( $settings, 'tags' ),
            'include_products'     => 'manual' === $source ? Settings::ids( $settings, 'include_products' ) : [],
            'exclude_products'     => Settings::ids( $settings, 'exclude_products' ),
            'exclude_out_of_stock' => Settings::truthy( $settings, 'exclude_out_of_stock' ),
            'exclude_no_image'     => Settings::truthy( $settings, 'exclude_no_image' ),
            'enable_filters'       => Settings::truthy( $settings, 'enable_filters', true ),
            'enable_pagination'    => $paginated,
        ];

        // Passed explicitly rather than left to the base's get_query_var() fallback, which reads
        // only `paged` — a static page numbers itself with `page`. Pagination::current_page()
        // reads both, and it is the caller's job to have asked it.
        if ( $paginated ) {
            $args['paged'] = (int) $paged;
        }

        // The Product Filter module's selection, when one arrived with this request. Left absent on
        // an ordinary page load, where the module reads the selection out of the URL itself — the
        // grid base runs the hook either way, and an empty argument is how it says "read it".
        if ( ! empty( $settings[ Filter::ARG_KEY ] ) && is_array( $settings[ Filter::ARG_KEY ] ) ) {
            $args[ Filter::ARG_KEY ] = $settings[ Filter::ARG_KEY ];
        }

        $queried = self::queried_object( $settings );

        if ( $queried ) {
            $args['queried_object'] = $queried;
        }

        return apply_filters( 'woolentor_product_kit_query_args', $args, $settings, $paged );
    }

    /**
     * A source the site's licence actually covers.
     *
     * Five of the nine sources are Pro. A free site that picked one keeps the setting — it is only
     * refused at the moment it would be used, so the page starts working the day Pro is installed
     * rather than needing to be edited again.
     *
     * @param  array  $settings
     * @param  string $key
     * @return string
     */
    public static function source( array $settings, $key = 'query_type' ) {
        $source = Settings::choice( $settings, $key, self::SOURCES, 'recent' );

        if ( Schema::is_locked( 'query', 'query_type', $source ) ) {
            return 'recent';
        }

        return $source;
    }

    /**
     * The products for a section.
     *
     * @param  array $settings
     * @param  int   $paged
     * @return \WP_Query|null  Null when the grid base could not be loaded at all.
     */
    public static function get( array $settings, $paged = 0 ) {
        if ( ! self::load_base() ) {
            return null;
        }

        $products = \WooLentor_Product_Grid_Base::instance()->get_products( self::build_args( $settings, $paged ) );

        self::prime_terms( $products );

        return $products;
    }

    /**
     * How many products a settings array matches, without fetching them.
     *
     * Used for tab counts, where the number is wanted and the posts are not.
     *
     * @param  array $settings
     * @return int|null
     */
    public static function count( array $settings ) {
        if ( ! self::load_base() ) {
            return null;
        }

        $settings['posts_per_page'] = 1;

        $query = \WooLentor_Product_Grid_Base::instance()->get_products( self::build_args( $settings ) );

        return $query ? (int) $query->found_posts : null;
    }

    /**
     * The queried term when the section is sitting on a shop, category, tag or attribute archive,
     * so it follows the archive instead of ignoring it. The grid base already reads this key.
     *
     * A section rendered through the endpoint has no archive to read — the request arrived at
     * admin-ajax.php — so one may be handed over in the settings instead. It is already verified
     * against the database by whoever put it there.
     *
     * @param  array $settings
     * @return array|null
     */
    public static function queried_object( array $settings = [] ) {
        if ( ! empty( $settings['queried_object']['term_id'] ) && ! empty( $settings['queried_object']['taxonomy'] ) ) {
            return $settings['queried_object'];
        }

        if ( is_admin() && ! wp_doing_ajax() ) {
            return null;
        }

        $queried = (array) get_queried_object();

        if ( empty( $queried['term_id'] ) || empty( $queried['taxonomy'] ) ) {
            return null;
        }

        return [
            'term_id'  => (int) $queried['term_id'],
            'taxonomy' => (string) $queried['taxonomy'],
            'name'     => (string) ( $queried['name'] ?? '' ),
        ];
    }

    /**
     * Prime every product taxonomy for the whole result set in one go.
     *
     * WP_Query's lazy term loading already covers get_the_terms(). wc_get_product_terms() does not
     * go through it, so a swatch reader or an attribute line spends one query per card — measured
     * at nine extra queries for an eight-card section. One call ahead of the loop removes them all.
     *
     * @param  \WP_Query|null $products
     * @return void
     */
    public static function prime_terms( $products ) {
        if ( ! $products || empty( $products->posts ) ) {
            return;
        }

        $ids = array_filter( array_map( 'absint', (array) wp_list_pluck( $products->posts, 'ID' ) ) );

        if ( $ids ) {
            update_object_term_cache( $ids, 'product' );
        }
    }

    /**
     * The grid base is not loaded globally — every consumer requires it, the way
     * class.ajax_actions.php and the product grid blocks do. Without this a section would silently
     * render nothing on a page where no product grid happens to be present.
     *
     * @return bool
     */
    private static function load_base() {
        if ( class_exists( '\WooLentor_Product_Grid_Base' ) ) {
            return true;
        }

        $base = WOOLENTOR_ADDONS_PL_PATH . 'includes/addons/product-grid/base/class.product-grid-base.php';

        if ( file_exists( $base ) ) {
            require_once $base;
        }

        return class_exists( '\WooLentor_Product_Grid_Base' );
    }
}
