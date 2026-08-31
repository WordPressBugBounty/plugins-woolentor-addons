<?php
/**
 * Shop by Category — Editorial / Variant 3.
 *
 * A 55/45 split. The left half is a pair of tall portrait photographs, each with the category name
 * and a superscript count sitting over a gradient at the foot. The right half is the section
 * header — eyebrow over a large serif heading — followed by a ruled list of every category, each
 * row a name with its count raised beside it and a circular chevron at the far right.
 *
 * Reference: design-reference/new_temlate/editorial-style/v3/homepage.html:4549 — `.categories__inner`
 *
 * The two photographs are the first two rows of the same set the list draws, exactly as the
 * reference does it — the same categories appear in both halves rather than the panel carrying a
 * second, separate picker.
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

$wl_sbc_photos = array_slice( $rows, 0, 2 );
?>
<div class="wl-sbc-spread">
    <?php if ( ! empty( $wl_sbc_photos ) ) : ?>
        <div class="wl-sbc-photos">
            <?php foreach ( $wl_sbc_photos as $wl_sbc_row ) : ?>
                <?php echo $this->card_open( $wl_sbc_row, 'wl-sbc-photo' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
                    <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                        <span class="wl-sbc-media">
                            <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="<?php echo esc_attr( $wl_sbc_row['name'] ); ?>" loading="lazy">
                        </span>
                    <?php endif; ?>

                    <span class="wl-sbc-shade" aria-hidden="true"></span>

                    <span class="wl-sbc-body">
                        <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>
                        <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                        <?php if ( '' !== $wl_sbc_count ) : ?>
                            <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                        <?php endif; ?>
                    </span>
                <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="wl-sbc-content">
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
                    <span class="wl-sbc-label">
                        <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>
                        <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                        <?php if ( '' !== $wl_sbc_count ) : ?>
                            <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                        <?php endif; ?>
                    </span>

                    <span class="wl-sbc-go">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </span>
                <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
