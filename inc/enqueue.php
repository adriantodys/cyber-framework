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
 *   wspolny harmonogram breakpointow z cyber_breakpoints().
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
	 * Mapowanie kanonicznych nazw breakpointow na klucze opcji tego modulu.
	 * UWAGA: pola marginesow powstaly przed konwencja z CLAUDE.md sekcja 18
	 * i uzywaja koncowek _mobile_l / _mobile_s zamiast _mobile / _mobile_small.
	 * Rozbieznosc jest swiadomie NIE naprawiana tutaj — zmiana nazwy pola ACF
	 * oznacza utrate zapisanych wartosci w wp_options i wymaga migracji.
	 */
	$margin_keys = array(
		'tablet'       => 'page_margin_tablet',
		'mobile'       => 'page_margin_mobile_l',
		'mobile_small' => 'page_margin_mobile_s',
	);

	foreach ( cyber_breakpoints() as $breakpoint => $max_width ) {
		$css .= sprintf(
			'@media (max-width:%1$dpx){:root{--cyber-container-margin:%2$dpx;}}',
			$max_width,
			(int) cyber_get_option( $margin_keys[ $breakpoint ] )
		);
	}

	return $css;
}

/**
 * Mapa pol z wielkosciami czcionek na koncowki zmiennych CSS.
 *
 * Klucz  = klucz opcji (bez prefiksu cyber_, patrz cyber_option_schema()).
 * Wartosc = koncowka zmiennej, czyli --cyber-font-size-[wartosc].
 *
 * Podkreslenia zamieniane sa na myslniki, bo taka jest konwencja nazw
 * w CSS Custom Properties uzywana w tym motywie.
 *
 * @return array<string, string> Klucz opcji => koncowka zmiennej CSS.
 */
function cyber_font_size_map() {
	return array(
		'font_size_h1'          => 'h1',
		'font_size_h2'          => 'h2',
		'font_size_h3'          => 'h3',
		'font_size_h4'          => 'h4',
		'font_size_h5'          => 'h5',
		'font_size_h6'          => 'h6',
		'font_size_overtitle_1' => 'overtitle-1',
		'font_size_overtitle_2' => 'overtitle-2',
		'font_size_text'        => 'text',
		'font_size_links'       => 'links',
	);
}

/**
 * Mapa pol z grubosciami czcionek na koncowki zmiennych CSS.
 *
 * Analogiczna do cyber_font_size_map(), ale te wartosci NIE sa skalowane
 * przez breakpointy — grubosc jest jedna dla wszystkich urzadzen
 * (CLAUDE.md sekcja 19, wyjatek od wariantu B).
 *
 * @return array<string, string> Klucz opcji => koncowka zmiennej CSS.
 */
function cyber_font_weight_map() {
	return array(
		'font_weight_headings'  => 'headings',
		'font_weight_overtitle' => 'overtitle',
		'font_weight_text'      => 'text',
		'font_weight_links'     => 'links',
	);
}

/**
 * Przelicza wielkosc desktopowa na wartosc dla breakpointu.
 *
 * Skalowanie liczone jest w PHP i wypisywane jako gotowa liczba px — swiadomie
 * bez calc() w CSS (CLAUDE.md sekcja 19, decyzja modulu).
 *
 * @param int $base_px Wartosc desktopowa w px.
 * @param int $scale   Skala w procentach.
 * @return int Wartosc w px, nigdy mniejsza niz 1 — font-size:0px ukrylby tekst.
 */
function cyber_scale_font_size( $base_px, $scale ) {
	return max( 1, (int) round( $base_px * $scale / 100 ) );
}

/**
 * Buduje CSS ze zmiennymi typografii na podstawie Global Options.
 *
 * Wariant B z CLAUDE.md sekcja 19: konkretne wartosci istnieja tylko dla
 * desktopu, a kazdy breakpoint dostaje jedna skale procentowa, ktora przelicza
 * wszystkie wielkosci naraz.
 *
 * Skalowaniu podlegaja WYLACZNIE wielkosci (font-size). Rodziny (font-family)
 * i grubosci (font-weight) sa stale dla wszystkich urzadzen, wiec wystepuja
 * tylko w bloku bazowym :root i nie powtarzaja sie w zadnym @media.
 *
 * Wartosci font-family i font-weight pochodza z pol typu 'choice' —
 * cyber_get_option() przepuszcza wylacznie stringi z listy dozwolonej
 * w cyber_option_schema(), wiec do CSS nie trafi nic spoza tej listy.
 *
 * @return string CSS bez znacznika <style>.
 */
function cyber_font_css() {
	$sizes = array();

	foreach ( cyber_font_size_map() as $option_key => $css_name ) {
		$sizes[ $css_name ] = (int) cyber_get_option( $option_key );
	}

	$css = sprintf(
		':root{--cyber-font-family-headings:%1$s;--cyber-font-family-text:%2$s;',
		cyber_get_option( 'font_family_headings' ),
		cyber_get_option( 'font_family_text' )
	);

	foreach ( cyber_font_weight_map() as $option_key => $css_name ) {
		$css .= sprintf(
			'--cyber-font-weight-%1$s:%2$s;',
			$css_name,
			cyber_get_option( $option_key )
		);
	}

	foreach ( $sizes as $css_name => $base_px ) {
		$css .= sprintf( '--cyber-font-size-%1$s:%2$dpx;', $css_name, $base_px );
	}

	$css .= '}';

	foreach ( cyber_breakpoints() as $breakpoint => $max_width ) {
		$scale = (int) cyber_get_option( 'font_scale_' . $breakpoint );

		$css .= sprintf( '@media (max-width:%dpx){:root{', $max_width );

		foreach ( $sizes as $css_name => $base_px ) {
			$css .= sprintf(
				'--cyber-font-size-%1$s:%2$dpx;',
				$css_name,
				cyber_scale_font_size( $base_px, $scale )
			);
		}

		$css .= '}}';
	}

	return $css;
}

/**
 * Mapa pol modulu "Header Desktop" na zmienne CSS.
 *
 * Klucz    = klucz opcji (bez prefiksu cyber_).
 * [0]      = pelna nazwa zmiennej CSS.
 * [1]      = jednostka doklejana do wartosci ('px' albo pusty string).
 *
 * Zadna z tych wartosci nie jest skalowana przez breakpointy — modul opisuje
 * wylacznie widok desktopowy (CLAUDE.md sekcja 19, klasyfikacja jawna).
 *
 * @return array<string, array{0: string, 1: string}> Mapa pol na zmienne.
 */
function cyber_header_css_map() {
	return array(
		'header_padding_top'          => array( '--cyber-header-padding-top', 'px' ),
		'header_padding_bottom'       => array( '--cyber-header-padding-bottom', 'px' ),
		'header_menu_item_gap'        => array( '--cyber-header-menu-gap', 'px' ),
		'header_menu_link_padding'    => array( '--cyber-header-menu-link-padding', 'px' ),
		'header_menu_font_size'       => array( '--cyber-header-menu-font-size', 'px' ),
		'header_menu_font_weight'     => array( '--cyber-header-menu-font-weight', '' ),
		'header_menu_color'           => array( '--cyber-header-menu-color', '' ),
		'header_menu_color_hover'     => array( '--cyber-header-menu-color-hover', '' ),
		'header_menu_color_active'    => array( '--cyber-header-menu-color-active', '' ),
		'header_submenu_item_gap'     => array( '--cyber-header-submenu-gap', 'px' ),
		'header_submenu_link_padding' => array( '--cyber-header-submenu-link-padding', 'px' ),
		'header_submenu_font_size'    => array( '--cyber-header-submenu-font-size', 'px' ),
		'header_submenu_font_weight'  => array( '--cyber-header-submenu-font-weight', '' ),
		'header_submenu_color'        => array( '--cyber-header-submenu-color', '' ),
		'header_submenu_color_hover'  => array( '--cyber-header-submenu-color-hover', '' ),
		'header_submenu_color_active' => array( '--cyber-header-submenu-color-active', '' ),
	);
}

/**
 * Buduje CSS ze zmiennymi headera na podstawie Global Options.
 *
 * Wyrownanie (menu i submenu) NIE trafia tutaj — jest modyfikatorem klasy
 * w markupie (.cyber-menu--center itd.), bo zmienia uklad, a nie wartosc.
 *
 * Kolory pochodza z pol typu 'color', wiec cyber_get_option() przepuszcza
 * wylacznie poprawny HEX; liczby sa rzutowane na int. Do CSS nie trafia
 * zaden ciag spoza tych dwoch ksztaltow.
 *
 * @return string CSS bez znacznika <style>.
 */
function cyber_header_css() {
	$css = ':root{';

	foreach ( cyber_header_css_map() as $option_key => $definition ) {
		list( $css_var, $unit ) = $definition;

		$value = cyber_get_option( $option_key );

		if ( 'px' === $unit ) {
			$value = sprintf( '%dpx', (int) $value );
		}

		$css .= sprintf( '%1$s:%2$s;', $css_var, $value );
	}

	return $css . '}';
}

/**
 * Wypisuje zmienne Global Options jako inline <style> w <head>.
 *
 * Jeden blok <style> dla calego motywu — kolejne moduly dopisuja tu swoja
 * funkcje budujaca CSS, zamiast rejestrowac wlasny hook (CLAUDE.md sekcja 6,
 * "Konwencja: ACF Options -> CSS").
 *
 * Priorytet 20 jest istotny: wp_head wypisuje arkusze stylow wczesniej
 * (wp_enqueue_scripts na priorytecie 1, wp_print_styles na 8). Dzieki temu te
 * reguly wygrywaja z domyslnymi wartosciami z assets/css/main.css przy tej samej
 * specyficznosci selektora :root.
 *
 * @return void
 */
function cyber_print_inline_css() {
	$css = cyber_container_css() . cyber_font_css() . cyber_header_css();

	printf(
		'<style id="cyber-global-vars">%s</style>' . "\n",
		wp_strip_all_tags( $css )
	);
}
add_action( 'wp_head', 'cyber_print_inline_css', 20 );
