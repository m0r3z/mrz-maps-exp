<?php
/**
 * Métabox : cosmétique (marqueur par défaut, spiderfier, marqueurs par terme).
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$default_url = (string) $values['marker_default_url'];
$default_id  = (int) $values['marker_default_id'];
$width       = (int) $values['marker_default_width'];
$primary_tax = (string) $values['primary_taxonomy'];

$all_tax = get_taxonomies( array( 'public' => true ), 'objects' );

$initial_terms = array();
if ( '' !== $primary_tax && taxonomy_exists( $primary_tax ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $primary_tax,
			'hide_empty' => false,
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		$initial_terms = $terms;
	}
}
?>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Marqueur par défaut', 'mrz-maps-experience' ); ?></th>
			<td>
				<div class="mrz-maps-exp-media-picker">
					<input type="hidden" name="mrz_maps_exp[marker_default_id]" class="mrz-maps-exp-media-id" value="<?php echo esc_attr( $default_id ); ?>" />
					<input type="text" name="mrz_maps_exp[marker_default_url]" class="mrz-maps-exp-media-url regular-text" value="<?php echo esc_attr( $default_url ); ?>" placeholder="https://…/marker.svg" />
					<button type="button" class="button mrz-maps-exp-media-choose"><?php esc_html_e( 'Choisir une image', 'mrz-maps-experience' ); ?></button>
					<button type="button" class="button mrz-maps-exp-media-clear"><?php esc_html_e( 'Retirer', 'mrz-maps-experience' ); ?></button>
					<div class="mrz-maps-exp-media-preview">
						<?php if ( '' !== $default_url ) : ?>
							<img src="<?php echo esc_url( $default_url ); ?>" alt="" style="max-width:<?php echo (int) $width; ?>px;height:auto;" />
						<?php endif; ?>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Image SVG ou PNG utilisée comme marqueur pour les posts sans icône de terme. Laissez vide pour utiliser le marqueur fourni par le plugin.', 'mrz-maps-experience' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_marker_default_width"><?php esc_html_e( 'Largeur du marqueur (px)', 'mrz-maps-experience' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[marker_default_width]" id="mrz_maps_exp_marker_default_width" value="<?php echo esc_attr( $width ); ?>" min="8" max="128" step="1" />
				<p class="description"><?php esc_html_e( 'S\'applique à tous les marqueurs (défaut + marqueurs par terme).', 'mrz-maps-experience' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Effet Spiderfier', 'mrz-maps-experience' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_maps_exp[spiderfier]" value="1" <?php checked( ! empty( $values['spiderfier'] ) ); ?> />
					<?php esc_html_e( 'Dépiler en éventail les marqueurs superposés lorsqu\'un groupe est cliqué (utile quand plusieurs posts partagent les mêmes coordonnées).', 'mrz-maps-experience' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_primary_taxonomy"><?php esc_html_e( 'Taxonomie des marqueurs', 'mrz-maps-experience' ); ?></label>
			</th>
			<td>
				<select name="mrz_maps_exp[primary_taxonomy]" id="mrz_maps_exp_primary_taxonomy">
					<option value=""><?php esc_html_e( '— Aucune —', 'mrz-maps-experience' ); ?></option>
					<?php foreach ( $all_tax as $tax ) : ?>
						<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $primary_tax, $tax->name ); ?>>
							<?php echo esc_html( $tax->labels->singular_name . ' (' . $tax->name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Chaque terme de cette taxonomie peut avoir son propre marqueur (priorité sur les icônes des taxonomies de filtre).', 'mrz-maps-experience' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Marqueurs par terme', 'mrz-maps-experience' ); ?></th>
			<td>
				<div
					id="mrz_maps_exp_term_markers"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'mrz_maps_exp_fetch_terms' ) ); ?>"
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php if ( empty( $primary_tax ) ) : ?>
						<p class="description mrz-maps-exp-term-markers-empty"><?php esc_html_e( 'Sélectionnez d\'abord une taxonomie ci-dessus.', 'mrz-maps-experience' ); ?></p>
					<?php elseif ( empty( $initial_terms ) ) : ?>
						<p class="description"><?php esc_html_e( 'Aucun terme disponible dans cette taxonomie.', 'mrz-maps-experience' ); ?></p>
					<?php else : ?>
						<?php foreach ( $initial_terms as $term ) : ?>
							<?php
							$icon_url = (string) get_term_meta( $term->term_id, '_mrz_maps_exp_icon_url', true );
							$icon_id  = (int) get_term_meta( $term->term_id, '_mrz_maps_exp_icon_id', true );
							?>
							<div class="mrz-maps-exp-term-row" data-term-id="<?php echo (int) $term->term_id; ?>">
								<span class="mrz-maps-exp-term-name"><?php echo esc_html( $term->name ); ?></span>
								<div class="mrz-maps-exp-media-picker">
									<input type="hidden" name="mrz_maps_exp[term_icons][<?php echo (int) $term->term_id; ?>][id]" class="mrz-maps-exp-media-id" value="<?php echo esc_attr( $icon_id ); ?>" />
									<input type="text" name="mrz_maps_exp[term_icons][<?php echo (int) $term->term_id; ?>][url]" class="mrz-maps-exp-media-url regular-text" value="<?php echo esc_attr( $icon_url ); ?>" />
									<button type="button" class="button mrz-maps-exp-media-choose"><?php esc_html_e( 'Choisir', 'mrz-maps-experience' ); ?></button>
									<button type="button" class="button mrz-maps-exp-media-clear"><?php esc_html_e( 'Retirer', 'mrz-maps-experience' ); ?></button>
									<div class="mrz-maps-exp-media-preview">
										<?php if ( '' !== $icon_url ) : ?>
											<img src="<?php echo esc_url( $icon_url ); ?>" alt="" style="max-width:<?php echo (int) $width; ?>px;height:auto;" />
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<template id="mrz-maps-exp-term-row-template">
	<div class="mrz-maps-exp-term-row" data-term-id="__TERM_ID__">
		<span class="mrz-maps-exp-term-name">__TERM_NAME__</span>
		<div class="mrz-maps-exp-media-picker">
			<input type="hidden" name="mrz_maps_exp[term_icons][__TERM_ID__][id]" class="mrz-maps-exp-media-id" value="__ICON_ID__" />
			<input type="text" name="mrz_maps_exp[term_icons][__TERM_ID__][url]" class="mrz-maps-exp-media-url regular-text" value="__ICON_URL__" />
			<button type="button" class="button mrz-maps-exp-media-choose"><?php esc_html_e( 'Choisir', 'mrz-maps-experience' ); ?></button>
			<button type="button" class="button mrz-maps-exp-media-clear"><?php esc_html_e( 'Retirer', 'mrz-maps-experience' ); ?></button>
			<div class="mrz-maps-exp-media-preview"></div>
		</div>
	</div>
</template>
