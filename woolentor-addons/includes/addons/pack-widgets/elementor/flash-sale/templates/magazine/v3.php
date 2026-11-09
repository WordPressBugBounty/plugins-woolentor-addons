<?php
/**
 * Flash Sale — Magazine / Variant 3: the printed sale list.
 *
 * Designed, not transcribed: magazine-style v3 holds a timer in `.buying-guides` but no sale products. The espresso panel follows that banner; the list reads as a printed page.
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
                </div>
                <div class="wl-fs-body">
                    <?php echo $this->category( $product, $settings ) . $this->title( $product ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ) . $this->cart( $product, $settings ); ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
