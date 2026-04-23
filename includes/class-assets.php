<?php
/**
 * Enregistrement centralisé des assets front.
 */

namespace GmapsAA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {

	const HANDLE_GMAPS      = 'gmaps-aa-gmaps';
	const HANDLE_SPIDERFIER = 'gmaps-aa-spiderfier';
	const HANDLE_SCRIPT     = 'gmaps-aa';
	const HANDLE_STYLE      = 'gmaps-aa';

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
			GMAPS_AA_URL . 'public/css/public.css',
			array(),
			GMAPS_AA_VERSION
		);

		wp_register_script(
			self::HANDLE_SPIDERFIER,
			apply_filters(
				'gmaps_aa_spiderfier_url',
				GMAPS_AA_URL . 'assets/vendor/oms.min.js'
			),
			array(),
			'1.0.3',
			true
		);

		wp_register_script(
			self::HANDLE_SCRIPT,
			GMAPS_AA_URL . 'public/js/gmaps-aa.js',
			array( self::HANDLE_SPIDERFIER ),
			GMAPS_AA_VERSION,
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
		$api_key = gmaps_aa_get_api_key();
		if ( '' === $api_key ) {
			return false;
		}

		$this->ensure_registered();

		// Détection des loaders Google Maps déjà en place (ex: Salient nectar_gmap).
		$already_loaded = wp_script_is( self::HANDLE_GMAPS, 'enqueued' )
			|| wp_script_is( 'nectar-gmap', 'enqueued' )
			|| wp_script_is( 'nectar-gmaps', 'enqueued' );

		$skip_gmaps = apply_filters( 'gmaps_aa_skip_gmaps_enqueue', $already_loaded );
		if ( ! $skip_gmaps ) {
			wp_enqueue_script(
				self::HANDLE_GMAPS,
				add_query_arg(
					array(
						'key'       => rawurlencode( $api_key ),
						'libraries' => 'places',
						'callback'  => 'gmapsAABoot',
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
		if ( GMAPS_AA_CPT === $post->post_type ) {
			DataProvider::invalidate( $post_id );
			return;
		}
		// Un post source a changé : invalide toutes les cartes.
		$this->invalidate_all();
	}

	public function invalidate_all() {
		$maps = get_posts(
			array(
				'post_type'      => GMAPS_AA_CPT,
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
