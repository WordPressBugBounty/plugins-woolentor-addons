<?php
namespace WooLentor\Product_Kit;

/**
 * Elementor controls, generated from schema/product-section.json.
 *
 * The option list used to be written twice — once as Elementor `add_control()` calls and once as
 * block attributes — which is exactly how the two builders drift apart. It is written once now, as
 * data, and this file turns it into controls.
 *
 * **Control ids are the schema's field keys.** That is the whole point: a page saved before a
 * widget moved onto the generator renders identically after it, and a block reading the same schema
 * hands `Product_Query` a settings array with the same keys.
 *
 * Every Elementor reference is inside a method. The file is safe to load on a site with no
 * Elementor; nothing calls these until a widget registers its controls.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Elementor_Controls {

    /**
     * Add one schema group as a controls section.
     *
     * @param  \Elementor\Widget_Base $widget
     * @param  string                 $group  query | pagination | tabs | slider
     * @param  array                  $args {
     *     @type string $section_id  Section id. Defaults to "section_{$group}".
     *     @type string $label       Section label. Defaults to the schema's group label.
     *     @type array  $conditions  Elementor nested conditions for the section.
     *     @type array  $condition   Flat Elementor condition for the section.
     *     @type array  $overrides   field key => extra control args, merged last.
     *     @type array  $skip        Field keys to leave out.
     * }
     * @return void
     */
    public static function add_section( $widget, $group, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'section_id' => 'section_' . $group,
            'label'      => '',
            'conditions' => [],
            'condition'  => [],
            'overrides'  => [],
            'skip'       => [],
        ] );

        $section = [ 'label' => $args['label'] ?: self::group_label( $group ) ];

        if ( $args['conditions'] ) {
            $section['conditions'] = $args['conditions'];
        }

        if ( $args['condition'] ) {
            $section['condition'] = $args['condition'];
        }

        $widget->start_controls_section( $args['section_id'], $section );

        self::add_fields( $widget, $group, $args );

        $widget->end_controls_section();
    }

    /**
     * Add a group's fields to whatever section is already open.
     *
     * Separate from add_section() because a consumer sometimes wants the schema's controls inside a
     * section of its own making, next to controls the schema knows nothing about.
     *
     * @param  \Elementor\Widget_Base $widget
     * @param  string                 $group
     * @param  array                  $args  overrides / skip, as add_section().
     * @return void
     */
    public static function add_fields( $widget, $group, array $args = [] ) {
        $overrides = (array) ( $args['overrides'] ?? [] );
        $skip      = (array) ( $args['skip'] ?? [] );

        foreach ( Schema::fields( $group ) as $key => $field ) {
            if ( in_array( $key, $skip, true ) ) {
                continue;
            }

            $control = self::control_args( $field, self::strings( $group, $key ) );

            if ( null === $control ) {
                continue;
            }

            if ( 'repeater' === ( $field['control'] ?? '' ) ) {
                $control['fields'] = self::repeater_fields( $group, $key, $field );
            }

            if ( isset( $overrides[ $key ] ) ) {
                $control = array_merge( $control, (array) $overrides[ $key ] );
            }

            $widget->add_control( $key, $control );

            self::add_pro_notice( $widget, $group, $key, $field );
        }
    }

    /**
     * The panel notice a free site sees when it picks an option its licence does not cover.
     *
     * Conditioned on the locked values *and* on whatever already hides the control, so the notice
     * cannot appear under a control that is itself hidden — Pagination Type's notice must not show
     * when pagination is switched off.
     *
     * @param  \Elementor\Widget_Base $widget
     * @param  string                 $group
     * @param  string                 $key
     * @param  array                  $field
     * @return void
     */
    private static function add_pro_notice( $widget, $group, $key, array $field ) {
        if ( ! function_exists( 'woolentor_upgrade_pro_notice' ) ) {
            return;
        }

        $condition = self::condition( $field );

        if ( Schema::is_pro_field( $group, $key ) ) {
            // A whole-control gate: the notice appears once the user switches the feature on, which
            // is the moment the answer "not on this licence" is worth giving.
            $condition[ $key ] = 'yes';
        } else {
            $locked = Schema::locked_options( $group, $key );

            if ( empty( $locked ) ) {
                return;
            }

            $condition[ $key ] = $locked;
        }

        woolentor_upgrade_pro_notice( $widget, $key . '_pro_notice', $condition, [ 'mode' => 'alert' ] );
    }

    /**
     * The Style-tab section for a carousel's arrows and dots.
     *
     * Not generated from the schema, and deliberately so: the schema describes *settings*, which a
     * block and a widget share, while these are Elementor selectors, which a block does not have.
     * A block styles its own carousel through theme.json or its own stylesheet.
     *
     * Control ids are Hero Banner's, for the same reason the slider option names are — that widget
     * has them saved on live pages, and this is the section it would move onto.
     *
     * @param  \Elementor\Widget_Base $widget
     * @param  array                  $args {
     *     @type string $section_id     Defaults to 'style_slider_controller'.
     *     @type string $label          Section label.
     *     @type array  $conditions     Nested Elementor conditions.
     *     @type array  $condition      Flat Elementor condition.
     *     @type string $dots_position  'absolute' — the dots sit over the slide and are moved with
     *                                  `left`, as a hero's do. 'stack' — they sit under the track
     *                                  and are moved with `margin-top`, as a product rail's do.
     * }
     * @return void
     */
    public static function add_slider_style_section( $widget, array $args = [] ) {
        $args = wp_parse_args( $args, [
            'section_id'    => 'style_slider_controller',
            'label'         => esc_html__( 'Slider Controller Style', 'woolentor' ),
            'conditions'    => [],
            'condition'     => [],
            'dots_position' => 'stack',
        ] );

        $section = [ 'label' => $args['label'], 'tab' => \Elementor\Controls_Manager::TAB_STYLE ];

        if ( $args['conditions'] ) {
            $section['conditions'] = $args['conditions'];
        }

        if ( $args['condition'] ) {
            $section['condition'] = $args['condition'];
        }

        $widget->start_controls_section( $args['section_id'], $section );

        $widget->start_controls_tabs( 'slider_controller_tabs' );

            $widget->start_controls_tab( 'slider_controller_normal', [ 'label' => esc_html__( 'Normal', 'woolentor' ) ] );

                $widget->add_control( 'nav_arrow_heading', [
                    'label' => esc_html__( 'Navigation Arrow', 'woolentor' ),
                    'type'  => \Elementor\Controls_Manager::HEADING,
                ] );

                $widget->add_responsive_control( 'nav_arrow_position', [
                    'label'      => esc_html__( 'Position (Top)', 'woolentor' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'range'      => [
                        'px' => [ 'min' => 0, 'max' => 1000, 'step' => 1 ],
                        '%'  => [ 'min' => 0, 'max' => 100 ],
                    ],
                    'selectors'  => [ '{{WRAPPER}} .wl-pack-nav' => 'top: {{SIZE}}{{UNIT}};' ],
                ] );

                $widget->add_control( 'nav_arrow_color', [
                    'label'     => esc_html__( 'Color', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-nav' => 'color: {{VALUE}};' ],
                ] );

                $widget->add_control( 'nav_arrow_bg', [
                    'label'     => esc_html__( 'Background Color', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-nav' => 'background-color: {{VALUE}};' ],
                ] );

                $widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [
                    'name'     => 'nav_arrow_border',
                    'selector' => '{{WRAPPER}} .wl-pack-nav',
                ] );

                $widget->add_responsive_control( 'nav_arrow_border_radius', [
                    'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-nav' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                ] );

                $widget->add_responsive_control( 'nav_arrow_padding', [
                    'label'      => esc_html__( 'Padding', 'woolentor' ),
                    'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors'  => [ '{{WRAPPER}} .wl-pack-nav' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
                ] );

                $widget->add_control( 'nav_dots_heading', [
                    'label'     => esc_html__( 'Navigation Dots', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ] );

                self::dots_position_control( $widget, $args['dots_position'] );

                $widget->add_control( 'nav_dots_bg', [
                    'label'     => esc_html__( 'Background Color', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-dots li button' => 'background-color: {{VALUE}};' ],
                ] );

                $widget->add_responsive_control( 'nav_dots_size', [
                    'label'      => esc_html__( 'Size', 'woolentor' ),
                    'type'       => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px' ],
                    'range'      => [ 'px' => [ 'min' => 4, 'max' => 24 ] ],
                    'selectors'  => [
                        '{{WRAPPER}} .wl-pack-dots li button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    ],
                ] );

                $widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [
                    'name'     => 'nav_dots_border',
                    'selector' => '{{WRAPPER}} .wl-pack-dots li button',
                ] );

                $widget->add_responsive_control( 'nav_dots_border_radius', [
                    'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-dots li button' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                ] );

            $widget->end_controls_tab();

            $widget->start_controls_tab( 'slider_controller_hover', [ 'label' => esc_html__( 'Hover', 'woolentor' ) ] );

                $widget->add_control( 'nav_arrow_hover_heading', [
                    'label' => esc_html__( 'Navigation Arrow', 'woolentor' ),
                    'type'  => \Elementor\Controls_Manager::HEADING,
                ] );

                $widget->add_control( 'nav_arrow_color_hover', [
                    'label'     => esc_html__( 'Color', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-nav:hover' => 'color: {{VALUE}};' ],
                ] );

                $widget->add_control( 'nav_arrow_bg_hover', [
                    'label'     => esc_html__( 'Background Color', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-nav:hover' => 'background-color: {{VALUE}};' ],
                ] );

                $widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [
                    'name'     => 'nav_arrow_border_hover',
                    'selector' => '{{WRAPPER}} .wl-pack-nav:hover',
                ] );

                $widget->add_responsive_control( 'nav_arrow_border_radius_hover', [
                    'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-nav:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                ] );

                $widget->add_control( 'nav_dots_hover_heading', [
                    'label'     => esc_html__( 'Navigation Dots', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ] );

                // One colour for hover and for the dot that is currently showing: they are the same
                // state to a visitor — "this one" — and two controls for it invite a page where the
                // active dot and the hovered dot disagree.
                $widget->add_control( 'nav_dots_bg_hover', [
                    'label'       => esc_html__( 'Background Color', 'woolentor' ),
                    'type'        => \Elementor\Controls_Manager::COLOR,
                    'description' => esc_html__( 'Also used for the dot of the slide currently showing.', 'woolentor' ),
                    'selectors'   => [
                        '{{WRAPPER}} .wl-pack-dots li button:hover'        => 'background-color: {{VALUE}};',
                        '{{WRAPPER}} .wl-pack-dots li.slick-active button' => 'background-color: {{VALUE}};',
                    ],
                ] );

                $widget->add_group_control( \Elementor\Group_Control_Border::get_type(), [
                    'name'     => 'nav_dots_border_hover',
                    'selector' => '{{WRAPPER}} .wl-pack-dots li button:hover',
                ] );

                $widget->add_responsive_control( 'nav_dots_border_radius_hover', [
                    'label'     => esc_html__( 'Border Radius', 'woolentor' ),
                    'type'      => \Elementor\Controls_Manager::DIMENSIONS,
                    'selectors' => [ '{{WRAPPER}} .wl-pack-dots li button:hover' => 'border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ],
                ] );

            $widget->end_controls_tab();

        $widget->end_controls_tabs();

        $widget->end_controls_section();
    }

    /**
     * Where the dots sit, which depends on what they sit on.
     *
     * A hero lays them over the slide, so they are moved across it with `left`. A product rail has
     * no photograph to lay them on — they sit under the track — so the useful control is the gap
     * above them. Offering `left` there would be a control that does nothing, because the rail's
     * dots are statically positioned.
     *
     * @param  \Elementor\Widget_Base $widget
     * @param  string                 $mode
     * @return void
     */
    private static function dots_position_control( $widget, $mode ) {
        if ( 'absolute' === $mode ) {
            $widget->add_responsive_control( 'nav_dots_position', [
                'label'      => esc_html__( 'Position (Left)', 'woolentor' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 1000, 'step' => 5 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'default'    => [ 'unit' => '%', 'size' => 50 ],
                'selectors'  => [ '{{WRAPPER}} .wl-pack-dots' => 'left: {{SIZE}}{{UNIT}};' ],
            ] );

            return;
        }

        $widget->add_responsive_control( 'nav_dots_spacing', [
            'label'      => esc_html__( 'Space Above', 'woolentor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
            'selectors'  => [ '{{WRAPPER}} .wl-pack-dots' => 'margin-top: {{SIZE}}{{UNIT}};' ],
        ] );
    }

    /**
     * The translated strings a field needs, resolved from wherever the field lives — a group, or a
     * row inside a repeater. Resolving them here rather than inside control_args() is what keeps a
     * row field from picking up a same-named field at the top level.
     *
     * @param  string      $group
     * @param  string      $key
     * @param  string|null $row_key
     * @return array
     */
    private static function strings( $group, $key, $row_key = null ) {
        if ( null === $row_key ) {
            return [
                'label'       => Schema::label( $group, $key ),
                'description' => Schema::description( $group, $key ),
                'options'     => Schema::options( $group, $key ),
                'default'     => Schema::field_text( $group, $key, 'default' ),
                'placeholder' => Schema::field_text( $group, $key, 'placeholder' ),
            ];
        }

        $row = Schema::row_field( $group, $key, $row_key );

        return [
            'label'       => Schema::row_label( $group, $key, $row_key ),
            'description' => Schema::translate_public( $row['description'] ?? '' ),
            'options'     => Schema::row_options( $group, $key, $row_key ),
            'default'     => Schema::translate_public( $row['default'] ?? '' ),
            'placeholder' => Schema::translate_public( $row['placeholder'] ?? '' ),
        ];
    }

    /**
     * One schema field as Elementor control arguments.
     *
     * @param  array $field
     * @param  array $t  Translated strings from strings().
     * @return array|null
     */
    private static function control_args( array $field, array $t ) {
        $control = [ 'label' => $t['label'] ];

        if ( '' !== $t['description'] ) {
            $control['description'] = $t['description'];
        }

        if ( ! empty( $field['separator'] ) ) {
            $control['separator'] = $field['separator'];
        }

        $condition = self::condition( $field );

        if ( $condition ) {
            $control['condition'] = $condition;
        }

        switch ( $field['control'] ?? '' ) {
            case 'select':
                $control['type']    = \Elementor\Controls_Manager::SELECT;
                $control['default'] = (string) ( $field['default'] ?? '' );
                $control['options'] = $t['options'];
                break;

            case 'number':
                $control['type']    = \Elementor\Controls_Manager::NUMBER;
                $control['default'] = (int) ( $field['default'] ?? 0 );

                foreach ( [ 'min', 'max', 'step' ] as $bound ) {
                    if ( isset( $field[ $bound ] ) ) {
                        $control[ $bound ] = (int) $field[ $bound ];
                    }
                }
                break;

            case 'text':
                $control['type']        = \Elementor\Controls_Manager::TEXT;
                $control['default']     = $t['default'];
                $control['label_block'] = true;
                break;

            case 'switch':
                // Elementor stores a switch as 'yes' or an empty string, which is why every read in
                // the kit goes through Settings::truthy() rather than comparing to either.
                $control['type']         = \Elementor\Controls_Manager::SWITCHER;
                $control['return_value'] = 'yes';
                $control['default']      = ! empty( $field['default'] ) ? 'yes' : '';
                break;

            case 'taxonomy':
                $control['type']        = \Elementor\Controls_Manager::SELECT2;
                $control['multiple']    = true;
                $control['label_block'] = true;
                $control['options']     = self::taxonomy_options( (string) ( $field['taxonomy'] ?? '' ) );
                break;

            case 'product-search':
                // The catalogue is searched over AJAX rather than listed, so a store with more
                // products than a select can hold is still fully reachable.
                $control['type']        = 'woolentor-select';
                $control['multiple']    = true;
                $control['ajax_search'] = true;
                $control['post_type']   = 'product';
                $control['label_block'] = true;

                if ( '' !== $t['placeholder'] ) {
                    $control['placeholder'] = $t['placeholder'];
                }
                break;

            case 'heading':
                // Not a setting — a label that groups the controls under it. It carries no default
                // and never reaches a settings array.
                $control['type'] = \Elementor\Controls_Manager::HEADING;
                break;

            case 'repeater':
                // `fields` is filled in by the caller, which has the group and key needed to
                // resolve each row field's own strings.
                $control['type']    = \Elementor\Controls_Manager::REPEATER;
                $control['default'] = [];

                if ( ! empty( $field['titleField'] ) ) {
                    $control['title_field'] = '{{{ ' . sanitize_key( $field['titleField'] ) . ' }}}';
                }

                // Elementor hides a row's remove button once only one row is left. Where an empty
                // list is a real configuration, that traps a row added by mistake with no way to
                // delete it, so the guard is switched off.
                if ( isset( $field['preventEmpty'] ) ) {
                    $control['prevent_empty'] = (bool) $field['preventEmpty'];
                }
                break;

            default:
                return null;
        }

        return $control;
    }

    /**
     * A repeater's own controls, built through the same translator so a row's fields cannot drift
     * from the section's.
     *
     * A repeater field's conditions can only see other fields in the same row, never the widget's
     * own settings — which is fine, because every condition inside one is row-local.
     *
     * @param  string $group
     * @param  string $key
     * @param  array  $field
     * @return array
     */
    private static function repeater_fields( $group, $key, array $field ) {
        $repeater = new \Elementor\Repeater();

        foreach ( (array) ( $field['fields'] ?? [] ) as $row_key => $row_field ) {
            $args = self::control_args( $row_field, self::strings( $group, $key, $row_key ) );

            if ( null === $args ) {
                continue;
            }

            $repeater->add_control( $row_key, $args );
        }

        return $repeater->get_controls();
    }

    /**
     * `showWhen` / `hideWhen` as an Elementor `condition` array.
     *
     * A boolean in the schema means a switch, and Elementor stores those as 'yes'.
     *
     * @param  array $field
     * @return array
     */
    private static function condition( array $field ) {
        $condition = [];

        foreach ( (array) ( $field['showWhen'] ?? [] ) as $name => $value ) {
            $condition[ $name ] = self::condition_value( $value );
        }

        foreach ( (array) ( $field['hideWhen'] ?? [] ) as $name => $value ) {
            $condition[ $name . '!' ] = self::condition_value( $value );
        }

        return $condition;
    }

    /**
     * @param  mixed $value
     * @return mixed
     */
    private static function condition_value( $value ) {
        if ( is_bool( $value ) ) {
            return $value ? 'yes' : '';
        }

        // A one-item list is written as a scalar, which is what the hand-written controls used and
        // what keeps a generated control identical to the one it replaced.
        if ( is_array( $value ) && 1 === count( $value ) ) {
            return reset( $value );
        }

        return $value;
    }

    /**
     * Term slug => name, matching how the query manager reads taxonomies.
     *
     * @param  string $taxonomy
     * @return array
     */
    private static function taxonomy_options( $taxonomy ) {
        if ( '' === $taxonomy ) {
            return [];
        }

        $options = [];
        $terms   = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => true ] );

        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->slug ] = $term->name;
            }
        }

        return $options;
    }

    /**
     * A group's section label.
     *
     * @param  string $group
     * @return string
     */
    private static function group_label( $group ) {
        $groups = Schema::all()['groups'] ?? [];

        return Schema::translate_public( $groups[ $group ]['label'] ?? '' );
    }
}
