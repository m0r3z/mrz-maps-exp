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

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Vue incluse depuis MapConfig::render() : variables locales à la méthode.

$shortcode = '[mrzme id="' . (int) $post->ID . '"]';
?>
<p><?php esc_html_e( 'Paste this shortcode into your content:', 'mrz-maps-exp' ); ?></p>
<input type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select();" class="widefat code" />
<p class="description"><?php esc_html_e( 'Example with a forced filter:', 'mrz-maps-exp' ); ?>
	<code>[mrzme id="<?php echo (int) $post->ID; ?>" filter_taxonomy="category" filter_term="5"]</code>
</p>
