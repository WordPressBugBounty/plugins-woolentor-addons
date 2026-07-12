<?php
/**
 * Hero Banner — Modern / Variant 2
 * Cinematic Full-Width: 100vh bg image with slow zoom, left-aligned content,
 * per-line outline heading, bottom-right glassmorphism stats panel, rotated scroll indicator.
 *
 * Outer wrappers (.wl-hero-banner.wl-hero-modern-v2 > .wl-hero-slide) are emitted by render().
 * This template provides the inner markup for each slide.
 *
 * @var string   $eyebrow
 * @var string[] $heading_lines          Raw heading split by <br>, one element per line.
 * @var string[] $heading_outline_phrases Lines that should render with outline (ghost) stroke.
 * @var string   $description
 * @var string   $btn_primary_text
 * @var string   $btn_primary_url
 * @var string   $btn_secondary_text
 * @var string   $btn_secondary_url
 * @var string   $hero_image_url         Full-bleed background photo.
 * @var string   $hero_image_alt
 * @var string   $scroll_text            Rotated scroll indicator text.
 * @var string   $v2_stat_1_n / _1_l     Stat 1 value + label.
 * @var string   $v2_stat_2_n / _2_l     Stat 2 value + label.
 * @var string   $v2_stat_3_n / _3_l     Stat 3 value + label.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php if ( $hero_image_url ) : ?>
<div class="wl-hv2-bg">
    <img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="eager" class="wl-hv2-bg-img">
</div>
<?php endif; ?>

<div class="wl-hv2-overlay"></div>

<div class="wl-hv2-container">
    <div class="wl-hv2-content">

        <?php if ( $eyebrow ) : ?>
        <div class="wl-hv2-eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
        <?php endif; ?>

        <?php if ( $heading_lines ) : ?>
        <h1 class="wl-hv2-title">
            <?php foreach ( $heading_lines as $line ) :
                // Case 1: entire line matches a phrase → whole block span is outline.
                $is_full_outline = in_array( $line, $heading_outline_phrases, true );
                $block_cls       = $is_full_outline ? ' class="wl-hl-outline"' : '';
                $line_html       = esc_html( $line );

                // Case 2: line contains a phrase (no <br> split used) → inject inline outline span.
                if ( ! $is_full_outline ) {
                    foreach ( $heading_outline_phrases as $phrase ) {
                        $escaped_phrase = esc_html( $phrase );
                        if ( $escaped_phrase !== '' && strpos( $line_html, $escaped_phrase ) !== false ) {
                            $line_html = str_replace(
                                $escaped_phrase,
                                '<span class="wl-hl-outline">' . esc_html( $escaped_phrase ) . '</span>',
                                $line_html
                            );
                        }
                    }
                }
            ?>
            <span<?php echo $block_cls; ?>><?php echo $line_html; ?></span>
            <?php endforeach; ?>
        </h1>
        <?php endif; ?>

        <?php if ( $description ) : ?>
        <p class="wl-hv2-desc"><?php echo wp_kses_post( $description ); ?></p>
        <?php endif; ?>

        <?php if ( $btn_primary_text || $btn_secondary_text ) : ?>
        <div class="wl-hv2-actions">
            <?php if ( $btn_primary_text ) : ?>
            <a href="<?php echo esc_url( $btn_primary_url ); ?>" class="wl-hv2-btn wl-btn-primary"><?php echo esc_html( $btn_primary_text ); ?></a>
            <?php endif; ?>
            <?php if ( $btn_secondary_text ) : ?>
            <a href="<?php echo esc_url( $btn_secondary_url ); ?>" class="wl-hv2-btn wl-btn-ghost"><?php echo esc_html( $btn_secondary_text ); ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php if ( $scroll_text ) : ?>
<div class="wl-hv2-scroll" aria-hidden="true"><?php echo esc_html( $scroll_text ); ?></div>
<?php endif; ?>

<?php if ( $v2_stat_1_n || $v2_stat_2_n || $v2_stat_3_n ) : ?>
<div class="wl-hv2-stats">
    <?php if ( $v2_stat_1_n ) : ?>
    <div class="wl-hv2-stat">
        <span class="wl-hv2-stat__n"><?php echo esc_html( $v2_stat_1_n ); ?></span>
        <?php if ( $v2_stat_1_l ) : ?><span class="wl-hv2-stat__l"><?php echo esc_html( $v2_stat_1_l ); ?></span><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ( $v2_stat_2_n ) : ?>
    <div class="wl-hv2-stat">
        <span class="wl-hv2-stat__n"><?php echo esc_html( $v2_stat_2_n ); ?></span>
        <?php if ( $v2_stat_2_l ) : ?><span class="wl-hv2-stat__l"><?php echo esc_html( $v2_stat_2_l ); ?></span><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ( $v2_stat_3_n ) : ?>
    <div class="wl-hv2-stat">
        <span class="wl-hv2-stat__n"><?php echo esc_html( $v2_stat_3_n ); ?></span>
        <?php if ( $v2_stat_3_l ) : ?><span class="wl-hv2-stat__l"><?php echo esc_html( $v2_stat_3_l ); ?></span><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
