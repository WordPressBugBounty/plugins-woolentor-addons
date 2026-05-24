<?php
namespace Woolentor\Modules\FreeShippingBar\Frontend;
use WooLentor\Traits\Singleton;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Ajax_Handler {
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
        add_action( 'wp_ajax_nopriv_woolentor_fsb_cart_total', [ $this, 'get_cart_total' ] );
        add_action( 'wp_ajax_woolentor_fsb_cart_total',        [ $this, 'get_cart_total' ] );
    }

    /**
     * Return the current WooCommerce cart subtotal so the bar can refresh
     * after add-to-cart / remove actions without a full page reload.
     *
     * @return void
     */
    public function get_cart_total() {
        check_ajax_referer( 'wl-fsb-nonce', 'nonce' );

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error( [ 'message' => 'Cart not available' ] );
        }

        wp_send_json_success( [
            'cart_total' => (float) WC()->cart->get_displayed_subtotal(),
        ] );
    }
}
