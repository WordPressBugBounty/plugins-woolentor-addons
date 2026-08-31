<?php
/**
 * Product Showcase — Modern / Variant 3.
 *
 * A centred header — eyebrow, headline, then a row of outline pills — over a four-across grid of
 * bordered cards, with a clay-coloured arrow link centred beneath. Unlike v1 and v2 the tab row
 * sits under the headline rather than beside it.
 *
 * Reference: design-reference/new_temlate/modern-style/v3/01-homepage.html:6829 — `.product-grid`
 *
 * The card lives in v3-card.php, which the tab handler re-renders on its own. The outer .wl-ps
 * wrapper and the [data-wl-pack] scope are emitted by render().
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

$wl_ps_view_all = $this->view_all_link( $header['view_all'], $this->arrow( 15 ) );
$wl_ps_tabs     = $this->tab_row( $tabs );
$wl_ps_card     = $this->card_template( $style, $variant );
$wl_ps_rail     = $this->is_rail( $settings );
?>

<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] || '' !== $wl_ps_tabs ) : ?>
    <div class="wl-ps-head">
        <?php if ( '' !== $header['eyebrow'] ) : ?>
            <span class="wl-ps-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
        <?php endif; ?>

        <?php if ( '' !== $header['headline'] ) : ?>
            <h2 class="wl-ps-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
        <?php endif; ?>

        <?php echo $wl_ps_tabs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside tab_row() ?>
    </div>
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

<?php if ( '' !== $wl_ps_view_all ) : ?>
    <div class="wl-ps-foot-row">
        <?php echo $wl_ps_view_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside view_all_link() ?>
    </div>
<?php endif; ?>
