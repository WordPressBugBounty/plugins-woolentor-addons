<?php
/**
 * Shop by Category — Modern / Variant 3.
 *
 * Six white cards in a 3 × 2 grid. Each card carries the name top-left, an outline icon
 * bottom-left, a lifestyle thumbnail down the right side, and a dark circular count badge
 * overlapping the thumbnail's left edge. The header is centred — eyebrow and headline only.
 *
 * Reference: design-reference/new_temlate/modern-style/v3/01-homepage.html:6637 — `.coll-grid` / `.coll-card`
 *
 * The icon is the one slot the taxonomy cannot fill: WooCommerce stores a category thumbnail but
 * has no icon field, so the slot is drawn only when the Overrides repeater supplies one. Without
 * overrides the card is still complete — name, badge and thumbnail all come from the term.
 *
 * The outer .wl-sbc wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $rows      Category rows: id, name, url, count, image, icon, children.
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
            <div class="wl-sbc-info">
                <h3 class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></h3>
                <?php echo $this->row_icon( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside row_icon() ?>
            </div>

            <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
            <?php if ( '' !== $wl_sbc_count ) : ?>
                <span class="wl-sbc-badge"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
            <?php endif; ?>

            <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                <div class="wl-sbc-media">
                    <img src="<?php echo esc_url( $wl_sbc_row['image'] ); ?>" alt="<?php echo esc_attr( $wl_sbc_row['name'] ); ?>" loading="lazy">
                </div>
            <?php endif; ?>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
