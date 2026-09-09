<?php
/**
 * Szkielet stopki dokumentu.
 *
 * SZKIELET TYMCZASOWY (etap 1) — patrz komentarz w header.php.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="cyber-footer">
	<div class="cyber-container">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: rok, 2: nazwa witryny */
					__( '© %1$s %2$s', 'cyber-framework' ),
					gmdate( 'Y' ),
					get_bloginfo( 'name' )
				)
			);
			?>
		</p>
		<?php // TODO (etap 3): pola ACF stopki. ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
