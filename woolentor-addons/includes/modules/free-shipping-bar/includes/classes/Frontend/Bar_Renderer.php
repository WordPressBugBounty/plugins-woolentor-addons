<?php
namespace Woolentor\Modules\FreeShippingBar\Frontend;
use WooLentor\Traits\Singleton;

if (!defined('ABSPATH')){
    exit;
}
// Exit if accessed directly

class Bar_Renderer {
    use Singleton;

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init_hooks() {
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action( 'wp_footer', [$this, 'render_bar']);
    }

    // -------------------------------------------------------------------------
    // Settings helper
    // -------------------------------------------------------------------------

    /**
     * Get a module setting value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function setting( $key, $default = '' ) {
        return woolentor_get_option( $key, 'woolentor_free_shipping_bar_settings', $default );
    }

    // -------------------------------------------------------------------------
    // Free-shipping threshold detection
    // -------------------------------------------------------------------------

    /**
     * Detect the free-shipping minimum-order-amount from WooCommerce shipping
     * zones, falling back to a manual override set in the admin panel.
     *
     * @return float  0 if no threshold is found.
     */
    public static function get_threshold() {
        $manual = (float) self::setting( 'manual_threshold', 0 );

        // In module mode the manual threshold is the only source — skip WC zone scan.
        if ( self::setting( 'shipping_mode', 'module' ) === 'module' ) {
            return (float) apply_filters( 'woolentor_fsb_get_threshold', $manual );
        }

        // WooCommerce mode: manual override still takes priority.
        if ( $manual > 0 ) {
            return $manual;
        }

        if ( ! function_exists( 'WC' ) || ! WC()->shipping() ) {
            return (float) apply_filters( 'woolentor_fsb_get_threshold', 0 );
        }

        // Walk through all shipping zones (including zone 0 = "Rest of World")
        $zones     = \WC_Shipping_Zones::get_zones();
        $zones[]   = [ 'zone_id' => 0 ];
        $threshold = 0;

        foreach ( $zones as $zone_data ) {
            $zone    = new \WC_Shipping_Zone( $zone_data['zone_id'] );
            $methods = $zone->get_shipping_methods( true ); // enabled only

            foreach ( $methods as $method ) {
                if ( $method->id === 'free_shipping' ) {
                    $min = isset( $method->instance_settings['min_amount'] )
                        ? (float) $method->instance_settings['min_amount']
                        : 0;

                    if ( $min > 0 ) {
                        $threshold = $min;
                        break 2;
                    }
                }
            }
        }

        /**
         * Allow Pro to override the threshold (e.g. geo-targeting).
         *
         * @param float $threshold Detected threshold (0 if none found).
         */
        return (float) apply_filters( 'woolentor_fsb_get_threshold', $threshold );
    }

    // -------------------------------------------------------------------------
    // Page / device visibility
    // -------------------------------------------------------------------------

    /**
     * Resolve the current page's ID, accounting for WooCommerce virtual pages.
     *
     * WooCommerce Shop/Cart/Checkout/MyAccount are loaded as post-type archives or
     * virtual endpoints — get_queried_object_id() returns 0 for them.
     *
     * @return int
     */
    public static function get_current_page_id() {
        if ( function_exists( 'is_shop' ) && is_shop() ) {
            return (int) wc_get_page_id( 'shop' );
        }
        if ( function_exists( 'is_cart' ) && is_cart() ) {
            return (int) wc_get_page_id( 'cart' );
        }
        if ( function_exists( 'is_checkout' ) && is_checkout() ) {
            return (int) wc_get_page_id( 'checkout' );
        }
        if ( function_exists( 'is_account_page' ) && is_account_page() ) {
            return (int) wc_get_page_id( 'myaccount' );
        }
        return (int) get_queried_object_id();
    }

    /**
     * Check whether the bar should be shown on the current page.
     *
     * @return bool
     */
    public static function should_show_on_page() {
        $pages = self::setting( 'display_pages', [] );

        // display_pages is a multiselect of WordPress page IDs (integers as strings).
        // Cast each entry to int and drop zeros/empty values.
        $page_ids = is_array( $pages )
            ? array_values( array_filter( array_map( 'absint', $pages ) ) )
            : [];

        if ( empty( $page_ids ) ) {
            // No specific pages selected → show on every page.
            $show = true;
        } else {
            $show = in_array( self::get_current_page_id(), $page_ids, true );
        }

        /**
         * Allow Pro (or custom code) to override page visibility.
         *
         * @param bool $show Whether the free module decided to show the bar.
         */
        return (bool) apply_filters( 'woolentor_fsb_should_show_on_page', $show );
    }

    // -------------------------------------------------------------------------
    // Asset enqueue
    // -------------------------------------------------------------------------

    /**
     * Enqueue frontend CSS and JS.
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( ! self::should_show_on_page() ) {
            return;
        }

        $threshold = self::get_threshold();
        if ( $threshold <= 0 ) {
            return;
        }

        wp_enqueue_style(
            'woolentor-free-shipping-bar',
            \Woolentor\Modules\FreeShippingBar\MODULE_ASSETS . '/css/free-shipping-bar.css',
            [],
            WOOLENTOR_VERSION
        );

        wp_enqueue_script(
            'woolentor-free-shipping-bar',
            \Woolentor\Modules\FreeShippingBar\MODULE_ASSETS . '/js/free-shipping-bar.js',
            [],
            WOOLENTOR_VERSION,
            true
        );

        // Dynamic inline CSS from admin colour settings
        $inline_css = self::build_inline_css();
        if ( $inline_css ) {
            wp_add_inline_style( 'woolentor-free-shipping-bar', $inline_css );
        }

        // Cart total for initial render (server-side)
        $cart_total = function_exists( 'WC' ) && WC()->cart
            ? (float) WC()->cart->get_displayed_subtotal()
            : 0;

        $localize_data = [
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( 'wl-fsb-nonce' ),
            'threshold'      => $threshold,
            'cartTotal'      => $cart_total,
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'priceFormat'    => get_woocommerce_price_format(),
            'decimals'       => wc_get_price_decimals(),
            'decimalSep'     => wc_get_price_decimal_separator(),
            'thousandSep'    => wc_get_price_thousand_separator(),
            'msgInitial'     => self::setting( 'msg_initial', esc_html__( 'Spend {amount} more to get FREE shipping!', 'woolentor' ) ),
            'msgSuccess'     => self::setting( 'msg_success', esc_html__( '🎉 You\'ve unlocked FREE shipping!', 'woolentor' ) ),
            'position'       => self::setting( 'bar_position', 'top' ),
            'showOnDevice'   => self::setting( 'show_on_device', 'all' ),
        ];

        /**
         * Allow Pro to append extra data (countdown config, A/B variant, analytics key, etc.).
         *
         * @param array $localize_data Script data passed to wlFSB object.
         */
        $localize_data = apply_filters( 'woolentor_fsb_localize_data', $localize_data );

        wp_localize_script( 'woolentor-free-shipping-bar', 'wlFSB', $localize_data );
    }

    /**
     * Build dynamic inline CSS from admin color/size settings.
     *
     * @return string
     */
    public static function build_inline_css() {
        $bg_color   = self::setting( 'bar_bg_color', '' );
        $text_color = self::setting( 'bar_text_color', '' );
        $fill_color = self::setting( 'bar_fill_color', '' );
        $font_size  = self::setting( 'bar_font_size', '' );

        $css = '';

        if ( $bg_color ) {
            $bg_color = sanitize_hex_color( $bg_color );
            if ( $bg_color ) {
                $css .= "#wl-free-shipping-bar { background-color: {$bg_color} !important; }";
            }
        }

        if ( $text_color ) {
            $text_color = sanitize_hex_color( $text_color );
            if ( $text_color ) {
                $css .= ".wl-fsb-message { color: {$text_color} !important; }";
            }
        }

        if ( $fill_color ) {
            $fill_color = sanitize_hex_color( $fill_color );
            if ( $fill_color ) {
                $css .= ".wl-fsb-progress-fill { background-color: {$fill_color} !important; }";
            }
        }

        if ( $font_size ) {
            $css .= ".wl-fsb-message { font-size: {$font_size}px !important; }";
        }

        return $css;
    }

    // -------------------------------------------------------------------------
    // Bar render
    // -------------------------------------------------------------------------

    /**
     * Output the bar HTML in the footer.
     * JS repositions it to top/bottom of <body> and makes it visible.
     *
     * @return void
     */
    public function render_bar() {
        if ( ! self::should_show_on_page() ) {
            return;
        }

        if ( self::get_threshold() <= 0 ) {
            return;
        }

        /**
         * Allow Pro to inject extra CSS classes on the bar wrapper (skins, animations).
         *
         * @param array $classes Default wrapper classes.
         */
        $classes    = apply_filters( 'woolentor_fsb_bar_classes', [ 'wl-fsb-wrap', 'wl-fsb-hidden' ] );
        $class_attr = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
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
            <button class="wl-fsb-close" aria-label="<?php esc_attr_e( 'Close', 'woolentor' ); ?>">&times;</button>
        </div>
        <?php
    }
}
