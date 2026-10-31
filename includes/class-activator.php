<?php
/**
 * Actions exécutées à l'activation du plugin.
 */

namespace Mrzme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {

	public static function activate() {
		if ( ! mrzme_has_acf() ) {
			deactivate_plugins( MRZME_BASENAME );
			wp_die(
				esc_html__( 'mrz-maps-exp requires Advanced Custom Fields (Pro recommended). Please install and activate ACF before activating this plugin.', 'mrz-maps-exp' ),
				esc_html__( 'Missing dependency', 'mrz-maps-exp' ),
				array( 'back_link' => true )
			);
		}

		( new CPT() )->register_post_type();
		flush_rewrite_rules();
	}
}
