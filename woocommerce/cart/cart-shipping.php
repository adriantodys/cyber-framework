<?php
/**
 * Shipping Methods Display
 *
 * In 2.1 we show methods per package. This allows for multiple methods per order if so desired.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.8.0
 */

/*
 * ---------------------------------------------------------------------------
 * NADPISANIE MOTYWU Cyber Framework — drugie w projekcie.
 *
 * Skopiowane z WooCommerce 11.1.0, plik templates/cart/cart-shipping.php
 * w wersji @version 8.8.0. Naglowek powyzej zostaje NIETKNIETY, inaczej
 * WooCommerce -> Status przestanie ostrzegac o nieaktualnej kopii.
 *
 * POWOD: tabela podsumowania zamowienia ma TRZY kolumny, a ten wiersz ma dwie
 * komorki i zadnego colspan. Lista metod dostawy konczyla sie przez to
 * w polowie szerokosci tabeli zamiast isc przez cala.
 *
 * Nie da sie tego naprawic CSS-em. Colspan jest atrybutem i nie ma dla niego
 * odpowiednika wsrod wlasciwosci CSS, a ustawienie display:block na komorce
 * niczego nie zmienia: przegladarka owija taki element w ANONIMOWA komorke
 * tabeli, wiec nadal siedzi w jednej kolumnie.
 *
 * ZAKRES ZMIAN wobec oryginalu: wiersz rozbity na DWA, kazdy z jedna komorka
 * o colspan="3". Etykieta i lista metod zajmuja dzieki temu pelna szerokosc,
 * jedna pod druga — tak jak w projekcie.
 *
 * Dwie komorki po colspan="3" w JEDNYM wierszu nie zadzialalyby: th zajalby
 * kolumny 1-3, a td zaczalby sie od kolumny 4, wiec tabela urosloby do szesciu
 * kolumn. Stad podzial na dwa wiersze, a nie samo dodanie atrybutu.
 *
 * Reszta pliku, wraz ze wszystkimi hookami, filtrami i galeziami warunkowymi,
 * jest bez zmian.
 *
 * UWAGA: liczba 3 jest zwiazana z liczba kolumn tabeli podsumowania, ktora
 * ustala woocommerce/checkout/review-order.php. Zmiana liczby kolumn tam
 * wymaga poprawienia tej wartosci tutaj.
 *
 * PRZY AKTUALIZACJI WooCommerce sprawdz, czy rdzen podbil @version tego pliku.
 * Patrz docs/architecture.md, sekcja "Strona zamowienia (checkout)".
 * ---------------------------------------------------------------------------
 */

defined( 'ABSPATH' ) || exit;

$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';
?>
<tr class="woocommerce-shipping-totals shipping shipping__label">
	<th colspan="3"><?php echo wp_kses_post( $package_name ); ?></th>
</tr>
<tr class="woocommerce-shipping-totals shipping shipping__methods">
	<td colspan="3" data-title="<?php echo esc_attr( $package_name ); ?>">
		<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
			<ul id="shipping_method" class="woocommerce-shipping-methods">
				<?php foreach ( $available_methods as $method ) : ?>
					<li>
						<?php
						if ( 1 < count( $available_methods ) ) {
							printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // WPCS: XSS ok.
						} else {
							printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // WPCS: XSS ok.
						}
						printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // WPCS: XSS ok.
						do_action( 'woocommerce_after_shipping_rate', $method, $index );
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( is_cart() ) : ?>
				<p class="woocommerce-shipping-destination">
					<?php
					if ( $formatted_destination ) {
						// Translators: $s shipping destination.
						printf( esc_html__( 'Shipping to %s.', 'woocommerce' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' );
						$calculator_text = esc_html__( 'Change address', 'woocommerce' );
					} else {
						echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', __( 'Shipping options will be updated during checkout.', 'woocommerce' ) ) );
					}
					?>
				</p>
			<?php endif; ?>
			<?php
		elseif ( ! $has_calculated_shipping || ! $formatted_destination ) :
			if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
				echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'woocommerce' ) ) );
			} else {
				echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'woocommerce' ) ) );
			}
		elseif ( ! is_cart() ) :
			echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ) ) );
		else :
			echo wp_kses_post(
				/**
				 * Provides a means of overriding the default 'no shipping available' HTML string.
				 *
				 * @since 3.0.0
				 *
				 * @param string $html                  HTML message.
				 * @param string $formatted_destination The formatted shipping destination.
				 */
				apply_filters(
					'woocommerce_cart_no_shipping_available_html',
					// Translators: $s shipping destination.
					sprintf( esc_html__( 'No shipping options were found for %s.', 'woocommerce' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ),
					$formatted_destination
				)
			);
			$calculator_text = esc_html__( 'Enter a different address', 'woocommerce' );
		endif;
		?>

		<?php if ( $show_package_details ) : ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
		<?php endif; ?>

		<?php if ( $show_shipping_calculator ) : ?>
			<?php woocommerce_shipping_calculator( $calculator_text ); ?>
		<?php endif; ?>
	</td>
</tr>
