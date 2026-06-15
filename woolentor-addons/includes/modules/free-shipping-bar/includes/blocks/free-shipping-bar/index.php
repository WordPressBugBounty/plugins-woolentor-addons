<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Woolentor\Modules\FreeShippingBar\Frontend\Bar_Renderer;

if ( ! class_exists( Bar_Renderer::class ) ) {
    require_once \Woolentor\Modules\FreeShippingBar\MODULE_PATH . '/includes/classes/Frontend/Bar_Renderer.php';
}

$uniqClass   = 'woolentorblock-' . $settings['blockUniqId'];
$areaClasses = [ $uniqClass, 'woolentor-free-shipping-bar-area' ];

! empty( $settings['className'] ) ? $areaClasses[] = esc_attr( $settings['className'] ) : '';

echo '<div class="' . esc_attr( implode( ' ', $areaClasses ) ) . '">';

$shortcode_params = [
    'inline'          => 'yes',
    'widget'          => 'yes',
    'dismissible'     => ! empty( $settings['dismissible'] )      ? 'yes' : 'no',
    'show_icon'       => ! empty( $settings['showIcon'] )         ? 'yes' : 'no',
    'icon'            => ! empty( $settings['icon'] )             ? $settings['icon'] : '',
    'show_countdown'  => ! empty( $settings['showCountdown'] )    ? 'yes' : 'no',
    'message'         => ! empty( $settings['customMessage'] )    ? $settings['customMessage'] : '',
    'message_success' => ! empty( $settings['customSuccessMessage'] ) ? $settings['customSuccessMessage'] : '',
];

echo woolentor_do_shortcode( 'woolentor_free_shipping_bar', $shortcode_params ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo '</div>';
