<?php
namespace WooLentor\Product_Kit;

/**
 * The kit's slider layer.
 *
 * Every pack widget that carries a carousel writes the same Slick configuration by hand. This is
 * that configuration, read from the shared schema so an Elementor widget and a Gutenberg block
 * configure the same carousel from the same keys.
 *
 * It emits attributes only. The mechanism is the bundled Slick, driven by WLPackSlider — a pack
 * widget writes the adapter and the skin, never the mechanism.
 *
 * **A rail replaces the tab row, it does not sit under one.** A carousel already moves through its
 * own products, so a tab row above it would be two ways to change what is on screen; the same goes
 * for a pager. Turning the slider on hides both.
 *
 * The shared script still rebuilds the track after any swap — off the `wl-section:rendered` event —
 * so a consumer that does combine them is not broken by the kit. Product Showcase does not.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Slider {

    /**
     * The attributes that turn a card list into a carousel, or an empty string when this layout is
     * not one.
     *
     * @param  array $settings
     * @param  array $args {
     *     @type bool $enabled  The consumer's veto — a grid layout passes false.
     * }
     * @return string
     */
    public static function attrs( array $settings, array $args = [] ) {
        $args = wp_parse_args( $args, [ 'enabled' => null ] );
        $on   = null === $args['enabled'] ? self::enabled( $settings ) : (bool) $args['enabled'];

        if ( ! $on ) {
            return '';
        }

        $config = wp_json_encode( self::config( $settings ) );

        if ( ! $config ) {
            return '';
        }

        return ' data-wl-slider="true" data-slider-settings=\'' . esc_attr( $config ) . '\'';
    }

    /**
     * Is this section a carousel?
     *
     * The switch is a Pro feature on every design, so a free site always answers no however the
     * setting was saved — and the setting is kept, so the rail appears the day Pro is installed.
     *
     * @param  array $settings
     * @return bool
     */
    public static function enabled( array $settings ) {
        if ( Schema::is_pro_field( 'slider', 'enable_slider' ) ) {
            return false;
        }

        return Settings::truthy( $settings, 'enable_slider' );
    }

    /**
     * The Slick configuration for a settings array.
     *
     * @param  array $settings
     * @return array
     */
    public static function config( array $settings ) {
        // The keys are WLPackSlider's, not Slick's — the adapter maps them. The control names are
        // the ones Hero Banner already ships, so that widget can move onto the kit later without a
        // data migration on anybody's saved pages.
        $config = [
            'arrows'         => Settings::truthy( $settings, 'slider_arrows', true ),
            'dots'           => Settings::truthy( $settings, 'slider_dots' ),
            'infinite'       => Settings::truthy( $settings, 'slider_infinite' ),
            'fade'           => Settings::truthy( $settings, 'slider_fade' ),
            'autoplay'       => Settings::truthy( $settings, 'slider_autoplay' ),
            'autoplay_speed' => Settings::number( $settings, 'slider_autoplay_speed', 5000, 500, 30000 ),
            'speed'          => Settings::number( $settings, 'slider_speed', 600, 100, 5000 ),
            'pause_on_hover' => Settings::truthy( $settings, 'slider_pause_on_hover', true ),
            'items'          => Settings::number( $settings, 'sl_items', 4, 1, 12 ),
            'scroll'         => Settings::number( $settings, 'sl_scroll', 1, 1, 12 ),
            'tablet_width'   => Settings::number( $settings, 'sl_tablet_width', 1024, 320, 1600 ),
            'tablet_items'   => Settings::number( $settings, 'sl_tablet_items', 3, 1, 12 ),
            'tablet_scroll'  => Settings::number( $settings, 'sl_tablet_scroll', 1, 1, 12 ),
            'mobile_width'   => Settings::number( $settings, 'sl_mobile_width', 640, 240, 1024 ),
            'mobile_items'   => Settings::number( $settings, 'sl_mobile_items', 2, 1, 12 ),
            'mobile_scroll'  => Settings::number( $settings, 'sl_mobile_scroll', 1, 1, 12 ),
        ];

        // Slick can only cross-fade a single slide, and it fades the whole track — turning it on
        // with more than one card per view leaves cards stacked on each other. A rail that asks for
        // both gets the rail, because that is what its layout is.
        if ( $config['fade'] && $config['items'] > 1 ) {
            $config['fade'] = false;
        }

        // Scrolling further than the rail shows leaves a gap at the end of the track.
        foreach ( [ '' => '', 'tablet_' => 'tablet_', 'mobile_' => 'mobile_' ] as $device ) {
            $config[ $device . 'scroll' ] = min( $config[ $device . 'scroll' ], $config[ $device . 'items' ] );
        }

        return apply_filters( 'woolentor_product_kit_slider_config', $config, $settings );
    }
}
