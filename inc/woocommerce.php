<?php
/**
 * Integracja z WooCommerce — wspolna warstwa ochronna.
 *
 * WooCommerce jest w tym motywie zaleznoscia MIEKKA, inaczej niz ACF PRO
 * (patrz inc/acf.php). Motyw dziala bez niego w calosci, a funkcje, ktore go
 * wymagaja, wylaczaja sie same zamiast wywracac strone bialym ekranem.
 *
 * Ten plik jest JEDYNYM miejscem, ktore o tym decyduje. Kolejne moduly sklepowe
 * (koszyk w stopce, mini-cart, strony sklepu) pytaja stad i dopisuja sie do
 * cyber_woocommerce_required_by(), zamiast wolac class_exists( 'WooCommerce' )
 * u siebie. Rozsypanie tego warunku po plikach oznacza, ze przy wylaczeniu
 * wtyczki czesc miejsc przestaje dzialac cicho, a czesc glosno.
 *
 * Zasada komunikatow:
 * - panel   — ostrzezenie dla kogos, kto moze zainstalowac wtyczke,
 * - front   — podpowiedz WYLACZNIE dla zalogowanego administratora,
 * - gosc    — nie widzi nic; brak wtyczki to nie jest jego problem.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Czy WooCommerce jest aktywne.
 *
 * Jedno zrodlo prawdy dla calego motywu.
 *
 * @return bool
 */
function cyber_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Funkcje motywu, ktore w BIEZACEJ konfiguracji wymagaja WooCommerce.
 *
 * Zwraca etykiety dla czlowieka, nie identyfikatory — trafiaja wprost do tresci
 * komunikatu. Lista jest pusta, dopoki admin nie wlaczy zadnej z tych funkcji:
 * sam brak WooCommerce nie jest bledem i nie ma o czym informowac.
 *
 * Punkt rozszerzenia dla przyszlych modulow sklepowych: filtr
 * `cyber_woocommerce_required_by`. Dopisanie sie do niego sprawia, ze modul
 * dostaje komplet ostrzezen (panel + front) bez wlasnego kodu.
 *
 * @return string[] Etykiety funkcji wymagajacych WooCommerce.
 */
function cyber_woocommerce_required_by() {
	$features = array();

	if ( 'woocommerce' === cyber_get_option( 'header_variant' ) ) {
		$features[] = __( 'wariant headera „WooCommerce” (ikony konta i koszyka)', 'cyber-framework' );
	}

	/**
	 * Lista funkcji motywu wymagajacych aktywnego WooCommerce.
	 *
	 * @param string[] $features Etykiety funkcji.
	 */
	return (array) apply_filters( 'cyber_woocommerce_required_by', $features );
}

/**
 * Tresc komunikatu o braku WooCommerce.
 *
 * Zwraca CZYSTY tekst, bez znacznikow i bez escapowania — to zadanie miejsca
 * wypisania (CLAUDE.md sekcja 8). Pusty string oznacza "nie ma o czym mowic"
 * i wystepuje w DWOCH przypadkach: wtyczka jest aktywna albo zadna funkcja
 * motywu jej nie potrzebuje.
 *
 * Funkcja sprawdza obecnosc wtyczki SAMA, mimo ze robi to juz kazde miejsce
 * wywolania. Bez tego jej nazwa klamie: przy aktywnym WooCommerce zwracalaby
 * pelny komunikat o braku, a pierwszy kod, ktory zapomnialby o wlasnym
 * warunku, pokazalby ostrzezenie na dzialajacym sklepie.
 *
 * @return string Komunikat albo pusty string.
 */
function cyber_woocommerce_missing_message() {
	if ( cyber_is_woocommerce_active() ) {
		return '';
	}

	$features = cyber_woocommerce_required_by();

	if ( array() === $features ) {
		return '';
	}

	return sprintf(
		/* translators: %s: lista funkcji motywu wymagajacych WooCommerce. */
		__( 'Cyber Framework: %s wymaga aktywnej wtyczki WooCommerce. Bez niej ta czesc motywu po prostu sie nie renderuje — reszta witryny dziala normalnie.', 'cyber-framework' ),
		implode( ', ', $features )
	);
}

/**
 * Ostrzezenie w panelu administracyjnym.
 *
 * Pokazywane tylko komus, kto moze z nim cokolwiek zrobic (activate_plugins),
 * i tylko wtedy, gdy jakas funkcja faktycznie jest wlaczona.
 *
 * @return void
 */
function cyber_woocommerce_missing_notice() {
	if ( cyber_is_woocommerce_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = cyber_woocommerce_missing_message();

	if ( '' === $message ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html( $message )
	);
}
add_action( 'admin_notices', 'cyber_woocommerce_missing_notice' );

/**
 * Podpowiedz na froncie dla zalogowanego administratora.
 *
 * Widok wypisuje ja w miejscu, ktore mialo naleze do WooCommerce, zeby admin
 * zobaczyl PRZYCZYNE pustego miejsca zamiast zastanawiac sie, co sie zepsulo.
 * Gosc witryny nie dostaje nic.
 *
 * Zwraca czysty tekst — escapuje widok.
 *
 * @param string $feature Nazwa funkcji, np. 'ikony konta i koszyka'.
 * @return string Tekst podpowiedzi albo pusty string.
 */
function cyber_woocommerce_missing_hint( $feature ) {
	if ( cyber_is_woocommerce_active() || ! current_user_can( 'activate_plugins' ) ) {
		return '';
	}

	return sprintf(
		/* translators: %s: nazwa funkcji motywu. */
		__( 'Wymaga WooCommerce: %s. Widzisz to tylko Ty, jako administrator.', 'cyber-framework' ),
		$feature
	);
}

/**
 * Liczba pozycji w koszyku.
 *
 * Podwojny warunek jest celowy: WC() istnieje takze wtedy, gdy koszyk nie zostal
 * jeszcze zainicjowany (REST, cron, czesc zadan w panelu), a odwolanie do
 * niezainicjowanego koszyka jest bledem krytycznym.
 *
 * @return int Liczba pozycji, 0 gdy koszyka nie ma.
 */
function cyber_wc_cart_count() {
	if ( ! cyber_is_woocommerce_active() || ! function_exists( 'WC' ) ) {
		return 0;
	}

	$wc = WC();

	if ( ! $wc || ! isset( $wc->cart ) || ! $wc->cart ) {
		return 0;
	}

	return (int) $wc->cart->get_cart_contents_count();
}

/**
 * Dane menu WooCommerce w headerze.
 *
 * null oznacza "nie ma czego renderowac" — widok nie musi znac powodu.
 *
 * @return array|null {
 *     @type string $account_url Adres strony Moje konto.
 *     @type string $cart_url    Adres koszyka.
 *     @type int    $count       Liczba pozycji w koszyku.
 * }
 */
function cyber_header_woocommerce_data() {
	if ( ! cyber_is_woocommerce_active() ) {
		return null;
	}

	return array(
		'account_url' => wc_get_page_permalink( 'myaccount' ),
		'cart_url'    => wc_get_cart_url(),
		'count'       => cyber_wc_cart_count(),
	);
}

/**
 * Aktualizuje licznik koszyka po dodaniu produktu, bez przeladowania strony.
 *
 * WooCommerce podmienia element pasujacy do selektora wlasnym AJAX-em. Klucz
 * tablicy to selektor CSS, wartosc to nowy markup CALEGO elementu, dlatego
 * template-part musi wypisac rowniez znacznik zewnetrzny.
 *
 * Markup zyje w widoku, nie tutaj — bufor jest jedynie sposobem na oddanie go
 * WooCommerce w postaci stringa, ktorego ten filtr wymaga.
 *
 * @param array $fragments Fragmenty do podmiany.
 * @return array Fragmenty z licznikiem koszyka.
 */
function cyber_wc_cart_count_fragment( $fragments ) {
	ob_start();
	get_template_part(
		'template-parts/header/cart-count',
		null,
		array( 'count' => cyber_wc_cart_count() )
	);
	$fragments['.cyber-wc-count'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'cyber_wc_cart_count_fragment' );
