<?php
/**
 * Product Bundle — Modern / Variant 1
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>

<div class="wl-pb-grid">
    <?php foreach ( $bundles as $bundle ) : ?>
        <?php
        $my_items = $items[ $bundle['index'] ] ?? [];

        if ( empty( $my_items ) ) {
            continue;
        }

        $totals = $this->bundle_totals( $my_items );
        ?>
        <article class="wl-pb-card"<?php echo $this->bundle_attrs( $bundle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bundle_attrs(). ?>>

            <div class="wl-pb-card-head">
                <?php if ( '' !== $bundle['tag'] ) : ?>
                    <span class="wl-pb-tag"><?php echo esc_html( $bundle['tag'] ); ?></span>
                <?php endif; ?>
                <?php echo $this->saving_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside saving_html(). ?>
            </div>

            <?php echo $this->thumbstrip_html( $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside thumbstrip_html(). ?>

            <?php if ( '' !== $bundle['name'] ) : ?>
                <h4 class="wl-pb-name"><?php echo wp_kses( $bundle['name'], [ 'em' => [], 'strong' => [] ] ); ?></h4>
            <?php endif; ?>

            <?php echo $this->list_html( $my_items, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside list_html(). ?>

            <div class="wl-pb-foot">
                <?php
                // No label here: the reference prints the two figures alone, and the button beside
                // them says what they are for.
                echo $this->total_html( $totals, $settings, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside total_html().
                echo $this->cta_html( $bundle, $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html().
                ?>
            </div>

        </article>
    <?php endforeach; ?>
</div>
