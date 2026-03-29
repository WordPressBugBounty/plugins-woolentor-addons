<?php

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

class Woolentor_Admin_Init{
    
    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Woolentor_Admin_Init]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct(){
        $this->include();
    }

    /**
     * [include] Load Necessary file
     * @return [void]
     */
    public function include(){
        require_once( WOOLENTOR_ADDONS_PL_PATH .'includes/api.php');
        require_once('include/admin_field-manager.php');

        // Elementor Template Library integration
        if ( did_action( 'elementor/loaded' ) ) {
            require_once('include/class.library-source.php');
            require_once('include/class.elementor-template-library.php');
            \Woolentor\ElementorTemplate\Elementor_Library_Manage::instance();
        }
    }


}

Woolentor_Admin_Init::instance();