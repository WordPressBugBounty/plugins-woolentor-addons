<?php
namespace WooLentor\Product_Kit;

/**
 * Who owns a product section, and who can draw it.
 *
 * This is the only part of the kit that knows builders exist, and it knows them only as names. A
 * **provider** turns an address — post id plus section id — into the settings that were saved
 * there. A **renderer** turns those settings into cards.
 *
 * Adding Gutenberg support is one provider and no changes anywhere else: not to the endpoint, not
 * to the JavaScript, not to the query, pagination or tab layers.
 *
 * The browser never sends a query, only an address. That is what stops a visitor widening a
 * section's query by editing what the page posted back — the failure mode the plugin's older
 * load-more endpoint has, where the whole settings object travels in the page and comes back
 * trusted.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Section_Registry {

    /**
     * @var callable[]  provider name => callback( int $post_id, string $section_id )
     */
    private static $providers = [];

    /**
     * @var callable[]  widget or block name => callback( array $settings, array $context )
     */
    private static $renderers = [];

    /**
     * Teach the kit a builder.
     *
     * The callback returns null when it cannot find the section, or:
     *
     *     [
     *         'settings' => array,             // what was saved there
     *         'name'     => string,            // widget or block name, for renderer lookup
     *         'object'   => Renders_Section,   // optional, when the builder hands back a live one
     *     ]
     *
     * @param  string   $name
     * @param  callable $callback
     * @return void
     */
    public static function register_provider( $name, callable $callback ) {
        self::$providers[ sanitize_key( $name ) ] = $callback;
    }

    /**
     * Teach the kit how one widget or block draws its cards.
     *
     * Not needed when the provider hands back an object that implements Renders_Section — an
     * Elementor widget does, because Elementor can rebuild the instance. A block cannot, so its
     * PHP registers a callable here instead.
     *
     * @param  string   $name
     * @param  callable $callback
     * @return void
     */
    public static function register_renderer( $name, callable $callback ) {
        self::$renderers[ $name ] = $callback;
    }

    /**
     * Is this a builder we know?
     *
     * @param  string $provider
     * @return bool
     */
    public static function has_provider( $provider ) {
        return isset( self::$providers[ sanitize_key( (string) $provider ) ] );
    }

    /**
     * Address in, settings and a way to draw them out.
     *
     * @param  string $provider
     * @param  int    $post_id
     * @param  string $section_id
     * @return array|null  [ 'settings' => array, 'render' => callable ]
     */
    public static function resolve( $provider, $post_id, $section_id ) {
        $provider = sanitize_key( (string) $provider );

        if ( ! isset( self::$providers[ $provider ] ) ) {
            return null;
        }

        $found = call_user_func( self::$providers[ $provider ], (int) $post_id, (string) $section_id );

        if ( ! is_array( $found ) || ! isset( $found['settings'] ) || ! is_array( $found['settings'] ) ) {
            return null;
        }

        $render = self::renderer_for( $found );

        if ( ! $render ) {
            return null;
        }

        return [
            'settings' => $found['settings'],
            'render'   => $render,
            'name'     => (string) ( $found['name'] ?? '' ),
        ];
    }

    /**
     * The live object first, a registered callable second. Nothing else — a section whose renderer
     * cannot be found renders nothing rather than falling back to some other widget's card, which
     * would hand the visitor markup the page never had.
     *
     * @param  array $found
     * @return callable|null
     */
    private static function renderer_for( array $found ) {
        $object = $found['object'] ?? null;

        if ( $object instanceof Renders_Section ) {
            return [ $object, 'render_section' ];
        }

        $name = (string) ( $found['name'] ?? '' );

        return isset( self::$renderers[ $name ] ) ? self::$renderers[ $name ] : null;
    }
}
