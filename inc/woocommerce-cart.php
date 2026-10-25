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

	/*
	 * Filtr etykiet rejestrujemy dopiero tutaj, a nie przy ladowaniu pliku.
	 * 'gettext' fires dla KAZDEGO ciagu WordPressa, WooCommerce i wszystkich
	 * wtyczek — takze w panelu. Callback i tak nic nie robi poza koszykiem,
	 * a koszyk rozpoznajemy najwczesniej na 'wp', wiec wczesniejsza
	 * rejestracja tylko dokladala wywolan na kazdej innej podstronie.
	 */
	add_filter( 'gettext', 'cyber_wc_cart_labels', 10, 3 );
}
add_action( 'wp', 'cyber_wc_cart_setup_hooks' );

/**
 * Prog darmowej dostawy z ustawien WooCommerce.
 *
 * Kwoty NIE wpisujemy w motywie ani w ACF: zrodlem jest metoda wysylki
 * "Darmowa wysylka" (WooCommerce -> Ustawienia -> Wysylka) z warunkiem
 * "Minimalna kwota zamowienia". Dzieki temu obietnica "darmowa dostawa od X"
 * pokazuje dokladnie ten prog, ktory WooCommerce potem naprawde stosuje.
 *
 * Przy kilku strefach z roznymi progami bierzemy najnizszy — adres klienta
 * nie jest jeszcze znany, a obietnica "od X zl" ma byc prawdziwa w kazdej.
 *
 * @return array|null Tablica 'amount' (float) i 'ignore_discounts' (bool)
 *                    albo null, gdy sklep nie ma darmowej wysylki z progiem.
 */
function cyber_wc_free_shipping_threshold() {
	static $threshold = false;

	if ( false !== $threshold ) {
		return $threshold;
	}

	$threshold = null;

	if ( ! cyber_is_woocommerce_active() || ! class_exists( 'WC_Shipping_Zones' ) ) {
		return $threshold;
	}

	$methods = array();

	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		$methods = array_merge( $methods, $zone['shipping_methods'] );
	}

	// Strefa 0 = "Pozostale lokalizacje"; get_zones() jej nie zwraca.
	$methods = array_merge( $methods, ( new WC_Shipping_Zone( 0 ) )->get_shipping_methods() );

	foreach ( $methods as $method ) {
		if ( 'free_shipping' !== $method->id || ! $method->is_enabled() ) {
			continue;
		}

		if ( ! in_array( $method->requires, array( 'min_amount', 'either' ), true ) ) {
			continue;
		}

		$amount = (float) $method->min_amount;

		if ( $amount > 0 && ( null === $threshold || $amount < $threshold['amount'] ) ) {
			$threshold = array(
				'amount'           => $amount,
				'ignore_discounts' => 'yes' === $method->ignore_discounts,
			);
		}
	}

	return $threshold;
}

/**
 * Gdy dostepna jest darmowa wysylka, znikaja platne metody wysylki.
 *
 * WooCommerce sam pokazuje "Darmowa wysylka" dopiero od minimalnej kwoty
 * (warunek metody), ale platnych metod nie chowa — klient widzialby obok
 * siebie np. "Kurier: 18 zl" i "Darmowa wysylka". Gdy w paczce jest
 * dostepna metoda free_shipping, zostawiamy tylko ja (i odbior osobisty,
 * ktory jest osobnym wyborem, nie platna alternatywa).
 *
 * Zaznaczenia nie trzeba wymuszac: jesli wybrana wczesniej metoda zniknela
 * z listy, WooCommerce (wc_get_chosen_shipping_method_for_package()) wybiera
 * domyslna z pozostalych — czyli darmowa. Ponizej progu filtr nic nie robi,
 * bo WooCommerce w ogole nie zwraca stawki free_shipping.
 *
 * Dziala w koszyku, na stronie zamowienia i w jej odswiezaniu AJAX-em.
 *
 * @param WC_Shipping_Rate[] $rates Stawki paczki, klucz = id stawki.
 * @return WC_Shipping_Rate[]
 */
function cyber_wc_hide_paid_shipping_when_free( $rates ) {
	$kept = array();

	foreach ( $rates as $rate_id => $rate ) {
		if ( in_array( $rate->get_method_id(), array( 'free_shipping', 'local_pickup' ), true ) ) {
			$kept[ $rate_id ] = $rate;
		}
	}

	foreach ( $kept as $rate ) {
		if ( 'free_shipping' === $rate->get_method_id() ) {
			return $kept;
		}
	}

	return $rates;
}
add_filter( 'woocommerce_package_rates', 'cyber_wc_hide_paid_shipping_when_free', 100 );

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

	// Mapa jest stala w obrebie zadania, a kazdy __() w srodku ponownie
	// przechodzi przez caly lancuch filtrow 'gettext' — budujemy ja raz.
	static $labels = null;

	if ( null === $labels ) {
		$labels = array(
			'Cart totals' => __( 'Podsumowanie koszyka', 'cyber-framework' ),
			'Subtotal'    => __( 'Razem', 'cyber-framework' ),
			'Total'       => __( 'Wartość zamówienia', 'cyber-framework' ),
		);
	}

	return isset( $labels[ $text ] ) ? $labels[ $text ] : $translated;
}

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
