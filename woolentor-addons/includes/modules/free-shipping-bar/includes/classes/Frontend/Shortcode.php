<?php
namespace Woolentor\Modules\FreeShippingBar\Frontend;
use WooLentor\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Shortcode {
    use Singleton;

    public function __construct() {
        add_shortcode( 'woolentor_free_shipping_bar', [ $this, 'render' ] );
    }

    /**
     * [woolentor_free_shipping_bar] shortcode handler.
     *
     * Enqueues assets (safe to call after wp_enqueue_scripts) and returns
     * the bar HTML so it can be placed anywhere on the page.
     *
     * @param  array  $atts
     * @param  string $content
     * @return string
     */
    public function render( $atts, $content = '' ) {
        $renderer = Bar_Renderer::instance();

        if ( Bar_Renderer::get_threshold() <= 0 ) {
            return '';
        }

        $default_atts = [
            'inline'      => 'yes',
            'dismissible' => 'yes',
        ];

        $atts = shortcode_atts( $default_atts, $atts, 'woolentor_free_shipping_bar' );

        $renderer->enqueue_assets();

        /**
         * Allow Pro to inject extra CSS classes on the bar wrapper (skins, animations).
         *
         * @param array $classes Default wrapper classes.
         */
        $classes = apply_filters( 'woolentor_fsb_bar_classes', [ 'wl-fsb-wrap', 'wl-fsb-hidden' ] );

        if ( 'yes' === $atts['inline'] ) {
            $classes[] = 'wl-fsb-inline';
        }

        $class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );

        ob_start();
        ?>
        <div id="wl-free-shipping-bar" class="<?php echo esc_attr( $class_attr ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Free shipping progress', 'woolentor' ); ?>">
            <div class="wl-fsb-inner">
                <p class="wl-fsb-message"></p>
                <div class="wl-fsb-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="wl-fsb-progress-fill"></div>
                </div>
                <?php
                /**
                 * Allow Pro to append extra HTML inside the inner wrapper (countdown timer, etc.).
                 */
                do_action( 'woolentor_fsb_bar_inner_html' );
                ?>
            </div>

            <?php if ( 'yes' === $atts['dismissible'] ) : ?>
                <button class="wl-fsb-close" aria-label="<?php esc_attr_e( 'Close', 'woolentor' ); ?>">&times;</button>
            <?php endif; ?>
        </div>
        <?php

        Bar_Renderer::mark_rendered();

        return ob_get_clean();
    }
}
