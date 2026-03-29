<?php

namespace Woolentor\ElementorTemplate;
use Elementor\TemplateLibrary\Source_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Library_Source extends Source_Base {

	/**
	 * Get remote template ID.
	 * @return string
	 */
	public function get_id() {
		return 'woolentor-library';
	}

	/**
	 * Get remote template title.
	 * @return string
	 */
	public function get_title() {
		return __( 'ShopLentor Library', 'woolentor' );
	}

	/**
	 * Register remote template data.
	 * @return void
	 */
	public function register_data() {}

	/**
	 * Retrieve templates from ShopLentor library servers.
	 * @param  array  $args
	 * @return array
	 */
	public function get_items( $args = [] ) {
		$library_data = self::get_library_data();

		$templates = [];
		if ( ! empty( $library_data['templates'] ) ) {
			foreach ( $library_data['templates'] as $template_data ) {
				// Only include Elementor templates
				if ( ! empty( $template_data['builder'] ) ) {
					$builder = $template_data['builder'];
					if ( is_array( $builder ) && ! in_array( 'elementor', $builder ) ) {
						continue;
					}
					if ( is_string( $builder ) && strpos( $builder, 'elementor' ) === false ) {
						continue;
					}
				}
				$templates[] = $this->prepare_template( $template_data );
			}
		}
		return $templates;
	}

	/**
	 * Retrieve the templates data from ShopLentor server.
	 * @return array
	 */
	public static function get_library_data() {
		$data = \Woolentor_Template_Library_Manager::get_templates_info();

		if ( empty( $data ) ) {
			return [];
		}

		return $data;
	}

	/**
	 * Get remote template.
	 * @param  int $template_id
	 * @return array
	 */
	public function get_item( $template_id ) {
		$templates = $this->get_items();
		return isset( $templates[ $template_id ] ) ? $templates[ $template_id ] : [];
	}

	/**
	 * Save remote template - not supported.
	 * @param  array $template_data
	 * @return \WP_Error
	 */
	public function save_item( $template_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot save template to a ShopLentor source' );
	}

	/**
	 * Update remote template - not supported.
	 * @param  array $new_data
	 * @return \WP_Error
	 */
	public function update_item( $new_data ) {
		return new \WP_Error( 'invalid_request', 'Cannot update template to a ShopLentor source' );
	}

	/**
	 * Delete remote template - not supported.
	 * @param  int $template_id
	 * @return \WP_Error
	 */
	public function delete_template( $template_id ) {
		return new \WP_Error( 'invalid_request', 'Cannot delete template from a ShopLentor source' );
	}

	/**
	 * Export remote template - not supported.
	 * @param  int $template_id
	 * @return \WP_Error
	 */
	public function export_template( $template_id ) {
		return new \WP_Error( 'invalid_request', 'Cannot export template from a ShopLentor source' );
	}

	/**
	 * Get remote template data for import.
	 * @param  array  $args
	 * @param  string $context
	 * @return array
	 */
	public function get_data( array $args, $context = 'display' ) {
		$data = self::get_template_content( $args['template_id'] );

		$data = json_decode( $data, true );

		if ( empty( $data ) || empty( $data['content'] ) ) {
			throw new \Exception( esc_html__( 'Template does not have any content', 'woolentor' ) );
		}

		$data['content'] = $this->replace_elements_ids( $data['content']['content'] );
		$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

		$post_id  = $args['editor_post_id'];
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );

		if ( $document ) {
			$data['content'] = $document->get_elements_raw_data( $data['content'], true );
		}

		return $data;
	}

	/**
	 * Get template content from remote server.
	 * @param  int $template_id
	 * @return string
	 */
	public static function get_template_content( $template_id ) {
		if ( empty( $template_id ) ) {
			return;
		}

		$template_data = \Woolentor_Template_Library_Manager::get_template_data( 'template', $template_id );

		if ( ! empty( $template_data ) ) {
			return wp_json_encode( $template_data );
		}

		return '';
	}

	/**
	 * Prepare template items to match model.
	 * @param  array $template_data
	 * @return array
	 */
	private function prepare_template( array $template_data ) {
		return [
			'template_id' => isset( $template_data['id'] ) ? $template_data['id'] : '',
			'title'       => isset( $template_data['title'] ) ? $template_data['title'] : '',
			'type'        => isset( $template_data['type'] ) ? $template_data['type'] : '',
			'thumbnail'   => isset( $template_data['thumbnail'] ) ? $template_data['thumbnail'] : '',
			'date'        => isset( $template_data['human_date'] ) ? $template_data['human_date'] : '',
			'tags'        => isset( $template_data['tags'] ) ? $template_data['tags'] : [],
			'isPro'       => isset( $template_data['isPro'] ) ? $template_data['isPro'] : false,
			'tmpType'     => isset( $template_data['tmpType'] ) ? $template_data['tmpType'] : '',
			'url'         => isset( $template_data['url'] ) ? $template_data['url'] : '',
			'shareId'     => isset( $template_data['shareId'] ) ? $template_data['shareId'] : '',
		];
	}

}
