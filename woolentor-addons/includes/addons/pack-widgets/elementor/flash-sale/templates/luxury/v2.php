<?php
/**
 * Flash Sale — Luxury / Variant 2: the ruled banner.
 *
 * Designed, not transcribed: luxury-style v2's homepage has no product-led sale. The dark olive ruled ground comes from its featured testimonial; the square cards from its `.pc`.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-fs-banner wl-fs-surface">
    <?php echo $this->eyebrow( $settings ) . $this->heading( $settings ) . $this->description( $settings ) . $this->countdown( $settings, $style, $variant ); ?>
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
                <?php echo $this->title( $product ) . $this->price( $product ); ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
