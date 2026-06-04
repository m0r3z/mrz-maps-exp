<?php
/**
 * Actions exécutées à l'activation du plugin.
 */

namespace MrzMapsExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {

	public static function activate() {
		if ( ! mrz_maps_exp_has_acf() ) {
			deactivate_plugins( MRZ_MAPS_EXP_BASENAME );
			wp_die(
				esc_html__( 'mrz-maps-exp nécessite Advanced Custom Fields (Pro recommandé). Veuillez installer et activer ACF avant d\'activer ce plugin.', 'mrz-maps-exp' ),
				esc_html__( 'Dépendance manquante', 'mrz-maps-exp' ),
				array( 'back_link' => true )
			);
		}

		( new CPT() )->register_post_type();
		flush_rewrite_rules();
	}
}
