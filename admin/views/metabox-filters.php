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
	'dropdown' => __( 'Menu déroulant', 'gmaps-aa' ),
	'radio'    => __( 'Boutons radio', 'gmaps-aa' ),
	'checkbox' => __( 'Cases à cocher', 'gmaps-aa' ),
);

$acf_filters = (array) $values['acf_filters'];
?>

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Options globales', 'gmaps-aa' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="gmaps_aa[show_filter_counts]" value="1" <?php checked( ! empty( $values['show_filter_counts'] ) ); ?> />
		<?php esc_html_e( 'Afficher le nombre de résultats à côté de chaque option de filtre.', 'gmaps-aa' ); ?>
	</label>
</p>

<hr />

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Filtres par taxonomie', 'gmaps-aa' ); ?></h3>

<div class="gmaps-aa-taxo-list">
	<?php foreach ( $all_tax as $tax ) : ?>
		<?php
		$slug        = $tax->name;
		$checked     = in_array( $slug, (array) $values['taxonomies'], true );
		$mode        = isset( $values['taxo_modes'][ $slug ] ) ? $values['taxo_modes'][ $slug ] : 'dropdown';
		$object_type = implode( ',', (array) $tax->object_type );
		?>
		<div class="gmaps-aa-taxo-row" data-object-types="<?php echo esc_attr( $object_type ); ?>">
			<label>
				<input type="checkbox" name="gmaps_aa[taxonomies][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $checked ); ?> />
				<?php echo esc_html( $tax->labels->singular_name . ' (' . $slug . ')' ); ?>
			</label>
			<select name="gmaps_aa[taxo_modes][<?php echo esc_attr( $slug ); ?>]" class="gmaps-aa-taxo-mode">
				<?php foreach ( $modes_labels as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endforeach; ?>
</div>
<p class="description"><?php esc_html_e( 'Seules les taxonomies liées au post type sélectionné sont affichées.', 'gmaps-aa' ); ?></p>

<hr />

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Filtres par champ ACF', 'gmaps-aa' ); ?></h3>
<p class="description">
	<?php esc_html_e( 'Pour les champs Select, Radio, Checkbox ou Vrai/Faux, les options sont détectées automatiquement depuis la configuration ACF. Pour les autres types (texte, nombre), les valeurs distinctes des posts sont collectées dynamiquement.', 'gmaps-aa' ); ?>
</p>

<div class="gmaps-aa-acf-filters" data-next-index="<?php echo (int) count( $acf_filters ); ?>">
	<?php foreach ( $acf_filters as $i => $row ) : ?>
		<?php
		$field = isset( $row['field'] ) ? $row['field'] : '';
		$label = isset( $row['label'] ) ? $row['label'] : '';
		$mode  = isset( $row['mode'] ) ? $row['mode'] : 'dropdown';
		?>
		<div class="gmaps-aa-acf-row" data-index="<?php echo (int) $i; ?>">
			<label class="gmaps-aa-acf-col">
				<span><?php esc_html_e( 'Nom du champ ACF', 'gmaps-aa' ); ?></span>
				<input type="text" name="gmaps_aa[acf_filters][<?php echo (int) $i; ?>][field]" value="<?php echo esc_attr( $field ); ?>" class="regular-text" placeholder="type_annonce" />
			</label>
			<label class="gmaps-aa-acf-col">
				<span><?php esc_html_e( 'Libellé affiché', 'gmaps-aa' ); ?></span>
				<input type="text" name="gmaps_aa[acf_filters][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" placeholder="Type d'annonce" />
			</label>
			<label class="gmaps-aa-acf-col">
				<span><?php esc_html_e( 'Type de filtre', 'gmaps-aa' ); ?></span>
				<select name="gmaps_aa[acf_filters][<?php echo (int) $i; ?>][mode]">
					<?php foreach ( $modes_labels as $value => $mlabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
							<?php echo esc_html( $mlabel ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<button type="button" class="button gmaps-aa-acf-remove"><?php esc_html_e( 'Retirer', 'gmaps-aa' ); ?></button>
		</div>
	<?php endforeach; ?>
</div>

<p>
	<button type="button" class="button gmaps-aa-acf-add"><?php esc_html_e( 'Ajouter un filtre ACF', 'gmaps-aa' ); ?></button>
</p>

<template id="gmaps-aa-acf-row-template">
	<div class="gmaps-aa-acf-row" data-index="__INDEX__">
		<label class="gmaps-aa-acf-col">
			<span><?php esc_html_e( 'Nom du champ ACF', 'gmaps-aa' ); ?></span>
			<input type="text" name="gmaps_aa[acf_filters][__INDEX__][field]" value="" class="regular-text" placeholder="type_annonce" />
		</label>
		<label class="gmaps-aa-acf-col">
			<span><?php esc_html_e( 'Libellé affiché', 'gmaps-aa' ); ?></span>
			<input type="text" name="gmaps_aa[acf_filters][__INDEX__][label]" value="" class="regular-text" placeholder="Type d'annonce" />
		</label>
		<label class="gmaps-aa-acf-col">
			<span><?php esc_html_e( 'Type de filtre', 'gmaps-aa' ); ?></span>
			<select name="gmaps_aa[acf_filters][__INDEX__][mode]">
				<?php foreach ( $modes_labels as $value => $mlabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $mlabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="button" class="button gmaps-aa-acf-remove"><?php esc_html_e( 'Retirer', 'gmaps-aa' ); ?></button>
	</div>
</template>
