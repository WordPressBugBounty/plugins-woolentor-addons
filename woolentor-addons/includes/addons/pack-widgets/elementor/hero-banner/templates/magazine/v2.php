<?php
/**
 * Hero Banner — Magazine / Variant 2 — Fashion editorial (L'ÉDITION style).
 * Left: full-bleed cover story with pill tag, large heading, spin badge, dual CTAs.
 * Right: product card + stat card + lookbook card.
 * Template is included inside .wl-hero-slide by render().
 *
 * Per-slide vars: $eyebrow, $heading, $description, $btn_primary_text, $btn_primary_url,
 *                 $btn_secondary_text, $btn_secondary_url, $hero_image_url, $hero_image_alt,
 *                 $pill_tag, $spin_badge_text
 * Widget-level:   $mag_v2_prod, $mag_v2_stat, $mag_v2_look  (arrays — extracted by render())
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-hm2-grid">

    <!-- ─── Cover story (left) ─────────────────────────────────────── -->
    <article class="wl-hm2-cover">

        <?php if ( $hero_image_url ) : ?>
        <div class="wl-hm2-cover-img" aria-hidden="true">
            <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="eager">
        </div>
        <?php endif; ?>
        <div class="wl-hm2-cover-overlay" aria-hidden="true"></div>

        <!-- Top: pill tag -->
        <div class="wl-hm2-cover-top">
            <?php if ( $pill_tag ) : ?>
            <span class="wl-hm2-pill-tag">
                <span class="wl-hm2-pill-dot" aria-hidden="true"></span>
                <?php echo esc_html( $pill_tag ); ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- Bottom: editorial content -->
        <div class="wl-hm2-cover-bottom">

            <?php if ( $eyebrow ) : ?>
            <span class="wl-hm2-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
            <?php endif; ?>

            <?php if ( $heading ) : ?>
            <h2 class="wl-hm2-title"><?php echo wp_kses_post( $heading ); ?></h2>
            <?php endif; ?>

            <?php if ( $description ) : ?>
            <p class="wl-hm2-sub"><?php echo wp_kses_post( $description ); ?></p>
            <?php endif; ?>

            <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
            <div class="wl-hm2-cta">
                <?php if ( $btn_primary_text ) : ?>
                <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hm2-btn-coral">
                    <?php echo esc_html( $btn_primary_text ); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>
                <?php if ( $btn_secondary_text ) : ?>
                <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hm2-btn-ghost"><?php echo esc_html( $btn_secondary_text ); ?></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div><!-- .wl-hm2-cover-bottom -->

        <!-- Spin badge -->
        <?php if ( $spin_badge_text ) : ?>
        <div class="wl-hm2-spin-badge" aria-hidden="true">
            <svg class="wl-hm2-spin-ring" viewBox="0 0 120 120">
                <defs>
                    <path id="wl-hm2-circle" d="M60,60 m-46,0 a46,46 0 1,1 92,0 a46,46 0 1,1 -92,0"/>
                </defs>
                <text><textPath href="#wl-hm2-circle" startOffset="0%"><?php echo esc_html( $spin_badge_text ); ?></textPath></text>
            </svg>
            <div class="wl-hm2-spin-core">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"/></svg>
            </div>
        </div>
        <?php endif; ?>

    </article><!-- .wl-hm2-cover -->

    <!-- ─── Side panel (right) ─────────────────────────────────────── -->
    <div class="wl-hm2-side">

        <!-- Product card -->
        <?php if ( ! empty( $mag_v2_prod ) ) : ?>
        <div class="wl-hm2-prod-card">
            <div class="wl-hm2-pimg">
                <?php if ( $mag_v2_prod['image'] ) : ?>
                <img src="<?php echo esc_url( $mag_v2_prod['image'] ); ?>" alt="<?php echo esc_attr( $mag_v2_prod['name'] ); ?>" loading="lazy">
                <?php endif; ?>
                <?php if ( $mag_v2_prod['chip1'] || $mag_v2_prod['chip2'] ) : ?>
                <div class="wl-hm2-ptags">
                    <?php if ( $mag_v2_prod['chip1'] ) : ?>
                    <span class="wl-hm2-chip-coral"><?php echo esc_html( $mag_v2_prod['chip1'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $mag_v2_prod['chip2'] ) : ?>
                    <span class="wl-hm2-chip-light"><?php echo esc_html( $mag_v2_prod['chip2'] ); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="wl-hm2-pbody">
                <div>
                    <?php if ( $mag_v2_prod['cat'] ) : ?>
                    <div class="wl-hm2-pcat"><?php echo esc_html( $mag_v2_prod['cat'] ); ?></div>
                    <?php endif; ?>
                    <?php if ( $mag_v2_prod['name'] ) : ?>
                    <div class="wl-hm2-pname"><?php echo esc_html( $mag_v2_prod['name'] ); ?></div>
                    <?php endif; ?>
                    <?php if ( $mag_v2_prod['price'] ) : ?>
                    <div class="wl-hm2-pprice">
                        <?php echo wp_kses_post( $mag_v2_prod['price'] ); ?>
                        <?php if ( $mag_v2_prod['orig_price'] ) : ?>
                        <span class="was"><?php echo wp_kses_post( $mag_v2_prod['orig_price'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ( $mag_v2_prod['btn_url'] ) : ?>
                <a href="<?php echo esc_url( $mag_v2_prod['btn_url'] ); ?>" class="wl-hm2-padd" aria-label="<?php esc_attr_e( 'View product', 'woolentor' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stat + lookbook row -->
        <div class="wl-hm2-side-split">

            <!-- Stat mini card -->
            <?php if ( ! empty( $mag_v2_stat ) ) : ?>
            <div class="wl-hm2-mini-stat">
                <div class="wl-hm2-ms-top">
                    <?php if ( $mag_v2_stat['eyebrow'] ) : ?>
                    <span class="wl-hm2-ms-eyebrow"><?php echo esc_html( $mag_v2_stat['eyebrow'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $mag_v2_stat['badge'] ) : ?>
                    <span class="wl-hm2-ms-badge"><?php echo esc_html( $mag_v2_stat['badge'] ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="wl-hm2-ms-main">
                    <?php if ( $mag_v2_stat['num'] ) : ?>
                    <span class="wl-hm2-num"><?php echo esc_html( $mag_v2_stat['num'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $mag_v2_stat['unit'] ) : ?>
                    <span class="wl-hm2-ms-unit"><?php echo esc_html( $mag_v2_stat['unit'] ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="wl-hm2-ms-foot">
                    <?php if ( $mag_v2_stat['1_val'] || $mag_v2_stat['1_label'] ) : ?>
                    <div class="wl-hm2-ms-cell">
                        <b><?php echo esc_html( $mag_v2_stat['1_val'] ); ?></b>
                        <span><?php echo esc_html( $mag_v2_stat['1_label'] ); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ( ( $mag_v2_stat['1_val'] || $mag_v2_stat['1_label'] ) && ( $mag_v2_stat['2_val'] || $mag_v2_stat['2_label'] ) ) : ?>
                    <div class="wl-hm2-ms-div" aria-hidden="true"></div>
                    <?php endif; ?>
                    <?php if ( $mag_v2_stat['2_val'] || $mag_v2_stat['2_label'] ) : ?>
                    <div class="wl-hm2-ms-cell">
                        <b><?php echo esc_html( $mag_v2_stat['2_val'] ); ?></b>
                        <span><?php echo esc_html( $mag_v2_stat['2_label'] ); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lookbook mini card -->
            <?php if ( ! empty( $mag_v2_look ) ) : ?>
            <a href="<?php echo esc_url( $mag_v2_look['cta_url'] ); ?>" class="wl-hm2-mini-look">
                <?php if ( $mag_v2_look['image'] ) : ?>
                <img src="<?php echo esc_url( $mag_v2_look['image'] ); ?>" alt="<?php echo esc_attr( $mag_v2_look['title'] ); ?>" loading="lazy">
                <?php endif; ?>
                <div class="wl-hm2-ml-top">
                    <?php if ( $mag_v2_look['cat'] ) : ?>
                    <span class="wl-hm2-ml-cat"><?php echo esc_html( $mag_v2_look['cat'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $mag_v2_look['idx'] ) : ?>
                    <span class="wl-hm2-ml-idx"><?php echo esc_html( $mag_v2_look['idx'] ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="wl-hm2-ml-bot">
                    <?php if ( $mag_v2_look['title'] ) : ?>
                    <h4 class="wl-hm2-ml-title"><?php echo wp_kses_post( $mag_v2_look['title'] ); ?></h4>
                    <?php endif; ?>
                    <?php if ( $mag_v2_look['meta'] ) : ?>
                    <div class="wl-hm2-ml-meta"><?php echo wp_kses_post( $mag_v2_look['meta'] ); ?></div>
                    <?php endif; ?>
                    <?php if ( $mag_v2_look['cta_text'] ) : ?>
                    <div class="wl-hm2-ml-cta">
                        <span><?php echo wp_kses_post( $mag_v2_look['cta_text'] ); ?></span>
                        <span class="wl-hm2-ml-arrow" aria-hidden="true">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"/></svg>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endif; ?>

        </div><!-- .wl-hm2-side-split -->

    </div><!-- .wl-hm2-side -->

</div><!-- .wl-hm2-grid -->
