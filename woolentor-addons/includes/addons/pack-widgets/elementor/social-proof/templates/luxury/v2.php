<?php
/**
 * Social Proof — Luxury / Variant 2: the carousel with one card enlarged.
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
    echo $this->nav_html( $items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside nav_html().
    ?>
</div>

<div class="wl-sp-slider"<?php echo $this->slider_attr( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside slider_attr(). ?>>
    <?php foreach ( $items as $position => $item ) : ?>
        <?php
            // `is-featured` is a class on the ordinary item, not a second markup path, so every
            // Style-tab control still reaches it.
            $html = $this->item_html( $item, $settings, $style, $variant, $position );

            if ( 0 === $position ) {
                $html = preg_replace( '/class="wl-sp-item/', 'class="wl-sp-item is-featured', $html, 1 );
            }

            echo '<div class="wl-sp-slide">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html().
            ?>
    <?php endforeach; ?>
</div>
