=== gmaps-aa ===
Contributors: doubleA
Tags: google maps, acf, map, taxonomy, spiderfier, snazzy maps
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.4.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Cartographie Google Maps basée sur les champs ACF avec filtres par taxonomie et champ ACF, Spiderfier, Snazzy Maps et recherche par adresse.

== Description ==

Plugin WordPress générique et minimaliste pour afficher des custom posts sur une carte Google Maps. Les points sont tirés des champs ACF de type Google Map. Tout se configure via l'admin WordPress : type source, filtres par taxonomie et par champ ACF, pagination, templates d'infobulle et de liste, Snazzy Maps, marqueurs personnalisés par terme, layout responsive.

Fonctionnalités principales :

* Multi-cartes via un Custom Post Type
* Filtres par taxonomie et par champ ACF (mode dropdown/radio/checkbox, logique OU/ET)
* Pagination front et gestion du clic sur les items de liste (tooltip, aucun, lien vers le post)
* Recherche par adresse avec autocomplétion Google Places + filtrage par rayon
* Marqueurs personnalisables (défaut + par terme de taxonomie)
* Dépilement des marqueurs superposés (OverlappingMarkerSpiderfier)
* Tooltip 100 % personnalisée (OverlayView custom, pas la InfoWindow native)
* Snazzy Maps (styles JSON)
* Fit bounds automatique après filtrage
* Recentrage sur le post courant (single template)
* Responsive : empilement forcé filtres → carte → liste en dessous de 1024 px
* Shortcode avec filtre forcé optionnel

== Installation ==

1. Installer et activer Advanced Custom Fields (Pro recommandé).
2. Déclarer la clé Google Maps API dans le functions.php du thème via le filtre `gmaps_aa_api_key` ou la constante `GMAPS_AA_API_KEY`.
3. Activer gmaps-aa.
4. Créer une carte depuis le menu « gmaps-aa » et utiliser le shortcode `[gmaps_aa id="X"]`.

Voir le README.md du repo pour la documentation complète (hooks, placeholders, architecture, compatibilité Salient).

== Changelog ==

= 0.4.1 =
* Audit sécurité : remplacement des innerHTML risqués par createElement + img.src, retrait de 'style' de l'allowlist kses des templates
* Nettoyage du code mort (helpers inutilisés)
* Accessibilité clavier de la popup : role=dialog, focus trap, Échap pour fermer, focus automatique sur le bouton de fermeture à l'ouverture

= 0.4.0 =
* Libellé personnalisable par taxonomie (même présentation que les filtres ACF)
* Champs de recherche personnalisables (label et placeholder)
* Bouton « Filtres » pliable sur mobile (<768 px)
* Popup auto-repositionnée quand elle dépasse de la carte (panBy intelligent)
* Correction de l'ombrage sous les marqueurs (padding + margin pour éviter le clipping)
* Tooltip au-dessus du marqueur (fix offset + fermeture du gap avec la pointe)
* Option « Fermer la tooltip au clic sur la carte »
* Affichage conditionnel du sélecteur OU/ET (mode checkbox uniquement)
* Icône SVG du menu admin (data URI pour colorisation automatique par WP)
* Menu admin renommé en « GMaps » et déplacé en position 90
* Fichier .pot généré pour l'internationalisation

= 0.3.2 =
* Documentation README complète, changelog structuré

= 0.3.1 =
* Léger ombrage sous les marqueurs via drop-shadow CSS + padding box-sizing

= 0.3.0 =
* Suppression complète de l'option Marker clustering (friction supérieure au bénéfice)
* Spiderfier seul pour la gestion des marqueurs superposés

= 0.2.0 =
* Intégration OverlappingMarkerSpiderfier (bundlé localement)
* Option « Centrer sur le post courant » (page single)
* Option « Fermer la tooltip au clic sur la carte »
* Bump cache-buster pour assets

= 0.1.0 =
* Version initiale : CPT, métaboxes, template parser, shortcode, JS front, clustering, filtres taxo
