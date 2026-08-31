<?php
namespace WooLentor\Product_Kit;

/**
 * What a product section has to be able to do for the endpoint to serve it.
 *
 * An Elementor widget implements this on itself; a block registers a plain callable with the same
 * signature. Either way the endpoint never learns how a card is drawn — it resolves who owns the
 * section, hands over the settings, and packages what comes back.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

interface Renders_Section {

    /**
     * Render the card list for one tab and one page.
     *
     * The settings are the ones the server read back from the saved post — never anything the
     * browser sent — so an implementation may trust them exactly as much as it trusts a page load.
     *
     * @param  array $settings
     * @param  array $context {
     *     @type int    $tab         Tab index, or -1 when the section has no tab row.
     *     @type int    $paged       Page to render, from 1.
     *     @type int    $post_id     Post the section was saved on.
     *     @type string $section_id  Element or block id.
     * }
     * @return array {
     *     @type string $html        The cards. Empty is a legitimate answer.
     *     @type string $pagination  The pager that belongs with them, or an empty string.
     * }
     */
    public function render_section( array $settings, array $context );
}
