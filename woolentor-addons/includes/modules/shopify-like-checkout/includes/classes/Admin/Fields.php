<?php
namespace Woolentor\Modules\ShopifyLikeCheckout\Admin;
use WooLentor\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Fields {
    use Singleton;

    public function __construct(){
        add_filter( 'woolentor_admin_fields_vue', [ $this, 'admin_fields' ], 10, 1 );
    }

    /**
     * Admin Field Register
     * @param mixed $fields
     * @return mixed
     */
    public function admin_fields( $fields ){

        if ( ! isset( $fields['woolentor_others_tabs'] ) || ! is_array( $fields['woolentor_others_tabs'] ) ) {
            return $fields;
        }

        array_splice( $fields['woolentor_others_tabs'], 2, 0, $this->setting_fields() );
        return $fields;
    }

    /**
     * Setting Fields
     * @return array
     */
    public function setting_fields(){
        $fields = [
            [
                'id'     => 'woolentor_shopify_checkout_settings',
                'name'    => esc_html__( 'Shopify Style Checkout', 'woolentor' ),
                'type'     => 'module',
                'default'  => 'off',
                'section'  => 'woolentor_shopify_checkout_settings',
                'option_id'=> 'enable',
                'require_settings'  => true,
                'documentation' => esc_url('https://woolentor.com/doc/how-to-make-woocommerce-checkout-like-shopify/'),
                'preview' => esc_url('https://www.youtube.com/watch?v=z12cq8XmgPQ'),
                'setting_fields' => array(

                    array(
                        'id'  => 'enable',
                        'name' => esc_html__( 'Enable / Disable', 'woolentor' ),
                        'desc'  => esc_html__( 'You can enable / disable shopify style checkout page from here.', 'woolentor' ),
                        'type'  => 'checkbox',
                        'default' => 'off',
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'    => 'logo',
                        'name'   => esc_html__( 'Logo', 'woolentor' ),
                        'desc'    => esc_html__( 'You can upload your logo for shopify style checkout page from here.', 'woolentor' ),
                        'type'    => 'imageupload',
                        'options' => [
                            'button_label'        => esc_html__( 'Upload', 'woolentor' ),   
                            'button_remove_label' => esc_html__( 'Remove', 'woolentor' ),   
                        ],
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'        => 'logo_page',
                        'name'       => esc_html__( 'Logo URL', 'woolentor' ),
                        'desc'        => esc_html__( 'Link your logo to an existing page or a custom URL.', 'woolentor' ),
                        'type'        => 'select',
                        'options'     => (['custom'=> esc_html__( 'Custom URL', 'woolentor' )] + woolentor_post_name( 'page', ['limit'=>-1] )),
                        'default'     => '0',
                        'condition'   => array( 'key'=>'logo','operator'=> '!=','value'=> '' ),
                        'class'       => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'        => 'logo_custom_url',
                        'name'       => esc_html__( 'Custom URL', 'woolentor' ),
                        'desc'        => esc_html__( 'Insert a custom URL for the logo.', 'woolentor' ),
                        'type'        => 'text',
                        'placeholder' => esc_html__( 'your-domain.com', 'woolentor' ),
                        'condition'   => array( 'key'=>'logo_page','operator'=> '==', 'value'=>'custom' ),
                        'class'       => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'    => 'custommenu',
                        'name'   => esc_html__( 'Bottom Menu', 'woolentor' ),
                        'desc'    => esc_html__( 'You can choose menu for shopify style checkout page.', 'woolentor' ),
                        'type'    => 'select',
                        'default' => '0',
                        'options' => array( '0'=> esc_html__('Select Menu','woolentor') ) + woolentor_get_all_create_menus(),
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'    => 'enable_header_footer',
                        'name'   => esc_html__( 'Enable Theme Header & Footer', 'woolentor' ),
                        'desc'    => esc_html__( 'Display your active theme\'s header and footer on the Shopify-like checkout page. The checkout form is wrapped in a CSS isolation layer to prevent theme styles from breaking the design, but minor visual differences may still occur depending on your theme.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'class' => 'woolentor-action-field-left'
                    ),
                    array(
                        'id'    => 'hide_logo_on_header_footer',
                        'name'   => esc_html__( 'Hide Checkout Logo', 'woolentor' ),
                        'desc'    => esc_html__( 'Hide the logo area inside the checkout form when the Theme Header & Footer is enabled, to avoid duplicating the logo that already appears in the theme header.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'class' => 'woolentor-action-field-left',
                        'condition' => [
                            'key' => 'enable_header_footer',
                            'operator' => '==',
                            'value' => 'on'
                        ],
                    ),

                    array(
                        'id'    => 'show_phone',
                        'name'   => esc_html__( 'Show Phone Number Field', 'woolentor' ),
                        'desc'    => esc_html__( 'Show the Phone Number Field.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'    => 'show_company',
                        'name'   => esc_html__( 'Show Company Name Field', 'woolentor' ),
                        'desc'    => esc_html__( 'Show the Company Name Field.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'    => 'hide_cart_nivigation',
                        'name'   => esc_html__( 'Hide Cart Navigation', 'woolentor' ),
                        'desc'    => esc_html__( 'Hide the "Cart" menu and "Return to cart" button.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'    => 'hide_shipping_step',
                        'name'   => esc_html__( 'Hide Shipping Step', 'woolentor' ),
                        'desc'    => esc_html__( 'Turn it ON to hide the "Shipping" Step.', 'woolentor' ),
                        'type'    => 'checkbox',
                        'class' => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'        => 'customize_labels',
                        'name'       => esc_html__( 'Rename Labels?', 'woolentor' ),
                        'desc'        => esc_html__( 'Enable it to customize labels of the checkout page.', 'woolentor' ),
                        'type'        => 'checkbox',
                        'class'       => 'woolentor-action-field-left'
                    ),

                    array(
                        'id'        => 'labels_list',
                        'name'       => esc_html__( 'Labels', 'woolentor' ),
                        'type'        => 'repeater',
                        'title_field' => 'select_tab',
                        'condition'   => array( 'key'=>'customize_labels', 'operator'=>'==', 'value'=>'on' ),
                        'max_items' => '3',
                        'options' => [
                            'button_label' => esc_html__( 'Add Custom Label', 'woolentor' ),
                        ],
                        'fields'  => [

                            array(
                                'id'    => 'select_tab',
                                'name'   => esc_html__( 'Select Tab', 'woolentor' ),
                                'desc'    => esc_html__( 'Select the tab for which you want to change the labels. ', 'woolentor' ),
                                'type'    => 'select',
                                'class'   => 'woolentor-action-field-left',
                                'default' => 'information',
                                'options' => array(
                                    'information'  => esc_html__('Information','woolentor'),
                                    'shipping'      => esc_html__('Shipping','woolentor'),
                                    'payment'       => esc_html__('Payment','woolentor'),
                                ),
                            ),

                            array(
                                'id'        => 'tab_label',
                                'name'       => esc_html__( 'Tab Label', 'woolentor' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left',
                            ),

                            array(
                                'id'        => 'label_1',
                                'name'       => esc_html__( 'Button Label One', 'woolentor' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left',
                            ),

                            array(
                                'id'        => 'label_2',
                                'name'       => esc_html__( 'Button Label Two', 'woolentor' ),
                                'type'        => 'text',
                                'class'       => 'woolentor-action-field-left',
                            ),

                        ]
                    ),
                    
                )

            ]
        ];

        return $fields;
    }

}