<?php
/**
 * Campaign Banner — Magazine / Variant 2.
 *
 * The same band read in the dark: a starred coral label over a tight condensed headline on
 * near-black, with four frosted countdown cells and coral colons at the other end.
 *
 * Reference: design-reference/new_temlate/magazine-style/v2/homepage.html:5461 — `.flash-head`
 *
 * v1 and v2 are the same section on two different homepages, which is exactly why they must not
 * render the same way. They are kept apart on ground and weight: v1 is black-on-paper with a
 * red-ruled kicker and a black timer box; v2 is white-on-black with a starred coral label and
 * frosted cells. The `.flash-slider` rail of products beneath the reference header belongs to
 * Feature Product, not here.
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
