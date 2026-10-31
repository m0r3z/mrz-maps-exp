<?php
/**
 * Classe principale : bootstrap des modules.
 */

namespace Mrzme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	/**
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var bool
	 */
	private $booted = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Déclare le dossier languages/ du plugin comme source de traductions.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'mrz-maps-exp', false, dirname( MRZME_BASENAME ) . '/languages' );
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		// Traductions livrées dans languages/ (fr_FR notamment). Un pack de
		// translate.wordpress.org, s'il existe, reste prioritaire.
		add_action( 'init', array( $this, 'load_textdomain' ), 1 );

		// Migration one-shot des data pré-1.1.0 (renommage des préfixes internes
		// de _mrz_maps_exp_ / _gmaps_aa_ vers _mrzme_). Idempotent, coût nul
		// après la première exécution.
		Migrator::maybe_migrate();

		if ( ! mrzme_has_acf() ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_acf' ) );
			return;
		}

		( new CPT() )->register();
		( new MapConfig() )->register();
		( new TaxonomyMarkers() )->register();
		( new Assets() )->register();
		( new Shortcode() )->register();
	}

	public function notice_missing_acf() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'mrz-maps-exp requires Advanced Custom Fields (Pro recommended) to work.', 'mrz-maps-exp' )
		);
	}
}
