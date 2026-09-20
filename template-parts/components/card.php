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

/*
 * Klucze opcjonalne — uzywaja ich karty wpisow (sekcja Wpisy, blog, widget):
 * meta   : linia nad tytulem (data, kategoria),
 * link   : adres tytulu i zdjecia (karta wpisu prowadzi do wpisu),
 * tag    : znacznik tytulu; na liscie bloga h2, bo h1 to tytul strony.
 * Karty z sekcji Karty tych kluczy nie maja i wygladaja jak wczesniej.
 */
$cyber_meta  = isset( $cyber_item['meta'] ) ? (string) $cyber_item['meta'] : '';
$cyber_link  = isset( $cyber_item['title_url'] ) ? (string) $cyber_item['title_url'] : '';
$cyber_tag   = isset( $cyber_item['title_tag'] ) && in_array( $cyber_item['title_tag'], array( 'h2', 'h3', 'h4' ), true ) ? $cyber_item['title_tag'] : 'h3';
$cyber_image = '' === $cyber_item['image_url'] && $cyber_item['image_id']
	? wp_get_attachment_image( $cyber_item['image_id'], 'large', false, array( 'class' => 'cyber-card__image' ) )
	: '';

// Zdjecie jako tlo karty — adres idzie zmienna, nie atrybutem src.
printf(
	'<article class="cyber-card"%s>',
	'' !== $cyber_item['image_url']
		? sprintf( ' style="--cyber-card-image:url(%s);"', esc_url( $cyber_item['image_url'] ) )
		: ''
);
?>
	<?php if ( '' !== $cyber_image ) : ?>
		<div class="cyber-card__media">
			<?php if ( '' !== $cyber_link ) : ?>
				<?php // Zdjecie prowadzi tam co tytul; poza tabulacja i czytnikiem, zeby nie dublowac linku. ?>
				<a class="cyber-card__media-link" href="<?php echo esc_url( $cyber_link ); ?>" tabindex="-1" aria-hidden="true">
					<?php echo $cyber_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escapuje atrybuty. ?>
				</a>
			<?php else : ?>
				<?php echo $cyber_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escapuje atrybuty. ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $cyber_meta ) : ?>
		<p class="cyber-card__meta"><?php echo esc_html( $cyber_meta ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $cyber_item['overtitle'] ) : ?>
		<p class="cyber-overtitle cyber-card__overtitle"><?php echo esc_html( $cyber_item['overtitle'] ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $cyber_item['title'] ) : ?>
		<<?php echo esc_attr( $cyber_tag ); ?> class="cyber-card__title">
			<?php if ( '' !== $cyber_link ) : ?>
				<a class="cyber-card__title-link" href="<?php echo esc_url( $cyber_link ); ?>"><?php echo esc_html( $cyber_item['title'] ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $cyber_item['title'] ); ?>
			<?php endif; ?>
		</<?php echo esc_attr( $cyber_tag ); ?>>
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
