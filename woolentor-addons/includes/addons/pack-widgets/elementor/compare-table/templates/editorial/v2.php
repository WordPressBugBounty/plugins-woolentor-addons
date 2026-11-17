<?php
/**
 * Compare Table — Editorial / Variant 2: the size-guide table.
 *
 * Designed in editorial-style/v2's language from its product page's `table.sg-table`
 * (`product.html`): a huge uppercase title, a small-caps label column, sky-blue accent.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

echo $this->head( $settings, $style, $variant );
echo $this->table( $items, $rows, $settings, $style, $variant );
