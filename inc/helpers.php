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
 * Schemat opcji globalnych: wartosci domyslne i reguly walidacji.
 *
 * Klucze podawane sa BEZ prefiksu `cyber_` — prefiks dokladany jest automatycznie
 * w cyber_option_field_name(). Zrodlem prawdy dla tej tablicy jest
 * docs/acf-schema.md; kazda zmiana pola musi byc odzwierciedlona w obu miejscach.
 *
 * Znaczenie kluczy konfiguracji:
 * - type      : 'choice' (wartosc musi nalezec do 'choices'), 'px' albo 'percent'
 *              (oba to liczba calkowita w zadanym zakresie; roznia sie tylko jednostka).
 * - default   : wartosc uzywana, gdy pole jest puste lub ACF nie jest dostepne.
 * - choices   : dozwolone wartosci dla typu 'choice'.
 * - min / max : dopuszczalny zakres dla typu 'px' / 'percent' (walidacja zakresu, sekcja 9).
 * - nullable  : true = pusta wartosc jest poprawna i znaczaca (brak limitu).
 *
 * @return array<string, array<string, mixed>> Schemat opcji.
 */
function cyber_option_schema() {
	return array(
		'page_width_type'         => array(
			'type'    => 'choice',
			'default' => '80',
			'choices' => array( '100', '80', '60' ),
		),
		'page_width_60'           => array(
			'type'    => 'px',
			'default' => 1150,
			'min'     => 320,
			'max'     => 4000,
		),
		'page_width_80'           => array(
			'type'    => 'px',
			'default' => 1500,
			'min'     => 320,
			'max'     => 4000,
		),
		'page_width_100'          => array(
			'type'     => 'px',
			'default'  => '',
			'min'      => 320,
			'max'      => 4000,
			'nullable' => true,
		),
		'page_margin_desktop'     => array(
			'type'    => 'px',
			'default' => 40,
			'min'     => 0,
			'max'     => 200,
		),
		'page_margin_tablet'      => array(
			'type'    => 'px',
			'default' => 30,
			'min'     => 0,
			'max'     => 200,
		),
		'page_margin_mobile_l'    => array(
			'type'    => 'px',
			'default' => 30,
			'min'     => 0,
			'max'     => 200,
		),
		'page_margin_mobile_s'    => array(
			'type'    => 'px',
			'default' => 20,
			'min'     => 0,
			'max'     => 200,
		),
		'font_size_h1'            => array(
			'type'    => 'px',
			'default' => 48,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h2'            => array(
			'type'    => 'px',
			'default' => 40,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h3'            => array(
			'type'    => 'px',
			'default' => 32,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h4'            => array(
			'type'    => 'px',
			'default' => 26,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h5'            => array(
			'type'    => 'px',
			'default' => 22,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_h6'            => array(
			'type'    => 'px',
			'default' => 18,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_overtitle_1'   => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_overtitle_2'   => array(
			'type'    => 'px',
			'default' => 14,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_text'          => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 200,
		),
		'font_size_links'         => array(
			'type'    => 'px',
			'default' => 16,
			'min'     => 8,
			'max'     => 200,
		),
		'font_scale_tablet'       => array(
			'type'    => 'percent',
			'default' => 90,
			'min'     => 10,
			'max'     => 200,
		),
		'font_scale_mobile'       => array(
			'type'    => 'percent',
			'default' => 80,
			'min'     => 10,
			'max'     => 200,
		),
		'font_scale_mobile_small' => array(
			'type'    => 'percent',
			'default' => 70,
			'min'     => 10,
			'max'     => 200,
		),
		'font_family_headings'    => array(
			'type'    => 'choice',
			'default' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
			'choices' => array(
				'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
				'Georgia, "Times New Roman", Times, serif',
				'"Helvetica Neue", Helvetica, Arial, sans-serif',
			),
		),
		'font_family_text'        => array(
			'type'    => 'choice',
			'default' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
			'choices' => array(
				'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
				'Georgia, "Times New Roman", Times, serif',
				'"Helvetica Neue", Helvetica, Arial, sans-serif',
			),
		),
		'font_weight_headings'    => array(
			'type'    => 'choice',
			'default' => '700',
			'choices' => array( '300', '400', '500', '600', '700', '800' ),
		),
		'font_weight_overtitle'   => array(
			'type'    => 'choice',
			'default' => '600',
			'choices' => array( '300', '400', '500', '600', '700', '800' ),
		),
		'font_weight_text'        => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => array( '300', '400', '500', '600', '700', '800' ),
		),
		'font_weight_links'       => array(
			'type'    => 'choice',
			'default' => '400',
			'choices' => array( '300', '400', '500', '600', '700', '800' ),
		),
	);
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

	if ( function_exists( 'get_field' ) ) {
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

	if ( 'px' === $config['type'] || 'percent' === $config['type'] ) {
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
