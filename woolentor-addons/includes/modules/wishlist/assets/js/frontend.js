;(function($){
"use strict";
    
    var $body = $('body');

    /**
     * Run wishlist mutations one at a time.
     *
     * A guest's wishlist lives entirely in a cookie, and every add/remove/quantity request
     * rewrites the whole of it server side: read $_COOKIE, change one entry, send the result
     * back as Set-Cookie. Two requests in flight at once therefore both read the *same*
     * pre-change cookie, and whichever response lands last overwrites the other. Clicking two
     * wishlist buttons in quick succession silently loses the first product that way.
     *
     * Chaining the requests means each one is sent only after the previous Set-Cookie has been
     * applied, so it always reads the cookie its predecessor wrote.
     */
    let wishSuiteChain = $.Deferred().resolve().promise();
    const wishSuiteQueue = function( task ){
        const result = wishSuiteChain.then( task, task );
        // Swallow failures on the stored chain, otherwise one failed request stalls every
        // mutation that follows it.
        wishSuiteChain = result.then( function(){}, function(){} );
        return result;
    };

    // Add product in wishlist table
    if( 'on' !== WishSuite.option_data['btn_limit_login_off'] ){
        $body.on('click', 'a.wishsuite-btn', function (e) {
            var $this = $(this),
                id = $this.data('product_id'),
                addedText = $this.data('added-text');

            e.preventDefault();

            if( $this.hasClass('loading') ){
                return;
            }
            $this.addClass('loading');

            wishSuiteQueue(function(){
                return $.ajax({
                    url: WishSuite.ajaxurl,
                    data: {
                        action: 'wishsuite_add_to_list',
                        id: id,
                        nonce: WishSuite.wsnonce
                    },
                    dataType: 'json',
                    method: 'GET',
                }).done(function ( response ) {
                    // add_to_wishlist() answers with wp_send_json_success() even when it stored
                    // nothing, and only sends item_count when the product really went in, so
                    // that is what "added" has to be keyed on.
                    if ( response && response.success && response.data && 'undefined' !== typeof response.data.item_count ) {
                        $this.removeClass('wishsuite-btn loading').addClass('added');
                        $this.html( addedText );
                        $body.find('.wishsuite-counter').html( response.data.item_count );
                    } else {
                        $this.removeClass('loading');
                    }
                }).fail(function ( response ) {
                    // Leave the button untouched so the visitor can retry. This used to run
                    // through jQuery's complete callback, which fires on failure too and so
                    // reported products as added that were never stored.
                    $this.removeClass('loading');
                    console.log('Something wrong with AJAX response.', response );
                });
            });

        });
    }

    /**
     * AJAX Request for Remove item
     * @param {*} $this 
     * @param {*} $table 
     * @param {*} productId 
     * @param {*} message 
     */
    const wishSuiteItemRemove = ($this, $table, productId, message = '')=>{
        $table.addClass('loading');

        // Queued for the same reason as the add request: it rewrites the whole guest cookie.
        wishSuiteQueue(function(){
        return $.ajax({
            url: WishSuite.ajaxurl,
            data: {
                action: 'wishsuite_remove_from_list',
                id: productId,
                nonce: WishSuite.wsnonce
            },
            dataType: 'json',
            method: 'GET',
            success: function (response) {
                if ( response ) {

                    let totalPage = Math.ceil(response.data.item_count / response.data.per_page);
                    let currentUrl = window.location.href;
                    let newUrl = wishSuiteGetPageNumberFromUrl(currentUrl) >= totalPage ? currentUrl.replace(/(\/page\/)(\d+)/, '$1' + (totalPage == 0 ? 1 : totalPage)) : currentUrl;

                    if( wishSuiteGetPageNumberFromUrl(currentUrl) == totalPage ){
                        var target_row = $this.closest('tr');
                        target_row.hide(400, function() {
                            $(this).remove();
                            var table_row = $('.wishsuite-table-content table tbody tr').length;
                            if( table_row == 1 ){
                                $('.wishsuite-table-content table tbody tr.wishsuite-empty-tr').show();
                            }
                        });
                    }
                    $body.find('.wishsuite-counter').html( response.data.item_count );

                    window.history.pushState('page', 'Title', newUrl);
                    wishSuiteDataRegenarate(newUrl);

                } else {
                    console.log( 'Something wrong loading compare data' );
                }
            },
            error: function (data) {
                console.log('Something wrong with AJAX response.');
            },
            complete: function () {
                // $table.removeClass('loading');
                // $this.addClass('loading');
            },
        });
        });
    }

    // Remove data from wishlist table
    $body.on('click', 'a.wishsuite-remove', function (e) {
        var $table = $('.wishsuite-table-content');

        e.preventDefault();
        var $this = $(this),
            id = $this.data('product_id');

        wishSuiteItemRemove($this, $table, id);

    });

    /**
     * Ajax Pagination
     */
    $body.on("click",'.wishsuite-table-content .wishsuite-pagination ul li a',function(e){
        e.preventDefault();
        let $this = $(this);
        let requestUrl = $this.attr("href");

        window.history.pushState('page', 'Title', requestUrl);
        wishSuiteDataRegenarate(requestUrl);

    });
    /**
     * Regenerate Wishlist table data from URL
     */
    const wishSuiteDataRegenarate = (requestUrl)=>{
        $('body .wishsuite-table-content').addClass('loading');
        $.ajax({
            url: requestUrl,
            context: document.body
        }).success(function(data) {
            const allHtml = document.createRange().createContextualFragment(data);
            const tableContent = allHtml.querySelector(".wishsuite-table-content");
            $('body .wishsuite-table-content').removeClass('loading');
            $('body .wishsuite-table-content').html(tableContent);
        });
    }
    /**
     * Get Current page number from URL
     * @param {current url} url 
     * @returns Page Number
     */
    const wishSuiteGetPageNumberFromUrl = (url)=> {
        // Extract page number using a regular expression
        let match = url.match(/\/page\/(\d+)/);
        return match ? match[1] : null;
    }

    /**
     * Rebuild the share links from the rows currently on screen, so a quantity the visitor
     * just changed is the quantity their recipient receives.
     */
    const wishSuiteUpdateShareLinks = ()=>{
        const $share = $('.wishsuite-social-share');
        if( !$share.length ){ return; }

        const base  = $share.attr('data-share-base');
        const title = $share.attr('data-title') || '';
        const thumb = $share.attr('data-thumb') || '';
        if( !base ){ return; }

        const ids = [], qtys = [];
        $('.wishsuite-table-content table tbody tr').not('.wishsuite-empty-tr').each(function(){
            const $row = $(this);
            const pid  = $row.find('[data-product_id]').first().attr('data-product_id');
            if( !pid ){ return; }
            const qty = parseFloat( $row.find('input.qty').val() );
            ids.push( pid );
            qtys.push( qty > 0 ? qty : 1 );
        });

        if( !ids.length ){ return; }

        // Commas left literal, matching the PHP built link: they are legal sub-delimiters in a
        // query string and encoding them makes the shared URL unreadable.
        const link = base + ( base.indexOf('?') > -1 ? '&' : '?' )
            + 'wishsuitepids=' + encodeURIComponent( ids.join(',') ).replace( /%2C/g, ',' )
            + '&wishsuiteqty=' + encodeURIComponent( qtys.join(',') ).replace( /%2C/g, ',' );
        const encoded = encodeURIComponent( link );
        const encodedTitle = encodeURIComponent( title );

        const urls = {
            facebook:      'https://www.facebook.com/sharer/sharer.php?u=' + encoded,
            twitter:       'https://twitter.com/share?url=' + encoded + '&text=' + encodedTitle,
            pinterest:     'https://pinterest.com/pin/create/button/?url=' + encoded + '&media=' + thumb,
            linkedin:      'https://www.linkedin.com/shareArticle?mini=true&url=' + encoded + '&title=' + encodedTitle,
            email:         'mailto:?subject=' + encodedTitle + '&body=' + encoded,
            reddit:        'https://reddit.com/submit?url=' + encoded + '&title=' + encodedTitle,
            telegram:      'https://telegram.me/share/url?url=' + encoded,
            odnoklassniki: 'https://connect.ok.ru/offer?url=' + encoded + '&title=' + encodedTitle,
            whatsapp:      'https://wa.me/?text=' + encoded,
            vk:            'https://vk.com/share.php?url=' + encoded
        };

        $share.find('a[data-platform]').each(function(){
            const platform = $(this).attr('data-platform');
            if( urls[ platform ] ){
                $(this).attr('href', urls[ platform ]);
            }
        });

        $share.find('.wishsuite-copy-link').attr('data-clipboard', link);
    };

    // Quentity
    let wishSuiteQuantityTimer = null;
    $("div.wishsuite-table-content").on("change", "input.qty", function() {
        const $input = $(this);
        $input.closest('tr').find( "[data-quantity]" ).attr( "data-quantity", this.value );

        wishSuiteUpdateShareLinks();

        // Persist it so the chosen quantity survives a page refresh.
        clearTimeout(wishSuiteQuantityTimer);
        const productId = $input.closest('tr').find('[data-product_id]').first().data('product_id');
        const newQty = parseFloat($input.val());
        if( productId && newQty > 0 ){
            wishSuiteQuantityTimer = setTimeout(function(){
                // Queued: this rewrites the whole guest cookie as well.
                wishSuiteQueue(function(){
                    return $.ajax({
                        url: WishSuite.ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'wishsuite_update_quantity',
                            id: productId,
                            quantity: newQty,
                            nonce: WishSuite.wsnonce
                        }
                    });
                });
            }, 500);
        }
    });

    /**
     * Copy the shareable wishlist link to the clipboard.
     */
    const wishSuiteFallbackCopy = ( text )=>{
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch(err){ ok = false; }
        document.body.removeChild(area);
        return ok;
    };

    $body.on('click', '.wishsuite-copy-link', function(e){
        e.preventDefault();
        const $btn = $(this);
        // attr() not data(): jQuery caches data() on first read, so it would keep handing back
        // the original URL after wishSuiteUpdateShareLinks() rewrites the attribute.
        const link = $btn.attr('data-clipboard');
        if( !link ){ return; }

        const original = $btn.attr('data-tooltip') || '';
        const copied   = $btn.attr('data-copied') || 'Copied';

        const showCopied = function(){
            $btn.addClass('wishsuite-copied').attr('data-tooltip', copied).attr('aria-label', copied);
            setTimeout(function(){
                $btn.removeClass('wishsuite-copied').attr('data-tooltip', original).attr('aria-label', original);
            }, 2000);
        };

        // navigator.clipboard is undefined on plain HTTP, so keep the execCommand fallback.
        if( navigator.clipboard && navigator.clipboard.writeText ){
            navigator.clipboard.writeText(link).then(showCopied).catch(function(){
                if( wishSuiteFallbackCopy(link) ){ showCopied(); }
            });
        } else if( wishSuiteFallbackCopy(link) ){
            showCopied();
        }
    });

    // Delete table row after added to cart
    $(document).on('added_to_cart',function( e, fragments, carthash, button ){
        if( 'on' === WishSuite.option_data['after_added_to_cart'] ){

            let $table = $('.wishsuite-table-content');
            let product_id = button.data('product_id');
            wishSuiteItemRemove(button, $table, product_id);

        }
    });

    /**
     * Variation Product Add to cart from wishsuite page
     */
    $(document).on( 'click', '.wishsuite_table .product_type_variable.add_to_cart_button', function (e) {
        e.preventDefault();

        var $this = $(this),
            $product = $this.parents('.wishsuite-product-add_to_cart').first(),
            $content = $product.find('.wishsuite-quick-cart-form'),
            id = $this.data('product_id'),
            btn_loading_class = 'loading';

        if ($this.hasClass(btn_loading_class)) return;

        // Show Form
        if ( $product.hasClass('quick-cart-loaded') ) {
            $product.addClass('quick-cart-open');
            return;
        }

        var data = {
            action: 'wishsuite_quick_variation_form',
            id: id,
            nonce: WishSuite.wsnonce
        };
        $.ajax({
            type: 'post',
            url: WishSuite.ajaxurl,
            data: data,
            beforeSend: function (response) {
                $this.addClass(btn_loading_class);
                $product.addClass('loading-quick-cart');
            },
            success: function (response) {
                $content.append( response );
                wishsuite_render_variation_data( $product );
                wishsuite_inser_to_cart();
            },
            complete: function (response) {
                setTimeout(function () {
                    $this.removeClass(btn_loading_class);
                    $product.removeClass('loading-quick-cart');
                    $product.addClass('quick-cart-open quick-cart-loaded');
                }, 100);
            },
        });

        return false;

    });

    $(document).on('click', '.wishsuite-quick-cart-close', function () {
        var $this = $(this),
            $product = $this.parents('.wishsuite-product-add_to_cart');
        $product.removeClass('quick-cart-open');
    });

    $(document.body).on('added_to_cart', function ( e, fragments, carthash, button ) {

        var target_row = $(button).closest('tr') || button.closest('tr');
        target_row.find('.wishsuite-addtocart').addClass('added');
        $('.wishsuite-product-add_to_cart').removeClass('quick-cart-open');

    });

    /**
     * [wishsuite_render_variation_data] show variation data
     * @param  {[selector]} $product
     * @return {[void]} 
     */
    function wishsuite_render_variation_data( $product ) {
        $product.find('.variations_form').wc_variation_form().find('.variations select:eq(0)').change();
        $product.find('.variations_form').trigger('wc_variation_form');
    }

    /**
     * [wishsuite_inser_to_cart] Add to cart
     * @return {[void]}
     */
    function wishsuite_inser_to_cart(){

        $(document).on( 'click', '.wishsuite-quick-cart-form .single_add_to_cart_button:not(.disabled)', function (e) {
            e.preventDefault();

            var $this = $(this),
                $form           = $this.closest('form.cart'),
                product_qty     = $form.find('input[name=quantity]').val() || 1,
                product_id      = $form.find('input[name=product_id]').val() || $this.val(),
                variation_id    = $form.find('input[name=variation_id]').val() || 0;

            $this.addClass('loading');

            /* For Variation product */    
            var item = {},
                variations = $form.find( 'select[name^=attribute]' );
                if ( !variations.length) {
                    variations = $form.find( '[name^=attribute]:checked' );
                }
                if ( !variations.length) {
                    variations = $form.find( 'input[name^=attribute]' );
                }

                variations.each( function() {
                    var $thisitem = $( this ),
                        attributeName = $thisitem.attr( 'name' ),
                        attributevalue = $thisitem.val(),
                        index,
                        attributeTaxName;
                        $thisitem.removeClass( 'error' );
                    if ( attributevalue.length === 0 ) {
                        index = attributeName.lastIndexOf( '_' );
                        attributeTaxName = attributeName.substring( index + 1 );
                        $thisitem.addClass( 'required error' );
                    } else {
                        item[attributeName] = attributevalue;
                    }
                });

            var data = {
                action: 'wishsuite_insert_to_cart',
                product_id: product_id,
                product_sku: '',
                quantity: product_qty,
                variation_id: variation_id,
                variations: item,
                nonce: WishSuite.wsnonce
            };

            $( document.body ).trigger('adding_to_cart', [$this, data]);

            $.ajax({
                type: 'post',
                url:  WishSuite.ajaxurl,
                data: data,

                beforeSend: function (response) {
                    $this.removeClass('added').addClass('loading');
                },

                complete: function (response) {
                    $this.addClass('added').removeClass('loading');
                },

                success: function (response) {
                    if ( response.error & response.product_url ) {
                        window.location = response.product_url;
                        return;
                    } else {
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $this]);
                    }
                },

            });

            return false;
        });

    }

    
    var wishsuite_default_data = {
        price_html:'',
        image_html:'',
    };
    $(document).on('show_variation', '.wishsuite_table .variations_form', function ( alldata, attributes, status ) {

        var target_row = alldata.target.closest('tr');

        // Get First image data
        if( typeof wishsuite_default_data.price_html !== 'undefined' && wishsuite_default_data.price_html.length === 0 ){
            wishsuite_default_data.price_html = $(target_row).find('.wishsuite-product-price').html();
            wishsuite_default_data.image_html = $(target_row).find('.wishsuite-product-image').html();
        }

        // Set variation data
        $(target_row).find('.wishsuite-product-price').html( attributes.price_html );
        wishsuite_variation_image_set( target_row, attributes.image );

        // reset data
        wishsuite_variation_data_reset( target_row, wishsuite_default_data );

    });

    // Reset data
    function wishsuite_variation_data_reset( target_row, default_data ){
        $( target_row ).find('.reset_variations').on('click', function(e){
            $(target_row).find('.wishsuite-product-price').html( default_data.price_html );
            $(target_row).find('.wishsuite-product-image').html( default_data.image_html );
        });
    }

    // variation image set
    function wishsuite_variation_image_set( target_row, image ){
        $(target_row).find('.wishsuite-product-image img').wc_set_variation_attr('src',image.full_src);
        $(target_row).find('.wishsuite-product-image img').wc_set_variation_attr('srcset',image.srcset);
        $(target_row).find('.wishsuite-product-image img').wc_set_variation_attr('sizes',image.sizes);
    }

    /**
     * Keep the wishlist table in sync after a back/forward-cache restore.
     *
     * A guest's wishlist lives in a cookie, so the table is only correct for as long as that
     * cookie matches the one that was present when PHP rendered the page. Logged in visitors
     * never notice a mismatch: WP::send_headers() sends `no-store` for them, which makes the
     * page ineligible for the bfcache, so it is always re-rendered. Guests get no Cache-Control
     * header at all, so the browser restores the old DOM and the item they just added is
     * missing until they reload by hand.
     *
     * The bfcache preserves the JS heap along with the DOM, so the cookie value captured here
     * at render time survives the restore and can be compared against the live one. Comparing
     * cookies rather than the rendered rows keeps this correct on paginated tables.
     */
    const wishSuiteReadCookie = () => {
        const name = ( WishSuite.cookie_name || 'wishsuite_item_list' ) + '=';
        const found = document.cookie.split('; ').find( c => c.indexOf( name ) === 0 );
        return found ? found.slice( name.length ) : '';
    };

    const wishSuiteRenderedCookie = wishSuiteReadCookie();

    window.addEventListener( 'pageshow', function( event ){
        // Only a bfcache restore serves a DOM that PHP never had a chance to refresh.
        if ( ! event.persisted ) {
            return;
        }
        if ( ! document.querySelector('.wishsuite-table-content') ) {
            return;
        }
        if ( wishSuiteReadCookie() === wishSuiteRenderedCookie ) {
            return;
        }
        window.location.reload();
    });

})(jQuery);