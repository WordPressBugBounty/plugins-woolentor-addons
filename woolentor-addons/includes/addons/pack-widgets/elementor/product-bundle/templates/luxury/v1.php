<?php
/**
 * Product Bundle — Luxury / Variant 1
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
        $alt    = '' !== $bundle['name'] ? wp_strip_all_tags( $bundle['name'] ) : __( 'Product bundle', 'woolentor' );
        ?>
        <article class="wl-pb-card"<?php echo $this->bundle_attrs( $bundle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bundle_attrs(). ?>>

            <div class="wl-pb-image">
                <?php if ( $bundle['id'] ) : ?>
                    <?php echo wp_get_attachment_image( $bundle['id'], 'large', false, [
                        'class'   => 'wl-pb-photo',
                        'alt'     => $alt,
                        'loading' => 'lazy',
                    ] ); ?>
                <?php elseif ( $bundle['url'] ) : ?>
                    <img class="wl-pb-photo" src="<?php echo esc_url( $bundle['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
                <?php endif; ?>

                <span class="wl-pb-scrim" aria-hidden="true"></span>

                <div class="wl-pb-image-title">
                    <?php if ( '' !== $bundle['tag'] ) : ?>
                        <span class="wl-pb-tag"><?php echo esc_html( $bundle['tag'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( '' !== $bundle['name'] ) : ?>
                        <h3 class="wl-pb-name"><?php echo wp_kses( $bundle['name'], [ 'em' => [], 'strong' => [] ] ); ?></h3>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wl-pb-body">
                <?php echo $this->list_html( $my_items, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside list_html(). ?>

                <?php
                // The reference writes its own wording beside the figure, so the figure comes
                // through without the control's "Save" label attached to it as well.
                $saving = $this->saving_html( $totals, $settings, false );
                ?>
                <?php if ( '' !== $saving ) : ?>
                    <div class="wl-pb-savings-row">
                        <span class="wl-pb-savings-label"><?php esc_html_e( 'You save with this bundle', 'woolentor' ); ?></span>
                        <?php echo $saving; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside saving_html(). ?>
                    </div>
                <?php endif; ?>

                <div class="wl-pb-pricing">
                    <?php
                    echo $this->total_html( $totals, $settings, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside total_html().
                    echo $this->ribbon_html( $bundle, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside ribbon_html().
                    ?>
                </div>

                <div class="wl-pb-foot">
                    <?php echo $this->cta_html( $bundle, $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>
            </div>

        </article>
    <?php endforeach; ?>
</div>
