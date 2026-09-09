<?php
/**
 * Fallback motywu — wymagany przez WordPress.
 *
 * SZKIELET TYMCZASOWY (etap 1). Docelowe szablony widokow (front-page, page,
 * single, archive, 404, search) powstaja w etapie 5 i trafiaja do templates/.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="cyber-container">
	<?php if ( have_posts() ) : ?>

		<?php if ( ! is_front_page() && ! is_singular() ) : ?>
			<h1><?php echo esc_html( wp_get_document_title() ); ?></h1>
		<?php endif; ?>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<?php if ( is_singular() ) : ?>
					<h1><?php the_title(); ?></h1>
					<?php the_content(); ?>
				<?php else : ?>
					<h2>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<?php the_excerpt(); ?>
				<?php endif; ?>
			</article>
			<?php
		endwhile;

		the_posts_pagination();
		?>

	<?php else : ?>
		<p><?php esc_html_e( 'Brak tresci do wyswietlenia.', 'cyber-framework' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
