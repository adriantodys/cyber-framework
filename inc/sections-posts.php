<?php
/**
 * Sekcja "Wpisy" — wpisy albo dowolny publiczny typ tresci jako karty.
 *
 * TEN SAM WYGLAD CO KARTY. Sekcja nie ma wlasnego wygladu elementu: klonuje
 * ustawienia z group_section_cards i renderuje wspolnym komponentem karty.
 * Tryb slidera klonuje ustawienia z group_section_carousel i uzywa wspolnego
 * komponentu template-parts/components/carousel.php. Wlasne sa tylko: zrodlo
 * (typ tresci, najnowsze albo reczny wybor) i ustawienia elementu (co pokazac).
 *
 * Wpis biezacej strony nigdy nie pokazuje sie w sekcji na samym sobie.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wypelnia liste typow tresci w panelu — publiczne typy rejestruja wtyczki,
 * wiec lista nie moze stac na sztywno w JSON-ie.
 *
 * @param array $field Pole ACF.
 * @return array
 */
function cyber_posts_type_choices( $field ) {
	$field['choices'] = cyber_post_card_types();

	return $field;
}
add_filter( 'acf/load_field/key=field_cyber_posts_type', 'cyber_posts_type_choices' );

/**
 * Reczny wybor pozwala siegnac po kazdy dozwolony typ tresci.
 *
 * @param array $field Pole Relationship.
 * @return array
 */
function cyber_posts_manual_types( $field ) {
	$field['post_type'] = array_keys( cyber_post_card_types() );

	return $field;
}
add_filter( 'acf/load_field/key=field_cyber_posts_manual', 'cyber_posts_manual_types' );

/**
 * Czy sekcja jest w trybie slidera.
 *
 * @param array $row Wiersz Flexible Content.
 * @return bool
 */
function cyber_posts_is_slider( array $row ) {
	return ! empty( $row['cyber_posts_slider'] );
}

/**
 * Wpisy sekcji wedlug ustawien zrodla.
 *
 * @param array $row Wiersz Flexible Content.
 * @return WP_Post[]
 */
function cyber_posts_query( array $row ) {
	$types   = cyber_post_card_types();
	$type    = isset( $row['cyber_posts_type'] ) && isset( $types[ $row['cyber_posts_type'] ] ) ? $row['cyber_posts_type'] : 'post';
	$current = get_queried_object_id();

	if ( isset( $row['cyber_posts_source'] ) && 'manual' === $row['cyber_posts_source'] ) {
		$ids = array_values(
			array_filter(
				array_map( 'absint', (array) ( isset( $row['cyber_posts_manual'] ) ? $row['cyber_posts_manual'] : array() ) ),
				function ( $id ) use ( $current ) {
					return $id && $id !== $current;
				}
			)
		);

		if ( ! $ids ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'           => array_keys( $types ),
				'post__in'            => array_slice( $ids, 0, 24 ),
				'orderby'             => 'post__in',
				'posts_per_page'      => 24,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
			)
		);
	}

	$count   = max( 1, min( 24, absint( isset( $row['cyber_posts_count'] ) ? $row['cyber_posts_count'] : 3 ) ) );
	$orderby = cyber_section_choice(
		isset( $row['cyber_posts_orderby'] ) ? $row['cyber_posts_orderby'] : 'date',
		array( 'date', 'title', 'menu_order', 'rand' ),
		'date'
	);

	$query = array(
		'post_type'           => $type,
		'posts_per_page'      => $count,
		'post_status'         => 'publish',
		'orderby'             => $orderby,
		'order'               => 'date' === $orderby ? 'DESC' : 'ASC',
		'post__not_in'        => $current ? array( $current ) : array(),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$categories = array_filter( array_map( 'absint', (array) ( isset( $row['cyber_posts_category'] ) ? $row['cyber_posts_category'] : array() ) ) );

	if ( 'post' === $type && $categories ) {
		$query['category__in'] = $categories;
	}

	return get_posts( $query );
}

/**
 * Elementy kart sekcji.
 *
 * Wyglad odnosnika i rozmiar przycisku pochodza z ustawien kart
 * (cyber_cards_link_style, cyber_cards_button_size), tak jak w sekcji Karty.
 *
 * @param array $row Wiersz Flexible Content.
 * @return array[]
 */
function cyber_posts_items( array $row ) {
	$flag = function ( $key, $default ) use ( $row ) {
		return isset( $row[ $key ] ) ? ! empty( $row[ $key ] ) : $default;
	};

	$label = $flag( 'cyber_posts_show_button', true )
		? sanitize_text_field( (string) ( isset( $row['cyber_posts_button_label'] ) ? $row['cyber_posts_button_label'] : __( 'Czytaj więcej', 'cyber-framework' ) ) )
		: '';

	$opts = array(
		'image'       => $flag( 'cyber_posts_show_image', true ),
		'date'        => $flag( 'cyber_posts_show_date', true ),
		'category'    => $flag( 'cyber_posts_show_category', false ),
		'excerpt'     => $flag( 'cyber_posts_show_excerpt', true ),
		'words'       => max( 5, min( 60, absint( isset( $row['cyber_posts_excerpt_length'] ) ? $row['cyber_posts_excerpt_length'] : 20 ) ) ),
		'label'       => $label,
		'link_style'  => cyber_section_choice( isset( $row['cyber_cards_link_style'] ) ? $row['cyber_cards_link_style'] : 'button', cyber_cards_link_styles(), 'button' ),
		'button_size' => cyber_section_choice( isset( $row['cyber_cards_button_size'] ) ? $row['cyber_cards_button_size'] : 'medium', cyber_button_sizes(), 'medium' ),
		'title_tag'   => cyber_section_choice( isset( $row['cyber_posts_title_tag'] ) ? $row['cyber_posts_title_tag'] : 'h3', array( 'h2', 'h3', 'h4' ), 'h3' ),
	);

	$items = array();

	foreach ( cyber_posts_query( $row ) as $post ) {
		$item = cyber_post_card_item( $post, $opts );

		// Tryb "zdjecie jako tlo" z ustawien kart dziala tak samo jak w kartach.
		if ( ! empty( $row['cyber_cards_image_as_bg'] ) && $item['image_id'] ) {
			$item['image_url'] = (string) wp_get_attachment_image_url( $item['image_id'], 'large' );
		}

		$items[] = $item;
	}

	return $items;
}

/**
 * Czy wpis ma sekcje Wpisy w trybie slidera — wtedy laduje sie Swiper.
 *
 * Podpiete pod cyber_slider_on_page() w inc/sections-slider.php.
 *
 * @param array $row Wiersz Flexible Content.
 * @return bool
 */
function cyber_posts_needs_swiper( array $row ) {
	return isset( $row['acf_fc_layout'] ) && 'posts' === $row['acf_fc_layout'] && cyber_posts_is_slider( $row );
}
