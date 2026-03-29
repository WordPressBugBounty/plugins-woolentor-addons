<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Woolentor_Wb_Product_Reviews_Widget extends Widget_Base {

    public function get_name() {
        return 'wl-single-product-reviews';
    }

    public function get_title() {
        return __( 'WL: Product Reviews', 'woolentor' );
    }

    public function get_icon() {
        return 'eicon-product-rating';
    }

    public function get_categories() {
        return array( 'woolentor-addons' );
    }

    public function get_help_url() {
        return 'https://woolentor.com/documentation/';
    }

    public function get_style_depends(){
        return [
            'woolentor-widgets',
        ];
    }

    public function get_keywords(){
        return ['reviews','product review','review form','form'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            array(
                'label' => __( 'Product Reviews', 'woolentor' ),
            )
        );

            $this->add_control(
                'html_notice',
                array(
                    'label' => __( 'Element Information', 'woolentor' ),
                    'show_label' => false,
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => __( 'Products reviews', 'woolentor' ),
                )
            );

        $this->end_controls_section();

    }


    protected function render( $instance = [] ) {

        $settings   = $this->get_settings_for_display();
        global $product;
        $product = wc_get_product();

        if( woolentor_is_preview_mode() ){
            //echo \WooLentor_Default_Data::instance()->default( $this->get_name() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
            <div class="woocommerce woocommerce-page single-product woocommerce-js">
                <div id="review_form_wrapper">
                    <div id="review_form">
                        <div id="respond" class="comment-respond">
                            <form action="#" method="post" id="commentform" class="comment-form" novalidate="">
                                <div class="comment-form-rating">
                                <label for="rating"><?php esc_html_e('Your rating', 'woolentor'); ?> <span class="required"><?php esc_html_e('*', 'woolentor'); ?></span></label>
                                <p class="stars">
                                    <span>
                                        <a class="star-1" href="#"><?php esc_html_e('1', 'woolentor'); ?></a>
                                        <a class="star-2" href="#"><?php esc_html_e('2', 'woolentor'); ?></a>
                                        <a class="star-3" href="#"><?php esc_html_e('3', 'woolentor'); ?></a>
                                        <a class="star-4" href="#"><?php esc_html_e('4', 'woolentor'); ?></a>
                                        <a class="star-5" href="#"><?php esc_html_e('5', 'woolentor'); ?></a>
                                    </span>
                                </p>
                                <select name="rating" id="rating" required="" style="display: none;">
                                    <option value=""><?php esc_html_e('Rate…', 'woolentor'); ?></option>
                                    <option value="5"><?php esc_html_e('Perfect', 'woolentor'); ?></option>
                                    <option value="4"><?php esc_html_e('Good', 'woolentor'); ?></option>
                                    <option value="3"><?php esc_html_e('Average', 'woolentor'); ?></option>
                                    <option value="2"><?php esc_html_e('Not that bad', 'woolentor'); ?></option>
                                    <option value="1"><?php esc_html_e('Very poor', 'woolentor'); ?></option>
                                </select>
                                </div>
                                <p class="comment-form-comment">
                                    <label for="comment">
                                        <?php esc_html_e('Your review', 'woolentor'); ?><span class="required"><?php esc_html_e('*', 'woolentor'); ?></span>
                                    </label>
                                    <textarea id="comment" name="comment" cols="45" rows="8" required=""></textarea>
                                </p>
                                <p class="form-submit">
                                    <input name="submit" type="submit" id="submit" class="submit" value="<?php esc_attr_e('Submit', 'woolentor'); ?>">
                                    <input type="hidden" name="comment_post_ID" value="307" id="comment_post_ID">
                                    <input type="hidden" name="comment_parent" id="comment_parent" value="0">
                                </p>
                            </form>
                        </div>
                        <!-- #respond -->
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <?php
        } else{
            if ( empty( $product ) ) { return; }
            add_filter( 'comments_template', array( 'WC_Template_Loader', 'comments_template_loader' ) );
            echo '<div class="woocommerce-tabs-list">';
                comments_template();
            echo '</div>';
        }

    }

}
