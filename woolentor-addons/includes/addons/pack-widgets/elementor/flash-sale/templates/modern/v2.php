<?php
/**
 * Flash Sale — Modern / Variant 2: the split.
 *
 * Designed, not transcribed: modern-style v2's homepage has no product-led sale. Its testimonial card's dark rounded panel with an olive bloom holds the copy; its `.pc` cards hold the products.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-fs-split">
    <div class="wl-fs-aside wl-fs-surface">
        <?php echo $this->eyebrow( $settings ) . $this->heading( $settings ) . $this->description( $settings ) . $this->countdown( $settings, $style, $variant ) . $this->cta( $settings ); ?>
    </div>
    <div class="wl-fs-grid">
        <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
            <article class="wl-fs-item">
                <div class="wl-fs-media">
                    <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ); ?>
                    <div class="wl-fs-icons"><?php echo $this->icons( $product, $settings ); ?></div>
                    <?php echo $this->cart( $product, $settings ); ?>
                </div>
                <div class="wl-fs-body">
                    <?php echo $this->title( $product ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ); ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
