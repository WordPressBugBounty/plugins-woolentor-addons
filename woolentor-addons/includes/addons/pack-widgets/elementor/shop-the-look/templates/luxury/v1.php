<?php
/**
 * Shop the Look — Luxury / Variant 1: The Composition.
 *
 * Designed, not transcribed. Luxury V1's homepage carries a `.section-lookbook` CSS rule and a
 * "10b. LOOKBOOK" comment but no markup at all — the designer started the section and dropped it —
 * so this is written in the pack's own language instead. Spec: §3.2 of the widget plan.
 *
 * The language is taken from `luxury-style/v1/home-luxury.html`: the eyebrow is a gold hairline
 * followed by letterspaced uppercase text (`.section-lifestyle`), items carry an index numeral
 * (`.lifestyle-tabs`), and the pack frames things with a gold hairline rather than dropping a
 * shadow behind them.
 *
 * The one thing most likely to drift: **the card closes with a gold text link, not a filled
 * button.** No section in Luxury V1 uses a filled button at card scale.
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

                <?php
                foreach ( $my_pins as $index => $pin ) :
                    $product = $pin['product'];

                    ob_start();
                    ?>
                    <span class="wl-stl-card-index"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
                    <div class="wl-stl-card-inner">
                        <a class="wl-stl-card-media" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
                            <?php echo $this->card_media( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media(). ?>
                        </a>
                        <div class="wl-stl-card-body">
                            <?php echo $this->card_brand( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_brand(). ?>
                            <?php echo $this->card_title( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_title(). ?>
                            <?php echo $this->card_price( $product, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce price HTML. ?>
                        </div>
                    </div>
                    <div class="wl-stl-card-foot">
                        <?php echo $this->card_link( $product, $settings, __( 'Add to Bag', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_link(). ?>
                    </div>
                    <?php
                    $card = ob_get_clean();

                    echo $this->pin_html( $pin, $settings, $card ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html().
                endforeach;
                ?>
            </div>

            <?php
            $count = $this->count_html( $my_pins, $settings, __( '{count} items in this composition', 'woolentor' ) );
            $cta   = $this->cta_html( $settings, __( 'Shop the Composition', 'woolentor' ) );
            ?>
            <?php if ( $count || $cta ) : ?>
                <div class="wl-stl-foot">
                    <?php echo $count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_html(). ?>
                    <?php echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
