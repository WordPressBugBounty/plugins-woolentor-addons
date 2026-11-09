<?php
/**
 * Flash Sale — Magazine / Variant 1: flash deals, boxed timer.
 *
 * Transcribed from `design-reference/new_temlate/magazine-style/v1/home-magazine.html` —
 * `.section-flash`.
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
                <div class="wl-fs-icons"><?php echo $this->icons( $product, $settings ); ?></div>
                <?php echo $this->cart( $product, $settings, $this->icon( 'bag' ) ); ?>
            </div>
            <div class="wl-fs-body">
                <?php echo $this->brand( $product, $settings ) . $this->title( $product ) . $this->rating( $product, $settings ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ); ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
