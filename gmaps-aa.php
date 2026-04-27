<?php
/**
 * Plugin Name:       GMaps-AA
 * Plugin URI:        https://github.com/doubleA/gmaps-aa
 * Description:       Cartographie Google Maps basée sur les CPT et champs ACF, avec filtres par taxonomie, Snazzy Maps et recherche par adresse.
 * Version:           0.4.4
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Doublea.io
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gmaps-aa
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GMAPS_AA_VERSION', '0.4.4' );
define( 'GMAPS_AA_FILE', __FILE__ );
define( 'GMAPS_AA_DIR', plugin_dir_path( __FILE__ ) );
define( 'GMAPS_AA_URL', plugin_dir_url( __FILE__ ) );
define( 'GMAPS_AA_BASENAME', plugin_basename( __FILE__ ) );
define( 'GMAPS_AA_CPT', 'gmaps_aa_map' );

require_once GMAPS_AA_DIR . 'includes/helpers.php';

spl_autoload_register(
	static function ( $class ) {
		if ( strpos( $class, 'GmapsAA\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( 'GmapsAA\\' ) );
		$relative = str_replace( '\\', '/', $relative );
		$parts    = explode( '/', $relative );
		$last     = array_pop( $parts );
		// Insère un tiret avant une majuscule qui suit une minuscule/chiffre,
		// ou avant une majuscule suivie d'une minuscule (fin d'acronyme).
		$last     = preg_replace( '/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '-$0', $last );
		$last     = strtolower( $last );
		$prefix   = empty( $parts ) ? '' : strtolower( implode( '/', $parts ) ) . '/';
		$path     = GMAPS_AA_DIR . 'includes/' . $prefix . 'class-' . $last . '.php';

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( 'GmapsAA\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GmapsAA\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		GmapsAA\Plugin::instance()->boot();
	}
);
