<?php
namespace WooLentor\Product_Kit;

/**
 * The kit's pagination layer.
 *
 * Owns three things a product section needs and no two consumers should implement twice: which
 * page the section is on, where the number links point, and the pager's markup.
 *
 * Nothing here is Elementor- or block-specific. The only thing a consumer supplies is its own CSS
 * prefix, so the markup carries that widget's or block's class names while the behaviour — the
 * `data-wl-section-*` contract that product-section.js binds to — stays identical everywhere.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Pagination {

    const TYPES = [ 'numbers', 'load_more', 'infinite' ];

    /**
     * Pagination as a section actually resolves it, rather than as it is stored.
     *
     * `$available` is the consumer's veto: a carousel layout has no room for a pager, and hiding
     * the control that turns one on does not clear what is stored under it. A section paged on a
     * grid layout and then switched to a carousel would otherwise still draw one.
     *
     * @param  array $settings
     * @param  bool  $available
     * @return array  [ enabled, type, more_text, done_text ]
     */
    public static function settings( array $settings, $available = true ) {
        $off = [ 'enabled' => false, 'type' => 'numbers', 'more_text' => '', 'done_text' => '' ];

        if ( ! $available || ! Settings::truthy( $settings, 'enable_pagination' ) ) {
            return $off;
        }

        $type = Settings::choice( $settings, 'pagination_type', self::TYPES, 'numbers' );

        // Load More and infinite scroll are Pro. Numbers is what a free site gets, and it is the
        // one that needs no JavaScript — so the fallback is also the most robust of the three.
        if ( Schema::is_locked( 'pagination', 'pagination_type', $type ) ) {
            $type = 'numbers';
        }

        return [
            'enabled'   => true,
            'type'      => $type,
            'more_text' => Settings::text( $settings, 'load_more_text', esc_html__( 'Load More', 'woolentor' ) ),
            'done_text' => Settings::text( $settings, 'load_more_complete_text', esc_html__( 'No more products', 'woolentor' ) ),
        ];
    }

    /**
     * Which page the section is on. Only the numbers pager can arrive by URL — Load More and
     * infinite scroll always start at one and ask for the rest over AJAX.
     *
     * WordPress's own page number, so the URL stays `/some-page/page/2/` rather than a query string
     * of the kit's own invention. It is what the product grid widgets already produce, and a
     * visitor should not be able to tell which ShopLentor section drew the pager.
     *
     * Both vars are read: an archive numbers itself with `paged`, a static page with `page`, and a
     * product section can sit on either.
     *
     * @param  array $settings
     * @param  bool  $available
     * @return int
     */
    public static function current_page( array $settings, $available = true ) {
        $pagination = self::settings( $settings, $available );

        if ( ! $pagination['enabled'] || 'numbers' !== $pagination['type'] ) {
            return 1;
        }

        return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
    }

    /**
     * The query argument that remembers which tab a number link was clicked from, so page two of
     * "Best Sellers" comes back as page two of "Best Sellers" rather than page two of tab one.
     *
     * Keyed on the section id, because a tab index is per section — unlike the page number, which
     * is the page's.
     *
     * @param  string $section_id
     * @return string
     */
    public static function tab_arg( $section_id ) {
        return 'wl-ps-tab-' . sanitize_key( (string) $section_id );
    }

    /**
     * Which tab the section opens on. Zero unless a number link carried one.
     *
     * @param  array  $settings
     * @param  string $section_id
     * @param  bool   $available
     * @return int
     */
    public static function current_tab( array $settings, $section_id, $available = true ) {
        $pagination = self::settings( $settings, $available );

        if ( ! $pagination['enabled'] || 'numbers' !== $pagination['type'] ) {
            return 0;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a tab index in a link, not a state change.
        return max( 0, absint( $_GET[ self::tab_arg( $section_id ) ] ?? 0 ) );
    }

    /**
     * The pager under the cards, or an empty string when there is nothing to page through.
     *
     * Rendered here rather than by a consumer's templates: it sits below the whole section, it is
     * identical in every layout, and a layout added later gets it without anyone remembering to.
     *
     * @param  \WP_Query|null $products
     * @param  array          $settings
     * @param  array          $args {
     *     @type string $prefix      CSS prefix for this consumer, e.g. 'wl-ps'.
     *     @type string $section_id  Element or block id, for the tab argument.
     *     @type int    $active_tab  Index of the open tab, or -1 when the section has no tab row.
     *     @type int    $base_post   Post the section lives on, when the current request is not that
     *                               page. The AJAX endpoint needs it; a page load does not.
     *     @type bool   $available   See settings().
     * }
     * @return string
     */
    public static function html( $products, array $settings, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'prefix'     => 'wl-section',
            'section_id' => '',
            'active_tab' => -1,
            'base_post'  => 0,
            'available'  => true,
        ] );

        $pagination = self::settings( $settings, $args['available'] );

        if ( ! $pagination['enabled'] || ! $products ) {
            return '';
        }

        $total = (int) $products->max_num_pages;
        $paged = max( 1, absint( $products->get( 'paged' ) ) );

        if ( $total <= 1 ) {
            return '';
        }

        $prefix = sanitize_html_class( $args['prefix'] );
        $open   = '<div class="' . esc_attr( $prefix . '-pagination ' . $prefix . '-pagination--' . $pagination['type'] ) . '"'
            . ' data-wl-section-pager>';

        if ( 'numbers' === $pagination['type'] ) {
            return $open . self::number_links( $paged, $total, $prefix, $args ) . '</div>';
        }

        // Load More and infinite scroll differ only in what triggers the next request, so they
        // carry the same next-page bookkeeping.
        $done = $paged >= $total;
        $data = ' data-wl-section-next="' . absint( $paged + 1 ) . '" data-wl-section-max="' . absint( $total ) . '"';

        if ( 'load_more' === $pagination['type'] ) {
            // The button stays once the last page is in, saying so, rather than vanishing — a
            // control that disappears reads as a page that broke.
            return $open
                . '<button type="button" class="' . esc_attr( $prefix . '-page ' . $prefix . '-loadmore' ) . ( $done ? ' is-done' : '' ) . '"'
                . $data . ' data-wl-section-more' . ( $done ? ' disabled' : '' ) . '>'
                . esc_html( $done ? $pagination['done_text'] : $pagination['more_text'] )
                . '</button>'
                . '</div>';
        }

        // Nothing left to scroll into means no sentinel, so the observer has nothing to watch.
        if ( $done ) {
            return '';
        }

        return $open
            . '<div class="' . esc_attr( $prefix . '-infinite' ) . '"' . $data . ' data-wl-section-observe aria-hidden="true">'
            . '<span class="' . esc_attr( $prefix . '-spinner' ) . '"></span>'
            . '</div>'
            . '</div>';
    }

    /**
     * Numbered links, in WordPress's own page format — `/some-page/page/2/`.
     *
     * A tabbed section adds its tab index as a query argument so a number link returns to the tab
     * it left. A section with no tabs adds nothing at all and its links stay clean.
     *
     * @param  int    $paged
     * @param  int    $total
     * @param  string $prefix
     * @param  array  $args
     * @return string
     */
    private static function number_links( $paged, $total, $prefix, array $args ) {
        $links_args = [
            'current'   => $paged,
            'total'     => $total,
            'type'      => 'array',
            'prev_text' => self::arrow(),
            'next_text' => self::arrow(),
        ];

        if ( $args['active_tab'] >= 0 && '' !== $args['section_id'] ) {
            $links_args['add_args'] = [ self::tab_arg( $args['section_id'] ) => (int) $args['active_tab'] ];
        }

        $links_args = array_merge( $links_args, self::link_parts( (int) $args['base_post'] ) );

        $links = paginate_links( $links_args );

        if ( empty( $links ) ) {
            return '';
        }

        $out = '';

        foreach ( $links as $link ) {
            // paginate_links() writes its own classes; the consumer's page class is added alongside
            // so one set of style controls reaches the numbers, the button and the arrows alike.
            $out .= str_replace( 'page-numbers', 'page-numbers ' . $prefix . '-page', $link );
        }

        return $out;
    }

    /**
     * Where the number links point.
     *
     * Left to paginate_links() on a normal page load, which is what gives `/some-page/page/2/`.
     * Inside the AJAX endpoint there is no such page — the request arrived at admin-ajax.php — so
     * the link is rebuilt from the section's own post, the way get_pagenum_link() would have.
     *
     * `base` and `format` are kept as two parts rather than one finished string, because that is
     * how paginate_links() knows page one has no page segment: it substitutes an empty `%_%` for
     * the first page and the format for every other. One finished string would link page one to
     * `/page/1/`, which is a canonical redirect on every visit.
     *
     * @param  int $base_post
     * @return array  Empty to let paginate_links() work it out.
     */
    private static function link_parts( $base_post ) {
        if ( ! $base_post ) {
            return [];
        }

        $link = get_permalink( $base_post );

        if ( ! $link ) {
            return [];
        }

        global $wp_rewrite;

        if ( $wp_rewrite && $wp_rewrite->using_permalinks() ) {
            return [
                'base'   => trailingslashit( $link ) . '%_%',
                'format' => user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'single_paged' ),
            ];
        }

        return [
            'base'   => $link . '%_%',
            'format' => ( false === strpos( $link, '?' ) ? '?' : '&' ) . 'page=%#%',
        ];
    }

    /**
     * The arrow both ends of the pager share. CSS turns the previous one around.
     *
     * @param  int $size
     * @return string
     */
    public static function arrow( $size = 15 ) {
        return '<svg width="' . absint( $size ) . '" height="' . absint( $size ) . '" viewBox="0 0 24 24" fill="none"'
            . ' stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"'
            . ' aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
    }
}
