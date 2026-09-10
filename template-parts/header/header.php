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
 *     @type bool   $menu_indicator   Czy pozycje z podmenu maja dostac strzalke.
 *     @type int    $mobile_breakpoint Prog przelaczenia na menu mobilne, w px.
 *     @type bool   $has_menu       Czy do lokalizacji 'primary' przypisano menu.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_logo_url       = isset( $args['logo_url'] ) ? $args['logo_url'] : '';
$cyber_site_name      = isset( $args['site_name'] ) ? $args['site_name'] : '';
$cyber_menu_alignment = isset( $args['menu_alignment'] ) ? $args['menu_alignment'] : 'right';
$cyber_has_menu       = ! empty( $args['has_menu'] );
$cyber_menu_indicator = ! empty( $args['menu_indicator'] );
$cyber_mobile_bp      = isset( $args['mobile_breakpoint'] ) ? (int) $args['mobile_breakpoint'] : 980;

/*
 * Jeden markup menu obsluguje oba widoki — patrz docs/architecture.md.
 * Element <nav> jest jednoczesnie panelem mobilnym, dlatego niesie id
 * (dla aria-controls), prog dla JS oraz klase wariantu pozycji.
 */
$cyber_mobile_menu_id = 'cyber-mobile-menu';
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

				<button
					class="cyber-hamburger"
					type="button"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $cyber_mobile_menu_id ); ?>"
					aria-label="<?php esc_attr_e( 'Otworz menu', 'cyber-framework' ); ?>"
					data-label-open="<?php esc_attr_e( 'Otworz menu', 'cyber-framework' ); ?>"
					data-label-close="<?php esc_attr_e( 'Zamknij menu', 'cyber-framework' ); ?>"
				>
					<span class="cyber-hamburger__bar" aria-hidden="true"></span>
					<span class="cyber-hamburger__bar" aria-hidden="true"></span>
					<span class="cyber-hamburger__bar" aria-hidden="true"></span>
				</button>

				<nav
					id="<?php echo esc_attr( $cyber_mobile_menu_id ); ?>"
					class="cyber-header__nav cyber-mobile-menu cyber-mobile-menu--dropdown"
					data-breakpoint="<?php echo esc_attr( (string) $cyber_mobile_bp ); ?>"
					aria-label="<?php esc_attr_e( 'Menu glowne', 'cyber-framework' ); ?>"
				>
					<?php wp_nav_menu( cyber_header_menu_args( $cyber_menu_alignment, $cyber_menu_indicator ) ); ?>
				</nav>

			<?php endif; ?>

		</div>
	</div>
</header>
