<?php
namespace Woolentor\Modules\ShopifyLikeCheckout;
use WooLentor\Traits\Singleton;

/**
 * Frontend handlers class
 */
class Frontend {
    use Singleton;
    
    /**
     * Initialize the class
     */
    private function __construct() {
        $this->includes();
        $this->init();
    }

    /**
     * Include necessary files
     */
    public function includes(){
        require_once( __DIR__. '/Frontend/Manage_Checkout.php' );
    }

    /**
     * Initialize the class
     */
    public function init(){
        Frontend\Manage_Checkout::instance();
    }

}