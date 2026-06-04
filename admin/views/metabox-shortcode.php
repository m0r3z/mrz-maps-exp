<?php
/**
 * Métabox : affichage du shortcode prêt-à-copier.
 *
 * @var array  $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
$shortcode = '[mrz_maps_exp id="' . (int) $post->ID . '"]';
?>
<p><?php esc_html_e( 'Collez ce shortcode dans vos contenus :', 'mrz-maps-experience' ); ?></p>
<input type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select();" class="widefat code" />
<p class="description"><?php esc_html_e( 'Exemple avec filtre forcé :', 'mrz-maps-experience' ); ?>
	<code>[mrz_maps_exp id="<?php echo (int) $post->ID; ?>" filter_taxonomy="category" filter_term="5"]</code>
</p>
