<?php
/**
 * Helper functions for the Shopify-like Checkout module.
 *
 * @package Woolentor\Modules\ShopifyLikeCheckout
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! function_exists( 'woolentor_shopify_like_checkout' ) ) {
    /**
     * Get the Shopify-like Checkout manager instance.
     *
     * Use this helper inside templates and other code instead of referencing
     * the class directly. If the underlying class name or namespace ever
     * changes, only this function needs to be updated.
     *
     * @return \Woolentor\Modules\ShopifyLikeCheckout\Frontend\Manage_Checkout
     */
    function woolentor_shopify_like_checkout() {
        return \Woolentor\Modules\ShopifyLikeCheckout\Frontend\Manage_Checkout::instance();
    }
}
