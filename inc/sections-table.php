<?php
/**
 * Sekcja "Tabela" — porownanie w wierszach i kolumnach.
 *
 * KOLUMNY SA DEFINIOWANE OSOBNO. Repeater "Kolumny" opisuje kazda kolumne raz:
 * naglowek, szerokosc i wyrownanie. Liczba kolumn tabeli = liczba pozycji na
 * tej liscie (maks. 6), wiec nie da sie jej rozjechac z trescia. Wiersze maja
 * juz tylko komorki.
 *
 * Pusta lista kolumn to nadal poprawny stan: liczba kolumn wynika wtedy
 * z najdalszej wypelnionej komorki, a tabela nie ma wiersza naglowkowego.
 *
 * Semantyka: <table> z <thead> (wiersz naglowkowy) i <th scope="row">
 * (etykiety wierszy) — czytnik ekranu odczytuje komorke razem z naglowkami.
 * Na waskim ekranie tabela przewija sie poziomo w kontenerze z fokusem
 * z klawiatury, zamiast sciskac kolumny do nieczytelnosci.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Definicje kolumn po normalizacji.
 *
 * Szerokosc 0 (albo puste pole) znaczy "dobierz sama" — kolumna nie trafia
 * wtedy do <colgroup>. Wyrownanie 'inherit' bierze ustawienie calej tabeli.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<int, array{label: string, width: int, align: string}>
 */
function cyber_table_columns( array $row ) {
	$source = isset( $row['cyber_table_columns'] ) && is_array( $row['cyber_table_columns'] ) ? $row['cyber_table_columns'] : array();
	$out    = array();

	foreach ( array_slice( $source, 0, 6 ) as $column ) {
		if ( ! is_array( $column ) ) {
			continue;
		}

		$out[] = array(
			'label' => isset( $column['cyber_table_col_label'] ) ? trim( (string) $column['cyber_table_col_label'] ) : '',
			'width' => min( 100, absint( isset( $column['cyber_table_col_width'] ) ? $column['cyber_table_col_width'] : 0 ) ),
			'align' => cyber_section_choice(
				isset( $column['cyber_table_col_align'] ) ? $column['cyber_table_col_align'] : 'inherit',
				array_merge( array( 'inherit' ), cyber_alignments() ),
				'inherit'
			),
		);
	}

	return $out;
}

/**
 * Wiersze tabeli po normalizacji.
 *
 * Liczba kolumn pochodzi z definicji kolumn; bez nich — z najdalszej
 * wypelnionej komorki. Wiersze sa przycinane albo dopelniane pustymi
 * komorkami, zeby kazdy mial tyle samo komorek co naglowek.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{rows: array<int, string[]>, columns: int}
 */
function cyber_table_data( array $row ) {
	$source  = isset( $row['cyber_table_rows'] ) && is_array( $row['cyber_table_rows'] ) ? $row['cyber_table_rows'] : array();
	$rows    = array();
	$columns = 0;

	foreach ( $source as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$cells = array();

		for ( $i = 1; $i <= 6; $i++ ) {
			$value   = isset( $item[ 'cyber_table_cell_' . $i ] ) ? trim( (string) $item[ 'cyber_table_cell_' . $i ] ) : '';
			$cells[] = $value;

			if ( '' !== $value ) {
				$columns = max( $columns, $i );
			}
		}

		// Calkiem pusty wiersz repeatera nie jest wierszem tabeli.
		if ( '' !== implode( '', $cells ) ) {
			$rows[] = $cells;
		}
	}

	$defined = count( cyber_table_columns( $row ) );

	if ( $defined > 0 ) {
		$columns = $defined;
	}

	$columns = max( 1, $columns );

	foreach ( $rows as $i => $cells ) {
		$rows[ $i ] = array_pad( array_slice( $cells, 0, $columns ), $columns, '' );
	}

	return array(
		'rows'    => $rows,
		'columns' => $columns,
	);
}

/**
 * Klasa wyrownania komorki dla kolumny o danym numerze.
 *
 * Wyrownanie zmienia jedna wlasciwosc, ale musi trafic na KAZDA komorke:
 * <col> w CSS przyjmuje tylko obramowanie, tlo, szerokosc i widocznosc —
 * text-align z kolumny nie dziala.
 *
 * @param array $columns Wynik cyber_table_columns().
 * @param int   $index   Numer kolumny liczony od zera.
 * @return string Klasa albo pusty string dla 'inherit'.
 */
function cyber_table_cell_class( array $columns, $index ) {
	if ( ! isset( $columns[ $index ] ) || 'inherit' === $columns[ $index ]['align'] ) {
		return '';
	}

	return ' cyber-table__cell--' . $columns[ $index ]['align'];
}

/**
 * Tresc komorki: tekst z polem textarea (new_lines = br).
 *
 * ACF oddaje <br />, reszta znacznikow jest odrzucana.
 *
 * @param string $value Wartosc komorki.
 * @return string Bezpieczny HTML.
 */
function cyber_table_cell( $value ) {
	return wp_kses( (string) $value, array( 'br' => array() ) );
}

/**
 * Klasy i zmienne CSS tabeli.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string, head_col: bool}
 */
function cyber_table_attributes( array $row ) {
	$sizes   = array( 'text', 'h6', 'h5', 'h4', 'h3' );
	$weights = array( '300', '400', '500', '600', '700', '800' );
	$align   = cyber_section_choice( isset( $row['cyber_table_align'] ) ? $row['cyber_table_align'] : 'center', cyber_alignments(), 'center' );
	$classes = array( 'cyber-table', 'cyber-table--' . $align );
	$vars    = array();

	$font = function ( $key, $default ) use ( $row, $sizes ) {
		$size = cyber_section_choice( isset( $row[ $key ] ) ? $row[ $key ] : $default, $sizes, $default );

		return sprintf( 'var(--cyber-font-size-%s)', $size );
	};

	$weight = function ( $key, $default ) use ( $row, $weights ) {
		$value = isset( $row[ $key ] ) ? (string) $row[ $key ] : $default;

		return in_array( $value, $weights, true ) ? $value : $default;
	};

	$vars['--cyber-table-head-size']    = $font( 'cyber_table_head_size', 'text' );
	$vars['--cyber-table-head-weight']  = $weight( 'cyber_table_head_weight', '600' );
	$vars['--cyber-table-label-size']   = $font( 'cyber_table_label_size', 'text' );
	$vars['--cyber-table-label-weight'] = $weight( 'cyber_table_label_weight', '400' );
	$vars['--cyber-table-cell-size']    = $font( 'cyber_table_cell_size', 'h5' );
	$vars['--cyber-table-cell-weight']  = $weight( 'cyber_table_cell_weight', '600' );
	$vars['--cyber-table-pad-y']        = sprintf( '%dpx', cyber_section_spacing( isset( $row['cyber_table_pad_y'] ) ? $row['cyber_table_pad_y'] : 36 ) );
	$vars['--cyber-table-pad-x']        = sprintf( '%dpx', cyber_section_spacing( isset( $row['cyber_table_pad_x'] ) ? $row['cyber_table_pad_x'] : 24 ) );
	$vars['--cyber-table-radius']       = sprintf( '%dpx', min( 40, absint( isset( $row['cyber_table_radius'] ) ? $row['cyber_table_radius'] : 8 ) ) );

	$min_width = min( 2000, absint( isset( $row['cyber_table_min_width'] ) ? $row['cyber_table_min_width'] : 640 ) );

	if ( $min_width > 0 ) {
		$vars['--cyber-table-min-width'] = sprintf( '%dpx', $min_width );
	}

	$colors = array(
		'--cyber-table-bg'        => 'cyber_table_bg',
		'--cyber-table-head-bg'   => 'cyber_table_head_bg',
		'--cyber-table-border'    => 'cyber_table_border',
		'--cyber-table-stripe-bg' => 'cyber_table_stripe_bg',
		'--cyber-table-color'     => 'cyber_table_color',
	);

	foreach ( $colors as $var => $key ) {
		$value = cyber_sanitize_color( isset( $row[ $key ] ) ? $row[ $key ] : '' );

		if ( '' !== $value ) {
			$vars[ $var ] = $value;
		}
	}

	if ( ! empty( $row['cyber_table_stripes'] ) ) {
		$classes[] = 'cyber-table--stripes';
	}

	$style = '';

	foreach ( $vars as $name => $value ) {
		$style .= sprintf( '%1$s:%2$s;', $name, $value );
	}

	return array(
		'class'    => implode( ' ', $classes ),
		'style'    => $style,
		'head_col' => ! isset( $row['cyber_table_head_col'] ) || ! empty( $row['cyber_table_head_col'] ),
	);
}

/**
 * Skrypt panelu: liczba pol komorek w wierszu idzie za liczba kolumn.
 *
 * Warunek ACF porownuje wartosc pola, a tutaj chodzi o liczbe wierszy
 * repeatera "Kolumny" — tego conditional logic nie potrafi. Skrypt laduje sie
 * wylacznie tam, gdzie ACF rysuje swoj formularz.
 *
 * @return void
 */
function cyber_table_admin_assets() {
	wp_enqueue_script(
		'cyber-admin-sections',
		CYBER_URI . '/assets/js/admin-sections.js',
		array( 'acf-input' ),
		cyber_asset_version( 'assets/js/admin-sections.js' ),
		true
	);
}
add_action( 'acf/input/admin_enqueue_scripts', 'cyber_table_admin_assets' );
