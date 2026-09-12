<?php
/**
 * Modul "Breadcrumb" — sciezka okruszkow pod headerem.
 *
 * Plik nie renderuje niczego: buduje dane i rozstrzyga, ktory z dwoch paskow
 * ma sie pokazac. Markup zyje w template-parts/breadcrumb/ (CLAUDE.md sekcja 4).
 *
 * DWA MODULY, NIE JEDEN Z PRZELACZNIKIEM. Zakladki "Breadcrumb" i "Breadcrumb
 * WooCommerce" maja osobne wlaczniki i osobne pola stylu, bo redaktor ma moc
 * wlaczyc okruszki wylacznie w sklepie albo wylacznie poza nim. Wspolny jest
 * tylko markup i jedna mapa zmiennych CSS — reszta jest niezalezna.
 *
 * Na stronach sklepu sciezke buduje WooCommerce (woocommerce_breadcrumb()),
 * bo tylko ono zna hierarchie kategorii produktow. Poza sklepem motyw sklada
 * ja sam, bo WordPress nie ma wbudowanych okruszkow.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Kontekst breadcrumb dla biezacego widoku.
 *
 * Warunek WooCommerce jest CELOWO szerszy niz samo is_woocommerce(): ta funkcja
 * nie obejmuje koszyka, zamowienia ani konta klienta, a to rowniez sa strony
 * sklepu z punktu widzenia odwiedzajacego.
 *
 * Kolejnosc w koniunkcji ma znaczenie — bez cyber_is_woocommerce_active()
 * wywolanie is_woocommerce() przy nieaktywnej wtyczce byloby bledem krytycznym.
 *
 * @return string 'wc' albo 'default'.
 */
function cyber_breadcrumb_context() {
	if ( cyber_is_woocommerce_active()
		&& ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		return 'wc';
	}

	return 'default';
}

/**
 * Czy breadcrumb jest wlaczony w danym kontekscie.
 *
 * @param string $context Kontekst z cyber_breadcrumb_context().
 * @return bool
 */
function cyber_breadcrumb_enabled( $context ) {
	$key = ( 'wc' === $context ) ? 'breadcrumb_wc_enable' : 'breadcrumb_enable';

	return (bool) cyber_get_option( $key );
}

/**
 * Buduje sciezke okruszkow poza sklepem.
 *
 * Ostatni element ma PUSTY adres — to strona biezaca, wiec nie linkuje sama
 * do siebie. Widok rozpoznaje ja po tym, a nie po pozycji w tablicy.
 *
 * Strona glowna zwraca pusta tablice: sciezka zlozona z jednego okruszka
 * "Strona glowna" nie niesie zadnej informacji.
 *
 * Zakres jest swiadomie podstawowy: strony z pelna hierarchia rodzicow, wpisy,
 * archiwa, wyszukiwanie i 404. Sciezka wpisu przez kategorie (Strona glowna >
 * Kategoria > Wpis) to rozszerzenie do zrobienia wtedy, gdy blog faktycznie
 * powstanie — dorzucenie go to jedna galaz w tej funkcji, bez zmian w widoku.
 *
 * @return array Lista tablic 'label' i 'url'.
 */
function cyber_breadcrumb_items() {
	if ( is_front_page() ) {
		return array();
	}

	$items = array(
		array(
			'label' => __( 'Strona glowna', 'cyber-framework' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular() ) {
		$post_id = get_queried_object_id();

		// get_post_ancestors() zwraca od najblizszego rodzica, my chcemy od korzenia.
		foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor_id ) {
			$items[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}

		$items[] = array(
			'label' => get_the_title( $post_id ),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'label' => sprintf(
				/* translators: %s: szukana fraza. */
				__( 'Wyniki wyszukiwania: %s', 'cyber-framework' ),
				get_search_query()
			),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'label' => __( 'Nie znaleziono strony', 'cyber-framework' ),
			'url'   => '',
		);
	} elseif ( is_home() ) {
		/*
		 * Osobna galaz przed is_archive(): przy statycznej stronie glownej lista
		 * wpisow jest zwykla strona, a get_the_archive_title() nie zna jej tytulu.
		 */
		$blog_id = (int) get_option( 'page_for_posts' );

		$items[] = array(
			'label' => $blog_id ? get_the_title( $blog_id ) : __( 'Blog', 'cyber-framework' ),
			'url'   => '',
		);
	} elseif ( is_archive() ) {
		// get_the_archive_title() zwraca znaczniki (<span>Kategoria:</span>), stad strip.
		$items[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);
	}

	/*
	 * Zaden warunek nie dolozyl drugiego elementu — zostala sama "Strona glowna",
	 * ktora linkowalaby donikad poza samym soba. Pasek z jednym okruszkiem nie
	 * niesie informacji, wiec nie renderujemy go wcale. Ta sama zasada co
	 * "pusty pasek sie nie renderuje" w Top Header i Copyright.
	 */
	if ( 1 === count( $items ) ) {
		return array();
	}

	return $items;
}

/**
 * Dane paska breadcrumb albo null, gdy nie ma czego pokazac.
 *
 * Ten sam wzorzec co Top Header i Copyright: decyzja zapada w warstwie logiki,
 * widok tylko wyswietla.
 *
 * @return array|null {
 *     @type string $context Kontekst: 'wc' albo 'default'.
 *     @type array  $items   Sciezka. Dla kontekstu 'wc' pusta — sciezke
 *                           wypisuje samo WooCommerce.
 * }
 */
function cyber_breadcrumb_data() {
	$context = cyber_breadcrumb_context();

	if ( ! cyber_breadcrumb_enabled( $context ) ) {
		return null;
	}

	if ( 'wc' === $context ) {
		return array(
			'context' => 'wc',
			'items'   => array(),
		);
	}

	$items = cyber_breadcrumb_items();

	if ( array() === $items ) {
		return null;
	}

	return array(
		'context' => 'default',
		'items'   => $items,
	);
}
