<?php
/**
 * Sekcja "Licznik" (counter) — liczby z opisem, odliczane od zera.
 *
 * LICZBA JEST W HTML OD POCZATKU. Strona bez JavaScriptu, wyszukiwarka
 * i czytnik ekranu dostaja wartosc koncowa. Skrypt (assets/js/counter.js)
 * dopiero przy wejsciu sekcji na ekran cofa ja do zera i odlicza w gore —
 * bez biblioteki: IntersectionObserver + requestAnimationFrame to kilkadziesiat
 * linii, a gotowa biblioteka byloby kolejnym wpisem w rejestrze (CLAUDE.md
 * sekcja 2) za to samo.
 *
 * Czytnik ekranu czyta tekst ukryty wizualnie z wartoscia koncowa; animowana
 * liczba ma aria-hidden, zeby nie odczytywac posrednich wartosci.
 *
 * LINIA PIONOWA miedzy kolumnami dziala przy dowolnej liczbie kolumn i na
 * kazdym breakpoincie bez znajomosci tej liczby w CSS: kazdy element ma linie
 * po lewej, w polowie odstepu, a kontener przycina to, co wystaje — czyli linie
 * pierwszej kolumny kazdego wiersza.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liczniki po normalizacji.
 *
 * Liczba decyduje o liczbie miejsc po przecinku: 4.5 animuje sie z jednym
 * miejscem, 1000 bez. Element bez liczby jest pomijany.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<int, array<string, mixed>>
 */
function cyber_counter_items( array $row ) {
	$items = isset( $row['cyber_counter_items'] ) ? $row['cyber_counter_items'] : array();
	$out   = array();

	foreach ( is_array( $items ) ? $items : array() as $item ) {
		if ( ! is_array( $item ) || ! isset( $item['cyber_counter_number'] ) || ! is_numeric( $item['cyber_counter_number'] ) ) {
			continue;
		}

		$raw      = (string) $item['cyber_counter_number'];
		$number   = max( -1e12, min( 1e12, (float) $raw ) );
		$decimals = false !== strpos( $raw, '.' ) ? min( 3, strlen( rtrim( substr( $raw, strpos( $raw, '.' ) + 1 ), '0' ) ) ) : 0;

		$out[] = array(
			'number'   => $number,
			'decimals' => $decimals,
			'prefix'   => isset( $item['cyber_counter_prefix'] ) ? sanitize_text_field( (string) $item['cyber_counter_prefix'] ) : '',
			'suffix'   => isset( $item['cyber_counter_suffix'] ) ? sanitize_text_field( (string) $item['cyber_counter_suffix'] ) : '',
			'title'    => isset( $item['cyber_counter_title'] ) ? sanitize_text_field( (string) $item['cyber_counter_title'] ) : '',
			'label'    => isset( $item['cyber_counter_label'] ) ? sanitize_text_field( (string) $item['cyber_counter_label'] ) : '',
			'icon'     => absint( isset( $item['cyber_counter_icon'] ) ? $item['cyber_counter_icon'] : 0 ),
		);
	}

	return $out;
}

/**
 * Formatuje liczbe tak samo jak skrypt: przecinek dziesietny, opcjonalnie
 * twarda spacja jako separator tysiecy.
 *
 * @param float $number    Liczba.
 * @param int   $decimals  Miejsca po przecinku.
 * @param bool  $thousands Czy grupowac tysiace.
 * @return string
 */
function cyber_counter_format( $number, $decimals, $thousands ) {
	return number_format( (float) $number, (int) $decimals, ',', $thousands ? "\u{00A0}" : '' );
}

/**
 * Konfiguracja sekcji po walidacji.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<string, mixed>
 */
function cyber_counter_config( array $row ) {
	$duration = isset( $row['cyber_counter_duration'] ) ? absint( $row['cyber_counter_duration'] ) : 2000;

	return array(
		'animate'   => ! isset( $row['cyber_counter_animate'] ) || ! empty( $row['cyber_counter_animate'] ),
		'duration'  => max( 300, min( 10000, $duration ? $duration : 2000 ) ),
		'thousands' => ! empty( $row['cyber_counter_thousands'] ),
	);
}

/**
 * Klasy i zmienne CSS kontenera licznikow.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string}
 */
function cyber_counter_attributes( array $row ) {
	$align   = cyber_section_choice( isset( $row['cyber_counter_align'] ) ? $row['cyber_counter_align'] : 'center', cyber_alignments(), 'center' );
	$classes = array( 'cyber-counter', 'cyber-counter--' . $align );
	$vars    = array(
		'--cyber-counter-cols'   => cyber_cards_column_count( isset( $row['cyber_counter_columns'] ) ? $row['cyber_counter_columns'] : 4, cyber_cards_columns(), 4 ),
		'--cyber-counter-cols-t' => cyber_cards_column_count( isset( $row['cyber_counter_columns_tablet'] ) ? $row['cyber_counter_columns_tablet'] : 2, cyber_cards_columns_tablet(), 2 ),
		'--cyber-counter-cols-m' => cyber_cards_column_count( isset( $row['cyber_counter_columns_mobile'] ) ? $row['cyber_counter_columns_mobile'] : 1, cyber_cards_columns_mobile(), 1 ),
	);

	$spacings = array(
		'--cyber-counter-gap-x'     => array( 'cyber_counter_gap_x', 24 ),
		'--cyber-counter-gap-y'     => array( 'cyber_counter_gap_y', 48 ),
		'--cyber-counter-gap-label' => array( 'cyber_counter_gap_label', 12 ),
	);

	foreach ( $spacings as $var => $definition ) {
		list( $key, $default ) = $definition;

		$vars[ $var ] = sprintf( '%dpx', cyber_section_spacing( isset( $row[ $key ] ) ? $row[ $key ] : $default ) );
	}

	if ( ! isset( $row['cyber_counter_divider'] ) || ! empty( $row['cyber_counter_divider'] ) ) {
		$classes[] = 'cyber-counter--divider';

		$divider = cyber_sanitize_color( isset( $row['cyber_counter_divider_color'] ) ? $row['cyber_counter_divider_color'] : '' );

		if ( '' !== $divider ) {
			$vars['--cyber-counter-divider'] = $divider;
		}
	}

	$vars['--cyber-counter-size'] = sprintf(
		'var(--cyber-font-size-%s)',
		cyber_section_choice(
			isset( $row['cyber_counter_number_size'] ) ? $row['cyber_counter_number_size'] : 'h1',
			cyber_cards_title_sizes(),
			'h1'
		)
	);

	// Tytul: odwolanie do globalnej wielkosci naglowka, jak tytul karty.
	$vars['--cyber-counter-title-size'] = sprintf(
		'var(--cyber-font-size-%s)',
		cyber_section_choice(
			isset( $row['cyber_counter_title_size'] ) ? $row['cyber_counter_title_size'] : 'h5',
			cyber_cards_title_sizes(),
			'h5'
		)
	);

	$weight = isset( $row['cyber_counter_number_weight'] ) ? (string) $row['cyber_counter_number_weight'] : '';

	if ( in_array( $weight, array( '300', '400', '500', '600', '700', '800', '900' ), true ) ) {
		$vars['--cyber-counter-weight'] = $weight;
	}

	$colors = array(
		'--cyber-counter-color'       => 'cyber_counter_number_color',
		'--cyber-counter-label-color' => 'cyber_counter_label_color',
	);

	foreach ( $colors as $var => $key ) {
		$value = cyber_sanitize_color( isset( $row[ $key ] ) ? $row[ $key ] : '' );

		if ( '' !== $value ) {
			$vars[ $var ] = $value;
		}
	}

	$style = '';

	foreach ( $vars as $name => $value ) {
		$style .= sprintf( '%1$s:%2$s;', $name, $value );
	}

	return array(
		'class' => implode( ' ', $classes ),
		'style' => $style,
	);
}

/**
 * Czy wpis ma licznik z animacja — wtedy i tylko wtedy laduje sie skrypt.
 *
 * Z rozwinietymi sekcjami globalnymi, tak jak Swiper.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return bool
 */
function cyber_counter_needs_script( $post_id ) {
	foreach ( cyber_section_rows_expanded( $post_id ) as $row ) {
		if ( isset( $row['acf_fc_layout'] ) && 'counter' === $row['acf_fc_layout'] && cyber_counter_config( $row )['animate'] ) {
			return true;
		}
	}

	return false;
}

/**
 * Skrypt licznika — tylko na wpisie z animowanym licznikiem (CLAUDE.md sekcja 10).
 *
 * @return void
 */
function cyber_counter_assets() {
	if ( ! is_singular() || ! cyber_counter_needs_script( get_queried_object_id() ) ) {
		return;
	}

	wp_enqueue_script(
		'cyber-counter',
		CYBER_URI . '/assets/js/counter.js',
		array(),
		cyber_asset_version( 'assets/js/counter.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_counter_assets', 20 );
