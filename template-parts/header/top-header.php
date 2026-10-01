<?php
/**
 * Top Header — cienki pasek nad naglowkiem.
 *
 * Widok czysty: dane przychodza jawnie przez $args z header.php
 * (CLAUDE.md sekcja 4). Decyzja "co pokazac" zapadla juz wczesniej,
 * w cyber_top_header_data() — tutaj nie ma zadnych warunkow biznesowych,
 * tylko sprawdzenie, czy dana pozycja istnieje.
 *
 * @package Cyber_Framework
 *
 * @param array $args {
 *     @type array|null $phone  Tablica 'text' i 'href' albo null.
 *     @type array|null $email  Tablica 'text' i 'href' albo null.
 *     @type array      $social Lista profili — sluzy wylacznie do decyzji, czy
 *                              pasek ma sie renderowac. Same ikony wypisuje
 *                              wspolny komponent cyber_social_icons().
 *     @type string     $variant Wariant paska. Domyslnie 'default'.
 *     @type array      $languages      Lista tablic 'code', 'name', 'url', 'lang',
 *                                      'current' (cyber_language_switcher_items()).
 *     @type string     $languages_hint Podpowiedz dla administratora, gdy
 *                                      wlaczonego przelacznika nie da sie pokazac.
 * }
 */

defined( 'ABSPATH' ) || exit;

$cyber_phone = isset( $args['phone'] ) ? $args['phone'] : null;
$cyber_email = isset( $args['email'] ) ? $args['email'] : null;
$cyber_langs = isset( $args['languages'] ) ? $args['languages'] : array();
$cyber_hint  = isset( $args['languages_hint'] ) ? $args['languages_hint'] : '';

$cyber_class = cyber_variant_class( 'cyber-topheader', isset( $args['variant'] ) ? $args['variant'] : 'default' );
?>

<div class="<?php echo esc_attr( $cyber_class ); ?>">
	<div class="cyber-container">
		<div class="cyber-topheader__inner">

			<div class="cyber-topheader__contact">
				<?php if ( null !== $cyber_phone ) : ?>
					<a class="cyber-topheader__link" href="<?php echo esc_url( $cyber_phone['href'] ); ?>">
						<?php
						// Ikona dekoracyjna — nazwe niesie widoczny tekst obok niej.
						echo cyber_get_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<span><?php echo esc_html( $cyber_phone['text'] ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( null !== $cyber_email ) : ?>
					<a class="cyber-topheader__link" href="<?php echo esc_url( $cyber_email['href'] ); ?>">
						<?php
						echo cyber_get_icon( 'envelope' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<span><?php echo esc_html( $cyber_email['text'] ); ?></span>
					</a>
				<?php endif; ?>
			</div>

			<div class="cyber-topheader__end">
				<?php
				// Wspolny komponent — na pasku respektuje wylaczniki cyber_topheader_show_*.
				cyber_social_icons(
					array(
						'respect_toggles' => true,
						'class'           => 'cyber-topheader__social',
					)
				);
				?>

				<?php if ( array() !== $cyber_langs ) : ?>
					<nav class="cyber-topheader__lang" aria-label="<?php esc_attr_e( 'Wybor jezyka', 'cyber-framework' ); ?>">
						<ul class="cyber-topheader__lang-list">
							<?php foreach ( $cyber_langs as $cyber_lang ) : ?>
								<li class="cyber-topheader__lang-item">
									<a
										class="cyber-topheader__lang-link<?php echo $cyber_lang['current'] ? ' cyber-topheader__lang-link--current' : ''; ?>"
										href="<?php echo esc_url( $cyber_lang['url'] ); ?>"
										<?php if ( '' !== $cyber_lang['lang'] ) : ?>
											lang="<?php echo esc_attr( $cyber_lang['lang'] ); ?>"
											hreflang="<?php echo esc_attr( $cyber_lang['lang'] ); ?>"
										<?php endif; ?>
										<?php if ( $cyber_lang['current'] ) : ?>
											aria-current="page"
										<?php endif; ?>
									>
										<?php echo esc_html( $cyber_lang['code'] ); ?>
										<?php if ( '' !== $cyber_lang['name'] ) : ?>
											<?php // Pelna nazwa dla czytnika; widoczny kod zostaje poczatkiem nazwy linku. ?>
											<span class="screen-reader-text"><?php echo esc_html( $cyber_lang['name'] ); ?></span>
										<?php endif; ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php elseif ( '' !== $cyber_hint ) : ?>
					<span class="cyber-topheader__lang-missing"><?php echo esc_html( $cyber_hint ); ?></span>
				<?php endif; ?>
			</div>

		</div>
	</div>
</div>
