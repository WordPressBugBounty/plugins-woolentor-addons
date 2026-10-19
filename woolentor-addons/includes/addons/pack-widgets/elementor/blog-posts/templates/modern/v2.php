<?php
/**
 * Blog Posts — Modern / Variant 2: three cards with a category badge and a byline.
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html().
echo $this->items_html( $posts, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside items_html().
