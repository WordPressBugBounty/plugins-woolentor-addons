<?php
/**
 * Hero Banner — Modern / Variant 3 — Split-screen: lifestyle left / product spotlight right.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow
 * @var string $heading           Processed heading (with .wl-hl spans for highlight).
 * @var string $description
 * @var string $btn_primary_text
 * @var string $btn_primary_url
 * @var string $btn_secondary_text
 * @var string $btn_secondary_url
 * @var string $hero_image_url    Full-bleed lifestyle photo for left panel.
 * @var string $hero_image_alt
 * @var string $card2_eyebrow     Product name (right panel).
 * @var string $card2_subtitle    Product price (right panel).
 * @var string $card2_image_url   Product photo.
 * @var string $card2_image_alt
 * @var int    $index             0-based slide index (from render() foreach).
 * @var int    $slide_total       Total repeater item count.
 * @var bool   $slider_enabled    Whether the slider is active.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$slide_current_fmt = str_pad( $index + 1, 2, '0', STR_PAD_LEFT );
$slide_total_fmt   = str_pad( $slide_total, 2, '0', STR_PAD_LEFT );
$fill_pct          = $slide_total > 0 ? round( ( $index + 1 ) / $slide_total * 100, 2 ) : 100;
?>

<!-- Left: lifestyle image + content -->
<div class="wl-hv3-left">
    <?php if ( $hero_image_url ) : ?>
    <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" class="wl-hv3-bg" loading="eager">
    <?php endif; ?>
    <div class="wl-hv3-overlay" aria-hidden="true"></div>
    <div class="wl-hv3-text">
        <?php if ( $eyebrow ) : ?>
        <div class="wl-hv3-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
        <?php endif; ?>
        <?php if ( $heading ) : ?>
        <h2 class="wl-hv3-heading"><?php echo wp_kses_post($heading); ?></h2>
        <?php endif; ?>
        <?php if ( $description ) : ?>
        <p class="wl-hv3-subtext"><?php echo wp_kses_post($description); ?></p>
        <?php endif; ?>
        <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
        <div class="wl-hv3-cta">
            <?php if ( $btn_primary_text ) : ?>
            <a href="<?php echo esc_url($btn_primary_url); ?>" class="wl-hv3-btn wl-hv3-btn--primary"><?php echo esc_html( $btn_primary_text ); ?></a>
            <?php endif; ?>
            <?php if ( $btn_secondary_text ) : ?>
            <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hv3-btn wl-hv3-btn--outline"><?php echo esc_html( $btn_secondary_text ); ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Right: product spotlight + slide controls -->
<div class="wl-hv3-right">
    <div class="wl-hv3-product">
        <?php if ( $card2_image_url ) : ?>
        <div class="wl-hv3-product-img-wrap">
            <img src="<?php echo esc_url( $card2_image_url ); ?>" alt="<?php echo esc_attr( $card2_image_alt ); ?>" class="wl-hv3-product-img" loading="lazy">
        </div>
        <?php endif; ?>
        <?php if ( $card2_eyebrow ) : ?>
        <p class="wl-hv3-product-name"><?php echo esc_html( $card2_eyebrow ); ?></p>
        <?php endif; ?>
        <?php if ( $card2_subtitle ) : ?>
        <p class="wl-hv3-product-price"><?php echo esc_html( $card2_subtitle ); ?></p>
        <?php endif; ?>
    </div>

    <?php if ( $slider_enabled && $slide_total > 1 ) : ?>
    <div class="wl-hv3-controls">
        <div class="wl-hv3-counter" aria-label="Slide <?php echo esc_attr( ( $index + 1 ) ); ?> of <?php echo esc_attr( $slide_total ); ?>">
            <span class="wl-hv3-counter-num wl-hv3-counter-num--active"><?php echo esc_html( $slide_current_fmt ); ?></span>
            <div class="wl-hv3-counter-track">
                <div class="wl-hv3-counter-fill" style="width:<?php echo esc_attr( $fill_pct ); ?>%"></div>
            </div>
            <span class="wl-hv3-counter-num"><?php echo esc_html( $slide_total_fmt ); ?></span>
        </div>
    </div>
    <?php endif; ?>
</div>
