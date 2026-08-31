<?php
/**
 * Shop the Look — Editorial / Variant 1: the scenes.
 *
 * Reference: design-reference/new_temlate/editorial-style/v1/home-editorial.html — `.section-inspo`.
 * The richest of the seven references and the only free one with more than one look: three scenes —
 * Living room, Gaming room, Kitchen room — cross-fading inside one framed stage, with the tab strip
 * **below** the image rather than above it. No header of any kind; the section opens straight onto
 * the scene.
 *
 * Its card is a different shape from Modern V1's: the product image sits on top, full width, and the
 * body underneath carries the name, the price and a pill *View Product* link — not an Add to Cart.
 *
 * `.inspo-pin.card-left` appears five times in the reference, which is why the edge flip is a
 * requirement rather than a preference. A row set to Auto is flipped by the script; a row that
 * chose a side keeps it.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-stl-stage">
    <?php foreach ( $looks as $position => $look ) : ?>
        <?php
        $my_pins = $pins[ $look['index'] ] ?? [];
        $alt     = $look['label'] ? $look['label'] : __( 'Shop the look', 'woolentor' );
        ?>
        <div class="wl-stl-look<?php echo 0 === $position ? ' is-active' : ''; ?>" data-wl-stl-look="<?php echo esc_attr( $look['index'] ); ?>">
            <div class="wl-stl-image">
                <?php if ( $look['id'] ) : ?>
                    <?php echo wp_get_attachment_image( $look['id'], 'full', false, [
                        'class'   => 'wl-stl-photo',
                        'alt'     => $alt,
                        'loading' => 'lazy',
                    ] ); ?>
                <?php elseif ( $look['url'] ) : ?>
                    <img class="wl-stl-photo" src="<?php echo esc_url( $look['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
                <?php endif; ?>

                <?php
                foreach ( $my_pins as $pin ) :
                    $product = $pin['product'];

                    ob_start();
                    ?>
                    <span class="wl-stl-card-media">
                        <?php echo $this->card_media( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media(). ?>
                    </span>
                    <div class="wl-stl-card-body">
                        <?php echo $this->card_title( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_title(). ?>
                        <?php echo $this->card_price( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML. ?>
                        <?php echo $this->card_link( $product, $settings, __( 'View Product', 'woolentor' ), false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_link(). ?>
                    </div>
                    <?php
                    $card = ob_get_clean();

                    echo $this->pin_html( $pin, $settings, $card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html().
                endforeach;
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php echo $this->switcher_html( $looks, 'tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>
