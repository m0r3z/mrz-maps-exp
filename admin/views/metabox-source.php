<?php
/**
 * Métabox : source des données (post type, champ ACF coordonnées, limite).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$public_pts = get_post_types( array( 'public' => true ), 'objects' );
?>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_source_pt"><?php esc_html_e( 'Post type source', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<select name="mrz_maps_exp[source_pt]" id="mrz_maps_exp_source_pt" class="mrz-maps-exp-source-pt">
					<?php foreach ( $public_pts as $pt ) : ?>
						<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $values['source_pt'], $pt->name ); ?>>
							<?php echo esc_html( $pt->labels->singular_name . ' (' . $pt->name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Le post type dont les entrées seront affichées sur la carte.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_acf_field"><?php esc_html_e( 'Champ ACF des coordonnées', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="text" name="mrz_maps_exp[acf_field]" id="mrz_maps_exp_acf_field" value="<?php echo esc_attr( $values['acf_field'] ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Nom du champ ACF de type « Google Map » qui contient les coordonnées.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_limit"><?php esc_html_e( 'Nombre max de posts', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[limit]" id="mrz_maps_exp_limit" value="<?php echo esc_attr( $values['limit'] ); ?>" min="0" step="1" />
				<p class="description"><?php esc_html_e( 'Limite de chargement côté serveur. 0 = illimité.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_per_page"><?php esc_html_e( 'Posts par page (liste)', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[per_page]" id="mrz_maps_exp_per_page" value="<?php echo esc_attr( $values['per_page'] ); ?>" min="0" step="1" />
				<p class="description"><?php esc_html_e( 'Pagination côté utilisateur : nombre de posts affichés par page dans la liste. 0 = pas de pagination (tous affichés). La carte affiche toujours tous les marqueurs.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
