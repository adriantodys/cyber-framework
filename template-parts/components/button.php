<?php
/**
 * Komponent: przycisk.
 *
 * Widok czysty — dane przychodza jawnie przez $args z cyber_button()
 * (CLAUDE.md sekcja 4). Nie wolaj tego pliku bezposrednio przez
 * get_template_part(); uzyj cyber_button(), ktore waliduje argumenty.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type string $text   Tekst przycisku.
 *     @type string $url    Adres docelowy.
 *     @type string $size   Rozmiar: large, medium albo small.
 *     @type string $target Atrybut target albo pusty string.
 *     @type string $rel    Atrybut rel albo pusty string.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<a class="btn btn-<?php echo esc_attr( $args['size'] ); ?>" href="<?php echo esc_url( $args['url'] ); ?>"
<?php
if ( '' !== $args['target'] ) {
	printf( ' target="%s"', esc_attr( $args['target'] ) );
}

if ( '' !== $args['rel'] ) {
	printf( ' rel="%s"', esc_attr( $args['rel'] ) );
}
?>
><?php echo esc_html( $args['text'] ); ?></a>
