<?php
/**
 * Product Showcase — Luxury / Variant 2 — one card.
 *
 * A 3:4 image on a pale olive surface, no card frame. A square badge sits top-left; wishlist,
 * compare and quick view stack top-right and slide in from the right on hover, while an olive
 * "Add to Bag" bar lifts inside the bottom edge. The body puts the swatches *first* — the
 * reference reads colour before name — then category, name, and a price/rating row.
 *
 * Reference: luxury-style/v2/homepage.html:4552 — `.pc`
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_brand    = $this->card_brand( $product, $settings );
$wl_ps_price    = $this->card_price( $product, $settings );
$wl_ps_rating   = $this->card_rating( $product, $settings );
$wl_ps_swatches = $this->card_swatches( $product, $settings );
$wl_ps_icons    = $this->card_icon_actions( $product, $settings, [ 'wishlist', 'compare', 'quickview' ] );
$wl_ps_cart     = $this->card_cart( $product, $settings );
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

        <?php if ( '' !== $wl_ps_cart ) : ?>
            <div class="wl-ps-actions">
                <?php echo $wl_ps_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cart() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wl-ps-body">
        <?php echo $wl_ps_swatches; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_swatches() ?>

        <?php echo $wl_ps_brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand() ?>

        <h3 class="wl-ps-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <?php if ( '' !== $wl_ps_price || '' !== $wl_ps_rating ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
                <?php echo $wl_ps_rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_rating() ?>
            </div>
        <?php endif; ?>
    </div>
</article>
