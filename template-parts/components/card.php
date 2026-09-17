<?php
/**
 * Komponent: karta.
 *
 * Jeden element z sekcji "Karty" i z sekcji "Karuzela kart" — obie sekcje
 * renderuja karte TYM SAMYM plikiem, wiec zmiana wygladu karty dziala w obu
 * miejscach naraz, a druga kopia markupu nie ma jak sie rozjechac.
 *
 * Wyglad karty (tlo, cien, obramowanie, wyrownanie, kolory) nie siedzi tutaj,
 * tylko w klasach i zmiennych kontenera z cyber_cards_attributes(). Komponent
 * wypisuje wylacznie strukture.
 *
 * Widok czysty: dane przychodza jawnie w $args, znormalizowane przez
 * cyber_cards_items() (CLAUDE.md sekcja 4).
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array $item Element z cyber_cards_items().
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_item = isset( $args['item'] ) ? $args['item'] : array();

if ( ! $cyber_item ) {
	return;
}

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
