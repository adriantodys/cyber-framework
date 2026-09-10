/**
 * Header Mobile — obsluga hamburgera.
 *
 * Czysty JS, bez zaleznosci (CLAUDE.md sekcja 2). Skrypt nie zna zadnych
 * wartosci z panelu: prog przelaczania i etykiety przycisku przychodza
 * z markupu przez atrybuty data-*, zeby liczba i tlumaczenia zyly w jednym
 * miejscu — w PHP.
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.cyber-hamburger' );

	if ( ! toggle ) {
		return;
	}

	var menu = document.getElementById( toggle.getAttribute( 'aria-controls' ) );

	if ( ! menu ) {
		return;
	}

	var breakpoint = parseInt( menu.getAttribute( 'data-breakpoint' ), 10 );
	var labelOpen = toggle.getAttribute( 'data-label-open' ) || '';
	var labelClose = toggle.getAttribute( 'data-label-close' ) || '';

	/**
	 * Ustawia stan menu i synchronizuje atrybuty ARIA przycisku.
	 *
	 * @param {boolean} isOpen Czy menu ma byc otwarte.
	 */
	function setState( isOpen ) {
		menu.classList.toggle( 'is-open', isOpen );
		toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', isOpen ? labelClose : labelOpen );
	}

	/**
	 * Czy menu jest aktualnie otwarte.
	 *
	 * @return {boolean} Stan menu.
	 */
	function isOpen() {
		return menu.classList.contains( 'is-open' );
	}

	toggle.addEventListener( 'click', function () {
		setState( ! isOpen() );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key || ! isOpen() ) {
			return;
		}

		setState( false );

		// Fokus wraca na przycisk — inaczej po zamknieciu ladowalby na <body>.
		toggle.focus();
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! isOpen() || menu.contains( event.target ) || toggle.contains( event.target ) ) {
			return;
		}

		setState( false );
	} );

	window.addEventListener( 'resize', function () {
		// Po przejsciu na widok desktopowy menu desktopowe przejmuje role panelu.
		if ( isOpen() && breakpoint && window.innerWidth > breakpoint ) {
			setState( false );
		}
	} );
} )();
