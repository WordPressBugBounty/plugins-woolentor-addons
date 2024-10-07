<?php
namespace Woolentor\Modules\Badges;
use WooLentor\Traits\ModuleBase;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Product_Badges{
    use ModuleBase;

    /**
     * Class Constructor
     */
    public function __construct(){

        // Definded Constants
        $this->define_constants();

        // Include Nessary file
        $this->include();

        // initialize
        $this->init();

    }

    /**
     * Defined Required Constants
     *
     * @return void
     */
    public function define_constants(){
        define( 'Woolentor\Modules\Badges\MODULE_FILE', __FILE__ );
        define( 'Woolentor\Modules\Badges\MODULE_PATH', __DIR__ );
        define( 'Woolentor\Modules\Badges\WIDGETS_PATH', MODULE_PATH. "/includes/widgets" );
        define( 'Woolentor\Modules\Badges\BLOCKS_PATH', MODULE_PATH. "/includes/blocks" );
        define( 'Woolentor\Modules\Badges\TEMPLATE_PATH', MODULE_PATH. "/includes/templates/" );
        define( 'Woolentor\Modules\Badges\MODULE_URL', plugins_url( '', MODULE_FILE ) );
        define( 'Woolentor\Modules\Badges\MODULE_ASSETS', MODULE_URL . '/assets' );
        define( 'Woolentor\Modules\Badges\ENABLED', self::$_enabled );
    }

    /**
     * Load Required File
     *
     * @return void
     */
    public function include(){
        require_once( MODULE_PATH. "/includes/classes/Admin.php" );
        require_once( MODULE_PATH. "/includes/classes/Frontend.php" );
    }

    /**
     * Module Initilize
     *
     * @return void
     */
    public function init(){
        // For Admin
        if ( $this->is_request( 'admin' ) ) {
            Admin::instance();
        }

        if( self::$_enabled ){
            // For Frontend
            if ( $this->is_request( 'frontend' ) ) {
                Frontend::instance();
            }
        }
        
    }

}