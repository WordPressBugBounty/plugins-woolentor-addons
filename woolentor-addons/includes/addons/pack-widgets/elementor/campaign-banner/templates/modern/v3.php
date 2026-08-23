<?php
/**
 * Campaign Banner — Modern / Variant 3.
 *
 * A tall split: a warm-black panel on the left carrying a clay tag, an oversized figure with a
 * clay suffix, a headline, copy, a boxed countdown, a clay pill button and a line of fine print —
 * and a photograph on the right with product cards floating over its foot.
 *
 * Reference: design-reference/new_temlate/modern-style/v3/01-homepage.html:8411 — `.promo`
 *
 * Two deliberate departures from the reference:
 *
 * 1. The reference pads the text panel `80px 64px 80px 280px` so the copy lines up with the site
 *    container on a full-bleed section. A widget sits inside an Elementor container that already
 *    has padding, so a symmetric clamp is used instead — 280px would push the copy off-centre in
 *    every normal layout. Users placing it full-bleed can restore it from Section → Padding.
 * 2. The floating card row is a slider, but it reuses the shared WLPackSlider — the same Slick
 *    setup every other pack slider runs — so this widget adds no slider code. It is skipped when
 *    the cards already fit, and the row stays scrollable if the script never loads.
 *
 * The countdown markup comes from $this->countdown() and is driven by WLPackCountdown in
 * pack-widgets.js. The outer .wl-cb wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $data  eyebrow, live_dot, figure, figure_suffix, headline, description, coupon, note,
 *                   media_image, media_alt, media_tag, primary, secondary, stats, cards, countdown.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_cb_arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
    . '<line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>';
?>
<div class="wl-cb-inner wl-cb-inner--split">
    <div class="wl-cb-body">
        <?php if ( '' !== $data['eyebrow'] ) : ?>
            <span class="wl-cb-eyebrow">
                <?php if ( $data['live_dot'] ) : ?>
                    <span class="wl-cb-dot" aria-hidden="true"></span>
                <?php endif; ?>
                <?php echo esc_html( $data['eyebrow'] ); ?>
            </span>
        <?php endif; ?>

        <?php echo $this->figure( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside figure() ?>

        <?php if ( '' !== $data['headline'] ) : ?>
            <h2 class="wl-cb-headline"><?php echo $this->headline( $data['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
        <?php endif; ?>

        <?php if ( '' !== $data['description'] ) : ?>
            <p class="wl-cb-desc"><?php echo esc_html( $data['description'] ); ?></p>
        <?php endif; ?>

        <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>

        <?php if ( '' !== $data['coupon'] ) : ?>
            <span class="wl-cb-coupon"><?php echo esc_html( $data['coupon'] ); ?></span>
        <?php endif; ?>

        <?php
        $wl_cb_buttons = $this->button( $data['primary'], 'primary', $wl_cb_arrow )
            . $this->button( $data['secondary'], 'secondary', $wl_cb_arrow );
        if ( '' !== $wl_cb_buttons ) :
            ?>
            <div class="wl-cb-actions"><?php echo $wl_cb_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside button() ?></div>
        <?php endif; ?>

        <?php if ( '' !== $data['note'] ) : ?>
            <p class="wl-cb-note"><?php echo esc_html( $data['note'] ); ?></p>
        <?php endif; ?>
    </div>

    <?php
    // The cards go inside the media wrapper so they share its stacking context and sit above
    // the foot gradient rather than behind it.
    echo $this->media( $data, $this->cards( $data['cards'], $data['cards_label'], $data['cards_slider'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() and cards()
    ?>
</div>
