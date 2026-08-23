<?php
/**
 * Store Highlights — Luxury / Variant 2.
 *
 * Understated single-line rows on a pale sage ground: a small olive icon, then the claim and
 * its supporting note running on together at a quiet 12px. Hairlines separate the rows.
 *
 * Reference: design-reference/new_temlate/luxury-style/v2/Checkout.html:4354 — `.co-sum__trust`
 * (and the matching `.csum__trust` in Cart.html). This variant's homepage carries a scrolling
 * values band instead of a static highlights block; that band belongs to the separate Marquee
 * widget, so the checkout summary's trust list — same designer, same variant — sets the language.
 *
 * The outer .wl-sh wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array  $items          Each: icon, title, subtitle, url, is_external, nofollow, is_highlighted.
 * @var string $section_label  Unused by this layout.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-sh-list">
    <?php foreach ( $items as $item ) : ?>
        <?php echo $this->item_open( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_open() ?>
            <?php echo $this->item_icon( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_icon() ?>
            <?php if ( $item['title'] ) : ?>
                <span class="wl-sh-title"><?php echo esc_html( $item['title'] ); ?></span>
            <?php endif; ?>
            <?php if ( $item['subtitle'] ) : ?>
                <span class="wl-sh-sub"><?php echo wp_kses_post( $item['subtitle'] ); ?></span>
            <?php endif; ?>
        <?php echo $this->item_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
