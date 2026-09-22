<?php
/**
 * Galerie — typ tresci "Galerie" i wspolne funkcje sekcji Galeria.
 *
 * JEDNA SEKCJA, DWA ZRODLA. Sekcja "Galeria" pokazuje albo zdjecia wgrane
 * w samej sekcji, albo zdjecia z galerii (CPT cyber_gallery) — z paskiem
 * filtrow po kategoriach. Wyglad siatki, podpisy i lightbox sa w obu trybach
 * te same, wiec to jeden layout z przelacznikiem, a nie dwie sekcje
 * z bliznaczym kodem.
 *
 * GALERIA MA WLASNA STRONE. CPT jest publiczny: pojedyncza galeria ma adres,
 * wlasne sekcje (grupa cyber_sections jest do niej przypieta) i szablon
 * templates/single-gallery.php. Archiwum jest wylaczone — lista galerii
 * powstaje z sekcji na dowolnej stronie.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slug typu tresci galerii.
 *
 * Nie zmieniac: slug jest zapisany przy kazdym wpisie i w lokalizacjach grup pol.
 */
const CYBER_GALLERY_TYPE = 'cyber_gallery';

/**
 * Slug taksonomii kategorii galerii.
 */
const CYBER_GALLERY_TAX = 'cyber_gallery_cat';

/**
 * Rejestruje typ tresci "Galerie" i jego kategorie.
 *
 * @return void
 */
function cyber_register_galleries() {
	register_post_type(
		CYBER_GALLERY_TYPE,
		array(
			'labels'            => array(
				'name'          => __( 'Galerie', 'cyber-framework' ),
				'singular_name' => __( 'Galeria', 'cyber-framework' ),
				'menu_name'     => __( 'Galerie', 'cyber-framework' ),
				'add_new'       => __( 'Dodaj nowa', 'cyber-framework' ),
				'add_new_item'  => __( 'Dodaj galerie', 'cyber-framework' ),
				'edit_item'     => __( 'Edytuj galerie', 'cyber-framework' ),
				'search_items'  => __( 'Szukaj galerii', 'cyber-framework' ),
				'not_found'     => __( 'Brak galerii.', 'cyber-framework' ),
				'all_items'     => __( 'Wszystkie galerie', 'cyber-framework' ),
			),
			'description'       => __( 'Zestawy zdjec pokazywane sekcja "Galeria".', 'cyber-framework' ),
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => false,
			'menu_position'     => 22,
			'menu_icon'         => 'dashicons-format-gallery',
			'capability_type'   => 'page',
			'map_meta_cap'      => true,
			'hierarchical'      => false,
			'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'has_archive'       => false,
			'rewrite'           => array( 'slug' => 'galeria' ),
			'taxonomies'        => array( CYBER_GALLERY_TAX ),
		)
	);

	register_taxonomy(
		CYBER_GALLERY_TAX,
		CYBER_GALLERY_TYPE,
		array(
			'labels'            => array(
				'name'          => __( 'Kategorie galerii', 'cyber-framework' ),
				'singular_name' => __( 'Kategoria galerii', 'cyber-framework' ),
				'menu_name'     => __( 'Kategorie', 'cyber-framework' ),
				'add_new_item'  => __( 'Dodaj kategorie', 'cyber-framework' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => array( 'slug' => 'galerie-kategoria' ),
		)
	);
}
add_action( 'init', 'cyber_register_galleries' );

/**
 * Jednorazowe odswiezenie regul adresow po dodaniu typu tresci.
 *
 * Bez tego pierwsza galeria zwracalaby 404, dopoki ktos nie zapisalby ustawien
 * bezposrednich odnoskow. flush_rewrite_rules() jest kosztowne, wiec odpala sie
 * raz — opcja trzyma wersje regul.
 *
 * @return void
 */
function cyber_gallery_flush_rewrites() {
	if ( get_option( 'cyber_gallery_rewrites' ) === CYBER_VERSION ) {
		return;
	}

	flush_rewrite_rules( false );

	/*
	 * autoload = true (a nie false): ta flaga jest czytana na KAZDYM zadaniu,
	 * w warunku powyzej. Opcja bez autoloadu nie siedzi w cache alloptions,
	 * wiec kazde zadanie placilo za nia osobnym SELECT-em — do konca zycia
	 * witryny, zeby sprawdzic wartosc, ktora juz sie nie zmieni.
	 */
	update_option( 'cyber_gallery_rewrites', CYBER_VERSION, true );
}
add_action( 'init', 'cyber_gallery_flush_rewrites', 20 );

/**
 * Szablon pojedynczej galerii z katalogu templates/.
 *
 * @param string[] $templates Kandydaci w kolejnosci WordPressa.
 * @return string[]
 */
function cyber_gallery_template_hierarchy( $templates ) {
	if ( is_singular( CYBER_GALLERY_TYPE ) ) {
		array_unshift( $templates, 'templates/single-gallery.php' );
	}

	return $templates;
}
add_filter( 'single_template_hierarchy', 'cyber_gallery_template_hierarchy' );

/**
 * Zdjecia pojedynczej galerii.
 *
 * @param int $gallery_id Identyfikator wpisu galerii.
 * @return int[] Identyfikatory zalacznikow.
 */
function cyber_gallery_images( $gallery_id ) {
	if ( ! cyber_is_acf_active() ) {
		return array();
	}

	$images = get_field( 'cyber_gallery_images', $gallery_id );

	return array_values( array_filter( array_map( 'absint', (array) $images ) ) );
}

/**
 * Zdjecie jako element galerii.
 *
 * Podpis bierze sie z biblioteki mediow — tytul albo pole "Podpis"
 * zalacznika, zaleznie od ustawienia sekcji. Tekst alternatywny zawsze
 * z zalacznika, bo to on opisuje zawartosc zdjecia.
 *
 * @param int    $image_id Identyfikator zalacznika.
 * @param string $caption  'none', 'title' albo 'caption'.
 * @param array  $terms    Identyfikatory kategorii galerii, z ktorej pochodzi zdjecie.
 * @return array<string, mixed>|null Null, gdy zalacznik nie istnieje.
 */
function cyber_gallery_item( $image_id, $caption, $terms = array() ) {
	$image_id = absint( $image_id );
	$full     = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

	if ( ! $full ) {
		return null;
	}

	$text = '';

	if ( 'title' === $caption ) {
		$text = get_the_title( $image_id );
	} elseif ( 'caption' === $caption ) {
		$text = wp_get_attachment_caption( $image_id );
	}

	return array(
		'id'      => $image_id,
		'full'    => $full,
		'caption' => sanitize_text_field( (string) $text ),
		'terms'   => array_values( array_filter( array_map( 'absint', (array) $terms ) ) ),
	);
}

/**
 * Galerie wybrane w sekcji.
 *
 * Pusty wybor = wszystkie opublikowane galerie, opcjonalnie zawezone do
 * wskazanych kategorii.
 *
 * @param array $row Wiersz Flexible Content.
 * @return WP_Post[]
 */
function cyber_gallery_posts( array $row ) {
	$ids   = array_values( array_filter( array_map( 'absint', (array) ( isset( $row['cyber_gal_galleries'] ) ? $row['cyber_gal_galleries'] : array() ) ) ) );
	$terms = array_values( array_filter( array_map( 'absint', (array) ( isset( $row['cyber_gal_terms'] ) ? $row['cyber_gal_terms'] : array() ) ) ) );

	$query = array(
		'post_type'           => CYBER_GALLERY_TYPE,
		'post_status'         => 'publish',
		'posts_per_page'      => $ids ? count( $ids ) : 50,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $ids ) {
		$query['post__in'] = $ids;
		$query['orderby']  = 'post__in';
	} else {
		$query['orderby'] = 'date';
		$query['order']   = 'DESC';
	}

	if ( $terms ) {
		$query['tax_query'] = array(
			array(
				'taxonomy' => CYBER_GALLERY_TAX,
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		);
	}

	return get_posts( $query );
}

/**
 * Zdjecia sekcji razem z lista kategorii do filtrowania.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{items: array[], filters: array[]}
 */
function cyber_gallery_data( array $row ) {
	$caption = cyber_section_choice(
		isset( $row['cyber_gal_caption'] ) ? $row['cyber_gal_caption'] : 'none',
		array( 'none', 'title', 'caption' ),
		'none'
	);

	$items   = array();
	$filters = array();
	$source  = cyber_section_choice(
		isset( $row['cyber_gal_source'] ) ? $row['cyber_gal_source'] : 'images',
		array( 'images', 'galleries' ),
		'images'
	);

	if ( 'galleries' === $source ) {
		foreach ( cyber_gallery_posts( $row ) as $gallery ) {
			$terms = wp_get_post_terms( $gallery->ID, CYBER_GALLERY_TAX );

			if ( is_wp_error( $terms ) ) {
				$terms = array();
			}

			foreach ( $terms as $term ) {
				// Filtr pokazuje sie tylko dla kategorii, ktore maja zdjecia.
				$filters[ $term->term_id ] = array(
					'id'   => (int) $term->term_id,
					'name' => $term->name,
				);
			}

			foreach ( cyber_gallery_images( $gallery->ID ) as $image_id ) {
				$item = cyber_gallery_item( $image_id, $caption, wp_list_pluck( $terms, 'term_id' ) );

				if ( $item ) {
					$items[] = $item;
				}
			}
		}
	} else {
		$images = isset( $row['cyber_gal_images'] ) ? (array) $row['cyber_gal_images'] : array();

		foreach ( $images as $image_id ) {
			$item = cyber_gallery_item( $image_id, $caption );

			if ( $item ) {
				$items[] = $item;
			}
		}
	}

	$limit = absint( isset( $row['cyber_gal_limit'] ) ? $row['cyber_gal_limit'] : 0 );

	if ( $limit > 0 ) {
		$items = array_slice( $items, 0, min( 200, $limit ) );
	}

	// Filtry maja sens dopiero przy dwoch kategoriach i tylko w trybie galerii.
	$show_filters = 'galleries' === $source
		&& ( ! isset( $row['cyber_gal_filters'] ) || ! empty( $row['cyber_gal_filters'] ) )
		&& count( $filters ) > 1;

	return array(
		'items'   => $items,
		'filters' => $show_filters ? array_values( $filters ) : array(),
	);
}

/**
 * Klasy i zmienne CSS siatki galerii.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string, lightbox: bool}
 */
function cyber_gallery_attributes( array $row ) {
	$classes  = array( 'cyber-gallery' );
	$lightbox = ! isset( $row['cyber_gal_lightbox'] ) || ! empty( $row['cyber_gal_lightbox'] );

	$vars = array(
		'--cyber-gallery-cols'   => cyber_cards_column_count( isset( $row['cyber_gal_columns'] ) ? $row['cyber_gal_columns'] : 4, cyber_cards_columns(), 4 ),
		'--cyber-gallery-cols-t' => cyber_cards_column_count( isset( $row['cyber_gal_columns_tablet'] ) ? $row['cyber_gal_columns_tablet'] : 3, cyber_cards_columns_tablet(), 3 ),
		'--cyber-gallery-cols-m' => cyber_cards_column_count( isset( $row['cyber_gal_columns_mobile'] ) ? $row['cyber_gal_columns_mobile'] : 2, array( 1, 2, 3 ), 2 ),
		'--cyber-gallery-gap'    => sprintf( '%dpx', cyber_section_spacing( isset( $row['cyber_gal_gap'] ) ? $row['cyber_gal_gap'] : 12 ) ),
		'--cyber-gallery-radius' => sprintf( '%dpx', min( 40, absint( isset( $row['cyber_gal_radius'] ) ? $row['cyber_gal_radius'] : 0 ) ) ),
	);

	$ratio = cyber_section_choice(
		isset( $row['cyber_gal_ratio'] ) ? $row['cyber_gal_ratio'] : '4-3',
		cyber_cards_image_ratios(),
		'4-3'
	);

	if ( 'auto' !== $ratio ) {
		$classes[] = 'cyber-gallery--ratio-' . $ratio;
	}

	if ( ! isset( $row['cyber_gal_zoom'] ) || ! empty( $row['cyber_gal_zoom'] ) ) {
		$classes[] = 'cyber-gallery--zoom';
	}

	if ( $lightbox ) {
		$classes[] = 'cyber-gallery--lightbox';
	}

	$colors = array(
		'--cyber-gallery-filter-bg'           => 'cyber_gal_filter_bg',
		'--cyber-gallery-filter-color'        => 'cyber_gal_filter_color',
		'--cyber-gallery-filter-active-bg'    => 'cyber_gal_filter_active_bg',
		'--cyber-gallery-filter-active-color' => 'cyber_gal_filter_active_color',
	);

	$vars = array_merge( $vars, cyber_row_colors( $row, $colors ) );

	$style = cyber_css_declarations( $vars );

	return array(
		'class'    => implode( ' ', $classes ),
		'style'    => $style,
		'lightbox' => $lightbox,
	);
}

/**
 * Czy wpis ma sekcje Galeria — wtedy laduja sie assety.
 *
 * Z rozwinietymi sekcjami globalnymi, tak jak Swiper i licznik.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return bool
 */
function cyber_gallery_on_page( $post_id ) {
	foreach ( cyber_section_rows_expanded( $post_id ) as $row ) {
		if ( isset( $row['acf_fc_layout'] ) && 'gallery' === $row['acf_fc_layout'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Styl i skrypt galerii — tylko tam, gdzie galeria faktycznie jest.
 *
 * Pojedyncza galeria (CPT) ma siatke zdjec w szablonie, wiec assety ida tam
 * zawsze; na pozostalych stronach decyduje obecnosc sekcji.
 *
 * @return void
 */
function cyber_gallery_assets() {
	if ( ! is_singular() ) {
		return;
	}

	if ( ! is_singular( CYBER_GALLERY_TYPE ) && ! cyber_gallery_on_page( get_queried_object_id() ) ) {
		return;
	}

	wp_enqueue_style(
		'cyber-gallery',
		CYBER_URI . '/assets/css/gallery.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/gallery.css' )
	);

	wp_enqueue_script(
		'cyber-gallery',
		CYBER_URI . '/assets/js/gallery.js',
		array(),
		cyber_asset_version( 'assets/js/gallery.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'cyber-gallery',
		'cyberGallery',
		array(
			'close' => __( 'Zamknij', 'cyber-framework' ),
			'prev'  => __( 'Poprzednie zdjecie', 'cyber-framework' ),
			'next'  => __( 'Nastepne zdjecie', 'cyber-framework' ),
			'of'    => __( 'z', 'cyber-framework' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_gallery_assets', 20 );
