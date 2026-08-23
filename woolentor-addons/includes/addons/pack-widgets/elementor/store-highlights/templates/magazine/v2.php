<?php
/**
 * Store Highlights — Magazine / Variant 2.
 *
 * A small-caps label with a rule on either side, above a row of white cards that lift on
 * hover. Each card pairs a large rounded icon chip with a bold title and a quiet second line.
 * The chips cycle through the pack's four accent tints so the row reads as a set.
 *
 * Reference: design-reference/new_temlate/magazine-style/v2/homepage.html:5624 — `.trust`
 *
 * The outer .wl-sh wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array  $items          Each: icon, title, subtitle, url, is_external, nofollow, is_highlighted.
 * @var string $section_label  Optional small-caps label above the group.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php if ( $section_label ) : ?>
    <span class="wl-sh-label"><?php echo esc_html( $section_label ); ?></span>
<?php endif; ?>
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
