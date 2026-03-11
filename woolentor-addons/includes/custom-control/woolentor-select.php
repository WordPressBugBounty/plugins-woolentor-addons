<?php
namespace WooLentor\CustomControl;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Select extends \Elementor\Base_Data_Control {

    /**
     * Control type
     */
    public function get_type() {
        return 'woolentor-select';
    }

    /**
     * Default value
     */
    public function get_default_value() {
        return '';
    }

    /**
     * Default settings
     */
    protected function get_default_settings() {
        return [
            'multiple'    => false,
            'ajax_search' => false,
            'post_type'   => 'product',
            'placeholder' => '',
            'options'     => [],
        ];
    }

    /**
     * Enqueue control scripts and styles
     */
    public function enqueue() {
        wp_register_script(
            'woolentor-select-control',
            WOOLENTOR_ADDONS_PL_URL . 'includes/custom-control/assets/js/woolentor-select.js',
            [ 'jquery' ],
            WOOLENTOR_VERSION,
            true
        );

        wp_localize_script( 'woolentor-select-control', 'woolentorSelectControl', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'woolentor_select_control' ),
        ]);

        wp_enqueue_script( 'woolentor-select-control' );
    }

    /**
     * Render control template
     */
    public function content_template() {
        $control_uid = $this->get_control_uid();
        ?>
        <div class="elementor-control-field">
            <# if ( data.label ) { #>
                <label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
            <# } #>
            <div class="elementor-control-input-wrapper elementor-control-unit-5">
                <# var multiple = data.multiple ? 'multiple' : ''; #>
                <select
                    id="<?php echo esc_attr( $control_uid ); ?>"
                    class="woolentor-select-control"
                    {{ multiple }}
                    data-ajax-search="{{ data.ajax_search }}"
                    data-post-type="{{ data.post_type }}"
                    data-placeholder="{{ data.placeholder }}"
                    data-setting="{{ data.name }}"
                >
                    <# if ( ! data.ajax_search ) {
                        _.each( data.options, function( label, value ) { #>
                            <option value="{{ value }}">{{{ label }}}</option>
                        <# });
                    } #>
                </select>
            </div>
        </div>
        <# if ( data.description ) { #>
            <div class="elementor-control-field-description">{{{ data.description }}}</div>
        <# } #>
        <?php
    }

}
