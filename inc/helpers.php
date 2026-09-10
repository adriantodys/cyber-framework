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
 * Schemat opcji globalnych: wartosci domyslne i reguly walidacji.
 *
 * Klucze podawane sa BEZ prefiksu `cyber_` — prefiks dokladany jest automatycznie
 * w cyber_option_field_name(). Zrodlem prawdy dla tej tablicy jest
 * docs/acf-schema.md; kazda zmiana pola musi byc odzwierciedlona w obu miejscach.
 *
 * Znaczenie kluczy konfiguracji:
 * - type      : 'choice' (wartosc musi nalezec do 'choices'), 'px' albo 'percent'
 *              (oba to liczba calkowita w zadanym zakresie; roznia sie tylko jednostka),
 *              'color' (kolor HEX), 'color_alpha' (HEX albo rgb/rgba — pole Color
 *              Picker z wlaczona przezroczystoscia), 'url' (adres pliku, np. logo
 *              z pola Image), 'bool' (pole True/False), 'text' / 'textarea'
 *              (tekst, opcjonalnie sprawdzany kluczem 'pattern') albo 'email'.
 * - default   : wartosc uzywana, gdy pole jest puste lub ACF nie jest dostepne.
 * - choices   : dozwolone wartosci dla typu 'choice'.
 * - min / max : dopuszczalny zakres dla typu 'px' / 'percent' (walidacja zakresu, sekcja 9).
 * - nullable  : true = pusta wartosc jest poprawna i znaczaca (brak limitu,
 *              brak logo, niewypelnione pole kontaktowe).
 * - pattern   : wzorzec preg dla typu 'text' — wartosc niepasujaca jest odrzucana.
 *
 * @return array<string, array<string, mixed>> Schemat opcji.
 */
function cyber_option_schema() {
	return array(
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
			'choices' => array(
				'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
				'Georgia, "Times New Roman", Times, serif',
				'"Helvetica Neue", Helvetica, Arial, sans-serif',
			),
		),
		'font_family_text'            => array(
			'type'    => 'choice',
			'default' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
			'choices' => array(
				'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
				'Georgia, "Times New Roman", Times, serif',
				'"Helvetica Neue", Helvetica, Arial, sans-serif',
			),
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
			'choices' => array( 'left', 'center', 'right' ),
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
			'type'    => 'color',
			'default' => '#1a1a1a',
		),
		'header_menu_color_hover'     => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'header_menu_color_active'    => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'header_submenu_indicator'    => array(
			'type'    => 'bool',
			'default' => true,
		),
		'header_submenu_alignment'    => array(
			'type'    => 'choice',
			'default' => 'left',
			'choices' => array( 'left', 'center', 'right' ),
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
			'type'    => 'color',
			'default' => '#1a1a1a',
		),
		'header_submenu_color_hover'  => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'header_submenu_color_active' => array(
			'type'    => 'color',
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
			'type'    => 'color',
			'default' => '#ffffff',
		),
		'btn_large_color_hover'       => array(
			'type'    => 'color',
			'default' => '#ffffff',
		),
		'btn_large_bg_color'          => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'btn_large_bg_color_hover'    => array(
			'type'    => 'color',
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
			'type'    => 'color',
			'default' => '#ffffff',
		),
		'btn_medium_color_hover'      => array(
			'type'    => 'color',
			'default' => '#ffffff',
		),
		'btn_medium_bg_color'         => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'btn_medium_bg_color_hover'   => array(
			'type'    => 'color',
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
			'type'    => 'color',
			'default' => '#ffffff',
		),
		'btn_small_color_hover'       => array(
			'type'    => 'color',
			'default' => '#ffffff',
		),
		'btn_small_bg_color'          => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'btn_small_bg_color_hover'    => array(
			'type'    => 'color',
			'default' => '#0041c2',
		),
		'color_headings'              => array(
			'type'    => 'color',
			'default' => '#111111',
		),
		'color_text'                  => array(
			'type'    => 'color',
			'default' => '#333333',
		),
		'color_overtitle_1'           => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'color_overtitle_2'           => array(
			'type'    => 'color',
			'default' => '#666666',
		),
		'color_links'                 => array(
			'type'    => 'color',
			'default' => '#0057ff',
		),
		'color_hover'                 => array(
			'type'    => 'color',
			'default' => '#0041c2',
		),
		'color_border_1'              => array(
			'type'    => 'color',
			'default' => '#e0e0e0',
		),
		'color_border_2'              => array(
			'type'    => 'color',
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

	if ( 'bool' === $config['type'] ) {
		/*
		 * ACF zwraca dla pola True/False 1 albo 0 — zadna z tych wartosci nie jest
		 * "pusta" w rozumieniu warunku wyzej, wiec swiadome wylaczenie toggle'a
		 * nie zostanie podmienione na wartosc domyslna.
		 */
		return (bool) $value;
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
		$value = sanitize_hex_color( (string) $value );

		return ( null === $value || '' === $value ) ? $fallback : $value;
	}

	if ( 'color_alpha' === $config['type'] ) {
		$raw = trim( (string) $value );
		$hex = sanitize_hex_color( $raw );

		if ( null !== $hex && '' !== $hex ) {
			return $hex;
		}

		/*
		 * ACF z enable_opacity zwraca rgba(). sanitize_hex_color() takiej wartosci
		 * nie przepusci, a cien bez kanalu alfa jest w praktyce bezuzyteczny —
		 * stad osobny typ z wlasnym, scislym wzorcem.
		 */
		$pattern = '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)$/';

		return preg_match( $pattern, $raw ) ? $raw : $fallback;
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
