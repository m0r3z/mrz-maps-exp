<?php
/**
 * Plugin Name:       MRZ Maps Exp
 * Plugin URI:        https://github.com/m0r3z/mrz-maps-exp
 * Description:       Cartographie Google Maps basée sur les CPT et champs ACF, avec filtres par taxonomie, Snazzy Maps et recherche par adresse.
 * Version:           1.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Morez.co
 * Author URI:        https://morez.co
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       mrz-maps-exp
 * Domain Path:       /languages
 *
 * MRZ Maps Exp — Copyright (C) 2026 Morez.co <hello@morez.co>
 * "MRZ Maps Exp" is a trademark of Morez.co. See LICENSE for full terms.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 as published by
 * the Free Software Foundation. See LICENSE for the full license text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRZME_VERSION', '1.1.0' );
define( 'MRZME_FILE', __FILE__ );
define( 'MRZME_DIR', plugin_dir_path( __FILE__ ) );
define( 'MRZME_URL', plugin_dir_url( __FILE__ ) );
define( 'MRZME_BASENAME', plugin_basename( __FILE__ ) );
define( 'MRZME_CPT', 'mrzme_map' );

require_once MRZME_DIR . 'includes/helpers.php';

spl_autoload_register(
	static function ( $class ) {
		if ( strpos( $class, 'Mrzme\\' ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( 'Mrzme\\' ) );
		$relative = str_replace( '\\', '/', $relative );
		$parts    = explode( '/', $relative );
		$last     = array_pop( $parts );
		// Insère un tiret avant une majuscule qui suit une minuscule/chiffre,
		// ou avant une majuscule suivie d'une minuscule (fin d'acronyme).
		$last     = preg_replace( '/(?<=[a-z0-9])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z])/', '-$0', $last );
		$last     = strtolower( $last );
		$prefix   = empty( $parts ) ? '' : strtolower( implode( '/', $parts ) ) . '/';
		$path     = MRZME_DIR . 'includes/' . $prefix . 'class-' . $last . '.php';

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( 'Mrzme\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Mrzme\\Deactivator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		Mrzme\Plugin::instance()->boot();
	}
);
