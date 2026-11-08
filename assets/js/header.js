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

	/*
	 * Ten sam prog obsluguje akordeon podmenu. Nie ma tu drugiej wartosci —
	 * matchMedia czyta dokladnie to, co przyszlo w data-breakpoint.
	 */
	var mobileQuery = window.matchMedia( '(max-width:' + breakpoint + 'px)' );
	var parents = menu.querySelectorAll( '.menu-item-has-children' );
	var labelOpen = toggle.getAttribute( 'data-label-open' ) || '';
	var labelClose = toggle.getAttribute( 'data-label-close' ) || '';

	/**
	 * Ustawia stan menu i synchronizuje atrybuty ARIA przycisku.
	 *
	 * @param {boolean} isOpen Czy menu ma byc otwarte.
	 */
	function setState( isOpen ) {
		menu.classList.toggle( 'is-open', isOpen );

		// Modyfikator przycisku steruje wylacznie animacja kresek w X.
		toggle.classList.toggle( 'cyber-hamburger--active', isOpen );
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

	/**
	 * Zwraca bezposredni link pozycji menu.
	 *
	 * @param {Element} item Element <li>.
	 * @return {Element|null} Link pozycji albo null.
	 */
	function itemLink( item ) {
		return item.querySelector( ':scope > a' );
	}

	/**
	 * Otwiera albo zamyka podmenu pojedynczej pozycji.
	 *
	 * @param {Element} item      Element <li> z klasa menu-item-has-children.
	 * @param {boolean} isExpanded Czy podmenu ma byc rozwiniete.
	 */
	function setSubmenuState( item, isExpanded ) {
		var link = itemLink( item );

		item.classList.toggle( 'is-open', isExpanded );

		if ( link ) {
			link.setAttribute( 'aria-expanded', isExpanded ? 'true' : 'false' );
		}
	}

	/**
	 * Dopasowuje stan akordeonu do aktualnego widoku.
	 *
	 * Powyzej progu atrybut aria-expanded jest usuwany, a nie ustawiany na
	 * "false" — na desktopie podmenu otwiera hover i focus-within, wiec link
	 * nie jest juz elementem rozwijajacym i nie powinien tego oglaszac.
	 */
	function syncSubmenus() {
		var isMobile = mobileQuery.matches;
		var i;
		var link;

		for ( i = 0; i < parents.length; i++ ) {
			parents[ i ].classList.remove( 'is-open' );
			link = itemLink( parents[ i ] );

			if ( ! link ) {
				continue;
			}

			if ( isMobile ) {
				link.setAttribute( 'aria-expanded', 'false' );
			} else {
				link.removeAttribute( 'aria-expanded' );
			}
		}
	}

	toggle.addEventListener( 'click', function () {
		setState( ! isOpen() );
	} );

	/**
	 * Czy link prowadzi pod prawdziwy adres.
	 *
	 * Rodzic podmenu bywa pozycja "Niestandardowy link" z adresem "#" —
	 * taki link nie ma dokad prowadzic i sluzy wylacznie do rozwijania.
	 *
	 * @param {Element} link Link pozycji.
	 * @return {boolean} Czy ma adres inny niz pusty lub "#".
	 */
	function hasDestination( link ) {
		var href = ( link.getAttribute( 'href' ) || '' ).trim();

		return '' !== href && '#' !== href;
	}

	/*
	 * Delegacja zamiast nasluchu na kazdej pozycji: menu moze byc dowolnie
	 * dlugie, a pozycje bez dzieci maja dzialac normalnie — dlatego handler
	 * wychodzi natychmiast, gdy klikniety link nie nalezy do pozycji z podmenu.
	 *
	 * Dwa dotkniecia rodzica: pierwsze rozwija podmenu (link nie przechodzi
	 * dalej), drugie — na pozycji juz rozwinietej — przechodzi do strony
	 * rodzica, jesli ma dokad. Bez tego strona rodzica bylaby na telefonie
	 * nieosiagalna. Link bez adresu albo z "#" tylko zwija i rozwija.
	 */
	menu.addEventListener( 'click', function ( event ) {
		if ( ! mobileQuery.matches ) {
			return;
		}

		var link = event.target.closest( 'a' );

		if ( ! link ) {
			return;
		}

		var item = link.closest( 'li' );

		if ( ! item || ! item.classList.contains( 'menu-item-has-children' ) || itemLink( item ) !== link ) {
			return;
		}

		if ( 'true' === link.getAttribute( 'aria-expanded' ) && hasDestination( link ) ) {
			return;
		}

		event.preventDefault();
		setSubmenuState( item, ! item.classList.contains( 'is-open' ) );
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

	/*
	 * Zdarzenie "change" na matchMedia zamiast "resize": przegladarka wola je
	 * wylacznie przy przekroczeniu progu, a nie przy kazdym pikselu zmiany
	 * szerokosci, wiec handler nie musi sam porownywac stanu.
	 */
	mobileQuery.addEventListener( 'change', function ( event ) {
		var isMobile = event.matches;

		// Po przejsciu na widok desktopowy menu desktopowe przejmuje role panelu.
		if ( ! isMobile && isOpen() ) {
			setState( false );
		}

		syncSubmenus();
	} );

	syncSubmenus();
} )();
