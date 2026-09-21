<?php
/**
 * Social Proof — Magazine / Variant 1: the summary and the wall.
 *
 * The V1 role: every review at once, no navigation.
 *
 * Available: $items, $settings, $style, $variant, and $this (the widget, for the helpers).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-sp-split">

    <div class="wl-sp-aside">
        <?php
        echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
        echo $this->summary_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside summary_html().
        echo $this->histogram_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside histogram_html().
        ?>
    </div>

    <div class="wl-sp-wall">
        <?php foreach ( $items as $position => $item ) : ?>
            <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
        <?php endforeach; ?>
    </div>

</div>
