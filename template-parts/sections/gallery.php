<?php
/**
 * Sekcja "Galeria".
 *
 * Zdjecia wgrane w sekcji albo zdjecia z galerii (CPT) z paskiem filtrow.
 * Sama siatka to wspolny komponent template-parts/components/gallery.php —
 * ten sam, ktorego uzywa strona pojedynczej galerii.
 *
 * Dane przychodza jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4);
 * zapytania i walidacja siedza w inc/gallery.php.
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

$cyber_data = cyber_gallery_data( $cyber_row );
$cyber_show = array(
	'top'    => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_gal_show', 'top' ),
	'bottom' => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_gal_show', 'bottom' ),
);

if ( ! $cyber_data['items'] && ! $cyber_show['top'] && ! $cyber_show['bottom'] ) {
	// Gosc nie widzi nic, redaktor dostaje powod pustego miejsca.
	if ( current_user_can( 'edit_pages' ) ) {
		echo '<div class="cyber-container"><p class="cyber-section-missing">' . esc_html__( 'Sekcja Galeria: brak zdjec do pokazania (sprawdz zrodlo i wybrane galerie).', 'cyber-framework' ) . '</p></div>';
	}
	return;
}

cyber_section_open( $cyber_attributes );

if ( $cyber_show['top'] ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}

if ( $cyber_data['items'] ) :
	?>
	<div class="cyber-section__body">
		<?php
		get_template_part(
			'template-parts/components/gallery',
			null,
			array(
				'items'      => $cyber_data['items'],
				'filters'    => $cyber_data['filters'],
				'attributes' => cyber_gallery_attributes( $cyber_row ),
				'all_label'  => isset( $cyber_row['cyber_gal_filter_all'] ) ? (string) $cyber_row['cyber_gal_filter_all'] : '',
			)
		);
		?>
	</div>
	<?php
endif;

if ( $cyber_show['bottom'] ) {
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
}

cyber_section_close();
