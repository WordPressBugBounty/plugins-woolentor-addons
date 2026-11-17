<?php
/**
 * Compare Table — Modern / Variant 1: one card per product inside one rounded frame.
 *
 * Transcribed from `design-reference/new_temlate/modern-style/v1/home-modern.html` — `.sec-head`,
 * `.compare-wrap`, `.compare-grid`, `.cmp-col`.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->cards( $items, $rows, $settings, $style, $variant );
