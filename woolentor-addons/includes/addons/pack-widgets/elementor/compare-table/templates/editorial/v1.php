<?php
/**
 * Compare Table — Editorial / Variant 1: the spec sheet.
 *
 * Designed in editorial-style/v1's language from its product page's `table.pd-specs`
 * (`product-editorial.html`): cream, sand hairlines, a serif name under a small category line.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->table( $items, $rows, $settings, $style, $variant );
