<?php
/**
 * Logika sekcji stopki: pasek Copyright.
 *
 * Odpowiednik inc/header.php po stronie stopki — plik nie renderuje niczego,
 * przygotowuje dane dla widoku (CLAUDE.md sekcja 4). Struktura modulu jest
 * tozsama z paskiem Top Header i celowo powiela jego wzorzec, zamiast tworzyc
 * nowy (CLAUDE.md sekcja 16a).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Znacznik zastepowany biezacym rokiem w tekscie copyright.
 */
const CYBER_COPYRIGHT_YEAR_TOKEN = '{year}';

/**
 * Buduje dane paska Copyright.
 *
 * Tekst jest statyczny i pochodzi wprost z pola — z jednym wyjatkiem: znacznik
 * {year} zamieniany jest na biezacy rok. Bez tego wpisany na sztywno rok
 * zestarzalby sie w kazdy Nowy Rok, a redaktor rzadko wraca do stopki.
 * Kto nie uzyje znacznika, dostaje dokladnie to, co wpisal.
 *
 * Linki renderujemy tylko wtedy, gdy faktycznie maja adres — brak wymogu
 * wypelnienia obu na raz.
 *
 * Pola prawne sa typu Page Link, wiec zwracaja sam URL wybranej strony (albo
 * pusta wartosc), a nie tablice jak pole Link. Etykieta nie pochodzi z ACF —
 * Page Link jej nie przechowuje — dlatego przekazujemy tylko klucz, a tekst
 * dopisuje widok (CLAUDE.md sekcja 5a).
 *
 * @return array {
 *     @type string $text  Tekst copyright z podstawionym rokiem albo pusty string.
 *     @type array  $links Lista tablic 'key' i 'url'.
 * }
 */
function cyber_copyright_data() {
	$text = cyber_get_option( 'copyright_text' );

	if ( '' !== $text ) {
		$text = str_replace( CYBER_COPYRIGHT_YEAR_TOKEN, wp_date( 'Y' ), $text );
	}

	$data = array(
		'text'  => $text,
		'links' => array(),
	);

	// Klucz => pole. Kolejnosc tablicy jest kolejnoscia linkow na pasku.
	$legal_links = array(
		'privacy' => 'copyright_privacy_link',
		'cookies' => 'copyright_cookies_link',
	);

	foreach ( $legal_links as $key => $option_key ) {
		$url = cyber_get_option( $option_key );

		if ( '' === $url ) {
			continue;
		}

		$data['links'][] = array(
			'key' => $key,
			'url' => $url,
		);
	}

	return $data;
}

/**
 * Czy pasek Copyright ma cokolwiek do pokazania.
 *
 * Ta sama zasada co w Top Header: pusty pasek bylby kolorowa belka bez tresci.
 *
 * @param array $data Wynik cyber_copyright_data().
 * @return bool Czy renderowac pasek.
 */
function cyber_copyright_has_content( array $data ) {
	return '' !== $data['text'] || array() !== $data['links'];
}
