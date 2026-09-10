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
 *     @type array      $social Lista tablic 'platform', 'label', 'url'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_phone  = isset( $args['phone'] ) ? $args['phone'] : null;
$cyber_email  = isset( $args['email'] ) ? $args['email'] : null;
$cyber_social = isset( $args['social'] ) ? $args['social'] : array();
?>

<div class="cyber-topheader">
	<div class="cyber-container">
		<div class="cyber-topheader__inner">

			<div class="cyber-topheader__contact">
				<?php if ( null !== $cyber_phone ) : ?>
					<a class="cyber-topheader__link" href="<?php echo esc_url( $cyber_phone['href'] ); ?>">
						<?php echo esc_html( $cyber_phone['text'] ); ?>
					</a>
				<?php endif; ?>

				<?php if ( null !== $cyber_email ) : ?>
					<a class="cyber-topheader__link" href="<?php echo esc_url( $cyber_email['href'] ); ?>">
						<?php echo esc_html( $cyber_email['text'] ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( array() !== $cyber_social ) : ?>
				<div class="cyber-topheader__social">
					<?php foreach ( $cyber_social as $cyber_item ) : ?>
						<a
							class="cyber-topheader__link"
							href="<?php echo esc_url( $cyber_item['url'] ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( $cyber_item['label'] ); ?>"
						>
							<?php
							/*
							 * Ikona to staly markup z cyber_get_social_icon(), bez zadnych
							 * danych uzytkownika. Escapowanie zamienilo by znaczniki SVG
							 * w tekst, dlatego wypisujemy je bezposrednio.
							 */
							echo cyber_get_social_icon( $cyber_item['platform'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
