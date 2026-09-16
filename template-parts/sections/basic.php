<?php
/**
 * Sekcja podstawowa — szkielet wszystkich pozostalych.
 *
 * Trzy czesci w stalej kolejnosci:
 *
 *   WYSIWYG gora  ->  kontener na elementy  ->  WYSIWYG dol
 *
 * Srodkowy kontener jest na tym etapie CELOWO pusty. Elementy (karty, kafle,
 * galeria, formularz) dokladane sa pozniej jako pola wewnatrz tego layoutu —
 * a wtedy trafiaja dokladnie tutaj, bez ruszania opakowania i bez ruszania
 * ustawien wygladu. To jest cala rola tego pliku: byc wzorcem, z ktorego
 * powstaja kolejne sekcje.
 *
 * Widok nie siega po ACF ani po stan globalny — komplet danych przychodzi
 * jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4).
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array $attributes Klasy, zmienne CSS i kotwica opakowania.
 *     @type array $row        Wiersz Flexible Content z wartosciami pol.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();
$cyber_row        = isset( $args['row'] ) ? $args['row'] : array();

if ( ! $cyber_attributes ) {
	return;
}

cyber_section_open( $cyber_attributes );
cyber_section_wysiwyg( $cyber_row, 'top' );
?>

	<div class="cyber-section__body">
		<?php
		/**
		 * Miejsce na elementy sekcji.
		 *
		 * Hook istnieje, zeby dolozenie zawartosci nie wymagalo przepisywania
		 * tego pliku — a przy okazji pozwala wtyczce albo motywowi potomnemu
		 * wejsc w srodek sekcji bez nadpisywania szablonu.
		 *
		 * @param array $row Wiersz Flexible Content.
		 */
		do_action( 'cyber_section_basic_body', $cyber_row );
		?>
	</div>

<?php
cyber_section_wysiwyg( $cyber_row, 'bottom' );
cyber_section_close();
