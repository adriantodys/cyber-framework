<?php
/**
 * Sekcja "Tabela".
 *
 * Dane i klasy przygotowuje inc/sections-table.php; widok wypisuje strukture.
 * Naglowki, szerokosci i wyrownanie kolumn pochodza z repeatera "Kolumny",
 * tresc z repeatera "Wiersze". Komplet danych przychodzi jawnie w $args
 * z cyber_render_sections() (CLAUDE.md sekcja 4).
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

$cyber_data = cyber_table_data( $cyber_row );
$cyber_show = array(
	'top'    => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_table_show', 'top' ),
	'bottom' => cyber_section_shows_wysiwyg( $cyber_row, 'cyber_table_show', 'bottom' ),
);

if ( ! $cyber_data['rows'] && ! $cyber_show['top'] && ! $cyber_show['bottom'] ) {
	return;
}

$cyber_table   = cyber_table_attributes( $cyber_row );
$cyber_columns = cyber_table_columns( $cyber_row );

// Wiersz naglowkowy powstaje, gdy choc jedna kolumna ma naglowek.
$cyber_labels = '' !== implode( '', wp_list_pluck( $cyber_columns, 'label' ) );

// <colgroup> ma sens tylko wtedy, gdy ktoras kolumna ma ustawiona szerokosc.
$cyber_widths = 0 < array_sum( wp_list_pluck( $cyber_columns, 'width' ) );

cyber_section_open( $cyber_attributes );

if ( $cyber_show['top'] ) {
	cyber_section_wysiwyg( $cyber_row, 'top' );
}

if ( $cyber_data['rows'] ) :
	?>
	<div class="cyber-section__body">
		<?php
		/*
		 * Kontener przewijany poziomo dostaje tabindex i role="region" z nazwa —
		 * inaczej uzytkownik klawiatury nie przewinie szerokiej tabeli.
		 */
		?>
		<div class="<?php echo esc_attr( $cyber_table['class'] ); ?>" style="<?php echo esc_attr( $cyber_table['style'] ); ?>" role="region" aria-label="<?php esc_attr_e( 'Tabela', 'cyber-framework' ); ?>" tabindex="0">
			<table class="cyber-table__table">
				<?php if ( $cyber_widths ) : ?>
					<colgroup>
						<?php foreach ( $cyber_columns as $cyber_column ) : ?>
							<col<?php echo $cyber_column['width'] > 0 ? ' style="' . esc_attr( sprintf( 'width:%d%%;', $cyber_column['width'] ) ) . '"' : ''; ?> />
						<?php endforeach; ?>
					</colgroup>
				<?php endif; ?>

				<?php if ( $cyber_labels ) : ?>
					<thead>
						<tr>
							<?php foreach ( $cyber_columns as $cyber_index => $cyber_column ) : ?>
								<th scope="col" class="cyber-table__cell<?php echo esc_attr( cyber_table_cell_class( $cyber_columns, $cyber_index ) ); ?>">
									<?php echo cyber_table_cell( $cyber_column['label'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
				<?php endif; ?>

				<tbody>
					<?php foreach ( $cyber_data['rows'] as $cyber_cells ) : ?>
						<tr>
							<?php foreach ( $cyber_cells as $cyber_index => $cyber_cell ) : ?>
								<?php $cyber_class = 'cyber-table__cell' . cyber_table_cell_class( $cyber_columns, $cyber_index ); ?>
								<?php if ( 0 === $cyber_index && $cyber_table['head_col'] ) : ?>
									<th scope="row" class="<?php echo esc_attr( $cyber_class . ' cyber-table__label' ); ?>">
										<?php echo cyber_table_cell( $cyber_cell ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
									</th>
								<?php else : ?>
									<td class="<?php echo esc_attr( $cyber_class ); ?>">
										<?php echo cyber_table_cell( $cyber_cell ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses() w srodku. ?>
									</td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
endif;

if ( $cyber_show['bottom'] ) {
	cyber_section_wysiwyg( $cyber_row, 'bottom' );
}

cyber_section_close();
