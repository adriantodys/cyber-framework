/**
 * Galeria: filtry kategorii i lightbox.
 *
 * BEZ BIBLIOTEKI. Lightbox stoi na natywnym <dialog>, wiec z przegladarki
 * dostajemy za darmo: zamkniecie klawiszem Escape, pulapke fokusu wewnatrz
 * okna, powrot fokusu na klikniete zdjecie po zamknieciu i tlo blokujace
 * klikniecia. Zostaje do napisania zmiana zdjecia (strzalki, klawiatura)
 * i podpis — kilkadziesiat linii zamiast kolejnej zaleznosci (CLAUDE.md
 * sekcja 2).
 *
 * KAZDE ZDJECIE JEST LINKIEM do pliku w pelnym rozmiarze. Skrypt przechwytuje
 * klikniecie; bez niego galeria nadal dziala — zdjecie otwiera sie w karcie.
 *
 * Filtr pokazuje zdjecia jednej kategorii. Lightbox zna tylko zdjecia
 * WIDOCZNE w chwili otwarcia, wiec strzalki nie przeskakuja do ukrytych.
 *
 * @package Cyber_Framework
 */
( function () {
	'use strict';

	var strings = window.cyberGallery || { close: 'Zamknij', prev: 'Poprzednie', next: 'Nastepne', of: 'z' };
	var dialog = null;
	var image = null;
	var caption = null;
	var counter = null;
	var links = [];
	var current = 0;

	/**
	 * Tworzy okno lightboxa przy pierwszym uzyciu.
	 *
	 * @return {HTMLElement|null} Element <dialog> albo null, gdy przegladarka go nie zna.
	 */
	function build() {
		if ( dialog ) {
			return dialog;
		}

		if ( 'undefined' === typeof window.HTMLDialogElement ) {
			return null;
		}

		dialog = document.createElement( 'dialog' );
		dialog.className = 'cyber-lightbox';
		dialog.innerHTML =
			'<div class="cyber-lightbox__inner">' +
				'<img class="cyber-lightbox__image" src="" alt="" />' +
				'<p class="cyber-lightbox__caption"></p>' +
				'<p class="cyber-lightbox__counter"></p>' +
			'</div>' +
			'<button type="button" class="cyber-lightbox__close" data-action="close"></button>' +
			'<button type="button" class="cyber-lightbox__prev" data-action="prev"></button>' +
			'<button type="button" class="cyber-lightbox__next" data-action="next"></button>';

		image = dialog.querySelector( '.cyber-lightbox__image' );
		caption = dialog.querySelector( '.cyber-lightbox__caption' );
		counter = dialog.querySelector( '.cyber-lightbox__counter' );

		dialog.querySelector( '[data-action="close"]' ).setAttribute( 'aria-label', strings.close );
		dialog.querySelector( '[data-action="prev"]' ).setAttribute( 'aria-label', strings.prev );
		dialog.querySelector( '[data-action="next"]' ).setAttribute( 'aria-label', strings.next );

		dialog.addEventListener( 'click', function ( event ) {
			var action = event.target.dataset ? event.target.dataset.action : '';

			if ( 'prev' === action ) {
				show( current - 1 );
			} else if ( 'next' === action ) {
				show( current + 1 );
			} else if ( 'close' === action || event.target === dialog ) {
				// Klikniecie w tlo poza zdjeciem zamyka okno.
				dialog.close();
			}
		} );

		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				show( current - 1 );
			} else if ( 'ArrowRight' === event.key ) {
				show( current + 1 );
			}
		} );

		document.body.appendChild( dialog );

		return dialog;
	}

	/**
	 * Pokazuje zdjecie o danym numerze (zawija sie na koncach listy).
	 *
	 * @param {number} index Numer zdjecia.
	 * @return {void}
	 */
	function show( index ) {
		if ( ! links.length ) {
			return;
		}

		current = ( index + links.length ) % links.length;

		var link = links[ current ];
		var thumb = link.querySelector( 'img' );
		var text = link.dataset.caption || '';

		image.src = link.getAttribute( 'href' );
		image.alt = thumb ? thumb.getAttribute( 'alt' ) || '' : '';
		caption.textContent = text;
		caption.hidden = '' === text;
		counter.textContent = ( current + 1 ) + ' ' + strings.of + ' ' + links.length;

		// Sasiednie zdjecie ma byc gotowe, zanim ktos kliknie strzalke.
		var next = links[ ( current + 1 ) % links.length ];

		if ( next ) {
			new window.Image().src = next.getAttribute( 'href' );
		}

		var single = links.length < 2;

		dialog.querySelector( '[data-action="prev"]' ).hidden = single;
		dialog.querySelector( '[data-action="next"]' ).hidden = single;
	}

	/**
	 * Otwiera lightbox na klikniete zdjecie.
	 *
	 * @param {HTMLElement} gallery Kontener galerii.
	 * @param {HTMLElement} link    Klikniety odnosnik.
	 * @return {boolean} Czy okno sie otworzylo.
	 */
	function open( gallery, link ) {
		if ( ! build() ) {
			return false;
		}

		// Tylko zdjecia widoczne po biezacym filtrze.
		links = Array.prototype.filter.call(
			gallery.querySelectorAll( '.cyber-gallery__link' ),
			function ( el ) {
				return ! el.closest( '.cyber-gallery__item' ).hidden;
			}
		);

		var index = links.indexOf( link );

		if ( index < 0 ) {
			return false;
		}

		show( index );
		dialog.showModal();

		return true;
	}

	/**
	 * Filtrowanie zdjec po kategorii galerii.
	 *
	 * @param {HTMLElement} gallery Kontener galerii.
	 * @param {HTMLElement} button  Klikniety filtr.
	 * @return {void}
	 */
	function filter( gallery, button ) {
		var term = button.dataset.term;

		gallery.querySelectorAll( '.cyber-gallery__filter' ).forEach( function ( el ) {
			var active = el === button;

			el.classList.toggle( 'is-active', active );
			el.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		} );

		gallery.querySelectorAll( '.cyber-gallery__item' ).forEach( function ( item ) {
			var terms = ( item.dataset.terms || '' ).split( ' ' );

			item.hidden = 'all' !== term && terms.indexOf( term ) < 0;
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.cyber-gallery__filter' );

		if ( button ) {
			filter( button.closest( '.cyber-gallery' ), button );
			return;
		}

		var link = event.target.closest( '.cyber-gallery--lightbox .cyber-gallery__link' );

		if ( ! link || event.metaKey || event.ctrlKey || 0 !== event.button ) {
			return;
		}

		if ( open( link.closest( '.cyber-gallery' ), link ) ) {
			event.preventDefault();
		}
	} );
}() );
