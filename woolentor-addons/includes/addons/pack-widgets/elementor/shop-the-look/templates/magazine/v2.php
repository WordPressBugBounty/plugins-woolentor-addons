<?php
/**
 * Shop the Look — Magazine / Variant 2: editor-styled, tabs across the top.
 *
 * Reference: design-reference/new_temlate/magazine-style/v2/homepage.html — `#shop-the-look`.
 * Its markup is nearly empty in the source because the whole section is built in JavaScript from a
 * `looks` array — which is why the first reference scan missed it. What it builds: a header with an
 * editor byline on the left and **numbered look tabs** on the right, then the photograph with `+`
 * hotspots and a hover popover, and a list of cards beside it.
 *
 * Here the looks are rendered server-side and the tabs are the shared switcher, so nothing is
 * assembled in the browser and the section is in the page for a crawler to read.
 *
 * Available: $looks, $pins, $settings, $style, $variant, and $this (the widget, for card pieces).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wl-stl-topbar">
    <?php
    echo $this->header_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
    echo $this->switcher_html( $looks, 'tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html().
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
                        <span class="wl-stl-flag">
                            <?php
                            printf(
                                /* translators: 1: look number, 2: look label */
                                esc_html__( 'Look %1$s · %2$s', 'woolentor' ),
                                esc_html( str_pad( (string) $look['index'], 2, '0', STR_PAD_LEFT ) ),
                                esc_html( $look['label'] )
                            );
                            ?>
                        </span>
                    <?php endif; ?>

                    <?php foreach ( $my_pins as $pin ) : ?>
                        <?php echo $this->pin_html( $pin, $settings, $this->card_compact( $pin, $settings, '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside pin_html(). ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wl-stl-panel">
        <?php foreach ( $looks as $position => $look ) : ?>
            <?php $my_pins = $pins[ $look['index'] ] ?? []; ?>
            <div class="wl-stl-look<?php echo 0 === $position ? ' is-active' : ''; ?>" data-wl-stl-look="<?php echo esc_attr( $look['index'] ); ?>">

                <?php if ( $my_pins ) : ?>
                    <div class="wl-stl-items">
                        <?php foreach ( $my_pins as $pin ) : ?>
                            <?php echo $this->panel_row_html( $pin, $settings, 0, [ 'cart' => 'icon' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside panel_row_html(). ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                $count = $this->count_html( $my_pins, $settings, __( 'Complete look · {count} items', 'woolentor' ) );
                $total = $this->total_html( $my_pins );
                ?>
                <?php if ( $count || $total ) : ?>
                    <div class="wl-stl-foot">
                        <span class="wl-stl-foot-meta"><?php echo $count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_html(). ?></span>
                        <?php echo $total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from wc_price(). ?>
                    </div>
                <?php endif; ?>

                <?php echo $this->bulk_html( $my_pins, $settings, __( 'Add All', 'woolentor' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bulk_html(). ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>
