<?php
/**
 * Komponent: przewijany rzad kart (Swiper albo tasma ciagla).
 *
 * Wspolny dla sekcji "Karuzela kart" i sekcji "Wpisy" w trybie slidera — jedna
 * definicja markupu karuzeli, jak jedna definicja karty (components/card.php).
 * Konfiguracja przewijania pochodzi z pol cyber_carousel_* wiersza (sekcja
 * Wpisy klonuje je z group_section_carousel), wyglad karty z cyber_cards_*.
 *
 * Opakowanie sekcji i kontener .cyber-section__inner otwiera sekcja: w trybie
 * "full" rzad idzie od krawedzi do krawedzi okna.
 *
 * Konfiguracja jedzie w atrybutach data-* i czyta ja assets/js/slider.js —
 * widok nie generuje JavaScriptu.
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array $items Karty w formacie cyber_cards_items().
 *     @type array $row   Wiersz Flexible Content z ustawieniami karuzeli i kart.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_items = isset( $args['items'] ) ? $args['items'] : array();
$cyber_row   = isset( $args['row'] ) ? $args['row'] : array();

if ( ! $cyber_items ) {
	return;
}

$cyber_config   = cyber_carousel_config( $cyber_row, count( $cyber_items ) );
$cyber_carousel = cyber_carousel_attributes( $cyber_row, count( $cyber_items ) );
?>
<div class="cyber-carousel-wrap">
	<div
		class="<?php echo esc_attr( $cyber_carousel['class'] ); ?>"
		style="<?php echo esc_attr( $cyber_carousel['style'] ); ?>"
		data-per-view="<?php echo esc_attr( (string) $cyber_config['per_view'] ); ?>"
		data-per-view-tablet="<?php echo esc_attr( (string) $cyber_config['per_view_t'] ); ?>"
		data-per-view-mobile="<?php echo esc_attr( (string) $cyber_config['per_view_m'] ); ?>"
		data-gap="<?php echo esc_attr( (string) $cyber_config['gap'] ); ?>"
		data-speed="<?php echo esc_attr( (string) $cyber_config['speed'] ); ?>"
		data-continuous="<?php echo $cyber_config['continuous'] ? '1' : '0'; ?>"
		data-loop="<?php echo $cyber_config['loop'] ? '1' : '0'; ?>"
		data-autoplay="<?php echo $cyber_config['autoplay'] ? '1' : '0'; ?>"
		data-delay="<?php echo esc_attr( (string) $cyber_config['delay'] ); ?>"
		data-navigation="<?php echo $cyber_config['navigation'] ? '1' : '0'; ?>"
		data-pagination="<?php echo $cyber_config['pagination'] ? '1' : '0'; ?>"
	>
		<?php if ( $cyber_config['continuous'] ) : ?>
			<?php
			$cyber_band = cyber_carousel_continuous_items(
				$cyber_items,
				max( $cyber_config['per_view'], $cyber_config['per_view_t'], $cyber_config['per_view_m'] )
			);

			/*
			 * Czas przejazdu jednego zestawu = szybkosc na karte x liczba kart
			 * w zestawie. Dzieki temu pole "Szybkosc" znaczy to samo niezaleznie
			 * od tego, ile kart ma karuzela.
			 */
			$cyber_duration = (int) $cyber_config['speed'] * (int) $cyber_band['per_set'];
			?>
			<div class="swiper-wrapper cyber-carousel__band" style="<?php echo esc_attr( sprintf( '--cyber-carousel-duration:%dms;', $cyber_duration ) ); ?>">
				<?php foreach ( $cyber_band['items'] as $cyber_index => $cyber_item ) : ?>
					<?php
					$cyber_copy = $cyber_band['copies'][ $cyber_index ];

					ob_start();
					get_template_part( 'template-parts/components/card', null, array( 'item' => $cyber_item ) );
					$cyber_card = ob_get_clean();

					// Kopia nie moze dostac fokusu z klawiatury.
					if ( $cyber_copy ) {
						$cyber_card = preg_replace( '/<(a|button)\b/', '<$1 tabindex="-1"', $cyber_card );
					}
					?>
					<div class="swiper-slide cyber-carousel__slide<?php echo $cyber_copy ? ' cyber-carousel__slide--copy' : ''; ?>"<?php echo $cyber_copy ? ' aria-hidden="true"' : ''; ?>>
						<?php
						// Markup karty z komponentu jest juz wyescapowany w samym komponencie.
						echo $cyber_card; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="swiper-wrapper">
				<?php foreach ( $cyber_items as $cyber_item ) : ?>
					<div class="swiper-slide cyber-carousel__slide">
						<?php get_template_part( 'template-parts/components/card', null, array( 'item' => $cyber_item ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php
	/*
	 * Strzalki i kropki leza POD rzedem kart, w jednym pasku: <- . . . ->
	 * Nad kartami przykrywalyby tresc skrajnych kart — w karuzeli, inaczej
	 * niz w sliderze, karty wypelniaja cala szerokosc az do krawedzi.
	 */
	?>
	<?php if ( $cyber_config['navigation'] || $cyber_config['pagination'] ) : ?>
		<div class="cyber-carousel__controls">
			<?php if ( $cyber_config['navigation'] ) : ?>
				<button type="button" class="swiper-button-prev cyber-carousel__prev"></button>
			<?php endif; ?>

			<?php if ( $cyber_config['pagination'] ) : ?>
				<div class="swiper-pagination cyber-carousel__pagination"></div>
			<?php endif; ?>

			<?php if ( $cyber_config['navigation'] ) : ?>
				<button type="button" class="swiper-button-next cyber-carousel__next"></button>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
