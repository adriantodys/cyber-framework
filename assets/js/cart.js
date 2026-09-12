/**
 * Koszyk WooCommerce — licznik ilosci.
 *
 * Czysty JS, bez zaleznosci (CLAUDE.md sekcja 2). Kolejkowany wylacznie
 * na stronie koszyka.
 *
 * Dwa zadania:
 * 1. Doklada przyciski minus i plus wokol pola ilosci. Bez JS zostaje samo pole
 *    liczbowe, ktore nadal dziala — to progresywne ulepszenie.
 * 2. Przelicza koszyk po zmianie ilosci. Projekt nie przewiduje przycisku
 *    "Zaktualizuj koszyk", wiec jest on ukryty CSS-em — bez tego skryptu zmiana
 *    ilosci nie robilaby nic. display:none nie blokuje klikniecia z poziomu JS.
 *
 * Etykiety przychodza z PHP (wp_localize_script), zeby zostaly tlumaczalne.
 */
( function () {
	'use strict';

	var form = document.querySelector( '.woocommerce-cart-form' );

	if ( ! form ) {
		return;
	}

	var updateButton = form.querySelector( '[name="update_cart"]' );
	var labels = window.cyberCart || {};
	var timer = null;

	/**
	 * Zleca przeliczenie koszyka.
	 *
	 * Opoznienie chroni przed wyslaniem formularza przy kazdym kliknieciu —
	 * liczy sie stan po ostatniej zmianie, nie po kazdej z osobna.
	 */
	function scheduleUpdate() {
		if ( ! updateButton ) {
			return;
		}

		window.clearTimeout( timer );

		timer = window.setTimeout( function () {
			updateButton.disabled = false;
			updateButton.click();
		}, 600 );
	}

	/**
	 * Zmienia wartosc pola o podany krok, respektujac jego wlasne ograniczenia.
	 *
	 * @param {HTMLInputElement} input Pole ilosci.
	 * @param {number}           step  Krok: -1 albo 1.
	 */
	function changeQuantity( input, step ) {
		var current = parseInt( input.value, 10 );
		var min = parseInt( input.getAttribute( 'min' ), 10 );
		var max = parseInt( input.getAttribute( 'max' ), 10 );

		if ( isNaN( current ) ) {
			current = 0;
		}

		if ( isNaN( min ) ) {
			min = 0;
		}

		var next = current + step;

		if ( next < min ) {
			return;
		}

		if ( ! isNaN( max ) && next > max ) {
			return;
		}

		input.value = next;
		scheduleUpdate();
	}

	/**
	 * Tworzy przycisk kroku.
	 *
	 * @param {string} sign  Znak na przycisku.
	 * @param {string} label Dostepna nazwa przycisku.
	 * @return {HTMLButtonElement} Gotowy przycisk.
	 */
	function makeButton( sign, label ) {
		var button = document.createElement( 'button' );

		button.type = 'button';
		button.className = 'cyber-qty-btn';
		button.textContent = sign;
		button.setAttribute( 'aria-label', label || sign );

		return button;
	}

	var wrappers = form.querySelectorAll( '.quantity' );
	var i;

	for ( i = 0; i < wrappers.length; i++ ) {
		( function ( wrapper ) {
			var input = wrapper.querySelector( 'input.qty' );

			// Pozycje o stalej ilosci maja pole ukryte — nie ma czym sterowac.
			if ( ! input || 'hidden' === input.type ) {
				return;
			}

			var minus = makeButton( '−', labels.decrease );
			var plus = makeButton( '+', labels.increase );

			minus.addEventListener( 'click', function () {
				changeQuantity( input, -1 );
			} );

			plus.addEventListener( 'click', function () {
				changeQuantity( input, 1 );
			} );

			wrapper.insertBefore( minus, input );
			wrapper.appendChild( plus );

			// Wpisanie liczby z klawiatury ma dzialac tak samo jak przyciski.
			input.addEventListener( 'change', scheduleUpdate );
		} )( wrappers[ i ] );
	}
} )();
