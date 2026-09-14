<?php
/**
 * WooCommerce — sklep i kategorie produktow.
 *
 * Uklad dwukolumnowy dla listy produktow: waska kolumna z widgetami po lewej,
 * glowna kolumna po prawej. Obowiazuje na stronie sklepu ORAZ na archiwach
 * taksonomii produktow (kategorie, tagi, atrybuty).
 *
 * Ta sama zasada co przy koszyku i zamowieniu: wszystko z hookow i CSS,
 * ZERO nadpisan szablonow (CLAUDE.md sekcja 2). Uklad wchodzi przez
 * woocommerce_before_main_content, wiec archive-product.php zostaje nietkniety.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dozwolone widoki listy produktow.
 *
 * Nazwa widoku jest jednoczesnie czlonem klasy modyfikujacej
 * (.cyber-shop--grid) i wartoscia zapamietywana w ciasteczku.
 *
 * @return string[] Nazwy widokow.
 */
function cyber_shop_views() {
	return array( 'grid', 'list' );
}

/**
 * Dozwolone liczby produktow na stronie.
 *
 * @return int[] Wartosci do wyboru.
 */
function cyber_shop_per_page_choices() {
	return array( 12, 24, 48, 96 );
}

/**
 * Nazwa ciasteczka pamietajacego wybrany widok.
 */
const CYBER_SHOP_VIEW_COOKIE = 'cyber_shop_view';

/**
 * Klucz nonce dla doladowywania produktow.
 */
const CYBER_SHOP_MORE_NONCE = 'cyber_shop_more';

/**
 * Czy biezacy widok to lista produktow.
 *
 * Obejmuje strone sklepu i KAZDE archiwum taksonomii produktow, nie tylko
 * kategorie — tagi i atrybuty maja wygladac tak samo.
 *
 * @return bool
 */
function cyber_is_product_archive() {
	return cyber_is_woocommerce_active()
		&& function_exists( 'is_shop' )
		&& ( is_shop() || is_product_taxonomy() );
}

/**
 * Wybrany widok listy produktow.
 *
 * Czytany z ciasteczka, zeby serwer od razu wyrenderowal wlasciwy uklad.
 * Gdyby widok zyl wylacznie w localStorage, na kazdym wejsciu mignelaby
 * siatka, zanim skrypt zdazylby przelaczyc na liste.
 *
 * Wartosc z ciasteczka jest danymi od uzytkownika, wiec przechodzi przez
 * biala liste (CLAUDE.md sekcja 9) — nie trafia do klasy CSS bez sprawdzenia.
 *
 * @return string 'grid' albo 'list'.
 */
function cyber_shop_view() {
	$views = cyber_shop_views();

	if ( ! isset( $_COOKIE[ CYBER_SHOP_VIEW_COOKIE ] ) ) {
		return $views[0];
	}

	$view = sanitize_key( wp_unslash( $_COOKIE[ CYBER_SHOP_VIEW_COOKIE ] ) );

	return in_array( $view, $views, true ) ? $view : $views[0];
}

/**
 * Liczba produktow na stronie wybrana przez odwiedzajacego.
 *
 * Parametr z adresu przechodzi przez biala liste: absint() sam nie wystarczy,
 * bo wpisanie per_page=100000 byloby poprawna liczba, a zabiloby zapytanie.
 *
 * @return int Liczba z listy dozwolonych.
 */
function cyber_shop_per_page() {
	$choices = cyber_shop_per_page_choices();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- publiczny parametr listy, nie zapis.
	$requested = isset( $_GET['per_page'] ) ? absint( wp_unslash( $_GET['per_page'] ) ) : 0;

	return in_array( $requested, $choices, true ) ? $requested : $choices[0];
}
add_filter( 'loop_shop_per_page', 'cyber_shop_per_page', 20 );

/**
 * Obszary widgetow sklepu.
 *
 * Trzy, zgodnie z decyzja projektowa:
 * - kolumna boczna jest WSPOLNA dla sklepu i kategorii, bo zwykle trafia tam
 *   to samo drzewo kategorii,
 * - paski nad lista sa OSOBNE, bo filtry na stronie sklepu i w konkretnej
 *   kategorii rzadko maja byc identyczne.
 *
 * @return void
 */
function cyber_shop_widget_areas() {
	$areas = array(
		'cyber-shop-sidebar' => array(
			'name'        => __( 'Sklep — kolumna boczna', 'cyber-framework' ),
			'description' => __( 'Widoczna na stronie sklepu i na stronach kategorii.', 'cyber-framework' ),
		),
		'cyber-shop-top'     => array(
			'name'        => __( 'Sklep — pasek nad listą', 'cyber-framework' ),
			'description' => __( 'Widoczny wyłącznie na stronie sklepu.', 'cyber-framework' ),
		),
		'cyber-category-top' => array(
			'name'        => __( 'Kategoria — pasek nad listą', 'cyber-framework' ),
			'description' => __( 'Widoczny wyłącznie na stronach kategorii, tagów i atrybutów.', 'cyber-framework' ),
		),
	);

	foreach ( $areas as $id => $area ) {
		register_sidebar(
			array(
				'id'            => $id,
				'name'          => $area['name'],
				'description'   => $area['description'],
				'before_widget' => '<div id="%1$s" class="cyber-widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="cyber-widget__title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'cyber_shop_widget_areas' );

/**
 * Otwiera uklad dwukolumnowy listy produktow.
 *
 * Zastepuje domyslne opakowanie WooCommerce. Kontener jest tutaj, a nie
 * w szablonie, bo archive-product.php renderuje sie bezposrednio w <main>
 * z header.php — nie przechodzi przez index.php, wiec nie dostaje
 * .cyber-container po drodze.
 *
 * Klase widoku buduje cyber_variant_class(), ten sam mechanizm co w headerze
 * i stopce (CLAUDE.md sekcja 20).
 *
 * @return void
 */
function cyber_shop_wrapper_open() {
	?>
	<div class="cyber-container">
		<div class="<?php echo esc_attr( cyber_variant_class( 'cyber-shop', cyber_shop_view() ) ); ?>">

			<aside class="cyber-shop__sidebar">
				<?php dynamic_sidebar( 'cyber-shop-sidebar' ); ?>
			</aside>

			<div class="cyber-shop__main">
	<?php
}

/**
 * Zamyka uklad dwukolumnowy listy produktow.
 *
 * @return void
 */
function cyber_shop_wrapper_close() {
	?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Tresc kategorii z pola ACF zamiast natywnego opisu.
 *
 * Decyzja projektowa: tresc zyje w polu WYSIWYG (acf-json/group_product_category.json),
 * a natywne pole Opis jest ukrywane w panelu, zeby redaktor mial jedno
 * oczywiste miejsce do pisania.
 *
 * ZNANY KOSZT TEJ DECYZJI: wtyczki SEO i eksporty czytaja natywny opis terminu,
 * ktory pozostaje pusty. Jesli meta description kategorii zacznie byc istotna,
 * trzeba bedzie ja zasilic osobno — patrz docs/architecture.md.
 *
 * @return void
 */
function cyber_shop_category_content() {
	if ( ! is_product_taxonomy() || ! function_exists( 'get_field' ) ) {
		return;
	}

	$term = get_queried_object();

	if ( ! $term instanceof WP_Term ) {
		return;
	}

	$content = get_field( 'cyber_category_content', $term );

	// Brak tresci jest normalnym stanem — zostaje sama nazwa kategorii.
	if ( empty( $content ) ) {
		return;
	}

	printf(
		'<div class="cyber-shop__description">%s</div>',
		wp_kses_post( $content )
	);
}

/**
 * Pasek widgetow nad lista produktow.
 *
 * Sklep i kategorie maja OSOBNE obszary, wiec redaktor moze pokazac w sklepie
 * inny zestaw filtrow niz w konkretnej kategorii.
 *
 * Podpiety do woocommerce_archive_description, a NIE do
 * woocommerce_before_shop_loop. Ten drugi odpala sie dopiero wewnatrz warunku
 * "if ( woocommerce_product_loop() )" w archive-product.php, wiec w pustej
 * kategorii nie zadzialalby wcale — a filtry sa wtedy najbardziej potrzebne,
 * bo to nimi odwiedzajacy zdejmuje zawezenie, ktore nic nie znalazlo.
 *
 * woocommerce_archive_description odpala sie przed tym warunkiem, wiec pasek
 * pojawia sie takze wtedy, gdy lista jest pusta.
 *
 * @return void
 */
function cyber_shop_widgets_bar() {
	$area = is_shop() ? 'cyber-shop-top' : 'cyber-category-top';

	if ( ! is_active_sidebar( $area ) ) {
		return;
	}

	printf( '<div class="cyber-shop__widgets cyber-shop__widgets--%s">', esc_attr( is_shop() ? 'shop' : 'category' ) );
	dynamic_sidebar( $area );
	echo '</div>';
}

/**
 * Otwiera pasek narzedzi nad lista produktow.
 *
 * Sortowanie WooCommerce (priorytet 30) laduje w srodku, miedzy otwarciem (25)
 * a sterowaniem widokiem (35).
 *
 * @return void
 */
function cyber_shop_toolbar_open() {
	echo '<div class="cyber-shop__toolbar"><div class="cyber-shop__toolbar-left">';
}

/**
 * Sterowanie widokiem i liczba produktow na stronie.
 *
 * Przelacznik widoku dziala po stronie przegladarki — nie przeladowuje strony,
 * bo oba uklady maja ten sam markup i roznia sie wylacznie CSS-em.
 *
 * Liczba produktow na stronie MUSI przejsc przez serwer, bo zmienia zapytanie.
 * Formularz niesie pozostale parametry (sortowanie, filtry) w polach ukrytych,
 * zeby zmiana liczby nie kasowala wyboru sortowania.
 *
 * @return void
 */
function cyber_shop_toolbar_controls() {
	$view     = cyber_shop_view();
	$per_page = cyber_shop_per_page();
	?>
	</div><div class="cyber-shop__toolbar-right">

		<div class="cyber-shop__views" role="group" aria-label="<?php esc_attr_e( 'Widok listy produktów', 'cyber-framework' ); ?>">
			<button type="button" class="cyber-shop__view" data-view="grid" aria-pressed="<?php echo 'grid' === $view ? 'true' : 'false'; ?>">
				<?php echo cyber_get_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Widok siatki', 'cyber-framework' ); ?></span>
			</button>
			<button type="button" class="cyber-shop__view" data-view="list" aria-pressed="<?php echo 'list' === $view ? 'true' : 'false'; ?>">
				<?php echo cyber_get_icon( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Widok listy', 'cyber-framework' ); ?></span>
			</button>
		</div>

		<form class="cyber-shop__per-page" method="get">
			<label for="cyber-per-page"><?php esc_html_e( 'Na stronie:', 'cyber-framework' ); ?></label>
			<select name="per_page" id="cyber-per-page" onchange="this.form.submit()">
				<?php foreach ( cyber_shop_per_page_choices() as $choice ) : ?>
					<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $per_page, $choice ); ?>>
						<?php echo esc_html( $choice ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			/*
			 * Pozostale parametry adresu jako pola ukryte — inaczej zmiana
			 * liczby produktow skasowalaby wybrane sortowanie i filtry.
			 * Ta sama funkcja, ktorej WooCommerce uzywa we wlasnym formularzu
			 * sortowania.
			 */
			wc_query_string_form_fields( null, array( 'per_page', 'submit', 'paged', 'product-page' ) );
			?>
		</form>

	</div>
	<?php
}

/**
 * Zamyka pasek narzedzi.
 *
 * @return void
 */
function cyber_shop_toolbar_close() {
	echo '</div>';
}

/**
 * Opcje sortowania zgodne z projektem.
 *
 * WooCommerce nie ma sortowania alfabetycznego w domyslnej liscie, mimo ze
 * obsluguje orderby=title w zapytaniu. Dokladamy je jako pierwsza pozycje
 * i ustawiamy jako domyslna.
 *
 * Pominiete swiadomie: "Domyslne sortowanie" (kolejnosc reczna) i "Ocena" —
 * projekt ich nie przewiduje.
 *
 * @param array $options Domyslne opcje WooCommerce.
 * @return array Opcje z projektu.
 */
function cyber_shop_orderby_options( $options ) {
	unset( $options );

	return array(
		'title'      => __( 'Alfabetycznie', 'cyber-framework' ),
		'popularity' => __( 'Popularne', 'cyber-framework' ),
		'date'       => __( 'Najnowsze', 'cyber-framework' ),
		'price'      => __( 'Cena: od najniższej', 'cyber-framework' ),
		'price-desc' => __( 'Cena: od najwyższej', 'cyber-framework' ),
	);
}
add_filter( 'woocommerce_catalog_orderby', 'cyber_shop_orderby_options' );
add_filter( 'woocommerce_default_catalog_orderby', 'cyber_shop_default_orderby' );

/**
 * Domyslne sortowanie listy produktow.
 *
 * Nadpisuje ustawienie sklepu, bo projekt zaklada alfabetyczne.
 *
 * @return string Klucz sortowania.
 */
function cyber_shop_default_orderby() {
	return 'title';
}

/**
 * Sortowanie wybrane przez odwiedzajacego.
 *
 * Parametr z adresu przechodzi przez liste opcji projektu — wartosc spoza niej
 * wraca do domyslnej, zamiast trafic do zapytania.
 *
 * @return string Klucz sortowania.
 */
function cyber_shop_current_orderby() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- publiczny parametr listy, nie zapis.
	$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';

	return array_key_exists( $orderby, cyber_shop_orderby_options( array() ) )
		? $orderby
		: cyber_shop_default_orderby();
}

/**
 * Skrocony opis produktu, widoczny wylacznie w widoku listy.
 *
 * Wypisywany ZAWSZE, a ukrywany CSS-em w widoku siatki. Dzieki temu
 * przelaczenie widoku nie wymaga przeladowania strony ani zapytania AJAX —
 * oba uklady maja ten sam markup.
 *
 * @return void
 */
function cyber_shop_excerpt() {
	$excerpt = get_the_excerpt();

	if ( '' === trim( $excerpt ) ) {
		return;
	}

	printf(
		'<div class="cyber-product__excerpt">%s</div>',
		esc_html( $excerpt )
	);
}

/**
 * Przycisk doladowania kolejnych produktow.
 *
 * Zastepuje paginacje, gdy w Global Options wybrano tryb "Pokaz wiecej".
 * Na ostatniej stronie nie renderuje sie wcale — nie ma czego doladowac.
 *
 * @return void
 */
function cyber_shop_load_more() {
	$total = (int) wc_get_loop_prop( 'total_pages' );
	$page  = max( 1, (int) wc_get_loop_prop( 'current_page' ) );

	if ( $total <= $page ) {
		return;
	}

	/*
	 * Kontekst archiwum jedzie w atrybutach data-*, zeby endpoint mogl zlozyc
	 * DOKLADNIE to samo zapytanie. Przekazujemy identyfikatory, a nie gotowe
	 * argumenty WP_Query — te serwer buduje sam, po sprawdzeniu kazdego z nich.
	 */
	$taxonomy = '';
	$term_id  = 0;

	if ( is_product_taxonomy() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$taxonomy = $term->taxonomy;
			$term_id  = $term->term_id;
		}
	}

	printf(
		'<div class="cyber-shop__more"><button type="button" class="btn btn-large cyber-shop__more-button"'
			. ' data-page="%1$d" data-max="%2$d" data-per-page="%3$d" data-orderby="%4$s"'
			. ' data-taxonomy="%5$s" data-term="%6$d">%7$s</button></div>',
		esc_attr( $page ),
		esc_attr( $total ),
		esc_attr( cyber_shop_per_page() ),
		esc_attr( cyber_shop_current_orderby() ),
		esc_attr( $taxonomy ),
		esc_attr( $term_id ),
		esc_html__( 'Pokaż więcej', 'cyber-framework' )
	);
}

/**
 * Przestawia hooki WooCommerce na wersje motywu.
 *
 * Podpiete na 'wp', a nie przy ladowaniu pliku: domyslne callbacki rejestruja
 * sie w includes/wc-template-hooks.php przy starcie wtyczki, wiec
 * remove_action() musi odpalic pozniej.
 *
 * @return void
 */
function cyber_shop_setup_hooks() {
	if ( ! cyber_is_woocommerce_active() ) {
		return;
	}

	// Wlasne opakowanie ukladu zamiast domyslnego <div id="primary">.
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_before_main_content', 'cyber_shop_wrapper_open', 10 );
	add_action( 'woocommerce_after_main_content', 'cyber_shop_wrapper_close', 10 );

	/*
	 * Kolumna boczna WooCommerce. archive-product.php konczy sie hookiem
	 * woocommerce_sidebar, ktory prowadzi do get_sidebar( 'shop' ).
	 *
	 * Motyw nie ma ani sidebar-shop.php, ani sidebar.php, wiec WordPress siega
	 * po plik awaryjny wp-includes/theme-compat/sidebar.php — a ten ma wpisane
	 * NA SZTYWNO wyszukiwarke, liste stron, archiwa i kategorie. Nie sa to
	 * widgety: nie widac ich w panelu i nie da sie ich usunac przez Wyglad.
	 *
	 * Nasz uklad ma wlasna kolumne boczna wewnatrz siatki (cyber-shop-sidebar),
	 * wiec hook WooCommerce jest tu zbedny i tylko doklada obcy blok pod lista.
	 */
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	// Okruszki ma wlasne inc/breadcrumb.php — drugie byloby duplikatem.
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

	// Opis kategorii z pola ACF zamiast natywnego opisu terminu.
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	add_action( 'woocommerce_archive_description', 'cyber_shop_category_content', 10 );

	// Licznik "Wyniki 1-12 z 40" nie wystepuje w projekcie.
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

	/*
	 * Pasek widgetow tuz pod opisem — na hooku, ktory odpala sie takze
	 * w pustej kategorii. Pasek narzedzi zostaje przy petli produktow:
	 * sortowanie i liczba na stronie nie maja sensu bez produktow.
	 */
	add_action( 'woocommerce_archive_description', 'cyber_shop_widgets_bar', 20 );

	add_action( 'woocommerce_before_shop_loop', 'cyber_shop_toolbar_open', 25 );
	add_action( 'woocommerce_before_shop_loop', 'cyber_shop_toolbar_controls', 35 );
	add_action( 'woocommerce_before_shop_loop', 'cyber_shop_toolbar_close', 40 );

	// Skrocony opis pod tytulem — widoczny tylko w widoku listy.
	add_action( 'woocommerce_after_shop_loop_item_title', 'cyber_shop_excerpt', 15 );

	if ( 'loadmore' === cyber_get_option( 'wc_pagination_type' ) ) {
		remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
		add_action( 'woocommerce_after_shop_loop', 'cyber_shop_load_more', 10 );
	}
}
add_action( 'wp', 'cyber_shop_setup_hooks' );

/**
 * AJAX: kolejna strona produktow dla przycisku "Pokaz wiecej".
 *
 * Komplet zabezpieczen wg CLAUDE.md sekcja 9:
 *
 * - NONCE — check_ajax_referer() na wejsciu.
 * - SANITIZACJA — kazdy parametr osobno, zgodnie z jego typem.
 * - WALIDACJA — strona i liczba na stronie z bialej listy, sortowanie z listy
 *   opcji projektu, taksonomia musi nalezec do produktow, a termin istniec.
 * - ESCAPING — odpowiedz to JSON; markup produktow buduje szablon WooCommerce,
 *   ktory escapuje u siebie.
 *
 * BRAK current_user_can() jest swiadomy: to publiczna lista produktow, ta sama,
 * ktora kazdy widzi bez logowania. Endpoint niczego nie zapisuje i nie
 * przyjmuje zadnego identyfikatora uzytkownika — zwraca wylacznie to, co
 * i tak jest widoczne pod adresem kategorii.
 *
 * Parametry sa przepisywane na zapytanie OD ZERA, a nie przyjmowane jako
 * gotowa tablica argumentow. Przyjecie argumentow WP_Query od przegladarki
 * pozwoliloby czytac dowolne wpisy, takze robocze i prywatne.
 *
 * @return void
 */
function cyber_shop_load_more_products() {
	check_ajax_referer( CYBER_SHOP_MORE_NONCE, 'nonce' );

	if ( ! cyber_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'Sklep jest niedostępny.', 'cyber-framework' ) ), 400 );
	}

	$page     = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 0;
	$per_page = isset( $_POST['per_page'] ) ? absint( wp_unslash( $_POST['per_page'] ) ) : 0;
	$orderby  = isset( $_POST['orderby'] ) ? sanitize_key( wp_unslash( $_POST['orderby'] ) ) : 'title';

	if ( $page < 2 ) {
		wp_send_json_error( array( 'message' => __( 'Nieprawidłowy numer strony.', 'cyber-framework' ) ), 400 );
	}

	if ( ! in_array( $per_page, cyber_shop_per_page_choices(), true ) ) {
		$per_page = cyber_shop_per_page_choices()[0];
	}

	if ( ! array_key_exists( $orderby, cyber_shop_orderby_options( array() ) ) ) {
		$orderby = 'title';
	}

	/*
	 * Wykluczenie produktow ukrytych w katalogu budujemy JAWNIE, a nie przez
	 * WC()->query->get_main_tax_query(). Ta metoda opiera sie na glownym
	 * zapytaniu strony, ktorego w zadaniu AJAX nie ma — zwraca wtedy pusta
	 * tablice, a pusty element w tax_query wywraca cale zapytanie i zwraca
	 * zero produktow.
	 */
	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'exclude-from-catalog' ),
			'operator' => 'NOT IN',
		),
	);

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'posts_per_page'      => $per_page,
		'paged'               => $page,
	);

	/*
	 * Kontekst archiwum. Taksonomia musi byc zarejestrowana DLA PRODUKTOW —
	 * bez tego mozna by podac dowolna taksonomie i wyciagnac obce wpisy.
	 */
	$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
	$term_id  = isset( $_POST['term'] ) ? absint( wp_unslash( $_POST['term'] ) ) : 0;

	if ( '' !== $taxonomy && $term_id > 0 ) {
		$product_taxonomies = get_object_taxonomies( 'product' );

		if ( ! in_array( $taxonomy, $product_taxonomies, true ) || ! term_exists( $term_id, $taxonomy ) ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowa kategoria.', 'cyber-framework' ) ), 400 );
		}

		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $term_id,
		);
	}

	$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

	// Sortowanie liczy WooCommerce, zeby cena i popularnosc dzialaly tak samo jak w petli.
	$args = array_merge( $args, WC()->query->get_catalog_ordering_args( $orderby ) );

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		wp_send_json_success(
			array(
				'html'     => '',
				'has_more' => false,
			)
		);
	}

	ob_start();

	wc_set_loop_prop( 'is_shortcode', false );
	wc_set_loop_prop( 'columns', wc_get_default_products_per_row() );

	while ( $query->have_posts() ) {
		$query->the_post();
		wc_get_template_part( 'content', 'product' );
	}

	wp_reset_postdata();

	wp_send_json_success(
		array(
			'html'     => ob_get_clean(),
			'has_more' => $page < (int) $query->max_num_pages,
		)
	);
}
add_action( 'wp_ajax_' . CYBER_SHOP_MORE_NONCE, 'cyber_shop_load_more_products' );
add_action( 'wp_ajax_nopriv_' . CYBER_SHOP_MORE_NONCE, 'cyber_shop_load_more_products' );

/**
 * Ukrywa natywne pole Opis w edycji kategorii produktu.
 *
 * Tresc kategorii zyje w polu ACF, wiec dwa pola tekstowe obok siebie tylko
 * myliyby redaktora. Pole jest UKRYTE, a nie usuniete — dane zapisane w nim
 * wczesniej zostaja w bazie i nic ich nie kasuje.
 *
 * @return void
 */
function cyber_shop_hide_term_description() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-product_cat' !== $screen->id ) {
		return;
	}

	echo '<style>.term-description-wrap{display:none;}</style>';
}
add_action( 'admin_head', 'cyber_shop_hide_term_description' );

/**
 * Assety listy produktow — kolejkowane wylacznie tam, gdzie sa potrzebne.
 *
 * CLAUDE.md sekcja 10.
 *
 * @return void
 */
function cyber_shop_assets() {
	if ( ! cyber_is_product_archive() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-wc-shop',
		CYBER_URI . '/assets/css/woocommerce-shop.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/woocommerce-shop.css' )
	);

	wp_enqueue_script(
		'cyber-wc-shop',
		CYBER_URI . '/assets/js/shop.js',
		array(),
		cyber_asset_version( 'assets/js/shop.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'cyber-wc-shop',
		'cyberShop',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => CYBER_SHOP_MORE_NONCE,
			'nonce'   => wp_create_nonce( CYBER_SHOP_MORE_NONCE ),
			'cookie'  => CYBER_SHOP_VIEW_COOKIE,
			'error'   => __( 'Nie udało się wczytać kolejnych produktów. Odśwież stronę i spróbuj ponownie.', 'cyber-framework' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_shop_assets', 20 );
