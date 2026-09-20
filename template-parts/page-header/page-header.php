<?php
/**
 * Page header — tytul strony na tle, nad trescia.
 *
 * Widok czysty: komplet danych przychodzi jawnie w $args z cyber_page_header()
 * (CLAUDE.md sekcja 4). O tym, czy sie pokazac, zdecydowano wczesniej.
 *
 * Tytul jest znacznikiem <h1> strony — szablony widokow nie wypisuja wtedy
 * drugiego (index.php nie wypisuje tytulu stron, single-post.php ukrywa swoj).
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type string $title   Tytul do wypisania.
 *     @type string $excerpt Zajawka albo pusty string.
 *     @type array  $video   Tablica 'url' i 'type' albo pusta.
 *     @type string $class   Klasy opakowania.
 *     @type string $style   Zmienne CSS opakowania.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_title = isset( $args['title'] ) ? (string) $args['title'] : '';

if ( '' === $cyber_title ) {
	return;
}

$cyber_video = isset( $args['video'] ) && $args['video'] ? $args['video'] : array();
?>
<section class="<?php echo esc_attr( isset( $args['class'] ) ? $args['class'] : 'cyber-page-header' ); ?>" style="<?php echo esc_attr( isset( $args['style'] ) ? $args['style'] : '' ); ?>">
	<?php if ( $cyber_video ) : ?>
		<?php
		/*
		 * Wideo dekoracyjne: bez dzwieku, w petli, ukryte przed czytnikami
		 * ekranu. Pod nim zostaje zdjecie — widac je, zanim wideo zaladuje
		 * pierwsza klatke, i przy ograniczeniu animacji w systemie.
		 */
		?>
		<video class="cyber-page-header__video" autoplay muted loop playsinline preload="metadata" aria-hidden="true" tabindex="-1">
			<source src="<?php echo esc_url( $cyber_video['url'] ); ?>" type="<?php echo esc_attr( $cyber_video['type'] ); ?>" />
		</video>
	<?php endif; ?>

	<div class="cyber-page-header__overlay" aria-hidden="true"></div>

	<div class="cyber-page-header__inner">
		<h1 class="cyber-page-header__title"><?php echo esc_html( $cyber_title ); ?></h1>

		<?php if ( ! empty( $args['excerpt'] ) ) : ?>
			<p class="cyber-page-header__excerpt"><?php echo esc_html( $args['excerpt'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
