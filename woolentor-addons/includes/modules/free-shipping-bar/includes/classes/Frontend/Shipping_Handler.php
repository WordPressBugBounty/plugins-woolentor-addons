<?php
namespace Woolentor\Modules\FreeShippingBar\Frontend;
use WooLentor\Traits\Singleton;

if (!defined('ABSPATH')){
    exit;
}

/**
 * Applies free shipping programmatically when shipping_mode === 'module'.
 *
 * Hooks into woocommerce_package_rates to inject a zero-cost Free Shipping
 * rate whenever the cart subtotal meets or exceeds the configured threshold.
 * WooCommerce shipping zone configuration is not required in this mode.
 */
class Shipping_Handler {
    use Singleton;

    private function __construct() {
        // Clear the WC shipping session cache before totals are calculated so
        // woocommerce_package_rates always re-runs when mode is 'module'.
        add_action( 'woocommerce_before_calculate_totals', [ $this, 'clear_shipping_session_cache' ], 5 );
        add_filter( 'woocommerce_package_rates', [ $this, 'maybe_apply_free_shipping' ], 100, 2 );
    }

    /**
     * Clear WooCommerce shipping session cache for all packages.
     *
     * WC caches rates per package-hash and returns them without running
     * woocommerce_package_rates when the hash matches.  Nulling the session
     * entries forces a fresh calculation on every request in module mode.
     *
     * @return void
     */
    public function clear_shipping_session_cache() {
        if ( Bar_Renderer::setting( 'shipping_mode', 'module' ) !== 'module' ) {
            return;
        }
        if ( ! function_exists( 'WC' ) || ! WC()->session ) {
            return;
        }
        for ( $i = 0; $i <= 5; $i++ ) {
            WC()->session->set( 'shipping_for_package_' . $i, null );
        }
    }

    /**
     * Inject a free shipping rate when cart total meets the threshold.
     *
     * @param  array $rates   Available shipping rates for the package.
     * @param  array $package WooCommerce shipping package.
     * @return array
     */
    public function maybe_apply_free_shipping( $rates, $package ) {
        $threshold = Bar_Renderer::get_threshold();

        if ( Bar_Renderer::setting( 'shipping_mode', 'module' ) !== 'module' ) {
            return $rates;
        }
        if ( $threshold <= 0 ) {
            return $rates;
        }

        $cart_total = isset( $package['cart_subtotal'] )
            ? (float) $package['cart_subtotal']
            : ( WC()->cart ? (float) WC()->cart->get_displayed_subtotal() : 0 );

        if ( $cart_total < $threshold ) {
            return $rates;
        }

        $free_rate = new \WC_Shipping_Rate(
            'woolentor_fsb_free_shipping',
            (string) apply_filters( 'woolentor_fsb_free_shipping_label', esc_html__( 'Free Shipping', 'woolentor' ) ),
            0,
            [],
            'woolentor_fsb'
        );

        if ( Bar_Renderer::setting( 'module_hide_other_rates', 'on' ) === 'on' ) {
            return [ 'woolentor_fsb_free_shipping' => $free_rate ];
        }

        $rates['woolentor_fsb_free_shipping'] = $free_rate;
        return $rates;
    }
}
