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
				<label for="mrzme_snazzy"><?php esc_html_e( 'Snazzy Maps JSON', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<textarea name="mrz_maps_exp[snazzy]" id="mrzme_snazzy" rows="8" class="large-text code" placeholder="[ { &quot;featureType&quot;: &quot;all&quot;, ... } ]"><?php echo esc_textarea( $values['snazzy'] ); ?></textarea>
				<p class="description">
					<?php
					echo wp_kses(
						__( 'Paste the JSON of a <a href="https://snazzymaps.com/" target="_blank" rel="noopener">Snazzy Maps</a> style. Leave empty for the default style.', 'mrz-maps-exp' ),
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
