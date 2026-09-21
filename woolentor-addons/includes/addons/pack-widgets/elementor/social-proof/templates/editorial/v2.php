<?php
/**
 * Social Proof — Editorial / Variant 2: press, then the carousel.
 *
 * The V2 role: a track the shopper pages through.
 *
 * Available: $items, $settings, $style, $variant, and $this (the widget, for the helpers).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>
<?php echo $this->press_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside press_html(). ?>

<div class="wl-sp-slider"<?php echo $this->slider_attr( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside slider_attr(). ?>>
    <?php foreach ( $items as $position => $item ) : ?>
        <div class="wl-sp-slide">
            <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="wl-sp-bottom">
    <?php
    echo $this->summary_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside summary_html().
    echo $this->nav_html( $items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside nav_html().
    ?>
</div>
