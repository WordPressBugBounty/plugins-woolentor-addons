<?php
/**
 * Product Bundle — Editorial / Variant 2
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>

<div class="wl-pb-stack">
    <?php foreach ( $bundles as $bundle ) : ?>
        <?php
        $my_items = $items[ $bundle['index'] ] ?? [];

        if ( empty( $my_items ) ) {
            continue;
        }

        $totals = $this->bundle_totals( $my_items );
        $alt    = '' !== $bundle['name'] ? wp_strip_all_tags( $bundle['name'] ) : __( 'Product bundle', 'woolentor' );

        $label = trim( (string) $bundle['tag'] );
        if ( '' === $label ) {
            /* translators: %s: bundle number, zero padded */
            $label = sprintf( esc_html__( 'Set %s', 'woolentor' ), str_pad( (string) $bundle['index'], 2, '0', STR_PAD_LEFT ) );
        }
        ?>
        <div class="wl-pb-split"<?php echo $this->bundle_attrs( $bundle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bundle_attrs(). ?>>

            <div class="wl-pb-image">
                <?php if ( $bundle['id'] ) : ?>
                    <?php echo wp_get_attachment_image( $bundle['id'], 'full', false, [
                        'class'   => 'wl-pb-photo',
                        'alt'     => $alt,
                        'loading' => 'lazy',
                    ] ); ?>
                <?php elseif ( $bundle['url'] ) : ?>
                    <img class="wl-pb-photo" src="<?php echo esc_url( $bundle['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
                <?php endif; ?>

                <span class="wl-pb-flag"><?php echo esc_html( $label ); ?></span>
            </div>

            <div class="wl-pb-body">
                <?php if ( '' !== $bundle['name'] ) : ?>
                    <h3 class="wl-pb-name"><?php echo wp_kses( $bundle['name'], [ 'em' => [], 'strong' => [] ] ); ?></h3>
                <?php endif; ?>

                <?php if ( '' !== $bundle['desc'] ) : ?>
                    <p class="wl-pb-intro"><?php echo esc_html( $bundle['desc'] ); ?></p>
                <?php endif; ?>

                <?php
                echo $this->list_html( $my_items, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside list_html().
                echo $this->selectbar_html( $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside selectbar_html().
                ?>

                <div class="wl-pb-pricing">
                    <?php
                    echo $this->total_html( $totals, $settings, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside total_html().
                    echo $this->saving_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside saving_html().
                    ?>
                </div>

                <div class="wl-pb-foot">
                    <?php echo $this->cta_html( $bundle, $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>
