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
     * Single source of truth for all bar HTML — used by the footer hook,
     * the Elementor widget, the Gutenberg block, and direct shortcode usage.
     *
     * Attributes:
     *   inline          yes|no   – Render inline (no fixed positioning).  Default: no.
     *   widget          yes|no   – Use widget/block card layout.           Default: no.
     *   dismissible     yes|no   – Show the close button.                  Default: yes.
     *   show_icon       yes|no   – Show the icon area.                     Default: no.
     *   icon            string   – CSS icon class (e.g. "fas fa-truck").   Default: ''.
     *   show_countdown  yes|no   – Adds wl-fsb-no-countdown when 'no'.     Default: yes.
     *   message         string   – Custom initial message ({amount} token). Default: ''.
     *   message_success string   – Custom success message.                  Default: ''.
     *
     * @param  array  $atts
     * @param  string $content
     * @return string
     */
    public function render( $atts, $content = '' ) {
        Bar_Renderer::instance();

        if ( Bar_Renderer::get_threshold() <= 0 ) {
            return '';
        }

        $atts = shortcode_atts( [
            'inline'          => 'no',
            'widget'          => 'no',
            'dismissible'     => 'yes',
            'show_icon'       => 'no',
            'icon'            => '',
            'show_countdown'  => 'yes',
            'message'         => '',
            'message_success' => '',
        ], $atts, 'woolentor_free_shipping_bar' );

        Bar_Renderer::mark_rendered();

        return 'yes' === $atts['widget']
            ? $this->render_widget_markup( $atts )
            : $this->render_bar_markup( $atts );
    }

    // -------------------------------------------------------------------------
    // Markup builders
    // -------------------------------------------------------------------------

    /**
     * Widget / block card layout.
     * Used when widget="yes" — called by the Elementor widget and Gutenberg block.
     *
     * @param  array $atts
     * @return string
     */
    private function render_widget_markup( array $atts ) {
        $classes = apply_filters( 'woolentor_fsb_bar_classes', [ 'wl-fsb-wrap', 'wl-fsb-widget', 'wl-fsb-inline' ] );

        if ( 'yes' !== $atts['show_countdown'] ) {
            $classes[] = 'wl-fsb-no-countdown';
        }

        $class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
        $data_attrs = $this->build_data_attrs( $atts );

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $class_attr ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Free shipping progress', 'woolentor' ); ?>"<?php echo $data_attrs; ?>>
            <div class="wl-fsb-inner">
                <div class="wl-fsb-content">
                    <?php if ( 'yes' === $atts['show_icon'] ) :
                        $icon_html = $this->build_icon_html( $atts );
                        if ( $icon_html ) : ?>
                        <div class="wl-fsb-icon" aria-hidden="true">
                            <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php endif; endif; ?>
                    <div class="wl-fsb-text">
                        <p class="wl-fsb-message"></p>
                        <div class="wl-fsb-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                            <div class="wl-fsb-progress-fill"></div>
                        </div>
                        <?php do_action( 'woolentor_fsb_bar_inner_html' ); ?>
                    </div>
                </div>
            </div>

            <?php if ( 'yes' === $atts['dismissible'] ) : ?>
                <button class="wl-fsb-close" aria-label="<?php esc_attr_e( 'Close', 'woolentor' ); ?>">&times;</button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Global / footer bar layout.
     * Used when widget="no" (default) — fixed bar at top or bottom of viewport,
     * or inline in page content when inline="yes".
     *
     * @param  array $atts
     * @return string
     */
    private function render_bar_markup( array $atts ) {
        $classes = apply_filters( 'woolentor_fsb_bar_classes', [ 'wl-fsb-wrap', 'wl-fsb-hidden' ] );

        if ( 'yes' === $atts['inline'] ) {
            $classes[] = 'wl-fsb-inline';
        }

        $class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
        $data_attrs = $this->build_data_attrs( $atts );

        ob_start();
        ?>
        <div id="wl-free-shipping-bar" class="<?php echo esc_attr( $class_attr ); ?>" role="complementary" aria-label="<?php esc_attr_e( 'Free shipping progress', 'woolentor' ); ?>"<?php echo $data_attrs; ?>>
            <div class="wl-fsb-inner">
                <p class="wl-fsb-message"></p>
                <div class="wl-fsb-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="wl-fsb-progress-fill"></div>
                </div>
                <?php do_action( 'woolentor_fsb_bar_inner_html' ); ?>
            </div>

            <?php if ( 'yes' === $atts['dismissible'] ) : ?>
                <button class="wl-fsb-close" aria-label="<?php esc_attr_e( 'Close', 'woolentor' ); ?>">&times;</button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build the data-msg-* attribute string from shortcode atts.
     *
     * @param  array $atts
     * @return string
     */
    private function build_data_attrs( array $atts ) {
        $data = '';
        if ( ! empty( $atts['message'] ) ) {
            $data .= ' data-msg-initial="' . esc_attr( $atts['message'] ) . '"';
        }
        if ( ! empty( $atts['message_success'] ) ) {
            $data .= ' data-msg-success="' . esc_attr( $atts['message_success'] ) . '"';
        }
        return $data;
    }

    /**
     * Build the icon HTML for the widget layout.
     *
     * The woolentor_fsb_icon_html filter lets callers (e.g. the Elementor widget)
     * replace the default <i> tag with their own rendered icon (SVG, etc.).
     *
     * @param  array $atts
     * @return string
     */
    private function build_icon_html( array $atts ) {
        $default = ! empty( $atts['icon'] )
            ? '<i class="' . esc_attr( $atts['icon'] ) . '"></i>'
            : '';

        return (string) apply_filters( 'woolentor_fsb_icon_html', $default, $atts );
    }
}
