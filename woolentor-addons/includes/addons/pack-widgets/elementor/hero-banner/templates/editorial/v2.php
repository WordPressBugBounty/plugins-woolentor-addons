<?php
/**
 * Hero Banner — Editorial / Variant 2 — Full-bleed dual-image fashion hero.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow            Issue/collection label (e.g. "SS26 Collection").
 * @var string $heading            Serif title with <span class="wl-hl"> for italic words.
 * @var string $btn_primary_text   CTA button label.
 * @var string $btn_primary_url    CTA button URL.
 * @var string $hero_image_url     Left panel photograph (also used as video poster).
 * @var string $hero_image_alt
 * @var string $hero_video_url     Left panel video URL (mp4/webm). When set, replaces the image.
 * @var string $card2_image_url    Right panel photograph (also used as video poster).
 * @var string $card2_image_alt
 * @var string $card2_video_url    Right panel video URL (mp4/webm). When set, replaces the image.
 * @var int    $index              0-based slide index.
 * @var int    $slide_total        Total repeater item count.
 * @var bool   $slider_enabled     Whether the slider is active.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$slide_current_fmt = str_pad( $index + 1, 2, '0', STR_PAD_LEFT );
$slide_total_fmt   = str_pad( $slide_total, 2, '0', STR_PAD_LEFT );
$fill_pct          = $slide_total > 0 ? round( ( $index + 1 ) / $slide_total * 100, 2 ) : 100;
?>

<!-- Left panel: video (silent loop) or photograph -->
<div class="wl-hev2-img wl-hev2-img--left">
    <?php if ( $hero_video_url ) : ?>
    <video src="<?php echo esc_url( $hero_video_url ); ?>" autoplay muted loop playsinline></video>
    <?php endif; ?>
</div>

<!-- Right panel: video (silent loop) or photograph -->
<div class="wl-hev2-img wl-hev2-img--right">
    <?php if ( $hero_image_url ) : ?>
    <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="eager">
    <?php endif; ?>
</div>

<!-- Radial gradient overlay -->
<div class="wl-hev2-overlay" aria-hidden="true"></div>

<!-- Vertical center divider -->
<span class="wl-hev2-midline" aria-hidden="true"></span>

<!-- Center content -->
<div class="wl-hev2-content">
    <?php if ( $eyebrow ) : ?>
    <span class="wl-hev2-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
    <?php endif; ?>

    <?php if ( $heading ) : ?>
    <h2 class="wl-hev2-title"><?php echo wp_kses_post( $heading ); ?></h2>
    <?php endif; ?>

    <?php if ( $btn_primary_text ) : ?>
    <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hev2-btn">
        <?php echo esc_html( $btn_primary_text ); ?>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    <?php endif; ?>
</div>

<!-- Bottom controls: counter, progress bar, prev/next arrows -->
<?php if ( $slider_enabled && $slide_total > 1 ) : ?>
<div class="wl-hev2-controls">
    <span class="wl-hev2-count" aria-label="Slide <?php echo esc_attr( $index + 1 ); ?> of <?php echo esc_attr( $slide_total ); ?>">
        <b class="wl-hev2-count-cur"><?php echo esc_html( $slide_current_fmt ); ?></b>
        <span class="wl-hev2-count-sep"> / </span>
        <span class="wl-hev2-count-total"><?php echo esc_html( $slide_total_fmt ); ?></span>
    </span>
    <div class="wl-hev2-bar" aria-hidden="true">
        <span class="wl-hev2-bar-fill" style="width:<?php echo esc_attr( $fill_pct ); ?>%"></span>
    </div>
    <div class="wl-hev2-arrows">
        <button type="button" class="wl-hev2-btn-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'woolentor' ); ?>">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" class="wl-hev2-btn-next" aria-label="<?php esc_attr_e( 'Next slide', 'woolentor' ); ?>">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </div>
</div>
<?php endif; ?>
