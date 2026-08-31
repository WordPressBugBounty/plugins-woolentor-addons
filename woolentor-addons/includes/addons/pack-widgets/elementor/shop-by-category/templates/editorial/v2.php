<?php
/**
 * Shop by Category — Editorial / Variant 2.
 *
 * A full-bleed band of equal vertical panels, each one a photograph under a dark wash. The copy
 * sits centred in the panel — a numbered collection label, the category name in serif, and the
 * category's own description — and lifts on hover to make room for a white circular arrow that
 * fades up beneath it.
 *
 * Reference: design-reference/new_temlate/editorial-style/v2/homepage.html:5151 — `.showcase` / `.sc-panel`
 *
 * The plan listed this variant as needing a full-page scan; the section is there, named `showcase`
 * with `id="categories"`, which is why a grep for "categor" on the class names missed it.
 *
 * This variant draws no section header — the reference band runs edge to edge with no heading
 * above it. The Eyebrow control is not wasted: it becomes the prefix of each panel's numbered
 * label, so "Collection" gives "Collection 01", "Collection 02" and so on.
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
<div class="wl-sbc-grid">
    <?php foreach ( $rows as $wl_sbc_i => $wl_sbc_row ) : ?>
        <?php echo $this->card_open( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                <span class="wl-sbc-media">
                    <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="<?php echo esc_attr( $wl_sbc_row['name'] ); ?>" loading="lazy">
                </span>
            <?php endif; ?>

            <span class="wl-sbc-shade" aria-hidden="true"></span>

            <span class="wl-sbc-body">
                <?php if ( '' !== $header['eyebrow'] ) : ?>
                    <span class="wl-sbc-eyebrow">
                        <?php echo esc_html( trim( $header['eyebrow'] . ' ' . sprintf( '%02d', $wl_sbc_i + 1 ) ) ); ?>
                    </span>
                <?php endif; ?>

                <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>

                <?php if ( '' !== $wl_sbc_row['desc'] ) : ?>
                    <span class="wl-sbc-row-desc"><?php echo esc_html( $wl_sbc_row['desc'] ); ?></span>
                <?php endif; ?>

                <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                <?php if ( '' !== $wl_sbc_count ) : ?>
                    <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                <?php endif; ?>
            </span>

            <span class="wl-sbc-go">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M7 17 17 7M7 7h10v10"/>
                </svg>
            </span>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
