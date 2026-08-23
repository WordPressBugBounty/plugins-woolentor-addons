<?php
/**
 * Campaign Banner Widget — Pattern B (Style + Variant dropdowns).
 *
 * The single wide promotional section each style pack ships: an eyebrow, a large headline, a line
 * of copy, an optional countdown or stats row, one or two calls to action, and a photograph — laid
 * out as a split panel, a full-bleed overlay, or a compact strip depending on the variant.
 *
 * Split from Offer Banner because the content differs: that widget is a repeater of small cards,
 * this one is a single section. Anything that needs a real WooCommerce product — gallery, price,
 * Add to Cart — belongs to Feature Product instead.
 *
 * The countdown mechanism is not defined here. pack-widgets.js carries WLPackCountdown, a thin
 * adapter over the plugin's bundled `countdown-min` library, and pack-widgets-base.css carries
 * .wl-pack-countdown*. This widget only emits the markup and supplies each pack's skin.
 *
 * Spec: blueprint/campaign-banner-widget-plan.md
 *
 * @package WooLentor
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woolentor_Campaign_Banner_Widget extends Widget_Base {

    /** Combinations whose template renders the oversized figure beside the headline. */
    const NUMBER_VARIANTS = [
        'modern' => [ 'v3' ],
        'luxury' => [ 'v2' ],
    ];

    /** Combinations whose template renders the stats row. */
    const STATS_VARIANTS = [
        'modern' => [ 'v2' ],
    ];

    /** Combinations whose template renders the floating product cards over the media. */
    const CARDS_VARIANTS = [
        'modern' => [ 'v3' ],
    ];

    /** Combinations whose template renders the coupon line. */
    const COUPON_VARIANTS = [
        'editorial' => [ 'v2' ],
        'luxury'    => [ 'v3' ],
    ];

    /**
     * Combinations whose media panel can hold more than one image. Editorial v1 fades between
     * three shots under a dot strip; editorial v3 steps through them with corner arrows.
     */
    const MEDIA_SLIDER_VARIANTS = [
        'editorial' => [ 'v1', 'v3' ],
        'magazine'  => [ 'v3' ],
    ];

    /** Combinations that are a band or strip with no photograph at all. */
    const NOMEDIA_VARIANTS = [
        'modern'   => [ 'v1' ],
        'luxury'   => [ 'v3' ],
        'magazine' => [ 'v1', 'v2' ],
    ];

    /**
     * The one reference countdown that runs without separators — modern v1's plain cell grid.
     * Every other variant has them, so a single global default cannot be right for all twelve:
     * the Separator control resolves "Default" through this map.
     */
    const NO_SEPARATOR_VARIANTS = [
        'modern' => [ 'v1' ],
    ];

    /** Separator glyphs the control offers. */
    const SEPARATORS = [
        'colon' => ':',
        'dot'   => '·',
        'dash'  => '–',
        'slash' => '/',
    ];

    /** The countdown units a template may render, in descending order. */
    const UNITS = [ 'days', 'hours', 'minutes', 'seconds' ];

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        $this->register_pack_styles();
        $this->register_pack_scripts();
    }

    /**
     * The shared script is registered by whichever pack widget constructs first, so the
     * dependency list here must match hero_banner.php exactly — a mismatched set would
     * silently lose one. `countdown-min` is deliberately not added: it is requested by this
     * widget's get_script_depends() instead, so a page carrying only a Hero Banner never
     * downloads it.
     */
    private function register_pack_scripts() {
        if ( ! wp_script_is( 'wl-pack-widgets', 'registered' ) ) {
            wp_register_script(
                'wl-pack-widgets',
                WOOLENTOR_ADDONS_PL_URL . 'assets/pack-widgets/js/pack-widgets.js',
                [ 'jquery', 'slick' ],
                WOOLENTOR_VERSION,
                true
            );
        }
    }

    private function register_pack_styles() {
        foreach ( array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() ) as $pack ) {
            $handle = "wl-pack-campaign-banner-{$pack}";
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style(
                    $handle,
                    WOOLENTOR_ADDONS_PL_URL . "assets/pack-widgets/css/campaign-banner/{$pack}.css",
                    [ \WooLentor\Style_Pack_Manager::get_style_handle() ],
                    WOOLENTOR_VERSION
                );
            }
        }
    }

    public function get_name() {
        return 'woolentor-campaign-banner';
    }

    public function get_title() {
        return esc_html__( 'Campaign Banner - 2026', 'woolentor' );
    }

    public function get_icon() {
        return 'woolentor-widget-new-icon eicon-banner';
    }

    public function get_categories() {
        return [ 'woolentor-addons' ];
    }

    public function get_keywords() {
        return [ 'campaign', 'banner', 'promo', 'sale', 'countdown', 'deal', 'offer', 'pack', 'style', 'woolentor' ];
    }

    public function get_script_depends() {
        return [ 'countdown-min', 'wl-pack-widgets' ];
    }

    public function get_style_depends() {
        return array_map(
            fn( $pack ) => "wl-pack-campaign-banner-{$pack}",
            array_keys( \WooLentor\Style_Pack_Manager::get_pack_labels() )
        );
    }

    /**
     * Style => locked variants. Every pack's v2 and v3 require Pro.
     * This file is only loaded when Pro is NOT active.
     */
    private function get_pro_map() {
        $map = [];
        foreach ( \WooLentor\Style_Pack_Manager::get_pack_slugs() as $slug ) {
            $map[ $slug ] = [ 'v2', 'v3' ];
        }
        return $map;
    }

    /**
     * Build a nested Elementor 'conditions' array from a style => variants map.
     * OR across the mapped combinations, AND between style and variant within each.
     *
     * @param  array $map  [ 'modern' => [ 'v1' ], ... ]
     * @return array
     */
    private function build_conditions( array $map ) {
        $terms = [];
        foreach ( $map as $style => $variants ) {
            foreach ( $variants as $variant ) {
                $terms[] = [
                    'relation' => 'and',
                    'terms'    => [
                        [ 'name' => 'style',   'operator' => '==', 'value' => $style ],
                        [ 'name' => 'variant', 'operator' => '==', 'value' => $variant ],
                    ],
                ];
            }
        }
        return [ 'relation' => 'or', 'terms' => $terms ];
    }

    /**
     * Negated form of build_conditions() — true when the current selection is NOT any of
     * the mapped combinations. Used both for the Pro gate and for "every variant except these".
     *
     * @param  array $map
     * @return array
     */
    private function build_negated_conditions( array $map ) {
        $terms = [];
        foreach ( $map as $style => $variants ) {
            foreach ( $variants as $variant ) {
                $terms[] = [
                    'relation' => 'or',
                    'terms'    => [
                        [ 'name' => 'style',   'operator' => '!=', 'value' => $style ],
                        [ 'name' => 'variant', 'operator' => '!=', 'value' => $variant ],
                    ],
                ];
            }
        }
        return [ 'relation' => 'and', 'terms' => $terms ];
    }

    /**
     * AND of several condition groups — a section that is variant-specific *and* behind the
     * Pro gate, or one that must satisfy two maps at once.
     *
     * @param  array ...$groups
     * @return array
     */
    private function build_and_conditions( ...$groups ) {
        return [ 'relation' => 'and', 'terms' => $groups ];
    }

    /** Shorthand: not Pro-locked. */
    private function unlocked() {
        return $this->build_negated_conditions( $this->get_pro_map() );
    }

    /** Shorthand: not Pro-locked, and one of the mapped style/variant pairs. */
    private function unlocked_for( array $map ) {
        return $this->build_and_conditions( $this->unlocked(), $this->build_conditions( $map ) );
    }

    protected function register_controls() {

        // ── Style Pack ────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_pack', [
            'label' => esc_html__( 'Style Pack', 'woolentor' ),
        ] );

            $this->add_control( 'style', [
                'label'   => esc_html__( 'Style', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'modern',
                'options' => \WooLentor\Style_Pack_Manager::get_pack_labels(),
            ] );

            $this->add_control( 'variant', [
                'label'   => esc_html__( 'Variant', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'v1',
                'options' => \WooLentor\Style_Pack_Manager::get_variant_options(),
            ] );

            woolentor_upgrade_pro_notice(
                $this,
                'campaign_banner_variant_pro_notice',
                [ 'variant' => [ 'v2', 'v3' ] ],
                [ 'mode' => 'alert' ]
            );

        $this->end_controls_section();

        $this->register_content_controls();
        $this->register_countdown_controls();
        $this->register_media_controls();
        $this->register_button_controls();
        $this->register_extras_controls();
        $this->register_pro_content_notice();
        $this->register_style_controls();
        $this->register_pro_style_notice();
    }

    // ── Content tab ───────────────────────────────────────────────────────────

    private function register_content_controls() {
        $this->start_controls_section( 'section_content', [
            'label'      => esc_html__( 'Content', 'woolentor' ),
            'conditions' => $this->unlocked(),
        ] );

            $this->add_control( 'eyebrow', [
                'label'       => esc_html__( 'Eyebrow', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Daily Deal · Live now', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'eyebrow_live_dot', [
                'label'        => esc_html__( 'Pulsing Dot', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'The small live indicator before the eyebrow.', 'woolentor' ),
                'condition'    => [ 'eyebrow!' => '' ],
            ] );

            $this->add_control( 'display_number', [
                'label'       => esc_html__( 'Display Figure', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => '30',
                'separator'   => 'before',
                'description' => esc_html__( 'The oversized number beside the headline. Modern v3 and Luxury v2 only.', 'woolentor' ),
            ] );

            $this->add_control( 'display_suffix', [
                'label'       => esc_html__( 'Figure Suffix', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'placeholder' => esc_html__( "%\nDiscount", 'woolentor' ),
                'description' => esc_html__( 'Line breaks are kept — the reference stacks the % over the word beneath it.', 'woolentor' ),
            ] );

            $this->add_control( 'headline', [
                'label'       => esc_html__( 'Headline', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'separator'   => 'before',
                'default'     => esc_html__( 'Drops every 24h, only while stocks last.', 'woolentor' ),
                'description' => esc_html__( 'Line breaks and <br>, <em>, <strong> are allowed. Several packs colour the <em> part.', 'woolentor' ),
            ] );

            $this->add_control( 'description', [
                'label'   => esc_html__( 'Description', 'woolentor' ),
                'type'    => Controls_Manager::TEXTAREA,
                'rows'    => 3,
                'default' => esc_html__( 'Up to 40% off rotating top-rated picks. Today\'s deals end soon.', 'woolentor' ),
            ] );

            $this->add_control( 'coupon_text', [
                'label'       => esc_html__( 'Coupon Line', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Use code SS26 at checkout', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Editorial v2 and Luxury v3 only.', 'woolentor' ),
            ] );

            $this->add_control( 'note', [
                'label'       => esc_html__( 'Fine Print', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'While stocks last. Cannot be combined with other offers.', 'woolentor' ),
                'description' => esc_html__( 'Luxury Variant 2 sets this vertically down the right edge, as a campaign side label.', 'woolentor' ),
                'label_block' => true,
            ] );

        $this->end_controls_section();
    }

    private function register_countdown_controls() {
        $this->start_controls_section( 'section_countdown', [
            'label'      => esc_html__( 'Countdown', 'woolentor' ),
            'conditions' => $this->unlocked(),
        ] );

            $this->add_control( 'countdown_show', [
                'label'        => esc_html__( 'Show Countdown', 'woolentor' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'countdown_end', [
                'label'       => esc_html__( 'Ends At', 'woolentor' ),
                'type'        => Controls_Manager::DATE_TIME,
                'picker_options' => [ 'enableTime' => true ],
                'default'     => gmdate( 'Y-m-d H:i', strtotime( '+7 days' ) ),
                'description' => esc_html__( 'Your site\'s timezone. The deadline is sent to the browser as a UTC instant, so a visitor with a wrong clock still sees the right countdown.', 'woolentor' ),
                'condition'   => [ 'countdown_show' => 'yes' ],
            ] );

            $this->add_control( 'countdown_label', [
                'label'       => esc_html__( 'Lead-in Label', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Ends in', 'woolentor' ),
                'description' => esc_html__( 'A short line set beside the figures rather than under them. Magazine Variant 1 reads "Ends in"; leave it empty and nothing is rendered.', 'woolentor' ),
                'condition'   => [ 'countdown_show' => 'yes' ],
            ] );

            $this->add_control( 'countdown_units', [
                'label'       => esc_html__( 'Units', 'woolentor' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'default'     => [ 'days', 'hours', 'minutes', 'seconds' ],
                'options'     => [
                    'days'    => esc_html__( 'Days', 'woolentor' ),
                    'hours'   => esc_html__( 'Hours', 'woolentor' ),
                    'minutes' => esc_html__( 'Minutes', 'woolentor' ),
                    'seconds' => esc_html__( 'Seconds', 'woolentor' ),
                ],
                'description' => esc_html__( 'Drop the larger units and the next one absorbs them — an hours-first countdown shows 49 hours rather than losing two days.', 'woolentor' ),
                'condition'   => [ 'countdown_show' => 'yes' ],
            ] );

            $this->add_control( 'label_days', [
                'label'     => esc_html__( 'Days Label', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Days', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes', 'countdown_units' => 'days' ],
            ] );

            $this->add_control( 'label_hours', [
                'label'     => esc_html__( 'Hours Label', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Hours', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes', 'countdown_units' => 'hours' ],
            ] );

            $this->add_control( 'label_minutes', [
                'label'     => esc_html__( 'Minutes Label', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Mins', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes', 'countdown_units' => 'minutes' ],
            ] );

            $this->add_control( 'label_seconds', [
                'label'     => esc_html__( 'Seconds Label', 'woolentor' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Secs', 'woolentor' ),
                'condition' => [ 'countdown_show' => 'yes', 'countdown_units' => 'seconds' ],
            ] );

            $this->add_control( 'countdown_separator', [
                'label'       => esc_html__( 'Separator', 'woolentor' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => [
                    ''      => esc_html__( 'Default (from variant)', 'woolentor' ),
                    'none'   => esc_html__( 'None', 'woolentor' ),
                    'colon'  => ':',
                    'dot'    => '·',
                    'dash'   => '–',
                    'slash'  => '/',
                ],
                'description' => esc_html__( 'Default follows the design each variant was drawn from — a colon everywhere except Modern v1, whose cells stand alone.', 'woolentor' ),
                'condition'   => [ 'countdown_show' => 'yes' ],
            ] );

            $this->add_control( 'countdown_expired_action', [
                'label'     => esc_html__( 'When It Ends', 'woolentor' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'hide',
                'options'   => [
                    'hide'         => esc_html__( 'Hide the countdown', 'woolentor' ),
                    'hide-section' => esc_html__( 'Hide the whole section', 'woolentor' ),
                    'message'      => esc_html__( 'Show a message', 'woolentor' ),
                ],
                'condition' => [ 'countdown_show' => 'yes' ],
            ] );

            $this->add_control( 'countdown_expired_text', [
                'label'       => esc_html__( 'Ended Message', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'This offer has ended', 'woolentor' ),
                'label_block' => true,
                'condition'   => [ 'countdown_show' => 'yes', 'countdown_expired_action' => 'message' ],
            ] );

        $this->end_controls_section();
    }

    private function register_media_controls() {
        $this->start_controls_section( 'section_media', [
            'label'      => esc_html__( 'Media', 'woolentor' ),
            'conditions' => $this->build_and_conditions(
                $this->unlocked(),
                $this->build_negated_conditions( self::NOMEDIA_VARIANTS )
            ),
        ] );

            // Slider variants take their images from the Slides repeater instead, so this
            // control is hidden for them — two image sources on one panel is how the gallery
            // ended up quietly overriding the chosen image.
            $this->add_control( 'media_image', [
                'label'      => esc_html__( 'Image', 'woolentor' ),
                'type'       => Controls_Manager::MEDIA,
                'default'    => [ 'url' => Utils::get_placeholder_image_src() ],
                'conditions' => $this->build_negated_conditions( self::MEDIA_SLIDER_VARIANTS ),
            ] );

            $slides = new Repeater();

            $slides->add_control( 'slide_image', [
                'label'   => esc_html__( 'Image', 'woolentor' ),
                'type'    => Controls_Manager::MEDIA,
                'default' => [ 'url' => Utils::get_placeholder_image_src() ],
            ] );

            $slides->add_control( 'slide_tag_label', [
                'label'       => esc_html__( 'Badge Label', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Coming Soon', 'woolentor' ),
                'label_block' => true,
            ] );

            $slides->add_control( 'slide_tag_title', [
                'label'       => esc_html__( 'Badge Title', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'ChronoX Limited Edition', 'woolentor' ),
                'label_block' => true,
            ] );

            $slides->add_control( 'slide_tag_meta', [
                'label'       => esc_html__( 'Badge Detail', 'woolentor' ),
                'type'        => Controls_Manager::TEXTAREA,
                'rows'        => 2,
                'default'     => esc_html__( '32 units at launch · from $429', 'woolentor' ),
            ] );

            $this->add_control( 'media_slides', [
                'label'       => esc_html__( 'Slides', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $slides->get_controls(),
                'title_field' => '{{{ slide_tag_title || slide_tag_label }}}',
                'description' => esc_html__( 'Each slide carries its own badge, the way the reference swaps it as the panel changes. One row is a still image; two or more make a slider.', 'woolentor' ),
                'conditions'  => $this->build_conditions( self::MEDIA_SLIDER_VARIANTS ),
                'default'     => [
                    [
                        'slide_tag_label' => esc_html__( 'Coming Soon', 'woolentor' ),
                        'slide_tag_title' => esc_html__( 'ChronoX Limited Edition', 'woolentor' ),
                        'slide_tag_meta'  => esc_html__( '32 units at launch · from $429', 'woolentor' ),
                        'slide_image'     => [ 'url' => Utils::get_placeholder_image_src() ],
                    ],
                ],
            ] );

        $this->end_controls_section();
    }

    private function register_button_controls() {
        $this->start_controls_section( 'section_buttons', [
            'label'      => esc_html__( 'Buttons', 'woolentor' ),
            'conditions' => $this->unlocked(),
        ] );

            $this->add_control( 'primary_cta_text', [
                'label'       => esc_html__( 'Primary Button', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Shop the Sale', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'Leave empty to hide it.', 'woolentor' ),
            ] );

            $this->add_control( 'primary_cta_link', [
                'label'         => esc_html__( 'Primary Link', 'woolentor' ),
                'type'          => Controls_Manager::URL,
                'placeholder'   => 'https://example.com',
                'show_external' => true,
            ] );

            $this->add_control( 'secondary_cta_text', [
                'label'       => esc_html__( 'Secondary Button', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'separator'   => 'before',
                'placeholder' => esc_html__( 'Watch the Film', 'woolentor' ),
                'label_block' => true,
            ] );

            $this->add_control( 'secondary_cta_link', [
                'label'         => esc_html__( 'Secondary Link', 'woolentor' ),
                'type'          => Controls_Manager::URL,
                'placeholder'   => 'https://example.com',
                'show_external' => true,
            ] );

        $this->end_controls_section();
    }

    private function register_extras_controls() {

        // — Stats row —
        $stats = new Repeater();

        $stats->add_control( 'stat_value', [
            'label'   => esc_html__( 'Value', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => '200+',
        ] );

        $stats->add_control( 'stat_label', [
            'label'   => esc_html__( 'Label', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'new designs', 'woolentor' ),
        ] );

        $this->start_controls_section( 'section_stats', [
            'label'      => esc_html__( 'Stats Row', 'woolentor' ),
            'conditions' => $this->unlocked_for( self::STATS_VARIANTS ),
        ] );

            $this->add_control( 'stats', [
                'label'       => esc_html__( 'Stats', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $stats->get_controls(),
                'title_field' => '{{{ stat_value }}} {{{ stat_label }}}',
                'default'     => [
                    [ 'stat_value' => '200+',  'stat_label' => esc_html__( 'new designs', 'woolentor' ) ],
                    [ 'stat_value' => '100%',  'stat_label' => esc_html__( 'organic', 'woolentor' ) ],
                    [ 'stat_value' => esc_html__( 'Free', 'woolentor' ), 'stat_label' => esc_html__( 'shipping $150+', 'woolentor' ) ],
                ],
            ] );

        $this->end_controls_section();

        // — Floating product cards —
        $cards = new Repeater();

        $cards->add_control( 'card_image', [
            'label'   => esc_html__( 'Image', 'woolentor' ),
            'type'    => Controls_Manager::MEDIA,
            'default' => [ 'url' => Utils::get_placeholder_image_src() ],
        ] );

        $cards->add_control( 'card_title', [
            'label'       => esc_html__( 'Title', 'woolentor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Oslo Sectional', 'woolentor' ),
            'label_block' => true,
        ] );

        $cards->add_control( 'card_price', [
            'label'   => esc_html__( 'Price', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => '$1,743',
        ] );

        $cards->add_control( 'card_old_price', [
            'label'   => esc_html__( 'Old Price', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => '$2,490',
        ] );

        $cards->add_control( 'card_badge', [
            'label'   => esc_html__( 'Badge', 'woolentor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( '30% Off', 'woolentor' ),
        ] );

        $cards->add_control( 'card_link', [
            'label'         => esc_html__( 'Link', 'woolentor' ),
            'type'          => Controls_Manager::URL,
            'show_external' => true,
        ] );

        $this->start_controls_section( 'section_cards', [
            'label'      => esc_html__( 'Product Cards', 'woolentor' ),
            'conditions' => $this->unlocked_for( self::CARDS_VARIANTS ),
        ] );

            $this->add_control( 'cards_notice', [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__( 'These are static cards you fill in yourself, not a product query. For a real WooCommerce product use the Feature Product widget.', 'woolentor' ),
                'content_classes' => 'elementor-descriptor',
            ] );

            $this->add_control( 'cards_label', [
                'label'       => esc_html__( 'Row Heading', 'woolentor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'On Sale Now', 'woolentor' ),
                'label_block' => true,
                'description' => esc_html__( 'The small label above the card row.', 'woolentor' ),
            ] );

            $this->add_control( 'cards_per_view', [
                'label'   => esc_html__( 'Cards Per View', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 3,
                'min'     => 1,
                'max'     => 6,
                'step'    => 1,
            ] );

            $this->add_control( 'cards_per_view_tablet', [
                'label'   => esc_html__( 'Cards Per View (Tablet)', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 2,
                'min'     => 1,
                'max'     => 6,
                'step'    => 1,
            ] );

            $this->add_control( 'cards_per_view_mobile', [
                'label'   => esc_html__( 'Cards Per View (Mobile)', 'woolentor' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 1,
                'min'     => 1,
                'max'     => 6,
                'step'    => 1,
            ] );

            $this->add_control( 'cards', [
                'label'       => esc_html__( 'Cards', 'woolentor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $cards->get_controls(),
                'title_field' => '{{{ card_title }}}',
                'default'     => [
                    [ 'card_title' => esc_html__( 'Oslo Sectional', 'woolentor' ) ],
                    [ 'card_title' => esc_html__( 'Arne Lounge Chair', 'woolentor' ) ],
                    [ 'card_title' => esc_html__( 'Koto Accent Chair', 'woolentor' ) ],
                ],
            ] );

        $this->end_controls_section();
    }

    /** Content-tab notice section shown in place of Content when a Pro variant is selected. */
    private function register_pro_content_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_notice', [
            'label'     => esc_html__( 'Content', 'woolentor' ),
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_upgrade_notice', $condition );
        $this->end_controls_section();
    }

    /** Style-tab notice section shown when a Pro variant is selected. */
    private function register_pro_style_notice() {
        $condition = [ 'variant' => [ 'v2', 'v3' ] ];

        $this->start_controls_section( 'section_pro_style_notice', [
            'label'     => esc_html__( 'Pro Feature', 'woolentor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => $condition,
        ] );
            woolentor_upgrade_pro_notice( $this, 'pro_style_upgrade_notice', $condition, [
                'message' => __( 'Style controls are only available in ShopLentor Pro. Upgrade to customize colors, typography, and spacing for this variant.', 'woolentor' ),
            ] );
        $this->end_controls_section();
    }

    // ── Style tab ─────────────────────────────────────────────────────────────

    /**
     * All 12 templates share the same semantic classes, so every selector here is a single
     * short string rather than a per-variant selector list. Every default lives in the pack
     * stylesheet — selecting a Style and a Variant must reproduce the reference with nothing
     * touched here.
     */
    private function register_style_controls() {
        $unlocked = $this->unlocked();

        // — Section —
        $this->start_controls_section( 'style_section', [
            'label'      => esc_html__( 'Section', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_group_control( Group_Control_Background::get_type(), [
                'name'     => 'section_bg',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wl-cb-inner',
            ] );

            $this->add_responsive_control( 'section_min_height', [
                'label'      => esc_html__( 'Minimum Height', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [
                    'px' => [ 'min' => 120, 'max' => 900 ],
                    'vh' => [ 'min' => 10,  'max' => 100 ],
                ],
                'selectors'  => [ '{{WRAPPER}} .wl-cb-inner' => 'min-height: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'section_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-cb-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'section_border',
                'selector' => '{{WRAPPER}} .wl-cb-inner',
            ] );

            $this->add_responsive_control( 'section_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-cb-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'section_gap', [
                'label'      => esc_html__( 'Gap Between Panels', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-cb-inner' => 'gap: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Eyebrow —
        $this->start_controls_section( 'style_eyebrow', [
            'label'      => esc_html__( 'Eyebrow', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'eyebrow_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-eyebrow' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'eyebrow_dot_color', [
                'label'     => esc_html__( 'Dot Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-dot' => 'background-color: {{VALUE}};' ],
                'condition' => [ 'eyebrow_live_dot' => 'yes' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'eyebrow_typography',
                'selector' => '{{WRAPPER}} .wl-cb-eyebrow',
            ] );

            $this->add_responsive_control( 'eyebrow_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-cb-eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Display figure (variant-specific) —
        $this->start_controls_section( 'style_figure', [
            'label'      => esc_html__( 'Display Figure', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->unlocked_for( self::NUMBER_VARIANTS ),
        ] );

            $this->add_control( 'figure_color', [
                'label'     => esc_html__( 'Figure Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-figure-value' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'figure_typography',
                'selector' => '{{WRAPPER}} .wl-cb-figure-value',
            ] );

            $this->add_control( 'figure_suffix_color', [
                'label'     => esc_html__( 'Suffix Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-figure-suffix' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'figure_suffix_typography',
                'selector' => '{{WRAPPER}} .wl-cb-figure-suffix',
            ] );

        $this->end_controls_section();

        // — Headline —
        $this->start_controls_section( 'style_headline', [
            'label'      => esc_html__( 'Headline', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'headline_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-headline' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'headline_accent_color', [
                'label'       => esc_html__( 'Accent Color', 'woolentor' ),
                'type'        => Controls_Manager::COLOR,
                'description' => esc_html__( 'Applies to the <em> part of the headline.', 'woolentor' ),
                'selectors'   => [ '{{WRAPPER}} .wl-cb-headline em' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'headline_typography',
                'selector' => '{{WRAPPER}} .wl-cb-headline',
            ] );

            $this->add_responsive_control( 'headline_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-cb-headline' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Description —
        $this->start_controls_section( 'style_desc', [
            'label'      => esc_html__( 'Description', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'desc_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-desc' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'desc_typography',
                'selector' => '{{WRAPPER}} .wl-cb-desc',
            ] );

            $this->add_responsive_control( 'desc_max_width', [
                'label'      => esc_html__( 'Max Width', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => 160, 'max' => 900 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-cb-desc' => 'max-width: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'desc_spacing', [
                'label'      => esc_html__( 'Spacing Below', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-cb-desc' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
            ] );

        $this->end_controls_section();

        // — Countdown —
        $this->start_controls_section( 'style_countdown', [
            'label'      => esc_html__( 'Countdown', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
            'condition'  => [ 'countdown_show' => 'yes' ],
        ] );

            $this->add_control( 'cd_cell_bg', [
                'label'     => esc_html__( 'Cell Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-pack-countdown-unit' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Border::get_type(), [
                'name'     => 'cd_cell_border',
                'selector' => '{{WRAPPER}} .wl-pack-countdown-unit',
            ] );

            $this->add_responsive_control( 'cd_cell_radius', [
                'label'      => esc_html__( 'Cell Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pack-countdown-unit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'cd_cell_padding', [
                'label'      => esc_html__( 'Cell Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-pack-countdown-unit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'cd_cell_min_width', [
                'label'      => esc_html__( 'Cell Minimum Width', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pack-countdown-unit' => 'min-width: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_responsive_control( 'cd_gap', [
                'label'      => esc_html__( 'Gap', 'woolentor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'selectors'  => [ '{{WRAPPER}} .wl-pack-countdown' => '--wl-pack-countdown-gap: {{SIZE}}{{UNIT}};' ],
            ] );

            $this->add_control( 'cd_num_color', [
                'label'     => esc_html__( 'Number Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-pack-countdown-num' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'cd_num_typography',
                'selector' => '{{WRAPPER}} .wl-pack-countdown-num',
            ] );

            $this->add_control( 'cd_label_color', [
                'label'     => esc_html__( 'Label Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-pack-countdown-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'cd_label_typography',
                'selector' => '{{WRAPPER}} .wl-pack-countdown-label',
            ] );

            $this->add_control( 'cd_sep_color', [
                'label'     => esc_html__( 'Separator Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [ '{{WRAPPER}} .wl-pack-countdown-sep' => 'color: {{VALUE}};' ],
                'condition' => [ 'countdown_separator!' => '' ],
            ] );

        $this->end_controls_section();

        // — Buttons —
        $this->start_controls_section( 'style_buttons', [
            'label'      => esc_html__( 'Buttons', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->start_controls_tabs( 'button_style_tabs' );

                $this->start_controls_tab( 'btn_primary_tab', [ 'label' => esc_html__( 'Primary', 'woolentor' ) ] );

                    $this->add_control( 'btn_color', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--primary' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--primary' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_color_hover', [
                        'label'     => esc_html__( 'Hover Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--primary:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn_bg_hover', [
                        'label'     => esc_html__( 'Hover Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--primary:hover' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'btn_border',
                        'selector' => '{{WRAPPER}} .wl-cb-btn--primary',
                    ] );

                $this->end_controls_tab();

                $this->start_controls_tab( 'btn_secondary_tab', [ 'label' => esc_html__( 'Secondary', 'woolentor' ) ] );

                    $this->add_control( 'btn2_color', [
                        'label'     => esc_html__( 'Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--secondary' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn2_bg', [
                        'label'     => esc_html__( 'Background', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--secondary' => 'background-color: {{VALUE}};' ],
                    ] );

                    $this->add_control( 'btn2_color_hover', [
                        'label'     => esc_html__( 'Hover Color', 'woolentor' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [ '{{WRAPPER}} .wl-cb-btn--secondary:hover' => 'color: {{VALUE}};' ],
                    ] );

                    $this->add_group_control( Group_Control_Border::get_type(), [
                        'name'     => 'btn2_border',
                        'selector' => '{{WRAPPER}} .wl-cb-btn--secondary',
                    ] );

                $this->end_controls_tab();

            $this->end_controls_tabs();

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'      => 'btn_typography',
                'selector'  => '{{WRAPPER}} .wl-cb-btn',
                'separator' => 'before',
            ] );

            $this->add_responsive_control( 'btn_padding', [
                'label'      => esc_html__( 'Padding', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-cb-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'btn_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-cb-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

        $this->end_controls_section();

        // — Coupon (variant-specific) —
        $this->start_controls_section( 'style_coupon', [
            'label'      => esc_html__( 'Coupon Line', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->unlocked_for( self::COUPON_VARIANTS ),
        ] );

            $this->add_control( 'coupon_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-coupon' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'coupon_bg', [
                'label'     => esc_html__( 'Background', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-coupon' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'coupon_typography',
                'selector' => '{{WRAPPER}} .wl-cb-coupon',
            ] );

        $this->end_controls_section();

        // — Stats (variant-specific) —
        $this->start_controls_section( 'style_stats', [
            'label'      => esc_html__( 'Stats Row', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->unlocked_for( self::STATS_VARIANTS ),
        ] );

            $this->add_control( 'stat_value_color', [
                'label'     => esc_html__( 'Value Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-stat-value' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'stat_value_typography',
                'selector' => '{{WRAPPER}} .wl-cb-stat-value',
            ] );

            $this->add_control( 'stat_label_color', [
                'label'     => esc_html__( 'Label Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-stat-label' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'stat_label_typography',
                'selector' => '{{WRAPPER}} .wl-cb-stat-label',
            ] );

            $this->add_control( 'stat_divider_color', [
                'label'     => esc_html__( 'Divider Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-stat + .wl-cb-stat' => 'border-color: {{VALUE}};' ],
            ] );

        $this->end_controls_section();

        // — Media (every variant that has one) —
        $this->start_controls_section( 'style_media', [
            'label'      => esc_html__( 'Media', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $this->build_and_conditions(
                $unlocked,
                $this->build_negated_conditions( self::NOMEDIA_VARIANTS )
            ),
        ] );

            $this->add_responsive_control( 'media_position', [
                'label'   => esc_html__( 'Focal Point', 'woolentor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    ''       => esc_html__( 'Default (center)', 'woolentor' ),
                    'top'    => esc_html__( 'Top', 'woolentor' ),
                    'bottom' => esc_html__( 'Bottom', 'woolentor' ),
                    'left'   => esc_html__( 'Left', 'woolentor' ),
                    'right'  => esc_html__( 'Right', 'woolentor' ),
                ],
                'selectors' => [ '{{WRAPPER}} .wl-cb-img' => 'object-position: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'media_radius', [
                'label'      => esc_html__( 'Border Radius', 'woolentor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wl-cb-media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ] );

            $this->add_control( 'media_tag_color', [
                'label'      => esc_html__( 'Tag Color', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'separator'  => 'before',
                'selectors'  => [ '{{WRAPPER}} .wl-cb-media-tag' => 'color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::MEDIA_SLIDER_VARIANTS ),
            ] );

            $this->add_control( 'media_tag_bg', [
                'label'      => esc_html__( 'Tag Background', 'woolentor' ),
                'type'       => Controls_Manager::COLOR,
                'selectors'  => [ '{{WRAPPER}} .wl-cb-media-tag' => 'background-color: {{VALUE}};' ],
                'conditions' => $this->build_conditions( self::MEDIA_SLIDER_VARIANTS ),
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'       => 'media_tag_typography',
                'selector'   => '{{WRAPPER}} .wl-cb-media-tag',
                'conditions' => $this->build_conditions( self::MEDIA_SLIDER_VARIANTS ),
            ] );

        $this->end_controls_section();

        // — Fine print —
        $this->start_controls_section( 'style_note', [
            'label'      => esc_html__( 'Fine Print', 'woolentor' ),
            'tab'        => Controls_Manager::TAB_STYLE,
            'conditions' => $unlocked,
        ] );

            $this->add_control( 'note_color', [
                'label'     => esc_html__( 'Color', 'woolentor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wl-cb-note' => 'color: {{VALUE}};' ],
            ] );

            $this->add_group_control( Group_Control_Typography::get_type(), [
                'name'     => 'note_typography',
                'selector' => '{{WRAPPER}} .wl-cb-note',
            ] );

        $this->end_controls_section();
    }

    // ── Render helpers ────────────────────────────────────────────────────────

    /**
     * The headline, with the small set of inline tags the reference headlines use. Line breaks
     * typed in the textarea become <br>, so a two-line headline needs no HTML from the user.
     *
     * @param  string $text
     * @return string
     */
    protected function headline( $text ) {
        $allowed = [ 'br' => [], 'em' => [], 'strong' => [], 'b' => [], 'i' => [], 'span' => [ 'class' => [] ] ];
        return nl2br( wp_kses( $text, $allowed ) );
    }

    /**
     * One call to action. Renders nothing without a label, so a variant that shows only a
     * primary button is simply left with an empty secondary field.
     *
     * @param  array  $cta   From build_data(): text, url, is_external, nofollow.
     * @param  string $kind  'primary' or 'secondary'.
     * @param  string $icon  Raw inline SVG, or empty.
     * @return string
     */
    protected function button( array $cta, $kind = 'primary', $icon = '' ) {
        if ( '' === $cta['text'] ) {
            return '';
        }

        $class = 'wl-cb-btn wl-cb-btn--' . $kind;
        $inner = '<span>' . esc_html( $cta['text'] ) . '</span>' . $icon;

        if ( '' === $cta['url'] ) {
            return '<span class="' . esc_attr( $class ) . '">' . $inner . '</span>';
        }

        $rel    = [];
        $target = '';
        if ( ! empty( $cta['is_external'] ) ) {
            $target = ' target="_blank"';
            $rel[]  = 'noopener';
            $rel[]  = 'noreferrer';
        }
        if ( ! empty( $cta['nofollow'] ) ) {
            $rel[] = 'nofollow';
        }

        return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $cta['url'] ) . '"' . $target
            . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' ) . '>' . $inner . '</a>';
    }

    /**
     * The countdown block. Emits only the markup contract; WLPackCountdown in pack-widgets.js
     * drives it, and the numbers rendered here are the server's first frame so the block does
     * not flash empty before the script runs.
     *
     * @param  array $cd  From build_data()['countdown'].
     * @return string
     */
    protected function countdown( array $cd ) {
        if ( ! $cd['show'] || empty( $cd['units'] ) ) {
            return '';
        }

        $remaining = max( 0, $cd['end'] - time() );
        $divisors  = [ 'days' => 86400, 'hours' => 3600, 'minutes' => 60, 'seconds' => 1 ];

        $out = '<div class="wl-pack-countdown wl-cb-countdown" role="timer"'
            . ' data-wl-end="' . esc_attr( $cd['end'] ) . '"'
            . ' data-wl-expired="' . esc_attr( $cd['action'] ) . '"'
            . ' data-wl-expired-text="' . esc_attr( $cd['text'] ) . '"'
            . ' aria-label="' . esc_attr__( 'Time remaining in this offer', 'woolentor' ) . '">';

        // A lead-in, not a unit: it sits outside the loop so no separator is drawn after it.
        if ( '' !== $cd['lead'] ) {
            $out .= '<span class="wl-pack-countdown-lead">' . esc_html( $cd['lead'] ) . '</span>';
        }

        $first = true;
        foreach ( $cd['units'] as $unit ) {
            if ( ! $first && '' !== $cd['separator'] ) {
                $out .= '<span class="wl-pack-countdown-sep" aria-hidden="true">'
                    . esc_html( $cd['separator'] ) . '</span>';
            }

            // The first unit rendered absorbs everything above it, matching the running-total
            // format directive WLPackCountdown hands the library.
            $value      = intdiv( $remaining, $divisors[ $unit ] );
            $remaining -= $value * $divisors[ $unit ];
            $first      = false;

            $out .= '<span class="wl-pack-countdown-unit">'
                . '<span class="wl-pack-countdown-num" data-unit="' . esc_attr( $unit ) . '">'
                . esc_html( str_pad( (string) $value, 2, '0', STR_PAD_LEFT ) )
                . '</span>';

            if ( '' !== $cd['labels'][ $unit ] ) {
                $out .= '<span class="wl-pack-countdown-label">'
                    . esc_html( $cd['labels'][ $unit ] ) . '</span>';
            }

            $out .= '</span>';
        }

        return $out . '</div>';
    }

    /**
     * The photograph plus its optional corner tag.
     *
     * @param  array  $data
     * @param  string $extra  Markup to place over the image inside the same wrapper — modern v3
     *                        floats its product cards there, and they have to share the media
     *                        panel's stacking context to sit above the gradient.
     * @return string
     */
    protected function media( array $data, $extra = '', array $slider = [] ) {
        $slides = $data['media_slides'] ?? [];

        if ( ! empty( $slides ) ) {
            return $this->media_slider( $data, $slides, $extra, $slider );
        }

        if ( '' === $data['media_image'] ) {
            return '';
        }

        $out = '<div class="wl-cb-media"><img class="wl-cb-img" src="' . esc_url( $data['media_image'] )
            . '" alt="' . esc_attr( $data['media_alt'] ) . '" loading="lazy" decoding="async">';

        return $out . $extra . '</div>';
    }

    /**
     * The media panel as a run of slides, each carrying its own badge.
     *
     * The badge lives inside the slide rather than beside the track, so it changes with the
     * photograph for free — the reference does the same swap from JavaScript, keyed off data
     * attributes. One row renders as a still image with its badge; two or more become a slider
     * on the shared WLPackSlider.
     *
     * @param  array  $data
     * @param  array  $slides
     * @param  string $extra
     * @param  array  $slider  Overrides for the settings blob — arrows vs dots, mainly.
     * @return string
     */
    private function media_slider( array $data, array $slides, $extra, array $slider ) {
        $out = '<div class="wl-cb-media">';

        if ( count( $slides ) > 1 ) {
            $out .= '<div class="wl-cb-media-track" data-wl-slider="true" data-slider-settings="'
                . esc_attr( wp_json_encode( array_merge( [
                    'items'         => 1,
                    'scroll'        => 1,
                    'arrows'        => false,
                    'dots'          => true,
                    'fade'          => true,
                    'tablet_width'  => 1025,
                    'tablet_items'  => 1,
                    'tablet_scroll' => 1,
                    'mobile_width'  => 768,
                    'mobile_items'  => 1,
                    'mobile_scroll' => 1,
                ], $slider ) ) ) . '">';
        }

        foreach ( $slides as $slide ) {
            if ( '' === $slide['image'] ) {
                continue;
            }

            $out .= count( $slides ) > 1 ? '<div class="wl-cb-media-slide">' : '';
            $out .= '<img class="wl-cb-img" src="' . esc_url( $slide['image'] ) . '" alt="'
                . esc_attr( $slide['tag_title'] ?: $data['media_alt'] ) . '" loading="lazy" decoding="async">';
            $out .= $this->media_badge( $slide );
            $out .= count( $slides ) > 1 ? '</div>' : '';
        }

        if ( count( $slides ) > 1 ) {
            $out .= '</div>';
        }

        return $out . $extra . '</div>';
    }

    /**
     * One slide's badge — a dotted label, a serif title and a line of detail. Any of the three
     * may be empty; the badge itself only appears when at least one is filled.
     *
     * @param  array $slide
     * @return string
     */
    private function media_badge( array $slide ) {
        if ( '' === $slide['tag_label'] && '' === $slide['tag_title'] && '' === $slide['tag_meta'] ) {
            return '';
        }

        $out = '<span class="wl-cb-media-tag">';

        if ( '' !== $slide['tag_label'] ) {
            $out .= '<span class="wl-cb-media-tag-label"><span class="wl-cb-media-tag-dot" aria-hidden="true"></span>'
                . esc_html( $slide['tag_label'] ) . '</span>';
        }
        if ( '' !== $slide['tag_title'] ) {
            $out .= '<span class="wl-cb-media-tag-title">' . esc_html( $slide['tag_title'] ) . '</span>';
        }
        if ( '' !== $slide['tag_meta'] ) {
            $out .= '<span class="wl-cb-media-tag-meta">' . nl2br( esc_html( $slide['tag_meta'] ) ) . '</span>';
        }

        return $out . '</span>';
    }

    /**
     * The thumbnail picker that drives the media slider — magazine v3's category strip.
     *
     * The buttons sit in the copy column while the photograph they select is in the panel opposite,
     * so they cannot be Slick's own dots: those are appended inside the slider. They carry a slide
     * index instead, and WLCampaignBannerSlider hands it to slickGoTo, keeping `is-active` in sync
     * from Slick's own afterChange — the slider stays the single source of truth for which slide is
     * showing.
     *
     * Each button reuses its slide's photograph as the thumbnail. The reference ships a second,
     * smaller crop per category; one image control per slide is the better trade for a widget.
     *
     * @param  array $slides
     * @return string
     */
    protected function media_picker( array $slides ) {
        $slides = array_values( array_filter( $slides, fn( $s ) => '' !== $s['image'] ) );

        if ( count( $slides ) < 2 ) {
            return '';
        }

        $out = '<div class="wl-cb-picker" role="tablist" aria-label="'
            . esc_attr__( 'Choose a photograph', 'woolentor' ) . '">';

        foreach ( $slides as $i => $slide ) {
            $label = '' !== $slide['tag_label'] ? $slide['tag_label'] : $slide['tag_title'];

            $out .= '<button type="button" class="wl-cb-picker-btn' . ( 0 === $i ? ' is-active' : '' )
                . '" data-wl-goto="' . esc_attr( $i ) . '" role="tab" aria-selected="'
                . ( 0 === $i ? 'true' : 'false' ) . '">'
                . '<span class="wl-cb-picker-img"><img src="' . esc_url( $slide['image'] ) . '" alt="'
                . esc_attr( $label ) . '" loading="lazy" decoding="async"></span>';

            if ( '' !== $label ) {
                $out .= '<span class="wl-cb-picker-label">' . esc_html( $label ) . '</span>';
            }

            $out .= '</button>';
        }

        return $out . '</div>';
    }

    /**
     * The oversized figure beside the headline — modern v3's `30 %`, luxury v2's `N°02`.
     *
     * @param  array $data
     * @return string
     */
    protected function figure( array $data ) {
        if ( '' === $data['figure'] && '' === $data['figure_suffix'] ) {
            return '';
        }

        $out = '<span class="wl-cb-figure">';

        if ( '' !== $data['figure'] ) {
            $out .= '<span class="wl-cb-figure-value">' . esc_html( $data['figure'] ) . '</span>';
        }

        // The reference stacks its suffix — `%` over `Discound`. Line breaks typed in the field
        // survive, so the user gets that without writing HTML.
        if ( '' !== $data['figure_suffix'] ) {
            $out .= '<span class="wl-cb-figure-suffix">' . $this->headline( $data['figure_suffix'] ) . '</span>';
        }

        return $out . '</span>';
    }

    /**
     * The floating product cards modern v3 lays over its photograph.
     *
     * These are static content the user fills in, not a WooCommerce query — a real product
     * belongs to the Feature Product widget.
     *
     * The row is a slider, driven by the same WLPackSlider every other pack slider uses, so this
     * widget contributes no slider code of its own. It is skipped when the cards already fit,
     * and pack-widgets-base.css keeps the un-initialised row scrollable so a page without the
     * script still works.
     *
     * @param  array  $cards
     * @param  string $label   Small heading above the row.
     * @param  array  $slider  items / tablet_items / mobile_items.
     * @return string
     */
    protected function cards( array $cards, $label = '', array $slider = [] ) {
        if ( empty( $cards ) ) {
            return '';
        }

        $out = '<div class="wl-cb-cards">';

        if ( '' !== $label ) {
            $out .= '<span class="wl-cb-cards-label">' . esc_html( $label ) . '</span>';
        }

        // WLPackSlider reads these; it skips the slider entirely when the cards already fit,
        // and the CSS fallback keeps the row usable if the script never runs.
        $per_view = [
            'items'        => $slider['items'] ?? 3,
            'tablet_items' => $slider['tablet_items'] ?? 2,
            'mobile_items' => $slider['mobile_items'] ?? 1,
        ];

        // The same three counts go to both engines: Slick reads the JSON, and the CSS fallback
        // reads the custom properties. That way the cards are the same size whether the slider
        // runs or is skipped, which is the whole point of the fallback.
        $out .= '<div class="wl-cb-cards-track" data-wl-slider="true"'
            . ' style="--wl-cb-cards-view:' . absint( $per_view['items'] )
            . ';--wl-cb-cards-view-tablet:' . absint( $per_view['tablet_items'] )
            . ';--wl-cb-cards-view-mobile:' . absint( $per_view['mobile_items'] ) . '"'
            . ' data-slider-settings="'
            . esc_attr( wp_json_encode( [
                'items'         => $per_view['items'],
                'scroll'        => 1,
                'tablet_width'  => 1025,
                'tablet_items'  => $per_view['tablet_items'],
                'tablet_scroll' => 1,
                'mobile_width'  => 768,
                'mobile_items'  => $per_view['mobile_items'],
                'mobile_scroll' => 1,
            ] ) ) . '">';

        foreach ( $cards as $card ) {
            $tag   = '' === $card['url'] ? 'div' : 'a';
            $attrs = '';

            if ( 'a' === $tag ) {
                $rel = [];
                if ( ! empty( $card['is_external'] ) ) {
                    $attrs = ' target="_blank"';
                    $rel[] = 'noopener';
                    $rel[] = 'noreferrer';
                }
                if ( ! empty( $card['nofollow'] ) ) {
                    $rel[] = 'nofollow';
                }
                $attrs = ' href="' . esc_url( $card['url'] ) . '"' . $attrs
                    . ( $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '' );
            }

            // The slide is the wrapper, not the card. Slick makes its direct children the
            // slides and the gutter is padding on them — put that padding on the card itself
            // and it lands inside the border, so the cards touch.
            $out .= '<div class="wl-cb-card-slide"><' . $tag . ' class="wl-cb-card"' . $attrs . '>';

            if ( '' !== $card['image'] ) {
                $out .= '<img class="wl-cb-card-img" src="' . esc_url( $card['image'] ) . '" alt="'
                    . esc_attr( $card['title'] ) . '" loading="lazy" decoding="async">';
            }

            $out .= '<span class="wl-cb-card-info">';

            if ( '' !== $card['badge'] ) {
                $out .= '<span class="wl-cb-card-badge">' . esc_html( $card['badge'] ) . '</span>';
            }
            if ( '' !== $card['title'] ) {
                $out .= '<span class="wl-cb-card-name">' . esc_html( $card['title'] ) . '</span>';
            }
            if ( '' !== $card['price'] || '' !== $card['old_price'] ) {
                $out .= '<span class="wl-cb-card-prices">';
                if ( '' !== $card['price'] ) {
                    $out .= '<span class="wl-cb-card-price">' . esc_html( $card['price'] ) . '</span>';
                }
                if ( '' !== $card['old_price'] ) {
                    $out .= '<s class="wl-cb-card-old">' . esc_html( $card['old_price'] ) . '</s>';
                }
                $out .= '</span>';
            }

            $out .= '</span></' . $tag . '></div>';
        }

        return $out . '</div></div>';
    }

    /**
     * The stats row — modern v2's "200+ new designs · 100% organic · Free shipping".
     *
     * @param  array $stats
     * @return string
     */
    protected function stats( array $stats ) {
        if ( empty( $stats ) ) {
            return '';
        }

        $out = '<div class="wl-cb-stats">';
        foreach ( $stats as $stat ) {
            $out .= '<span class="wl-cb-stat">'
                . '<span class="wl-cb-stat-value">' . esc_html( $stat['value'] ) . '</span> '
                . '<span class="wl-cb-stat-label">' . esc_html( $stat['label'] ) . '</span>'
                . '</span>';
        }
        return $out . '</div>';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Normalize settings into the flat $data contract the templates expect.
     * Values stay raw; the helpers above and the templates are the escaping point.
     *
     * @param  array $settings
     * @return array
     */
    private function build_data( array $settings, $style = '', $variant = '' ) {
        $stats = [];
        foreach ( (array) ( $settings['stats'] ?? [] ) as $row ) {
            $stats[] = [
                'value' => $row['stat_value'] ?? '',
                'label' => $row['stat_label'] ?? '',
            ];
        }

        $cards = [];
        foreach ( (array) ( $settings['cards'] ?? [] ) as $row ) {
            $cards[] = [
                'image'       => $row['card_image']['url'] ?? '',
                'title'       => $row['card_title'] ?? '',
                'price'       => $row['card_price'] ?? '',
                'old_price'   => $row['card_old_price'] ?? '',
                'badge'       => $row['card_badge'] ?? '',
                'url'         => $row['card_link']['url'] ?? '',
                'is_external' => ! empty( $row['card_link']['is_external'] ),
                'nofollow'    => ! empty( $row['card_link']['nofollow'] ),
            ];
        }

        return [
            'eyebrow'        => $settings['eyebrow'] ?? '',
            'live_dot'       => 'yes' === ( $settings['eyebrow_live_dot'] ?? '' ),
            'figure'         => $settings['display_number'] ?? '',
            'figure_suffix'  => $settings['display_suffix'] ?? '',
            'headline'       => $settings['headline'] ?? '',
            'description'    => $settings['description'] ?? '',
            'coupon'         => $settings['coupon_text'] ?? '',
            'note'           => $settings['note'] ?? '',
            'media_image'    => $settings['media_image']['url'] ?? '',
            'media_alt'      => $settings['media_image']['alt'] ?? ( $settings['headline'] ?? '' ),
            // Hiding a control does not clear what is stored under it, so every variant-specific
            // field is cleared here alongside its condition — otherwise a value typed on one
            // variant keeps rendering after switching to another.
            'media_slides'   => in_array( $variant, self::MEDIA_SLIDER_VARIANTS[ $style ] ?? [], true )
                ? array_map(
                    fn( $row ) => [
                        'image'     => $row['slide_image']['url'] ?? '',
                        'tag_label' => $row['slide_tag_label'] ?? '',
                        'tag_title' => $row['slide_tag_title'] ?? '',
                        'tag_meta'  => $row['slide_tag_meta'] ?? '',
                    ],
                    (array) ( $settings['media_slides'] ?? [] )
                )
                : [],
            'primary'        => [
                'text'        => $settings['primary_cta_text'] ?? '',
                'url'         => $settings['primary_cta_link']['url'] ?? '',
                'is_external' => ! empty( $settings['primary_cta_link']['is_external'] ),
                'nofollow'    => ! empty( $settings['primary_cta_link']['nofollow'] ),
            ],
            'secondary'      => [
                'text'        => $settings['secondary_cta_text'] ?? '',
                'url'         => $settings['secondary_cta_link']['url'] ?? '',
                'is_external' => ! empty( $settings['secondary_cta_link']['is_external'] ),
                'nofollow'    => ! empty( $settings['secondary_cta_link']['nofollow'] ),
            ],
            'stats'          => $stats,
            'cards'          => $cards,
            'cards_label'    => $settings['cards_label'] ?? '',
            'cards_slider'   => [
                'items'        => max( 1, absint( $settings['cards_per_view'] ?? 3 ) ),
                'tablet_items' => max( 1, absint( $settings['cards_per_view_tablet'] ?? 2 ) ),
                'mobile_items' => max( 1, absint( $settings['cards_per_view_mobile'] ?? 1 ) ),
            ],
            'countdown'      => $this->build_countdown( $settings, $style, $variant ),
        ];
    }

    /**
     * Countdown settings, with the deadline resolved to a UTC timestamp.
     *
     * The DATE_TIME control hands back a wall-clock string in the site's timezone.
     * get_gmt_from_date() converts it using the site's offset, so the instant sent to the
     * browser is unambiguous — a visitor whose clock is wrong still sees the right countdown,
     * and a cached page does not run long.
     *
     * @param  array $settings
     * @return array
     */
    private function build_countdown( array $settings, $style = '', $variant = '' ) {
        $units = array_values( array_intersect(
            self::UNITS,
            array_map( 'sanitize_key', (array) ( $settings['countdown_units'] ?? [] ) )
        ) );

        $raw = trim( (string) ( $settings['countdown_end'] ?? '' ) );
        $end = $raw ? (int) strtotime( get_gmt_from_date( $raw ) . ' UTC' ) : 0;

        $action = sanitize_key( $settings['countdown_expired_action'] ?? 'hide' );

        return [
            'show'      => 'yes' === ( $settings['countdown_show'] ?? '' ) && $end > 0 && ! empty( $units ),
            'end'       => $end,
            'units'     => $units,
            'lead'      => $settings['countdown_label'] ?? '',
            'separator' => $this->countdown_separator( $settings, $style, $variant ),
            'action'    => in_array( $action, [ 'hide', 'hide-section', 'message' ], true ) ? $action : 'hide',
            'text'      => $settings['countdown_expired_text'] ?? '',
            'labels'    => [
                'days'    => $settings['label_days'] ?? '',
                'hours'   => $settings['label_hours'] ?? '',
                'minutes' => $settings['label_minutes'] ?? '',
                'seconds' => $settings['label_seconds'] ?? '',
            ],
        ];
    }

    /**
     * Resolve the separator glyph. An untouched control means "whatever this variant's reference
     * used" — a colon for eleven of the twelve, nothing for modern v1.
     *
     * @param  array  $settings
     * @param  string $style
     * @param  string $variant
     * @return string
     */
    private function countdown_separator( array $settings, $style, $variant ) {
        $choice = (string) ( $settings['countdown_separator'] ?? '' );

        if ( 'none' === $choice ) {
            return '';
        }

        if ( '' === $choice ) {
            return in_array( $variant, self::NO_SEPARATOR_VARIANTS[ $style ] ?? [], true ) ? '' : ':';
        }

        return self::SEPARATORS[ $choice ] ?? '';
    }

    /**
     * Render the real template with demo content so the user can see a Pro variant in the
     * editor before upgrading. Frontend gets the upgrade notice instead.
     */
    private function render_pro_preview( $style, $variant ) {
        if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Campaign Banner' );
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            \WooLentor\Style_Pack_Manager::render_upgrade_notice( $style, $variant, 'Campaign Banner' );
            return;
        }

        $data = [
            'eyebrow'       => __( 'Limited Time', 'woolentor' ),
            'live_dot'      => true,
            'figure'        => '30',
            'figure_suffix' => __( '% Off', 'woolentor' ),
            'headline'      => __( "Select Modern Sofas\nBed & Chairs.", 'woolentor' ),
            'description'   => __( 'Carefully curated pieces at their best prices of the year.', 'woolentor' ),
            'coupon'        => __( 'Use code SALE30 at checkout', 'woolentor' ),
            'note'          => __( 'Preview content.', 'woolentor' ),
            'media_image'   => Utils::get_placeholder_image_src(),
            'media_alt'     => __( 'Preview', 'woolentor' ),
            'media_slides'  => [],
            'primary'       => [ 'text' => __( 'Shop the Sale', 'woolentor' ), 'url' => '', 'is_external' => false, 'nofollow' => false ],
            'secondary'     => [ 'text' => __( 'Watch the Film', 'woolentor' ), 'url' => '', 'is_external' => false, 'nofollow' => false ],
            'stats'         => [
                [ 'value' => '200+', 'label' => __( 'new designs', 'woolentor' ) ],
                [ 'value' => '100%', 'label' => __( 'organic', 'woolentor' ) ],
            ],
            'cards'         => [],
            'cards_label'   => __( 'On Sale Now', 'woolentor' ),
            'cards_slider'  => [ 'items' => 3, 'tablet_items' => 2, 'mobile_items' => 1 ],
            'countdown'     => [
                'show'      => true,
                'end'       => time() + ( 4 * DAY_IN_SECONDS ),
                'units'     => self::UNITS,
                'separator' => '',
                'action'    => 'hide',
                'text'      => '',
                'labels'    => [
                    'days'    => __( 'Days', 'woolentor' ),
                    'hours'   => __( 'Hours', 'woolentor' ),
                    'minutes' => __( 'Mins', 'woolentor' ),
                    'seconds' => __( 'Secs', 'woolentor' ),
                ],
            ],
        ];

        echo '<div data-wl-pack="' . esc_attr( $style ) . '" style="position:relative;">';
        echo '<div class="wl-cb wl-cb-' . esc_attr( $style ) . ' wl-cb-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
        echo '<div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.78);color:#fff;'
            . 'padding:4px 10px;border-radius:3px;font-size:11px;font-weight:700;pointer-events:none;z-index:99;">'
            . esc_html__( 'Pro — Preview Only', 'woolentor' )
            . '</div>';
        echo '</div>';
    }

    protected function render() {
        if ( ! class_exists( '\WooLentor\Style_Pack_Manager' ) ) {
            echo '<p>' . esc_html__( 'Style Pack Manager not found.', 'woolentor' ) . '</p>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $style    = \WooLentor\Style_Pack_Manager::sanitize_pack( $settings['style'] ?? 'modern', 'modern' );
        $variant  = \WooLentor\Style_Pack_Manager::sanitize_variant( $settings['variant'] ?? 'v1' );

        // Pro variant gate — this file is only loaded when Pro is NOT active.
        if ( \WooLentor\Style_Pack_Manager::is_pro_variant( $style, $variant, $this->get_pro_map() ) ) {
            $this->render_pro_preview( $style, $variant );
            return;
        }

        $data = $this->build_data( $settings, $style, $variant );

        // An expired campaign set to hide the whole section is skipped server-side, so it never
        // flashes on screen before the script runs — and it stays hidden with JS disabled. The
        // editor keeps showing it, otherwise the widget would vanish and become uneditable.
        if (
            $data['countdown']['show']
            && 'hide-section' === $data['countdown']['action']
            && $data['countdown']['end'] <= time()
            && ! \Elementor\Plugin::$instance->editor->is_edit_mode()
        ) {
            return;
        }

        $template = \WooLentor\Style_Pack_Manager::resolve_template( __DIR__ . '/templates', $style, $variant );

        if ( ! $template ) {
            echo '<p>' . esc_html__( 'Campaign Banner template not found.', 'woolentor' ) . '</p>';
            return;
        }

        echo '<div data-wl-pack="' . esc_attr( $style ) . '">';
        echo '<div class="wl-cb wl-cb-' . esc_attr( $style ) . ' wl-cb-' . esc_attr( $style ) . '-' . esc_attr( $variant ) . '">';
        include $template;
        echo '</div>';
        echo '</div>';
    }
}
