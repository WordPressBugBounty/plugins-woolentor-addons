<?php
/**
 * Store Highlights — Modern / Variant 2.
 *
 * Bordered proof cards. Icon on top, a large stat-weight title, then a muted supporting line.
 * An item with "Highlight This Item" switched on takes the accent border.
 *
 * Reference: design-reference/new_temlate/modern-style/v2/homepage.html — `.bs-proof`
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
