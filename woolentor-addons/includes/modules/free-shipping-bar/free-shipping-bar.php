<?php
namespace Woolentor\Modules\FreeShippingBar;
use WooLentor\Traits\ModuleBase;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Free_Shipping_Bar {
    use ModuleBase;

    /**
     * Module version
     */
    const VERSION = '1.0.0';

    /**
     * Class Constructor
     */
    public function __construct() {
        $this->define_constants();
        $this->include();
        $this->init();
    }

    /**
     * Define Required Constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'Woolentor\Modules\FreeShippingBar\MODULE_FILE', __FILE__ );
        define( 'Woolentor\Modules\FreeShippingBar\MODULE_PATH', __DIR__ );
        define( 'Woolentor\Modules\FreeShippingBar\MODULE_INCLUDES_PATH', __DIR__ . '/includes' );
        define( 'Woolentor\Modules\FreeShippingBar\MODULE_URL', plugins_url( '', MODULE_FILE ) );
        define( 'Woolentor\Modules\FreeShippingBar\MODULE_ASSETS', MODULE_URL . '/assets' );
        define( 'Woolentor\Modules\FreeShippingBar\WIDGETS_PATH', MODULE_INCLUDES_PATH . '/widgets' );
        define( 'Woolentor\Modules\FreeShippingBar\BLOCKS_PATH', MODULE_INCLUDES_PATH . '/blocks' );
    }

    /**
     * Load Required Files
     *
     * @return void
     */
    public function include() {
        require_once MODULE_INCLUDES_PATH . '/classes/Admin.php';
        require_once MODULE_INCLUDES_PATH . '/classes/Frontend.php';

        if ( self::$_enabled ) {
            require_once MODULE_INCLUDES_PATH . '/classes/Widgets_And_Blocks.php';
        }
    }

    /**
     * Module Initialize
     *
     * @return void
     */
    public function init() {
        // Load Pro extension if available
        $this->load_pro_module();

        // Admin is always initialised so settings are visible regardless of enabled state
        if ( $this->is_request( 'admin' ) || $this->is_request( 'rest' ) ) {
            Admin::instance();
        }

        if ( ! self::$_enabled ) {
            return;
        }

        if ( $this->is_request( 'frontend' ) || $this->is_request( 'block' ) ) {
            Frontend::instance();
        }

        Widgets_And_Blocks::instance();
    }

    /**
     * Load the Pro module file when WooLentor Pro is active.
     *
     * @return void
     */
    private function load_pro_module() {
        if ( $this->is_pro() ) {
            $pro_file = WOOLENTOR_ADDONS_PL_PATH_PRO . 'includes/modules/free-shipping-bar/free-shipping-bar.php';
            if ( file_exists( $pro_file ) ) {
                require_once $pro_file;
                \WoolentorPro\Modules\FreeShippingBar\Free_Shipping_Bar_Pro::instance(self::$_enabled);
            }
        }
    }
}
