<?php
/**
 * Flash Sale — Modern / Variant 3: the dark panel and its photograph.
 *
 * Transcribed from `design-reference/new_temlate/modern-style/v3/01-homepage.html` — `.promo`, with real products in the floating rail.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
$shown = $this->shown( $settings, 3 );
?>
<div class="wl-fs-plate">
    <div class="wl-fs-panel wl-fs-surface">
        <?php echo $this->eyebrow( $settings ) . $this->percent( $settings ) . $this->heading( $settings ) . $this->description( $settings ) . $this->countdown( $settings, $style, $variant ) . $this->cta( $settings ) . $this->fine( $settings ); ?>
    </div>
    <div class="wl-fs-photo">
        <?php echo $this->photo( $settings ); ?>
        <div class="wl-fs-float">
            <div class="wl-fs-float-head">
                <?php echo $this->panel_head( $settings ); ?>
                <div class="wl-fs-nav"><?php echo $this->arrows( $items, $settings, $shown ); ?></div>
            </div>
            <div class="wl-fs-rail" style="--wl-fs-shown:<?php echo (int) $shown; ?>"<?php echo $this->slider_attr( $settings, $style, $variant, $shown ); ?>>
                <?php foreach ( $items as $product ) : ?>
                    <div class="wl-fs-slide">
                        <a class="wl-fs-item" href="<?php echo esc_url( $product->get_permalink() ); ?>">
                            <span class="wl-fs-media"><?php echo $this->image( $product ); ?></span>
                            <span class="wl-fs-body">
                                <?php echo $this->badge( $product, $settings ) . $this->name( $product ) . $this->price( $product ); ?>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
