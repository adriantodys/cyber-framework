<?php
/**
 * Opinie — typ tresci "Opinie" i karta opinii w sekcji Wpisy.
 *
 * TYLKO PANEL. Opinia nie ma wlasnej strony ani archiwum na froncie —
 * wyswietla ja sekcja "Wpisy / CPT" (typ "Opinie" albo reczny wybor).
 * Dlatego CPT jest niepubliczny, a do listy typow sekcji dopisuje sie
 * filtrem cyber_post_card_types.
 *
 * KTO CO TRZYMA:
 * - tytul wpisu        = podpis autora (np. "Magda"),
 * - obrazek wyrozniajacy = zdjecie osoby, opcjonalne (okragly awatar),
 * - cyber_review_text   = tresc opinii (pole ACF, group_review),
 * - cyber_review_rating = ocena 1-5 (gwiazdki).
 *
 * WYGLAD: gwiazdki, tresc w cudzyslowie jako tytul karty, pod nia awatar z podpisem. Karta to
 * wspolny komponent template-parts/components/card.php z wariantem
 * cyber-card--review; tlo, odstepy, kolumny i rozmiar tytulu ustawia sie
 * jak w kazdej sekcji kart (ustawienia kart sekcji Wpisy).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slug typu tresci opinii.
 *
 * Nie zmieniac: slug jest zapisany przy kazdej opinii, w lokalizacji grupy
 * pol (group_review) i w ustawieniach sekcji Wpisy, ktore go wybraly.
 */
const CYBER_REVIEW_TYPE = 'cyber_review';

/**
 * Najwyzsza ocena — liczba gwiazdek na karcie.
 */
const CYBER_REVIEW_MAX_RATING = 5;

/**
 * Rejestruje typ tresci "Opinie".
 *
 * @return void
 */
function cyber_register_reviews() {
	register_post_type(
		CYBER_REVIEW_TYPE,
		array(
			'labels'              => array(
				'name'                  => __( 'Opinie', 'cyber-framework' ),
				'singular_name'         => __( 'Opinia', 'cyber-framework' ),
				'menu_name'             => __( 'Opinie', 'cyber-framework' ),
				'add_new'               => __( 'Dodaj nową', 'cyber-framework' ),
				'add_new_item'          => __( 'Dodaj opinię', 'cyber-framework' ),
				'edit_item'             => __( 'Edytuj opinię', 'cyber-framework' ),
				'new_item'              => __( 'Nowa opinia', 'cyber-framework' ),
				'search_items'          => __( 'Szukaj opinii', 'cyber-framework' ),
				'not_found'             => __( 'Brak opinii.', 'cyber-framework' ),
				'not_found_in_trash'    => __( 'Brak opinii w koszu.', 'cyber-framework' ),
				'all_items'             => __( 'Wszystkie opinie', 'cyber-framework' ),
				'featured_image'        => __( 'Zdjęcie osoby', 'cyber-framework' ),
				'set_featured_image'    => __( 'Ustaw zdjęcie osoby', 'cyber-framework' ),
				'remove_featured_image' => __( 'Usuń zdjęcie osoby', 'cyber-framework' ),
				'use_featured_image'    => __( 'Użyj jako zdjęcia osoby', 'cyber-framework' ),
			),
			'description'         => __( 'Opinie klientów pokazywane sekcją "Wpisy / CPT".', 'cyber-framework' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'menu_position'       => 23,
			'menu_icon'           => 'dashicons-format-quote',
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			// page-attributes daje pole "Kolejnosc" — sekcja moze sortowac po menu_order.
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
		)
	);
}
add_action( 'init', 'cyber_register_reviews' );

/**
 * Podpowiedz w polu tytulu: tytul opinii to podpis autora.
 *
 * @param string  $text Domyslny tekst.
 * @param WP_Post $post Edytowany wpis.
 * @return string
 */
function cyber_review_title_placeholder( $text, $post ) {
	return CYBER_REVIEW_TYPE === $post->post_type ? __( 'Podpis autora, np. Magda', 'cyber-framework' ) : $text;
}
add_filter( 'enter_title_here', 'cyber_review_title_placeholder', 10, 2 );

/**
 * Opinie w liscie typow sekcji "Wpisy / CPT".
 *
 * @param array<string, string> $types Slug => etykieta.
 * @return array<string, string>
 */
function cyber_review_card_type( $types ) {
	$object = get_post_type_object( CYBER_REVIEW_TYPE );

	if ( $object ) {
		$types[ CYBER_REVIEW_TYPE ] = $object->labels->name;
	}

	return $types;
}
add_filter( 'cyber_post_card_types', 'cyber_review_card_type' );

/**
 * Ocena opinii w zakresie 1-5. Brak albo bledna wartosc = 5.
 *
 * @param int $post_id Opinia.
 * @return int
 */
function cyber_review_rating( $post_id ) {
	$rating = cyber_is_acf_active() ? absint( get_field( 'cyber_review_rating', $post_id ) ) : 0;

	return ( $rating >= 1 && $rating <= CYBER_REVIEW_MAX_RATING ) ? $rating : CYBER_REVIEW_MAX_RATING;
}

/**
 * Opinia jako element karty sekcji Wpisy.
 *
 * Zastepuje karte zbudowana dla zwyklego wpisu, z ktorej bierze tylko
 * ustawienia wygladu (znacznik tytulu). Opinia nie ma:
 * - daty i kategorii — to nie artykul,
 * - odnosnikow ani przycisku — nie ma wlasnej strony.
 *
 * Tresc opinii trafia w miejsce TYTULU karty (duzy, cytat w „…”), podpis
 * w miejsce TEKSTU — dzieki temu
 * pola "Rozmiar tytulu" i "Kolor/grubosc tekstu" z ustawien kart steruja
 * odpowiednio cytatem i podpisem.
 *
 * @param array   $item       Karta z cyber_post_card_item() (dla title_tag).
 * @param WP_Post $post       Opinia.
 * @param bool    $show_image Czy pokazac zdjecie osoby (ustawienie "Zdjecie wyrozniajace").
 * @return array<string, mixed>
 */
function cyber_review_card_item( array $item, WP_Post $post, $show_image ) {
	$text = cyber_is_acf_active() ? trim( (string) get_field( 'cyber_review_text', $post->ID ) ) : '';

	return array(
		'image_id'    => $show_image ? (int) get_post_thumbnail_id( $post ) : 0,
		'image_url'   => '',
		'image_size'  => 'thumbnail',
		'overtitle'   => '',
		'rating'      => cyber_review_rating( $post->ID ),
		'meta'        => '',
		'title'       => '' !== $text ? '„' . wp_strip_all_tags( $text ) . '”' : '',
		'title_url'   => '',
		'title_tag'   => $item['title_tag'],
		'text'        => wp_strip_all_tags( get_the_title( $post ) ),
		'url'         => '',
		'label'       => '',
		'target'      => '',
		'link_style'  => $item['link_style'],
		'button_size' => $item['button_size'],
		'variant'     => 'review',
	);
}
