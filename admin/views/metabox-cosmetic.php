<?php
/**
 * Métabox : cosmétique (marqueur par défaut, spiderfier, marqueurs par terme).
 *
 * @var array    $values
 * @var \WP_Post $post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$default_url = (string) $values['marker_default_url'];
$default_id  = (int) $values['marker_default_id'];
$width       = (int) $values['marker_default_width'];
$primary_tax = (string) $values['primary_taxonomy'];

$all_tax = get_taxonomies( array( 'public' => true ), 'objects' );

$initial_terms = array();
if ( '' !== $primary_tax && taxonomy_exists( $primary_tax ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $primary_tax,
			'hide_empty' => false,
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		$initial_terms = $terms;
	}
}
?>
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row"><?php esc_html_e( 'Default marker', 'mrz-maps-exp' ); ?></th>
			<td>
				<div class="mrz-maps-exp-media-picker">
					<input type="hidden" name="mrzme[marker_default_id]" class="mrz-maps-exp-media-id" value="<?php echo esc_attr( $default_id ); ?>" />
					<input type="text" name="mrzme[marker_default_url]" class="mrz-maps-exp-media-url regular-text" value="<?php echo esc_attr( $default_url ); ?>" placeholder="https://…/marker.svg" />
					<button type="button" class="button mrz-maps-exp-media-choose"><?php esc_html_e( 'Choose an image', 'mrz-maps-exp' ); ?></button>
					<button type="button" class="button mrz-maps-exp-media-clear"><?php esc_html_e( 'Remove', 'mrz-maps-exp' ); ?></button>
					<div class="mrz-maps-exp-media-preview">
						<?php if ( '' !== $default_url ) : ?>
							<img src="<?php echo esc_url( $default_url ); ?>" alt="" style="max-width:<?php echo (int) $width; ?>px;height:auto;" />
						<?php endif; ?>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'SVG or PNG image used as the marker for posts without a term icon. Leave empty to use the marker bundled with the plugin.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_marker_default_width"><?php esc_html_e( 'Marker width (px)', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[marker_default_width]" id="mrzme_marker_default_width" value="<?php echo esc_attr( $width ); ?>" min="8" max="128" step="1" />
				<p class="description"><?php esc_html_e( 'Applies to all markers (default and per-term markers).', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Spiderfier effect', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzme[spiderfier]" value="1" <?php checked( ! empty( $values['spiderfier'] ) ); ?> />
					<?php esc_html_e( 'Fan out overlapping markers when a group is clicked (useful when several posts share the same coordinates).', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_primary_taxonomy"><?php esc_html_e( 'Marker taxonomy', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<select name="mrzme[primary_taxonomy]" id="mrzme_primary_taxonomy">
					<option value=""><?php esc_html_e( '— None —', 'mrz-maps-exp' ); ?></option>
					<?php foreach ( $all_tax as $tax ) : ?>
						<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $primary_tax, $tax->name ); ?>>
							<?php echo esc_html( $tax->labels->singular_name . ' (' . $tax->name . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Each term in this taxonomy can have its own marker (takes precedence over filter taxonomy icons).', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Per-term markers', 'mrz-maps-exp' ); ?></th>
			<td>
				<div
					id="mrzme_term_markers"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'mrzme_fetch_terms' ) ); ?>"
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
					<?php if ( empty( $primary_tax ) ) : ?>
						<p class="description mrz-maps-exp-term-markers-empty"><?php esc_html_e( 'Select a taxonomy above first.', 'mrz-maps-exp' ); ?></p>
					<?php elseif ( empty( $initial_terms ) ) : ?>
						<p class="description"><?php esc_html_e( 'No terms available in this taxonomy.', 'mrz-maps-exp' ); ?></p>
					<?php else : ?>
						<?php foreach ( $initial_terms as $term ) : ?>
							<?php
							$icon_url = (string) get_term_meta( $term->term_id, '_mrzme_icon_url', true );
							$icon_id  = (int) get_term_meta( $term->term_id, '_mrzme_icon_id', true );
							?>
							<div class="mrz-maps-exp-term-row" data-term-id="<?php echo (int) $term->term_id; ?>">
								<span class="mrz-maps-exp-term-name"><?php echo esc_html( $term->name ); ?></span>
								<div class="mrz-maps-exp-media-picker">
									<input type="hidden" name="mrzme[term_icons][<?php echo (int) $term->term_id; ?>][id]" class="mrz-maps-exp-media-id" value="<?php echo esc_attr( $icon_id ); ?>" />
									<input type="text" name="mrzme[term_icons][<?php echo (int) $term->term_id; ?>][url]" class="mrz-maps-exp-media-url regular-text" value="<?php echo esc_attr( $icon_url ); ?>" />
									<button type="button" class="button mrz-maps-exp-media-choose"><?php esc_html_e( 'Choose', 'mrz-maps-exp' ); ?></button>
									<button type="button" class="button mrz-maps-exp-media-clear"><?php esc_html_e( 'Remove', 'mrz-maps-exp' ); ?></button>
									<div class="mrz-maps-exp-media-preview">
										<?php if ( '' !== $icon_url ) : ?>
											<img src="<?php echo esc_url( $icon_url ); ?>" alt="" style="max-width:<?php echo (int) $width; ?>px;height:auto;" />
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<template id="mrz-maps-exp-term-row-template">
	<div class="mrz-maps-exp-term-row" data-term-id="__TERM_ID__">
		<span class="mrz-maps-exp-term-name">__TERM_NAME__</span>
		<div class="mrz-maps-exp-media-picker">
			<input type="hidden" name="mrzme[term_icons][__TERM_ID__][id]" class="mrz-maps-exp-media-id" value="__ICON_ID__" />
			<input type="text" name="mrzme[term_icons][__TERM_ID__][url]" class="mrz-maps-exp-media-url regular-text" value="__ICON_URL__" />
			<button type="button" class="button mrz-maps-exp-media-choose"><?php esc_html_e( 'Choose', 'mrz-maps-exp' ); ?></button>
			<button type="button" class="button mrz-maps-exp-media-clear"><?php esc_html_e( 'Remove', 'mrz-maps-exp' ); ?></button>
			<div class="mrz-maps-exp-media-preview"></div>
		</div>
	</div>
</template>
