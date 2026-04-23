<?php
/**
 * Métabox : affichage (hauteur, zoom, centre, layout).
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
				<label for="gmaps_aa_height"><?php esc_html_e( 'Hauteur (px)', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[height]" id="gmaps_aa_height" value="<?php echo esc_attr( $values['height'] ); ?>" min="100" max="2000" step="10" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_zoom"><?php esc_html_e( 'Zoom initial', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[zoom]" id="gmaps_aa_zoom" value="<?php echo esc_attr( $values['zoom'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Niveau de zoom au chargement de la page. De 1 (monde) à 22 (bâtiment).', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_zoom_min"><?php esc_html_e( 'Zoom minimum', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[zoom_min]" id="gmaps_aa_zoom_min" value="<?php echo esc_attr( $values['zoom_min'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Niveau de dézoom maximal autorisé à l\'utilisateur.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_zoom_max"><?php esc_html_e( 'Zoom maximum', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[zoom_max]" id="gmaps_aa_zoom_max" value="<?php echo esc_attr( $values['zoom_max'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Niveau de zoom maximal autorisé à l\'utilisateur.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="gmaps_aa_zoom_search"><?php esc_html_e( 'Zoom après recherche', 'gmaps-aa' ); ?></label>
			</th>
			<td>
				<input type="number" name="gmaps_aa[zoom_search]" id="gmaps_aa_zoom_search" value="<?php echo esc_attr( $values['zoom_search'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Zoom appliqué après la sélection d\'une adresse dans la barre de recherche.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Zoom desktop à la molette', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmaps_aa[cooperative_zoom]" value="1" <?php checked( ! empty( $values['cooperative_zoom'] ) ); ?> />
					<?php esc_html_e( 'Exiger Ctrl/Cmd + molette pour zoomer (affiche le message d\'aide sinon).', 'gmaps-aa' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Décoche pour zoomer directement à la molette sans message. Le comportement tactile (mobile/tablette) n\'est pas affecté : toujours un doigt pour déplacer, pinch pour zoomer.', 'gmaps-aa' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Fermer la tooltip au clic sur la carte', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmaps_aa[close_popup_on_map_click]" value="1" <?php checked( ! empty( $values['close_popup_on_map_click'] ) ); ?> />
					<?php esc_html_e( 'Fermer la tooltip ouverte lorsque l\'utilisateur clique ailleurs sur la carte.', 'gmaps-aa' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Centrer sur le post courant', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmaps_aa[center_on_current]" value="1" <?php checked( ! empty( $values['center_on_current'] ) ); ?> />
					<?php esc_html_e( 'Si le shortcode est rendu sur une page single du post type source, centrer automatiquement la carte sur les coordonnées du post courant (zoom = « Zoom après recherche »).', 'gmaps-aa' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Fit bounds après filtrage', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="gmaps_aa[fitbounds]" value="1" <?php checked( ! empty( $values['fitbounds'] ) ); ?> />
					<?php esc_html_e( 'Ajuster automatiquement le centre et le zoom pour englober les marqueurs visibles après chaque filtrage.', 'gmaps-aa' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Bouton de réinitialisation', 'gmaps-aa' ); ?></th>
			<td>
				<p>
					<label>
						<input type="checkbox" name="gmaps_aa[show_clear_btn]" value="1" <?php checked( ! empty( $values['show_clear_btn'] ) ); ?> />
						<?php esc_html_e( 'Afficher un bouton de réinitialisation de tous les filtres.', 'gmaps-aa' ); ?>
					</label>
				</p>
				<p>
					<label>
						<?php esc_html_e( 'Texte du bouton :', 'gmaps-aa' ); ?>
						<input type="text" name="gmaps_aa[clear_btn_text]" value="<?php echo esc_attr( $values['clear_btn_text'] ); ?>" placeholder="<?php esc_attr_e( 'Effacer', 'gmaps-aa' ); ?>" class="regular-text" />
					</label>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Centre de la carte', 'gmaps-aa' ); ?></th>
			<td>
				<label>
					<?php esc_html_e( 'Latitude :', 'gmaps-aa' ); ?>
					<input type="number" name="gmaps_aa[center_lat]" id="gmaps_aa_center_lat" value="<?php echo esc_attr( $values['center_lat'] ); ?>" step="any" min="-90" max="90" class="small-text" />
				</label>
				<label>
					<?php esc_html_e( 'Longitude :', 'gmaps-aa' ); ?>
					<input type="number" name="gmaps_aa[center_lng]" id="gmaps_aa_center_lng" value="<?php echo esc_attr( $values['center_lng'] ); ?>" step="any" min="-180" max="180" class="small-text" />
				</label>
				<p class="description"><?php esc_html_e( 'Cliquez sur la carte ci-dessous pour définir le centre.', 'gmaps-aa' ); ?></p>
				<div id="gmaps_aa_picker" class="gmaps-aa-picker" style="width:100%;height:300px;border:1px solid #ccd0d4;margin-top:8px;"></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Layout des filtres', 'gmaps-aa' ); ?></th>
			<td>
				<?php
				$filter_layouts = array(
					'above'      => __( 'Au-dessus', 'gmaps-aa' ),
					'side-left'  => __( 'À côté gauche', 'gmaps-aa' ),
					'side-right' => __( 'À côté droit', 'gmaps-aa' ),
				);
				foreach ( $filter_layouts as $val => $label ) :
					?>
					<label style="margin-right:16px;">
						<input type="radio" name="gmaps_aa[layout_filters]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_filters'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Layout de la liste', 'gmaps-aa' ); ?></th>
			<td>
				<?php
				$list_layouts = array(
					'below'      => __( 'En dessous', 'gmaps-aa' ),
					'side-left'  => __( 'À côté gauche', 'gmaps-aa' ),
					'side-right' => __( 'À côté droit', 'gmaps-aa' ),
					'none'       => __( 'Masquée', 'gmaps-aa' ),
				);
				foreach ( $list_layouts as $val => $label ) :
					?>
					<label style="margin-right:16px;">
						<input type="radio" name="gmaps_aa[layout_list]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_list'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Format de la liste', 'gmaps-aa' ); ?></th>
			<td>
				<?php foreach ( array( 'list' => __( 'Liste', 'gmaps-aa' ), 'grid' => __( 'Grille', 'gmaps-aa' ) ) as $val => $label ) : ?>
					<label style="margin-right:16px;">
						<input type="radio" name="gmaps_aa[list_format]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_format'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Clic sur un item de la liste', 'gmaps-aa' ); ?></th>
			<td>
				<?php
				$click_actions = array(
					'tooltip' => __( 'Afficher la tooltip sur la carte', 'gmaps-aa' ),
					'none'    => __( 'Ne rien faire', 'gmaps-aa' ),
					'link'    => __( 'Ouvrir la page du post', 'gmaps-aa' ),
				);
				foreach ( $click_actions as $val => $label ) :
					?>
					<label style="display:block;margin-bottom:4px;">
						<input type="radio" name="gmaps_aa[list_click_action]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_click_action'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
	</tbody>
</table>
