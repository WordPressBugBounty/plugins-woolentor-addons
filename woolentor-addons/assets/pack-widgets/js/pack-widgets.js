/**
 * Pack Widgets — Frontend JavaScript
 * Handles slider and interactive behaviour for Style Pack widgets.
 */
( function ( $ ) {
    'use strict';

    // ── Shared Slider Core ────────────────────────────────────────────────────

    /**
     * WLPackSlider — reusable Slick initialiser for any pack-widget slider.
     *
     * Usage from a widget handler:
     *   WLPackSlider( $scope.find( '.my-widget[data-wl-slider="true"]' ).eq(0) );
     *
     * Settings are read from the element's data-slider-settings JSON attribute.
     * Arrow/dot elements are injected with classes .wl-pack-nav / .wl-pack-dots
     * so pack-widgets-base.css styles them automatically.
     *
     * @param {jQuery} $el  The slider root element (has data-wl-slider="true").
     */
    var WLPackSlider = function ( $el, overrides ) {
        if ( ! $el || ! $el.length ) return;

        var s = $.extend( {
            arrows         : true,
            dots           : true,
            infinite       : true,
            autoplay       : true,
            autoplay_speed : 5000,
            speed          : 600,
            pause_on_hover : true,
            fade           : false,
            items          : 1,
            scroll         : 1,
            tablet_width   : 768,
            tablet_items   : 1,
            tablet_scroll  : 1,
            mobile_width   : 480,
            mobile_items   : 1,
            mobile_scroll  : 1,
        }, $el.data( 'slider-settings' ) || {}, overrides || {} );

        var slickOpts = {
            slidesToShow   : s.items,
            slidesToScroll : s.scroll,
            arrows         : s.arrows,
            prevArrow      : '<button type="button" class="wl-pack-nav wl-pack-nav-prev" aria-label="Previous slide"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
            nextArrow      : '<button type="button" class="wl-pack-nav wl-pack-nav-next" aria-label="Next slide"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
            dots           : s.dots,
            dotsClass      : 'wl-pack-dots',
            infinite       : s.infinite,
            autoplay       : s.autoplay,
            autoplaySpeed  : s.autoplay_speed,
            speed          : s.speed,
            fade           : s.fade,
            cssEase        : s.fade ? 'ease' : 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
            pauseOnHover   : s.pause_on_hover,
            responsive     : [
                {
                    breakpoint : s.tablet_width,
                    settings   : {
                        slidesToShow   : s.tablet_items,
                        slidesToScroll : s.tablet_scroll,
                    },
                },
                {
                    breakpoint : s.mobile_width,
                    settings   : {
                        slidesToShow   : s.mobile_items,
                        slidesToScroll : s.mobile_scroll,
                    },
                },
            ],
        };
        if ( s.slide ) {
            slickOpts.slide = s.slide;
        }
        $el.not( '.slick-initialized' ).slick( slickOpts );

        // A11y: Slick incorrectly sets role="listbox" on the track element.
        $el.find( '.slick-track' ).removeAttr( 'role' );
    };

    // ── Shared Countdown Core ─────────────────────────────────────────────────

    /**
     * WLPackCountdown — deadline handling for any pack widget, built on the
     * jQuery.countdown ("The Final Countdown") already bundled with the plugin as
     * the `countdown-min` handle. The same reasoning as using Slick for sliders:
     * the library is here, it is tested, and it keeps one interval for every
     * instance on the page.
     */
    var WLPackCountdown = ( function () {
        // %D/%I/%N/%T are the library's running totals; the first unit a template
        // renders uses its total so nothing above it is silently dropped — an
        // hours/minutes/seconds countdown shows 49 hours, not 1.
        var TOTAL = { days: '%D', hours: '%I', minutes: '%N', seconds: '%T' };
        var PLAIN = { days: '%D', hours: '%H', minutes: '%M', seconds: '%S' };
        var ORDER = [ 'days', 'hours', 'minutes', 'seconds' ];

        function expire( $el, action, text ) {
            if ( 'hide-section' === action ) {
                // The server also skips an already-expired section, so this only
                // matters when the deadline passes while the page is open, or when
                // the page came from a cache.
                var $wrap = $el.closest( '[data-wl-pack]' );
                ( $wrap.length ? $wrap : $el ).addClass( 'wl-pack-hidden' );
                return;
            }

            if ( 'message' === action && text ) {
                $el.addClass( 'wl-pack-countdown--expired' )
                   .html( $( '<span/>', { 'class': 'wl-pack-countdown-note' } ).text( text ) );
                return;
            }

            $el.addClass( 'wl-pack-countdown--done' );
        }

        /**
         * @param {jQuery} $scope  Any container; every .wl-pack-countdown inside is started.
         */
        return function ( $scope ) {
            if ( ! $scope || ! $scope.length ) return;
            if ( ! $.fn.countdown ) return; // countdown-min not on this page.

            $scope.find( '.wl-pack-countdown' ).addBack( '.wl-pack-countdown' ).each( function () {
                var $el = $( this );

                if ( $el.data( 'wlCountdownReady' ) ) return;

                var end = parseInt( $el.attr( 'data-wl-end' ), 10 );
                if ( ! end ) return;

                var slots = [];
                for ( var i = 0; i < ORDER.length; i++ ) {
                    var $num = $el.find( '.wl-pack-countdown-num[data-unit="' + ORDER[ i ] + '"]' );
                    if ( $num.length ) {
                        slots.push( {
                            $num   : $num,
                            format : slots.length ? PLAIN[ ORDER[ i ] ] : TOTAL[ ORDER[ i ] ],
                        } );
                    }
                }
                if ( ! slots.length ) return;

                $el.data( 'wlCountdownReady', true );

                var action = $el.attr( 'data-wl-expired' ) || 'hide';
                var text   = $el.attr( 'data-wl-expired-text' ) || '';

                $el.countdown( new Date( end * 1000 ) )
                   .on( 'update.countdown', function ( event ) {
                       for ( var s = 0; s < slots.length; s++ ) {
                           slots[ s ].$num.text( event.strftime( slots[ s ].format ) );
                       }
                   } )
                   .on( 'finish.countdown', function () {
                       for ( var s = 0; s < slots.length; s++ ) {
                           slots[ s ].$num.text( '00' );
                       }
                       expire( $el, action, text );
                   } );
            } );
        };
    } )();

    // ── Widget Handlers ───────────────────────────────────────────────────────

    /**
     * Hero Banner slider handler.
     * Editorial v2: fade transition + custom in-slide counter/bar/arrows.
     * Modern v3: standard Slick + beforeChange counter sync.
     * All others: standard WLPackSlider.
     */
    var WLHeroBannerSlider = function ( $scope ) {
        var $el = $scope.find( '.wl-hero-banner[data-wl-slider="true"]' ).eq( 0 );
        if ( ! $el.length ) return;

        $el.find( '.wl-hero-slide--hidden' ).removeClass( 'wl-hero-slide--hidden' );

        // Editorial v2: fade mode, no native arrows/dots — uses custom in-slide controls.
        if ( $el.hasClass( 'wl-hero-editorial-v2' ) ) {
            WLPackSlider( $el, { arrows: false, dots: false, fade: true } );
            $el.on( 'click', '.wl-hev2-btn-prev', function () { $el.slick( 'slickPrev' ); } );
            $el.on( 'click', '.wl-hev2-btn-next', function () { $el.slick( 'slickNext' ); } );
            $el.on( 'beforeChange', function ( e, slick, current, next ) {
                var total = slick.slideCount;
                var num   = next + 1;
                var pct   = ( num / total ) * 100;
                var $tgt  = $el.find( '.slick-slide[data-slick-index="' + next + '"]' );
                $tgt.find( '.wl-hev2-count-cur' ).text( num < 10 ? '0' + num : '' + num );
                $tgt.find( '.wl-hev2-bar-fill' ).css( 'width', pct.toFixed( 2 ) + '%' );
            } );
            return;
        }

        // Luxury v3: social strip is a non-slide sibling inside the banner — tell Slick to only
        // use .wl-hero-slide children so the strip isn't treated as a slide.
        if ( $el.hasClass( 'wl-hero-luxury-v3' ) ) {
            WLPackSlider( $el, { slide: '.wl-hero-slide' } );
            return;
        }

        WLPackSlider( $el );

        // Modern v3: keep the in-panel counter + progress bar in sync on each slide change.
        if ( $el.hasClass( 'wl-hero-modern-v3' ) ) {
            $el.on( 'beforeChange', function ( e, slick, current, next ) {
                var total   = slick.slideCount;
                var num     = next + 1;
                var pct     = ( num / total ) * 100;
                var $target = $el.find( '.slick-slide[data-slick-index="' + next + '"]' );
                $target.find( '.wl-hv3-counter-num--active' ).text( num < 10 ? '0' + num : '' + num );
                $target.find( '.wl-hv3-counter-fill' ).css( 'width', pct.toFixed( 2 ) + '%' );
            } );
        }
    };

    /**
     * Campaign Banner — the floating product card row.
     *
     * Reuses WLPackSlider, so the arrows, easing and responsive breakpoints are the ones every
     * other pack slider already uses, and the widget adds no slider code of its own.
     *
     * The slider is skipped when there are no more cards than fit. A row of three shown three
     * at a time needs no arrows, and Slick would still render them — so the un-initialised
     * fallback in pack-widgets-base.css keeps it as a plain row instead.
     *
     * @param {jQuery} $scope
     */
    var WLCampaignBannerSlider = function ( $scope ) {
        $scope.find( '.wl-cb-cards-track[data-wl-slider="true"]' ).each( function () {
            var $el = $( this );
            if ( $el.hasClass( 'slick-initialized' ) ) return;

            var s     = $el.data( 'slider-settings' ) || {};
            var shown = parseInt( s.items, 10 ) || 3;

            if ( $el.children().length <= shown ) return;

            WLPackSlider( $el, { dots: false, autoplay: false, infinite: false } );
        } );

        // The media panel, when the user supplied more than one shot. Its arrows/dots come
        // from the settings blob, so a variant chooses between them without any code here.
        $scope.find( '.wl-cb-media-track[data-wl-slider="true"]' ).each( function () {
            var $el = $( this );
            if ( $el.hasClass( 'slick-initialized' ) ) return;
            if ( $el.children().length < 2 ) return;

            WLPackSlider( $el, { autoplay: false } );

            // Magazine v3's thumbnail picker. It lives in the copy column, on the far side of
            // the widget from the slider, so it cannot be Slick's own dots — those are appended
            // inside the slider element. The buttons drive slickGoTo, and the active state is
            // read back from afterChange rather than set on click, so the slider stays the one
            // source of truth even when something else changes the slide.
            var $picker = $scope.find( '.wl-cb-picker' ).first();
            if ( ! $picker.length ) return;

            var $btns = $picker.find( '.wl-cb-picker-btn' );

            $btns.on( 'click', function () {
                $el.slick( 'slickGoTo', parseInt( $( this ).data( 'wl-goto' ), 10 ) || 0 );
            } );

            $el.on( 'afterChange', function ( e, slick, current ) {
                $btns.removeClass( 'is-active' ).attr( 'aria-selected', 'false' )
                    .filter( '[data-wl-goto="' + current + '"]' )
                    .addClass( 'is-active' ).attr( 'aria-selected', 'true' );
            } );
        } );
    };

    /**
     * Shop by Category slider handler.
     *
     * Luxury v1 and v3 are carousels; every other style-variant is a static grid and is left
     * alone. Slick appends its arrows to the slider element, but both references put them in a
     * control row beneath the track next to a progress rail — so the arrows are moved there after
     * init, and the rail is filled from the slide position.
     */
    var WLShopByCategorySlider = function ( $scope ) {
        var $el = $scope.find( '.wl-sbc-grid[data-wl-slider="true"]' ).eq( 0 );
        if ( ! $el.length ) return;

        WLPackSlider( $el );
        if ( ! $el.hasClass( 'slick-initialized' ) ) return;

        var $outer = $el.closest( '.wl-sbc-slider-outer' );
        var $nav   = $outer.find( '.wl-sbc-nav' ).eq( 0 );
        var $fill  = $outer.find( '.wl-sbc-progress-fill' ).eq( 0 );

        if ( $nav.length ) {
            $nav.append( $el.find( '.wl-pack-nav' ) );
        }

        if ( ! $fill.length ) return;

        // How much of the track has been revealed: the slides in view plus everything already
        // scrolled past. Measuring the first-visible slide against the last reachable one instead
        // makes the rail start at a share of the track and then jump backwards on the first move,
        // because at index 0 that ratio is zero rather than "five of eight are showing".
        var paint = function ( index ) {
            var slick   = $el.slick( 'getSlick' );
            var perView = slick.options.slidesToShow || 1;
            var total   = slick.slideCount || 1;
            var pct     = Math.min( 100, ( ( ( index || 0 ) + perView ) / total ) * 100 );
            $fill.css( 'width', pct.toFixed( 2 ) + '%' );
        };

        $el.on( 'afterChange', function ( e, slick, current ) { paint( current ); } );
        paint( $el.slick( 'slickCurrentSlide' ) );
    };

    /**
     * Product Showcase carousel.
     *
     * Luxury v1 is the one variant whose reference is a rail rather than a grid. Its own markup is
     * a native scroll-snap track; this uses the bundled Slick instead, so the arrows, the keyboard
     * handling and the responsive slide counts are the ones every other pack widget already uses.
     *
     * Tabs and pagination replace the card list, and Slick has already turned that list into a
     * track — so the carousel is torn down before a swap and rebuilt after it, off the
     * `wl-section:rendered` event the Product Kit's shared script fires. The server never knows a
     * slider is involved: it sends the same plain cards a first page load got.
     */
    var WLProductShowcaseSlider = function ( $scope ) {
        var $el = $scope.find( '.wl-ps-grid[data-wl-slider="true"]' ).eq( 0 );
        if ( ! $el.length ) return;

        WLPackSlider( $el );
        if ( ! $el.hasClass( 'slick-initialized' ) ) return;

        // The reference hangs its arrows outside the track. Slick appends them to the slider
        // element, so they are moved to the wrapper that has room for them.
        var $outer = $el.closest( '.wl-ps-slider-outer' );
        if ( $outer.length ) {
            $outer.append( $el.find( '.wl-pack-nav' ) );
        }

        var $section = $el.closest( '[data-wl-section]' );
        if ( ! $section.length || $section.data( 'wlPsSliderRebind' ) ) return;
        $section.data( 'wlPsSliderRebind', true );

        $section.on( 'wl-section:rendered', function () {
            var $list = $section.find( '.wl-ps-grid[data-wl-slider="true"]' ).eq( 0 );
            if ( ! $list.length ) return;

            // The swap replaced the track's children, so Slick's own bookkeeping is stale. It is
            // destroyed rather than refreshed: refresh keeps the slides it knew about, and after a
            // tab switch none of them are the same products.
            if ( $list.hasClass( 'slick-initialized' ) ) {
                $list.slick( 'unslick' );
            }

            $section.find( '.wl-ps-slider-outer > .wl-pack-nav' ).remove();

            WLProductShowcaseSlider( $section );
        } );
    };

    /**
     * Shop the Look — pin open/close, the edge flip, and the look switcher.
     *
     * The positioning of a pin is not this script's business: X and Y are written
     * straight to CSS from the repeater through {{CURRENT_ITEM}}, so nothing here
     * measures or moves a pin. What is here is the part CSS cannot do safely.
     *
     * Image Marker opens its tooltip on :hover in pure CSS. That does not survive
     * contact with this widget: the card holds an Add to Cart, so the pointer has
     * to travel from the pin to the button and any gap on the way closes it;
     * touch has no hover; and a pin that is not focusable holds a button no
     * keyboard user can reach. So click is the way in, and hover is added on top
     * for pointer devices only.
     */
    var WLShopTheLook = function ( $scope ) {
        $scope.find( '[data-wl-stl]' ).each( function () {
            var $stl = $( this );

            if ( $stl.data( 'wlStlReady' ) ) {
                return;
            }
            $stl.data( 'wlStlReady', true );

            // Handlers on document and window outlive the element they belong
            // to. In the editor Elementor throws the whole widget away and
            // rebuilds it on every change, so without a namespace of its own
            // each rebuild would leave another set behind, closing over a node
            // that is no longer on the page. Namespaced per widget and cleared
            // before binding, a rebuild replaces its own and nothing else's.
            var ns = '.wlStl' + ( $stl.closest( '.elementor-element' ).attr( 'data-id' ) || 'x' );

            $( document ).off( 'click' + ns + ' keydown' + ns );
            $( window ).off( 'resize' + ns );

            var closeAll = function ( except ) {
                $stl.find( '.wl-stl-pin.is-open' ).each( function () {
                    if ( except && this === except ) {
                        return;
                    }
                    var $pin = $( this );
                    $pin.removeClass( 'is-open' );
                    $pin.find( '.wl-stl-pin-btn' ).attr( 'aria-expanded', 'false' );
                    $pin.find( '.wl-stl-card' ).prop( 'hidden', true );
                } );
            };

            /**
             * Keep an open card inside the picture, on both axes.
             *
             * Horizontally it flips: a card that would run past the right edge
             * opens leftward instead. The reference requires this — Editorial V1
             * ships `.card-left` on five of its pins. A row that forced a side
             * keeps it; only `auto` is resolved here.
             *
             * Vertically it shifts, because flipping is not available: a pin near
             * the top or the bottom would otherwise centre its card half outside
             * the picture. The correction goes out as a custom property the card's
             * own transform reads, so the CSS keeps owning the centring.
             *
             * Everything is measured from real rectangles rather than offsets, so
             * a card is placed against what it actually occupies — including any
             * shadow-free padding a pack gives it.
             */
            var GAP = 14;

            var placeCard = function ( $pin ) {
                var $image = $pin.closest( '.wl-stl-image' );
                var card   = $pin.find( '.wl-stl-card' ).get( 0 );

                if ( ! $image.length || ! card ) {
                    return;
                }

                // Below a phone's width the card is a fixed bottom sheet, which
                // is already inside the viewport by construction.
                if ( 'absolute' !== window.getComputedStyle( card ).position ) {
                    return;
                }

                var forced = $pin.hasClass( 'is-card-left' ) || $pin.hasClass( 'is-card-right' );

                // Measured from a clean state, or the previous correction is read
                // back as if it were the natural position.
                card.style.setProperty( '--wl-stl-card-y', '0px' );
                card.style.maxWidth = '';
                if ( ! forced ) {
                    $pin.removeClass( 'is-flipped' );
                }

                var img = $image.get( 0 ).getBoundingClientRect();
                var pin = $pin.get( 0 ).getBoundingClientRect();
                var box = card.getBoundingClientRect();

                // A narrow image column — a widget in a sidebar, a three-column
                // row — can be narrower than the pack's card. Nothing can place a
                // card that is wider than the picture, so it is capped first and
                // everything below then works with the real width.
                if ( box.width > img.width - GAP * 2 ) {
                    card.style.maxWidth = Math.max( 120, Math.round( img.width - GAP * 2 ) ) + 'px';
                    box = card.getBoundingClientRect();
                }

                if ( ! forced ) {
                    var overflowsRight = pin.right + GAP + box.width > img.right;
                    var fitsLeft       = pin.left - GAP - box.width >= img.left;

                    // Only flip when the other side is actually better. A card
                    // wider than the space on either side stays where it is
                    // rather than swapping one overflow for another.
                    if ( overflowsRight && fitsLeft ) {
                        $pin.addClass( 'is-flipped' );
                        box = card.getBoundingClientRect();
                    }
                }

                var shift = 0;

                if ( box.height < img.height ) {
                    if ( box.top < img.top ) {
                        shift = img.top - box.top;
                    } else if ( box.bottom > img.bottom ) {
                        shift = img.bottom - box.bottom;
                    }
                }

                if ( shift ) {
                    card.style.setProperty( '--wl-stl-card-y', Math.round( shift ) + 'px' );
                }
            };

            var open = function ( $pin ) {
                closeAll( $pin.get( 0 ) );
                $pin.addClass( 'is-open' );
                $pin.find( '.wl-stl-pin-btn' ).attr( 'aria-expanded', 'true' );
                $pin.find( '.wl-stl-card' ).prop( 'hidden', false );
                placeCard( $pin );
            };

            $stl.on( 'click', '.wl-stl-pin-btn', function ( event ) {
                event.preventDefault();

                var $pin = $( this ).closest( '.wl-stl-pin' );

                if ( $pin.hasClass( 'is-open' ) ) {
                    closeAll();
                    return;
                }

                open( $pin );
            } );

            // Hover as an enhancement, never as the only way in. matchMedia is
            // asked rather than assumed, so a touch device gets click only.
            if ( window.matchMedia && window.matchMedia( '(hover: hover)' ).matches ) {
                $stl.on( 'mouseenter', '.wl-stl-pin', function () {
                    open( $( this ) );
                } );
            }

            // Tabbing into Add to Cart must not close the card under the cursor,
            // so focus leaving the pin entirely is what closes it.
            $stl.on( 'focusout', '.wl-stl-pin', function ( event ) {
                var $pin = $( this );
                if ( ! $pin.get( 0 ).contains( event.relatedTarget ) ) {
                    $pin.removeClass( 'is-open' );
                    $pin.find( '.wl-stl-pin-btn' ).attr( 'aria-expanded', 'false' );
                    $pin.find( '.wl-stl-card' ).prop( 'hidden', true );
                }
            } );

            /**
             * Close on a click outside any pin.
             *
             * Decided here by asking where the click came from, rather than by
             * stopping propagation inside the pin — which is what this used to
             * do, and what broke Add to Cart. WooCommerce binds its loop button
             * on `document`, so a card that stopped the event never let the
             * click reach it and every Add to Cart navigated away instead of
             * adding in place. Nothing in this widget may stop a click from
             * reaching the document.
             */
            $( document ).on( 'click' + ns, function ( event ) {
                if ( $( event.target ).closest( '.wl-stl-pin' ).length ) {
                    return;
                }
                closeAll();
            } );

            $( document ).on( 'keydown' + ns, function ( event ) {
                if ( 'Escape' !== event.key && 27 !== event.keyCode ) {
                    return;
                }
                var $open = $stl.find( '.wl-stl-pin.is-open' );
                if ( $open.length ) {
                    $open.find( '.wl-stl-pin-btn' ).trigger( 'focus' );
                    closeAll();
                }
            } );

            // The flip depends on the image's width, so it is re-resolved when
            // that changes rather than only when the card opens.
            $( window ).on( 'resize' + ns, function () {
                $stl.find( '.wl-stl-pin.is-open' ).each( function () {
                    placeCard( $( this ) );
                } );
            } );

            /**
             * Add every product in a look, in one request.
             *
             * One call, not one per product: each of WooCommerce's own add-to-cart
             * responses carries a full set of cart fragments, so three products
             * added separately would race three fragment replacements and show a
             * cart count that is briefly, visibly wrong. The endpoint adds the
             * whole list server-side and refreshes the fragments once.
             */
            $stl.on( 'click', '[data-wl-stl-bulk]', function ( event ) {
                event.preventDefault();

                var $btn = $( this );

                if ( $btn.prop( 'disabled' ) ) {
                    return;
                }

                var ids = ( $btn.attr( 'data-wl-stl-bulk' ) || '' ).split( ',' ).filter( Boolean );

                if ( ! ids.length || typeof wlPackWidgets === 'undefined' ) {
                    return;
                }

                var original = $btn.text();

                $btn.prop( 'disabled', true ).addClass( 'is-loading' );

                $.post( wlPackWidgets.ajaxurl, {
                    action: 'woolentor_add_look_to_cart',
                    nonce: $btn.attr( 'data-wl-stl-nonce' ),
                    product_ids: ids
                } ).done( function ( response ) {
                    if ( ! response || ! response.success ) {
                        $btn.text( original );
                        return;
                    }

                    // The same events WooCommerce's own button fires, so mini-cart
                    // widgets and themes update without knowing about this widget.
                    $( document.body ).trigger( 'wc_fragment_refresh' );

                    /**
                     * One `added_to_cart` per product, each carrying that
                     * product's own row button.
                     *
                     * The fourth argument is not optional in practice: listeners
                     * treat it as guaranteed and read `button.data('product_id')`
                     * straight off it — the wishlist module does, with no guard —
                     * so firing without a button throws inside somebody else's
                     * handler, and firing with the bulk button hands them one that
                     * has no product id to read.
                     *
                     * The row buttons are the honest answer: they are real, they
                     * are already in the page, and each carries the id of the
                     * product it belongs to. Announcing each add separately is
                     * also what actually happened, so a listener with a per-product
                     * rule — "take it off the wishlist once it is in the cart" —
                     * acts on all of them rather than one or none.
                     *
                     * This costs nothing the single path does not already cost:
                     * the cart was written server-side in one request, and these
                     * are notifications carrying fragments that are already in
                     * hand. It is also what puts the check on every row that was
                     * added, since WooCommerce's own handler marks the button it
                     * is given.
                     */
                    ids.forEach( function ( id ) {
                        var $rowBtn = $stl
                            .find( '.wl-stl-cart-btn[data-product_id="' + id + '"]' )
                            .first();

                        if ( ! $rowBtn.length ) {
                            return;
                        }

                        $( document.body ).trigger( 'added_to_cart', [
                            response.data.fragments,
                            response.data.cart_hash,
                            $rowBtn
                        ] );
                    } );

                    $btn.addClass( 'is-added' );

                    var done = $btn.attr( 'data-wl-stl-added' );

                    if ( done ) {
                        $btn.text( done );
                    }
                } ).fail( function () {
                    $btn.text( original );
                } ).always( function () {
                    $btn.prop( 'disabled', false ).removeClass( 'is-loading' );
                } );
            } );

            /**
             * The look switcher. Every look is already in the page, hidden, so
             * switching is a class change and never a request.
             */
            var pad = function ( n ) {
                return n < 10 ? '0' + n : String( n );
            };

            var show = function ( target ) {
                closeAll();

                $stl.find( '[data-wl-stl-switch]' )
                    .removeClass( 'is-active' )
                    .attr( 'aria-pressed', 'false' );
                $stl.find( '[data-wl-stl-switch="' + target + '"]' )
                    .addClass( 'is-active' )
                    .attr( 'aria-pressed', 'true' );

                $stl.find( '[data-wl-stl-look]' ).removeClass( 'is-active' );
                $stl.find( '[data-wl-stl-look="' + target + '"]' ).addClass( 'is-active' );

                $stl.find( '.wl-stl-counter-now' ).text( pad( parseInt( target, 10 ) ) );
            };

            $stl.on( 'click', '[data-wl-stl-switch]', function ( event ) {
                event.preventDefault();
                show( $( this ).attr( 'data-wl-stl-switch' ) );
            } );

            /**
             * The counter's arrows step rather than name a look, and they wrap at
             * either end so neither arrow is ever a dead control. The stage and
             * the panel both carry a copy of every look, so the count comes from
             * the stage alone.
             */
            $stl.on( 'click', '[data-wl-stl-step]', function ( event ) {
                event.preventDefault();

                var $looks = $stl.find( '.wl-stl-stage > [data-wl-stl-look]' );
                var total  = $looks.length;

                if ( total < 2 ) {
                    return;
                }

                var current = parseInt( $looks.filter( '.is-active' ).attr( 'data-wl-stl-look' ), 10 ) || 1;
                var step    = parseInt( $( this ).attr( 'data-wl-stl-step' ), 10 ) || 1;
                var next    = ( ( current - 1 + step ) % total + total ) % total + 1;

                show( String( next ) );
            } );

            /**
             * A pin and its row are one control. They carry the same repeater row
             * id, so neither has to look the other up — hovering either marks the
             * pair active and everything else dim, and each pack's CSS decides
             * which of the two it draws.
             */
            var highlight = function ( id, on ) {
                var $pins = $stl.find( '[data-wl-stl-pin]' );
                var $rows = $stl.find( '[data-wl-stl-row]' );

                if ( ! on ) {
                    $pins.add( $rows ).removeClass( 'is-active is-dim' );
                    return;
                }

                $pins.each( function () {
                    var match = $( this ).attr( 'data-wl-stl-pin' ) === id;
                    $( this ).toggleClass( 'is-active', match ).toggleClass( 'is-dim', ! match );
                } );

                $rows.each( function () {
                    var match = $( this ).attr( 'data-wl-stl-row' ) === id;
                    $( this ).toggleClass( 'is-active', match ).toggleClass( 'is-dim', ! match );
                } );
            };

            $stl.on( 'mouseenter', '[data-wl-stl-pin], [data-wl-stl-row]', function () {
                highlight( $( this ).attr( 'data-wl-stl-pin' ) || $( this ).attr( 'data-wl-stl-row' ), true );
            } );

            $stl.on( 'mouseleave', '[data-wl-stl-pin], [data-wl-stl-row]', function () {
                highlight( null, false );
            } );
        } );
    };

    /**
     * WLProductBundle — bundle switching, item toggling and one-request add to cart.
     */
    var WLProductBundle = function ( $scope ) {
        var $widgets = $scope.find( '[data-wl-pb]' );

        if ( ! $widgets.length ) {
            return;
        }

        $widgets.each( function () {
            var $pb = $( this );
            var ns  = '.wlpb' + ( $pb.attr( 'data-wl-pb-uid' ) || Math.random().toString( 36 ).slice( 2 ) );

            // Re-rendering a widget in the editor would otherwise stack a second set of handlers
            // on the same nodes and fire everything twice.
            if ( $pb.attr( 'data-wl-pb-bound' ) ) {
                $pb.off( $pb.attr( 'data-wl-pb-bound' ) );
            }

            $pb.attr( 'data-wl-pb-bound', ns );

            var money = {};

            try {
                money = JSON.parse( $pb.attr( 'data-wl-pb-money' ) || '{}' );
            } catch ( e ) {
                money = {};
            }

            /**
             * Format a number the way the server would.
             *
             * WooCommerce's own separators and pattern come through on the widget, because a shop
             * selling in euros writes `1.234,56 €` and one selling in dollars writes `$1,234.56`.
             * Guessing either would produce a total that disagrees with the one beside it.
             */
            function formatMoney( amount ) {
                if ( ! money.format ) {
                    return String( amount );
                }

                var negative = amount < 0;
                var fixed    = Math.abs( amount ).toFixed( money.decimals );
                var parts    = fixed.split( '.' );

                parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, money.thousand );

                var number = parts.length > 1 ? parts.join( money.decimal ) : parts[ 0 ];

                return ( negative ? '-' : '' ) + money.format
                    .replace( '%1$s', money.symbol )
                    .replace( '%2$s', number );
            }

            /** Every box that holds one bundle's own items, total and button. */
            function boxes() {
                return $pb.find( '[data-wl-pb-bundle]' );
            }

            /** Re-price one bundle from the rows that are still on. */
            function retotal( $box ) {
                var now = 0;
                var was = 0;
                var on  = 0;

                $box.find( '[data-wl-pb-item]' ).each( function () {
                    var $item = $( this );

                    if ( $item.hasClass( 'is-off' ) ) {
                        return;
                    }

                    now += parseFloat( $item.attr( 'data-wl-pb-price' ) ) || 0;
                    was += parseFloat( $item.attr( 'data-wl-pb-regular' ) ) || 0;
                    on++;
                } );

                $box.find( '[data-wl-pb-now]' ).text( formatMoney( now ) );

                var saved = Math.max( 0, was - now );
                var $was  = $box.find( '[data-wl-pb-was]' );
                var $save = $box.find( '[data-wl-pb-save]' );

                // A saving of zero is not shown at all rather than printed as "Save 0": the
                // struck price and the badge both stop meaning anything once they are equal.
                if ( saved > 0.009 ) {
                    $was.text( formatMoney( was ) ).show();
                    $save.show().find( '.wl-pb-save-value' ).each( function () {
                        var $v      = $( this );
                        var percent = was > 0 ? Math.round( ( saved / was ) * 100 ) : 0;
                        var mode    = $v.attr( 'data-wl-pb-save-mode' ) || 'amount';

                        if ( 'percent' === mode ) {
                            $v.text( percent + '%' );
                        } else if ( 'both' === mode ) {
                            $v.text( formatMoney( saved ) + ' — ' + percent + '%' );
                        } else {
                            $v.text( formatMoney( saved ) );
                        }
                    } );
                } else {
                    $was.hide();
                    $save.hide();
                }

                $box.find( '[data-wl-pb-selected]' ).text( on );

                // The button is the only thing that can act, so it is the thing that reports an
                // empty selection — disabled, and saying why.
                var $cta  = $box.find( '[data-wl-pb-add]' );
                var empty = $cta.attr( 'data-wl-pb-empty' ) || '';

                if ( ! $cta.hasClass( 'is-added' ) ) {
                    if ( ! on ) {
                        $cta.prop( 'disabled', true );

                        if ( empty ) {
                            $cta.find( '.wl-pb-cta-text' ).text( empty );
                        }
                    } else {
                        $cta.prop( 'disabled', false )
                            .find( '.wl-pb-cta-text' ).text( $cta.attr( 'data-wl-pb-label' ) || '' );
                    }
                }

                giftProgress( $box, now );
            }

            /**
             * Move the free-gift bar. It reports progress toward a promotion the merchant has set
             * up in WooCommerce; it never adds anything, and it never claims the gift is in the
             * cart.
             */
            function giftProgress( $box, now ) {
                var $gift = $box.find( '[data-wl-pb-gift]' );

                if ( ! $gift.length ) {
                    return;
                }

                var threshold = parseFloat( $gift.attr( 'data-wl-pb-gift' ) ) || 0;

                if ( threshold <= 0 ) {
                    return;
                }

                var left    = Math.max( 0, threshold - now );
                var percent = Math.min( 100, ( now / threshold ) * 100 );

                $gift.find( '[data-wl-pb-gift-fill]' ).css( 'width', percent + '%' );
                $gift.toggleClass( 'is-unlocked', left <= 0 );

                var $left = $gift.find( '[data-wl-pb-gift-left]' );

                if ( $left.length ) {
                    $left.text( formatMoney( left ) );
                }
            }

            function setItem( $item, on ) {
                if ( ! $item.hasClass( 'is-toggle' ) ) {
                    return;
                }

                $item.toggleClass( 'is-off', ! on );
                $item.find( '[data-wl-pb-toggle]' ).attr( 'aria-checked', on ? 'true' : 'false' );

                // Magazine V2 recaps the set beside its total. The recap line is keyed by product
                // id and carries no price attribute of its own, so it follows the tile without
                // ever being counted a second time.
                var id = $item.attr( 'data-wl-pb-id' );

                if ( id ) {
                    $item.closest( '[data-wl-pb-bundle]' )
                        .find( '[data-wl-pb-line="' + id + '"]' )
                        .toggleClass( 'is-off', ! on );
                }
            }

            // ── Toggling ──────────────────────────────────────────────────────

            $pb.on( 'click' + ns, '[data-wl-pb-toggle]', function () {
                var $item = $( this ).closest( '[data-wl-pb-item]' );
                setItem( $item, $item.hasClass( 'is-off' ) );
                retotal( $item.closest( '[data-wl-pb-bundle]' ) );
            } );

            // Clicking anywhere on the row is a convenience on top of the real control; when the
            // click landed on that control it has already been handled.
            $pb.on( 'click' + ns, '.wl-pb-item.is-toggle', function ( event ) {
                if ( $( event.target ).closest( '[data-wl-pb-toggle]' ).length ) {
                    return;
                }

                var $item = $( this );
                setItem( $item, $item.hasClass( 'is-off' ) );
                retotal( $item.closest( '[data-wl-pb-bundle]' ) );
            } );

            $pb.on( 'click' + ns, '[data-wl-pb-all]', function () {
                var $box = $( this ).closest( '[data-wl-pb-bundle]' );
                $box.find( '.wl-pb-item.is-toggle' ).each( function () {
                    setItem( $( this ), true );
                } );
                retotal( $box );
            } );

            $pb.on( 'click' + ns, '[data-wl-pb-none]', function () {
                var $box = $( this ).closest( '[data-wl-pb-bundle]' );
                $box.find( '.wl-pb-item.is-toggle' ).each( function () {
                    setItem( $( this ), false );
                } );
                retotal( $box );
            } );

            // ── Switching ─────────────────────────────────────────────────────

            function show( index ) {
                var $all = $pb.find( '.wl-pb-stage > .wl-pb-bundle' );

                if ( ! $all.length ) {
                    return;
                }

                $all.removeClass( 'is-active' )
                    .filter( '[data-wl-pb-bundle="' + index + '"]' ).addClass( 'is-active' );

                $pb.find( '[data-wl-pb-go]' ).each( function () {
                    var $btn   = $( this );
                    var active = String( $btn.attr( 'data-wl-pb-go' ) ) === String( index );
                    $btn.toggleClass( 'is-active', active ).attr( 'aria-pressed', active ? 'true' : 'false' );
                } );

                var order = $all.map( function () {
                    return $( this ).attr( 'data-wl-pb-bundle' );
                } ).get();

                var position = order.indexOf( String( index ) ) + 1;

                $pb.find( '[data-wl-pb-counter] .wl-pb-switch-now' )
                    .text( position < 10 ? '0' + position : String( position ) );

                // The counter is a one-arrow pager, so it cycles. Disabling its only control at
                // the last bundle would strand the shopper there with no way back — which is
                // exactly what it did. The two-arrow shape stops at the ends instead, because
                // there a disabled arrow still leaves the other one to use.
                if ( ! wraps() ) {
                    $pb.find( '[data-wl-pb-prev]' ).prop( 'disabled', position <= 1 );
                    $pb.find( '[data-wl-pb-next]' ).prop( 'disabled', position >= order.length );
                }
            }

            function wraps() {
                return $pb.find( '[data-wl-pb-switch="counter"]' ).length > 0;
            }

            function step( delta ) {
                var $all = $pb.find( '.wl-pb-stage > .wl-pb-bundle' );
                var order = $all.map( function () {
                    return $( this ).attr( 'data-wl-pb-bundle' );
                } ).get();

                if ( ! order.length ) {
                    return;
                }

                var current = $all.filter( '.is-active' ).attr( 'data-wl-pb-bundle' );
                var next    = order.indexOf( String( current ) ) + delta;

                if ( wraps() ) {
                    next = ( next + order.length ) % order.length;
                } else if ( next < 0 || next >= order.length ) {
                    return;
                }

                show( order[ next ] );
            }

            $pb.on( 'click' + ns, '[data-wl-pb-go]', function () {
                show( $( this ).attr( 'data-wl-pb-go' ) );
            } );

            $pb.on( 'click' + ns, '[data-wl-pb-prev]', function () {
                step( -1 );
            } );

            $pb.on( 'click' + ns, '[data-wl-pb-next]', function () {
                step( 1 );
            } );

            // ── Add to cart ───────────────────────────────────────────────────

            $pb.on( 'click' + ns, '[data-wl-pb-add]', function ( event ) {
                event.preventDefault();

                var $btn = $( this );

                if ( $btn.prop( 'disabled' ) || $btn.hasClass( 'is-loading' ) ) {
                    return;
                }

                var $box  = $btn.closest( '[data-wl-pb-bundle]' );
                var items = [];

                $box.find( '[data-wl-pb-item]' ).each( function () {
                    var $item = $( this );

                    if ( $item.hasClass( 'is-off' ) ) {
                        return;
                    }

                    items.push( {
                        id:  $item.attr( 'data-wl-pb-id' ),
                        qty: $item.attr( 'data-wl-pb-qty' ) || 1
                    } );
                } );

                if ( ! items.length || typeof wlPackWidgets === 'undefined' ) {
                    return;
                }

                var $label   = $btn.find( '.wl-pb-cta-text' );
                var original = $label.text();

                $btn.addClass( 'is-loading' ).prop( 'disabled', true );

                $.post( wlPackWidgets.ajaxurl, {
                    action: 'woolentor_add_bundle_to_cart',
                    nonce:  $btn.attr( 'data-wl-pb-nonce' ),
                    coupon: $btn.attr( 'data-wl-pb-coupon' ) || '',
                    items:  items
                } ).done( function ( response ) {
                    if ( ! response || ! response.success ) {
                        $label.text( original );
                        return;
                    }

                    $( document.body ).trigger( 'wc_fragment_refresh' );

                    /**
                     * One `added_to_cart` per product.
                     *
                     * The fourth argument is not optional in practice: listeners read
                     * `button.data('product_id')` straight off it with no guard — the wishlist
                     * module does — so firing without a button throws inside somebody else's
                     * handler. This widget has no per-product button to hand over, so each event
                     * carries a detached one holding that product's id: honest about which product
                     * it announces, and enough for every listener that reads it.
                     */
                    ( response.data.ids || [] ).forEach( function ( id ) {
                        $( document.body ).trigger( 'added_to_cart', [
                            response.data.fragments,
                            response.data.cart_hash,
                            $( '<button type="button" data-product_id="' + id + '"></button>' ),
                            id
                        ] );
                    } );

                    $btn.addClass( 'is-added' );

                    var done = $btn.attr( 'data-wl-pb-added' );
                    $label.text( done || original );
                } ).fail( function () {
                    $label.text( original );
                } ).always( function () {
                    $btn.removeClass( 'is-loading' ).prop( 'disabled', false );
                } );
            } );

            // First paint: the markup already carries the server's figures, so this only settles
            // the switcher's arrows and the button's empty state.
            boxes().each( function () {
                retotal( $( this ) );
            } );

            var $active = $pb.find( '.wl-pb-stage > .wl-pb-bundle.is-active' ).first();

            if ( $active.length ) {
                show( $active.attr( 'data-wl-pb-bundle' ) );
            }
        } );
    };

    /**
     * WLSocialProof — hands each slider to the shared Slick core and wires the pack's own picker.
     *
     * There is deliberately **no bespoke slider here**. Slick is already bundled, already
     * keyboard- and touch-capable, and every other pack widget uses it through `WLPackSlider`,
     * so a merchant meets the same switches under the same names. All this module adds is the
     * part Slick has no opinion about: a filmstrip, a row of portraits or a set of tabs that
     * drives it, and a counter that follows it.
     *
     * Nothing here calls stopPropagation(). WooCommerce delegates handlers on `document`, and
     * stopping a click inside a pack widget has already turned an AJAX add into a full page
     * navigation once.
     */
    var WLSocialProof = function ( $scope ) {
        $scope.find( '.wl-sp-slider[data-wl-slider="true"]' ).each( function () {
            var $slider = $( this );
            var $sp     = $slider.closest( '[data-wl-sp]' );

            // Slick refuses to re-init a live instance, and the editor re-renders on every
            // keystroke in a control.
            if ( $slider.hasClass( 'slick-initialized' ) ) {
                $slider.slick( 'unslick' );
            }

            WLPackSlider( $slider );

            // Bound on the widget rather than the picker: a carousel's arrows sit in the header
            // row and a spotlight's in its picker, and both drive the same slider.
            var ns    = '.wlsp' + Math.random().toString( 36 ).slice( 2 );
            var bound = $sp.attr( 'data-wl-sp-bound' );

            if ( bound ) {
                $sp.off( bound );
            }

            $sp.attr( 'data-wl-sp-bound', ns );

            function paint( index ) {
                $sp.find( '[data-wl-sp-go]' ).each( function () {
                    var $btn   = $( this );
                    var active = parseInt( $btn.attr( 'data-wl-sp-go' ), 10 ) === index;
                    $btn.toggleClass( 'is-active', active ).attr( 'aria-pressed', active ? 'true' : 'false' );
                } );

                var now = index + 1;
                $sp.find( '.wl-sp-counter-now' ).text( now < 10 ? '0' + now : String( now ) );
            }

            // Slick owns the current index; the picker only asks it to move and then redraws
            // from what Slick reports. Keeping one source of truth is what stops the highlight
            // drifting out of step with the slide during autoplay.
            $slider.on( 'afterChange' + ns, function ( event, slick, current ) {
                paint( current );
            } );

            $sp.on( 'click' + ns, '[data-wl-sp-go]', function () {
                $slider.slick( 'slickGoTo', parseInt( $( this ).attr( 'data-wl-sp-go' ), 10 ) || 0 );
            } );

            $sp.on( 'click' + ns, '[data-wl-sp-prev]', function () {
                $slider.slick( 'slickPrev' );
            } );

            $sp.on( 'click' + ns, '[data-wl-sp-next]', function () {
                $slider.slick( 'slickNext' );
            } );

            paint( 0 );
        } );
    };

    // ── Elementor editor / preview ────────────────────────────────────────────

    $( window ).on( 'elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-hero-banner.default',
            WLHeroBannerSlider
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-campaign-banner.default',
            WLCampaignBannerSlider
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-shop-by-category.default',
            WLShopByCategorySlider
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-product-showcase.default',
            function ( $scope ) {
                WLProductShowcaseSlider( $scope );
            }
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-shop-the-look.default',
            WLShopTheLook
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-product-bundle.default',
            WLProductBundle
        );

        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-social-proof.default',
            WLSocialProof
        );

        // Countdown is a shared component rather than one widget's behaviour, so it
        // binds to every element instead of a single widget type. In the editor this
        // fires again after each re-render, and the guard flag on the element keeps
        // one instance per countdown.
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/global',
            WLPackCountdown
        );
    } );

    // ── Plain frontend (outside Elementor editor) ─────────────────────────────

    $( document ).ready( function () {
        if ( typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode() ) {
            return;
        }
        $( '.elementor-widget-woolentor-hero-banner' ).each( function () {
            WLHeroBannerSlider( $( this ) );
        } );

        $( '.elementor-widget-woolentor-campaign-banner' ).each( function () {
            WLCampaignBannerSlider( $( this ) );
        } );

        $( '.elementor-widget-woolentor-shop-by-category' ).each( function () {
            WLShopByCategorySlider( $( this ) );
        } );

        $( '.elementor-widget-woolentor-product-showcase' ).each( function () {
            WLProductShowcaseSlider( $( this ) );
        } );

        $( '.elementor-widget-woolentor-shop-the-look' ).each( function () {
            WLShopTheLook( $( this ) );
        } );

        $( '.elementor-widget-woolentor-product-bundle' ).each( function () {
            WLProductBundle( $( this ) );
        } );

        $( '.elementor-widget-woolentor-social-proof' ).each( function () {
            WLSocialProof( $( this ) );
        } );

        WLPackCountdown( $( document.body ) );
    } );

} )( jQuery );
