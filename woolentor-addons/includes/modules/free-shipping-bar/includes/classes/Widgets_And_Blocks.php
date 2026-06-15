<?php
namespace Woolentor\Modules\FreeShippingBar;
use WooLentor\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Widgets and Blocks class.
 */
class Widgets_And_Blocks {
    use Singleton;

    /**
     * Widgets constructor.
     */
    public function __construct() {

        // Elementor Widget
        add_filter( 'woolentor_widget_list', [ $this, 'widget_list' ] );

        // Gutenberg Block
        add_filter( 'woolentor_block_list', [ $this, 'block_list' ] );

    }

    /**
     * Widget list.
     */
    public function widget_list( $widget_list = [] ) {

        $widget_list['common']['wl_free_shipping_bar'] = [
            'title'    => esc_html__( 'Free Shipping Bar', 'woolentor' ),
            'location' => WIDGETS_PATH,
        ];

        return $widget_list;
    }

    /**
     * Block list.
     */
    public function block_list( $block_list = [] ) {

        $block_list['free_shipping_bar'] = [
            'label'              => __( 'Free Shipping Bar', 'woolentor' ),
            'name'               => 'woolentor/free-shipping-bar',
            'server_side_render' => true,
            'type'               => 'common',
            'active'             => true,
            'location'           => BLOCKS_PATH,
            'enqueue_assets' => function(){
                wp_enqueue_style('woolentor-free-shipping-bar', MODULE_URL . '/assets/css/free-shipping-bar.css', [], WOOLENTOR_VERSION);
                wp_enqueue_script('woolentor-free-shipping-bar', MODULE_URL . '/assets/js/free-shipping-bar.js', [], WOOLENTOR_VERSION, true);
                if(woolentor_is_pro() && defined('\WoolentorPro\Modules\FreeShippingBar\MODULE_URL')){
                    wp_enqueue_style('woolentor-fsb-pro', \WoolentorPro\Modules\FreeShippingBar\MODULE_URL . '/assets/css/free-shipping-bar-pro.css', [], WOOLENTOR_VERSION);
                    wp_enqueue_script('woolentor-fsb-pro', \WoolentorPro\Modules\FreeShippingBar\MODULE_URL . '/assets/js/free-shipping-bar-pro.js', [], WOOLENTOR_VERSION, true);
                }
            }
        ];

        return $block_list;
    }

}
