<?php
/**
 * Campaign Banner — Magazine / Variant 1.
 *
 * The newsroom flash-deal header: a red-ruled kicker over a heavy condensed headline whose `<em>`
 * turns signal-red, and — pushed to the far end of the band — the countdown boxed in news-black,
 * "Ends in" set beside the figures rather than above them.
 *
 * Reference: design-reference/new_temlate/magazine-style/v1/home-magazine.html:6809 — `.flash-head`
 *
 * The reference's `.flash-grid` of deal cards below the header is **not** part of this widget:
 * those carry prices, discount badges, wishlist, quick view and compare, which is Feature Product's
 * territory. Only the header band is the campaign banner.
 *
 * "Ends in" comes from Countdown → Lead-in Label, the control added for this variant.
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
            <h2 class="wl-cb-headline"><?php echo $this->headline( $data['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
        <?php endif; ?>

        <?php
        $wl_cb_buttons = $this->button( $data['primary'] ) . $this->button( $data['secondary'], 'secondary' );

        if ( '' !== $data['description'] || '' !== $data['coupon'] || '' !== $wl_cb_buttons || '' !== $data['note'] ) :
            ?>
            <div class="wl-cb-cta-group">
                <?php if ( '' !== $data['description'] ) : ?>
                    <p class="wl-cb-desc"><?php echo esc_html( $data['description'] ); ?></p>
                <?php endif; ?>

                <?php if ( '' !== $data['coupon'] ) : ?>
                    <span class="wl-cb-coupon"><?php echo esc_html( $data['coupon'] ); ?></span>
                <?php endif; ?>

                <?php if ( '' !== $wl_cb_buttons ) : ?>
                    <div class="wl-cb-actions"><?php echo $wl_cb_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside button() ?></div>
                <?php endif; ?>

                <?php if ( '' !== $data['note'] ) : ?>
                    <p class="wl-cb-note"><?php echo esc_html( $data['note'] ); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>
</div>
