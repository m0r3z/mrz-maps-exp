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
$meta_like = $wpdb->esc_like( '_mrz_maps_exp_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		$meta_like
	)
);

// Supprime les term_meta icônes.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s",
		$meta_like
	)
);

// Supprime les options et transients.
delete_option( 'mrz_maps_exp_settings' );

$transient_like         = $wpdb->esc_like( '_transient_mrz_maps_exp_' ) . '%';
$transient_timeout_like = $wpdb->esc_like( '_transient_timeout_mrz_maps_exp_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$transient_like,
		$transient_timeout_like
	)
);
