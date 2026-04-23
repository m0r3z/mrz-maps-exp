=== gmaps-aa ===
Contributors: doubleA
Tags: google maps, acf, map, taxonomy, spiderfier, snazzy maps
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cartographie Google Maps basée sur les champs ACF avec filtres par taxonomie, Snazzy Maps et recherche par adresse.

== Description ==

Plugin générique et minimaliste pour afficher des custom posts sur une carte Google Maps à partir des champs ACF de type google_map. Configurable depuis l'admin WordPress (type de source, filtres, templates d'infobulle et de liste, Snazzy Maps, recherche par rayon).

== Installation ==

1. Installer et activer Advanced Custom Fields (Pro recommandé).
2. Déclarer la clé Google Maps API dans le functions.php du thème via le filtre `gmaps_aa_api_key` ou la constante `GMAPS_AA_API_KEY`.
3. Activer gmaps-aa.
4. Créer une carte depuis le menu « gmaps-aa » et utiliser le shortcode `[gmaps_aa id="X"]`.

== Changelog ==

= 0.1.0 =
* Version initiale (en développement).
