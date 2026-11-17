<?php
/**
 * Compare Table — Modern / Variant 3: the quiet table.
 *
 * Designed in modern-style/v3's language from its product page's `.acc__specs`
 * (`03-detail.html`): off-white, soft grey rules, graphite values, clay accent.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->table( $items, $rows, $settings, $style, $variant );
