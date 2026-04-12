<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$slc                  = woolentor_shopify_like_checkout();
$enable_header_footer = ! empty( $slc->enable_header_footer );

$messages_html = wc_print_notices( true );
add_action( 'woolentor_shopify_like_checkout_after_breadcrumb', function() use ( $messages_html ) {
    if( !empty( $messages_html ) ){
        echo '<div class="woolentor-checkout__all_notices">'. wc_kses_notice( $messages_html ). '</div>';
    }
}, 15 );

if ( $enable_header_footer ) :
    get_header();
    ?>
    <div class="woolentor-checkout__box wl-slc-isolated">
        <?php echo woolentor_do_shortcode( 'woocommerce_checkout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
    <?php
    get_footer();
else :
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
            <title><?php echo wp_get_document_title(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></title>
        <?php endif; ?>
        <?php wp_head(); ?>
    </head>
    <body <?php body_class('woolentor-checkout__box'); ?>>
        <?php
            if( function_exists('wp_body_open') ){
                wp_body_open();
            }

            echo woolentor_do_shortcode( 'woocommerce_checkout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            wp_footer();
        ?>
    </body>
</html>
    <?php
endif;
