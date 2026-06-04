<?php
/**
 * Plugin Name:       MRZ Maps Experience
 * Plugin URI:        https://github.com/m0r3z/mrz-maps-exp
 * Description:       Cartographie Google Maps basée sur les CPT et champs ACF, avec filtres par taxonomie, Snazzy Maps et recherche par adresse.
 * Version:           1.0.1
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Morez.co
 * Author URI:        https://morez.co
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       mrz-maps-exp
 * Domain Path:       /languages
 *
 * MRZ Maps Experience — Copyright (C) 2026 Morez.co <hello@morez.co>
 * "MRZ Maps Experience" is a trademark of Morez.co. See LICENSE for full terms.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 as published by
 * the Free Software Foundation. See LICENSE for the full license text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRZ_MAPS_EXP_VERSION', '1.0.1' );
define( 'MRZ_MAPS_EXP_FILE', __FILE__ );
define( 'MRZ_MAPS_EXP_DIR', plugin_dir_path( __FILE__ ) );
define( 'MRZ_MAPS_EXP_URL', plugin_dir_url( __FILE__ ) );
define( 'MRZ_MAPS_EXP_BASENAME', plugin_basename( __FILE__ ) );
define( 'MRZ_MAPS_EXP_CPT', 'mrz_maps_exp_map' );

require_once MRZ_MAPS_EXP_DIR . 'includes/helpers.php';

spl_autoload_register(
	static function ( $class ) {
		if ( strpos( $class, 'MrzMapsExp\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( 'MrzMapsExp\\' ) );
		$relative = str_replace( '\\', '/', $relative );
		$parts    = explode( '/', $relative );
		$last     = array_pop( $parts );
		// Insère un tiret avant une majuscule qui suit une minuscule/chiffre,
		// ou avant une majuscule suivie d'une minuscule (fin d'acronyme).
		$last     = preg_replace( '/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '-$0', $last );
		$last     = strtolower( $last );
		$prefix   = empty( $parts ) ? '' : strtolower( implode( '/', $parts ) ) . '/';
		$path     = MRZ_MAPS_EXP_DIR . 'includes/' . $prefix . 'class-' . $last . '.php';

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( 'MrzMapsExp\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MrzMapsExp\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		MrzMapsExp\Plugin::instance()->boot();
	}
);
