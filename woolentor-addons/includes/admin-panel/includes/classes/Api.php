<?php
namespace WoolentorOptions;

use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Api extends WP_REST_Controller {

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->includes();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );

        // Diagnostic Data Collect and Send from Setup Wizard
        add_action('woolentor_diagnostic_data_collect_and_send', [ $this, 'collect_and_send_data' ]);
    }

    /**
     * Include the controller classes
     *
     * @return void
     */
    private function includes() {
        if ( !class_exists( __NAMESPACE__ . '\Api\Settings'  ) ) {
            require_once __DIR__ . '/Api/Settings.php';
        }
        if (!class_exists(__NAMESPACE__ . '\Api\Plugins')) {
            require_once __DIR__ . '/Api/Plugins.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Api\Static_Content'  ) ) {
            require_once __DIR__ . '/Api/Static_Content.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Api\Custom_Actions'  ) ) {
            require_once __DIR__ . '/Api/Custom_Actions.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Api\TemplateLibrary'  ) ) {
            require_once __DIR__ . '/Api/TemplateLibrary.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Api\ChangeLog'  ) ) {
            require_once __DIR__ . '/Api/ChangeLog.php';
        }
        if ( !class_exists( __NAMESPACE__ . '\Api\Onboarding'  ) ) {
            require_once __DIR__ . '/Api/Onboarding.php';
        }
    }

    /**
     * Collect and Send Diagnostic Data
     *
     * @param string $nonce
     * @return void
     */
    public function collect_and_send_data( $from_cron = false ) {
        $diagnostic_file = WOOLENTOROPT_INCLUDES . '/classes/Admin/Diagnostic_Data.php';
        if ( file_exists( $diagnostic_file ) ) {
            require_once $diagnostic_file;
            $diagnostic = \WoolentorOptions\Admin\Diagnostic_Data::get_instance();
            $diagnostic->collect_and_send_data( '', $from_cron );
        }
    }

    /**
     * Register the API routes
     *
     * @return void
     */
    public function register_routes() {
        (new Api\Settings())->register_routes();
        (new Api\Plugins())->register_routes();
        (new Api\Static_Content())->register_routes();
        (new Api\Custom_Actions())->register_routes();
        (new Api\TemplateLibrary())->register_routes();
        (new Api\ChangeLog())->register_routes();
        (new Api\Onboarding())->register_routes();
    }

}