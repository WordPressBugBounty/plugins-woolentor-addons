<?php
/**
 * Flash Sale — Editorial / Variant 2: the huge title and its plate.
 *
 * Transcribed from `design-reference/new_temlate/editorial-style/v2/homepage.html` — `#sale`.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
$shown = $this->shown( $settings, 3 );
?>
<div class="wl-fs-head">
    <div class="wl-fs-head-main"><?php echo $this->eyebrow( $settings ) . $this->heading( $settings ); ?></div>
    <?php echo $this->countdown( $settings, $style, $variant ); ?>
</div>

<div class="wl-fs-plate">
    <div class="wl-fs-photo">
        <?php echo $this->flag( $settings ) . $this->photo( $settings ); ?>
    </div>
    <div class="wl-fs-panel wl-fs-surface">
        <?php echo $this->panel_head( $settings ); ?>
        <div class="wl-fs-rail" style="--wl-fs-shown:<?php echo (int) $shown; ?>"<?php echo $this->slider_attr( $settings, $style, $variant, $shown ); ?>>
            <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
                <div class="wl-fs-slide">
                    <article class="wl-fs-item">
                        <div class="wl-fs-media">
                            <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ); ?>
                            <div class="wl-fs-icons"><?php echo $this->icons( $product, $settings, [ 'wishlist', 'compare' ] ); ?></div>
                            <div class="wl-fs-actions">
                                <?php echo $this->cart( $product, $settings ) . $this->icons( $product, $settings, [ 'quickview' ] ); ?>
                            </div>
                        </div>
                        <div class="wl-fs-body">
                            <?php echo $this->category( $product, $settings ) . $this->title( $product ) . $this->price( $product ) . $this->swatches( $product, $settings ); ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="wl-fs-nav"><?php echo $this->arrows( $items, $settings, $shown ); ?></div>
    </div>
</div>
