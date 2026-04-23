<?php
/**
 * Nettoyage à la désinstallation du plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Supprime tous les posts du CPT et leurs métas associées.
$cpt       = 'gmaps_aa_map';
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

// Supprime les post_meta orphelines éventuelles (_gmaps_aa_*).
$wpdb->query(
	"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\\_gmaps\\_aa\\_%'"
);

// Supprime les term_meta icônes.
$wpdb->query(
	"DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE '\\_gmaps\\_aa\\_%'"
);

// Supprime les options et transients.
delete_option( 'gmaps_aa_settings' );

$wpdb->query(
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_gmaps\\_aa\\_%' OR option_name LIKE '\\_transient\\_timeout\\_gmaps\\_aa\\_%'"
);
