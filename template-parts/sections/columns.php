<?php
/**
 * Sekcja "Kolumny tekstowe" (WYSIWYG).
 *
 * W odroznieniu od pozostalych sekcji NIE ma pol WYSIWYG nad i pod trescia:
 * kolumny same sa polami WYSIWYG, wiec naglowek sekcji wpisuje sie w pierwszej
 * kolumnie albo w osobnej sekcji podstawowej nad ta. Opakowanie i ustawienia
 * sekcji zostaja wspolne ze wszystkimi layoutami.
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

$cyber_columns = cyber_columns_attributes( $cyber_row );
$cyber_items   = cyber_columns_items( $cyber_row );

cyber_section_open( $cyber_attributes );
?>

	<div class="<?php echo esc_attr( $cyber_columns['class'] ); ?>" style="<?php echo esc_attr( $cyber_columns['style'] ); ?>">
		<?php foreach ( $cyber_items as $cyber_content ) : ?>
			<div class="cyber-column cyber-wysiwyg">
				<?php echo cyber_kses_content( $cyber_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
			</div>
		<?php endforeach; ?>
	</div>

<?php
cyber_section_close();
