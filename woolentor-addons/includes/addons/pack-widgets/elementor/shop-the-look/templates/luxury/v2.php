<?php
/**
 * Shop the Look — Luxury / Variant 2: the curated set.
 *
 * Reference: design-reference/new_temlate/luxury-style/v2/homepage.html — `.look#lookbook`.
 * The most restrained of the four V2s. A split: the photograph left with plain **dots** — no glyph
 * at all — each opening a small tooltip carrying only the product image, its name and its price;
 * the panel right reads *Curated Edit / The Riviera Set*, then one row per product with a colour
 * line, an *Add to Bag* link under the name and the price pushed right.
 *
 * Its own detail, and the reason the pin and the row share a repeater id: `.look__row.is-dim` —
 * hovering a pin dims every row that is not its own. The image and the list are one control.
 *
 * Its footer is one button carrying the total inside its own label — *Add All to Bag — €1,060* —
 * which is why the bulk label takes a `{total}` placeholder rather than printing the price beside
 * the button the way Modern V2 does.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->switcher_html( $looks, 'tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>

<div class="wl-stl-split">

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

                    <?php foreach ( $my_pins as $pin ) : ?>
                        <?php
                        // No link inside the tooltip: this pack's pin says what a thing is and what
                        // it costs, and the row beside it is where a visitor acts.
                        echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html().
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wl-stl-panel">
        <?php foreach ( $looks as $position => $look ) : ?>
            <?php $my_pins = $pins[ $look['index'] ] ?? []; ?>
            <div class="wl-stl-look<?php echo 0 === $position ? ' is-active' : ''; ?>" data-wl-stl-look="<?php echo esc_attr( $look['index'] ); ?>">

                <?php echo $this->header_html( $settings, $look ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>

                <?php if ( $my_pins ) : ?>
                    <div class="wl-stl-items">
                        <?php foreach ( $my_pins as $pin ) : ?>
                            <?php echo $this->panel_row_html( $pin, $settings, 0, [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside panel_row_html().
                                'cart'         => 'text',
                                'meta'         => true,
                                'cart_in_body' => true,
                            ] ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php echo $this->bulk_html( $my_pins, $settings, __( 'Add All to Bag — {total}', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bulk_html(). ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>
