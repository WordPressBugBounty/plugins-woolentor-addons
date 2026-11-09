<?php
/**
 * Flash Sale — Magazine / Variant 2: the dark panel and its rail.
 *
 * Transcribed from `design-reference/new_temlate/magazine-style/v2/homepage.html` — `#flash-sale`, with the `.fcard` the page builds in its script.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
$shown = $this->shown( $settings, 4 );
?>
<div class="wl-fs-panel wl-fs-surface">
    <div class="wl-fs-head">
        <div class="wl-fs-head-main"><?php echo $this->eyebrow( $settings ) . $this->heading( $settings ); ?></div>
        <?php echo $this->countdown( $settings, $style, $variant ); ?>
    </div>
    <div class="wl-fs-rail-wrap">
        <div class="wl-fs-rail" style="--wl-fs-shown:<?php echo (int) $shown; ?>"<?php echo $this->slider_attr( $settings, $style, $variant, $shown ); ?>>
            <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
                <div class="wl-fs-slide">
                    <article class="wl-fs-item">
                        <div class="wl-fs-media">
                            <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ) . $this->low_tag( $product, $settings ); ?>
                        </div>
                        <div class="wl-fs-body">
                            <?php echo $this->category( $product, $settings ) . $this->title( $product ) . $this->price( $product ) . $this->stock( $product, $settings, $style, $variant ) . $this->cart( $product, $settings, $this->icon( 'bag' ) ); ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <?php echo $this->arrows( $items, $settings, $shown ); ?>
    </div>
</div>
