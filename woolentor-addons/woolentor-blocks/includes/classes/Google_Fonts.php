<?php
namespace WooLentorBlocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global Google Fonts Enqueue Handler
 *
 * Scans WooLentor blocks for *FontFamily attributes, validates against the
 * bundled google-fonts list when available, and enqueues stylesheets in the head.
 */
class Google_Fonts {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * System fonts that don't need Google Font loading.
	 *
	 * @var array
	 */
	private $system_fonts = array(
		'sans-serif',
		'serif',
		'monospace',
		'cursive',
		'fantasy',
	);

	/**
	 * Lowercased family => canonical family name from JSON.
	 *
	 * @var array|null null = JSON missing, skip strict validation.
	 */
	private $google_family_map = null;

	/**
	 * Fonts already enqueued this request (canonical name => true).
	 *
	 * @var array
	 */
	private $enqueued_fonts = array();

	/**
	 * Initializes a singleton instance.
	 *
	 * @return Google_Fonts
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'register_fonts_from_parsed_content' ), 25 );
		add_filter( 'render_block', array( $this, 'collect_fonts_from_block' ), 10, 2 );
	}

	/**
	 * Parse post / template content before output and enqueue Google Fonts (prints in head).
	 *
	 * @return void
	 */
	public function register_fonts_from_parsed_content() {
		$this->google_family_map = $this->load_google_family_map();

		foreach ( $this->get_content_strings_to_parse() as $content ) {
			if ( empty( $content ) || ! has_blocks( $content ) ) {
				continue;
			}
			$fonts = array();
			$this->collect_from_blocks( $fonts, parse_blocks( $content ) );
			foreach ( array_keys( $fonts ) as $family ) {
				$this->enqueue_google_font( $family );
			}
		}
	}

	/**
	 * Collect font families while blocks render (reusable in widgets, late content, etc.).
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Unmodified block content.
	 */
	public function collect_fonts_from_block( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || strpos( $block['blockName'], 'woolentor/' ) !== 0 ) {
			return $block_content;
		}

		if ( null === $this->google_family_map ) {
			$this->google_family_map = $this->load_google_family_map();
		}

		$attrs = ! empty( $block['attrs'] ) ? $block['attrs'] : array();
		$fonts = array();
		$this->collect_font_attrs_from_array( $fonts, $attrs );
		foreach ( array_keys( $fonts ) as $family ) {
			$this->enqueue_google_font( $family );
		}

		return $block_content;
	}

	/**
	 * @return array<int, string>
	 */
	private function get_content_strings_to_parse() {
		$chunks  = array();
		$seen    = array();
		$add     = function ( $html ) use ( &$chunks, &$seen ) {
			$html = is_string( $html ) ? $html : '';
			$key  = md5( $html );
			if ( '' === $html || isset( $seen[ $key ] ) ) {
				return;
			}
			$seen[ $key ] = true;
			$chunks[]     = $html;
		};

		global $_wp_current_template_content;

		$post_id = function_exists( 'woolentorBlocks_get_ID' ) ? woolentorBlocks_get_ID() : 0;
		if ( $post_id && is_numeric( $post_id ) ) {
			$post = get_post( (int) $post_id );
			if ( $post ) {
				$add( $post->post_content );
			}
		}

		if ( is_singular() ) {
			$qid = get_queried_object_id();
			if ( $qid ) {
				$post = get_post( $qid );
				if ( $post ) {
					$add( $post->post_content );
				}
			}
		}

		if ( ! empty( $_wp_current_template_content ) ) {
			$add( $_wp_current_template_content );
		}

		return $chunks;
	}

	/**
	 * @param array<string, bool> $fonts
	 * @param array               $blocks
	 */
	private function collect_from_blocks( &$fonts, $blocks ) {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			if ( ! empty( $block['blockName'] ) && 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
				$ref_html = (string) get_post_field( 'post_content', (int) $block['attrs']['ref'] );
				if ( '' !== $ref_html && has_blocks( $ref_html ) ) {
					$this->collect_from_blocks( $fonts, parse_blocks( $ref_html ) );
				}
			}

			if ( ! empty( $block['blockName'] ) && strpos( $block['blockName'], 'woolentor/' ) === 0 ) {
				$this->collect_font_attrs_from_array( $fonts, $block['attrs'] ?? array() );
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$this->collect_from_blocks( $fonts, $block['innerBlocks'] );
			}
		}
	}

	/**
	 * @param array<string, bool> $fonts
	 * @param array               $attrs
	 */
	private function collect_font_attrs_from_array( &$fonts, $attrs ) {
		foreach ( $attrs as $key => $value ) {
			if ( ! empty( $value ) && is_string( $value ) && ( preg_match( '/FontFamily$/', $key ) || 'fontFamily' === $key ) ) {
				$canonical = $this->normalize_google_family_name( sanitize_text_field( $value ) );
				if ( $canonical ) {
					$fonts[ $canonical ] = true;
				}
			} elseif ( is_array( $value ) ) {
				$this->collect_font_attrs_from_array( $fonts, $value );
			}
		}
	}

	/**
	 * @param string $font_name Raw attribute value.
	 * @return string Canonical family name or empty if not a loadable Google font.
	 */
	private function normalize_google_family_name( $font_name ) {
		$trimmed = trim( $font_name );
		if ( '' === $trimmed ) {
			return '';
		}

		$lower = strtolower( $trimmed );
		if ( in_array( $lower, $this->system_fonts, true ) ) {
			return '';
		}

		if ( is_array( $this->google_family_map ) && ! empty( $this->google_family_map ) ) {
			if ( isset( $this->google_family_map[ $lower ] ) ) {
				return $this->google_family_map[ $lower ];
			}
			return '';
		}

		// JSON unavailable: preserve previous behavior (any non-system string).
		return $trimmed;
	}

	/**
	 * @return array<string, string>|null Map of lowercase name => canonical family; null if file missing.
	 */
	private function load_google_family_map() {
		$paths = array(
			WOOLENTOR_BLOCK_PATH . '/includes/data/google-fonts.json',
			WOOLENTOR_BLOCK_PATH . '/src/components/font-family-control/google-fonts.json',
		);

		foreach ( $paths as $path ) {
			if ( ! is_readable( $path ) ) {
				continue;
			}
			$json = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$data = json_decode( $json, true );
			if ( ! is_array( $data ) ) {
				return null;
			}
			$map = array();
			foreach ( $data as $row ) {
				if ( empty( $row['family'] ) || ! is_string( $row['family'] ) ) {
					continue;
				}
				$family = $row['family'];
				$map[ strtolower( $family ) ] = $family;
			}
			return ! empty( $map ) ? $map : null;
		}

		return null;
	}

	/**
	 * @param string $canonical_font_name Canonical Google Font family name.
	 */
	private function enqueue_google_font( $canonical_font_name ) {
		if ( '' === $canonical_font_name || isset( $this->enqueued_fonts[ $canonical_font_name ] ) ) {
			return;
		}

		$this->enqueued_fonts[ $canonical_font_name ] = true;

		$handle   = 'woolentor-gfont-' . sanitize_title( $canonical_font_name );
		$font_url = $this->get_google_font_url( $canonical_font_name );
		wp_enqueue_style( $handle, $font_url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Google Fonts CSS URL (aligned with editor: weights + italics + display=swap).
	 *
	 * @param string $font_name Font family name.
	 * @return string
	 */
	private function get_google_font_url( $font_name ) {
		$family = str_replace( ' ', '+', $font_name );
		return 'https://fonts.googleapis.com/css?family=' . $family
			. ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic'
			. '&display=swap';
	}
}
