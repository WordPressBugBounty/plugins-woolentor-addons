<?php
/**
 * Offer Banner — Luxury / Variant 2.
 *
 * A pair of 16:11 cards on pale sage, each carrying an inset hairline frame with lime corner
 * ticks. Content is left-aligned and vertically centred: a ruled lime eyebrow, an italic
 * Cormorant headline, a soft note, and an outlined button whose gap widens as it fills lime.
 *
 * Reference: design-reference/new_temlate/luxury-style/v2/homepage.html:4467
 * — `.promo2__inner .pbanner`
 *
 * The frame and its two corner ticks are drawn entirely with pseudo-elements in the pack
 * stylesheet — the card's ::before is the frame, and the overlay's ::before/::after are the
 * ticks — so this variant needs no markup the other eleven do not have.
 *
 * The grid, card stacking and overlay mechanics come from .wl-ob-* in pack-widgets-base.css.
 * The outer .wl-ob wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $items  Each: image, image_alt, eyebrow, title, subtitle, cta_text, url,
 *                    is_external, nofollow, card_is_link, size, tone, badge_value,
 *                    badge_label, features, meta, note.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ob_arrow = '<svg width="13" height="9" viewBox="0 0 14 10" fill="none" aria-hidden="true">'
    . '<path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.2"'
    . ' stroke-linecap="round" /></svg>';
?>
<div class="wl-ob-grid">
    <?php foreach ( $items as $item ) : ?>
        <?php echo $this->card_open( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php echo $this->card_media( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
            <div class="wl-ob-body">
                <?php if ( '' !== $item['eyebrow'] ) : ?>
                    <span class="wl-ob-eyebrow"><?php echo esc_html( $item['eyebrow'] ); ?></span>
                <?php endif; ?>

                <?php if ( '' !== $item['title'] ) : ?>
                    <h3 class="wl-ob-title"><?php echo $this->card_title( $item['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in card_title() ?></h3>
                <?php endif; ?>

                <?php if ( '' !== $item['subtitle'] ) : ?>
                    <p class="wl-ob-sub"><?php echo esc_html( $item['subtitle'] ); ?></p>
                <?php endif; ?>

                <?php echo $this->card_cta( $item, $wl_ob_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cta() ?>
            </div>
        <?php echo $this->card_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
