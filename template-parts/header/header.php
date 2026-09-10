<?php
/**
 * Header Desktop — markup.
 *
 * Komponent nie siega po stan globalny: wszystkie dane dostaje jawnie przez
 * $args z get_template_part() (CLAUDE.md sekcja 4). Wartosci liczbowe i kolory
 * nie przechodza przez PHP — trafiaja na front jako zmienne CSS wypisywane
 * w wp_head przez cyber_header_css().
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type string $logo_url       URL logo albo pusty string, gdy nie wgrano.
 *     @type string $site_name      Nazwa witryny — trafia w alt logo.
 *     @type string $menu_alignment Wyrownanie menu: left, center albo right.
 *     @type bool   $has_menu       Czy do lokalizacji 'primary' przypisano menu.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_logo_url       = isset( $args['logo_url'] ) ? $args['logo_url'] : '';
$cyber_site_name      = isset( $args['site_name'] ) ? $args['site_name'] : '';
$cyber_menu_alignment = isset( $args['menu_alignment'] ) ? $args['menu_alignment'] : 'right';
$cyber_has_menu       = ! empty( $args['has_menu'] );
?>

<header class="cyber-header">
	<div class="cyber-container">
		<div class="cyber-header__inner">

			<p class="cyber-header__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php if ( '' !== $cyber_logo_url ) : ?>
						<img
							class="cyber-header__logo"
							src="<?php echo esc_url( $cyber_logo_url ); ?>"
							alt="<?php echo esc_attr( $cyber_site_name ); ?>"
						>
					<?php else : ?>
						<?php echo esc_html( $cyber_site_name ); ?>
					<?php endif; ?>
				</a>
			</p>

			<?php if ( $cyber_has_menu ) : ?>
				<nav class="cyber-header__nav" aria-label="<?php esc_attr_e( 'Menu glowne', 'cyber-framework' ); ?>">
					<?php wp_nav_menu( cyber_header_menu_args( $cyber_menu_alignment ) ); ?>
				</nav>
			<?php endif; ?>

		</div>
	</div>
</header>
