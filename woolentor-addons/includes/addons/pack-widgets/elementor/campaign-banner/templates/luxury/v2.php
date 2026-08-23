<?php
/**
 * Campaign Banner — Luxury / Variant 2.
 *
 * The seasonal campaign plate: a darkened photograph filling the section under a 100° dark-to-clear
 * wash, an inset hairline frame with two lime corner ticks, the issue number in italic lime above a
 * ruled eyebrow, a display headline, and two buttons — a solid one and a ghost.
 *
 * Reference: design-reference/new_temlate/luxury-style/v2/homepage.html:5142 — `.camp`
 *
 * Two mappings worth naming:
 *
 *   - **Fine Print becomes the vertical side label.** The reference sets `Côte d'Azur · 2026` in
 *     `writing-mode: vertical-rl` down the right edge. Rather than add a control only one of the
 *     twelve variants would ever use, this variant renders the Fine Print field there. The control
 *     says so in its own description.
 *   - **The countdown is ours, not the reference's.** `.camp` has no timer; every Campaign Banner
 *     variant offers one, so it sits above the buttons in this variant's lime-on-dark treatment and
 *     collapses to nothing when switched off.
 *
 * The outer .wl-cb wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $data
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_cb_arrow = '<svg width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true">'
    . '<path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.3"'
    . ' stroke-linecap="round" /></svg>';

$wl_cb_play = '<span class="wl-cb-play" aria-hidden="true">'
    . '<svg width="7" height="8" viewBox="0 0 7 8" fill="currentColor"><path d="M0 0l7 4-7 4z" /></svg>'
    . '</span>';
?>
<div class="wl-cb-inner wl-cb-inner--overlay">
    <?php echo $this->media( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
    <span class="wl-cb-media-scrim" aria-hidden="true"></span>

    <?php if ( '' !== $data['note'] ) : ?>
        <span class="wl-cb-vlabel"><?php echo esc_html( $data['note'] ); ?></span>
    <?php endif; ?>

    <div class="wl-cb-body">
        <?php echo $this->figure( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside figure() ?>

        <?php if ( '' !== $data['eyebrow'] ) : ?>
            <span class="wl-cb-eyebrow">
                <?php if ( $data['live_dot'] ) : ?>
                    <span class="wl-cb-dot" aria-hidden="true"></span>
                <?php endif; ?>
                <?php echo esc_html( $data['eyebrow'] ); ?>
            </span>
        <?php endif; ?>

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
        // The play glyph leads the ghost button in the reference, so it is passed as that button's
        // icon and pulled in front of the label by CSS order.
        $wl_cb_buttons = $this->button( $data['primary'], 'primary', $wl_cb_arrow )
            . $this->button( $data['secondary'], 'secondary', $wl_cb_play );

        if ( '' !== $wl_cb_buttons ) :
            ?>
            <div class="wl-cb-actions"><?php echo $wl_cb_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside button() ?></div>
        <?php endif; ?>
    </div>
</div>
