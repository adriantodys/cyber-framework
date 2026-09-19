<?php
/**
 * Komponent: lista ikon social media.
 *
 * Widok czysty — dane przychodza jawnie przez $args z cyber_social_icons()
 * (CLAUDE.md sekcja 4). Decyzja, ktore profile pokazac, zapadla juz wczesniej,
 * w cyber_social_links(); tutaj nie ma zadnych warunkow.
 *
 * Nie wolaj tego pliku bezposrednio przez get_template_part() — uzyj
 * cyber_social_icons(), ktore buduje i waliduje liste.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type array  $items Lista tablic 'platform', 'label', 'url'.
 *     @type string $class       Dodatkowa klasa kontenera albo pusty string.
 *     @type bool   $show_labels Nazwa platformy obok ikony (sekcja Kontakt).
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_items = isset( $args['items'] ) ? $args['items'] : array();
$cyber_class = isset( $args['class'] ) ? trim( 'cyber-social-icons ' . $args['class'] ) : 'cyber-social-icons';
$cyber_names = ! empty( $args['show_labels'] );

if ( $cyber_names ) {
	$cyber_class .= ' cyber-social-icons--labels';
}
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<?php foreach ( $cyber_items as $cyber_item ) : ?>
		<a
			class="cyber-social-icons__link"
			href="<?php echo esc_url( $cyber_item['url'] ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			<?php if ( ! $cyber_names ) : ?>
				aria-label="<?php echo esc_attr( $cyber_item['label'] ); ?>"
			<?php endif; ?>
		>
			<?php
			/*
			 * Ikona to staly markup z cyber_get_social_icon(), bez zadnych danych
			 * uzytkownika. Escapowanie zamienilo by znaczniki SVG w tekst.
			 */
			echo cyber_get_social_icon( $cyber_item['platform'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			<?php if ( $cyber_names ) : ?>
				<?php // Widoczna nazwa jest dostepna nazwa linku — aria-label bylby zbedny. ?>
				<span class="cyber-social-icons__label"><?php echo esc_html( $cyber_item['label'] ); ?></span>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
