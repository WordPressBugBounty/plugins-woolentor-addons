<?php
/**
 * Shop the Look — Editorial / Variant 2: the full look, with a counter.
 *
 * Reference: design-reference/new_temlate/editorial-style/v2/homepage.html — `.look#lookbook`.
 * A section header with the eyebrow and headline on the left and a `01 / 03` counter with prev and
 * next arrows on the right — this pack steps through its looks rather than listing them. Below, a
 * split: the photograph left with a tag badge and **numbered** pins, the panel right.
 *
 * The panel's story changes per look: its own kicker, title and description, then the item rows,
 * then *The full look* with the summed total and **Add the Full Look**.
 *
 * Its pins are numerals rather than a plus, and a pin and its row light each other up on hover —
 * the image and the list behave as one control.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-stl-topbar">
    <?php
    // Section-level only: the per-look story belongs in the panel, beside the items it describes.
    echo $this->header_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
    echo $this->switcher_html( $looks, 'counter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html().
    ?>
</div>

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
                        <span class="wl-stl-flag"><?php echo esc_html( $look['label'] ); ?></span>
                    <?php endif; ?>

                    <?php foreach ( $my_pins as $index => $pin ) : ?>
                        <?php echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, __( 'View Product', 'woolentor' ) ), $index + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html(). ?>
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
                        <?php foreach ( $my_pins as $index => $pin ) : ?>
                            <?php echo $this->panel_row_html( $pin, $settings, $index + 1, [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside panel_row_html().
                                'index_in_thumb' => true,
                                'cart'           => 'text',
                                'meta'           => true,
                            ] ); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                $count = $this->count_html( $my_pins, $settings, __( 'The full look', 'woolentor' ) );
                $total = $this->total_html( $my_pins );
                ?>
                <?php if ( $count || $total ) : ?>
                    <div class="wl-stl-foot">
                        <span class="wl-stl-foot-meta">
                            <?php
                            // The label sits above the price here, not beside it — the reference
                            // stacks them and pushes the button to the far end of the row.
                            echo $count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_html().
                            echo $total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from wc_price().
                            ?>
                        </span>
                        <?php echo $this->bulk_html( $my_pins, $settings, __( 'Add the Full Look', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bulk_html(). ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>
