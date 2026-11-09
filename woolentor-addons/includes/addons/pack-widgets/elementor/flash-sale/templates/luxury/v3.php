<?php
/**
 * Flash Sale — Luxury / Variant 3: the timer banner and four overlay cards.
 *
 * Transcribed from `design-reference/new_temlate/luxury-style/v3/homepage.html` — `.flash-sale`. The reference prints a title and a separate name; a product has one name, so the bar holds thumb, price and link.
 *
 * Available: $items, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-fs-banner wl-fs-surface">
    <?php echo $this->eyebrow( $settings ) . $this->countdown( $settings, $style, $variant ) . $this->description( $settings ) . $this->cta( $settings ); ?>
</div>

<div class="wl-fs-grid">
    <?php foreach ( $items as $product ) : ?>
        <a class="wl-fs-item" href="<?php echo esc_url( $product->get_permalink() ); ?>">
            <span class="wl-fs-media"><?php echo $this->image( $product ) . $this->badge( $product, $settings ); ?></span>
            <span class="wl-fs-overlay" aria-hidden="true"></span>
            <span class="wl-fs-body">
                <span class="wl-fs-copy"><?php echo $this->category( $product, $settings ) . $this->name( $product ); ?></span>
                <span class="wl-fs-bar">
                    <span class="wl-fs-bar-left">
                        <span class="wl-fs-mini" aria-hidden="true"><?php echo $this->image( $product ); ?></span>
                        <?php echo $this->price( $product ); ?>
                    </span>
                    <?php echo $this->shop_link( $settings ); ?>
                </span>
            </span>
        </a>
    <?php endforeach; ?>
</div>
