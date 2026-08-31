<?php
/**
 * Shop by Category — Magazine / Variant 1.
 *
 * A twelve-column mosaic. The first tile runs half the width and two rows deep, the next two are
 * tall quarters, then two short quarters, then a wide half — a repeating six-tile rhythm the CSS
 * drives from :nth-child, so the section keeps its shape whatever the category count is. Every
 * tile is a photograph under a bottom-weighted wash with the name, its count and a yellow circular
 * arrow that rotates on hover.
 *
 * Reference: design-reference/new_temlate/magazine-style/v1/home-magazine.html:7049 — `.cat-grid` / `.cat-tile`
 *
 * The reference's feature tile carries an extra kicker line ("Featured · 248 products") above the
 * name. Here that slot is the category's own description, so the first tile fills it from the
 * taxonomy rather than from copy typed into the panel.
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

$wl_sbc_view_all = $this->view_all_link( $header['view_all'], $this->arrow( 14 ) );
?>
<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] || '' !== $wl_sbc_view_all ) : ?>
    <div class="wl-sbc-head">
        <div class="wl-sbc-head-main">
            <?php if ( '' !== $header['eyebrow'] ) : ?>
                <span class="wl-sbc-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
            <?php endif; ?>

            <?php if ( '' !== $header['headline'] ) : ?>
                <h2 class="wl-sbc-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
            <?php endif; ?>
        </div>

        <?php echo $wl_sbc_view_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside view_all_link() ?>
    </div>
<?php endif; ?>

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
                <span class="wl-sbc-label">
                    <?php if ( 0 === $wl_sbc_i % 6 && '' !== $wl_sbc_row['desc'] ) : ?>
                        <span class="wl-sbc-row-desc"><?php echo esc_html( $wl_sbc_row['desc'] ); ?></span>
                    <?php endif; ?>

                    <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>

                    <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                    <?php if ( '' !== $wl_sbc_count ) : ?>
                        <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                    <?php endif; ?>
                </span>

                <span class="wl-sbc-go"><?php echo $this->arrow( 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup ?></span>
            </span>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
