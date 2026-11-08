<?php
/**
 * Breadcrumb — sciezka okruszkow na stronach sklepu.
 *
 * Sciezke buduje samo WooCommerce, bo tylko ono zna hierarchie kategorii
 * produktow, strony sklepu i atrybutow. Motyw podstawia wylacznie WLASNE
 * znaczniki przez argumenty wrap_before / before / after, zeby oba paski —
 * sklepowy i zwykly — mialy identyczny markup i jeden wspolny blok CSS.
 *
 * delimiter jest pusty CELOWO: separator rysuje pseudoelement w CSS, tak samo
 * jak w pasku Copyright. Znak wpisany w markup przez WooCommerce bylby czytany
 * przez czytniki ekranu jako tresc.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type string $context        Kontekst, tutaj zawsze 'wc'.
 *     @type bool   $in_page_header Okruszki wewnatrz page headera, pod tytulem —
 *                                  bez wlasnego kontenera (szerokosc trzyma
 *                                  page header) i z klasa stanu. Opcjonalny.
 * }
 */

defined( 'ABSPATH' ) || exit;

/*
 * Podwojny warunek: wariant jest wybrany w panelu, ale wtyczke mozna wylaczyc
 * w kazdej chwili. Brak funkcji oznacza po prostu brak paska — nigdy bledu.
 */
if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
	return;
}

$cyber_class  = cyber_variant_class( 'cyber-breadcrumb', isset( $args['context'] ) ? $args['context'] : 'wc' );
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
		 * Ten sam markup co w breadcrumb.php: <nav> > <ol> > <li>. WooCommerce
		 * daje kazdemu okruszkowi identyczny znacznik "before", wiec
		 * aria-current="page" dla ostatniego trzeba dopisac po fakcie —
		 * bufor wyjscia i podmiana OSTATNIEGO wystapienia otwierajacego <li>.
		 * Markup jest juz wyescapowany przez WooCommerce (esc_url / esc_html
		 * w templates/global/breadcrumb.php), a podmieniany fragment jest staly.
		 */
		$cyber_item_open = '<li class="cyber-breadcrumb__item">';

		ob_start();
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '',
				'wrap_before' => '<nav class="cyber-breadcrumb__nav" aria-label="' . esc_attr__( 'Okruszki', 'cyber-framework' ) . '"><ol class="cyber-breadcrumb__list">',
				'wrap_after'  => '</ol></nav>',
				'before'      => $cyber_item_open,
				'after'       => '</li>',
				'home'        => __( 'Strona glowna', 'cyber-framework' ),
			)
		);
		$cyber_html = (string) ob_get_clean();
		$cyber_last = strrpos( $cyber_html, $cyber_item_open );

		if ( false !== $cyber_last ) {
			$cyber_html = substr_replace(
				$cyber_html,
				'<li class="cyber-breadcrumb__item" aria-current="page">',
				$cyber_last,
				strlen( $cyber_item_open )
			);
		}

		echo $cyber_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup WooCommerce, wyescapowany u zrodla.
		?>
	<?php if ( ! $cyber_inside ) : ?>
	</div>
	<?php endif; ?>
</div>
