<?php
/**
 * WooCommerce — strona zamowienia (checkout).
 *
 * Ta sama zasada co przy koszyku (inc/woocommerce-cart.php): klasyczny
 * shortcode [woocommerce_checkout], wyglad z hookow i CSS.
 *
 * JEDEN WYJATEK od reguly "zero nadpisan" (CLAUDE.md sekcja 2):
 * woocommerce/checkout/review-order.php. Projekt wymaga tabeli o trzech
 * kolumnach, a naglowka trzeciej nie da sie dolozyc zadnym hookiem, bo w thead
 * nie ma punktu zaczepienia. Decyzja podjeta jawnie przed implementacja;
 * zakres zmian i wersja skopiowanego pliku sa opisane w tamtym pliku
 * oraz w docs/architecture.md.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Czy jestesmy na stronie zamowienia.
 *
 * is_checkout() obejmuje takze strone platnosci i podziekowania, dlatego
 * dodatkowo wykluczamy is_order_received_page() — tam uklad dwukolumnowy
 * nie ma czego ustawiac.
 *
 * @return bool
 */
function cyber_is_checkout_page() {
	return cyber_is_woocommerce_active()
		&& function_exists( 'is_checkout' )
		&& is_checkout()
		&& ! is_order_received_page();
}

/**
 * Kolejnosc i etykiety pol rozliczeniowych.
 *
 * Kolejnosc pochodzi wprost z projektu graficznego i rozni sie od domyslnej
 * WooCommerce: kraj przed ulica, kod pocztowy przed miastem, firma na koncu.
 * Sterujemy kluczem priority, wiec nie usuwamy ani nie dodajemy zadnego pola —
 * zmiana dotyczy WYLACZNIE kolejnosci i etykiet.
 *
 * Etykiety ustawiamy jawnie zamiast polegac na tlumaczeniu WooCommerce, bo
 * projekt uzywa innych slow niz ono: Ulica zamiast Adres, Miasto zamiast
 * Miejscowosc.
 *
 * @param array $fields Pola formularza zamowienia.
 * @return array Pola w kolejnosci projektu.
 */
function cyber_wc_checkout_fields( $fields ) {
	$order = array(
		'billing_first_name' => 10,
		'billing_last_name'  => 20,
		'billing_country'    => 30,
		'billing_address_1'  => 40,
		'billing_address_2'  => 45,
		'billing_postcode'   => 50,
		'billing_city'       => 60,
		'billing_state'      => 65,
		'billing_phone'      => 70,
		'billing_email'      => 80,
		'billing_company'    => 90,
	);

	foreach ( $order as $key => $priority ) {
		if ( isset( $fields['billing'][ $key ] ) ) {
			$fields['billing'][ $key ]['priority'] = $priority;
		}
	}

	$labels = array(
		'billing_address_1' => __( 'Ulica', 'cyber-framework' ),
		'billing_city'      => __( 'Miasto', 'cyber-framework' ),
		'billing_phone'     => __( 'Numer telefonu', 'cyber-framework' ),
		'billing_email'     => __( 'Adres email', 'cyber-framework' ),
		'billing_company'   => __( 'Nazwa firmy', 'cyber-framework' ),
	);

	foreach ( $labels as $key => $label ) {
		if ( isset( $fields['billing'][ $key ] ) ) {
			$fields['billing'][ $key ]['label'] = $label;
		}
	}

	// Imie i nazwisko w jednej linii — klasy ukladu pochodza z WooCommerce.
	if ( isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['class'] = array( 'form-row-first' );
	}

	if ( isset( $fields['billing']['billing_last_name'] ) ) {
		$fields['billing']['billing_last_name']['class'] = array( 'form-row-last' );
	}

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label'] = __( 'Uwagi do zamówienia', 'cyber-framework' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'cyber_wc_checkout_fields' );

/**
 * Usuwa domyslny formularz kuponu sprzed formularza zamowienia.
 *
 * Projekt chce kuponu w prawej kolumnie, miedzy tabela a platnosciami.
 * Zastepuje go wlasny blok, patrz cyber_wc_checkout_coupon_box().
 *
 * @return void
 */
function cyber_wc_checkout_remove_default_coupon() {
	if ( ! cyber_is_woocommerce_active() ) {
		return;
	}

	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

	/*
	 * Filtr etykiet rejestrujemy dopiero tutaj — patrz ten sam komentarz
	 * w inc/woocommerce-cart.php. 'gettext' fires dla kazdego ciagu w calej
	 * instalacji, a callback dziala wylacznie na stronie zamowienia.
	 */
	add_filter( 'gettext', 'cyber_wc_checkout_labels', 10, 3 );
}
add_action( 'wp', 'cyber_wc_checkout_remove_default_coupon' );

/**
 * Wlasny blok kuponu w prawej kolumnie.
 *
 * DLACZEGO NIE FORMULARZ WooCommerce. Docelowa pozycja — miedzy tabela
 * podsumowania a blokiem platnosci — lezy WEWNATRZ <form class="checkout">.
 * Szablon WooCommerce opakowuje kupon we wlasny <form>, a HTML zabrania
 * zagniezdzania formularzy: przegladarka usuwa wewnetrzny znacznik i zostawia
 * jego dzieci. Skutki sa trzy i wszystkie zle:
 *
 * 1. Atrybut style="display:none" znika razem z wrapperem, wiec pola sa
 *    widoczne od razu.
 * 2. Skrypt wtyczki przelacza selektor .checkout_coupon, ktorego juz nie ma,
 *    wiec odnosnik nic nie robi.
 * 3. Przycisk kuponu typu submit nalezy wtedy do formularza ZAMOWIENIA —
 *    klikniecie probuje zlozyc zamowienie zamiast dodac kupon.
 *
 * Dlatego markup jest wlasny i CELOWO nie zawiera <form>, a oba przyciski maja
 * type="button". Samo dodanie kuponu wykonuje assets/js/checkout.js, wolajac
 * natywny endpoint WooCommerce (wc-ajax=apply_coupon) wraz z jego nonce —
 * nie dublujemy logiki wtyczki, tylko jej interfejs.
 *
 * Pole kodu NIE ma atrybutu name: lezy wewnatrz formularza zamowienia, wiec
 * nazwane trafialoby do danych skladanego zamowienia bez potrzeby.
 *
 * @return void
 */
function cyber_wc_checkout_coupon_box() {
	if ( ! cyber_is_woocommerce_active() || ! wc_coupons_enabled() ) {
		return;
	}

	?>
	<div class="cyber-coupon">
		<p class="cyber-coupon__toggle">
			<?php esc_html_e( 'Masz kupon?', 'cyber-framework' ); ?>
			<button type="button" class="cyber-coupon__link" aria-expanded="false" aria-controls="cyber-coupon-fields">
				<?php esc_html_e( 'Kliknij tutaj, aby dodać swój kod', 'cyber-framework' ); ?>
			</button>
		</p>

		<div class="cyber-coupon__fields" id="cyber-coupon-fields" hidden>
			<label class="screen-reader-text" for="cyber_coupon_code">
				<?php esc_html_e( 'Kod kuponu', 'cyber-framework' ); ?>
			</label>
			<input type="text" id="cyber_coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Kod kuponu', 'cyber-framework' ); ?>" value="">
			<button type="button" class="btn btn-medium cyber-coupon__apply">
				<?php esc_html_e( 'Wykorzystaj kupon', 'cyber-framework' ); ?>
			</button>
		</div>

		<?php // Komunikat zwrotny WooCommerce po probie dodania kuponu. ?>
		<div class="cyber-coupon__message" role="status"></div>
	</div>
	<?php
}
add_action( 'woocommerce_checkout_order_review', 'cyber_wc_checkout_coupon_box', 15 );

/**
 * Przycisk zlozenia zamowienia w klasach motywu.
 *
 * Zastepuje domyslny markup WooCommerce, zachowujac WSZYSTKIE atrybuty, od
 * ktorych zalezy dzialanie: name, id, value i data-value. Bramki platnosci
 * podmieniaja etykiete przez data-order_button_text, wiec value i data-value
 * musza zostac.
 *
 * @param string $html Domyslny markup przycisku.
 * @return string Markup w klasach motywu.
 */
function cyber_wc_checkout_order_button( $html ) {
	unset( $html );

	/*
	 * Celowo CUDZY hook, bez prefiksu cyber_. To filtr WooCommerce i wtyczki
	 * sklepowe podpinaja sie wlasnie pod niego, zeby zmienic napis na
	 * przycisku. Nadanie mu wlasnej nazwy odcieloby je od tego miejsca —
	 * motyw przejmuje markup przycisku, a nie kontrole nad jego trescia.
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- patrz wyzej.
	$text = apply_filters( 'woocommerce_order_button_text', __( 'Kupuję i płacę', 'cyber-framework' ) );

	return sprintf(
		'<button type="submit" class="btn btn-large cyber-wc-place-order" name="woocommerce_checkout_place_order" id="place_order" value="%1$s" data-value="%1$s">%2$s</button>',
		esc_attr( $text ),
		esc_html( $text )
	);
}
add_filter( 'woocommerce_order_button_html', 'cyber_wc_checkout_order_button' );

/**
 * Etykiety WooCommerce dopasowane do projektu.
 *
 * Filtr zawezony do strony zamowienia, tak samo jak w koszyku. Ciagi Total
 * i Subtotal wystepuja w WooCommerce w dziesiatkach miejsc, wiec globalna
 * podmiana rozjechalaby zamowienia, maile i panel.
 *
 * @param string $translated Ciag po tlumaczeniu.
 * @param string $text       Ciag zrodlowy.
 * @param string $domain     Domena tlumaczenia.
 * @return string
 */
function cyber_wc_checkout_labels( $translated, $text, $domain ) {
	if ( 'woocommerce' !== $domain || ! cyber_is_checkout_page() ) {
		return $translated;
	}

	// Jak w koszyku: siedem __() w srodku, wywolywanych przy kazdym ciagu
	// WooCommerce na tej stronie — budujemy mape raz.
	static $labels = null;

	if ( null === $labels ) {
		$labels = array(
			'Billing details' => __( 'Dane rozliczeniowe', 'cyber-framework' ),
			'Your order'      => __( 'Twoje zamówienie', 'cyber-framework' ),
			'Product'         => __( 'Produkt', 'cyber-framework' ),
			'Quantity'        => __( 'Ilość', 'cyber-framework' ),
			'Subtotal'        => __( 'Kwota zamówienia', 'cyber-framework' ),
			'Shipping'        => __( 'Dostawa', 'cyber-framework' ),
			'Total'           => __( 'Łącznie', 'cyber-framework' ),
		);
	}

	return isset( $labels[ $text ] ) ? $labels[ $text ] : $translated;
}

/**
 * Etykieta wiersza dostawy w podsumowaniu.
 *
 * NIE da sie jej zmienic filtrem gettext. WooCommerce buduje ja przez _x()
 * z kontekstem "shipping packages", a _x() idzie hookiem gettext_with_context,
 * nie gettext — dlatego domyslne tlumaczenie "Przesylka" przechodzilo obok
 * naszej mapy etykiet.
 *
 * Wtyczka wystawia na to dedykowany filtr, wiec uzywamy jego zamiast dokladac
 * drugi filtr tlumaczen.
 *
 * @param string $name Nazwa paczki wysylkowej.
 * @return string Etykieta z projektu.
 */
function cyber_wc_checkout_shipping_label( $name ) {
	unset( $name );

	return __( 'Dostawa', 'cyber-framework' );
}
add_filter( 'woocommerce_shipping_package_name', 'cyber_wc_checkout_shipping_label' );

/**
 * Klucz nonce dla zmiany ilosci na stronie zamowienia.
 */
const CYBER_CHECKOUT_QTY_NONCE = 'cyber_checkout_qty';

/**
 * Licznik ilosci z przyciskami w tabeli podsumowania.
 *
 * Zastepuje domyslne "x 2" sterowaniem. Wartosc jest wypisywana jako tekst,
 * nie pole formularza — autorytetem pozostaje koszyk po stronie serwera,
 * a tabela i tak przeladowuje sie po kazdej zmianie.
 *
 * Pozycje sprzedawane pojedynczo (is_sold_individually) nie dostaja przyciskow,
 * bo ich ilosci i tak nie da sie zmienic.
 *
 * @param string $html          Domyslny markup ilosci.
 * @param array  $cart_item     Pozycja koszyka.
 * @param string $cart_item_key Klucz pozycji.
 * @return string Markup licznika.
 */
function cyber_wc_checkout_quantity( $html, $cart_item, $cart_item_key ) {
	$product  = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
	$quantity = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 0;

	if ( ! $product instanceof WC_Product || $product->is_sold_individually() ) {
		return $html;
	}

	return sprintf(
		'<span class="cyber-qty" data-key="%1$s"><button type="button" class="cyber-qty-btn" data-step="-1" aria-label="%2$s">&minus;</button><span class="cyber-qty-value" aria-live="polite">%3$d</span><button type="button" class="cyber-qty-btn" data-step="1" aria-label="%4$s">+</button></span>',
		esc_attr( $cart_item_key ),
		esc_attr__( 'Zmniejsz ilość', 'cyber-framework' ),
		$quantity,
		esc_attr__( 'Zwiększ ilość', 'cyber-framework' )
	);
}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'cyber_wc_checkout_quantity', 10, 3 );

/**
 * AJAX: zmiana ilosci pozycji koszyka ze strony zamowienia.
 *
 * Komplet zabezpieczen wymagany przez CLAUDE.md sekcja 9:
 *
 * - NONCE — check_ajax_referer() na wejsciu, bez wyjatkow.
 * - SANITIZACJA — klucz przez sanitize_text_field(), ilosc przez absint().
 * - WALIDACJA — pozycja musi istniec w koszyku TEGO uzytkownika, a ilosc
 *   miescic sie w stanie magazynowym i limicie zakupu produktu.
 * - ESCAPING — odpowiedz to JSON z danymi liczbowymi i przetlumaczonym
 *   komunikatem; nie trafia do DOM jako HTML.
 *
 * BRAK current_user_can() jest SWIADOMY i konieczny: koszyk prowadza takze
 * goscie. Endpoint nie jest jednak otwarty — operuje wylacznie na koszyku
 * z sesji osoby wykonujacej zadanie, wiec nie da sie nim dosiegnac cudzych
 * danych. Nonce chroni przed wykonaniem zadania z obcej strony.
 *
 * Minimalna ilosc to 1, nie 0. Zejscie do zera usunieloby pozycje w trakcie
 * skladania zamowienia, a przy ostatniej pozycji oproznilo koszyk i wyrzucilo
 * klienta z checkoutu. Usuwanie pozycji nalezy do koszyka.
 *
 * @return void
 */
function cyber_wc_checkout_update_quantity() {
	check_ajax_referer( CYBER_CHECKOUT_QTY_NONCE, 'nonce' );

	if ( ! cyber_is_woocommerce_active() || ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Koszyk jest niedostępny.', 'cyber-framework' ) ), 400 );
	}

	$key      = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$quantity = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 0;

	if ( '' === $key || $quantity < 1 ) {
		wp_send_json_error( array( 'message' => __( 'Nieprawidłowe dane.', 'cyber-framework' ) ), 400 );
	}

	$item = WC()->cart->get_cart_item( $key );

	if ( ! $item ) {
		wp_send_json_error( array( 'message' => __( 'Nie znaleziono pozycji w koszyku.', 'cyber-framework' ) ), 404 );
	}

	$product = $item['data'];

	if ( ! $product instanceof WC_Product ) {
		wp_send_json_error( array( 'message' => __( 'Nie znaleziono produktu.', 'cyber-framework' ) ), 404 );
	}

	// Limit zakupu: -1 oznacza brak ograniczenia.
	$max = $product->get_max_purchase_quantity();

	if ( $max > 0 && $quantity > $max ) {
		$quantity = $max;
	}

	if ( ! $product->has_enough_stock( $quantity ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Brak wystarczającej ilości w magazynie.', 'cyber-framework' ) ),
			409
		);
	}

	WC()->cart->set_quantity( $key, $quantity, true );

	wp_send_json_success( array( 'quantity' => $quantity ) );
}
add_action( 'wp_ajax_' . CYBER_CHECKOUT_QTY_NONCE, 'cyber_wc_checkout_update_quantity' );
add_action( 'wp_ajax_nopriv_' . CYBER_CHECKOUT_QTY_NONCE, 'cyber_wc_checkout_update_quantity' );

/**
 * Assety strony zamowienia — kolejkowane wylacznie tam, gdzie sa potrzebne.
 *
 * CLAUDE.md sekcja 10. Modul nie ma wlasnego JavaScriptu: rozwijanie kuponu
 * obsluguje skrypt WooCommerce, a przeliczanie sum po zmianie dostawy
 * to natywny mechanizm update_order_review.
 *
 * @return void
 */
function cyber_wc_checkout_assets() {
	if ( ! cyber_is_checkout_page() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-wc-checkout',
		CYBER_URI . '/assets/css/woocommerce-checkout.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/woocommerce-checkout.css' )
	);

	/*
	 * Zaleznosc od jquery jest tu KONIECZNA, mimo zasady z CLAUDE.md sekcja 2.
	 * Po zmianie ilosci trzeba zlecic WooCommerce przeliczenie podsumowania,
	 * a jedyny sposob to zdarzenie 'update_checkout' na document.body —
	 * ktore wtyczka nasluchuje przez jQuery. Zdarzenie natywne nie dotrze
	 * do jQuery'owego nasluchu.
	 *
	 * Nie dokladamy przez to nowej zaleznosci: WooCommerce i tak laduje jQuery
	 * na tej stronie na potrzeby wlasnego skryptu kasy.
	 */
	wp_enqueue_script(
		'cyber-wc-checkout',
		CYBER_URI . '/assets/js/checkout.js',
		array( 'jquery', 'wc-checkout' ),
		cyber_asset_version( 'assets/js/checkout.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'cyber-wc-checkout',
		'cyberCheckout',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => CYBER_CHECKOUT_QTY_NONCE,
			'nonce'   => wp_create_nonce( CYBER_CHECKOUT_QTY_NONCE ),
			'error'   => __( 'Nie udało się zmienić ilości. Odśwież stronę i spróbuj ponownie.', 'cyber-framework' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_wc_checkout_assets', 20 );
