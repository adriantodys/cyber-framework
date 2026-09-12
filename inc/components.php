<?php
/**
 * Funkcje pomocnicze komponentow reuzywalnych.
 *
 * Warstwa logiki komponentow: normalizacja i walidacja argumentow. Sam markup
 * zyje w template-parts/components/ i dostaje gotowe, sprawdzone dane
 * (CLAUDE.md sekcja 4 i 7).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wypisuje przycisk.
 *
 * Wygladem steruja wylacznie zmienne CSS z zakladki "Przyciski" — komponent
 * nie przyjmuje kolorow ani rozmiarow w pikselach, tylko nazwe rozmiaru.
 *
 * Pusty tekst albo pusty adres oznacza "przycisk nie jest skonfigurowany"
 * i nie generuje zadnego znacznika. To celowe: w sekcjach ACF przycisk bywa
 * polem opcjonalnym i widok nie powinien musiec tego sprawdzac u siebie.
 *
 * @param array $args {
 *     Argumenty przycisku.
 *
 *     @type string $text   Tekst na przycisku. Wymagany.
 *     @type string $url    Adres docelowy. Wymagany.
 *     @type string $size   Rozmiar: large, medium albo small. Domyslnie medium.
 *     @type string $target Atrybut target, np. '_blank'. Domyslnie pusty.
 *     @type string $rel    Atrybut rel. Domyslnie pusty.
 * }
 * @return void
 */
function cyber_button( $args = array() ) {
	$defaults = array(
		'text'   => '',
		'url'    => '',
		'size'   => 'medium',
		'target' => '',
		'rel'    => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( '' === trim( (string) $args['text'] ) || '' === trim( (string) $args['url'] ) ) {
		return;
	}

	if ( ! in_array( $args['size'], cyber_button_sizes(), true ) ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html(
				sprintf(
					'Nieznany rozmiar przycisku "%s". Dozwolone: %s.',
					$args['size'],
					implode( ', ', cyber_button_sizes() )
				)
			),
			'0.1.0'
		);

		$args['size'] = $defaults['size'];
	}

	/*
	 * Link otwierany w nowej karcie bez rel="noopener" daje stronie docelowej
	 * dostep do window.opener. Wspolczesne przegladarki domyslaja sie tego same,
	 * ale nie polegamy na tym (CLAUDE.md sekcja 9).
	 */
	if ( '_blank' === $args['target'] && '' === $args['rel'] ) {
		$args['rel'] = 'noopener';
	}

	get_template_part( 'template-parts/components/button', null, $args );
}

/**
 * Buduje liste klas wrappera modulu: klasa bazowa + modyfikator wariantu.
 *
 * CLAUDE.md sekcja 20: komponent, ktory moze zyskac alternatywny wariant
 * wizualny, ma go dostawac jako wymienna klase na glownym wrapperze, a nie
 * przez przepisywanie selektora bazowego. Wariant 'default' istnieje po to,
 * zeby dolozenie drugiego bylo nowa klasa obok istniejacej.
 *
 * Wariant jest przepuszczany przez sanitize_html_class(), bo trafia wprost
 * do atrybutu class — pusta albo niepoprawna wartosc wraca do 'default'
 * zamiast wygenerowac klase-smiecia.
 *
 * Stany niezalezne od wariantu (np. przyklejony header) doklada sie osobna
 * klasa obok zwroconej — to druga, prostopadla os i nie zajmuje slotu wariantu.
 *
 * @param string $base    Klasa bazowa modulu, np. 'cyber-header'.
 * @param string $variant Nazwa wariantu. Domyslnie 'default'.
 * @return string Klasa bazowa i modyfikator, np. 'cyber-header cyber-header--default'.
 */
function cyber_variant_class( $base, $variant = 'default' ) {
	$base    = sanitize_html_class( $base );
	$variant = sanitize_html_class( $variant );

	if ( '' === $variant ) {
		$variant = 'default';
	}

	return $base . ' ' . $base . '--' . $variant;
}

/**
 * Zwraca liste profili spolecznosciowych do wyswietlenia.
 *
 * Zrodlem sa pola cyber_social_* z zakladki "Social Media" — zaden modul nie
 * ma wlasnych pol na te adresy (CLAUDE.md sekcja 22).
 *
 * Dwa konteksty roznia sie jednym warunkiem:
 * - Top Header respektuje wylaczniki cyber_topheader_show_* (koniunkcja:
 *   wlacznik ORAZ wypelniony adres),
 * - Stopka ich nie ma — liczy sie wylacznie to, czy adres jest wypelniony.
 *
 * @param bool $respect_toggles Czy uwzgledniac wylaczniki Top Header.
 * @return array Lista tablic 'platform', 'label', 'url'.
 */
function cyber_social_links( $respect_toggles = false ) {
	$links = array();

	foreach ( cyber_social_platforms() as $platform => $label ) {
		$url = cyber_get_option( 'social_' . $platform );

		if ( '' === $url ) {
			continue;
		}

		if ( $respect_toggles && ! cyber_get_option( 'topheader_show_' . $platform ) ) {
			continue;
		}

		$links[] = array(
			'platform' => $platform,
			'label'    => $label,
			'url'      => $url,
		);
	}

	return $links;
}

/**
 * Wypisuje liste ikon social media.
 *
 * Wspolny komponent dla Top Header i stopki — pojedyncza petla po platformach
 * zyje w jednym miejscu, zamiast byc kopiowana do kazdego widoku
 * (CLAUDE.md sekcja 22b).
 *
 * Gdy nie ma zadnego wypelnionego adresu, komponent nie generuje niczego —
 * pusty kontener flex zostawialby w layoucie dziure po odstepach.
 *
 * @param array $args {
 *     @type bool   $respect_toggles Czy uwzgledniac wylaczniki Top Header. Domyslnie false.
 *     @type string $class           Dodatkowa klasa kontenera, np. 'cyber-footer__social'.
 * }
 * @return void
 */
function cyber_social_icons( $args = array() ) {
	$defaults = array(
		'respect_toggles' => false,
		'class'           => '',
	);

	$args  = wp_parse_args( $args, $defaults );
	$items = cyber_social_links( (bool) $args['respect_toggles'] );

	if ( array() === $items ) {
		return;
	}

	get_template_part(
		'template-parts/components/social-icons',
		null,
		array(
			'items' => $items,
			'class' => (string) $args['class'],
		)
	);
}
