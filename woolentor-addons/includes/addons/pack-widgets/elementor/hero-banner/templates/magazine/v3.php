<?php
/**
 * Hero Banner — Magazine / Variant 3 — Folio home store (3-column grid).
 * Left: feature panel with content. Middle: tall room promo. Right: 2 stacked spots.
 * Template is included inside .wl-hero-slide by render().
 *
 * Per-slide vars: $eyebrow, $heading, $description, $btn_primary_text, $btn_primary_url,
 *                 $hero_image_url, $hero_image_alt
 * Widget-level:   $mag_v3_promo, $mag_v3_spot1, $mag_v3_spot2  (arrays — extracted by render())
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-hm3-grid">

    <!-- ─── Left: Feature panel ────────────────────────────────────── -->
    <div class="wl-hm3-feature">

        <?php if ( $hero_image_url ) : ?>
        <img class="wl-hm3-feature-img" src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>" loading="eager">
        <?php endif; ?>

        <div class="wl-hm3-shade" aria-hidden="true"></div>

        <div class="wl-hm3-body">
            <?php if ( $eyebrow ) : ?>
            <span class="wl-hm3-eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <?php endif; ?>

            <?php if ( $heading ) : ?>
            <h2 class="wl-hm3-title"><?php echo wp_kses_post($heading); ?></h2>
            <?php endif; ?>

            <?php if ( $description ) : ?>
            <p class="wl-hm3-desc"><?php echo wp_kses_post($description); ?></p>
            <?php endif; ?>

            <?php if ( $btn_primary_text ) : ?>
            <a href="<?php echo esc_url($btn_primary_url); ?>" class="wl-hm3-btn">
                <?php echo esc_html($btn_primary_text); ?>
            </a>
            <?php endif; ?>
        </div>

    </div><!-- .wl-hm3-feature -->

    <!-- ─── Middle: Tall room promo ────────────────────────────────── -->
    <?php if ( ! empty( $mag_v3_promo ) ) : ?>
    <a href="<?php echo esc_url($mag_v3_promo['btn_url']); ?>" class="wl-hm3-promo">

        <?php if ( $mag_v3_promo['image'] ) : ?>
        <div class="wl-hm3-promo-img" aria-hidden="true">
            <img src="<?php echo esc_url($mag_v3_promo['image']); ?>" alt="" loading="lazy">
        </div>
        <?php endif; ?>

        <div class="wl-hm3-promo-top">
            <?php if ( $mag_v3_promo['label'] ) : ?>
            <span class="wl-hm3-promo-label"><?php echo esc_html($mag_v3_promo['label']); ?></span>
            <?php endif; ?>
            <?php if ( $mag_v3_promo['title'] ) : ?>
            <h3 class="wl-hm3-promo-title"><?php echo wp_kses_post($mag_v3_promo['title']); ?></h3>
            <?php endif; ?>
        </div>

        <?php if ( $mag_v3_promo['btn_text'] ) : ?>
        <div class="wl-hm3-promo-btn-wrap">
            <span class="wl-hm3-promo-btn"><?php echo esc_html($mag_v3_promo['btn_text']); ?></span>
        </div>
        <?php endif; ?>

    </a><!-- .wl-hm3-promo -->
    <?php endif; ?>

    <!-- ─── Right: Two stacked product spotlights ──────────────────── -->
    <div class="wl-hm3-stack">

        <?php foreach ( [ $mag_v3_spot1, $mag_v3_spot2 ] as $wl_spot ) :
            if ( empty( $wl_spot ) ) continue;
        ?>
        <a href="<?php echo esc_url($wl_spot['url']); ?>" class="wl-hm3-spot">

            <?php if ( $wl_spot['image'] ) : ?>
            <div class="wl-hm3-spot-img" aria-hidden="true">
                <img src="<?php echo esc_url($wl_spot['image']); ?>" alt="" loading="lazy">
            </div>
            <?php endif; ?>

            <div class="wl-hm3-spot-shade" aria-hidden="true"></div>

            <div class="wl-hm3-spot-text">
                <div class="wl-hm3-spot-head">
                    <?php if ( $wl_spot['label'] ) : ?>
                    <span class="wl-hm3-spot-label"><?php echo esc_html($wl_spot['label']); ?></span>
                    <?php endif; ?>
                    <?php if ( $wl_spot['title'] ) : ?>
                    <h4 class="wl-hm3-spot-title"><?php echo wp_kses_post($wl_spot['title']); ?></h4>
                    <?php endif; ?>
                </div>
                <?php if ( $wl_spot['price'] ) : ?>
                <span class="wl-hm3-spot-price"><?php echo esc_html($wl_spot['price']); ?></span>
                <?php endif; ?>
            </div>

        </a><!-- .wl-hm3-spot -->
        <?php endforeach; ?>

    </div><!-- .wl-hm3-stack -->

</div><!-- .wl-hm3-grid -->
