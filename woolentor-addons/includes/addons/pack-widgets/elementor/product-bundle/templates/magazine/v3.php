<?php
/**
 * Product Bundle — Magazine / Variant 3
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>
<?php echo $this->switcher_html( $bundles, 'tabs' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>

<div class="wl-pb-stage">
    <?php foreach ( $bundles as $position => $bundle ) : ?>
        <?php
        $my_items = $items[ $bundle['index'] ] ?? [];

        if ( empty( $my_items ) ) {
            continue;
        }

        $totals = $this->bundle_totals( $my_items );
        $alt    = '' !== $bundle['name'] ? wp_strip_all_tags( $bundle['name'] ) : __( 'Product bundle', 'woolentor' );
        ?>
        <div class="wl-pb-bundle<?php echo 0 === $position ? ' is-active' : ''; ?>"<?php echo $this->bundle_attrs( $bundle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bundle_attrs(). ?>>
            <div class="wl-pb-split">

                <div class="wl-pb-plate">
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

                        <?php if ( '' !== $bundle['tag'] ) : ?>
                            <span class="wl-pb-flag"><?php echo esc_html( $bundle['tag'] ); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php echo $this->caption_html( $bundle, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside caption_html(). ?>
                </div>

                <div class="wl-pb-body">
                    <?php if ( '' !== $bundle['name'] ) : ?>
                        <h3 class="wl-pb-name"><?php echo wp_kses( $bundle['name'], [ 'em' => [], 'strong' => [] ] ); ?></h3>
                    <?php endif; ?>

                    <?php if ( '' !== $bundle['desc'] ) : ?>
                        <p class="wl-pb-intro"><?php echo esc_html( $bundle['desc'] ); ?></p>
                    <?php endif; ?>

                    <?php echo $this->list_html( $my_items, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside list_html(). ?>

                    <div class="wl-pb-pricing">
                        <?php
                        echo $this->total_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside total_html().
                        echo $this->saving_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside saving_html().
                        ?>
                    </div>

                    <div class="wl-pb-foot">
                        <?php echo $this->cta_html( $bundle, $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>
