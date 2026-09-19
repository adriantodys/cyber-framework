<?php
/**
 * Sekcja globalna — podpowiedz, gdy nie ma czego wyswietlic.
 *
 * Sama tresc sekcji globalnej NIE przechodzi przez ten plik: cyber_render_sections()
 * podmienia wiersz na sekcje wybranego wpisu i renderuje je ich wlasnymi
 * szablonami. Tutaj trafia tylko przypadek pusty — szkic, kosz, usuniety wpis,
 * wpis bez sekcji.
 *
 * Trzy poziomy komunikatu (CLAUDE.md sekcja 2): gosc nie widzi nic, redaktor
 * widzi na froncie krotka informacje, dlaczego w tym miejscu jest pusto.
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type string $notice Powod z cyber_global_section_problem().
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_notice = isset( $args['notice'] ) ? (string) $args['notice'] : '';

if ( '' === $cyber_notice || ! current_user_can( 'edit_pages' ) ) {
	return;
}
?>
<div class="cyber-container">
	<p class="cyber-section-missing"><?php echo esc_html( $cyber_notice ); ?></p>
</div>
