<?php
/**
 * Actions exécutées à l'activation du plugin.
 */

namespace GmapsAA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {

	public static function activate() {
		if ( ! gmaps_aa_has_acf() ) {
			deactivate_plugins( GMAPS_AA_BASENAME );
			wp_die(
				esc_html__( 'gmaps-aa nécessite Advanced Custom Fields (Pro recommandé). Veuillez installer et activer ACF avant d\'activer ce plugin.', 'gmaps-aa' ),
				esc_html__( 'Dépendance manquante', 'gmaps-aa' ),
				array( 'back_link' => true )
			);
		}

		( new CPT() )->register_post_type();
		flush_rewrite_rules();
	}
}
