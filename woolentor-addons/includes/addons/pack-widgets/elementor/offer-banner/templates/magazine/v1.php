<?php
/**
 * Offer Banner — Magazine / Variant 1.
 *
 * This variant's homepage carries no banner card grid, so the layout is built from its own
 * designer's card language rather than borrowed from a neighbouring variant — the `.pick-card`
 * treatment at `magazine-style/v1/home-magazine.html:2728`:
 *
 *   - a cyber-yellow stamp pinned to the photograph
 *   - a yellow wide-tracked category line
 *   - a heavy uppercase Barlow Condensed headline
 *   - a solid cyber-yellow button that flips to white on hover
 *   - a card that lifts six pixels and picks up a yellow hairline
 *
 * Two up rather than the reference's three: this widget makes statements, not a product shelf,
 * and the other two magazine variants are pairs. Everything else is that designer's own.
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
    . ' stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
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

                <?php echo $this->card_cta( $item, $wl_ob_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cta() ?>
            </div>
        <?php echo $this->card_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
