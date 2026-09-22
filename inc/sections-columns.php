<?php
/**
 * Sekcja "Kolumny tekstowe" (WYSIWYG).
 *
 * Od jednej do czterech kolumn, kazda z wlasnym polem WYSIWYG, zawsze w jednym
 * wierszu na desktopie. Proporcje wybiera sie z zamknietej listy zaleznej od
 * liczby kolumn.
 *
 * PODZIAL ODPOWIEDZIALNOSCI — ten sam co przy kartach. Opakowanie sekcji i tlo
 * calej sekcji obsluguje inc/sections.php. Ten modul zajmuje sie WYLACZNIE
 * wierszem kolumn.
 *
 * Jako jedyna sekcja nie ma WYSIWYG nad i pod trescia — kolumny same sa polami
 * WYSIWYG, wiec dodatkowe pola tresci bylyby powtorzeniem.
 *
 * PROPORCJE TO fr, NIE PROCENTY. Kolumny 40% + 60% plus odstep miedzy nimi
 * daja wiecej niz 100% i wiersz wyjezdza poza kontener. Jednostka fr dzieli
 * miejsce, ktore ZOSTAJE po odjeciu odstepow, wiec 2fr 3fr zachowuje proporcje
 * 40/60 przy kazdym odstepie. Kazda sciezka idzie jako minmax(0, Nfr), zeby
 * dlugie slowo albo szeroki obrazek nie rozpychaly kolumny ponad przydzial.
 *
 * RESPONSYWNOSC JEST AUTOMATYCZNA, bez pol w panelu:
 *
 *   liczba kolumn | desktop          | tablet (<=980px)   | mobile (<=767px)
 *   --------------+------------------+--------------------+-----------------
 *   1             | 100%             | 100%               | 100%
 *   2             | wybrana proporcja| wybrana proporcja  | jedna pod druga
 *   3             | wybrana proporcja| trzy rowne         | jedna pod druga
 *   4             | cztery rowne     | dwie na dwie       | jedna pod druga
 *
 * Trzy kolumny na tablecie ida w rowne, bo proporcja 20/60/20 przy szerokosci
 * ok. 900px zostawia boczne kolumny na ok. 170px — za malo na akapit tekstu.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dozwolone uklady kolumn.
 *
 * Klucz = wartosc pola w panelu, zapisywana w bazie, wiec jest kontraktem tak
 * samo jak klucz layoutu. Wartosc = sciezki siatki na desktopie.
 *
 * @return array<int, array<string, string>> Liczba kolumn => uklad => sciezki.
 */
function cyber_columns_layouts() {
	return array(
		1 => array(
			'100' => 'minmax(0, 1fr)',
		),
		2 => array(
			'50-50' => 'minmax(0, 1fr) minmax(0, 1fr)',
			'40-60' => 'minmax(0, 2fr) minmax(0, 3fr)',
			'60-40' => 'minmax(0, 3fr) minmax(0, 2fr)',
		),
		3 => array(
			'33-33-33' => 'minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr)',
			'20-60-20' => 'minmax(0, 1fr) minmax(0, 3fr) minmax(0, 1fr)',
			'40-20-40' => 'minmax(0, 2fr) minmax(0, 1fr) minmax(0, 2fr)',
		),
		4 => array(
			'25-25-25-25' => 'repeat(4, minmax(0, 1fr))',
		),
	);
}

/**
 * Sciezki siatki na tablecie, zalezne wylacznie od liczby kolumn.
 *
 * Dwie kolumny dostaja null, czyli zostaja przy proporcji z desktopu —
 * obsluguje to lancuch zapasowy var() w arkuszu.
 *
 * @param int $count Liczba kolumn.
 * @return string|null Sciezki albo null, gdy tablet dziedziczy desktop.
 */
function cyber_columns_tablet_template( $count ) {
	$map = array(
		1 => 'minmax(0, 1fr)',
		2 => null,
		3 => 'repeat(3, minmax(0, 1fr))',
		4 => 'repeat(2, minmax(0, 1fr))',
	);

	return isset( $map[ $count ] ) ? $map[ $count ] : null;
}

/**
 * Liczba kolumn i wybrany uklad, po walidacji.
 *
 * Proporcje dla dwoch i trzech kolumn siedza w dwoch osobnych polach, bo zestaw
 * dozwolonych wartosci zalezy od liczby kolumn. Pole przy innej liczbie kolumn
 * jest w panelu ukryte, ale jego wartosc zostaje w bazie — dlatego czytamy
 * wylacznie pole pasujace do biezacej liczby.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{count: int, layout: string, template: string}
 */
function cyber_columns_resolve( array $row ) {
	$layouts = cyber_columns_layouts();

	$count = isset( $row['cyber_columns_count'] ) ? (int) $row['cyber_columns_count'] : 2;

	if ( ! isset( $layouts[ $count ] ) ) {
		$count = 2;
	}

	$allowed = $layouts[ $count ];
	$key     = 'cyber_columns_layout_' . $count;
	$layout  = isset( $row[ $key ] ) ? (string) $row[ $key ] : '';

	// Klucze w rodzaju '100' PHP zamienia na int — porownujemy jako stringi.
	if ( ! in_array( $layout, array_map( 'strval', array_keys( $allowed ) ), true ) ) {
		$layout = (string) key( $allowed );
	}

	return array(
		'count'    => $count,
		'layout'   => $layout,
		'template' => $allowed[ $layout ],
	);
}

/**
 * Klasy i zmienne CSS wiersza kolumn.
 *
 * Ustawienia wygladu sa wspolne dla wszystkich kolumn sekcji. Zmienne siedza
 * na kontenerze, a kolumny je dziedzicza — nie trzeba powtarzac ich na kazdej.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string}
 */
function cyber_columns_attributes( array $row ) {
	$resolved = cyber_columns_resolve( $row );
	$classes  = array(
		'cyber-columns',
		'cyber-columns--' . $resolved['count'],
	);

	$vars = array(
		'--cyber-columns-template' => $resolved['template'],
	);

	$tablet = cyber_columns_tablet_template( $resolved['count'] );

	if ( null !== $tablet ) {
		$vars['--cyber-columns-template-t'] = $tablet;
	}

	$spacings = array(
		'--cyber-columns-gap-x' => 'cyber_columns_gap_x',
		'--cyber-columns-gap-y' => 'cyber_columns_gap_y',
		'--cyber-columns-pad-x' => 'cyber_columns_pad_x',
		'--cyber-columns-pad-y' => 'cyber_columns_pad_y',
	);

	foreach ( $spacings as $var => $key ) {
		$vars[ $var ] = sprintf(
			'%dpx',
			cyber_section_spacing( isset( $row[ $key ] ) ? $row[ $key ] : 0 )
		);
	}

	$bg = cyber_sanitize_color( isset( $row['cyber_columns_bg'] ) ? $row['cyber_columns_bg'] : '' );

	if ( '' !== $bg ) {
		$vars['--cyber-columns-bg'] = $bg;
	}

	$bg_hover = cyber_sanitize_color( isset( $row['cyber_columns_bg_hover'] ) ? $row['cyber_columns_bg_hover'] : '' );

	if ( '' !== $bg_hover ) {
		$vars['--cyber-columns-bg-hover'] = $bg_hover;
		$classes[]                        = 'cyber-columns--hover';
	}

	$image = cyber_section_image_url( isset( $row['cyber_columns_bg_image'] ) ? $row['cyber_columns_bg_image'] : 0 );

	if ( '' !== $image ) {
		$vars['--cyber-columns-bg-image'] = sprintf( 'url(%s)', $image );

		$vars['--cyber-columns-bg-size'] = cyber_section_choice(
			isset( $row['cyber_columns_bg_size'] ) ? $row['cyber_columns_bg_size'] : 'cover',
			cyber_section_bg_sizes(),
			'cover'
		);

		$vars['--cyber-columns-bg-position'] = sprintf(
			'%1$s %2$s',
			cyber_section_choice(
				isset( $row['cyber_columns_bg_position_x'] ) ? $row['cyber_columns_bg_position_x'] : 'center',
				cyber_section_bg_positions_x(),
				'center'
			),
			cyber_section_choice(
				isset( $row['cyber_columns_bg_position_y'] ) ? $row['cyber_columns_bg_position_y'] : 'center',
				cyber_section_bg_positions_y(),
				'center'
			)
		);

		$vars['--cyber-columns-bg-repeat'] = empty( $row['cyber_columns_bg_repeat'] ) ? 'no-repeat' : 'repeat';
	}

	/*
	 * Obramowanie: wylacznik w panelu, barwa z zakladki Kolory, grubosc na
	 * sztywno w arkuszu — ten sam wzorzec co w kartach.
	 */
	if ( ! empty( $row['cyber_columns_border'] ) ) {
		$classes[] = 'cyber-columns--border';
	}

	$radius = absint( isset( $row['cyber_columns_radius'] ) ? $row['cyber_columns_radius'] : 0 );

	if ( $radius > 0 ) {
		$vars['--cyber-columns-radius'] = sprintf( '%dpx', min( $radius, 200 ) );
	}

	$style = cyber_css_declarations( $vars );

	return array(
		'class' => implode( ' ', $classes ),
		'style' => $style,
	);
}

/**
 * Tresc kolumn w liczbie wynikajacej z ustawienia.
 *
 * Pusta kolumna NIE jest pomijana — w przeciwienstwie do pustej karty. Kolumny
 * tworza uklad: przy 20/60/20 z pustym lewym polem pominiecie go przesunelo by
 * srodkowa tresc na lewa krawedz i zepsulo proporcje, o ktore redaktorowi
 * chodzilo. Pole wolne od tresci zostaje pustym miejscem.
 *
 * Pola kolumn ponad wybrana liczbe moga miec tresc w bazie (redaktor zmniejszyl
 * liczbe kolumn), ale nie sa renderowane.
 *
 * @param array $row Wiersz Flexible Content.
 * @return string[] Tresc kolumn, gotowa do przepuszczenia przez cyber_kses_content().
 */
function cyber_columns_items( array $row ) {
	$resolved = cyber_columns_resolve( $row );
	$items    = array();

	for ( $i = 1; $i <= $resolved['count']; $i++ ) {
		$key     = 'cyber_columns_' . $i;
		$items[] = isset( $row[ $key ] ) ? (string) $row[ $key ] : '';
	}

	return $items;
}
