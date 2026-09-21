<?php
/**
 * Social Proof — Luxury / Variant 3: the rich card, one at a time.
 *
 * The V3 role: one review, large, with a picker.
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

<div class="wl-sp-slider wl-sp-stage"<?php echo $this->slider_attr( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside slider_attr(). ?>>
    <?php foreach ( $items as $position => $item ) : ?>
        <div class="wl-sp-slide">
            <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
        </div>
    <?php endforeach; ?>
</div>

<?php echo $this->picker_html( $items, $this->picker_mode( $style, $variant ), $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside picker_html(). ?>
