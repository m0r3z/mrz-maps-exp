<?php
/**
 * Shortcodes [mrzme id="X"] (canonical since v1.1.0)
 * and [mrz_maps_exp id="X"] (legacy alias kept for existing sites).
 */

namespace Mrzme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Shortcode {

	const CANONICAL_TAG = 'mrzme';
	const LEGACY_TAG    = 'mrz_maps_exp';

	public function register() {
		add_shortcode( self::CANONICAL_TAG, array( $this, 'render' ) );
		// Rétrocompat : les sites en production peuvent avoir [mrz_maps_exp]
		// en dur dans leurs pages. On garde l'alias en vie pour ne pas les
		// casser au bump 1.1.0. Ne sera pas retiré tant qu'une majorité de
		// sites ne l'aura pas migré.
		add_shortcode( self::LEGACY_TAG, array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'                 => 0,
				'filter_taxonomy'    => '',
				'filter_term'        => '',
				'hide_forced_filter' => 'false',
			),
			$atts,
			'mrz_maps_exp'
		);

		$map_id = absint( $atts['id'] );
		if ( $map_id <= 0 || get_post_type( $map_id ) !== MRZME_CPT ) {
			return '';
		}

		$data = DataProvider::get_map_data( $map_id );
		if ( null === $data ) {
			return '';
		}

		// Filtre forcé par shortcode.
		$forced_tax  = sanitize_key( $atts['filter_taxonomy'] );
		$forced_term = absint( $atts['filter_term'] );
		$hide_forced = in_array( strtolower( (string) $atts['hide_forced_filter'] ), array( 'true', '1', 'yes' ), true );

		$public_tax = array_keys( get_taxonomies( array( 'public' => true ), 'names' ) );
		if ( '' !== $forced_tax && in_array( $forced_tax, $public_tax, true ) && $forced_term > 0 ) {
			$data['points'] = array_values(
				array_filter(
					$data['points'],
					function ( $p ) use ( $forced_tax, $forced_term ) {
						return isset( $p['terms'][ $forced_tax ] )
							&& in_array( $forced_term, $p['terms'][ $forced_tax ], true );
					}
				)
			);
			$data['forced'] = array(
				'taxonomy' => $forced_tax,
				'term'     => $forced_term,
				'hide'     => $hide_forced,
			);
		}

		// Recentrage sur le post courant si l'option est activée dans l'admin
		// et qu'on est sur une page single du post type source.
		if ( ! empty( $data['config']['centerOnCurrent'] ) && is_singular() ) {
			$current_id = get_queried_object_id();
			if ( $current_id && get_post_type( $current_id ) === $data['config']['sourcePt'] ) {
				foreach ( $data['points'] as $p ) {
					if ( (int) $p['id'] === (int) $current_id ) {
						$data['config']['center'] = array(
							'lat' => (float) $p['lat'],
							'lng' => (float) $p['lng'],
						);
						$data['config']['zoom'] = (int) $data['config']['zoomSearch'];
						break;
					}
				}
			}
		}

		$assets = new Assets();
		if ( ! $assets->enqueue_for_shortcode() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<!-- mrz-maps-exp: clé API Google Maps manquante (visible uniquement par les rédacteurs) -->';
			}
			return '';
		}

		$uid = 'mrz-maps-exp-' . $map_id . '-' . wp_generate_uuid4();

		ob_start();
		include MRZME_DIR . 'public/views/map-wrapper.php';
		return ob_get_clean();
	}
}
