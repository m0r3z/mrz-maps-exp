<?php
/**
 * Métabox : source des données (post type, champ ACF coordonnées, limite).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Vue incluse depuis MapConfig::render() : variables locales à la méthode.

$public_pts = get_post_types( array( 'public' => true ), 'objects' );
?>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrzme_source_pt"><?php esc_html_e( 'Source post type', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<select name="mrzme[source_pt]" id="mrzme_source_pt" class="mrz-maps-exp-source-pt">
					<?php foreach ( $public_pts as $pt ) : ?>
						<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $values['source_pt'], $pt->name ); ?>>
							<?php echo esc_html( $pt->labels->singular_name . ' (' . $pt->name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'The post type whose entries are displayed on the map.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_acf_field"><?php esc_html_e( 'ACF coordinates field', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="text" name="mrzme[acf_field]" id="mrzme_acf_field" value="<?php echo esc_attr( $values['acf_field'] ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Name of the ACF “Google Map” field that holds the coordinates.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_limit"><?php esc_html_e( 'Maximum number of posts', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[limit]" id="mrzme_limit" value="<?php echo esc_attr( $values['limit'] ); ?>" min="0" step="1" />
				<p class="description"><?php esc_html_e( 'Server-side loading limit. 0 = unlimited.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_per_page"><?php esc_html_e( 'Posts per page (list)', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[per_page]" id="mrzme_per_page" value="<?php echo esc_attr( $values['per_page'] ); ?>" min="0" step="1" />
				<p class="description"><?php esc_html_e( 'Client-side pagination: number of posts shown per page in the list. 0 = no pagination (all shown). The map always shows all markers.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
