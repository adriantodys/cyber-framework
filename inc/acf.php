<?php
/**
 * Integracja z ACF PRO — Local JSON.
 *
 * Katalog acf-json/ jest zrodlem prawdy dla konfiguracji pol (CLAUDE.md sekcja 5).
 * Odczyt idzie z parenta, childa i katalogow dolozonych filtrem (moduly);
 * zapis wraca do katalogu, z ktorego grupa zostala wczytana, a nowa grupa
 * laduje w motywie aktywnym. Dzieki temu kazda zmiana grupy pol w adminie
 * ACF laduje we WLASCIWYM repozytorium jako plik JSON (CLAUDE.md sekcja 23).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Czy ACF jest dostepny do odczytu pol.
 *
 * ACF jest zaleznoscia TWARDA (CLAUDE.md sekcja 2), ale zasada "jedna
 * zaleznosc, jeden plik, ktory o niej decyduje" obowiazuje tak samo jak przy
 * WooCommerce i CF7. Moduly pytaja tutaj, zamiast rozsypywac po dziewieciu
 * plikach wlasne function_exists( 'get_field' ).
 *
 * @return bool
 */
function cyber_is_acf_active() {
	return function_exists( 'get_field' );
}

/**
 * Katalog Local JSON motywu AKTYWNEGO: child, gdy jest, inaczej parent.
 *
 * To katalog dla grup NOWYCH — takich, ktore nie maja jeszcze pliku. Grupa,
 * ktora plik ma, wraca do swojego katalogu (cyber_acf_json_save_paths()),
 * wiec zapis w panelu na stronie klienta nigdy nie dotyka acf-json/ parenta.
 *
 * @return string Sciezka bezwzgledna, bez koncowego ukosnika.
 */
function cyber_acf_json_active_dir() {
	return ( is_child_theme() ? get_stylesheet_directory() : CYBER_DIR ) . '/acf-json';
}

/**
 * Katalogi Local JSON, z ktorych motyw czyta grupy pol.
 *
 * Kolejnosc: parent, child (jesli aktywny), katalogi dolozone filtrem
 * `cyber_acf_json_dirs` — np. acf-json/ kazdego modulu w child/modules/.
 * Zwracane sa tylko istniejace katalogi; brak acf-json/ w child nie jest
 * bledem, dopoki child nie ma wlasnych grup.
 *
 * @return string[] Sciezki bezwzgledne.
 */
function cyber_acf_json_dirs() {
	$dirs = array( CYBER_DIR . '/acf-json' );

	if ( is_child_theme() ) {
		$dirs[] = get_stylesheet_directory() . '/acf-json';
	}

	/**
	 * Dodatkowe katalogi Local JSON (child theme, moduly).
	 *
	 * Child dopisuje tu katalogi swoich modulow. Klucz grupy musi byc unikalny
	 * w calym zestawie — przy powtorce wygrywa plik wczytany pozniej.
	 *
	 * @param string[] $dirs Sciezki bezwzgledne.
	 */
	$dirs = apply_filters( 'cyber_acf_json_dirs', $dirs );

	return array_values( array_unique( array_filter( array_map( 'strval', (array) $dirs ), 'is_dir' ) ) );
}

/**
 * Sciezka zapisu Local JSON dla grup bez pliku.
 *
 * @param string $path Domyslna sciezka ustawiona przez ACF.
 * @return string Sciezka do acf-json/ motywu aktywnego (child albo parent).
 */
function cyber_acf_json_save_point( $path ) {
	unset( $path );

	return cyber_acf_json_active_dir();
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

	return array_merge( array_values( (array) $paths ), cyber_acf_json_dirs() );
}
add_filter( 'acf/settings/load_json', 'cyber_acf_json_load_point' );

/**
 * Grupa z plikiem zapisuje sie tam, skad zostala wczytana.
 *
 * ACF zapisuje KAZDA grupe do jednej sciezki save_json. Z childem to pulapka:
 * redaktor poprawia etykiete w grupie parenta, ACF zapisuje plik do child/
 * acf-json/, a od tej chwili istnieja dwie kopie tej samej grupy, z ktorych
 * child wygrywa i parent przestaje dostawac aktualizacje. Ten filtr szuka
 * pliku grupy w katalogach z cyber_acf_json_dirs() i kieruje zapis do jego
 * katalogu. Grupa bez pliku idzie do motywu aktywnego (save_json).
 *
 * Filtr `acf/json/save_paths` istnieje od ACF 6.2. Starsze ACF nie
 * routuje — wtedy zapis grupy parenta na stronie z childem LADUJE w child
 * i trzeba go przeniesc recznie.
 *
 * @param string[] $paths Sciezki zapisu zaproponowane przez ACF.
 * @param array    $post  Grupa pol (albo CPT / taksonomia / Options Page z UI ACF).
 * @return string[] Sciezki zapisu.
 */
function cyber_acf_json_save_paths( $paths, $post ) {
	if ( empty( $post['key'] ) || ! function_exists( 'acf_get_local_json_files' ) ) {
		return $paths;
	}

	$internal_type = function_exists( 'acf_determine_internal_post_type' )
		? acf_determine_internal_post_type( $post['key'] )
		: 'acf-field-group';

	$files = acf_get_local_json_files( $internal_type ? $internal_type : 'acf-field-group' );

	if ( isset( $files[ $post['key'] ] ) ) {
		return array( dirname( $files[ $post['key'] ] ) );
	}

	return $paths;
}
add_filter( 'acf/json/save_paths', 'cyber_acf_json_save_paths', 10, 2 );

/**
 * Ostrzega, gdy child nie ma zapisywalnego acf-json/.
 *
 * Bez tego katalogu ACF zapisze nowa grupe tylko w bazie — po cichu, bez
 * pliku, wiec grupa nie trafi do repozytorium strony. Komunikat tylko dla
 * kogos, kto moze to naprawic; gosc witryny nie widzi nic.
 *
 * @return void
 */
function cyber_acf_child_json_notice() {
	if ( ! is_child_theme() || ! cyber_is_acf_active() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$dir = cyber_acf_json_active_dir();

	if ( is_dir( $dir ) && wp_is_writable( $dir ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: sciezka katalogu */
				__( 'Cyber Framework: motyw potomny nie ma zapisywalnego katalogu %s. Nowe grupy pol ACF nie zapisza sie jako pliki JSON.', 'cyber-framework' ),
				$dir
			)
		)
	);
}
add_action( 'admin_notices', 'cyber_acf_child_json_notice' );

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
