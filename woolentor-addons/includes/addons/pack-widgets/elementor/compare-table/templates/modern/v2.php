<?php
/**
 * Compare Table — Modern / Variant 2: separate cards on paper.
 *
 * Designed in modern-style/v2's language: paper, olive and rust, rounded cards, and the spec
 * line of its product page (`.pdp-spec-row`, `Product.html`). The highlighted card takes an olive
 * border and the olive button.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->cards( $items, $rows, $settings, $style, $variant );
