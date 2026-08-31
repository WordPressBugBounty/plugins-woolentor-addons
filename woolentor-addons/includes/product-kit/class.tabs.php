<?php
namespace WooLentor\Product_Kit;

/**
 * The kit's tab layer.
 *
 * A tab row is a list of rows, each saying where its products come from. Anything a row leaves
 * alone falls through to the section's own query settings, so switching tabs cannot change the
 * shape of the grid — only what is in it.
 *
 * The rows come from an Elementor repeater or a block's array attribute; the keys are the same
 * either way, which is what the schema is for.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Tabs {

    const SOURCES = [ 'inherit', 'query_type', 'category', 'tag' ];

    /**
     * The tab row for a section, or an empty array when it has none.
     *
     * @param  array $settings
     * @param  int   $active     Which tab is showing.
     * @param  bool  $available  The consumer's veto — a layout with no tab row passes false and
     *                           pays nothing for the feature, whatever is stored.
     * @return array  [ [ label, index, active, count ], … ]
     */
    public static function build( array $settings, $active = 0, $available = true ) {
        if ( ! $available ) {
            return [];
        }

        $rows       = (array) ( $settings['tabs'] ?? [] );
        $show_count = Settings::truthy( $settings, 'tab_show_count' );
        $tabs       = [];

        foreach ( $rows as $index => $row ) {
            $label = trim( (string) ( $row['tab_label'] ?? '' ) );

            if ( '' === $label ) {
                continue;
            }

            $tabs[] = [
                'label'  => $label,
                'index'  => (int) $index,
                'active' => (int) $index === (int) $active,
                'count'  => $show_count ? self::count( $settings, $row ) : null,
            ];
        }

        return $tabs;
    }

    /**
     * Fold one tab's own source into the section's settings.
     *
     * @param  array $settings
     * @param  array $row
     * @return array
     */
    public static function apply( array $settings, array $row ) {
        $source = Settings::choice( $row, 'tab_source', self::SOURCES, 'inherit' );

        if ( 'query_type' === $source ) {
            // Through Product_Query::source(), so a tab cannot reach a source the section's own
            // control would have refused.
            $settings['query_type'] = Product_Query::source( $row, 'tab_query_type' );

            return $settings;
        }

        if ( 'category' === $source || 'tag' === $source ) {
            $key   = 'category' === $source ? 'categories' : 'tags';
            $field = 'category' === $source ? 'tab_categories' : 'tab_tags';

            $settings[ $key ] = Settings::slugs( $row, $field );

            // A taxonomy tab has to be able to reach the whole catalogue. "Hand-picked" and
            // "current page query" both ignore a taxonomy filter, so a tab under either of them
            // would silently show the same products on every tab.
            if ( in_array( $settings['query_type'] ?? '', [ 'manual', 'current_query' ], true ) ) {
                $settings['query_type'] = 'products';
            }
        }

        return $settings;
    }

    /**
     * Fold the tab that is showing into the settings. With no tab row this is a no-op.
     *
     * @param  array $settings
     * @param  array $tabs
     * @return array
     */
    public static function apply_active( array $settings, array $tabs ) {
        $index = self::active_index( $tabs );

        if ( $index < 0 ) {
            return $settings;
        }

        $rows = (array) ( $settings['tabs'] ?? [] );

        return isset( $rows[ $index ] ) ? self::apply( $settings, $rows[ $index ] ) : $settings;
    }

    /**
     * The settings for one tab by position, or null when that tab does not exist.
     *
     * This is what the AJAX endpoint uses: a request naming a tab a section does not have must not
     * fall back to the section's own query, or a tampered index would render something the page
     * never offered.
     *
     * @param  array $settings
     * @param  int   $index
     * @param  bool  $available
     * @return array|null
     */
    public static function settings_for( array $settings, $index, $available = true ) {
        if ( ! $available || $index < 0 ) {
            return null;
        }

        $rows = (array) ( $settings['tabs'] ?? [] );

        return isset( $rows[ $index ] ) ? self::apply( $settings, $rows[ $index ] ) : null;
    }

    /**
     * Index of the open tab, or -1 when the section has no tab row.
     *
     * @param  array $tabs
     * @return int
     */
    public static function active_index( array $tabs ) {
        foreach ( $tabs as $tab ) {
            if ( ! empty( $tab['active'] ) ) {
                return (int) $tab['index'];
            }
        }

        return -1;
    }

    /**
     * How many products a tab would show. One extra query per tab, which is why the control that
     * turns it on says so.
     *
     * @param  array $settings
     * @param  array $row
     * @return int|null
     */
    public static function count( array $settings, array $row ) {
        return Product_Query::count( self::apply( $settings, $row ) );
    }

    /**
     * The tab row's markup.
     *
     * Each button carries its own index and nothing else — the query behind a tab never reaches the
     * browser, so it cannot be edited there. The endpoint reads the saved settings back from the
     * post the section belongs to.
     *
     * @param  array $tabs
     * @param  array $args {
     *     @type string $prefix  CSS prefix for this consumer, e.g. 'wl-ps'.
     * }
     * @return string
     */
    public static function row_html( array $tabs, array $args = [] ) {
        if ( empty( $tabs ) ) {
            return '';
        }

        $args   = wp_parse_args( $args, [ 'prefix' => 'wl-section' ] );
        $prefix = sanitize_html_class( $args['prefix'] );

        $out = '<div class="' . esc_attr( $prefix . '-tabs' ) . '" role="tablist">';

        foreach ( $tabs as $tab ) {
            $out .= '<button type="button" class="' . esc_attr( $prefix . '-tab' ) . ( $tab['active'] ? ' is-active' : '' ) . '"'
                . ' role="tab" aria-selected="' . ( $tab['active'] ? 'true' : 'false' ) . '"'
                . ' data-wl-section-tab="' . absint( $tab['index'] ) . '">'
                . '<span class="' . esc_attr( $prefix . '-tab-label' ) . '">' . esc_html( $tab['label'] ) . '</span>';

            if ( null !== $tab['count'] ) {
                $out .= '<span class="' . esc_attr( $prefix . '-tab-count' ) . '">'
                    . esc_html( number_format_i18n( $tab['count'] ) )
                    . '</span>';
            }

            $out .= '</button>';
        }

        return $out . '</div>';
    }
}
