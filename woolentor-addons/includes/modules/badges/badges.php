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

        // If is Active Pro.
        if( is_plugin_active('woolentor-addons-pro/woolentor_addons_pro.php') && defined( "WOOLENTOR_ADDONS_PL_PATH_PRO" ) ){
            if( file_exists(WOOLENTOR_ADDONS_PL_PATH_PRO .'includes/modules/badges/badges.php')){
                require_once( WOOLENTOR_ADDONS_PL_PATH_PRO .'includes/modules/badges/badges.php' );
            }
        }
    }

    /**
     * Module Initilize
     *
     * @return void
     */
    public function init(){
        // For Admin
        if ( $this->is_request( 'admin' ) || $this->is_request( 'rest' ) ) {
            Admin::instance();
        }

        if( self::$_enabled ){
            // For Frontend
            if ( $this->is_request( 'frontend' ) ) {
                Frontend::instance();
            }
        }
        
    }

    /**
     * String name a badge's text is registered and translated under (WPML / Polylang).
     *
     * Built from the badge title, so two badges with the same title share one translation.
     * @param array $badge
     * @return string
     */
    public static function text_string_name( $badge ){
        $title = !empty( $badge['badge_title'] ) ? $badge['badge_title'] : 'Badge One'; // Not translated: the name must be the same in every language.
        return 'woolentor_badge_text_' . str_replace( ' ', '', trim( preg_replace( '/\(.+\)/', '', $title ) ) );
    }

}