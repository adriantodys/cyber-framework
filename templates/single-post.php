<?php
/**
 * Pojedynczy wpis (typ "post").
 *
 * Kolejnosc jak na makiecie: zdjecie wyrozniajace, tytul, data, tresc.
 * Obok pasek boczny z widgetami. Sekcje Flexible Content wpisu renderuja sie
 * POD ukladem dwukolumnowym, na pelna szerokosc — ich tlo ma siegac krawedzi
 * okna, a w waskiej kolumnie tresci by sie urwalo.
 *
 * Wpiety w hierarchie przez cyber_blog_template_hierarchy() (inc/blog.php).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$cyber_post = get_post();
	$cyber_meta = cyber_blog_single_meta( $cyber_post );
	?>
	<div class="<?php echo esc_attr( cyber_blog_layout_class() ); ?>">
		<article <?php post_class( 'cyber-blog__main cyber-post' ); ?>>
			<?php if ( cyber_get_option( 'blog_single_image' ) && has_post_thumbnail() ) : ?>
				<div class="cyber-post__image">
					<?php
					// Pierwszy obraz ekranu — laduje sie od razu i z wysokim priorytetem.
					the_post_thumbnail(
						'large',
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<h1 class="cyber-post__title"><?php the_title(); ?></h1>

			<?php if ( $cyber_meta ) : ?>
				<p class="cyber-post__meta"><?php echo esc_html( implode( ' · ', $cyber_meta ) ); ?></p>
			<?php endif; ?>

			<div class="cyber-post__content cyber-wysiwyg">
				<?php the_content(); ?>
			</div>

			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="cyber-post__pages" aria-label="' . esc_attr__( 'Strony wpisu', 'cyber-framework' ) . '">',
					'after'  => '</nav>',
				)
			);

			$cyber_hint = cyber_blog_sidebar_hint();
			?>
			<?php if ( '' !== $cyber_hint ) : ?>
				<p class="cyber-section-missing"><?php echo esc_html( $cyber_hint ); ?></p>
			<?php endif; ?>
		</article>

		<?php get_template_part( 'template-parts/blog/sidebar' ); ?>
	</div>

	<?php cyber_render_sections(); ?>
	<?php
endwhile;

get_footer();
