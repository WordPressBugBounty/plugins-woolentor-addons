/**
 * Blog Posts - 2026 — Load More (Pro).
 *
 * The only script the widget has. The layouts are static; this loads only on a page where a
 * Blog Posts widget has Load More switched on.
 *
 * The button names its state: aria-busy while a page is on its way, and when the last page has
 * arrived it is replaced by the end text in a live region — a button that silently stops working
 * is the bug this avoids. Focus moves to the first new post so keyboard readers carry on from
 * where they were.
 */
( function () {
	'use strict';

	function setBusy( button, busy, label, text ) {
		button.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
		button.disabled = busy;
		if ( text ) {
			label.textContent = text;
		}
	}

	function load( button ) {
		if ( 'true' === button.getAttribute( 'aria-busy' ) ) {
			return;
		}

		var data   = button.dataset;
		var root   = button.closest( '[data-wl-bp]' );
		var wrap   = button.closest( '.wl-bp-more' );
		var items  = root ? root.querySelector( '[data-wl-bp-items]' ) : null;
		var label  = button.querySelector( '.wl-bp-more-label' ) || button;
		var status = wrap ? wrap.querySelector( '.wl-bp-more-status' ) : null;
		var idle   = label.textContent;
		var page   = parseInt( data.page, 10 ) + 1;

		if ( ! items || ! window.fetch ) {
			return;
		}

		setBusy( button, true, label, data.loading || idle );

		var body = new FormData();
		body.append( 'action', 'woolentor_blog_posts_load_more' );
		body.append( 'nonce', data.nonce );
		body.append( 'post_id', data.post );
		body.append( 'element_id', data.element );
		body.append( 'page', String( page ) );
		body.append( 'exclude', data.exclude || '0' );

		window.fetch( data.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				if ( ! result || ! result.success || ! result.data ) {
					throw new Error( 'load-more' );
				}

				var holder = document.createElement( 'div' );
				var first  = null;

				holder.innerHTML = result.data.html;

				while ( holder.firstElementChild ) {
					if ( ! first ) {
						first = holder.firstElementChild;
					}
					items.appendChild( holder.firstElementChild );
				}

				button.dataset.page = String( page );

				if ( first ) {
					var link = first.querySelector( '.wl-bp-title a' );
					if ( link ) {
						link.focus();
					}
				}

				if ( result.data.has_more && first ) {
					setBusy( button, false, label, idle );
					return;
				}

				button.remove();
				if ( status ) {
					status.textContent = data.end || '';
				}
			} )
			.catch( function () {
				// Leave the button usable so the reader can try again.
				setBusy( button, false, label, idle );
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '[data-wl-bp-more]' ) : null;
		if ( button ) {
			event.preventDefault();
			load( button );
		}
	} );
}() );
