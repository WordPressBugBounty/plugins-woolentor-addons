<?php
namespace Woolentor\Modules\CurrencySwitcher;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Widgets class.
 */
class Widgets_And_Blocks {

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Admin]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
     * Widgets constructor.
     */
    public function __construct() {

        // Elementor Widget
        add_filter( 'woolentor_widget_list', [ $this, 'widget_list' ] );

        // Guttenberg Block
        add_filter('woolentor_block_list', [ $this, 'block_list' ] );

    }

    /**
     * Widget list.
     */
    public function widget_list( $widget_list = [] ) {
        
        $widget_list['common']['wl_currency_switcher'] = [
            'title'    => esc_html__('Currency Switcher','woolentor'),
            'location' => WIDGETS_PATH,
        ];

        return $widget_list;
    }

    /**
     * Block list.
     */
    public function block_list( $block_list = [] ){

        $block_list['currency_switcher'] = [
            'label'  => __('Currency Switcher','woolentor'),
            'name'   => 'woolentor/currency-switcher',
            'server_side_render' => true,
            'type'   => 'common',
            'active' => true,
            'location' => BLOCKS_PATH,
        ];

        return $block_list;
    }

}