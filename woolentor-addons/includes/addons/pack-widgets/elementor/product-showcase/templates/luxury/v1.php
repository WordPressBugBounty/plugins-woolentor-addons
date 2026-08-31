<?php
/**
 * Product Showcase — Luxury / Variant 1.
 *
 * The one carousel in the set. A header with the copy left and a gold-underlined link right, then
 * a rail of bordered cream cards with arrows hung outside the track.
 *
 * Reference: design-reference/new_temlate/luxury-style/v1/home-luxury.html:6691 — `.products-carousel`
 *
 * The reference's own track is native scroll-snap; this uses the bundled Slick instead, the way
 * every other pack-widget carousel does. Each card sits in a `.wl-ps-slide` shell because Slick
 * promotes the track's direct children as they are — the gap belongs on the shell, never on the
 * card, or the card's own border and background would be painted across it.
 *
 * @var \WP_Query $products  The section's products. Ownership stays with render(), which resets.
 * @var array     $header    eyebrow, headline, description, count_line, view_all.
 * @var array     $tabs      Tab rows: label, index, active, count.
 * @var array     $settings  Raw widget settings — read only through $this-> helpers.
 * @var string    $style     Pack slug.
 * @var string    $variant   Variant slug.
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wl_ps_view_all = $this->view_all_link( $header['view_all'], $this->arrow( 14 ) );
$wl_ps_tabs     = $this->tab_row( $tabs );
$wl_ps_card     = $this->card_template( $style, $variant );
$wl_ps_rail     = $this->is_rail( $settings );
?>

<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] || '' !== $wl_ps_view_all || '' !== $wl_ps_tabs ) : ?>
    <div class="wl-ps-head">
        <div class="wl-ps-head-main">
            <?php if ( '' !== $header['eyebrow'] ) : ?>
                <span class="wl-ps-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
            <?php endif; ?>

            <?php if ( '' !== $header['headline'] ) : ?>
                <h2 class="wl-ps-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
            <?php endif; ?>
        </div>

        <?php echo $wl_ps_view_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside view_all_link() ?>
    </div>

    <?php echo $wl_ps_tabs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside tab_row() ?>
<?php endif; ?>

<?php if ( $wl_ps_rail ) : ?><div class="wl-ps-slider-outer"><?php endif; ?>
    <div class="wl-ps-grid"<?php echo $this->grid_attrs( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside grid_attrs() ?>>
        <?php while ( $products->have_posts() ) : $products->the_post(); ?>
            <?php
            global $product;

            if ( ! is_a( $product, 'WC_Product' ) ) {
                continue;
            }
            ?>
            <?php if ( $wl_ps_rail ) : ?><div class="wl-ps-slide"><?php endif; ?>
                <?php include $wl_ps_card; ?>
            <?php if ( $wl_ps_rail ) : ?></div><?php endif; ?>
        <?php endwhile; ?>

        <?php echo $this->empty_notice( $products ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside empty_notice() ?>
    </div>
<?php if ( $wl_ps_rail ) : ?></div><?php endif; ?>
