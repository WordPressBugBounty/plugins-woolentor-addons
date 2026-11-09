<?php
/**
 * Flash Sale — Modern / Variant 1: the dark band and its deals.
 *
 * Transcribed from `design-reference/new_temlate/modern-style/v1/home-modern.html` — `.deal-head`,
 * `.deal-grid`.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-fs-band">
    <div class="wl-fs-head-main">
        <?php echo $this->eyebrow( $settings ) . $this->heading( $settings ) . $this->description( $settings ); ?>
    </div>
    <?php echo $this->countdown( $settings, $style, $variant ); ?>
</div>

<div class="wl-fs-grid">
    <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
        <article class="wl-fs-item">
            <div class="wl-fs-media">
                <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ); ?>
                <div class="wl-fs-icons"><?php echo $this->icons( $product, $settings, [ 'wishlist', 'compare' ] ); ?></div>
            </div>
            <div class="wl-fs-body">
                <div class="wl-fs-row"><?php echo $this->brand( $product, $settings ) . $this->rating( $product, $settings ); ?></div>
                <?php echo $this->title( $product ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ); ?>
                <div class="wl-fs-actions">
                    <?php echo $this->cart( $product, $settings, $this->icon( 'bag' ) ) . $this->icons( $product, $settings, [ 'quickview' ] ); ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
