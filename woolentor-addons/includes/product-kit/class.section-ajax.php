<?php
namespace WooLentor\Product_Kit;

/**
 * The one endpoint every product section pages and switches tabs through.
 *
 * It does four things and nothing else: check the nonce, check the visitor may read the post, ask
 * the registry who owns the section, and package what the owner draws. It never touches a query
 * and never knows what a card looks like.
 *
 * The browser sends an address, never a query:
 *
 *     provider   which builder saved this section
 *     post_id    which post it was saved on
 *     section_id which element or block it is
 *     tab        which tab, or absent for a section with no tab row
 *     paged      which page
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Section_Ajax {

    const ACTION = 'woolentor_product_section';
    const NONCE  = 'woolentor_product_section';

    public static function init() {
        add_action( 'wp_ajax_' . self::ACTION, [ __CLASS__, 'handle' ] );
        add_action( 'wp_ajax_nopriv_' . self::ACTION, [ __CLASS__, 'handle' ] );
    }

    /**
     * The attributes a section carries so the browser can call back.
     *
     * Returns an empty string when nothing on the page can call back — no tab row and no AJAX
     * pager — so a plain section carries no nonce and no endpoint surface at all.
     *
     * @param  array $args {
     *     @type string $provider
     *     @type int    $post_id
     *     @type string $section_id
     *     @type bool   $enabled
     *     @type bool   $filters     Section responds to the Product Filter module. Marks the
     *                               section for the shared script and, on its own, is reason
     *                               enough to carry the address.
     * }
     * @return string
     */
    public static function attrs( array $args ) {
        $args = wp_parse_args( $args, [
            'provider'   => '',
            'post_id'    => 0,
            'section_id' => '',
            'enabled'    => true,
            'filters'    => false,
        ] );

        $enabled = $args['enabled'] || $args['filters'];

        if ( ! $enabled || ! $args['post_id'] || '' === $args['section_id'] || '' === $args['provider'] ) {
            return '';
        }

        return ' data-wl-section'
            . ' data-wl-section-provider="' . esc_attr( sanitize_key( $args['provider'] ) ) . '"'
            . ' data-wl-section-post="' . absint( $args['post_id'] ) . '"'
            . ' data-wl-section-id="' . esc_attr( $args['section_id'] ) . '"'
            . ' data-wl-section-nonce="' . esc_attr( wp_create_nonce( self::NONCE ) ) . '"'
            . ( $args['filters'] ? ' data-wl-section-filters' : '' );
    }

    /**
     * @return void
     */
    public static function handle() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::NONCE ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Security check failed', 'woolentor' ) ] );
        }

        $provider   = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : '';
        $post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $section_id = isset( $_POST['section_id'] ) ? sanitize_text_field( wp_unslash( $_POST['section_id'] ) ) : '';
        $paged      = isset( $_POST['paged'] ) ? max( 1, absint( $_POST['paged'] ) ) : 1;

        // -1 means "this section has no tab row". Sending no tab at all is not the same as sending
        // tab zero, so an absent value is never defaulted to it.
        $tab = ( isset( $_POST['tab'] ) && '' !== $_POST['tab'] ) ? absint( $_POST['tab'] ) : -1;

        if ( ! $post_id || '' === $section_id || ! Section_Registry::has_provider( $provider ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Missing section reference', 'woolentor' ) ] );
        }

        // Only content a visitor could already read. Without this a guessed post id would render a
        // draft or a private page's section.
        if ( 'publish' !== get_post_status( $post_id ) && ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Section not available', 'woolentor' ) ] );
        }

        $section = Section_Registry::resolve( $provider, $post_id, $section_id );

        if ( ! $section ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Section not found', 'woolentor' ) ] );
        }

        // The Product Filter module's selection, when the visitor has one. This is the only part of
        // the request that is not an address — and it is not a query either: it narrows the
        // section's own saved query through the module's hook and can never widen it.
        $settings = $section['settings'];

        if ( isset( $_POST['filters'] ) ) {
            $settings = Filter::apply( $settings, Filter::sanitize( wp_unslash( $_POST['filters'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised inside Filter::sanitize().
        }

        if ( isset( $_POST['termobj'] ) && is_array( $_POST['termobj'] ) ) {
            $settings = Filter::apply_queried_object( $settings, wp_unslash( $_POST['termobj'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised and verified inside apply_queried_object().
        }

        $result = call_user_func( $section['render'], $settings, [
            'tab'        => $tab,
            'paged'      => $paged,
            'post_id'    => $post_id,
            'section_id' => $section_id,
        ] );

        if ( ! is_array( $result ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Section could not be rendered', 'woolentor' ) ] );
        }

        wp_send_json_success( [
            'html'       => (string) ( $result['html'] ?? '' ),
            'pagination' => (string) ( $result['pagination'] ?? '' ),
        ] );
    }
}
