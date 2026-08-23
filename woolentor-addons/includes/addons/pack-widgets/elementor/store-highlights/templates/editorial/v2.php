<?php
/**
 * Store Highlights — Editorial / Variant 2.
 *
 * Small-caps section label above a row of numbered columns divided by hairline rules. No icon
 * box: the numeral carries the eye, the icon sits small and quiet beside the title.
 *
 * Item numbers come from a CSS counter rather than a control, so reordering the repeater
 * renumbers automatically and there is no field for a user to keep in sync.
 *
 * Adapted from design-reference/new_temlate/editorial-style/v2/ — this variant's homepage has
 * no highlight strip, so the hairline-rule and small-caps language of its cart option cards
 * (`cart.html` `.opt-card`) is carried over instead.
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
            <span class="wl-sh-head">
                <?php echo $this->item_icon( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_icon() ?>
                <?php if ( $item['title'] ) : ?>
                    <span class="wl-sh-title"><?php echo esc_html( $item['title'] ); ?></span>
                <?php endif; ?>
            </span>
            <?php if ( $item['subtitle'] ) : ?>
                <span class="wl-sh-sub"><?php echo wp_kses_post( $item['subtitle'] ); ?></span>
            <?php endif; ?>
        <?php echo $this->item_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
