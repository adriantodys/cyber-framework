<?php
/**
 * Integracja z Contact Form 7 — wspolna warstwa ochronna.
 *
 * Contact Form 7 jest zaleznoscia MIEKKA, tak jak WooCommerce (inc/woocommerce.php).
 * Motyw dziala bez niej w calosci; sekcja kontaktowa bez wtyczki pokazuje dane
 * kontaktowe i tresc, a miejsce formularza zostaje puste.
 *
 * Ten plik jest JEDYNYM miejscem, ktore o tym decyduje (CLAUDE.md sekcja 2).
 * Kolejne moduly z formularzem pytaja stad i dopisuja sie do filtra
 * `cyber_cf7_required_by`, zamiast sprawdzac wtyczke u siebie.
 *
 * Trzy poziomy komunikatu:
 * - gosc    — nie widzi nic,
 * - front   — zalogowany administrator widzi podpowiedz w miejscu formularza,
 * - panel   — ostrzezenie dla activate_plugins, tylko gdy formularz jest uzyty.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Typ tresci formularzy Contact Form 7.
 */
const CYBER_CF7_POST_TYPE = 'wpcf7_contact_form';

/**
 * Czy Contact Form 7 jest aktywne.
 *
 * @return bool
 */
function cyber_is_cf7_active() {
	return defined( 'WPCF7_VERSION' ) && function_exists( 'wpcf7_contact_form' );
}

/**
 * Wpisy, ktorych sekcje kontaktowe maja wybrany formularz.
 *
 * Szuka po meta: ACF zapisuje wybor jako cyber_sections_{N}_cyber_contact_section_form.
 * Obejmuje takze sekcje globalne. Wynik zapamietany na czas zadania.
 *
 * @return WP_Post[]
 */
function cyber_cf7_used_on() {
	static $posts = null;

	if ( null !== $posts ) {
		return $posts;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- wyszukiwanie po wzorcu klucza meta, tylko dla administratora.
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->postmeta} m
			INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE m.meta_key LIKE %s AND m.meta_value NOT IN ( '', '0' )
			AND p.post_type <> 'revision' AND p.post_status = 'publish'
			ORDER BY p.post_title ASC",
			$wpdb->esc_like( CYBER_SECTIONS_FIELD . '_' ) . '%' . $wpdb->esc_like( '_cyber_contact_section_form' )
		)
	);

	$posts = array_filter( array_map( 'get_post', array_map( 'absint', $ids ) ) );

	return $posts;
}

/**
 * Funkcje motywu, ktore w BIEZACEJ konfiguracji wymagaja Contact Form 7.
 *
 * Pusta lista, dopoki zadna opublikowana sekcja nie ma wybranego formularza —
 * sam brak wtyczki nie jest bledem.
 *
 * @return string[] Etykiety dla czlowieka.
 */
function cyber_cf7_required_by() {
	$features = array();
	$posts    = cyber_cf7_used_on();

	if ( $posts ) {
		$features[] = sprintf(
			/* translators: %s: lista tytulow stron. */
			__( 'formularz w sekcji Kontakt (%s)', 'cyber-framework' ),
			implode( ', ', wp_list_pluck( $posts, 'post_title' ) )
		);
	}

	/**
	 * Lista funkcji motywu wymagajacych aktywnego Contact Form 7.
	 *
	 * @param string[] $features Etykiety funkcji.
	 */
	return (array) apply_filters( 'cyber_cf7_required_by', $features );
}

/**
 * Ostrzezenie w panelu o braku Contact Form 7.
 *
 * Tylko dla activate_plugins i tylko wtedy, gdy formularz faktycznie jest uzyty.
 * Zapytanie o uzycie idzie wylacznie przy braku wtyczki.
 *
 * @return void
 */
function cyber_cf7_missing_notice() {
	if ( cyber_is_cf7_active() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$features = cyber_cf7_required_by();

	if ( ! $features ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: lista funkcji motywu wymagajacych Contact Form 7. */
				__( 'Cyber Framework: %s wymaga aktywnej wtyczki Contact Form 7. Bez niej formularz sie nie wyswietla — reszta sekcji dziala normalnie.', 'cyber-framework' ),
				implode( ', ', $features )
			)
		)
	);
}
add_action( 'admin_notices', 'cyber_cf7_missing_notice' );

/**
 * Podpowiedz na froncie, gdy WYBRANEGO formularza nie da sie wyswietlic.
 *
 * Wylacznie dla zalogowanego administratora — gosc nie dostaje nic. Sekcja bez
 * wybranego formularza to poprawny stan (prawa kolumna z sama trescia), wiec
 * podpowiedzi wtedy nie ma. Zwraca czysty tekst; escapuje widok.
 *
 * @param int $form_id Wybrany formularz.
 * @return string
 */
function cyber_cf7_missing_hint( $form_id ) {
	if ( ! $form_id || ! current_user_can( 'activate_plugins' ) ) {
		return '';
	}

	if ( ! cyber_is_cf7_active() ) {
		return __( 'Formularz: wtyczka Contact Form 7 jest nieaktywna. Goscie nie widza w tym miejscu niczego.', 'cyber-framework' );
	}

	return __( 'Formularz: wybrany formularz Contact Form 7 nie istnieje albo nie jest opublikowany.', 'cyber-framework' );
}

/**
 * HTML formularza Contact Form 7.
 *
 * Pusty string, gdy wtyczka jest nieaktywna albo ID nie wskazuje opublikowanego
 * formularza. Markup generuje wtyczka (shortcode) — motyw go nie przepisuje,
 * wyglad daje arkusz.
 *
 * @param int $form_id Identyfikator formularza.
 * @return string
 */
function cyber_cf7_form_html( $form_id ) {
	$form_id = absint( $form_id );

	if ( ! $form_id || ! cyber_is_cf7_active() ) {
		return '';
	}

	$post = get_post( $form_id );

	if ( ! $post || CYBER_CF7_POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
		return '';
	}

	return (string) do_shortcode( sprintf( '[contact-form-7 id="%d"]', $form_id ) );
}
