<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uniqClass 	 = 'woolentorblock-'.$settings['blockUniqId'];
$areaClasses = array( $uniqClass, 'woolentor-currency-switcher-area' );

!empty( $settings['className'] ) ? $areaClasses[] = esc_attr( $settings['className'] ) : '';


echo '<div class="'.esc_attr(implode(' ', $areaClasses )).'">';
    $shortcode_attributes = [
        'style' => $settings['currencyStyle'],
        'show_currency_name' => $settings['showCurrencyName'] ? 'yes' : 'no',
        'show_currency_symbol' => $settings['showCurrencySymbol'] ? 'yes' : 'no',
        'selected_show_currency_name' => $settings['selectedShowCurrencyName'] ? 'yes' : 'no',
        'selected_show_currency_symbol' => $settings['selectedShowCurrencySymbol'] ? 'yes' : 'no',
    ];
    if( woolentor_is_pro() ){
        $shortcode_attributes['flags']      = $settings['showFlags'] ? 'yes' : 'no';
        $shortcode_attributes['flag_style'] = $settings['flagStyle'];
    }
    echo woolentor_do_shortcode( 'woolentor_currency_switcher', $shortcode_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '</div>';