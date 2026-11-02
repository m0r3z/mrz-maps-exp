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
<?php
$allowed_code = array( 'code' => array() );
?>
<p>
	<?php esc_html_e( 'Available placeholders:', 'mrz-maps-exp' ); ?>
	<?php
	echo ' ';
	echo wp_kses(
		'<code>{post_title}</code>, <code>{post_url}</code>, <code>{post_excerpt}</code>, <code>{post_thumbnail}</code>, <code>{post_thumbnail_url}</code>, <code>{%nom_champ_acf%}</code>, <code>{taxonomy:slug}</code>, <code>{taxonomy:slug:first}</code>.',
		$allowed_code
	);
	?>
	<br />
	<?php esc_html_e( 'Conditionals:', 'mrz-maps-exp' ); ?>
	<?php
	echo ' ';
	echo wp_kses(
		'<code>{#if %mon_champ%}&lt;div&gt;...&lt;/div&gt;{/if}</code>',
		$allowed_code
	);
	?>
</p>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrzme_tpl_tooltip"><?php esc_html_e( 'Tooltip template', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<textarea name="mrzme[tpl_tooltip]" id="mrzme_tpl_tooltip" rows="8" class="large-text code"><?php echo esc_textarea( $values['tpl_tooltip'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'HTML displayed in each marker\'s tooltip.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_tpl_list"><?php esc_html_e( 'List template', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<textarea name="mrzme[tpl_list]" id="mrzme_tpl_list" rows="8" class="large-text code"><?php echo esc_textarea( $values['tpl_list'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'HTML displayed for each list/grid entry.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
