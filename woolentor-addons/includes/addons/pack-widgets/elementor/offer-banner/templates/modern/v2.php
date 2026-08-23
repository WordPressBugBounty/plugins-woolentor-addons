<?php
/**
 * Offer Banner — Modern / Variant 2.
 *
 * This variant's homepage carries no banner card grid, so the layout is built from its own
 * designer's card language rather than borrowed from a neighbouring variant: the `.cat3__card`
 * treatment at `modern-style/v2/homepage.html:2335` — a nearly square 3px corner, a darkened
 * photograph, bottom-centred content, an olive accent bar that wipes in across the top on hover,
 * and a ghost outline button.
 *
 * One deliberate departure from that reference: `.cat3__cbtn` is invisible until hover. That works
 * for a category tile, where the whole card is the affordance, but a promotional banner exists to
 * be clicked — so the button stays visible here and keeps the ghost outline treatment.
 *
 * The accent bar is drawn with a pseudo-element in the pack stylesheet, so no extra markup.
 *
 * Note the field order: the reference puts the name first and the small meta line under it, so
 * this template renders the title above the eyebrow. The Content tab still lists them the usual
 * way round — only this variant's layout differs.
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
?>
<div class="wl-ob-grid">
    <?php foreach ( $items as $item ) : ?>
        <?php echo $this->card_open( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_open() ?>
            <?php echo $this->card_media( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_media() ?>
            <div class="wl-ob-body">
                <?php if ( '' !== $item['title'] ) : ?>
                    <h3 class="wl-ob-title"><?php echo $this->card_title( $item['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in card_title() ?></h3>
                <?php endif; ?>

                <?php if ( '' !== $item['eyebrow'] ) : ?>
                    <span class="wl-ob-eyebrow"><?php echo esc_html( $item['eyebrow'] ); ?></span>
                <?php endif; ?>

                <?php if ( '' !== $item['subtitle'] ) : ?>
                    <p class="wl-ob-sub"><?php echo esc_html( $item['subtitle'] ); ?></p>
                <?php endif; ?>

                <?php echo $this->card_cta( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside card_cta() ?>
            </div>
        <?php echo $this->card_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
    <?php endforeach; ?>
</div>
