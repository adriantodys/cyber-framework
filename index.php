<?php
/**
 * Fallback motywu — wymagany przez WordPress.
 *
 * SZKIELET TYMCZASOWY (etap 1). Docelowe szablony widokow (front-page, page,
 * single, archive, 404, search) powstaja w etapie 5 i trafiaja do templates/.
 *
 * SEKCJE. Widok pojedynczy sklada sie z dwoch czesci: tresci klasycznego
 * edytora, zamknietej w kontenerze o szerokosci strony, oraz sekcji Flexible
 * Content wypisanych POD nia i POZA kontenerem. Sekcja musi byc poza nim,
 * bo jej tlo idzie na pelna szerokosc okna — wewnatrz .cyber-container
 * konczyloby sie na krawedzi tresci.
 *
 * Na tym etapie sekcje DOPELNIAJA tresc edytora, a nie zastepuja jej.
 * Rozstrzygniecie, czy na stronach edytor ma zniknac, nalezy do etapu 5.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php if ( ! have_posts() ) : ?>

	<div class="cyber-container">
		<p><?php esc_html_e( 'Brak tresci do wyswietlenia.', 'cyber-framework' ); ?></p>
	</div>

<?php elseif ( is_singular() ) : ?>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php
		/*
		 * Tytul niesie page header, jesli jest wlaczony — wtedy szablon nie
		 * wypisuje drugiego <h1>. Bez page headera: wpisy i pozostale typy
		 * tresci dostaja widoczny <h1>, a strony (page) <h1> UKRYTY WIZUALNIE
		 * (.screen-reader-text). Strona nie zmienia wygladu — pierwszy widoczny
		 * naglowek nadal daje redaktor w tresci albo w sekcji — ale czytnik
		 * ekranu i wyszukiwarka dostaja jeden <h1> na kazdej stronie
		 * (CLAUDE.md sekcja 11).
		 *
		 * Pusta tresc edytora nie zostawia po sobie pustego kontenera — strona
		 * zlozona wylacznie z sekcji zaczyna sie od pierwszej sekcji. Ukryty
		 * <h1> nie otwiera wtedy kontenera (nie ma wymiarow, wiec nie zostawia
		 * odstepu) i stoi przed sekcjami sam. Tresc zostaje, bo na niej stoja
		 * m.in. strony sklepu (shortcode koszyka i zamowienia).
		 */
		$cyber_page_header  = cyber_page_header_shows();
		$cyber_show_title   = ! is_page() && ! $cyber_page_header;
		$cyber_hidden_title = is_page() && ! $cyber_page_header;
		$cyber_has_body     = '' !== trim( (string) get_the_content() );
		?>

		<?php if ( $cyber_show_title || $cyber_has_body ) : ?>
			<div class="cyber-container">
				<article <?php post_class(); ?>>
					<?php if ( $cyber_show_title ) : ?>
						<h1><?php the_title(); ?></h1>
					<?php elseif ( $cyber_hidden_title ) : ?>
						<h1 class="screen-reader-text"><?php the_title(); ?></h1>
					<?php endif; ?>

					<?php if ( $cyber_has_body ) : ?>
						<div class="cyber-wysiwyg">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>
				</article>
			</div>
		<?php elseif ( $cyber_hidden_title ) : ?>
			<h1 class="screen-reader-text"><?php the_title(); ?></h1>
		<?php endif; ?>

		<?php cyber_render_sections(); ?>
	<?php endwhile; ?>

<?php else : ?>

	<div class="cyber-container">
		<?php
		/*
		 * Kazdy widok listy dostaje dokladnie jeden <h1> (CLAUDE.md sekcja 11) —
		 * takze strona glowna ustawiona na liste wpisow. Na niej tytul dokumentu
		 * to "Nazwa - opis", wiec bierzemy sama nazwe witryny; dalej zostaje
		 * tytul dokumentu.
		 */
		?>
		<h1>
			<?php echo esc_html( is_front_page() ? get_bloginfo( 'name' ) : wp_get_document_title() ); ?>
		</h1>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h2>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php the_excerpt(); ?>
			</article>
			<?php
		endwhile;

		the_posts_pagination();
		?>
	</div>

<?php endif; ?>

<?php
get_footer();
