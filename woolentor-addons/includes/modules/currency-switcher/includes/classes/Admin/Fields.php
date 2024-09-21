<?php
namespace Woolentor\Modules\CurrencySwitcher\Admin;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Fields {

    private static $_instance = null;

    /**
     * Get Instance
     */
    public static function instance(){
        if( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        add_filter( 'woolentor_admin_fields', [ $this, 'admin_fields' ], 99, 1 );
    }

    public function admin_fields( $fields ){
        if( woolentor_is_pro() && method_exists( '\WoolentorPro\Modules\CurrencySwitcher\Currency_Switcher', 'Fields') ){
            array_splice( $fields['woolentor_others_tabs']['modules'], 11, 0, \WoolentorPro\Modules\CurrencySwitcher\Currency_Switcher::instance()->Fields() );
        }else{
            array_splice( $fields['woolentor_others_tabs']['modules'], 11, 0, $this->currency_sitting_fields() );
        }

        $fields['woolentor_elements_tabs'][] = [
            'name'    => 'wl_currency_switcher',
            'label'   => esc_html__( 'Currency Switcher', 'woolentor' ),
            'type'    => 'element',
            'default' => 'on'
        ];

        // Block
        $fields['woolentor_gutenberg_tabs']['blocks'][] = [
            'name'  => 'currency_switcher',
            'label' => esc_html__( 'Currency Switcher', 'woolentor' ),
            'type'  => 'element',
            'default' => 'on',
        ];

        return $fields;
    }

    /**
     * Currency Fields;
     */
    public function currency_sitting_fields(){
        $wc_currency = get_woocommerce_currency();
        $fields = array(
            array(
                'name'     => 'currency_switcher',
                'label'    => esc_html__( 'Currency Switcher', 'woolentor' ),
                'type'     => 'module',
                'default'  => 'off',
                'section'  => 'woolentor_currency_switcher',
                'option_id'=> 'enable',
                'require_settings'  => true,
                'documentation' => esc_url('https://woolentor.com/doc/currency-switcher-for-woocommerce/'),
                'setting_fields' => array(
                    
                    array(
                        'name'  => 'enable',
                        'label' => esc_html__( 'Enable / Disable', 'woolentor-pro' ),
                        'desc'  => esc_html__( 'You can enable / disable currency switcher from here.', 'woolentor-pro' ),
                        'type'  => 'checkbox',
                        'default' => 'off',
                        'class'   =>'enable woolentor-action-field-left',
                    ),

                    array(
                        'name'        => 'woolentor_currency_list',
                        'label'       => esc_html__( 'Currency Switcher', 'woolentor-pro' ),
                        'type'        => 'repeater',
                        'title_field' => 'currency',
                        'condition'   => [ 'enable', '==', '1' ],
                        'add_limit'   => 2,
                        'custom_button' => [
                            'text' => esc_html__( 'Update Exchange Rates', 'woolentor-pro' ),
                            'option_section' => 'woolentor_currency_switcher',
                            'option_id' => 'default_currency',
                            'option_selector' => '.wlcs-default-selection .woolentor-admin-select select',
                            'callback' => 'woolentor_currency_exchange_rate'
                        ],
                        'fields'  => [

                            array(
                                'name'    => 'currency',
                                'label'   => esc_html__( 'Currency', 'woolentor-pro' ),
                                'type'    => 'select',
                                'default' => $wc_currency,
                                'options' => woolentor_wc_currency_list(),
                                'class'   => 'woolentor-action-field-left wlcs-currency-selection wlcs-currency-selection-field',
                            ),

                            array(
                                'name'        => 'currency_decimal',
                                'label'       => esc_html__( 'Decimal', 'woolentor-pro' ),
                                'type'        => 'number',
                                'default'     => 2,
                                'class'       => 'woolentor-action-field-left',
                            ),

                            array(
                                'name'    => 'currency_position',
                                'label'   => esc_html__( 'Currency Symbol Position', 'woolentor-pro' ),
                                'type'    => 'select',
                                'class'   => 'woolentor-action-field-left',
                                'default' => get_option( 'woocommerce_currency_pos' ),
                                'options' => array(
                                    'left'  => esc_html__('Left','woolentor-pro'),
                                    'right' => esc_html__('Right','woolentor-pro'),
                                    'left_space' => esc_html__('Left Space','woolentor-pro'),
                                    'right_space' => esc_html__('Right Space','woolentor-pro'),
                                ),
                            ),

                            array(
                                'name'        => 'currency_excrate',
                                'label'       => esc_html__( 'Exchange Rate', 'woolentor-pro' ),
                                'type'        => 'number',
                                'default'     => 1,
                                'class'       => 'woolentor-action-field-left wlcs-currency-dynamic-exchange-rate',
                            ),

                            array(
                                'name'        => 'currency_excfee',
                                'label'       => esc_html__( 'Exchange Fee', 'woolentor-pro' ),
                                'type'        => 'number',
                                'default'     => 0,
                                'class'       => 'woolentor-action-field-left',
                            ),

                            array(
                                'name'    => 'disallowed_payment_methodp',
                                'label'   => esc_html__( 'Payment Method Disables', 'woolentor-pro' ),
                                'type'    => 'select',
                                'options' => array(
                                    'select' => esc_html__('This is a pro features','woolentor'),
                                ),
                                'class' => 'woolentor-action-field-left',
                                'is_pro'  => true,
                            ),

                            array(
                                'name'     => 'custom_currency_symbolp',
                                'label'   => esc_html__( 'Custom Currency Symbol', 'woolentor' ),
                                'type'    => 'text',
                                'class'   => 'woolentor-action-field-left',
                                'default' => 'This is a pro features',
                                'is_pro'  => true,
                            ),

                        ],

                        'default' => array (
                            [
                                'currency'         => $wc_currency,
                                'currency_decimal' => 2,
                                'currency_position'=> get_option( 'woocommerce_currency_pos' ),
                                'currency_excrate' => 1,
                                'currency_excfee'  => 0
                            ],
                        ),

                    ),

                    array(
                        'name'    => 'default_currency',
                        'label'   => esc_html__( 'Default Currency', 'woolentor' ),
                        'type'    => 'select',
                        'options' => woolentor_added_currency_list(),
                        'default' => $wc_currency,
                        'class'   => 'woolentor-action-field-left wlcs-default-selection',
                        'condition'=> [ 'enable', '==', '1' ],
                    ),

                )
            )
        );

        return $fields;

    }

}