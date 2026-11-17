<?php
/**
 * Compare Table — Luxury / Variant 3: the quiet side-by-side.
 *
 * Transcribed from `design-reference/new_temlate/luxury-style/v3/homepage.html` — `.si--center`,
 * `table.compare-tbl`. Each column ends in a link to the product; the highlighted one is dark.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->table( $items, $rows, $settings, $style, $variant, '<span class="wl-ct-corner-text">' . esc_html__( 'Feature', 'woolentor' ) . '</span>' );
