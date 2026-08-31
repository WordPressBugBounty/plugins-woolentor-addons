<?php
namespace WooLentor\Product_Kit;

/**
 * Reader for schema/product-section.json.
 *
 * The option list is written once, as data, because writing it twice — an Elementor control block
 * and a block.json attribute list — is how the two builders drift apart. PHP reads it here to
 * register Elementor controls and to know the defaults; the block build reads the same file to
 * generate attributes and drive the shared inspector.
 *
 * Labels in the file are English source strings. They are run through the translation functions on
 * the way out, so the schema stays a plain data file and the strings still land in the POT.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Schema {

    /**
     * @var array|null  Parsed file, read once per request.
     */
    private static $data = null;

    /**
     * The whole schema.
     *
     * @return array
     */
    public static function all() {
        if ( null !== self::$data ) {
            return self::$data;
        }

        self::$data = [];

        $file = __DIR__ . '/schema/product-section.json';

        if ( file_exists( $file ) ) {
            $decoded = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a bundled file, not a remote request.

            if ( is_array( $decoded ) ) {
                self::$data = $decoded;
            }
        }

        return self::$data;
    }

    /**
     * One group's field list — `query`, `pagination`, `tabs`, `slider`.
     *
     * @param  string $group
     * @return array
     */
    public static function fields( $group ) {
        $groups = self::all()['groups'] ?? [];

        return $groups[ $group ]['fields'] ?? [];
    }

    /**
     * One field's definition, by group and key.
     *
     * @param  string $group
     * @param  string $key
     * @return array
     */
    public static function field( $group, $key ) {
        return self::fields( $group )[ $key ] ?? [];
    }

    /**
     * One field inside a repeater, by group, repeater key and row key.
     *
     * @param  string $group
     * @param  string $key
     * @param  string $row_key
     * @return array
     */
    public static function row_field( $group, $key, $row_key ) {
        return self::field( $group, $key )['fields'][ $row_key ] ?? [];
    }

    /**
     * A field's option list, translated, ready for a select control.
     *
     * @param  string $group
     * @param  string $key
     * @return array
     */
    public static function options( $group, $key ) {
        return self::options_for( self::field( $group, $key ) );
    }

    /**
     * The same, for a field inside a repeater.
     *
     * @param  string $group
     * @param  string $key
     * @param  string $row_key
     * @return array
     */
    public static function row_options( $group, $key, $row_key ) {
        return self::options_for( self::row_field( $group, $key, $row_key ) );
    }

    /**
     * A field definition's option list, wherever it came from, with Pro-only options marked when
     * the site is on the free build.
     *
     * Marked here rather than in the Elementor generator so every consumer says the same thing — a
     * block inspector reading this schema labels its options exactly as the widget panel does.
     *
     * @param  array $field
     * @return array
     */
    private static function options_for( array $field ) {
        $options = $field['options'] ?? [];
        $pro     = (array) ( $field['pro'] ?? [] );

        // `optionsFrom` lets a field borrow another's list — the tab row's source is the section's
        // source, and one of them having an option the other lacks would be a bug, not a feature.
        if ( empty( $options ) && ! empty( $field['optionsFrom'] ) ) {
            $parts = explode( '.', (string) $field['optionsFrom'] );

            if ( 2 === count( $parts ) ) {
                $borrowed = self::field( $parts[0], $parts[1] );
                $options  = $borrowed['options'] ?? [];

                if ( empty( $pro ) ) {
                    $pro = (array) ( $borrowed['pro'] ?? [] );
                }
            }
        }

        $mark       = $pro && ! self::is_pro();
        $translated = [];

        foreach ( $options as $value => $label ) {
            $text = self::translate( $label );

            if ( $mark && in_array( $value, $pro, true ) ) {
                /* translators: %s: the option's own label, e.g. "Best Selling". */
                $text = sprintf( __( '%s (Pro)', 'woolentor' ), $text );
            }

            $translated[ $value ] = $text;
        }

        return $translated;
    }

    /**
     * Which of a field's options require Pro, on a site that does not have it. Empty on Pro, and
     * empty for a field that gates nothing — so a caller can use it as "is anything locked here".
     *
     * @param  string $group
     * @param  string $key
     * @return array
     */
    public static function locked_options( $group, $key ) {
        if ( self::is_pro() ) {
            return [];
        }

        $pro = self::field( $group, $key )['pro'] ?? [];

        // `"pro": true` locks the whole control rather than a list of its values — see
        // is_pro_field(). There are no individual options to name in that case.
        return is_array( $pro ) ? array_values( $pro ) : [];
    }

    /**
     * Is this whole control a Pro feature?
     *
     * Two shapes of gate live under one key. `"pro": [ … ]` names the option *values* a free site
     * may not use — the control is still offered, the listed options carry "(Pro)". `"pro": true`
     * gates the control itself, which is what a switch needs: there is no value to label, only the
     * feature it turns on.
     *
     * @param  string $group
     * @param  string $key
     * @return bool
     */
    public static function is_pro_field( $group, $key ) {
        if ( self::is_pro() ) {
            return false;
        }

        return true === ( self::field( $group, $key )['pro'] ?? null );
    }

    /**
     * Is this value one the site's licence does not cover?
     *
     * The gate is applied where the value is *used*, not where it is stored, so a page built on the
     * free build keeps its real setting and starts working the moment Pro is installed. Storing a
     * placeholder instead would lose the choice.
     *
     * @param  string $group
     * @param  string $key
     * @param  string $value
     * @return bool
     */
    public static function is_locked( $group, $key, $value ) {
        return in_array( $value, self::locked_options( $group, $key ), true );
    }

    /**
     * Whether ShopLentor Pro is active. Wrapped so the kit has one answer and works if the helper
     * has not loaded yet.
     *
     * @return bool
     */
    public static function is_pro() {
        return function_exists( 'woolentor_is_pro' ) ? (bool) woolentor_is_pro() : false;
    }

    /**
     * A field's label, translated.
     *
     * @param  string $group
     * @param  string $key
     * @return string
     */
    public static function label( $group, $key ) {
        $label = self::translate( self::field( $group, $key )['label'] ?? '' );

        if ( '' !== $label && self::is_pro_field( $group, $key ) ) {
            /* translators: %s: the control's own label, e.g. "Enable Slider". */
            $label = sprintf( __( '%s (Pro)', 'woolentor' ), $label );
        }

        return $label;
    }

    /**
     * A repeater row field's label, translated.
     *
     * @param  string $group
     * @param  string $key
     * @param  string $row_key
     * @return string
     */
    public static function row_label( $group, $key, $row_key ) {
        return self::translate( self::row_field( $group, $key, $row_key )['label'] ?? '' );
    }

    /**
     * A field's description, translated. Empty when it has none.
     *
     * @param  string $group
     * @param  string $key
     * @return string
     */
    public static function description( $group, $key ) {
        return self::translate( self::field( $group, $key )['description'] ?? '' );
    }

    /**
     * Any other translatable string on a field — a text default, a placeholder. Returns an empty
     * string when the property is absent or is not a string, so a caller may ask for it without
     * first knowing the field's type.
     *
     * @param  string $group
     * @param  string $key
     * @param  string $prop
     * @return string
     */
    public static function field_text( $group, $key, $prop ) {
        return self::translate( self::field( $group, $key )[ $prop ] ?? '' );
    }

    /**
     * Translate a schema string from outside this class — a group label, say.
     *
     * @param  string $text
     * @return string
     */
    public static function translate_public( $text ) {
        return self::translate( $text );
    }

    /**
     * Source strings live in the JSON, so they are translated on the way out rather than at the
     * point they are written. `translate()` is used deliberately: the strings are extracted from
     * this file's own `__()` calls below, which is what keeps them in the POT.
     *
     * Anything that is not a string is not a translatable string. That matters because callers ask
     * for a field's `default` without knowing its type, and several fields default to an array —
     * `categories`, `tabs`, the two product pickers. Casting those would raise "Array to string
     * conversion" on every control the generator builds.
     *
     * @param  mixed $text
     * @return string
     */
    private static function translate( $text ) {
        if ( ! is_string( $text ) || '' === $text ) {
            return '';
        }

        // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction, WordPress.WP.I18n.NonSingularStringLiteralText -- the source strings are in schema/product-section.json and are listed in pot_strings() so they still reach the POT file.
        return translate( $text, 'woolentor' );
    }

    /**
     * Never called. It exists so every source string in the schema is visible to the POT scanner,
     * which reads PHP and cannot read a JSON file.
     *
     * Keep it in step with schema/product-section.json — a string added there and not here is a
     * string that ships untranslatable.
     *
     * @return void
     */
    public static function pot_strings() {
        __( 'Products', 'woolentor' );
        __( 'Source', 'woolentor' );
        __( 'Latest Products', 'woolentor' );
        __( 'Featured', 'woolentor' );
        __( 'Best Selling', 'woolentor' );
        __( 'Top Rated', 'woolentor' );
        __( 'On Sale', 'woolentor' );
        __( 'Recently Viewed', 'woolentor' );
        __( 'All Products', 'woolentor' );
        __( 'Hand-picked', 'woolentor' );
        __( 'Current Page Query', 'woolentor' );
        /* translators: %s: the option's own label, e.g. "Best Selling". */
        __( '%s (Pro)', 'woolentor' );
        __( 'Categories', 'woolentor' );
        __( 'Leave empty for every category.', 'woolentor' );
        __( 'Tags', 'woolentor' );
        __( 'Products To Show', 'woolentor' );
        __( 'Order By', 'woolentor' );
        __( 'Date', 'woolentor' );
        __( 'Title', 'woolentor' );
        __( 'Price', 'woolentor' );
        __( 'Popularity', 'woolentor' );
        __( 'Rating', 'woolentor' );
        __( 'Menu Order', 'woolentor' );
        __( 'Random', 'woolentor' );
        __( 'Order', 'woolentor' );
        __( 'Ascending', 'woolentor' );
        __( 'Descending', 'woolentor' );
        __( 'Exclude Products', 'woolentor' );
        __( 'Search products...', 'woolentor' );
        __( 'Hide Out Of Stock', 'woolentor' );
        __( 'Hide Products Without An Image', 'woolentor' );
        __( 'Respond To Product Filters', 'woolentor' );
        __( 'Let the Product Filter module narrow this section, the same way it narrows the product grid.', 'woolentor' );

        __( 'Pagination', 'woolentor' );
        __( 'Enable Pagination', 'woolentor' );
        __( 'With this on, "Products To Show" becomes the number of products per page.', 'woolentor' );
        __( 'Pagination Type', 'woolentor' );
        __( 'Numbers', 'woolentor' );
        __( 'Load More', 'woolentor' );
        __( 'Infinite Scroll', 'woolentor' );
        __( 'Button Text', 'woolentor' );
        __( 'Finished Text', 'woolentor' );
        __( 'No more products', 'woolentor' );
        __( 'Shown on the button once every product has been loaded.', 'woolentor' );

        __( 'Tab Row', 'woolentor' );
        __( 'Tabs', 'woolentor' );
        __( 'Leave empty for a section with no tab row. The first tab is the one shown on load.', 'woolentor' );
        __( 'Label', 'woolentor' );
        __( 'New Arrivals', 'woolentor' );
        __( 'Shows', 'woolentor' );
        __( 'Everything (the section\'s own settings)', 'woolentor' );
        __( 'A product source', 'woolentor' );
        __( 'Show Product Count', 'woolentor' );
        __( 'Prints the number of matching products beside each label. Each tab costs one extra count query.', 'woolentor' );

        __( 'Slider Options', 'woolentor' );
        __( 'Enable Slider', 'woolentor' );
        __( 'Turns this section into a carousel. A carousel has no tab row and no pager — it scrolls through its own products.', 'woolentor' );
        __( 'Show Arrows', 'woolentor' );
        __( 'Show Dots', 'woolentor' );
        __( 'Infinite Loop', 'woolentor' );
        __( 'Autoplay', 'woolentor' );
        __( 'Autoplay Speed (ms)', 'woolentor' );
        __( 'Transition Speed (ms)', 'woolentor' );
        __( 'Pause on Hover', 'woolentor' );
        __( 'Fade Transition', 'woolentor' );
        __( 'Only for a one-card rail — Slick cross-fades a single slide.', 'woolentor' );
        __( 'Desktop', 'woolentor' );
        __( 'Tablet', 'woolentor' );
        __( 'Mobile', 'woolentor' );
        __( 'Cards Per View', 'woolentor' );
        __( 'Cards To Scroll', 'woolentor' );
        __( 'Tablet Breakpoint (px)', 'woolentor' );
        __( 'Mobile Breakpoint (px)', 'woolentor' );
    }
}
