<?php
/**
 * Compare Table — Magazine / Variant 3: the printed spec cards.
 *
 * Designed in magazine-style/v3's language from its product page's `.pdp-highlights`
 * (`03-magazine-product-detail.html`): warm paper, espresso, terracotta, 2px corners, each spec
 * a small uppercase key over a large value. The highlighted card is printed in espresso.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->cards( $items, $rows, $settings, $style, $variant );
