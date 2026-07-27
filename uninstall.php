<?php
/**
 * Nettoyage à la désinstallation du plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// On nettoie à la fois les données au préfixe courant (`mrzme_`) et celles
// issues des versions précédentes (`mrz_maps_exp_`, `gmaps_aa_`) au cas où
// la migration one-shot n'aurait pas eu l'occasion de tourner avant la
// désinstallation (ex : upgrade puis désactivation sans jamais charger une
// page admin).

// Supprime tous les posts du CPT (nouveau slug + anciens) et leurs métas associées.
$cpt_slugs = array( 'mrzme_map', 'mrz_maps_exp_map', 'gmaps_aa_map' );
$placeholders = implode( ',', array_fill( 0, count( $cpt_slugs ), '%s' ) );
$map_ids = $wpdb->get_col(
	$wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( {$placeholders} )",
		$cpt_slugs
	)
);

if ( ! empty( $map_ids ) ) {
	foreach ( $map_ids as $map_id ) {
		wp_delete_post( (int) $map_id, true );
	}
}

// Supprime les post_meta et term_meta orphelines pour chaque ancien préfixe.
$meta_prefixes = array( '_mrzme_', '_mrz_maps_exp_', '_gmaps_aa_' );
foreach ( $meta_prefixes as $prefix ) {
	$like = $wpdb->esc_like( $prefix ) . '%';
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
			$like
		)
	);
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s",
			$like
		)
	);
}

// Supprime les options et transients (nouveau + anciens).
foreach ( array( 'mrzme_settings', 'mrz_maps_exp_settings', 'gmaps_aa_settings' ) as $opt ) {
	delete_option( $opt );
}
delete_option( 'mrzme_data_migration_v1_1_done' );

$option_prefixes = array( 'mrzme_map_', 'mrz_maps_exp_map_', 'gmaps_aa_map_' );
foreach ( $option_prefixes as $prefix ) {
	$transient_like         = $wpdb->esc_like( '_transient_' . $prefix ) . '%';
	$transient_timeout_like = $wpdb->esc_like( '_transient_timeout_' . $prefix ) . '%';
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$transient_like,
			$transient_timeout_like
		)
	);
}
