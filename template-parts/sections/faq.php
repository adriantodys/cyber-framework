<?php
/**
 * Sekcja "FAQ".
 *
 * Kazde pytanie to natywne <details>: <summary> jest przyciskiem, odpowiedz
 * rozwija przegladarka. Zero JavaScriptu — szczegoly w inc/sections-faq.php.
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

$cyber_items = cyber_faq_items( $cyber_row );
$cyber_show  = array(
	'top'    => cyber_faq_shows_wysiwyg( $cyber_row, 'top' ),
	'bottom' => cyber_faq_shows_wysiwyg( $cyber_row, 'bottom' ),
);

if ( ! $cyber_items && ! $cyber_show['top'] && ! $cyber_show['bottom'] ) {
	return;
}

$cyber_config = cyber_faq_config( $cyber_row );
$cyber_faq    = cyber_faq_attributes( $cyber_row );
$cyber_group  = $cyber_config['single'] ? cyber_faq_group_name() : '';
$cyber_first  = true;

cyber_section_open( $cyber_attributes );

if ( $cyber_show['top'] ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}

if ( $cyber_items ) :
	?>
	<div class="cyber-section__body">
		<div class="<?php echo esc_attr( $cyber_faq['class'] ); ?>" style="<?php echo esc_attr( $cyber_faq['style'] ); ?>">
			<?php foreach ( cyber_faq_split( $cyber_items, $cyber_config['columns'] ) as $cyber_column ) : ?>
				<div class="cyber-faq__col">
					<?php foreach ( $cyber_column as $cyber_item ) : ?>
						<details class="cyber-faq__item"<?php echo $cyber_group ? ' name="' . esc_attr( $cyber_group ) . '"' : ''; ?><?php echo $cyber_first && $cyber_config['first_open'] ? ' open' : ''; ?>>
							<summary class="cyber-faq__summary">
								<?php
								if ( $cyber_config['show_icon'] ) {
									cyber_faq_icon( $cyber_item['icon'], $cyber_config['icon'] );
								}
								?>
								<span class="cyber-faq__question"><?php echo esc_html( $cyber_item['question'] ); ?></span>
								<?php if ( $cyber_config['show_toggle'] ) : ?>
									<span class="cyber-faq__toggle" aria-hidden="true"></span>
								<?php endif; ?>
							</summary>

							<?php if ( '' !== trim( $cyber_item['answer'] ) ) : ?>
								<div class="cyber-faq__answer cyber-wysiwyg">
									<?php echo cyber_kses_content( $cyber_item['answer'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
								</div>
							<?php endif; ?>
						</details>
						<?php $cyber_first = false; ?>
					<?php endforeach; ?>
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
