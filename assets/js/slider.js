/**
 * Karuzele Swipera — sekcje "Slider" i "Karuzela kart".
 *
 * Ladowany jako modul ES przez wp_enqueue_script_module() (inc/sections-slider.php)
 * i wylacznie na wpisie, ktory ma ktoras z tych sekcji. Modul nie dostaje
 * danych z wp_localize_script() — etykiety czyta z atrybutu data-i18n
 * kazdej karuzeli, a etykiety przycisku pauzy z data-* na samym przycisku.
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

/*
 * Uzytkownik, ktory w systemie wylaczyl animacje, nie dostaje samoczynnie
 * przewijanej karuzeli. Strzalki, kropki i przeciaganie dzialaja dalej.
 */
const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * Czyta etykiety dla czytnikow ekranu z atrybutu data-i18n.
 *
 * JSON pochodzi z PHP (cyber_slider_i18n()); uszkodzony atrybut daje pusty
 * obiekt, a Swiper uzywa wtedy swoich angielskich domyslnych.
 *
 * @param {HTMLElement} el Element karuzeli.
 * @return {Object}
 */
function strings( el ) {
	try {
		return JSON.parse( el.getAttribute( 'data-i18n' ) || '{}' ) || {};
	} catch ( error ) {
		return {};
	}
}

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
	const labels = strings( el );
	const options = {
		modules: [ Navigation, Pagination, Autoplay, A11y, Keyboard ],
		loop: flag( el, 'loop' ),
		speed: reducedMotion ? 0 : 600,
		grabCursor: true,
		keyboard: { enabled: true, onlyInViewport: true },
		a11y: {
			prevSlideMessage: labels.prev,
			nextSlideMessage: labels.next,
			firstSlideMessage: labels.first,
			lastSlideMessage: labels.last,
			paginationBulletMessage: labels.pagination,
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
 * Przycisk zatrzymania ruchu (WCAG 2.2.2) — wspolny dla autoplay Swipera
 * i tasmy ciaglej.
 *
 * Przycisk wychodzi z PHP z atrybutem hidden (cyber_slider_autoplay_button()):
 * odslaniamy go dopiero tutaj, gdy jest co zatrzymywac. Przy ograniczeniu
 * animacji w systemie ruchu nie ma, wiec przycisk zostaje ukryty — wywolujacy
 * po prostu go nie podpina.
 *
 * aria-pressed="true" oznacza stan zatrzymany; etykieta zmienia sie razem
 * z nim, zeby czytnik ekranu oglaszal, co zrobi nastepne nacisniecie.
 *
 * @param {HTMLElement|null} button   Element .cyber-autoplay albo null.
 * @param {Function}         onToggle Wywolywane z true (zatrzymaj) albo false (wznow).
 * @return {void}
 */
function bindAutoplayButton( button, onToggle ) {
	if ( ! button ) {
		return;
	}

	const labelPause = button.getAttribute( 'data-label-pause' ) || '';
	const labelPlay = button.getAttribute( 'data-label-play' ) || '';

	button.hidden = false;

	button.addEventListener( 'click', () => {
		const paused = 'true' !== button.getAttribute( 'aria-pressed' );

		button.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
		button.setAttribute( 'aria-label', paused ? labelPlay : labelPause );
		onToggle( paused );
	} );
}

/**
 * Podpina przycisk pauzy pod autoplay Swipera.
 *
 * Wywolywane tylko wtedy, gdy autoplay faktycznie jest w opcjach — przy
 * wylaczonym autoplay albo ograniczeniu animacji commonOptions() go nie
 * ustawil i przycisk zostaje ukryty.
 *
 * @param {Object}      swiper  Instancja Swipera.
 * @param {Object}      options Opcje, z ktorymi powstala.
 * @param {HTMLElement} scope   Element, w ktorym szukac przycisku.
 * @param {string}      base    Przedrostek klas karuzeli.
 * @return {void}
 */
function bindSwiperAutoplay( swiper, options, scope, base ) {
	if ( ! options.autoplay ) {
		return;
	}

	bindAutoplayButton( scope.querySelector( '.' + base + '__autoplay' ), ( paused ) => {
		if ( paused ) {
			swiper.autoplay.stop();
		} else {
			swiper.autoplay.start();
		}
	} );
}

/**
 * Slider: jeden slajd na widoku.
 *
 * @param {HTMLElement} el Element .cyber-slider.
 * @return {void}
 */
function initSlider( el ) {
	const options = commonOptions( el, el, 'cyber-slider' );

	// Wideo w tle gra tylko na aktywnym slajdzie.
	if ( el.querySelector( '.cyber-slide__video' ) ) {
		options.on = {
			init: syncVideos,
			slideChange: syncVideos,
		};
	}

	bindSwiperAutoplay( new Swiper( el, options ), options, el, 'cyber-slider' );
}

/**
 * Odtwarza wideo aktywnego slajdu, a pozostale zatrzymuje.
 *
 * Wideo nie ma atrybutu autoplay: przy kilku slajdach z filmem przegladarka
 * pobieralaby i dekodowala wszystkie naraz. Przy ograniczeniu animacji
 * w systemie nic nie startuje (arkusz dodatkowo chowa wideo).
 *
 * @param {Object} swiper Instancja Swipera.
 * @return {void}
 */
function syncVideos( swiper ) {
	swiper.slides.forEach( ( slide, index ) => {
		const video = slide.querySelector( '.cyber-slide__video' );

		if ( ! video ) {
			return;
		}

		if ( index === swiper.activeIndex && ! reducedMotion ) {
			// Odrzucenie odtwarzania (np. tryb oszczedzania energii) zostawia zdjecie.
			video.play().catch( () => {} );
		} else {
			video.pause();
		}
	} );
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
	const scope = el.closest( '.cyber-carousel-wrap' ) || el;

	/*
	 * Tryb ciagly nie uzywa Swipera: to tasma z animacja CSS (patrz
	 * cyber_carousel_continuous_items() w PHP). Uruchomienie Swipera na niej
	 * nadpisaloby transform tasmy i zatrzymalo animacje. Skrypt doklada tu
	 * wylacznie przycisk pauzy: klasa stanu .cyber-carousel--paused zatrzymuje
	 * animacje w arkuszu (animation-play-state). Przy ograniczeniu animacji
	 * tasma i tak stoi, wiec przycisk zostaje ukryty.
	 */
	if ( el.classList.contains( 'cyber-carousel--continuous' ) ) {
		if ( ! reducedMotion ) {
			bindAutoplayButton( scope.querySelector( '.cyber-carousel__autoplay' ), ( paused ) => {
				el.classList.toggle( 'cyber-carousel--paused', paused );
			} );
		}

		return;
	}

	const options = commonOptions( el, scope, 'cyber-carousel' );

	// Karuzela jedzie dalej rowniez pod kursorem — inaczej niz slider.
	if ( options.autoplay ) {
		options.autoplay.pauseOnMouseEnter = false;
	}

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

	bindSwiperAutoplay( new Swiper( el, options ), options, scope, 'cyber-carousel' );
}

document.querySelectorAll( '.cyber-slider' ).forEach( initSlider );
document.querySelectorAll( '.cyber-carousel' ).forEach( initCarousel );
