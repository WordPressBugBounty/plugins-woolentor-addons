<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Woolentor_Wb_Ever_Compare_Table_Widget extends Widget_Base {

    public function get_name() {
        return 'wb-evercompare-table';
    }

    public function get_title() {
        return __( 'WL: EverCompare', 'woolentor' );
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return array( 'woolentor-addons' );
    }

    public function get_help_url() {
        return 'https://woolentor.com/documentation/';
    }

    public function get_style_depends(){
        return [
            'evercompare-frontend',
            'woolentor-widgets',
        ];
    }

    public function get_script_depends(){
        return ['evercompare-frontend'];
    }

    public function get_keywords(){
        return ['compare','product compare','ever compare'];
    }

    protected function register_controls() {

        // Content
        $this->start_controls_section(
            'evercompare_content',
            [
                'label' => __( 'EverCompare', 'woolentor' ),
            ]
        );

            $this->add_control(
                'empty_table_text',
                [
                    'label' => __( 'Empty table text', 'woolentor' ),
                    'type' => Controls_Manager::TEXT,
                    'label_block'=>true,
                ]
            );

        $this->end_controls_section();

        // Heading Style
        $this->start_controls_section(
            'heading_style_section',
            [
                'label' => __( 'Heading', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_control(
                'heading_color',
                [
                    'label' => __( 'Heading Color', 'woolentor' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .htcolumn-field-name' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'heading_typography',
                    'label' => __( 'Typography', 'woolentor' ),
                    'selector' => '{{WRAPPER}} .htcolumn-field-name',
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name' => 'heading_background',
                    'label' => __( 'Even Heading Background', 'woolentor' ),
                    'types' => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .htcompare-row:nth-child(2n) .htcompare-col',
                    'exclude' =>['image'],
                    'fields_options'=>[
                        'background'=>[
                            'label'=>__( 'Even Heading Background', 'woolentor' )
                        ]
                    ]
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name' => 'heading_background_odd',
                    'label' => __( 'Odd Heading Background', 'woolentor' ),
                    'types' => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .htcompare-row:nth-child(2n+1) .htcompare-col',
                    'exclude' =>['image'],
                    'fields_options'=>[
                        'background'=>[
                            'label'=>__( 'Odd Heading Background', 'woolentor' )
                        ]
                    ]
                ]
            );
            
        $this->end_controls_section();

        // Content Style
        $this->start_controls_section(
            'content_style_section',
            [
                'label' => __( 'Content', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
            $this->add_control(
                'content_color',
                [
                    'label' => __( 'Content Color', 'woolentor' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .htcolumn-value' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'content_link_color',
                [
                    'label' => __( 'Content Link Color', 'woolentor' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .htcolumn-value a' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'cart_btn_color',
                [
                    'label' => __( 'Cart Button Color', 'woolentor' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .htcolumn-value a.htcompare-cart-button' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Background::get_type(),
                [
                    'name' => 'cart_btn_background',
                    'label' => __( 'Cart Button Background', 'woolentor' ),
                    'types' => [ 'classic', 'gradient' ],
                    'selector' => '{{WRAPPER}} .htcolumn-value a.htcompare-cart-button',
                    'exclude' =>['image'],
                    'fields_options'=>[
                        'background'=>[
                            'label'=>__( 'Cart Button Background', 'woolentor' )
                        ]
                    ]
                ]
            );

            $this->add_control(
                'border_color',
                [
                    'label' => __( 'Border Color', 'woolentor' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .htcompare-col' => 'border-color: {{VALUE}}',
                    ],
                ]
            );

        $this->end_controls_section();

        // Link Share Button Style
        $this->start_controls_section(
            'link_share_button_style_section',
            [
                'label' => __( 'Link Share Button', 'woolentor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'share_button_typography',
                    'label' => __( 'Typography', 'woolentor' ),
                    'selector' => '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link',
                ]
            );

            $this->add_control(
                'share_button_padding',
                [
                    'label' => esc_html__( 'Padding', 'woolentor' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em', 'rem' ],
                    'selectors' => [
                        '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->start_controls_tabs('share_button_style_tabs');

                $this->start_controls_tab(
                    'share_button_normal_tab',
                    [
                        'label' => __( 'Normal', 'woolentor' ),
                    ]
                );

                    $this->add_control(
                        'share_button_normal_color',
                        [
                            'label' => __( 'Color', 'woolentor' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'share_button_normal_background',
                            'label' => __( 'Background', 'woolentor' ),
                            'types' => [ 'classic', 'gradient' ],
                            'selector' => '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link',
                            'exclude' =>['image'],
                            'fields_options'=>[
                                'background'=>[
                                    'label'=>__( 'Background', 'woolentor' )
                                ]
                            ]
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'share_button_normal_border',
                            'selector' => '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link',
                        ]
                    );

                $this->end_controls_tab();

                $this->start_controls_tab(
                    'share_button_hover_tab',
                    [
                        'label' => __( 'Hover', 'woolentor' ),
                    ]
                );

                    $this->add_control(
                        'share_button_hover_color',
                        [
                            'label' => __( 'Color', 'woolentor' ),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link:hover' => 'color: {{VALUE}}',
                            ],
                        ]
                    );

                    $this->add_group_control(
                        Group_Control_Background::get_type(),
                        [
                            'name' => 'share_button_hover_background',
                            'label' => __( 'Background', 'woolentor' ),
                            'types' => [ 'classic', 'gradient' ],
                            'selector' => '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link:hover',
                            'exclude' =>['image'],
                            'fields_options'=>[
                                'background'=>[
                                    'label'=>__( 'Background', 'woolentor' )
                                ]
                            ]
                        ]
                    );
                    
                    $this->add_group_control(
                        Group_Control_Border::get_type(),
                        [
                            'name' => 'share_button_hover_border',
                            'selector' => '{{WRAPPER}} .ever-compare-shareable-link .evercompare-copy-link:hover',
                        ]
                    );

                $this->end_controls_tab();

            $this->end_controls_tabs();
        
        $this->end_controls_section();

    }

    protected function render( $instance = [] ) {
        $settings   = $this->get_settings_for_display();

        $short_code_attributes = [
            'empty_compare_text' => $settings['empty_table_text'],
        ];
        echo woolentor_do_shortcode( 'evercompare_table', $short_code_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

}
