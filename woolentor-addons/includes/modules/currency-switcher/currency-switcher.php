<?php
namespace Woolentor\Modules\CurrencySwitcher;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Currency_Switcher{

    /**
     * Enabled.
     */
    private static $_enabled = true;

    private static $_instance = null;

    /**
     * Get Instance
     */
    public static function instance( $enabled = true ){
        self::$_enabled = $enabled;
        if( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

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
        define( 'Woolentor\Modules\CurrencySwitcher\MODULE_FILE', __FILE__ );
        define( 'Woolentor\Modules\CurrencySwitcher\MODULE_PATH', __DIR__ );
        define( 'Woolentor\Modules\CurrencySwitcher\WIDGETS_PATH', MODULE_PATH. "/includes/widgets" );
        define( 'Woolentor\Modules\CurrencySwitcher\BLOCKS_PATH', MODULE_PATH. "/includes/blocks" );
        define( 'Woolentor\Modules\CurrencySwitcher\MODULE_URL', plugins_url( '', MODULE_FILE ) );
        define( 'Woolentor\Modules\CurrencySwitcher\MODULE_ASSETS', MODULE_URL . '/assets' );
        define( 'Woolentor\Modules\CurrencySwitcher\ENABLED', self::$_enabled );
    }

    /**
     * Load Required File
     *
     * @return void
     */
    public function include(){
        require_once( MODULE_PATH. "/includes/Functions.php" );
        require_once( MODULE_PATH. "/includes/classes/Admin.php" );
        require_once( MODULE_PATH. "/includes/classes/Frontend.php" );
        require_once( MODULE_PATH. "/includes/classes/Widgets_And_Blocks.php" );
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) || ( ! empty( $_REQUEST['action'] ) && 'elementor' === $_REQUEST['action'] ) ) && ! defined( 'DOING_CRON' );
        }
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
        // For Frontend
        if ( self::$_enabled && $this->is_request( 'frontend' ) ) {
            Frontend::instance();
        }

        // Register Widget and blocks
        if( self::$_enabled ){
            Widgets_And_Blocks::instance();
        }

    }

}

/**
 * Returns the instance.
 */
function woolentor_currency_switcher( $enabled = true ) {
    return Currency_Switcher::instance( $enabled );
}