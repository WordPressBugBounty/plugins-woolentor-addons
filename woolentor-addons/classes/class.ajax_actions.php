<?php  
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Woolentor_Ajax_Action{

	/**
	 * [$instance]
	 * @var null
	 */
	private static $instance = null;

	/**
	 * [instance]
	 * @return [Woolentor_Ajax_Action]
	 */
    public static function instance(){
        if( is_null( self::$instance ) ){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * [__construct]
     */
    function __construct(){

        // For Ajax Add to cart
		add_action( 'wp_ajax_woolentor_insert_to_cart', [ $this, 'insert_to_cart' ] );
		add_action( 'wp_ajax_nopriv_woolentor_insert_to_cart', [ $this, 'insert_to_cart' ] );

        // For Single Product ajax add to cart
        add_action( 'wp_ajax_woolentor_add_to_cart_single_product', [ $this, 'add_to_cart_from_single_product' ] );
		add_action( 'wp_ajax_nopriv_woolentor_add_to_cart_single_product', [ $this, 'add_to_cart_from_single_product' ] );

        // Sugest Price Elementor addon
        add_action( 'wp_ajax_woolentor_suggest_price_action', [$this, 'suggest_price'] );
        add_action( 'wp_ajax_nopriv_woolentor_suggest_price_action', [$this, 'suggest_price'] );

        // Load more products for product grid
        add_action( 'wp_ajax_woolentor_load_more_products', [$this, 'load_more_products'] );
        add_action( 'wp_ajax_nopriv_woolentor_load_more_products', [$this, 'load_more_products'] );

    }

    /**
     * [insert_to_cart] Insert add to cart
     * @return [JSON]
     */
    public function insert_to_cart(){

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woolentor_psa_nonce' ) ){
            $errormessage = array(
                'message'  => __('Nonce Varification Faild !','woolentor')
            );
            wp_send_json_error( $errormessage );
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( ! isset( $_POST['product_id'] ) ) {
            return;
        }

        $product_id         = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
        $quantity           = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
        $variation_id       = !empty( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $variations         = !empty( $_POST['variations'] ) ? array_map( 'sanitize_text_field', $_POST['variations'] ) : array();
        $passed_validation  = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );
        $product_status     = get_post_status( $product_id );

        if ( $passed_validation && \WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) && 'publish' === $product_status ) {
            do_action( 'woocommerce_ajax_added_to_cart', $product_id );
            if ( 'yes' === get_option('woocommerce_cart_redirect_after_add') ) {
                wc_add_to_cart_message( array( $product_id => $quantity ), true );
            }
            \WC_AJAX::get_refreshed_fragments();
        } else {
            $data = array(
                'error' => true,
                'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
            );
            wp_send_json_error( $data );
        }
        wp_send_json_success();
        
    }

    /**
     * Ajax Actin For Single Product Add to Cart
     */
    public function add_to_cart_from_single_product() {

        add_action( 'wp_loaded', [ 'WC_Form_Handler', 'add_to_cart_action' ], 20 );

        $wc_notice = wc_get_notices();
        if ( is_callable( [ 'WC_AJAX', 'get_refreshed_fragments' ] ) && ! isset( $wc_notice['error'] ) ) {
            \WC_AJAX::get_refreshed_fragments();
        }

        wp_send_json_success();
    }

    /**
     * [single_product_insert_to_cart] Single product ajax add to cart callable function
     * @return [JSON]
     * @todo Delete After 2 Majon Release
     */
    public function single_product_insert_to_cart(){
        
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( ! isset( $_POST['product_id'] ) ) {
            return;
        }

        $product_id         = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
        $product            = wc_get_product( $product_id );
        $quantity           = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
        $variation_id       = !empty( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $variations         = !empty( $_POST['variations'] ) ? array_map( 'sanitize_text_field', json_decode( stripslashes( $_POST['variations'] ), true ) ) : array();
        $passed_validation  = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );
        $product_status     = get_post_status( $product_id );

        $cart_item_data = $_POST['alldata'];

        if ( $passed_validation && 'publish' === $product_status ) {

            if( count( $variations ) == 0 ){
                \WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );
            }

            do_action( 'woocommerce_ajax_added_to_cart', $product_id );
            if ( 'yes' === get_option('woocommerce_cart_redirect_after_add') ) {
                wc_add_to_cart_message( [ $product_id => $quantity ], true );
            }
            \WC_AJAX::get_refreshed_fragments();
        } else {
            $data = [
                'error' => true,
                'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
            ];
            wp_send_json_error( $data );
        }
        wp_send_json_success();
        
    }

    /**
     * Email Send for suggest_price
     * @return [void]
     */
    public function suggest_price(){
        $response = [
            'error' => false,
        ];

        // Verify nonce
        if ( !isset( $_POST['woolentor_suggest_price_nonce_field'] ) || !wp_verify_nonce( $_POST['woolentor_suggest_price_nonce_field'], 'woolentor_suggest_price_nonce' ) ){
            $response['error'] = true;
            $response['message'] = esc_html__('Sorry, your nonce verification failed.','woolentor');
            wp_send_json_error( $response );
        }

        // Get and validate form token (this links to server-stored recipient email)
        $form_token = isset( $_POST['form_token'] ) ? sanitize_text_field( $_POST['form_token'] ) : '';
        if ( empty( $form_token ) ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Invalid form submission.','woolentor');
            wp_send_json_error( $response );
        }

        // Retrieve recipient email from server-side storage (NOT from user input)
        $stored_data = get_transient( 'woolentor_suggest_price_' . $form_token );
        if ( false === $stored_data || !is_array( $stored_data ) ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Form session expired. Please refresh and try again.','woolentor');
            wp_send_json_error( $response );
        }

        // Get recipient from server-stored data (secure - not user controlled)
        $allowed_recipient = isset( $stored_data['recipient_email'] ) ? sanitize_email( $stored_data['recipient_email'] ) : '';
        $stored_product_id = isset( $stored_data['product_id'] ) ? absint( $stored_data['product_id'] ) : 0;

        // Validate stored recipient
        if ( empty( $allowed_recipient ) || ! is_email( $allowed_recipient ) ) {
            // Fallback to admin email if stored email is invalid
            $allowed_recipient = get_option( 'admin_email' );
        }

        // Get and validate product
        $product_id = absint( $_POST['product_id'] ?? 0 );

        // Verify product_id matches stored product_id (prevents manipulation)
        if ( $product_id !== $stored_product_id ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Invalid product.','woolentor');
            wp_send_json_error( $response );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Invalid product.','woolentor');
            wp_send_json_error( $response );
        }

        // Get user messages from widget settings (stored server-side)
        $msg_success = isset( $stored_data['msg_success'] ) ? sanitize_text_field( $stored_data['msg_success'] ) : esc_html__('Thank you for contacting us.','woolentor');
        $msg_error = isset( $stored_data['msg_error'] ) ? sanitize_text_field( $stored_data['msg_error'] ) : esc_html__('Something went wrong. Please try again.','woolentor');

        // Validate sender's name
        $name = isset( $_POST['wlname'] ) ? sanitize_text_field( $_POST['wlname'] ) : '';
        if ( empty( $name ) ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Name is required.','woolentor');
            wp_send_json_error( $response );
        }
        // Limit name length
        if ( strlen( $name ) > 100 ) {
            $name = substr( $name, 0, 100 );
        }

        // Validate sender's email (sanitize to prevent CRLF injection)
        $email = isset( $_POST['wlemail'] ) ? sanitize_email( trim( $_POST['wlemail'] ) ) : '';
        if ( empty( $email ) ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Email is required.','woolentor');
            wp_send_json_error( $response );
        }
        if ( ! is_email( $email ) ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Invalid email address.','woolentor');
            wp_send_json_error( $response );
        }

        // Validate and sanitize message
        $message = isset( $_POST['wlmessage'] ) ? wp_strip_all_tags( $_POST['wlmessage'] ) : '';
        if ( empty( $message ) ) {
            $response['error'] = true;
            $response['message'] = esc_html__('Message is required.','woolentor');
            wp_send_json_error( $response );
        }
        // Limit message length to prevent abuse
        if ( strlen( $message ) > 1000 ) {
            $message = substr( $message, 0, 1000 );
        }

        // Build subject from actual product name (not user input)
        $subject = sprintf(
            /* translators: %s: Product name */
            esc_html__( 'Suggest Price For - %s', 'woolentor' ),
            $product->get_name()
        );

        // Build email body with sender info
        $email_body = sprintf(
            /* translators: 1: Sender name, 2: Sender email, 3: Product name, 4: Message */
            esc_html__( "Name: %1\$s\nEmail: %2\$s\nProduct: %3\$s\n\nMessage:\n%4\$s", 'woolentor' ),
            $name,
            $email,
            $product->get_name(),
            $message
        );

        // Set headers with Reply-To (sanitized email prevents header injection)
        $headers = [
            'Reply-To: ' . $name . ' <' . $email . '>'
        ];

        // Send email to the server-stored recipient (NOT user-controlled)
        $mail_sent_status = wp_mail( $allowed_recipient, $subject, $email_body, $headers );

        // Delete the old transient after use (one-time use token)
        delete_transient( 'woolentor_suggest_price_' . $form_token );

        if( $mail_sent_status ) {
            $response['error'] = false;
            $response['message'] = esc_html( $msg_success );

            // Generate a new token for subsequent submissions (without page refresh)
            $new_token = wp_generate_password( 32, false, false );

            // Store the same data with new token
            $new_transient_data = [
                'recipient_email' => $allowed_recipient,
                'product_id'      => $product_id,
                'msg_success'     => $msg_success,
                'msg_error'       => $msg_error,
            ];
            set_transient( 'woolentor_suggest_price_' . $new_token, $new_transient_data, HOUR_IN_SECONDS );

            // Return new token to client for form update
            $response['new_token'] = $new_token;

        } else {
            $response['error'] = true;
            $response['message'] = esc_html( $msg_error );

            // On error, regenerate token as well (old one is already deleted)
            $new_token = wp_generate_password( 32, false, false );
            $new_transient_data = [
                'recipient_email' => $allowed_recipient,
                'product_id'      => $product_id,
                'msg_success'     => $msg_success,
                'msg_error'       => $msg_error,
            ];
            set_transient( 'woolentor_suggest_price_' . $new_token, $new_transient_data, HOUR_IN_SECONDS );
            $response['new_token'] = $new_token;
        }

        wp_send_json_success( $response );
    }

    /**
     * Ajax Callback for Load more and Infinite scrool
     *
     * @return void
     */
    public function load_more_products() {

        // Load dependencies
        if ( ! class_exists( 'WooLentor_Product_Grid_Base' ) ) {
            require_once WOOLENTOR_ADDONS_PL_PATH . 'includes/addons/product-grid/base/class.product-grid-base.php';
        }

        $product_grid_base = new WooLentor_Product_Grid_Base();

        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! (wp_verify_nonce( $_POST['nonce'], 'woolentor_psa_nonce' ) || wp_verify_nonce( $_POST['nonce'], 'woolentorblock-nonce' )) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'woolentor' ) ) );
        }

        // Get settings and page number
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 2;

        $setting_data = isset( $_POST['settings'] ) ? (is_string($_POST['settings']) ? stripslashes( $_POST['settings'] ) : '' ) : '';
        $setting_data = json_decode( $setting_data, true );
        $view_layout = isset( $_POST['viewlayout'] ) ? $_POST['viewlayout'] : '';

        if(!empty($view_layout)){
            $setting_data['layout'] = $view_layout;
        }

        $setting_data['paged'] = $page;

        ob_start();
        $product_grid_base->render_items( $setting_data, true );
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html' => $html,
            'current_page' => $page
        ));

    }

}

Woolentor_Ajax_Action::instance();
