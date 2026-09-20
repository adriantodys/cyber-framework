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
		'inc/editor.php',
		'inc/header.php',
		'inc/footer.php',
		'inc/components.php',
		'inc/contact.php',
		'inc/woocommerce.php',
		'inc/contact-form-7.php',
		'inc/breadcrumb.php',
		'inc/woocommerce-cart.php',
		'inc/woocommerce-checkout.php',
		'inc/woocommerce-shop.php',
		'inc/woocommerce-product.php',
		'inc/sections.php',
		'inc/sections-global.php',
		'inc/sections-cards.php',
		'inc/sections-columns.php',
		'inc/sections-slider.php',
		'inc/sections-carousel.php',
		'inc/sections-faq.php',
		'inc/sections-counter.php',
		'inc/sections-contact.php',
		'inc/posts.php',
		'inc/sections-posts.php',
		'inc/sections-table.php',
		'inc/class-cyber-recent-posts-widget.php',
		'inc/blog.php',
		'inc/page-header.php',
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
