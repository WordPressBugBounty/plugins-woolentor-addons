<?php

namespace Woolentor\ElementorTemplate;
use Elementor\Core\Common\Modules\Ajax\Module as Ajax;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Elementor_Library_Manage {

	/**
	 * @var Library_Source|null
	 */
	protected static $source = null;

	/**
	 * @var Elementor_Library_Manage|null
	 */
	private static $_instance = null;

	/**
	 * Singleton instance
	 * @return Elementor_Library_Manage
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'elementor/editor/footer', [ $this, 'print_template_views' ] );
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_scripts' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'preview_scripts' ] );
	}

	/**
	 * Print template views in editor footer
	 */
	public function print_template_views() {
		include_once( WOOLENTOR_ADDONS_PL_PATH . 'includes/admin/include/templates/library/templates.php' );
	}

	/**
	 * Register AJAX actions for Elementor
	 */
	public function register_ajax_actions( Ajax $ajax ) {

		// Get template list
		$ajax->register_ajax_action( 'get_woolentor_library_data', function( $data ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				throw new \Exception( 'Access Denied' );
			}

			$result = [];

			if ( ! empty( $data['sync'] ) ) {
				$last_req_time = (int) get_option( 'woolentor_api_last_req' );

				if ( $last_req_time ) {
					if ( time() > $last_req_time + 86400 ) {
						update_option( 'woolentor_api_last_req', time() );
						\Woolentor_Template_Library_Manager::clear_cache( 'template' );
					}
				} else {
					update_option( 'woolentor_api_last_req', time() );
					\Woolentor_Template_Library_Manager::clear_cache( 'template' );
				}
			}

			$source = $this->get_source();
			$result['templates'] = $source->get_items();
			return $result;
		} );

		// Get single template content for insertion
		$ajax->register_ajax_action( 'get_woolentor_template_data', function( $data ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				throw new \Exception( 'Access Denied' );
			}

			if ( ! empty( $data['editor_post_id'] ) ) {
				$editor_post_id = absint( $data['editor_post_id'] );

				if ( ! get_post( $editor_post_id ) ) {
					throw new \Exception( esc_html__( 'Post not found', 'woolentor' ) );
				}

				\Elementor\Plugin::$instance->db->switch_to_post( $editor_post_id );
			}

			if ( empty( $data['template_id'] ) ) {
				throw new \Exception( esc_html__( 'Template id missing', 'woolentor' ) );
			}

			$result = $this->get_template_data( $data );

			return $result;
		} );
	}

	/**
	 * Enqueue editor scripts and styles
	 */
	public function editor_scripts() {

		wp_enqueue_style(
			'woolentor-template-library',
			WOOLENTOR_ADDONS_PL_URL . 'includes/admin/assets/css/woolentor-template-library.css',
			[ 'elementor-editor' ],
			WOOLENTOR_VERSION
		);

		wp_enqueue_script(
			'woolentor-template-library',
			WOOLENTOR_ADDONS_PL_URL . 'includes/admin/assets/js/woolentor-template-library.js',
			[ 'elementor-editor', 'jquery-hover-intent' ],
			WOOLENTOR_VERSION,
			true
		);

		// Detect current template type from post meta
		$template_type = '';
		$post_id = 0;

		if ( isset( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );
		} elseif ( isset( $_GET['post_id'] ) ) {
			$post_id = absint( $_GET['post_id'] );
		}

		if ( $post_id && get_post_type( $post_id ) === 'woolentor-template' ) {
			$raw_type = get_post_meta( $post_id, 'woolentor_template_meta_type', true );
			$template_type = self::map_template_type( $raw_type );
		}

		wp_localize_script( 'woolentor-template-library', 'WOOLENTORTMPL', [
			'logo' => WOOLENTOR_ADDONS_PL_URL . 'includes/admin/assets/images/logo.png',
			'hasPro' => is_plugin_active( 'woolentor-addons-pro/woolentor_addons_pro.php' ),
			'templateType' => $template_type,
		] );
	}

	/**
	 * Enqueue preview styles
	 */
	public function preview_scripts() {
		$inline_styles = '
			.elementor-add-new-section .elementor-add-woolentor-template-button {
				background-color: #6549D5;
				margin-left: 5px;
				vertical-align: top;
			}
		';
		wp_add_inline_style( 'editor-preview', $inline_styles );
	}

	/**
	 * Get Library Source instance
	 * @return Library_Source
	 */
	public function get_source() {
		if ( is_null( self::$source ) ) {
			self::$source = new Library_Source();
		}
		return self::$source;
	}

	/**
	 * Map raw template type to standardized type
	 * @param  string $type
	 * @return string
	 */
	public static function map_template_type( $type ) {
		$map = [
			'single'         => 'single',
			'quickview'      => 'single',
			'shop'           => 'shop',
			'archive'        => 'shop',
			'cart'           => 'cart',
			'emptycart'      => 'cart',
			'minicart'       => 'minicart',
			'checkout'       => 'checkout',
			'checkouttop'    => 'checkout',
			'myaccount'      => 'myaccount',
			'myaccountlogin' => 'myaccount',
			'dashboard'      => 'myaccount',
			'orders'         => 'myaccount',
			'downloads'      => 'myaccount',
			'edit-address'   => 'myaccount',
			'edit-account'   => 'myaccount',
			'lost-password'  => 'myaccount',
			'reset-password' => 'myaccount',
			'thankyou'       => 'thankyou',
			'popup'          => 'popup',
		];

		return isset( $map[ $type ] ) ? $map[ $type ] : '';
	}

	/**
	 * Get template data for insertion
	 * @param  array $args
	 * @return array
	 */
	public function get_template_data( array $args ) {
		$source = $this->get_source();
		$data = $source->get_data( $args );
		return $data;
	}

}
