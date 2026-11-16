<?php
/**
 * Utwardzenie instalacji: mniej informacji o witrynie na zewnatrz.
 *
 * Zadna z tych regul nie jest "bezpieczenstwem" w sensie sekcji 9 CLAUDE.md
 * (tam chodzi o escapowanie, nonce, uprawnienia). To ograniczenie
 * rekonesansu: utrudnienie poznania loginow uzytkownikow i wersji
 * WordPressa, czyli dwoch rzeczy, od ktorych zaczyna sie kazdy
 * zautomatyzowany atak na wp-login.php. Lista i uzasadnienie kazdej
 * reguly: docs/security.md, "Utwardzenie instalacji".
 *
 * Wszystko dziala wylacznie na froncie — panel i REST dla zalogowanych
 * zostaja bez zmian.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ukrywa liste uzytkownikow w REST API przed niezalogowanymi.
 *
 * /wp/v2/users zwraca publicznie slug (= zwykle login) i nazwe kazdego
 * autora. Zalogowani (edytor, panel, ACF) dostaja endpoint bez zmian.
 *
 * @param array $endpoints Zarejestrowane trasy REST.
 * @return array
 */
function cyber_rest_hide_users( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
}
add_filter( 'rest_endpoints', 'cyber_rest_hide_users' );

/**
 * Archiwum autora i zapytanie ?author=N koncza sie 404.
 *
 * /?author=1 jest klasyczna metoda enumeracji: WordPress przekierowuje na
 * /author/{login}/ i zdradza login w adresie. Sam adres /author/{login}/
 * odpowiada 200 albo 404, wiec tez pozwala sprawdzic, czy login istnieje.
 *
 * Motyw nie traci na tym niczego: zaden widok nie linkuje do archiwum
 * autora (nazwa autora wpisu jest tekstem, nie linkiem), wiec archiwum
 * i tak nie bylo osiagalne z witryny. Wpis 'author' w hierarchii szablonow
 * bloga (inc/blog.php) zostaje — jest martwy, ale nieszkodliwy.
 *
 * Priorytet 1: przed redirect_canonical (10) i przed szablonem.
 *
 * @return void
 */
function cyber_block_author_archives() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- sam fakt obecnosci parametru, wartosc nie jest czytana.
	if ( ! is_author() && ! isset( $_GET['author'] ) ) {
		return;
	}

	global $wp_query;

	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}
add_action( 'template_redirect', 'cyber_block_author_archives', 1 );

/**
 * Wylacza przekierowanie /?author=N -> /author/{login}/.
 *
 * Zabezpieczenie wyzej odpowiada 404 na template_redirect z priorytetem 1,
 * ale redirect_canonical moze zadzialac wczesniej w innych sciezkach
 * (np. wtyczka cache). Druga linia obrony.
 *
 * @param string|false $redirect  Adres przekierowania.
 * @param string       $requested Zadany adres.
 * @return string|false
 */
function cyber_no_author_redirect( $redirect, $requested ) {
	if ( preg_match( '/[?&]author=\d+/i', (string) $requested ) ) {
		return false;
	}

	return $redirect;
}
add_filter( 'redirect_canonical', 'cyber_no_author_redirect', 10, 2 );

/**
 * Usuwa dane autora z odpowiedzi oEmbed (/wp-json/oembed/1.0/embed).
 *
 * oEmbed wpisu zwraca author_name i author_url = adres archiwum autora
 * z loginem — ta sama informacja, ktora blokuja reguly wyzej.
 *
 * @param array $data Dane oEmbed.
 * @return array
 */
function cyber_oembed_strip_author( $data ) {
	unset( $data['author_name'], $data['author_url'] );

	return $data;
}
add_filter( 'oembed_response_data', 'cyber_oembed_strip_author' );

/**
 * Usuwa wersje WordPressa z <meta name="generator"> i z kanalow RSS.
 *
 * Filtr the_generator obejmuje RSS/Atom; remove_action zdejmuje meta
 * z <head> (wczesniej w inc/setup.php — przeniesione tutaj, zeby calosc
 * utwardzenia byla w jednym pliku).
 *
 * @return void
 */
function cyber_hide_wp_version() {
	remove_action( 'wp_head', 'wp_generator' );
	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'cyber_hide_wp_version' );

/**
 * Zdejmuje ?ver=<wersja WP> z adresow CSS i JS rdzenia na froncie.
 *
 * Kazdy skrypt rdzenia niesie wersje WordPressa w adresie (?ver=6.8.1),
 * wiec ukrycie generatora bez tego nic nie daje. Dotyczy TYLKO wersji
 * rownej wersji WP — assety motywu (filemtime) i wtyczek (wlasne wersje)
 * zostaja z parametrem, wiec cache przegladarki dalej sie uniewaznia.
 *
 * Koszt: po aktualizacji WordPressa przegladarka odwiedzajacego moze
 * przez jakis czas trzymac stary plik rdzenia, bo adres sie nie zmienil.
 * W panelu nic nie zdejmujemy — tam ten koszt bylby odczuwalny.
 *
 * @param string $src Adres pliku.
 * @return string
 */
function cyber_strip_core_version( $src ) {
	if ( is_admin() || ! $src ) {
		return $src;
	}

	if ( false !== strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
add_filter( 'style_loader_src', 'cyber_strip_core_version', 9999 );
add_filter( 'script_loader_src', 'cyber_strip_core_version', 9999 );
