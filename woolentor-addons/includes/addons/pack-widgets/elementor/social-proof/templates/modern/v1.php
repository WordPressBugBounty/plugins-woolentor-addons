<?php
/**
 * Social Proof — Modern / Variant 1: the scrolling wall.
 *
 * The V1 role: every review at once, no navigation.
 *
 * Available: $items, $settings, $style, $variant, and $this (the widget, for the helpers).
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$columns = $this->columns( $items, 3 );
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>
<?php echo $this->summary_html( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside summary_html(). ?>

<div class="wl-sp-wall is-marquee">
    <?php foreach ( $columns as $index => $column ) : ?>
        <div class="wl-sp-col<?php echo 1 === $index ? ' is-reverse' : ''; ?>">
            <?php foreach ( $column as $position => $item ) : ?>
                <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
            <?php endforeach; ?>

            <?php
            // The second pass is what makes the -50% slide seamless. It is the same markup with
            // no accessible presence, so nothing is announced twice.
            ?>
            <div class="wl-sp-col-copy" aria-hidden="true">
                <?php foreach ( $column as $position => $item ) : ?>
                    <?php echo $this->item_html( $item, $settings, $style, $variant, $position ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_html(). ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
