<?php
/**
 * Blog: lista wpisow, pojedynczy wpis i pasek boczny z widgetami.
 *
 * WIDOKI W templates/. WordPress szuka home.php, single.php i archiwow
 * w katalogu glownym motywu; filtry {$type}_template_hierarchy dokladaja na
 * poczatek listy pliki z templates/ (CLAUDE.md sekcja 3). Blogowy uklad
 * dostaja:
 *
 *   templates/blog.php         — strona wpisow (home), kategorie, tagi,
 *                                archiwa dat i autorow,
 *   templates/single-post.php  — pojedynczy wpis typu "post".
 *
 * Inne typy tresci, wyszukiwarka i strony zostaja przy index.php, a sklep
 * przy szablonach WooCommerce.
 *
 * USTAWIENIA w Global Options -> Blog. Wartosci wplywajace na wyglad ida do
 * CSS jako zmienne w wp_head (cyber_blog_css()), karty listy korzystaja z tych
 * samych klas co sekcja Karty (cyber_cards_attributes()).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Identyfikator obszaru widgetow bloga.
 */
const CYBER_BLOG_SIDEBAR = 'cyber-blog';

/**
 * Obszar widgetow paska bocznego bloga i widget "Ostatnie wpisy".
 *
 * @return void
 */
function cyber_blog_widgets_init() {
	register_sidebar(
		array(
			'id'            => CYBER_BLOG_SIDEBAR,
			'name'          => __( 'Blog — pasek boczny', 'cyber-framework' ),
			'description'   => __( 'Prawa kolumna na liscie wpisow i we wpisie. Wlacznik i szerokosc: Global Options -> Blog.', 'cyber-framework' ),
			'before_widget' => '<section id="%1$s" class="cyber-blog-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="cyber-blog-widget__title">',
			'after_title'   => '</h2>',
		)
	);

	register_widget( 'Cyber_Recent_Posts_Widget' );
}
add_action( 'widgets_init', 'cyber_blog_widgets_init' );

/**
 * Czy biezacy widok jest listą bloga.
 *
 * @return bool
 */
function cyber_is_blog_list() {
	return is_home() || is_category() || is_tag() || is_date() || is_author();
}

/**
 * Czy biezacy widok korzysta z ukladu bloga (lista albo wpis).
 *
 * @return bool
 */
function cyber_is_blog_view() {
	return cyber_is_blog_list() || is_singular( 'post' );
}

/**
 * Doklada szablony z templates/ na poczatek hierarchii WordPressa.
 *
 * @param string[] $templates Kandydaci w kolejnosci WordPressa.
 * @return string[]
 */
function cyber_blog_template_hierarchy( $templates ) {
	if ( is_singular( 'post' ) ) {
		array_unshift( $templates, 'templates/single-post.php' );
	} elseif ( cyber_is_blog_list() ) {
		array_unshift( $templates, 'templates/blog.php' );
	}

	return $templates;
}

foreach ( array( 'home', 'category', 'tag', 'date', 'author', 'single' ) as $cyber_type ) {
	add_filter( $cyber_type . '_template_hierarchy', 'cyber_blog_template_hierarchy' );
}
unset( $cyber_type );

/**
 * Czy pasek boczny ma sie pojawic.
 *
 * Wlaczony w Global Options i obszar ma choc jeden widget. Pusty obszar nie
 * zostawia pustej kolumny — tresc idzie wtedy na cala szerokosc.
 *
 * @return bool
 */
function cyber_blog_has_sidebar() {
	return (bool) cyber_get_option( 'blog_sidebar' ) && is_active_sidebar( CYBER_BLOG_SIDEBAR );
}

/**
 * Podpowiedz dla administratora: pasek wlaczony, ale bez widgetow.
 *
 * @return string Czysty tekst albo pusty string.
 */
function cyber_blog_sidebar_hint() {
	if ( ! cyber_get_option( 'blog_sidebar' ) || is_active_sidebar( CYBER_BLOG_SIDEBAR ) || ! current_user_can( 'edit_theme_options' ) ) {
		return '';
	}

	return __( 'Pasek boczny bloga jest wlaczony, ale nie ma w nim widgetow: Wyglad -> Widgety -> "Blog — pasek boczny". Goscie widza tresc na cala szerokosc.', 'cyber-framework' );
}

/**
 * Klasa opakowania ukladu bloga.
 *
 * @return string
 */
function cyber_blog_layout_class() {
	return 'cyber-container cyber-blog ' . ( cyber_blog_has_sidebar() ? 'cyber-blog--sidebar' : 'cyber-blog--full' );
}

/**
 * Zmienne CSS bloga z Global Options — wypisywane w wp_head.
 *
 * Wlasna petla zamiast cyber_css_vars_from_map(): rozmiar tytulu wpisu to
 * odwolanie do globalnej wielkosci naglowka (var(--cyber-font-size-h3)),
 * nie liczba z jednostka. Tytuly na liscie ustawia wspolny mechanizm kart
 * (cyber_blog_cards_row()), wiec tu ich nie ma.
 *
 * @return string CSS bez znacznika <style>.
 */
function cyber_blog_css() {
	$vars = array(
		'--cyber-blog-sidebar-width'     => (int) cyber_get_option( 'blog_sidebar_width' ) . '%',
		'--cyber-blog-gap'               => (int) cyber_get_option( 'blog_gap' ) . 'px',
		'--cyber-blog-post-title-size'   => sprintf( 'var(--cyber-font-size-%s)', cyber_get_option( 'blog_single_title_size' ) ),
		'--cyber-blog-post-title-weight' => (int) cyber_get_option( 'blog_single_title_weight' ),
	);

	$css = ':root{';

	foreach ( $vars as $name => $value ) {
		$css .= sprintf( '%1$s:%2$s;', $name, $value );
	}

	return $css . '}';
}

/**
 * Ustawienia kart listy bloga jako wiersz w formacie sekcji Karty.
 *
 * Dzieki temu lista bloga, sekcja Wpisy i sekcja Karty dziela walidacje,
 * klasy i CSS — jeden wyglad karty w calym motywie.
 *
 * @return array
 */
function cyber_blog_cards_row() {
	return array(
		'cyber_cards_columns'        => cyber_get_option( 'blog_columns' ),
		'cyber_cards_columns_tablet' => cyber_get_option( 'blog_columns_tablet' ),
		'cyber_cards_columns_mobile' => 1,
		'cyber_cards_gap_x'          => cyber_get_option( 'blog_gap_x' ),
		'cyber_cards_gap_y'          => cyber_get_option( 'blog_gap_y' ),
		'cyber_cards_image_ratio'    => cyber_get_option( 'blog_image_ratio' ),
		'cyber_cards_gap_media'      => 24,
		'cyber_cards_gap_title'      => 12,
		'cyber_cards_gap_text'       => 24,
		'cyber_cards_title_size'     => cyber_get_option( 'blog_title_size' ),
		'cyber_cards_title_weight'   => cyber_get_option( 'blog_title_weight' ),
	);
}

/**
 * Opcje karty wpisu na liscie bloga.
 *
 * @return array
 */
function cyber_blog_card_opts() {
	return array(
		'image'       => true,
		'date'        => (bool) cyber_get_option( 'blog_show_date' ),
		'category'    => (bool) cyber_get_option( 'blog_show_category' ),
		'excerpt'     => (bool) cyber_get_option( 'blog_show_excerpt' ),
		'words'       => (int) cyber_get_option( 'blog_excerpt_length' ),
		'label'       => cyber_get_option( 'blog_show_button' ) ? (string) cyber_get_option( 'blog_button_label' ) : '',
		'link_style'  => 'button',
		'button_size' => (string) cyber_get_option( 'blog_button_size' ),
		'title_tag'   => 'h2',
	);
}

/**
 * Tytul listy bloga.
 *
 * Strona wpisow: tytul strony ustawionej w Ustawienia -> Czytanie albo "Blog".
 * Archiwa: nazwa kategorii, tagu, autora albo okresu — bez przedrostka
 * "Kategoria:", ktory WordPress dokleja w get_the_archive_title().
 *
 * @return string Czysty tekst.
 */
function cyber_blog_title() {
	if ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );

		return $page ? get_the_title( $page ) : __( 'Blog', 'cyber-framework' );
	}

	add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
	$title = wp_strip_all_tags( get_the_archive_title() );
	remove_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

	return $title;
}

/**
 * Czy tytul listy jest widoczny.
 *
 * Strona wpisow: wedlug Global Options (makieta go nie ma — wtedy tylko dla
 * czytnikow ekranu, strona i tak ma jeden <h1>). Archiwa: zawsze, bo bez
 * nazwy kategorii lista nie ma kontekstu.
 *
 * @return bool
 */
function cyber_blog_title_visible() {
	return ! is_home() || (bool) cyber_get_option( 'blog_show_title' );
}

/**
 * Linia meta pojedynczego wpisu: data, kategorie, autor — wedlug ustawien.
 *
 * @param WP_Post $post Wpis.
 * @return string[] Czyste teksty.
 */
function cyber_blog_single_meta( WP_Post $post ) {
	$meta = array();

	if ( cyber_get_option( 'blog_single_date' ) ) {
		$meta[] = cyber_post_date( $post );
	}

	if ( cyber_get_option( 'blog_single_category' ) ) {
		$names = wp_list_pluck( get_the_category( $post->ID ), 'name' );

		if ( $names ) {
			$meta[] = implode( ', ', $names );
		}
	}

	if ( cyber_get_option( 'blog_single_author' ) ) {
		$meta[] = get_the_author_meta( 'display_name', (int) $post->post_author );
	}

	return array_filter( $meta );
}

/**
 * Style bloga — tylko na widokach bloga.
 *
 * Karty listy i widgetu korzystaja z arkusza sekcji (klasy .cyber-cards),
 * wiec on tez sie tu laduje.
 *
 * @return void
 */
function cyber_blog_assets() {
	if ( ! cyber_is_blog_view() ) {
		return;
	}

	wp_enqueue_style(
		'cyber-sections',
		CYBER_URI . '/assets/css/sections.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/sections.css' )
	);

	wp_enqueue_style(
		'cyber-blog',
		CYBER_URI . '/assets/css/blog.css',
		array( 'cyber-main', 'cyber-sections' ),
		cyber_asset_version( 'assets/css/blog.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_blog_assets', 20 );
