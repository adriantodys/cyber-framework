<?php
/**
 * Sekcja "Karuzela kart".
 *
 * Opakowanie otwiera sie ZAWSZE bez kontenera szerokosci, bo w trybie "full"
 * rzad kart idzie od krawedzi do krawedzi okna. Kontener .cyber-section__inner
 * powstaje tu recznie: wokol tresci nad i pod karuzela zawsze, a wokol samej
 * karuzeli tylko w trybie "section".
 *
 * Sam rzad kart i pasek sterowania to wspolny komponent
 * template-parts/components/carousel.php — ten sam uzywa sekcja "Wpisy"
 * w trybie slidera.
 *
 * Widok nie siega po ACF ani po stan globalny: komplet danych przychodzi
 * jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4).
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

$cyber_items = cyber_cards_items( $cyber_row );
$cyber_show  = array(
	'top'    => cyber_cards_shows_wysiwyg( $cyber_row, 'top' ) && ! empty( $cyber_row['cyber_section_wysiwyg_top'] ),
	'bottom' => cyber_cards_shows_wysiwyg( $cyber_row, 'bottom' ) && ! empty( $cyber_row['cyber_section_wysiwyg_bottom'] ),
);

if ( ! $cyber_items && ! $cyber_show['top'] && ! $cyber_show['bottom'] ) {
	return;
}

$cyber_full = 'full' === cyber_carousel_width( $cyber_row );

cyber_section_open( $cyber_attributes, false );

if ( $cyber_show['top'] ) {
	echo '<div class="cyber-section__inner">';
	cyber_section_wysiwyg( $cyber_row, 'top' );
	echo '</div>';
}

if ( $cyber_items ) :
	if ( ! $cyber_full ) {
		echo '<div class="cyber-section__inner">';
	}

	get_template_part(
		'template-parts/components/carousel',
		null,
		array(
			'items' => $cyber_items,
			'row'   => $cyber_row,
		)
	);

	if ( ! $cyber_full ) {
		echo '</div>';
	}
endif;

if ( $cyber_show['bottom'] ) {
	echo '<div class="cyber-section__inner">';
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
	echo '</div>';
}

cyber_section_close( false );
