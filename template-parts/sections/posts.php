<?php
/**
 * Sekcja "Wpisy" — karty wpisow w siatce albo w sliderze.
 *
 * Siatka: ten sam markup co sekcja Karty. Slider: wspolny komponent karuzeli.
 * Dane przychodza jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4);
 * zapytanie o wpisy robi inc/sections-posts.php.
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array $attributes Klasy, zmienne CSS i kotwica opakowania sekcji.
 *     @type array $row        Wiersz Flexible Content z wartosciami pol.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();
$cyber_row        = isset( $args['row'] ) ? $args['row'] : array();

if ( ! $cyber_attributes ) {
	return;
}

$cyber_items  = cyber_posts_items( $cyber_row );
$cyber_slider = cyber_posts_is_slider( $cyber_row ) && count( $cyber_items ) > 1;
$cyber_show   = array(
	'top'    => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_posts_show', 'top' ),
	'bottom' => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_posts_show', 'bottom' ),
);

// Brak wpisow: gosc nie widzi nic, redaktor dostaje podpowiedz.
if ( ! $cyber_items && ! $cyber_show['top'] && ! $cyber_show['bottom'] ) {
	if ( current_user_can( 'edit_pages' ) ) {
		echo '<div class="cyber-container"><p class="cyber-section-missing">' . esc_html__( 'Sekcja Wpisy: brak opublikowanych elementow spelniajacych ustawienia zrodla.', 'cyber-framework' ) . '</p></div>';
	}
	return;
}

$cyber_full = $cyber_slider && 'full' === cyber_carousel_width( $cyber_row );

// Slider w trybie "full" wychodzi poza kontener — jak sekcja Karuzela.
cyber_section_open( $cyber_attributes, false );

if ( $cyber_show['top'] ) {
	echo '<div class="cyber-section__inner">';
	cyber_section_wysiwyg( $cyber_row, 'top' );
	echo '</div>';
}

if ( $cyber_items ) {
	if ( ! $cyber_full ) {
		echo '<div class="cyber-section__inner">';
	}

	if ( $cyber_slider ) {
		get_template_part(
			'template-parts/components/carousel',
			null,
			array(
				'items' => $cyber_items,
				'row'   => $cyber_row,
			)
		);
	} else {
		$cyber_cards = cyber_cards_attributes( $cyber_row );

		printf(
			'<div class="%1$s" style="%2$s">',
			esc_attr( $cyber_cards['class'] ),
			esc_attr( $cyber_cards['style'] )
		);

		foreach ( $cyber_items as $cyber_item ) {
			get_template_part( 'template-parts/components/card', null, array( 'item' => $cyber_item ) );
		}

		echo '</div>';
	}

	if ( ! $cyber_full ) {
		echo '</div>';
	}
}

if ( $cyber_show['bottom'] ) {
	echo '<div class="cyber-section__inner">';
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
	echo '</div>';
}

cyber_section_close( false );
