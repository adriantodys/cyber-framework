<?php
/**
 * Pasek Copyright — cienki pasek pod stopka.
 *
 * Widok czysty: dane przychodza jawnie przez $args z footer.php w rootcie
 * (CLAUDE.md sekcja 4). Struktura jest tozsama z template-parts/header/
 * top-header.php — ten sam uklad kontener / inner / dwie strony.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type string $text  Tekst copyright albo pusty string.
 *     @type array  $links   Lista tablic 'key' i 'url'.
 *     @type string $variant Wariant paska. Domyslnie 'default'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_text  = isset( $args['text'] ) ? $args['text'] : '';
$cyber_links = isset( $args['links'] ) ? $args['links'] : array();

$cyber_class = cyber_variant_class( 'cyber-copyright', isset( $args['variant'] ) ? $args['variant'] : 'default' );

/*
 * Etykiety linkow prawnych sa STALE i zyja tutaj, nie w ACF. Pole Page Link
 * przechowuje wylacznie wybrana strone, bez wlasnego tytulu, a nazwy dokumentow
 * prawnych sa ustalone i nie powinny zalezec od tego, jak redaktor nazwal strone
 * w drzewie witryny (CLAUDE.md sekcja 5a).
 *
 * Sa tlumaczalne przez __(), dlatego tablica, a nie define() — stala nie
 * przeszlaby przez mechanizm tlumaczen.
 */
$cyber_link_labels = array(
	'privacy' => __( 'Polityka prywatnosci', 'cyber-framework' ),
	'cookies' => __( 'Polityka cookies', 'cyber-framework' ),
);
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<div class="cyber-container">
		<div class="cyber-copyright__inner">

			<?php if ( '' !== $cyber_text ) : ?>
				<div class="cyber-copyright__text"><?php echo esc_html( $cyber_text ); ?></div>
			<?php endif; ?>

			<?php if ( array() !== $cyber_links ) : ?>
				<div class="cyber-copyright__links">
					<?php foreach ( $cyber_links as $cyber_link ) : ?>
						<?php
						if ( ! isset( $cyber_link_labels[ $cyber_link['key'] ] ) ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $cyber_link['url'] ); ?>">
							<?php echo esc_html( $cyber_link_labels[ $cyber_link['key'] ] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
