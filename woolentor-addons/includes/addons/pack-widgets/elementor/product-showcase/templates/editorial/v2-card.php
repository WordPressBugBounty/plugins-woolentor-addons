<?php
/**
 * Product Showcase — Editorial / Variant 2 — one card.
 *
 * A 3:4 image on a cool surface with rounded corners. A pill badge stacks top-left; the wishlist
 * is a glass circle top-right with compare below it, both always visible. On hover an action row
 * lifts from inside the bottom edge: a dark "Add to Bag" bar with a square quick-view button
 * beside it. Below the image, category and name sit on the left with the price pushed right, and
 * the swatches take their own row underneath.
 *
 * Reference: editorial-style/v2/homepage.html:4858 — `.product`
 *
 * This is the one reference in the set written against real WooCommerce class names, which is why
 * the title keeps `woocommerce-loop-product__title` — a theme styling the shop loop will reach
 * this card too.
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_brand    = $this->card_brand( $product, $settings );
$wl_ps_price    = $this->card_price( $product, $settings );
$wl_ps_swatches = $this->card_swatches( $product, $settings );
$wl_ps_icons    = $this->card_icon_actions( $product, $settings, [ 'wishlist', 'compare' ] );
$wl_ps_cart     = $this->card_cart( $product, $settings );
$wl_ps_qview    = $this->card_icon_actions( $product, $settings, [ 'quickview' ] );
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

        <?php if ( '' !== $wl_ps_cart || '' !== $wl_ps_qview ) : ?>
            <div class="wl-ps-actions">
                <?php echo $wl_ps_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cart() ?>
                <?php echo $wl_ps_qview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wl-ps-body">
        <div class="wl-ps-info">
            <div class="wl-ps-info-main">
                <?php echo $wl_ps_brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand() ?>

                <h3 class="wl-ps-title woocommerce-loop-product__title">
                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                </h3>
            </div>

            <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
        </div>

        <?php echo $wl_ps_swatches; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_swatches() ?>
    </div>
</article>
