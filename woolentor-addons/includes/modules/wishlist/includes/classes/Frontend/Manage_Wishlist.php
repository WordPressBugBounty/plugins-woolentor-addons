<?php
namespace WishSuite\Frontend;
use WooLentor\Traits\Singleton;
/**
 * Manage Wishlist class
 */
class Manage_Wishlist {
    use Singleton;

    /**
     * [products_data_cache] Per request cache for get_products_data().
     * @var array
     */
    private $products_data_cache = array();

    /**
     * Initialize the class
     */
    private function __construct() {
        add_action( 'init', [ $this, 'button_manager' ] );

        // Remove wishlist item after add to cart.
        add_action( 'woocommerce_add_to_cart', [ $this, 'remove_wishlist_after_add_to_cart' ], 10, 6 );

        // Keep the wishlist page out of every speculative/proxy cache. See the two methods below.
        add_filter( 'wp_speculation_rules_href_exclude_paths', [ $this, 'exclude_from_speculative_loading' ] );
        add_action( 'template_redirect', [ $this, 'wishlist_page_no_cache' ] );

    }

    /**
     * [get_wishlist_page_id] The page holding the wishlist table.
     * @return [int]
     */
    public function get_wishlist_page_id(){
        return absint( woolentor_get_option( 'wishlist_page', 'wishsuite_table_settings_tabs' ) );
    }

    /**
     * [exclude_from_speculative_loading] Stop the browser prefetching the wishlist page.
     *
     * WordPress ships Speculative Loading, which prefetches internal links on hover/pointerdown.
     * The wishlist button links to the wishlist page, so the browser fetches that page while the
     * visitor is still adding the product. The prefetched copy is rendered before the wishlist
     * cookie exists, i.e. empty, and it is what the browser then shows when the visitor follows
     * the link, until they reload by hand.
     *
     * Logged in visitors never see it: WP::send_headers() sends `no-store` for them, which makes
     * the prefetched response ineligible for reuse. Guests get no Cache-Control at all.
     *
     * @param  [array] $paths
     * @return [array]
     */
    public function exclude_from_speculative_loading( $paths ){
        $page_id = $this->get_wishlist_page_id();

        if ( $page_id ) {
            $path = wp_parse_url( get_permalink( $page_id ), PHP_URL_PATH );
            if ( $path ) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * [wishlist_page_no_cache] Mark the wishlist page as personalised.
     *
     * Covers the prefetchers and page caches that the speculation rules filter above cannot
     * reach: third party prefetch scripts, proxies and full page cache plugins. `no-store` is
     * deliberately not sent, so the page stays eligible for the back/forward cache; the pageshow
     * handler in frontend.js keeps that case correct.
     *
     * @return [void]
     */
    public function wishlist_page_no_cache(){
        $page_id = $this->get_wishlist_page_id();

        if ( ! $page_id || ! is_page( $page_id ) ) {
            return;
        }

        // Core already sends its own nocache headers, including no-store, for logged in users.
        if ( is_user_logged_in() ) {
            return;
        }

        if ( ! headers_sent() ) {
            header( 'Cache-Control: private, no-cache, must-revalidate, max-age=0' );
        }

        // Honoured by WP Rocket, LiteSpeed, W3TC, WP Super Cache and Batcache.
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
    }

    /**
     * [add_product] Product Add
     * @param [int] $id
     */
    public function add_product( $id ){

        $user_id = get_current_user_id();
        $add_status = false;

        if( $user_id ){

            $args = [
                'product_id' => $id,
                'user_id' => $user_id
            ];

            $insert_id = \WishSuite\Manage_Data::instance()->create( $args );
            $add_status = $insert_id;

        }else{

            $items = $this->get_guest_items();
            $product_id = absint( $id );

            if( $product_id && ! isset( $items[ $product_id ] ) ){
                $items[ $product_id ] = 1;
            }

            $this->save_guest_items( $items );
            $add_status = true;

        }

        $this->products_data_cache = array();

        return $add_status;

    }

    /**
     * [get_guest_cookie_lifetime] Guest wishlist cookie lifetime, in days.
     * @return [int]
     */
    public function get_guest_cookie_lifetime() {
        $days = (int) apply_filters( 'wishsuite_guest_cookie_lifetime_days', 30 );
        return $days > 0 ? $days : 30;
    }

    /**
     * [get_guest_items] Read the guest wishlist cookie as a product_id => quantity map.
     *
     * The cookie historically held a plain list of IDs ( [12,15] ). It now holds a map
     * ( {"12":2,"15":1} ) so quantities survive a page refresh. Both shapes are accepted so
     * visitors with an existing cookie don't lose their wishlist.
     *
     * @return [array]
     */
    public function get_guest_items() {
        $cookie_name = $this->get_cookie_name();

        if ( empty( $_COOKIE[ $cookie_name ] ) ) {
            return array();
        }

        $decoded = json_decode( wp_unslash( $_COOKIE[ $cookie_name ] ), true );

        if ( ! is_array( $decoded ) ) {
            return array();
        }

        // Detect the legacy list shape ( sequential 0..n keys ) vs the id => qty map.
        $is_list = true;
        $index   = 0;
        foreach ( $decoded as $cookie_key => $cookie_value ) {
            if ( $cookie_key !== $index++ ) {
                $is_list = false;
                break;
            }
        }

        $items = array();

        if ( $is_list ) {
            foreach ( $decoded as $cookie_value ) {
                $product_id = absint( $cookie_value );
                if ( $product_id ) {
                    $items[ $product_id ] = 1;
                }
            }
        } else {
            foreach ( $decoded as $cookie_key => $cookie_value ) {
                $product_id = absint( $cookie_key );
                if ( $product_id ) {
                    $items[ $product_id ] = max( 1, absint( $cookie_value ) );
                }
            }
        }

        return $items;
    }

    /**
     * [save_guest_items] Persist the guest wishlist map to the cookie.
     * @param  [array] $items product_id => quantity
     * @return [void]
     */
    public function save_guest_items( $items ) {
        $cookie_name = $this->get_cookie_name();

        if ( empty( $items ) ) {
            // Expire it rather than writing an empty session cookie.
            setcookie( $cookie_name, '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false, false );
            unset( $_COOKIE[ $cookie_name ] );
            return;
        }

        $encoded = wp_json_encode( $items );

        // A 0 expiry made this a session cookie, so guest wishlists were lost as soon as the
        // browser closed. Give it an explicit lifetime instead.
        $expiration = time() + ( $this->get_guest_cookie_lifetime() * DAY_IN_SECONDS );

        setcookie( $cookie_name, $encoded, $expiration, COOKIEPATH, COOKIE_DOMAIN, false, false );
        $_COOKIE[ $cookie_name ] = $encoded;
    }

    /**
     * [update_product_quantity] Persist a wishlist item quantity.
     * @param  [int] $id
     * @param  [int] $quantity
     * @return [mixed]
     */
    public function update_product_quantity( $id, $quantity ){
        $product_id = absint( $id );
        $quantity   = absint( $quantity );

        if ( ! $product_id || $quantity < 1 ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( $user_id ) {
            $updated = \WishSuite\Manage_Data::instance()->update([
                'user_id'    => $user_id,
                'product_id' => $product_id,
                'quantity'   => $quantity,
            ]);
        } else {
            // Guests are stored in the cookie only, so the quantity lives there too.
            $items = $this->get_guest_items();

            if ( ! isset( $items[ $product_id ] ) ) {
                return false;
            }

            $items[ $product_id ] = $quantity;
            $this->save_guest_items( $items );
            $updated = true;
        }

        $this->products_data_cache = array();

        return $updated;
    }

    /**
     * [remove_product]
     * @param  [type] $id
     * @return [void]
     */
    public function remove_product( $id ){
        $user_id = get_current_user_id();
        $delete_status = false;

        if( $user_id ){
            $deleted = \WishSuite\Manage_Data::instance()->delete( $user_id, $id );
            $delete_status = $deleted;
        }else{

            $items      = $this->get_guest_items();
            $product_id = absint( $id );

            if( isset( $items[ $product_id ] ) ){
                unset( $items[ $product_id ] );
                $this->save_guest_items( $items );
                $delete_status = true;
            }else{
                $delete_status = false;
            }

        }

        $this->products_data_cache = array();

        return $delete_status;
    }

    /**
     * [remove_wishlist_after_add_to_cart]
     * @param  [type] $cart_item_key
     * @param  [type] $product_id
     * @param  [type] $quantity
     * @param  [type] $variation_id
     * @param  [type] $variation
     * @param  [type] $cart_item_data
     * @return [type]
     */
    public function remove_wishlist_after_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ){
        if( isset( $product_id ) && 'on' === woolentor_get_option( 'after_added_to_cart', 'wishsuite_table_settings_tabs', 'on' ) ){
            $this->remove_product( $product_id );
        }
    }

    /**
     * [button_manager] Button Manager
     * @return [void]
     */
    public function button_manager(){

        $shop_page_btn_position     = woolentor_get_option( 'shop_btn_position', 'wishsuite_settings_tabs', 'after_cart_btn' );
        $product_page_btn_position  = woolentor_get_option( 'product_btn_position', 'wishsuite_settings_tabs', 'after_cart_btn' );

        $enable_btn         = woolentor_get_option( 'btn_show_shoppage', 'wishsuite_settings_tabs', 'off' );
        $product_enable_btn = woolentor_get_option( 'btn_show_productpage', 'wishsuite_settings_tabs', 'on' );
        
        // Shop Button Position
        if( $shop_page_btn_position != 'use_shortcode' && $enable_btn == 'on' ){
            switch ( $shop_page_btn_position ) {
                case 'before_cart_btn':
                    add_action( 'woocommerce_after_shop_loop_item', [ $this, 'button_print' ], 7 );
                    break;

                case 'top_thumbnail':
                    add_action( 'woocommerce_before_shop_loop_item', [ $this, 'button_print' ], 5 );
                    break;

                case 'custom_position':
                    $hook_name = woolentor_get_option( 'shop_custom_hook_name', 'wishsuite_settings_tabs', '' );
                    $priority = woolentor_get_option( 'shop_custom_hook_priority', 'wishsuite_settings_tabs', 10 );
                    if( !empty( $hook_name ) ){
                        add_action( $hook_name, [ $this, 'button_print' ], $priority );
                    }
                    break;
                
                default:
                    add_action( 'woocommerce_after_shop_loop_item', [ $this, 'button_print' ], 20 );
                    break;
            }
        }
        
        // Product Page Button Position
        if( $product_page_btn_position != 'use_shortcode' && $product_enable_btn == 'on' ){
            switch ( $product_page_btn_position ) {
                case 'before_cart_btn':
                    add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'button_print' ], 20 );
                    break;

                case 'after_thumbnail':
                    add_action( 'woocommerce_product_thumbnails', [ $this, 'button_print' ], 21 );
                    break;

                case 'after_summary':
                    add_action( 'woocommerce_after_single_product_summary', [ $this, 'button_print' ], 11 );
                    break;

                case 'custom_position':
                    $hook_name = woolentor_get_option( 'product_custom_hook_name', 'wishsuite_settings_tabs', '' );
                    $priority = woolentor_get_option( 'product_custom_hook_priority', 'wishsuite_settings_tabs', 10 );
                    if( !empty( $hook_name ) ){
                        add_action( $hook_name, [ $this, 'button_print' ], $priority );
                    }
                    break;
                
                default:
                    add_action( 'woocommerce_single_product_summary', [ $this, 'button_print' ], 31 );
                    break;
            }
        }

    }

    /**
     * [add_button]
     * @return [void]
     */
    public function button_print(){
        echo do_shortcode( '[wishsuite_button]' );
    }

    /**
     * [button_html] Wishlist Button HTML
     * @param  [type] $atts template attr
     * @return [HTML]
     */
    public function button_html( $atts ) {
        $button_attr = apply_filters( 'wishsuite_button_arg', $atts );
        return wishsuite_get_template( 'wishsuite-button-'.$atts['template_name'].'.php', $button_attr, false );
    }

    /**
     * [table_html] Wishlist table HTML
     * @return [HTML]
     */
    public function table_html( $atts ) {
        $table_attr = apply_filters( 'wishsuite_table_arg', $atts );
        return wishsuite_get_template( 'wishsuite-table.php', $table_attr, false );
    }

    /**
     * [counter_html] Wishlist counter HTML
     * @return [HTML]
     */
    public function count_html( $atts ) {
        $count_attr = apply_filters( 'wishsuite_count_arg', $atts );
        return wishsuite_get_template( 'wishsuite-count.php', $count_attr, false );
    }

    /**
     * [get_cookie_name] Get cookie name
     * @return [string]
     */
    public function get_cookie_name() {
        $name = 'wishsuite_item_list';
        if ( is_multisite() ){
            $name .= '_' . get_current_blog_id();
        }
        return $name;
    }
    
    /**
     * [get_wishlist_products]
     * @param  integer $per_page
     * @param  integer $offset
     * @return [array]
     */
    public function get_wishlist_products( $per_page = -1, $offset = 0 ){

        if( is_user_logged_in() ){
            $args = [
                'number'  => $per_page,
                'offset'  => $offset,
            ];
            $items = \WishSuite\Manage_Data::instance()->read( $args );

            $ids = array();
            foreach ( $items as $itemkey => $item ) {
                $ids[] = $item['product_id'];
            }
            return $ids;
        }else{
            // Returned as strings to stay compatible with the strict in_array() check in
            // is_product_in_wishlist().
            return array_map( 'strval', array_keys( $this->get_guest_items() ) );
        }

    }

    /**
     * [is_product_in_wishlist] Check product in list
     * @param  [int] $id [description]
     * @return boolean
     */
    public function is_product_in_wishlist( $id ) {
        $id = (string) $id;
        $list = $this->get_wishlist_products();
        if ( is_array( $list ) ) {
            return in_array( $id, $list, true );
        }else{
            return false;
        }
    }

    /**
     * [get_products_data] generate wishlist products data
     * @return [array] product list
     */
    public function get_products_data( $limit = -1, $page = 1 ) {

        // The table shortcode, the counter, the pagination and the share block all ask for the
        // same data within one request. The share parameters belong in the key because they
        // change the result for the very same limit/page pair.
        $cache_key = $limit . '|' . $page
            . '|' . ( isset( $_GET['wishsuitepids'] ) ? sanitize_text_field( wp_unslash( $_GET['wishsuitepids'] ) ) : '' )
            . '|' . ( isset( $_GET['wishsuiteqty'] ) ? sanitize_text_field( wp_unslash( $_GET['wishsuiteqty'] ) ) : '' );

        if ( isset( $this->products_data_cache[ $cache_key ] ) ) {
            return $this->products_data_cache[ $cache_key ];
        }

        $ids = $this->get_wishlist_products();

        $shared_qty_map = array();
        $shareablebtn = woolentor_get_option( 'enable_social_share','wishsuite_table_settings_tabs','on' );
        if ( ( $shareablebtn === 'on' ) && isset( $_GET['wishsuitepids'] ) ) {
            $query_perametter_ids = sanitize_text_field( wp_unslash( $_GET['wishsuitepids'] ) );
            if( !empty( $query_perametter_ids ) ){
                $ids = explode( ',', $query_perametter_ids );

                // Quantities travel alongside the ids, in the same order, so a shared wishlist
                // arrives with the quantities its owner chose.
                if ( ! empty( $_GET['wishsuiteqty'] ) ) {
                    $shared_qty = explode( ',', sanitize_text_field( wp_unslash( $_GET['wishsuiteqty'] ) ) );
                    foreach ( $ids as $id_index => $shared_id ) {
                        if ( isset( $shared_qty[ $id_index ] ) && absint( $shared_qty[ $id_index ] ) > 0 ) {
                            $shared_qty_map[ absint( $shared_id ) ] = absint( $shared_qty[ $id_index ] );
                        }
                    }
                }
            }
        }

        if ( empty( $ids ) ) {
            return array();
        }

        // Guest quantities live in the cookie, logged in ones in the wishlist table.
        $guest_items = is_user_logged_in() ? array() : $this->get_guest_items();

        $args = array(
            'include' => $ids,
            'limit'   => $limit,
            'page' => $page
        );

        $products = wc_get_products( $args );

        $products_data = array();

        $fields = $this->get_all_fields();

        $fields = array_filter( $fields, function(  $field ) {
            return 'pa_' === substr( $field, 0, 3 );
        }, ARRAY_FILTER_USE_KEY );

        $data_none = '-';

        foreach ( $products as $product ) {

            $rating_count   = $product->get_rating_count();
            $average        = $product->get_average_rating();

            $current_product_id = $product->get_id();

            if ( isset( $shared_qty_map[ $current_product_id ] ) ) {
                $min_value = $shared_qty_map[ $current_product_id ];
            } elseif ( ! empty( $guest_items[ $current_product_id ] ) ) {
                $min_value = $guest_items[ $current_product_id ];
            } else {
                $get_row = \WishSuite\Manage_Data::instance()->read_single_item( get_current_user_id(), $current_product_id );
                if( is_object( $get_row ) && $get_row->quantity ){
                    $min_value = $get_row->quantity;
                }else{
                    $min_value = $product->get_min_purchase_quantity();
                }
            }
            $quantity_args = array(
                'input_value' => $min_value,
                'min_value'   => $product->get_min_purchase_quantity(),
                'max_value'   => $product->get_max_purchase_quantity(),
            );

            $products_data[ $product->get_id() ] = array(
                'id'            => $product->get_id(),
                'remove'        => $product->get_id(),
                'image'         => $product->get_image() ? $product->get_image('wishsuite-image') : $data_none,
                'title'         => $product->get_title() ? $product->get_title() : $data_none,
                'image_id'      => $product->get_image_id(),
                'permalink'     => $product->get_permalink(),
                'price'         => $product->get_price_html() ? $product->get_price_html() : $data_none,
                'rating'        => wc_get_rating_html( $average, $rating_count ),
                'add_to_cart'   => $this->add_to_cart_html( $product, $min_value ) ? $this->add_to_cart_html( $product, $min_value ) : $data_none,
                'quantity'      => woocommerce_quantity_input( $quantity_args, $product, false ),
                'quantity_value'=> $min_value,
                'dimensions'    => wc_format_dimensions( $product->get_dimensions( false ) ),
                'description'   => $product->get_short_description() ? $product->get_short_description() : $data_none,
                'weight'        => $product->get_weight() ? $product->get_weight() : $data_none,
                'sku'           => $product->get_sku() ? $product->get_sku() : $data_none,
                'availability'  => $this->availability_html( $product ),
            );

            foreach ( $fields as $field_id => $field_name ) {
                if ( taxonomy_exists( $field_id ) ) {
                    $products_data[ $product->get_id() ][ $field_id ] = array();
                    $terms = get_the_terms( $product->get_id(), $field_id );
                    if ( ! empty( $terms ) ) {
                        foreach ( $terms as $term ) {
                            $term = sanitize_term( $term, $field_id );
                            $products_data[ $product->get_id() ][ $field_id ][] = $term->name;
                        }
                    } else {
                        $products_data[ $product->get_id() ][ $field_id ][] = '-';
                    }
                    $products_data[ $product->get_id() ][ $field_id ] = implode( ', ', $products_data[ $product->get_id() ][ $field_id ] );
                }
            }

        }

        $this->products_data_cache[ $cache_key ] = $products_data;

        return $products_data;
    }

    /**
     * [get_all_fields] Table field list
     * @return [array] Table Field list
     */
    public function get_all_fields() {
        
        $default_show = array(
            'remove'        => esc_html__( 'Remove', 'wishsuite' ),
            'image'         => esc_html__( 'Image', 'wishsuite' ),
            'title'         => esc_html__( 'Title', 'wishsuite' ),
            'price'         => esc_html__( 'Price', 'wishsuite' ),
            'quantity'      => esc_html__( 'Quantity', 'wishsuite' ),
            'add_to_cart'   => esc_html__( 'Add To Cart', 'wishsuite' ),
        );

        $fields_settings = woolentor_get_option( 'show_fields', 'wishsuite_table_settings_tabs' );

        if ( isset( $fields_settings ) && ( is_array( $fields_settings ) ) && count( $fields_settings ) > 1 ) {
            $fields = $fields_settings;
        }else{
            $fields = $default_show;
        }

        // A shared wishlist belongs to whoever sent it, so the visitor viewing it has nothing
        // of their own to remove.
        $shareablebtn = woolentor_get_option( 'enable_social_share', 'wishsuite_table_settings_tabs', 'on' );
        if ( ( 'on' === $shareablebtn ) && isset( $_GET['wishsuitepids'] ) ) {
            unset( $fields['remove'] );
        }

        return $fields;
    }

    /**
     * [is_products_have_field]
     * @param  [string]  $field_id 
     * @param  [object]  $products
     * @return boolean   
     */
    public function is_products_have_field( $field_id, $products ) {
        foreach ( $products as $product_id => $product ) {
            if ( isset( $product[ $field_id ] ) && ( ! empty( $product[ $field_id ] ) && '-' !== $product[ $field_id ] && 'N/A' !== $product[ $field_id ] ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * [display_field]
     * @param  [string] $field_id
     * @param  [array] $product
     * @return [html] 
     */
    public function display_field( $field_id, $product ) {

        $type = $field_id;

        if ( 'pa_' === substr( $field_id, 0, 3 ) ) {
            $type = 'attribute';
        }
        
        switch ( $type ) {
            case 'remove':
                ?>
                    <a href="#" class="wishsuite-remove" data-product_id="<?php echo esc_attr( $product['id'] ); ?>">&nbsp;</a>
                <?php
                break;

            case 'image':
                ?>
                    <a href="<?php echo esc_url(get_permalink( $product['id'] )); ?>"> <?php echo wp_kses_post($product['image']); ?> </a>
                <?php
                break;

            case 'title':
                echo '<a href="'.esc_url(get_permalink( $product['id'] )).'">'.$product[ $field_id ].'</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;

            case 'price':
                echo wp_kses_post( $product[ $field_id ] );
                break;

            case 'quantity':
                echo $product[ $field_id ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;

            case 'ratting':
                echo '<span class="wishsuite-product-ratting">'.wp_kses_post( $product[ $field_id ] ).'</span>';
                break;

            case 'add_to_cart':
                echo apply_filters( 'wishsuite_add_to_cart_btn', $product[ $field_id ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;

            case 'attribute':
                echo wp_kses_post( $product[ $field_id ] );
                break;

            case 'weight':
                if ( $product[ $field_id ] ) {
                    $unit = $product[ $field_id ] !== '-' ? get_option( 'woocommerce_weight_unit' ) : '';
                    echo wc_format_localized_decimal( $product[ $field_id ] ) . ' ' . esc_attr( $unit ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } 
                break;

            case 'description':
                echo apply_filters( 'woocommerce_short_description', $product[ $field_id ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                break;

            default:
                echo wp_kses_post( $product[ $field_id ] );
                break;
        }


    }

    /**
     * [field_name]
     * @param  [string] $field
     * @return [string] 
     */
    public function field_name( $field, $custom = false ){
        return wishsuite_field_name( $field, $custom );
    }

    /**
     * [add_to_cart_html]
     * @param [object] $product
     */
    public function add_to_cart_html( $product, $quentity ) {
        if ( ! $product ) return;

        $btn_class = 'wishsuite-addtocart button product_type_' . $product->get_type();

        $btn_class .= $product->is_purchasable() && $product->is_in_stock() ? ' add_to_cart_button' : '';

        $btn_class .= $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? ' ajax_add_to_cart' : '';

        $cart_btn = $product->add_to_cart_text();

        ob_start();

        if( 'variable' === $product->get_type() ):
        ?>
            <div class="wishsuite-quick-cart-area">
                <div class="wishsuite-quick-cart-close">
                    <span>&#10005;</span>
                </div>
                <div class="wishsuite-quick-cart-form"></div>
            </div>
        <?php endif; ?>
            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="<?php echo esc_attr( $quentity ); ?>" class="<?php echo esc_attr($btn_class); ?>" data-product_id="<?php echo esc_attr($product->get_id()); ?>"><?php echo esc_html( $cart_btn );?></a>
        <?php
        return ob_get_clean();

    }

    /**
     * [availability_html]
     * @param  [object] $product
     * @return [html]
     */
    public function availability_html( $product ) {
        $html         = '';
        $availability = $product->get_availability();

        if( empty( $availability['availability'] ) ) {
            $availability['availability'] = __( 'In stock', 'woocommerce' );
        }

        if ( ! empty( $availability['availability'] ) ) {
            ob_start();

            wc_get_template( 'single-product/stock.php', array(
                'product'      => $product,
                'class'        => $availability['class'],
                'availability' => $availability['availability'],
            ) );

            $html = ob_get_clean();
        }

        return apply_filters( 'woocommerce_get_stock_html', $html, $product );
    }

    /**
     * [social_media_share]
     * @return [void]
     */
    public function social_share(){

        if( woolentor_get_option( 'enable_social_share','wishsuite_table_settings_tabs','on' ) !== 'on' ){
            return;
        }

        // Built from the rendered rows so the shared link carries the same quantities the
        // visitor is looking at.
        $products = $this->get_products_data();
        $ids      = array();
        $qtys     = array();

        foreach ( $products as $shared_id => $shared_product ) {
            $ids[]  = $shared_id;
            $qtys[] = ! empty( $shared_product['quantity_value'] ) ? absint( $shared_product['quantity_value'] ) : 1;
        }

        $atts = [
            'products_ids' => $ids,
            'products_qty' => $qtys,
        ];
        $social_share_attr = apply_filters( 'wishsuite_social_share_arg', $atts );
        wishsuite_get_template( 'wishsuite-social-share.php', $social_share_attr, true );
        
    }

    /**
     * [pagination]
     * @return void
     */
    public function pagination(){
        $current_page = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
        $total_items = count( \WishSuite\Frontend\Manage_Wishlist::instance()->get_products_data() );
        $product_per_page = woolentor_get_option( 'wishlist_product_per_page', 'wishsuite_table_settings_tabs', 20 );
        $total_pages = ceil($total_items / $product_per_page);
        
        // Only proceed if there are items to paginate
        if ($total_pages > 0) {
            $args = array(
                'base' => str_replace( $total_pages, '%#%', esc_url( get_pagenum_link( $total_pages ) ) ),
                'format' => '?paged=%#%',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $current_page,
                'show_all' => false,
                'end_size' => 1,
                'mid_size' => 2,
                'type' => 'list',
                'add_args' => true,
                'add_fragment' => ''
            );
            
            $pagination = paginate_links($args);
            
            // Only output if pagination links exist
            if (!empty($pagination)) {
                echo '<nav class="wishsuite-pagination">' . wp_kses_post($pagination) . '</nav>';
            }
        }
    }


}