<?php
namespace Woolentor\Modules\CurrencySwitcher\Frontend;
use WooLentor\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Manage_Price {
    use Singleton;

    private $currency_code = 'USD';
	private $decimal;
    private $currency_symbol;
	private $symbol_position;
    private $exchange_rate;
    private $exchange_fee;
	private $disallowed_payment_method;
    private $rounding = 'none';
    private $rounding_to = 1;
    private $rounding_minus = 0;
    private $rounding_sale = 'skip_equal';

    public function __construct(){
        $this->set_current_currency();

        add_filter('woocommerce_currency', [ $this, 'set_currency_code' ] );
        add_filter('wc_get_price_decimals', [ $this, 'price_decimals' ] );
		add_filter('woocommerce_price_format', [ $this, 'price_format' ], 999 );
        
        // Product price
        add_filter('woocommerce_product_get_price', [ $this, 'product_price_modifier' ], 10, 2 );
        add_filter('woocommerce_product_get_regular_price', [ $this, 'product_regular_price_modifier' ], 10, 2 );
		add_filter('woocommerce_product_get_sale_price', [ $this, 'product_sale_price_modifier' ], 10, 2 );
        // Variation prices
		add_filter('woocommerce_variation_prices', [ $this, 'variation_product_regular_price_modifier' ], 999 );
        // Variable price
		add_filter('woocommerce_product_variation_get_price', [ $this, 'product_price_modifier' ], 999, 2 );
		add_filter('woocommerce_product_variation_get_regular_price', [ $this, 'product_regular_price_modifier' ], 999, 2 );
		add_filter('woocommerce_product_variation_get_sale_price', [ $this, 'product_sale_price_modifier' ], 999, 2 );
        // Coupon Amount
        add_action('woocommerce_coupon_loaded', [ $this, 'coupon_amount_modifier' ], 999 );
        // Shipping const
		add_filter('woocommerce_shipping_packages', [$this, 'shipping_cost_modifier']);
        if( woolentor_is_pro() ){
            add_filter( 'woocommerce_currency_symbol', [ $this, 'currency_symbol' ], 99999 );
            // Payment gatways
            if( !is_admin() ){
                add_filter('woocommerce_available_payment_gateways', [ $this, 'available_payment_gateways_modifier' ] );
            }
        }
    }

    /**
	 * Set Current Currency Switcher Property
	 * @return void
	 */
    private function set_current_currency() {
		$this->currency_code = woolentor_current_currency_code();
        $current_currency = woolentor_current_currency( $this->currency_code );
		$this->currency_symbol = (isset( $current_currency['custom_currency_symbol'] ) && $current_currency['custom_currency_symbol'] !== '') ? $current_currency['custom_currency_symbol'] : get_woocommerce_currency_symbol( $this->currency_code );
        if( count($current_currency) > 0 ){
            $this->exchange_rate = (int)( $current_currency['currency_excrate'] > 0 ) ? $current_currency['currency_excrate'] : 1;
            $this->exchange_fee = (int)( $current_currency['currency_excfee'] >= 0 ) ? $current_currency['currency_excfee'] : 0;
            $this->decimal = isset( $current_currency['currency_decimal'] ) ? $current_currency['currency_decimal'] : '';
            $this->symbol_position = isset( $current_currency['currency_position'] ) ? $current_currency['currency_position'] : '';
            $this->disallowed_payment_method = isset( $current_currency['disallowed_payment_method'] ) ? $current_currency['disallowed_payment_method'] : '';

            // Price rounding (Pro)
            if( woolentor_is_pro() ){
                $rounding = isset( $current_currency['currency_rounding'] ) ? $current_currency['currency_rounding'] : 'none';
                $this->rounding = in_array( $rounding, [ 'up', 'down', 'nearest' ], true ) ? $rounding : 'none';

                $rounding_to = isset( $current_currency['currency_rounding_to'] ) ? (float) $current_currency['currency_rounding_to'] : 1;
                $this->rounding_to = $rounding_to > 0 ? $rounding_to : 1;

                $rounding_minus = isset( $current_currency['currency_rounding_minus'] ) ? (float) $current_currency['currency_rounding_minus'] : 0;
                $this->rounding_minus = $rounding_minus > 0 ? $rounding_minus : 0;

                $rounding_sale = isset( $current_currency['currency_rounding_sale'] ) ? $current_currency['currency_rounding_sale'] : 'skip_equal';
                $this->rounding_sale = in_array( $rounding_sale, [ 'skip_equal', 'always', 'none' ], true ) ? $rounding_sale : 'skip_equal';
            }
        }
    }

    /**
     * Set Currency Code
     */
    public function set_currency_code(){
        return strtoupper( $this->currency_code );
    }

    /**
	 * Modify WC price decimals
	 */
    public function price_decimals( $wc_decimal ) {
        if( $this->decimal || $this->decimal == '0' ) {
            return $this->decimal;
        }
        else {
            return $wc_decimal;
        }
    }

    /**
	 * Modify WC price format
	 */
    public function price_format() {
        $currency_position = $this->symbol_position;
        $format = '%1$s%2$s';
        switch ( $currency_position ) {
            case 'left':
                $format = '%1$s%2$s';
                break;
            case 'right':
                $format = '%2$s%1$s';
                break;
            case 'left_space':
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space':
                $format = '%2$s&nbsp;%1$s';
                break;
        }
        return $format;
    }

    /**
	 * Modify Currency symbol
	 */
    public function currency_symbol( $currency_symbol, $currency = '' ){
        $currency_symbol = $this->currency_symbol;
        return $currency_symbol;
    }

    /**
     * Calculate Price
     *
     * @param [type] $value
     */
    public function calculate_price( $value ) {
        if( !empty( $value ) ) {
            if( $this->currency_code != woolentor_default_currency() ) {
                return ( ( $this->exchange_rate + $this->exchange_fee ) * (float)$value );
            }
        }
        return $value;
    }

    /**
     * Is price rounding active for the current currency
     *
     * @return bool
     */
    private function is_rounding_active() {
        return 'none' !== $this->rounding && $this->currency_code != woolentor_default_currency();
    }

    /**
     * Round a converted price: Up / Down / Nearest to the "Round To" step, then subtract "Rounding Minus".
     * e.g. 45.08, Up, 1, minus 0.01 => 46 - 0.01 = 45.99
     *
     * @param float $value Converted price
     * @return float
     */
    public function round_price( $value ) {
        if( '' === $value || null === $value || ! is_numeric( $value ) || (float) $value <= 0 ) {
            return $value;
        }

        $value = (float) $value;
        $step  = (float) $this->rounding_to;

        switch ( $this->rounding ) {
            case 'up':
                $rounded = ceil( round( $value / $step, 6 ) ) * $step;
                break;

            case 'down':
                $rounded = floor( round( $value / $step, 6 ) ) * $step;
                break;

            case 'nearest':
                $rounded = round( $value / $step ) * $step;
                break;

            default:
                return $value;
        }

        // "Down" can give 0 for a small price (e.g. 4.50 down to 10). Keep the converted price then.
        if( $rounded <= 0 ) {
            return $value;
        }

        // Subtract only when the result stays above 0.
        if( $this->rounding_minus > 0 && ( $rounded - $this->rounding_minus ) > 0 ) {
            $rounded -= $this->rounding_minus;
        }

        return round( $rounded, wc_get_price_decimals() );
    }

    /**
     * Convert and round regular / sale price together, so the sale price option can compare them.
     *
     * @param mixed $regular Regular price in default currency
     * @param mixed $sale    Sale price in default currency ('' when not on sale)
     * @return array [ 'regular' => mixed, 'sale' => mixed ]
     */
    public function get_rounded_prices( $regular, $sale ) {
        $converted_regular = $this->calculate_price( $regular );
        $converted_sale    = $this->calculate_price( $sale );

        if( ! $this->is_rounding_active() ) {
            return [ 'regular' => $converted_regular, 'sale' => $converted_sale ];
        }

        $final_regular = $this->round_price( $converted_regular );

        if( '' === $sale || null === $sale ) {
            return [ 'regular' => $final_regular, 'sale' => $converted_sale ];
        }

        switch ( $this->rounding_sale ) {
            case 'always':
                $final_sale = $this->round_price( $converted_sale );
                break;

            case 'none':
                $final_sale = $converted_sale;
                break;

            default: // skip_equal
                $final_sale = $this->round_price( $converted_sale );
                if( '' !== $final_regular && (float) $final_sale >= (float) $final_regular ) {
                    $final_sale = $converted_sale;
                }
                break;
        }

        // Never show a sale price that is not lower than the regular price because of rounding
        // (e.g. "Down" pulled the regular price under the unrounded sale price): keep the regular price unrounded.
        if( 'always' !== $this->rounding_sale && '' !== $final_regular && (float) $final_sale >= (float) $final_regular ) {
            $final_regular = $converted_regular;
        }

        return [ 'regular' => $final_regular, 'sale' => $final_sale ];
    }

    /**
     * Raw (default currency) prices of a product, read without our filters.
     *
     * @param \WC_Product $product
     * @return array
     */
    private function get_raw_prices( $product ) {
        return [
            'price'   => $product->get_price( 'edit' ),
            'regular' => $product->get_regular_price( 'edit' ),
            'sale'    => $product->get_sale_price( 'edit' ),
        ];
    }

    /**
     * Converted (and rounded) price for one price type of a product.
     *
     * @param mixed       $value   Price in default currency
     * @param \WC_Product $product
     * @param string      $type    price | regular | sale
     * @return mixed
     */
    private function product_price_by_type( $value, $product, $type ) {
        if( ! $this->is_rounding_active() || ! is_a( $product, 'WC_Product' ) ) {
            return $this->calculate_price( $value );
        }

        $raw     = $this->get_raw_prices( $product );
        $rounded = $this->get_rounded_prices( $raw['regular'], $raw['sale'] );

        if( 'regular' === $type ) {
            return $rounded['regular'];
        }

        if( 'sale' === $type ) {
            return $rounded['sale'];
        }

        // Active price: follow the sale or regular price it comes from.
        if( '' !== $raw['sale'] && (string) $value === (string) $raw['sale'] && $product->is_on_sale( 'edit' ) ) {
            return $rounded['sale'];
        }
        if( (string) $value === (string) $raw['regular'] ) {
            return $rounded['regular'];
        }

        return $this->round_price( $this->calculate_price( $value ) );
    }

    /**
     * Modify Product Price
     */
    public function product_price_modifier( $price, $product = null ) {
		return $this->product_price_by_type( $price, $product, 'price' );
    }

    /**
	 * Modify Products Regular Price
	 */
    public function product_regular_price_modifier( $price, $product = null ) {
		return $this->product_price_by_type( $price, $product, 'regular' );
    }

    /**
	 * Modify Products Sale Price
	 */
    public function product_sale_price_modifier( $price, $product = null ) {
		return $this->product_price_by_type( $price, $product, 'sale' );
    }

    /**
	 * Modify Variation Products Price (price range of variable products)
	 */
    public function variation_product_regular_price_modifier( $prices ) {
		$new_price = [];

        if( $this->is_rounding_active() && isset( $prices['regular_price'] ) && is_array( $prices['regular_price'] ) ) {
            foreach( $prices['regular_price'] as $variation_id => $regular ) {
                $sale    = isset( $prices['sale_price'][ $variation_id ] ) ? $prices['sale_price'][ $variation_id ] : '';
                $price   = isset( $prices['price'][ $variation_id ] ) ? $prices['price'][ $variation_id ] : $regular;
                $rounded = $this->get_rounded_prices( $regular, ( $sale !== '' && $sale !== $regular ) ? $sale : '' );

                $new_price['regular_price'][ $variation_id ] = $rounded['regular'];
                $new_price['sale_price'][ $variation_id ]    = ( $sale !== '' && $sale !== $regular ) ? $rounded['sale'] : $rounded['regular'];
                $new_price['price'][ $variation_id ]         = ( $sale !== '' && $sale !== $regular && (string) $price === (string) $sale ) ? $rounded['sale'] : $rounded['regular'];
            }
            return $new_price;
        }

        foreach( $prices as $key => $values) {
            foreach( $values as $mn => $val ) {
                $new_price[$key][$mn] = $this->calculate_price( $val );
            }
        }
		if( empty( $new_price ) ) {
            return $prices;
        }
        return $new_price;
	}

    /**
	 * Modify Variation Products Price
	 */
    public function variation_product_price_modifier( $price ) {
		return $this->calculate_price( $price );
	}

    /**
	 * Modify Products Coupon Amount
	 */
    public function coupon_amount_modifier( $coupon ) {
        $type = $coupon->get_discount_type();
        if( $type === 'fixed_cart' || $type === 'fixed_product' ) {
            $coupon->set_amount( $this->calculate_price( $coupon->get_amount() ) );
        }
		return $coupon;
	}

    /**
	 * Modify Shipping const
	 */
    public function shipping_cost_modifier($packages){
		foreach( $packages as $package ) {
			foreach( $package['rates'] as $rate ) {
				$rate->set_cost( $this->calculate_price( $rate->get_cost() ) );
			}
		}
		return $packages;
	}

    /**
	 * Exclude disallowed payment method
	 */
    public function available_payment_gateways_modifier( $available_gate_ways ) {
        if( is_array( $this->disallowed_payment_method ) ) {
            foreach ( $this->disallowed_payment_method as $gateway ) {
                unset( $available_gate_ways[$gateway] );
            }
        }
        return $available_gate_ways;
    }

}