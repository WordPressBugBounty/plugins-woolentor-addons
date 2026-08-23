<?php
/**
 * Marquee — Modern / Variant 1.
 *
 * A labelled brand strip: a small uppercase lead above a white band of large, muted wordmarks
 * that darken on hover, parted by soft dots. Hairline edges, top and bottom.
 *
 * The item list is echoed twice inside the track: the keyframes translate the track by -50%,
 * so two identical halves make the loop seamless. The second pass is aria-hidden so screen
 * readers announce each item once. The animation itself comes from .wl-pack-marquee* in
 * pack-widgets-base.css — no JavaScript is loaded for this widget.
 *
 * Reference: design-reference/new_temlate/modern-style/v1/home-modern.html:7464 — `.brand-strip`
 *
 * The outer .wl-mq wrapper and the [data-wl-pack] scope are emitted by render().
 *
 * @var array  $items          Each: text, icon, image, url, is_external, nofollow, is_highlighted.
 * @var string $section_label  Optional lead above the band.
 * @var array  $marquee        speed (int seconds), direction, separator, pause_on_hover, edge_fade.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php if ( $section_label ) : ?>
    <span class="wl-mq-label"><?php echo esc_html( $section_label ); ?></span>
<?php endif; ?>
<div class="<?php echo esc_attr( $this->band_classes( $marquee ) ); ?>"
    style="--wl-pack-marquee-duration:<?php echo esc_attr( $marquee['speed'] ); ?>s"
    aria-label="<?php echo esc_attr__( 'Brands we partner with', 'woolentor' ); ?>">
    <div class="wl-pack-marquee-track">
        <?php
        // Pass 1 is the accessible copy; pass 2 only exists to make the scroll seamless.
        foreach ( [ false, true ] as $is_duplicate ) :
            ?>
            <div class="wl-mq-set wl-pack-marquee-set"<?php echo $is_duplicate ? ' aria-hidden="true"' : ''; ?>>
                <?php foreach ( $items as $item ) : ?>
                    <?php echo $this->item_open( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_open() ?>
                        <?php echo $this->item_media( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside item_media() ?>
                        <?php if ( $item['text'] ) : ?>
                            <span class="wl-mq-text"><?php echo esc_html( $item['text'] ); ?></span>
                        <?php endif; ?>
                    <?php echo $this->item_close( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static tag ?>
                    <?php if ( 'none' !== $marquee['separator'] ) : ?>
                        <span class="wl-pack-marquee-sep wl-pack-marquee-sep--<?php echo esc_attr( $marquee['separator'] ); ?>" aria-hidden="true"></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
