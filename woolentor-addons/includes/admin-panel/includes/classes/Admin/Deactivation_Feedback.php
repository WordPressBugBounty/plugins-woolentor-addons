<?php
namespace WoolentorOptions\Admin;

// If this file is accessed directly, exit.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Deactivation_Feedback {

    public $PROJECT_NAME = 'ShopLentor';
    public $PROJECT_TYPE = 'wordpress-plugin';
    public $PROJECT_VERSION = WOOLENTOR_VERSION;
    public $PROJECT_SLUG = 'woolentor-addons'; // Without plugin main file.
    public $PROJECT_PRO_SLUG = 'woolentor-addons-pro/woolentor_addons_pro.php';
    public $PROJECT_PRO_ACTIVE;
    public $PROJECT_PRO_INSTALL;
    public $PROJECT_PRO_VERSION;
    public $DATA_CENTER = 'https://exit-feedback.hasthemes.com/w/46df05fb-d342-4d1f-975b-b0f5bb316367';
    public $WEBHOOK_SECRET = '5e257224ca07abad5299d028349b655099a5a8d88d3b526f713010aa64b6945a';

    public function init() {
        $this->PROJECT_PRO_ACTIVE = $this->is_pro_plugin_active();
        $this->PROJECT_PRO_INSTALL = $this->is_pro_plugin_installed();
        $this->PROJECT_PRO_VERSION = $this->get_pro_version();
        
        add_action( 'admin_footer', [ $this, 'deactivation_feedback' ] );
        add_action( 'wp_ajax_woolentor_deactivation_feedback', [ $this, 'handle_feedback' ] );
    }
    
    public function deactivation_feedback() {
        // Only show on plugins page
        $screen = get_current_screen();
        if ($screen->id !== 'plugins') {
            return;
        }

        $this->deactivation_form_html();
    }
    
    /**
     * Handle AJAX feedback submission
     */
    public function handle_feedback() {
        // Add nonce verification
        if ( !check_ajax_referer('woolentor_deactivation_nonce', 'nonce', false) ) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        if(!current_user_can( 'administrator' )) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        // Sanitize and prepare data
        $reason = sanitize_text_field($_POST['reason']);
        $message = sanitize_textarea_field($_POST['message']);

        // Prepare data for Pabbly
        $data = array_merge(
            [
                'deactivate_reason' => $reason,
                'deactivate_message' => $message,
            ],
            $this->get_data(),
        );

        $body = wp_json_encode( $data );

        $site_url = wp_parse_url( home_url(), PHP_URL_HOST );
        $headers = [
            'user-agent'   => $this->PROJECT_NAME . '/' . md5( $site_url ) . ';',
            'Content-Type' => 'application/json',
        ];

        $signature = $this->generate_signature( $body );
        if ( ! empty( $signature ) ) {
            $headers['X-Webhook-Signature'] = $signature;
        }

        // Send data to Pabbly
        $response = wp_remote_post($this->DATA_CENTER, [
            'method'      => 'POST',
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => false,
            'sslverify'   => false,
            'headers'     => $headers,
            'body'        => $body,
            'cookies'     => []
        ]);

        // Check for errors
        if (!is_wp_error($response)) {
            wp_send_json_success('Feedback submitted successfully');
        } else {
            wp_send_json_error('Failed to submit feedback: ' . $response->get_error_message());
        }
    }

    public function get_data() {

        // Get plugin specific information
        $project = [
            'name'          => $this->PROJECT_NAME,
            'type'          => $this->PROJECT_TYPE,
            'version'       => $this->PROJECT_VERSION,
            'pro_active'    => $this->PROJECT_PRO_ACTIVE,
            'pro_installed' => $this->PROJECT_PRO_INSTALL,
            'pro_version'   => $this->PROJECT_PRO_VERSION,
        ];

        $site_title = get_bloginfo( 'name' );
        $site_url = wp_parse_url( home_url(), PHP_URL_HOST );
        $admin_email = get_option( 'admin_email' );

        $admin_first_name = '';
        $admin_last_name = '';
        $admin_display_name = '';

        $users = get_users( array(
            'role'    => 'administrator',
            'orderby' => 'ID',
            'order'   => 'ASC',
            'number'  => 1,
            'paged'   => 1,
        ) );

        $admin_user = ( is_array ( $users ) && isset ( $users[0] ) && is_object ( $users[0] ) ) ? $users[0] : null;

        if ( ! empty( $admin_user ) ) {
            $admin_first_name = ( isset( $admin_user->first_name ) ? $admin_user->first_name : '' );
            $admin_last_name = ( isset( $admin_user->last_name ) ? $admin_user->last_name : '' );
            $admin_display_name = ( isset( $admin_user->display_name ) ? $admin_user->display_name : '' );
        }

        $ip_address = $this->get_ip_address();

        $data = [
            'project'            => $project,
            'site_title'         => $site_title,
            'site_address'       => $site_url,
            'site_url'           => $site_url,
            'admin_email'        => $admin_email,
            'admin_first_name'   => $admin_first_name,
            'admin_last_name'    => $admin_last_name,
            'admin_display_name' => $admin_display_name,
            'server_info'        => $this->get_server_info(),
            'wordpress_info'     => $this->get_wordpress_info(),
            'plugins_count'      => $this->get_plugins_count(),
            'ip_address'         => $ip_address,
            'country_name'       => $this->get_country_from_ip( $ip_address ),
            'plugin_list'       => $this->get_active_plugins(),
        ];

        return $data;
    }

    /**
     * Get server info.
     */
    private function get_server_info() {
        global $wpdb;

        $software = ( isset ( $_SERVER['SERVER_SOFTWARE'] ) && !empty ( $_SERVER['SERVER_SOFTWARE'] ) ) ? $_SERVER['SERVER_SOFTWARE'] : '';
        $php_version = function_exists ( 'phpversion' ) ? phpversion () : '';
        $mysql_version = method_exists ( $wpdb, 'db_version' ) ? $wpdb->db_version () : '';

        $server_info = array(
            'software'             => $software,
            'php_version'          => $php_version,
            'mysql_version'        => $mysql_version,
        );

        return $server_info;
    }

    /**
     * Get wordpress info.
     */
    private function get_wordpress_info() {
        $wordpress_info = [];

        $debug_mode = ( defined ( 'WP_DEBUG' ) && WP_DEBUG ) ? 'yes' : 'no';
        $locale = get_locale();
        $version = get_bloginfo( 'version' );
        $theme_slug = get_stylesheet();

        $wordpress_info = [
            'debug_mode'   => $debug_mode,
            'locale'       => $locale,
            'version'      => $version,
            'theme_slug'   => $theme_slug,
        ];

        $theme = wp_get_theme( $wordpress_info['theme_slug'] );

        if ( is_object( $theme ) && ! empty( $theme ) && method_exists( $theme, 'get' ) ) {
            $theme_name    = $theme->get( 'Name' );
            $theme_version = $theme->get( 'Version' );
            $theme_uri     = $theme->get( 'ThemeURI' );
            $theme_author  = $theme->get( 'Author' );

            $wordpress_info = array_merge( $wordpress_info, [
                'theme_name'    => $theme_name,
                'theme_version' => $theme_version,
                'theme_uri'     => $theme_uri,
                'theme_author'  => $theme_author,
            ] );
        }

        return $wordpress_info;
    }

    /**
     * Get users count.
     */
    private function get_users_count() {
        $users_count = [];

        $users_count_data = count_users();

        $total_users = isset ( $users_count_data['total_users'] ) ? $users_count_data['total_users'] : 0;
        $avail_roles = isset ( $users_count_data['avail_roles'] ) ? $users_count_data['avail_roles'] : [];

        $users_count['total'] = $total_users;

        if ( is_array( $avail_roles ) && ! empty( $avail_roles ) ) {
            foreach ( $avail_roles as $role => $count ) {
                $users_count[ $role ] = $count;
            }
        }

        return $users_count;
    }

    /**
     * Get plugins count.
     */
    private function get_plugins_count() {
        $total_plugins_count = 0;
        $active_plugins_count = 0;
        $inactive_plugins_count = 0;

        $plugins = get_plugins();
        $plugins = is_array ( $plugins ) ? $plugins : [];

        $active_plugins = get_option( 'active_plugins', [] );
        $active_plugins = is_array ( $active_plugins ) ? $active_plugins : [];

        if ( ! empty( $plugins ) ) {
            foreach ( $plugins as $key => $data ) {
                if ( in_array( $key, $active_plugins, true ) ) {
                    $active_plugins_count++;
                } else {
                    $inactive_plugins_count++;
                }

                $total_plugins_count++;
            }
        }

        $plugins_count = [
            'total'    => $total_plugins_count,
            'active'   => $active_plugins_count,
            'inactive' => $inactive_plugins_count,
        ];

        return $plugins_count;
    }

    /**
     * Get active plugins.
     */
    private function get_active_plugins() {
        $active_plugins = get_option('active_plugins');
        $all_plugins = get_plugins();
        $active_plugin_string = '';
        foreach($all_plugins as $plugin_path => $plugin) {
            if ( ! in_array($plugin_path, $active_plugins) ) {
                continue;
            }
            $active_plugin_string .= sprintf(
                "%s (v%s) - %s | ",
                $plugin['Name'],
                $plugin['Version'],
                'Active'
            );
        }
        $active_plugin_string = rtrim($active_plugin_string, ' | ');
        return $active_plugin_string;
    }

    /**
     * Get IP Address
     */
    private function get_ip_address() {
        $response = wp_remote_get( 'https://icanhazip.com/' );

        if ( is_wp_error( $response ) ) {
            return '';
        }

        $ip_address = wp_remote_retrieve_body( $response );
        $ip_address = trim( $ip_address );

        if ( ! filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
            return '';
        }

        return $ip_address;
    }

    /**
     * Get Country Form ID Address
     */
    private function get_country_from_ip( $ip_address ) {
        $api_url = 'http://ip-api.com/json/' . $ip_address;
    
        // Fetch data from the API
        $response = wp_remote_get( $api_url );
    
        if ( is_wp_error( $response ) ) {
            return 'Error';
        }
    
        // Decode the JSON response
        $data = json_decode( wp_remote_retrieve_body($response) );
    
        if ($data && $data->status === 'success') {
            return $data->country;
        } else {
            return 'Unknown';
        }
    }

    /**
     * Generate HMAC-SHA256 signature for webhook payload.
     */
    private function generate_signature( $payload ) {
        if ( empty( $this->WEBHOOK_SECRET ) ) {
            return '';
        }
        return 'sha256=' . hash_hmac( 'sha256', $payload, $this->WEBHOOK_SECRET );
    }

    /**
     * Is pro active.
     */
    private function is_pro_plugin_active() {
        $result = is_plugin_active( $this->PROJECT_PRO_SLUG );
        $result = ( true === $result ) ? 'yes' : 'no';
        return $result;
    }

    /**
     * Is pro installed.
     */
    private function is_pro_plugin_installed() {
        $plugins = get_plugins();
        $result = isset ( $plugins[$this->PROJECT_PRO_SLUG] ) ? 'yes' : 'no';
        return $result;
    }

    /**
     * Get pro version.
     */
    private function get_pro_version() {
        $plugins = get_plugins();
        $data = ( isset ( $plugins[$this->PROJECT_PRO_SLUG] ) && is_array ( $plugins[$this->PROJECT_PRO_SLUG] ) ) ? $plugins[$this->PROJECT_PRO_SLUG] : [];
        $version = isset ( $data['Version'] ) ? sanitize_text_field ( $data['Version'] ) : '';
        return $version;
    }

    /**
     * Deactivation form html.
     */
    public function deactivation_form_html() {
        require_once( WOOLENTOROPT_INCLUDES . '/templates/deactive-feedback.php' );
    }

}
