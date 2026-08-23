<?php
/**
 * Campaign Banner — Luxury / Variant 1.
 *
 * **Adapted, not taken from the reference.** The luxury v1 homepage carries no campaign section at
 * all. Its `.section-spotlight` looks close, but it is a product spotlight — price, rating, Add to
 * Cart — so it belongs to the Feature Product widget, not here. The whole page was searched before
 * this was written; the plan records the finding.
 *
 * Built only from luxury v1's own vocabulary, the rule this project has followed for every adapted
 * variant: the ivory ground, the ink display type, the single gold hairline and the flat ink
 * button are all lifted from that homepage — nothing is borrowed from v2 or v3.
 *
 * The shape is the quietest of the three: a two-column split with the copy on the ivory left and
 * the photograph held in a gold offset frame on the right. One CTA, never two — restraint is the
 * point of this pack's first variant.
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

    <?php echo $this->media( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
</div>
