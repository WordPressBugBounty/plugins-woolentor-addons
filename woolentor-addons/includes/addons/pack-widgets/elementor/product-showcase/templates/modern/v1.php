<?php
/**
 * Product Showcase — Modern / Variant 1.
 *
 * A four-across grid of white cards under a split header: the copy block on the left, the tab row
 * on the right, and a centred outline button beneath the grid. Each card is a square image with a
 * badge stack top-left, wishlist and compare stacked top-right, and an Add-to-Cart bar that rises
 * out of the bottom edge on hover with quick view beside it.
 *
 * Reference: design-reference/new_temlate/modern-style/v1/home-modern.html:5833 — `.prod-grid`
 *
 * The card itself lives in v1-card.php, which the tab handler re-renders on its own. The outer
 * .wl-ps wrapper and the [data-wl-pack] scope are emitted by render().
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

$wl_ps_view_all = $this->view_all_link( $header['view_all'], $this->arrow( 16 ) );
$wl_ps_tabs     = $this->tab_row( $tabs );
$wl_ps_card     = $this->card_template( $style, $variant );
$wl_ps_rail     = $this->is_rail( $settings );
?>

<?php if ( '' !== $header['eyebrow'] || '' !== $header['headline'] || '' !== $header['description'] || '' !== $wl_ps_tabs ) : ?>
    <div class="wl-ps-head">
        <div class="wl-ps-head-main">
            <?php if ( '' !== $header['eyebrow'] ) : ?>
                <span class="wl-ps-eyebrow"><?php echo esc_html( $header['eyebrow'] ); ?></span>
            <?php endif; ?>

            <?php if ( '' !== $header['headline'] ) : ?>
                <h2 class="wl-ps-headline"><?php echo $this->headline( $header['headline'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- run through wp_kses() in headline() ?></h2>
            <?php endif; ?>

            <?php if ( '' !== $header['description'] ) : ?>
                <p class="wl-ps-desc"><?php echo esc_html( $header['description'] ); ?></p>
            <?php endif; ?>
        </div>

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
