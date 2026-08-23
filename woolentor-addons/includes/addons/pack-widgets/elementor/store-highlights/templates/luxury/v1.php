<?php
/**
 * Store Highlights — Luxury / Variant 1.
 *
 * Dark graphite band with gold hairline gradients along its top and bottom edge. Each item is
 * centred: a 64px gold-ringed circle holding the icon, then the title and a muted description
 * below it. Vertical rules divide the columns.
 *
 * Reference: design-reference/new_temlate/luxury-style/v1/home-luxury.html:8097 — `.section-trust`
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
