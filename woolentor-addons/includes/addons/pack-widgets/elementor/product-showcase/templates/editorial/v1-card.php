<?php
/**
 * Product Showcase — Editorial / Variant 1 — one card.
 *
 * Square image on sand with a small square badge top-left. Wishlist and compare sit in a column
 * top-right and slide in from off the edge on hover; a split bar rises from the foot of the image
 * with Quick View on the left and Add to Cart on the right. Below: a clay category label, the
 * name, an italic tagline that stretches to keep the price row level across the row, then price
 * and rating on one line.
 *
 * Reference: editorial-style/v1/home-editorial.html:6109 — `.feat-card`
 *
 * The reference's action bar is injected by script rather than written into the page, which is why
 * a static read of the markup shows no cart button. It is part of the design.
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_brand   = $this->card_brand( $product, $settings );
$wl_ps_tagline = $this->card_tagline( $product, $settings );
$wl_ps_price   = $this->card_price( $product, $settings );
$wl_ps_rating  = $this->card_rating( $product, $settings );
$wl_ps_icons   = $this->card_icon_actions( $product, $settings, [ 'wishlist', 'compare' ] );
$wl_ps_qview   = $this->card_icon_actions( $product, $settings, [ 'quickview' ] );
$wl_ps_cart    = $this->card_cart( $product, $settings );
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

        <?php echo $wl_ps_tagline; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_tagline() ?>

        <?php if ( '' !== $wl_ps_price || '' !== $wl_ps_rating ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
                <?php echo $wl_ps_rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_rating() ?>
            </div>
        <?php endif; ?>
    </div>
</article>
