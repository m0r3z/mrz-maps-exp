<?php
/**
 * Fonctions utilitaires globales.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vérifie si ACF (Pro ou Free) est actif.
 */
function gmaps_aa_has_acf() {
	return function_exists( 'get_field' ) && function_exists( 'acf_get_setting' );
}

/**
 * Résout la clé Google Maps API selon l'ordre :
 *   1. filtre `gmaps_aa_api_key`
 *   2. constante `GMAPS_AA_API_KEY`
 *   3. valeur stockée par ACF (`acf_get_setting('google_api_key')`)
 */
function gmaps_aa_get_api_key() {
	$key = (string) apply_filters( 'gmaps_aa_api_key', '' );

	if ( '' === $key && defined( 'GMAPS_AA_API_KEY' ) ) {
		$key = (string) GMAPS_AA_API_KEY;
	}

	if ( '' === $key && function_exists( 'acf_get_setting' ) ) {
		$acf_key = acf_get_setting( 'google_api_key' );
		if ( is_string( $acf_key ) ) {
			$key = $acf_key;
		}
	}

	return $key;
}

/**
 * Capability requise pour administrer les cartes.
 */
function gmaps_aa_capability() {
	return apply_filters( 'gmaps_aa_capability', 'manage_options' );
}

/**
 * Raccourci d'échappement pour attributs data-*.
 */
function gmaps_aa_esc_data( $value ) {
	return esc_attr( is_scalar( $value ) ? (string) $value : wp_json_encode( $value ) );
}
