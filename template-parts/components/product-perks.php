<?php
/**
 * Komponent: kafelki pod przyciskiem "Dodaj do koszyka" na stronie produktu.
 *
 * Widok czysty — liste buduje cyber_product_perk_items()
 * (inc/woocommerce-product.php).
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type array[] $items Lista tablic 'icon' (nazwa z cyber_icons()), 'title', 'text'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_items = isset( $args['items'] ) ? $args['items'] : array();

if ( ! $cyber_items ) {
	return;
}
?>
<ul class="cyber-product__perks">
	<?php foreach ( $cyber_items as $cyber_item ) : ?>
		<li class="cyber-product__perk">
			<span class="cyber-product__perk-icon">
				<?php
				echo cyber_get_icon( $cyber_item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- staly markup z rejestru ikon.
				?>
			</span>
			<span class="cyber-product__perk-text">
				<strong><?php echo esc_html( $cyber_item['title'] ); ?></strong>
				<span><?php echo esc_html( $cyber_item['text'] ); ?></span>
			</span>
		</li>
	<?php endforeach; ?>
</ul>
