<?php
/**
 * Komponent: siatka zdjec galerii (z filtrami i lightboxem).
 *
 * Wspolny dla sekcji "Galeria" i strony pojedynczej galerii (CPT) — jedna
 * definicja markupu, jeden arkusz, jeden skrypt.
 *
 * Kazde zdjecie jest LINKIEM do pliku w pelnym rozmiarze. Bez JavaScriptu
 * klikniecie otwiera zdjecie w nowej karcie; skrypt (assets/js/gallery.js)
 * przechwytuje klikniecie i pokazuje lightbox. Dzieki temu galeria dziala
 * takze wtedy, gdy skrypt sie nie wykona.
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array  $items      Zdjecia z cyber_gallery_data().
 *     @type array  $filters    Kategorie do filtrowania (moze byc pusta tablica).
 *     @type array  $attributes Klasy, zmienne CSS i wlacznik lightboxa.
 *     @type string $all_label  Etykieta przycisku "wszystkie".
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_items = isset( $args['items'] ) ? $args['items'] : array();

if ( ! $cyber_items ) {
	return;
}

$cyber_attributes = isset( $args['attributes'] ) ? $args['attributes'] : array( 'class' => 'cyber-gallery', 'style' => '' );
$cyber_filters    = isset( $args['filters'] ) ? $args['filters'] : array();
$cyber_all        = isset( $args['all_label'] ) && '' !== $args['all_label'] ? $args['all_label'] : __( 'Wszystkie', 'cyber-framework' );
?>
<div class="<?php echo esc_attr( $cyber_attributes['class'] ); ?>" style="<?php echo esc_attr( $cyber_attributes['style'] ); ?>">

	<?php if ( $cyber_filters ) : ?>
		<?php
		/*
		 * Filtry to przyciski, nie linki: nie prowadza pod zaden adres, tylko
		 * chowaja czesc zdjec. aria-pressed niesie stan dla czytnika ekranu.
		 */
		?>
		<div class="cyber-gallery__filters" role="group" aria-label="<?php esc_attr_e( 'Kategorie galerii', 'cyber-framework' ); ?>">
			<button type="button" class="cyber-gallery__filter is-active" data-term="all" aria-pressed="true">
				<?php echo esc_html( $cyber_all ); ?>
			</button>

			<?php foreach ( $cyber_filters as $cyber_filter ) : ?>
				<button type="button" class="cyber-gallery__filter" data-term="<?php echo esc_attr( (string) $cyber_filter['id'] ); ?>" aria-pressed="false">
					<?php echo esc_html( $cyber_filter['name'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="cyber-gallery__grid">
		<?php foreach ( $cyber_items as $cyber_index => $cyber_item ) : ?>
			<figure class="cyber-gallery__item" data-terms="<?php echo esc_attr( implode( ' ', $cyber_item['terms'] ) ); ?>">
				<a
					class="cyber-gallery__link"
					href="<?php echo esc_url( $cyber_item['full'] ); ?>"
					data-index="<?php echo esc_attr( (string) $cyber_index ); ?>"
					data-caption="<?php echo esc_attr( $cyber_item['caption'] ); ?>"
				>
					<?php
					echo wp_get_attachment_image(
						$cyber_item['id'],
						'large',
						false,
						array(
							'class'   => 'cyber-gallery__image',
							'loading' => $cyber_index < 4 ? 'eager' : 'lazy',
						)
					);
					?>
				</a>

				<?php if ( '' !== $cyber_item['caption'] ) : ?>
					<figcaption class="cyber-gallery__caption"><?php echo esc_html( $cyber_item['caption'] ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</div>
