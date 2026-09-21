<?php
/**
 * Product Bundle — Luxury / Variant 3
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

                <span class="wl-pb-scrim" aria-hidden="true"></span>

                <div class="wl-pb-image-title">
                    <?php if ( '' !== $bundle['tag'] ) : ?>
                        <span class="wl-pb-tag"><?php echo esc_html( $bundle['tag'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( '' !== $bundle['name'] ) : ?>
                        <h3 class="wl-pb-name"><?php echo wp_kses( $bundle['name'], [ 'em' => [], 'strong' => [] ] ); ?></h3>
                    <?php endif; ?>
                    <?php echo $this->ribbon_html( $bundle, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside ribbon_html(). ?>
                </div>
            </div>

            <?php if ( '' !== $bundle['desc'] ) : ?>
                <p class="wl-pb-intro"><?php echo esc_html( $bundle['desc'] ); ?></p>
            <?php endif; ?>

            <?php echo $this->thumbstrip_html( $my_items, $settings, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside thumbstrip_html(). ?>

            <?php
            // Not `.wl-pb-summary`: that class is Magazine V2's boxed column and the Style tab
            // groups it with the cards under "Box". This is a closing strip — a hairline, a
            // saving, a price and a button — with no box to paint.
            ?>
            <div class="wl-pb-close">
                <?php echo $this->saving_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside saving_html(). ?>

                <div class="wl-pb-pricing">
                    <?php echo $this->total_html( $totals, $settings, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside total_html(). ?>
                </div>

                <div class="wl-pb-foot">
                    <?php echo $this->cta_html( $bundle, $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>
