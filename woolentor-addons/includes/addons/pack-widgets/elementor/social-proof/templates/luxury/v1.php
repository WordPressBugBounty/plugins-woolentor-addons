<?php
/**
 * Social Proof — Luxury / Variant 1: the featured review and its company.
 *
 * The V1 role: every review at once, no navigation.
 *
 * Available: $items, $settings, $style, $variant, and $this (the widget, for the helpers).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$featured = array_shift( $items );
?>
<div class="wl-sp-head-row">
    <?php
    echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
    echo $this->stats_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside stats_html().
    ?>
</div>

<div class="wl-sp-split">

    <div class="wl-sp-featured">
        <?php echo $this->item_html( $featured, $settings, $style, $variant, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
    </div>

    <?php if ( $items ) : ?>
        <div class="wl-sp-col">
            <?php foreach ( $items as $position => $item ) : ?>
                <?php echo $this->item_html( $item, $settings, $style, $variant, $position + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php echo $this->cta_html( $settings, array_merge( [ $featured ], $items ), $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
