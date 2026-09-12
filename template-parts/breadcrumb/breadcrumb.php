<?php
/**
 * Breadcrumb — sciezka okruszkow poza sklepem.
 *
 * Widok czysty: dane przychodza jawnie przez $args z header.php w rootcie
 * (CLAUDE.md sekcja 4). Decyzja "czy pokazac" zapadla juz w cyber_breadcrumb_data().
 *
 * Ostatni element sciezki NIE jest linkiem — rozpoznajemy go po pustym adresie,
 * a nie po pozycji w tablicy. Dostaje aria-current="page", zeby czytnik ekranu
 * oglosil, ze to strona biezaca.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type string $context Kontekst, tutaj zawsze 'default'.
 *     @type array  $items   Lista tablic 'label' i 'url'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_items = isset( $args['items'] ) ? $args['items'] : array();

if ( array() === $cyber_items ) {
	return;
}

$cyber_class = cyber_variant_class( 'cyber-breadcrumb', isset( $args['context'] ) ? $args['context'] : 'default' );
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<div class="cyber-container">
		<nav class="cyber-breadcrumb__nav" aria-label="<?php esc_attr_e( 'Okruszki', 'cyber-framework' ); ?>">
			<?php foreach ( $cyber_items as $cyber_item ) : ?>
				<?php if ( '' !== $cyber_item['url'] ) : ?>
					<span class="cyber-breadcrumb__item">
						<a href="<?php echo esc_url( $cyber_item['url'] ); ?>"><?php echo esc_html( $cyber_item['label'] ); ?></a>
					</span>
				<?php else : ?>
					<span class="cyber-breadcrumb__item" aria-current="page"><?php echo esc_html( $cyber_item['label'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
	</div>
</div>
