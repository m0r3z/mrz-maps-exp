<?php
/**
 * Métabox : source des données (post type, champ ACF, taxonomies).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$public_pts = get_post_types( array( 'public' => true ), 'objects' );
$all_tax    = get_taxonomies( array( 'public' => true ), 'objects' );

$modes_labels = array(
	'dropdown' => __( 'Menu déroulant', 'gmaps-aa' ),
	'radio'    => __( 'Boutons radio', 'gmaps-aa' ),
	'checkbox' => __( 'Cases à cocher', 'gmaps-aa' ),
);
?>
<table class="form-table gmaps-aa-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_source_pt"><?php esc_html_e( 'Post type source', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<select name="gmaps_aa[source_pt]" id="gmaps_aa_source_pt" class="gmaps-aa-source-pt">
					<?php foreach ( $public_pts as $pt ) : ?>
						<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $values['source_pt'], $pt->name ); ?>>
							<?php echo esc_html( $pt->labels->singular_name . ' (' . $pt->name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Le post type dont les entrées seront affichées sur la carte.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_acf_field"><?php esc_html_e( 'Nom du champ ACF', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="text" name="gmaps_aa[acf_field]" id="gmaps_aa_acf_field" value="<?php echo esc_attr( $values['acf_field'] ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Nom du champ ACF de type « Google Map » qui contient les coordonnées.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_limit"><?php esc_html_e( 'Nombre max de posts', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[limit]" id="gmaps_aa_limit" value="<?php echo esc_attr( $values['limit'] ); ?>" min="0" step="1" />
				<p class="description"><?php esc_html_e( '0 = illimité.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Taxonomies à utiliser', 'gmaps-aa' ); ?></th>
			<td>
				<div class="gmaps-aa-taxo-list">
					<?php foreach ( $all_tax as $tax ) : ?>
						<?php
						$slug        = $tax->name;
						$checked     = in_array( $slug, (array) $values['taxonomies'], true );
						$mode        = isset( $values['taxo_modes'][ $slug ] ) ? $values['taxo_modes'][ $slug ] : 'dropdown';
						$object_type = implode( ',', (array) $tax->object_type );
						?>
						<div class="gmaps-aa-taxo-row" data-object-types="<?php echo esc_attr( $object_type ); ?>">
							<label>
								<input type="checkbox" name="gmaps_aa[taxonomies][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $checked ); ?> />
								<?php echo esc_html( $tax->labels->singular_name . ' (' . $slug . ')' ); ?>
							</label>
							<select name="gmaps_aa[taxo_modes][<?php echo esc_attr( $slug ); ?>]" class="gmaps-aa-taxo-mode">
								<?php foreach ( $modes_labels as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="description"><?php esc_html_e( 'Seules les taxonomies liées au post type sélectionné sont affichées.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
