<?php
namespace Woolentor\Modules\FreeShippingBar;
use WooLentor\Traits\Singleton;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Admin {
    use Singleton;

    /**
     * Constructor
     */
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Load required files
     *
     * @return void
     */
    private function includes() {
        require_once __DIR__ . '/Admin/Fields.php';
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init_hooks() {
        Admin\Fields::instance();
    }
}
