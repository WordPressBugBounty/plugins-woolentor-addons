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
 * rate whenever the cart subtotal meets or exceeds the configured threshold
 * and the destination zone is eligible (see is_package_eligible()).
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

        $cart_total = Bar_Renderer::get_cart_total();

        if ( $cart_total < $threshold ) {
            return $rates;
        }

        if ( ! self::is_package_eligible( $package ) ) {
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

    /**
     * Check whether the package destination may receive module free shipping.
     *
     * - If "Free Shipping Zones" are selected, the matching zone must be one of them.
     * - If the matching zone has no enabled shipping methods, the store does not ship
     *   there, so no free rate is added. Stores without any shipping methods at all
     *   keep the previous behaviour (free rate everywhere).
     *
     * @param  array $package WooCommerce shipping package.
     * @return bool
     */
    public static function is_package_eligible( $package ) {
        $zone = \WC_Shipping_Zones::get_zone_matching_package( $package );

        $allowed_zones = Bar_Renderer::setting( 'module_allowed_zones', [] );
        $allowed_zones = is_array( $allowed_zones ) ? array_map( 'strval', array_filter( $allowed_zones, 'strlen' ) ) : [];

        if ( ! empty( $allowed_zones ) && ! in_array( (string) $zone->get_id(), $allowed_zones, true ) ) {
            return false;
        }

        if ( empty( $zone->get_shipping_methods( true ) ) && self::store_has_shipping_methods() ) {
            return false;
        }

        return (bool) apply_filters( 'woolentor_fsb_package_eligible', true, $package, $zone );
    }

    /**
     * Build a shipping package from the customer's address (shipping first,
     * billing as fallback).
     *
     * @return array|null Null when the customer's country is not known yet.
     */
    public static function get_customer_package() {
        if ( ! function_exists( 'WC' ) || ! WC()->customer ) {
            return null;
        }

        $customer = WC()->customer;
        $country  = $customer->get_shipping_country() ?: $customer->get_billing_country();
        if ( ! $country ) {
            return null;
        }

        return [
            'destination' => [
                'country'   => $country,
                'state'     => $customer->get_shipping_state() ?: $customer->get_billing_state(),
                'postcode'  => $customer->get_shipping_postcode() ?: $customer->get_billing_postcode(),
                'city'      => $customer->get_shipping_city() ?: $customer->get_billing_city(),
                'address'   => $customer->get_shipping_address() ?: $customer->get_billing_address(),
                'address_1' => $customer->get_shipping_address() ?: $customer->get_billing_address(),
                'address_2' => $customer->get_shipping_address_2() ?: $customer->get_billing_address_2(),
            ],
        ];
    }

    /**
     * WooCommerce shipping zone matching the customer's address.
     *
     * @return \WC_Shipping_Zone|null Null when the customer's country is not known yet.
     */
    public static function get_customer_zone() {
        $package = self::get_customer_package();
        return $package ? \WC_Shipping_Zones::get_zone_matching_package( $package ) : null;
    }

    /**
     * Whether free shipping can apply to the current customer's destination.
     *
     * Used by the bar so it doesn't promise free shipping where none will be applied.
     * - Module mode: the destination must pass is_package_eligible().
     * - WooCommerce mode: the matched zone must have an enabled Free Shipping method.
     * Always true when the destination is unknown.
     *
     * @return bool
     */
    public static function is_customer_eligible() {
        $package = self::get_customer_package();
        if ( ! $package ) {
            return true;
        }

        if ( Bar_Renderer::setting( 'shipping_mode', 'module' ) === 'module' ) {
            $eligible = self::is_package_eligible( $package );
        } else {
            $eligible = self::zone_has_free_shipping( \WC_Shipping_Zones::get_zone_matching_package( $package ) );
        }

        /**
         * Whether to hide the bar when free shipping is not available for the customer's location.
         *
         * @param bool  $hide    Default true.
         * @param array $package Customer destination package.
         */
        if ( ! $eligible && ! apply_filters( 'woolentor_fsb_hide_when_not_eligible', true, $package ) ) {
            return true;
        }

        return $eligible;
    }

    /**
     * Whether a shipping zone has an enabled WooCommerce Free Shipping method.
     *
     * @param  \WC_Shipping_Zone $zone
     * @return bool
     */
    private static function zone_has_free_shipping( $zone ) {
        foreach ( $zone->get_shipping_methods( true ) as $method ) {
            if ( 'free_shipping' === $method->id ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Whether any shipping zone (including "Rest of World") has an enabled method.
     *
     * @return bool
     */
    private static function store_has_shipping_methods() {
        static $has_methods = null;

        if ( null === $has_methods ) {
            $has_methods = false;
            $zone_ids    = array_merge( wp_list_pluck( \WC_Shipping_Zones::get_zones(), 'zone_id' ), [ 0 ] );

            foreach ( $zone_ids as $zone_id ) {
                $zone = new \WC_Shipping_Zone( $zone_id );
                if ( ! empty( $zone->get_shipping_methods( true ) ) ) {
                    $has_methods = true;
                    break;
                }
            }
        }

        return $has_methods;
    }
}
