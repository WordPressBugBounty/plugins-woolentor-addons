<?php
namespace WooLentor;
/**
 * Style Pack Manager
 * Central registry for all style packs. Single source of truth for pack slugs,
 * labels, variant counts, and CSS asset registration.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Style_Pack_Manager {

    private static $_instance = null;

    /**
     * Registered packs.
     * @var array
     */
    private static $packs = [];

    /**
     * Number of design variants per pack (same for all packs).
     */
    const VARIANT_COUNT = 3;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->register_packs();
        add_action( 'wp_enqueue_scripts', [ $this, 'register_pack_styles' ], 5 );
        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'register_pack_styles' ], 5 );
    }

    // ─── Registry ─────────────────────────────────────────────────────────────

    private function register_packs() {
        $packs = [
            'editorial' => [
                'slug'      => 'editorial',
                'label'     => 'Editorial',
                'variants'  => self::VARIANT_COUNT,
                'pages'     => [ 'home', 'shop', 'product', 'cart', 'checkout' ]
            ],
            'modern' => [
                'slug'      => 'modern',
                'label'     => 'Modern',
                'variants'  => self::VARIANT_COUNT,
                'pages'     => [ 'home', 'shop', 'product', 'cart', 'checkout' ]
            ],
            'luxury' => [
                'slug'      => 'luxury',
                'label'     => 'Luxury',
                'variants'  => self::VARIANT_COUNT,
                'pages'     => [ 'home', 'shop', 'product', 'cart', 'checkout' ]
            ],
            'magazine' => [
                'slug'      => 'magazine',
                'label'     => 'Magazine',
                'variants'  => self::VARIANT_COUNT,
                'pages'     => [ 'home', 'shop', 'product', 'cart', 'checkout' ]
            ],
        ];

        self::$packs = apply_filters( 'woolentor_style_packs', $packs );
    }

    // ─── Public getters ───────────────────────────────────────────────────────

    /**
     * Get all registered packs.
     * @return array
     */
    public static function get_all_packs() {
        return self::$packs;
    }

    /**
     * Get a single pack by slug. Returns null if not found.
     * @param  string $slug
     * @return array|null
     */
    public static function get_pack( $slug ) {
        return self::$packs[ $slug ] ?? null;
    }

    /**
     * Get all pack slugs.
     * @return string[]
     */
    public static function get_pack_slugs() {
        return array_keys( self::$packs );
    }

    /**
     * Get slug => label map. Used to populate Elementor SELECT controls.
     * @return array
     */
    public static function get_pack_labels() {
        $labels = [];
        foreach ( self::$packs as $slug => $pack ) {
            $labels[ $slug ] = __( $pack['label'], 'woolentor' );
        }
        return $labels;
    }

    /**
     * Get variant options for Elementor SELECT controls.
     * Returns [ 'v1' => 'Variant 1', 'v2' => 'Variant 2', 'v3' => 'Variant 3' ]
     * @return array
     */
    public static function get_variant_options() {
        $options = [];
        for ( $i = 1; $i <= self::VARIANT_COUNT; $i++ ) {
            /* translators: %d: variant number */
            $options[ 'v' . $i ] = sprintf( __( 'Variant %d', 'woolentor' ), $i );
        }
        return $options;
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    /**
     * Check whether a pack slug is registered.
     * @param  string $slug
     * @return bool
     */
    public static function is_valid_pack( $slug ) {
        return isset( self::$packs[ $slug ] );
    }

    /**
     * Check whether a variant key is valid (v1 … v{VARIANT_COUNT}).
     * @param  string $variant
     * @return bool
     */
    public static function is_valid_variant( $variant ) {
        $allowed = [];
        for ( $i = 1; $i <= self::VARIANT_COUNT; $i++ ) {
            $allowed[] = 'v' . $i;
        }
        return in_array( $variant, $allowed, true );
    }

    /**
     * Sanitize and validate a pack slug. Returns fallback on failure.
     * @param  string $slug
     * @param  string $fallback
     * @return string
     */
    public static function sanitize_pack( $slug, $fallback = 'modern' ) {
        $slug = sanitize_key( $slug );
        return self::is_valid_pack( $slug ) ? $slug : $fallback;
    }

    /**
     * Sanitize and validate a variant key. Returns 'v1' on failure.
     * @param  string $variant
     * @return string
     */
    public static function sanitize_variant( $variant ) {
        $variant = sanitize_key( $variant );
        return self::is_valid_variant( $variant ) ? $variant : 'v1';
    }

    // ─── Asset registration ───────────────────────────────────────────────────

    /**
     * Register all pack font faces and the single combined variables stylesheet.
     * All 4 packs' tokens live in one file — scoped per [data-wl-pack="{slug}"].
     */
    public function register_pack_styles() {
        $font_handles = [];

        $handle = self::get_style_handle();
        if ( ! wp_style_is( $handle, 'registered' ) ) {
            wp_register_style(
                $handle,
                WOOLENTOR_ADDONS_PL_URL . 'assets/pack-widgets/css/pack-widgets-base.css',
                $font_handles,
                WOOLENTOR_VERSION
            );
        }
    }

    /**
     * Get the registered CSS handle for pack variables.
     * All packs share one combined stylesheet — the $slug parameter is accepted
     * for backward compatibility but is ignored.
     *
     * @param  string $slug  Unused. Kept for backward compatibility.
     * @return string        WP style handle.
     */
    public static function get_style_handle( $slug = '' ) {
        return 'wl-pack-base';
    }

    // ─── Template resolution ──────────────────────────────────────────────────

    /**
     * Resolve and validate a template file path for Pattern B widgets.
     * Prevents directory traversal attacks.
     *
     * Builds the path: {base_dir}/{pack}/{variant}.php
     * Returns empty string if the file does not exist or escapes base_dir.
     *
     * @param  string $base_dir  Absolute path to the widget's templates/ folder.
     * @param  string $pack      Pack slug  (e.g. 'modern').
     * @param  string $variant   Variant key (e.g. 'v1').
     * @return string            Validated absolute path, or empty string.
     */
    public static function resolve_template( $base_dir, $pack, $variant ) {
        $pack    = self::sanitize_pack( $pack );
        $variant = self::sanitize_variant( $variant );

        $base_dir  = trailingslashit( wp_normalize_path( $base_dir ) );
        $candidate = wp_normalize_path( $base_dir . $pack . '/' . $variant . '.php' );

        if ( ! file_exists( $candidate ) ) {
            return '';
        }

        $real_base   = wp_normalize_path( realpath( $base_dir ) );
        $real_target = wp_normalize_path( realpath( $candidate ) );

        if ( ! $real_target || strpos( $real_target, $real_base ) !== 0 ) {
            return '';
        }

        return apply_filters( 'woolentor_pack_template_path', $real_target, $pack, $variant );
    }

    /**
     * Check whether a style + variant combination is pro-only.
     *
     * @param  string $style    Pack slug (e.g. 'editorial').
     * @param  string $variant  Variant key (e.g. 'v2').
     * @param  array  $pro_map  Widget's own map: [ 'style' => [ 'v2', 'v3' ], ... ]
     * @return bool
     */
    public static function is_pro_variant( $style, $variant, array $pro_map ) {
        return isset( $pro_map[ $style ] ) && in_array( $variant, $pro_map[ $style ], true );
    }

    /**
     * Echo a frontend "requires Pro" notice for a locked variant.
     * Called by pack widgets on the frontend when pro is not active.
     *
     * @param string $style        Pack slug (e.g. 'editorial').
     * @param string $variant      Variant key (e.g. 'v2').
     * @param string $widget_label Human-readable widget name (e.g. 'Hero Banner').
     */
    public static function render_upgrade_notice( $style, $variant, $widget_label = '' ) {
        $variant_label = 'Variant ' . ltrim( $variant, 'v' );
        $style_label   = ucfirst( $style );
        $label         = $widget_label ? $widget_label : __( 'This widget', 'woolentor' );
        echo '<div style="display:flex;align-items:center;justify-content:center;'
            . 'min-height:200px;padding:40px 20px;background:#f4f4f4;'
            . 'border:2px dashed #ddd;border-radius:6px;text-align:center;">'
            . '<div>'
            . '<div style="font-size:36px;margin-bottom:10px;">&#x1F512;</div>'
            . '<div style="font-weight:700;font-size:16px;margin-bottom:8px;">'
            . esc_html( $style_label . ' — ' . $variant_label )
            . '</div>'
            /* translators: %s: widget label, e.g. "Hero Banner" */
            . '<p style="font-size:13px;color:#888;margin:0;">'
            . sprintf( esc_html__( 'This %s variant requires ShopLentor Pro.', 'woolentor' ), esc_html( $label ) )
            . '</p>'
            . '</div>'
            . '</div>';
    }
}
