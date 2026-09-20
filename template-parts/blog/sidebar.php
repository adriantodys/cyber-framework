<?php
/**
 * Pasek boczny bloga — obszar widgetow "Blog — pasek boczny".
 *
 * Nie renderuje sie, gdy jest wylaczony w Global Options -> Blog albo nie ma
 * w nim widgetow (cyber_blog_has_sidebar()).
 *
 * @package Cyber_Framework
 */

defined( 'ABSPATH' ) || exit;

if ( ! cyber_blog_has_sidebar() ) {
	return;
}
?>
<aside class="cyber-blog__sidebar" aria-label="<?php esc_attr_e( 'Pasek boczny', 'cyber-framework' ); ?>">
	<?php dynamic_sidebar( CYBER_BLOG_SIDEBAR ); ?>
</aside>
