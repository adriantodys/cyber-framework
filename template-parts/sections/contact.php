<?php
/**
 * Sekcja "Kontakt" — dane kontaktowe i formularz w dwoch kolumnach.
 *
 * Dane kontaktowe i social media pochodza z Global Options; logika wyboru
 * siedzi w inc/sections-contact.php, formularz w inc/contact-form-7.php.
 *
 * Widok nie siega po ACF ani po stan globalny poza helperami: komplet danych
 * wiersza przychodzi jawnie w $args z cyber_render_sections() (CLAUDE.md sekcja 4).
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

$cyber_config  = cyber_contact_section_config( $cyber_row );
$cyber_contact = cyber_contact_section_attributes( $cyber_row );
$cyber_data    = cyber_contact_section_data( $cyber_row );
$cyber_form    = cyber_cf7_form_html( $cyber_config['form'] );
$cyber_hint    = '' === $cyber_form ? cyber_cf7_missing_hint( $cyber_config['form'] ) : '';
$cyber_left    = isset( $cyber_row['cyber_contact_section_left'] ) ? (string) $cyber_row['cyber_contact_section_left'] : '';
$cyber_right   = isset( $cyber_row['cyber_contact_section_right'] ) ? (string) $cyber_row['cyber_contact_section_right'] : '';
$cyber_show    = array(
	'top'    => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_contact_section_show', 'top' ),
	'bottom' => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_contact_section_show', 'bottom' ),
);

cyber_section_open( $cyber_attributes );

if ( $cyber_show['top'] ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}
?>
	<div class="cyber-section__body">
		<div class="<?php echo esc_attr( $cyber_contact['class'] ); ?>" style="<?php echo esc_attr( $cyber_contact['style'] ); ?>">

			<div class="cyber-contact__col cyber-contact__col--info">
				<?php if ( '' !== trim( $cyber_left ) ) : ?>
					<div class="cyber-contact__content cyber-wysiwyg">
						<?php echo cyber_kses_content( $cyber_left ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
					</div>
				<?php endif; ?>

				<?php if ( $cyber_data ) : ?>
					<dl class="cyber-contact__data">
						<?php foreach ( $cyber_data as $cyber_item ) : ?>
							<div class="cyber-contact__item cyber-contact__item--<?php echo esc_attr( $cyber_item['key'] ); ?>">
								<dt class="cyber-contact__label">
									<?php
									if ( $cyber_config['show_icons'] && '' !== $cyber_item['icon'] ) {
										echo cyber_get_icon( $cyber_item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- staly markup z rejestru ikon.
									}
									echo esc_html( $cyber_item['label'] );
									?>
								</dt>
								<dd class="cyber-contact__value">
									<?php if ( '' !== $cyber_item['href'] ) : ?>
										<a href="<?php echo esc_url( $cyber_item['href'], array( 'mailto', 'tel' ) ); ?>"><?php echo esc_html( $cyber_item['text'] ); ?></a>
									<?php else : ?>
										<?php echo nl2br( esc_html( $cyber_item['text'] ) ); ?>
									<?php endif; ?>
								</dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( $cyber_config['show_social'] && cyber_social_links() ) : ?>
					<div class="cyber-contact__social">
						<?php if ( '' !== $cyber_config['social_title'] ) : ?>
							<p class="cyber-contact__social-title"><?php echo esc_html( $cyber_config['social_title'] ); ?></p>
						<?php endif; ?>
						<?php
						cyber_social_icons(
							array(
								'class'       => 'cyber-contact__social-list',
								'show_labels' => $cyber_config['social_labels'],
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>

			<div class="cyber-contact__col cyber-contact__col--form">
				<?php if ( '' !== trim( $cyber_right ) ) : ?>
					<div class="cyber-contact__content cyber-wysiwyg">
						<?php echo cyber_kses_content( $cyber_right ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
					</div>
				<?php endif; ?>

				<?php if ( '' !== $cyber_form ) : ?>
					<div class="cyber-contact__form">
						<?php
						// Markup formularza generuje Contact Form 7 (shortcode) i sam go escapuje.
						echo $cyber_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>
				<?php elseif ( '' !== $cyber_hint ) : ?>
					<p class="cyber-section-missing"><?php echo esc_html( $cyber_hint ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</div>
<?php
if ( $cyber_show['bottom'] ) {
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
}

cyber_section_close();
