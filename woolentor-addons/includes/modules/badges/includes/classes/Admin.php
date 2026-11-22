<?php
namespace Woolentor\Modules\Badges;
use WooLentor\Traits\Singleton;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin handlers class
 */
class Admin {
    use Singleton;
    
    /**
     * Initialize the class
     */
    private function __construct() {
        $this->includes();
        $this->init();
    }

    /**
     * Load Required files
     *
     * @return void
     */
    private function includes(){
        require_once( __DIR__. '/Admin/Fields.php' );
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init(){
        Admin\Fields::instance();
        add_action( 'admin_init', [ $this, 'register_badge_strings' ] );
    }

    /**
     * Register each badge's text with WPML / Polylang so it can be translated.
     *
     * Runs on every admin load: Polylang needs that, and WPML skips a string it already has.
     * @return void
     */
    public function register_badge_strings(){
        if ( ! function_exists( 'woolentor_register_string' ) ) {
            return;
        }
        $badges_list = woolentor_get_option( 'badges_list', 'woolentor_badges_settings', [] );
        if ( empty( $badges_list ) || ! is_array( $badges_list ) ) {
            return;
        }
        foreach ( $badges_list as $badge ) {
            if ( empty( $badge['badge_text'] ) || ! is_string( $badge['badge_text'] ) ) {
                continue;
            }
            woolentor_register_string( Product_Badges::text_string_name( $badge ), $badge['badge_text'] );
        }
    }

}