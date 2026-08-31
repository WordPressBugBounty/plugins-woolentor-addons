<?php
/**
 * Shop by Category — Modern / Variant 1.
 *
 * Six cards in a row. A square thumbnail on top, then a frosted footer bar holding the category
 * name over its product count, with a circular outline arrow pushed to the right. The header is
 * left-aligned with the "View all categories" link pinned to the right edge of the row.
 *
 * Reference: design-reference/new_temlate/modern-style/v1/home-modern.html:5710 — `.cat-grid` / `.cat-card`
 *
 * The outer .wl-sbc wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $rows      Category rows: id, name, url, count, image, icon, children.
 * @var array $header    eyebrow, headline, description, view_all.
 * @var array $settings  Raw widget settings — read only through $this->count_text().
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_sbc_view_all = $this->view_all_link( $header['view_all'], $this->arrow( 14 ) );
?>
<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] || '' !== $header['description'] || '' !== $wl_sbc_view_all ) : ?>
    <div class="wl-sbc-head">
        <div class="wl-sbc-head-main">
            <?php if ( '' !== $header['eyebrow'] ) : ?>
                <span class="wl-sbc-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
            <?php endif; ?>

            <?php if ( '' !== $header['headline'] ) : ?>
                <h2 class="wl-sbc-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
            <?php endif; ?>

            <?php if ( '' !== $header['description'] ) : ?>
                <p class="wl-sbc-desc"><?php echo esc_html( $header['description'] ); ?></p>
            <?php endif; ?>
        </div>

        <?php echo $wl_sbc_view_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside view_all_link() ?>
    </div>
<?php endif; ?>

<div class="wl-sbc-grid">
    <?php foreach ( $rows as $wl_sbc_row ) : ?>
        <?php echo $this->card_open( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                <div class="wl-sbc-media">
                    <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="<?php echo esc_attr( $wl_sbc_row['name'] ); ?>" loading="lazy">
                </div>
            <?php endif; ?>

            <div class="wl-sbc-body">
                <div class="wl-sbc-label">
                    <h6 class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></h6>
                    <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                    <?php if ( '' !== $wl_sbc_count ) : ?>
                        <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                    <?php endif; ?>
                </div>

                <span class="wl-sbc-go"><?php echo $this->arrow( 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup ?></span>
            </div>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
