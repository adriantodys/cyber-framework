/**
 * Sekcja "Slider" — inicjalizacja Swipera.
 *
 * Ladowany jako modul ES (type="module", patrz cyber_slider_module_tag())
 * i wylacznie na wpisie, ktory ma sekcje slidera.
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
 * zostala juz zwalidowana. Ten plik nie decyduje, czy petla albo autoplay
 * maja sens — robi to cyber_slider_config().
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
 * Uruchamia jedna karuzele.
 *
 * @param {HTMLElement} el Element .cyber-slider.
 * @return {void}
 */
function initSlider( el ) {
	const autoplay = flag( el, 'autoplay' ) && ! reducedMotion;
	const delay = parseInt( el.getAttribute( 'data-delay' ), 10 ) || 5000;

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

	if ( autoplay ) {
		options.autoplay = {
			delay: delay,
			pauseOnMouseEnter: true,
			// Po kliknieciu strzalki autoplay wraca — nie gasnie na zawsze.
			disableOnInteraction: false,
		};
	}

	if ( flag( el, 'navigation' ) ) {
		options.navigation = {
			prevEl: el.querySelector( '.cyber-slider__prev' ),
			nextEl: el.querySelector( '.cyber-slider__next' ),
		};
	}

	if ( flag( el, 'pagination' ) ) {
		options.pagination = {
			el: el.querySelector( '.cyber-slider__pagination' ),
			clickable: true,
		};
	}

	new Swiper( el, options );
}

document.querySelectorAll( '.cyber-slider' ).forEach( initSlider );
