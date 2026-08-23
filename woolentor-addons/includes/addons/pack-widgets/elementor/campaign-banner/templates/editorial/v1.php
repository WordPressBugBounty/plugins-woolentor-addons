<?php
/**
 * Campaign Banner — Editorial / Variant 1.
 *
 * A near-black drop announcement: copy on the left — a clay live-dot eyebrow, a serif headline,
 * a hairline, an enormous serif countdown — and a desaturated photograph on the right under a
 * left-to-right scrim, with a frosted badge pinned to its foot.
 *
 * Reference: design-reference/new_temlate/editorial-style/v1/home-editorial.html:6586
 * — `.section-countdown`
 *
 * Two deliberate departures from the reference:
 *
 * 1. **The notify-me email form is not rendered.** A form needs somewhere to submit; a field that
 *    silently swallows an address is worse than no field. The primary button takes that slot and
 *    the "Get notified…" line above it stays as the description, so the design reads the same.
 *    A store wanting real signups puts its newsletter widget beside this one.
 * 2. **The badge is one label, not three.** The reference's badge carries an eyebrow, a product
 *    name and a stock line, and swaps all three per slide from JavaScript. A single static string
 *    is the honest equivalent for a content-driven widget.
 *
 * The countdown markup comes from $this->countdown(); the media panel becomes a slider on its own
 * when the user adds more than one image. Both are driven by pack-widgets.js.
 * The outer .wl-cb wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $data
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
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

        <?php if ( '' !== $data['headline'] ) : ?>
            <h2 class="wl-cb-headline"><?php echo $this->headline( $data['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
        <?php endif; ?>

        <span class="wl-cb-rule" aria-hidden="true"></span>

        <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>

        <?php
        // The reference keeps the prompt, the action and the fine print as one tight block —
        // the section's 44px rhythm separates major blocks, not the lines inside this one.
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

    <?php echo $this->media( $data, '<span class="wl-cb-media-scrim" aria-hidden="true"></span>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
</div>
