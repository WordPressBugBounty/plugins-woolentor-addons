<?php
/**
 * Shop the Look — Magazine / Variant 3: the room study, in Magazine's third language.
 *
 * Designed, not transcribed. Magazine V3's homepage carries `room-transform` — a before/after image
 * slider with room tabs — which shares a word with this widget and nothing else: it has no pins,
 * and its subject is a transformation rather than a shoppable image. So this is written in the
 * pack's own language instead. Spec: §3.2 of the widget plan.
 *
 * That language is a completely different one from Magazine V1's: warm paper and a Fraunces serif
 * with a terracotta accent, every corner at 2px, and no shadows anywhere. Idiom from
 * `.sec-head--row`: a small terracotta label above a serif heading.
 *
 * Built to the V3 role — one look, a quiet list, one outbound link, no total and no bulk add — so
 * the three V3s behave identically and only look different.
 *
 * **One deviation from §3.2, deliberate.** That spec also put a *View all rooms* text link at the
 * top right. It is dropped: it would need a control of its own for one variant, and it would point
 * a second link at the same place as the footer button in a section this short. The single
 * outbound action is the footer, which is what the V3 role asks for.
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
        $alt     = $look['label'] ? $look['label'] : __( 'Shop the room', 'woolentor' );
        $eyebrow = trim( (string) ( $settings['eyebrow'] ?? '' ) );
        ?>
        <div class="wl-stl-look<?php echo 0 === $position ? ' is-active' : ''; ?>" data-wl-stl-look="<?php echo esc_attr( $look['index'] ); ?>">
            <div class="wl-stl-split">

                <?php
                // The plate wraps the photograph and its printed caption together. The caption
                // sits *under* the picture in this pack rather than on it — Magazine V3 carries
                // no shadows, and a caption laid over a photograph needs one to stay legible —
                // so it has to be outside the ratio box, and the two need a shared grid item
                // or the caption becomes a third column.
                ?>
                <div class="wl-stl-plate">

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

                    <?php foreach ( $my_pins as $pin ) : ?>
                        <?php
                        // A tooltip, not a card: the full detail is in the list at right, and
                        // nothing in V3 should compete with the list.
                        echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html().
                        ?>
                    <?php endforeach; ?>
                </div>

                    <?php if ( '' !== $eyebrow ) : ?>
                        <span class="wl-stl-caption">
                            <?php
                            // The same caption the other two V3s carry, so the three read as one
                            // family across the packs.
                            printf(
                                /* translators: 1: look number, 2: section eyebrow */
                                esc_html__( '%1$s / %2$s', 'woolentor' ),
                                esc_html( str_pad( (string) $look['index'], 2, '0', STR_PAD_LEFT ) ),
                                esc_html( $eyebrow )
                            );
                            ?>
                        </span>
                    <?php endif; ?>

                </div>

                <div class="wl-stl-panel">
                    <?php if ( $my_pins ) : ?>
                        <div class="wl-stl-list">
                            <?php foreach ( $my_pins as $index => $pin ) : ?>
                                <?php echo $this->row_html( $pin, $settings, $index + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside row_html(). ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php echo $this->cta_html( $settings, __( 'Shop the Full Room', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>
