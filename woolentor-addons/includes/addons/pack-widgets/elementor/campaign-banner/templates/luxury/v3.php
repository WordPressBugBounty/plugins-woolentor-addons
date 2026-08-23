<?php
/**
 * Campaign Banner — Luxury / Variant 3.
 *
 * The compact sale strip: a pale rounded band with the label on the left, an oversized inline
 * `HH : MM : SS` timer beside it, two lines of copy pushed right, and a charcoal pill carrying the
 * coupon code at the end. No photograph — this variant is a rule across the page, not a plate.
 *
 * Reference: design-reference/new_temlate/luxury-style/v3/homepage.html:5005 — `.sale-banner`
 *
 * The reference's four sale cards below the strip are **not** part of this widget: they are
 * products with prices, thumbnails and a Shop action, which is Feature Product's territory. Only
 * the strip is the campaign banner.
 *
 * The timer shows bare figures, no unit captions — set Countdown → Units to Hours / Minutes /
 * Seconds to match the reference exactly. The captions are hidden by this variant's CSS rather
 * than by the markup, so a merchant who does want "DAYS" under the number only has to override one
 * rule.
 *
 * The outer .wl-cb wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $data
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-cb-inner wl-cb-inner--band">
    <?php if ( '' !== $data['eyebrow'] ) : ?>
        <span class="wl-cb-eyebrow">
            <?php if ( $data['live_dot'] ) : ?>
                <span class="wl-cb-dot" aria-hidden="true"></span>
            <?php endif; ?>
            <?php echo esc_html( $data['eyebrow'] ); ?>
        </span>
    <?php endif; ?>

    <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>

    <div class="wl-cb-body">
        <?php if ( '' !== $data['headline'] ) : ?>
            <h3 class="wl-cb-headline"><?php echo $this->headline( $data['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h3>
        <?php endif; ?>

        <?php if ( '' !== $data['description'] ) : ?>
            <p class="wl-cb-desc"><?php echo esc_html( $data['description'] ); ?></p>
        <?php endif; ?>
    </div>

    <?php
    $wl_cb_buttons = $this->button( $data['primary'] ) . $this->button( $data['secondary'], 'secondary' );

    if ( '' !== $data['coupon'] || '' !== $wl_cb_buttons ) :
        ?>
        <div class="wl-cb-actions">
            <?php if ( '' !== $data['coupon'] ) : ?>
                <span class="wl-cb-coupon"><?php echo esc_html( $data['coupon'] ); ?></span>
            <?php endif; ?>
            <?php echo $wl_cb_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside button() ?>
        </div>
    <?php endif; ?>

    <?php if ( '' !== $data['note'] ) : ?>
        <p class="wl-cb-note"><?php echo esc_html( $data['note'] ); ?></p>
    <?php endif; ?>
</div>
