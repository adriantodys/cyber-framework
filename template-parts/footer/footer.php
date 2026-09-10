<?php
/**
 * Stopka — uklad czterokolumnowy.
 *
 * Widok czysty: dane przychodza jawnie przez $args z footer.php w rootcie
 * (CLAUDE.md sekcja 4). Ikony social media wypisuje wspolny komponent,
 * ten sam, ktorego uzywa Top Header.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type string $logo_url  URL logo stopki albo pusty string.
 *     @type string $site_name Nazwa witryny — alt logo i tekst zastepczy.
 *     @type string $content   Tresc kolumny 1 z pola WYSIWYG albo pusty string.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_logo_url  = isset( $args['logo_url'] ) ? $args['logo_url'] : '';
$cyber_site_name = isset( $args['site_name'] ) ? $args['site_name'] : '';
$cyber_content   = isset( $args['content'] ) ? $args['content'] : '';
?>

<footer class="cyber-footer">
	<div class="cyber-container">
		<div class="cyber-footer__grid">

			<div class="cyber-footer__col cyber-footer__col--1">
				<?php if ( '' !== $cyber_logo_url ) : ?>
					<p class="cyber-footer__brand">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<img
								class="cyber-footer__logo"
								src="<?php echo esc_url( $cyber_logo_url ); ?>"
								alt="<?php echo esc_attr( $cyber_site_name ); ?>"
							>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $cyber_content ) : ?>
					<div class="cyber-footer__content cyber-footer-text">
						<?php
						/*
						 * Tresc redaktorska z pola WYSIWYG — wp_kses_post() przepuszcza
						 * te same znaczniki co edytor wpisow (CLAUDE.md sekcja 9).
						 */
						echo wp_kses_post( $cyber_content );
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php // Zarezerwowane na przyszla zawartosc — patrz CLAUDE.md, sekcja pol zarezerwowanych. ?>
			<div class="cyber-footer__col cyber-footer__col--2"></div>

			<?php // Zarezerwowane na przyszla zawartosc — patrz CLAUDE.md, sekcja pol zarezerwowanych. ?>
			<div class="cyber-footer__col cyber-footer__col--3"></div>

			<div class="cyber-footer__col cyber-footer__col--4">
				<?php
				/*
				 * Bez wylacznikow, inaczej niz w Top Header: w stopce liczy sie
				 * wylacznie to, czy adres profilu jest wypelniony.
				 */
				cyber_social_icons( array( 'class' => 'cyber-footer__social' ) );
				?>
			</div>

		</div>
	</div>
</footer>
