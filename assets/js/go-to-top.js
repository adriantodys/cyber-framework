/**
 * Przycisk "do gory" — pokazywanie po przewinieciu i powrot na gore strony.
 *
 * Prog pokazania przychodzi w atrybucie data-cyber-totop (piksele),
 * z Global Options -> Przycisk do gory. Wyglad i przejscia: go-to-top.css.
 *
 * @package Cyber_Framework
 */

( function () {
	'use strict';

	const button = document.querySelector( '[data-cyber-totop]' );

	if ( ! button ) {
		return;
	}

	const showAfter = Math.max( 0, parseInt( button.getAttribute( 'data-cyber-totop' ), 10 ) || 0 );
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	let ticking = false;

	function update() {
		ticking = false;
		button.classList.toggle( 'is-visible', window.scrollY > showAfter );
	}

	/*
	 * Zdarzenie scroll strzela dziesiatki razy na sekunde — stan przycisku
	 * liczymy najwyzej raz na klatke obrazu.
	 */
	function onScroll() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}

	button.addEventListener( 'click', function ( event ) {
		window.scrollTo( {
			top: 0,
			behavior: reducedMotion.matches ? 'auto' : 'smooth',
		} );

		/*
		 * Uzytkownik klawiatury zostalby z fokusem na przycisku, ktory za
		 * chwile zniknie — fokus przeskoczylby wtedy w przypadkowe miejsce.
		 * Przenosimy go na pierwszy element strony (skip-link). event.detail
		 * rowne 0 oznacza aktywacje klawiatura: przy kliknieciu mysza skip-link
		 * nie ma sie pokazywac.
		 */
		if ( 0 === event.detail ) {
			const first = document.querySelector( '.cyber-skip-link' );

			if ( first ) {
				first.focus( { preventScroll: true } );
			}
		}
	} );

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	update();
} )();
