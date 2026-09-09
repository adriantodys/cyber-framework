<?php
/**
 * Rejestracja Options Page(s).
 *
 * Options Page nie da sie utworzyc przez UI ACF — wymaga rejestracji w PHP
 * (CLAUDE.md sekcja 5 pkt 8). Slug musi zgadzac sie z wartoscia lokalizacji
 * w acf-json/group_global_options.json.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rejestruje glowna strone ustawien motywu.
 *
 * @return void
 */
function cyber_register_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'      => __( 'Ustawienia Cyber Framework', 'cyber-framework' ),
			'menu_title'      => __( 'Cyber Framework', 'cyber-framework' ),
			'menu_slug'       => CYBER_OPTIONS_SLUG,
			'capability'      => 'manage_options',
			'redirect'        => false,
			'icon_url'        => 'dashicons-admin-customizer',
			'position'        => 59,
			'update_button'   => __( 'Zapisz ustawienia', 'cyber-framework' ),
			'updated_message' => __( 'Ustawienia zapisane.', 'cyber-framework' ),
		)
	);
}
add_action( 'acf/init', 'cyber_register_options_page' );
