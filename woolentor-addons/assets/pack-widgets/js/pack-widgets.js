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

        WLPackCountdown( $( document.body ) );
    } );

} )( jQuery );
