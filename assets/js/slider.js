/**
 * Karuzele Swipera — sekcje "Slider" i "Karuzela kart".
 *
 * Ladowany jako modul ES (type="module", patrz cyber_slider_module_tag())
 * i wylacznie na wpisie, ktory ma ktoras z tych sekcji.
 *
 * Importujemy rdzen i piec modulow zamiast pelnej paczki swiper-bundle:
 * 27,7 kB gzip zamiast 43,8 kB. Sciezki sa wzgledne wobec tego pliku, a wersja
 * biblioteki siedzi w nazwie katalogu, wiec aktualizacja Swipera to zmiana
 * jednej stalej w PHP i szesciu sciezek ponizej.
 *
 * UWAGA: rdzen eksportuje klase pod skrocona nazwa "S" (export{Swiper as S}).
 * To wewnetrzna nazwa pliku swiper-core.min.mjs z wersji 14.2.0 — przy
 * aktualizacji biblioteki trzeba ja sprawdzic, bo nie jest czescia
 * publicznego API.
 *
 * Konfiguracja kazdej karuzeli przychodzi w atrybutach data-* z PHP, gdzie
 * zostala juz zwalidowana.
 */
import { S as Swiper } from '../vendor/swiper-14.2.0/shared/swiper-core.min.mjs';
import Navigation from '../vendor/swiper-14.2.0/modules/navigation.min.mjs';
import Pagination from '../vendor/swiper-14.2.0/modules/pagination.min.mjs';
import Autoplay from '../vendor/swiper-14.2.0/modules/autoplay.min.mjs';
import A11y from '../vendor/swiper-14.2.0/modules/a11y.min.mjs';
import Keyboard from '../vendor/swiper-14.2.0/modules/keyboard.min.mjs';

const strings = window.cyberSlider || {};

/*
 * Uzytkownik, ktory w systemie wylaczyl animacje, nie dostaje samoczynnie
 * przewijanej karuzeli. Strzalki, kropki i przeciaganie dzialaja dalej.
 */
const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Czyta wlacznik z atrybutu data-*.
 *
 * @param {HTMLElement} el   Element karuzeli.
 * @param {string}      name Nazwa atrybutu bez przedrostka data-.
 * @return {boolean}
 */
function flag( el, name ) {
	return '1' === el.getAttribute( 'data-' + name );
}

/**
 * Czyta liczbe z atrybutu data-*.
 *
 * @param {HTMLElement} el       Element karuzeli.
 * @param {string}      name     Nazwa atrybutu bez przedrostka data-.
 * @param {number}      fallback Wartosc, gdy atrybutu brak.
 * @return {number}
 */
function number( el, name, fallback ) {
	const value = parseInt( el.getAttribute( 'data-' + name ), 10 );

	return isNaN( value ) ? fallback : value;
}

/**
 * Opcje wspolne dla obu rodzajow karuzel.
 *
 * @param {HTMLElement} el    Element karuzeli.
 * @param {HTMLElement} scope Element, w ktorym szukac strzalek i kropek.
 * @param {string}      base  Przedrostek klas strzalek i kropek.
 * @return {Object}
 */
function commonOptions( el, scope, base ) {
	const options = {
		modules: [ Navigation, Pagination, Autoplay, A11y, Keyboard ],
		loop: flag( el, 'loop' ),
		speed: reducedMotion ? 0 : 600,
		grabCursor: true,
		keyboard: { enabled: true, onlyInViewport: true },
		a11y: {
			prevSlideMessage: strings.prev,
			nextSlideMessage: strings.next,
			firstSlideMessage: strings.first,
			lastSlideMessage: strings.last,
			paginationBulletMessage: strings.pagination,
		},
	};

	if ( flag( el, 'autoplay' ) && ! reducedMotion ) {
		options.autoplay = {
			delay: number( el, 'delay', 5000 ),
			pauseOnMouseEnter: true,
			// Po kliknieciu strzalki autoplay wraca — nie gasnie na zawsze.
			disableOnInteraction: false,
		};
	}

	if ( flag( el, 'navigation' ) ) {
		options.navigation = {
			prevEl: scope.querySelector( '.' + base + '__prev' ),
			nextEl: scope.querySelector( '.' + base + '__next' ),
		};
	}

	if ( flag( el, 'pagination' ) ) {
		options.pagination = {
			el: scope.querySelector( '.' + base + '__pagination' ),
			clickable: true,
		};
	}

	return options;
}

/**
 * Slider: jeden slajd na widoku.
 *
 * @param {HTMLElement} el Element .cyber-slider.
 * @return {void}
 */
function initSlider( el ) {
	new Swiper( el, commonOptions( el, el, 'cyber-slider' ) );
}

/**
 * Karuzela kart: kilka kart na widoku, liczba zalezna od szerokosci ekranu.
 *
 * Breakpointy Swipera licza sie od dolu (min-width), a motyw trzyma progi
 * jako max-width 767 i 980 (CLAUDE.md sekcja 18) — stad 768 i 981.
 *
 * Strzalki i kropki leza obok kontenera .swiper, w .cyber-carousel-wrap,
 * wiec szukamy ich tam, a nie wewnatrz karuzeli.
 *
 * @param {HTMLElement} el Element .cyber-carousel.
 * @return {void}
 */
function initCarousel( el ) {
	/*
	 * Tryb ciagly nie uzywa Swipera: to tasma z animacja CSS (patrz
	 * cyber_carousel_continuous_items() w PHP). Uruchomienie Swipera na niej
	 * nadpisaloby transform tasmy i zatrzymalo animacje.
	 */
	if ( el.classList.contains( 'cyber-carousel--continuous' ) ) {
		return;
	}

	const scope = el.closest( '.cyber-carousel-wrap' ) || el;
	const options = commonOptions( el, scope, 'cyber-carousel' );

	const perView = number( el, 'per-view', 4 );
	const perViewTablet = number( el, 'per-view-tablet', 2 );
	const perViewMobile = number( el, 'per-view-mobile', 1 );
	const gap = number( el, 'gap', 24 );
	const maxPerView = Math.max( perView, perViewTablet, perViewMobile );

	/*
	 * Szybkosc przesuwania dotyczy obu trybow. Przy ograniczeniu animacji
	 * commonOptions() ustawil juz speed 0 — nie nadpisujemy tego.
	 */
	if ( ! reducedMotion ) {
		options.speed = number( el, 'speed', 600 );
	}

	const slides = el.querySelectorAll( '.swiper-slide' ).length;

	/*
	 * Petla potrzebuje wiecej kart, niz widac naraz — inaczej Swiper ja wylacza
	 * z ostrzezeniem w konsoli. Porownujemy z NAJWIEKSZA liczba widocznych kart,
	 * zeby petla nie zalezala od tego, na jakim ekranie strone otwarto.
	 */
	if ( options.loop && slides <= maxPerView ) {
		options.loop = false;
	}

	options.slidesPerView = perViewMobile;
	options.spaceBetween = gap;
	options.breakpoints = {
		768: { slidesPerView: perViewTablet },
		981: { slidesPerView: perView },
	};

	// Karty mniej niz miejsc na widoku: nie ma czego przewijac.
	options.watchOverflow = true;

	new Swiper( el, options );
}

document.querySelectorAll( '.cyber-slider' ).forEach( initSlider );
document.querySelectorAll( '.cyber-carousel' ).forEach( initCarousel );
