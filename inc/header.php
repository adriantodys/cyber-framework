<?php
/**
 * Logika modulu "Header Desktop".
 *
 * Plik nie renderuje niczego — przygotowuje argumenty dla wp_nav_menu() oraz
 * nanosi klasy motywu na znaczniki generowane przez rdzen WordPressa. Markup
 * zyje w template-parts/header/header.php (CLAUDE.md sekcja 4).
 *
 * Lokalizacja menu: 'primary', zarejestrowana w inc/setup.php. Modul jej NIE
 * rejestruje ponownie i nie tworzy wlasnej (CLAUDE.md sekcja 21).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slug lokalizacji menu glownego.
 */
const CYBER_HEADER_MENU_LOCATION = 'primary';

/**
 * Argumenty wp_nav_menu() dla menu glownego w headerze.
 *
 * Wyrownanie trafia do klasy jako modyfikator, a nie do zmiennej CSS — zmienia
 * uklad elementow, a nie pojedyncza wartosc (patrz cyber_header_css()).
 *
 * fallback_cb ustawione na false swiadomie: gdy zadne menu nie jest przypisane
 * do lokalizacji, header nie wyswietla listy wszystkich stron witryny, tylko
 * nie renderuje nawigacji w ogole.
 *
 * @param string $alignment Wyrownanie menu: left, center albo right.
 * @return array Argumenty dla wp_nav_menu().
 */
function cyber_header_menu_args( $alignment ) {
	return array(
		'theme_location' => CYBER_HEADER_MENU_LOCATION,
		'container'      => false,
		'menu_class'     => 'cyber-menu cyber-menu--' . $alignment,
		'depth'          => 0,
		'fallback_cb'    => false,
	);
}

/**
 * Dokleja klasy motywu do znacznika <ul> podmenu.
 *
 * Rdzen WordPressa nadaje podmenu wylacznie klase 'sub-menu'. Zamiast pisac
 * wlasnego Walkera tylko po to, zeby dolozyc dwie klasy, korzystamy z filtra —
 * mniej kodu do utrzymania i zero ryzyka rozjechania sie z rdzeniem.
 *
 * @param string[] $classes Klasy znacznika <ul> podmenu.
 * @param stdClass $args    Argumenty wywolania wp_nav_menu().
 * @param int      $depth   Poziom zagniezdzenia.
 * @return string[] Klasy z modyfikatorami motywu.
 */
function cyber_header_submenu_css_class( $classes, $args, $depth ) {
	unset( $depth );

	if ( ! isset( $args->theme_location ) || CYBER_HEADER_MENU_LOCATION !== $args->theme_location ) {
		return $classes;
	}

	$classes[] = 'cyber-submenu';
	$classes[] = 'cyber-submenu--' . cyber_get_option( 'header_submenu_alignment' );

	return $classes;
}
add_filter( 'nav_menu_submenu_css_class', 'cyber_header_submenu_css_class', 10, 3 );
