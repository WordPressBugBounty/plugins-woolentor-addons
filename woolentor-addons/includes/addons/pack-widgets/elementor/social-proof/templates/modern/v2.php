<?php
/**
 * Social Proof — Modern / Variant 2: the spotlight in a dark card.
 *
 * Available: $items, $settings, $style, $variant, and $this (the widget, for the helpers).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>

<div class="wl-sp-panel">
    <span class="wl-sp-ghost" aria-hidden="true">&ldquo;</span>

    <div class="wl-sp-slider wl-sp-stage"<?php echo $this->slider_attr( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside slider_attr(). ?>>
        <?php foreach ( $items as $position => $item ) : ?>
            <div class="wl-sp-slide">
                <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    // Counter, faces and arrows on one line — the reference's `.tm-nav`.
    echo $this->picker_html( $items, $this->picker_mode( $style, $variant ), $settings, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside picker_html().
    ?>
</div>
