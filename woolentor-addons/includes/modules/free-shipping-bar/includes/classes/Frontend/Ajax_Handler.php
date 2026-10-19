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
     * Return the current WooCommerce cart subtotal, whether the customer's
     * destination is eligible and the threshold for it, so the bar can refresh
     * without a full page reload.
     *
     * @return void
     */
    public function get_cart_total() {
        check_ajax_referer( 'wl-fsb-nonce', 'nonce' );

        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error( [ 'message' => 'Cart not available' ] );
        }

        if ( ! empty( $_POST['page_id'] ) ) {
            Bar_Renderer::set_page_id_override( absint( wp_unslash( $_POST['page_id'] ) ) );
        }

        $data = [
            'cart_total' => Bar_Renderer::get_cart_total(),
            'eligible'   => Shipping_Handler::is_customer_eligible() ? 'yes' : 'no',
            'threshold'  => Bar_Renderer::get_threshold(),
        ];

        /**
         * Filter the bar refresh data (Pro adds the messages of the matching bar).
         *
         * @param array $data Refresh data sent to the bar script.
         */
        wp_send_json_success( apply_filters( 'woolentor_fsb_ajax_data', $data ) );
    }
}
