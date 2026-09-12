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
 *     @type string $context Kontekst, tutaj zawsze 'wc'.
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

$cyber_class = cyber_variant_class( 'cyber-breadcrumb', isset( $args['context'] ) ? $args['context'] : 'wc' );
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<div class="cyber-container">
		<?php
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '',
				'wrap_before' => '<nav class="cyber-breadcrumb__nav" aria-label="' . esc_attr__( 'Okruszki', 'cyber-framework' ) . '">',
				'wrap_after'  => '</nav>',
				'before'      => '<span class="cyber-breadcrumb__item">',
				'after'       => '</span>',
				'home'        => __( 'Strona glowna', 'cyber-framework' ),
			)
		);
		?>
	</div>
</div>
