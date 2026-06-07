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
        $this->init_includes();
        $this->init_hooks();
    }

    private function init_includes(){
        require_once __DIR__ . '/Frontend/Bar_Renderer.php';
        require_once __DIR__ . '/Frontend/Shipping_Handler.php';
        require_once __DIR__ . '/Frontend/Ajax_Handler.php';
        require_once __DIR__ . '/Frontend/Shortcode.php';
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
        Frontend\Shortcode::instance();
    }
}
