<?php
namespace WoolentorOptions\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

/**
 * Ajax Select Field Handler
 */
class Ajax_Select extends WP_REST_Controller {

    /**
     * Constructor
     */
    public function __construct() {
        $this->namespace = 'woolentoropt/v1';
        $this->rest_base = 'ajax-select';
    }

    /**
     * Register Routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/search',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'search_posts'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/titles',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'get_titles'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
    }

    /**
     * Permission Check
     */
    public function permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__( 'You do not have permissions to access this resource.', 'woolentor' ),
                [ 'status' => 401 ]
            );
        }
        return true;
    }

    /**
     * Search posts by keyword
     */
    public function search_posts( $request ) {
        $search_term = isset( $request['s'] ) ? sanitize_text_field( $request['s'] ) : '';
        $post_type   = isset( $request['post_type'] ) ? sanitize_text_field( $request['post_type'] ) : 'product';

        if ( empty( $search_term ) ) {
            return rest_ensure_response( [ 'results' => [] ] );
        }

        $query = new \WP_Query( [
            's'              => $search_term,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'fields'         => 'ids',
        ] );

        $results = [];
        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post_id ) {
                $results[] = [
                    'id'   => $post_id,
                    'text' => get_the_title( $post_id ),
                ];
            }
        }

        return rest_ensure_response( [ 'results' => $results ] );
    }

    /**
     * Get titles for given post IDs
     */
    public function get_titles( $request ) {
        $ids       = isset( $request['ids'] ) ? array_map( 'absint', (array) $request['ids'] ) : [];
        $post_type = isset( $request['post_type'] ) ? sanitize_text_field( $request['post_type'] ) : 'product';

        $results = [];
        foreach ( $ids as $id ) {
            $post = get_post( $id );
            if ( $post && $post->post_type === $post_type && $post->post_status === 'publish' ) {
                $results[] = [
                    'id'   => $id,
                    'text' => $post->post_title,
                ];
            }
        }

        return rest_ensure_response( [ 'results' => $results ] );
    }
}
