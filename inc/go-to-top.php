<?php
/**
 * Przycisk "do gory" — kwadrat ze strzalka w rogu ekranu.
 *
 * Pojawia sie po przewinieciu strony w dol o zadana liczbe pikseli i wraca
 * nia na sama gore. Ustawienia: Global Options -> Przycisk do gory.
 * Domyslnie wylaczony.
 *
 * Pliki modulu:
 *
 *   inc/go-to-top.php                          dane, markup w wp_footer, assety
 *   template-parts/go-to-top/go-to-top.php     widok
 *   assets/css/go-to-top.css                   wyglad i stany
 *   assets/js/go-to-top.js                     pokazywanie po przewinieciu, powrot na gore
 *
 * Zmienne CSS (--cyber-totop-*) buduje cyber_go_to_top_css() w inc/enqueue.php,
 * razem z pozostalymi modulami Global Options (CLAUDE.md sekcja 6).
 *
 * Przycisk wypisuje sie na hooku wp_footer, a nie wywolaniem w footer.php:
 * dotyczy kazdego widoku motywu (strony, blog, sklep), a modul pozostaje
 * w calosci w swoich plikach.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dane przycisku dla widoku albo null, gdy przycisk jest wylaczony.
 *
 * @return array{class: string, show_after: int, label: string, icon: string}|null
 */
function cyber_go_to_top_data() {
	if ( ! cyber_get_option( 'totop_enable' ) ) {
		return null;
	}

	/*
	 * Polozenie to klasa modyfikujaca (CLAUDE.md sekcja 20), a "ukryj na
	 * telefonie" — osobna klasa stanu obok wariantu, nie kolejna jego wartosc
	 * (sekcja 20, "Stan to nie wariant").
	 */
	$classes = array(
		cyber_variant_class( 'cyber-totop' ),
		'cyber-totop--' . sanitize_html_class( (string) cyber_get_option( 'totop_position' ) ),
	);

	if ( ! cyber_get_option( 'totop_mobile' ) ) {
		$classes[] = 'cyber-totop--hide-mobile';
	}

	return array(
		'class'      => implode( ' ', $classes ),
		'show_after' => (int) cyber_get_option( 'totop_show_after' ),
		'label'      => __( 'Przewin do gory strony', 'cyber-framework' ),
		'icon'       => cyber_get_icon( 'arrow-up' ),
	);
}

/**
 * Wypisuje przycisk na koncu <body>.
 *
 * Priorytet 5 — przed skryptami, ktore wp_footer drukuje na 20.
 *
 * @return void
 */
function cyber_go_to_top() {
	$data = cyber_go_to_top_data();

	if ( null === $data ) {
		return;
	}

	get_template_part( 'template-parts/go-to-top/go-to-top', null, $data );
}
add_action( 'wp_footer', 'cyber_go_to_top', 5 );

/**
 * Arkusz i skrypt przycisku — tylko gdy przycisk jest wlaczony (CLAUDE.md sekcja 10).
 *
 * @return void
 */
function cyber_go_to_top_assets() {
	if ( ! cyber_get_option( 'totop_enable' ) ) {
		return;
	}

	wp_enqueue_style(
		'cyber-go-to-top',
		CYBER_URI . '/assets/css/go-to-top.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/go-to-top.css' )
	);

	wp_enqueue_script(
		'cyber-go-to-top',
		CYBER_URI . '/assets/js/go-to-top.js',
		array(),
		cyber_asset_version( 'assets/js/go-to-top.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_go_to_top_assets', 20 );
