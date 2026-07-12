<?php
/**
 * Hero Banner — Magazine / Variant 1 — Tech/Gaming dark hero + sidebar deal cards.
 * Full-bleed hero main panel (left) + 2 stacked deal cards (right).
 * Template is included inside .wl-hero-slide by render().
 *
 * Per-slide vars: $eyebrow, $heading, $description, $btn_primary_text, $btn_primary_url,
 *                 $btn_secondary_text, $btn_secondary_url, $hero_image_url, $hero_image_alt,
 *                 $discount_badge, $rating_text, $eyebrow_date
 * Widget-level:   $mag_v1_c1, $mag_v1_c2  (arrays — extracted by render())
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-hm1-grid">

    <!-- ─── Main hero panel ────────────────────────────────────────── -->
    <article class="wl-hm1-main">

        <?php if ( $hero_image_url ) : ?>
        <div class="wl-hm1-bg" aria-hidden="true">
            <img src="<?php echo esc_url($hero_image_url); ?>" alt="<?php echo esc_attr($hero_image_alt); ?>" loading="eager">
        </div>
        <?php endif; ?>

        <div class="wl-hm1-overlay" aria-hidden="true"></div>

        <?php if ( $discount_badge ) : ?>
        <div class="wl-hm1-stats" aria-hidden="true">
            <span class="wl-hm1-discount"><?php echo esc_html( $discount_badge ); ?></span>
        </div>
        <?php endif; ?>

        <div class="wl-hm1-body">

            <?php if ( $eyebrow || $eyebrow_date ) : ?>
            <div class="wl-hm1-eyebrow" aria-label="<?php esc_attr_e( 'Live update', 'woolentor' ); ?>">
                <span class="wl-hm1-ping" aria-hidden="true"></span>
                <?php if ( $eyebrow ) : ?>
                <span class="wl-hm1-eyebrow-label"><?php echo esc_html( $eyebrow ); ?></span>
                <?php endif; ?>
                <?php if ( $eyebrow && $eyebrow_date ) : ?>
                <span class="wl-hm1-eyebrow-div" aria-hidden="true"></span>
                <?php endif; ?>
                <?php if ( $eyebrow_date ) : ?>
                <span class="wl-hm1-eyebrow-date"><?php echo esc_html( $eyebrow_date ); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( $heading ) : ?>
            <h2 class="wl-hm1-title"><?php echo wp_kses_post( $heading ); ?></h2>
            <?php endif; ?>

            <?php if ( $description ) : ?>
            <p class="wl-hm1-deck"><?php echo wp_kses_post( $description ); ?></p>
            <?php endif; ?>

            <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
            <div class="wl-hm1-cta">
                <?php if ( $btn_primary_text ) : ?>
                <a href="<?php echo esc_url($btn_primary_url); ?>" class="wl-hm1-btn-deal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <?php echo esc_html( $btn_primary_text ); ?>
                </a>
                <?php endif; ?>
                <?php if ( $btn_secondary_text ) : ?>
                <a href="<?php echo esc_url($btn_secondary_url); ?>" class="wl-hm1-btn-ghost">
                    <?php echo esc_html( $btn_secondary_text ); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ( $rating_text ) : ?>
            <div class="wl-hm1-rating">
                <span class="wl-hm1-stars" aria-hidden="true">★★★★★</span>
                <span><?php echo esc_html( $rating_text ); ?></span>
            </div>
            <?php endif; ?>

        </div><!-- .wl-hm1-body -->
    </article><!-- .wl-hm1-main -->

    <!-- ─── Sidebar cards ──────────────────────────────────────────── -->
    <aside class="wl-hm1-side">

        <?php
        $wl_hm1_cards = [ $mag_v1_c1, $mag_v1_c2 ];
        foreach ( $wl_hm1_cards as $wl_hm1_card ) :
            if ( empty( $wl_hm1_card ) ) continue;
            $wl_hm1_is_light = ( 'light' === $wl_hm1_card['badge_style'] );
            $wl_hm1_card_class = 'wl-hm1-card' . ( $wl_hm1_is_light ? ' wl-hm1-card--light' : '' );
        ?>
        <a href="<?php echo esc_url( $wl_hm1_card['cta_url'] ); ?>" class="<?php echo esc_attr( $wl_hm1_card_class ); ?>">

            <?php if ( $wl_hm1_card['image'] ) : ?>
            <div class="wl-hm1-card-img" aria-hidden="true">
                <img src="<?php echo esc_url( $wl_hm1_card['image'] ); ?>" alt="" loading="lazy">
            </div>
            <?php endif; ?>

            <div class="wl-hm1-card-grad" aria-hidden="true"></div>

            <?php if ( $wl_hm1_card['badge'] ) : ?>
            <div class="wl-hm1-card-badge">
                <?php
                $wl_chip_class = 'wl-hm1-chip wl-hm1-chip--' . esc_attr( $wl_hm1_card['badge_style'] );
                ?>
                <span class="<?php echo $wl_chip_class; ?>"><?php echo esc_html( $wl_hm1_card['badge'] ); ?></span>
            </div>
            <?php endif; ?>

            <div class="wl-hm1-card-body">
                <?php if ( $wl_hm1_card['heading'] ) : ?>
                <h3 class="wl-hm1-card-heading"><?php echo wp_kses_post($wl_hm1_card['heading']); ?></h3>
                <?php endif; ?>
                <?php if ( $wl_hm1_card['desc'] ) : ?>
                <p class="wl-hm1-card-desc"><?php echo wp_kses_post($wl_hm1_card['desc']); ?></p>
                <?php endif; ?>
                <?php if ( $wl_hm1_card['cta_text'] ) : ?>
                <span class="wl-hm1-card-cta">
                    <?php echo esc_html($wl_hm1_card['cta_text']); ?>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </span>
                <?php endif; ?>
            </div><!-- .wl-hm1-card-body -->

        </a><!-- .wl-hm1-card -->
        <?php endforeach; ?>

    </aside><!-- .wl-hm1-side -->

</div><!-- .wl-hm1-grid -->
