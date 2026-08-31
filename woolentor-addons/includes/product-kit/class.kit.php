<?php
namespace WooLentor\Product_Kit;

/**
 * Product Kit bootstrap.
 *
 * Loads the kit's classes, registers its shared frontend asset and wires the section endpoint.
 * Nothing here knows about Elementor or Gutenberg — a builder is added by registering a provider,
 * and the providers live in their own files under providers/.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Kit {

    const SCRIPT_HANDLE = 'wl-product-section';
    const STYLE_HANDLE  = 'wl-product-section';

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->load();

        Section_Ajax::init();
        Filter::init();

        // Registered rather than enqueued: a page with no product section should not carry the
        // script at all. Consumers ask for it when they draw something that can call back.
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 5 );
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'register_assets' ], 5 );
    }

    private function load() {
        $dir = WOOLENTOR_ADDONS_PL_PATH . 'includes/product-kit/';

        require_once $dir . 'class.settings.php';
        require_once $dir . 'class.schema.php';
        require_once $dir . 'class.product-query.php';
        require_once $dir . 'class.pagination.php';
        require_once $dir . 'class.tabs.php';
        require_once $dir . 'class.slider.php';
        require_once $dir . 'class.filter.php';
        require_once $dir . 'interface.renders-section.php';
        require_once $dir . 'class.section-registry.php';
        require_once $dir . 'class.section-ajax.php';

        // Safe on a site with no Elementor: every Elementor reference inside it is in a method
        // body, and nothing calls those until a widget registers its controls.
        require_once $dir . 'class.elementor-controls.php';

        // Providers. Each one guards itself at call time rather than at load time, so this list
        // stays a plain list and a builder that is not active simply never resolves.
        require_once $dir . 'providers/class.elementor-provider.php';
        require_once $dir . 'providers/class.block-provider.php';

        Elementor_Provider::register();
        Block_Provider::register();
    }

    /**
     * The one script and stylesheet every product section shares, whichever builder drew it.
     *
     * @return void
     */
    public function register_assets() {
        if ( ! wp_script_is( self::SCRIPT_HANDLE, 'registered' ) ) {
            wp_register_script(
                self::SCRIPT_HANDLE,
                WOOLENTOR_ADDONS_PL_URL . 'assets/product-kit/js/product-section.js',
                [ 'jquery' ],
                WOOLENTOR_VERSION,
                true
            );

            wp_localize_script( self::SCRIPT_HANDLE, 'wlProductSection', [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            ] );
        }

        if ( ! wp_style_is( self::STYLE_HANDLE, 'registered' ) ) {
            wp_register_style(
                self::STYLE_HANDLE,
                WOOLENTOR_ADDONS_PL_URL . 'assets/product-kit/css/product-section.css',
                [],
                WOOLENTOR_VERSION
            );
        }
    }

    /**
     * What a consumer calls from its own get_script_depends() / enqueue.
     *
     * @return array
     */
    public static function asset_handles() {
        return [ 'script' => self::SCRIPT_HANDLE, 'style' => self::STYLE_HANDLE ];
    }
}
