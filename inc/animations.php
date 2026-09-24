<?php
/**
 * Animacje wejscia sekcji — silnik wlasny (IntersectionObserver + CSS).
 *
 * DWIE WARSTWY, CELOWO ROZDZIELONE. Ten plik i dwa pliki assetow to SILNIK:
 *
 *   inc/animations.php        rejestr animacji, atrybut na sekcji, kolejkowanie
 *   assets/js/animations.js   obserwuje sekcje i uruchamia animacje
 *   assets/css/animations.css wyglad kazdej animacji
 *
 * DANE nie naleza do silnika i zostaja przy jego wymianie: pole sekcji
 * cyber_section_animation (group_section_settings), zakladka Global Options
 * "Animacje", wpisy anim_* w cyber_option_schema() i zmienne CSS
 * z cyber_animation_css() (inc/enqueue.php).
 *
 * WYMIANA SILNIKA (np. na biblioteke): usun trzy pliki wyzej i linie
 * 'inc/animations.php' w functions.php, a nowy modul wepnij w ten sam filtr
 * cyber_section_attributes i czytaj te same wartosci pola. Klucze animacji
 * (from-bottom, zoom-in...) sa zapisane w bazie przy kazdej sekcji — nowy
 * silnik tlumaczy je na swoje nazwy, a nie zmienia. Pelna instrukcja:
 * docs/components.md, sekcja "Animacje wejscia sekcji".
 *
 * Silnik jest zbudowany tak, zeby strona bez niego wygladala poprawnie:
 * sekcje sa w HTML widoczne, ukrywa je dopiero klasa dodana skryptem.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wartosc pola oznaczajaca "bez animacji".
 */
const CYBER_ANIMATION_NONE = 'none';

/**
 * Rejestr animacji — jedyne zrodlo listy.
 *
 * Z niego biora sie opcje w panelu (acf/load_field) i biala lista przy
 * renderowaniu. Klucz jest zapisywany w bazie przy kazdej sekcji, wiec
 * obowiazuje ta sama zasada co przy kluczu layoutu (CLAUDE.md sekcja 7):
 * etykiete mozna zmienic, klucza nie. Wiersz z kluczem spoza rejestru
 * renderuje sie po prostu bez animacji.
 *
 * Kazda animacja poza "none" musi miec regule w assets/css/animations.css.
 *
 * @return array<string, string> Klucz => etykieta w panelu.
 */
function cyber_animation_types() {
	return array(
		CYBER_ANIMATION_NONE => 'Brak',
		'fade'               => 'Zanikanie',
		'from-bottom'        => 'Wjazd z dolu',
		'from-top'           => 'Wjazd z gory',
		'from-left'          => 'Wjazd z lewej',
		'from-right'         => 'Wjazd z prawej',
		'zoom-in'            => 'Powiekszenie',
		'zoom-out'           => 'Pomniejszenie',
		'blur'               => 'Rozmycie',
	);
}

/**
 * Wypelnia liste animacji w panelu z rejestru.
 *
 * Ten sam wzorzec co lista czcionek (inc/fonts.php): JSON ma kopie opcji,
 * zeby pole mialo sens takze bez tego modulu, ale o tym, co widzi redaktor,
 * decyduje rejestr.
 *
 * @param array $field Pole ACF.
 * @return array
 */
function cyber_animation_field( $field ) {
	$field['choices'] = cyber_animation_types();

	return $field;
}
add_filter( 'acf/load_field/key=field_cyber_section_animation', 'cyber_animation_field' );

/**
 * Animacja wybrana w wierszu sekcji, po walidacji.
 *
 * @param array $row Wiersz Flexible Content.
 * @return string Klucz z rejestru albo CYBER_ANIMATION_NONE.
 */
function cyber_section_animation( array $row ) {
	$value = isset( $row['cyber_section_animation'] ) ? (string) $row['cyber_section_animation'] : '';

	return isset( cyber_animation_types()[ $value ] ) ? $value : CYBER_ANIMATION_NONE;
}

/**
 * Doklada do opakowania sekcji atrybut data-cyber-animate.
 *
 * Atrybut, a nie klasa modyfikujaca: animacja jest osobna osia obok wariantu
 * sekcji (CLAUDE.md sekcja 20, "Stan to nie wariant"), a biblioteki animacji
 * konfiguruje sie wlasnie atrybutami data-*. Przy wymianie silnika zmienia sie
 * nazwa i wartosc atrybutu, a nie struktura markupu.
 *
 * @param array  $attributes Atrybuty z cyber_section_attributes().
 * @param string $type       Klucz layoutu (nieuzywany — animacja dotyczy kazdej sekcji).
 * @param array  $row        Wiersz Flexible Content.
 * @return array
 */
function cyber_animation_section_attributes( $attributes, $type, $row ) {
	unset( $type );

	if ( ! cyber_get_option( 'anim_enable' ) ) {
		return $attributes;
	}

	$animation = cyber_section_animation( (array) $row );

	if ( CYBER_ANIMATION_NONE !== $animation ) {
		$attributes['data']['cyber-animate'] = $animation;
	}

	return $attributes;
}
add_filter( 'cyber_section_attributes', 'cyber_animation_section_attributes', 10, 3 );

/**
 * Czy na wpisie jest choc jedna animowana sekcja.
 *
 * Z rozwinietymi sekcjami globalnymi — animowana sekcja moze stac wylacznie
 * we wpisie sekcji globalnej. Wylaczone wiersze sa juz odfiltrowane.
 *
 * @param int $post_id Identyfikator wpisu.
 * @return bool
 */
function cyber_animations_needed( $post_id ) {
	if ( ! cyber_get_option( 'anim_enable' ) ) {
		return false;
	}

	foreach ( cyber_section_rows_expanded( $post_id ) as $row ) {
		if ( CYBER_ANIMATION_NONE !== cyber_section_animation( $row ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Skrypt startowy w <head>: flaga dla CSS i konfiguracja silnika.
 *
 * DLACZEGO W HEAD. Sekcje ukrywa klasa cyber-anim-ready na <html>. Gdyby
 * dodawal ja dopiero skrypt w stopce, przegladarka zdazylaby pokazac sekcje,
 * a potem by je schowala — widoczne mrugniecie na gorze strony. Kilka bajtow
 * w <head> dziala przed pierwszym malowaniem.
 *
 * Flaga NIE powstaje, gdy odwiedzajacy ma w systemie ograniczenie ruchu albo
 * przegladarka nie zna IntersectionObserver — wtedy sekcje sa po prostu
 * widoczne. Jesli glowny skrypt nie wystartuje w 3 s (blad sieci, blokada),
 * flaga jest zdejmowana i strona pokazuje tresc zamiast pustych miejsc.
 *
 * @return string Kod JS.
 */
function cyber_animation_init_script() {
	$config = array(
		'offset' => (int) cyber_get_option( 'anim_offset' ),
		'once'   => (bool) cyber_get_option( 'anim_once' ),
	);

	return 'window.cyberAnimations=' . wp_json_encode( $config ) . ';'
		. '(function(d,w){'
		. 'if(!("IntersectionObserver" in w)||w.matchMedia("(prefers-reduced-motion: reduce)").matches){return;}'
		. 'var c=d.documentElement;c.classList.add("cyber-anim-ready");'
		. 'w.setTimeout(function(){if(!w.cyberAnimationsStarted){c.classList.remove("cyber-anim-ready");}},3000);'
		. '})(document,window);';
}

/**
 * Assety animacji — tylko na wpisie z animowana sekcja (CLAUDE.md sekcja 10).
 *
 * @return void
 */
function cyber_animation_assets() {
	if ( ! is_singular() || ! cyber_is_acf_active() || ! cyber_animations_needed( get_queried_object_id() ) ) {
		return;
	}

	wp_enqueue_style(
		'cyber-animations',
		CYBER_URI . '/assets/css/animations.css',
		array( 'cyber-main' ),
		cyber_asset_version( 'assets/css/animations.css' )
	);

	// Uchwyt bez pliku — nosi wylacznie skrypt startowy w <head>.
	wp_register_script( 'cyber-animations-init', false, array(), CYBER_VERSION, false );
	wp_enqueue_script( 'cyber-animations-init' );
	wp_add_inline_script( 'cyber-animations-init', cyber_animation_init_script() );

	wp_enqueue_script(
		'cyber-animations',
		CYBER_URI . '/assets/js/animations.js',
		array(),
		cyber_asset_version( 'assets/js/animations.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cyber_animation_assets', 20 );
