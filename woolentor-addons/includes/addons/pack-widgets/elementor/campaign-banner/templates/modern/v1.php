<?php
/**
 * Campaign Banner — Modern / Variant 1.
 *
 * A dark graphite band with a cyan glow bleeding in from the right. Copy on the left — a pulsing
 * live tag, a tight headline, a line of supporting text — and four glassy countdown cells on the
 * right. No photograph: this variant is a band, and the media controls are hidden for it.
 *
 * Reference: design-reference/new_temlate/modern-style/v1/home-modern.html:6969 — `.deal-head`
 *
 * The reference carries no buttons, coupon or fine print, but a user may add them; they render
 * beneath the copy rather than being dropped.
 *
 * The countdown markup comes from $this->countdown() and is driven by WLPackCountdown in
 * pack-widgets.js — a thin adapter over the plugin's bundled `countdown-min` library.
 * The outer .wl-cb wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $data  eyebrow, live_dot, figure, figure_suffix, headline, description, coupon, note,
 *                   media_image, media_alt, media_tag, primary, secondary, stats, cards, countdown.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-cb-inner wl-cb-inner--band">
    <div class="wl-cb-body">
        <?php if ( '' !== $data['eyebrow'] ) : ?>
            <span class="wl-cb-eyebrow">
                <?php if ( $data['live_dot'] ) : ?>
                    <span class="wl-cb-dot" aria-hidden="true"></span>
                <?php endif; ?>
                <?php echo esc_html( $data['eyebrow'] ); ?>
            </span>
        <?php endif; ?>

        <?php if ( '' !== $data['headline'] ) : ?>
            <h3 class="wl-cb-headline"><?php echo $this->headline( $data['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h3>
        <?php endif; ?>

        <?php if ( '' !== $data['description'] ) : ?>
            <p class="wl-cb-desc"><?php echo esc_html( $data['description'] ); ?></p>
        <?php endif; ?>

        <?php if ( '' !== $data['coupon'] ) : ?>
            <span class="wl-cb-coupon"><?php echo esc_html( $data['coupon'] ); ?></span>
        <?php endif; ?>

        <?php
        $wl_cb_buttons = $this->button( $data['primary'] ) . $this->button( $data['secondary'], 'secondary' );
        if ( '' !== $wl_cb_buttons ) :
            ?>
            <div class="wl-cb-actions"><?php echo $wl_cb_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside button() ?></div>
        <?php endif; ?>

        <?php if ( '' !== $data['note'] ) : ?>
            <p class="wl-cb-note"><?php echo esc_html( $data['note'] ); ?></p>
        <?php endif; ?>
    </div>

    <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>
</div>
