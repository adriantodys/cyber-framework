<?php
/**
 * Sekcje — Flexible Content.
 *
 * Jedno pole Flexible Content (cyber_sections) budujace tresc strony z klockow.
 * Kazdy klocek to layout ACF, ktory ma swoj odpowiednik w rejestrze ponizej
 * i jeden plik w template-parts/sections/.
 *
 * WSPOLNY SZKIELET. Kazdy layout sklada sie z tych samych trzech czesci:
 *
 *   [WYSIWYG gora]  ->  [wlasna tresc layoutu]  ->  [WYSIWYG dol]
 *
 * plus zestaw ustawien wygladu (tlo, szerokosc, odstepy). Zadna z tych czesci
 * nie jest powtarzana w definicji layoutu — wchodza polem Clone z dwoch grup
 * zrodlowych (group_section_content, group_section_settings), wiec istnieje
 * dokladnie jedna ich definicja. To jest roznica wobec podejscia "skopiuj
 * zestaw pol": piata kopia nie ma jak rozjechac sie z pierwsza.
 *
 * WARTOSCI PER INSTANCJA TRAFIAJA W ATRYBUT style — swiadome odstepstwo od
 * CLAUDE.md sekcja 6, ktora zakazuje inline style. Tamta regula powstala dla
 * wartosci GLOBALNYCH, wypisywanych raz w wp_head. Sekcja ma N instancji na
 * stronie i kazda ma inne tlo, wiec nie ma czego wypisac raz. Alternatywy sa
 * gorsze: blok <style> z regulami na #id wymagalby przelecenia calej petli
 * przed <head> albo ladowalby w stopce i powodowal przeskok layoutu.
 *
 * Media query zostaje w arkuszu i konsumuje warianty mobilne tych samych
 * zmiennych — dzieki temu PHP nie generuje ani jednej reguly CSS.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nazwa pola Flexible Content.
 */
const CYBER_SECTIONS_FIELD = 'cyber_sections';

/**
 * Rejestr typow sekcji.
 *
 * Jedno zrodlo prawdy: klucz layoutu ACF => opis. Z niego wynika, ktory plik
 * renderuje sekcje i — docelowo — gdzie wolno jej sie pojawic.
 *
 * Klucze wpisu:
 *
 * - label    : etykieta dla czlowieka (logi, komunikaty). Etykiete widoczna
 *              w panelu trzyma ACF, tutaj sluzy do diagnostyki.
 * - template : nazwa pliku w template-parts/sections/ bez rozszerzenia.
 * - contexts : typy tresci, w ktorych layout ma byc dostepny. Pole jest juz
 *              wypelnione, ale JESZCZE NIEUZYWANE — filtrowanie layoutow per
 *              typ wpisu to osobny krok. Do tego czasu wszystkie layouty sa
 *              dostepne wszedzie, gdzie przypieta jest grupa pol.
 *
 * KLUCZ LAYOUTU JEST KONTRAKTEM. Etykiete mozna zmieniac dowolnie, klucza
 * nigdy: ACF trzyma go przy kazdym zapisanym wierszu i nie znajdujac layoutu
 * o tej nazwie po cichu pomija wiersz przy renderowaniu ORAZ przy zapisie
 * (pro/fields/class-acf-field-flexible-content.php, "bail early if no
 * layout"). Zmiana nazwy klucza kasuje tresc na wszystkich stronach, ktore
 * z niej korzystaly, bez ostrzezenia i bez kosza.
 *
 * @return array<string, array<string, mixed>> Klucz layoutu => opis.
 */
function cyber_section_types() {
	return array(
		'basic'    => array(
			'label'    => 'Sekcja podstawowa',
			'template' => 'basic',
			'contexts' => array( 'page', 'post' ),
		),
		'cards'    => array(
			'label'    => 'Karty (icon boxes)',
			'template' => 'cards',
			'contexts' => array( 'page', 'post' ),
		),
		'columns'  => array(
			'label'    => 'Kolumny tekstowe (WYSIWYG)',
			'template' => 'columns',
			'contexts' => array( 'page', 'post' ),
		),
		'slider'   => array(
			'label'    => 'Slider',
			'template' => 'slider',
			'contexts' => array( 'page', 'post' ),
		),
		'carousel' => array(
			'label'    => 'Karuzela kart',
			'template' => 'carousel',
			'contexts' => array( 'page', 'post' ),
		),
		'faq'      => array(
			'label'    => 'FAQ (pytania i odpowiedzi)',
			'template' => 'faq',
			'contexts' => array( 'page', 'post' ),
		),
		'counter'  => array(
			'label'    => 'Licznik (counter)',
			'template' => 'counter',
			'contexts' => array( 'page', 'post' ),
		),
		'contact'  => array(
			'label'    => 'Kontakt (dane + formularz)',
			'template' => 'contact',
			'contexts' => array( 'page', 'post' ),
		),
		'posts'    => array(
			'label'    => 'Wpisy / CPT (karty lub slider)',
			'template' => 'posts',
			'contexts' => array( 'page', 'post' ),
		),
		'gallery'  => array(
			'label'    => 'Galeria (zdjecia albo galerie z CPT)',
			'template' => 'gallery',
			'contexts' => array( 'page', 'post' ),
		),
		'table'    => array(
			'label'    => 'Tabela',
			'template' => 'table',
			'contexts' => array( 'page', 'post' ),
		),
		/*
		 * Wstawia sekcje z wpisu CPT (inc/sections-global.php). Renderer
		 * podmienia wiersz na sekcje wybranego wpisu; plik szablonu wypisuje
		 * wylacznie podpowiedz dla redaktora, gdy nie ma czego pokazac.
		 * Swiadomie BEZ kontekstu cyber_global_section — brak zagniezdzania.
		 */
		'global'   => array(
			'label'    => 'Sekcja globalna',
			'template' => 'global',
			'contexts' => array( 'page', 'post' ),
		),
	);
}

/**
 * Dozwolone szerokosci wnetrza sekcji.
 *
 * Wartosc => nazwa zmiennej CSS ze srodka arkusza. 'inherit' siega po
 * szerokosc wybrana w Global Options, czyli idzie za ustawieniem strony
 * zamiast je powtarzac. To wartosc domyslna i powinna nia zostac: sekcja
 * z wpisana na sztywno szerokoscia przestaje reagowac na zmiane w panelu.
 *
 * @return array<string, string> Wartosc pola => zmienna CSS.
 */
function cyber_section_widths() {
	return array(
		'inherit' => 'var(--cyber-container-width)',
		'100'     => 'var(--cyber-width-100)',
		'80'      => 'var(--cyber-width-80)',
		'60'      => 'var(--cyber-width-60)',
	);
}

/**
 * Dozwolone odstepy sekcji.
 *
 * Zamknieta skala z CLAUDE.md sekcja 6, powiekszona o zero. Pole w panelu jest
 * Selectem wlasnie dlatego: przy polu liczbowym redaktor wpisze 35 i skala
 * przestanie istniec po trzech stronach. Tutaj panel jej pilnuje, nie dyscyplina.
 *
 * @return int[] Dozwolone wartosci w px.
 */
function cyber_section_spacings() {
	return cyber_spacing_scale();
}

/**
 * Dozwolone wartosci background-size.
 *
 * @return string[]
 */
function cyber_section_bg_sizes() {
	return array( 'cover', 'contain', 'auto' );
}

/**
 * Dozwolone wartosci poziomej pozycji tla.
 *
 * @return string[]
 */
function cyber_section_bg_positions_x() {
	return array( 'left', 'center', 'right' );
}

/**
 * Dozwolone wartosci pionowej pozycji tla.
 *
 * @return string[]
 */
function cyber_section_bg_positions_y() {
	return array( 'top', 'center', 'bottom' );
}

/* -------------------------------------------------------------------------- *
 * Walidacja pojedynczych wartosci
 * -------------------------------------------------------------------------- */

/**
 * Przepuszcza wartosc przez biala liste.
 *
 * @param mixed    $value   Wartosc z pola.
 * @param string[] $allowed Dozwolone wartosci.
 * @param string   $default Wartosc awaryjna.
 * @return string
 */
function cyber_section_choice( $value, array $allowed, $default ) {
	$value = sanitize_key( (string) $value );

	return in_array( $value, $allowed, true ) ? $value : $default;
}

/**
 * Sprowadza odstep do najblizszej dozwolonej wartosci ze skali.
 *
 * Wartosc spoza skali nie jest odrzucana na rzecz zera — to zjadaloby odstep
 * bez sladu. Zamiast tego laduje na najblizszym progu, czyli tam, gdzie
 * redaktor prawdopodobnie celowal.
 *
 * @param mixed $value Wartosc z pola.
 * @return int Odstep w px.
 */
function cyber_section_spacing( $value ) {
	$allowed = cyber_section_spacings();

	if ( ! is_numeric( $value ) ) {
		return 0;
	}

	$value = (int) round( (float) $value );

	if ( in_array( $value, $allowed, true ) ) {
		return $value;
	}

	$best = 0;

	foreach ( $allowed as $step ) {
		if ( abs( $step - $value ) < abs( $best - $value ) ) {
			$best = $step;
		}
	}

	return $best;
}

/**
 * Zamienia identyfikator zalacznika na adres pliku.
 *
 * Pole Image zwraca ID (return_format 'id'), zeby motyw mogl sam zdecydowac
 * o rozmiarze. Tlo sekcji idzie na pelna szerokosc okna, wiec bierzemy 'full';
 * za lzejszy wariant na telefonie odpowiada osobne pole z wlasnym kadrem.
 *
 * @param mixed $id Identyfikator zalacznika.
 * @return string Adres albo pusty string.
 */
function cyber_section_image_url( $id ) {
	$id = absint( $id );

	if ( ! $id ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $id, 'full' );

	return $url ? esc_url_raw( $url ) : '';
}

/**
 * Czysci liste dodatkowych klas CSS.
 *
 * Jedno pole na dowolna liczbe klas rozdzielonych spacja, zamiast kilku
 * ponumerowanych slotow — te i tak zawsze konczy sie pytaniem, czemu akurat
 * dwa, a nie trzy. Kazdy czlon przechodzi przez sanitize_html_class(), wiec
 * z pola nie da sie wyjsc poza atrybut class.
 *
 * @param mixed $value Wartosc pola.
 * @return string[] Lista bezpiecznych klas.
 */
function cyber_section_classes( $value ) {
	$parts = preg_split( '/\s+/', trim( (string) $value ) );
	$out   = array();

	foreach ( (array) $parts as $part ) {
		$class = sanitize_html_class( $part );

		if ( '' !== $class ) {
			$out[] = $class;
		}
	}

	return array_values( array_unique( $out ) );
}

/* -------------------------------------------------------------------------- *
 * Budowa opakowania sekcji
 * -------------------------------------------------------------------------- */

/**
 * Sklada klasy i zmienne CSS opakowania sekcji.
 *
 * Zwraca gotowe do wypisania czesci atrybutow, ale NIE wypisuje ich sama —
 * escapowanie nalezy do widoku, przy samym wyjsciu (CLAUDE.md sekcja 8).
 *
 * @param string $type Klucz layoutu.
 * @param array  $row  Wiersz Flexible Content.
 * @return array{class: string, style: string, id: string} Czesci atrybutow.
 */
function cyber_section_attributes( $type, array $row ) {
	$classes = array(
		'cyber-section',
		'cyber-section--' . sanitize_html_class( $type ),
	);

	$classes = array_merge(
		$classes,
		cyber_section_classes( isset( $row['cyber_section_class'] ) ? $row['cyber_section_class'] : '' )
	);

	$vars = array();

	// Szerokosc wnetrza. Jedna wlasciwosc, wiec zmienna, a nie klasa
	// modyfikujaca (CLAUDE.md sekcja 20 dotyczy wyrownania, nie rozmiaru).
	$widths = cyber_section_widths();

	/*
	 * array_map na kluczach nie jest ozdobnikiem. PHP zamienia klucze tablicy
	 * wygladajace na liczbe na typ int, wiec array_keys() zwraca tu
	 * array( 'inherit', 100, 80, 60 ) — a porownanie scisle in_array( '80', ... )
	 * nie trafia w int 80 i kazda szerokosc poza 'inherit' po cichu wracala
	 * do wartosci domyslnej.
	 */
	$width = cyber_section_choice(
		isset( $row['cyber_section_width'] ) ? $row['cyber_section_width'] : 'inherit',
		array_map( 'strval', array_keys( $widths ) ),
		'inherit'
	);

	$vars['--cyber-section-width'] = $widths[ $width ];

	// Tlo: kolor pod spodem, zdjecie na nim, nakladka na samej gorze.
	$bg = cyber_sanitize_color( isset( $row['cyber_section_bg_color'] ) ? $row['cyber_section_bg_color'] : '' );

	if ( '' !== $bg ) {
		$vars['--cyber-section-bg'] = $bg;
	}

	$image = cyber_section_image_url( isset( $row['cyber_section_bg_image'] ) ? $row['cyber_section_bg_image'] : 0 );

	if ( '' !== $image ) {
		$vars['--cyber-section-bg-image'] = sprintf( 'url(%s)', $image );
	}

	$image_mobile = cyber_section_image_url(
		isset( $row['cyber_section_bg_image_mobile'] ) ? $row['cyber_section_bg_image_mobile'] : 0
	);

	if ( '' !== $image_mobile ) {
		$vars['--cyber-section-bg-image-mobile'] = sprintf( 'url(%s)', $image_mobile );
	}

	if ( '' !== $image || '' !== $image_mobile ) {
		$vars['--cyber-section-bg-size'] = cyber_section_choice(
			isset( $row['cyber_section_bg_size'] ) ? $row['cyber_section_bg_size'] : 'cover',
			cyber_section_bg_sizes(),
			'cover'
		);

		$vars['--cyber-section-bg-position'] = sprintf(
			'%1$s %2$s',
			cyber_section_choice(
				isset( $row['cyber_section_bg_position_x'] ) ? $row['cyber_section_bg_position_x'] : 'center',
				cyber_section_bg_positions_x(),
				'center'
			),
			cyber_section_choice(
				isset( $row['cyber_section_bg_position_y'] ) ? $row['cyber_section_bg_position_y'] : 'center',
				cyber_section_bg_positions_y(),
				'center'
			)
		);

		$vars['--cyber-section-bg-repeat'] = empty( $row['cyber_section_bg_repeat'] ) ? 'no-repeat' : 'repeat';
	}

	/*
	 * Nakladka to OSOBNA warstwa, nie kolor tla. W CSS background-color jest
	 * malowany POD background-image, wiec kolor z alfa nie przyciemni zdjecia
	 * — zostanie calkowicie zaslonięty. Przyciemnienie wymaga wlasnego pudelka
	 * nad zdjeciem i stad to pole.
	 */
	$overlay = cyber_sanitize_color(
		isset( $row['cyber_section_overlay_color'] ) ? $row['cyber_section_overlay_color'] : ''
	);

	if ( '' !== $overlay ) {
		$vars['--cyber-section-overlay'] = $overlay;
		$classes[]                       = 'cyber-section--has-overlay';
	}

	// Odstepy: cztery na desktop, cztery na telefon. Media query siedzi w CSS.
	$sides = array(
		'pt' => 'cyber_section_pt',
		'pr' => 'cyber_section_pr',
		'pb' => 'cyber_section_pb',
		'pl' => 'cyber_section_pl',
	);

	foreach ( $sides as $short => $key ) {
		$vars[ '--cyber-section-' . $short ] = sprintf(
			'%dpx',
			cyber_section_spacing( isset( $row[ $key ] ) ? $row[ $key ] : 0 )
		);

		$mobile_key = $key . '_m';

		/*
		 * Zmienna mobilna powstaje TYLKO wtedy, gdy pole faktycznie ma wartosc.
		 * Wypisywana zawsze zabijalaby lancuch zapasowy w arkuszu: wiersz bez
		 * ustawionego odstepu mobilnego dostawalby na telefonie zero zamiast
		 * odziedziczyc wartosc desktopowa.
		 */
		if ( isset( $row[ $mobile_key ] ) && '' !== $row[ $mobile_key ] ) {
			$vars[ '--cyber-section-' . $short . '-m' ] = sprintf(
				'%dpx',
				cyber_section_spacing( $row[ $mobile_key ] )
			);
		}
	}

	$style = cyber_css_declarations( $vars );

	$anchor = isset( $row['cyber_section_anchor'] ) ? sanitize_title( (string) $row['cyber_section_anchor'] ) : '';

	return array(
		'class' => implode( ' ', $classes ),
		'style' => $style,
		'id'    => $anchor,
	);
}

/* -------------------------------------------------------------------------- *
 * Renderowanie
 * -------------------------------------------------------------------------- */

/**
 * Wypisuje wszystkie sekcje przypisane do wpisu.
 *
 * Widok nie siega samodzielnie po stan globalny — kazdy template-part dostaje
 * komplet danych jawnie przez $args (CLAUDE.md sekcja 4). Dlatego petla nie
 * uzywa have_rows()/get_sub_field(): ten idiom wymusilby, zeby plik sekcji sam
 * wolal ACF, czyli dokladnie to, czego sekcja 4 zakazuje.
 *
 * @param int|null $post_id Identyfikator wpisu. Domyslnie biezacy.
 * @return void
 */
function cyber_render_sections( $post_id = null ) {
	if ( ! cyber_is_acf_active() ) {
		return;
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return;
	}

	$rows = get_field( CYBER_SECTIONS_FIELD, $post_id );

	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return;
	}

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || empty( $row['acf_fc_layout'] ) ) {
			continue;
		}

		// Wylacznik: sekcja zaparkowana zostaje w bazie, znika tylko z frontu.
		if ( isset( $row['cyber_section_enabled'] ) && ! $row['cyber_section_enabled'] ) {
			continue;
		}

		if ( CYBER_GLOBAL_SECTION_LAYOUT !== $row['acf_fc_layout'] ) {
			cyber_render_section_row( $row );
			continue;
		}

		/*
		 * Sekcja globalna: wiersz zamienia sie na sekcje wybranego wpisu CPT.
		 * Te przechodza przez ten sam kod co sekcje strony. Wiersze "global"
		 * wewnatrz nie istnieja — odfiltrowuje je cyber_global_section_rows().
		 */
		$global_id   = cyber_global_section_id( $row );
		$global_rows = cyber_global_section_rows( $global_id );

		if ( ! $global_rows ) {
			get_template_part(
				'template-parts/sections/global',
				null,
				array( 'notice' => cyber_global_section_problem( $global_id ) )
			);
			continue;
		}

		foreach ( $global_rows as $global_row ) {
			if ( isset( $global_row['cyber_section_enabled'] ) && ! $global_row['cyber_section_enabled'] ) {
				continue;
			}

			cyber_render_section_row( $global_row );
		}
	}
}

/**
 * Wypisuje jedna sekcje.
 *
 * @param array $row Wiersz Flexible Content (dowolny layout poza "global").
 * @return void
 */
function cyber_render_section_row( array $row ) {
	$types = cyber_section_types();
	$type  = (string) $row['acf_fc_layout'];

	// Layout bez wpisu w rejestrze nie ma czym sie wyrenderowac.
	if ( ! isset( $types[ $type ] ) || CYBER_GLOBAL_SECTION_LAYOUT === $type ) {
		return;
	}

	$attributes       = cyber_section_attributes( $type, $row );
	$attributes['id'] = cyber_section_unique_id( $attributes['id'] );

	get_template_part(
		'template-parts/sections/' . $types[ $type ]['template'],
		null,
		array(
			'attributes' => $attributes,
			'row'        => $row,
		)
	);
}

/**
 * Pilnuje, zeby kotwica sekcji byla unikalna na stronie.
 *
 * Ta sama sekcja globalna wstawiona na strone dwa razy dalaby dwa elementy
 * o tym samym id — niepoprawny HTML, a link #kotwica prowadzilby zawsze do
 * pierwszego. Kolejne wystapienia dostaja przyrostek: oferta, oferta-2...
 *
 * @param string $id Kotwica z cyber_section_attributes().
 * @return string
 */
function cyber_section_unique_id( $id ) {
	static $used = array();

	if ( '' === $id ) {
		return '';
	}

	if ( ! isset( $used[ $id ] ) ) {
		$used[ $id ] = 1;
		return $id;
	}

	++$used[ $id ];

	return $id . '-' . $used[ $id ];
}

/**
 * Otwiera opakowanie sekcji.
 *
 * Wspolne dla wszystkich layoutow, zeby plik sekcji zajmowal sie wylacznie
 * wlasna trescia. Markup jest tu, a nie w kazdym pliku z osobna, bo zmiana
 * struktury opakowania w dwunastu plikach naraz to gwarancja rozjazdu.
 *
 * $with_inner = false pomija kontener .cyber-section__inner. Potrzebuje tego
 * sekcja, ktorej tresc ma wyjsc na pelna szerokosc okna (slider ze zdjeciem
 * "100% przegladarki") — kontener z max-width by ja przycial. Taka sekcja sama
 * odpowiada wtedy za zwezenie tresci i zamyka opakowanie z tym samym argumentem.
 *
 * @param array $attributes Wynik cyber_section_attributes().
 * @param bool  $with_inner Czy otworzyc kontener szerokosci. Domyslnie tak.
 * @return void
 */
function cyber_section_open( array $attributes, $with_inner = true ) {
	printf(
		'<section%1$s class="%2$s" style="%3$s">',
		$attributes['id'] ? sprintf( ' id="%s"', esc_attr( $attributes['id'] ) ) : '',
		esc_attr( $attributes['class'] ),
		esc_attr( $attributes['style'] )
	);

	// Nakladka nad zdjeciem, pod trescia. Puste pudelko, bez roli dla czytnikow.
	if ( false !== strpos( $attributes['class'], 'cyber-section--has-overlay' ) ) {
		echo '<div class="cyber-section__overlay" aria-hidden="true"></div>';
	}

	if ( $with_inner ) {
		echo '<div class="cyber-section__inner">';
	}
}

/**
 * Zamyka opakowanie sekcji.
 *
 * @param bool $with_inner Musi byc taki sam jak przy cyber_section_open().
 * @return void
 */
function cyber_section_close( $with_inner = true ) {
	echo $with_inner ? '</div></section>' : '</section>';
}

/**
 * Wypisuje tresc pola WYSIWYG sekcji.
 *
 * @param array  $row  Wiersz Flexible Content.
 * @param string $slot 'top' albo 'bottom'.
 * @return void
 */
function cyber_section_wysiwyg( array $row, $slot ) {
	$key = 'cyber_section_wysiwyg_' . $slot;

	if ( empty( $row[ $key ] ) ) {
		return;
	}

	printf(
		'<div class="cyber-section__%1$s cyber-wysiwyg">%2$s</div>',
		esc_attr( $slot ),
		cyber_kses_content( $row[ $key ] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku.
	);
}

/**
 * Czy blok WYSIWYG nad albo pod sekcja ma sie wyswietlic.
 *
 * Dla sekcji, w ktorych edytory maja wlaczniki (domyslnie wylaczone). Brak
 * wartosci liczy sie jako wylaczony, zgodnie z default_value pola, wiec panel
 * i front pokazuja ten sam stan. Pusty edytor tez nie daje bloku.
 *
 * @param array  $row    Wiersz Flexible Content.
 * @param string $toggle Przedrostek wlacznika, np. 'cyber_faq_show'.
 * @param string $slot   'top' albo 'bottom'.
 * @return bool
 */
function cyber_section_shows_wysiwyg( array $row, $toggle, $slot ) {
	return ! empty( $row[ $toggle . '_' . $slot ] ) && ! empty( $row[ 'cyber_section_wysiwyg_' . $slot ] );
}

/**
 * Wlaczniki edytorow WYSIWYG: klucz klonu edytora => klucz pola wlacznika.
 *
 * JEDNO miejsce dla wszystkich sekcji z wlacznikami tresci nad i pod.
 * Wczesniej kazda sekcja miala wlasna, identyczna kopie filtra — nowa sekcja
 * dopisuje tu dwa wiersze zamiast kopiowac funkcje.
 *
 * Karuzela uzywa wlacznikow kart (klonuje je z group_section_cards), wiec
 * rozni sie tylko klucz klonu edytora.
 *
 * @return array<string, string>
 */
function cyber_section_wysiwyg_toggles() {
	return array(
		'field_cyber_section_cards_top'       => 'field_cyber_cards_show_top',
		'field_cyber_section_cards_bottom'    => 'field_cyber_cards_show_bottom',
		'field_cyber_section_carousel_top'    => 'field_cyber_cards_show_top',
		'field_cyber_section_carousel_bottom' => 'field_cyber_cards_show_bottom',
		'field_cyber_section_faq_top'         => 'field_cyber_faq_show_top',
		'field_cyber_section_faq_bottom'      => 'field_cyber_faq_show_bottom',
		'field_cyber_section_counter_top'     => 'field_cyber_counter_show_top',
		'field_cyber_section_counter_bottom'  => 'field_cyber_counter_show_bottom',
		'field_cyber_section_contact_top'     => 'field_cyber_contact_section_show_top',
		'field_cyber_section_contact_bottom'  => 'field_cyber_contact_section_show_bottom',
		'field_cyber_section_posts_top'       => 'field_cyber_posts_show_top',
		'field_cyber_section_posts_bottom'    => 'field_cyber_posts_show_bottom',
		'field_cyber_section_table_top'       => 'field_cyber_table_show_top',
		'field_cyber_section_table_bottom'    => 'field_cyber_table_show_bottom',
		'field_cyber_section_gallery_top'     => 'field_cyber_gal_show_top',
		'field_cyber_section_gallery_bottom'  => 'field_cyber_gal_show_bottom',
	);
}

/**
 * Chowa w panelu edytor WYSIWYG, gdy jego wlacznik jest wylaczony.
 *
 * Edytory nad i pod sekcja pochodza ze WSPOLNEJ grupy group_section_content,
 * z ktorej korzysta tez sekcja podstawowa (bez wlacznikow). Warunek wpisany
 * w tej grupie dzialalby we wszystkich sekcjach naraz — dlatego dokladamy go
 * w locie i tylko polom przyniesionym przez konkretny klon.
 *
 * $field['_clone'] niesie klucz klonu, ktory przyniosl pole
 * (includes/acf-field-functions.php). Priorytet 20, po filtrze ACF (10), ktory
 * przywraca polu oryginalny klucz — warunek odwoluje sie do oryginalnego
 * klucza wlacznika, tak jak widzi go JavaScript formularza.
 *
 * @param array $field Pole ACF przygotowywane do wyswietlenia w formularzu.
 * @return array Pole z warunkiem albo bez zmian.
 */
function cyber_section_wysiwyg_condition( $field ) {
	$map = cyber_section_wysiwyg_toggles();

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
add_filter( 'acf/prepare_field', 'cyber_section_wysiwyg_condition', 20 );

/**
 * Warunki nakladane na pola przyniesione przez klon — per klucz klonu.
 *
 * ACF nie przenosi warunku z pola Clone na pola klonowane
 * (pro/fields/class-acf-field-clone.php nie obsluguje conditional_logic),
 * a te same pola zrodlowe w innej sekcji maja byc widoczne zawsze. Warunek
 * dokladamy wiec w locie, jak przy wlacznikach WYSIWYG.
 *
 * Wpis: klucz klonu => array(
 *     'field'    => klucz pola, od ktorego zalezy widocznosc,
 *     'operator' => '==' albo '!=' (True/False porownuje sie z '1'),
 *     'value'    => wartosc,
 *     'only'     => lista oryginalnych kluczy pol, ktorych warunek dotyczy
 *                 (pusta = wszystkie pola klonu),
 * ).
 *
 * @return array<string, array<string, mixed>>
 */
function cyber_section_clone_conditions() {
	return array(
		// Wpisy: siatka tylko bez slidera, ustawienia karuzeli tylko ze sliderem.
		'field_cyber_section_posts_cards'    => array(
			'field'    => 'field_cyber_posts_slider',
			'operator' => '!=',
			'value'    => '1',
			'only'     => array(
				'field_cyber_msg_cards_grid',
				'field_cyber_cards_columns',
				'field_cyber_cards_columns_tablet',
				'field_cyber_cards_columns_mobile',
				'field_cyber_cards_gap_x',
				'field_cyber_cards_gap_y',
			),
		),
		'field_cyber_section_posts_carousel' => array(
			'field'    => 'field_cyber_posts_slider',
			'operator' => '==',
			'value'    => '1',
			'only'     => array(),
		),
	);
}

/**
 * Doklada warunek z cyber_section_clone_conditions() do pola klonu.
 *
 * Istniejacy warunek pola (np. "czas animacji tylko przy autoplay") zostaje:
 * nowa regula jest dopisywana do KAZDEJ grupy OR jako dodatkowe AND.
 *
 * @param array $field Pole ACF przygotowywane do wyswietlenia w formularzu.
 * @return array
 */
function cyber_section_clone_condition( $field ) {
	$map = cyber_section_clone_conditions();

	if ( empty( $field['_clone'] ) || ! isset( $map[ $field['_clone'] ] ) ) {
		return $field;
	}

	$rule = $map[ $field['_clone'] ];

	if ( $rule['only'] && ! in_array( $field['key'], $rule['only'], true ) ) {
		return $field;
	}

	$and    = array(
		'field'    => $rule['field'],
		'operator' => $rule['operator'],
		'value'    => $rule['value'],
	);
	$groups = is_array( $field['conditional_logic'] ) ? $field['conditional_logic'] : array();

	if ( ! $groups ) {
		$groups = array( array() );
	}

	foreach ( $groups as $i => $group ) {
		$groups[ $i ][] = $and;
	}

	$field['conditional_logic'] = $groups;

	return $field;
}
add_filter( 'acf/prepare_field', 'cyber_section_clone_condition', 21 );

/**
 * Lista znacznikow dozwolonych w tresci WYSIWYG sekcji.
 *
 * To lista wp_kses_post() poszerzona o <iframe>. Samo wp_kses_post() wycina
 * ramki w calosci — mapa Google albo film wklejony do edytora znikal z frontu,
 * choc w panelu byl widoczny.
 *
 * Ramka dostaje wylacznie atrybuty potrzebne osadzeniu. Bez srcdoc (tresc
 * HTML wprost w atrybucie) i bez zdarzen on* — wp_kses() odrzuca kazdy
 * atrybut spoza listy, a src przechodzi przez jego filtr protokolow, wiec
 * javascript: i data: nie przejda.
 *
 * @return array<string, array<string, bool>>
 */
function cyber_kses_content_tags() {
	$tags = wp_kses_allowed_html( 'post' );

	$tags['iframe'] = array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'title'           => true,
		'style'           => true,
		'class'           => true,
		'frameborder'     => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'loading'         => true,
		'referrerpolicy'  => true,
	);

	return $tags;
}

/**
 * Filtruje tresc WYSIWYG sekcji przed wypisaniem.
 *
 * Uzywane przez kazda sekcje, ktora wypisuje tresc edytora: WYSIWYG nad i pod
 * sekcja, kolumny tekstowe, tresc slajdu.
 *
 * @param string $html Tresc pola.
 * @return string
 */
function cyber_kses_content( $html ) {
	return wp_kses( (string) $html, cyber_kses_content_tags() );
}

/* -------------------------------------------------------------------------- *
 * Assety
 * -------------------------------------------------------------------------- */

/**
 * Styl sekcji — kolejkowany tylko tam, gdzie sekcje faktycznie sa.
 *
 * Enqueue warunkowy zgodnie z CLAUDE.md sekcja 10. Puste pole Flexible Content
 * na stronie bez sekcji nie ma powodu ciagnac arkusza.
 *
 * @return void
 */
function cyber_section_assets() {
	if ( ! is_singular() || ! cyber_is_acf_active() ) {
		return;
	}

	// Z rozwinietymi sekcjami globalnymi — to one moga byc jedyna trescia strony.
	if ( ! cyber_section_rows_expanded( get_queried_object_id() ) ) {
		return;
	}

	wp_enqueue_style(
		'cyber-sections',
		CYBER_URI . '/assets/css/sections.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/sections.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_section_assets', 20 );
