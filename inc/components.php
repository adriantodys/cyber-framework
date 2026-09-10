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
