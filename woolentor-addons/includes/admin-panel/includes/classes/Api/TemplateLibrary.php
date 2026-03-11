<?php
namespace WoolentorOptions\Api;

use WP_REST_Controller;
use WP_Error;

/**
 * Template Library Handler
 */
class TemplateLibrary extends WP_REST_Controller {

    /**
     * Constructor
     */
    public function __construct() {
        $this->namespace = 'woolentoropt/v1';
        $this->rest_base = 'templates';
    }

    /**
     * Register Routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_templates_info'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/gutenberg',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_gutenberg_templates_info'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/import',
            [
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'import_template'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
    }

    /**
     * Permission Check
     */
    public function permissions_check($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permissions to manage this resource.', 'woolentor'),
                ['status' => 401]
            );
        }
        return true;
    }

    /**
     * Get Templates Info
     */
    public function get_templates_info($request) {
        return class_exists('\Woolentor_Template_Library_Manager') ? \Woolentor_Template_Library_Manager::get_templates_info() : [];
    }

    /**
     * Get Gutenberg Templates Info
     */
    public function get_gutenberg_templates_info($request) {
        return class_exists('\Woolentor_Template_Library_Manager') ? \Woolentor_Template_Library_Manager::get_gutenberg_templates_info() : [];
    }

    /**
     * Get Gutenberg Patterns Info
     */
    public function get_gutenberg_patterns_info($request) {
        return class_exists('\Woolentor_Template_Library_Manager') ? \Woolentor_Template_Library_Manager::get_gutenberg_patterns_info() : [];
    }

    /**
     * Import Template
     */
    public function import_template($request) {

        if ( ! wp_verify_nonce( $request['verifynonce'], 'woolentor_verifynonce' ) ) {
            $errormessage = array(
                'message'  => __('Are you cheating?','woolentor')
            );
            wp_send_json_error( $errormessage );
        }

        $template_id    = sanitize_text_field( $request['template_id'] );
        $template_title = sanitize_text_field( $request['template_title'] );
        $import_to      = sanitize_text_field( $request['import_to'] );
        $template_type  = !empty( $request['template_type'] ) ? sanitize_text_field( $request['template_type'] ) : 'other';
        $builder        = !empty( $request['builder'] ) ? sanitize_text_field( $request['builder'] ) : 'elementor';
        $parent_id      = !empty( $request['parent_id'] ) ? sanitize_text_field( $request['parent_id'] ) : '';
        $defaulttitle   = ucfirst( $parent_id ) .' -> '.$template_title;

        // Create the post
        $new_post_id = $this->create_import_post( $import_to, $template_title, $defaulttitle, $template_type );

        if ( is_wp_error( $new_post_id ) ) {
            return rest_ensure_response([
                'error' => true,
                'message' => __('Failed to create post.', 'woolentor')
            ]);
        }

        // Fetch template data based on builder type
        $data_type     = ( $builder === 'gutenberg' ) ? 'gutenberg' : 'template';
        $response_data = \Woolentor_Template_Library_Manager::get_template_data( $data_type, $template_id );

        // Import content based on builder type
        if ( $builder === 'gutenberg' ) {
            $this->import_gutenberg_content( $new_post_id, $response_data );
        } else {
            $this->import_elementor_content( $new_post_id, $response_data );
        }

        // Set builder meta for woolentor-template imports
        if ( $import_to === 'builder' && $new_post_id && ! is_wp_error( $new_post_id ) ) {
            update_post_meta( $new_post_id, 'woolentor_template_meta_type', $template_type );
            update_post_meta( $new_post_id, 'woolentor_template_meta_editor', $builder );
        }

        // Popup settings
        $this->set_popup_settings( $new_post_id, $response_data );

        // Page template
        if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
            $default_template = ( $builder === 'gutenberg' ) ? 'woolentor_fullwidth' : 'elementor_header_footer';
            update_post_meta( $new_post_id, '_wp_page_template', !empty( $response_data['page_template'] ) ? $response_data['page_template'] : $default_template );
        }

        // Build response
        $edit_action = ( $builder === 'gutenberg' ) ? 'edit' : 'elementor';
        $data_response = [
            'id'           => $new_post_id,
            'edittxt'      => $import_to === 'page' ? esc_html__( 'Edit Page', 'woolentor' ) : esc_html__( 'Edit Template', 'woolentor' ),
            'edit_action'  => $edit_action,
            'editlink'     => esc_url( admin_url( 'post.php?post=' . $new_post_id . '&action=' . $edit_action ) )
        ];

        return rest_ensure_response( $data_response );
    }

    /**
     * Create a post for template import
     */
    private function create_import_post( $import_to, $template_title, $defaulttitle, $template_type ) {
        $post_type = $import_to === 'page' ? 'page' : ( $import_to === 'builder' ? 'woolentor-template' : 'elementor_library' );

        $args = [
            'post_type'    => $post_type,
            'post_status'  => ( $import_to === 'page' || $template_type == 'popup' ) ? 'draft' : 'publish',
            'post_title'   => $import_to === 'page' ? $template_title : $defaulttitle,
            'post_content' => '',
        ];

        return wp_insert_post( $args );
    }

    /**
     * Import Elementor template content into a post
     */
    private function import_elementor_content( $post_id, $response_data ) {
        $json_value = wp_slash( wp_json_encode( $response_data['content']['content'] ) );
        update_post_meta( $post_id, '_elementor_data', $json_value );
        update_post_meta( $post_id, '_elementor_template_type', $response_data['type'] );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );

        if ( isset( $response_data['page_settings'] ) ) {
            update_post_meta( $post_id, '_elementor_page_settings', $response_data['page_settings'] );
        }
    }

    /**
     * Import Gutenberg template content into a post
     */
    private function import_gutenberg_content( $post_id, $response_data ) {
        $content = is_array( $response_data['content'] ) ? wp_json_encode( $response_data['content'] ) : $response_data['content'];
        wp_update_post( [ 'ID' => $post_id, 'post_content' => $content ] );
    }

    /**
     * Set popup builder settings if applicable
     */
    private function set_popup_settings( $post_id, $response_data ) {
        if ( !empty( $response_data['type'] ) && $response_data['type'] == 'popup' ) {
            update_post_meta( $post_id, '_wlpb_popup_seetings', $response_data['popup_settings'] );
        }
    }
}