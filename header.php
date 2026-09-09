<?php
/**
 * Szkielet naglowka dokumentu.
 *
 * SZKIELET TYMCZASOWY (etap 1). Wlasciwy header sterowany polami ACF powstaje
 * w etapie 3 (CLAUDE.md sekcja 17) i zamieszka w template-parts/header/.
 * Ten plik istnieje tylko po to, zeby motyw dalo sie aktywowac i przetestowac.
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

<header class="cyber-header">
	<div class="cyber-container">
		<p class="cyber-header__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
			</a>
		</p>
		<?php // TODO (etap 3): nawigacja i pola ACF headera. ?>
	</div>
</header>

<main id="cyber-main" class="cyber-main">
