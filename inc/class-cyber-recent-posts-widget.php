<?php
/**
 * Widget "Ostatnie wpisy" z obrazkiem, data i przyciskiem.
 *
 * Wbudowany widget WordPressa "Najnowsze wpisy" wypisuje sama liste tytulow;
 * makieta paska bocznego bloga ma karty (zdjecie, data, tytul, przycisk).
 * Widget renderuje je wspolnym komponentem karty, wiec wyglada jak lista
 * bloga i sekcja Wpisy.
 *
 * Klasyczny widget (WP_Widget) — widgety blokowe sa w motywie wylaczone
 * (CLAUDE.md sekcja 1). Zapis ustawien obsluguje WP_Widget razem z nonce
 * ekranu widgetow; update() waliduje kazda wartosc.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget "Ostatnie wpisy" motywu.
 */
class Cyber_Recent_Posts_Widget extends WP_Widget {

	/**
	 * Rejestracja widgetu.
	 */
	public function __construct() {
		parent::__construct(
			'cyber_recent_posts',
			__( 'Cyber: Ostatnie wpisy', 'cyber-framework' ),
			array(
				'classname'   => 'cyber-recent-posts',
				'description' => __( 'Najnowsze wpisy jako karty: zdjecie, data, tytul, przycisk.', 'cyber-framework' ),
			)
		);
	}

	/**
	 * Ustawienia domyslne.
	 *
	 * @return array
	 */
	private function defaults() {
		return array(
			'title'       => __( 'Ostatnie wpisy', 'cyber-framework' ),
			'number'      => 3,
			'show_image'  => 1,
			'show_date'   => 1,
			'button'      => __( 'Czytaj więcej', 'cyber-framework' ),
			'button_size' => 'small',
		);
	}

	/**
	 * Widok widgetu.
	 *
	 * @param array $args     Opakowanie z register_sidebar().
	 * @param array $instance Ustawienia.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$posts    = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => max( 1, min( 10, (int) $instance['number'] ) ),
				'post_status'         => 'publish',
				'post__not_in'        => is_singular( 'post' ) ? array( get_queried_object_id() ) : array(),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( ! $posts ) {
			return;
		}

		$cards = cyber_cards_attributes(
			array(
				'cyber_cards_columns'        => 1,
				'cyber_cards_columns_tablet' => 1,
				'cyber_cards_columns_mobile' => 1,
				'cyber_cards_gap_y'          => 36,
				'cyber_cards_gap_media'      => 24,
				'cyber_cards_gap_title'      => 24,
				'cyber_cards_image_ratio'    => '16-9',
				'cyber_cards_title_size'     => 'h6',
				'cyber_cards_title_weight'   => '700',
			)
		);

		$opts = array(
			'image'       => ! empty( $instance['show_image'] ),
			'date'        => ! empty( $instance['show_date'] ),
			'label'       => (string) $instance['button'],
			'link_style'  => 'button',
			'button_size' => in_array( $instance['button_size'], cyber_button_sizes(), true ) ? $instance['button_size'] : 'small',
			'title_tag'   => 'h3',
		);

		// Opakowanie widgetu pochodzi z register_sidebar() — znaczniki motywu.
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = apply_filters( 'widget_title', (string) $instance['title'], $instance, $this->id_base );

		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		printf( '<div class="%1$s" style="%2$s">', esc_attr( $cards['class'] . ' cyber-recent-posts__list' ), esc_attr( $cards['style'] ) );

		foreach ( $posts as $post ) {
			get_template_part( 'template-parts/components/card', null, array( 'item' => cyber_post_card_item( $post, $opts ) ) );
		}

		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Formularz w panelu.
	 *
	 * @param array $instance Ustawienia.
	 * @return string
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$field    = function ( $name, $label, $type = 'text' ) use ( $instance ) {
			$id = $this->get_field_id( $name );

			if ( 'checkbox' === $type ) {
				printf(
					'<p><input type="checkbox" class="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> <label for="%1$s">%4$s</label></p>',
					esc_attr( $id ),
					esc_attr( $this->get_field_name( $name ) ),
					checked( ! empty( $instance[ $name ] ), true, false ),
					esc_html( $label )
				);
				return;
			}

			printf(
				'<p><label for="%1$s">%2$s</label><input class="%3$s" id="%1$s" name="%4$s" type="%5$s" value="%6$s"%7$s /></p>',
				esc_attr( $id ),
				esc_html( $label ),
				'number' === $type ? 'tiny-text' : 'widefat',
				esc_attr( $this->get_field_name( $name ) ),
				esc_attr( $type ),
				esc_attr( (string) $instance[ $name ] ),
				'number' === $type ? ' min="1" max="10" step="1"' : ''
			);
		};

		$field( 'title', __( 'Tytul:', 'cyber-framework' ) );
		$field( 'number', __( 'Liczba wpisow (1-10):', 'cyber-framework' ), 'number' );
		$field( 'show_image', __( 'Zdjecie wyrozniajace', 'cyber-framework' ), 'checkbox' );
		$field( 'show_date', __( 'Data', 'cyber-framework' ), 'checkbox' );
		$field( 'button', __( 'Tekst przycisku (puste = bez przycisku):', 'cyber-framework' ) );

		$id = $this->get_field_id( 'button_size' );
		printf( '<p><label for="%1$s">%2$s</label> <select id="%1$s" name="%3$s">', esc_attr( $id ), esc_html__( 'Rozmiar przycisku:', 'cyber-framework' ), esc_attr( $this->get_field_name( 'button_size' ) ) );
		foreach ( cyber_button_sizes() as $size ) {
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $size ), selected( $instance['button_size'], $size, false ), esc_html( ucfirst( $size ) ) );
		}
		echo '</select></p>';

		return 'form';
	}

	/**
	 * Walidacja zapisu.
	 *
	 * @param array $new_instance Nowe wartosci z formularza.
	 * @param array $old_instance Poprzednie wartosci.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'       => sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' ),
			'number'      => max( 1, min( 10, absint( isset( $new_instance['number'] ) ? $new_instance['number'] : 3 ) ) ),
			'show_image'  => empty( $new_instance['show_image'] ) ? 0 : 1,
			'show_date'   => empty( $new_instance['show_date'] ) ? 0 : 1,
			'button'      => sanitize_text_field( isset( $new_instance['button'] ) ? $new_instance['button'] : '' ),
			'button_size' => isset( $new_instance['button_size'] ) && in_array( $new_instance['button_size'], cyber_button_sizes(), true ) ? $new_instance['button_size'] : 'small',
		);
	}
}
