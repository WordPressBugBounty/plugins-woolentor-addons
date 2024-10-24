<?php
namespace WooLentor\Traits;

trait ModuleBase {
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
     * Check WooLentor Pro is active
     * @return bool
     */
    private function is_pro(){
        if( is_plugin_active('woolentor-addons-pro/woolentor_addons_pro.php') && defined( "WOOLENTOR_ADDONS_PL_PATH_PRO" ) ){
            return true;
        }else{
            return false;
        }
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


}
