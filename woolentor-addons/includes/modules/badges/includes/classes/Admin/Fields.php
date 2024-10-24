<?php
namespace Woolentor\Modules\Badges\Admin;
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
        if( woolentor_is_pro() && method_exists( '\WoolentorPro\Modules\Badges\Product_Badges', 'Fields') ){
            array_splice( $fields['woolentor_others_tabs']['modules'], 25, 0, \WoolentorPro\Modules\Badges\Product_Badges::instance()->Fields() );
        }else{
            array_splice( $fields['woolentor_others_tabs']['modules'], 13, 0, $this->sitting_fields() );
        }

        return $fields;
    }

    /**
     * Settings Fields;
     */
    public function sitting_fields(){
        $fields = [
            [
                'name'   => 'badges_settings',
                'label'  => esc_html__( 'Product Badges', 'woolentor' ),
                'type'   => 'module',
                'default'=> 'off',
                'section'  => 'woolentor_badges_settings',
                'option_id' => 'enable',
                'documentation' => esc_url('https://woolentor.com/doc/product-badges-module/'),
                'require_settings'  => true,
                'setting_fields' => [
                    [
                        'name'    => 'enable',
                        'label'   => esc_html__( 'Enable / Disable', 'woolentor' ),
                        'desc'    => esc_html__( 'Enable / disable this module.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'default' => 'off',
                        'class'   => 'woolentor-action-field-left'
                    ],

                    [
                        'name'        => 'badges_list',
                        'label'       => esc_html__( 'Badge List', 'woolentor' ),
                        'type'        => 'repeater',
                        'title_field' => 'badge_title',
                        'condition'   => [ 'enable', '==', 'true' ],
                        'add_limit'   => 2,
                        'options' => [
                            'button_label' => esc_html__( 'Add New Badge', 'woolentor' ),  
                        ],
                        'fields'  => [
                            [
                                'name'        => 'badge_title',
                                'label'       => esc_html__( 'Badge Title', 'woolentor' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left'
                            ],
                            [
                                'name'        => 'badge_type',
                                'label'       => esc_html__( 'Badge Type', 'woolentor' ),
                                'type'        => 'select',
                                'default'     => 'text',
                                'options' => [
                                    'text' => esc_html__( 'Text', 'woolentor-pro' ),
                                    'image'=> esc_html__( 'Image', 'woolentor-pro' ),
                                ],
                                'class'       => 'woolentor-action-field-left'
                            ],
                            [
                                'name'        => 'badge_text',
                                'label'       => esc_html__( 'Badge Text', 'woolentor' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'  => 'badge_text_color',
                                'label' => esc_html__( 'Text Color', 'woolentor' ),
                                'desc'  => esc_html__( 'Badge text color.', 'woolentor' ),
                                'type'  => 'color',
                                'class' => 'woolentor-action-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'  => 'badge_bg_color',
                                'label' => esc_html__( 'Background Color', 'woolentor' ),
                                'desc'  => esc_html__( 'Badge background color.', 'woolentor' ),
                                'type'  => 'color',
                                'class' => 'woolentor-action-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'              => 'badge_font_size',
                                'label'             => esc_html__( 'Text Font Size (PX)', 'woolentor' ),
                                'desc'              => esc_html__( 'Set the font size for badge text.', 'woolentor' ),
                                'min'               => 1,
                                'max'               => 1000,
                                'default'           => '15',
                                'step'              => '1',
                                'type'              => 'number',
                                'sanitize_callback' => 'number',
                                'condition' => [ 'badge_type', '==', 'text' ],
                                'class'       => 'woolentor-action-field-left',
                            ],
                            [
                                'name'    => 'badge_padding',
                                'label'   => esc_html__( 'Badge padding', 'woolentor' ),
                                'desc'    => esc_html__( 'Badge area padding.', 'woolentor' ),
                                'type'    => 'dimensions',
                                'options' => [
                                    'top'   => esc_html__( 'Top', 'woolentor' ),
                                    'right' => esc_html__( 'Right', 'woolentor' ),
                                    'bottom'=> esc_html__( 'Bottom', 'woolentor' ),
                                    'left'  => esc_html__( 'Left', 'woolentor' ),
                                    'unit'  => esc_html__( 'Unit', 'woolentor' ),
                                ],
                                'class' => 'woolentor-action-field-left woolentor-dimention-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'    => 'badge_border_radius',
                                'label'   => esc_html__( 'Badge border radius', 'woolentor' ),
                                'desc'    => esc_html__( 'Badge area button border radius.', 'woolentor' ),
                                'type'    => 'dimensions',
                                'options' => [
                                    'top'   => esc_html__( 'Top', 'woolentor' ),
                                    'right' => esc_html__( 'Right', 'woolentor' ),
                                    'bottom'=> esc_html__( 'Bottom', 'woolentor' ),
                                    'left'  => esc_html__( 'Left', 'woolentor' ),
                                    'unit'  => esc_html__( 'Unit', 'woolentor' ),
                                ],
                                'class' => 'woolentor-action-field-left woolentor-dimention-field-left',
                                'condition' => [ 'badge_type', '==', 'text' ],
                            ],
                            [
                                'name'    => 'badge_image',
                                'label'   => esc_html__( 'Badge Image', 'woolentor-pro' ),
                                'desc'    => esc_html__( 'Upload your custom badge from here.', 'woolentor' ),
                                'type'    => 'image_upload',
                                'options' => [
                                    'button_label'        => esc_html__( 'Upload', 'woolentor' ),   
                                    'button_remove_label' => esc_html__( 'Remove', 'woolentor' ),   
                                ],
                                'class' => 'woolentor-action-field-left',
                                'condition'   => [ 'badge_type', '==', 'image' ],
                            ],

                            [
                                'name'      => 'badge_setting_heading',
                                'headding'  => esc_html__( 'Badge Settings', 'woolentor' ),
                                'type'      => 'title'
                            ],

                            [
                                'name'    => 'badge_position',
                                'label'   => esc_html__( 'Badge Position', 'woolentor' ),
                                'desc'    => esc_html__( 'Choose a badge position from here.', 'woolentor' ),
                                'type'    => 'select',
                                'default' => 'top_left',
                                'options' => [
                                    'top_left'   => esc_html__( 'Top Left', 'woolentor' ),
                                    'top_right'  => esc_html__( 'Top Right', 'woolentor' ),
                                    'bottom_left'=> esc_html__( 'Bottom Left', 'woolentor' ),
                                    'bottom_right'=> esc_html__( 'Bottom Right', 'woolentor' ),
                                ],
                                'class'       => 'woolentor-action-field-left',
                            ],
                            [
                                'name'    => 'badge_custom_positionp',
                                'label'   => esc_html__( 'Custom Position', 'woolentor' ),
                                'desc'    => esc_html__( 'Badge Custom Position.', 'woolentor' ),
                                'type'    => 'dimensions',
                                'options' => [
                                    'top'   => esc_html__( 'Top', 'woolentor' ),
                                    'right' => esc_html__( 'Right', 'woolentor' ),
                                    'bottom'=> esc_html__( 'Bottom', 'woolentor' ),
                                    'left'  => esc_html__( 'Left', 'woolentor' ),
                                    'unit'  => esc_html__( 'Unit', 'woolentor' ),
                                ],
                                'class' => 'woolentor-action-field-left woolentor-dimention-field-left',
                                'is_pro'    => true,
                            ],
                            [
                                'name'    => 'badge_condition',
                                'label'   => esc_html__( 'Badge Condition', 'woolentor' ),
                                'type'    => 'select',
                                'default' => 'none',
                                'options' => [
                                    'none' => esc_html__( 'Select Option', 'woolentor' ),
                                    'all_product' => esc_html__( 'All Products', 'woolentor' ),
                                    'selected_product'=> esc_html__( 'Selected Product', 'woolentor' ),
                                    'category'=> esc_html__( 'Category', 'woolentor' ),
                                    'on_sale'=> esc_html__( 'On Sale Only', 'woolentor' ),
                                    'outof_stock'=> esc_html__( 'Out Of Stock', 'woolentor' ),
                                ],
                                'class'       => 'woolentor-action-field-left',
                            ],

                            [
                                'name'        => 'categories',
                                'label'       => esc_html__( 'Select Categories', 'woolentor' ),
                                'desc'        => esc_html__( 'Select the categories in which products the badge will be show.', 'woolentor' ),
                                'type'        => 'multiselect',
                                'options'     => woolentor_taxonomy_list('product_cat','term_id'),
                                'condition'   => [ 'badge_condition', '==', 'category' ],
                                'class'       => 'woolentor-action-field-left'
                            ],

                            [
                                'name'        => 'products',
                                'label'       => esc_html__( 'Select Products', 'woolentor' ),
                                'desc'        => esc_html__( 'Select individual products in which the badge will be show.', 'woolentor' ),
                                'type'        => 'multiselect',
                                'options'     => woolentor_post_name( 'product' ),
                                'condition'   => [ 'badge_condition', '==', 'selected_product' ],
                                'class'       => 'woolentor-action-field-left'
                            ],

                            [
                                'name'        => 'exclude_productsp',
                                'label'       => esc_html__( 'Exclude Products', 'woolentor' ),
                                'type'        => 'select',
                                'default'     => '0',
                                'options'     => [
                                    'select'  => esc_html__('This is a pro features','woolentor'),
                                ],
                                'condition'   => [ 'badge_condition', '!=', 'none' ],
                                'class'       => 'woolentor-action-field-left',
                                'is_pro'      => true,
                            ]


                        ],
                    ],

                ]
            ]
        ];

        return $fields;

    }

}