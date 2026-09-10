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
 *     @type array  $links Lista tablic 'url', 'title', 'target', 'rel'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_text  = isset( $args['text'] ) ? $args['text'] : '';
$cyber_links = isset( $args['links'] ) ? $args['links'] : array();
?>

<div class="cyber-copyright">
	<div class="cyber-container">
		<div class="cyber-copyright__inner">

			<?php if ( '' !== $cyber_text ) : ?>
				<div class="cyber-copyright__text"><?php echo esc_html( $cyber_text ); ?></div>
			<?php endif; ?>

			<?php if ( array() !== $cyber_links ) : ?>
				<div class="cyber-copyright__links">
					<?php foreach ( $cyber_links as $cyber_link ) : ?>
						<a
							href="<?php echo esc_url( $cyber_link['url'] ); ?>"
							<?php if ( '' !== $cyber_link['target'] ) : ?>
								target="<?php echo esc_attr( $cyber_link['target'] ); ?>"
							<?php endif; ?>
							<?php if ( '' !== $cyber_link['rel'] ) : ?>
								rel="<?php echo esc_attr( $cyber_link['rel'] ); ?>"
							<?php endif; ?>
						><?php echo esc_html( $cyber_link['title'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
