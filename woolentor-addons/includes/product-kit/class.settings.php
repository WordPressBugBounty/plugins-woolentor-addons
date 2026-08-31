<?php
namespace WooLentor\Product_Kit;

/**
 * Settings readers shared by every part of the kit.
 *
 * The whole point of the kit is that one settings array can come from either builder, and the two
 * builders do not agree on what a value looks like. Elementor's SWITCHER stores `'yes'` and `''`;
 * a block's boolean attribute stores `true` and `false`; a shortcode would send `'1'`. Every read
 * in the kit goes through here so no consumer has to know which builder it is being called from.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Settings {

    /**
     * A switch, however the builder chose to store it.
     *
     * @param  array  $settings
     * @param  string $key
     * @param  bool   $default  Used only when the key is absent — an explicit empty value is a
     *                          deliberate "off", not a missing setting.
     * @return bool
     */
    public static function truthy( array $settings, $key, $default = false ) {
        if ( ! array_key_exists( $key, $settings ) ) {
            return (bool) $default;
        }

        $value = $settings[ $key ];

        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_numeric( $value ) ) {
            return (int) $value > 0;
        }

        return in_array( strtolower( (string) $value ), [ 'yes', 'true', 'on', '1' ], true );
    }

    /**
     * A trimmed string, with a fallback for an empty one.
     *
     * @param  array  $settings
     * @param  string $key
     * @param  string $fallback
     * @return string
     */
    public static function text( array $settings, $key, $fallback = '' ) {
        $value = trim( (string) ( $settings[ $key ] ?? '' ) );

        return '' !== $value ? $value : $fallback;
    }

    /**
     * A list of post ids. Both builders can hand these over as strings.
     *
     * @param  array  $settings
     * @param  string $key
     * @return int[]
     */
    public static function ids( array $settings, $key ) {
        return array_values( array_filter( array_map( 'absint', (array) ( $settings[ $key ] ?? [] ) ) ) );
    }

    /**
     * A list of term slugs, which is how the query manager reads taxonomies.
     *
     * @param  array  $settings
     * @param  string $key
     * @return string[]
     */
    public static function slugs( array $settings, $key ) {
        $slugs = array_map( 'sanitize_title', (array) ( $settings[ $key ] ?? [] ) );

        return array_values( array_filter( $slugs, static function ( $slug ) {
            return '' !== $slug;
        } ) );
    }

    /**
     * A whole number inside a range.
     *
     * @param  array    $settings
     * @param  string   $key
     * @param  int      $default
     * @param  int      $min
     * @param  int|null $max
     * @return int
     */
    public static function number( array $settings, $key, $default, $min = 1, $max = null ) {
        $value = $settings[ $key ] ?? '';
        $value = ( '' === $value || null === $value ) ? $default : (int) $value;
        $value = max( $min, $value );

        return null === $max ? $value : min( $max, $value );
    }

    /**
     * One of a fixed set of slugs, falling back to the first when the stored value is not in it.
     *
     * @param  array  $settings
     * @param  string $key
     * @param  array  $allowed
     * @param  string $default
     * @return string
     */
    public static function choice( array $settings, $key, array $allowed, $default ) {
        $value = sanitize_key( (string) ( $settings[ $key ] ?? '' ) );

        return in_array( $value, $allowed, true ) ? $value : $default;
    }
}
