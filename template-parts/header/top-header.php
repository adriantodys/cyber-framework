<?php
/**
 * Top Header — cienki pasek nad naglowkiem.
 *
 * Widok czysty: dane przychodza jawnie przez $args z header.php
 * (CLAUDE.md sekcja 4). Decyzja "co pokazac" zapadla juz wczesniej,
 * w cyber_top_header_data() — tutaj nie ma zadnych warunkow biznesowych,
 * tylko sprawdzenie, czy dana pozycja istnieje.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type array|null $phone  Tablica 'text' i 'href' albo null.
 *     @type array|null $email  Tablica 'text' i 'href' albo null.
 *     @type array      $social Lista profili — sluzy wylacznie do decyzji, czy
 *                              pasek ma sie renderowac. Same ikony wypisuje
 *                              wspolny komponent cyber_social_icons().
 *     @type string     $variant Wariant paska. Domyslnie 'default'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_phone  = isset( $args['phone'] ) ? $args['phone'] : null;
$cyber_email  = isset( $args['email'] ) ? $args['email'] : null;

$cyber_class = cyber_variant_class( 'cyber-topheader', isset( $args['variant'] ) ? $args['variant'] : 'default' );
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<div class="cyber-container">
		<div class="cyber-topheader__inner">

			<div class="cyber-topheader__contact">
				<?php if ( null !== $cyber_phone ) : ?>
					<a class="cyber-topheader__link" href="<?php echo esc_url( $cyber_phone['href'] ); ?>">
						<?php
						// Ikona dekoracyjna — nazwe niesie widoczny tekst obok niej.
						echo cyber_get_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<span><?php echo esc_html( $cyber_phone['text'] ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( null !== $cyber_email ) : ?>
					<a class="cyber-topheader__link" href="<?php echo esc_url( $cyber_email['href'] ); ?>">
						<?php
						echo cyber_get_icon( 'envelope' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<span><?php echo esc_html( $cyber_email['text'] ); ?></span>
					</a>
				<?php endif; ?>
			</div>

			<?php
			// Wspolny komponent — na pasku respektuje wylaczniki cyber_topheader_show_*.
			cyber_social_icons(
				array(
					'respect_toggles' => true,
					'class'           => 'cyber-topheader__social',
				)
			);
			?>

		</div>
	</div>
</div>
