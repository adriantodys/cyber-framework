/**
 * Strona pojedynczego produktu — galeria i licznik ilosci.
 *
 * Czysty JS, bez zaleznosci (CLAUDE.md sekcja 2). Kolejkowany wylacznie na
 * stronie produktu (inc/woocommerce-product.php).
 *
 * Dwa niezalezne zadania:
 *
 * 1. GALERIA. Pasek miniatur jest wyzszy niz jego okno, wiec nadmiar chowa sie
 *    pod krawedzia. Przesuwa sie go przytrzymaniem i ruchem — mysza albo
 *    palcem. Uzywamy Pointer Events, ktore obsluguja oba wejscia jednym
 *    zestawem zdarzen; nie ma osobnej sciezki dotykowej, ktora moglaby sie
 *    rozjechac z mysza.
 *
 *    Pasek przesuwamy transformem, a nie natywnym scrollem, bo natywny pasek
 *    przewijania musialby byc ukrywany osobnym hackiem w kazdej przegladarce,
 *    a i tak nie dalby przeciagania mysza.
 *
 *    KLIK KONTRA PRZECIAGNIECIE. Wybor zdjecia rozstrzygamy na pointerup,
 *    a nie zdarzeniem click. Powod jest mechaniczny: pasek przechwytuje
 *    wskaznik (setPointerCapture), zeby przeciaganie dzialalo takze po
 *    wyjechaniu kursorem poza pasek. Przechwycenie przekierowuje pointerup na
 *    element przechwytujacy, wiec przegladarka wystawia potem click na PASKU,
 *    nie na miniaturze — szukanie miniatury w event.target nic by nie dalo.
 *
 *    Za "klik" uznajemy wiec: wcisniecie na miniaturze i puszczenie bez ruchu
 *    wiekszego niz prog. Klawiatura idzie osobna sciezka (event.detail === 0),
 *    bo Enter i spacja generuja click bez zadnego zdarzenia wskaznika.
 *
 *    Na waskim ekranie CSS klada pasek poziomo (flex-direction: row). Skrypt
 *    odczytuje to z wyliczonego stylu, wiec kierunek przeciagania idzie za
 *    ukladem i nie wymaga drugiego progu w JS.
 *
 * 2. LICZNIK ILOSCI. Przyciski plus i minus wchodza hookami WooCommerce
 *    wewnatrz pola ilosci, wiec skrypt tylko zmienia wartosc — z poszanowaniem
 *    min, max i step z atrybutow, ktore ustawil sklep.
 */
( function () {
	'use strict';

	var DRAG_TOLERANCE = 6;

	/**
	 * Ogranicza wartosc do przedzialu.
	 *
	 * @param {number} value Wartosc.
	 * @param {number} min   Dolna granica.
	 * @param {number} max   Gorna granica.
	 * @return {number} Wartosc w przedziale.
	 */
	function clamp( value, min, max ) {
		return Math.min( Math.max( value, min ), max );
	}

	/* ------------------------------------------------------------------ *
	 * Galeria
	 * ------------------------------------------------------------------ */

	var gallery = document.querySelector( '[data-cyber-gallery]' );
	var track = gallery ? gallery.querySelector( '.cyber-product__track' ) : null;
	var stage = document.getElementById( 'cyber-product-image' );

	var offset = 0;
	var dragging = false;
	var startPoint = 0;
	var startOffset = 0;
	var travelled = 0;
	var pressedThumb = null;

	/**
	 * Czy pasek lezy poziomo.
	 *
	 * @return {boolean}
	 */
	function isHorizontal() {
		return 'row' === window.getComputedStyle( track ).flexDirection;
	}

	/**
	 * Najwiekszy dopuszczalny przesuw paska.
	 *
	 * @return {number} Wartosc w pikselach.
	 */
	function maxOffset() {
		var content = isHorizontal() ? track.scrollWidth : track.scrollHeight;
		var window_ = isHorizontal() ? gallery.clientWidth : gallery.clientHeight;

		return Math.max( 0, content - window_ );
	}

	/**
	 * Przepisuje biezacy przesuw na transform.
	 *
	 * @return {void}
	 */
	function applyOffset() {
		track.style.transform = isHorizontal()
			? 'translateX(' + -offset + 'px)'
			: 'translateY(' + -offset + 'px)';
	}

	/**
	 * Przesuwa pasek tak, zeby wskazana miniatura byla w calosci widoczna.
	 *
	 * Potrzebne przy obsludze klawiatura: fokus moze trafic na miniature
	 * schowana pod krawedzia, a ta i tak nie ma natywnego scrolla, ktory
	 * dociagnalby ja sam.
	 *
	 * @param {Element} thumb Miniatura.
	 * @return {void}
	 */
	function revealThumb( thumb ) {
		var horizontal = isHorizontal();
		var start = horizontal ? thumb.offsetLeft : thumb.offsetTop;
		var size = horizontal ? thumb.offsetWidth : thumb.offsetHeight;
		var window_ = horizontal ? gallery.clientWidth : gallery.clientHeight;

		if ( start < offset ) {
			offset = start;
		} else if ( start + size > offset + window_ ) {
			offset = start + size - window_;
		}

		offset = clamp( offset, 0, maxOffset() );
		applyOffset();
	}

	/**
	 * Wstawia zdjecie miniatury na miejsce glownego.
	 *
	 * @param {Element} thumb Klikneta miniatura.
	 * @return {void}
	 */
	function selectThumb( thumb ) {
		if ( ! stage ) {
			return;
		}

		var full = thumb.getAttribute( 'data-full' );

		if ( ! full ) {
			return;
		}

		stage.setAttribute( 'src', full );
		stage.setAttribute( 'srcset', thumb.getAttribute( 'data-srcset' ) || '' );

		var thumbs = track.querySelectorAll( '.cyber-product__thumb' );
		var i;

		for ( i = 0; i < thumbs.length; i++ ) {
			var active = thumbs[ i ] === thumb;

			thumbs[ i ].classList.toggle( 'is-active', active );
			thumbs[ i ].setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		}
	}

	/**
	 * Konczy przeciaganie.
	 *
	 * @param {PointerEvent} event Zdarzenie.
	 * @return {void}
	 */
	function endDrag( event ) {
		if ( ! dragging ) {
			return;
		}

		dragging = false;
		gallery.classList.remove( 'is-dragging' );

		if ( gallery.releasePointerCapture && gallery.hasPointerCapture( event.pointerId ) ) {
			gallery.releasePointerCapture( event.pointerId );
		}

		var thumb = pressedThumb;

		pressedThumb = null;

		// Puszczenie w miejscu na tej samej miniaturze, na ktorej wcisnieto.
		if ( 'pointerup' === event.type && thumb && travelled <= DRAG_TOLERANCE ) {
			selectThumb( thumb );
		}
	}

	if ( gallery && track ) {
		gallery.addEventListener( 'pointerdown', function ( event ) {
			dragging = true;
			travelled = 0;
			startPoint = isHorizontal() ? event.clientX : event.clientY;
			startOffset = offset;

			/*
			 * Miniature czytamy TERAZ, bo pointerdown jest ostatnim zdarzeniem
			 * wskaznika, ktore trafia w prawdziwy element — od setPointerCapture
			 * nizej wszystko celuje juz w pasek.
			 */
			pressedThumb = event.target.closest
				? event.target.closest( '.cyber-product__thumb' )
				: null;

			gallery.classList.add( 'is-dragging' );

			if ( gallery.setPointerCapture ) {
				gallery.setPointerCapture( event.pointerId );
			}
		} );

		gallery.addEventListener( 'pointermove', function ( event ) {
			if ( ! dragging ) {
				return;
			}

			var point = isHorizontal() ? event.clientX : event.clientY;
			var delta = point - startPoint;

			travelled = Math.max( travelled, Math.abs( delta ) );
			offset = clamp( startOffset - delta, 0, maxOffset() );

			applyOffset();
		} );

		gallery.addEventListener( 'pointerup', endDrag );
		gallery.addEventListener( 'pointercancel', endDrag );

		/*
		 * Wylacznie klawiatura. Enter i spacja na miniaturze generuja click
		 * z detail === 0, bez zadnego zdarzenia wskaznika — mysz i palec
		 * obsluguje endDrag(), wiec tutaj nie ma szansy na podwojny wybor.
		 */
		gallery.addEventListener( 'click', function ( event ) {
			if ( 0 !== event.detail ) {
				return;
			}

			var thumb = event.target.closest ? event.target.closest( '.cyber-product__thumb' ) : null;

			if ( thumb ) {
				selectThumb( thumb );
			}
		} );

		gallery.addEventListener( 'focusin', function ( event ) {
			var thumb = event.target.closest ? event.target.closest( '.cyber-product__thumb' ) : null;

			if ( thumb ) {
				revealThumb( thumb );
			}
		} );

		/*
		 * Zmiana szerokosci okna moze przelaczyc pasek miedzy pionem a poziomem
		 * albo zmienic jego dlugosc — poprzedni przesuw bywa wtedy poza zakresem.
		 */
		window.addEventListener( 'resize', function () {
			offset = clamp( offset, 0, maxOffset() );
			applyOffset();
		} );
	}

	/* ------------------------------------------------------------------ *
	 * Licznik ilosci
	 * ------------------------------------------------------------------ */

	/**
	 * Zmienia ilosc o jeden krok.
	 *
	 * Min, max i step czytamy z atrybutow inputa — to sklep decyduje, ile
	 * sztuk wolno kupic, nie skrypt.
	 *
	 * @param {Element} button Przycisk plus albo minus.
	 * @return {void}
	 */
	function stepQuantity( button ) {
		var wrapper = button.closest( '.quantity' );
		var input = wrapper ? wrapper.querySelector( 'input.qty' ) : null;

		if ( ! input ) {
			return;
		}

		var step = parseFloat( input.getAttribute( 'step' ) ) || 1;
		var min = parseFloat( input.getAttribute( 'min' ) );
		var max = parseFloat( input.getAttribute( 'max' ) );
		var current = parseFloat( input.value );

		if ( isNaN( min ) ) {
			min = 0;
		}

		if ( isNaN( current ) ) {
			current = min;
		}

		var next = 'up' === button.getAttribute( 'data-step' )
			? current + step
			: current - step;

		next = Math.max( next, min );

		if ( ! isNaN( max ) && max > 0 ) {
			next = Math.min( next, max );
		}

		if ( next === current ) {
			return;
		}

		input.value = String( next );

		// WooCommerce i wtyczki nasluchuja na change, nie na naszym kliknieciu.
		input.dispatchEvent( new window.Event( 'change', { bubbles: true } ) );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '.cyber-qty__button' ) : null;

		if ( button ) {
			stepQuantity( button );
		}
	} );
} )();
