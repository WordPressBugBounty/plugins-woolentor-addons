<?php
/**
 * Flash Sale — Editorial / Variant 1: the quiet notice.
 *
 * Designed, not transcribed: editorial-style v1's `.section-countdown` is a product launch with no
 * products. Written in that pack's language — cream, clay, a serif, no card chrome — with the
 * light-weight numerals of that section and the bare columns of its journal.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-fs-head">
    <div class="wl-fs-head-main">
        <?php echo $this->eyebrow( $settings ) . $this->heading( $settings ); ?>
    </div>
    <?php echo $this->countdown( $settings, $style, $variant ); ?>
</div>

<div class="wl-fs-grid">
    <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
        <article class="wl-fs-item">
            <div class="wl-fs-media">
                <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ); ?>
            </div>
            <div class="wl-fs-body">
                <?php echo $this->category( $product, $settings ) . $this->title( $product ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ) . $this->cart( $product, $settings ); ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
