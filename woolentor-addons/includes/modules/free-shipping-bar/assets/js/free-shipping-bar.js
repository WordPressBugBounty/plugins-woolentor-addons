/**
 * WooLentor – Free Shipping Bar
 * Vanilla JS, no jQuery dependency.
 *
 * Reads configuration from the `wlFSB` object injected by wp_localize_script.
 */
( function () {
    'use strict';

    var cfg = window.wlFSB || {};

    // Bail if config is missing or no threshold is set
    if ( ! cfg || ! cfg.threshold || parseFloat( cfg.threshold ) <= 0 ) {
        return;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Format a number as a WooCommerce-style price string.
     *
     * @param  {number} amount
     * @return {string}
     */
    function formatPrice( amount ) {
        var decimals   = parseInt( cfg.decimals, 10 ) || 2;
        var decSep     = cfg.decimalSep   || '.';
        var thousSep   = cfg.thousandSep  || ',';
        var symbol     = cfg.currencySymbol || '$';
        var format     = cfg.priceFormat  || '%1$s%2$s'; // %1$s = symbol, %2$s = amount

        var fixed = parseFloat( amount ).toFixed( decimals );
        var parts = fixed.split( '.' );
        parts[0]  = parts[0].replace( /\B(?=(\d{3})+(?!\d))/g, thousSep );
        var formatted = parts.join( decSep );

        return format
            .replace( '%1$s', symbol )
            .replace( '%2$s', formatted );
    }

    /**
     * Build the bar message, replacing the {amount} placeholder.
     *
     * @param  {number}  remaining
     * @param  {boolean} isComplete
     * @return {string}
     */
    function buildMessage( remaining, isComplete, bar ) {
        if ( isComplete ) {
            if ( bar && bar.dataset.msgSuccess ) {
                return bar.dataset.msgSuccess;
            }
            return cfg.msgSuccess || '🎉 You\'ve unlocked FREE shipping!';
        }
        var template = cfg.msgInitial || 'Spend {amount} more to get FREE shipping!';
        if ( bar && bar.dataset.msgInitial ) {
            template = bar.dataset.msgInitial;
        }
        return template.replace( '{amount}', formatPrice( remaining ) );
    }

    // -------------------------------------------------------------------------
    // Device targeting
    // -------------------------------------------------------------------------

    function isDeviceAllowed() {
        var device = cfg.showOnDevice || 'all';
        if ( device === 'all' ) return true;

        var isMobile = window.innerWidth <= 768;
        if ( device === 'mobile' && isMobile )  return true;
        if ( device === 'desktop' && ! isMobile ) return true;
        return false;
    }

    // -------------------------------------------------------------------------
    // Admin bar height
    // -------------------------------------------------------------------------

    function getAdminBarHeight() {
        var el = document.getElementById( 'wpadminbar' );
        return el ? el.offsetHeight : 0;
    }

    // -------------------------------------------------------------------------
    // Fixed-header detection
    // -------------------------------------------------------------------------

    var _fixedEls = [];

    function offsetFixedHeaders( barH ) {
        _fixedEls = [];
        var seen = [];

        var candidates = document.querySelectorAll(
            'header, #header, #masthead, #site-header, #page-header,' +
            ' .site-header, .page-header, .main-header, .header-main,' +
            ' .sticky-header, .fixed-header, .l-header, .c-header,' +
            ' [data-elementor-type="header"]'
        );

        for ( var i = 0; i < candidates.length; i++ ) {
            var el = candidates[ i ];
            if ( seen.indexOf( el ) !== -1 ) continue;
            seen.push( el );

            var cs = window.getComputedStyle( el );
            if ( cs.position !== 'fixed' ) continue;

            var origTop = parseFloat( cs.top ) || 0;
            if ( el.getBoundingClientRect().top > barH + 60 ) continue;

            el.style.top = ( origTop + barH ) + 'px';
            _fixedEls.push( { el: el, origTop: origTop } );
        }
    }

    function restoreFixedHeaders() {
        for ( var i = 0; i < _fixedEls.length; i++ ) {
            _fixedEls[ i ].el.style.top = _fixedEls[ i ].origTop + 'px';
        }
        _fixedEls = [];
    }

    // -------------------------------------------------------------------------
    // Body / document offset (per-bar)
    // -------------------------------------------------------------------------

    function applyBodyOffset( bar ) {
        restoreFixedHeaders();

        var barH   = bar.offsetHeight;
        var adminH = getAdminBarHeight();

        if ( cfg.position === 'bottom' ) {
            document.body.style.paddingBottom = barH + 'px';
        } else if ( adminH > 0 ) {
            document.body.style.paddingTop = barH + 'px';
        } else {
            document.documentElement.style.marginTop = barH + 'px';
            offsetFixedHeaders( barH );
        }
    }

    function removeBodyOffset() {
        document.body.style.paddingTop           = '';
        document.body.style.paddingBottom        = '';
        document.documentElement.style.marginTop = '';
        restoreFixedHeaders();
    }

    // -------------------------------------------------------------------------
    // Positioning (per-bar)
    // -------------------------------------------------------------------------

    function setBarTop( bar ) {
        if ( cfg.position !== 'bottom' ) {
            bar.style.top = getAdminBarHeight() + 'px';
        }
    }

    function positionBar( bar ) {
        bar.classList.remove( 'wl-fsb-top', 'wl-fsb-bottom' );
        if ( cfg.position === 'bottom' ) {
            bar.classList.add( 'wl-fsb-bottom' );
        } else {
            bar.classList.add( 'wl-fsb-top' );
            setBarTop( bar );
        }
    }

    // -------------------------------------------------------------------------
    // Core update logic (per-bar)
    // -------------------------------------------------------------------------

    function updateBar( bar, cartTotal ) {
        var msgEl   = bar.querySelector( '.wl-fsb-message' );
        var fillEl  = bar.querySelector( '.wl-fsb-progress-fill' );
        var trackEl = bar.querySelector( '.wl-fsb-progress-track' );

        var threshold  = parseFloat( cfg.threshold );
        var total      = parseFloat( cartTotal ) || 0;
        var remaining  = Math.max( 0, threshold - total );
        var pct        = Math.min( 100, ( total / threshold ) * 100 );
        var isComplete = remaining === 0;

        if ( msgEl )   msgEl.innerHTML = buildMessage( remaining, isComplete, bar );
        if ( fillEl )  fillEl.style.width = pct.toFixed( 2 ) + '%';
        if ( trackEl ) trackEl.setAttribute( 'aria-valuenow', Math.round( pct ) );

        bar.classList.toggle( 'wl-fsb-complete', isComplete );
    }

    // -------------------------------------------------------------------------
    // Show / hide (per-bar)
    // -------------------------------------------------------------------------

    function showBar( bar ) {
        bar.classList.remove( 'wl-fsb-hidden' );
        if ( ! bar.classList.contains( 'wl-fsb-inline' ) ) {
            applyBodyOffset( bar );
        }
    }

    function hideBar( bar ) {
        bar.classList.add( 'wl-fsb-hidden' );
        if ( ! bar.classList.contains( 'wl-fsb-inline' ) ) {
            removeBodyOffset();
        }
    }

    // -------------------------------------------------------------------------
    // AJAX: fetch fresh cart total
    // -------------------------------------------------------------------------

    function fetchCartTotal( callback ) {
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', cfg.ajaxUrl, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
        xhr.onload = function () {
            if ( xhr.status === 200 ) {
                try {
                    var resp = JSON.parse( xhr.responseText );
                    if ( resp.success && resp.data && resp.data.cart_total !== undefined ) {
                        callback( resp.data.cart_total );
                    }
                } catch ( e ) { /* silent */ }
            }
        };
        xhr.send( 'action=woolentor_fsb_cart_total&nonce=' + encodeURIComponent( cfg.nonce ) );
    }

    // -------------------------------------------------------------------------
    // Session dismiss
    // -------------------------------------------------------------------------

    var SESSION_KEY = 'wl_fsb_dismissed';

    function isDismissed() {
        try {
            return sessionStorage.getItem( SESSION_KEY ) === '1';
        } catch ( e ) {
            return false;
        }
    }

    function setDismissed() {
        try {
            sessionStorage.setItem( SESSION_KEY, '1' );
        } catch ( e ) { /* silent */ }
    }

    // -------------------------------------------------------------------------
    // WooCommerce cart events (global, run once)
    // -------------------------------------------------------------------------

    var _refreshTimer = null;

    function triggerRefresh() {
        clearTimeout( _refreshTimer );
        _refreshTimer = setTimeout( function () {
            document.dispatchEvent( new CustomEvent( 'wl_fsb_cart_changed' ) );
        }, 150 );
    }

    function proxyCartEvents() {
        var jqEvents = [
            'added_to_cart',
            'removed_from_cart',
            'updated_cart_totals',
            'wc_cart_totals_refreshed',
            'wc_fragments_refreshed',
            'wc_fragments_loaded',
            'updated_wc_div',
        ];

        if ( typeof jQuery !== 'undefined' ) {
            jqEvents.forEach( function ( evt ) {
                jQuery( document.body ).on( evt, triggerRefresh );
            } );
        }

        var cartTotalsEl = document.querySelector( '.cart_totals' );
        if ( cartTotalsEl && cartTotalsEl.parentNode ) {
            new MutationObserver( function ( mutations ) {
                for ( var i = 0; i < mutations.length; i++ ) {
                    if ( mutations[ i ].addedNodes.length || mutations[ i ].removedNodes.length ) {
                        triggerRefresh();
                        return;
                    }
                }
            } ).observe( cartTotalsEl.parentNode, { childList: true, subtree: false } );
        }

        if ( window.wp && window.wp.data ) {
            var prevBlockTotal = null;
            window.wp.data.subscribe( function () {
                var selector = window.wp.data.select( 'wc/store/cart' );
                if ( ! selector ) return;

                var totals = selector.getCartTotals();
                if ( ! totals ) return;

                var current = totals.total_price;
                if ( prevBlockTotal !== null && prevBlockTotal !== current ) {
                    triggerRefresh();
                }
                prevBlockTotal = current;
            } );
        }

        var blockSummary = document.querySelector(
            '.wc-block-cart__totals, .wp-block-woocommerce-cart-order-summary-block'
        );
        if ( blockSummary ) {
            new MutationObserver( triggerRefresh ).observe(
                blockSummary,
                { childList: true, subtree: false }
            );
        }
    }

    // -------------------------------------------------------------------------
    // Per-bar initialisation
    // -------------------------------------------------------------------------

    function initBar( bar ) {
        var isInline = bar.classList.contains( 'wl-fsb-inline' );
        var closeBtn = bar.querySelector( '.wl-fsb-close' );

        if ( ! isInline ) {
            document.body.appendChild( bar );
            positionBar( bar );
        }

        updateBar( bar, cfg.cartTotal || 0 );
        showBar( bar );

        if ( closeBtn ) {
            closeBtn.addEventListener( 'click', function () {
                setDismissed();
                hideBar( bar );
            } );
        }
    }

    // -------------------------------------------------------------------------
    // Bootstrap all bars
    // -------------------------------------------------------------------------

    function initAllBars() {
        if ( ! isDeviceAllowed() ) return;
        if ( isDismissed() )       return;

        var bars = document.querySelectorAll( '.wl-fsb-wrap' );
        for ( var i = 0; i < bars.length; i++ ) {
            initBar( bars[ i ] );
        }

        proxyCartEvents();

        document.addEventListener( 'wl_fsb_cart_changed', function () {
            fetchCartTotal( function ( freshTotal ) {
                var allBars = document.querySelectorAll( '.wl-fsb-wrap' );
                for ( var j = 0; j < allBars.length; j++ ) {
                    updateBar( allBars[ j ], freshTotal );
                }
            } );
        } );

        window.addEventListener( 'resize', function () {
            if ( ! isDeviceAllowed() ) {
                var allBars = document.querySelectorAll( '.wl-fsb-wrap' );
                for ( var k = 0; k < allBars.length; k++ ) {
                    hideBar( allBars[ k ] );
                }
            } else if ( ! isDismissed() ) {
                var allBars = document.querySelectorAll( '.wl-fsb-wrap' );
                for ( var k = 0; k < allBars.length; k++ ) {
                    var b = allBars[ k ];
                    if ( ! b.classList.contains( 'wl-fsb-inline' ) ) {
                        setBarTop( b );
                    }
                    showBar( b );
                }
            }
        } );

        function reapplyIfVisible() {
            var allBars = document.querySelectorAll( '.wl-fsb-wrap' );
            for ( var i = 0; i < allBars.length; i++ ) {
                var b = allBars[ i ];
                if ( ! b.classList.contains( 'wl-fsb-inline' ) && ! b.classList.contains( 'wl-fsb-hidden' ) ) {
                    setBarTop( b );
                    applyBodyOffset( b );
                }
            }
        }

        window.addEventListener( 'load', reapplyIfVisible );
        setTimeout( reapplyIfVisible, 300 );
    }

    // Run after DOM is ready
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initAllBars );
    } else {
        initAllBars();
    }

    // -------------------------------------------------------------------------
    // Elementor scoped handler
    // -------------------------------------------------------------------------

    var WoolentorFreeShippingBarHandler = function ( $scope, $ ) {
        var bar = null;
        if( $scope?.detail?.uniqid && $scope?.detail?.uniqid.length !== 0 ) {
            var el = jQuery('.woolentorblock-'+$scope.detail.uniqid);
            bar = el.find( '.wl-fsb-wrap' )[0];
        }else{
            bar = $scope.find( '.wl-fsb-wrap' )[ 0 ];
        };
        if ( ! bar ) return;
        if ( ! cfg || ! cfg.threshold || parseFloat( cfg.threshold ) <= 0 ) return;
        initBar( bar );
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/woolentor_free_shipping_bar.default', WoolentorFreeShippingBarHandler);
    });

    // Gutenberg Editor Mode — cache the AJAX result so re-selecting the block
    // never fires a second request; null means not yet fetched.
    var _editorCartTotal = null;

    document.addEventListener( "wooLentorFreeShippingBar", function ( event ) {
        if ( _editorCartTotal !== null ) {
            cfg.cartTotal = _editorCartTotal;
            WoolentorFreeShippingBarHandler( event );
            return;
        }

        var done = false;
        function doInit() {
            if ( done ) return;
            done = true;
            WoolentorFreeShippingBarHandler( event );
        }

        fetchCartTotal( function ( freshTotal ) {
            _editorCartTotal = freshTotal;
            cfg.cartTotal    = freshTotal;
            doInit();
        } );

        // Fallback: if the AJAX call stalls, init with the preview value anyway.
        setTimeout( doInit, 3000 );
    }, false );

} )();
