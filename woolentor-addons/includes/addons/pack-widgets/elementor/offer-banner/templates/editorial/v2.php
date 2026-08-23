<?php
/**
 * Offer Banner — Editorial / Variant 2.
 *
 * This variant's homepage carries no banner card grid, so the layout is built from its own
 * designer's vocabulary rather than borrowed from a neighbouring variant:
 *
 *   - the ruled sky-blue eyebrow at `editorial-style/v2/homepage.html:1351` — a 30px hairline
 *     drawn before the label
 *   - the Playfair heading with an italic `<em>` in the accent colour, `:1374`
 *   - the frosted white pill tag pinned to a 12px-radius photo card, `:2446`
 *
 * Nothing is taken from v1 or v3. Because `<em>` survives the title's wp_kses() filter, a user can
 * reproduce the two-tone headline by typing it — no extra control needed.
 *
 * The eyebrow rule and the badge position come from the pack stylesheet, so no extra markup.
 *
 * The grid, card stacking and overlay mechanics come from .wl-ob-* in pack-widgets-base.css.
 * The outer .wl-ob wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array $items  Each: image, image_alt, eyebrow, title, subtitle, cta_text, url,
 *                    is_external, nofollow, card_is_link, size, tone, badge_value,
 *                    badge_label, features, meta, note.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ob_arrow = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
    . ' stroke-width="2.2" stroke-linecap="round" aria-hidden="true">'
    . '<path d="M5 12h14M12 5l7 7-7 7" /></svg>';
?>
<div class="wl-ob-grid">
    <?php foreach ( $items as $item ) : ?>
        <?php echo $this->card_open( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php echo $this->card_media( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
            <?php echo $this->card_badge( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_badge() ?>
            <div class="wl-ob-body">
                <?php if ( '' !== $item['eyebrow'] ) : ?>
                    <span class="wl-ob-eyebrow"><?php echo esc_html( $item['eyebrow'] ); ?></span>
                <?php endif; ?>

                <?php if ( '' !== $item['title'] ) : ?>
                    <h3 class="wl-ob-title"><?php echo $this->card_title( $item['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in card_title() ?></h3>
                <?php endif; ?>

                <?php if ( '' !== $item['subtitle'] ) : ?>
                    <p class="wl-ob-sub"><?php echo esc_html( $item['subtitle'] ); ?></p>
                <?php endif; ?>

                <?php echo $this->card_cta( $item, $wl_ob_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cta() ?>
            </div>
        <?php echo $this->card_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
