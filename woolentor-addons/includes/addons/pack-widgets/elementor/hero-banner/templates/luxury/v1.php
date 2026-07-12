<?php
/**
 * Hero Banner — Luxury / Variant 1 — Full-bleed dark slider with gold accents.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow            Gold uppercase label (e.g. "Spring Drop 2025").
 * @var string $heading            Bold white title; render() injects <span class="wl-hl"> for gold words.
 * @var string $description        Muted white subtitle.
 * @var string $btn_primary_text   Gold pill CTA label.
 * @var string $btn_primary_url
 * @var string $btn_secondary_text Ghost text CTA label.
 * @var string $btn_secondary_url
 * @var string $hero_image_url     Full-bleed background photograph (positioned center-right).
 * @var string $hero_image_alt     Alt text (decorative — empty string is fine for background photos).
 * @var int    $index              0-based slide index.
 * @var int    $slide_total        Total slide count.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$slide_current_fmt = str_pad( $index + 1, 2, '0', STR_PAD_LEFT );
$slide_total_fmt   = str_pad( $slide_total, 2, '0', STR_PAD_LEFT );
?>

<!-- Full-bleed background image -->
<div class="wl-hlv1-bg" aria-hidden="true">
    <?php if ( $hero_image_url ) : ?>
    <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>">
    <?php endif; ?>
    <div class="wl-hlv1-overlay"></div>
</div>

<!-- Left-side text content -->
<div class="wl-hlv1-content">

    <?php if ( $eyebrow ) : ?>
    <div class="wl-hlv1-eyebrow">
        <span class="wl-hlv1-eyebrow-dash" aria-hidden="true"></span>
        <span class="wl-hlv1-eyebrow-text"><?php echo esc_html( $eyebrow ); ?></span>
    </div>
    <?php endif; ?>

    <?php if ( $heading ) : ?>
    <h2 class="wl-hlv1-title"><?php echo wp_kses_post( $heading ); ?></h2>
    <?php endif; ?>

    <?php if ( $description ) : ?>
    <p class="wl-hlv1-sub"><?php echo wp_kses_post( $description ); ?></p>
    <?php endif; ?>

    <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
    <div class="wl-hlv1-cta">
        <?php if ( $btn_primary_text ) : ?>
        <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hlv1-btn wl-hlv1-btn-primary"><?php echo esc_html( $btn_primary_text ); ?></a>
        <?php endif; ?>
        <?php if ( $btn_secondary_text ) : ?>
        <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hlv1-btn wl-hlv1-btn-ghost"><?php echo esc_html( $btn_secondary_text ); ?></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Bottom-right slide counter (decorative, aria-hidden) -->
<?php if ( $slide_total > 1 ) : ?>
<div class="wl-hlv1-counter" aria-hidden="true">
    <span class="wl-hlv1-counter-cur"><?php echo esc_html( $slide_current_fmt ); ?></span>
    <span class="wl-hlv1-counter-sep"> / </span>
    <span class="wl-hlv1-counter-total"><?php echo esc_html( $slide_total_fmt ); ?></span>
</div>
<?php endif; ?>
