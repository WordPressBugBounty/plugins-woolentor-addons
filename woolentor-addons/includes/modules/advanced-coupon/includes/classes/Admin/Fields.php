<?php
namespace Woolentor\Modules\AdvancedCoupon\Admin;
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
        array_splice( $fields['woolentor_others_tabs']['modules'], 13, 0, $this->sitting_fields() );
        return $fields;
    }

    /**
     * Settings Fields Fields;
     */
    public function sitting_fields(){
        
        $fields = [
            [
                'name'   => 'advanced_coupon_settings',
                'label'  => esc_html__( 'Advanced Coupon', 'woolentor' ),
                'type'   => 'module',
                'default'=> 'off',
                'section'  => 'woolentor_advanced_coupon_settings',
                'option_id' => 'enable',
                'documentation' => esc_url('https://woolentor.com/doc/'),
                'require_settings'  => true,
                'setting_fields' => [
                    [
                        'name'    => 'enable',
                        'label'   => esc_html__( 'Enable / Disable', 'woolentor' ),
                        'desc'    => esc_html__( 'Enable / disable this module.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'default' => 'off',
                        'class'   => 'woolentor-action-field-left'
                    ]

                ]
            ]
        ];

        return $fields;

    }

}