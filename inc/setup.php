<?php
/**
 * Konfiguracja motywu: theme supports, menu, rozmiary obrazkow.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Deklaruje wsparcie motywu dla funkcji WordPressa.
 *
 * Gutenberg jest wylaczony w calym motywie (patrz inc/editor.php). Tresc edytuje sie
 * klasycznym edytorem, layout buduje ACF (CLAUDE.md sekcja 1).
 *
 * @return void
 */
function cyber_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	/*
	 * Nie definiujemy rozmiarow "na zapas": kazdy add_image_size() to dodatkowe
	 * pliki generowane przy kazdym uploadzie.
	 *
	 * cyber-card-m — karta wpisu na telefonie (lista bloga). Wbudowane rozmiary
	 * przeskakuja z 300 px (medium) od razu na 768 px (medium_large), a karta
	 * na telefonie potrzebuje ok. 600 px (szerokosc ekranu x gestosc pikseli).
	 * Bez posredniego rozmiaru przegladarka bierze 768 px i PageSpeed zglasza
	 * "obrazy o niewlasciwym rozmiarze". Proporcjonalny (bez przycinania), wiec
	 * trafia do srcset obok 'large'. Dotyczy nowych uploadow — istniejace
	 * zdjecia trzeba przegenerowac (wp media regenerate).
	 */
	add_image_size( 'cyber-card-m', 640, 0 );

	/*
	 * Bez tej deklaracji WooCommerce traktuje motyw jako niewspierany i owija
	 * widoki sklepu wlasnymi znacznikami. Uklad listy produktow wchodzi przez
	 * hooki woocommerce_before_main_content / after_main_content
	 * (inc/woocommerce-shop.php), wiec zaden szablon sklepu nie jest nadpisany.
	 */
	add_theme_support( 'woocommerce' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu glowne', 'cyber-framework' ),
			'footer'  => __( 'Menu w stopce', 'cyber-framework' ),
		)
	);
}
add_action( 'after_setup_theme', 'cyber_theme_setup' );

/**
 * Usuwa generator meta tag — mniej informacji o wersji WP na zewnatrz.
 *
 * @return void
 */
function cyber_cleanup_head() {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'cyber_cleanup_head' );
