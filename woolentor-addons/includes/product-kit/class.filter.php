<?php
namespace WooLentor\Product_Kit;

/**
 * The kit's bridge to the Product Filter module.
 *
 * A page load already works without this file: `enable_filters` reaches the grid base, which runs
 * `woolentor_filterable_shortcode_products_query`, and the module reads the selection out of the
 * URL. What did not work is the module's **AJAX** mode, where nothing reloads and the selection
 * never reaches the section at all.
 *
 * The older widgets solve that by printing their whole settings object into the page as
 * `data-wl-widget-settings` and posting it back to the module's own endpoint, which then has to
 * carry a `switch` naming every widget that wants to be filterable. The kit does not do either:
 *
 *   - the settings would travel through the browser, which is exactly the trust the kit's
 *     addressing model exists to avoid — and a Product Showcase settings object is far larger than
 *     a product grid's;
 *   - every new widget or block would need a new `case` inside the **Pro** plugin, so a free-side
 *     widget could not be made filterable at all without shipping a Pro release with it.
 *
 * Instead the kit listens for the module's own `wlpf_process_ajax_filter` event, and renders the
 * result through the endpoint it already has. The browser still sends only an address, plus the
 * filter selection the visitor just made — never a query and never a settings object. Nothing in
 * the Pro module changes, and every consumer of the kit becomes filterable by declaring one flag.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Filter {

    /**
     * The key the grid base reads a filter selection from.
     */
    const ARG_KEY = 'filter_arg';

    /**
     * @return void
     */
    public static function init() {
        add_filter( 'woolentor_wc_query_manager_args', [ __CLASS__, 'restore_query_args' ], 10, 2 );
    }

    /**
     * Carry across the three keys the module and the query manager name differently.
     *
     * The module's query builder writes **WP_Query's** own names — `author__in`, and `orderby` /
     * `order` / `meta_key`. The query manager reads its own — `author`, `query_orderby`,
     * `query_order` — and rebuilds `orderby` from scratch in `apply_sorting()`. So a selection the
     * module resolved perfectly well was being dropped on the floor between the two.
     *
     * Two of the six element types were affected, and only partly:
     *
     *   - **Author (vendor)** — never applied at all.
     *   - **Sorting** — price and price-desc worked, because WooCommerce implements those with a
     *     `posts_clauses` filter that survives the trip. Popularity, rating, title and date are
     *     `meta_key` / `orderby` based, and did not.
     *
     * This renames rather than reimplements: the module has already worked out the correct WP_Query
     * arguments through `WC()->query->get_catalog_ordering_args()`, and they are used as they are.
     * Writing a second sorting or author implementation in the kit would be the duplication worth
     * avoiding.
     *
     * The keys are WP_Query's, which nothing on the plugin's own settings path writes — the query
     * manager's own vocabulary is `query_orderby` / `query_order` / `author`. Their presence means
     * the filter hook put them there, so an unfiltered query passes through untouched.
     *
     * This is the same gap in the shipped `Product Grid - Modern / Luxury / Editorial / Magazine`
     * widgets, which run through this query manager too, so they are repaired by the same hook.
     *
     * @param  array $args      Query manager's finished WP_Query arguments.
     * @param  array $settings  Query manager's settings, after the module's hook has run.
     * @return array
     */
    public static function restore_query_args( $args, $settings ) {
        if ( ! is_array( $args ) || ! is_array( $settings ) ) {
            return $args;
        }

        if ( ! empty( $settings['orderby'] ) ) {
            $args['orderby'] = $settings['orderby'];

            if ( ! empty( $settings['order'] ) ) {
                $args['order'] = $settings['order'];
            }

            if ( ! empty( $settings['meta_key'] ) ) {
                $args['meta_key'] = $settings['meta_key'];
            }
        }

        if ( ! empty( $settings['author__in'] ) ) {
            $authors = array_values( array_filter( array_map( 'absint', (array) $settings['author__in'] ) ) );

            if ( $authors ) {
                $args['author__in'] = $authors;
            }
        }

        return $args;
    }

    /**
     * Does this section respond to the Product Filter module?
     *
     * Both halves matter: the user's switch, and whether there is a module to respond to. A free
     * site gets no extra attribute in its markup and no listener doing nothing.
     *
     * @param  array $settings
     * @return bool
     */
    public static function responds( array $settings ) {
        return Settings::truthy( $settings, 'enable_filters', true ) && self::module_active();
    }

    /**
     * Is the Product Filter module loaded?
     *
     * Asked of the hook rather than of a class name, so the kit stays uncoupled from where the
     * module lives — it is Pro today and the answer would not change if it moved.
     *
     * @return bool
     */
    public static function module_active() {
        return false !== has_filter( 'woolentor_filterable_shortcode_products_query' );
    }

    /**
     * Fold a filter selection into a settings array.
     *
     * `page` is dropped on purpose. The kit owns paging — its own `paged` is already in the request
     * and is what the pager was rendered from. Leaving the module's page in would let it overwrite
     * `paged` further down and put the section on a page its own pager never offered.
     *
     * @param  array $settings
     * @param  array $filters
     * @return array
     */
    public static function apply( array $settings, array $filters ) {
        if ( empty( $filters ) || ! self::responds( $settings ) ) {
            return $settings;
        }

        unset( $filters['page'] );

        if ( empty( $filters ) ) {
            return $settings;
        }

        $settings[ self::ARG_KEY ] = $filters;

        return $settings;
    }

    /**
     * The archive term a section is sitting on, as the module reports it.
     *
     * A page load reads this from `get_queried_object()`. Inside the endpoint there is no archive —
     * the request arrived at admin-ajax.php — so a section on a category page would quietly widen
     * to the whole catalogue on the first filter. The module already carries the term in its own
     * localised data; this takes it from there, sanitised, and hands it to the query the same way a
     * page load would.
     *
     * @param  array $settings
     * @param  array $raw  term_id / taxonomy, as posted.
     * @return array
     */
    public static function apply_queried_object( array $settings, array $raw ) {
        $term_id  = absint( $raw['term_id'] ?? 0 );
        $taxonomy = sanitize_key( (string) ( $raw['taxonomy'] ?? '' ) );

        if ( ! $term_id || '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
            return $settings;
        }

        // Confirmed against the database rather than taken on the browser's word — the pair has to
        // be a term that actually exists in that taxonomy.
        $term = get_term( $term_id, $taxonomy );

        if ( ! $term || is_wp_error( $term ) ) {
            return $settings;
        }

        $settings['queried_object'] = [
            'term_id'  => $term_id,
            'taxonomy' => $taxonomy,
            'name'     => (string) $term->name,
        ];

        return $settings;
    }

    /**
     * A posted filter selection, sanitised.
     *
     * The module's own endpoint casts the same payload on arrival; this does the equivalent so the
     * kit does not depend on Pro's helpers for its own safety. Shape is preserved — the module's
     * query builder reads nested arrays — and every leaf becomes a plain string.
     *
     * @param  mixed $raw
     * @param  int   $depth
     * @return array
     */
    public static function sanitize( $raw, $depth = 0 ) {
        if ( ! is_array( $raw ) || $depth > 6 ) {
            return [];
        }

        $clean = [];

        foreach ( $raw as $key => $value ) {
            $key = sanitize_text_field( (string) $key );

            if ( '' === $key ) {
                continue;
            }

            if ( is_array( $value ) ) {
                $clean[ $key ] = self::sanitize( $value, $depth + 1 );
                continue;
            }

            // A boolean stays one. `include_children` on a taxonomy clause is a boolean, and
            // casting it to a string would hand WP_Tax_Query "1" and "" instead.
            if ( is_bool( $value ) ) {
                $clean[ $key ] = $value;
                continue;
            }

            if ( is_scalar( $value ) ) {
                $clean[ $key ] = sanitize_text_field( (string) $value );
            }
        }

        return $clean;
    }
}
