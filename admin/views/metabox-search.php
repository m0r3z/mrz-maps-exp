<?php
/**
 * Métabox : recherche par adresse + rayon.
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
			<th scope="row"><?php esc_html_e( 'Activer la recherche', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmaps_aa[search_enabled]" value="1" <?php checked( ! empty( $values['search_enabled'] ) ); ?> />
					<?php esc_html_e( 'Afficher un champ de recherche par adresse.', 'gmaps-aa' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_search_radius"><?php esc_html_e( 'Rayon par défaut (km)', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[search_radius]" id="gmaps_aa_search_radius" value="<?php echo esc_attr( $values['search_radius'] ); ?>" min="1" max="500" step="1" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Afficher le cercle de rayon', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmaps_aa[search_show_circle]" value="1" <?php checked( ! empty( $values['search_show_circle'] ) ); ?> />
					<?php esc_html_e( 'Dessiner un cercle visuel autour de l\'adresse recherchée.', 'gmaps-aa' ); ?>
				</label>
			</td>
		</tr>
	</tbody>
</table>
