<?php
/**
 * Lista bloga: strona wpisow, kategorie, tagi, archiwa dat i autorow.
 *
 * Wpiety w hierarchie WordPressa przez cyber_blog_template_hierarchy()
 * (inc/blog.php). Karty wpisow to wspolny komponent karty, ustawienia
 * z Global Options -> Blog.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cyber_cards = cyber_blog_cards_row();
$cyber_cards = cyber_cards_attributes( $cyber_cards );
$cyber_opts  = cyber_blog_card_opts();
?>

<div class="<?php echo esc_attr( cyber_blog_layout_class() ); ?>">
	<div class="cyber-blog__main">
		<header class="cyber-blog__header<?php echo cyber_blog_title_visible() ? '' : ' screen-reader-text'; ?>">
			<h1 class="cyber-blog__title"><?php echo esc_html( cyber_blog_title() ); ?></h1>
			<?php if ( is_category() || is_tag() ) : ?>
				<?php $cyber_description = term_description(); ?>
				<?php if ( $cyber_description ) : ?>
					<div class="cyber-blog__description cyber-wysiwyg"><?php echo wp_kses_post( $cyber_description ); ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="<?php echo esc_attr( $cyber_cards['class'] . ' cyber-blog__list' ); ?>" style="<?php echo esc_attr( $cyber_cards['style'] ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/components/card', null, array( 'item' => cyber_post_card_item( get_post(), $cyber_opts ) ) );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'class'              => 'cyber-pagination',
					'mid_size'           => 1,
					'prev_text'          => __( '&larr; Poprzednia', 'cyber-framework' ),
					'next_text'          => __( 'Nastepna &rarr;', 'cyber-framework' ),
					'screen_reader_text' => __( 'Nawigacja po wpisach', 'cyber-framework' ),
				)
			);
			?>
		<?php else : ?>
			<p class="cyber-blog__empty"><?php esc_html_e( 'Brak wpisow do wyswietlenia.', 'cyber-framework' ); ?></p>
		<?php endif; ?>

		<?php $cyber_hint = cyber_blog_sidebar_hint(); ?>
		<?php if ( '' !== $cyber_hint ) : ?>
			<p class="cyber-section-missing"><?php echo esc_html( $cyber_hint ); ?></p>
		<?php endif; ?>
	</div>

	<?php get_template_part( 'template-parts/blog/sidebar' ); ?>
</div>

<?php
get_footer();
