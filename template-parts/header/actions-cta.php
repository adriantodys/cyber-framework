<?php
/**
 * Header — slot akcji: przycisk CTA.
 *
 * Widok czysty: dane przychodza jawnie przez $args z template-parts/header/
 * header.php (CLAUDE.md sekcja 4). Nie wolaj tego pliku bezposrednio — slot
 * wybiera wariant headera.
 *
 * Przycisk nie ma wlasnych pol wygladu. Uzywa rozmiaru 'medium', czyli
 * dziedziczy KOMPLET stylow z zakladki Przyciski: geometrie, grubosc i cztery
 * kolory. Zmiana wygladu CTA to zmiana rozmiaru Medium, a nie nowe pola tutaj.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type array $cta Tablica 'text' i 'url'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_cta = isset( $args['cta'] ) ? $args['cta'] : null;

if ( null === $cyber_cta ) {
	return;
}
?>
<div class="cyber-header__actions cyber-header__actions--cta">
	<?php
	cyber_button(
		array(
			'text' => $cyber_cta['text'],
			'url'  => $cyber_cta['url'],
			'size' => 'medium',
		)
	);
	?>
</div>
