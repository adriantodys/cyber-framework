<?php
/**
 * Czcionki motywu — jeden rejestr rodzin.
 *
 * DWA ZRODLA STALY SIE JEDNYM. Lista rodzin byla wpisana rownolegle w polu ACF
 * (choices w group_global_options.json) i w cyber_option_schema(). Dodanie
 * czcionki wymagalo zmiany w obu miejscach naraz, a rozjazd oznaczalby, ze
 * redaktor wybiera wartosc, ktorej walidacja potem nie przyjmuje i po cichu
 * podmienia na domyslna. Teraz obie listy pochodza z cyber_font_families().
 *
 * CZCIONKI SA LOKALNE, nie z Google Fonts. Pliki leza w assets/fonts/, a
 * deklaracje @font-face w assets/css/fonts.css (PHP nie generuje regul CSS —
 * CLAUDE.md sekcja 6). Zaden adres nie wychodzi poza serwer: strona dziala bez
 * polaczenia z fonts.googleapis.com, a przegladarka nie wysyla tam zadania
 * z adresem IP odwiedzajacego.
 *
 * KLUCZEM JEST GOTOWY STOS CSS, nie slug. Wartosc pola trafia wprost do
 * zmiennej --cyber-font-family-*, a zapisane wybory z poprzednich wersji
 * zostaja poprawne.
 *
 * NOWA CZCIONKA: plik woff2 do assets/fonts/[slug]/, blok @font-face
 * w assets/css/fonts.css, jeden wiersz w tym rejestrze i wpis w tabeli
 * czcionek w CLAUDE.md (licencja!). Nic wiecej — pole w panelu, walidacja
 * i warunkowe ladowanie arkusza biora sie z rejestru.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rodziny czcionek dostepne w Global Options.
 *
 * Klucz  = stos CSS zapisywany w opcji i wypisywany w font-family.
 * label  = etykieta w panelu.
 * local  = katalog w assets/fonts/ dla czcionki z motywu; pusty dla rodzin
 *          systemowych, ktore nie potrzebuja zadnego pliku.
 *
 * @return array<string, array{label: string, local: string}>
 */
function cyber_font_families() {
	return array(
		'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif' => array(
			'label' => 'Systemowa — bezszeryfowa (domyślna)',
			'local' => '',
		),
		'Georgia, "Times New Roman", Times, serif'                        => array(
			'label' => 'Georgia — szeryfowa',
			'local' => '',
		),
		'"Helvetica Neue", Helvetica, Arial, sans-serif'                  => array(
			'label' => 'Helvetica / Arial — bezszeryfowa',
			'local' => '',
		),
		'"Space Grotesk", system-ui, sans-serif'                          => array(
			'label' => 'Space Grotesk — z motywu',
			'local' => 'space-grotesk',
		),
		'"Manrope", system-ui, sans-serif'                                => array(
			'label' => 'Manrope — z motywu',
			'local' => 'manrope',
		),
		'"Geist", system-ui, sans-serif'                                  => array(
			'label' => 'Geist — z motywu',
			'local' => 'geist',
		),
	);
}

/**
 * Stosy czcionek dozwolone w walidacji opcji.
 *
 * @return string[]
 */
function cyber_font_family_choices() {
	return array_keys( cyber_font_families() );
}

/**
 * Wypelnia liste rodzin w panelu z rejestru.
 *
 * Dotyczy obu pol naraz — naglowkow i tekstu.
 *
 * @param array $field Pole ACF.
 * @return array
 */
function cyber_font_family_field( $field ) {
	$choices = array();

	foreach ( cyber_font_families() as $stack => $family ) {
		$choices[ $stack ] = $family['label'];
	}

	$field['choices'] = $choices;

	return $field;
}
add_filter( 'acf/load_field/key=field_cyber_font_family_headings', 'cyber_font_family_field' );
add_filter( 'acf/load_field/key=field_cyber_font_family_text', 'cyber_font_family_field' );

/**
 * Czy ktorakolwiek z wybranych czcionek pochodzi z motywu.
 *
 * @return bool
 */
function cyber_fonts_local_in_use() {
	$families = cyber_font_families();

	foreach ( array( 'font_family_headings', 'font_family_text' ) as $key ) {
		$stack = (string) cyber_get_option( $key );

		if ( isset( $families[ $stack ] ) && '' !== $families[ $stack ]['local'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Arkusz z deklaracjami @font-face — tylko przy czcionce z motywu.
 *
 * Enqueue warunkowy zgodnie z CLAUDE.md sekcja 10. Sam arkusz nie pobiera
 * jeszcze zadnego pliku: przegladarka siega po woff2 dopiero wtedy, gdy
 * font-family faktycznie wskazuje dana rodzine.
 *
 * @return void
 */
function cyber_fonts_assets() {
	if ( ! cyber_fonts_local_in_use() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-fonts',
		CYBER_URI . '/assets/css/fonts.css',
		array(),
		cyber_asset_version( 'assets/css/fonts.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_fonts_assets', 5 );
