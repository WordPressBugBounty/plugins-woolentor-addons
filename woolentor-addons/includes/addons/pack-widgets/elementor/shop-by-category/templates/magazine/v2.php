<?php
/**
 * Shop by Category — Magazine / Variant 2.
 *
 * A newsstand of magazine covers on a dark band. Each category is a 3:4.1 cover — a photograph
 * inside a hairline frame, an issue kicker and number across the top, and the count over the name
 * at the foot, with an "Enter" link that fades up on hover. Alternate covers ride 34px lower so the
 * row reads as a rack rather than a grid. The header is centred above them.
 *
 * Reference: design-reference/new_temlate/magazine-style/v2/homepage.html:5364 — `.cat-news` / `.cat-cover`
 *
 * The plan listed this variant as needing a full-page scan. The markup is not in the HTML at all —
 * the reference builds `#catNews` from a JS array at homepage.html:5940, which is why every search
 * of the document body came back empty. The cover template is that string.
 *
 * The issue kicker is the Eyebrow control, so "L'ÉDITION Dept." is editable rather than baked in,
 * and the number beside it is the card's position.
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
<?php if ( '' !== $header['headline'] ) : ?>
    <div class="wl-sbc-head">
        <h2 class="wl-sbc-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
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
            <span class="wl-sbc-frame" aria-hidden="true"></span>

            <span class="wl-sbc-top">
                <?php if ( '' !== $header['eyebrow'] ) : ?>
                    <span class="wl-sbc-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
                <?php endif; ?>
                <span class="wl-sbc-num"><?php echo esc_html( sprintf( 'N°%02d', $wl_sbc_i + 1 ) ); ?></span>
            </span>

            <span class="wl-sbc-body">
                <?php $wl_sbc_count = $this->count_text( $wl_sbc_row, $settings ); ?>
                <?php if ( '' !== $wl_sbc_count ) : ?>
                    <span class="wl-sbc-count"><?php echo $wl_sbc_count; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside count_text() ?></span>
                <?php endif; ?>

                <span class="wl-sbc-name"><?php echo esc_html( $wl_sbc_row['name'] ); ?></span>

                <span class="wl-sbc-btn">
                    <?php echo esc_html( '' !== $header['card_button'] ? $header['card_button'] : __( 'Enter', 'woolentor' ) ); ?>
                    <span class="wl-sbc-go"><?php echo $this->arrow( 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup ?></span>
                </span>
            </span>
        <?php echo $this->card_close( $wl_sbc_row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_close() ?>
    <?php endforeach; ?>
</div>
