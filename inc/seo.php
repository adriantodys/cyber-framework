<?php
/**
 * SEO: kody sledzenia i skrypty z Global Options.
 *
 * Trzy pola zakladki SEO (docs/acf-schema.md) wypisywane w trzech miejscach
 * dokumentu: poczatek <head>, poczatek <body>, koniec <body>. To miejsce na
 * Google Tag Manager, gtag.js, piksele, kody weryfikacji domeny i wlasne
 * skrypty — bez wtyczki "insert headers and footers".
 *
 * BEZPIECZENSTWO. Kod jest wypisywany dokladnie tak, jak go wkleil
 * administrator — bez escapowania i bez kses, bo escapowanie zamieniloby
 * <script> w tekst, a kses go usunal. Granica zaufania to uprawnienia:
 * pole edytuje tylko manage_options (Options Page), a ACF przy zapisie
 * przepuszcza <script> wylacznie uzytkownikowi z unfiltered_html (na
 * multisite: super admin). Typ schematu 'code' w cyber_option_schema()
 * robi wylacznie trim(). Szczegoly: docs/security.md.
 *
 * Kod wychodzi dla KAZDEGO odwiedzajacego, takze zalogowanego — wykluczenie
 * administratorow z analityki robi sie po stronie narzedzia (filtr IP w GA,
 * zmienna w GTM), nie w motywie.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wypisuje kod z jednego pola SEO, jesli jest wypelnione.
 *
 * @param string $key Klucz opcji: seo_head_code, seo_body_start_code
 *                    albo seo_body_end_code.
 * @return void
 */
function cyber_seo_print_code( $key ) {
	$code = (string) cyber_get_option( $key );

	if ( '' === $code ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- surowy kod administratora (typ 'code'), patrz docblock pliku i docs/security.md.
	echo "\n" . $code . "\n";
}

/**
 * Kod w <head>, jak najwyzej — GTM i gtag chca byc przed innymi skryptami.
 *
 * Priorytet 1 na wp_head: przed arkuszami motywu (priorytet domyslny 8-10
 * dla wp_print_styles) i przed blokiem zmiennych (20). <meta charset>
 * i viewport sa juz wypisane, bo stoja w header.php przed wp_head().
 *
 * @return void
 */
function cyber_seo_head_code() {
	cyber_seo_print_code( 'seo_head_code' );
}
add_action( 'wp_head', 'cyber_seo_head_code', 1 );

/**
 * Kod na poczatku <body> — fragment <noscript> GTM "zaraz po otwarciu body".
 *
 * @return void
 */
function cyber_seo_body_start_code() {
	cyber_seo_print_code( 'seo_body_start_code' );
}
add_action( 'wp_body_open', 'cyber_seo_body_start_code', 1 );

/**
 * Kod na koncu <body> — po skryptach motywu (wp_footer wypisuje je na 20).
 *
 * @return void
 */
function cyber_seo_body_end_code() {
	cyber_seo_print_code( 'seo_body_end_code' );
}
add_action( 'wp_footer', 'cyber_seo_body_end_code', 99 );
