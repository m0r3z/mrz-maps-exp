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
<table class="form-table mrz-maps-exp-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_height"><?php esc_html_e( 'Hauteur (px)', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[height]" id="mrz_maps_exp_height" value="<?php echo esc_attr( $values['height'] ); ?>" min="100" max="2000" step="10" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_zoom"><?php esc_html_e( 'Zoom initial', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[zoom]" id="mrz_maps_exp_zoom" value="<?php echo esc_attr( $values['zoom'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Niveau de zoom au chargement de la page. De 1 (monde) à 22 (bâtiment).', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_zoom_min"><?php esc_html_e( 'Zoom minimum', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[zoom_min]" id="mrz_maps_exp_zoom_min" value="<?php echo esc_attr( $values['zoom_min'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Niveau de dézoom maximal autorisé à l\'utilisateur.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_zoom_max"><?php esc_html_e( 'Zoom maximum', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[zoom_max]" id="mrz_maps_exp_zoom_max" value="<?php echo esc_attr( $values['zoom_max'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Niveau de zoom maximal autorisé à l\'utilisateur.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrz_maps_exp_zoom_search"><?php esc_html_e( 'Zoom après recherche', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrz_maps_exp[zoom_search]" id="mrz_maps_exp_zoom_search" value="<?php echo esc_attr( $values['zoom_search'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Zoom appliqué après la sélection d\'une adresse dans la barre de recherche.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Zoom desktop à la molette', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_maps_exp[cooperative_zoom]" value="1" <?php checked( ! empty( $values['cooperative_zoom'] ) ); ?> />
					<?php esc_html_e( 'Exiger Ctrl/Cmd + molette pour zoomer (affiche le message d\'aide sinon).', 'mrz-maps-exp' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Décoche pour zoomer directement à la molette sans message. Le comportement tactile (mobile/tablette) n\'est pas affecté : toujours un doigt pour déplacer, pinch pour zoomer.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Fermer la tooltip au clic sur la carte', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_maps_exp[close_popup_on_map_click]" value="1" <?php checked( ! empty( $values['close_popup_on_map_click'] ) ); ?> />
					<?php esc_html_e( 'Fermer la tooltip ouverte lorsque l\'utilisateur clique ailleurs sur la carte.', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Centrer sur le post courant', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_maps_exp[center_on_current]" value="1" <?php checked( ! empty( $values['center_on_current'] ) ); ?> />
					<?php esc_html_e( 'Si le shortcode est rendu sur une page single du post type source, centrer automatiquement la carte sur les coordonnées du post courant (zoom = « Zoom après recherche »).', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Fit bounds après filtrage', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrz_maps_exp[fitbounds]" value="1" <?php checked( ! empty( $values['fitbounds'] ) ); ?> />
					<?php esc_html_e( 'Ajuster automatiquement le centre et le zoom pour englober les marqueurs visibles après chaque filtrage.', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Bouton de réinitialisation', 'mrz-maps-exp' ); ?></th>
			<td>
				<p>
					<label>
						<input type="checkbox" name="mrz_maps_exp[show_clear_btn]" value="1" <?php checked( ! empty( $values['show_clear_btn'] ) ); ?> />
						<?php esc_html_e( 'Afficher un bouton de réinitialisation de tous les filtres.', 'mrz-maps-exp' ); ?>
					</label>
				</p>
				<p>
					<label>
						<?php esc_html_e( 'Texte du bouton :', 'mrz-maps-exp' ); ?>
						<input type="text" name="mrz_maps_exp[clear_btn_text]" value="<?php echo esc_attr( $values['clear_btn_text'] ); ?>" placeholder="<?php esc_attr_e( 'Effacer', 'mrz-maps-exp' ); ?>" class="regular-text" />
					</label>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Centre de la carte', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<?php esc_html_e( 'Latitude :', 'mrz-maps-exp' ); ?>
					<input type="number" name="mrz_maps_exp[center_lat]" id="mrz_maps_exp_center_lat" value="<?php echo esc_attr( $values['center_lat'] ); ?>" step="any" min="-90" max="90" class="small-text" />
				</label>
				<label>
					<?php esc_html_e( 'Longitude :', 'mrz-maps-exp' ); ?>
					<input type="number" name="mrz_maps_exp[center_lng]" id="mrz_maps_exp_center_lng" value="<?php echo esc_attr( $values['center_lng'] ); ?>" step="any" min="-180" max="180" class="small-text" />
				</label>
				<p class="description"><?php esc_html_e( 'Cliquez sur la carte ci-dessous pour définir le centre.', 'mrz-maps-exp' ); ?></p>
				<div id="mrz_maps_exp_picker" class="mrz-maps-exp-picker" style="width:100%;height:300px;border:1px solid #ccd0d4;margin-top:8px;"></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Layout des filtres', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php
				$filter_layouts = array(
					'above'      => __( 'Au-dessus', 'mrz-maps-exp' ),
					'side-left'  => __( 'À côté gauche', 'mrz-maps-exp' ),
					'side-right' => __( 'À côté droit', 'mrz-maps-exp' ),
				);
				foreach ( $filter_layouts as $val => $label ) :
					?>
					<label style="margin-right:16px;">
						<input type="radio" name="mrz_maps_exp[layout_filters]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_filters'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Layout de la liste', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php
				$list_layouts = array(
					'below'      => __( 'En dessous', 'mrz-maps-exp' ),
					'side-left'  => __( 'À côté gauche', 'mrz-maps-exp' ),
					'side-right' => __( 'À côté droit', 'mrz-maps-exp' ),
					'none'       => __( 'Masquée', 'mrz-maps-exp' ),
				);
				foreach ( $list_layouts as $val => $label ) :
					?>
					<label style="margin-right:16px;">
						<input type="radio" name="mrz_maps_exp[layout_list]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_list'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Format de la liste', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php foreach ( array( 'list' => __( 'Liste', 'mrz-maps-exp' ), 'grid' => __( 'Grille', 'mrz-maps-exp' ) ) as $val => $label ) : ?>
					<label style="margin-right:16px;">
						<input type="radio" name="mrz_maps_exp[list_format]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_format'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Clic sur un item de la liste', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php
				$click_actions = array(
					'tooltip' => __( 'Afficher la tooltip sur la carte', 'mrz-maps-exp' ),
					'none'    => __( 'Ne rien faire', 'mrz-maps-exp' ),
					'link'    => __( 'Ouvrir la page du post', 'mrz-maps-exp' ),
				);
				foreach ( $click_actions as $val => $label ) :
					?>
					<label style="display:block;margin-bottom:4px;">
						<input type="radio" name="mrz_maps_exp[list_click_action]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_click_action'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
	</tbody>
</table>
