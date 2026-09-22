<?php
/**
 * Page header — pasek z tytulem strony nad trescia.
 *
 * Domyslnie WYLACZONY. Wlacza sie go raz, w Global Options -> Page header,
 * i wybiera tam typy tresci, ktorych dotyczy (strony, wpisy, dowolny CPT).
 * Pojedyncza strona moze ten wybor nadpisac w skrzynce "Cyber Framework —
 * Page header" (grupa group_page_header): wymusic pokazanie, ukryc, podmienic
 * zdjecie, wideo, tytul czy zajawke.
 *
 * ROZSTRZYGNIECIE WIDOCZNOSCI MA JEDNO MIEJSCE — cyber_page_header_data().
 * Widok dostaje gotowa tablice albo null (CLAUDE.md sekcja 4) i nie pyta
 * o zadne ustawienie.
 *
 * TLO: kolor, na nim zdjecie, na nim wideo, na wszystkim nakladka. Wideo gra
 * bez dzwieku i w petli; przy ograniczeniu animacji w systemie arkusz je chowa
 * i zostaje zdjecie. Zadnego JavaScriptu.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Typy tresci, dla ktorych page header ma sens.
 *
 * Publiczne typy bez zalacznikow i bez sekcji globalnych (te nie maja wlasnych
 * stron). Lista trafia do pola wyboru w Global Options i do walidacji.
 *
 * @return array<string, string> Slug => etykieta.
 */
function cyber_page_header_types() {
	$types = array();

	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $slug => $object ) {
		if ( in_array( $slug, array( 'attachment', 'cyber_global_section' ), true ) ) {
			continue;
		}

		$types[ $slug ] = $object->labels->name;
	}

	return $types;
}

/**
 * Wypelnia liste typow tresci w panelu — typy rejestruja wtyczki, wiec nie
 * moga stac na sztywno w JSON-ie.
 *
 * @param array $field Pole ACF.
 * @return array
 */
function cyber_page_header_type_choices( $field ) {
	$field['choices'] = cyber_page_header_types();

	return $field;
}
add_filter( 'acf/load_field/key=field_cyber_pageheader_types', 'cyber_page_header_type_choices' );

/**
 * Wartosc pola z grupy page headera dla biezacego wpisu.
 *
 * @param string $key     Nazwa pola bez prefiksu cyber_ph_.
 * @param int    $post_id Identyfikator wpisu.
 * @return mixed Pusty string, gdy ACF nie dziala albo pole jest puste.
 */
function cyber_page_header_field( $key, $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$value = get_field( 'cyber_ph_' . $key, $post_id );

	return ( null === $value || false === $value ) ? '' : $value;
}

/**
 * Czy page header ma sie pokazac na tym wpisie.
 *
 * Kolejnosc decyzji:
 *   1. ustawienie wpisu 'on' albo 'off' — rozstrzyga samo,
 *   2. globalny wlacznik + typ tresci na liscie.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return bool
 */
function cyber_page_header_visible( $post_id ) {
	$override = cyber_page_header_field( 'show', $post_id );

	if ( 'on' === $override ) {
		return true;
	}

	if ( 'off' === $override ) {
		return false;
	}

	if ( ! cyber_get_option( 'pageheader_enable' ) ) {
		return false;
	}

	$types = (array) cyber_get_option( 'pageheader_types' );

	return in_array( (string) get_post_type( $post_id ), $types, true );
}

/**
 * Adres pliku wideo, jesli to faktycznie plik wideo.
 *
 * Pole zwraca URL, wiec typ sprawdzamy po rozszerzeniu — inny plik wybrany
 * w panelu nie ma szans sie odtworzyc i nie ma po co go wypisywac.
 *
 * @param string $url Adres z pola.
 * @return array{url: string, type: string} Pusta tablica, gdy nie ma wideo.
 */
function cyber_page_header_video( $url ) {
	$url = esc_url_raw( (string) $url );

	if ( '' === $url ) {
		return array();
	}

	$types     = array(
		'mp4'  => 'video/mp4',
		'webm' => 'video/webm',
	);
	$extension = strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

	if ( ! isset( $types[ $extension ] ) ) {
		return array();
	}

	return array(
		'url'  => $url,
		'type' => $types[ $extension ],
	);
}

/**
 * Zajawka page headera.
 *
 * Wlasny tekst z pola, a bez niego zajawka wpisu (pole "Zajawka" albo poczatek
 * tresci). Zwraca czysty tekst — escapuje widok.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return string
 */
function cyber_page_header_excerpt( $post_id ) {
	$own = trim( (string) cyber_page_header_field( 'excerpt', $post_id ) );

	if ( '' !== $own ) {
		return sanitize_textarea_field( $own );
	}

	$post = get_post( $post_id );

	if ( ! $post ) {
		return '';
	}

	return wp_strip_all_tags( get_the_excerpt( $post ) );
}

/**
 * Komplet danych page headera albo null, gdy sie nie pokazuje.
 *
 * @return array<string, mixed>|null
 */
function cyber_page_header_data() {
	static $cache = null;

	// Dane liczone raz: pyta o nie kolejkowanie assetow, header.php i szablony.
	if ( null !== $cache ) {
		return $cache ? $cache : null;
	}

	$cache = false;

	if ( ! is_singular() ) {
		return null;
	}

	$post_id = get_queried_object_id();

	if ( ! $post_id || ! cyber_page_header_visible( $post_id ) ) {
		return null;
	}

	$width = cyber_page_header_field( 'width', $post_id );

	if ( ! in_array( $width, array( 'full', 'container' ), true ) ) {
		$width = (string) cyber_get_option( 'pageheader_width' );
	}

	// Pole wpisu ma pierwszenstwo; puste oddaje glos ustawieniu globalnemu.
	$image_src = cyber_page_header_field( 'image', $post_id );
	$video_src = cyber_page_header_field( 'video', $post_id );

	if ( ! $image_src ) {
		$image_src = cyber_get_option( 'pageheader_bg_image' );
	}

	if ( ! $video_src ) {
		$video_src = cyber_get_option( 'pageheader_bg_video' );
	}

	$image   = esc_url_raw( (string) $image_src );
	$video   = cyber_page_header_video( $video_src );
	$overlay = cyber_sanitize_color( cyber_page_header_field( 'overlay', $post_id ) );

	if ( '' === $overlay ) {
		$overlay = (string) cyber_get_option( 'pageheader_overlay' );
	}

	$height = cyber_page_header_field( 'height', $post_id );
	$height = is_numeric( $height ) ? max( 0, min( 900, (int) $height ) ) : (int) cyber_get_option( 'pageheader_height' );

	$excerpt_mode = cyber_page_header_field( 'show_excerpt', $post_id );
	$show_excerpt = 'on' === $excerpt_mode || ( 'off' !== $excerpt_mode && cyber_get_option( 'pageheader_show_excerpt' ) );
	$excerpt      = $show_excerpt ? cyber_page_header_excerpt( $post_id ) : '';

	$title = trim( (string) cyber_page_header_field( 'title', $post_id ) );

	$vars = array(
		'--cyber-ph-height' => sprintf( '%dpx', $height ),
		'--cyber-ph-bg'     => (string) cyber_get_option( 'pageheader_bg_color' ),
	);

	if ( '' !== $image ) {
		$vars['--cyber-ph-image'] = sprintf( 'url(%s)', $image );
	}

	if ( '' !== $overlay ) {
		$vars['--cyber-ph-overlay'] = $overlay;
	}

	$style = '';

	foreach ( $vars as $name => $value ) {
		$style .= sprintf( '%1$s:%2$s;', $name, $value );
	}

	$cache = array(
		'title'   => '' !== $title ? $title : wp_strip_all_tags( get_the_title( $post_id ) ),
		'excerpt' => $excerpt,
		'video'   => $video,
		'has_bg'  => '' !== $image || $video,
		'class'   => sprintf(
			'cyber-page-header cyber-page-header--%1$s cyber-page-header--%2$s%3$s',
			'container' === $width ? 'container' : 'full',
			cyber_section_choice( cyber_get_option( 'pageheader_align' ), cyber_alignments(), 'center' ),
			0 === $height ? ' cyber-page-header--auto' : ''
		),
		'style'   => $style,
	);

	return $cache;
}

/**
 * Czy page header pokazuje sie na biezacym widoku.
 *
 * Szablony pytaja o to, zeby nie wypisac DRUGIEGO <h1> — tytul strony niesie
 * wtedy page header (CLAUDE.md sekcja 11).
 *
 * @return bool
 */
function cyber_page_header_shows() {
	return null !== cyber_page_header_data();
}

/**
 * Zmienne CSS page headera z Global Options.
 *
 * Rozmiary tytulu i zajawki to odwolania do globalnych wielkosci, nie liczby
 * z jednostka — stad wlasna petla zamiast cyber_css_vars_from_map().
 *
 * @return string CSS bez znacznika <style>.
 */
function cyber_page_header_css() {
	$sizes   = array( 'text', 'h6', 'h5', 'h4' );
	$excerpt = (string) cyber_get_option( 'pageheader_excerpt_size' );

	$vars = array(
		'--cyber-ph-height-m'      => sprintf( '%dpx', (int) cyber_get_option( 'pageheader_height_mobile' ) ),
		'--cyber-ph-title-size'    => sprintf( 'var(--cyber-font-size-%s)', cyber_get_option( 'pageheader_title_size' ) ),
		'--cyber-ph-title-weight'  => (int) cyber_get_option( 'pageheader_title_weight' ),
		'--cyber-ph-title-color'   => (string) cyber_get_option( 'pageheader_title_color' ),
		'--cyber-ph-excerpt-size'  => sprintf( 'var(--cyber-font-size-%s)', in_array( $excerpt, $sizes, true ) ? $excerpt : 'text' ),
		'--cyber-ph-excerpt-color' => (string) cyber_get_option( 'pageheader_excerpt_color' ),
	);

	$css = ':root{';

	foreach ( $vars as $name => $value ) {
		$css .= sprintf( '%1$s:%2$s;', $name, $value );
	}

	return $css . '}';
}

/**
 * Wypisuje page header — wolane z header.php, nad okruszkami.
 *
 * @return void
 */
function cyber_page_header() {
	$data = cyber_page_header_data();

	if ( null === $data ) {
		return;
	}

	get_template_part( 'template-parts/page-header/page-header', null, $data );
}

/**
 * Styl page headera — tylko tam, gdzie faktycznie sie pokazuje.
 *
 * @return void
 */
function cyber_page_header_assets() {
	if ( null === cyber_page_header_data() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-page-header',
		CYBER_URI . '/assets/css/page-header.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/page-header.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_page_header_assets', 20 );
