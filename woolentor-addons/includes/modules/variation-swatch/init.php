<?php
namespace Woolentor\Modules;
use const Woolentor\Modules\Swatchly\MODULE_PATH;
use const Woolentor\Modules\Swatchly\MODULE_FILE;
use const Woolentor\Modules\Swatchly\MODULE_URL;
use WooLentor\Traits\Singleton;


// If this file is accessed directly, exit
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main class
 *
 * @since 1.0.0
 */
final class Swatchly {
    use Singleton;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->run();
    }

    /**
     * Define the required constants
     *
     * @since 1.0.0
     */
    private function define_constants() {
        define( 'Woolentor\Modules\Swatchly\MODULE_FILE', __FILE__ );
        define( 'Woolentor\Modules\Swatchly\MODULE_PATH', __DIR__ );
        define( 'Woolentor\Modules\Swatchly\MODULE_URL', plugins_url( '', MODULE_FILE ) );
        define( 'Woolentor\Modules\Swatchly\MODULE_ASSETS', MODULE_URL . '/assets' );
    }

    /**
     * Include required core files
     *
     * @since 1.0.0
     */
    public function includes() {
        if ( !function_exists( 'get_current_screen' ) ){ 
            require_once ABSPATH . '/wp-admin/includes/screen.php'; 
        } 

        /**
         * Load files.
         */
        require_once MODULE_PATH .'/includes/functions.php';
        require_once MODULE_PATH .'/includes/Helper.php';
        require_once MODULE_PATH .'/includes/ajax-actions.php';

        require_once MODULE_PATH .'/includes/Admin/Woo_Config.php';
        require_once MODULE_PATH .'/includes/Admin/Attribute_Taxonomy_Metabox.php';
        require_once MODULE_PATH .'/includes/Admin/Product_Metabox.php';
        require_once MODULE_PATH .'/includes/Admin.php';

        require_once MODULE_PATH .'/includes/Frontend/Woo_Config.php';
        require_once MODULE_PATH .'/includes/Frontend.php';
    }

    /**
     * First initialization of the module
     *
     * @since 1.0.0
     */
    private function run() {

        new Swatchly\Frontend();
        new Swatchly\Frontend\Woo_Config();
        if( is_admin() || defined( 'DOING_AJAX' ) ){
            new Swatchly\Admin();
        }

    }
}

/**
 * Returns the main instance of Swatchly Module
 *
 * @since 1.0.0
 */

function swatchly_module() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
    return Swatchly::instance();
}

// Kick-off the module
swatchly_module();