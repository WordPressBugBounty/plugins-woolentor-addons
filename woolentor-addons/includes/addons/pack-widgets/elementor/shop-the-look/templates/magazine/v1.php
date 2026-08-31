<?php
/**
 * Shop the Look — Magazine / Variant 1: The Kit.
 *
 * Designed, not transcribed. Magazine V1's homepage has no lookbook of any kind — every keyword
 * returns zero — so this is written in the pack's own language instead. Spec: §3.2 of the plan.
 *
 * The language is taken from `magazine-style/v1/home-magazine.html`, from `.section-bundles` and
 * `.section-picks`: a red kicker over a condensed uppercase headline, a red flag on the image the
 * way the flash and deal sections badge theirs, tick-marked spec lines, `now` / `was` price pairs,
 * and a footer strip that counts the set and totals it. Magazine is the loud pack; the card closes
 * with a solid red Add to Cart rather than a text link.
 *
 * Its footer totals the look but does **not** add it in one action: bulk add belongs to the V2s,
 * all of which are Pro, and building it for a free variant would drag the bulk endpoint onto the
 * free path. So the footer's button is *Shop All Items*, not *Add the Kit*.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>
<?php echo $this->switcher_html( $looks, 'tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>

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

                <?php if ( '' !== $look['label'] ) : ?>
                    <span class="wl-stl-flag"><?php echo esc_html( $look['label'] ); ?></span>
                <?php endif; ?>

                <?php
                foreach ( $my_pins as $pin ) :
                    $product = $pin['product'];

                    ob_start();
                    ?>
                    <div class="wl-stl-card-inner">
                        <a class="wl-stl-card-media" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
                            <?php echo $this->card_media( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media(). ?>
                        </a>
                        <div class="wl-stl-card-body">
                            <?php echo $this->card_brand( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand(). ?>
                            <?php echo $this->card_title( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_title(). ?>
                            <?php echo $this->card_meta( $product, $settings, 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_meta(). ?>
                            <?php echo $this->card_price( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML. ?>
                        </div>
                    </div>
                    <?php echo $this->card_cart( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce button markup. ?>
                    <?php
                    $card = ob_get_clean();

                    echo $this->pin_html( $pin, $settings, $card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html().
                endforeach;
                ?>
            </div>

            <?php
            $count = $this->count_html( $my_pins, $settings, __( '{count} items in this look', 'woolentor' ) );
            $total = $this->total_html( $my_pins );
            $cta   = $this->cta_html( $settings, __( 'Shop All Items', 'woolentor' ) );
            ?>
            <?php if ( $count || $total || $cta ) : ?>
                <div class="wl-stl-foot">
                    <?php echo $count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_html(). ?>

                    <?php if ( $total ) : ?>
                        <span class="wl-stl-total-wrap">
                            <span class="wl-stl-total-label"><?php echo esc_html__( 'Look Price', 'woolentor' ); ?></span>
                            <?php echo $total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from wc_price(). ?>
                        </span>
                    <?php endif; ?>

                    <?php echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
