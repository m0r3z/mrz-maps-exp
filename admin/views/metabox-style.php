<?php
/**
 * Métabox : style (Snazzy Maps JSON).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_snazzy"><?php esc_html_e( 'JSON Snazzy Maps', 'mrz-maps-experience' ); ?></label>
			</th>
			<td>
				<textarea name="mrz_maps_exp[snazzy]" id="mrz_maps_exp_snazzy" rows="8" class="large-text code" placeholder="[ { &quot;featureType&quot;: &quot;all&quot;, ... } ]"><?php echo esc_textarea( $values['snazzy'] ); ?></textarea>
				<p class="description">
					<?php
					echo wp_kses(
						__( 'Collez le JSON d\'un style <a href="https://snazzymaps.com/" target="_blank" rel="noopener">Snazzy Maps</a>. Laissez vide pour le style par défaut.', 'mrz-maps-experience' ),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
								'rel'    => true,
							),
						)
					);
					?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
