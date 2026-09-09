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
 * Gutenberg wlaczony jest wylacznie dla tresci edytorskich (wpisy, tresc strony),
 * nigdy jako narzedzie do budowania layoutu — layout buduje ACF (CLAUDE.md sekcja 1).
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
	 * Rozmiary obrazkow nie sa jeszcze zdefiniowane — zostana dodane razem
	 * z pierwszymi komponentami, ktore ich faktycznie potrzebuja (etap 4).
	 * Nie definiujemy rozmiarow "na zapas": kazdy add_image_size() to dodatkowe
	 * pliki generowane przy kazdym uploadzie.
	 */

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
