<?php
/**
 * Modul "Kontakt" — walidacja danych przy zapisie w panelu.
 *
 * Modul celowo NIE ma warstwy frontendowej: zadnych zmiennych CSS, zadnego
 * markupu, zadnego wpisu w inc/enqueue.php. To wylacznie warstwa danych
 * przygotowana pod przyszle moduly (CLAUDE.md sekcja 22).
 *
 * Tutaj zyje jedynie walidacja po stronie admina — redaktor ma dostac czytelny
 * komunikat przy zapisie, zamiast odkryc pozniej, ze motyw odrzucil wartosc
 * i pokazuje pustke.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Komunikaty bledow dla pol o ustalonym formacie.
 *
 * Klucze odpowiadaja kluczom z cyber_contact_patterns() (inc/helpers.php),
 * ktore sa jedynym zrodlem samych wzorcow.
 *
 * @return array<string, string> Klucz opcji => komunikat dla redaktora.
 */
function cyber_contact_messages() {
	return array(
		'contact_phone' => __( 'Telefon moze zawierac wylacznie cyfry, spacje i myslniki, opcjonalnie ze znakiem + na poczatku.', 'cyber-framework' ),
		'contact_nip'   => __( 'NIP musi miec dokladnie 10 cyfr, bez myslnikow i spacji.', 'cyber-framework' ),
		'contact_krs'   => __( 'KRS musi miec dokladnie 10 cyfr.', 'cyber-framework' ),
		'contact_regon' => __( 'REGON musi miec 9 albo 14 cyfr.', 'cyber-framework' ),
	);
}

/**
 * Waliduje wartosc pola kontaktowego przy zapisie w panelu.
 *
 * Pusta wartosc jest zawsze poprawna — zadne z tych pol nie jest wymagane,
 * a niewypelniony NIP to normalny stan witryny, ktora go nie podaje.
 *
 * @param bool|string $valid Wynik dotychczasowej walidacji ACF.
 * @param mixed       $value Wartosc wpisana przez redaktora.
 * @param array       $field Definicja pola ACF.
 * @return bool|string true albo komunikat bledu.
 */
function cyber_contact_validate_value( $valid, $value, $field ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$value = trim( (string) $value );

	if ( '' === $value ) {
		return $valid;
	}

	// Nazwa pola w ACF ma prefiks cyber_, klucze schematu go nie maja.
	$key      = preg_replace( '/^cyber_/', '', $field['name'] );
	$pattern  = cyber_contact_pattern( $key );
	$messages = cyber_contact_messages();

	if ( '' === $pattern || ! isset( $messages[ $key ] ) ) {
		return $valid;
	}

	return preg_match( $pattern, $value ) ? $valid : $messages[ $key ];
}

/*
 * Filtr rejestrowany osobno dla kazdego pola — ACF udostepnia wariant
 * acf/validate_value/name={pole}, wiec nie ma potrzeby podpinac sie pod
 * walidacje wszystkich pol w calym panelu i filtrowac ich po nazwie.
 */
foreach ( array_keys( cyber_contact_messages() ) as $cyber_contact_field ) {
	add_filter(
		'acf/validate_value/name=cyber_' . $cyber_contact_field,
		'cyber_contact_validate_value',
		10,
		3
	);
}

unset( $cyber_contact_field );
