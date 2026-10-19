<?php
/**
 * Blog Posts — Magazine / Variant 3: three articles and a centred button.
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
echo $this->items_html( $posts, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside items_html().
echo $this->cta_html( $settings, 'foot' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html().
