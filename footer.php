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
get_template_part(
	'template-parts/footer/footer',
	null,
	array(
		'logo_url'  => cyber_get_option( 'footer_logo' ),
		'site_name' => get_bloginfo( 'name' ),
		'content'   => cyber_get_option( 'footer_content' ),
	)
);

$cyber_copyright = cyber_copyright_data();

if ( cyber_copyright_has_content( $cyber_copyright ) ) {
	get_template_part( 'template-parts/footer/copyright', null, $cyber_copyright );
}
?>

<?php wp_footer(); ?>
</body>
</html>
