/**
 * Lista produktow — przelacznik widoku i doladowywanie.
 *
 * Czysty JS, bez zaleznosci (CLAUDE.md sekcja 2). Kolejkowany wylacznie
 * na stronie sklepu i archiwach taksonomii produktow.
 *
 * Dwa niezalezne zadania:
 *
 * 1. PRZELACZNIK WIDOKU. Siatka i lista maja ten sam markup i roznia sie
 *    wylacznie CSS-em, wiec przelaczenie to podmiana jednej klasy — bez
 *    przeladowania i bez zapytania do serwera. Wybor laduje w ciasteczku,
 *    zeby serwer od razu wyrenderowal wlasciwy uklad przy kolejnym wejsciu;
 *    gdyby siedzial w localStorage, na kazdej stronie mignelaby najpierw
 *    siatka.
 *
 * 2. DOLADOWYWANIE. Dziala tylko wtedy, gdy w Global Options wybrano tryb
 *    z przyciskiem. Przycisk niesie kontekst archiwum w atrybutach data-*,
 *    a serwer sklada z nich zapytanie od zera.
 */
( function () {
	'use strict';

	var config = window.cyberShop || {};
	var shop = document.querySelector( '.cyber-shop' );

	if ( ! shop ) {
		return;
	}

	/* ------------------------------------------------------------------ *
	 * Przelacznik widoku
	 * ------------------------------------------------------------------ */

	var views = [ 'grid', 'list' ];

	/**
	 * Zapamietuje wybrany widok na rok.
	 *
	 * @param {string} view Nazwa widoku.
	 * @return {void}
	 */
	function rememberView( view ) {
		if ( ! config.cookie ) {
			return;
		}

		document.cookie = config.cookie + '=' + encodeURIComponent( view ) +
			';path=/;max-age=31536000;samesite=lax';
	}

	/**
	 * Przelacza uklad listy produktow.
	 *
	 * @param {string} view Nazwa widoku.
	 * @return {void}
	 */
	function setView( view ) {
		if ( -1 === views.indexOf( view ) ) {
			return;
		}

		var i;

		for ( i = 0; i < views.length; i++ ) {
			shop.classList.toggle( 'cyber-shop--' + views[ i ], views[ i ] === view );
		}

		var buttons = document.querySelectorAll( '.cyber-shop__view' );

		for ( i = 0; i < buttons.length; i++ ) {
			buttons[ i ].setAttribute(
				'aria-pressed',
				buttons[ i ].getAttribute( 'data-view' ) === view ? 'true' : 'false'
			);
		}

		rememberView( view );
	}

	/* ------------------------------------------------------------------ *
	 * Doladowywanie produktow
	 * ------------------------------------------------------------------ */

	var busy = false;

	/**
	 * Dokleja kolejna strone produktow pod juz wyswietlone.
	 *
	 * @param {Element} button Przycisk doladowania.
	 * @return {void}
	 */
	function loadMore( button ) {
		if ( busy || ! config.ajaxUrl || ! config.nonce ) {
			return;
		}

		var list = shop.querySelector( 'ul.products' );

		if ( ! list ) {
			return;
		}

		var page = parseInt( button.getAttribute( 'data-page' ), 10 ) + 1;
		var max = parseInt( button.getAttribute( 'data-max' ), 10 );

		if ( isNaN( page ) || isNaN( max ) || page > max ) {
			return;
		}

		var body = new window.URLSearchParams();

		body.append( 'action', config.action );
		body.append( 'nonce', config.nonce );
		body.append( 'page', String( page ) );
		body.append( 'per_page', button.getAttribute( 'data-per-page' ) || '' );
		body.append( 'orderby', button.getAttribute( 'data-orderby' ) || '' );
		body.append( 'taxonomy', button.getAttribute( 'data-taxonomy' ) || '' );
		body.append( 'term', button.getAttribute( 'data-term' ) || '0' );

		busy = true;
		button.disabled = true;
		button.classList.add( 'is-busy' );

		window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		} ).then( function ( response ) {
			return response.json();
		} ).then( function ( result ) {
			busy = false;

			if ( ! result || ! result.success ) {
				button.disabled = false;
				button.classList.remove( 'is-busy' );
				window.alert( config.error );

				return;
			}

			/*
			 * insertAdjacentHTML, a nie innerHTML += — przepisanie calej listy
			 * zniszczyloby stan juz wyrenderowanych produktow i zerwaloby
			 * nasluchy, ktore podpiely do nich wtyczki sklepu.
			 */
			if ( result.data.html ) {
				list.insertAdjacentHTML( 'beforeend', result.data.html );
			}

			if ( result.data.has_more ) {
				button.setAttribute( 'data-page', String( page ) );
				button.disabled = false;
				button.classList.remove( 'is-busy' );

				return;
			}

			// Nie ma czego doladowac — przycisk znika razem z kontenerem.
			var wrapper = button.closest( '.cyber-shop__more' );

			if ( wrapper ) {
				wrapper.remove();
			}
		} ).catch( function () {
			busy = false;
			button.disabled = false;
			button.classList.remove( 'is-busy' );
			window.alert( config.error );
		} );
	}

	/*
	 * Delegacja: przycisk doladowania zmienia stan, a po zmianie widoku
	 * przyciski przelacznika i tak zostaja te same. Jeden nasluch obsluguje
	 * oba przypadki i nie wymaga ponownego podpinania.
	 */
	document.addEventListener( 'click', function ( event ) {
		var target = event.target.closest ? event.target : null;

		if ( ! target ) {
			return;
		}

		var viewButton = target.closest( '.cyber-shop__view' );

		if ( viewButton ) {
			setView( viewButton.getAttribute( 'data-view' ) );

			return;
		}

		var moreButton = target.closest( '.cyber-shop__more-button' );

		if ( moreButton ) {
			loadMore( moreButton );
		}
	} );
} )();
