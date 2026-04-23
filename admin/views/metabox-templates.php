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
	esc_html_e( 'Placeholders disponibles :', 'gmaps-aa' );
	echo ' <code>{post_title}</code>, <code>{post_url}</code>, <code>{post_excerpt}</code>, <code>{post_thumbnail}</code>, <code>{post_thumbnail_url}</code>, <code>{%nom_champ_acf%}</code>, <code>{taxonomy:slug}</code>, <code>{taxonomy:slug:first}</code>.';
	?>
	<br />
	<?php
	esc_html_e( 'Conditionnels :', 'gmaps-aa' );
	echo ' <code>{#if %mon_champ%}&lt;div&gt;...&lt;/div&gt;{/if}</code>';
	?>
</p>
<table class="form-table gmaps-aa-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_tpl_tooltip"><?php esc_html_e( 'Template infobulle', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<textarea name="gmaps_aa[tpl_tooltip]" id="gmaps_aa_tpl_tooltip" rows="8" class="large-text code"><?php echo esc_textarea( $values['tpl_tooltip'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'HTML affiché dans l\'infobulle de chaque marker.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_tpl_list"><?php esc_html_e( 'Template liste', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<textarea name="gmaps_aa[tpl_list]" id="gmaps_aa_tpl_list" rows="8" class="large-text code"><?php echo esc_textarea( $values['tpl_list'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'HTML affiché pour chaque entrée de la liste/grille.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
