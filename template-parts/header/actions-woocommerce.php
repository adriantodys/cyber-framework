<?php
/**
 * Header — slot akcji: konto i koszyk WooCommerce.
 *
 * Widok czysty: dane przychodza jawnie przez $args (CLAUDE.md sekcja 4).
 * Wartosc null oznacza "WooCommerce nieaktywne" — widok nie sprawdza tego sam,
 * bo decyzja nalezy do inc/woocommerce.php.
 *
 * Zachowanie przy braku wtyczki jest SWIADOME i trojstopniowe:
 * - gosc witryny nie widzi nic, header renderuje sie normalnie bez ikon,
 * - zalogowany administrator widzi krotka podpowiedz, zeby wiedziec, dlaczego
 *   w tym miejscu jest pusto,
 * - w panelu czeka pelne ostrzezenie (cyber_woocommerce_missing_notice()).
 *
 * Nigdy nie wywracamy strony bledem krytycznym z powodu braku wtyczki.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type array|null $wc Tablica 'account_url', 'cart_url', 'count' albo null.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_wc = isset( $args['wc'] ) ? $args['wc'] : null;

if ( null === $cyber_wc ) {
	$cyber_hint = cyber_woocommerce_missing_hint(
		__( 'ikony konta i koszyka w headerze', 'cyber-framework' )
	);

	// Pusty tekst = uzytkownik nie jest administratorem. Nie renderujemy nic.
	if ( '' === $cyber_hint ) {
		return;
	}
	?>
	<div class="cyber-header__actions cyber-header__actions--wc">
		<span class="cyber-wc-missing"><?php echo esc_html( $cyber_hint ); ?></span>
	</div>
	<?php
	return;
}
?>
<div class="cyber-header__actions cyber-header__actions--wc">

	<a class="cyber-wc-link" href="<?php echo esc_url( $cyber_wc['account_url'] ); ?>">
		<?php
		// Ikona dekoracyjna — nazwe niesie tekst dla czytnikow ekranu obok niej.
		echo cyber_get_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<span class="screen-reader-text"><?php esc_html_e( 'Moje konto', 'cyber-framework' ); ?></span>
	</a>

	<a class="cyber-wc-link cyber-wc-link--cart" href="<?php echo esc_url( $cyber_wc['cart_url'] ); ?>">
		<?php
		echo cyber_get_icon( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<span class="screen-reader-text"><?php esc_html_e( 'Koszyk', 'cyber-framework' ); ?></span>
		<?php
		/*
		 * Licznik w osobnym pliku, bo ten sam markup oddajemy WooCommerce jako
		 * fragment AJAX (cyber_wc_cart_count_fragment()). Dwie kopie rozjechalyby
		 * sie przy pierwszej zmianie.
		 */
		get_template_part(
			'template-parts/header/cart-count',
			null,
			array( 'count' => $cyber_wc['count'] )
		);
		?>
	</a>

</div>
