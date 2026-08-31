<?php
/**
 * Shop by Category — Modern / Variant 2.
 *
 * Four full-bleed image cards of equal height, a gradient shade over each, and the copy centred
 * along the bottom: name, count, then a middle-dot child line. A "Shop Now" button sits under the
 * copy and is revealed on hover — it is absent from the reference screenshot for that reason, not
 * because the design lacks one. The header is centred with a rule flanking the eyebrow.
 *
 * Reference: design-reference/new_temlate/modern-style/v2/homepage.html:5514 — `.cat3__grid` / `.cat3__card`
 *
 * The image is a background rather than an <img> because the card is a fixed-height tile the photo
 * has to fill and pan inside; the alt text is carried by the card's aria-label instead.
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
<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] || '' !== $header['description'] ) : ?>
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
    </div>
<?php endif; ?>

<div class="wl-sbc-grid">
    <?php foreach ( $rows as $wl_sbc_row ) : ?>
        <?php echo $this->card_open( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php if ( '' !== $wl_sbc_row['image'] ) : ?>
                <span class="wl-sbc-media" role="img" aria-label="<?php echo esc_attr( $wl_sbc_row['name'] ); ?>"
                    style="background-image:url('<?php echo esc_url( $wl_sbc_row['image'] ); ?>');"></span>
            <?php endif; ?>

            <span class="wl-sbc-shade" aria-hidden="true"></span>

            <span class="wl-sbc-body">
                <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>

                <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                <?php if ( '' !== $wl_sbc_count ) : ?>
                    <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                <?php endif; ?>

                <?php if ( ! empty( $wl_sbc_row['children'] ) ) : ?>
                    <span class="wl-sbc-sub"><?php echo esc_html( implode( ' · ', array_slice( $wl_sbc_row['children'], 0, 3 ) ) ); ?></span>
                <?php endif; ?>

                <?php if ( '' !== $header['card_button'] ) : ?>
                    <span class="wl-sbc-btn"><?php echo esc_html( $header['card_button'] ); ?></span>
                <?php endif; ?>
            </span>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
