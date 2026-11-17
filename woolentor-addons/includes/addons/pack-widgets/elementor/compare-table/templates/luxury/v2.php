<?php
/**
 * Compare Table — Luxury / Variant 2: the sage ledger.
 *
 * Designed in luxury-style/v2's language from its product page's size guide
 * (`Product.html`, `.pdp-sizeguide table`): pale sage, dark olive, lime, square corners. The
 * highlighted column is set in dark olive.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->table( $items, $rows, $settings, $style, $variant );
