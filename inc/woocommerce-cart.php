<?php
/**
 * WooCommerce — strona koszyka.
 *
 * Koszyk korzysta z KLASYCZNEGO shortcode'u [woocommerce_cart], nie z bloku.
 * Decyzja projektowa: motyw ma wylaczony edytor blokowy (CLAUDE.md sekcja 1),
 * wiec bloku i tak nie dalo by sie skonfigurowac, a klasyczny markup jest
 * stabilniejszy do stylowania — klasy .shop_table, .cart_totals czy .quantity
 * nie zmienily sie od lat, w odroznieniu od renderowanego Reactem .wc-block-*.
 *
 * ZERO NADPISAN SZABLONOW. Caly wyglad powstaje z hookow, filtrow i CSS.
 * Dzieki temu aktualizacja WooCommerce niczego tu nie psuje i nie pojawia sie
 * ostrzezenie "template is out of date" w WooCommerce -> Status.
 *
 * Gdyby ktorys element okazal sie nieosiagalny bez zmiany struktury HTML,
 * nadpisanie pojedynczego szablonu jest swiadoma decyzja do podjecia osobno,
 * wraz z odnotowaniem wersji skopiowanego pliku.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Czy jestesmy na stronie koszyka.
 *
 * Opakowanie is_cart() z podwojnym zabezpieczeniem: funkcja istnieje tylko
 * przy aktywnym WooCommerce, a sama odpowiada sensownie dopiero po akcji 'wp'
 * (CartCheckoutUtils::is_page_type()). Wszystkie hooki tego pliku odpalaja sie
 * przy renderowaniu, wiec ten warunek jest wtedy juz wiarygodny.
 *
 * @return bool
 */
function cyber_is_cart_page() {
	return cyber_is_woocommerce_active() && function_exists( 'is_cart' ) && is_cart();
}

/**
 * Naglowek nad tabela produktow.
 *
 * WooCommerce nie ma wlasnego naglowka dla lewej kolumny — prawa dostaje
 * <h2>Cart totals</h2> z szablonu. Dokladamy odpowiednik, zeby obie kolumny
 * zaczynaly sie tak samo.
 *
 * Poziom to <h2>, a nie <h3>: tytul strony jest <h1>, wiec <h3> przeskakiwalby
 * poziom, czego zabrania CLAUDE.md sekcja 11. Wizualnie oba naglowki dostaja
 * rozmiar h3 przez CSS, wiec wyglad zgadza sie z projektem, a struktura
 * dokumentu zostaje poprawna.
 *
 * @return void
 */
function cyber_wc_cart_products_heading() {
	printf(
		'<h2 class="cyber-wc-heading">%s</h2>',
		esc_html__( 'Produkty w koszyku', 'cyber-framework' )
	);
}
add_action( 'woocommerce_before_cart_table', 'cyber_wc_cart_products_heading', 5 );

/**
 * Wlasny przycisk przejscia do zamowienia.
 *
 * Zastepuje domyslny przycisk WooCommerce, zeby uzywal klas motywu
 * (.btn .btn-large) i dziedziczyl KOMPLET stylow z zakladki Przyciski —
 * tak samo jak przycisk CTA w headerze. Zero wlasnych pol wygladu.
 *
 * @return void
 */
function cyber_wc_cart_checkout_button() {
	printf(
		'<a href="%1$s" class="btn btn-large cyber-wc-checkout-button">%2$s</a>',
		esc_url( wc_get_checkout_url() ),
		esc_html__( 'Przejdź do zamówienia', 'cyber-framework' )
	);
}

/**
 * Przestawia hooki koszyka na wersje motywu.
 *
 * Podpiete na 'wp', a nie od razu przy ladowaniu pliku: domyslne callbacki
 * WooCommerce rejestruja sie w includes/wc-template-hooks.php przy starcie
 * wtyczki, wiec remove_action() musi odpalic sie pozniej.
 *
 * @return void
 */
function cyber_wc_cart_setup_hooks() {
	if ( ! cyber_is_woocommerce_active() ) {
		return;
	}

	// Produkty powiazane pod koszykiem — projekt ich nie przewiduje.
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

	// Domyslny przycisk -> wlasny, w klasach motywu.
	remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
	add_action( 'woocommerce_proceed_to_checkout', 'cyber_wc_cart_checkout_button', 20 );
}
add_action( 'wp', 'cyber_wc_cart_setup_hooks' );

/**
 * Etykiety WooCommerce dopasowane do projektu.
 *
 * Filtr jest ZAWEZONY do strony koszyka. Ciagi "Total" i "Subtotal" wystepuja
 * w WooCommerce w dziesiatkach miejsc — w zamowieniach, mailach i panelu —
 * wiec globalna podmiana rozjechalaby cala wtyczke.
 *
 * Porownujemy $text (oryginal angielski), nie $translated: dzieki temu filtr
 * dziala niezaleznie od tego, czy i jakie tlumaczenie WooCommerce jest wgrane.
 *
 * @param string $translated Ciag po tlumaczeniu.
 * @param string $text       Ciag zrodlowy.
 * @param string $domain     Domena tlumaczenia.
 * @return string
 */
function cyber_wc_cart_labels( $translated, $text, $domain ) {
	if ( 'woocommerce' !== $domain || ! cyber_is_cart_page() ) {
		return $translated;
	}

	$labels = array(
		'Cart totals' => __( 'Podsumowanie koszyka', 'cyber-framework' ),
		'Subtotal'    => __( 'Razem', 'cyber-framework' ),
		'Total'       => __( 'Wartość zamówienia', 'cyber-framework' ),
	);

	return isset( $labels[ $text ] ) ? $labels[ $text ] : $translated;
}
add_filter( 'gettext', 'cyber_wc_cart_labels', 10, 3 );

/**
 * Assety strony koszyka — kolejkowane WYLACZNIE tam, gdzie sa potrzebne.
 *
 * CLAUDE.md sekcja 10: zaden z tych plikow nie ma prawa ladowac sie na stronie,
 * ktora nie jest koszykiem.
 *
 * Skrypt obsluguje krokowy licznik ilosci i automatyczne przeliczanie koszyka
 * po jej zmianie. Bez niego ukryty przycisk "Zaktualizuj koszyk" oznaczalby,
 * ze zmiana ilosci nie robi nic — dlatego JS jest tu wymogiem, nie ozdoba.
 *
 * @return void
 */
function cyber_wc_cart_assets() {
	if ( ! cyber_is_cart_page() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-wc-cart',
		CYBER_URI . '/assets/css/woocommerce-cart.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/woocommerce-cart.css' )
	);

	wp_enqueue_script(
		'cyber-wc-cart',
		CYBER_URI . '/assets/js/cart.js',
		array(),
		cyber_asset_version( 'assets/js/cart.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	/*
	 * Etykiety przyciskow licznika ida z PHP, zeby zostaly tlumaczalne —
	 * skrypt nie ma wlasnych ciagow tekstowych (ten sam wzorzec co
	 * data-label-* w assets/js/header.js).
	 */
	wp_localize_script(
		'cyber-wc-cart',
		'cyberCart',
		array(
			'decrease' => __( 'Zmniejsz ilość', 'cyber-framework' ),
			'increase' => __( 'Zwiększ ilość', 'cyber-framework' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_wc_cart_assets', 20 );
