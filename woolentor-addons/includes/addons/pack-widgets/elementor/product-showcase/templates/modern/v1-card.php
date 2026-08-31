<?php
/**
 * Product Showcase — Modern / Variant 1 — one card.
 *
 * Split out of v1.php so the tab handler can re-render the card list on its own and produce
 * byte-identical markup to the first page load. The section template includes this once per
 * product; the AJAX endpoint includes it in its own loop. Neither owns a second copy of the card.
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
$wl_ps_cart     = $this->card_cart( $product, $settings, '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>' );
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
        <?php if ( '' !== $wl_ps_brand || '' !== $wl_ps_rating ) : ?>
            <div class="wl-ps-meta">
                <?php echo $wl_ps_brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand() ?>
                <?php echo $wl_ps_rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_rating() ?>
            </div>
        <?php endif; ?>

        <h3 class="wl-ps-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h3>

        <?php if ( '' !== $wl_ps_price || '' !== $wl_ps_swatches ) : ?>
            <div class="wl-ps-foot">
                <?php echo $wl_ps_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML ?>
                <?php echo $wl_ps_swatches; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_swatches() ?>
            </div>
        <?php endif; ?>
    </div>
</article>
