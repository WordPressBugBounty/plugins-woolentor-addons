<?php
/**
 * Social Proof — Magazine / Variant 2: the track with a progress bar.
 *
 * The V2 role: a track the shopper pages through.
 *
 * Available: $items, $settings, $style, $variant, and $this (the widget, for the helpers).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-sp-head-row">
    <?php
    echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
    echo $this->summary_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside summary_html().
    ?>
</div>

<div class="wl-sp-slider"<?php echo $this->slider_attr( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside slider_attr(). ?>>
    <?php foreach ( $items as $position => $item ) : ?>
        <div class="wl-sp-slide">
            <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="wl-sp-bottom">
    <?php
    echo $this->progress_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- no dynamic content.
    echo $this->nav_html( $items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside nav_html().
    ?>
</div>
