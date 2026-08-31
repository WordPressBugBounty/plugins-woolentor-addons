<?php
/**
 * Product Showcase — Modern / Variant 3 — one card.
 *
 * A bordered white card with a 4:5 image. On hover a dark translucent bar rises across the foot of
 * the image carrying a pale Add to Cart button and three square icon buttons — quick view, compare
 * and wishlist, in that order. The body reads category, name, attribute line, then price on the
 * left and the star rating on the right.
 *
 * Reference: modern-style/v3/01-homepage.html:6829 — `.product-card`
 *
 * @var \WC_Product $product   The current product — set up by the caller's loop.
 * @var array       $settings  Raw widget settings — read only through $this-> helpers.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_brand  = $this->card_brand( $product, $settings );
$wl_ps_meta   = $this->card_meta( $product, $settings );
$wl_ps_price  = $this->card_price( $product, $settings );
$wl_ps_rating = $this->card_rating( $product, $settings );
$wl_ps_icons  = $this->card_icon_actions( $product, $settings, [ 'quickview', 'compare', 'wishlist' ] );
$wl_ps_cart   = $this->card_cart( $product, $settings );
?>
<article class="wl-ps-card">
    <div class="wl-ps-thumb">
        <a class="wl-ps-media-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
            <?php echo $this->card_media( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
        </a>

        <?php echo $this->card_badges( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badges() ?>

        <?php if ( '' !== $wl_ps_cart || '' !== $wl_ps_icons ) : ?>
            <div class="wl-ps-actions">
                <?php echo $wl_ps_cart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cart() ?>

                <?php if ( '' !== $wl_ps_icons ) : ?>
                    <div class="wl-ps-iconrow">
                        <?php echo $wl_ps_icons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_icon_actions() ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="wl-ps-body">
        <?php echo $wl_ps_brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand() ?>

        <h3 class="wl-ps-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <?php echo $wl_ps_meta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_meta() ?>

        <?php if ( '' !== $wl_ps_price || '' !== $wl_ps_rating ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
                <?php echo $wl_ps_rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_rating() ?>
            </div>
        <?php endif; ?>
    </div>
</article>
