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
	'dropdown' => __( 'Menu déroulant', 'mrz-maps-exp' ),
	'radio'    => __( 'Boutons radio', 'mrz-maps-exp' ),
	'checkbox' => __( 'Cases à cocher', 'mrz-maps-exp' ),
);
$logic_labels = array(
	'or'  => __( 'OU', 'mrz-maps-exp' ),
	'and' => __( 'ET', 'mrz-maps-exp' ),
);

$acf_filters = (array) $values['acf_filters'];
?>

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Recherche', 'mrz-maps-exp' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="mrz_maps_exp[search_enabled]" value="1" <?php checked( ! empty( $values['search_enabled'] ) ); ?> />
		<?php esc_html_e( 'Afficher un champ de recherche.', 'mrz-maps-exp' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="mrz_maps_exp[search_local_match]" value="1" <?php checked( ! empty( $values['search_local_match'] ) ); ?> />
		<?php esc_html_e( 'Suggérer aussi les posts dont le titre correspond à la saisie (en plus des adresses Google).', 'mrz-maps-exp' ); ?>
	</label>
</p>
<p>
	<?php esc_html_e( 'Position du champ :', 'mrz-maps-exp' ); ?>
	<?php
	$search_layouts_labels = array(
		'inline' => __( 'Dans le bloc filtres', 'mrz-maps-exp' ),
		'top'    => __( 'En haut, pleine largeur', 'mrz-maps-exp' ),
	);
	foreach ( $search_layouts_labels as $val => $label ) :
		?>
		<label style="margin-right:16px;">
			<input type="radio" name="mrz_maps_exp[search_layout]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['search_layout'], $val ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>
	<?php endforeach; ?>
	<br />
	<span class="description"><?php esc_html_e( 'En mode « En haut », le champ est sorti du bloc des filtres et occupe toute la largeur. Pratique quand les filtres sont placés sur le côté.', 'mrz-maps-exp' ); ?></span>
</p>
<p>
	<label>
		<?php esc_html_e( 'Rayon par défaut (km) :', 'mrz-maps-exp' ); ?>
		<input type="number" name="mrz_maps_exp[search_radius]" value="<?php echo esc_attr( $values['search_radius'] ); ?>" min="1" max="500" step="1" />
	</label>
	<span class="description"><?php esc_html_e( 'Appliqué uniquement quand l\'utilisateur sélectionne une adresse.', 'mrz-maps-exp' ); ?></span>
</p>
<p>
	<label>
		<?php esc_html_e( 'Libellé affiché au-dessus du champ :', 'mrz-maps-exp' ); ?>
		<input type="text" name="mrz_maps_exp[search_label]" value="<?php echo esc_attr( $values['search_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Rechercher', 'mrz-maps-exp' ); ?>" />
	</label>
</p>
<p>
	<label>
		<?php esc_html_e( 'Placeholder du champ :', 'mrz-maps-exp' ); ?>
		<input type="text" name="mrz_maps_exp[search_placeholder]" value="<?php echo esc_attr( $values['search_placeholder'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Rechercher une adresse…', 'mrz-maps-exp' ); ?>" />
	</label>
</p>

<hr />

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Options globales', 'mrz-maps-exp' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="mrz_maps_exp[show_filter_counts]" value="1" <?php checked( ! empty( $values['show_filter_counts'] ) ); ?> />
		<?php esc_html_e( 'Afficher le nombre de résultats à côté de chaque option de filtre.', 'mrz-maps-exp' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="mrz_maps_exp[url_filters_enabled]" value="1" <?php checked( ! empty( $values['url_filters_enabled'] ) ); ?> />
		<?php esc_html_e( 'Synchroniser les filtres avec l\'URL (lien partageable).', 'mrz-maps-exp' ); ?>
	</label>
	<br />
	<span class="description"><?php esc_html_e( 'Format : ?gm_ID_tax_slug=12,34&gm_ID_acf_field=valeur. L\'URL se met à jour automatiquement quand l\'utilisateur change un filtre.', 'mrz-maps-exp' ); ?></span>
</p>

<hr />

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Filtres par taxonomie', 'mrz-maps-exp' ); ?></h3>

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
				<span><?php esc_html_e( 'Taxonomie', 'mrz-maps-exp' ); ?></span>
				<span class="mrz-maps-exp-taxo-activate-row">
					<input type="checkbox" name="mrz_maps_exp[taxonomies][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $checked ); ?> />
					<span class="mrz-maps-exp-taxo-name"><?php echo esc_html( $tax->labels->singular_name . ' (' . $slug . ')' ); ?></span>
				</span>
			</label>
			<label class="mrz-maps-exp-taxo-col">
				<span><?php esc_html_e( 'Libellé affiché', 'mrz-maps-exp' ); ?></span>
				<input type="text" name="mrz_maps_exp[taxo_labels][<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $custom_label ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $tax->labels->singular_name ); ?>" />
			</label>
			<label class="mrz-maps-exp-taxo-col">
				<span><?php esc_html_e( 'Type de filtre', 'mrz-maps-exp' ); ?></span>
				<select name="mrz_maps_exp[taxo_modes][<?php echo esc_attr( $slug ); ?>]" class="mrz-maps-exp-taxo-mode">
					<?php foreach ( $modes_labels as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="mrz-maps-exp-taxo-col">
				<span><?php esc_html_e( 'Logique', 'mrz-maps-exp' ); ?></span>
				<select name="mrz_maps_exp[taxo_logic][<?php echo esc_attr( $slug ); ?>]" class="mrz-maps-exp-taxo-logic" title="<?php esc_attr_e( 'Combinaison entre cases cochées', 'mrz-maps-exp' ); ?>">
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
<p class="description"><?php esc_html_e( 'Seules les taxonomies liées au post type sélectionné sont affichées. Le libellé remplace le nom de la taxonomie affiché au-dessus du filtre sur le site.', 'mrz-maps-exp' ); ?></p>

<hr />

<h3 class="mrz-maps-exp-section-title"><?php esc_html_e( 'Filtres par champ ACF', 'mrz-maps-exp' ); ?></h3>
<p class="description">
	<?php esc_html_e( 'Pour les champs Select, Radio, Checkbox ou Vrai/Faux, les options sont détectées automatiquement depuis la configuration ACF. Pour les autres types (texte, nombre), les valeurs distinctes des posts sont collectées dynamiquement.', 'mrz-maps-exp' ); ?>
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
				<span><?php esc_html_e( 'Nom du champ ACF', 'mrz-maps-exp' ); ?></span>
				<input type="text" name="mrz_maps_exp[acf_filters][<?php echo (int) $i; ?>][field]" value="<?php echo esc_attr( $field ); ?>" class="regular-text" placeholder="type_annonce" />
			</label>
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'Libellé affiché', 'mrz-maps-exp' ); ?></span>
				<input type="text" name="mrz_maps_exp[acf_filters][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" placeholder="Type d'annonce" />
			</label>
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'Type de filtre', 'mrz-maps-exp' ); ?></span>
				<select name="mrz_maps_exp[acf_filters][<?php echo (int) $i; ?>][mode]">
					<?php foreach ( $modes_labels as $value => $mlabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
							<?php echo esc_html( $mlabel ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="mrz-maps-exp-acf-col">
				<span><?php esc_html_e( 'Logique', 'mrz-maps-exp' ); ?></span>
				<select name="mrz_maps_exp[acf_filters][<?php echo (int) $i; ?>][logic]" title="<?php esc_attr_e( 'Combinaison entre cases cochées (sans effet en mode dropdown/radio)', 'mrz-maps-exp' ); ?>">
					<?php foreach ( $logic_labels as $value => $llabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $row_logic, $value ); ?>>
							<?php echo esc_html( $llabel ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<button type="button" class="button mrz-maps-exp-acf-remove"><?php esc_html_e( 'Retirer', 'mrz-maps-exp' ); ?></button>
		</div>
	<?php endforeach; ?>
</div>

<p>
	<button type="button" class="button mrz-maps-exp-acf-add"><?php esc_html_e( 'Ajouter un filtre ACF', 'mrz-maps-exp' ); ?></button>
</p>

<template id="mrz-maps-exp-acf-row-template">
	<div class="mrz-maps-exp-acf-row" data-index="__INDEX__">
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Nom du champ ACF', 'mrz-maps-exp' ); ?></span>
			<input type="text" name="mrz_maps_exp[acf_filters][__INDEX__][field]" value="" class="regular-text" placeholder="type_annonce" />
		</label>
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Libellé affiché', 'mrz-maps-exp' ); ?></span>
			<input type="text" name="mrz_maps_exp[acf_filters][__INDEX__][label]" value="" class="regular-text" placeholder="Type d'annonce" />
		</label>
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Type de filtre', 'mrz-maps-exp' ); ?></span>
			<select name="mrz_maps_exp[acf_filters][__INDEX__][mode]">
				<?php foreach ( $modes_labels as $value => $mlabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $mlabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="mrz-maps-exp-acf-col">
			<span><?php esc_html_e( 'Logique', 'mrz-maps-exp' ); ?></span>
			<select name="mrz_maps_exp[acf_filters][__INDEX__][logic]">
				<?php foreach ( $logic_labels as $value => $llabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $llabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="button" class="button mrz-maps-exp-acf-remove"><?php esc_html_e( 'Retirer', 'mrz-maps-exp' ); ?></button>
	</div>
</template>
