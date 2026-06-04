<?php
/**
 * Permet d'attacher une icône de marker à chaque terme de taxonomie publique.
 */

namespace MrzMapsExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TaxonomyMarkers {

	const NONCE_ACTION = 'mrz_maps_exp_save_term';
	const NONCE_NAME   = '_mrz_maps_exp_term_nonce';

	public function register() {
		// Les taxonomies sont enregistrées sur `init` — on hooke après.
		add_action( 'init', array( $this, 'register_taxonomy_hooks' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function register_taxonomy_hooks() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_form_fields' ), 10, 1 );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_form_fields' ), 10, 2 );
			add_action( "created_{$taxonomy}", array( $this, 'save' ), 10, 2 );
			add_action( "edited_{$taxonomy}", array( $this, 'save' ), 10, 2 );
		}
	}

	public function add_form_fields( $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<div class="form-field">
			<label for="mrz_maps_exp_icon_url"><?php esc_html_e( 'Icône marker mrz-maps-exp', 'mrz-maps-exp' ); ?></label>
			<div class="mrz-maps-exp-term-icon">
				<input type="hidden" name="mrz_maps_exp_icon_id" id="mrz_maps_exp_icon_id" value="" />
				<input type="text" name="mrz_maps_exp_icon_url" id="mrz_maps_exp_icon_url" value="" class="regular-text" />
				<button type="button" class="button mrz-maps-exp-term-icon-choose"><?php esc_html_e( 'Choisir une image', 'mrz-maps-exp' ); ?></button>
				<button type="button" class="button mrz-maps-exp-term-icon-clear"><?php esc_html_e( 'Retirer', 'mrz-maps-exp' ); ?></button>
				<div class="mrz-maps-exp-term-icon-preview" style="margin-top:8px;"></div>
			</div>
			<p><?php esc_html_e( 'Image utilisée comme marker sur la carte pour les posts associés à ce terme.', 'mrz-maps-exp' ); ?></p>
		</div>
		<?php
	}

	public function edit_form_fields( $term, $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$icon_id  = (int) get_term_meta( $term->term_id, '_mrz_maps_exp_icon_id', true );
		$icon_url = (string) get_term_meta( $term->term_id, '_mrz_maps_exp_icon_url', true );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="mrz_maps_exp_icon_url"><?php esc_html_e( 'Icône marker mrz-maps-exp', 'mrz-maps-exp' ); ?></label>
			</th>
			<td>
				<div class="mrz-maps-exp-term-icon">
					<input type="hidden" name="mrz_maps_exp_icon_id" id="mrz_maps_exp_icon_id" value="<?php echo esc_attr( $icon_id ); ?>" />
					<input type="text" name="mrz_maps_exp_icon_url" id="mrz_maps_exp_icon_url" value="<?php echo esc_attr( $icon_url ); ?>" class="regular-text" />
					<button type="button" class="button mrz-maps-exp-term-icon-choose"><?php esc_html_e( 'Choisir une image', 'mrz-maps-exp' ); ?></button>
					<button type="button" class="button mrz-maps-exp-term-icon-clear"><?php esc_html_e( 'Retirer', 'mrz-maps-exp' ); ?></button>
					<div class="mrz-maps-exp-term-icon-preview" style="margin-top:8px;">
						<?php if ( '' !== $icon_url ) : ?>
							<img src="<?php echo esc_url( $icon_url ); ?>" alt="" style="max-width:60px;height:auto;" />
						<?php endif; ?>
					</div>
				</div>
				<p class="description"><?php esc_html_e( 'Image utilisée comme marker sur la carte pour les posts associés à ce terme.', 'mrz-maps-exp' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public function save( $term_id, $tt_id = 0 ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$url = isset( $_POST['mrz_maps_exp_icon_url'] )
			? esc_url_raw( wp_unslash( $_POST['mrz_maps_exp_icon_url'] ) )
			: '';
		$id  = isset( $_POST['mrz_maps_exp_icon_id'] ) ? absint( $_POST['mrz_maps_exp_icon_id'] ) : 0;

		if ( $id > 0 && ! wp_attachment_is_image( $id ) ) {
			// Attachment invalide : on ignore l'ID, on garde juste l'URL.
			$id = 0;
		}

		if ( '' === $url && 0 === $id ) {
			delete_term_meta( $term_id, '_mrz_maps_exp_icon_url' );
			delete_term_meta( $term_id, '_mrz_maps_exp_icon_id' );
		} else {
			update_term_meta( $term_id, '_mrz_maps_exp_icon_url', $url );
			update_term_meta( $term_id, '_mrz_maps_exp_icon_id', $id );
		}
	}

	public function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'mrz-maps-exp-term-icon',
			MRZ_MAPS_EXP_URL . 'admin/js/term-icon.js',
			array( 'jquery' ),
			MRZ_MAPS_EXP_VERSION,
			true
		);
	}
}
