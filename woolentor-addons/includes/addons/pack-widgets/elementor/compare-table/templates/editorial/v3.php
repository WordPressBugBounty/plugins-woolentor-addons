<?php
/**
 * Compare Table — Editorial / Variant 3: the specifications spread.
 *
 * Designed in editorial-style/v3's language from its product page's `.specs-section`
 * (`product-detail.html`): the copy held on the left, the table on the right, paper and clay.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.
?>
<div class="wl-ct-split">
    <?php echo $this->head( $settings, $style, $variant ); ?>
    <div class="wl-ct-split-body"><?php echo $this->table( $items, $rows, $settings, $style, $variant ); ?></div>
</div>
