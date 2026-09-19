<?php
/**
 * Sekcja "FAQ" — pytania i odpowiedzi rozwijane po kliknieciu.
 *
 * BEZ BIBLIOTEKI I BEZ JAVASCRIPTU. Akordeon to natywne <details>/<summary>:
 *
 * - klawiatura, czytniki ekranu i stan otwarty/zamkniety dzialaja z przegladarki,
 *   bez aria-expanded utrzymywanego recznie w skrypcie;
 * - tryb "tylko jedna odpowiedz otwarta" daje atrybut name — elementy <details>
 *   o tej samej nazwie zamykaja sie nawzajem (Chrome 120, Safari 17.2,
 *   Firefox 130). Starsza przegladarka po prostu pozwoli otworzyc kilka naraz;
 * - odpowiedz jest w HTML od poczatku, wiec wyszukiwarka i Ctrl+F ja widza,
 *   a przegladarka sama rozwija pytanie, w ktorym Ctrl+F znalazl tekst.
 *
 * Plynne rozwijanie robi arkusz (::details-content + interpolate-size). Tam,
 * gdzie przegladarka tego nie zna, odpowiedz pojawia sie od razu — bez bledu.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dozwolone liczby kolumn.
 *
 * @return string[]
 */
function cyber_faq_columns() {
	return array( '1', '2' );
}

/**
 * Pytania sekcji po normalizacji.
 *
 * Pytanie bez tresci jest pomijane — pusty wiersz repeatera zdarza sie przy
 * kazdym zapisie, a <summary> bez tekstu bylby pustym przyciskiem.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<int, array{question: string, answer: string, icon: int}>
 */
function cyber_faq_items( array $row ) {
	$items = isset( $row['cyber_faq_items'] ) ? $row['cyber_faq_items'] : array();
	$out   = array();

	foreach ( is_array( $items ) ? $items : array() as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$question = isset( $item['cyber_faq_question'] ) ? sanitize_text_field( (string) $item['cyber_faq_question'] ) : '';

		if ( '' === $question ) {
			continue;
		}

		$out[] = array(
			'question' => $question,
			'answer'   => isset( $item['cyber_faq_answer'] ) ? (string) $item['cyber_faq_answer'] : '',
			'icon'     => absint( isset( $item['cyber_faq_item_icon'] ) ? $item['cyber_faq_item_icon'] : 0 ),
		);
	}

	return $out;
}

/**
 * Czy blok WYSIWYG nad albo pod pytaniami ma sie wyswietlic.
 *
 * Domyslnie NIE — tak jak w kartach. Brak wartosci liczy sie jako wylaczony,
 * zgodnie z default_value pola, wiec panel i front pokazuja ten sam stan.
 *
 * @param array  $row  Wiersz Flexible Content.
 * @param string $slot 'top' albo 'bottom'.
 * @return bool
 */
function cyber_faq_shows_wysiwyg( array $row, $slot ) {
	return cyber_section_shows_wysiwyg( $row, 'cyber_faq_show', $slot );
}

/**
 * Pytania rozdzielone na kolumny.
 *
 * Dwie kolumny to dwie OSOBNE listy, nie siatka: w siatce otwarcie pytania
 * rozciagaloby caly wiersz i przesuwalo pytanie obok. Pierwsza polowa idzie
 * do lewej kolumny, reszta do prawej — przy nieparzystej liczbie lewa ma o jedno
 * pytanie wiecej. Kolejnosc czytania (i tabulacji) zostaje ta sama co w panelu.
 *
 * @param array  $items   Wynik cyber_faq_items().
 * @param string $columns '1' albo '2'.
 * @return array[] Lista kolumn, kazda z lista pytan.
 */
function cyber_faq_split( array $items, $columns ) {
	if ( '2' !== $columns || count( $items ) < 2 ) {
		return array( $items );
	}

	return array_chunk( $items, (int) ceil( count( $items ) / 2 ) );
}

/**
 * Konfiguracja sekcji po walidacji.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<string, mixed>
 */
function cyber_faq_config( array $row ) {
	return array(
		'columns'     => cyber_section_choice(
			isset( $row['cyber_faq_columns'] ) ? $row['cyber_faq_columns'] : '1',
			cyber_faq_columns(),
			'1'
		),
		'single'      => ! isset( $row['cyber_faq_single'] ) || ! empty( $row['cyber_faq_single'] ),
		'first_open'  => ! isset( $row['cyber_faq_first_open'] ) || ! empty( $row['cyber_faq_first_open'] ),
		'show_icon'   => ! empty( $row['cyber_faq_show_icon'] ),
		'icon'        => absint( isset( $row['cyber_faq_icon'] ) ? $row['cyber_faq_icon'] : 0 ),
		'show_toggle' => ! isset( $row['cyber_faq_show_toggle'] ) || ! empty( $row['cyber_faq_show_toggle'] ),
	);
}

/**
 * Klasy i zmienne CSS kontenera pytan.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string}
 */
function cyber_faq_attributes( array $row ) {
	$config  = cyber_faq_config( $row );
	$classes = array( 'cyber-faq', 'cyber-faq--cols-' . $config['columns'] );
	$vars    = array(
		'--cyber-faq-pad-y' => sprintf( '%dpx', cyber_section_spacing( isset( $row['cyber_faq_pad_y'] ) ? $row['cyber_faq_pad_y'] : 24 ) ),
	);

	if ( '2' === $config['columns'] ) {
		$vars['--cyber-faq-gap-x'] = sprintf( '%dpx', cyber_section_spacing( isset( $row['cyber_faq_gap_x'] ) ? $row['cyber_faq_gap_x'] : 48 ) );
	}

	if ( ! isset( $row['cyber_faq_divider'] ) || ! empty( $row['cyber_faq_divider'] ) ) {
		$classes[] = 'cyber-faq--divider';
	}

	if ( $config['show_icon'] ) {
		$classes[] = 'cyber-faq--has-icon';
	}

	if ( $config['show_toggle'] ) {
		$classes[] = 'cyber-faq--has-toggle';
	}

	// Rozmiar pytania to odwolanie do globalnej wielkosci naglowka (jak tytul karty).
	$vars['--cyber-faq-q-size'] = sprintf(
		'var(--cyber-font-size-%s)',
		cyber_section_choice(
			isset( $row['cyber_faq_question_size'] ) ? $row['cyber_faq_question_size'] : 'h5',
			cyber_cards_title_sizes(),
			'h5'
		)
	);

	$weight = isset( $row['cyber_faq_question_weight'] ) ? (string) $row['cyber_faq_question_weight'] : '';

	if ( in_array( $weight, cyber_font_weight_choices(), true ) ) {
		$vars['--cyber-faq-q-weight'] = $weight;
	}

	$colors = array(
		'--cyber-faq-open-bg' => 'cyber_faq_open_bg',
		'--cyber-faq-q-hover' => 'cyber_faq_hover_color',
		'--cyber-faq-q-color' => 'cyber_faq_question_color',
		'--cyber-faq-a-color' => 'cyber_faq_answer_color',
	);

	foreach ( $colors as $var => $key ) {
		$value = cyber_sanitize_color( isset( $row[ $key ] ) ? $row[ $key ] : '' );

		if ( '' !== $value ) {
			$vars[ $var ] = $value;
		}
	}

	if ( isset( $vars['--cyber-faq-q-hover'] ) ) {
		$classes[] = 'cyber-faq--hover';
	}

	if ( isset( $vars['--cyber-faq-open-bg'] ) ) {
		$classes[] = 'cyber-faq--open-bg';
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
 * Nazwa grupy <details> dla trybu "jedna odpowiedz otwarta".
 *
 * Unikalna na strone: dwie sekcje FAQ (albo ta sama sekcja globalna wstawiona
 * dwa razy) nie moga zamykac sobie nawzajem pytan.
 *
 * @return string
 */
function cyber_faq_group_name() {
	static $count = 0;

	++$count;

	return 'cyber-faq-' . $count;
}

/**
 * Wypisuje ikone pytania (z lewej strony).
 *
 * Kolejnosc: ikona pytania z repeatera, ikona sekcji, ikona motywu. Obrazek jest
 * dekoracyjny — pytanie niesie tresc samo, wiec alt jest pusty.
 *
 * @param int $item_icon    Ikona ustawiona przy pytaniu.
 * @param int $section_icon Ikona ustawiona dla sekcji.
 * @return void
 */
function cyber_faq_icon( $item_icon, $section_icon ) {
	$id = $item_icon ? $item_icon : $section_icon;

	echo '<span class="cyber-faq__icon" aria-hidden="true">';

	if ( $id && wp_attachment_is_image( $id ) ) {
		echo wp_get_attachment_image(
			$id,
			'thumbnail',
			false,
			array(
				'class'   => 'cyber-faq__icon-image',
				'alt'     => '',
				'loading' => 'lazy',
			)
		);
	} else {
		echo cyber_get_icon( 'question' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- staly markup z rejestru ikon.
	}

	echo '</span>';
}
