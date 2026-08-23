<?php
/**
 * Campaign Banner — Editorial / Variant 3.
 *
 * **Adapted, not taken from the reference.** This homepage's `.flash-sale`
 * (`editorial-style/v3/homepage.html:4950`) looks like a campaign banner and is not one: its
 * prev/next buttons swap the photograph *and* a paired group of products in one move
 * (`homepage.html:5493`), and each of those products carries Add to Cart, wishlist, quick view,
 * compare and colour swatches. That is a WooCommerce product component, so it belongs to the
 * Feature Product widget.
 *
 * A full-bleed frame, like v2 — but composed the other way round so the two never read as the
 * same design twice:
 *
 *   - v2 stacks its copy along the **foot**, buttons to the right, on deep teal-black
 *   - v3 **centres** everything inside an inset warm-stone frame, on this variant's paper palette
 *
 * The chip language is this variant's own `.banner-card__cta` (`homepage.html:850`) — a frosted
 * pane at 10% paper with a 32% hairline — and the 52×2 clay rule is lifted from its hero. It is
 * deliberately *not* built like the hero itself: Hero Banner is a separate widget, and two
 * widgets that look alike on one page help nobody.
 *
 * The Slides repeater's badge sits over the photograph's foot; the Display Figure is a watermark
 * behind the copy.
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
    <?php echo $this->media( $data, '', [ 'arrows' => true, 'dots' => false, 'fade' => false ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
    <span class="wl-cb-media-scrim" aria-hidden="true"></span>

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

        <span class="wl-cb-rule" aria-hidden="true"></span>

        <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>

        <?php
        $wl_cb_buttons = $this->button( $data['primary'], 'primary', $wl_cb_arrow )
            . $this->button( $data['secondary'], 'secondary' );

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
</div>
