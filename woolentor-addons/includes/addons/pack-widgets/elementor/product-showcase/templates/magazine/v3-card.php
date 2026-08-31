<?php
/**
 * Product Showcase — Magazine / Variant 3 — one card.
 *
 * The leanest card in the set, and deliberately so: a square image on linen with a rounded frame,
 * a single centred "Quick view" pill that fades up on hover, and a centred body of name, price and
 * swatches. No wishlist, no compare, no add-to-cart, no rating, and no category line — the
 * reference has the category in its markup but hides it, so it is not rendered here either.
 *
 * Reference: magazine-style/v3/homepage.html:4967 — `.product-card`
 *
 * This variant is the reason every card part is an independent switch rather than a variant flag.
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_price    = $this->card_price( $product, $settings );
$wl_ps_swatches = $this->card_swatches( $product, $settings );
$wl_ps_qview    = $this->card_icon_actions( $product, $settings, [ 'quickview' ] );
?>
<article class="wl-ps-card">
    <div class="wl-ps-thumb">
        <a class="wl-ps-media-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
            <?php echo $this->card_media( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
        </a>

        <?php echo $this->card_badges( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badges() ?>

        <?php if ( '' !== $wl_ps_qview ) : ?>
            <div class="wl-ps-actions">
                <?php echo $wl_ps_qview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wl-ps-body">
        <h3 class="wl-ps-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <?php if ( '' !== $wl_ps_price ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
            </div>
        <?php endif; ?>

        <?php echo $wl_ps_swatches; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_swatches() ?>
    </div>
</article>
