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
				<label for="mrzme_height"><?php esc_html_e( 'Height (px)', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[height]" id="mrzme_height" value="<?php echo esc_attr( $values['height'] ); ?>" min="100" max="2000" step="10" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_zoom"><?php esc_html_e( 'Initial zoom', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[zoom]" id="mrzme_zoom" value="<?php echo esc_attr( $values['zoom'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Zoom level when the page loads, from 1 (world) to 22 (building).', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_zoom_min"><?php esc_html_e( 'Minimum zoom', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[zoom_min]" id="mrzme_zoom_min" value="<?php echo esc_attr( $values['zoom_min'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Maximum zoom-out level allowed for users.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_zoom_max"><?php esc_html_e( 'Maximum zoom', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[zoom_max]" id="mrzme_zoom_max" value="<?php echo esc_attr( $values['zoom_max'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Maximum zoom-in level allowed for users.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="mrzme_zoom_search"><?php esc_html_e( 'Zoom after search', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<input type="number" name="mrzme[zoom_search]" id="mrzme_zoom_search" value="<?php echo esc_attr( $values['zoom_search'] ); ?>" min="1" max="22" step="1" />
				<p class="description"><?php esc_html_e( 'Zoom level applied after an address is selected in the search bar.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Desktop scroll-wheel zoom', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzme[cooperative_zoom]" value="1" <?php checked( ! empty( $values['cooperative_zoom'] ) ); ?> />
					<?php esc_html_e( 'Require Ctrl/Cmd + scroll wheel to zoom (shows a help message otherwise).', 'mrz-maps-exp' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Uncheck to zoom directly with the scroll wheel, without a message. Touch behavior (mobile/tablet) is not affected: one finger to pan, pinch to zoom.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Close tooltip on map click', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzme[close_popup_on_map_click]" value="1" <?php checked( ! empty( $values['close_popup_on_map_click'] ) ); ?> />
					<?php esc_html_e( 'Close the open tooltip when the user clicks elsewhere on the map.', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Center on current post', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzme[center_on_current]" value="1" <?php checked( ! empty( $values['center_on_current'] ) ); ?> />
					<?php esc_html_e( 'When the shortcode is rendered on a single page of the source post type, automatically center the map on the current post\'s coordinates (zoom = “Zoom after search”).', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Fit bounds after filtering', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="mrzme[fitbounds]" value="1" <?php checked( ! empty( $values['fitbounds'] ) ); ?> />
					<?php esc_html_e( 'Automatically adjust the center and zoom to fit the visible markers after each filter change.', 'mrz-maps-exp' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Reset button', 'mrz-maps-exp' ); ?></th>
			<td>
				<p>
					<label>
						<input type="checkbox" name="mrzme[show_clear_btn]" value="1" <?php checked( ! empty( $values['show_clear_btn'] ) ); ?> />
						<?php esc_html_e( 'Show a button that resets all filters.', 'mrz-maps-exp' ); ?>
					</label>
				</p>
				<p>
					<label>
						<?php esc_html_e( 'Button text:', 'mrz-maps-exp' ); ?>
						<input type="text" name="mrzme[clear_btn_text]" value="<?php echo esc_attr( $values['clear_btn_text'] ); ?>" placeholder="<?php esc_attr_e( 'Clear', 'mrz-maps-exp' ); ?>" class="regular-text" />
					</label>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Map center', 'mrz-maps-exp' ); ?></th>
			<td>
				<label>
					<?php esc_html_e( 'Latitude:', 'mrz-maps-exp' ); ?>
					<input type="number" name="mrzme[center_lat]" id="mrzme_center_lat" value="<?php echo esc_attr( $values['center_lat'] ); ?>" step="any" min="-90" max="90" class="small-text" />
				</label>
				<label>
					<?php esc_html_e( 'Longitude:', 'mrz-maps-exp' ); ?>
					<input type="number" name="mrzme[center_lng]" id="mrzme_center_lng" value="<?php echo esc_attr( $values['center_lng'] ); ?>" step="any" min="-180" max="180" class="small-text" />
				</label>
				<p class="description"><?php esc_html_e( 'Click the map below to set the center.', 'mrz-maps-exp' ); ?></p>
				<div id="mrzme_picker" class="mrz-maps-exp-picker" style="width:100%;height:300px;border:1px solid #ccd0d4;margin-top:8px;"></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Filters layout', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php
				$filter_layouts = array(
					'above'      => __( 'Above', 'mrz-maps-exp' ),
					'side-left'  => __( 'Left side', 'mrz-maps-exp' ),
					'side-right' => __( 'Right side', 'mrz-maps-exp' ),
				);
				foreach ( $filter_layouts as $val => $label ) :
					?>
					<label style="margin-right:16px;">
						<input type="radio" name="mrzme[layout_filters]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_filters'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'List layout', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php
				$list_layouts = array(
					'below'      => __( 'Below', 'mrz-maps-exp' ),
					'side-left'  => __( 'Left side', 'mrz-maps-exp' ),
					'side-right' => __( 'Right side', 'mrz-maps-exp' ),
					'none'       => __( 'Hidden', 'mrz-maps-exp' ),
				);
				foreach ( $list_layouts as $val => $label ) :
					?>
					<label style="margin-right:16px;">
						<input type="radio" name="mrzme[layout_list]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['layout_list'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'List format', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php foreach ( array( 'list' => __( 'List', 'mrz-maps-exp' ), 'grid' => __( 'Grid', 'mrz-maps-exp' ) ) as $val => $label ) : ?>
					<label style="margin-right:16px;">
						<input type="radio" name="mrzme[list_format]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_format'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'List item click', 'mrz-maps-exp' ); ?></th>
			<td>
				<?php
				$click_actions = array(
					'tooltip' => __( 'Show the tooltip on the map', 'mrz-maps-exp' ),
					'none'    => __( 'Do nothing', 'mrz-maps-exp' ),
					'link'    => __( 'Open the post page', 'mrz-maps-exp' ),
				);
				foreach ( $click_actions as $val => $label ) :
					?>
					<label style="display:block;margin-bottom:4px;">
						<input type="radio" name="mrzme[list_click_action]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $values['list_click_action'], $val ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</td>
		</tr>
	</tbody>
</table>
