<?php
namespace Woolentor\Modules\AdvancedCoupon;
use WooLentor\Traits\Singleton;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin handlers class
 */
class Admin {
    use Singleton;
    
    /**
     * Initialize the class
     */
    private function __construct() {
        $this->includes();
        $this->init();
    }

    /**
     * Load Required files
     *
     * @return void
     */
    private function includes(){
        require_once( __DIR__. '/Admin/Fields.php' );
        if( ENABLED ){
            require_once(__DIR__.'/Admin/Coupon_Meta_Boxes.php');
        }
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init(){
        Admin\Fields::instance();
        if( ENABLED ){
            add_action('current_screen', function ($screen) {
                if ( $screen->post_type === 'shop_coupon' ) {
                    add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
                    Admin\Coupon_Meta_Boxes::instance();
                }
            });
        }
    }

    /**
     * Admin Scripts
     * @return void
     */
    public function admin_scripts(){
        wp_enqueue_style('woolentor-advanced-coupon', MODULE_ASSETS . '/css/admin.css');
    }

}