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

    // ── Elementor editor / preview ────────────────────────────────────────────

    $( window ).on( 'elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/woolentor-hero-banner.default',
            WLHeroBannerSlider
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
    } );

} )( jQuery );
