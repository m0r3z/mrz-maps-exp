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
<table class="form-table gmaps-aa-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_snazzy"><?php esc_html_e( 'JSON Snazzy Maps', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<textarea name="gmaps_aa[snazzy]" id="gmaps_aa_snazzy" rows="8" class="large-text code" placeholder="[ { &quot;featureType&quot;: &quot;all&quot;, ... } ]"><?php echo esc_textarea( $values['snazzy'] ); ?></textarea>
				<p class="description">
					<?php
					echo wp_kses(
						__( 'Collez le JSON d\'un style <a href="https://snazzymaps.com/" target="_blank" rel="noopener">Snazzy Maps</a>. Laissez vide pour le style par défaut.', 'gmaps-aa' ),
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
