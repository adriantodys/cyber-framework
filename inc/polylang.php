<?php
/**
 * Integracja z Polylang — wspolna warstwa ochronna.
 *
 * Polylang jest zaleznoscia MIEKKA, tak jak WooCommerce (inc/woocommerce.php)
 * i Contact Form 7 (inc/contact-form-7.php). Motyw dziala bez niej w calosci;
 * przelacznik jezykow w Top Header bez wtyczki po prostu sie nie renderuje.
 *
 * Ten plik jest JEDYNYM miejscem, ktore o tym decyduje (CLAUDE.md sekcja 2).
 * Kolejne moduly wielojezyczne pytaja stad i dopisuja sie do filtra
 * `cyber_polylang_required_by`, zamiast sprawdzac wtyczke u siebie.
 *
 * Trzy poziomy komunikatu:
 * - gosc    — nie widzi nic,
 * - front   — zalogowany administrator widzi podpowiedz w miejscu przelacznika,
 * - panel   — ostrzezenie dla activate_plugins, tylko gdy przelacznik jest wlaczony.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Czy Polylang jest aktywny.
 *
 * Sprawdza funkcje API, z ktorej motyw faktycznie korzysta — nie stala wersji.
 * Polylang Pro definiuje te same funkcje, wiec przechodzi przez ten sam warunek.
 *
 * @return bool
 */
function cyber_is_polylang_active() {
	return function_exists( 'pll_the_languages' );
}

/**
 * Funkcje motywu, ktore w BIEZACEJ konfiguracji wymagaja Polylang.
 *
 * Pusta lista, dopoki admin nie wlaczy przelacznika jezykow — sam brak
 * wtyczki nie jest bledem i nie ma o czym informowac.
 *
 * @return string[] Etykiety dla czlowieka.
 */
function cyber_polylang_required_by() {
	$features = array();

	if ( cyber_get_option( 'topheader_show_languages' ) ) {
		$features[] = __( 'przelacznik jezykow w Top Header', 'cyber-framework' );
	}

	/**
	 * Lista funkcji motywu wymagajacych aktywnego Polylang.
	 *
	 * @param string[] $features Etykiety funkcji.
	 */
	return (array) apply_filters( 'cyber_polylang_required_by', $features );
}

/**
 * Ostrzezenie w panelu o braku Polylang.
 *
 * Tylko dla activate_plugins i tylko wtedy, gdy jakas funkcja jest wlaczona.
 * Kto moze instalowac wtyczki, dostaje od razu odnosnik do wyszukiwarki
 * wtyczek z wpisanym "Polylang".
 *
 * @return void
 */
function cyber_polylang_missing_notice() {
	if ( cyber_is_polylang_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$features = cyber_polylang_required_by();

	if ( array() === $features ) {
		return;
	}

	$message = sprintf(
		/* translators: %s: lista funkcji motywu wymagajacych Polylang. */
		__( 'Cyber Framework: %s wymaga aktywnej wtyczki Polylang. Zainstaluj i wlacz Polylang albo wylacz przelacznik w Global Options → Top Header. Do tego czasu przelacznik sie nie wyswietla — reszta witryny dziala normalnie.', 'cyber-framework' ),
		implode( ', ', $features )
	);

	$link = '';

	if ( current_user_can( 'install_plugins' ) ) {
		$link = sprintf(
			' <a href="%s">%s</a>',
			esc_url( admin_url( 'plugin-install.php?s=polylang&tab=search&type=term' ) ),
			esc_html__( 'Zainstaluj Polylang', 'cyber-framework' )
		);
	}

	printf(
		'<div class="notice notice-warning"><p>%s%s</p></div>',
		esc_html( $message ),
		$link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- zbudowany wyzej z esc_url() i esc_html__().
	);
}
add_action( 'admin_notices', 'cyber_polylang_missing_notice' );

/**
 * Jezyki do przelacznika.
 *
 * Normalizuje surowa liste z pll_the_languages() do ksztaltu, ktorego potrzebuje
 * widok — widok nie zna struktury danych wtyczki. Pusta tablica, gdy Polylang
 * jest nieaktywny albo jezykow jest mniej niz dwa (przelacznik z jedna pozycja
 * nie ma czego przelaczac).
 *
 * Domyslne ustawienia Polylang zostaja: jezyk bez zadnej tresci jest pomijany,
 * a jezyk bez tlumaczenia biezacej strony prowadzi na swoja strone glowna.
 *
 * @return array Lista tablic 'code', 'name', 'url', 'lang', 'current'.
 */
function cyber_language_switcher_items() {
	if ( ! cyber_is_polylang_active() ) {
		return array();
	}

	$raw = pll_the_languages(
		array(
			'raw'           => 1,
			'hide_if_empty' => 1,
		)
	);

	if ( ! is_array( $raw ) ) {
		return array();
	}

	$items = array();

	foreach ( $raw as $language ) {
		if ( empty( $language['slug'] ) || empty( $language['url'] ) ) {
			continue;
		}

		$items[] = array(
			'code'    => strtoupper( sanitize_key( $language['slug'] ) ),
			'name'    => isset( $language['name'] ) ? (string) $language['name'] : '',
			'url'     => (string) $language['url'],
			// Locale WordPressa (en_GB) → tag BCP 47 dla atrybutu lang (en-GB).
			'lang'    => isset( $language['locale'] ) ? str_replace( '_', '-', (string) $language['locale'] ) : '',
			'current' => ! empty( $language['current_lang'] ),
		);
	}

	return count( $items ) < 2 ? array() : $items;
}

/**
 * Podpowiedz na froncie, gdy wlaczonego przelacznika nie da sie wyswietlic.
 *
 * Wylacznie dla zalogowanego administratora — gosc nie dostaje nic. Rozroznia
 * dwie przyczyny: brak wtyczki i wtyczke z mniej niz dwoma jezykami.
 * Zwraca czysty tekst; escapuje widok.
 *
 * @return string
 */
function cyber_language_switcher_hint() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return '';
	}

	if ( ! cyber_is_polylang_active() ) {
		return __( 'Przelacznik jezykow: wtyczka Polylang jest nieaktywna. Goscie nie widza w tym miejscu niczego.', 'cyber-framework' );
	}

	return __( 'Przelacznik jezykow: Polylang ma mniej niz dwa jezyki z trescia. Goscie nie widza w tym miejscu niczego.', 'cyber-framework' );
}
