<?php
/**
 * Shop by Category — Editorial / Variant 1.
 *
 * A two-column spread. The left column is an editorial intro that stays put while the right column
 * scrolls — a numbered eyebrow, a serif headline with an italic accent, a lead paragraph and a
 * ruled "View all departments" link. The right column is a table of contents: one ruled row per
 * category carrying its index, name, description, count and arrow, with a small thumbnail that
 * slides in from the right on hover.
 *
 * Reference: design-reference/new_temlate/editorial-style/v1/home-editorial.html:5939 — `.dept-spread`
 *
 * This is the variant that put the term description into the row contract. The reference writes a
 * one-line descriptor under every department name, and WooCommerce already stores exactly that on
 * the category — so it is read from the taxonomy rather than typed into the panel.
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
<div class="wl-sbc-spread">
    <div class="wl-sbc-head">
        <?php if ( '' !== $header['eyebrow'] ) : ?>
            <span class="wl-sbc-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
        <?php endif; ?>

        <?php if ( '' !== $header['headline'] ) : ?>
            <h2 class="wl-sbc-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
        <?php endif; ?>

        <?php if ( '' !== $header['description'] ) : ?>
            <p class="wl-sbc-desc"><?php echo esc_html( $header['description'] ); ?></p>
        <?php endif; ?>

        <?php echo $wl_sbc_view_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside view_all_link() ?>
    </div>

    <div class="wl-sbc-grid">
        <?php foreach ( $rows as $wl_sbc_i => $wl_sbc_row ) : ?>
            <?php echo $this->card_open( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
                <span class="wl-sbc-num"><?php echo esc_html( sprintf( '%02d', $wl_sbc_i + 1 ) ); ?></span>

                <span class="wl-sbc-info">
                    <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>
                    <?php if ( '' !== $wl_sbc_row['desc'] ) : ?>
                        <span class="wl-sbc-row-desc"><?php echo esc_html( $wl_sbc_row['desc'] ); ?></span>
                    <?php endif; ?>
                </span>

                <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                <?php if ( '' !== $wl_sbc_count ) : ?>
                    <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                <?php endif; ?>

                <span class="wl-sbc-go"><?php echo $this->arrow( 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup ?></span>

                <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                    <span class="wl-sbc-media" aria-hidden="true">
                        <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="" loading="lazy">
                    </span>
                <?php endif; ?>
            <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
        <?php endforeach; ?>
    </div>
</div>
