<?php
/**
 * Store Highlights — Modern / Variant 3.
 *
 * Soft grey band of top-aligned rows: a 46px rounded neutral icon tile on the left, a 18px
 * heading and a relaxed grey line to its right. Calmer and roomier than modern v1's tight band.
 *
 * Reference: design-reference/new_temlate/modern-style/v3/02-shop.html:8370 — `section.benefits`
 * ("Why shop with Forma"). This variant's homepage carries a scrolling ticker instead of a
 * highlights block; that ticker belongs to the separate Marquee widget, so the shop page's
 * benefits grid — same designer, same variant — is the highlights layout here.
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
            <span class="wl-sh-text">
                <?php if ( $item['title'] ) : ?>
                    <span class="wl-sh-title"><?php echo esc_html( $item['title'] ); ?></span>
                <?php endif; ?>
                <?php if ( $item['subtitle'] ) : ?>
                    <span class="wl-sh-sub"><?php echo wp_kses_post( $item['subtitle'] ); ?></span>
                <?php endif; ?>
            </span>
        <?php echo $this->item_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
