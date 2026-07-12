<?php
/**
 * Hero Banner — Editorial / Variant 1 — Magazine split layout.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow            Kicker label (e.g. "Issue 03 · The Sound Edit").
 * @var string $heading            Title with <span class="wl-hl"> injected by render() for italic+clay words.
 * @var string $description        Intro paragraph.
 * @var string $btn_primary_text   Primary CTA label.
 * @var string $btn_primary_url    Primary CTA URL.
 * @var string $btn_secondary_text Ghost link label.
 * @var string $btn_secondary_url  Ghost link URL.
 * @var string $hero_image_url     Editorial photograph (left panel).
 * @var string $hero_image_alt
 * @var string $card2_eyebrow      Vertical issue text (e.g. "Spring · Vol III · MMXXVI").
 * @var string $card2_subtitle     Photo caption (e.g. "Photographed by A. Vasquez · Issue 03").
 * @var string $card2_description  Meta row text (e.g. "Published 12 May 2026 · 9 min read · By Anna Vasquez").
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$arrow_svg = '<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 2.97a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06l2.97-2.97H3.75a.75.75 0 0 1 0-1.5h7.44L8.22 4.03a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>';
?>

<?php if ( $card2_eyebrow ) : ?>
<span class="wl-hev1-vtext" aria-hidden="true"><?php echo esc_html( $card2_eyebrow ); ?></span>
<?php endif; ?>

<div class="wl-hev1-inner">

    <!-- Left: editorial photograph -->
    <div class="wl-hev1-image-wrap">
        <div class="wl-hev1-image">
            <?php if ( $hero_image_url ) : ?>
            <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="eager">
            <?php endif; ?>
            <?php if ( $card2_subtitle ) : ?>
            <div class="wl-hev1-caption"><?php echo wp_kses_post( $card2_subtitle ); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: editorial content -->
    <div class="wl-hev1-content">

        <?php if ( $eyebrow ) : ?>
        <span class="wl-hev1-kicker"><?php echo esc_html( $eyebrow ); ?></span>
        <?php endif; ?>

        <?php if ( $heading ) : ?>
        <h2 class="wl-hev1-title"><?php echo wp_kses_post( $heading ); ?></h2>
        <?php endif; ?>

        <?php if ( $description ) : ?>
        <p class="wl-hev1-intro"><?php echo wp_kses_post( $description ); ?></p>
        <?php endif; ?>

        <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
        <div class="wl-hev1-cta">
            <?php if ( $btn_primary_text ) : ?>
            <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hev1-btn-primary">
                <?php echo esc_html( $btn_primary_text ); ?><?php echo $arrow_svg; ?>
            </a>
            <?php endif; ?>
            <?php if ( $btn_secondary_text ) : ?>
            <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hev1-btn-link">
                <?php echo esc_html( $btn_secondary_text ); ?><?php echo $arrow_svg; ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ( $card2_description ) : ?>
        <p class="wl-hev1-meta"><?php echo wp_kses_post( $card2_description ); ?></p>
        <?php endif; ?>

    </div>

</div>
