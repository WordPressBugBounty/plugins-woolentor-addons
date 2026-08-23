<?php
/**
 * Offer Banner — Modern / Variant 3.
 *
 * An asymmetric pair on a fixed-height row: a wide card beside a narrow one. Content is centred
 * in each card over a bottom-weighted scrim, with a very large headline and a small white pill
 * button carrying a diagonal arrow.
 *
 * The wide/narrow split comes from the grid track, not from the markup — the pack stylesheet sets
 * --wl-ob-template to 2fr 1fr. Each card's own "Card Size" only scales its headline, so reordering
 * the repeater rearranges the pair without breaking the track.
 *
 * Reference: design-reference/new_temlate/modern-style/v3/01-homepage.html:7084
 * — `.promo-banners__grid`
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

$wl_ob_arrow = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
    . '<line x1="7" y1="17" x2="17" y2="7" /><polyline points="7 7 17 7 17 17" /></svg>';
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
                    <h2 class="wl-ob-title"><?php echo $this->card_title( $item['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in card_title() ?></h2>
                <?php endif; ?>

                <?php if ( '' !== $item['subtitle'] ) : ?>
                    <p class="wl-ob-sub"><?php echo esc_html( $item['subtitle'] ); ?></p>
                <?php endif; ?>

                <?php echo $this->card_cta( $item, $wl_ob_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cta() ?>
            </div>
        <?php echo $this->card_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
