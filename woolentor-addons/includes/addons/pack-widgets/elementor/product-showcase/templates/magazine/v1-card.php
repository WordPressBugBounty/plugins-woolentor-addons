<?php
/**
 * Product Showcase — Magazine / Variant 1 — one card.
 *
 * A bordered white card with a square image. Flags stack in the *top-right* — this is the one
 * variant that does not put them on the left — and a two-button bar rises from the foot of the
 * image on hover: quick view in white, add to cart in black. The body is a news column: brand,
 * a two-line clamped name, a yellow star row with the average and the count, then a dashed rule
 * above a row that pairs the price stack with the colour dots.
 *
 * Reference: magazine-style/v1/home-magazine.html:7360 — `.bs-card`
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
$wl_ps_qview    = $this->card_icon_actions( $product, $settings, [ 'quickview' ] );
$wl_ps_cart     = $this->card_cart( $product, $settings );
?>
<article class="wl-ps-card">
    <div class="wl-ps-thumb">
        <a class="wl-ps-media-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
            <?php echo $this->card_media( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
        </a>

        <?php echo $this->card_badges( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badges() ?>

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

        <?php if ( '' !== $wl_ps_price || '' !== $wl_ps_swatches ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
                <?php echo $wl_ps_swatches; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_swatches() ?>
            </div>
        <?php endif; ?>
    </div>
</article>
