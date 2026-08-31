<?php
/**
 * Shop by Category — Magazine / Variant 3.
 *
 * A header with the eyebrow over the headline, then a horizontal rail of tall 4:5 cards that snap
 * as it scrolls. Each card sets the category name in the display serif with its count raised
 * beside it, and the photograph pushes in slightly on hover.
 *
 * Reference: design-reference/new_temlate/magazine-style/v3/homepage.html:4792 — `.cats__track` / `.catx`
 *
 * The reference splits the rail into Living / Bedroom tabs. That grouping is editorial — nothing in
 * `product_cat` says which room a category belongs to — so the tabs are dropped and the rail shows
 * one set. A store that wants two groups places two widgets, each with its own selection.
 *
 * The rail is a native scroll-snap track rather than a scripted carousel, which is what the
 * reference uses; no JavaScript is involved in this variant.
 *
 * The outer .wl-sbc wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $rows      Category rows: id, name, url, count, image, icon, desc, children.
 * @var array $header    eyebrow, headline, description, card_button, view_all.
 * @var array $settings  Raw widget settings — read only through $this->count_text().
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] ) : ?>
    <div class="wl-sbc-head">
        <?php if ( '' !== $header['eyebrow'] ) : ?>
            <span class="wl-sbc-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
        <?php endif; ?>

        <?php if ( '' !== $header['headline'] ) : ?>
            <h2 class="wl-sbc-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="wl-sbc-grid">
    <?php foreach ( $rows as $wl_sbc_row ) : ?>
        <?php echo $this->card_open( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                <span class="wl-sbc-media">
                    <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="<?php echo esc_attr( $wl_sbc_row['name'] ); ?>" loading="lazy">
                </span>
            <?php endif; ?>

            <span class="wl-sbc-label">
                <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>
                <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                <?php if ( '' !== $wl_sbc_count ) : ?>
                    <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                <?php endif; ?>
            </span>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
