<?php
/**
 * Wpisy jako karty — wspolne dla sekcji "Wpisy", listy bloga i widgetu
 * "Ostatnie wpisy".
 *
 * Wszystkie trzy miejsca pokazuja wpis tym samym komponentem karty
 * (template-parts/components/card.php). Ten plik zamienia WP_Post na element
 * w formacie cyber_cards_items(), zeby komponent nie musial wiedziec, skad
 * przyszla karta.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Formaty daty dostepne w Global Options -> Blog.
 *
 * @return array<string, string> Klucz => format PHP ('' = format z Ustawien WordPressa).
 */
function cyber_blog_date_formats() {
	return array(
		'short'   => 'j M Y',
		'numeric' => 'd.m.Y',
		'wp'      => '',
	);
}

/**
 * Data wpisu w formacie z Global Options -> Blog.
 *
 * @param WP_Post $post Wpis.
 * @return string
 */
function cyber_post_date( WP_Post $post ) {
	$formats = cyber_blog_date_formats();
	$key     = (string) cyber_get_option( 'blog_date_format' );
	$format  = isset( $formats[ $key ] ) ? $formats[ $key ] : $formats['short'];

	return (string) get_the_date( $format, $post );
}

/**
 * Publiczne typy tresci, ktore moze pokazac sekcja "Wpisy".
 *
 * Bez zalacznikow (nie maja strony z trescia) i bez sekcji globalnych.
 *
 * @return array<string, string> Slug => etykieta.
 */
function cyber_post_card_types() {
	$types = array();

	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $slug => $object ) {
		if ( 'attachment' === $slug ) {
			continue;
		}

		$types[ $slug ] = $object->labels->name;
	}

	return $types;
}

/**
 * Zajawka wpisu jako czysty tekst o zadanej liczbie slow.
 *
 * Wlasny fragment wpisu ma pierwszenstwo; bez niego — poczatek tresci bez
 * shortcode'ow i znacznikow.
 *
 * @param WP_Post $post  Wpis.
 * @param int     $words Liczba slow.
 * @return string
 */
function cyber_post_excerpt( WP_Post $post, $words ) {
	$text = '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : strip_shortcodes( $post->post_content );

	return wp_trim_words( wp_strip_all_tags( $text ), max( 1, (int) $words ), '…' );
}

/**
 * Wpis jako element karty (format cyber_cards_items()).
 *
 * @param WP_Post $post Wpis.
 * @param array   $opts {
 *     @type bool   $image       Zdjecie wyrozniajace.
 *     @type bool   $date        Data w linii meta.
 *     @type bool   $category    Pierwsza kategoria w linii meta.
 *     @type bool   $excerpt     Zajawka.
 *     @type int    $words       Dlugosc zajawki.
 *     @type string $label       Tekst przycisku; pusty = bez przycisku.
 *     @type string $link_style  'button' albo 'link'.
 *     @type string $button_size Rozmiar przycisku.
 *     @type string $title_tag   Znacznik tytulu.
 * }
 * @return array<string, mixed>
 */
function cyber_post_card_item( WP_Post $post, array $opts ) {
	$opts = wp_parse_args(
		$opts,
		array(
			'image'       => true,
			'date'        => true,
			'category'    => false,
			'excerpt'     => false,
			'words'       => 20,
			'label'       => '',
			'link_style'  => 'button',
			'button_size' => 'medium',
			'title_tag'   => 'h3',
		)
	);

	$meta = array();

	if ( $opts['date'] ) {
		$meta[] = cyber_post_date( $post );
	}

	if ( $opts['category'] && 'post' === $post->post_type ) {
		$terms = get_the_category( $post->ID );

		if ( $terms ) {
			$meta[] = $terms[0]->name;
		}
	}

	$url = (string) get_permalink( $post );

	return array(
		'image_id'    => $opts['image'] ? (int) get_post_thumbnail_id( $post ) : 0,
		'image_url'   => '',
		'overtitle'   => '',
		'meta'        => implode( ' · ', $meta ),
		'title'       => wp_strip_all_tags( get_the_title( $post ) ),
		'title_url'   => $url,
		'title_tag'   => $opts['title_tag'],
		'text'        => $opts['excerpt'] ? cyber_post_excerpt( $post, $opts['words'] ) : '',
		'url'         => '' !== $opts['label'] ? $url : '',
		'label'       => (string) $opts['label'],
		'target'      => '',
		'link_style'  => $opts['link_style'],
		'button_size' => $opts['button_size'],
	);
}
