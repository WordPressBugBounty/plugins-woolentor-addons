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
    function buildMessage( remaining, isComplete ) {
        if ( isComplete ) {
            return cfg.msgSuccess || '🎉 You\'ve unlocked FREE shipping!';
        }
        var template = cfg.msgInitial || 'Spend {amount} more to get FREE shipping!';
        return template.replace( '{amount}', formatPrice( remaining ) );
    }

    // -------------------------------------------------------------------------
    // DOM references
    // -------------------------------------------------------------------------

    var bar         = document.getElementById( 'wl-free-shipping-bar' );
    if ( ! bar ) return;

    var msgEl       = bar.querySelector( '.wl-fsb-message' );
    var fillEl      = bar.querySelector( '.wl-fsb-progress-fill' );
    var trackEl     = bar.querySelector( '.wl-fsb-progress-track' );
    var closeBtn    = bar.querySelector( '.wl-fsb-close' );

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
    // Positioning + body offset
    // -------------------------------------------------------------------------

    // Returns the current height of the WP admin bar (0 when not logged in).
    function getAdminBarHeight() {
        var el = document.getElementById( 'wpadminbar' );
        return el ? el.offsetHeight : 0;
    }

    // Sets bar.style.top so it sits flush below the admin bar (top position only).
    // Must be called after positionBar() and whenever the window resizes.
    function setBarTop() {
        if ( cfg.position !== 'bottom' ) {
            bar.style.top = getAdminBarHeight() + 'px';
        }
    }

    // ── Fixed-header detection ────────────────────────────────────
    // Tracks elements whose `top` we adjusted so we can restore them.
    var _fixedEls = [];

    // Scans for position:fixed elements near the top of the page and pushes
    // them below the bar. Called when there is no WP admin bar (guest users),
    // because body.paddingTop / html.marginTop do not move fixed elements —
    // they are anchored to the viewport, not the document.
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
            if ( el === bar || seen.indexOf( el ) !== -1 ) continue;
            seen.push( el );

            var cs = window.getComputedStyle( el );
            if ( cs.position !== 'fixed' ) continue;

            var origTop = parseFloat( cs.top ) || 0;
            // Skip elements that are already below the bar
            if ( el.getBoundingClientRect().top > barH + 60 ) continue;

            el.style.top = ( origTop + barH ) + 'px';
            _fixedEls.push( { el: el, origTop: origTop } );
        }
    }

    // Restores the original `top` values of any elements we shifted.
    function restoreFixedHeaders() {
        for ( var i = 0; i < _fixedEls.length; i++ ) {
            _fixedEls[ i ].el.style.top = _fixedEls[ i ].origTop + 'px';
        }
        _fixedEls = [];
    }

    // ── Body / document offset ────────────────────────────────────
    // Pushes page content so it is never hidden under the bar.
    // Always resets first so it is safe to call multiple times (e.g. resize).
    //
    //  Admin bar present → WP already applies `html { margin-top }`.
    //    We only add `body.paddingTop` to cover our bar's extra height.
    //
    //  No admin bar (guest) → Use `html.marginTop` for in-flow / sticky
    //    headers, PLUS directly shift any position:fixed headers found near
    //    the top of the page.
    function applyBodyOffset() {
        restoreFixedHeaders(); // always start clean before recalculating

        var barH   = bar.offsetHeight;
        var adminH = getAdminBarHeight();

        if ( cfg.position === 'bottom' ) {
            document.body.style.paddingBottom = barH + 'px';
        } else if ( adminH > 0 ) {
            // Admin bar present: WP already sets html { margin-top: adminH } which
            // shifts <body> below the admin bar. We only need barH of additional
            // padding to clear our bar — adding adminH here would double-count it.
            document.body.style.paddingTop = barH + 'px';
        } else {
            // No admin bar: html.marginTop handles in-flow + sticky elements;
            // offsetFixedHeaders handles position:fixed headers.
            document.documentElement.style.marginTop = barH + 'px';
            offsetFixedHeaders( barH );
        }
    }

    // Removes all offsets when the bar is dismissed or hidden.
    function removeBodyOffset() {
        document.body.style.paddingTop           = '';
        document.body.style.paddingBottom        = '';
        document.documentElement.style.marginTop = '';
        restoreFixedHeaders();
    }

    function positionBar() {
        if ( cfg.position === 'bottom' ) {
            bar.classList.add( 'wl-fsb-bottom' );
        } else {
            bar.classList.add( 'wl-fsb-top' );
            setBarTop();
        }
    }

    // -------------------------------------------------------------------------
    // Core update logic
    // -------------------------------------------------------------------------

    /**
     * Update the bar UI based on the current cart total.
     *
     * @param {number} cartTotal
     */
    function updateBar( cartTotal ) {
        var threshold  = parseFloat( cfg.threshold );
        var total      = parseFloat( cartTotal ) || 0;
        var remaining  = Math.max( 0, threshold - total );
        var pct        = Math.min( 100, ( total / threshold ) * 100 );
        var isComplete = remaining === 0;

        msgEl.innerHTML = buildMessage( remaining, isComplete );

        fillEl.style.width = pct.toFixed( 2 ) + '%';
        trackEl.setAttribute( 'aria-valuenow', Math.round( pct ) );

        bar.classList.toggle( 'wl-fsb-complete', isComplete );
    }

    // -------------------------------------------------------------------------
    // AJAX: fetch fresh cart total from the server
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
    // Show / hide
    // -------------------------------------------------------------------------

    function showBar() {
        bar.classList.remove( 'wl-fsb-hidden' );
        applyBodyOffset();
    }

    function hideBar() {
        bar.classList.add( 'wl-fsb-hidden' );
        removeBodyOffset();
    }

    // -------------------------------------------------------------------------
    // WooCommerce cart events
    // -------------------------------------------------------------------------

    var _refreshTimer = null;

    function triggerRefresh() {
        clearTimeout( _refreshTimer );
        _refreshTimer = setTimeout( function () {
            document.dispatchEvent( new CustomEvent( 'wl_fsb_cart_changed' ) );
        }, 150 );
    }

    /**
     * WooCommerce fires jQuery-based events on document.body after classic cart
     * updates. Block cart uses the wp.data store (wc/store/cart). We listen to
     * both plus MutationObserver fallbacks so the bar refreshes in all setups.
     */
    function proxyCartEvents() {
        // ── Classic cart: jQuery events ───────────────────────────────────────
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

        // ── Classic cart: MutationObserver fallback ───────────────────────────
        // Watches the parent of .cart_totals — when WC replaces the element via
        // fragments the parent sees an added/removed child.
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

        // ── Block cart: wp.data store subscription ────────────────────────────
        // WC Blocks updates its Redux-like store on every cart REST API response.
        // Subscribing lets us detect quantity changes without any jQuery events.
        if ( window.wp && window.wp.data ) {
            var prevBlockTotal = null;
            window.wp.data.subscribe( function () {
                var selector = window.wp.data.select( 'wc/store/cart' );
                if ( ! selector ) return;

                var totals = selector.getCartTotals();
                if ( ! totals ) return;

                // total_price is the grand total string in minor units (e.g. "18000").
                // Used purely as a change-detection key; the actual displayed value
                // comes from our own AJAX endpoint.
                var current = totals.total_price;
                if ( prevBlockTotal !== null && prevBlockTotal !== current ) {
                    triggerRefresh();
                }
                prevBlockTotal = current;
            } );
        }

        // ── Block cart: MutationObserver fallback ─────────────────────────────
        // Catches DOM rerenders of the block order-summary when the store
        // subscription hasn't fired yet (e.g. optimistic UI updates).
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
    // Bootstrap
    // -------------------------------------------------------------------------

    function init() {
        if ( ! isDeviceAllowed() ) return;
        if ( isDismissed() )       return;

        // Append bar directly to body (above/below everything)
        document.body.appendChild( bar );
        positionBar();

        // Initial paint with server-side cart total
        updateBar( cfg.cartTotal || 0 );

        // Remove 'hidden' without triggering applyBodyOffset yet — measure after visible
        bar.classList.remove( 'wl-fsb-hidden' );
        applyBodyOffset();

        // Proxy WooCommerce jQuery cart events to native
        proxyCartEvents();

        // Re-fetch cart total on any cart change
        document.addEventListener( 'wl_fsb_cart_changed', function () {
            fetchCartTotal( function ( freshTotal ) {
                updateBar( freshTotal );
            } );
        } );

        // Close button
        if ( closeBtn ) {
            closeBtn.addEventListener( 'click', function () {
                setDismissed();
                hideBar();
            } );
        }

        // Re-check on resize (device targeting + bar height + admin bar height can change)
        window.addEventListener( 'resize', function () {
            if ( ! isDeviceAllowed() ) {
                hideBar();
            } else if ( ! isDismissed() ) {
                // Admin bar switches between 46px (mobile) and 32px (desktop) at 783px.
                // setBarTop() repositions the bar; showBar() re-shows it if it was
                // hidden by the device check and calls applyBodyOffset() internally.
                setBarTop();
                showBar();
            }
        } );

        // Re-apply offsets after all scripts have run.
        //
        // Themes often initialise their sticky/fixed header via JS on 'load' or
        // asynchronously — meaning the header is still position:relative when our
        // DOMContentLoaded init() fires. By the time 'load' fires, all scripts
        // have executed and the header is in its final fixed state.
        // The 300 ms timeout is a fallback for themes that defer header setup
        // past the load event (e.g. lazy-initialised sliders, builder scripts).
        function reapplyIfVisible() {
            if ( ! bar.classList.contains( 'wl-fsb-hidden' ) ) {
                setBarTop();
                applyBodyOffset();
            }
        }

        window.addEventListener( 'load', reapplyIfVisible );
        setTimeout( reapplyIfVisible, 300 );
    }

    // Run after DOM is ready
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
