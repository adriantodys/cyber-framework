<?php
/**
 * WooCommerce — strona pojedynczego produktu.
 *
 * Uklad: sekcja gorna podzielona na dwie kolumny (zdjecia + galeria po lewej,
 * informacje o produkcie po prawej), pod nia sekcja z zakladkami. Szerokosci
 * kolumn, wysokosc sekcji ze zdjeciami i wysokosc miniatur pochodza z Global
 * Options i trafiaja do CSS jako zmienne (inc/enqueue.php).
 *
 * Ta sama zasada co przy koszyku, zamowieniu i liscie produktow: wszystko
 * z hookow i CSS, ZERO nadpisan szablonow (CLAUDE.md sekcja 2).
 * content-single-product.php zostaje nietkniety.
 *
 * KOLEJNOSC I WIDOCZNOSC ELEMENTOW. Kazdy element prawej kolumny ma w panelu
 * wlasny wylacznik i wlasna pozycje. Pozycja z pola ACF trafia WPROST do
 * priorytetu add_action(), wiec liczba wpisana przez admina jest kolejnoscia
 * renderowania — bez tablicy posredniej, ktora trzeba by trzymac w zgodzie
 * z hookami. Rejestr elementow siedzi w cyber_product_elements()
 * (inc/helpers.php), bo korzystaja z niego takze schemat opcji i pola ACF.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Czy biezacy widok to strona pojedynczego produktu.
 *
 * @return bool
 */
function cyber_is_product_page() {
	return cyber_is_woocommerce_active()
		&& function_exists( 'is_product' )
		&& is_product();
}

/**
 * Czy dany element strony produktu ma byc wyswietlony.
 *
 * @param string $key Klucz elementu z cyber_product_elements().
 * @return bool
 */
function cyber_product_shows( $key ) {
	return (bool) cyber_get_option( 'wc_product_show_' . $key );
}

/**
 * Pozycja elementu strony produktu.
 *
 * Wartosc jest priorytetem add_action(), a nie indeksem w jakiejs liscie —
 * dzieki temu wtyczka, ktora dopina sie do tego samego hooka, wciaz laduje
 * dokladnie tam, gdzie jej priorytet kaze.
 *
 * @param string $key Klucz elementu z cyber_product_elements().
 * @return int Priorytet.
 */
function cyber_product_position( $key ) {
	return (int) cyber_get_option( 'wc_product_pos_' . $key );
}

/**
 * Mapa elementow na hook i funkcje renderujaca.
 *
 * Elementy bez wpisu w tej tablicy (pole ilosci, plakietka promocji) nie
 * renderuja sie wlasnym hookiem — ich miejsce wynika z markupu, wiec maja
 * w panelu sam wylacznik, bez pozycji.
 *
 * Dolozenie kolejnego elementu WooCommerce: wpis w cyber_product_elements()
 * plus wpis tutaj. Nic wiecej — pola ACF, schemat opcji i podpiecie hooka
 * wynikaja z tych dwoch tablic.
 *
 * @return array<string, array{0: string, 1: string}> Klucz => hook i callback.
 */
function cyber_product_element_callbacks() {
	return array(
		'title'   => array( 'woocommerce_single_product_summary', 'woocommerce_template_single_title' ),
		'sku'     => array( 'woocommerce_single_product_summary', 'cyber_product_sku' ),
		'rating'  => array( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating' ),
		'excerpt' => array( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt' ),
		'price'   => array( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' ),
		'stock'   => array( 'woocommerce_single_product_summary', 'cyber_product_stock' ),
		'cart'    => array( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart' ),
		'meta'    => array( 'woocommerce_single_product_summary', 'cyber_product_meta' ),
		'tabs'    => array( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs' ),
		'upsells' => array( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display' ),
		'related' => array( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products' ),
	);
}

/* -------------------------------------------------------------------------- *
 * Opakowanie ukladu
 * -------------------------------------------------------------------------- */

/**
 * Otwiera kontener strony produktu.
 *
 * Zastepuje opakowanie listy produktow (cyber_shop_wrapper_open), ktore ma
 * kolumne boczna z widgetami — na stronie produktu bylaby pusta.
 *
 * @return void
 */
function cyber_product_wrapper_open() {
	?>
	<div class="cyber-container">
		<div class="cyber-product">
	<?php
}

/**
 * Zamyka kontener strony produktu.
 *
 * @return void
 */
function cyber_product_wrapper_close() {
	?>
		</div>
	</div>
	<?php
}

/**
 * Otwiera sekcje dwukolumnowa (zdjecia + informacje).
 *
 * Galeria i div.summary sa w content-single-product.php rodzenstwem, a zaraz
 * za nimi leca zakladki. Bez tego opakowania siatka objelaby takze zakladki
 * i wrzucilaby je do jednej z kolumn.
 *
 * @return void
 */
function cyber_product_top_open() {
	echo '<div class="cyber-product__top">';
}

/**
 * Zamyka sekcje dwukolumnowa.
 *
 * @return void
 */
function cyber_product_top_close() {
	echo '</div>';
}

/* -------------------------------------------------------------------------- *
 * Lewa kolumna — zdjecie glowne i galeria
 * -------------------------------------------------------------------------- */

/**
 * Zdjecie glowne produktu wraz z pionowa galeria miniatur.
 *
 * Wlasny markup zamiast woocommerce_show_product_images(). Powod: domyslna
 * galeria WooCommerce to flexslider + photoswipe + zoom, wlaczane przez
 * add_theme_support(). Motyw ich nie wlacza, a projekt wymaga ukladu, ktorego
 * flexslider nie potrafi — pionowego paska miniatur przewijanego
 * przeciagnieciem. Taniej jest zbudowac pasek od zera niz przestawiac obca
 * biblioteke, ktorej i tak nie ladujemy.
 *
 * Widoczna czesc paska jest wysoka na tyle, ile ma cala sekcja ze zdjeciem;
 * przy ustawieniach domyslnych (500px sekcja, 158px miniatura) miesci
 * dokladnie trzy miniatury, a reszta zostaje pod krawedzia i dojezdza
 * przeciagnieciem.
 *
 * @return void
 */
function cyber_product_gallery() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$ids = array();

	if ( $product->get_image_id() ) {
		$ids[] = (int) $product->get_image_id();
	}

	foreach ( $product->get_gallery_image_ids() as $gallery_id ) {
		$ids[] = (int) $gallery_id;
	}

	$ids = array_values( array_unique( array_filter( $ids ) ) );
	?>
	<div class="cyber-product__media">
		<div class="cyber-product__stage">
			<?php
			if ( cyber_product_shows( 'sale' ) ) {
				woocommerce_show_product_sale_flash();
			}

			if ( $ids ) {
				echo wp_get_attachment_image(
					$ids[0],
					'woocommerce_single',
					false,
					array(
						'class' => 'cyber-product__image',
						'id'    => 'cyber-product-image',
					)
				);
			} else {
				printf(
					'<img class="cyber-product__image" id="cyber-product-image" src="%1$s" alt="%2$s" />',
					esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ),
					esc_attr__( 'Brak zdjęcia produktu', 'cyber-framework' )
				);
			}
			?>
		</div>

		<?php if ( count( $ids ) > 1 ) : ?>
			<div class="cyber-product__gallery" data-cyber-gallery="1">
				<div class="cyber-product__track">
					<?php foreach ( $ids as $index => $id ) : ?>
						<button
							type="button"
							class="cyber-product__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-full="<?php echo esc_url( (string) wp_get_attachment_image_url( $id, 'woocommerce_single' ) ); ?>"
							data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $id, 'woocommerce_single' ) ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %d: numer zdjecia. */ __( 'Pokaż zdjęcie %d', 'cyber-framework' ), $index + 1 ) ); ?>"
							aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						>
							<?php
							echo wp_get_attachment_image(
								$id,
								'woocommerce_thumbnail',
								false,
								array( 'alt' => '' )
							);
							?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* -------------------------------------------------------------------------- *
 * Prawa kolumna — elementy wlasne
 * -------------------------------------------------------------------------- */

/**
 * Numer katalogowy produktu.
 *
 * WooCommerce pokazuje SKU wewnatrz bloku product_meta, razem z kategoriami
 * i tagami. W projekcie SKU stoi tuz pod tytulem, a kategorie i tagi to osobny,
 * domyslnie wylaczony element — stad dwie funkcje zamiast jednej.
 *
 * @return void
 */
function cyber_product_sku() {
	global $product;

	if ( ! $product instanceof WC_Product || ! wc_product_sku_enabled() ) {
		return;
	}

	$sku = (string) $product->get_sku();

	if ( '' === $sku ) {
		return;
	}

	printf(
		'<p class="cyber-product__sku">%1$s <span class="sku">%2$s</span></p>',
		esc_html__( 'SKU:', 'cyber-framework' ),
		esc_html( $sku )
	);
}

/**
 * Dostepnosc produktu w magazynie.
 *
 * Markup budujemy sami zamiast wolac wc_get_stock_html(). Ta funkcja jest
 * wywolywana takze przez szablon przycisku zakupu, a my ten sam napis
 * przestawiamy w inne miejsce — samo wywolanie w nowym miejscu nie usuneloby
 * go ze starego. Filtr cyber_product_hide_default_stock() gasi wersje
 * wbudowana, a ta funkcja wypisuje wlasna, na pozycji z panelu.
 *
 * @return void
 */
function cyber_product_stock() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$availability = $product->get_availability();

	if ( empty( $availability['availability'] ) ) {
		return;
	}

	printf(
		'<p class="cyber-product__stock stock %1$s">%2$s</p>',
		esc_attr( isset( $availability['class'] ) ? $availability['class'] : '' ),
		esc_html( $availability['availability'] )
	);
}

/**
 * Wycisza dostepnosc renderowana przez szablon przycisku zakupu.
 *
 * Gasimy WYLACZNIE dostepnosc produktu, ktory jest tresci strony. Produkt
 * z wariantami podaje dostepnosc kazdego wariantu przez ta sama funkcje
 * (availability_html w danych wariantu, podmieniane skryptem po wyborze) —
 * gdyby filtr lapal wszystko, klient przestalby widziec, czy wybrany wariant
 * jest na stanie.
 *
 * @param string     $html    Gotowy markup dostepnosci.
 * @param WC_Product $product Produkt, ktorego dotyczy markup.
 * @return string Pusty string dla produktu ze strony, oryginal w kazdym innym przypadku.
 */
function cyber_product_hide_default_stock( $html, $product ) {
	if ( ! cyber_is_product_page() || ! $product instanceof WC_Product ) {
		return $html;
	}

	return $product->get_id() === get_queried_object_id() ? '' : $html;
}
add_filter( 'woocommerce_get_stock_html', 'cyber_product_hide_default_stock', 10, 2 );

/**
 * Kategorie i tagi produktu.
 *
 * Bez SKU — ten ma wlasny element i wlasna pozycje.
 *
 * @return void
 */
function cyber_product_meta() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$categories = wc_get_product_category_list(
		$product->get_id(),
		', ',
		'<span class="posted_in">' . esc_html__( 'Kategorie:', 'cyber-framework' ) . ' ',
		'</span>'
	);

	$tags = wc_get_product_tag_list(
		$product->get_id(),
		', ',
		'<span class="tagged_as">' . esc_html__( 'Tagi:', 'cyber-framework' ) . ' ',
		'</span>'
	);

	if ( '' === $categories && '' === $tags ) {
		return;
	}

	printf(
		'<div class="product_meta cyber-product__meta">%1$s%2$s</div>',
		wp_kses_post( $categories ),
		wp_kses_post( $tags )
	);
}

/* -------------------------------------------------------------------------- *
 * Licznik ilosci
 * -------------------------------------------------------------------------- */

/**
 * Przycisk zmniejszajacy ilosc.
 *
 * Wchodzi hookiem WooCommerce wewnatrz pola ilosci, wiec nie wymaga nadpisania
 * szablonu global/quantity-input.php. Ten sam szablon obsluguje koszyk, stad
 * warunek na strone produktu — inaczej licznik pojawilby sie tam podwojnie.
 *
 * @return void
 */
function cyber_product_quantity_minus() {
	if ( ! cyber_is_product_page() ) {
		return;
	}

	printf(
		'<button type="button" class="cyber-qty__button" data-step="down" aria-label="%s">&minus;</button>',
		esc_attr__( 'Zmniejsz ilość', 'cyber-framework' )
	);
}

/**
 * Przycisk zwiekszajacy ilosc.
 *
 * @return void
 */
function cyber_product_quantity_plus() {
	if ( ! cyber_is_product_page() ) {
		return;
	}

	printf(
		'<button type="button" class="cyber-qty__button" data-step="up" aria-label="%s">+</button>',
		esc_attr__( 'Zwiększ ilość', 'cyber-framework' )
	);
}
add_action( 'woocommerce_before_quantity_input_field', 'cyber_product_quantity_minus' );
add_action( 'woocommerce_after_quantity_input_field', 'cyber_product_quantity_plus' );

/* -------------------------------------------------------------------------- *
 * Zakladki
 * -------------------------------------------------------------------------- */

/**
 * Zakladki pod sekcja dwukolumnowa.
 *
 * Na tym etapie projekt pokazuje wylacznie dlugi opis. Zamiast wycinac
 * pozostale zakladki na sztywno, przepuszczamy zestaw przez wlasny filtr —
 * to jest miejsce, w ktorym dopina sie kazda przyszla zakladka, na przyklad
 * zbudowana z pola ACF:
 *
 *     add_filter( 'cyber_product_tabs', function ( $tabs ) {
 *         $tabs['care'] = array(
 *             'title'    => 'Pielegnacja',
 *             'priority' => 20,
 *             'callback' => 'moj_render_zakladki',
 *         );
 *
 *         return $tabs;
 *     } );
 *
 * Drugi argument filtra to pelny, oryginalny zestaw WooCommerce — dzieki temu
 * przywrocenie wbudowanej zakladki (np. "Informacje dodatkowe") jest jedna
 * linia, a nie odtwarzaniem jej callbacku.
 *
 * @param array<string, array<string, mixed>> $tabs Zakladki WooCommerce.
 * @return array<string, array<string, mixed>> Zakladki do wyswietlenia.
 */
function cyber_product_tabs( $tabs ) {
	if ( ! cyber_is_product_page() ) {
		return $tabs;
	}

	$kept = array();

	if ( isset( $tabs['description'] ) ) {
		$tabs['description']['title'] = __( 'Opis produktu', 'cyber-framework' );
		$kept['description']          = $tabs['description'];
	}

	/**
	 * Filtruje zakladki strony produktu w motywie.
	 *
	 * @param array<string, array<string, mixed>> $kept Zakladki wybrane przez motyw.
	 * @param array<string, array<string, mixed>> $tabs Pelny zestaw WooCommerce.
	 */
	return apply_filters( 'cyber_product_tabs', $kept, $tabs );
}
add_filter( 'woocommerce_product_tabs', 'cyber_product_tabs', 98 );

/**
 * Wycisza naglowek wewnatrz zakladki z opisem.
 *
 * WooCommerce powtarza w panelu zakladki jej wlasny tytul jako <h2>. Przy
 * jednej zakladce daje to ten sam napis dwa razy pod soba.
 *
 * @param string $heading Naglowek panelu.
 * @return string Pusty string na stronie produktu, oryginal wszedzie indziej.
 */
function cyber_product_description_heading( $heading ) {
	return cyber_is_product_page() ? '' : $heading;
}
add_filter( 'woocommerce_product_description_heading', 'cyber_product_description_heading' );

/* -------------------------------------------------------------------------- *
 * Podpiecie hookow
 * -------------------------------------------------------------------------- */

/**
 * Przebudowuje strone produktu.
 *
 * Odpala sie na 'wp', czyli PO cyber_shop_setup_hooks() z tego samego hooka —
 * kolejnosc wynika z kolejnosci ladowania modulow w functions.php i jest
 * potrzebna, zeby zdjac opakowanie listy produktow, ktore tamten modul
 * podpina globalnie.
 *
 * @return void
 */
function cyber_product_setup_hooks() {
	if ( ! cyber_is_product_page() ) {
		return;
	}

	// Opakowanie listy produktow ma kolumne boczna — na produkcie niepotrzebna.
	remove_action( 'woocommerce_before_main_content', 'cyber_shop_wrapper_open', 10 );
	remove_action( 'woocommerce_after_main_content', 'cyber_shop_wrapper_close', 10 );
	add_action( 'woocommerce_before_main_content', 'cyber_product_wrapper_open', 10 );
	add_action( 'woocommerce_after_main_content', 'cyber_product_wrapper_close', 10 );

	// Sekcja dwukolumnowa obejmuje galerie i div.summary, ale juz nie zakladki.
	add_action( 'woocommerce_before_single_product_summary', 'cyber_product_top_open', 5 );
	add_action( 'woocommerce_after_single_product_summary', 'cyber_product_top_close', 1 );

	// Wlasna galeria zamiast flexslidera.
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	add_action( 'woocommerce_before_single_product_summary', 'cyber_product_gallery', 20 );

	/*
	 * Prawa kolumna i sekcja pod nia sa budowane od zera z rejestru. Zdejmujemy
	 * WSZYSTKIE domyslne callbacki tych dwoch hookow i dopinamy z powrotem tylko
	 * te wlaczone w panelu, na pozycjach z panelu.
	 *
	 * Swiadomie NIE ruszamy WC_Structured_Data (priorytet 60) — dane
	 * strukturalne produktu nie sa elementem wygladu.
	 */
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

	foreach ( cyber_product_element_callbacks() as $key => $definition ) {
		if ( ! cyber_product_shows( $key ) ) {
			continue;
		}

		list( $hook, $callback ) = $definition;

		add_action( $hook, $callback, cyber_product_position( $key ) );
	}
}
add_action( 'wp', 'cyber_product_setup_hooks' );

/**
 * Klasa stanu na elemencie body.
 *
 * Ukrycie pola ilosci jest stanem prezentacji, nie zmiana regul sklepu —
 * dlatego klasa, a nie filtr woocommerce_is_sold_individually, ktory
 * zmienialby dozwolona ilosc w koszyku (CLAUDE.md sekcja 20).
 *
 * @param string[] $classes Klasy body.
 * @return string[] Klasy body.
 */
function cyber_product_body_class( $classes ) {
	if ( cyber_is_product_page() && ! cyber_product_shows( 'quantity' ) ) {
		$classes[] = 'cyber-product-no-quantity';
	}

	return $classes;
}
add_filter( 'body_class', 'cyber_product_body_class' );

/**
 * Style i skrypt strony produktu.
 *
 * Enqueue warunkowy (CLAUDE.md sekcja 10) — pliki nie laduja sie poza
 * pojedynczym produktem.
 *
 * @return void
 */
function cyber_product_assets() {
	if ( ! cyber_is_product_page() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-wc-product',
		CYBER_URI . '/assets/css/woocommerce-product.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/woocommerce-product.css' )
	);

	wp_enqueue_script(
		'cyber-wc-product',
		CYBER_URI . '/assets/js/product.js',
		array(),
		cyber_asset_version( 'assets/js/product.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_product_assets', 20 );
