<?php
/**
 * Shop the Look — Modern / Variant 3: the room list.
 *
 * Reference: design-reference/new_temlate/modern-style/v3/01-homepage.html — `.stl.section#shop-room`.
 * A centred header, then a two-column body: the photograph left with `+` pins, bordered product rows
 * right, and one full-width outbound link below.
 *
 * This is the V3 role: one look, a quiet list, no total and no bulk add. The pin opens a compact
 * card — a thumbnail, the name, the price and a Quick Shop link — because the detail already lives
 * in the list beside it, and nothing in V3 should compete with the list.
 *
 * The reference shows one look, and switcher_html() draws nothing below two, so a single-look
 * section renders exactly it.
 *
 * The list sits inside a `.wl-stl-panel` like every other split layout's does, even though this
 * pack paints no panel: uniform structure is what lets one Panel style control serve all of them
 * instead of being offered here and doing nothing.
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
            <div class="wl-stl-split">

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
                        <?php echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, __( 'Quick Shop', 'woolentor' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html(). ?>
                    <?php endforeach; ?>
                </div>

                <?php if ( $my_pins ) : ?>
                    <div class="wl-stl-panel">
                        <div class="wl-stl-list">
                            <?php foreach ( $my_pins as $pin ) : ?>
                                <?php echo $this->row_html( $pin, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside row_html(). ?>
                            <?php endforeach; ?>
                        </div>

                        <?php echo $this->cta_html( $settings, __( 'Shop the Full Room', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>
</div>
