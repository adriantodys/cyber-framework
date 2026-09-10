<?php
/**
 * Naglowek dokumentu.
 *
 * Plik odpowiada za <head>, otwarcie <body> i skip-link. Sam header wizualny
 * mieszka w template-parts/header/header.php i dostaje dane jawnie przez $args
 * (CLAUDE.md sekcja 4) — tutaj tylko zbieramy je przez cyber_get_option().
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="cyber-skip-link" href="#cyber-main"><?php esc_html_e( 'Przejdz do tresci', 'cyber-framework' ); ?></a>

<?php
get_template_part(
	'template-parts/header/header',
	null,
	array(
		'logo_url'       => cyber_get_option( 'header_logo' ),
		'site_name'      => get_bloginfo( 'name' ),
		'menu_alignment' => cyber_get_option( 'header_menu_alignment' ),
		'has_menu'       => has_nav_menu( CYBER_HEADER_MENU_LOCATION ),
	)
);
?>

<main id="cyber-main" class="cyber-main">
