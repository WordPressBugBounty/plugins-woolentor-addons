<?php
/**
 * Hero Banner — Luxury / Variant 2 — Dual-panel split fashion hero.
 * Two slides render side-by-side as panels (no slider required).
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow            Gold dash + uppercase label (e.g. "Women's Collection").
 * @var string $heading            Large italic serif title.
 * @var string $description        Small muted body text.
 * @var string $btn_primary_text   Gold square CTA label.
 * @var string $btn_primary_url
 * @var string $hero_image_url     Full-bleed panel background photograph.
 * @var string $hero_image_alt
 * @var int    $index              0-based panel index (0 = left, 1 = right).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- Panel background image + bottom-up gradient overlay -->
<div class="wl-hlv2-media" aria-hidden="true">
    <?php if ( $hero_image_url ) : ?>
    <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>">
    <?php endif; ?>
    <div class="wl-hlv2-overlay"></div>
</div>

<!-- Bottom-anchored content: eyebrow → title → desc → CTA -->
<div class="wl-hlv2-content">

    <?php if ( $eyebrow ) : ?>
    <span class="wl-hlv2-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
    <?php endif; ?>

    <?php if ( $heading ) : ?>
    <h2 class="wl-hlv2-title"><?php echo wp_kses_post( $heading ); ?></h2>
    <?php endif; ?>

    <?php if ( $description ) : ?>
    <p class="wl-hlv2-desc"><?php echo wp_kses_post( $description ); ?></p>
    <?php endif; ?>

    <?php if ( $btn_primary_text ) : ?>
    <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hlv2-btn-primary">
        <?php echo esc_html( $btn_primary_text ); ?>
        <svg width="13" height="9" viewBox="0 0 14 10" fill="none" aria-hidden="true">
            <path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
        </svg>
    </a>
    <?php endif; ?>

</div>
