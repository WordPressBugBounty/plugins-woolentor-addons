<?php
/**
 * Shop the Look — Modern / Variant 2: the look switcher.
 *
 * Reference: design-reference/new_temlate/modern-style/v2/homepage.html — `.stl#lb`, the largest of
 * the seven references. Two columns edge to edge, 58% / 42%: the photograph left with a look-label
 * badge and plain white `+` pins, a dark panel right.
 *
 * The panel carries the eyebrow, a two-line headline, a description, a row of labelled look
 * thumbnails, then one row per product — index, thumbnail, category, name, price, colour swatches
 * and a bag button — and closes on the complete-look total and a single Add to Cart for all of it.
 *
 * This is the V2 role: several looks, full rows in a panel, a displayed total and a bulk add.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
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

                    <?php if ( '' !== $look['label'] ) : ?>
                        <span class="wl-stl-flag">
                            <span class="wl-stl-flag-dot" aria-hidden="true"></span>
                            <?php echo esc_html( $look['label'] ); ?>
                        </span>
                    <?php endif; ?>

                    <?php foreach ( $my_pins as $pin ) : ?>
                        <?php echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, __( 'Add to Bag', 'woolentor' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html(). ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wl-stl-panel">
        <?php echo $this->header_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>
        <?php echo $this->switcher_html( $looks, 'thumbs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>

        <?php foreach ( $looks as $position => $look ) : ?>
            <?php $my_pins = $pins[ $look['index'] ] ?? []; ?>
            <div class="wl-stl-look<?php echo 0 === $position ? ' is-active' : ''; ?>" data-wl-stl-look="<?php echo esc_attr( $look['index'] ); ?>">

                <?php if ( $my_pins ) : ?>
                    <div class="wl-stl-items">
                        <?php foreach ( $my_pins as $index => $pin ) : ?>
                            <?php echo $this->panel_row_html( $pin, $settings, $index + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside panel_row_html(). ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                $count = $this->count_html( $my_pins, $settings, __( 'Complete Look · {count} items', 'woolentor' ) );
                $total = $this->total_html( $my_pins );
                $note  = trim( (string) ( $settings['ship_note'] ?? '' ) );
                ?>
                <?php if ( $count || $total ) : ?>
                    <div class="wl-stl-foot">
                        <span class="wl-stl-foot-meta">
                            <?php echo $count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_html(). ?>
                            <?php if ( '' !== $note ) : ?>
                                <span class="wl-stl-note"><?php echo esc_html( $note ); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php echo $total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from wc_price(). ?>
                    </div>
                <?php endif; ?>

                <?php echo $this->bulk_html( $my_pins, $settings, __( 'Add Complete Look to Cart', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bulk_html(). ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>
