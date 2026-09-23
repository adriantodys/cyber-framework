<?php
/**
 * Sekcje globalne.
 *
 * Sekcja zdefiniowana RAZ i wstawiana na wiele stron. Przyklad: ta sama
 * karuzela realizacji na pieciu stronach — zdjecia zmienia sie w jednym
 * miejscu, a wszystkie strony widza zmiane od razu.
 *
 * DWIE CZESCI:
 *
 *   1. CPT cyber_global_section — magazyn tresci, tylko w panelu. Wpis nie ma
 *      adresu na froncie, nie trafia do wyszukiwarki ani do mapy strony.
 *      Ma podpiete TO SAMO pole cyber_sections co strony (lokalizacja
 *      w group_sections.json), wiec edytuje sie go identycznie jak strone:
 *      te same layouty, zadnej drugiej kopii pol. Jeden wpis moze miec kilka
 *      sekcji — wstawia sie wtedy caly zestaw.
 *
 *   2. Layout "global" w polu cyber_sections — wybor wpisu z CPT. Renderer
 *      podmienia ten wiersz na sekcje wybranego wpisu i puszcza je przez ten
 *      sam kod, co zwykle sekcje strony.
 *
 * ZAGNIEZDZANIE JEST NIEMOZLIWE. Sekcja globalna nie moze wstawic innej
 * sekcji globalnej: w panelu CPT layout "global" jest ukryty, a przy odczycie
 * wiersze "global" z wpisu CPT sa pomijane. Druga bariera zostaje, bo wiersz
 * mogl trafic do bazy inna droga (import, update_field) — a A -> B -> A
 * zawiesiloby renderowanie strony.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slug typu tresci sekcji globalnych.
 *
 * Nie zmieniac: slug jest zapisany przy kazdym wpisie (post_type) i w lokalizacji
 * grupy pol. Zmiana odcina wszystkie istniejace sekcje globalne.
 */
const CYBER_GLOBAL_SECTION_TYPE = 'cyber_global_section';

/**
 * Nazwa layoutu Flexible Content, ktory wstawia sekcje globalna.
 */
const CYBER_GLOBAL_SECTION_LAYOUT = 'global';

/**
 * Rejestruje typ tresci sekcji globalnych.
 *
 * public = false i publicly_queryable = false: wpis nie ma wlasnej strony.
 * Pokazuje sie wylacznie jako czesc innej strony, wiec adres
 * /cyber_global_section/karuzela/ bylby zduplikowana, oderwana od kontekstu
 * trescia do zaindeksowania.
 *
 * capability_type 'page': sekcje globalne edytuje ten, kto moze edytowac
 * strony — ta sama rola, ktora dzis uklada sekcje na stronach.
 *
 * @return void
 */
function cyber_register_global_sections() {
	register_post_type(
		CYBER_GLOBAL_SECTION_TYPE,
		array(
			'labels'              => array(
				'name'               => __( 'Sekcje globalne', 'cyber-framework' ),
				'singular_name'      => __( 'Sekcja globalna', 'cyber-framework' ),
				'menu_name'          => __( 'Sekcje globalne', 'cyber-framework' ),
				'add_new'            => __( 'Dodaj nowa', 'cyber-framework' ),
				'add_new_item'       => __( 'Dodaj sekcje globalna', 'cyber-framework' ),
				'edit_item'          => __( 'Edytuj sekcje globalna', 'cyber-framework' ),
				'new_item'           => __( 'Nowa sekcja globalna', 'cyber-framework' ),
				'search_items'       => __( 'Szukaj sekcji globalnych', 'cyber-framework' ),
				'not_found'          => __( 'Brak sekcji globalnych.', 'cyber-framework' ),
				'not_found_in_trash' => __( 'Brak sekcji globalnych w koszu.', 'cyber-framework' ),
				'all_items'          => __( 'Wszystkie sekcje', 'cyber-framework' ),
			),
			'description'         => __( 'Sekcje wstawiane na wiele stron i edytowane w jednym miejscu.', 'cyber-framework' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'menu_position'       => 21,
			'menu_icon'           => 'dashicons-screenoptions',
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'revisions' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
		)
	);
}
add_action( 'init', 'cyber_register_global_sections' );

/* -------------------------------------------------------------------------- *
 * Odczyt
 * -------------------------------------------------------------------------- */

/**
 * Identyfikator sekcji globalnej wybranej w wierszu layoutu "global".
 *
 * @param array $row Wiersz Flexible Content.
 * @return int 0, gdy nic nie wybrano.
 */
function cyber_global_section_id( array $row ) {
	$value = isset( $row['cyber_global_section_ref'] ) ? $row['cyber_global_section_ref'] : 0;

	// Post Object z return_format 'id' zwraca liczbe; obiekt obslugujemy na wypadek przestawienia pola.
	if ( $value instanceof WP_Post ) {
		$value = $value->ID;
	}

	return absint( $value );
}

/**
 * Sekcje zapisane we wpisie sekcji globalnej.
 *
 * Zwraca wiersze TYLKO dla opublikowanego wpisu wlasciwego typu. Szkic, wpis
 * w koszu albo ID innego typu tresci daja pusta liste — strona renderuje sie
 * wtedy bez tej sekcji, zamiast pokazac niedokonczona tresc.
 *
 * Wiersze "global" sa pomijane (patrz naglowek pliku: brak zagniezdzania).
 * Wynik jest zapamietywany na czas zadania — ta sama sekcja wstawiona na
 * stronie dwa razy oraz sprawdzenie assetow nie czytaja bazy ponownie.
 *
 * @param int $id Identyfikator wpisu CPT.
 * @return array[] Wiersze Flexible Content.
 */
function cyber_global_section_rows( $id ) {
	static $cache = array();

	$id = absint( $id );

	if ( ! $id || ! cyber_is_acf_active() ) {
		return array();
	}

	if ( isset( $cache[ $id ] ) ) {
		return $cache[ $id ];
	}

	$post = get_post( $id );
	$rows = array();

	if ( $post && CYBER_GLOBAL_SECTION_TYPE === $post->post_type && 'publish' === $post->post_status ) {
		$raw = get_field( CYBER_SECTIONS_FIELD, $id );

		foreach ( is_array( $raw ) ? $raw : array() as $row ) {
			if ( is_array( $row ) && isset( $row['acf_fc_layout'] ) && CYBER_GLOBAL_SECTION_LAYOUT !== $row['acf_fc_layout'] ) {
				$rows[] = $row;
			}
		}
	}

	$cache[ $id ] = $rows;

	return $rows;
}

/**
 * Powod, dla ktorego sekcja globalna sie nie wyswietla.
 *
 * Tekst dla zalogowanego redaktora — gosc witryny nie dostaje go nigdy
 * (CLAUDE.md sekcja 2: trzy poziomy komunikatu).
 *
 * @param int $id Identyfikator wpisu CPT.
 * @return string Pusty, gdy sekcja jest w porzadku.
 */
function cyber_global_section_problem( $id ) {
	$post = $id ? get_post( $id ) : null;

	if ( ! $post || CYBER_GLOBAL_SECTION_TYPE !== $post->post_type ) {
		return __( 'Sekcja globalna: nie wybrano sekcji albo zostala usunieta na stale.', 'cyber-framework' );
	}

	$title = '' !== $post->post_title ? $post->post_title : __( '(bez tytulu)', 'cyber-framework' );

	if ( 'trash' === $post->post_status ) {
		/* translators: %s: tytul sekcji globalnej */
		return sprintf( __( 'Sekcja globalna "%s" jest w koszu i nie wyswietla sie.', 'cyber-framework' ), $title );
	}

	if ( 'publish' !== $post->post_status ) {
		/* translators: %s: tytul sekcji globalnej */
		return sprintf( __( 'Sekcja globalna "%s" nie jest opublikowana i nie wyswietla sie.', 'cyber-framework' ), $title );
	}

	if ( ! cyber_global_section_rows( $id ) ) {
		/* translators: %s: tytul sekcji globalnej */
		return sprintf( __( 'Sekcja globalna "%s" nie ma jeszcze zadnej sekcji.', 'cyber-framework' ), $title );
	}

	return '';
}

/**
 * Wiersze sekcji wpisu z rozwinietymi sekcjami globalnymi.
 *
 * Plaska lista tego, co faktycznie trafi na strone — do decyzji o assetach
 * (arkusz sekcji, Swiper). Bez tego karuzela wstawiona jako sekcja globalna
 * wyswietlilaby sie bez skryptu, bo sama strona nie ma wiersza "carousel".
 *
 * Wylaczone wiersze "global" sa pomijane razem z zawartoscia.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return array[] Wiersze Flexible Content.
 */
function cyber_section_rows_expanded( $post_id ) {
	if ( ! $post_id || ! cyber_is_acf_active() ) {
		return array();
	}

	/*
	 * Cache jak w cyber_global_section_rows() nizej. Cztery moduly pytaja
	 * o to samo drzewo na tym samym hooku (wp_enqueue_scripts, priorytet 20):
	 * arkusz sekcji, slider, licznik i galeria. Bez cache kazdy z nich
	 * przechodzil cale rozwiniecie od nowa.
	 */
	static $cache = array();

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$raw = get_field( CYBER_SECTIONS_FIELD, $post_id );
	$out = array();

	foreach ( is_array( $raw ) ? $raw : array() as $row ) {
		if ( ! is_array( $row ) || empty( $row['acf_fc_layout'] ) ) {
			continue;
		}

		/*
		 * Wylacznik sprawdzamy PRZED rozgalezieniem na sekcje globalna —
		 * dokladnie tak, jak robi to cyber_render_sections(). Wczesniej warunek
		 * stal za rozgalezieniem i obejmowal wylacznie wiersze "global", wiec
		 * zwykly wylaczony wiersz przechodzil tedy dalej. Skutek byl widoczny
		 * nie w tresci, tylko w sieci: odstawiony slider nie renderowal sie,
		 * ale nadal kolejkowal Swipera (~30 kB gzip).
		 */
		if ( ! cyber_section_row_enabled( $row ) ) {
			continue;
		}

		if ( CYBER_GLOBAL_SECTION_LAYOUT !== $row['acf_fc_layout'] ) {
			$out[] = $row;
			continue;
		}

		/*
		 * Wiersze wewnatrz sekcji globalnej filtrujemy tutaj, a NIE w
		 * cyber_global_section_rows(). Ta ostatnia ma trzeciego konsumenta —
		 * cyber_global_section_problem() — ktory z pustej listy wnioskuje
		 * "sekcja globalna nie ma jeszcze zadnej sekcji". Filtrowanie u zrodla
		 * kazaloby mu pokazac ten komunikat sekcji, ktora sekcje ma, tylko
		 * wylaczone.
		 */
		foreach ( cyber_global_section_rows( cyber_global_section_id( $row ) ) as $global_row ) {
			if ( cyber_section_row_enabled( $global_row ) ) {
				$out[] = $global_row;
			}
		}
	}

	$cache[ $post_id ] = $out;

	return $out;
}

/* -------------------------------------------------------------------------- *
 * Panel
 * -------------------------------------------------------------------------- */

/**
 * Ukrywa layout "global" w edycji sekcji globalnej.
 *
 * Filtr dziala na formularz, nie na dane: ACF przy zapisie czyta pelna
 * definicje pola, wiec wiersz "global", ktory mimo to trafilby do bazy,
 * nie zginie — tylko nie da sie go dodac z panelu, a przy odczycie jest
 * pomijany (cyber_global_section_rows()).
 *
 * @param array $field Pole Flexible Content przygotowywane do formularza.
 * @return array
 */
function cyber_global_section_hide_layout( $field ) {
	if ( CYBER_GLOBAL_SECTION_TYPE !== get_post_type() || empty( $field['layouts'] ) ) {
		return $field;
	}

	foreach ( $field['layouts'] as $key => $layout ) {
		if ( isset( $layout['name'] ) && CYBER_GLOBAL_SECTION_LAYOUT === $layout['name'] ) {
			unset( $field['layouts'][ $key ] );
		}
	}

	return $field;
}
add_filter( 'acf/prepare_field/key=field_cyber_sections', 'cyber_global_section_hide_layout' );

/**
 * Wpisy, ktore wstawiaja dana sekcje globalna.
 *
 * Szuka po meta: ACF zapisuje wybor jako cyber_sections_{N}_cyber_global_section_ref
 * = ID. Pomija rewizje, autoszkice, kosz i same sekcje globalne (zagniezdzony
 * wiersz "global" i tak sie nie renderuje, wiec nie jest uzyciem).
 *
 * Jedno zapytanie na wiersz listy — lista sekcji globalnych jest krotka,
 * a zapytanie idzie tylko w panelu.
 *
 * @param int $id Identyfikator wpisu CPT.
 * @return WP_Post[]
 */
function cyber_global_section_usage( $id ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- wyszukiwanie po wzorcu klucza meta, tylko w panelu.
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->postmeta} m
			INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE m.meta_key LIKE %s AND m.meta_value = %s
			AND p.post_type NOT IN ( 'revision', %s ) AND p.post_status NOT IN ( 'trash', 'auto-draft', 'inherit' )
			ORDER BY p.post_title ASC",
			$wpdb->esc_like( CYBER_SECTIONS_FIELD . '_' ) . '%' . $wpdb->esc_like( '_cyber_global_section_ref' ),
			(string) absint( $id ),
			CYBER_GLOBAL_SECTION_TYPE
		)
	);

	return array_filter( array_map( 'get_post', array_map( 'absint', $ids ) ) );
}

/**
 * Kolumna "Uzywana na" na liscie sekcji globalnych.
 *
 * Przed zmiana albo usunieciem sekcji widac, ktore strony sie zmienia.
 *
 * @param array $columns Kolumny listy.
 * @return array
 */
function cyber_global_section_columns( $columns ) {
	$out = array();

	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;

		if ( 'title' === $key ) {
			$out['cyber_usage'] = __( 'Uzywana na', 'cyber-framework' );
		}
	}

	return $out;
}
add_filter( 'manage_' . CYBER_GLOBAL_SECTION_TYPE . '_posts_columns', 'cyber_global_section_columns' );

/**
 * Tresc kolumny "Uzywana na".
 *
 * @param string $column  Klucz kolumny.
 * @param int    $post_id Identyfikator wpisu CPT.
 * @return void
 */
function cyber_global_section_column( $column, $post_id ) {
	if ( 'cyber_usage' !== $column ) {
		return;
	}

	$posts = cyber_global_section_usage( $post_id );

	if ( ! $posts ) {
		echo '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">' . esc_html__( 'Nieuzywana', 'cyber-framework' ) . '</span>';
		return;
	}

	$links = array();

	foreach ( $posts as $post ) {
		$title = '' !== $post->post_title ? $post->post_title : __( '(bez tytulu)', 'cyber-framework' );
		$url   = get_edit_post_link( $post->ID );

		$links[] = $url
			? sprintf( '<a href="%1$s">%2$s</a>', esc_url( $url ), esc_html( $title ) )
			: esc_html( $title );
	}

	echo wp_kses_post( implode( ', ', $links ) );
}
add_action( 'manage_' . CYBER_GLOBAL_SECTION_TYPE . '_posts_custom_column', 'cyber_global_section_column', 10, 2 );
