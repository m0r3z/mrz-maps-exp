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
function mrzme_has_acf() {
	return function_exists( 'get_field' ) && function_exists( 'acf_get_setting' );
}

/**
 * Résout la clé Google Maps API selon l'ordre :
 *   1. filtre `mrzme_api_key`
 *   2. constante `MRZME_API_KEY`
 *   3. valeur stockée par ACF (`acf_get_setting('google_api_key')`)
 */
function mrzme_get_api_key() {
	$key = (string) apply_filters( 'mrzme_api_key', '' );

	if ( '' === $key && defined( 'MRZME_API_KEY' ) ) {
		$key = (string) MRZME_API_KEY;
	}

	if ( '' === $key && function_exists( 'acf_get_setting' ) ) {
		$acf_key = acf_get_setting( 'google_api_key' );
		if ( is_string( $acf_key ) ) {
			$key = $acf_key;
		}
	}

	return $key;
}

