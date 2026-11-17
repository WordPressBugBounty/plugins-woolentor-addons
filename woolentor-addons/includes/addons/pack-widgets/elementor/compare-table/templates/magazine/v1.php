<?php
/**
 * Compare Table — Magazine / Variant 1: the buying guide on a dark panel.
 *
 * Transcribed from `design-reference/new_temlate/magazine-style/v1/home-magazine.html` —
 * `.compare-wrap`, `.compare-head`, `table.compare-table`.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-ct-panel">
    <?php echo $this->head( $settings, $style, $variant ) . $this->table( $items, $rows, $settings, $style, $variant ); ?>
</div>
