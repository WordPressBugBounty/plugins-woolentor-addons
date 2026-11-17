<?php
/**
 * Compare Table — Luxury / Variant 1: the gold ledger.
 *
 * Designed in luxury-style/v1's language from its spotlight's `table.sp-acc-table`
 * (`home-luxury.html`): obsidian, gold hairlines, quiet labels.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->table( $items, $rows, $settings, $style, $variant );
