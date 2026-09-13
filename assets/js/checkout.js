/**
 * Strona zamowienia — licznik ilosci w podsumowaniu.
 *
 * Kolejkowany wylacznie na stronie zamowienia (inc/woocommerce-checkout.php).
 *
 * Przebieg jednej zmiany:
 * 1. Klik na minus albo plus wysyla zadanie AJAX z nonce do wlasnego endpointu.
 * 2. Endpoint aktualizuje koszyk po stronie serwera i waliduje stan magazynowy.
 * 3. Skrypt zleca WooCommerce przeliczenie podsumowania zdarzeniem
 *    'update_checkout', a wtyczka przerenderowuje caly blok #order_review.
 *
 * DELEGACJA jest tu wymogiem, nie stylem: po kroku 3 tabela wraz z przyciskami
 * jest w DOM nowym elementem, wiec nasluch podpiety bezposrednio do przycisku
 * przestalby istniec po pierwszej zmianie.
 *
 * jQuery jest uzyte WYLACZNIE do wyslania zdarzenia 'update_checkout' —
 * WooCommerce nasluchuje go przez jQuery i zdarzenie natywne tam nie dotrze.
 * Cala reszta to czysty JS.
 */
( function () {
	'use strict';

	var config = window.cyberCheckout || {};

	if ( ! config.ajaxUrl || ! config.nonce ) {
		return;
	}

	var busy = false;
	var watchdog = null;

	/**
	 * Zleca WooCommerce przeliczenie podsumowania zamowienia.
	 *
	 * Blokady NIE zdejmujemy tutaj. Przeliczenie jest asynchroniczne, a kolejne
	 * klikniecie w jego trakcie wyslaloby ilosc policzona ze starej, zaraz
	 * nieaktualnej wartosci. Flage zeruje dopiero nasluch 'updated_checkout'
	 * na koncu pliku.
	 *
	 * @return {void}
	 */
	function refreshOrderReview() {
		if ( ! window.jQuery ) {
			// Nie ma komu zlecic przeliczenia, wiec nie ma tez na co czekac.
			busy = false;

			return;
		}

		window.jQuery( document.body ).trigger( 'update_checkout' );

		/*
		 * Bezpiecznik. Gdyby przeliczenie sie nie powiodlo i 'updated_checkout'
		 * nigdy nie przyszlo, licznik zostalby zablokowany na stale.
		 */
		window.clearTimeout( watchdog );
		watchdog = window.setTimeout( function () {
			busy = false;
		}, 8000 );
	}

	/**
	 * Blokuje przyciski na czas zadania, zeby szybkie klikniecia nie wyslaly
	 * kilku sprzecznych zmian naraz.
	 *
	 * @param {Element} wrapper Kontener licznika.
	 * @param {boolean} state   Czy zablokowac.
	 * @return {void}
	 */
	function setBusy( wrapper, state ) {
		var buttons = wrapper.querySelectorAll( '.cyber-qty-btn' );
		var i;

		busy = state;
		wrapper.classList.toggle( 'is-busy', state );

		for ( i = 0; i < buttons.length; i++ ) {
			buttons[ i ].disabled = state;
		}
	}

	/**
	 * Wysyla nowa ilosc na serwer.
	 *
	 * @param {Element} wrapper  Kontener licznika.
	 * @param {string}  key      Klucz pozycji koszyka.
	 * @param {number}  quantity Nowa ilosc.
	 * @return {void}
	 */
	function sendQuantity( wrapper, key, quantity ) {
		var body = new window.URLSearchParams();

		body.append( 'action', config.action );
		body.append( 'nonce', config.nonce );
		body.append( 'key', key );
		body.append( 'quantity', String( quantity ) );

		setBusy( wrapper, true );

		window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		} ).then( function ( response ) {
			return response.json();
		} ).then( function ( result ) {
			if ( ! result || ! result.success ) {
				var message = result && result.data && result.data.message
					? result.data.message
					: config.error;

				window.alert( message );
				setBusy( wrapper, false );

				return;
			}

			/*
			 * Przyciskow nie odblokowujemy recznie: ten kontener zniknie razem
			 * z przerenderowanym podsumowaniem. Blokade zdejmuje nasluch
			 * 'updated_checkout', bo zyje ona w zmiennej modulu, a nie w DOM.
			 */
			refreshOrderReview();
		} ).catch( function () {
			window.alert( config.error );
			setBusy( wrapper, false );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '.cyber-qty-btn' ) : null;

		if ( ! button || busy ) {
			return;
		}

		var wrapper = button.closest( '.cyber-qty' );

		if ( ! wrapper ) {
			return;
		}

		var value = wrapper.querySelector( '.cyber-qty-value' );
		var key = wrapper.getAttribute( 'data-key' );
		var step = parseInt( button.getAttribute( 'data-step' ), 10 );
		var current = parseInt( value ? value.textContent : '', 10 );

		if ( ! key || isNaN( step ) || isNaN( current ) ) {
			return;
		}

		var next = current + step;

		/*
		 * Dolna granica to 1, nie 0. Usuwanie pozycji nalezy do koszyka —
		 * zejscie do zera w trakcie skladania zamowienia oproznialoby koszyk
		 * i wyrzucalo klienta ze strony.
		 */
		if ( next < 1 ) {
			return;
		}

		sendQuantity( wrapper, key, next );
	} );

	/* ------------------------------------------------------------------ *
	 * Kupon
	 *
	 * Blok kuponu CELOWO nie jest formularzem — lezy wewnatrz formularza
	 * zamowienia, a HTML zabrania zagniezdzania <form>. Dodanie kuponu
	 * wykonujemy wiec sami, wolajac natywny endpoint WooCommerce wraz
	 * z jego nonce. Nie dublujemy logiki wtyczki, tylko jej interfejs.
	 * ------------------------------------------------------------------ */

	/**
	 * Adres natywnego endpointu WooCommerce dla podanej akcji.
	 *
	 * @param {string} endpoint Nazwa akcji, np. apply_coupon.
	 * @return {string} Adres albo pusty string, gdy WooCommerce go nie podal.
	 */
	function wcAjaxUrl( endpoint ) {
		var params = window.wc_checkout_params;

		if ( ! params || ! params.wc_ajax_url ) {
			return '';
		}

		return params.wc_ajax_url.toString().replace( '%%endpoint%%', endpoint );
	}

	/**
	 * Rozwija albo zwija pola kuponu.
	 *
	 * @param {Element} root Kontener bloku kuponu.
	 * @return {void}
	 */
	function toggleCoupon( root ) {
		var link = root.querySelector( '.cyber-coupon__link' );
		var fields = root.querySelector( '.cyber-coupon__fields' );

		if ( ! link || ! fields ) {
			return;
		}

		var open = fields.hidden;

		fields.hidden = ! open;
		link.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

		if ( open ) {
			var input = fields.querySelector( '#cyber_coupon_code' );

			if ( input ) {
				input.focus();
			}
		}
	}

	/**
	 * Wysyla kod kuponu do WooCommerce i odswieza podsumowanie.
	 *
	 * @param {Element} root Kontener bloku kuponu.
	 * @return {void}
	 */
	function applyCoupon( root ) {
		var input = root.querySelector( '#cyber_coupon_code' );
		var message = root.querySelector( '.cyber-coupon__message' );
		var url = wcAjaxUrl( 'apply_coupon' );
		var params = window.wc_checkout_params || {};
		var code = input ? input.value.trim() : '';

		if ( '' === code || '' === url || busy ) {
			return;
		}

		var body = new window.URLSearchParams();

		body.append( 'security', params.apply_coupon_nonce || '' );
		body.append( 'coupon_code', code );

		busy = true;

		window.fetch( url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		} ).then( function ( response ) {
			return response.text();
		} ).then( function ( html ) {
			/*
			 * Odpowiedzia jest gotowy komunikat WooCommerce — informacja
			 * o dodaniu kuponu albo o bledzie. Wstawiamy go w miejsce na
			 * komunikaty, a nie w losowe miejsce strony.
			 */
			if ( message ) {
				message.innerHTML = html;
			}

			if ( input ) {
				input.value = '';
			}

			refreshOrderReview();
		} ).catch( function () {
			busy = false;

			if ( message ) {
				message.textContent = config.error;
			}
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var target = event.target.closest ? event.target : null;

		if ( ! target ) {
			return;
		}

		var link = target.closest( '.cyber-coupon__link' );

		if ( link ) {
			toggleCoupon( link.closest( '.cyber-coupon' ) );

			return;
		}

		var apply = target.closest( '.cyber-coupon__apply' );

		if ( apply ) {
			applyCoupon( apply.closest( '.cyber-coupon' ) );
		}
	} );

	/*
	 * Zdjecie blokady po przeliczeniu podsumowania.
	 *
	 * Blokada MUSI zyc w zmiennej modulu, a nie w atrybucie disabled przycisku:
	 * po 'updated_checkout' cala tabela wraz z przyciskami jest w DOM nowym
	 * elementem, wiec stan zapisany na starym przycisku i tak by przepadl.
	 *
	 * Konsekwencja, ktora kosztowala blad: skoro flaga przezywa przerenderowanie,
	 * ktos musi ja wyzerowac jawnie. Bez tego nasluchu licznik przyjmowal
	 * dokladnie jedna zmiane, a kazde kolejne klikniecie odbijalo sie od busy.
	 */
	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'updated_checkout', function () {
			window.clearTimeout( watchdog );
			busy = false;
		} );
	}
} )();
