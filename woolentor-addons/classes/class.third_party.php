<?php

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit();

/**
* Third party
*/
class WooLentorThirdParty{

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Base]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    function __construct(){
        $this->woocommerce_german_market();
        $this->theme_compatibility();
        $this->woocommerce_paypal_payments();
        $this->woocommerce_cart_checkout_block();
    }

    /**
     * WooCommerce German Market
     *
     * @return void
     */
    public function woocommerce_german_market(){
        if( class_exists('Woocommerce_German_Market') ){
            add_action( 'woolentor_universal_after_price', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ) );
            add_action( 'woolentor_addon_after_price', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ) );
        }
    }

    /**
     * WooCommerce PayPal Payments
     *
     * By default the plugin renders its smart buttons on the
     * "woocommerce_single_product_summary" hook, which never runs when a WooLentor
     * single product template takes over the product page. The buttons are moved to
     * "woocommerce_after_add_to_cart_form" instead, a hook the WL: Add To cart widget
     * fires through woocommerce_template_single_add_to_cart(). PayPal registers its
     * renderer on "wp" (priority 10), so the filter has to be added before that.
     *
     * @return void
     */
    public function woocommerce_paypal_payments(){
        add_action( 'wp', [ $this, 'woocommerce_paypal_payments_button_position' ], 1 );
    }

    /**
     * Move the PayPal Payments smart buttons inside the add to cart form
     * when a WooLentor single product template is in use.
     *
     * @return void
     */
    public function woocommerce_paypal_payments_button_position(){
        if( ! defined('PPCP_PAYPAL_BN_CODE') || ! function_exists('is_product') || ! is_product() || ! class_exists('Woolentor_Manage_WC_Template') ){
            return;
        }

        if( false === Woolentor_Manage_WC_Template::has_template( 'singleproductpage', '_selectproduct_layout' ) ){
            return;
        }

        add_filter( 'woocommerce_paypal_payments_single_product_renderer_hook', function(){
            return 'woocommerce_after_add_to_cart_form';
        } );
    }

    /**
     * WooCommerce Cart and Checkout Block
     *
     * WooCommerce stores the "woocommerce/cart" and "woocommerce/checkout" blocks in
     * the cart and checkout page content, so has_block() keeps reporting a block cart
     * or checkout even when a WooLentor template takes the page over and renders the
     * classic markup instead. Payment gateways use that check to pick between their
     * block and their classic integration, so they load the block one and their
     * classic output stays empty (Mollie credit card components, PayPal card fields
     * and express buttons and so on). The block markup is renamed on those requests
     * so the checks report what is really rendered. Gateways read it from
     * "wp_enqueue_scripts" onwards, hence "wp".
     *
     * @return void
     */
    public function woocommerce_cart_checkout_block(){
        add_action( 'wp', [ $this, 'woocommerce_cart_checkout_block_markup' ], 1 );
    }

    /**
     * Rename the cart or checkout block markup when a WooLentor template is in use.
     *
     * @return void
     */
    public function woocommerce_cart_checkout_block_markup(){
        if( is_admin() || ! function_exists('is_checkout') || ! class_exists('Woolentor_Manage_WC_Template') ){
            return;
        }

        $block = '';

        if( is_checkout() && ! is_checkout_pay_page() && ! is_wc_endpoint_url('order-received') ){

            if( false !== Woolentor_Manage_WC_Template::has_template( 'productcheckoutpage' ) ){
                $block = 'checkout';
            }

        } elseif( is_cart() ) {

            $is_empty = ! WC()->cart || WC()->cart->is_empty();

            if( ( ! $is_empty && false !== Woolentor_Manage_WC_Template::has_template( 'productcartpage' ) )
                || ( $is_empty && false !== Woolentor_Manage_WC_Template::has_template( 'productemptycartpage' ) ) ){
                $block = 'cart';
            }

        }

        if( empty( $block ) ){
            return;
        }

        global $post;
        if( ! ( $post instanceof WP_Post ) || strpos( $post->post_content, '<!-- wp:woocommerce/' . $block ) === false ){
            return;
        }

        $post->post_content = str_replace(
            [ '<!-- wp:woocommerce/' . $block, '<!-- /wp:woocommerce/' . $block ],
            [ '<!-- wp:woolentor/' . $block, '<!-- /wp:woolentor/' . $block ],
            $post->post_content
        );
    }

    /**
     * Theme Compatibility
     * @return void
     */
    public function theme_compatibility(){
        add_action( 'wp', [ $this, 'woocommerce_theme_compatibility' ], 99 );
    }

    /**
     * WooCommerce Theme Compatibility
     * @return void
     */
    public function woocommerce_theme_compatibility(){
        // Avada Theme
        $this->avada_theme_compatibility();
    }

    /**
     * Avada Theme Compatibility
     * @return void
     */
    public function avada_theme_compatibility(){
        if( !function_exists('woolentor_get_theme_byname') || !woolentor_get_theme_byname('Avada') ){
            return;
        }
        $shopify_is_enable = woolentor_get_option( 'enable','woolentor_shopify_checkout_settings', 'off' ) == 'on';
        if( $shopify_is_enable ){
            global $avada_woocommerce;
            if( is_object( $avada_woocommerce ) ){
                remove_action( 'woocommerce_before_checkout_form', [$avada_woocommerce, 'avada_top_user_container' ], 1 );
                remove_action( 'woocommerce_before_checkout_form', [$avada_woocommerce, 'checkout_coupon_form' ], 10 );
                remove_action( 'woocommerce_before_checkout_form', [$avada_woocommerce, 'before_checkout_form' ], 10 );
                remove_action( 'woocommerce_checkout_after_order_review', [$avada_woocommerce, 'checkout_after_order_review' ], 20 );
                remove_action( 'woocommerce_after_checkout_form', [ $avada_woocommerce, 'after_checkout_form' ] );
            }
        }
    }

    
}

WooLentorThirdParty::instance();