<?php
/**
 * Sekcja "Slider".
 *
 * Karuzela slajdow w opakowaniu sekcji. W trybie zdjecia "100% przegladarki"
 * opakowanie otwiera sie bez kontenera szerokosci, a tresc kazdego slajdu
 * zweza sie sama do szerokosci sekcji (patrz inc/sections-slider.php).
 *
 * Konfiguracja przewijania jedzie w atrybutach data-* i czyta ja
 * assets/js/slider.js — widok nie generuje JavaScriptu.
 *
 * Widok nie siega po ACF ani po stan globalny: komplet danych przychodzi
 * jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4).
 *
 * @package Cyber_Framework
 *
 * @var array $args {
 *     @type array $attributes Klasy, zmienne CSS i kotwica opakowania sekcji.
 *     @type array $row        Wiersz Flexible Content z wartosciami pol.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_row    = isset( $args['row'] ) ? $args['row'] : array();
$cyber_slides = cyber_slider_slides( $cyber_row );

if ( ! $cyber_slides ) {
	return;
}

/*
 * Atrybuty opakowania liczymy tu, a nie w rendererze: slider przepisuje swoje
 * odstepy na format sekcji i nie uzywa tla sekcji (cyber_slider_section_row).
 */
$cyber_attributes = cyber_section_attributes( 'slider', cyber_slider_section_row( $cyber_row ) );
$cyber_slider     = cyber_slider_attributes( $cyber_row );
$cyber_config     = cyber_slider_config( $cyber_row, count( $cyber_slides ) );
$cyber_inner      = ! $cyber_slider['full'];

// Paginacja zalezy od liczby slajdow, wiec klase znamy dopiero tutaj.
if ( $cyber_config['pagination'] ) {
	$cyber_slider['class'] .= ' cyber-slider--has-pagination';
}

cyber_section_open( $cyber_attributes, $cyber_inner );
?>

	<div
		class="<?php echo esc_attr( $cyber_slider['class'] ); ?>"
		style="<?php echo esc_attr( $cyber_slider['style'] ); ?>"
		data-loop="<?php echo $cyber_config['loop'] ? '1' : '0'; ?>"
		data-autoplay="<?php echo $cyber_config['autoplay'] ? '1' : '0'; ?>"
		data-delay="<?php echo esc_attr( (string) $cyber_config['delay'] ); ?>"
		data-navigation="<?php echo $cyber_config['navigation'] ? '1' : '0'; ?>"
		data-pagination="<?php echo $cyber_config['pagination'] ? '1' : '0'; ?>"
	>
		<div class="swiper-wrapper">
			<?php foreach ( $cyber_slides as $cyber_index => $cyber_slide ) : ?>
				<div class="swiper-slide cyber-slide">
					<?php cyber_slider_picture( $cyber_slide, 0 === $cyber_index ); ?>
					<?php cyber_slider_video( $cyber_slide ); ?>

					<div class="cyber-slide__overlay" aria-hidden="true"></div>

					<?php
					// Sama ramka (np. mapa) tez jest trescia, choc nie ma w niej tekstu.
					$cyber_has_text   = '' !== trim( wp_strip_all_tags( $cyber_slide['content'] ) ) || false !== stripos( $cyber_slide['content'], '<iframe' );
					$cyber_has_button = '' !== $cyber_slide['url'] && '' !== $cyber_slide['label'];
					?>

					<?php if ( $cyber_has_text || $cyber_has_button ) : ?>
						<div class="cyber-slide__body">
							<div class="cyber-slide__content">
								<?php if ( $cyber_has_text ) : ?>
									<div class="cyber-slide__text cyber-wysiwyg">
										<?php echo cyber_kses_content( $cyber_slide['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
									</div>
								<?php endif; ?>

								<?php if ( $cyber_has_button ) : ?>
									<div class="cyber-slide__footer">
										<?php
										cyber_button(
											array(
												'text'   => $cyber_slide['label'],
												'url'    => $cyber_slide['url'],
												'size'   => $cyber_slide['button_size'],
												'target' => $cyber_slide['target'],
											)
										);
										?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $cyber_config['pagination'] ) : ?>
			<div class="swiper-pagination cyber-slider__pagination"></div>
		<?php endif; ?>

		<?php if ( $cyber_config['navigation'] ) : ?>
			<button type="button" class="swiper-button-prev cyber-slider__prev"></button>
			<button type="button" class="swiper-button-next cyber-slider__next"></button>
		<?php endif; ?>
	</div>

<?php
cyber_section_close( $cyber_inner );
