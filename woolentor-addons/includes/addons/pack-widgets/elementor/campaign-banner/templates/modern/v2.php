<?php
/**
 * Campaign Banner — Modern / Variant 2.
 *
 * A split card on warm paper: a near-black text panel on the left, a photograph filling the right.
 * A ruled olive label, a very tight 800-weight headline, a line of copy, a sharp-cornered olive
 * button, and a stats row under a hairline rule.
 *
 * Reference: design-reference/new_temlate/modern-style/v2/homepage.html:6705
 * — `.campaign__card`
 *
 * The reference carries no countdown; the control is still honoured if a user turns it on, and it
 * sits between the copy and the button where it reads naturally.
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

$wl_cb_arrow = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2.2" stroke-linecap="round" aria-hidden="true">'
    . '<path d="M5 12h14M12 5l7 7-7 7" /></svg>';
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

        <?php echo $this->stats( $data['stats'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside stats() ?>

        <?php if ( '' !== $data['note'] ) : ?>
            <p class="wl-cb-note"><?php echo esc_html( $data['note'] ); ?></p>
        <?php endif; ?>
    </div>

    <?php echo $this->media( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
</div>
