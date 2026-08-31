<?php
/**
 * Product Showcase — Luxury / Variant 1 — one card.
 *
 * A bordered cream card with a square ivory image well. A pill badge sits top-left; wishlist and
 * compare drop in from above on the right. A dark strip runs along the foot of the image carrying
 * the product's attributes as scrolling chips, and on hover a scrim covers the image with a white
 * Quick View and a gold Add to Cart along its bottom. The body is generous: category, title, gold
 * stars with a review count, the price pair, then swatches.
 *
 * Reference: luxury-style/v1/home-luxury.html:6691 — `.product-card`
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_brand    = $this->card_brand( $product, $settings );
$wl_ps_rating   = $this->card_rating( $product, $settings );
$wl_ps_price    = $this->card_price( $product, $settings );
$wl_ps_swatches = $this->card_swatches( $product, $settings );
$wl_ps_icons    = $this->card_icon_actions( $product, $settings, [ 'wishlist', 'compare' ] );
$wl_ps_qview    = $this->card_icon_actions( $product, $settings, [ 'quickview' ] );
$wl_ps_cart     = $this->card_cart( $product, $settings );
$wl_ps_tags     = $this->card_tags( $product, $settings );
?>
<article class="wl-ps-card">
    <div class="wl-ps-thumb">
        <a class="wl-ps-media-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
            <?php echo $this->card_media( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
        </a>

        <?php echo $this->card_badges( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badges() ?>

        <?php if ( '' !== $wl_ps_icons ) : ?>
            <div class="wl-ps-iconcol">
                <?php echo $wl_ps_icons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
            </div>
        <?php endif; ?>

        <?php if ( '' !== $wl_ps_tags ) : ?>
            <div class="wl-ps-tagstrip" aria-hidden="true">
                <?php
                // Printed twice so the marquee has a second copy to scroll into — the animation
                // moves the track by exactly half its width, which only loops seamlessly if the
                // two halves are identical.
                echo '<div class="wl-ps-tagtrack">' . $wl_ps_tags . $wl_ps_tags . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_tags()
                ?>
            </div>
        <?php endif; ?>

        <?php if ( '' !== $wl_ps_qview || '' !== $wl_ps_cart ) : ?>
            <div class="wl-ps-actions">
                <?php echo $wl_ps_qview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
                <?php echo $wl_ps_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cart() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wl-ps-body">
        <?php echo $wl_ps_brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand() ?>

        <h3 class="wl-ps-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <?php echo $wl_ps_rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_rating() ?>

        <?php if ( '' !== $wl_ps_price ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
            </div>
        <?php endif; ?>

        <?php echo $wl_ps_swatches; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_swatches() ?>
    </div>
</article>
