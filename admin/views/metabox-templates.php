<?php
/**
 * Métabox : templates HTML (infobulle et liste).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p>
	<?php
	esc_html_e( 'Placeholders disponibles :', 'mrz-maps-exp' );
	echo ' <code>{post_title}</code>, <code>{post_url}</code>, <code>{post_excerpt}</code>, <code>{post_thumbnail}</code>, <code>{post_thumbnail_url}</code>, <code>{%nom_champ_acf%}</code>, <code>{taxonomy:slug}</code>, <code>{taxonomy:slug:first}</code>.';
	?>
	<br />
	<?php
	esc_html_e( 'Conditionnels :', 'mrz-maps-exp' );
	echo ' <code>{#if %mon_champ%}&lt;div&gt;...&lt;/div&gt;{/if}</code>';
	?>
</p>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_tpl_tooltip"><?php esc_html_e( 'Template infobulle', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<textarea name="mrz_maps_exp[tpl_tooltip]" id="mrz_maps_exp_tpl_tooltip" rows="8" class="large-text code"><?php echo esc_textarea( $values['tpl_tooltip'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'HTML affiché dans l\'infobulle de chaque marker.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_tpl_list"><?php esc_html_e( 'Template liste', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<textarea name="mrz_maps_exp[tpl_list]" id="mrz_maps_exp_tpl_list" rows="8" class="large-text code"><?php echo esc_textarea( $values['tpl_list'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'HTML affiché pour chaque entrée de la liste/grille.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
