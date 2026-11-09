<?php
/**
 * Flash Sale — Luxury / Variant 1: the gold ledger.
 *
 * Designed, not transcribed: luxury-style v1's `.section-deals` is a bundle builder. Written in that
 * pack's language — gold on obsidian, translucent cards, the hairlines of its trust figures.
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
        <?php echo $this->eyebrow( $settings ) . $this->heading( $settings ) . $this->description( $settings ); ?>
    </div>
    <?php echo $this->countdown( $settings, $style, $variant ); ?>
</div>

<div class="wl-fs-grid">
    <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
        <article class="wl-fs-item">
            <div class="wl-fs-media">
                <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ); ?>
                <div class="wl-fs-icons"><?php echo $this->icons( $product, $settings ); ?></div>
            </div>
            <div class="wl-fs-body">
                <?php echo $this->category( $product, $settings ) . $this->title( $product ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ) . $this->cart( $product, $settings ); ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
