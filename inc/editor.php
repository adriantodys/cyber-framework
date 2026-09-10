<?php
/**
 * Konfiguracja edytora tresci — wylaczenie Gutenberga.
 *
 * Decyzja projektowa: motyw uzywa wylacznie klasycznego edytora (TinyMCE) dla
 * wszystkich typow tresci — wpisow, stron i kazdego CPT, takze tych rejestrowanych
 * przez wtyczki. Layout buduje ACF (CLAUDE.md sekcja 1), a edytor tresci ma byc
 * jednym, przewidywalnym polem tekstowym, nie drugim systemem layoutu.
 *
 * Nie wymaga wtyczki Classic Editor — klasyczny edytor jest nadal czescia rdzenia
 * WordPressa i wystarczy odmowic uzycia edytora blokowego.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wylacza edytor blokowy dla wszystkich typow tresci.
 *
 * Filtr dostaje typ tresci, ale swiadomie go ignorujemy — regula jest globalna
 * i bezwarunkowa. Gdyby w przyszlosci pojawil sie wyjatek (np. jeden CPT na blogu
 * ma zostac na blokach), to jest jedyne miejsce, ktore trzeba zmienic.
 *
 * @param bool   $use_block_editor Czy WordPress zamierza uzyc edytora blokowego.
 * @param string $post_type        Typ tresci, dla ktorego zapada decyzja.
 * @return bool Zawsze false — klasyczny edytor.
 */
function cyber_disable_block_editor( $use_block_editor, $post_type ) {
	unset( $use_block_editor, $post_type );

	return false;
}
add_filter( 'use_block_editor_for_post_type', 'cyber_disable_block_editor', 10, 2 );

/*
 * Ten sam filtr pod nazwa uzywana przez wtyczke Gutenberg (wersja rozwojowa,
 * instalowana osobno). Bez tego wtyczka przejela by edytor mimo powyzszego filtra.
 */
add_filter( 'gutenberg_can_edit_post_type', 'cyber_disable_block_editor', 10, 2 );

/**
 * Usuwa arkusze stylow edytora blokowego z front-endu.
 *
 * Skoro zaden wpis ani strona nie zawiera blokow, wp-block-library i style globalne
 * sa martwym kodem doladowywanym na kazdej podstronie (CLAUDE.md sekcja 10).
 *
 * UWAGA: jesli kiedykolwiek wlaczony zostanie blokowy edytor widgetow albo wtyczka
 * zacznie renderowac bloki na froncie, te style trzeba przywrocic — inaczej
 * ich HTML straci formatowanie.
 *
 * @return void
 */
function cyber_dequeue_block_assets() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wp-components' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'cyber_dequeue_block_assets', 100 );
