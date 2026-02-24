<?php
namespace WoolentorOptions\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Onboarding extends WP_REST_Controller {

    protected $namespace;

    public function __construct() {
        $this->namespace = 'woolentoropt/v1';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/onboarding/complete',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'handle_complete' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/onboarding/skip',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'handle_skip' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );
    }

    public function permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Handle wizard completion — saves all wizard data + module states
     */
    public function handle_complete( $request ) {
        $params = $request->get_json_params();

        $editor_choice = isset( $params['editor_choice'] ) ? sanitize_text_field( $params['editor_choice'] ) : 'both';
        $user_type     = isset( $params['user_type'] ) ? sanitize_text_field( $params['user_type'] ) : 'starter';
        $email         = isset( $params['email'] ) ? sanitize_email( $params['email'] ) : '';
        $consent       = ! empty( $params['consent'] );
        $modules       = isset( $params['modules'] ) && is_array( $params['modules'] ) ? $params['modules'] : [];

        // Save wizard metadata
        update_option( 'woolentor_setup_wizard_data', [
            'editor_choice' => $editor_choice,
            'user_type'     => $user_type,
            'email'         => $email,
            'consent'       => $consent,
            'completed_at'  => current_time( 'mysql' ),
        ] );

        // Save module states to woolentor_others_tabs option
        // The modules array comes as [ { id: 'module_id', enabled: true/false } ]
        if ( ! empty( $modules ) ) {

            foreach ( $modules as $module ) {
                $module_section = isset( $module['section'] ) ? sanitize_text_field( $module['section'] ) : 'woolentor_others_tabs';
                $module_id = isset( $module['id'] ) ? sanitize_text_field( $module['id'] ) : '';
                $enabled   = ! empty( $module['enabled'] );

                if ( empty( $module_id ) ) {
                    continue;
                }

                $module_options = get_option( $module_section, [] );
                if ( ! is_array( $module_options ) ) {
                    $module_options = [];
                }

                $module_options[ $module_id ] = $enabled ? 'on' : 'off';
                update_option( $module_section, $module_options );
            }

        }

        // Mark wizard as completed
        update_option( 'woolentor_setup_wizard_completed', true );

        // Schedule diagnostic data collection in background if user consented
        if ( $consent ) {
            wp_schedule_single_event( time(), 'woolentor_diagnostic_data_collect_and_send', [ true ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Setup wizard completed successfully.', 'woolentor' ),
        ], 200 );
    }

    /**
     * Handle wizard skip — sets completed flag only
     */
    public function handle_skip( $request ) {
        update_option( 'woolentor_setup_wizard_completed', true );

        return new WP_REST_Response( [
            'success' => true,
            'message' => __( 'Setup wizard skipped.', 'woolentor' ),
        ], 200 );
    }
}
