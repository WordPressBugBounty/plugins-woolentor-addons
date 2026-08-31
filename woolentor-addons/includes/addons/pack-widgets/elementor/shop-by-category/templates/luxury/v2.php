<?php
/**
 * Shop by Category — Luxury / Variant 2.
 *
 * A centred header over a stack of full-width rows on an olive-tinted ground. Each row sets the
 * category name huge and uppercase across the centre with its count raised beside it as a
 * superscript; the thumbnail slides in from the left and the "View Products" link slides in from
 * the right, both on hover, so the resting state is nothing but the names.
 *
 * Reference: design-reference/new_temlate/luxury-style/v2/homepage.html:4499 — `.catlist` / `.catrow`
 *
 * The plan listed this variant as needing a full-page scan; the section is there, named `cats`
 * with `id="categories"`, which is why a grep for "categor" on the class names missed it.
 *
 * The reference stacks three product thumbnails per row and floats a fourth preview under the
 * cursor. A category has exactly one thumbnail in WooCommerce, so this draws that one — the fan of
 * three would mean inventing images the taxonomy does not have.
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
            <span class="wl-sbc-thumbs">
                <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                    <span class="wl-sbc-media">
                        <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="" loading="lazy">
                    </span>
                <?php endif; ?>
            </span>

            <span class="wl-sbc-name">
                <?php echo esc_html( $wl_sbc_row['name'] ); ?>
                <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                <?php if ( '' !== $wl_sbc_count ) : ?>
                    <sup class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></sup>
                <?php endif; ?>
            </span>

            <?php if ( '' !== $header['card_button'] ) : ?>
                <span class="wl-sbc-btn">
                    <?php echo esc_html( $header['card_button'] ); ?>
                    <svg width="13" height="9" viewBox="0 0 14 10" fill="none" aria-hidden="true">
                        <path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                </span>
            <?php endif; ?>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
