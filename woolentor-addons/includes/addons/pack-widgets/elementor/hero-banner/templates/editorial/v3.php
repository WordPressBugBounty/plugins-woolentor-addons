<?php
/**
 * Hero Banner — Editorial / Variant 3 — Asymmetric split: text left / photo right.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow            Badge label (e.g. "Spring 2025 Edit").
 * @var string $heading            Serif title; render() injects <span class="wl-hl"> for clay italic words.
 * @var string $description        Body paragraph.
 * @var string $btn_primary_text   Dark filled CTA label.
 * @var string $btn_primary_url
 * @var string $btn_secondary_text Outlined secondary CTA label.
 * @var string $btn_secondary_url
 * @var string $hero_image_url     Right panel photograph.
 * @var string $hero_image_alt
 * @var string $card2_eyebrow      Vertical label on right edge of photo (e.g. "The Edit · Spring 2025 · No. 01").
 * @var string $card2_subtitle     Caption text at bottom of photo.
 * @var string $card2_description  Issue ref at bottom of content panel (e.g. "No. 01 / 2025").
 * @var int    $index              0-based slide index (auto-generates ghost number).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$ghost_num = str_pad( $index + 1, 2, '0', STR_PAD_LEFT );
?>

<!-- Left: text content -->
<div class="wl-hev3-content">

    <!-- Ghost watermark number (decorative) -->
    <span class="wl-hev3-ghost-num" aria-hidden="true"><?php echo esc_html( $ghost_num ); ?></span>

    <!-- Badge + heading -->
    <div class="wl-hev3-top">
        <?php if ( $eyebrow ) : ?>
        <div class="wl-hev3-badge">
            <span class="wl-hev3-badge-dot" aria-hidden="true"></span>
            <?php echo esc_html( $eyebrow ); ?>
        </div>
        <?php endif; ?>

        <?php if ( $heading ) : ?>
        <h2 class="wl-hev3-title"><?php echo wp_kses_post( $heading ); ?></h2>
        <?php endif; ?>
    </div>

    <!-- Clay divider rule -->
    <div class="wl-hev3-rule" aria-hidden="true"></div>

    <!-- Body + CTA buttons -->
    <div class="wl-hev3-mid">
        <?php if ( $description ) : ?>
        <p class="wl-hev3-body"><?php echo wp_kses_post( $description ); ?></p>
        <?php endif; ?>

        <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
        <div class="wl-hev3-actions">
            <?php if ( $btn_primary_text ) : ?>
            <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hev3-btn wl-hev3-btn-primary">
                <?php echo esc_html( $btn_primary_text ); ?>
            </a>
            <?php endif; ?>
            <?php if ( $btn_secondary_text ) : ?>
            <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hev3-btn wl-hev3-btn-secondary">
                <?php echo esc_html($btn_secondary_text); ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer: scroll hint + issue ref -->
    <div class="wl-hev3-foot">
        <div class="wl-hev3-scroll-hint" aria-hidden="true">
            <span class="wl-hev3-scroll-line"></span>
            <span><?php esc_html_e( 'Scroll to explore', 'woolentor' ); ?></span>
        </div>
        <?php if ( $card2_description ) : ?>
        <span class="wl-hev3-edit-ref" aria-hidden="true"><?php echo wp_kses_post( $card2_description ); ?></span>
        <?php endif; ?>
    </div>

</div>

<!-- Right: editorial photograph -->
<div class="wl-hev3-media" aria-hidden="true">

    <?php if ( $card2_eyebrow ) : ?>
    <span class="wl-hev3-media-label"><?php echo esc_html( $card2_eyebrow ); ?></span>
    <?php endif; ?>

    <?php if ( $hero_image_url ) : ?>
    <img class="wl-hev3-img" src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="eager">
    <?php endif; ?>

    <?php if ( $card2_subtitle ) : ?>
    <div class="wl-hev3-caption">
        <span class="wl-hev3-caption-text"><?php echo wp_kses_post( $card2_subtitle ); ?></span>
    </div>
    <?php endif; ?>

</div>
