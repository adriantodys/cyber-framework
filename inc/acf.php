<?php
/**
 * Integracja z ACF PRO — Local JSON.
 *
 * Katalog acf-json/ jest zrodlem prawdy dla konfiguracji pol (CLAUDE.md sekcja 5).
 * Zapis idzie do katalogu motywu, odczyt takze — dzieki temu kazda zmiana grupy pol
 * w adminie ACF laduje w repozytorium jako plik JSON i podlega code review.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sciezka zapisu Local JSON.
 *
 * @param string $path Domyslna sciezka ustawiona przez ACF.
 * @return string Sciezka do acf-json/ w katalogu motywu.
 */
function cyber_acf_json_save_point( $path ) {
	unset( $path );

	return CYBER_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'cyber_acf_json_save_point' );

/**
 * Sciezki odczytu Local JSON.
 *
 * Domyslna sciezka ACF jest usuwana, zeby motyw ladowal wylacznie wlasne grupy pol
 * i nie zaciagal przypadkiem konfiguracji z innej lokalizacji.
 *
 * @param array $paths Sciezki odczytu ustawione przez ACF.
 * @return array Sciezki odczytu.
 */
function cyber_acf_json_load_point( $paths ) {
	unset( $paths[0] );

	$paths[] = CYBER_DIR . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'cyber_acf_json_load_point' );

/**
 * Ostrzega administratora, gdy ACF PRO nie jest aktywne.
 *
 * ACF PRO jest twarda zaleznoscia motywu — bez niego Options Page nie istnieje,
 * a cyber_get_option() zwraca wylacznie wartosci domyslne ze schematu.
 *
 * @return void
 */
function cyber_acf_missing_notice() {
	if ( class_exists( 'ACF' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__(
			'Cyber Framework wymaga wtyczki ACF PRO. Bez niej strona ustawien motywu jest niedostepna, a motyw korzysta z wartosci domyslnych.',
			'cyber-framework'
		)
	);
}
add_action( 'admin_notices', 'cyber_acf_missing_notice' );
