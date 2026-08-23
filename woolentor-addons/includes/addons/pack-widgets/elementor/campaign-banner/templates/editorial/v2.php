<?php
/**
 * Campaign Banner — Editorial / Variant 2.
 *
 * A full-bleed campaign frame: the photograph fills the section under a top-and-bottom gradient,
 * with the ruled eyebrow at the top and, at the foot, a sky-blue kicker pill, an enormous italic
 * serif headline, a line of copy and a dashed coupon chip — the buttons stacked to the right.
 *
 * Reference: design-reference/new_temlate/editorial-style/v2/homepage.html:5665 — `.campaign`
 *
 * The reference uses a looping muted video with an unmute button. This renders the poster image
 * instead: a widget that autoplays video costs the visitor bandwidth they did not ask for, and the
 * Media control is an image field across all twelve variants. The gradient, framing and type are
 * the same, so the still reads as the reference does before the video starts.
 *
 * The outer .wl-cb wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $data
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_cb_arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2" stroke-linecap="round" aria-hidden="true">'
    . '<path d="M5 12h14M12 5l7 7-7 7" /></svg>';
?>
<div class="wl-cb-inner wl-cb-inner--overlay">
    <?php echo $this->media( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
    <span class="wl-cb-media-scrim" aria-hidden="true"></span>

    <div class="wl-cb-body">
        <?php if ( '' !== $data['eyebrow'] ) : ?>
            <span class="wl-cb-eyebrow">
                <?php if ( $data['live_dot'] ) : ?>
                    <span class="wl-cb-dot" aria-hidden="true"></span>
                <?php endif; ?>
                <?php echo esc_html( $data['eyebrow'] ); ?>
            </span>
        <?php endif; ?>

        <div class="wl-cb-foot">
            <div class="wl-cb-foot-text">
                <?php echo $this->figure( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside figure() ?>

                <?php if ( '' !== $data['headline'] ) : ?>
                    <h2 class="wl-cb-headline"><?php echo $this->headline( $data['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
                <?php endif; ?>

                <?php if ( '' !== $data['description'] ) : ?>
                    <p class="wl-cb-desc"><?php echo esc_html( $data['description'] ); ?></p>
                <?php endif; ?>

                <?php if ( '' !== $data['coupon'] ) : ?>
                    <span class="wl-cb-coupon"><?php echo esc_html( $data['coupon'] ); ?></span>
                <?php endif; ?>
            </div>

            <?php
            $wl_cb_buttons = $this->button( $data['primary'], 'primary', $wl_cb_arrow )
                . $this->button( $data['secondary'], 'secondary' );
            if ( '' !== $wl_cb_buttons || '' !== $data['note'] ) :
                ?>
                <div class="wl-cb-actions">
                    <?php echo $wl_cb_buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside button() ?>
                    <?php if ( '' !== $data['note'] ) : ?>
                        <p class="wl-cb-note"><?php echo esc_html( $data['note'] ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>
    </div>
</div>
