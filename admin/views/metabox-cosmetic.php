<?php
/**
 * Métabox : cosmétique (marqueur par défaut, clusters, marqueurs par terme).
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$default_url   = (string) $values['marker_default_url'];
$default_id    = (int) $values['marker_default_id'];
$width         = (int) $values['marker_default_width'];
$cluster_color = (string) $values['cluster_color'];
$primary_tax   = (string) $values['primary_taxonomy'];

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
<table class="form-table gmaps-aa-table">
	<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Marqueur par défaut', 'gmaps-aa' ); ?></th>
			<td>
				<div class="gmaps-aa-media-picker">
					<input type="hidden" name="gmaps_aa[marker_default_id]" class="gmaps-aa-media-id" value="<?php echo esc_attr( $default_id ); ?>" />
					<input type="text" name="gmaps_aa[marker_default_url]" class="gmaps-aa-media-url regular-text" value="<?php echo esc_attr( $default_url ); ?>" placeholder="https://…/marker.svg" />
					<button type="button" class="button gmaps-aa-media-choose"><?php esc_html_e( 'Choisir une image', 'gmaps-aa' ); ?></button>
					<button type="button" class="button gmaps-aa-media-clear"><?php esc_html_e( 'Retirer', 'gmaps-aa' ); ?></button>
					<div class="gmaps-aa-media-preview">
						<?php if ( '' !== $default_url ) : ?>
							<img src="<?php echo esc_url( $default_url ); ?>" alt="" style="max-width:<?php echo (int) $width; ?>px;height:auto;" />
						<?php endif; ?>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Image SVG ou PNG utilisée comme marqueur pour les posts sans icône de terme. Laissez vide pour utiliser le marqueur fourni par le plugin.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_marker_default_width"><?php esc_html_e( 'Largeur du marqueur (px)', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[marker_default_width]" id="gmaps_aa_marker_default_width" value="<?php echo esc_attr( $width ); ?>" min="8" max="128" step="1" />
				<p class="description"><?php esc_html_e( 'S\'applique à tous les marqueurs (défaut + marqueurs par terme).', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_cluster_color"><?php esc_html_e( 'Couleur des clusters', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="text" name="gmaps_aa[cluster_color]" id="gmaps_aa_cluster_color" class="gmaps-aa-color-picker" value="<?php echo esc_attr( $cluster_color ); ?>" data-default-color="#0073aa" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_primary_taxonomy"><?php esc_html_e( 'Taxonomie des marqueurs', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<select name="gmaps_aa[primary_taxonomy]" id="gmaps_aa_primary_taxonomy">
					<option value=""><?php esc_html_e( '— Aucune —', 'gmaps-aa' ); ?></option>
					<?php foreach ( $all_tax as $tax ) : ?>
						<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $primary_tax, $tax->name ); ?>>
							<?php echo esc_html( $tax->labels->singular_name . ' (' . $tax->name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Chaque terme de cette taxonomie peut avoir son propre marqueur (priorité sur les icônes des taxonomies de filtre).', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Marqueurs par terme', 'gmaps-aa' ); ?></th>
			<td>
				<div
					id="gmaps_aa_term_markers"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'gmaps_aa_fetch_terms' ) ); ?>"
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php if ( empty( $primary_tax ) ) : ?>
						<p class="description gmaps-aa-term-markers-empty"><?php esc_html_e( 'Sélectionnez d\'abord une taxonomie ci-dessus.', 'gmaps-aa' ); ?></p>
					<?php elseif ( empty( $initial_terms ) ) : ?>
						<p class="description"><?php esc_html_e( 'Aucun terme disponible dans cette taxonomie.', 'gmaps-aa' ); ?></p>
					<?php else : ?>
						<?php foreach ( $initial_terms as $term ) : ?>
							<?php
							$icon_url = (string) get_term_meta( $term->term_id, '_gmaps_aa_icon_url', true );
							$icon_id  = (int) get_term_meta( $term->term_id, '_gmaps_aa_icon_id', true );
							?>
							<div class="gmaps-aa-term-row" data-term-id="<?php echo (int) $term->term_id; ?>">
								<span class="gmaps-aa-term-name"><?php echo esc_html( $term->name ); ?></span>
								<div class="gmaps-aa-media-picker">
									<input type="hidden" name="gmaps_aa[term_icons][<?php echo (int) $term->term_id; ?>][id]" class="gmaps-aa-media-id" value="<?php echo esc_attr( $icon_id ); ?>" />
									<input type="text" name="gmaps_aa[term_icons][<?php echo (int) $term->term_id; ?>][url]" class="gmaps-aa-media-url regular-text" value="<?php echo esc_attr( $icon_url ); ?>" />
									<button type="button" class="button gmaps-aa-media-choose"><?php esc_html_e( 'Choisir', 'gmaps-aa' ); ?></button>
									<button type="button" class="button gmaps-aa-media-clear"><?php esc_html_e( 'Retirer', 'gmaps-aa' ); ?></button>
									<div class="gmaps-aa-media-preview">
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

<template id="gmaps-aa-term-row-template">
	<div class="gmaps-aa-term-row" data-term-id="__TERM_ID__">
		<span class="gmaps-aa-term-name">__TERM_NAME__</span>
		<div class="gmaps-aa-media-picker">
			<input type="hidden" name="gmaps_aa[term_icons][__TERM_ID__][id]" class="gmaps-aa-media-id" value="__ICON_ID__" />
			<input type="text" name="gmaps_aa[term_icons][__TERM_ID__][url]" class="gmaps-aa-media-url regular-text" value="__ICON_URL__" />
			<button type="button" class="button gmaps-aa-media-choose"><?php esc_html_e( 'Choisir', 'gmaps-aa' ); ?></button>
			<button type="button" class="button gmaps-aa-media-clear"><?php esc_html_e( 'Retirer', 'gmaps-aa' ); ?></button>
			<div class="gmaps-aa-media-preview"></div>
		</div>
	</div>
</template>
