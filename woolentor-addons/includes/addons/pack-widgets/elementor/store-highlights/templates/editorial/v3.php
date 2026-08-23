<?php
/**
 * Store Highlights — Editorial / Variant 3.
 *
 * Centred stat bar: a single row of items sized to their content, divided by short vertical
 * hairlines. Each item reads as a large serif stat followed by a quiet sans label on the same
 * baseline — "240+ Curated Pieces", "Free Delivery over €500".
 *
 * Not a column grid: the row centres itself, so the Columns control is hidden for this variant.
 * The item is deliberately flat — no text wrapper — so the title and subtitle share one baseline.
 * An icon is optional here; the reference has none, but one renders inline if set.
 *
 * Reference: design-reference/new_temlate/editorial-style/v3/homepage.html:4457 — `.hero-stats`
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
