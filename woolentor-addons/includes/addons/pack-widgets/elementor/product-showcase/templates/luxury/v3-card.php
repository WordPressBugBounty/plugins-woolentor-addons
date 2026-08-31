<?php
/**
 * Product Showcase — Luxury / Variant 3 — one card.
 *
 * A 4:5 image on a neutral ground, no frame at all. A gold badge sits top-left. This is the one
 * card in the set that splits its actions across two corners: quick view and compare stack
 * top-right and slide in from the side, while a charcoal bar rises from the bottom carrying Add to
 * Cart with the wishlist as a square button beside it. Below: category, name, price.
 *
 * Reference: luxury-style/v3/homepage.html:4476 — `.product-card`
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_brand = $this->card_brand( $product, $settings );
$wl_ps_price = $this->card_price( $product, $settings );
$wl_ps_top   = $this->card_icon_actions( $product, $settings, [ 'quickview', 'compare' ] );
$wl_ps_cart  = $this->card_cart( $product, $settings );
$wl_ps_wish  = $this->card_icon_actions( $product, $settings, [ 'wishlist' ] );
?>
<article class="wl-ps-card">
    <div class="wl-ps-thumb">
        <a class="wl-ps-media-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
            <?php echo $this->card_media( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
        </a>

        <?php echo $this->card_badges( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badges() ?>

        <?php if ( '' !== $wl_ps_top ) : ?>
            <div class="wl-ps-iconcol">
                <?php echo $wl_ps_top; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
            </div>
        <?php endif; ?>

        <?php if ( '' !== $wl_ps_cart || '' !== $wl_ps_wish ) : ?>
            <div class="wl-ps-actions">
                <?php echo $wl_ps_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cart() ?>
                <?php echo $wl_ps_wish; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wl-ps-body">
        <?php echo $wl_ps_brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand() ?>

        <h3 class="wl-ps-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <?php if ( '' !== $wl_ps_price ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
            </div>
        <?php endif; ?>
    </div>
</article>
