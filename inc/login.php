<?php
/**
 * Strona logowania (wp-login.php): logo witryny zamiast logo WordPressa.
 *
 * Zrodlem jest to samo pole co w headerze — cyber_header_logo (Global Options →
 * Header Desktop). Modul nie ma wlasnych pol. Gdy logo nie jest wgrane, nic sie
 * nie zmienia: zostaje logo WordPressa, jego link i tekst.
 *
 * Zgodnie z CLAUDE.md sekcja 6 PHP nie generuje regul CSS — regula siedzi
 * w assets/css/login.css, a PHP wypisuje wylacznie zmienna --cyber-login-logo.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adres logo do strony logowania albo pusty string, gdy logo nie wgrano.
 *
 * @return string
 */
function cyber_login_logo_url() {
	return (string) cyber_get_option( 'header_logo' );
}

/**
 * Arkusz strony logowania ze zmienna logo — tylko gdy logo jest ustawione.
 *
 * Adres przechodzi przez esc_url_raw(), ktore usuwa cudzyslow, backslash,
 * znaki nowej linii i "<", wiec nie moze wyjsc ani z url("…"), ani z <style>.
 *
 * @return void
 */
function cyber_login_assets() {
	$logo = esc_url_raw( cyber_login_logo_url() );

	if ( '' === $logo ) {
		return;
	}

	// Zaleznosc od 'login' — arkusz laduje sie po arkuszu WordPressa i nadpisuje logo.
	wp_enqueue_style(
		'cyber-login',
		CYBER_URI . '/assets/css/login.css',
		array( 'login' ),
		cyber_asset_version( 'assets/css/login.css' )
	);

	wp_add_inline_style(
		'cyber-login',
		cyber_css_root( array( '--cyber-login-logo' => 'url("' . $logo . '")' ) )
	);
}
add_action( 'login_enqueue_scripts', 'cyber_login_assets' );

/**
 * Link logo prowadzi na strone glowna witryny, nie na wordpress.org.
 *
 * @param string $url Domyslny adres linku.
 * @return string
 */
function cyber_login_header_url( $url ) {
	return '' === cyber_login_logo_url() ? $url : home_url( '/' );
}
add_filter( 'login_headerurl', 'cyber_login_header_url' );

/**
 * Tekst linku logo (dostepna nazwa) to nazwa witryny, nie "Powered by WordPress".
 *
 * @param string $text Domyslny tekst linku.
 * @return string
 */
function cyber_login_header_text( $text ) {
	return '' === cyber_login_logo_url() ? $text : get_bloginfo( 'name', 'display' );
}
add_filter( 'login_headertext', 'cyber_login_header_text' );
