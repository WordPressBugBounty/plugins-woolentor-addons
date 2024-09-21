<?php

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

class Woolentor_Admin_Init{

    /**
     * Parent Menu Page Slug
     */
    const MENU_PAGE_SLUG = 'woolentor_page';

    /**
     * Menu capability
     */
    const MENU_CAPABILITY = 'manage_options';

    /**
     * [$parent_menu_hook] Parent Menu Hook
     * @var string
     */
    static $parent_menu_hook = '';
    
    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Woolentor_Admin_Init]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        $this->remove_all_notices();
        $this->include();
        $this->init();
    }

    /**
     * [init] Assets Initializes
     * @return [void]
     */
    public function init(){

        add_action( 'admin_menu', [ $this, 'add_menu' ], 25 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'dequeue_assets' ], 999 );
        Woolentor_Admin_Fields_Manager::instance()->init();

        // Dashboard Widget.
		add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widget' ], 9999 );

        add_action( 'admin_footer', [ $this, 'print_module_setting_popup' ], 99 );

        // Upgrade Pro Menu
        if( !is_plugin_active('woolentor-addons-pro/woolentor_addons_pro.php') ){
            add_action( 'admin_menu', [$this, 'add_other_menu'], 228 );
            add_action('admin_head', [ $this, 'admin_menu_item_adjust'] );
            add_action('admin_head', [ $this, 'enqueue_admin_head_scripts'], 11 );
        }

        add_action( 'wp_ajax_woolentor_save_opt_data', [ $this, 'save_data' ] );
        add_action( 'wp_ajax_woolentor_module_data', [ $this, 'module_data' ] );

        // Ajax action for Repeater Custom Button
        add_action( 'wp_ajax_woolentor_repeater_custom_action',[ $this, 'repeater_custom_action' ] );

    }

    /**
     * [include] Load Necessary file
     * @return [void]
     */
    public function include(){
        require_once( WOOLENTOR_ADDONS_PL_PATH .'includes/api.php');
        require_once('include/diagnostic-data.php');
        require_once('include/admin_field-manager.php');
        require_once('include/admin_fields.php');
        if( is_plugin_active('woolentor-addons-pro/woolentor_addons_pro.php') && defined( "WOOLENTOR_ADDONS_PL_PATH_PRO" ) && file_exists( WOOLENTOR_ADDONS_PL_PATH_PRO.'includes/admin/admin_fields.php' ) ){
            require_once WOOLENTOR_ADDONS_PL_PATH_PRO.'includes/admin/admin_fields.php';
        }
        require_once('include/template-library.php');
        require_once('include/class.extension-manager.php');
    }

    /**
     * [add_menu] Admin Menu
     */
    public function add_menu(){

        self::$parent_menu_hook = add_menu_page(
            esc_html__( 'ShopLentor', 'woolentor' ),
            esc_html__( 'ShopLentor', 'woolentor' ), 
            self::MENU_CAPABILITY, 
            self::MENU_PAGE_SLUG, 
            '', 
            WOOLENTOR_ADDONS_PL_URL.'includes/admin/assets/images/icons/menu-bar_20x20.png',
            '58.7'
        );

        add_submenu_page(
            self::MENU_PAGE_SLUG, 
            esc_html__( 'Settings', 'woolentor' ),
            esc_html__( 'Settings', 'woolentor' ),
            'manage_options', 
            'woolentor', 
            [ $this, 'plugin_page' ] 
        );

        // Remove Parent Submenu
        remove_submenu_page( 'woolentor_page','woolentor_page' );
        

    }

    /**
     * [add_other_menu] Admin Menu
     */
    public function add_other_menu(){
        add_submenu_page(
            self::MENU_PAGE_SLUG, 
            esc_html__('Upgrade to Pro', 'woolentor'),
            esc_html__('Upgrade to Pro', 'woolentor'), 
            'manage_options', 
            'https://woolentor.com/pricing/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free'
        );
    }

    // Add Class For pro Menu Item
    public function admin_menu_item_adjust(){
        global $submenu;

		// Check WooLentor Menu page exist or not
		if ( ! isset( $submenu['woolentor_page'] ) ) {
			return;
		}

        $position = key(
			array_filter( $submenu['woolentor_page'],  
				static function( $item ) {
					return strpos( $item[2], 'https://woolentor.com/pricing/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free' ) !== false;
				}
			)
		);

        if ( isset( $submenu['woolentor_page'][ $position ][4] ) ) {
			$submenu['woolentor_page'][ $position ][4] .= ' woolentor-upgrade-pro';
		} else {
			$submenu['woolentor_page'][ $position ][] = 'woolentor-upgrade-pro';
		}
    }

    // Add Custom scripts for pro menu item
    public function enqueue_admin_head_scripts(){
        $styles = '';
        $scripts = '';

        $styles .= '#adminmenu #toplevel_page_woolentor_page a.woolentor-upgrade-pro { font-weight: 600; background-color: #f56640; color: #ffffff; display: block; text-align: center;}';
        $scripts .= 'jQuery(document).ready( function($) {
			$("#adminmenu #toplevel_page_woolentor_page a.woolentor-upgrade-pro").attr("target","_blank");  
		});';
		
		printf( '<style>%s</style>', $styles ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<script>%s</script>', $scripts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * [enqueue_scripts] Add Scripts Base Menu Slug
     * @param  [string] $hook
     * @return [void]
     */
    public function enqueue_scripts( $hook  ) {
        if( $hook === 'shoplentor_page_woolentor' || $hook === 'shoplentor_page_woolentor_templates' || $hook === 'shoplentor_page_woolentor_extension'){
            wp_enqueue_style('woolentor-sweetalert');
            wp_enqueue_style('woolentor-admin');
            wp_enqueue_script('woolentor-install-manager');

            wp_enqueue_style( 'simple-line-icons-wl' );
            wp_enqueue_style(
                'fonticonpicker',
                WOOLENTOR_ADDONS_PL_URL . 'assets/lib/iconpicker/css/jquery.fonticonpicker.min.css', 
                array(),
                WOOLENTOR_VERSION 
            );

            wp_enqueue_style(
                'fonticonpicker-bootstrap',
                WOOLENTOR_ADDONS_PL_URL . 'assets/lib/iconpicker/css/jquery.fonticonpicker.bootstrap.min.css', 
                array(),
                WOOLENTOR_VERSION 
            );

            // JS
            wp_enqueue_script( 
                'fonticonpicker', 
                WOOLENTOR_ADDONS_PL_URL . 'assets/lib/iconpicker/js/jquery.fonticonpicker.min.js', 
                array( 'jquery' ), 
                WOOLENTOR_VERSION, 
                TRUE
            );

            wp_enqueue_script( 
                'select2', 
                WOOLENTOR_ADDONS_PL_URL . 'includes/admin/assets/lib/js/select2.min.js', 
                array( 'jquery' ), 
                WOOLENTOR_VERSION, 
                TRUE
            );


            wp_enqueue_script('woolentor-jquery-interdependencies');
            wp_enqueue_script('woolentor-condition');
            wp_enqueue_script('woolentor-sweetalert');
            wp_enqueue_script('woolentor-admin-main');

            wp_localize_script( 
                'woolentor-admin-main', 
                'woolentor_fields', 
                [
                    'iconset' => Woolentor_Icon_List::icon_sets(),
                ]
            );
        }
    }

    /**
     * [dequeue_assets] dequeue Scripts / Style Base Menu Slug
     * @param  [string] $hook
     * @return [void]
     */
    public function dequeue_assets( $hook ){
        if( $hook === 'shoplentor_page_woolentor' || $hook === 'shoplentor_page_woolentor_templates' || $hook === 'shoplentor_page_woolentor_extension'){
            if ( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) {
                wp_dequeue_script('wc-enhanced-select');
                wp_deregister_script('wc-enhanced-select');
            }
            // UNISEND for WooCommerce Plugin script dequeue
            if ( wp_script_is( 'woo-lithuaniapost', 'enqueued' ) ) {
                wp_dequeue_script('woo-lithuaniapost');
                wp_deregister_script('woo-lithuaniapost');
            }
        }
    }

    /**
     * [dashboard_widget] Register Dashboard Widget
     * @return [void]
     */
    public function dashboard_widget() {
		wp_add_dashboard_widget( 
            'hasthemes-dashboard-stories', 
            esc_html__( 'HasThemes Stories', 'woolentor' ), 
            [ $this, 'dashboard_hasthemes_widget' ] 
        );

		// Metaboxes Array.
		global $wp_meta_boxes;

		$dashboard_widget_list = $wp_meta_boxes['dashboard']['normal']['core'];

        $hastheme_dashboard_widget = [
            'hasthemes-dashboard-stories' => $dashboard_widget_list['hasthemes-dashboard-stories']
        ];

        $all_dashboard_widget = array_merge( $hastheme_dashboard_widget, $dashboard_widget_list );

		$wp_meta_boxes['dashboard']['normal']['core'] = $all_dashboard_widget;

	}

    /**
     * [dashboard_hasthemes_widget] Dashboard Stories Widget
     * @return [void]
     */
    public function dashboard_hasthemes_widget() {
        ob_start();
        self::load_template('widget');
        echo ob_get_clean();
    }

    /**
     * [load_template] Template load
     * @param  [string] $template template suffix
     * @return [void]
     */
    private static function load_template( $template ) {
        $tmp_file = WOOLENTOR_ADDONS_PL_PATH . 'includes/admin/templates/dashboard-' . $template . '.php';
        if ( file_exists( $tmp_file ) ) {
            include_once( $tmp_file );
        }
    }

    /**
     * [plugin_page] Load plugin page template
     * @return [void]
     */
    public function plugin_page(){
        if( !is_plugin_active('woolentor-addons-pro/woolentor_addons_pro.php') ){
            $this->offer_notice();
        }
        ?>
        <div class="wrap woolentor-admin-wrapper">
            <div class="woolentor-admin-main-content">
                <?php self::load_template('navs'); ?>
                <div class="woolentor-admin-main-body">
                    <?php self::load_template('welcome'); ?>
                    <?php self::load_template('gutenberg'); ?>
                    <?php self::load_template('settings'); ?>
                    <?php self::load_template('element'); ?>
                    <?php self::load_template('style'); ?>
                    <?php self::load_template('module'); ?>
                    <?php self::load_template('extension'); ?>
                    <?php self::load_template('freevspro'); ?>
                </div>
                <?php self::load_template('popup'); ?>
            </div>
            <?php self::load_template('sidebar'); ?>
            <?php Woolentor_Admin_Fields_Manager::instance()->script(); ?>
        </div>
        <?php
    }

    /**
     * [print_module_setting_popup] addmin_footer Callback
     * @return [void]
     */
    public function print_module_setting_popup(){
        $screen = get_current_screen();
        if ( 'shoplentor_page_woolentor' == $screen->base ) {
            self::load_template('module-setting-popup');
        }
    }

    /**
     * [remove_all_notices] remove addmin notices
     * @return [void]
     */
    public function remove_all_notices(){
        add_action('in_admin_header', function (){
            $current_screen = get_current_screen();
            $hide_screen = ['shoplentor_page_woolentor','shoplentor_page_woolentor_templates'];
            if(  in_array( $current_screen->id, $hide_screen) ){
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
            }
        }, 1000);
    }

    /**
     * [save_data] Wp Ajax Callback
     * @return [JSON|Null]
     */
    public function save_data(){

        if ( ! current_user_can( self::MENU_CAPABILITY ) ) {
            return;
        }

        check_ajax_referer( 'woolentor_save_opt_nonce', 'nonce' );

        $data     = ( !empty( $_POST['data'] ) ? woolentor_clean( $_POST['data'] ) : '' );
        $section  = ( !empty( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : '' );
        $fileds   = ( !empty( $_POST['fileds'] ) ? woolentor_clean( $_POST['fileds'] ) : '' );

        if( empty( $section ) || empty( $fileds ) ){
            return;
        }

        if( empty( $data ) ){
            $data = array();
        }

        if ( false == get_option( $section ) ) {
            add_option( $section );
        }
        
        foreach( $fileds as $filed ){
            if ( array_key_exists( $filed, $data ) ) {
                $value = $data[$filed];
            }else{
                $value = Null;
            }
            $this->update_option( $section, $filed, $value );
        }

        wp_send_json_success([
            'message' => esc_html__( 'Data Saved successfully!', 'woolentor' ),
            'data'    => $data
        ]);

    }

    /**
     * [update_option]
     * @return [void]
     */
    public function update_option( $section, $option_key, $new_value ){
        if( $new_value === Null ){ $new_value = ''; }
        $options_datad = is_array( get_option( $section ) ) ? get_option( $section ) : array();
        $options_datad[$option_key] = $new_value;
        update_option( $section, $options_datad );
    }

    /**
     * [module_data] Wp Ajax Callback
     * @return [JSON|Null]
     */
    public function module_data(){
        check_ajax_referer( 'woolentor_save_opt_nonce', 'nonce' );

        $subaction  = ( !empty( $_POST['subaction'] ) ? sanitize_text_field( $_POST['subaction'] ) : '' );
        $section    = ( !empty( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : '' );
        $fileds     = ( !empty( $_POST['fileds'] ) ? woolentor_clean( $_POST['fileds'] ) : '' );
        $fieldname  = ( !empty( $_POST['fieldname'] ) ? sanitize_text_field( $_POST['fieldname'] ) : '' );

        // Reset Module Data
        if( $subaction === 'reset_data' ){
            if( ! empty( $section ) ){
                delete_option( $section );
            }
        }

        // Get Module data
        if( empty( $section ) || empty( $fileds ) ){
            return;
        }

        $module_fields = Woolentor_Admin_Fields::instance()->fields()['woolentor_others_tabs']['modules'];
        $section_fields = [];
        foreach ( $module_fields as $module ) {
            if( isset( $module['section'] ) && $module['section'] === $section ){
                $section_fields = $module['setting_fields'];
                break;
            }else{
                if( isset( $module['name'] ) && $module['name'] === $fieldname ){
                    $section_fields = $module['setting_fields'];
                    break;
                }
            }
        }

        $response_content = $message = $field_html = '';
        if( $subaction === 'get_data' ){
            foreach( $section_fields as $field ){
                ob_start();
                Woolentor_Admin_Fields_Manager::instance()->add_field( $field, $section );
                $field_html .= ob_get_clean();
            }
            $message = esc_html__( 'Data Fetch successfully!', 'woolentor' );
            $response_content = $field_html;

        }

        wp_send_json_success([
            'message' => $message,
            'content' => $response_content,
            'fields'  => wp_json_encode( $fileds )
        ]);

    }

    /**
     * Repeater Field Custom Button
     */
    public function repeater_custom_action(){

        check_ajax_referer( 'woolentor_save_opt_nonce', 'nonce' );

        $data = woolentor_clean( $_POST['data'] );
        $callback = $data['callback'];
        $module_settings = get_option( $data['option_section'] );
        $dependent_value = !empty( $data['value'] ) ? $data['value'] : woolentor_get_option( $data['option_id'], $data['option_section'], false );

        if ( is_callable( $callback ) ){
            $response_data = call_user_func( $callback, [ 'depend_value' => $dependent_value, 'module_settings' => $module_settings ] );
        }else{
            $response_data = '';
        }

	    wp_send_json_success( $response_data );

    }

    /**
     * Manage Promo Notice for Setting page
     *
     * @return void
     */
    public function offer_notice(){
        if( !isset( \WooLentor\Base::$template_info['notices'] ) || !is_array( \WooLentor\Base::$template_info['notices'] ) ){
            return;
        }

        $notice_info = \WooLentor\Base::$template_info['notices'][0];
        if( isset( $notice_info['status'] ) ){
            if( $notice_info['status'] == 0 ){
                return;
            }
        }else{
            return;
        }

        $message = $notice_info['description'] ? $notice_info['description'] : '';

        \WooLentor_Notices::add_notice(
			[
				'id'          => 'setting-page-promo-banner',
                'type'        => 'custom',
                'dismissible' => true,
				'message'     => $message,
                'close_by'    => 'transient'
			]
		);

    }


}

Woolentor_Admin_Init::instance();