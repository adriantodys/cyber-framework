<?php
/**
 * Sekcja "Karty" (icon boxes).
 *
 * Ten sam szkielet co sekcja podstawowa — WYSIWYG gora, kontener, WYSIWYG dol
 * — z tym, ze kontener jest wypelniony siatka powtarzalnych elementow.
 *
 * Widok nie siega po ACF ani po stan globalny: komplet danych przychodzi
 * jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4), a elementy
 * sa juz znormalizowane przez cyber_cards_items().
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array $attributes Klasy, zmienne CSS i kotwica opakowania sekcji.
 *     @type array $row        Wiersz Flexible Content z wartosciami pol.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();
$cyber_row        = isset( $args['row'] ) ? $args['row'] : array();

if ( ! $cyber_attributes ) {
	return;
}

$cyber_cards = cyber_cards_attributes( $cyber_row );
$cyber_items = cyber_cards_items( $cyber_row );

cyber_section_open( $cyber_attributes );
if ( cyber_cards_shows_wysiwyg( $cyber_row, 'top' ) ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}

if ( $cyber_items ) :
	?>
	<div class="<?php echo esc_attr( $cyber_cards['class'] ); ?>" style="<?php echo esc_attr( $cyber_cards['style'] ); ?>">
		<?php foreach ( $cyber_items as $cyber_item ) : ?>
			<?php
			// Zdjecie jako tlo karty — adres idzie zmienna, nie atrybutem src.
			printf(
				'<article class="cyber-card"%s>',
				'' !== $cyber_item['image_url']
					? sprintf( ' style="--cyber-card-image:url(%s);"', esc_url( $cyber_item['image_url'] ) )
					: ''
			);
			?>
				<?php if ( '' === $cyber_item['image_url'] && $cyber_item['image_id'] ) : ?>
					<div class="cyber-card__media">
						<?php
						echo wp_get_attachment_image(
							$cyber_item['image_id'],
							'large',
							false,
							array( 'class' => 'cyber-card__image' )
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( '' !== $cyber_item['title'] ) : ?>
					<h3 class="cyber-card__title"><?php echo esc_html( $cyber_item['title'] ); ?></h3>
				<?php endif; ?>

				<?php if ( '' !== trim( $cyber_item['text'] ) ) : ?>
					<div class="cyber-card__text">
						<?php echo wp_kses_post( wpautop( $cyber_item['text'] ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( '' !== $cyber_item['url'] && '' !== $cyber_item['label'] ) : ?>
					<div class="cyber-card__footer">
						<?php
						if ( 'button' === $cyber_item['link_style'] ) {
							// Przycisk dziedziczy caly wyglad z zakladki Przyciski.
							cyber_button(
								array(
									'text'   => $cyber_item['label'],
									'url'    => $cyber_item['url'],
									'size'   => $cyber_item['button_size'],
									'target' => $cyber_item['target'],
								)
							);
						} else {
							printf(
								'<a class="cyber-card__link" href="%1$s"%2$s>%3$s</a>',
								esc_url( $cyber_item['url'] ),
								'_blank' === $cyber_item['target'] ? ' target="_blank" rel="noopener"' : '',
								esc_html( $cyber_item['label'] )
							);
						}
						?>
					</div>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
endif;

if ( cyber_cards_shows_wysiwyg( $cyber_row, 'bottom' ) ) {
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
}
cyber_section_close();
