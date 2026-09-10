<?php
/**
 * Cyber Framework — bootstrap motywu.
 *
 * Ten plik nie zawiera logiki. Jego jedynym zadaniem jest zdefiniowanie stalych
 * motywu i zaladowanie modulow z katalogu inc/ w kontrolowanej kolejnosci.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wersja motywu. Uzywana tylko jako metadana — wersjonowanie assetow opiera sie
 * na filemtime() (patrz inc/enqueue.php, CLAUDE.md sekcja 12).
 */
define( 'CYBER_VERSION', '0.1.0' );

/**
 * Sciezka bezwzgledna do katalogu motywu, bez koncowego ukosnika.
 */
define( 'CYBER_DIR', get_template_directory() );

/**
 * URL katalogu motywu, bez koncowego ukosnika.
 */
define( 'CYBER_URI', get_template_directory_uri() );

/**
 * Slug Options Page. Uzywany przy rejestracji strony ustawien oraz w lokalizacji
 * grupy pol ACF (acf-json/group_global_options.json).
 */
define( 'CYBER_OPTIONS_SLUG', 'cyber-settings' );

/**
 * Laduje moduly motywu.
 *
 * Kolejnosc ma znaczenie: helpers.php musi byc dostepny dla pozostalych modulow,
 * a acf.php musi zarejestrowac sciezki Local JSON zanim ACF zacznie ladowac pola.
 *
 * @return void
 */
function cyber_load_modules() {
	$modules = array(
		'inc/helpers.php',
		'inc/acf.php',
		'inc/options.php',
		'inc/setup.php',
		'inc/enqueue.php',
	);

	foreach ( $modules as $module ) {
		$path = CYBER_DIR . '/' . $module;

		if ( ! is_readable( $path ) ) {
			continue;
		}

		require_once $path;
	}
}
cyber_load_modules();

/**
 * Jawne zaladowanie modulu Options Page.
 *
 * Plik jest juz ladowany przez cyber_load_modules(); require_once jest
 * idempotentne, wiec ta linia sluzy wylacznie jako jawna deklaracja zaleznosci.
 */
require_once get_template_directory() . '/inc/options.php';
