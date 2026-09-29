<?php
/**
 * Sekcja "Kontakt" — dwie kolumny: formularz i dane kontaktowe.
 *
 * Kolumna formularza (domyslnie lewa): tresc WYSIWYG nad, formularz Contact
 * Form 7 — zaleznosc miekka, o ktorej decyduje inc/contact-form-7.php — i tresc
 * WYSIWYG pod.
 *
 * Kolumna danych (domyslnie prawa): tresc WYSIWYG nad, dane kontaktowe, social
 * media i tresc WYSIWYG pod. Dane NIE sa wpisywane w sekcji — pochodza z Global
 * Options (zakladki Kontakt i Social Media, CLAUDE.md sekcja 22), a sekcja ma
 * tylko wlaczniki, co pokazac. Zmiana telefonu w jednym miejscu zmienia go
 * w top headerze, stopce i we wszystkich sekcjach kontaktowych naraz.
 *
 * Kazdy z czterech edytorow w kolumnach ma wlasny wlacznik (domyslnie
 * wylaczony), a wlacznik cyber_contact_section_swap zamienia kolumny miejscami.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dozwolone proporcje kolumn: wartosc => szablon grid-template-columns.
 *
 * @return array<string, string>
 */
function cyber_contact_ratios() {
	return array(
		'50-50' => '1fr 1fr',
		'60-40' => '3fr 2fr',
		'40-60' => '2fr 3fr',
	);
}

/**
 * Dane kontaktowe do wyswietlenia w sekcji.
 *
 * Kolejnosc jak w zakladce Kontakt. Pozycja pojawia sie, gdy jej wlacznik
 * w sekcji jest wlaczony ORAZ pole w Global Options jest wypelnione.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<int, array{key: string, label: string, text: string, href: string, icon: string}>
 */
function cyber_contact_section_data( array $row ) {
	$defaults = array(
		'email'   => true,
		'phone'   => true,
		'address' => false,
		'nip'     => false,
		'regon'   => false,
	);

	$labels = array(
		'email'   => __( 'Email', 'cyber-framework' ),
		'phone'   => __( 'Telefon', 'cyber-framework' ),
		'address' => __( 'Adres', 'cyber-framework' ),
		'nip'     => __( 'NIP', 'cyber-framework' ),
		'regon'   => __( 'REGON', 'cyber-framework' ),
	);

	$icons = array(
		'email'   => 'envelope',
		'phone'   => 'phone',
		'address' => 'location',
	);

	$out = array();

	foreach ( $defaults as $key => $default ) {
		$toggle = 'cyber_contact_section_show_' . $key;
		$show   = isset( $row[ $toggle ] ) ? ! empty( $row[ $toggle ] ) : $default;
		$value  = trim( (string) cyber_get_option( 'contact_' . $key ) );

		if ( ! $show || '' === $value ) {
			continue;
		}

		$href = '';

		if ( 'email' === $key && is_email( $value ) ) {
			$href = 'mailto:' . $value;
		} elseif ( 'phone' === $key ) {
			$href = cyber_tel_href( $value );
		}

		$out[] = array(
			'key'   => $key,
			'label' => $labels[ $key ],
			'text'  => $value,
			'href'  => $href,
			'icon'  => isset( $icons[ $key ] ) ? $icons[ $key ] : '',
		);
	}

	return $out;
}

/**
 * Konfiguracja sekcji po walidacji.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<string, mixed>
 */
function cyber_contact_section_config( array $row ) {
	return array(
		'show_icons'    => ! empty( $row['cyber_contact_section_show_icons'] ),
		'show_social'   => ! isset( $row['cyber_contact_section_show_social'] ) || ! empty( $row['cyber_contact_section_show_social'] ),
		'social_title'  => isset( $row['cyber_contact_section_social_title'] )
			? sanitize_text_field( (string) $row['cyber_contact_section_social_title'] )
			: __( 'Nasze social media', 'cyber-framework' ),
		'social_labels' => ! isset( $row['cyber_contact_section_social_labels'] ) || ! empty( $row['cyber_contact_section_social_labels'] ),
		'form'          => absint( isset( $row['cyber_contact_section_form'] ) ? $row['cyber_contact_section_form'] : 0 ),
		'swap'          => ! empty( $row['cyber_contact_section_swap'] ),
	);
}

/**
 * Kolejnosc kolumn od lewej: 'form' i 'data'.
 *
 * Kolumny zamieniaja sie w markupie, nie przez CSS order — dzieki temu
 * ponizej 980px i dla czytnika ekranu pierwsza jest ta, ktora na desktopie
 * stoi z lewej, a proporcje z cyber_contact_ratios() dalej licza sie od lewej.
 *
 * @param array $config Wynik cyber_contact_section_config().
 * @return string[]
 */
function cyber_contact_section_order( array $config ) {
	return empty( $config['swap'] ) ? array( 'form', 'data' ) : array( 'data', 'form' );
}

/**
 * Tresci WYSIWYG nad i pod formularzem oraz nad i pod danymi kontaktowymi.
 *
 * Blok pojawia sie, gdy jego wlacznik jest wlaczony ORAZ edytor nie jest
 * pusty. Brak wartosci wlacznika liczy sie jako wylaczony, zgodnie
 * z default_value pola — panel i front pokazuja ten sam stan.
 *
 * Nazwy pol tresci nad kolumnami (cyber_contact_section_right / _left) sa
 * historyczne: z czasow, gdy formularz stal zawsze z prawej, a dane z lewej.
 * Zostaly, bo nazwa pola jest kluczem zapisanym w bazie — zmiana wyczyscilaby
 * tresc w istniejacych sekcjach.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{form_top: string, form_bottom: string, data_top: string, data_bottom: string}
 */
function cyber_contact_section_contents( array $row ) {
	$map = array(
		'form_top'    => 'cyber_contact_section_right',
		'form_bottom' => 'cyber_contact_section_form_bottom',
		'data_top'    => 'cyber_contact_section_left',
		'data_bottom' => 'cyber_contact_section_data_bottom',
	);

	$out = array();

	foreach ( $map as $slot => $key ) {
		$show  = ! empty( $row[ 'cyber_contact_section_show_' . $slot ] );
		$value = isset( $row[ $key ] ) ? (string) $row[ $key ] : '';

		$out[ $slot ] = $show && '' !== trim( $value ) ? $value : '';
	}

	return $out;
}

/**
 * Klasy i zmienne CSS kontenera kolumn.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string}
 */
function cyber_contact_section_attributes( array $row ) {
	$ratios = cyber_contact_ratios();
	$ratio  = isset( $row['cyber_contact_section_ratio'] ) ? (string) $row['cyber_contact_section_ratio'] : '50-50';
	$ratio  = isset( $ratios[ $ratio ] ) ? $ratio : '50-50';
	$size   = cyber_section_choice(
		isset( $row['cyber_contact_section_button_size'] ) ? $row['cyber_contact_section_button_size'] : 'large',
		cyber_button_sizes(),
		'large'
	);

	$classes = array( 'cyber-contact', 'cyber-contact--' . $ratio, 'cyber-contact--btn-' . $size );

	if ( ! isset( $row['cyber_contact_section_button_full'] ) || ! empty( $row['cyber_contact_section_button_full'] ) ) {
		$classes[] = 'cyber-contact--btn-full';
	}

	// Stan, nie wariant (CLAUDE.md sekcja 20): laczy sie z kazda proporcja.
	if ( ! empty( $row['cyber_contact_section_swap'] ) ) {
		$classes[] = 'cyber-contact--swap';
	}

	$vars = array(
		'--cyber-contact-cols'  => $ratios[ $ratio ],
		'--cyber-contact-gap-x' => sprintf( '%dpx', cyber_section_spacing( isset( $row['cyber_contact_section_gap_x'] ) ? $row['cyber_contact_section_gap_x'] : 64 ) ),
	);

	$colors = array(
		'--cyber-contact-label-color' => 'cyber_contact_section_label_color',
		'--cyber-contact-value-color' => 'cyber_contact_section_value_color',
		'--cyber-contact-field-bg'    => 'cyber_contact_section_field_bg',
	);

	$vars = array_merge( $vars, cyber_row_colors( $row, $colors ) );

	$style = cyber_css_declarations( $vars );

	return array(
		'class' => implode( ' ', $classes ),
		'style' => $style,
	);
}
