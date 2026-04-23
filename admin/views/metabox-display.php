<?php
/**
 * Métabox : affichage (hauteur, zoom, centre, layout).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table gmaps-aa-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_height"><?php esc_html_e( 'Hauteur (px)', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[height]" id="gmaps_aa_height" value="<?php echo esc_attr( $values['height'] ); ?>" min="100" max="2000" step="10" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_zoom"><?php esc_html_e( 'Zoom initial', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[zoom]" id="gmaps_aa_zoom" value="<?php echo esc_attr( $values['zoom'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'De 1 (monde) à 22 (bâtiment).', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Centre de la carte', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<?php esc_html_e( 'Latitude :', 'gmaps-aa' ); ?>
					<input type="number" name="gmaps_aa[center_lat]" id="gmaps_aa_center_lat" value="<?php echo esc_attr( $values['center_lat'] ); ?>" step="any" min="-90" max="90" class="small-text" />
				</label>
				<label>
					<?php esc_html_e( 'Longitude :', 'gmaps-aa' ); ?>
					<input type="number" name="gmaps_aa[center_lng]" id="gmaps_aa_center_lng" value="<?php echo esc_attr( $values['center_lng'] ); ?>" step="any" min="-180" max="180" class="small-text" />
				</label>
				<p class="description"><?php esc_html_e( 'Cliquez sur la carte ci-dessous pour définir le centre.', 'gmaps-aa' ); ?></p>
				<div id="gmaps_aa_picker" class="gmaps-aa-picker" style="width:100%;height:300px;border:1px solid #ccd0d4;margin-top:8px;"></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Layout des filtres', 'gmaps-aa' ); ?></th>
			<td>
				<?php foreach ( array( 'above' => __( 'Au-dessus', 'gmaps-aa' ), 'side' => __( 'À côté', 'gmaps-aa' ) ) as $val => $label ) : ?>
					<label style="margin-right:16px;">
						<input type="radio" name="gmaps_aa[layout_filters]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_filters'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Layout de la liste', 'gmaps-aa' ); ?></th>
			<td>
				<?php foreach ( array( 'below' => __( 'En dessous', 'gmaps-aa' ), 'side' => __( 'À côté', 'gmaps-aa' ), 'none' => __( 'Masquée', 'gmaps-aa' ) ) as $val => $label ) : ?>
					<label style="margin-right:16px;">
						<input type="radio" name="gmaps_aa[layout_list]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_list'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Format de la liste', 'gmaps-aa' ); ?></th>
			<td>
				<?php foreach ( array( 'list' => __( 'Liste', 'gmaps-aa' ), 'grid' => __( 'Grille', 'gmaps-aa' ) ) as $val => $label ) : ?>
					<label style="margin-right:16px;">
						<input type="radio" name="gmaps_aa[list_format]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_format'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
	</tbody>
</table>
