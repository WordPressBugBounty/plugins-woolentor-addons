<?php
/**
 * Shop the Look — Modern / Variant 1: the floating card.
 *
 * Reference: design-reference/new_temlate/modern-style/v1/home-modern.html — `.lookbook-sec`.
 * One wide lifestyle photograph, rounded, full-bleed. No header and no product list: the image is
 * the whole widget, and an open pin's card floats over it.
 *
 * The card stacks: the product image on top at 4:3 and full card width (`.lk-pc-img`), then the
 * body under it — brand, title, star row, price with the original struck through, and a full-width
 * Add to Cart. Not a thumbnail beside a column of text.
 *
 * The reference shows one look, and with one look this renders exactly that — switcher_html()
 * returns nothing below two. A site that adds a second look gets a switcher rather than having its
 * second look silently dropped, which is the one thing worse than a small deviation.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->switcher_html( $looks, 'thumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>

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
                    <a class="wl-stl-card-media" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
                        <?php echo $this->card_media( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media(). ?>
                    </a>
                    <div class="wl-stl-card-body">
                        <?php echo $this->card_brand( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand(). ?>
                        <?php echo $this->card_title( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_title(). ?>
                        <?php echo $this->card_rating( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_rating(). ?>
                        <?php echo $this->card_price( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML. ?>
                        <?php echo $this->card_cart( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce button markup. ?>
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
