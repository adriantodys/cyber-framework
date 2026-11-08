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
 *     @type string $context        Kontekst, tutaj zawsze 'default'.
 *     @type bool   $in_page_header Okruszki wewnatrz page headera, pod tytulem —
 *                                  bez wlasnego kontenera (szerokosc trzyma
 *                                  page header) i z klasa stanu. Opcjonalny.
 *     @type array  $items   Lista tablic 'label' i 'url'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_items = isset( $args['items'] ) ? $args['items'] : array();

if ( array() === $cyber_items ) {
	return;
}

$cyber_class  = cyber_variant_class( 'cyber-breadcrumb', isset( $args['context'] ) ? $args['context'] : 'default' );
$cyber_inside = ! empty( $args['in_page_header'] );

// Polozenie w page headerze to stan obok wariantu, nie kolejny wariant (CLAUDE.md sekcja 20).
if ( $cyber_inside ) {
	$cyber_class .= ' cyber-breadcrumb--in-page-header';
}
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<?php if ( ! $cyber_inside ) : ?>
	<div class="cyber-container">
	<?php endif; ?>
		<?php
		/*
		 * Lista uporzadkowana, nie ciag <span>: czytnik ekranu oglasza liczbe
		 * okruszkow i pozycje biezacego (wzorzec WAI-ARIA "Breadcrumb").
		 * Separator rysuje pseudoelement w CSS — nie ma go w markupie.
		 */
		?>
		<nav class="cyber-breadcrumb__nav" aria-label="<?php esc_attr_e( 'Okruszki', 'cyber-framework' ); ?>">
			<ol class="cyber-breadcrumb__list">
				<?php foreach ( $cyber_items as $cyber_item ) : ?>
					<?php if ( '' !== $cyber_item['url'] ) : ?>
						<li class="cyber-breadcrumb__item">
							<a href="<?php echo esc_url( $cyber_item['url'] ); ?>"><?php echo esc_html( $cyber_item['label'] ); ?></a>
						</li>
					<?php else : ?>
						<li class="cyber-breadcrumb__item" aria-current="page"><?php echo esc_html( $cyber_item['label'] ); ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php if ( ! $cyber_inside ) : ?>
	</div>
	<?php endif; ?>
</div>
