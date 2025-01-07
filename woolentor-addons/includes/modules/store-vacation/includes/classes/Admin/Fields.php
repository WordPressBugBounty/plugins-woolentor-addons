<?php
namespace Woolentor\Modules\StoreVacation\Admin;
use WooLentor\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Fields {
    use Singleton;

    public function __construct(){
        add_filter( 'woolentor_admin_fields', [ $this, 'admin_fields' ], 99, 1 );
    }

    /**
     * Admin Field Register
     * @param mixed $fields
     * @return mixed
     */
    public function admin_fields( $fields ){
        
        if( woolentor_is_pro() && method_exists( '\WoolentorPro\Modules\StoreVacation\Admin\Fields', 'sitting_fields') ){
            array_splice( $fields['woolentor_others_tabs']['modules'], 18, 0, \WoolentorPro\Modules\StoreVacation\Admin\Fields::instance()->sitting_fields() );
        }else{
            array_splice( $fields['woolentor_others_tabs']['modules'], 18, 0, $this->sitting_fields() );
        }

        if(\Woolentor\Modules\StoreVacation\ENABLED){

            $fields['woolentor_elements_tabs'][] = [
                'name'    => 'wl_vacation_notice',
                'label'   => esc_html__( 'Vacation Notice', 'woolentor' ),
                'type'    => 'element',
                'default' => 'on'
            ];

        }

        return $fields;
    }

    public function sitting_fields(){
        $fields = [
            [
                'name'     => 'store_vacation',
                'label'    => esc_html__( 'Store Vacation', 'woolentor' ),
                'type'     => 'module',
                'default'  => 'off',
                'section'  => 'woolentor_store_vacation_settings',
                'option_id'=> 'enable',
                'require_settings' => true,
                'documentation' => esc_url('https://woolentor.com/doc/setup-the-store-vacation-module-in-woocommerce/'),
                'setting_fields' => array(
                    
                    array(
                        'name'  => 'enable',
                        'label' => esc_html__( 'Enable / Disable', 'woolentor' ),
                        'desc'  => esc_html__( 'Enable/Disable store vacation mode', 'woolentor' ),
                        'type'  => 'checkbox',
                        'default' => 'off',
                        'class' => 'woolentor-action-field-left'
                    ),
    
                    array(
                        'name'    => 'vacation_start_date',
                        'label'   => esc_html__( 'Start Date', 'woolentor' ),
                        'type'    => 'date',
                        'desc'    => esc_html__( 'Select vacation start date', 'woolentor' ),
                        'class'   => 'woolentor-action-field-left'
                    ),
    
                    array(
                        'name'    => 'vacation_end_date',
                        'label'   => esc_html__( 'End Date', 'woolentor' ),
                        'type'    => 'date',
                        'desc'    => esc_html__( 'Select vacation end date', 'woolentor' ),
                        'class'   => 'woolentor-action-field-left'
                    ),
    
                    array(
                        'name'      => 'notice_heading',
                        'headding'  => esc_html__( 'Notice Settings', 'woolentor' ),
                        'type'      => 'title'
                    ),
        
                    array(
                        'name'    => 'notice_position',
                        'label'   => esc_html__( 'Notice Position', 'woolentor' ),
                        'type'    => 'select',
                        'default' => 'woocommerce_before_cart',
                        'options' => array(
                            'woocommerce_before_shop_loop'      => esc_html__('Before Shop Loop', 'woolentor'),
                            'woocommerce_before_single_product' => esc_html__('Before Single Product', 'woolentor'),
                            'woocommerce_before_cart'           => esc_html__('Before Cart', 'woolentor'),
                            'shop_and_single_product'           => esc_html__('Shop & Single Product', 'woolentor'),
                            'use_shortcode'                     => esc_html__( 'Use Shortcode / Widget', 'woolentor' ),
                        ),
                        'class'   => 'woolentor-action-field-left'
                    ),

                    array(
                        'name'    => 'vacation_use_shortcode_message',
                        'headding'=> wp_kses_post('Use the shortcode <code>[woolentor_vacation_notice]</code> or the widget to display the vacation notice wherever you need it.'),
                        'type'    => 'title',
                        'condition' => array( 'notice_position', '==', 'use_shortcode' ),
                        'class'     => 'woolentor_option_field_notice'
                    ),

                    array(
                        'name'    => 'vacation_message',
                        'label'   => esc_html__( 'Vacation Message', 'woolentor' ),
                        'type'    => 'textarea',
                        'desc'    => esc_html__( 'Enter message to display during vacation. You can use these placeholders: {start_date}, {end_date}, {days_remaining}', 'woolentor' ),
                        'default' => esc_html__( '🏖️ Dear valued customers, our store is currently on vacation from {start_date} to {end_date}. During this time, new orders will be temporarily suspended. We will resume normal operations on {end_date}. Thank you for your understanding!', 'woolentor' ),
                        'class'   => 'woolentor-action-field-left'
                    ),
        
                    array(
                        'name'    => 'notice_color',
                        'label'   => esc_html__( 'Notice Text Color', 'woolentor' ),
                        'type'    => 'color',
                        'default' => '#000000',
                        'class'   => 'woolentor-action-field-left'
                    ),
        
                    array(
                        'name'    => 'notice_bg_color',
                        'label'   => esc_html__( 'Notice Background Color', 'woolentor' ),
                        'type'    => 'color',
                        'default' => '#ffffff',
                        'class'   => 'woolentor-action-field-left'
                    ),

                    // Product Settings
                    array(
                        'name'      => 'product_heading',
                        'headding'  => esc_html__( 'Product Settings', 'woolentor' ),
                        'type'      => 'title'
                    ),

                    array(
                        'name'    => 'hide_add_to_cart',
                        'label'   => esc_html__( 'Turn Off Purchases', 'woolentor' ),
                        'type'    => 'checkbox',
                        'desc'    => esc_html__( 'Turn off purchases during vacation', 'woolentor' ),
                        'default' => 'off',
                        'class'   => 'woolentor-action-field-left'
                    ),

                    array(
                        'name'    => 'product_availability_text',
                        'label'   => esc_html__( 'Product Availability Text', 'woolentor' ),
                        'type'    => 'text',
                        'desc'    => esc_html__( 'Text to show instead of Add to Cart button', 'woolentor' ),
                        'default' => esc_html__( 'Available after vacation', 'woolentor' ),
                        'class'   => 'woolentor-action-field-left',
                        'condition' => array( 'hide_add_to_cart', '==', 'true' ),
                    ),
    
                )
            ]

        ];

        return $fields;
    }


}