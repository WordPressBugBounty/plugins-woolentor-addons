<?php
/**
 * Shop the Look — Editorial / Variant 3: the room study.
 *
 * Adapted from `design-reference/new_temlate/editorial-style/v3/homepage.html` — `.room-study`.
 * That section has the layout, the copy and the product rows already drawn; the one thing it is
 * missing is **pins on the image**, which is the one thing this widget exists to add. Everything
 * else is transcribed: the image with its badge and its numbered caption, the panel with an
 * eyebrow, a heading, a line of copy and a quiet list, and a single *Shop the Room* link.
 *
 * The V3 role throughout: one look, no total, no bulk add. A pin opens the compact card, because
 * the detail already lives in the list beside it.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->switcher_html( $looks, 'tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>

<div class="wl-stl-stage">
    <?php foreach ( $looks as $position => $look ) : ?>
        <?php
        $my_pins = $pins[ $look['index'] ] ?? [];
        $alt     = $look['label'] ? $look['label'] : __( 'Shop the room', 'woolentor' );
        $eyebrow = trim( (string) ( $settings['eyebrow'] ?? '' ) );
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

                    <?php if ( '' !== $look['label'] ) : ?>
                        <span class="wl-stl-flag"><?php echo esc_html( $look['label'] ); ?></span>
                    <?php endif; ?>

                    <?php if ( '' !== $eyebrow ) : ?>
                        <span class="wl-stl-caption">
                            <?php
                            // "04 / Inside the Room" — the reference's own caption, built from the
                            // look's number and the section eyebrow rather than a control of its own.
                            printf(
                                /* translators: 1: look number, 2: section eyebrow */
                                esc_html__( '%1$s / %2$s', 'woolentor' ),
                                esc_html( str_pad( (string) $look['index'], 2, '0', STR_PAD_LEFT ) ),
                                esc_html( $eyebrow )
                            );
                            ?>
                        </span>
                    <?php endif; ?>

                    <?php foreach ( $my_pins as $pin ) : ?>
                        <?php echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, __( 'View Product', 'woolentor' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html(). ?>
                    <?php endforeach; ?>
                </div>

                <div class="wl-stl-panel">
                    <?php echo $this->header_html( $settings, $look ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>

                    <?php if ( $my_pins ) : ?>
                        <div class="wl-stl-list">
                            <?php foreach ( $my_pins as $pin ) : ?>
                                <?php echo $this->row_html( $pin, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside row_html(). ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php echo $this->cta_html( $settings, __( 'Shop the Room', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>
