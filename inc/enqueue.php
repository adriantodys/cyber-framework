<?php
/**
 * Rejestracja i wersjonowanie assetow.
 *
 * Wersja pliku pochodzi z filemtime() (CLAUDE.md sekcja 12) — kazda zmiana pliku
 * automatycznie uniewaznia cache przegladarki, bez recznego podbijania numerkow.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Zwraca wersje assetu na podstawie czasu modyfikacji pliku.
 *
 * @param string $relative_path Sciezka wzgledem katalogu motywu, np. 'assets/css/main.css'.
 * @return string Znacznik czasu jako wersja lub CYBER_VERSION, gdy pliku brak.
 */
function cyber_asset_version( $relative_path ) {
	$path = CYBER_DIR . '/' . ltrim( $relative_path, '/' );

	if ( ! file_exists( $path ) ) {
		return CYBER_VERSION;
	}

	return (string) filemtime( $path );
}

/**
 * Rejestruje i kolejkuje assety front-endu.
 *
 * Zasada: globalnie laduje sie wylacznie arkusz bazowy. Assety komponentow beda
 * kolejkowane warunkowo, przy renderowaniu konkretnego komponentu (sekcja 10).
 *
 * @return void
 */
function cyber_enqueue_assets() {
	wp_enqueue_style(
		'cyber-main',
		CYBER_URI . '/assets/css/main.css',
		array(),
		cyber_asset_version( 'assets/css/main.css' )
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cyber_enqueue_assets' );

/**
 * Buduje CSS ze zmiennymi kontenera na podstawie Global Options.
 *
 * Funkcja jest celowo oddzielona od wypisywania — zwraca czysty string, wiec da
 * sie ja przetestowac bez uruchamiania wp_head.
 *
 * Reguly (zrodlo: docs/acf-schema.md, zakladka "Glowne ustawienia strony"):
 * - szerokosc dotyczy wylacznie desktopu i zalezy od cyber_page_width_type,
 * - dla typu '100' pole page_width_100 jest opcjonalnym capem: puste = pelna
 *   szerokosc okna, wypelnione = gorny limit szerokosci kontenera,
 * - marginesy obowiazuja ZAWSZE, niezaleznie od wybranej szerokosci — to jeden
 *   wspolny harmonogram breakpointow 980 / 767 / 479 px.
 *
 * Wszystkie wartosci pochodza z cyber_get_option(), czyli sa juz zwalidowane
 * co do typu i zakresu (inc/helpers.php).
 *
 * @return string CSS bez znacznika <style>.
 */
function cyber_container_css() {
	$type = cyber_get_option( 'page_width_type' );

	if ( '100' === $type ) {
		$cap = cyber_get_option( 'page_width_100' );

		// Pustka jest tu znaczaca: brak limitu, kontener idzie na pelna szerokosc.
		$width = ( '' === $cap ) ? '100%' : sprintf( '%dpx', (int) $cap );
	} else {
		$width = sprintf( '%dpx', (int) cyber_get_option( 'page_width_' . $type ) );
	}

	$css = sprintf(
		':root{--cyber-container-width:%1$s;--cyber-container-margin:%2$dpx;}',
		$width,
		(int) cyber_get_option( 'page_margin_desktop' )
	);

	/*
	 * Klucz = gorna granica breakpointu w px, wartosc = klucz opcji.
	 * Kolejnosc od najszerszego do najwezszego ma znaczenie — kazda kolejna
	 * regula nadpisuje poprzednia.
	 */
	$breakpoints = array(
		980 => 'page_margin_tablet',
		767 => 'page_margin_mobile_l',
		479 => 'page_margin_mobile_s',
	);

	foreach ( $breakpoints as $max_width => $option_key ) {
		$css .= sprintf(
			'@media (max-width:%1$dpx){:root{--cyber-container-margin:%2$dpx;}}',
			$max_width,
			(int) cyber_get_option( $option_key )
		);
	}

	return $css;
}

/**
 * Wypisuje zmienne kontenera jako inline <style> w <head>.
 *
 * Priorytet 20 jest istotny: wp_head wypisuje arkusze stylow wczesniej
 * (wp_enqueue_scripts na priorytecie 1, wp_print_styles na 8). Dzieki temu te
 * reguly wygrywaja z domyslnymi wartosciami z assets/css/main.css przy tej samej
 * specyficznosci selektora :root.
 *
 * @return void
 */
function cyber_print_container_css() {
	printf(
		'<style id="cyber-container-vars">%s</style>' . "\n",
		wp_strip_all_tags( cyber_container_css() )
	);
}
add_action( 'wp_head', 'cyber_print_container_css', 20 );
