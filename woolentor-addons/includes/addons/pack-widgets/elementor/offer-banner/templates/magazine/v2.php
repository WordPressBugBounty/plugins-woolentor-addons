<?php
/**
 * Offer Banner — Magazine / Variant 2.
 *
 * The richest layout in the set. Two tall cards, each sweeping a coral or near-black gradient
 * across the photograph from the left so the image still shows down the right side. A frosted
 * disc stamps the discount in the top-right corner. Inside: a campaign pill, a very large
 * condensed headline, a note, a ticked feature list, a button beside a coupon chip, and a line
 * of terms.
 *
 * The two tones come from each card's own "Card Tone" setting, so a user can order them however
 * they like rather than the first card always being coral.
 *
 * Reference: design-reference/new_temlate/magazine-style/v2/homepage.html:5419 — `.offer-grid`
 *
 * Despite the copy stopping at 74% width, `.of-media` is `position: absolute; inset: 0` — this
 * is an overlay card like the other eleven, not a panelled one.
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

$wl_ob_arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
    . '<line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" /></svg>';
?>
<div class="wl-ob-grid">
    <?php foreach ( $items as $item ) : ?>
        <?php echo $this->card_open( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php echo $this->card_media( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
            <?php echo $this->card_badge( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badge() ?>
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

                <?php echo $this->card_features( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_features() ?>

                <?php if ( '' !== $item['cta_text'] || '' !== $item['meta'] ) : ?>
                    <div class="wl-ob-foot">
                        <?php echo $this->card_cta( $item, $wl_ob_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cta() ?>
                        <?php if ( '' !== $item['meta'] ) : ?>
                            <span class="wl-ob-meta"><?php echo esc_html( $item['meta'] ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( '' !== $item['note'] ) : ?>
                    <p class="wl-ob-note"><?php echo esc_html( $item['note'] ); ?></p>
                <?php endif; ?>
            </div>
        <?php echo $this->card_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
