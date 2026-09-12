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
 * Wskaznik podmenu tez jest modyfikatorem klasy, a nie zmienna CSS: wlaczenie
 * i wylaczenie pseudoelementu to obecnosc reguly, a nie jej wartosc.
 *
 * @param string $alignment      Wyrownanie menu: left, center albo right.
 * @param bool   $with_indicator Czy pozycje z podmenu maja dostac strzalke.
 * @return array Argumenty dla wp_nav_menu().
 */
function cyber_header_menu_args( $alignment, $with_indicator = true ) {
	$menu_class = 'cyber-menu cyber-menu--' . $alignment;

	if ( $with_indicator ) {
		$menu_class .= ' cyber-menu--with-indicator';
	}

	return array(
		'theme_location' => CYBER_HEADER_MENU_LOCATION,
		'container'      => false,
		'menu_class'     => $menu_class,
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

/**
 * Dane przycisku CTA w headerze.
 *
 * Przycisk pojawia sie wylacznie wtedy, gdy ma CO pokazac i DOKAD prowadzic.
 * Sam tekst bez adresu albo sam adres bez tekstu to pole niedokonczone,
 * a nie polowiczny przycisk — dlatego null, nie czesciowa tablica.
 *
 * Modul nie ma wlasnych pol koloru ani geometrii: wygladem steruje wylacznie
 * rozmiar 'medium' z zakladki Przyciski (CLAUDE.md sekcja 7).
 *
 * @return array|null Tablica 'text' i 'url' albo null.
 */
function cyber_header_cta_data() {
	$text = cyber_get_option( 'header_cta_text' );
	$url  = cyber_get_option( 'header_cta_url' );

	if ( '' === $text || '' === $url ) {
		return null;
	}

	return array(
		'text' => $text,
		'url'  => $url,
	);
}

/**
 * Buduje dane paska Top Header.
 *
 * Modul jest pierwszym konsumentem pol z zakladek "Kontakt" i "Social Media"
 * (CLAUDE.md sekcja 22) — nie ma wlasnych pol na telefon, email ani adresy
 * profili, tylko czyta istniejace przez cyber_get_option().
 *
 * Regula widocznosci to koniunkcja i jest CELOWO w PHP, nie w conditional logic
 * ACF: element pojawia sie tylko wtedy, gdy wlacznik jest wlaczony ORAZ pole
 * zrodlowe nie jest puste. ACF potrafilby ukryc pole w panelu, ale to jest
 * decyzja widoku, a nie ksztaltu danych.
 *
 * Klucz 'social' sluzy wylacznie do decyzji, czy pasek ma sie renderowac —
 * same ikony wypisuje wspolny komponent cyber_social_icons() (bez wylacznikow
 * uzywa go stopka).
 *
 * @return array {
 *     @type array|null $phone  Tablica 'text' i 'href' albo null.
 *     @type array|null $email  Tablica 'text' i 'href' albo null.
 *     @type array      $social Lista tablic 'platform', 'label', 'url'.
 * }
 */
function cyber_top_header_data() {
	$data = array(
		'phone'  => null,
		'email'  => null,
		'social' => array(),
	);

	$phone = cyber_get_option( 'contact_phone' );

	if ( '' !== $phone && cyber_get_option( 'topheader_show_phone' ) ) {
		$data['phone'] = array(
			// W tresci zostaje zapis redaktora, w href tylko cyfry i wiodacy plus.
			'text' => $phone,
			'href' => 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ),
		);
	}

	$email = cyber_get_option( 'contact_email' );

	if ( '' !== $email && cyber_get_option( 'topheader_show_email' ) ) {
		$data['email'] = array(
			'text' => $email,
			'href' => 'mailto:' . $email,
		);
	}

	// Ta sama lista, ktora wypisze potem wspolny komponent cyber_social_icons().
	$data['social'] = cyber_social_links( true );

	return $data;
}

/**
 * Czy pasek Top Header ma cokolwiek do pokazania.
 *
 * Pusty pasek bylby kolorowym paskiem bez tresci — lepiej go nie renderowac
 * w ogole niz zostawic ozdobna belke nad naglowkiem.
 *
 * @param array $data Wynik cyber_top_header_data().
 * @return bool Czy renderowac pasek.
 */
function cyber_top_header_has_content( array $data ) {
	return null !== $data['phone'] || null !== $data['email'] || array() !== $data['social'];
}
