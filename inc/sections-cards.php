<?php
/**
 * Sekcja "Karty" (icon boxes).
 *
 * Klon sekcji podstawowej z wypelnionym srodkowym kontenerem: siatka
 * powtarzalnych elementow, kazdy ze zdjeciem, tytulem, tekstem i odnosnikiem.
 *
 * PODZIAL ODPOWIEDZIALNOSCI. Opakowanie sekcji, oba pola WYSIWYG i ustawienia
 * tla obsluguje inc/sections.php — ten modul zajmuje sie WYLACZNIE siatka.
 * Dzieki temu zmiana w opakowaniu nie wymaga dotykania zadnej sekcji, a nowa
 * sekcja z wlasna trescia dopisuje sie obok, nie zamiast.
 *
 * WYROWNANIE JEST KLASA, NIE ZMIENNA (CLAUDE.md sekcja 20): zmienia
 * text-align, a przy zdjeciu takze margin-inline, czyli wiecej niz jedna
 * wlasciwosc. Reszta ustawien to pojedyncze wartosci i idzie zmiennymi CSS.
 *
 * CZEGO TU NIE MA: pola "ilosc wierszy". Liczba wierszy siatki wynika z liczby
 * elementow podzielonej przez liczbe kolumn i nie da sie jej ustawic
 * niezaleznie — pole sterowaloby albo obcinaniem elementow, albo tworzeniem
 * pustych wierszy. Jesli kiedys bedzie potrzeba pokazac tylko czesc elementow,
 * to osobna funkcja ("pokaz pierwsze N"), a nie wiersze siatki.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dozwolone liczby kolumn na desktopie.
 *
 * @return int[]
 */
function cyber_cards_columns() {
	return array( 1, 2, 3, 4, 5, 6 );
}

/**
 * Dozwolone liczby kolumn na tablecie.
 *
 * @return int[]
 */
function cyber_cards_columns_tablet() {
	return array( 1, 2, 3, 4 );
}

/**
 * Dozwolone liczby kolumn na telefonie.
 *
 * @return int[]
 */
function cyber_cards_columns_mobile() {
	return array( 1, 2 );
}

/**
 * Dozwolone proporcje zdjecia karty ('auto' = bez przycinania).
 *
 * Myslnik zamiast dwukropka, bo wartosc trafia do nazwy klasy.
 *
 * @return string[]
 */
function cyber_cards_image_ratios() {
	return array( 'auto', '16-9', '3-2', '4-3', '1-1' );
}

/**
 * Dozwolone rozmiary tytulu karty — nazwy globalnych wielkosci naglowkow.
 *
 * @return string[]
 */
function cyber_cards_title_sizes() {
	return array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
}

/**
 * Sposoby prezentacji odnosnika w karcie.
 *
 * @return string[]
 */
function cyber_cards_link_styles() {
	return array( 'button', 'link' );
}

/**
 * Sprowadza liczbe kolumn do dozwolonej wartosci.
 *
 * @param mixed $value    Wartosc z pola.
 * @param int[] $allowed  Dozwolone wartosci.
 * @param int   $fallback Wartosc awaryjna.
 * @return int
 */
function cyber_cards_column_count( $value, array $allowed, $fallback ) {
	$value = (int) $value;

	return in_array( $value, $allowed, true ) ? $value : $fallback;
}

/**
 * Klasy i zmienne CSS kontenera siatki.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string}
 */
function cyber_cards_attributes( array $row ) {
	$classes = array( 'cyber-cards' );
	$vars    = array();

	// --- Siatka ---------------------------------------------------------
	$vars['--cyber-cards-cols'] = cyber_cards_column_count(
		isset( $row['cyber_cards_columns'] ) ? $row['cyber_cards_columns'] : 3,
		cyber_cards_columns(),
		3
	);

	$vars['--cyber-cards-cols-t'] = cyber_cards_column_count(
		isset( $row['cyber_cards_columns_tablet'] ) ? $row['cyber_cards_columns_tablet'] : 2,
		cyber_cards_columns_tablet(),
		2
	);

	$vars['--cyber-cards-cols-m'] = cyber_cards_column_count(
		isset( $row['cyber_cards_columns_mobile'] ) ? $row['cyber_cards_columns_mobile'] : 1,
		cyber_cards_columns_mobile(),
		1
	);

	$spacings = array(
		'--cyber-cards-gap-x'    => 'cyber_cards_gap_x',
		'--cyber-cards-gap-y'    => 'cyber_cards_gap_y',
		'--cyber-cards-pad-x'    => 'cyber_cards_pad_x',
		'--cyber-cards-pad-y'    => 'cyber_cards_pad_y',
		'--cyber-cards-gap-media' => 'cyber_cards_gap_media',
		'--cyber-cards-gap-title' => 'cyber_cards_gap_title',
		'--cyber-cards-gap-text'  => 'cyber_cards_gap_text',
	);

	foreach ( $spacings as $var => $key ) {
		$vars[ $var ] = sprintf(
			'%dpx',
			cyber_section_spacing( isset( $row[ $key ] ) ? $row[ $key ] : 0 )
		);
	}

	// --- Wyglad elementu ------------------------------------------------
	$bg = cyber_sanitize_color( isset( $row['cyber_cards_bg'] ) ? $row['cyber_cards_bg'] : '' );

	if ( '' !== $bg ) {
		$vars['--cyber-cards-bg'] = $bg;
	}

	$bg_hover = cyber_sanitize_color( isset( $row['cyber_cards_bg_hover'] ) ? $row['cyber_cards_bg_hover'] : '' );

	if ( '' !== $bg_hover ) {
		$vars['--cyber-cards-bg-hover'] = $bg_hover;
		$classes[]                      = 'cyber-cards--hover';
	}

	$radius = absint( isset( $row['cyber_cards_radius'] ) ? $row['cyber_cards_radius'] : 0 );

	if ( $radius > 0 ) {
		$vars['--cyber-cards-radius'] = sprintf( '%dpx', min( $radius, 200 ) );
	}

	/*
	 * Cien i obramowanie sa wlacznikami, nie polami koloru. Barwy pochodza
	 * z zakladki Kolory (--cyber-color-shadow, --cyber-color-border-1), a same
	 * wartosci cienia i grubosci ramki stoja na sztywno w arkuszu — tak, zeby
	 * wszystkie karty w projekcie wygladaly tak samo, a zmiana palety w panelu
	 * dzialala wszedzie naraz.
	 */
	if ( ! empty( $row['cyber_cards_shadow'] ) ) {
		$classes[] = 'cyber-cards--shadow';
	}

	if ( ! empty( $row['cyber_cards_border'] ) ) {
		$classes[] = 'cyber-cards--border';
	}

	/*
	 * Proporcje zdjecia: klasa, bo zmienia dwie wlasciwosci naraz (aspect-ratio
	 * i object-fit). Dotyczy zdjecia jako <img>; w trybie tla karta ma wlasna
	 * minimalna wysokosc.
	 */
	$ratio = cyber_section_choice(
		isset( $row['cyber_cards_image_ratio'] ) ? $row['cyber_cards_image_ratio'] : 'auto',
		cyber_cards_image_ratios(),
		'auto'
	);

	if ( 'auto' !== $ratio ) {
		$classes[] = 'cyber-cards--ratio-' . $ratio;
	}

	if ( ! empty( $row['cyber_cards_image_as_bg'] ) ) {
		$classes[] = 'cyber-cards--image-bg';

		$min_height = absint( isset( $row['cyber_cards_min_height'] ) ? $row['cyber_cards_min_height'] : 0 );

		if ( $min_height > 0 ) {
			$vars['--cyber-cards-min-height'] = sprintf( '%dpx', min( $min_height, 2000 ) );
		}
	}

	// --- Typografia -----------------------------------------------------
	$title_color = cyber_sanitize_color(
		isset( $row['cyber_cards_title_color'] ) ? $row['cyber_cards_title_color'] : ''
	);

	if ( '' !== $title_color ) {
		$vars['--cyber-cards-title-color'] = $title_color;
	}

	$text_color = cyber_sanitize_color(
		isset( $row['cyber_cards_text_color'] ) ? $row['cyber_cards_text_color'] : ''
	);

	if ( '' !== $text_color ) {
		$vars['--cyber-cards-text-color'] = $text_color;
	}

	/*
	 * Rozmiar tytulu to odwolanie do globalnej wielkosci naglowka, nie liczba.
	 * Zmienne --cyber-font-size-hN skaluja sie na breakpointach (Ustawienia
	 * czcionki), wiec tytul karty skaluje sie razem z nimi. Znacznik <h3>
	 * zostaje — rozmiar nie zmienia hierarchii naglowkow na stronie.
	 */
	$title_size = cyber_section_choice(
		isset( $row['cyber_cards_title_size'] ) ? $row['cyber_cards_title_size'] : 'h5',
		cyber_cards_title_sizes(),
		'h5'
	);

	$vars['--cyber-cards-title-size'] = sprintf( 'var(--cyber-font-size-%s)', $title_size );

	$weights = cyber_font_weight_choices();

	$title_weight = isset( $row['cyber_cards_title_weight'] ) ? (string) $row['cyber_cards_title_weight'] : '';

	if ( in_array( $title_weight, $weights, true ) ) {
		$vars['--cyber-cards-title-weight'] = $title_weight;
	}

	$text_weight = isset( $row['cyber_cards_text_weight'] ) ? (string) $row['cyber_cards_text_weight'] : '';

	if ( in_array( $text_weight, $weights, true ) ) {
		$vars['--cyber-cards-text-weight'] = $text_weight;
	}

	// --- Wyrownanie: klasa, nie zmienna ---------------------------------
	$aligns = array(
		'media' => 'cyber_cards_align_media',
		'title' => 'cyber_cards_align_title',
		'text'  => 'cyber_cards_align_text',
	);

	foreach ( $aligns as $part => $key ) {
		$classes[] = sprintf(
			'cyber-cards--%1$s-%2$s',
			$part,
			cyber_section_choice(
				isset( $row[ $key ] ) ? $row[ $key ] : 'left',
				cyber_alignments(),
				'left'
			)
		);
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
 * Czy blok WYSIWYG nad albo pod kartami ma sie wyswietlic.
 *
 * Domyslnie NIE — wlacznik trzeba swiadomie wlaczyc. Brak wartosci liczy sie
 * jako wylaczony, zgodnie z default_value pola w ACF, zeby front i panel
 * zawsze pokazywaly ten sam stan.
 *
 * @param array  $row  Wiersz Flexible Content.
 * @param string $slot 'top' albo 'bottom'.
 * @return bool
 */
function cyber_cards_shows_wysiwyg( array $row, $slot ) {
	$key = 'cyber_cards_show_' . $slot;

	return ! empty( $row[ $key ] );
}

/**
 * Normalizuje elementy repeatera do postaci gotowej dla widoku.
 *
 * Widok nie dostaje surowych wartosci ACF, tylko gotowe dane (CLAUDE.md
 * sekcja 4). Element bez tytulu, zdjecia i tekstu jest pomijany — pusty wiersz
 * repeatera zdarza sie przy kazdym zapisie i nie ma po co rysowac po nim
 * pustej ramki.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<int, array<string, mixed>> Lista elementow.
 */
function cyber_cards_items( array $row ) {
	$items = isset( $row['cyber_cards_items'] ) ? $row['cyber_cards_items'] : array();

	if ( empty( $items ) || ! is_array( $items ) ) {
		return array();
	}

	$as_background = ! empty( $row['cyber_cards_image_as_bg'] );
	$link_style    = cyber_section_choice(
		isset( $row['cyber_cards_link_style'] ) ? $row['cyber_cards_link_style'] : 'button',
		cyber_cards_link_styles(),
		'button'
	);

	$button_size = cyber_section_choice(
		isset( $row['cyber_cards_button_size'] ) ? $row['cyber_cards_button_size'] : 'medium',
		cyber_button_sizes(),
		'medium'
	);

	$out = array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$image_id = absint( isset( $item['cyber_card_image'] ) ? $item['cyber_card_image'] : 0 );
		$title    = isset( $item['cyber_card_title'] ) ? sanitize_text_field( (string) $item['cyber_card_title'] ) : '';
		$over     = isset( $item['cyber_card_overtitle'] ) ? sanitize_text_field( (string) $item['cyber_card_overtitle'] ) : '';
		$text     = isset( $item['cyber_card_text'] ) ? (string) $item['cyber_card_text'] : '';
		$link     = isset( $item['cyber_card_link'] ) ? $item['cyber_card_link'] : array();

		if ( ! $image_id && '' === $title && '' === $over && '' === trim( $text ) ) {
			continue;
		}

		$url    = '';
		$label  = '';
		$target = '';

		if ( is_array( $link ) && ! empty( $link['url'] ) ) {
			$url    = esc_url_raw( (string) $link['url'] );
			$label  = isset( $link['title'] ) ? sanitize_text_field( (string) $link['title'] ) : '';
			$target = isset( $link['target'] ) && '_blank' === $link['target'] ? '_blank' : '';
		}

		$out[] = array(
			'image_id'    => $image_id,
			'image_url'   => $as_background && $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '',
			'overtitle'   => $over,
			'title'       => $title,
			'text'        => $text,
			'url'         => $url,
			'label'       => $label,
			'target'      => $target,
			'link_style'  => $link_style,
			'button_size' => $button_size,
		);
	}

	return $out;
}
