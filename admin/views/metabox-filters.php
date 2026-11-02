<?php
/**
 * Métabox : configuration des filtres (taxonomies + champs ACF).
 *
 * @var array $values
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_tax      = get_taxonomies( array( 'public' => true ), 'objects' );
$modes_labels = array(
	'dropdown' => __( 'Dropdown', 'mrz-maps-exp' ),
	'radio'    => __( 'Radio buttons', 'mrz-maps-exp' ),
	'checkbox' => __( 'Checkboxes', 'mrz-maps-exp' ),
);
$logic_labels = array(
	'or'  => __( 'OR', 'mrz-maps-exp' ),
	'and' => __( 'AND', 'mrz-maps-exp' ),
);

$acf_filters = (array) $values['acf_filters'];
?>

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Search field', 'mrz-maps-exp' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="mrzme[search_enabled]" value="1" <?php checked( ! empty( $values['search_enabled'] ) ); ?> />
		<?php esc_html_e( 'Show a search field.', 'mrz-maps-exp' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="mrzme[search_local_match]" value="1" <?php checked( ! empty( $values['search_local_match'] ) ); ?> />
		<?php esc_html_e( 'Also suggest posts whose title matches the input (in addition to Google addresses).', 'mrz-maps-exp' ); ?>
	</label>
</p>
<p>
	<?php esc_html_e( 'Field position:', 'mrz-maps-exp' ); ?>
	<?php
	$search_layouts_labels = array(
		'inline' => __( 'Inside the filters block', 'mrz-maps-exp' ),
		'top'    => __( 'Top, full width', 'mrz-maps-exp' ),
	);
	foreach ( $search_layouts_labels as $val => $label ) :
		?>
		<label style="margin-right:16px;">
			<input type="radio" name="mrzme[search_layout]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['search_layout'], $val ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>
	<?php endforeach; ?>
	<br />
	<span class="description"><?php esc_html_e( 'In “Top” mode, the field is moved out of the filters block and spans the full width. Useful when the filters are placed on the side.', 'mrz-maps-exp' ); ?></span>
</p>
<p>
	<label>
		<?php esc_html_e( 'Default radius (km):', 'mrz-maps-exp' ); ?>
		<input type="number" name="mrzme[search_radius]" value="<?php echo esc_attr( $values['search_radius'] ); ?>" min="1" max="500" step="1" />
	</label>
	<span class="description"><?php esc_html_e( 'Only applied when the user selects an address.', 'mrz-maps-exp' ); ?></span>
</p>
<p>
	<label>
		<?php esc_html_e( 'Label shown above the field:', 'mrz-maps-exp' ); ?>
		<input type="text" name="mrzme[search_label]" value="<?php echo esc_attr( $values['search_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Search', 'mrz-maps-exp' ); ?>" />
	</label>
</p>
<p>
	<label>
		<?php esc_html_e( 'Field placeholder:', 'mrz-maps-exp' ); ?>
		<input type="text" name="mrzme[search_placeholder]" value="<?php echo esc_attr( $values['search_placeholder'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Search for an address…', 'mrz-maps-exp' ); ?>" />
	</label>
</p>

<hr />

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Global options', 'mrz-maps-exp' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="mrzme[show_filter_counts]" value="1" <?php checked( ! empty( $values['show_filter_counts'] ) ); ?> />
		<?php esc_html_e( 'Show the number of results next to each filter option.', 'mrz-maps-exp' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="mrzme[url_filters_enabled]" value="1" <?php checked( ! empty( $values['url_filters_enabled'] ) ); ?> />
		<?php esc_html_e( 'Sync filters with the URL (shareable link).', 'mrz-maps-exp' ); ?>
	</label>
	<br />
	<span class="description"><?php esc_html_e( 'Format: ?gm_ID_tax_slug=12,34&gm_ID_acf_field=value. The URL updates automatically when the user changes a filter.', 'mrz-maps-exp' ); ?></span>
</p>

<hr />

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Taxonomy filters', 'mrz-maps-exp' ); ?></h3>

<div class="mrz-maps-exp-taxo-list">
	<?php foreach ( $all_tax as $tax ) : ?>
		<?php
		$slug         = $tax->name;
		$checked      = in_array( $slug, (array) $values['taxonomies'], true );
		$mode         = isset( $values['taxo_modes'][ $slug ] ) ? $values['taxo_modes'][ $slug ] : 'dropdown';
		$logic        = isset( $values['taxo_logic'][ $slug ] ) ? $values['taxo_logic'][ $slug ] : 'or';
		$custom_label = isset( $values['taxo_labels'][ $slug ] ) ? $values['taxo_labels'][ $slug ] : '';
		$object_type  = implode( ',', (array) $tax->object_type );
		?>
		<div class="mrz-maps-exp-taxo-row" data-object-types="<?php echo esc_attr( $object_type ); ?>">
			<label class="mrz-maps-exp-taxo-col mrz-maps-exp-taxo-col-activate">
				<span><?php esc_html_e( 'Taxonomy', 'mrz-maps-exp' ); ?></span>
				<span class="mrz-maps-exp-taxo-activate-row">
					<input type="checkbox" name="mrzme[taxonomies][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $checked ); ?> />
					<span class="mrz-maps-exp-taxo-name"><?php echo esc_html( $tax->labels->singular_name . ' (' . $slug . ')' ); ?></span>
				</span>
			</label>
			<label class="mrz-maps-exp-taxo-col">
				<span><?php esc_html_e( 'Display label', 'mrz-maps-exp' ); ?></span>
				<input type="text" name="mrzme[taxo_labels][<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $custom_label ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $tax->labels->singular_name ); ?>" />
			</label>
			<label class="mrz-maps-exp-taxo-col">
				<span><?php esc_html_e( 'Filter type', 'mrz-maps-exp' ); ?></span>
				<select name="mrzme[taxo_modes][<?php echo esc_attr( $slug ); ?>]" class="mrz-maps-exp-taxo-mode">
					<?php foreach ( $modes_labels as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="mrz-maps-exp-taxo-col">
				<span><?php esc_html_e( 'Logic', 'mrz-maps-exp' ); ?></span>
				<select name="mrzme[taxo_logic][<?php echo esc_attr( $slug ); ?>]" class="mrz-maps-exp-taxo-logic" title="<?php esc_attr_e( 'How checked boxes are combined', 'mrz-maps-exp' ); ?>">
					<?php foreach ( $logic_labels as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $logic, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
	<?php endforeach; ?>
</div>
<p class="description"><?php esc_html_e( 'Only taxonomies attached to the selected post type are listed. The label replaces the taxonomy name displayed above the filter on the site.', 'mrz-maps-exp' ); ?></p>

<hr />

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'ACF field filters', 'mrz-maps-exp' ); ?></h3>
<p class="description">
	<?php esc_html_e( 'For Select, Radio Button, Checkbox or True / False fields, options are detected automatically from the ACF configuration. For other types (text, number), distinct values are collected dynamically from the posts.', 'mrz-maps-exp' ); ?>
</p>

<div class="mrz-maps-exp-acf-filters" data-next-index="<?php echo (int) count( $acf_filters ); ?>">
	<?php foreach ( $acf_filters as $i => $row ) : ?>
		<?php
		$field     = isset( $row['field'] ) ? $row['field'] : '';
		$label     = isset( $row['label'] ) ? $row['label'] : '';
		$mode      = isset( $row['mode'] ) ? $row['mode'] : 'dropdown';
		$row_logic = isset( $row['logic'] ) ? $row['logic'] : 'or';
		?>
		<div class="mrz-maps-exp-acf-row" data-index="<?php echo (int) $i; ?>">
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'ACF field name', 'mrz-maps-exp' ); ?></span>
				<input type="text" name="mrzme[acf_filters][<?php echo (int) $i; ?>][field]" value="<?php echo esc_attr( $field ); ?>" class="regular-text" placeholder="type_annonce" />
			</label>
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'Display label', 'mrz-maps-exp' ); ?></span>
				<input type="text" name="mrzme[acf_filters][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Listing type', 'mrz-maps-exp' ); ?>" />
			</label>
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'Filter type', 'mrz-maps-exp' ); ?></span>
				<select name="mrzme[acf_filters][<?php echo (int) $i; ?>][mode]">
					<?php foreach ( $modes_labels as $value => $mlabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
							<?php echo esc_html( $mlabel ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'Logic', 'mrz-maps-exp' ); ?></span>
				<select name="mrzme[acf_filters][<?php echo (int) $i; ?>][logic]" title="<?php esc_attr_e( 'How checked boxes are combined (no effect in dropdown/radio mode)', 'mrz-maps-exp' ); ?>">
					<?php foreach ( $logic_labels as $value => $llabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $row_logic, $value ); ?>>
							<?php echo esc_html( $llabel ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<button type="button" class="button mrz-maps-exp-acf-remove"><?php esc_html_e( 'Remove', 'mrz-maps-exp' ); ?></button>
		</div>
	<?php endforeach; ?>
</div>

<p>
	<button type="button" class="button mrz-maps-exp-acf-add"><?php esc_html_e( 'Add ACF filter', 'mrz-maps-exp' ); ?></button>
</p>

<template id="mrz-maps-exp-acf-row-template">
	<div class="mrz-maps-exp-acf-row" data-index="__INDEX__">
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'ACF field name', 'mrz-maps-exp' ); ?></span>
			<input type="text" name="mrzme[acf_filters][__INDEX__][field]" value="" class="regular-text" placeholder="type_annonce" />
		</label>
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Display label', 'mrz-maps-exp' ); ?></span>
			<input type="text" name="mrzme[acf_filters][__INDEX__][label]" value="" class="regular-text" placeholder="<?php esc_attr_e( 'Listing type', 'mrz-maps-exp' ); ?>" />
		</label>
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Filter type', 'mrz-maps-exp' ); ?></span>
			<select name="mrzme[acf_filters][__INDEX__][mode]">
				<?php foreach ( $modes_labels as $value => $mlabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $mlabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Logic', 'mrz-maps-exp' ); ?></span>
			<select name="mrzme[acf_filters][__INDEX__][logic]">
				<?php foreach ( $logic_labels as $value => $llabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $llabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="button" class="button mrz-maps-exp-acf-remove"><?php esc_html_e( 'Remove', 'mrz-maps-exp' ); ?></button>
	</div>
</template>
