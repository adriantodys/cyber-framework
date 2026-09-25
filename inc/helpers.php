<?php
/**
 * Funkcje pomocnicze — jedyny dozwolony punkt dostepu do danych globalnych.
 *
 * Widoki (templates/, template-parts/) NIGDY nie wolaja get_field() ani
 * get_option() bezposrednio (CLAUDE.md sekcja 4 i 6) — zawsze przez
 * cyber_get_option(). Dzieki temu cache, walidacja i wartosci domyslne zyja
 * w jednym miejscu.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Platformy spolecznosciowe obslugiwane przez motyw.
 *
 * Klucz = czlon nazwy pola (cyber_social_[klucz], cyber_topheader_show_[klucz])
 * oraz argument cyber_get_social_icon(). Wartosc = nazwa dla czytnikow ekranu.
 *
 * Kolejnosc tablicy jest kolejnoscia wyswietlania ikon.
 *
 * @return array<string, string> Slug platformy => nazwa.
 */
function cyber_social_platforms() {
	return array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'x'         => 'X (Twitter)',
		'linkedin'  => 'LinkedIn',
		'tiktok'    => 'TikTok',
	);
}

/**
 * Rejestr ikon motywu — ksztalty SVG bez otoczki <svg>.
 *
 * Jedno miejsce na wszystkie ikony (CLAUDE.md sekcja 22a): ikony platform
 * spolecznosciowych oraz ikony interfejsu, np. telefon i koperta. Wszystkie
 * maja ten sam viewBox 0 0 20 20 i uzywaja currentColor — zero barw marek,
 * zero kolorow wpisanych w atrybuty.
 *
 * @return array<string, string> Nazwa ikony => zawartosc znacznika <svg>.
 */
function cyber_icons() {
	return array(
		'facebook'  => '<path d="M12.4 19v-7.3h2.5l.4-2.9h-2.9V6.9c0-.8.2-1.4 1.4-1.4h1.6V2.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.1H6.8v2.9h2.5V19h3.1z"/>',
		'instagram' => '<rect x="2.6" y="2.6" width="14.8" height="14.8" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.7"/>'
			. '<circle cx="10" cy="10" r="3.5" fill="none" stroke="currentColor" stroke-width="1.7"/>'
			. '<circle cx="14.6" cy="5.5" r="1.1"/>',
		'youtube'   => '<path fill-rule="evenodd" d="M18.6 6.5c-.2-.9-.9-1.5-1.7-1.8C15.4 4.3 10 4.3 10 4.3s-5.4 0-6.9.4c-.8.3-1.5.9-1.7 1.8-.4 1.5-.4 3.5-.4 3.5s0 2 .4 3.5c.2.9.9 1.5 1.7 1.8 1.5.4 6.9.4 6.9.4s5.4 0 6.9-.4c.8-.3 1.5-.9 1.7-1.8.4-1.5.4-3.5.4-3.5s0-2-.4-3.5zM8.4 12.6V7.4L12.8 10l-4.4 2.6z"/>',
		'x'         => '<path d="M3.4 3h3.2l3.7 5 4.2-5h2.1l-5.3 6.3L17 17h-3.2l-3.9-5.3L5.3 17H3.2l5.6-6.6L3.4 3z"/>',
		'linkedin'  => '<circle cx="4.3" cy="4.4" r="1.7"/>'
			. '<rect x="3" y="7.7" width="2.7" height="9.3"/>'
			. '<path d="M7.7 17V7.7h2.6v1.3c.5-.9 1.6-1.5 2.9-1.5 2.2 0 3.8 1.4 3.8 4V17h-2.7v-4.9c0-1.3-.6-2.1-1.8-2.1-1.1 0-2.1.8-2.1 2.2V17H7.7z"/>',
		'tiktok'    => '<path d="M12.9 2h2.5c.2 1.9 1.4 3.3 3.2 3.5v2.6c-1.2 0-2.3-.4-3.2-1v5.3c0 3-2.4 5.4-5.4 5.4S4.6 15.4 4.6 12.4 7 7 10 7c.3 0 .6 0 .9.1v2.7c-.3-.1-.6-.2-.9-.2-1.5 0-2.7 1.2-2.7 2.8s1.2 2.8 2.7 2.8 2.9-1.2 2.9-2.8V2z"/>',

		// Ikony interfejsu.
		'phone'     => '<path d="M6.7 2.7c.5-.2 1.1 0 1.4.5l1.3 2.3c.3.5.2 1.1-.2 1.4L8 8c.7 1.5 2.3 3.1 3.8 3.8l1.1-1.2c.4-.4.9-.5 1.4-.2l2.3 1.3c.5.3.7.9.5 1.4l-.7 1.7c-.2.6-.8.9-1.4.8C9.4 14.9 5 10.5 4.2 4.9c-.1-.6.2-1.2.8-1.4l1.7-.8z"/>',
		'envelope'  => '<rect x="2.2" y="4.6" width="15.6" height="10.8" rx="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/>'
			. '<path d="M3.2 6.2 10 10.9l6.8-4.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
		'user'      => '<circle cx="10" cy="6.6" r="3.3" fill="none" stroke="currentColor" stroke-width="1.6"/>'
			. '<path d="M3.8 17.4c0-3.2 2.8-5.4 6.2-5.4s6.2 2.2 6.2 5.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
		'question'  => '<circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="1.6"/>'
			. '<path d="M7.7 7.6a2.4 2.4 0 0 1 4.6.9c0 1.6-2.3 2-2.3 3.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'
			. '<circle cx="10" cy="14.4" r="1"/>',
		'location'  => '<path d="M10 18.2s-5.6-5.3-5.6-9.6a5.6 5.6 0 0 1 11.2 0c0 4.3-5.6 9.6-5.6 9.6z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>'
			. '<circle cx="10" cy="8.4" r="2.1" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'cart'      => '<path d="M2.5 3.5h2l1.8 8.5h8.2l1.6-6H5.6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>'
			. '<circle cx="8" cy="15.6" r="1.4"/>'
			. '<circle cx="13.8" cy="15.6" r="1.4"/>',
		'grid'      => '<rect x="2.5" y="2.5" width="6" height="6" rx="1"/>'
			. '<rect x="11.5" y="2.5" width="6" height="6" rx="1"/>'
			. '<rect x="2.5" y="11.5" width="6" height="6" rx="1"/>'
			. '<rect x="11.5" y="11.5" width="6" height="6" rx="1"/>',
		'arrow-up'  => '<path d="M10 16.5v-13M4.5 9 10 3.5 15.5 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
		'list'      => '<rect x="2.5" y="3" width="4" height="4" rx="1"/>'
			. '<rect x="8.5" y="4.2" width="9" height="1.6" rx="0.8"/>'
			. '<rect x="2.5" y="8" width="4" height="4" rx="1"/>'
			. '<rect x="8.5" y="9.2" width="9" height="1.6" rx="0.8"/>'
			. '<rect x="2.5" y="13" width="4" height="4" rx="1"/>'
			. '<rect x="8.5" y="14.2" width="9" height="1.6" rx="0.8"/>',
	);
}

/**
 * Zwraca inline SVG ikony motywu.
 *
 * Wlasne ikony, bez biblioteki zewnetrznej (CLAUDE.md sekcja 2 i 22a). Ikona
 * jest dekoracyjna (aria-hidden), wiec dostepna nazwa musi znalezc sie
 * na elemencie nadrzednym — jako aria-label linku albo jego widoczny tekst.
 *
 * Zwracany markup jest STALY i pochodzi wylacznie z tej funkcji — nie zawiera
 * zadnych danych uzytkownika, wiec jest bezpieczny do wypisania bez escapowania
 * (escapowanie zniszczyloby znaczniki SVG).
 *
 * @param string $name Nazwa ikony, patrz cyber_icons().
 * @return string Znacznik <svg> albo pusty string dla nieznanej ikony.
 */
function cyber_get_icon( $name ) {
	$icons = cyber_icons();

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	/*
	 * Dwie klasy: wspolna .cyber-icon niesie kolor z Global Options (Kolory ->
	 * Ikony), a .cyber-icon--[nazwa] pozwala nadpisac kolor jednej ikony
	 * w arkuszu. Nazwa pochodzi z kluczy rejestru, nie z danych uzytkownika.
	 */
	return '<svg class="cyber-icon cyber-icon--' . $name . '" viewBox="0 0 20 20" fill="currentColor" '
		. 'aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * Zwraca inline SVG ikony platformy spolecznosciowej.
 *
 * Waskie wejscie do cyber_get_icon(): przepuszcza wylacznie platformy
 * z cyber_social_platforms(), zeby literowka albo nazwa ikony interfejsu
 * nie trafila tam, gdzie kod oczekuje ikony profilu.
 *
 * @param string $platform Slug platformy, patrz cyber_social_platforms().
 * @return string Znacznik <svg> albo pusty string dla nieznanej platformy.
 */
function cyber_get_social_icon( $platform ) {
	$platforms = cyber_social_platforms();

	return isset( $platforms[ $platform ] ) ? cyber_get_icon( $platform ) : '';
}

/**
 * Wzorce walidacyjne pol kontaktowych.
 *
 * Jedno zrodlo dla dwoch warstw: walidacji przy zapisie w panelu
 * (inc/contact.php) i walidacji przy odczycie (cyber_option_schema()).
 * Rozjechanie sie tych dwoch list oznaczaloby, ze redaktor zapisuje wartosc,
 * ktorej motyw potem nie przyjmuje.
 *
 * Wszystkie pola sa tekstowe, nie liczbowe — NIP, KRS i REGON moga miec
 * wiodace zera, a telefon znak +, ktore typ Number by zjadl.
 *
 * @return array<string, string> Klucz opcji => wzorzec preg.
 */
function cyber_contact_patterns() {
	return array(
		// Cyfry, spacje i myslniki; opcjonalny + na poczatku, potem cyfra.
		'contact_phone' => '/^\+?[0-9][0-9 \-]*$/',
		'contact_nip'   => '/^[0-9]{10}$/',
		'contact_krs'   => '/^[0-9]{10}$/',
		'contact_regon' => '/^(?:[0-9]{9}|[0-9]{14})$/',
	);
}

/**
 * Zwraca wzorzec walidacyjny pojedynczego pola kontaktowego.
 *
 * @param string $key Klucz opcji bez prefiksu, np. 'contact_nip'.
 * @return string Wzorzec preg albo pusty string, gdy pole go nie ma.
 */
function cyber_contact_pattern( $key ) {
	$patterns = cyber_contact_patterns();

	return isset( $patterns[ $key ] ) ? $patterns[ $key ] : '';
}

/**
 * Dozwolone wartosci font-weight — jedno zrodlo dla calego projektu.
 *
 * Uzywaja jej pola typografii, naglowka i przyciskow. Lista istnieje w kodzie
 * raz, zeby dodanie grubosci nie wymagalo szukania jej po kilku modulach
 * (odpowiednikiem po stronie panelu sa choices w acf-json/).
 *
 * @return string[] Wartosci font-weight.
 */
function cyber_font_weight_choices() {
	return array( '300', '400', '500', '600', '700', '800' );
}

/**
 * Warianty ukladu headera — jedno zrodlo dla schematu, widoku i CSS.
 *
 * Nazwa wariantu jest jednoczesnie wartoscia pola ACF i czlonem klasy
 * modyfikujacej (.cyber-header--centered). Lista zyje tutaj, a nie
 * w inc/header.php, zeby cyber_option_schema() nie zalezalo od pliku
 * ladowanego pozniej — tak samo jak cyber_font_weight_choices().
 *
 * Dolozenie wariantu to wpis tutaj, choices w acf-json/ i reguly CSS pod
 * .cyber-header--[nazwa]. Komponentu nie trzeba ruszac (CLAUDE.md sekcja 20).
 *
 * @return string[] Nazwy wariantow.
 */
function cyber_header_variants() {
	return array( 'default', 'centered', 'cta', 'woocommerce' );
}

/**
 * Schemat opcji globalnych: wartosci domyslne i reguly walidacji.
 *
 * Klucze podawane sa BEZ prefiksu `cyber_` — prefiks dokladany jest automatycznie
 * w cyber_option_field_name(). Zrodlem prawdy dla tej tablicy jest
 * docs/acf-schema.md; kazda zmiana pola musi byc odzwierciedlona w obu miejscach.
 *
 * Znaczenie kluczy konfiguracji:
 * - type      : 'choice' (wartosc musi nalezec do 'choices'), 'px' albo 'percent'
 *              (oba to liczba calkowita w zadanym zakresie; roznia sie tylko jednostka),
 *              'color_alpha' (HEX albo rgb/rgba — Color Picker z wlaczona
 *              przezroczystoscia; uzywaja go WSZYSTKIE pola kolorystyczne),
 *              'color' (sam HEX; typ chwilowo bez pola), 'url' (adres pliku, np. logo
 *              z pola Image), 'bool' (pole True/False), 'text' / 'textarea'
 *              (tekst, opcjonalnie sprawdzany kluczem 'pattern'), 'email'
 *              'html' (tresc z pola WYSIWYG) albo 'link' (pole ACF Link,
 *              tablica url / title / target).
 * - default   : wartosc uzywana, gdy pole jest puste lub ACF nie jest dostepne.
 * - choices   : dozwolone wartosci dla typu 'choice'.
 * - min / max : dopuszczalny zakres dla typu 'px' / 'percent' / 'int' (walidacja zakresu, sekcja 9).
 * - nullable  : true = pusta wartosc jest poprawna i znaczaca (brak limitu,
 *              brak logo, niewypelnione pole kontaktowe).
 * - pattern   : wzorzec preg dla typu 'text' — wartosc niepasujaca jest odrzucana.
 *
 * @return array<string, array<string, mixed>> Schemat opcji.
 */
function cyber_option_schema() {
	/*
	 * Schemat jest stala tablica 228 wpisow, a cyber_get_option() siega po niego
	 * przy KAZDYM wywolaniu — takze wtedy, gdy trafia we wlasny memo-cache.
	 * Bez tego statica jedna podstrona przebudowywala go 150-250 razy.
	 */
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$schema = array(
		'page_width_type'             => array(
			'type'    => 'choice',
			'default' => '80',
			'choices' => array( '100', '80', '60' ),
		),
		'page_width_60'               => array(
			'type'    => 'px',
			'default' => 1150,
			'min'     => 320,
			'max'     => 4000,
		),
		'page_width_80'               => array(
			'type'    => 'px',
			'default' => 1500,
			'min'     => 320,
			'max'     => 4000,
		),
		'page_width_100'              => array(
			'type'     => 'px',
			'default'  => '',
			'min'      => 320,
			'max'      => 4000,
			'nullable' => true,
		),
		'page_margin_desktop'         => array(
			'type'    => 'px',
			'default' => 40,
			'min'     => 0,
			'max'     => 200,
		),
		'page_margin_tablet'          => array(
			'type'    => 'px',
			'default' => 30,
			'min'     => 0,
			'max'     => 200,
		),
		'page_margin_mobile_l'        => array(
			'type'    => 'px',
			'default' => 30,
			'min'     => 0,
			'max'     => 200,
		),
		'page_margin_mobile_s'        => array(
			'type'    => 'px',
			'default' => 20,
			'min'     => 0,
			'max'     => 200,
		),
		'font_size_h1'                => array(
			'type'    => 'px',
			'default' => 48,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h2'                => array(
			'type'    => 'px',
			'default' => 40,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h3'                => array(
			'type'    => 'px',
			'default' => 32,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h4'                => array(
			'type'    => 'px',
			'default' => 26,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h5'                => array(
			'type'    => 'px',
			'default' => 22,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h6'                => array(
			'type'    => 'px',
			'default' => 18,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_overtitle_1'       => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_overtitle_2'       => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_text'              => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_links'             => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 200,
		),
		'font_scale_tablet'           => array(
			'type'    => 'percent',
			'default' => 90,
			'min'     => 10,
			'max'     => 200,
		),
		'font_scale_mobile'           => array(
			'type'    => 'percent',
			'default' => 80,
			'min'     => 10,
			'max'     => 200,
		),
		'font_scale_mobile_small'     => array(
			'type'    => 'percent',
			'default' => 70,
			'min'     => 10,
			'max'     => 200,
		),
		'font_family_headings'        => array(
			'type'    => 'choice',
			'default' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
			// Lista rodzin ma jedno zrodlo: cyber_font_families() w inc/fonts.php.
			'choices' => cyber_font_family_choices(),
		),
		'font_family_text'            => array(
			'type'    => 'choice',
			'default' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
			'choices' => cyber_font_family_choices(),
		),
		'font_weight_headings'        => array(
			'type'    => 'choice',
			'default' => '700',
			'choices' => cyber_font_weight_choices(),
		),
		'font_weight_overtitle'       => array(
			'type'    => 'choice',
			'default' => '600',
			'choices' => cyber_font_weight_choices(),
		),
		'font_weight_text'            => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => cyber_font_weight_choices(),
		),
		'font_weight_links'           => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => cyber_font_weight_choices(),
		),
		'header_logo'                 => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'header_menu_alignment'       => array(
			'type'    => 'choice',
			'default' => 'right',
			'choices' => cyber_alignments(),
		),
		'header_padding_top'          => array(
			'type'    => 'px',
			'default' => 24,
			'min'     => 0,
			'max'     => 200,
		),
		'header_padding_bottom'       => array(
			'type'    => 'px',
			'default' => 24,
			'min'     => 0,
			'max'     => 200,
		),
		'header_bg_color'             => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'header_sticky'               => array(
			'type'    => 'bool',
			'default' => false,
		),
		'header_variant'              => array(
			'type'    => 'choice',
			'default' => 'default',
			'choices' => cyber_header_variants(),
		),
		'header_cta_text'             => array(
			'type'     => 'text',
			'default'  => '',
			'nullable' => true,
		),
		'header_cta_url'              => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'header_menu_item_gap'        => array(
			'type'    => 'px',
			'default' => 32,
			'min'     => 0,
			'max'     => 200,
		),
		'header_menu_link_padding'    => array(
			'type'    => 'px',
			'default' => 8,
			'min'     => 0,
			'max'     => 100,
		),
		'header_menu_font_size'       => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 100,
		),
		'header_menu_font_weight'     => array(
			'type'    => 'choice',
			'default' => '500',
			'choices' => cyber_font_weight_choices(),
		),
		'header_menu_color'           => array(
			'type'    => 'color_alpha',
			'default' => '#1a1a1a',
		),
		'header_menu_color_hover'     => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'header_menu_color_active'    => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'header_submenu_indicator'    => array(
			'type'    => 'bool',
			'default' => true,
		),
		'header_submenu_alignment'    => array(
			'type'    => 'choice',
			'default' => 'left',
			'choices' => cyber_alignments(),
		),
		'header_submenu_item_gap'     => array(
			'type'    => 'px',
			'default' => 0,
			'min'     => 0,
			'max'     => 200,
		),
		'header_submenu_link_padding' => array(
			'type'    => 'px',
			'default' => 10,
			'min'     => 0,
			'max'     => 100,
		),
		'header_submenu_font_size'    => array(
			'type'    => 'px',
			'default' => 15,
			'min'     => 8,
			'max'     => 100,
		),
		'header_submenu_font_weight'  => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => cyber_font_weight_choices(),
		),
		'header_submenu_color'        => array(
			'type'    => 'color_alpha',
			'default' => '#1a1a1a',
		),
		'header_submenu_color_hover'  => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'header_submenu_color_active' => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'header_mobile_breakpoint'    => array(
			'type'    => 'px',
			'default' => 980,
			'min'     => 320,
			'max'     => 2000,
		),
		'btn_large_padding_y'         => array(
			'type'    => 'px',
			'default' => 18,
			'min'     => 4,
			'max'     => 120,
		),
		'btn_large_padding_x'         => array(
			'type'    => 'px',
			'default' => 40,
			'min'     => 4,
			'max'     => 200,
		),
		'btn_large_font_size'         => array(
			'type'    => 'px',
			'default' => 18,
			'min'     => 8,
			'max'     => 100,
		),
		'btn_large_font_weight'       => array(
			'type'    => 'choice',
			'default' => '600',
			'choices' => cyber_font_weight_choices(),
		),
		'btn_large_color'             => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'btn_large_color_hover'       => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'btn_large_bg_color'          => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'btn_large_bg_color_hover'    => array(
			'type'    => 'color_alpha',
			'default' => '#0041c2',
		),
		'btn_medium_padding_y'        => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 4,
			'max'     => 120,
		),
		'btn_medium_padding_x'        => array(
			'type'    => 'px',
			'default' => 32,
			'min'     => 4,
			'max'     => 200,
		),
		'btn_medium_font_size'        => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 100,
		),
		'btn_medium_font_weight'      => array(
			'type'    => 'choice',
			'default' => '600',
			'choices' => cyber_font_weight_choices(),
		),
		'btn_medium_color'            => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'btn_medium_color_hover'      => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'btn_medium_bg_color'         => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'btn_medium_bg_color_hover'   => array(
			'type'    => 'color_alpha',
			'default' => '#0041c2',
		),
		'btn_small_padding_y'         => array(
			'type'    => 'px',
			'default' => 10,
			'min'     => 4,
			'max'     => 120,
		),
		'btn_small_padding_x'         => array(
			'type'    => 'px',
			'default' => 24,
			'min'     => 4,
			'max'     => 200,
		),
		'btn_small_font_size'         => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 100,
		),
		'btn_small_font_weight'       => array(
			'type'    => 'choice',
			'default' => '600',
			'choices' => cyber_font_weight_choices(),
		),
		'btn_small_color'             => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'btn_small_color_hover'       => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'btn_small_bg_color'          => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'btn_small_bg_color_hover'    => array(
			'type'    => 'color_alpha',
			'default' => '#0041c2',
		),
		'color_headings'              => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'color_text'                  => array(
			'type'    => 'color_alpha',
			'default' => '#333333',
		),
		'color_overtitle_1'           => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'color_overtitle_2'           => array(
			'type'    => 'color_alpha',
			'default' => '#666666',
		),
		'color_links'                 => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'color_icons'                 => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'color_hover'                 => array(
			'type'    => 'color_alpha',
			'default' => '#0041c2',
		),
		'color_border_1'              => array(
			'type'    => 'color_alpha',
			'default' => '#e0e0e0',
		),
		'color_border_2'              => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'color_shadow'                => array(
			'type'    => 'color_alpha',
			'default' => 'rgba(0,0,0,0.12)',
		),
		'color_shadow_hover'          => array(
			'type'    => 'color_alpha',
			'default' => 'rgba(0,0,0,0.2)',
		),
		'contact_address'             => array(
			'type'     => 'textarea',
			'default'  => '',
			'nullable' => true,
		),
		'contact_hours'               => array(
			'type'     => 'textarea',
			'default'  => '',
			'nullable' => true,
		),
		'contact_phone'               => array(
			'type'     => 'text',
			'default'  => '',
			'pattern'  => cyber_contact_pattern( 'contact_phone' ),
			'nullable' => true,
		),
		'contact_email'               => array(
			'type'     => 'email',
			'default'  => '',
			'nullable' => true,
		),
		'contact_nip'                 => array(
			'type'     => 'text',
			'default'  => '',
			'pattern'  => cyber_contact_pattern( 'contact_nip' ),
			'nullable' => true,
		),
		'contact_krs'                 => array(
			'type'     => 'text',
			'default'  => '',
			'pattern'  => cyber_contact_pattern( 'contact_krs' ),
			'nullable' => true,
		),
		'contact_regon'               => array(
			'type'     => 'text',
			'default'  => '',
			'pattern'  => cyber_contact_pattern( 'contact_regon' ),
			'nullable' => true,
		),
		'social_facebook'             => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'social_instagram'            => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'social_youtube'              => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'social_x'                    => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'social_linkedin'             => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'social_tiktok'               => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'topheader_bg_color'          => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'topheader_font_color'        => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'topheader_font_size'         => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 40,
		),
		'topheader_show_phone'        => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_email'        => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_facebook'     => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_instagram'    => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_youtube'      => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_x'            => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_linkedin'     => array(
			'type'    => 'bool',
			'default' => true,
		),
		'topheader_show_tiktok'       => array(
			'type'    => 'bool',
			'default' => true,
		),
		'footer_logo'                 => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'footer_content'              => array(
			'type'     => 'html',
			'default'  => '',
			'nullable' => true,
		),
		'footer_bg_color'             => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'footer_title_font_size'      => array(
			'type'    => 'px',
			'default' => 20,
			'min'     => 8,
			'max'     => 100,
		),
		'footer_title_color'          => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'footer_text_font_size'       => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 100,
		),
		'footer_text_color'           => array(
			'type'    => 'color_alpha',
			'default' => '#333333',
		),
		'footer_link_font_size'       => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 100,
		),
		'footer_link_color'           => array(
			'type'    => 'color_alpha',
			'default' => '#0057ff',
		),
		'copyright_text'              => array(
			'type'     => 'text',
			'default'  => '',
			'nullable' => true,
		),
		'copyright_privacy_link'      => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'copyright_cookies_link'      => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'copyright_bg_color'          => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'copyright_text_color'        => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'copyright_link_color'        => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'copyright_text_font_size'    => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 40,
		),
		'copyright_link_font_size'    => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 40,
		),
		'breadcrumb_enable'           => array(
			'type'    => 'bool',
			'default' => false,
		),
		'breadcrumb_font_size'        => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 40,
		),
		'breadcrumb_color'            => array(
			'type'    => 'color_alpha',
			'default' => '#666666',
		),
		'breadcrumb_color_active'     => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'breadcrumb_wc_enable'        => array(
			'type'    => 'bool',
			'default' => false,
		),
		'breadcrumb_wc_font_size'     => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 40,
		),
		'breadcrumb_wc_color'         => array(
			'type'    => 'color_alpha',
			'default' => '#666666',
		),
		'breadcrumb_wc_color_active'  => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'wc_pagination_type'          => array(
			'type'    => 'choice',
			'default' => 'pagination',
			'choices' => array( 'pagination', 'loadmore' ),
		),
		'wc_color_main'               => array(
			'type'    => 'color_alpha',
			'default' => '#d32f2f',
		),
		'wc_product_col_image'        => array(
			'type'    => 'percent',
			'default' => 40,
			'min'     => 20,
			'max'     => 80,
		),
		'wc_product_col_summary'      => array(
			'type'    => 'percent',
			'default' => 60,
			'min'     => 20,
			'max'     => 80,
		),
		'wc_product_media_height'     => array(
			'type'    => 'px',
			'default' => 500,
			'min'     => 200,
			'max'     => 1200,
		),
		'wc_product_thumb_height'     => array(
			'type'    => 'px',
			'default' => 158,
			'min'     => 60,
			'max'     => 400,
		),
		'blog_sidebar'                => array(
			'type'    => 'bool',
			'default' => true,
		),
		'blog_sidebar_width'          => array(
			'type'    => 'choice',
			'default' => '30',
			'choices' => array( '25', '30', '33' ),
		),
		'blog_gap'                    => array(
			'type'    => 'choice',
			'default' => '64',
			'choices' => cyber_spacing_scale_choices(),
		),
		'blog_show_title'             => array(
			'type'    => 'bool',
			'default' => false,
		),
		'blog_columns'                => array(
			'type'    => 'choice',
			'default' => '2',
			'choices' => array( '1', '2', '3' ),
		),
		'blog_columns_tablet'         => array(
			'type'    => 'choice',
			'default' => '2',
			'choices' => array( '1', '2' ),
		),
		'blog_gap_x'                  => array(
			'type'    => 'choice',
			'default' => '24',
			'choices' => cyber_spacing_scale_choices(),
		),
		'blog_gap_y'                  => array(
			'type'    => 'choice',
			'default' => '48',
			'choices' => cyber_spacing_scale_choices(),
		),
		'blog_image_ratio'            => array(
			'type'    => 'choice',
			'default' => '16-9',
			'choices' => array( 'auto', '16-9', '3-2', '4-3', '1-1' ),
		),
		'blog_show_date'              => array(
			'type'    => 'bool',
			'default' => true,
		),
		'blog_show_category'          => array(
			'type'    => 'bool',
			'default' => false,
		),
		'blog_show_excerpt'           => array(
			'type'    => 'bool',
			'default' => false,
		),
		'blog_show_button'            => array(
			'type'    => 'bool',
			'default' => false,
		),
		'blog_excerpt_length'         => array(
			'type'    => 'int',
			'default' => 20,
			'min'     => 5,
			'max'     => 60,
		),
		'blog_button_label'           => array(
			'type'    => 'text',
			'default' => __( 'Czytaj więcej', 'cyber-framework' ),
		),
		'blog_button_size'            => array(
			'type'    => 'choice',
			'default' => 'small',
			'choices' => cyber_button_sizes(),
		),
		'blog_title_size'             => array(
			'type'    => 'choice',
			'default' => 'h5',
			'choices' => cyber_heading_levels(),
		),
		'blog_title_weight'           => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => cyber_font_weight_choices(),
		),
		'blog_single_image'           => array(
			'type'    => 'bool',
			'default' => true,
		),
		'blog_single_date'            => array(
			'type'    => 'bool',
			'default' => true,
		),
		'blog_single_category'        => array(
			'type'    => 'bool',
			'default' => false,
		),
		'blog_single_author'          => array(
			'type'    => 'bool',
			'default' => false,
		),
		'blog_single_title_size'      => array(
			'type'    => 'choice',
			'default' => 'h3',
			'choices' => cyber_heading_levels(),
		),
		'blog_single_title_weight'    => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => cyber_font_weight_choices(),
		),
		'blog_date_format'            => array(
			'type'    => 'choice',
			'default' => 'short',
			'choices' => array( 'short', 'numeric', 'wp' ),
		),
		'pageheader_enable'           => array(
			'type'    => 'bool',
			'default' => false,
		),
		'pageheader_types'            => array(
			'type'     => 'choices',
			'default'  => array( 'page' ),
			'nullable' => true,
		),
		'pageheader_width'            => array(
			'type'    => 'choice',
			'default' => 'full',
			'choices' => array( 'full', 'container' ),
		),
		'pageheader_height'           => array(
			'type'    => 'px',
			'default' => 360,
			'min'     => 120,
			'max'     => 900,
		),
		'pageheader_height_mobile'    => array(
			'type'    => 'px',
			'default' => 240,
			'min'     => 100,
			'max'     => 700,
		),
		'pageheader_align'            => array(
			'type'    => 'choice',
			'default' => 'center',
			'choices' => cyber_alignments(),
		),
		'pageheader_bg_image'         => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'pageheader_bg_video'         => array(
			'type'     => 'url',
			'default'  => '',
			'nullable' => true,
		),
		'pageheader_overlay'          => array(
			'type'    => 'color_alpha',
			'default' => 'rgba(0,0,0,0.35)',
		),
		'pageheader_bg_color'         => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'pageheader_title_size'       => array(
			'type'    => 'choice',
			'default' => 'h1',
			'choices' => cyber_heading_levels(),
		),
		'pageheader_title_weight'     => array(
			'type'    => 'choice',
			'default' => '700',
			'choices' => cyber_font_weight_choices(),
		),
		'pageheader_title_color'      => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'pageheader_show_excerpt'     => array(
			'type'    => 'bool',
			'default' => false,
		),
		'pageheader_excerpt_size'     => array(
			'type'    => 'choice',
			'default' => 'text',
			'choices' => array( 'text', 'h6', 'h5', 'h4' ),
		),
		'pageheader_excerpt_color'    => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'pageheader_breadcrumb'       => array(
			'type'    => 'bool',
			'default' => false,
		),

		// Animacje sekcji. Rodzaj animacji jest polem sekcji, tu tylko parametry wspolne.
		'anim_enable'                 => array(
			'type'    => 'bool',
			'default' => true,
		),
		'anim_duration'               => array(
			'type'    => 'int',
			'default' => 700,
			'min'     => 100,
			'max'     => 3000,
		),
		'anim_delay'                  => array(
			'type'    => 'int',
			'default' => 0,
			'min'     => 0,
			'max'     => 3000,
		),
		'anim_easing'                 => array(
			'type'    => 'choice',
			'default' => 'ease-out',
			'choices' => array( 'ease-out', 'ease', 'ease-in-out', 'linear' ),
		),
		'anim_offset'                 => array(
			'type'    => 'px',
			'default' => 80,
			'min'     => 0,
			'max'     => 400,
		),
		'anim_distance'               => array(
			'type'    => 'px',
			'default' => 40,
			'min'     => 0,
			'max'     => 200,
		),
		'anim_once'                   => array(
			'type'    => 'bool',
			'default' => true,
		),

		// Przycisk "do gory". Domyslnie wylaczony — nie pojawia sie sam na istniejacych witrynach.
		'totop_enable'                => array(
			'type'    => 'bool',
			'default' => false,
		),
		'totop_show_after'            => array(
			'type'    => 'px',
			'default' => 400,
			'min'     => 0,
			'max'     => 5000,
		),
		'totop_mobile'                => array(
			'type'    => 'bool',
			'default' => true,
		),
		'totop_position'              => array(
			'type'    => 'choice',
			'default' => 'right',
			'choices' => cyber_alignments(),
		),
		'totop_offset'                => array(
			'type'    => 'choice',
			'default' => '24',
			'choices' => cyber_spacing_scale_choices(),
		),
		'totop_size'                  => array(
			'type'    => 'px',
			'default' => 48,
			'min'     => 32,
			'max'     => 96,
		),
		'totop_icon_size'             => array(
			'type'    => 'px',
			'default' => 20,
			'min'     => 12,
			'max'     => 48,
		),
		'totop_radius'                => array(
			'type'    => 'px',
			'default' => 0,
			'min'     => 0,
			'max'     => 48,
		),
		'totop_bg'                    => array(
			'type'    => 'color_alpha',
			'default' => '#111111',
		),
		'totop_color'                 => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
		'totop_bg_hover'              => array(
			'type'    => 'color_alpha',
			'default' => '#333333',
		),
		'totop_color_hover'           => array(
			'type'    => 'color_alpha',
			'default' => '#ffffff',
		),
	);

	/*
	 * Wylaczniki i pozycje elementow strony produktu doklejamy z rejestru,
	 * zamiast wypisywac po dwa wpisy na element. Rejestr jest jedynym zrodlem
	 * prawdy: dodanie elementu to jedna linia w cyber_product_elements(),
	 * a nie trzy zmiany w trzech miejscach, ktore moga sie rozjechac.
	 */
	$cached = $schema + cyber_product_option_schema();

	return $cached;
}

/**
 * Rejestr elementow strony pojedynczego produktu.
 *
 * Jedno zrodlo prawdy dla trzech rzeczy naraz: pol ACF (wylacznik i pozycja),
 * schematu opcji oraz podpiecia hookow w inc/woocommerce-product.php.
 *
 * Klucze tablicy elementu:
 *
 * - label    : etykieta pola w panelu (widoczna tylko dla admina).
 * - section  : miejsce w ukladzie strony. 'summary' = prawa kolumna,
 *              'after' = sekcja pod kolumnami, 'cart' = wnetrze formularza
 *              zakupu, 'media' = lewa kolumna ze zdjeciami.
 * - show     : domyslny stan wylacznika.
 * - position : domyslna pozycja w obrebie sekcji. Liczba trafia wprost do
 *              priorytetu add_action(), wiec to, co admin wpisuje w panelu,
 *              JEST kolejnoscia renderowania — bez warstwy posredniej.
 *              Brak klucza = elementu nie da sie przestawic, bo jego miejsce
 *              wynika z markupu (pole ilosci siedzi wewnatrz formularza
 *              zakupu, plakietka promocji na zdjeciu).
 *
 * Dodanie kolejnego elementu WooCommerce sprowadza sie do jednego wpisu tutaj
 * plus jednej pozycji w cyber_product_element_callbacks().
 *
 * @return array<string, array<string, mixed>> Klucz elementu => opis.
 */
function cyber_product_elements() {
	return array(
		'title'    => array(
			'label'    => 'Tytul produktu',
			'section'  => 'summary',
			'show'     => true,
			'position' => 10,
		),
		'sku'      => array(
			'label'    => 'SKU',
			'section'  => 'summary',
			'show'     => true,
			'position' => 20,
		),
		'rating'   => array(
			'label'    => 'Ocena i liczba opinii',
			'section'  => 'summary',
			'show'     => false,
			'position' => 25,
		),
		'excerpt'  => array(
			'label'    => 'Krotki opis',
			'section'  => 'summary',
			'show'     => true,
			'position' => 30,
		),
		'price'    => array(
			'label'    => 'Cena',
			'section'  => 'summary',
			'show'     => true,
			'position' => 40,
		),
		'stock'    => array(
			'label'    => 'Dostepnosc w magazynie',
			'section'  => 'summary',
			'show'     => true,
			'position' => 50,
		),
		'cart'     => array(
			'label'    => 'Ilosc i przycisk Dodaj do koszyka',
			'section'  => 'summary',
			'show'     => true,
			'position' => 60,
		),
		'meta'     => array(
			'label'    => 'Kategorie i tagi',
			'section'  => 'summary',
			'show'     => false,
			'position' => 70,
		),
		'quantity' => array(
			'label'   => 'Pole ilosci',
			'section' => 'cart',
			'show'    => true,
		),
		'sale'     => array(
			'label'   => 'Plakietka promocji',
			'section' => 'media',
			'show'    => true,
		),
		'tabs'     => array(
			'label'    => 'Zakladki z opisem',
			'section'  => 'after',
			'show'     => true,
			'position' => 10,
		),
		'upsells'  => array(
			'label'    => 'Produkty polecane (upsell)',
			'section'  => 'after',
			'show'     => false,
			'position' => 20,
		),
		'related'  => array(
			'label'    => 'Podobne produkty',
			'section'  => 'after',
			'show'     => false,
			'position' => 30,
		),
	);
}

/**
 * Wpisy schematu opcji wygenerowane z rejestru elementow produktu.
 *
 * Nazwy kluczy sa scisle zwiazane z nazwami pol ACF:
 * cyber_wc_product_show_[element] i cyber_wc_product_pos_[element].
 *
 * @return array<string, array<string, mixed>> Fragment schematu opcji.
 */
function cyber_product_option_schema() {
	$schema = array();

	foreach ( cyber_product_elements() as $key => $element ) {
		$schema[ 'wc_product_show_' . $key ] = array(
			'type'    => 'bool',
			'default' => $element['show'],
		);

		if ( ! isset( $element['position'] ) ) {
			continue;
		}

		$schema[ 'wc_product_pos_' . $key ] = array(
			'type'    => 'int',
			'default' => $element['position'],
			'min'     => 1,
			'max'     => 200,
		);
	}

	return $schema;
}

/**
 * Buduje adres tel: z numeru zapisanego przez redaktora.
 *
 * W tresci zostaje zapis redaktora (spacje, myslniki, nawiasy), w href tylko
 * cyfry i wiodacy plus. Regula istniala w dwoch kopiach — w top headerze
 * i w sekcji Kontakt, gdzie komentarz odsylal do tej pierwszej slowami
 * zamiast wywolaniem.
 *
 * @param string $phone Numer w zapisie redaktora.
 * @return string Wartosc atrybutu href.
 */
function cyber_tel_href( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
}

/**
 * Wybiera z wiersza sekcji te kolory, ktore redaktor faktycznie ustawil.
 *
 * Pole koloru puste = "zostaw wartosc z arkusza", wiec pusta wartosc NIE
 * moze trafic do atrybutu style jako pusta deklaracja. Ten warunek istnial
 * w pieciu identycznych kopiach (galeria, kontakt, licznik, FAQ, tabela).
 *
 * @param array                 $row    Wiersz Flexible Content.
 * @param array<string, string> $colors Nazwa zmiennej CSS => nazwa pola.
 * @return array<string, string> Tylko ustawione kolory.
 */
function cyber_row_colors( array $row, array $colors ) {
	$out = array();

	foreach ( $colors as $var => $key ) {
		$value = cyber_sanitize_color( isset( $row[ $key ] ) ? $row[ $key ] : '' );

		if ( '' !== $value ) {
			$out[ $var ] = $value;
		}
	}

	return $out;
}

/**
 * Skleja pary "nazwa => wartosc" w liste deklaracji CSS.
 *
 * Trzy linijki, ale zanim tu trafily, istnialy w trzynastu kopiach — w kazdym
 * module sekcji, w blogu, w page headerze i w cyber_css_vars_from_map().
 * CLAUDE.md sekcja 6 ostrzegala przed tym przy trzech kopiach; problem odrosl,
 * bo kazdy kolejny modul mial dobry powod, zeby nie uzywac MAPY — i przy
 * okazji przepisywal takze sam emiter, ktorego ten powod nie dotyczyl.
 *
 * Rozdzielenie na dwie funkcje jest celowe: modul per instancja potrzebuje
 * samych deklaracji (ida do atrybutu style), modul globalny — calego bloku
 * :root{...} (idzie do <style> w wp_head).
 *
 * @param array<string, string|int> $vars Nazwa zmiennej CSS => wartosc.
 * @return string Deklaracje CSS, np. "--a:1px;--b:red;".
 */
function cyber_css_declarations( array $vars ) {
	$out = '';

	foreach ( $vars as $name => $value ) {
		$out .= sprintf( '%1$s:%2$s;', $name, $value );
	}

	return $out;
}

/**
 * Pakuje zmienne CSS w blok :root{...}.
 *
 * @param array<string, string|int> $vars Nazwa zmiennej CSS => wartosc.
 * @return string Blok CSS bez znacznika <style>.
 */
function cyber_css_root( array $vars ) {
	return ':root{' . cyber_css_declarations( $vars ) . '}';
}

/**
 * Sanityzuje wartosc koloru z pola Color Picker z wlaczona przezroczystoscia.
 *
 * ACF z enable_opacity zwraca przy pelnym kryciu HEX, a ponizej rgb()/rgba().
 * sanitize_hex_color() dwoch ostatnich nie przepusci, a kolor bez kanalu alfa
 * jest w tym projekcie w praktyce bezuzyteczny — stad wlasny, scisly wzorzec.
 *
 * Funkcja stoi osobno, bo korzystaja z niej dwa niezalezne swiaty: walidacja
 * opcji globalnych (cyber_validate_option_value) oraz pola per instancja
 * w sekcjach (inc/sections.php). Druga kopia tego wzorca rozjechalaby sie
 * przy pierwszej poprawce.
 *
 * @param mixed $value Wartosc surowa z pola.
 * @return string Kolor gotowy do uzycia w CSS albo pusty string.
 */
function cyber_sanitize_color( $value ) {
	$raw = trim( (string) $value );

	if ( '' === $raw ) {
		return '';
	}

	$hex = sanitize_hex_color( $raw );

	if ( null !== $hex && '' !== $hex ) {
		return $hex;
	}

	$pattern = '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)$/';

	return preg_match( $pattern, $raw ) ? $raw : '';
}

/**
 * Kanoniczny zestaw wartosci wyrownania.
 *
 * Jedno zrodlo prawdy dla WSZYSTKICH pol typu "wyrownaj" w projekcie
 * (CLAUDE.md sekcja 20). Zaden modul nie definiuje wlasnego zestawu — bez tego
 * po kilku modulach pojawiaja sie rownolegle warianty start/end albo l/c/r.
 *
 * UWAGA: to NIE jest to samo co pozycja tla (background-position), ktora
 * przypadkiem ma te same trzy wartosci w poziomie. Tamta opisuje punkt
 * zaczepienia obrazka, nie uklad tresci, i ma wlasny rejestr.
 *
 * @return string[] Dozwolone wartosci.
 */
function cyber_alignments() {
	return array( 'left', 'center', 'right' );
}

/**
 * Rozmiary przyciskow — jedno zrodlo dla CSS i dla komponentu.
 *
 * Kolejnosc odpowiada malejacej wadze wizualnej. Nazwa rozmiaru jest
 * jednoczesnie modyfikatorem klasy (.btn-large) i czlonem nazwy pola
 * (cyber_btn_large_*) oraz zmiennej (--cyber-btn-large-*).
 *
 * Rejestr stoi tutaj, a nie w inc/enqueue.php, bo siega po niego
 * cyber_option_schema() — a ta nie moze zalezec od pliku ladowanego
 * pozniej (ta sama zasada co przy cyber_font_weight_choices()).
 *
 * @return string[] Nazwy rozmiarow.
 */
function cyber_button_sizes() {
	return array( 'large', 'medium', 'small' );
}

/**
 * Poziomy naglowkow — jedno zrodlo dla schematu opcji i dla sekcji.
 *
 * Lista byla wpisana wprost w trzech wpisach cyber_option_schema()
 * (blog_title_size, blog_single_title_size, pageheader_title_size)
 * i w cyber_cards_title_sizes().
 *
 * @return string[] Poziomy od najwiekszego.
 */
function cyber_heading_levels() {
	return array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
}

/**
 * Zamknieta skala odstepow projektu w px (CLAUDE.md sekcja 6).
 *
 * Zero nie jest czescia skali wizualnej, ale jest poprawna wartoscia pola
 * ("bez odstepu"), wiec stoi na jej poczatku.
 *
 * Skala mieszkala dotad w cyber_section_spacings() (inc/sections.php) oraz
 * w trzech kopiach jako stringi w cyber_option_schema(). Zrodlo jest tutaj,
 * bo skala jest globalna, a nie wlasnoscia modulu sekcji.
 *
 * @return int[] Dozwolone wartosci w px.
 */
function cyber_spacing_scale() {
	return array( 0, 6, 12, 24, 36, 48, 64, 94 );
}

/**
 * Skala odstepow jako lista stringow — postac wymagana przez pola Select ACF.
 *
 * @return string[] Wartosci skali jako stringi.
 */
function cyber_spacing_scale_choices() {
	return array_map( 'strval', cyber_spacing_scale() );
}

/**
 * Kanoniczne breakpointy projektu.
 *
 * Jedno zrodlo prawdy dla WSZYSTKICH modulow (CLAUDE.md sekcja 18). Klucz to
 * nazwa breakpointu uzywana w kodzie i w nazwach pol ACF, wartosc to gorna
 * granica w px, zapisywana w CSS jako max-width (granica domknieta od gory).
 *
 * Desktop nie wystepuje w tablicy celowo — to wartosc bazowa, bez media query.
 *
 * Dodanie nowego progu wymaga jawnego uzasadnienia (CLAUDE.md sekcja 18) —
 * modul, ktory przelacza sie w innym momencie niz reszta, rozjezdza layout.
 *
 * @return array<string, int> Nazwa breakpointu => gorna granica w px.
 */
function cyber_breakpoints() {
	return array(
		'tablet'       => 980,
		'mobile'       => 767,
		'mobile_small' => 479,
	);
}

/**
 * Buduje pelna nazwe pola ACF na podstawie klucza skroconego.
 *
 * @param string $key Klucz bez prefiksu, np. 'page_width_type'.
 * @return string Nazwa pola ACF, np. 'cyber_page_width_type'.
 */
function cyber_option_field_name( $key ) {
	$key = (string) $key;

	return 0 === strpos( $key, 'cyber_' ) ? $key : 'cyber_' . $key;
}

/**
 * Zwraca zwalidowana wartosc opcji globalnej.
 *
 * Kolejnosc: wartosc z ACF -> walidacja -> wartosc domyslna ze schematu.
 * Gdy ACF PRO nie jest aktywne, funkcja zwraca wartosci domyslne, dzieki czemu
 * motyw nie przestaje dzialac (jedynie traci mozliwosc konfiguracji).
 *
 * @param string $key     Klucz opcji bez prefiksu, np. 'page_width_type'.
 * @param mixed  $default Opcjonalne nadpisanie wartosci domyslnej ze schematu.
 * @return mixed Zwalidowana wartosc opcji.
 */
function cyber_get_option( $key, $default = null ) {
	static $cache = array();

	$schema = cyber_option_schema();

	if ( ! isset( $schema[ $key ] ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html( sprintf( 'Nieznany klucz opcji "%s". Dodaj go do cyber_option_schema() i docs/acf-schema.md.', $key ) ),
			'0.1.0'
		);

		return $default;
	}

	// Cache tylko dla wywolan bez nadpisanej wartosci domyslnej — inaczej pierwszy
	// $default "zamrozilby" wynik dla wszystkich kolejnych wywolan.
	$cacheable = ( null === $default );

	if ( $cacheable && array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}

	$config   = $schema[ $key ];
	$fallback = ( null !== $default ) ? $default : $config['default'];
	$raw      = null;

	if ( cyber_is_acf_active() ) {
		$raw = get_field( cyber_option_field_name( $key ), 'option' );
	}

	$value = cyber_validate_option_value( $raw, $config, $fallback );

	if ( $cacheable ) {
		$cache[ $key ] = $value;
	}

	return $value;
}

/**
 * Waliduje pojedyncza wartosc opcji zgodnie z jej konfiguracja ze schematu.
 *
 * @param mixed $value    Wartosc surowa (z ACF lub inna).
 * @param array $config   Konfiguracja pola z cyber_option_schema().
 * @param mixed $fallback Wartosc uzywana, gdy $value jest pusta lub niepoprawna.
 * @return mixed Wartosc bezpieczna do uzycia w logice motywu.
 */
function cyber_validate_option_value( $value, array $config, $fallback ) {
	$is_empty = ( null === $value || '' === $value || array() === $value );

	if ( $is_empty ) {
		return ! empty( $config['nullable'] ) ? '' : $fallback;
	}

	if ( 'choice' === $config['type'] ) {
		$value = sanitize_text_field( (string) $value );

		return in_array( $value, $config['choices'], true ) ? $value : $fallback;
	}

	if ( 'choices' === $config['type'] ) {
		/*
		 * Wielokrotny wybor (pole Checkbox): lista wartosci, nie jedna wartosc.
		 * Dozwolone wartosci nie sa wpisane w schemacie, bo zalezą od tego, jakie
		 * typy tresci rejestruja wtyczki — sanityzujemy kazdy element jak klucz
		 * i odrzucamy puste.
		 */
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		$value = array_values( array_filter( array_map( 'sanitize_key', $value ) ) );

		return $value ? $value : ( ! empty( $config['nullable'] ) ? array() : $fallback );
	}

	if ( 'bool' === $config['type'] ) {
		/*
		 * ACF zwraca dla pola True/False 1 albo 0 — zadna z tych wartosci nie jest
		 * "pusta" w rozumieniu warunku wyzej, wiec swiadome wylaczenie toggle'a
		 * nie zostanie podmienione na wartosc domyslna.
		 */
		return (bool) $value;
	}

	if ( 'link' === $config['type'] ) {
		/*
		 * Typ chwilowo bez pola: odnosniki do stron witryny uzywaja Page Link
		 * (typ 'url'). Zostaje pod linki ZEWNETRZNE, ktore nadal wymagaja
		 * wlasnego tytulu i target (CLAUDE.md sekcja 5a).
		 *
		 * Pole ACF Link zwraca tablice url / title / target albo pusty string.
		 * Kazdy element sanitujemy osobno, a brak adresu traktujemy jak brak
		 * linku — sam tytul bez URL nie ma czego wskazac.
		 */
		if ( ! is_array( $value ) ) {
			return $fallback;
		}

		$url = isset( $value['url'] ) ? esc_url_raw( (string) $value['url'] ) : '';

		if ( '' === $url ) {
			return $fallback;
		}

		$target = isset( $value['target'] ) ? (string) $value['target'] : '';

		return array(
			'url'    => $url,
			'title'  => isset( $value['title'] ) ? sanitize_text_field( (string) $value['title'] ) : '',
			'target' => ( '_blank' === $target ) ? '_blank' : '',
		);
	}

	if ( 'html' === $config['type'] ) {
		/*
		 * Tresc z WYSIWYG jest juz sformatowana przez ACF (wpautop, shortcode).
		 * Filtrowanie znacznikow nalezy do momentu wypisania — widok uzywa
		 * wp_kses_post() (CLAUDE.md sekcja 8: escapowanie przy outpucie).
		 */
		$value = (string) $value;

		return ( '' === trim( $value ) ) ? $fallback : $value;
	}

	if ( 'textarea' === $config['type'] ) {
		$value = sanitize_textarea_field( (string) $value );

		return ( '' === $value ) ? $fallback : $value;
	}

	if ( 'email' === $config['type'] ) {
		$value = sanitize_email( (string) $value );

		return ( '' === $value || ! is_email( $value ) ) ? $fallback : $value;
	}

	if ( 'text' === $config['type'] ) {
		$value = sanitize_text_field( (string) $value );

		if ( '' === $value ) {
			return $fallback;
		}

		if ( ! empty( $config['pattern'] ) && ! preg_match( $config['pattern'], $value ) ) {
			return $fallback;
		}

		return $value;
	}

	if ( 'color' === $config['type'] ) {
		/*
		 * Typ chwilowo bez pola, tak samo jak 'link' powyzej. Odkad wszystkie
		 * pola Color Picker maja wlaczona przezroczystosc, kazde z nich uzywa
		 * 'color_alpha'. Ten typ zostaje pod ewentualne przyszle pole, ktore
		 * musi przyjac WYLACZNIE HEX i odrzucic kanal alfa.
		 */
		$value = sanitize_hex_color( (string) $value );

		return ( null === $value || '' === $value ) ? $fallback : $value;
	}

	if ( 'color_alpha' === $config['type'] ) {
		$color = cyber_sanitize_color( $value );

		return ( '' === $color ) ? $fallback : $color;
	}

	if ( 'url' === $config['type'] ) {
		/*
		 * Pole Image z return_format 'array' albo 'id' zwrocilo by inny typ niz string.
		 * Wyciagamy z tablicy URL, zeby przestawienie pola w UI nie wywrocilo widoku.
		 */
		if ( is_array( $value ) ) {
			$value = isset( $value['url'] ) ? $value['url'] : '';
		}

		$value = esc_url_raw( (string) $value );

		if ( '' === $value ) {
			return ! empty( $config['nullable'] ) ? '' : $fallback;
		}

		return $value;
	}

	if ( 'px' === $config['type'] || 'percent' === $config['type'] || 'int' === $config['type'] ) {
		if ( ! is_numeric( $value ) ) {
			return ! empty( $config['nullable'] ) ? '' : $fallback;
		}

		$value = (int) round( (float) $value );

		if ( $value < $config['min'] || $value > $config['max'] ) {
			return $fallback;
		}

		return $value;
	}

	return $fallback;
}
