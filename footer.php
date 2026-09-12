<?php
/**
 * Zamkniecie dokumentu.
 *
 * Sama stopka mieszka w template-parts/footer/footer.php i dostaje dane jawnie
 * przez $args (CLAUDE.md sekcja 4) — tutaj tylko zbieramy je przez
 * cyber_get_option() i zamykamy dokument.
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
/*
 * Pasek Copyright renderuje sie WEWNATRZ <footer>, wiec jego dane ida do widoku
 * stopki, a nie osobnym get_template_part() obok niej. Inaczej pasek lezalby
 * poza landmarkiem contentinfo i czytnik ekranu nie zaliczylby go do stopki.
 *
 * Decyzja "czy jest co pokazac" zostaje tutaj, w warstwie logiki — widok
 * sprawdza juz tylko, czy dane przyszly (tak samo jak Top Header).
 */
$cyber_copyright = cyber_copyright_data();

get_template_part(
	'template-parts/footer/footer',
	null,
	array(
		'logo_url'  => cyber_get_option( 'footer_logo' ),
		'site_name' => get_bloginfo( 'name' ),
		'content'   => cyber_get_option( 'footer_content' ),
		'copyright' => cyber_copyright_has_content( $cyber_copyright ) ? $cyber_copyright : null,
	)
);
?>

<?php wp_footer(); ?>
</body>
</html>
