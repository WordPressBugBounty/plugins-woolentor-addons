<?php
/**
 * Product Bundle — Magazine / Variant 2
 *
 * @package WooLentor
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php echo $this->header_html( $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside header_html(). ?>

<div class="wl-pb-stage">
    <?php foreach ( $bundles as $position => $bundle ) : ?>
        <?php
        $my_items = $items[ $bundle['index'] ] ?? [];

        if ( empty( $my_items ) ) {
            continue;
        }

        $totals = $this->bundle_totals( $my_items );
        ?>
        <div class="wl-pb-bundle<?php echo 0 === $position ? ' is-active' : ''; ?>"<?php echo $this->bundle_attrs( $bundle ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside bundle_attrs(). ?>>
            <div class="wl-pb-split">

                <div class="wl-pb-visual">
                    <div class="wl-pb-visual-head">
                        <h4 class="wl-pb-visual-title"><?php esc_html_e( 'Build the Set', 'woolentor' ); ?></h4>
                        <div class="wl-pb-visual-actions">
                            <span class="wl-pb-hint"><?php esc_html_e( 'Tap to add / remove', 'woolentor' ); ?></span>
                            <?php echo $this->switcher_html( $bundles, 'arrows' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside switcher_html(). ?>
                        </div>
                    </div>

                    <?php
                    // The tiles are the same rows every other variant renders — same classes, same
                    // data, same toggle. Only the pack stylesheet lays them out sideways.
                    ?>
                    <div class="wl-pb-tiles">
                        <?php echo $this->list_html( $my_items, $settings, $style, $variant ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside list_html(). ?>
                    </div>

                    <?php echo $this->selectbar_html( $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside selectbar_html(). ?>
                </div>

                <div class="wl-pb-summary">
                    <?php if ( '' !== $bundle['tag'] ) : ?>
                        <span class="wl-pb-tag"><?php echo esc_html( $bundle['tag'] ); ?></span>
                    <?php endif; ?>

                    <?php if ( '' !== $bundle['name'] ) : ?>
                        <h3 class="wl-pb-name"><?php echo wp_kses( $bundle['name'], [ 'em' => [], 'strong' => [] ] ); ?></h3>
                    <?php endif; ?>

                    <?php if ( '' !== $bundle['desc'] ) : ?>
                        <p class="wl-pb-intro"><?php echo esc_html( $bundle['desc'] ); ?></p>
                    <?php endif; ?>

                    <?php
                    echo $this->lines_html( $my_items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside lines_html().
                    echo $this->gift_html( $bundle, $totals ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside gift_html().
                    echo $this->total_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside total_html().
                    echo $this->saving_html( $totals, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside saving_html().
                    ?>

                    <div class="wl-pb-foot">
                        <?php echo $this->cta_html( $bundle, $my_items, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside cta_html(). ?>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>
