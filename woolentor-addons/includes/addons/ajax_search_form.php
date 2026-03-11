<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Woolentor_Ajax_Search_Form_Widget extends Widget_Base {

    public function get_name() {
        return 'wl-ajax-search-form';
    }
    
    public function get_title() {
        return __( 'WL: Ajax Product Search Form', 'woolentor' );
    }

    public function get_icon() {
        return 'eicon-site-search';
    }

    public function get_categories() {
        return array( 'woolentor-addons' );
    }

    public function get_help_url() {
        return 'https://woolentor.com/documentation/';
    }

    public function get_style_depends(){
        return [
            'woolentor-ajax-search',
        ];
    }

    public function get_script_depends(){
        return [
            'woolentor-ajax-search',
        ];
    }

    public function get_keywords(){
        return ['search','search form','product search','live search','ajax search','ajax search form','product ajax search','ajax search widget'];
    }

    protected function register_controls() {

        // Content Start
        $this->start_controls_section(
            'woolentor-ajax-search-form',
            [
                'label' => esc_html__( 'Search Form', 'woolentor' ),
            ]
        );
            
            $this->add_control(
                'limit',
                [
                    'label' => __( 'Show Number of Product', 'woolentor' ),
                    'type' => Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 100,
                    'step' => 1,
                    'default' => 10,
                ]
            );

            $this->add_control(
                'placeholder_text',
                [
                    'label'     => __( 'Placeholder Text', 'woolentor' ),
                    'type'      => Controls_Manager::TEXT,
                    'default'   => __( 'Search Products', 'woolentor' ),
                    'label_block'=>true,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'show_category',
                [
                    'label' => esc_html__( 'Show Category Dropdown', 'woolentor' ),
                    'type' => Controls_Manager::SWITCHER,
                    'return_value' => 'yes',
                    'default' => 'no',
                ]
            );

            $this->add_control(
                'all_category_text',
                [
                    'label'     => __( 'All Category Text', 'woolentor' ),
                    'type'      => Controls_Manager::TEXT,
                    'default'   => __( 'All Categories', 'woolentor' ),
                    'label_block' => true,
                    'condition'=>[
                        'show_category'=>'yes'
                    ]
                ]
            );

        $this->end_controls_section();
        // Content end

        $this->start_controls_section(
            'search_form_area',
            [
                'label' => __( 'Form Area', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl-ajax-search-form', 'search_form_area');

        $this->end_controls_section();

        // Input Box Area Style tab section
        $this->start_controls_section(
            'search_form_input_box_area',
            [
                'label' => __( 'Input Box Area', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl-ajax-search-form', 'search_form_input_box_area');

        $this->end_controls_section();

        // Input Box Style tab section
        $this->start_controls_section(
            'search_form_input',
            [
                'label' => __( 'Input Box', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
            
            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl-ajax-search-form', 'search_form_input');

        $this->end_controls_section();

        // Category Dropdown Style tab section
        $this->start_controls_section(
            'search_form_category_dropdown',
            [
                'label' => __( 'Category Dropdown', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition'=>[
                    'show_category'=>'yes'
                ]
            ]
        );

            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl-ajax-search-form', 'search_form_category_dropdown');

        $this->end_controls_section();

        // Submit Button
        $this->start_controls_section(
            'search_form_style_submit_button',
            [
                'label' => __( 'Button', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl-ajax-search-form', 'search_form_style_submit_button');

        $this->end_controls_section();

        // Search results Style section
        $this->start_controls_section(
            'search_form_style_results',
            [
                'label' => esc_html__( 'Search Results', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            woolentor_upgrade_pro_notice_elementor($this, Controls_Manager::RAW_HTML, 'wl-ajax-search-form', 'search_form_style_results');

        $this->end_controls_section();


    }

    protected function render() {

        $settings  = $this->get_settings_for_display();
        $shortcode_atts = [
            'limit'         => $settings[ 'limit' ],
            'placeholder'   => $settings[ 'placeholder_text' ],
            'show_category' => ( 'yes' === $settings['show_category'] ),
        ];
        if( 'yes' === $settings['show_category'] ){
            $shortcode_atts['all_category_text'] = $settings['all_category_text'];
        }
        echo woolentor_do_shortcode( 'woolentorsearch', $shortcode_atts );

    }

}