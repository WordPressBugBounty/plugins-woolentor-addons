<?php
/**
 * Blog Posts — Luxury / Variant 2: three square cards with a button.
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
echo $this->items_html( $posts, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside items_html().
