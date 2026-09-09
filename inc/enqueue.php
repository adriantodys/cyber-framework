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
