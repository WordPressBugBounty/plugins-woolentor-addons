<?php
namespace WooLentor\Product_Kit;

/**
 * Resolves a section saved by Elementor.
 *
 * Reads the settings back out of the saved document rather than trusting anything the browser sent,
 * and hands back the rebuilt widget instance — which, when it implements Renders_Section, is also
 * what draws the cards. So an AJAX render goes through exactly the same object and the same
 * template the first page load used.
 *
 * Every Elementor reference is inside the callback. The file is safe to load on a site with no
 * Elementor at all; the provider simply never resolves.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor_Provider {

    const NAME = 'elementor';

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
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
            return null;
        }

        $document = \Elementor\Plugin::$instance->documents->get( $post_id );

        if ( ! $document ) {
            return null;
        }

        $element = self::find( $document->get_elements_data(), $section_id );

        if ( ! $element || empty( $element['widgetType'] ) ) {
            return null;
        }

        $widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element );

        if ( ! $widget ) {
            return null;
        }

        return [
            'name'     => (string) $element['widgetType'],
            'settings' => $widget->get_settings_for_display(),
            'object'   => $widget,
        ];
    }

    /**
     * One element in a saved document, by id.
     *
     * @param  array  $elements
     * @param  string $section_id
     * @return array|null
     */
    private static function find( $elements, $section_id ) {
        foreach ( (array) $elements as $element ) {
            if ( isset( $element['id'] ) && $element['id'] === $section_id ) {
                return $element;
            }

            if ( ! empty( $element['elements'] ) ) {
                $found = self::find( $element['elements'], $section_id );

                if ( $found ) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * The post an Elementor section is being rendered from, for the callback address.
     *
     * @return int
     */
    public static function current_post_id() {
        if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
            return (int) get_the_ID();
        }

        $document = \Elementor\Plugin::$instance->documents->get_current();

        if ( $document ) {
            return (int) $document->get_main_id();
        }

        return (int) get_the_ID();
    }
}
