<?php
/**
 * Pojedyncza galeria (CPT cyber_gallery).
 *
 * Tytul, tresc z edytora, siatka zdjec z lightboxem, a pod spodem sekcje
 * Flexible Content — tak jak na stronach, bo grupa cyber_sections jest
 * przypieta takze do tego typu tresci.
 *
 * Wpiety w hierarchie przez cyber_gallery_template_hierarchy() (inc/gallery.php).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$cyber_items = array();

	foreach ( cyber_gallery_images( get_the_ID() ) as $cyber_image_id ) {
		$cyber_item = cyber_gallery_item( $cyber_image_id, 'caption' );

		if ( $cyber_item ) {
			$cyber_items[] = $cyber_item;
		}
	}
	?>
	<div class="cyber-container">
		<article <?php post_class( 'cyber-gallery-page' ); ?>>
			<?php if ( ! cyber_page_header_shows() ) : ?>
				<?php // Z wlaczonym page headerem tytul stoi tam — jeden <h1> na strone. ?>
				<h1 class="cyber-gallery-page__title"><?php the_title(); ?></h1>
			<?php endif; ?>

			<?php if ( '' !== trim( (string) get_the_content() ) ) : ?>
				<div class="cyber-gallery-page__content cyber-wysiwyg">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php
			get_template_part(
				'template-parts/components/gallery',
				null,
				array(
					'items'      => $cyber_items,
					'filters'    => array(),
					'attributes' => cyber_gallery_attributes( array() ),
				)
			);
			?>
		</article>
	</div>

	<?php cyber_render_sections(); ?>
	<?php
endwhile;

get_footer();
