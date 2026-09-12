<?php
/**
 * Header — licznik pozycji w koszyku.
 *
 * Wydzielony do osobnego pliku, bo ten sam markup trafia w dwa miejsca:
 * do headera przy renderowaniu strony ORAZ do WooCommerce jako fragment AJAX
 * (`woocommerce_add_to_cart_fragments` w inc/woocommerce.php). WooCommerce
 * podmienia CALY element pasujacy do selektora `.cyber-wc-count`, wiec ten plik
 * musi wypisac rowniez znacznik zewnetrzny — i nic poza nim.
 *
 * Pusty koszyk dostaje klase modyfikujaca zamiast znikac z DOM: gdyby elementu
 * nie bylo, WooCommerce nie mialby czego podmienic po dodaniu pierwszego
 * produktu i licznik pozostalby niewidoczny do przeladowania strony.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type int $count Liczba pozycji w koszyku.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_count = isset( $args['count'] ) ? (int) $args['count'] : 0;
?>
<span class="cyber-wc-count<?php echo ( 0 === $cyber_count ) ? ' cyber-wc-count--empty' : ''; ?>"><?php echo esc_html( number_format_i18n( $cyber_count ) ); ?></span>
