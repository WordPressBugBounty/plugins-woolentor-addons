<?php
namespace WooLentor\Product_Kit;

/**
 * Resolves a section saved as a Gutenberg block.
 *
 * The block editor stores its blocks in the post's own content, so the settings are read back with
 * `parse_blocks()` — the same principle as the Elementor provider, and the same guarantee: the
 * browser sends an address, the server looks up what was saved there, and nothing about the query
 * travels in the page.
 *
 * A block cannot be rebuilt into a live object the way an Elementor widget can, so this provider
 * returns the block name and leaves the drawing to whatever registered a renderer under it:
 *
 *     Section_Registry::register_renderer( 'woolentor/product-showcase', $callable );
 *
 * Every WordPress reference here is core, so the file is safe to load anywhere.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Block_Provider {

    const NAME = 'block';

    /**
     * The attribute a block carries its own id in. Every WooLentor block already writes one.
     */
    const ID_ATTRIBUTE = 'blockUniqId';

    /**
     * How deep a chain of synced patterns is followed before giving up. A pattern that includes
     * itself is a loop; a legitimate nesting is never anywhere near this deep.
     */
    const MAX_DEPTH = 10;

    /**
     * @return void
     */
    public static function register() {
        Section_Registry::register_provider( self::NAME, [ __CLASS__, 'resolve' ] );
    }

    /**
     * @param  int    $post_id
     * @param  string $section_id
     * @return array|null
     */
    public static function resolve( $post_id, $section_id ) {
        if ( '' === $section_id ) {
            return null;
        }

        $post = get_post( $post_id );

        if ( ! $post || ! has_blocks( $post->post_content ) ) {
            return null;
        }

        $block = self::find( parse_blocks( $post->post_content ), $section_id );

        if ( ! $block || empty( $block['blockName'] ) ) {
            return null;
        }

        return [
            'name'     => (string) $block['blockName'],
            'settings' => self::attributes( $block ),
        ];
    }

    /**
     * The block's saved attributes over its registered defaults.
     *
     * `parse_blocks()` returns only what the editor actually wrote into the content, so a block
     * left on its defaults comes back with those keys missing entirely. Merging the registered
     * defaults under them is what makes a block's settings array look like the widget's.
     *
     * @param  array $block
     * @return array
     */
    private static function attributes( array $block ) {
        $attrs    = (array) ( $block['attrs'] ?? [] );
        $defaults = [];

        if ( class_exists( '\WP_Block_Type_Registry' ) ) {
            $type = \WP_Block_Type_Registry::get_instance()->get_registered( $block['blockName'] );

            if ( $type && ! empty( $type->attributes ) ) {
                foreach ( $type->attributes as $key => $definition ) {
                    if ( array_key_exists( 'default', $definition ) ) {
                        $defaults[ $key ] = $definition['default'];
                    }
                }
            }
        }

        return array_merge( $defaults, $attrs );
    }

    /**
     * One block by its id attribute, anywhere in the tree.
     *
     * The first match wins. Duplicating a block duplicates its id too until the editor reassigns
     * one, and rendering the first of two identical sections is the only answer that is not a
     * guess.
     *
     * @param  array  $blocks
     * @param  string $section_id
     * @param  int    $depth
     * @param  array  $seen  Reusable block ids already followed, so a self-including pattern cannot
     *                       loop forever.
     * @return array|null
     */
    private static function find( $blocks, $section_id, $depth = 0, array &$seen = [] ) {
        if ( $depth > self::MAX_DEPTH ) {
            return null;
        }

        foreach ( (array) $blocks as $block ) {
            $attrs = (array) ( $block['attrs'] ?? [] );

            if ( isset( $attrs[ self::ID_ATTRIBUTE ] ) && (string) $attrs[ self::ID_ATTRIBUTE ] === $section_id ) {
                return $block;
            }

            if ( ! empty( $block['innerBlocks'] ) ) {
                $found = self::find( $block['innerBlocks'], $section_id, $depth + 1, $seen );

                if ( $found ) {
                    return $found;
                }
            }

            // A synced pattern keeps its blocks in a post of its own, so the content being walked
            // does not contain them. `core/block` is the only core block that indirects like this.
            if ( 'core/block' === ( $block['blockName'] ?? '' ) && ! empty( $attrs['ref'] ) ) {
                $ref = absint( $attrs['ref'] );

                if ( isset( $seen[ $ref ] ) ) {
                    continue;
                }

                $seen[ $ref ] = true;
                $pattern      = get_post( $ref );

                if ( ! $pattern || ! has_blocks( $pattern->post_content ) ) {
                    continue;
                }

                $found = self::find( parse_blocks( $pattern->post_content ), $section_id, $depth + 1, $seen );

                if ( $found ) {
                    return $found;
                }
            }
        }

        return null;
    }
}
