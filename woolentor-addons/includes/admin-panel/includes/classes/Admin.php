<?php
namespace WoolentorOptions;

/**
 * Admin class
 */
class Admin {

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->remove_all_notices();
        $this->includes();
        $this->init();
    }

     /**
     * Include the controller classes
     *
     * @return void
     */
    private function includes() {
        if ( !class_exists( __NAMESPACE__ . '\Admin\Menu'  ) ) {
            require_once __DIR__ . '/Admin/Menu.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Admin\Options_Field'  ) ) {
            require_once __DIR__ . '/Admin/Options_field.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Admin\Dashboard_Widget'  ) ) {
            require_once __DIR__ . '/Admin/Dashboard_Widget.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Admin\Diagnostic_Data'  ) ) {
            require_once __DIR__ . '/Admin/Diagnostic_Data.php';
        }
    }

    /**
     * Admin Initilize
     *
     * @return void
     */
    public function init() {
        (new Admin\Menu())->init();
        (new Admin\Dashboard_Widget())->init();

        // Add proxy actions
        add_action('wp_ajax_woolentor_proxy_image', [$this, 'woolentor_template_proxy']);
        add_action('wp_ajax_nopriv_woolentor_proxy_image', [$this, 'woolentor_template_proxy']);
    }

    /**
     * [remove_all_notices] remove addmin notices
     * @return [void]
     */
    public function remove_all_notices(){
        add_action('in_admin_header', function (){
            $current_screen = get_current_screen();
            $hide_screen = ['shoplentor_page_woolentor','edit-woolentor-template','woolentor-template'];
            if(  in_array( $current_screen->id, $hide_screen) ){
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
            }
        }, 1000);
    }

    /**
     * [woolentor_template_proxy] proxy image
     * @return [void]
     */
    public function woolentor_template_proxy() {
        if (!isset($_GET['url']) || empty($_GET['url'])) {
            wp_send_json_error('No URL provided');
            return;
        }
        
        $url = esc_url_raw($_GET['url']);
        
        // Ensure it's only from trusted domains
        if (strpos($url, 'library.shoplentor.com') === false) {
            wp_send_json_error('Invalid domain');
            return;
        }
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
            return;
        }
        
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        
        // Set appropriate headers
        header('Content-Type: ' . $content_type);
        echo wp_remote_retrieve_body($response);
        exit;
    }

}