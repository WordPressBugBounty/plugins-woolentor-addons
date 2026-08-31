/**
 * Product Section — tabs and AJAX pagination for every product widget and block.
 *
 * One behaviour for both builders. It binds on data attributes, never on class names, so an
 * Elementor widget and a Gutenberg block keep their own visual classes and share this file
 * without either knowing about the other.
 *
 * The contract:
 *
 *   [data-wl-section]           the section, carrying provider / post / id / nonce
 *   [data-wl-section-list]      the element the cards live in
 *   [data-wl-section-tab="0"]   a tab button, carrying its index
 *   [data-wl-section-pager]     the pager, replaced wholesale by whatever the server sends back
 *   [data-wl-section-more]      the Load More button
 *   [data-wl-section-observe]   the infinite-scroll sentinel
 *   data-wl-section-next / -max on the button or the sentinel
 *
 * Nothing about the query is held in the page. A tab button carries an index and a pager carries a
 * page number; the endpoint reads the settings back from the post the section was saved on. With
 * JavaScript off the first page of the first tab is already in the HTML, and the Numbers pager —
 * plain links — still works.
 *
 * @package WooLentor
 */

( function ( $ ) {
    'use strict';

    var LOADING = 'wl-section-loading';

    function ajaxUrl() {
        return ( window.wlProductSection && wlProductSection.ajaxurl )
            || ( window.wlPackWidgets && wlPackWidgets.ajaxurl )
            || ( window.woolentor_addons && woolentor_addons.woolentorajaxurl )
            || window.ajaxurl
            || '/wp-admin/admin-ajax.php';
    }

    /**
     * The page number a pager link points at — `/some-page/page/2/`, or `?page=2` / `?paged=2` where
     * the site is not using pretty permalinks. Zero when the link carries no page at all, which is
     * page one.
     */
    function pageFromLink( href ) {
        if ( ! href ) {
            return 0;
        }

        var query = href.match( /[?&]pag(?:e|ed)=(\d+)/ );

        if ( query ) {
            return parseInt( query[1], 10 );
        }

        var path = href.match( /\/page\/(\d+)/ );

        return path ? parseInt( path[1], 10 ) : 0;
    }

    /**
     * Bind one section. Idempotent: the editor re-renders a widget on every change, and the guard
     * flag keeps one set of handlers per section.
     */
    function bind( section ) {
        var $section = $( section );

        if ( $section.data( 'wlSectionBound' ) ) {
            return;
        }
        $section.data( 'wlSectionBound', true );

        var $list = $section.find( '[data-wl-section-list]' ).eq( 0 );

        if ( ! $list.length ) {
            return;
        }

        var observer = null;

        // The Product Filter module's current selection, once the visitor has made one. Held here
        // rather than in the markup so a tab switch and a Load More keep the filter that is on
        // screen — the alternative is a second page of products the filter never narrowed.
        var filters = null;

        // Which tab is open, or '' when the section has no tab row. The endpoint treats an absent
        // tab as "this section has none", which is not the same as tab zero.
        function activeTab() {
            var $active = $section.find( '[data-wl-section-tab].is-active' ).eq( 0 );
            return $active.length ? $active.attr( 'data-wl-section-tab' ) : '';
        }

        /**
         * One request, two uses. A tab switch replaces the cards; a pager appends them. The pager
         * itself is always replaced by what the server sent, so the browser never works out page
         * numbers of its own — it is told what the next one is.
         */
        function fetch( paged, append ) {
            if ( $section.hasClass( LOADING ) ) {
                return;
            }

            $section.addClass( LOADING );

            var payload = {
                action: 'woolentor_product_section',
                nonce: $section.attr( 'data-wl-section-nonce' ),
                provider: $section.attr( 'data-wl-section-provider' ),
                post_id: $section.attr( 'data-wl-section-post' ),
                section_id: $section.attr( 'data-wl-section-id' ),
                tab: activeTab(),
                paged: paged
            };

            if ( filters ) {
                payload.filters = filters;

                // The archive term the section is sitting on. There is no archive at the endpoint,
                // so without this a filtered section on a category page would widen to the whole
                // catalogue. The module already localises it.
                if ( window.wlpf_data && wlpf_data.termobj ) {
                    payload.termobj = wlpf_data.termobj;
                }
            }

            $.post( ajaxUrl(), payload )
                .done( function ( response ) {
                    if ( ! response || ! response.success ) {
                        return;
                    }

                    if ( append ) {
                        $list.append( response.data.html || '' );
                    } else {
                        // An empty tab clears the list rather than leaving the previous tab's
                        // products under the new label, which would be a lie about the store.
                        $list.html( response.data.html || '' );
                    }

                    swapPager( response.data.pagination || '' );

                    // Anything that decorates cards — a carousel, a countdown, a lazy loader —
                    // rebuilds itself here rather than this file knowing about any of them.
                    $section.trigger( 'wl-section:rendered', [ { append: append, paged: paged } ] );
                } )
                .always( function () {
                    $section.removeClass( LOADING );
                } );
        }

        /**
         * Put the server's pager in place of the one on screen. Replacing rather than editing keeps
         * the markup identical to a first page load, and re-arms the observer on the new sentinel.
         */
        function swapPager( html ) {
            var $existing = $section.find( '[data-wl-section-pager]' ).eq( 0 );

            if ( observer ) {
                observer.disconnect();
                observer = null;
            }

            if ( $existing.length ) {
                if ( html ) {
                    $existing.replaceWith( html );
                } else {
                    $existing.remove();
                }
            } else if ( html ) {
                // Last child of the section, which is where the server writes it — appending after
                // the list instead would put it above a layout's own footer row.
                $section.append( html );
            }

            watchSentinel();
        }

        // Infinite scroll. IntersectionObserver only — without it the sentinel simply never fires,
        // which is a section that stops after its first page rather than a broken one.
        function watchSentinel() {
            var el = $section.find( '[data-wl-section-observe]' ).get( 0 );

            if ( ! el || typeof window.IntersectionObserver === 'undefined' ) {
                return;
            }

            observer = new window.IntersectionObserver( function ( entries ) {
                if ( ! entries.length || ! entries[0].isIntersecting ) {
                    return;
                }

                var next = parseInt( el.getAttribute( 'data-wl-section-next' ), 10 ) || 0;
                var max = parseInt( el.getAttribute( 'data-wl-section-max' ), 10 ) || 0;

                if ( ! next || next > max ) {
                    return;
                }

                observer.disconnect();
                fetch( next, true );
            }, { rootMargin: '200px' } );

            observer.observe( el );
        }

        $section.on( 'click', '[data-wl-section-tab]', function ( event ) {
            event.preventDefault();

            var $tab = $( this );

            if ( $tab.hasClass( 'is-active' ) || $section.hasClass( LOADING ) ) {
                return;
            }

            // Marked active before the request so activeTab() reports the tab being opened, and so
            // a paged section comes back on page one of the new tab.
            $section.find( '[data-wl-section-tab]' )
                .removeClass( 'is-active' )
                .attr( 'aria-selected', 'false' );

            $tab.addClass( 'is-active' ).attr( 'aria-selected', 'true' );

            fetch( 1, false );
        } );

        $section.on( 'click', '[data-wl-section-more]', function ( event ) {
            event.preventDefault();

            var $btn = $( this );
            var next = parseInt( $btn.attr( 'data-wl-section-next' ), 10 ) || 0;
            var max = parseInt( $btn.attr( 'data-wl-section-max' ), 10 ) || 0;

            if ( ! next || next > max || $btn.is( ':disabled' ) ) {
                return;
            }

            fetch( next, true );
        } );

        // The Product Filter module, in its AJAX mode. It announces the new selection on the
        // document and then renders into its own wrappers; a kit section is not one of those, so it
        // takes the selection from here and renders through its own endpoint instead. That keeps
        // the settings out of the page — the request is still an address — and means the pager and
        // the tab row come back rebuilt rather than left behind stale.
        if ( undefined !== $section.attr( 'data-wl-section-filters' ) ) {
            $( document ).on( 'wlpf_process_ajax_filter', function ( event, selection, page ) {
                filters = selection || {};

                fetch( parseInt( page, 10 ) > 0 ? parseInt( page, 10 ) : 1, false );
            } );

            // A numbers link is a real URL, and with the module set not to write its selection into
            // the URL, following one would drop the filter. While a filter is on screen the link is
            // answered here instead; with none it stays an ordinary link that works without
            // JavaScript.
            $section.on( 'click', '[data-wl-section-pager] a.page-numbers', function ( event ) {
                if ( ! filters ) {
                    return;
                }

                event.preventDefault();

                var page = pageFromLink( $( this ).attr( 'href' ) );

                if ( page > 0 ) {
                    fetch( page, false );
                }
            } );
        }

        watchSentinel();
    }

    function bindAll( $scope ) {
        $scope.find( '[data-wl-section]' ).addBack( '[data-wl-section]' ).each( function () {
            bind( this );
        } );
    }

    $( document ).ready( function () {
        bindAll( $( document.body ) );
    } );

    // Elementor re-renders a widget on every edit, so the editor needs its own pass. The guard flag
    // on each section keeps this from binding twice on the frontend.
    $( window ).on( 'elementor/frontend/init', function () {
        if ( typeof elementorFrontend === 'undefined' ) {
            return;
        }

        elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
            bindAll( $scope );
        } );
    } );

    // Gutenberg blocks that render late, and anything else that injects a section after load.
    $( document ).on( 'wl-section:refresh', function ( event, scope ) {
        bindAll( $( scope || document.body ) );
    } );

}( jQuery ) );
