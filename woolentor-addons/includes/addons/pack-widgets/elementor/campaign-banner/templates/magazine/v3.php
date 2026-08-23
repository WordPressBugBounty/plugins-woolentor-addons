<?php
/**
 * Campaign Banner — Magazine / Variant 3.
 *
 * Warm paper, two columns: centred copy on the left over a row of rounded thumbnails, and a tall
 * feature photograph on the right with a frosted tag across its foot. Picking a thumbnail swaps
 * the photograph and its tag together.
 *
 * Reference: design-reference/new_temlate/magazine-style/v3/homepage.html:5437 — `.favcol`
 *
 * The swap runs on the shared media slider — Slick in fade mode — rather than on bespoke code. The
 * picker sits in the copy column, on the far side of the widget from the slider, so it cannot be
 * Slick's own dots; `media_picker()` emits indexed buttons and WLCampaignBannerSlider hands them to
 * slickGoTo, reading `is-active` back from afterChange. With one Slide row the picker and the
 * slider both disappear and a single photograph remains, which is the right fallback.
 *
 * Each slide's Tag Label is both the thumbnail's caption and the category line on the feature tag —
 * the reference carries the same string in `data-cat` twice for the same reason.
 *
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

        <?php if ( '' !== $data['description'] ) : ?>
            <p class="wl-cb-desc"><?php echo esc_html( $data['description'] ); ?></p>
        <?php endif; ?>

        <?php echo $this->media_picker( $data['media_slides'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media_picker() ?>

        <?php echo $this->countdown( $data['countdown'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside countdown() ?>

        <?php
        $wl_cb_buttons = $this->button( $data['primary'] ) . $this->button( $data['secondary'], 'secondary' );

        if ( '' !== $data['coupon'] || '' !== $wl_cb_buttons || '' !== $data['note'] ) :
            ?>
            <div class="wl-cb-cta-group">
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

    <?php echo $this->media( $data, '', [ 'arrows' => false, 'dots' => false, 'fade' => true ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside media() ?>
</div>
