<?php
/**
 * Nettoyage à la désinstallation du plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Supprime tous les posts du CPT et leurs métas associées.
$cpt       = 'mrz_maps_exp_map';
$map_ids   = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
		$cpt
	)
);

if ( ! empty( $map_ids ) ) {
	foreach ( $map_ids as $map_id ) {
		wp_delete_post( (int) $map_id, true );
	}
}

// Supprime les post_meta orphelines éventuelles (_mrz_maps_exp_*).
$wpdb->query(
	"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\\_mrz\\_maps\\_exp\\_%'"
);

// Supprime les term_meta icônes.
$wpdb->query(
	"DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE '\\_mrz\\_maps\\_exp\\_%'"
);

// Supprime les options et transients.
delete_option( 'mrz_maps_exp_settings' );

$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_mrz\\_maps\\_exp\\_%' OR option_name LIKE '\\_transient\\_timeout\\_mrz\\_maps\\_exp\\_%'"
);
