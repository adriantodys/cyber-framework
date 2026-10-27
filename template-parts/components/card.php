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
 * image_size : rozmiar obrazka z biblioteki mediow (sekcja Wpisy: 'medium').
 * image_sizes: atrybut sizes zdjecia (lista bloga: cyber_blog_image_sizes());
 *          bez niego WordPress wstawia domyslny, nieznajacy kolumn siatki.
 * price  : cena produktu jako HTML z WooCommerce (get_price_html()),
 * cart   : przycisk "Dodaj do koszyka" z cyber_post_card_product().
 * rating : ocena 1-5 jako gwiazdki (opinie, inc/reviews.php),
 * variant: wariant karty, klasa .cyber-card--[wariant] (opinie: 'review').
 * Karty z sekcji Karty tych kluczy nie maja i wygladaja jak wczesniej.
 */
$cyber_class = 'cyber-card' . ( ! empty( $cyber_item['variant'] ) ? ' cyber-card--' . sanitize_html_class( $cyber_item['variant'] ) : '' );
$cyber_stars = isset( $cyber_item['rating'] ) ? max( 0, min( 5, (int) $cyber_item['rating'] ) ) : 0;
$cyber_price = isset( $cyber_item['price'] ) ? (string) $cyber_item['price'] : '';
$cyber_cart  = isset( $cyber_item['cart'] ) && is_array( $cyber_item['cart'] ) && ! empty( $cyber_item['cart']['url'] ) ? $cyber_item['cart'] : array();
$cyber_meta  = isset( $cyber_item['meta'] ) ? (string) $cyber_item['meta'] : '';
$cyber_link  = isset( $cyber_item['title_url'] ) ? (string) $cyber_item['title_url'] : '';
$cyber_tag   = isset( $cyber_item['title_tag'] ) && in_array( $cyber_item['title_tag'], array( 'h2', 'h3', 'h4' ), true ) ? $cyber_item['title_tag'] : 'h3';
$cyber_size  = isset( $cyber_item['image_size'] ) && in_array( $cyber_item['image_size'], array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ? $cyber_item['image_size'] : 'large';
$cyber_attrs = array( 'class' => 'cyber-card__image' );

if ( ! empty( $cyber_item['image_sizes'] ) ) {
	$cyber_attrs['sizes'] = (string) $cyber_item['image_sizes'];
}

$cyber_image = '' === $cyber_item['image_url'] && $cyber_item['image_id']
	? wp_get_attachment_image( $cyber_item['image_id'], $cyber_size, false, $cyber_attrs )
	: '';

// Zdjecie jako tlo karty — adres idzie zmienna, nie atrybutem src.
printf(
	'<article class="%1$s"%2$s>',
	esc_attr( $cyber_class ),
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

	<?php if ( $cyber_stars ) : ?>
		<?php // Gwiazdki sa ozdoba — ocene czytnik dostaje jako tekst. ?>
		<p class="cyber-card__rating">
			<span aria-hidden="true"><?php echo esc_html( str_repeat( '★', $cyber_stars ) . str_repeat( '☆', 5 - $cyber_stars ) ); ?></span>
			<span class="screen-reader-text">
				<?php
				/* translators: %d: ocena od 1 do 5. */
				echo esc_html( sprintf( __( 'Ocena: %d z 5', 'cyber-framework' ), $cyber_stars ) );
				?>
			</span>
		</p>
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

	<?php if ( '' !== $cyber_price ) : ?>
		<p class="cyber-card__price"><?php echo wp_kses_post( $cyber_price ); ?></p>
	<?php endif; ?>

	<?php if ( $cyber_cart ) : ?>
		<div class="cyber-card__footer">
			<?php
			/*
			 * Klasy i atrybuty data-* jak w petli sklepu: po nich skrypt
			 * wc-add-to-cart dodaje produkt AJAX-em i dopisuje "Zobacz koszyk".
			 * Bez klasy ajax_add_to_cart (np. produkt zmienny) to zwykly link.
			 */
			$cyber_cart_class = array(
				'btn',
				'btn-' . ( isset( $cyber_item['button_size'] ) ? $cyber_item['button_size'] : 'medium' ),
				'cyber-card__cart',
				'button',
				'product_type_' . $cyber_cart['type'],
				'add_to_cart_button',
			);

			if ( ! empty( $cyber_cart['ajax'] ) ) {
				$cyber_cart_class[] = 'ajax_add_to_cart';
			}

			printf(
				'<a href="%1$s" class="%2$s" data-quantity="1" data-product_id="%3$d" data-product_sku="%4$s" aria-label="%5$s" rel="nofollow">%6$s</a>',
				esc_url( $cyber_cart['url'] ),
				esc_attr( implode( ' ', array_map( 'sanitize_html_class', $cyber_cart_class ) ) ),
				absint( $cyber_cart['product_id'] ),
				esc_attr( $cyber_cart['sku'] ),
				esc_attr( $cyber_cart['aria_label'] ),
				esc_html( $cyber_cart['label'] )
			);
			?>
		</div>
	<?php elseif ( '' !== $cyber_item['url'] && '' !== $cyber_item['label'] ) : ?>
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
