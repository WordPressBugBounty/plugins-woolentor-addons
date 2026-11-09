<?php
namespace Woolentor\Modules\CurrencySwitcher;
use WooLentor\Traits\Singleton;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Keep WooCommerce Analytics in the store currency.
 *
 * An order placed in a switched currency keeps its amounts in that currency (that is correct
 * for the order, emails and invoices). WooCommerce Analytics does not know about currencies and
 * adds every order total as if it was in the store currency. So we:
 *  1. Save the exchange rate used at checkout in the order meta.
 *  2. Divide the amounts by that rate when WooCommerce writes the Analytics lookup tables
 *     (wc_order_stats, wc_order_product_lookup, wc_order_coupon_lookup, wc_order_tax_lookup).
 *  3. Give a WooCommerce > Status > Tools action to fix orders placed before this change.
 */
class Order_Analytics {
    use Singleton;

    const RATE_META          = '_woolentor_currency_exchange_rate';
    const BASE_CURRENCY_META = '_woolentor_currency_base';
    const RATE_SOURCE_META   = '_woolentor_currency_rate_source';
    const TOOL_ID            = 'woolentor_currency_analytics_sync';

    /**
     * Rate cache by order ID.
     * @var array
     */
    private $rates = [];

    public function __construct(){
        // Save the rate when the order is created (classic and block checkout).
        add_action( 'woocommerce_checkout_create_order', [ $this, 'save_order_rate' ], 10, 1 );
        add_action( 'woocommerce_store_api_checkout_update_order_meta', [ $this, 'save_order_rate' ], 10, 1 );

        // Analytics lookup tables.
        add_action( 'woocommerce_analytics_update_order_stats', [ $this, 'convert_order_stats' ], 10, 1 );
        add_action( 'woocommerce_analytics_update_product', [ $this, 'convert_product_lookup' ], 10, 2 );
        add_action( 'woocommerce_analytics_update_coupon', [ $this, 'convert_coupon_lookup' ], 10, 2 );
        add_action( 'woocommerce_analytics_update_tax', [ $this, 'convert_tax_lookup' ], 10, 2 );

        // Tool for orders placed before the rate was saved.
        add_filter( 'woocommerce_debug_tools', [ $this, 'register_tool' ] );
    }

    /**
     * Store currency. Read the option directly: get_woocommerce_currency() is filtered by the
     * switcher and returns the visitor's currency on frontend / AJAX requests.
     * @return string
     */
    public static function store_currency(){
        return strtoupper( (string) get_option( 'woocommerce_currency' ) );
    }

    /**
     * Rate the switcher multiplies store prices with for a currency.
     * Same as Manage_Price::calculate_price(): (exchange rate + fee), and 1 for the default currency.
     * @param string $currency_code
     * @return float|false False when the currency is not in the switcher list.
     */
    public static function get_currency_rate( $currency_code ){
        $currency_code = strtoupper( (string) $currency_code );
        if ( '' === $currency_code ) {
            return false;
        }
        if ( strtoupper( (string) woolentor_default_currency() ) === $currency_code ) {
            return 1.0;
        }

        $currency = woolentor_current_currency( $currency_code );
        if ( empty( $currency ) || ! isset( $currency['currency'] ) || strtoupper( $currency['currency'] ) !== $currency_code ) {
            return false;
        }

        $rate = isset( $currency['currency_excrate'] ) ? (float) $currency['currency_excrate'] : 0;
        $fee  = isset( $currency['currency_excfee'] ) ? (float) $currency['currency_excfee'] : 0;
        $rate = $rate > 0 ? $rate : 1;
        $fee  = $fee >= 0 ? $fee : 0;

        return (float) ( $rate + $fee );
    }

    /**
     * Save the exchange rate on a new order.
     * @param \WC_Order $order
     * @return void
     */
    public function save_order_rate( $order ){
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        $rate = self::get_currency_rate( $order->get_currency() );
        if ( false === $rate ) {
            // Currency not handled by the switcher (e.g. store currency without a list entry).
            $order->delete_meta_data( self::RATE_META );
            $order->delete_meta_data( self::BASE_CURRENCY_META );
            $order->delete_meta_data( self::RATE_SOURCE_META );
            return;
        }

        $order->update_meta_data( self::RATE_META, wc_format_decimal( $rate ) );
        $order->update_meta_data( self::BASE_CURRENCY_META, self::store_currency() );
        $order->update_meta_data( self::RATE_SOURCE_META, 'checkout' );
    }

    /**
     * Rate to divide an order's amounts by. Refunds use their parent order.
     * @param \WC_Abstract_Order|int $order
     * @return float 1 when nothing has to be converted.
     */
    public function get_order_rate( $order ){
        $order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
        if ( ! $order instanceof \WC_Abstract_Order ) {
            return 1.0;
        }

        $order_id = $order->get_id();
        if ( isset( $this->rates[ $order_id ] ) ) {
            return $this->rates[ $order_id ];
        }

        $source = $order;
        if ( 'shop_order_refund' === $order->get_type() && $order->get_parent_id() ) {
            $parent = wc_get_order( $order->get_parent_id() );
            if ( $parent instanceof \WC_Abstract_Order ) {
                $source = $parent;
            }
        }

        $rate = (float) $source->get_meta( self::RATE_META, true );
        $base = (string) $source->get_meta( self::BASE_CURRENCY_META, true );

        // Only convert when the order is not already in the store currency it was saved for.
        if ( $rate <= 0 || strtoupper( $source->get_currency() ) === strtoupper( $base ) || $base !== self::store_currency() ) {
            $rate = 1.0;
        }

        /**
         * Filters the rate used to convert an order's Analytics data to the store currency.
         *
         * @param float              $rate  Divide order amounts by this. 1 = no conversion.
         * @param \WC_Abstract_Order $order Order or refund.
         */
        $rate = (float) apply_filters( 'woolentor_currency_switcher_analytics_rate', $rate, $order );
        $rate = $rate > 0 ? $rate : 1.0;

        $this->rates[ $order_id ] = $rate;
        return $rate;
    }


    /**
     * wc_order_stats row. Runs right after WooCommerce wrote the row.
     *
     * Not done in the 'woocommerce_analytics_update_order_stats_data' filter: for full refunds
     * WooCommerce sets net / tax / shipping from the parent order after that filter.
     * @param int $order_id
     * @return void
     */
    public function convert_order_stats( $order_id ){
        global $wpdb;
        $rate = $this->get_order_rate( $order_id );
        if ( 1.0 === $rate ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}wc_order_stats
            SET total_sales = ROUND( total_sales / %f, 6 ),
                tax_total = ROUND( tax_total / %f, 6 ),
                shipping_total = ROUND( shipping_total / %f, 6 ),
                net_total = ROUND( net_total / %f, 6 )
            WHERE order_id = %d",
            $rate, $rate, $rate, $rate, $order_id
        ) );
    }

    /**
     * wc_order_product_lookup row. Runs right after WooCommerce wrote the row with order-currency amounts.
     * @param int $order_item_id
     * @param int $order_id
     * @return void
     */
    public function convert_product_lookup( $order_item_id, $order_id ){
        global $wpdb;
        $rate = $this->get_order_rate( $order_id );
        if ( 1.0 === $rate ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}wc_order_product_lookup
            SET product_net_revenue = ROUND( product_net_revenue / %f, 6 ),
                coupon_amount = ROUND( coupon_amount / %f, 6 ),
                tax_amount = ROUND( tax_amount / %f, 6 ),
                shipping_amount = ROUND( shipping_amount / %f, 6 ),
                shipping_tax_amount = ROUND( shipping_tax_amount / %f, 6 ),
                product_gross_revenue = ROUND( product_gross_revenue / %f, 6 )
            WHERE order_item_id = %d AND order_id = %d",
            $rate, $rate, $rate, $rate, $rate, $rate, $order_item_id, $order_id
        ) );
    }

    /**
     * wc_order_coupon_lookup row.
     * @param int $coupon_id
     * @param int $order_id
     * @return void
     */
    public function convert_coupon_lookup( $coupon_id, $order_id ){
        global $wpdb;
        $rate = $this->get_order_rate( $order_id );
        if ( 1.0 === $rate ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}wc_order_coupon_lookup SET discount_amount = ROUND( discount_amount / %f, 6 ) WHERE order_id = %d AND coupon_id = %d",
            $rate, $order_id, $coupon_id
        ) );
    }

    /**
     * wc_order_tax_lookup row.
     * @param int $tax_rate_id
     * @param int $order_id
     * @return void
     */
    public function convert_tax_lookup( $tax_rate_id, $order_id ){
        global $wpdb;
        $rate = $this->get_order_rate( $order_id );
        if ( 1.0 === $rate ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}wc_order_tax_lookup
            SET shipping_tax = ROUND( shipping_tax / %f, 6 ), order_tax = ROUND( order_tax / %f, 6 ), total_tax = ROUND( total_tax / %f, 6 )
            WHERE order_id = %d AND tax_rate_id = %d",
            $rate, $rate, $rate, $order_id, $tax_rate_id
        ) );
    }

    /**
     * WooCommerce > Status > Tools entry.
     * @param array $tools
     * @return array
     */
    public function register_tool( $tools ){
        $tools[ self::TOOL_ID ] = [
            'name'     => esc_html__( 'ShopLentor: Fix Analytics for other-currency orders', 'woolentor' ),
            'button'   => esc_html__( 'Fix Analytics', 'woolentor' ),
            'desc'     => sprintf(
                '%1$s <strong class="red">%2$s</strong> %3$s',
                esc_html__( 'Converts orders placed with the ShopLentor Currency Switcher to the store currency in WooCommerce Analytics.', 'woolentor' ),
                esc_html__( 'Note:', 'woolentor' ),
                esc_html__( 'Orders placed before the exchange rate was saved at checkout get the exchange rate set in the Currency Switcher now, so their values are an estimate.', 'woolentor' )
            ),
            'callback' => [ $this, 'run_tool' ],
        ];
        return $tools;
    }

    /**
     * Save the rate on old orders and re-sync their Analytics data.
     * @return string Message shown on the Tools screen.
     */
    public function run_tool(){
        $result = $this->sync_orders();

        $message = sprintf(
            /* translators: 1: re-synced orders, 2: orders that got an estimated rate */
            esc_html__( '%1$d orders re-synced to Analytics (%2$d of them got the current exchange rate as an estimate).', 'woolentor' ),
            $result['synced'],
            $result['estimated']
        );

        if ( ! empty( $result['skipped'] ) ) {
            $message .= ' ' . sprintf(
                /* translators: %s: currency codes */
                esc_html__( 'Skipped orders in currencies that are not in the Currency Switcher list: %s.', 'woolentor' ),
                esc_html( implode( ', ', array_keys( $result['skipped'] ) ) )
            );
        }

        return $message;
    }

    /**
     * Find orders not in the store currency, add a rate where missing and re-import them to Analytics.
     * @return array{synced:int,estimated:int,skipped:array}
     */
    public function sync_orders(){
        $store_currency = self::store_currency();
        $result = [ 'synced' => 0, 'estimated' => 0, 'skipped' => [] ];

        $currencies = array_keys( get_woocommerce_currencies() );
        $page = 1;

        do {
            $order_ids = wc_get_orders( [
                'type'     => 'shop_order',
                'status'   => array_keys( wc_get_order_statuses() ),
                'currency' => array_values( array_diff( $currencies, [ $store_currency ] ) ),
                'limit'    => 200,
                'paged'    => $page,
                'orderby'  => 'ID',
                'order'    => 'ASC',
                'return'   => 'ids',
            ] );

            foreach ( $order_ids as $order_id ) {
                $order = wc_get_order( $order_id );
                if ( ! $order instanceof \WC_Order ) {
                    continue;
                }

                $currency = strtoupper( $order->get_currency() );
                if ( $currency === $store_currency ) {
                    continue;
                }

                if ( '' === (string) $order->get_meta( self::RATE_META, true ) ) {
                    $rate = self::get_currency_rate( $currency );
                    if ( false === $rate ) {
                        $result['skipped'][ $currency ] = true;
                        continue;
                    }
                    $order->update_meta_data( self::RATE_META, wc_format_decimal( $rate ) );
                    $order->update_meta_data( self::BASE_CURRENCY_META, $store_currency );
                    $order->update_meta_data( self::RATE_SOURCE_META, 'estimated' );
                    $order->save_meta_data();
                    $result['estimated']++;
                }

                unset( $this->rates[ $order_id ] );
                foreach ( $order->get_refunds() as $refund ) {
                    unset( $this->rates[ $refund->get_id() ] );
                }

                $this->import_order( $order_id );
                $result['synced']++;
            }

            $page++;
        } while ( count( $order_ids ) === 200 );

        return $result;
    }

    /**
     * Rewrite an order's Analytics rows.
     * @param int $order_id
     * @return void
     */
    private function import_order( $order_id ){
        $scheduler = '\Automattic\WooCommerce\Internal\Admin\Schedulers\OrdersScheduler';
        if ( ! class_exists( $scheduler ) || ! method_exists( $scheduler, 'import' ) ) {
            return;
        }

        $scheduler::import( $order_id );

        // Importing the parent only rewrites the refunds' order stats rows. Their product,
        // coupon and tax rows are written when the refund itself is imported.
        $order = wc_get_order( $order_id );
        if ( $order instanceof \WC_Order ) {
            foreach ( $order->get_refunds() as $refund ) {
                $scheduler::import( $refund->get_id() );
            }
        }
    }

}
