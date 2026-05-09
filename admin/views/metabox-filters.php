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
$logic_labels = array(
	'or'  => __( 'OU', 'gmaps-aa' ),
	'and' => __( 'ET', 'gmaps-aa' ),
);

$acf_filters = (array) $values['acf_filters'];
?>

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Recherche', 'gmaps-aa' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="gmaps_aa[search_enabled]" value="1" <?php checked( ! empty( $values['search_enabled'] ) ); ?> />
		<?php esc_html_e( 'Afficher un champ de recherche.', 'gmaps-aa' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="gmaps_aa[search_local_match]" value="1" <?php checked( ! empty( $values['search_local_match'] ) ); ?> />
		<?php esc_html_e( 'Suggérer aussi les posts dont le titre correspond à la saisie (en plus des adresses Google).', 'gmaps-aa' ); ?>
	</label>
</p>
<p>
	<?php esc_html_e( 'Position du champ :', 'gmaps-aa' ); ?>
	<?php
	$search_layouts_labels = array(
		'inline' => __( 'Dans le bloc filtres', 'gmaps-aa' ),
		'top'    => __( 'En haut, pleine largeur', 'gmaps-aa' ),
	);
	foreach ( $search_layouts_labels as $val => $label ) :
		?>
		<label style="margin-right:16px;">
			<input type="radio" name="gmaps_aa[search_layout]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['search_layout'], $val ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>
	<?php endforeach; ?>
	<br />
	<span class="description"><?php esc_html_e( 'En mode « En haut », le champ est sorti du bloc des filtres et occupe toute la largeur. Pratique quand les filtres sont placés sur le côté.', 'gmaps-aa' ); ?></span>
</p>
<p>
	<label>
		<?php esc_html_e( 'Rayon par défaut (km) :', 'gmaps-aa' ); ?>
		<input type="number" name="gmaps_aa[search_radius]" value="<?php echo esc_attr( $values['search_radius'] ); ?>" min="1" max="500" step="1" />
	</label>
	<span class="description"><?php esc_html_e( 'Appliqué uniquement quand l\'utilisateur sélectionne une adresse.', 'gmaps-aa' ); ?></span>
</p>
<p>
	<label>
		<?php esc_html_e( 'Libellé affiché au-dessus du champ :', 'gmaps-aa' ); ?>
		<input type="text" name="gmaps_aa[search_label]" value="<?php echo esc_attr( $values['search_label'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Rechercher', 'gmaps-aa' ); ?>" />
	</label>
</p>
<p>
	<label>
		<?php esc_html_e( 'Placeholder du champ :', 'gmaps-aa' ); ?>
		<input type="text" name="gmaps_aa[search_placeholder]" value="<?php echo esc_attr( $values['search_placeholder'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Rechercher une adresse…', 'gmaps-aa' ); ?>" />
	</label>
</p>

<hr />

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Options globales', 'gmaps-aa' ); ?></h3>
<p>
	<label>
		<input type="checkbox" name="gmaps_aa[show_filter_counts]" value="1" <?php checked( ! empty( $values['show_filter_counts'] ) ); ?> />
		<?php esc_html_e( 'Afficher le nombre de résultats à côté de chaque option de filtre.', 'gmaps-aa' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="gmaps_aa[url_filters_enabled]" value="1" <?php checked( ! empty( $values['url_filters_enabled'] ) ); ?> />
		<?php esc_html_e( 'Synchroniser les filtres avec l\'URL (lien partageable).', 'gmaps-aa' ); ?>
	</label>
	<br />
	<span class="description"><?php esc_html_e( 'Format : ?gm_ID_tax_slug=12,34&gm_ID_acf_field=valeur. L\'URL se met à jour automatiquement quand l\'utilisateur change un filtre.', 'gmaps-aa' ); ?></span>
</p>

<hr />

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Filtres par taxonomie', 'gmaps-aa' ); ?></h3>

<div class="gmaps-aa-taxo-list">
	<?php foreach ( $all_tax as $tax ) : ?>
		<?php
		$slug         = $tax->name;
		$checked      = in_array( $slug, (array) $values['taxonomies'], true );
		$mode         = isset( $values['taxo_modes'][ $slug ] ) ? $values['taxo_modes'][ $slug ] : 'dropdown';
		$logic        = isset( $values['taxo_logic'][ $slug ] ) ? $values['taxo_logic'][ $slug ] : 'or';
		$custom_label = isset( $values['taxo_labels'][ $slug ] ) ? $values['taxo_labels'][ $slug ] : '';
		$object_type  = implode( ',', (array) $tax->object_type );
		?>
		<div class="gmaps-aa-taxo-row" data-object-types="<?php echo esc_attr( $object_type ); ?>">
			<label class="gmaps-aa-taxo-col gmaps-aa-taxo-col-activate">
				<span><?php esc_html_e( 'Taxonomie', 'gmaps-aa' ); ?></span>
				<span class="gmaps-aa-taxo-activate-row">
					<input type="checkbox" name="gmaps_aa[taxonomies][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $checked ); ?> />
					<span class="gmaps-aa-taxo-name"><?php echo esc_html( $tax->labels->singular_name . ' (' . $slug . ')' ); ?></span>
				</span>
			</label>
			<label class="gmaps-aa-taxo-col">
				<span><?php esc_html_e( 'Libellé affiché', 'gmaps-aa' ); ?></span>
				<input type="text" name="gmaps_aa[taxo_labels][<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $custom_label ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $tax->labels->singular_name ); ?>" />
			</label>
			<label class="gmaps-aa-taxo-col">
				<span><?php esc_html_e( 'Type de filtre', 'gmaps-aa' ); ?></span>
				<select name="gmaps_aa[taxo_modes][<?php echo esc_attr( $slug ); ?>]" class="gmaps-aa-taxo-mode">
					<?php foreach ( $modes_labels as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="gmaps-aa-taxo-col">
				<span><?php esc_html_e( 'Logique', 'gmaps-aa' ); ?></span>
				<select name="gmaps_aa[taxo_logic][<?php echo esc_attr( $slug ); ?>]" class="gmaps-aa-taxo-logic" title="<?php esc_attr_e( 'Combinaison entre cases cochées', 'gmaps-aa' ); ?>">
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
<p class="description"><?php esc_html_e( 'Seules les taxonomies liées au post type sélectionné sont affichées. Le libellé remplace le nom de la taxonomie affiché au-dessus du filtre sur le site.', 'gmaps-aa' ); ?></p>

<hr />

<h3 class="gmaps-aa-section-title"><?php esc_html_e( 'Filtres par champ ACF', 'gmaps-aa' ); ?></h3>
<p class="description">
	<?php esc_html_e( 'Pour les champs Select, Radio, Checkbox ou Vrai/Faux, les options sont détectées automatiquement depuis la configuration ACF. Pour les autres types (texte, nombre), les valeurs distinctes des posts sont collectées dynamiquement.', 'gmaps-aa' ); ?>
</p>

<div class="gmaps-aa-acf-filters" data-next-index="<?php echo (int) count( $acf_filters ); ?>">
	<?php foreach ( $acf_filters as $i => $row ) : ?>
		<?php
		$field     = isset( $row['field'] ) ? $row['field'] : '';
		$label     = isset( $row['label'] ) ? $row['label'] : '';
		$mode      = isset( $row['mode'] ) ? $row['mode'] : 'dropdown';
		$row_logic = isset( $row['logic'] ) ? $row['logic'] : 'or';
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
			<label class="gmaps-aa-acf-col">
				<span><?php esc_html_e( 'Logique', 'gmaps-aa' ); ?></span>
				<select name="gmaps_aa[acf_filters][<?php echo (int) $i; ?>][logic]" title="<?php esc_attr_e( 'Combinaison entre cases cochées (sans effet en mode dropdown/radio)', 'gmaps-aa' ); ?>">
					<?php foreach ( $logic_labels as $value => $llabel ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $row_logic, $value ); ?>>
							<?php echo esc_html( $llabel ); ?>
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
		<label class="gmaps-aa-acf-col">
			<span><?php esc_html_e( 'Logique', 'gmaps-aa' ); ?></span>
			<select name="gmaps_aa[acf_filters][__INDEX__][logic]">
				<?php foreach ( $logic_labels as $value => $llabel ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $llabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button type="button" class="button gmaps-aa-acf-remove"><?php esc_html_e( 'Retirer', 'gmaps-aa' ); ?></button>
	</div>
</template>
