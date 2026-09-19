/**
 * Sekcja "Licznik" — odliczanie od zera do wartosci z HTML.
 *
 * Bez biblioteki. Wartosc koncowa stoi w HTML od poczatku (bez JS, dla
 * wyszukiwarki i czytnika ekranu); skrypt cofa ja do zera dopiero wtedy, gdy
 * licznik ma sie za chwile pojawic na ekranie, i odlicza w gore z wygaszeniem
 * tempa (ease-out). Kazdy licznik animuje sie raz.
 *
 * Szerokosc liczby jest zamrazana na czas animacji, zeby tekst obok nie
 * skakal przy zmianie liczby cyfr. Formatowanie zgodne z cyber_counter_format()
 * w PHP: przecinek dziesietny, opcjonalnie twarda spacja co trzy cyfry.
 *
 * @package Cyber_Framework
 */
( function () {
	'use strict';

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	/**
	 * @param {number}  value     Liczba.
	 * @param {number}  decimals  Miejsca po przecinku.
	 * @param {boolean} thousands Czy grupowac tysiace.
	 * @return {string}
	 */
	function format( value, decimals, thousands ) {
		const parts = Math.abs( value ).toFixed( decimals ).split( '.' );

		if ( thousands ) {
			parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, ' ' );
		}

		return ( value < 0 ? '-' : '' ) + parts.join( ',' );
	}

	/**
	 * @param {HTMLElement} el        Element .cyber-counter__number.
	 * @param {number}      duration  Czas animacji w ms.
	 * @param {boolean}     thousands Czy grupowac tysiace.
	 * @return {void}
	 */
	function run( el, duration, thousands ) {
		const to = parseFloat( el.dataset.counterTo ) || 0;
		const decimals = parseInt( el.dataset.counterDecimals, 10 ) || 0;
		const start = performance.now();

		// Szerokosc wartosci koncowej trzyma miejsce przez cala animacje.
		el.style.minWidth = el.getBoundingClientRect().width + 'px';

		function frame( now ) {
			const t = Math.min( 1, ( now - start ) / duration );
			const eased = 1 - Math.pow( 1 - t, 3 );

			el.textContent = format( to * eased, decimals, thousands );

			if ( t < 1 ) {
				window.requestAnimationFrame( frame );
			} else {
				el.textContent = format( to, decimals, thousands );
				el.style.minWidth = '';
			}
		}

		el.textContent = format( 0, decimals, thousands );
		window.requestAnimationFrame( frame );
	}

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) {
					return;
				}

				const box = entry.target;
				const duration = parseInt( box.dataset.counterDuration, 10 ) || 2000;
				const thousands = '1' === box.dataset.counterThousands;

				observer.unobserve( box );
				box.querySelectorAll( '.cyber-counter__number' ).forEach( ( el ) => run( el, duration, thousands ) );
			} );
		},
		// Pierwszy piksel sekcji na ekranie — liczba jest jeszcze ponizej krawedzi,
		// wiec nikt nie zobaczy przeskoku z wartosci koncowej na zero.
		{ threshold: 0 }
	);

	document.querySelectorAll( '.cyber-counter[data-counter-duration]' ).forEach( ( box ) => observer.observe( box ) );
}() );
