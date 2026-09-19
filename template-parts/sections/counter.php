<?php
/**
 * Sekcja "Licznik" (counter).
 *
 * Wartosc koncowa jest w HTML od poczatku; assets/js/counter.js odlicza do niej
 * od zera, gdy sekcja pojawi sie na ekranie. Szczegoly w inc/sections-counter.php.
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

$cyber_attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();
$cyber_row        = isset( $args['row'] ) ? $args['row'] : array();

if ( ! $cyber_attributes ) {
	return;
}

$cyber_items = cyber_counter_items( $cyber_row );
$cyber_show  = array(
	'top'    => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_counter_show', 'top' ),
	'bottom' => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_counter_show', 'bottom' ),
);

if ( ! $cyber_items && ! $cyber_show['top'] && ! $cyber_show['bottom'] ) {
	return;
}

$cyber_config  = cyber_counter_config( $cyber_row );
$cyber_counter = cyber_counter_attributes( $cyber_row );

cyber_section_open( $cyber_attributes );

if ( $cyber_show['top'] ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}

if ( $cyber_items ) :
	?>
	<div class="cyber-section__body">
		<div
			class="<?php echo esc_attr( $cyber_counter['class'] ); ?>"
			style="<?php echo esc_attr( $cyber_counter['style'] ); ?>"
			<?php if ( $cyber_config['animate'] ) : ?>
				data-counter-duration="<?php echo esc_attr( (string) $cyber_config['duration'] ); ?>"
				data-counter-thousands="<?php echo $cyber_config['thousands'] ? '1' : '0'; ?>"
			<?php endif; ?>
		>
			<?php foreach ( $cyber_items as $cyber_item ) : ?>
				<?php $cyber_value = cyber_counter_format( $cyber_item['number'], $cyber_item['decimals'], $cyber_config['thousands'] ); ?>
				<div class="cyber-counter__item">
					<?php if ( $cyber_item['icon'] && wp_attachment_is_image( $cyber_item['icon'] ) ) : ?>
						<div class="cyber-counter__icon">
							<?php
							echo wp_get_attachment_image(
								$cyber_item['icon'],
								'thumbnail',
								false,
								array(
									'class'   => 'cyber-counter__icon-image',
									'alt'     => '',
									'loading' => 'lazy',
								)
							);
							?>
						</div>
					<?php endif; ?>

					<p class="cyber-counter__value">
						<span aria-hidden="true">
							<?php if ( '' !== $cyber_item['prefix'] ) : ?>
								<span class="cyber-counter__prefix"><?php echo esc_html( $cyber_item['prefix'] ); ?></span>
							<?php endif; ?>
							<span
								class="cyber-counter__number"
								data-counter-to="<?php echo esc_attr( (string) $cyber_item['number'] ); ?>"
								data-counter-decimals="<?php echo esc_attr( (string) $cyber_item['decimals'] ); ?>"
							><?php echo esc_html( $cyber_value ); ?></span>
							<?php if ( '' !== $cyber_item['suffix'] ) : ?>
								<span class="cyber-counter__suffix"><?php echo esc_html( $cyber_item['suffix'] ); ?></span>
							<?php endif; ?>
						</span>
						<span class="screen-reader-text"><?php echo esc_html( trim( $cyber_item['prefix'] . ' ' . $cyber_value . ' ' . $cyber_item['suffix'] ) ); ?></span>
					</p>

					<?php if ( '' !== $cyber_item['title'] ) : ?>
						<h3 class="cyber-counter__title"><?php echo esc_html( $cyber_item['title'] ); ?></h3>
					<?php endif; ?>

					<?php if ( '' !== $cyber_item['label'] ) : ?>
						<p class="cyber-counter__label"><?php echo esc_html( $cyber_item['label'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
endif;

if ( $cyber_show['bottom'] ) {
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
}

cyber_section_close();
