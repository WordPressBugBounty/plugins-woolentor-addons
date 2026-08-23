<?php
/**
 * Store Highlights — Magazine / Variant 3.
 *
 * A centred row of rounded pill badges, each a small icon beside a short bold claim. The
 * quietest of the twelve layouts — it states the promises and gets out of the way.
 *
 * Not a column grid: the pills centre themselves and wrap as needed, so the Columns control
 * is hidden for this variant. A subtitle is optional and reads as a muted tail inside the pill.
 *
 * Reference: design-reference/new_temlate/magazine-style/v3/homepage.html:5504
 * — `.expert-tip__pill`. That section's larger tip cards are image-led, and this widget has no
 * image field, so the pill row is the part of it that fits the content model.
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
