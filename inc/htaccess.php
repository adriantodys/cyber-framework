<?php
/**
 * Blok Cyber Framework w .htaccess: blokada plikow informacyjnych i naglowki HTTP.
 *
 * Motyw dopisuje do .htaccess WLASNY blok miedzy znacznikami
 * "# BEGIN Cyber Framework" / "# END Cyber Framework" — tym samym
 * mechanizmem, ktorym WordPress utrzymuje blok "# BEGIN WordPress"
 * (insert_with_markers()). Blok jest podmieniany w calosci, wiec:
 * - reczna zmiana WEWNATRZ bloku znika przy nastepnym zapisie,
 * - wlasne reguly ida POZA znaczniki albo przez filtr cyber_htaccess_rules,
 * - po wylaczeniu w panelu albo zmianie motywu blok jest usuwany.
 *
 * Co tu NIE wchodzi i dlaczego: Options -Indexes (na hostingu bez
 * AllowOverride Options daje 500 na calej witrynie), xmlrpc.php (psuje
 * aplikacje mobilna i Jetpack). Zadna z regul ponizej nie ma znanego
 * scenariusza polozenia strony: dyrektywy Require wymagaja Apache 2.4,
 * ktory sprawdzamy; naglowki sa w <IfModule mod_headers.c>; HSTS wchodzi
 * tylko na witrynie https.
 *
 * Kiedy blok jest zapisywany (cyber_htaccess_sync()):
 * 1. aktywacja motywu (after_switch_theme — takze childa),
 * 2. zapis Global Options (acf/save_post dla 'options'),
 * 3. wejscie do panelu, gdy tresc bloku sie zmienila — porownanie skrotu
 *    (aktualizacja motywu, przejscie na https, filtr z childa),
 * 4. zmiana motywu na inny (switch_theme) — blok jest USUWANY.
 *
 * Serwer inny niz Apache / LiteSpeed ($is_apache): plik nie jest ruszany,
 * naglowki HTTP wysyla PHP na send_headers (bez blokady plikow — na to
 * motyw nie ma wplywu bez konfiguracji serwera).
 *
 * Ustawienia: Global Options -> .htaccess. Lista regul i koszty:
 * docs/security.md, "Blok .htaccess".
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Znacznik bloku w .htaccess ("# BEGIN Cyber Framework").
 */
const CYBER_HTACCESS_MARKER = 'Cyber Framework';

/**
 * Opcja ze skrotem ostatnio zapisanego bloku ('' = blok nie jest zapisany).
 */
const CYBER_HTACCESS_HASH_OPTION = 'cyber_htaccess_hash';

/**
 * Opcja z czasem ostatniego zapisu (unix timestamp).
 */
const CYBER_HTACCESS_TIME_OPTION = 'cyber_htaccess_time';

/**
 * Czy serwer czyta .htaccess (Apache albo LiteSpeed) w wersji z Require.
 *
 * $is_apache ustawia WordPress z SERVER_SOFTWARE. "Require all denied"
 * istnieje od Apache 2.4; starsza wersja zwrocilaby 500, wiec przy
 * wykrytym 2.2 nic nie piszemy. Brak numeru wersji w naglowku serwera
 * (czesta konfiguracja ServerTokens Prod) traktujemy jako 2.4 — Apache
 * 2.2 nie ma wsparcia od 2017 roku.
 *
 * @return bool
 */
function cyber_htaccess_server_ok() {
	global $is_apache;

	if ( empty( $is_apache ) ) {
		return false;
	}

	$software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

	return ! preg_match( '#Apache/(1\.|2\.[0-3]\.)#i', $software );
}

/**
 * Sciezka do .htaccess w katalogu glownym witryny.
 *
 * @return string
 */
function cyber_htaccess_path() {
	require_once ABSPATH . 'wp-admin/includes/file.php';

	return get_home_path() . '.htaccess';
}

/**
 * Czy da sie zapisac .htaccess (istniejacy plik albo jego katalog).
 *
 * @return bool
 */
function cyber_htaccess_writable() {
	$path = cyber_htaccess_path();

	return file_exists( $path ) ? wp_is_writable( $path ) : wp_is_writable( dirname( $path ) );
}

/**
 * Czy witryna dziala na https (warunek HSTS).
 *
 * @return bool
 */
function cyber_htaccess_is_https() {
	return function_exists( 'wp_is_using_https' ) ? wp_is_using_https() : ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) );
}

/**
 * Naglowki HTTP wlaczone w panelu: nazwa => wartosc ('' = usun naglowek).
 *
 * Jedno zrodlo dla bloku .htaccess i dla wysylki z PHP na serwerze bez
 * .htaccess. HSTS tylko na https — na http przegladarka i tak by go
 * zignorowala, a po przejsciu na https stary wpis moglby zaskoczyc.
 *
 * @return array<string, string>
 */
function cyber_htaccess_headers() {
	$headers = array();

	if ( cyber_get_option( 'htaccess_hsts' ) && cyber_htaccess_is_https() ) {
		$headers['Strict-Transport-Security'] = 'max-age=31536000';
	}

	if ( cyber_get_option( 'htaccess_frame' ) ) {
		$headers['X-Frame-Options']         = 'SAMEORIGIN';
		$headers['Content-Security-Policy'] = "frame-ancestors 'self'";
	}

	if ( cyber_get_option( 'htaccess_nosniff' ) ) {
		$headers['X-Content-Type-Options'] = 'nosniff';
	}

	if ( cyber_get_option( 'htaccess_referrer' ) ) {
		$headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
	}

	if ( cyber_get_option( 'htaccess_permissions' ) ) {
		$headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=()';
	}

	if ( cyber_get_option( 'htaccess_powered_by' ) ) {
		$headers['X-Powered-By'] = '';
	}

	/**
	 * Naglowki HTTP bloku .htaccess i wysylki z PHP.
	 *
	 * @param array<string, string> $headers Nazwa => wartosc; pusty string = unset.
	 */
	return apply_filters( 'cyber_htaccess_headers', $headers );
}

/**
 * Linie bloku .htaccess wynikajace z ustawien. Pusta tablica = brak bloku.
 *
 * @return string[]
 */
function cyber_htaccess_rules() {
	if ( ! cyber_get_option( 'htaccess_enable' ) ) {
		return array();
	}

	$lines = array();

	if ( cyber_get_option( 'htaccess_files' ) ) {
		$lines[] = '# Pliki informacyjne i logi: zdradzaja wersje WP, zadna funkcja strony ich nie uzywa.';
		$lines[] = '<FilesMatch "(\.log|readme\.html|license\.txt)$">';
		$lines[] = '    Require all denied';
		$lines[] = '</FilesMatch>';
		$lines[] = '<Files "wp-links-opml.php">';
		$lines[] = '    Require all denied';
		$lines[] = '</Files>';
	}

	$headers = cyber_htaccess_headers();

	if ( $headers ) {
		$lines[] = '# Naglowki HTTP (Global Options -> .htaccess).';
		$lines[] = '<IfModule mod_headers.c>';

		foreach ( $headers as $name => $value ) {
			$lines[] = '' === $value
				? sprintf( '    Header always unset %s', $name )
				: sprintf( '    Header always set %s "%s"', $name, $value );
		}

		$lines[] = '</IfModule>';
	}

	/**
	 * Linie bloku .htaccess (bez znacznikow BEGIN / END).
	 *
	 * Child dopisuje tu wlasne dyrektywy zamiast edytowac plik recznie —
	 * reczna zmiana wewnatrz bloku znika przy nastepnym zapisie.
	 *
	 * @param string[] $lines Linie bloku.
	 */
	return (array) apply_filters( 'cyber_htaccess_rules', $lines );
}

/**
 * Czy .htaccess zawiera aktualnie blok Cyber Framework.
 *
 * @return bool
 */
function cyber_htaccess_has_block() {
	$path = cyber_htaccess_path();

	if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- lokalny plik konfiguracyjny, nie zdalny adres.
	$contents = (string) file_get_contents( $path );

	return false !== strpos( $contents, '# BEGIN ' . CYBER_HTACCESS_MARKER );
}

/**
 * Usuwa blok Cyber Framework z .htaccess (razem ze znacznikami).
 *
 * insert_with_markers() z pusta tablica zostawilby znaczniki z samym
 * komentarzem, wiec usuwamy je sami. Plik bez bloku zostaje nietkniety.
 *
 * @return bool true, gdy bloku nie ma (usuniety albo nie bylo).
 */
function cyber_htaccess_remove() {
	$path = cyber_htaccess_path();

	if ( ! cyber_htaccess_has_block() ) {
		return true;
	}

	if ( ! wp_is_writable( $path ) ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- jak wyzej.
	$contents = (string) file_get_contents( $path );
	$pattern  = '/\R?# BEGIN ' . preg_quote( CYBER_HTACCESS_MARKER, '/' ) . '\R.*?# END ' . preg_quote( CYBER_HTACCESS_MARKER, '/' ) . '\R?/s';
	$contents = (string) preg_replace( $pattern, '', $contents );

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- ten sam plik, ktory zapisuje insert_with_markers().
	return false !== file_put_contents( $path, $contents );
}

/**
 * Zapisuje aktualny blok do .htaccess albo go usuwa, gdy nie ma co zapisac.
 *
 * Idempotentne: ten sam blok = brak zmiany pliku (insert_with_markers()
 * porownuje linie). Skrot w opcji pozwala pominac odczyt pliku na kazdym
 * zadaniu panelu.
 *
 * @param bool $force true = zapisz nawet przy tym samym skrocie (aktywacja, zapis ustawien).
 * @return bool Czy stan pliku jest zgodny z ustawieniami.
 */
function cyber_htaccess_sync( $force = false ) {
	if ( ! cyber_htaccess_server_ok() ) {
		return false;
	}

	$lines = cyber_htaccess_rules();
	$hash  = $lines ? md5( implode( "\n", $lines ) ) : '';

	if ( ! $force && $hash === (string) get_option( CYBER_HTACCESS_HASH_OPTION, '' ) ) {
		return true;
	}

	if ( ! $lines ) {
		$ok = cyber_htaccess_remove();
	} else {
		if ( ! cyber_htaccess_writable() ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/misc.php';

		$ok = insert_with_markers( cyber_htaccess_path(), CYBER_HTACCESS_MARKER, $lines );
	}

	if ( $ok ) {
		update_option( CYBER_HTACCESS_HASH_OPTION, $hash, false );
		update_option( CYBER_HTACCESS_TIME_OPTION, time(), false );
	}

	return (bool) $ok;
}

/**
 * Aktywacja motywu (parenta albo childa): blok od razu.
 *
 * @return void
 */
function cyber_htaccess_on_activate() {
	cyber_htaccess_sync( true );
}
add_action( 'after_switch_theme', 'cyber_htaccess_on_activate' );

/**
 * Zmiana motywu na inny: blok znika, zeby nie zostaly reguly bez wlasciciela.
 *
 * switch_theme odpala sie jeszcze w kontekscie STAREGO motywu, wiec ten
 * kod biegnie. Przelaczenie parent <-> child tez tu trafia; blok wroci
 * na after_switch_theme nowego motywu.
 *
 * @return void
 */
function cyber_htaccess_on_deactivate() {
	if ( cyber_htaccess_server_ok() ) {
		cyber_htaccess_remove();
	}

	delete_option( CYBER_HTACCESS_HASH_OPTION );
}
add_action( 'switch_theme', 'cyber_htaccess_on_deactivate' );

/**
 * Zapis Global Options: blok zgodny z nowymi przelacznikami od razu.
 *
 * @param int|string $post_id ID wpisu albo 'options'.
 * @return void
 */
function cyber_htaccess_on_options_save( $post_id ) {
	if ( 'options' !== $post_id ) {
		return;
	}

	cyber_htaccess_sync( true );
}
add_action( 'acf/save_post', 'cyber_htaccess_on_options_save', 20 );

/**
 * Wejscie do panelu: dopisanie bloku, gdy jego tresc sie zmienila
 * (aktualizacja motywu, przejscie na https, filtr childa).
 *
 * Pomijane w trakcie zapisu formularza ACF — wtedy cyber_get_option()
 * zapamietaloby w cache wartosci SPRZED zapisu, a wlasciwa synchronizacja
 * idzie z acf/save_post.
 *
 * @return void
 */
function cyber_htaccess_on_admin_init() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- sam fakt trwajacego zapisu ACF, dane nie sa czytane.
	if ( ! empty( $_POST['acf'] ) || wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	cyber_htaccess_sync();
}
add_action( 'admin_init', 'cyber_htaccess_on_admin_init' );

/**
 * Serwer bez .htaccess (nginx): naglowki HTTP z PHP, tylko na froncie.
 *
 * Na Apache nic — naglowki idą z pliku, a podwojny Header always set
 * dalby je dwa razy. Blokady plikow na nginx motyw nie zrobi; to
 * konfiguracja serwera (docs/security.md).
 *
 * @return void
 */
function cyber_htaccess_php_headers() {
	if ( cyber_htaccess_server_ok() || ! cyber_get_option( 'htaccess_enable' ) || headers_sent() ) {
		return;
	}

	foreach ( cyber_htaccess_headers() as $name => $value ) {
		if ( '' === $value ) {
			header_remove( $name );
			continue;
		}

		header( $name . ': ' . $value );
	}
}
add_action( 'send_headers', 'cyber_htaccess_php_headers' );

/**
 * Pole "Stan" w zakladce .htaccess: co motyw wie o pliku i co jest w bloku.
 *
 * Gdy zapis jest niemozliwy, pokazuje gotowy blok do recznego wklejenia —
 * administrator nie musi zgadywac, co by zostalo zapisane.
 *
 * @param array $field Pole Message.
 * @return array
 */
function cyber_htaccess_status_field( $field ) {
	$server_ok = cyber_htaccess_server_ok();
	$path      = cyber_htaccess_path();
	$lines     = cyber_htaccess_rules();
	$time      = (int) get_option( CYBER_HTACCESS_TIME_OPTION, 0 );

	$rows = array(
		__( 'Serwer', 'cyber-framework' )  => $server_ok
			? __( 'Apache / LiteSpeed — blok jest zapisywany do pliku', 'cyber-framework' )
			: __( 'inny niz Apache (np. nginx) — plik nie jest ruszany, naglowki HTTP wysyla PHP, blokada plikow wymaga konfiguracji serwera', 'cyber-framework' ),
		__( 'Plik', 'cyber-framework' )    => $path,
		__( 'Zapis', 'cyber-framework' )   => cyber_htaccess_writable() ? __( 'mozliwy', 'cyber-framework' ) : __( 'NIEMOZLIWY — brak uprawnien; wklej blok ponizej recznie', 'cyber-framework' ),
		__( 'Blok w pliku', 'cyber-framework' ) => cyber_htaccess_has_block() ? __( 'jest', 'cyber-framework' ) : __( 'brak', 'cyber-framework' ),
		__( 'HSTS', 'cyber-framework' )    => cyber_htaccess_is_https() ? __( 'witryna na https — wpisywany', 'cyber-framework' ) : __( 'witryna na http — pomijany', 'cyber-framework' ),
		__( 'Ostatni zapis', 'cyber-framework' ) => $time ? wp_date( 'Y-m-d H:i', $time ) : __( 'nigdy', 'cyber-framework' ),
	);

	$html = '<table class="widefat striped" style="max-width:720px"><tbody>';

	foreach ( $rows as $label => $value ) {
		$html .= '<tr><th scope="row" style="width:30%">' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
	}

	$html .= '</tbody></table>';

	if ( $lines ) {
		$html .= '<p><strong>' . esc_html__( 'Aktualna tresc bloku:', 'cyber-framework' ) . '</strong></p>';
		$html .= '<pre style="max-width:720px;overflow:auto;padding:12px;background:#f6f7f7;font-size:12px"># BEGIN ' . esc_html( CYBER_HTACCESS_MARKER ) . "\n"
			. esc_html( implode( "\n", $lines ) ) . "\n# END " . esc_html( CYBER_HTACCESS_MARKER ) . '</pre>';
	}

	$field['message'] = $html;

	return $field;
}
add_filter( 'acf/load_field/key=field_cyber_msg_htaccess_status', 'cyber_htaccess_status_field' );
