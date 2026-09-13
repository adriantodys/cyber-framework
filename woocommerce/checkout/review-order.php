<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

/*
 * ---------------------------------------------------------------------------
 * NADPISANIE MOTYWU Cyber Framework — JEDYNE w calym projekcie.
 *
 * Skopiowane z WooCommerce 11.1.0, plik templates/checkout/review-order.php
 * w wersji @version 11.0.0. Naglowek powyzej zostaje NIETKNIETY, inaczej
 * WooCommerce -> Status przestanie ostrzegac o nieaktualnej kopii.
 *
 * POWOD: projekt wymaga tabeli o TRZECH kolumnach (Produkt / Ilosc / Kwota).
 * Szablon rdzenia ma dwie, a ilosc wypisuje jako <strong> WEWNATRZ komorki
 * produktu. Naglowka trzeciej kolumny nie da sie dolozyc zadnym hookiem —
 * w <thead> nie ma punktu zaczepienia. To jedyny element strony zamowienia,
 * ktorego nie dalo sie uzyskac hookiem ani CSS-em (CLAUDE.md sekcja 2).
 *
 * ZAKRES ZMIAN wobec oryginalu, swiadomie minimalny:
 * 1. thead: dodana komorka th.product-quantity z etykieta ilosci.
 * 2. tbody: ilosc przeniesiona z komorki produktu do wlasnej komorki.
 *    Filtr woocommerce_checkout_cart_item_quantity zostaje zachowany, zeby
 *    wtyczki trzecie nadal mogly go uzywac.
 * 3. tfoot: colspan="2" na komorkach etykiet, zeby wiersze podsumowania
 *    zgadzaly sie z nowa liczba kolumn takze bez naszego CSS.
 *
 * Reszta pliku, wraz ze WSZYSTKIMI hookami i filtrami, jest bez zmian.
 *
 * PRZY AKTUALIZACJI WooCommerce sprawdz, czy rdzen podbil @version tego pliku.
 * Jesli tak, porownaj zmiany i przenies je tutaj. Patrz docs/architecture.md,
 * sekcja "Strona zamowienia (checkout)".
 * ---------------------------------------------------------------------------
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			/**
			 * Filter whether this cart item is visible in the checkout review order table.
			 *
			 * @since 2.1.0
			 * @param bool   $visible       Whether the cart item is visible. Default true.
			 * @param array  $cart_item     The cart item data.
			 * @param string $cart_item_key The cart item key.
			 */
			$visible = apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key );

			if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<td class="product-name">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
					<td class="product-quantity">
						<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', '<strong class="product-quantity">' . esc_html( $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
					<td class="product-total">
						<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th colspan="2"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th colspan="2"><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th colspan="2"><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th colspan="2"><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th colspan="2"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th colspan="2"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>
