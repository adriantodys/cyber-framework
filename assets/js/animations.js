/**
 * Animacje wejscia sekcji — silnik wlasny, bez biblioteki.
 *
 * Sekcja z atrybutem data-cyber-animate dostaje klase is-animated, gdy wjedzie
 * na ekran. Sam ruch opisuje assets/css/animations.css — ten plik tylko
 * decyduje KIEDY.
 *
 * Skrypt startowy w <head> (inc/animations.php) ustawia wczesniej:
 *   - klase cyber-anim-ready na <html> — bez niej CSS niczego nie ukrywa,
 *   - window.cyberAnimations = { offset, once } z Global Options -> Animacje.
 *
 * Brak klasy oznacza, ze animacje maja sie nie odpalic (ograniczenie ruchu
 * w systemie, brak IntersectionObserver albo skrypt spoznil sie ponad limit
 * i tresc zostala juz odslonieta). Wtedy ten plik nic nie robi — zaczecie
 * animacji na widocznej juz tresci skonczyloby sie mrugnieciem.
 *
 * @package Cyber_Framework
 */

( function () {
	'use strict';

	const root = document.documentElement;

	if ( ! root.classList.contains( 'cyber-anim-ready' ) ) {
		return;
	}

	// Sygnal dla skryptu startowego: silnik dziala, nie zdejmuj flagi.
	window.cyberAnimationsStarted = true;

	const config = window.cyberAnimations || {};
	const offset = Math.max( 0, parseInt( config.offset, 10 ) || 0 );
	const once = false !== config.once;
	const sections = document.querySelectorAll( '[data-cyber-animate]' );

	if ( ! sections.length ) {
		return;
	}

	/*
	 * Ujemny dolny margines obszaru obserwacji przesuwa "linie startu" w gore:
	 * sekcja musi wjechac o offset pikseli ponad dolna krawedz ekranu.
	 * Prog 0 zamiast procentu widocznosci — sekcja wyzsza niz kilka ekranow
	 * nigdy nie osiagnelaby np. 50% i zostalaby niewidoczna na zawsze.
	 */
	const observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-animated' );

					if ( once ) {
						observer.unobserve( entry.target );
					}

					return;
				}

				/*
				 * Tryb powtarzany: chowamy sekcje dopiero, gdy calkiem zniknie
				 * pod dolna krawedzia. Sekcja przewinieta w gore zostaje
				 * widoczna — inaczej kazdy powrot w gore migalby pusta strona.
				 */
				if ( ! once && entry.boundingClientRect.top > 0 ) {
					entry.target.classList.remove( 'is-animated' );
				}
			} );
		},
		{
			rootMargin: '0px 0px -' + offset + 'px 0px',
			threshold: 0,
		}
	);

	sections.forEach( function ( section ) {
		observer.observe( section );
	} );
} )();
