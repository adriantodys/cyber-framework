<?php
/**
 * Sekcja "Karuzela kart".
 *
 * Te same karty co w sekcji "Karty", ulozone w przewijany rzad zamiast siatki.
 * Kilka kart widocznych naraz, liczba osobno na desktop, tablet i telefon.
 *
 * JEDNA DEFINICJA KARTY. Karuzela nie ma wlasnych pol elementu ani wlasnego
 * wygladu karty — klonuje je z group_section_cards (repeater elementow
 * i ustawienia wygladu bez pol siatki) i renderuje wspolnym komponentem
 * template-parts/components/card.php. Pola w wierszu nazywaja sie wiec
 * cyber_cards_*, a normalizacja i klasy pochodza z inc/sections-cards.php.
 * Zmiana wygladu karty dziala w obu sekcjach naraz.
 *
 * Ten modul doklada wylacznie to, czego siatka nie ma: przewijanie.
 *
 * BIBLIOTEKA: Swiper 14.2.0, ta sama co w sekcji "Slider" — skrypt
 * assets/js/slider.js obsluguje oba rodzaje karuzel (inc/sections-slider.php).
 *
 * DWA TRYBY SZEROKOSCI (pole "Szerokosc karuzeli"):
 *
 *   section — karuzela w szerokosci sekcji, czyli za ustawieniem "Szerokosc
 *             sekcji" (domyslnie takiej jak strona).
 *   full    — rzad kart idzie od krawedzi do krawedzi okna. Tresc nad i pod
 *             karuzela nadal trzyma szerokosc sekcji.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dozwolone tryby szerokosci karuzeli.
 *
 * @return string[]
 */
function cyber_carousel_widths() {
	return array( 'section', 'full' );
}

/**
 * Konfiguracja karuzeli po walidacji.
 *
 * Liczby kart na widoku odpowiadaja kanonicznym progom z CLAUDE.md sekcja 18.
 * Swiper liczy breakpointy od DOLU (min-width), wiec skrypt tlumaczy je na
 * 768px (tablet) i 981px (desktop) — te same granice co max-width 767/980
 * w arkuszach.
 *
 * Petla wymaga wiecej slajdow niz widac naraz. Tego PHP nie rozstrzyga, bo
 * liczba widocznych kart zalezy od szerokosci ekranu — decyzje podejmuje
 * skrypt, porownujac liczbe kart z najwieksza liczba widocznych naraz.
 *
 * @param array $row   Wiersz Flexible Content.
 * @param int   $count Liczba kart.
 * @return array<string, mixed>
 */
function cyber_carousel_config( array $row, $count ) {
	$many = $count > 1;

	$per_view   = cyber_cards_column_count(
		isset( $row['cyber_carousel_per_view'] ) ? $row['cyber_carousel_per_view'] : 4,
		cyber_cards_columns(),
		4
	);
	$per_view_t = cyber_cards_column_count(
		isset( $row['cyber_carousel_per_view_tablet'] ) ? $row['cyber_carousel_per_view_tablet'] : 2,
		cyber_cards_columns_tablet(),
		2
	);
	$per_view_m = cyber_cards_column_count(
		isset( $row['cyber_carousel_per_view_mobile'] ) ? $row['cyber_carousel_per_view_mobile'] : 1,
		cyber_cards_columns_mobile(),
		1
	);

	$delay = isset( $row['cyber_carousel_delay'] ) ? absint( $row['cyber_carousel_delay'] ) : 5000;
	$delay = max( 1000, min( 30000, $delay ? $delay : 5000 ) );

	$speed = isset( $row['cyber_carousel_speed'] ) ? absint( $row['cyber_carousel_speed'] ) : 600;
	$speed = max( 100, min( 30000, $speed ? $speed : 600 ) );

	/*
	 * Ciagle przewijanie to osobny tryb, a nie kombinacja przelacznikow.
	 * Nie korzysta ze Swipera w ogole — patrz cyber_carousel_continuous_items().
	 * Strzalki, kropki, petla i autoplay nie maja w nim sensu, wiec nie
	 * powstaja, nawet jesli w bazie zostaly wlaczone z czasow przed wlaczeniem
	 * trybu (w panelu sa wtedy ukryte).
	 */
	$continuous = $many && ! empty( $row['cyber_carousel_continuous'] );

	return array(
		'per_view'   => $per_view,
		'per_view_t' => $per_view_t,
		'per_view_m' => $per_view_m,
		'gap'        => cyber_section_spacing( isset( $row['cyber_carousel_gap'] ) ? $row['cyber_carousel_gap'] : 24 ),
		'speed'      => $speed,
		'continuous' => $continuous,
		'loop'       => ! $continuous && $many && ! empty( $row['cyber_carousel_loop'] ),
		'autoplay'   => ! $continuous && $many && ! empty( $row['cyber_carousel_autoplay'] ),
		'delay'      => $delay,
		'navigation' => ! $continuous && $many && ! empty( $row['cyber_carousel_navigation'] ),
		'pagination' => ! $continuous && $many && ! empty( $row['cyber_carousel_pagination'] ),
	);
}

/**
 * Tryb szerokosci karuzeli.
 *
 * @param array $row Wiersz Flexible Content.
 * @return string 'section' albo 'full'.
 */
function cyber_carousel_width( array $row ) {
	return cyber_section_choice(
		isset( $row['cyber_carousel_width'] ) ? $row['cyber_carousel_width'] : 'section',
		cyber_carousel_widths(),
		'section'
	);
}

/**
 * Klasy i zmienne CSS karuzeli.
 *
 * Kontener dostaje ROWNIEZ klasy i zmienne siatki kart
 * (cyber_cards_attributes) — to one niosa wyglad karty: tlo, cien, obramowanie,
 * wyrownanie, kolory. Samo ukladanie w siatke arkusz wylacza dla karuzeli.
 *
 * Liczba kart jest potrzebna, bo od niej zalezy tryb ciagly: przy jednej
 * karcie nie ma czego przewijac i tasma nie powstaje. Wczesniej funkcja
 * pytala o konfiguracje z liczba 0 — tryb ciagly byl wtedy zawsze wylaczony,
 * a klasa .cyber-carousel--continuous nie trafiala na kontener mimo
 * wyrenderowanej tasmy. Wyszlo na pomiarze ruchu w Chrome.
 *
 * @param array $row   Wiersz Flexible Content.
 * @param int   $count Liczba kart.
 * @return array{class: string, style: string}
 */
function cyber_carousel_attributes( array $row, $count ) {
	$cards  = cyber_cards_attributes( $row );
	$config = cyber_carousel_config( $row, (int) $count );

	/*
	 * Liczba kart na widoku i odstep jada takze do CSS, nie tylko do skryptu.
	 * Skrypt jest modulem ES i uruchamia sie PO wyrenderowaniu strony — do tego
	 * czasu Swiper nie ustawil szerokosci slajdow i kazda karta zajmowalaby cala
	 * szerokosc. Arkusz uklada karty w docelowy rzad jeszcze przed startem
	 * skryptu, wiec nic nie przeskakuje.
	 */
	$style = $cards['style'] . sprintf(
		'--cyber-carousel-pv:%1$d;--cyber-carousel-pv-t:%2$d;--cyber-carousel-pv-m:%3$d;--cyber-carousel-gap:%4$dpx;',
		$config['per_view'],
		$config['per_view_t'],
		$config['per_view_m'],
		$config['gap']
	);

	$class = 'cyber-carousel swiper cyber-carousel--' . cyber_carousel_width( $row ) . ' ' . $cards['class'];

	if ( $config['continuous'] ) {
		$class .= ' cyber-carousel--continuous';
	}

	return array(
		'class' => $class,
		'style' => $style,
	);
}

/**
 * Karty dla trybu ciaglego: zestaw powielony tak, zeby tasma sie domykala.
 *
 * DLACZEGO NIE SWIPER. Petla Swipera przestawia karty z jednego konca rzedu na
 * drugi w trakcie ruchu (loopFix). Przy ruchu krokowym tego nie widac, ale
 * przy ciaglym, na starcie, rzad przeskakiwal o dwie karty — zmierzone
 * w Chrome: przed startem widoczna Karta 1, chwile po starcie juz Karta 3,
 * przy predkosci, ktora nie pozwala przejechac dwoch kart w tym czasie.
 *
 * ZAMIAST TEGO: tasma z animacja CSS. Karty sa wypisane DWA RAZY pod rzad,
 * a arkusz przesuwa cala tasme o polowe jej dlugosci w nieskonczonej, liniowej
 * petli. Koniec animacji wypada dokladnie w miejscu jej poczatku, wiec przeskoku
 * nie ma z definicji — to geometria, nie synchronizacja zegarow. Dziala bez
 * JavaScriptu.
 *
 * Jeden zestaw musi byc co najmniej tak szeroki jak ekran, inaczej przy
 * koncu zestawu w rzedzie pojawilaby sie dziura. Zestaw jest wiec powielany,
 * az liczba kart dorowna najwiekszej liczbie widocznych naraz.
 *
 * @param array $items    Karty z cyber_cards_items().
 * @param int   $max_view Najwieksza liczba kart widocznych naraz.
 * @return array{items: array, copies: bool[], per_set: int}
 */
function cyber_carousel_continuous_items( array $items, $max_view ) {
	$set = $items;

	while ( count( $set ) < max( 1, (int) $max_view ) ) {
		$set = array_merge( $set, $items );
	}

	$per_set = count( $set );
	$all     = array_merge( $set, $set );
	$copies  = array();

	/*
	 * Oryginalem jest tylko pierwsze wystapienie kazdej karty. Wszystko
	 * dalej to kopie: ukryte przed czytnikami ekranu i wyjete z kolejnosci
	 * tabulacji, zeby nikt nie przechodzil przez te same karty kilka razy.
	 */
	foreach ( $all as $index => $item ) {
		$copies[] = $index >= count( $items );
	}

	return array(
		'items'   => $all,
		'copies'  => $copies,
		'per_set' => $per_set,
	);
}

/**
 * Chowa w panelu edytory WYSIWYG karuzeli przy wylaczonym wlaczniku.
 *
 * Ten sam mechanizm co w kartach (cyber_cards_wysiwyg_condition) — wlaczniki
 * sa zreszta tymi samymi polami, sklonowanymi z group_section_cards. Rozni sie
 * wylacznie klucz klonu, ktory przyniosl edytor.
 *
 * @param array $field Pole ACF przygotowywane do wyswietlenia w formularzu.
 * @return array
 */
function cyber_carousel_wysiwyg_condition( $field ) {
	$map = array(
		'field_cyber_section_carousel_top'    => 'field_cyber_cards_show_top',
		'field_cyber_section_carousel_bottom' => 'field_cyber_cards_show_bottom',
	);

	if ( empty( $field['_clone'] ) || ! isset( $map[ $field['_clone'] ] ) ) {
		return $field;
	}

	$field['conditional_logic'] = array(
		array(
			array(
				'field'    => $map[ $field['_clone'] ],
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	return $field;
}
add_filter( 'acf/prepare_field', 'cyber_carousel_wysiwyg_condition', 20 );
