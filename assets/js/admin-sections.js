/**
 * Panel: pola sekcji, ktorych nie da sie ustawic warunkiem ACF.
 *
 * SEKCJA TABELA. Wiersz ma szesc pol komorek, ale tabela ma tyle kolumn, ile
 * pozycji ma lista "Kolumny". Warunek ACF tego nie zrobi — conditional logic
 * porownuje WARTOSC pola, a tu chodzi o LICZBE wierszy repeatera. Stad ten
 * skrypt: po kazdej zmianie listy kolumn chowa nadmiarowe pola komorek.
 *
 * Bez kolumn (pusta lista) widac wszystkie szesc pol — to tryb automatyczny,
 * w ktorym liczba kolumn wynika z wypelnionych komorek.
 *
 * Gdy skrypt sie nie wykona, panel pokazuje po prostu komplet pol: PHP i tak
 * przycina wiersze do liczby kolumn przy renderowaniu.
 *
 * @package Cyber_Framework
 */
( function () {
	'use strict';

	if ( 'undefined' === typeof acf ) {
		return;
	}

	var MAX = 6;
	var COLUMNS = 'field_cyber_table_columns';
	var CELL = 'field_cyber_table_cell_';

	/**
	 * Liczba kolumn w jednej sekcji Tabela.
	 *
	 * Wiersz-wzorzec repeatera ACF (.acf-clone) nie jest kolumna.
	 *
	 * @param {HTMLElement} columns Pole repeatera "Kolumny".
	 * @return {number}
	 */
	function count( columns ) {
		return columns.querySelectorAll( '.acf-table > tbody > tr.acf-row:not(.acf-clone):not([data-cyber-removing])' ).length;
	}

	/**
	 * Chowa pola komorek ponad liczbe kolumn — w obrebie JEDNEJ sekcji.
	 *
	 * Sekcje Tabela moga byc na stronie dwie, kazda z inna liczba kolumn, wiec
	 * zakresem jest layout Flexible Content, a nie caly formularz.
	 *
	 * @param {HTMLElement} scope Layout sekcji albo caly formularz.
	 * @return {void}
	 */
	function sync( scope ) {
		var columns = scope.querySelector( '.acf-field[data-key="' + COLUMNS + '"]' );

		if ( ! columns ) {
			return;
		}

		var total = count( columns );

		for ( var i = 1; i <= MAX; i++ ) {
			var hide = total > 0 && i > total;

			scope.querySelectorAll( '[data-key="' + CELL + i + '"]' ).forEach( function ( el ) {
				el.classList.toggle( 'acf-hidden', hide );
			} );
		}
	}

	/**
	 * Przelicza wszystkie sekcje Tabela w formularzu.
	 *
	 * @return {void}
	 */
	function syncAll() {
		var layouts = document.querySelectorAll( '.acf-flexible-content .layout' );

		if ( layouts.length ) {
			layouts.forEach( sync );
			return;
		}

		sync( document );
	}

	var timer = null;

	/**
	 * Przeliczenie po chwili — zmian w formularzu bywa kilka pod rzad.
	 *
	 * @return {void}
	 */
	function schedule() {
		window.clearTimeout( timer );
		timer = window.setTimeout( syncAll, 120 );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', syncAll );
	} else {
		syncAll();
	}

	// Nowa sekcja albo nowy wiersz kolumn.
	acf.addAction( 'ready', schedule );
	acf.addAction( 'append', schedule );

	/*
	 * Usuwanie wiersza jest animowane: w chwili akcji wiersz jeszcze jest
	 * w DOM, a po animacji nie ma juz zdarzenia, na ktorym mozna polegac
	 * (sprawdzone — obserwator DOM nie lapie tego usuniecia). Dlatego
	 * znakujemy znikajacy wiersz i od razu liczymy kolumny bez niego.
	 */
	acf.addAction( 'remove', function ( $el ) {
		var row = $el && $el.length ? $el[ 0 ] : $el;

		if ( row && row.matches && row.matches( 'tr.acf-row' ) ) {
			row.setAttribute( 'data-cyber-removing', '1' );
		}

		syncAll();
	} );
}() );
