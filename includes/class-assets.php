<?php
/**
 * Enregistrement centralisé des assets front.
 */

namespace MrzMapsExp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {

	const HANDLE_GMAPS      = 'mrz-maps-exp-gmaps';
	const HANDLE_SPIDERFIER = 'mrz-maps-exp-spiderfier';
	const HANDLE_SCRIPT     = 'mrz-maps-experience';
	const HANDLE_STYLE      = 'mrz-maps-experience';

	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		// Invalide cache carte en cas de changement.
		add_action( 'save_post', array( $this, 'invalidate_on_post_save' ), 10, 2 );
		add_action( 'edited_term', array( $this, 'invalidate_all' ) );
		add_action( 'deleted_term', array( $this, 'invalidate_all' ) );
	}

	public function register_assets() {
		wp_register_style(
			self::HANDLE_STYLE,
			MRZ_MAPS_EXP_URL . 'public/css/public.css',
			array(),
			MRZ_MAPS_EXP_VERSION
		);

		wp_register_script(
			self::HANDLE_SPIDERFIER,
			apply_filters(
				'mrz_maps_exp_spiderfier_url',
				MRZ_MAPS_EXP_URL . 'assets/vendor/oms.min.js'
			),
			array(),
			'1.0.1',
			true
		);

		wp_register_script(
			self::HANDLE_SCRIPT,
			MRZ_MAPS_EXP_URL . 'public/js/mrz-maps-experience.js',
			array( self::HANDLE_SPIDERFIER ),
			MRZ_MAPS_EXP_VERSION,
			true
		);
	}

	/**
	 * Garantit que les scripts sont enregistrés (au cas où le shortcode
	 * s'exécute avant wp_enqueue_scripts, ex: widgets, rendu AJAX).
	 */
	public function ensure_registered() {
		if ( ! wp_script_is( self::HANDLE_SCRIPT, 'registered' ) ) {
			$this->register_assets();
		}
	}

	/**
	 * Enqueue pour un shortcode donné. Retourne false si la clé API est absente.
	 */
	public function enqueue_for_shortcode() {
		$api_key = mrz_maps_exp_get_api_key();
		if ( '' === $api_key ) {
			return false;
		}

		$this->ensure_registered();

		// Détection des loaders Google Maps déjà en place (ex: Salient nectar_gmap).
		$already_loaded = wp_script_is( self::HANDLE_GMAPS, 'enqueued' )
			|| wp_script_is( 'nectar-gmap', 'enqueued' )
			|| wp_script_is( 'nectar-gmaps', 'enqueued' );

		$skip_gmaps = apply_filters( 'mrz_maps_exp_skip_gmaps_enqueue', $already_loaded );
		if ( ! $skip_gmaps ) {
			wp_enqueue_script(
				self::HANDLE_GMAPS,
				add_query_arg(
					array(
						'key'       => rawurlencode( $api_key ),
						'libraries' => 'places',
						'callback'  => 'mrzMapsExpBoot',
						'loading'   => 'async',
						'v'         => 'weekly',
					),
					'https://maps.googleapis.com/maps/api/js'
				),
				array(),
				null,
				array(
					'strategy'  => 'async',
					'in_footer' => true,
				)
			);
		}

		wp_enqueue_script( self::HANDLE_SPIDERFIER );
		wp_enqueue_script( self::HANDLE_SCRIPT );
		wp_enqueue_style( self::HANDLE_STYLE );

		return true;
	}

	public function invalidate_on_post_save( $post_id, $post ) {
		if ( MRZ_MAPS_EXP_CPT === $post->post_type ) {
			DataProvider::invalidate( $post_id );
			return;
		}
		// Un post source a changé : invalide toutes les cartes.
		$this->invalidate_all();
	}

	public function invalidate_all() {
		$maps = get_posts(
			array(
				'post_type'      => MRZ_MAPS_EXP_CPT,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $maps as $id ) {
			DataProvider::invalidate( (int) $id );
		}
	}
}
