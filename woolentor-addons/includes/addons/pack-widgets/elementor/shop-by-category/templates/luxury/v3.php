<?php
/**
 * Shop by Category — Luxury / Variant 3.
 *
 * A left-aligned header — small caps eyebrow over the headline — above a carousel of square cards.
 * Each card is a rounded 1:1 photograph with the category name below it and the count set in
 * lighter weight on the same line. A progress rail and two outlined circular arrows share a
 * control row under the track.
 *
 * Reference: design-reference/new_temlate/luxury-style/v3/homepage.html:4399 — `.cat-viewport` / `.cat-card`
 *
 * Like luxury v1 this is a carousel driven by the shared WLPackSlider through
 * $this->slider_attrs(); the two differ in crop, radius and how many cards the track shows.
 *
 * Each card is wrapped in a .wl-sbc-slide div because Slick promotes the grid's direct children to
 * slides without adding a wrapper of its own. Without this the gutter padding would land on the
 * card itself, and the Card > Background control would paint straight through the gap.
 *
 * The outer .wl-sbc wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array  $rows      Category rows: id, name, url, count, image, icon, desc, children.
 * @var array  $header    eyebrow, headline, description, card_button, view_all.
 * @var array  $settings  Raw widget settings — read through count_text() and slider_attrs().
 * @var string $style     Pack slug, for slider_attrs().
 * @var string $variant   Variant key, for slider_attrs().
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

<div class="wl-sbc-slider-outer">
    <div class="wl-sbc-grid"<?php echo $this->slider_attrs( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside slider_attrs() ?>>
        <?php foreach ( $rows as $wl_sbc_row ) : ?>
            <div class="wl-sbc-slide">
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
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wl-sbc-controls">
        <span class="wl-sbc-progress" aria-hidden="true"><span class="wl-sbc-progress-fill"></span></span>
        <span class="wl-sbc-nav"></span>
    </div>
</div>
