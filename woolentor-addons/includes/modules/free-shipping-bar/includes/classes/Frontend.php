<?php
namespace Woolentor\Modules\FreeShippingBar;
use WooLentor\Traits\Singleton;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Frontend{
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
        Frontend\Bar_Renderer::instance();
        Frontend\Shipping_Handler::instance();
        Frontend\Ajax_Handler::instance();
    }
}
