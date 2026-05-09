=== gmaps-aa ===
Contributors: doubleA
Tags: google maps, acf, map, taxonomy, spiderfier, snazzy maps
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.5.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Cartographie Google Maps basée sur les champs ACF avec filtres par taxonomie et champ ACF, Spiderfier, Snazzy Maps et recherche par adresse.

Copyright (C) 2024-2026 Doublea.io. "GMaps-AA" est une marque de Doublea.io.

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

= 0.5.2 =
* Mise au clair de la propriété intellectuelle : ajout d'un en-tête de copyright Doublea.io explicite dans `LICENSE`, `gmaps-aa.php` et `readme.txt`. Le nom et le logo « GMaps-AA » sont déclarés comme marques de Doublea.io.
* Harmonisation de la licence sur GPLv3 ou ultérieure dans tous les en-têtes (le header PHP indiquait précédemment GPLv2). Le code reste libre et redistribuable sous GPL ; la marque, en revanche, n'est pas couverte par la GPL et reste exclusive à Doublea.io.
* Correction de l'URL du repo dans le header plugin (d0ubl34/gmaps-aa) et ajout d'un Author URI.

= 0.5.1 =
* Synchronisation des filtres avec l'URL (option opt-in dans la métabox Filtres) : à l'activation, l'URL reflète automatiquement les filtres de taxonomie et de champs ACF actifs, et les filtres présents dans l'URL au chargement sont pré-cochés. Format : `?gm_<map_id>_tax_<slug>=12,34&gm_<map_id>_acf_<field>=valeur`. Permet de partager des liens pré-filtrés. Le préfixe par ID de carte évite les conflits si plusieurs cartes coexistent sur la même page.

= 0.5.0 =
* Recherche hybride : la dropdown du champ de recherche affiche désormais les fiches dont le titre correspond à la saisie, en plus des suggestions d'adresses Google. Au clic sur une fiche, la carte se centre dessus, applique le zoom de recherche et ouvre la tooltip.
* Nouvelle option « Suggérer aussi les fiches » dans la métabox Filtres (activée par défaut), pour activer/désactiver le matching local.
* Nouvelle option « Position du champ » : permet de sortir le champ de recherche du bloc des filtres pour le placer en pleine largeur au-dessus, utile lorsque les filtres sont latéraux.
* Remplacement du widget google.maps.places.Autocomplete par AutocompleteService + une dropdown custom 100 % stylable. Navigation clavier complète (↓/↑/Entrée/Échap/Tab) avec attributs ARIA combobox.

= 0.4.4 =
* Inclusion de la librairie OverlappingMarkerSpiderfier (assets/vendor/oms.min.js) dans le repo : précédemment ignorée par .gitignore, elle manquait dans tous les zipballs auto-générés GitHub et tous les git clone, ce qui faisait que la carte et la liste ne s'affichaient pas (seuls les filtres apparaissaient).

= 0.4.3 =
* Correction du sélecteur CSS de l'icône admin : dans la 0.4.2 l'ID cible ne correspondait pas à l'élément <li> réellement généré par WordPress, l'icône ne s'affichait donc plus. Utilisation d'un sélecteur tolérant (attribute-contains sur .menu-top).

= 0.4.2 =
* Correction du flash noir sur l'icône du menu admin au chargement de page : utilisation de CSS mask-image au lieu de data URI SVG. La couleur de l'icône suit désormais instantanément l'état du menu (gris au repos, blanc au hover / actif).

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
