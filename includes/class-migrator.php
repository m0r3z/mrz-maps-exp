<?php
/**
 * Migration one-shot des données antérieures à v1.1.0.
 *
 * v1.1.0 raccourcit le préfixe interne `mrz_maps_exp_` en `mrzme_` pour se
 * conformer à la règle wordpress.org des préfixes de 4+ caractères. Les
 * données stockées par les versions antérieures utilisent l'ancien préfixe :
 * il faut les convertir au premier chargement post-update pour que les cartes
 * existantes restent fonctionnelles.
 *
 * Concerne :
 *   - post_meta  : _gmaps_aa_*   et _mrz_maps_exp_*   → _mrzme_*
 *   - term_meta  : idem
 *   - CPT posts  : post_type gmaps_aa_map ou mrz_maps_exp_map → mrzme_map
 *   - transients : gmaps_aa_* et mrz_maps_exp_* → mrzme_*
 */

namespace Mrzme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Migrator {

	const OPTION_FLAG = 'mrzme_data_migration_v1_1_done';

	/**
	 * Anciens préfixes à migrer, du plus ancien au plus récent.
	 * Ordre : Doublea → mrz-maps-exp (v1.0.x) → mrzme (v1.1.0+).
	 */
	private static function old_meta_prefixes() {
		return array( '_gmaps_aa_', '_mrz_maps_exp_' );
	}

	private static function old_cpt_slugs() {
		return array( 'gmaps_aa_map', 'mrz_maps_exp_map' );
	}

	private static function old_transient_prefixes() {
		return array( 'gmaps_aa_map_', 'mrz_maps_exp_map_' );
	}

	/**
	 * À appeler tôt dans le boot du plugin. Idempotent (marqué via une option
	 * WP après exécution), donc coût nul pour les runs suivants.
	 */
	public static function maybe_migrate() {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}

		self::migrate_meta( 'postmeta' );
		self::migrate_meta( 'termmeta' );
		self::migrate_cpt_posts();
		self::migrate_transients();

		update_option( self::OPTION_FLAG, time(), false );
	}

	/**
	 * Renomme les clés meta d'une table (_gmaps_aa_foo / _mrz_maps_exp_foo → _mrzme_foo).
	 *
	 * @param string $table postmeta | termmeta
	 */
	private static function migrate_meta( $table ) {
		global $wpdb;
		$table_name = 'postmeta' === $table ? $wpdb->postmeta : $wpdb->termmeta;

		foreach ( self::old_meta_prefixes() as $old ) {
			$old_like = $wpdb->esc_like( $old ) . '%';
			// UPDATE ... SET meta_key = CONCAT('_mrzme_', SUBSTRING(meta_key, N))
			// où N = position du 1er caractère qui suit le vieux préfixe.
			$offset = strlen( $old ) + 1; // MySQL SUBSTRING est 1-indexé.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table_name} SET meta_key = CONCAT( '_mrzme_', SUBSTRING( meta_key, %d ) ) WHERE meta_key LIKE %s",
					$offset,
					$old_like
				)
			);
		}
	}

	/**
	 * Change le post_type des cartes existantes vers le nouveau slug mrzme_map.
	 */
	private static function migrate_cpt_posts() {
		global $wpdb;
		foreach ( self::old_cpt_slugs() as $old_slug ) {
			$wpdb->update(
				$wpdb->posts,
				array( 'post_type' => 'mrzme_map' ),
				array( 'post_type' => $old_slug ),
				array( '%s' ),
				array( '%s' )
			);
		}
	}

	/**
	 * Renomme les transients de cache par carte.
	 * Les transients WP sont stockés en options avec préfixe `_transient_`
	 * (ou `_transient_timeout_`).
	 */
	private static function migrate_transients() {
		global $wpdb;
		foreach ( self::old_transient_prefixes() as $old ) {
			foreach ( array( '_transient_', '_transient_timeout_' ) as $wp_prefix ) {
				$full_old  = $wp_prefix . $old;
				$full_new  = $wp_prefix . 'mrzme_map_';
				$old_like  = $wpdb->esc_like( $full_old ) . '%';
				$offset    = strlen( $full_old ) + 1;
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->options} SET option_name = CONCAT( %s, SUBSTRING( option_name, %d ) ) WHERE option_name LIKE %s",
						$full_new,
						$offset,
						$old_like
					)
				);
			}
		}
	}
}
