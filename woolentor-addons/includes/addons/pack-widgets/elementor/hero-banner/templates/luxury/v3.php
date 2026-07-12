<?php
/**
 * Hero Banner — Luxury / Variant 3 — Full-bleed dark slider.
 * Bold uppercase heading bottom-left, white pill CTA bottom-right.
 * Uses standard Slick slider with glass-circle arrows + dot indicators.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow            Optional vertical side label (e.g. "New 2025").
 * @var string $heading            Large bold uppercase heading.
 * @var string $btn_primary_text   White pill CTA label.
 * @var string $btn_primary_url
 * @var string $hero_image_url     Full-bleed background photograph.
 * @var string $hero_image_alt
 * @var int    $index              0-based slide index.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- Full-bleed background image + dual gradient overlay -->
<div class="wl-hlv3-bg" aria-hidden="true">
    <?php if ( $hero_image_url ) : ?>
    <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>">
    <?php endif; ?>
    <div class="wl-hlv3-overlay"></div>
</div>

<!-- Content row: heading bottom-left, pill CTA bottom-right -->
<div class="wl-hlv3-content">

    <?php if ( $heading ) : ?>
    <h2 class="wl-hlv3-title"><?php echo wp_kses_post( $heading ); ?></h2>
    <?php endif; ?>

    <?php if ( $btn_primary_text ) : ?>
    <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hlv3-btn-primary">
        <?php echo esc_html( $btn_primary_text ); ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
    </a>
    <?php endif; ?>

</div>
