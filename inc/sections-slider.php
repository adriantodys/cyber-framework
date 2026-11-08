<?php
/**
 * Sekcja "Slider".
 *
 * Karuzela slajdow ze zdjeciem (osobny kadr na telefon), trescia WYSIWYG
 * i przyciskiem. Przewijanie obsluguje Swiper 14.2.0 — jedyna zewnetrzna
 * biblioteka JS w motywie, zatwierdzona jawnie (CLAUDE.md sekcja 2).
 *
 * BIBLIOTEKA. Nie ladujemy pelnej paczki swiper-bundle (43,8 kB gzip), tylko
 * rdzen i piec modulow: nawigacja, paginacja, autoplay, dostepnosc, klawiatura
 * (27,7 kB gzip). Pliki leza w assets/vendor/swiper-14.2.0/ bajt w bajt jak
 * w paczce npm i sa ladowane jako moduly ES — bez narzedzi do budowania.
 * Wersja siedzi w nazwie katalogu, wiec aktualizacja biblioteki zmienia adresy
 * plikow i sama uniewaznia cache przegladarki.
 *
 * DWA TRYBY SZEROKOSCI (pole "Szerokosc zdjecia"):
 *
 *   section — cala karuzela miesci sie w szerokosci sekcji; zdjecie i tresc
 *             maja te sama szerokosc.
 *   full    — zdjecie idzie od krawedzi do krawedzi okna, a tresc zostaje
 *             w szerokosci sekcji. Opakowanie otwiera sie wtedy BEZ kontenera
 *             .cyber-section__inner, ktory przycialby zdjecie, a zwezenie
 *             tresci przejmuje sam slajd.
 *
 * ODSTEPY. Padding Y to odstep sekcji od sasiadow (gora i dol). Padding X
 * dziala na TRESC slajdu, nie na sekcje — gdyby zwezal sekcje, zdjecie
 * w trybie "full" nie dobiloby do krawedzi okna, czyli tryb przestalby dzialac.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Katalog biblioteki Swiper wzgledem katalogu motywu.
 */
const CYBER_SWIPER_DIR = 'assets/vendor/swiper-14.2.0';

/**
 * Dozwolone tryby szerokosci zdjecia.
 *
 * @return string[]
 */
function cyber_slider_image_widths() {
	return array( 'section', 'full' );
}

/**
 * Dopasowanie zdjecia: wartosc pola => wartosc object-fit.
 *
 * Pole nazywa sie jak background-size (cover/contain/auto), bo tak mysli o tym
 * redaktor. Zdjecie jest jednak znacznikiem img, a object-fit nie zna "auto" —
 * jego odpowiednikiem jest "none", czyli oryginalny rozmiar bez skalowania.
 *
 * @return array<string, string>
 */
function cyber_slider_image_fits() {
	return array(
		'cover'   => 'cover',
		'contain' => 'contain',
		'auto'    => 'none',
	);
}

/**
 * Zwraca zawartosc wiersza z przepisanymi odstepami na format sekcji.
 *
 * Opakowanie sekcji (cyber_section_attributes) zna cztery odstepy. Slider ma
 * jeden pionowy, wiec przepisujemy go na gore i dol, a boki zerujemy — padding X
 * idzie na tresc slajdu, patrz naglowek pliku.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array Wiersz gotowy dla cyber_section_attributes().
 */
function cyber_slider_section_row( array $row ) {
	$pad_y = isset( $row['cyber_slider_pad_y'] ) ? $row['cyber_slider_pad_y'] : 0;

	$row['cyber_section_pt'] = $pad_y;
	$row['cyber_section_pb'] = $pad_y;
	$row['cyber_section_pl'] = 0;
	$row['cyber_section_pr'] = 0;

	// Slider nie ma tla, nakladki sekcji ani wlasnych odstepow mobilnych.
	unset(
		$row['cyber_section_bg_color'],
		$row['cyber_section_bg_image'],
		$row['cyber_section_bg_image_mobile'],
		$row['cyber_section_overlay_color'],
		$row['cyber_section_pt_m'],
		$row['cyber_section_pb_m'],
		$row['cyber_section_pl_m'],
		$row['cyber_section_pr_m']
	);

	return $row;
}

/**
 * Konfiguracja karuzeli po walidacji.
 *
 * @param array $row   Wiersz Flexible Content.
 * @param int   $count Liczba slajdow do wyswietlenia.
 * @return array<string, mixed>
 */
function cyber_slider_config( array $row, $count ) {
	$many = $count > 1;

	$delay = isset( $row['cyber_slider_delay'] ) ? absint( $row['cyber_slider_delay'] ) : 5000;
	$delay = max( 1000, min( 30000, $delay ? $delay : 5000 ) );

	/*
	 * Pojedynczy slajd nie ma dokad przewijac: petla, autoplay, strzalki
	 * i kropki sa wtedy wylaczane niezaleznie od ustawien. Petla Swipera
	 * dziala od dwoch slajdow (slides.length >= slidesPerView + loopedSlides).
	 */
	return array(
		'loop'       => $many && ! empty( $row['cyber_slider_loop'] ),
		'autoplay'   => $many && ! empty( $row['cyber_slider_autoplay'] ),
		'delay'      => $delay,
		'navigation' => $many && ! empty( $row['cyber_slider_navigation'] ),
		'pagination' => $many && ! empty( $row['cyber_slider_pagination'] ),
	);
}

/**
 * Klasy i zmienne CSS karuzeli.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array{class: string, style: string, full: bool}
 */
function cyber_slider_attributes( array $row ) {
	$width = cyber_section_choice(
		isset( $row['cyber_slider_image_width'] ) ? $row['cyber_slider_image_width'] : 'section',
		cyber_slider_image_widths(),
		'section'
	);

	$classes = array( 'cyber-slider', 'swiper', 'cyber-slider--' . $width );

	$classes[] = 'cyber-slider--x-' . cyber_section_choice(
		isset( $row['cyber_slider_content_x'] ) ? $row['cyber_slider_content_x'] : 'left',
		cyber_alignments(),
		'left'
	);

	$classes[] = 'cyber-slider--y-' . cyber_section_choice(
		isset( $row['cyber_slider_content_y'] ) ? $row['cyber_slider_content_y'] : 'center',
		cyber_section_bg_positions_y(),
		'center'
	);

	$vars = array();

	$height                   = isset( $row['cyber_slider_height'] ) ? absint( $row['cyber_slider_height'] ) : 600;
	$vars['--cyber-slider-h'] = sprintf( '%dpx', max( 100, min( 2000, $height ? $height : 600 ) ) );

	$height_m                   = isset( $row['cyber_slider_height_mobile'] ) ? absint( $row['cyber_slider_height_mobile'] ) : 400;
	$vars['--cyber-slider-h-m'] = sprintf( '%dpx', max( 100, min( 2000, $height_m ? $height_m : 400 ) ) );

	$fits = cyber_slider_image_fits();
	$fit  = cyber_section_choice(
		isset( $row['cyber_slider_image_fit'] ) ? $row['cyber_slider_image_fit'] : 'cover',
		array_keys( $fits ),
		'cover'
	);

	$vars['--cyber-slider-fit'] = $fits[ $fit ];

	$vars['--cyber-slider-position'] = sprintf(
		'%1$s %2$s',
		cyber_section_choice(
			isset( $row['cyber_slider_image_position_x'] ) ? $row['cyber_slider_image_position_x'] : 'center',
			cyber_section_bg_positions_x(),
			'center'
		),
		cyber_section_choice(
			isset( $row['cyber_slider_image_position_y'] ) ? $row['cyber_slider_image_position_y'] : 'center',
			cyber_section_bg_positions_y(),
			'center'
		)
	);

	$vars['--cyber-slider-pad-x'] = sprintf(
		'%dpx',
		cyber_section_spacing( isset( $row['cyber_slider_pad_x'] ) ? $row['cyber_slider_pad_x'] : 0 )
	);

	$overlay = cyber_sanitize_color( isset( $row['cyber_slider_overlay'] ) ? $row['cyber_slider_overlay'] : '' );

	if ( '' !== $overlay ) {
		$vars['--cyber-slider-overlay'] = $overlay;
		$classes[]                      = 'cyber-slider--has-overlay';
	}

	$style = cyber_css_declarations( $vars );

	return array(
		'class' => implode( ' ', $classes ),
		'style' => $style,
		'full'  => 'full' === $width,
	);
}

/**
 * Normalizuje slajdy repeatera.
 *
 * Slajd bez zdjecia i bez tresci jest pomijany — pusty wiersz repeatera
 * zdarza sie przy kazdym zapisie.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array<int, array<string, mixed>>
 */
function cyber_slider_slides( array $row ) {
	$slides = isset( $row['cyber_slider_slides'] ) ? $row['cyber_slider_slides'] : array();

	if ( empty( $slides ) || ! is_array( $slides ) ) {
		return array();
	}

	$button_size = cyber_section_choice(
		isset( $row['cyber_slider_button_size'] ) ? $row['cyber_slider_button_size'] : 'medium',
		cyber_button_sizes(),
		'medium'
	);

	$out = array();

	foreach ( $slides as $slide ) {
		if ( ! is_array( $slide ) ) {
			continue;
		}

		$image   = absint( isset( $slide['cyber_slide_image'] ) ? $slide['cyber_slide_image'] : 0 );
		$mobile  = absint( isset( $slide['cyber_slide_image_mobile'] ) ? $slide['cyber_slide_image_mobile'] : 0 );
		$content = isset( $slide['cyber_slide_content'] ) ? (string) $slide['cyber_slide_content'] : '';
		$link    = isset( $slide['cyber_slide_link'] ) ? $slide['cyber_slide_link'] : array();
		$video   = ! empty( $slide['cyber_slide_video_on'] )
			? cyber_slider_video_source( isset( $slide['cyber_slide_video'] ) ? $slide['cyber_slide_video'] : 0 )
			: array();

		if ( ! $image && ! $mobile && ! $video && '' === trim( wp_strip_all_tags( $content ) ) && false === stripos( $content, '<iframe' ) ) {
			continue;
		}

		// Wspolna normalizacja pola Link (inc/helpers.php) — ta sama co w kartach.
		$link = cyber_normalize_link( $link );

		$out[] = array(
			// Bez zdjecia desktopowego mobilne gra obie role.
			'image'       => $image ? $image : $mobile,
			'image_m'     => $image ? $mobile : 0,
			'video'       => $video,
			'content'     => $content,
			'url'         => $link ? $link['url'] : '',
			'label'       => $link ? $link['label'] : '',
			'target'      => $link ? $link['target'] : '',
			'button_size' => $button_size,
		);
	}

	return $out;
}

/**
 * Dozwolone formaty wideo slajdu — lista dozwolonych typow MIME.
 *
 * Atrybut type w <source> jest po prostu typem MIME zalacznika, wiec lista
 * jest lista, nie mapa 'video/mp4' => 'video/mp4'.
 *
 * @return string[]
 */
function cyber_slider_video_types() {
	return array( 'video/mp4', 'video/webm' );
}

/**
 * Plik wideo slajdu po walidacji.
 *
 * Pole File zwraca ID zalacznika. Typ sprawdzamy po stronie motywu, nie tylko
 * w ustawieniu pola (mime_types): pole mozna przestawic w panelu, a plik
 * w innym formacie i tak by sie nie odtworzyl.
 *
 * @param mixed $id Identyfikator zalacznika.
 * @return array{url?: string, type?: string} Pusta tablica, gdy nie ma czego odtworzyc.
 */
function cyber_slider_video_source( $id ) {
	$id = absint( $id );

	if ( ! $id ) {
		return array();
	}

	$mime = (string) get_post_mime_type( $id );
	$url  = wp_get_attachment_url( $id );

	if ( ! $url || ! in_array( $mime, cyber_slider_video_types(), true ) ) {
		return array();
	}

	return array(
		'url'  => esc_url_raw( $url ),
		'type' => $mime,
	);
}

/**
 * Wypisuje wideo w tle slajdu.
 *
 * Wideo lezy NAD zdjeciem, ktore zostaje pod spodem jako kadr zastepczy:
 * widac je, zanim wideo zaladuje pierwsza klatke, bez JavaScriptu i przy
 * ograniczeniu animacji w systemie (wtedy arkusz chowa wideo w ogole).
 *
 * Bez atrybutu autoplay: odtwarzanie wlacza assets/js/slider.js, i to tylko
 * na aktywnym slajdzie — reszta stoi i nie pobiera danych bez potrzeby.
 * Wideo jest dekoracja tla, wiec bez kontrolek, bez dzwieku i ukryte przed
 * czytnikami ekranu. Tresc slajdu niesie WYSIWYG, nie film.
 *
 * @param array $slide Slajd z cyber_slider_slides().
 * @return void
 */
function cyber_slider_video( array $slide ) {
	if ( empty( $slide['video'] ) ) {
		return;
	}

	printf(
		'<video class="cyber-slide__video" muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1"><source src="%1$s" type="%2$s" /></video>',
		esc_url( $slide['video']['url'] ),
		esc_attr( $slide['video']['type'] )
	);
}

/**
 * Wypisuje zdjecie slajdu jako <picture>.
 *
 * Znacznik img zamiast tla CSS: zdjecie ma tekst alternatywny, srcset,
 * a telefon pobiera WYLACZNIE swoj kadr — <source> z media query sprawia,
 * ze przegladarka w ogole nie siega po wersje desktopowa.
 *
 * Pierwszy slajd jest widoczny od razu, wiec laduje sie priorytetowo
 * (fetchpriority="high", bez lazy). Kolejne czekaja, az beda potrzebne.
 *
 * @param array $slide Slajd z cyber_slider_slides().
 * @param bool  $first Czy to pierwszy slajd karuzeli.
 * @return void
 */
function cyber_slider_picture( array $slide, $first ) {
	if ( ! $slide['image'] ) {
		return;
	}

	$attrs = array(
		'class'    => 'cyber-slide__image',
		'loading'  => $first ? 'eager' : 'lazy',
		'decoding' => 'async',
	);

	if ( $first ) {
		$attrs['fetchpriority'] = 'high';
	}

	echo '<picture class="cyber-slide__media">';

	if ( $slide['image_m'] ) {
		$srcset = wp_get_attachment_image_srcset( $slide['image_m'], 'large' );

		if ( ! $srcset ) {
			$srcset = (string) wp_get_attachment_image_url( $slide['image_m'], 'large' );
		}

		if ( $srcset ) {
			$breakpoints = cyber_breakpoints();

			printf(
				'<source media="(max-width: %1$dpx)" srcset="%2$s" sizes="100vw" />',
				(int) $breakpoints['mobile'],
				esc_attr( $srcset )
			);
		}
	}

	echo wp_get_attachment_image( $slide['image'], 'full', false, $attrs );
	echo '</picture>';
}

/* -------------------------------------------------------------------------- *
 * Assety
 * -------------------------------------------------------------------------- */

/**
 * Czy wpis ma choc jedna sekcje korzystajaca ze Swipera.
 *
 * Swipera uzywaja dwie sekcje: "Slider" i "Karuzela kart". Obie obsluguje
 * ten sam skrypt assets/js/slider.js, wiec assety sa wspolne.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return bool
 */
function cyber_slider_on_page( $post_id ) {
	if ( ! cyber_is_acf_active() ) {
		return false;
	}

	// Z rozwinietymi sekcjami globalnymi: karuzela moze przyjsc z wpisu CPT.
	foreach ( cyber_section_rows_expanded( $post_id ) as $row ) {
		if ( isset( $row['acf_fc_layout'] ) && in_array( $row['acf_fc_layout'], array( 'slider', 'carousel' ), true ) ) {
			return true;
		}

		// Sekcja Wpisy w trybie slidera uzywa tej samej karuzeli.
		if ( cyber_posts_needs_swiper( $row ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Style i skrypt slidera — tylko na wpisie, ktory ma slider.
 *
 * Enqueue warunkowy zgodnie z CLAUDE.md sekcja 10: 27,7 kB biblioteki
 * nie ma prawa ladowac sie na stronie bez karuzeli.
 *
 * @return void
 */
function cyber_slider_assets() {
	if ( ! is_singular() || ! cyber_slider_on_page( get_queried_object_id() ) ) {
		return;
	}

	$vendor = CYBER_URI . '/' . CYBER_SWIPER_DIR;

	/*
	 * Arkusze dostawcy bez wersji z filemtime — wersja siedzi w nazwie
	 * katalogu (CLAUDE.md sekcja 2, pkt 3), wiec aktualizacja Swipera sama
	 * zmienia adres pliku i uniewaznia cache przegladarki. null, a nie brak
	 * argumentu: brak dokleja ?ver= z wersja WordPressa, czyli query string,
	 * ktory nie ma tu nic do rzeczy.
	 */
	// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion -- patrz wyzej.
	wp_enqueue_style( 'swiper', $vendor . '/swiper.min.css', array(), null );
	wp_enqueue_style( 'swiper-navigation', $vendor . '/modules/navigation.min.css', array( 'swiper' ), null );
	wp_enqueue_style( 'swiper-pagination', $vendor . '/modules/pagination.min.css', array( 'swiper' ), null );
	wp_enqueue_style( 'swiper-a11y', $vendor . '/modules/a11y.min.css', array( 'swiper' ), null );
	// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion

	/*
	 * MODUL ES NATYWNIE. Biblioteka Swiper jest dostarczona jako moduly ES
	 * (import/export), a skrypt motywu je importuje — przegladarka wykona
	 * import tylko ze skryptu type="module". WordPress od 6.5 ma do tego
	 * wlasne API (Script Modules): wypisuje znacznik z wlasciwym typem
	 * i odklada go na koniec dokumentu. Wczesniej motyw podmienial atrybut
	 * type regexem w filtrze script_loader_tag — stad "Requires at least: 6.5"
	 * w style.css.
	 *
	 * Moduly NIE obsluguja wp_localize_script() ani wp_add_inline_script():
	 * etykiety dla czytnikow ekranu jada w atrybucie data-i18n kazdej karuzeli
	 * (cyber_slider_i18n()), a etykiety przycisku pauzy w data-* na samym
	 * przycisku (cyber_slider_autoplay_button()).
	 */
	wp_enqueue_script_module(
		'cyber-slider',
		CYBER_URI . '/assets/js/slider.js',
		array(),
		cyber_asset_version( 'assets/js/slider.js' )
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_slider_assets', 20 );

/**
 * Etykiety karuzeli dla czytnikow ekranu (modul dostepnosci Swipera).
 *
 * @return array<string, string>
 */
function cyber_slider_strings() {
	return array(
		'prev'       => __( 'Poprzedni slajd', 'cyber-framework' ),
		'next'       => __( 'Następny slajd', 'cyber-framework' ),
		'first'      => __( 'To jest pierwszy slajd', 'cyber-framework' ),
		'last'       => __( 'To jest ostatni slajd', 'cyber-framework' ),
		'pagination' => __( 'Przejdź do slajdu {{index}}', 'cyber-framework' ),
	);
}

/**
 * Etykiety karuzeli jako JSON do atrybutu data-i18n.
 *
 * Skrypt jest modulem ES, wiec nie dostanie danych przez wp_localize_script()
 * — czyta je z atrybutu elementu karuzeli. Wynik trafia w szablonie do
 * esc_attr(); cudzyslowy JSON-a wracaja do postaci dla skryptu przy odczycie
 * atrybutu przez przegladarke.
 *
 * @return string
 */
function cyber_slider_i18n() {
	return (string) wp_json_encode( cyber_slider_strings() );
}

/**
 * Wypisuje przycisk zatrzymania automatycznego przewijania (WCAG 2.2.2).
 *
 * Ruch, ktory startuje sam i trwa dluzej niz piec sekund, musi dac sie
 * zatrzymac — dotyczy autoplay Swipera i tasmy ciaglej karuzeli. Przycisk
 * wychodzi z atrybutem hidden: bez JavaScriptu nie ma czego zatrzymywac,
 * a przy ograniczeniu animacji w systemie skrypt autoplay nie uruchamia
 * i przycisku nie odslania. Oba stany (pauza / wznowienie) niosa osobne
 * etykiety w data-*, skrypt przelacza je razem z aria-pressed.
 *
 * Ikony pauzy i odtwarzania pochodza z rejestru cyber_icons() ('pause',
 * 'play') przez cyber_get_icon() — CLAUDE.md sekcja 22, zadnych SVG poza rejestrem.
 *
 * @param string $base Przedrostek klas BEM karuzeli: 'cyber-slider' albo 'cyber-carousel'.
 * @return void
 */
function cyber_slider_autoplay_button( $base ) {
	$base = sanitize_html_class( $base );

	printf(
		'<button type="button" class="cyber-autoplay %1$s__autoplay" aria-pressed="false" aria-label="%2$s" data-label-pause="%2$s" data-label-play="%3$s" hidden>%4$s%5$s</button>',
		esc_attr( $base ),
		esc_attr__( 'Zatrzymaj automatyczne przewijanie', 'cyber-framework' ),
		esc_attr__( 'Wznow automatyczne przewijanie', 'cyber-framework' ),
		// Ikony z rejestru cyber_icons() (CLAUDE.md sekcja 22): zaufany, staly markup SVG.
		cyber_get_icon( 'pause' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		cyber_get_icon( 'play' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}
