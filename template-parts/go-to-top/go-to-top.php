<?php
/**
 * Przycisk "do gory" — kwadrat ze strzalka.
 *
 * Widok czysty: komplet danych przychodzi jawnie w $args z cyber_go_to_top()
 * (CLAUDE.md sekcja 4). O tym, czy sie pokazac, zdecydowano wczesniej.
 *
 * <button>, nie <a href="#">: to akcja na stronie, nie nawigacja. Bez
 * JavaScriptu przycisk pozostaje ukryty (pokazuje go dopiero skrypt po
 * przewinieciu), wiec nie zostaje na ekranie martwy element.
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type string $class      Klasy przycisku (wariant, polozenie, stan).
 *     @type int    $show_after Liczba pikseli przewiniecia, po ktorej przycisk sie pokazuje.
 *     @type string $label      Dostepna nazwa przycisku.
 *     @type string $icon       Znacznik <svg> z cyber_get_icon().
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['class'] ) ) {
	return;
}
?>
<button type="button" class="<?php echo esc_attr( $args['class'] ); ?>" data-cyber-totop="<?php echo esc_attr( isset( $args['show_after'] ) ? (int) $args['show_after'] : 0 ); ?>" aria-label="<?php echo esc_attr( isset( $args['label'] ) ? $args['label'] : '' ); ?>">
	<?php echo isset( $args['icon'] ) ? $args['icon'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- staly SVG z cyber_get_icon(), bez danych uzytkownika. ?>
</button>
