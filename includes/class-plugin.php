<?php
/**
 * Classe principale : bootstrap des modules.
 */

namespace MrzMapsExp;

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

	public function boot() {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		// Pas de load_plugin_textdomain() : depuis WordPress 4.6, les traductions
		// hébergées sur translate.wordpress.org sont chargées automatiquement par
		// le cœur pour les plugins publiés sur le répertoire officiel.

		if ( ! mrz_maps_exp_has_acf() ) {
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
			esc_html__( 'mrz-maps-experience nécessite Advanced Custom Fields (Pro recommandé) pour fonctionner.', 'mrz-maps-experience' )
		);
	}
}
