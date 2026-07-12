<?php
/**
 * Hero Banner — Modern / Variant 1 — single slide inner markup.
 * Outer .wl-hero-banner wrapper and .wl-hero-slide div are emitted by render().
 *
 * @var string $eyebrow
 * @var string $heading           Heading with <span class="wl-hl"> injected by render().
 * @var string $description
 * @var string $btn_primary_text
 * @var string $btn_primary_url
 * @var string $btn_secondary_text
 * @var string $btn_secondary_url
 * @var string $hero_image_url    Full-bleed background photo for left card.
 * @var string $hero_image_alt
 * @var string $card2_eyebrow     Product name (orange gradient).
 * @var string $card2_subtitle    Subtitle / spec line.
 * @var string $card2_image_url   Product image.
 * @var string $card2_image_alt
 * @var string $card2_watermark   Large faint text behind product.
 * @var string $card2_description Body text.
 * @var string $card2_btn_text
 * @var string $card2_btn_url
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-hero-stage">
    <div class="wl-hero-cards">

        <!-- Left: large photo card -->
        <article class="wl-hc wl-hc-lg">
            <?php if ( $hero_image_url ) : ?>
                <img class="wl-hc-bg-img" src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="eager">
            <?php endif; ?>
            <div class="wl-hc-content">
                <?php if ( $eyebrow ) : ?>
                    <div class="wl-hc-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
                <?php endif; ?>
                <?php if ( $heading ) : ?>
                    <h2 class="wl-hc-heading"><?php echo wp_kses_post( $heading ); ?></h2>
                <?php endif; ?>
                <?php if ( $description ) : ?>
                    <p class="wl-hc-sub"><?php echo wp_kses_post( $description ); ?></p>
                <?php endif; ?>
                <div class="wl-hc-ctas">
                    <?php if ( $btn_primary_text ) : ?>
                        <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hc-btn wl-hc-btn-light">
                            <?php echo esc_html( $btn_primary_text ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( $btn_secondary_text ) : ?>
                        <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hc-btn wl-hc-btn-dark">
                            <?php echo esc_html( $btn_secondary_text ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </article>

        <!-- Right: showcase card -->
        <article class="wl-hc wl-hc-sm">
            <div class="wl-hc-sm-top">
                <?php if ( $card2_eyebrow ) : ?>
                    <div class="wl-hc-sm-eyebrow"><?php echo esc_html( $card2_eyebrow ); ?></div>
                <?php endif; ?>
                <?php if ( $card2_subtitle ) : ?>
                    <div class="wl-hc-sm-subtitle"><?php echo esc_html( $card2_subtitle ); ?></div>
                <?php endif; ?>
            </div>

            <div class="wl-hc-product-wrap">
                <?php if ( $card2_image_url ) : ?>
                    <img src="<?php echo esc_url($card2_image_url); ?>" alt="<?php echo esc_attr($card2_image_alt); ?>" loading="lazy">
                <?php endif; ?>
            </div>

            <?php if ( $card2_watermark ) : ?>
                <div class="wl-hc-watermark"><?php echo esc_html($card2_watermark); ?></div>
            <?php endif; ?>

            <?php if ( $card2_description ) : ?>
                <p class="wl-hc-sm-para"><?php echo wp_kses_post($card2_description); ?></p>
            <?php endif; ?>

            <?php if ( $card2_btn_text ) : ?>
                <div class="wl-hc-ctas">
                    <a href="<?php echo esc_url($card2_btn_url); ?>" class="wl-hc-btn wl-hc-btn-dark">
                        <?php echo esc_html($card2_btn_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </article>

    </div>
</div>
