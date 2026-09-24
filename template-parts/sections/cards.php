<?php
/**
 * Sekcja "Karty" (icon boxes).
 *
 * Wspolny szkielet sekcji — WYSIWYG gora, kontener, WYSIWYG dol — z kontenerem
 * wypelnionym siatka powtarzalnych elementow.
 *
 * Markup pojedynczej karty jest w template-parts/components/card.php —
 * wspolny z sekcja "Karuzela kart".
 *
 * Widok nie siega po ACF ani po stan globalny: komplet danych przychodzi
 * jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4), a elementy
 * sa juz znormalizowane przez cyber_cards_items().
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

$cyber_cards = cyber_cards_attributes( $cyber_row );
$cyber_items = cyber_cards_items( $cyber_row );

cyber_section_open( $cyber_attributes );
if ( cyber_cards_shows_wysiwyg( $cyber_row, 'top' ) ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}

if ( $cyber_items ) :
	?>
	<div class="<?php echo esc_attr( $cyber_cards['class'] ); ?>" style="<?php echo esc_attr( $cyber_cards['style'] ); ?>">
		<?php
		foreach ( $cyber_items as $cyber_item ) {
			// Wspolny komponent z sekcja "Karuzela kart" — jeden markup karty.
			get_template_part( 'template-parts/components/card', null, array( 'item' => $cyber_item ) );
		}
		?>
	</div>
	<?php
endif;

if ( cyber_cards_shows_wysiwyg( $cyber_row, 'bottom' ) ) {
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
}
cyber_section_close();
