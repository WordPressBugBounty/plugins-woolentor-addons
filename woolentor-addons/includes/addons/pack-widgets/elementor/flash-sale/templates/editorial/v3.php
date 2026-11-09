<?php
/**
 * Flash Sale — Editorial / Variant 3: the photograph and two cards.
 *
 * Transcribed from `design-reference/new_temlate/editorial-style/v3/homepage.html` — `.flash-sale`. The arrows sit on the photograph and page the cards.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
$shown = $this->shown( $settings, 2 );
?>
<div class="wl-fs-head">
    <?php echo $this->heading( $settings ) . $this->countdown( $settings, $style, $variant ); ?>
</div>

<div class="wl-fs-plate">
    <div class="wl-fs-photo">
        <?php echo $this->photo( $settings ); ?>
        <div class="wl-fs-nav"><?php echo $this->arrows( $items, $settings, $shown ); ?></div>
    </div>
    <div class="wl-fs-panel wl-fs-surface">
        <?php echo $this->panel_head( $settings ); ?>
        <div class="wl-fs-rail" style="--wl-fs-shown:<?php echo (int) $shown; ?>"<?php echo $this->slider_attr( $settings, $style, $variant, $shown ); ?>>
            <?php foreach ( $items as $product ) : $this->set_product( $product ); ?>
                <div class="wl-fs-slide">
                    <article class="wl-fs-item">
                        <div class="wl-fs-media">
                            <?php echo $this->thumb( $product ) . $this->badge( $product, $settings ); ?>
                            <div class="wl-fs-icons"><?php echo $this->icons( $product, $settings ); ?></div>
                            <?php echo $this->cart( $product, $settings ); ?>
                        </div>
                        <div class="wl-fs-body">
                            <?php echo $this->title( $product ) . $this->price( $product ) . $this->swatches( $product, $settings ); ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
