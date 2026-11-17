<?php
/**
 * Compare Table — Magazine / Variant 2: the aligned matrix, one column featured.
 *
 * Transcribed from `design-reference/new_temlate/magazine-style/v2/homepage.html` — `.sec-head`,
 * `.cmp` (`.ch`, `.cc`, `.cf`, `.feat`, `.cmp-flag`). The reference is a CSS grid of divs; this is
 * the same matrix as a real table, so each value is read with its label and its product.
 *
 * Available: $items, $rows, $settings, $style, $variant, and $this.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every helper escapes its own output.

$copy = $this->eyebrow( $settings ) . $this->heading( $settings );
$desc = $this->description( $settings );

if ( '' !== $copy || '' !== $desc ) : ?>
    <div class="wl-ct-head">
        <?php echo '' !== $copy ? '<div class="wl-ct-head-copy">' . $copy . '</div>' : ''; ?>
        <?php echo $desc; ?>
    </div>
<?php endif;

echo $this->table( $items, $rows, $settings, $style, $variant, $this->caption( $settings ) );
