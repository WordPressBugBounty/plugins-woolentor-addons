<?php
/**
 * Blog Posts — Magazine / Variant 2: the numbered index.
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
echo $this->items_html( $posts, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside items_html().
