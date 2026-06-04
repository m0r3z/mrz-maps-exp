=== MRZ Maps Experience ===
Contributors: mrzxp
Tags: google maps, map, acf, taxonomy, custom post type
Requires at least: 6.3
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Cartographie Google Maps basée sur les champs ACF, avec filtres par taxonomie et champ ACF, recherche hybride et marqueurs personnalisables.

== Description ==

MRZ Maps Experience est un plugin générique et minimaliste pour afficher n'importe quel custom post type sur une carte Google Maps. Les coordonnées sont tirées d'un champ ACF de type Google Map déclaré sur les posts. Tout se configure depuis l'admin WordPress, sans toucher au code du thème : type source, filtres, pagination, templates HTML, marqueurs personnalisés, layout responsive.

= Fonctionnalités =

* Multi-cartes via un Custom Post Type dédié — chaque carte a sa propre configuration.
* Filtres par taxonomie et par champ ACF, en mode menu déroulant, boutons radio ou cases à cocher, avec logique OU / ET.
* Recherche hybride dans un seul champ : suggestions d'adresses Google Places **et** posts dont le titre correspond à la saisie.
* Synchronisation optionnelle des filtres avec l'URL — pour partager un lien pré-filtré.
* Marqueurs personnalisables : icône par défaut, ou icône par terme de taxonomie.
* Tooltip 100% personnalisée via OverlayView (pas la InfoWindow native), surchargeable en CSS.
* Templates HTML configurables pour la tooltip et la liste, avec placeholders ACF / taxos.
* Pagination front, fit bounds automatique après filtrage, recentrage sur le post courant en single template.
* Dépilement des marqueurs superposés (OverlappingMarkerSpiderfier).
* Snazzy Maps : collez un JSON pour styliser la carte.
* Layout responsive — filtres au-dessus, à gauche ou à droite ; champ de recherche détachable en pleine largeur ; pliage des filtres en mobile.
* Shortcode avec filtre forcé optionnel : `[mrz_maps_exp id="X" filter_taxonomy="genre" filter_term="42"]`.
* Internationalisation prête (text-domain `mrz-maps-experience`, fichier .pot fourni).

= Pré-requis =

* WordPress 6.3 ou supérieur, PHP 7.4 ou supérieur.
* Advanced Custom Fields (Pro recommandé) — pour le champ Google Map.
* Une clé API Google Maps avec les API « Maps JavaScript », « Places » et « Geocoding » activées. La clé doit être déclarée dans le `functions.php` du thème via le filtre `mrz_maps_exp_api_key` ou la constante `MRZ_MAPS_EXP_API_KEY` (le plugin ne propose volontairement pas d'UI admin pour la clé — c'est un secret qui n'a rien à faire en base de données).

= Confidentialité / appels externes =

MRZ Maps Experience charge la bibliothèque Google Maps JS dans le navigateur de l'utilisateur final, comme tout plugin de cartographie. Aucune donnée n'est envoyée vers Morez.co ou un service tiers ; aucune télémétrie n'est collectée. La clé Google Maps fournie par l'administrateur du site est exposée côté navigateur (c'est nécessaire pour appeler l'API JS Google) — il est recommandé de la restreindre par domaine HTTP referrer dans la console Google Cloud.

= Code source et contributions =

Le développement se fait publiquement sur GitHub : https://github.com/m0r3z/mrz-maps-experience
Issues, pull requests et forks bienvenus.

== Installation ==

1. Installer et activer Advanced Custom Fields (Pro recommandé).
2. Déclarer la clé Google Maps API dans le `functions.php` du thème :
   `add_filter( 'mrz_maps_exp_api_key', function () { return 'VOTRE_CLE_API'; } );`
3. Installer et activer MRZ Maps Experience depuis l'écran « Extensions » de WordPress, ou téléverser le zip.
4. Aller dans le menu **MRZ Maps** → **Ajouter une carte**, configurer la source, les filtres, les templates, etc.
5. Insérer le shortcode généré dans la page de votre choix : `[mrz_maps_exp id="X"]`.

== Frequently Asked Questions ==

= ACF est-il vraiment obligatoire ? =

Oui. MRZ Maps Experience s'appuie sur les champs ACF de type « Google Map » pour récupérer latitude / longitude / adresse de chaque post. Le plugin a été pensé pour s'intégrer avec ACF plutôt que dupliquer un système de coordonnées custom. La version gratuite d'ACF suffit ; ACF Pro n'est requis que si vous utilisez les fonctionnalités Pro (repeater, etc.).

= Pourquoi la clé Google Maps n'est-elle pas configurable depuis l'admin ? =

Choix volontaire. Une clé API est un secret qui n'a pas sa place en base de données : elle pourrait être exfiltrée via un export DB, un backup non chiffré, ou un compte admin compromis. La clé doit être déclarée dans le code du thème via le filtre `mrz_maps_exp_api_key` ou la constante `MRZ_MAPS_EXP_API_KEY`. C'est aussi la pratique recommandée pour la rotation de clé.

= Le plugin est-il compatible avec le thème Salient / d'autres plugins de cartographie ? =

Oui. MRZ Maps Experience détecte si Google Maps JS est déjà chargé par un autre plugin (par exemple Salient via `salient-core` / `nectar_gmap`) et n'enqueue pas sa propre version dans ce cas. Un filtre `mrz_maps_exp_skip_gmaps_enqueue` permet d'override le comportement si nécessaire.

= Comment ajouter du contenu dans la tooltip ou la liste ? =

Dans la métabox « Templates HTML » de la carte, des templates HTML libres sont configurables avec des placeholders : `{post_title}`, `{post_url}`, `{post_excerpt}`, `{post_thumbnail}`, `{post_thumbnail_url}`, `{%nom_du_champ_acf%}`, `{taxonomy:slug}`, `{taxonomy:slug:first}`. Des conditionnels `{#if %champ%}…{/if}` sont supportés pour n'afficher une zone que si le champ est rempli.

= Peut-on filtrer la carte via l'URL pour partager un lien pré-filtré ? =

Oui — option opt-in dans la métabox Filtres. Quand activée, les filtres actifs sont reflétés dans l'URL en temps réel (`?gm_<map_id>_tax_<slug>=12,34&gm_<map_id>_acf_<field>=valeur`), et un lien collé avec ces paramètres pré-active automatiquement les filtres correspondants.

= Le plugin gère-t-il un grand nombre de marqueurs ? =

Le rendu front gère sans souci quelques milliers de marqueurs (les données sont injectées en JSON inline et filtrées côté navigateur). Pour des volumes plus importants, envisagez de limiter l'affichage initial via un filtre forcé sur la taxonomie ou un autre filtre admin (« Nombre max de posts »).

== Screenshots ==

1. Métabox **Source des données** : choix du post type, du champ ACF de coordonnées, et limite optionnelle de chargement.
2. Métabox **Templates HTML** : templates personnalisables pour l'infobulle et les items de liste, avec placeholders ACF et taxonomies.
3. Métabox **Cosmétique** : marqueur par défaut, taille, dépilement Spiderfier, et marqueurs par terme de taxonomie.
4. Métabox **Filtres** : configuration des filtres par taxonomie et par champ ACF (mode dropdown / radio / checkbox, logique OU / ET).

== External services ==

This plugin relies on the Google Maps JavaScript API to display the interactive map, geocode addresses entered in the search field, and provide place autocomplete suggestions. The Google Maps API key is supplied by the site administrator (via the `mrz_maps_exp_api_key` filter or `MRZ_MAPS_EXP_API_KEY` constant in the theme); the plugin itself does not bundle any key.

= What is sent and when =

* When a page containing the `[mrz_maps_exp]` shortcode is loaded, the visitor's browser loads the Google Maps JavaScript library from `https://maps.googleapis.com/maps/api/js`, including the `places` library. The Google Maps API key is appended as a query parameter.
* When the visitor types in the search field, each keystroke (debounced) sends the current query string to Google's Places Autocomplete service to retrieve address suggestions. If the visitor picks a suggestion, Google's Places Details service is then called to obtain the coordinates for that place.
* The visitor's IP address is transmitted to Google as part of these HTTP requests, as it is for any third-party request initiated by their browser.
* No data is sent to Google when the plugin is only installed or activated in the admin — only when a public page containing the shortcode is loaded.

The plugin does not send any data to Morez.co or to any other third-party service. No telemetry is collected.

= Provider and legal links =

* Service: Google Maps Platform (Google LLC).
* Terms of Service: https://cloud.google.com/maps-platform/terms
* Privacy Policy: https://policies.google.com/privacy

It is the responsibility of site administrators to obtain a valid Google Maps API key, accept Google's Terms of Service for the project that key belongs to, and disclose the use of Google Maps in their own site's privacy policy where required.

== Changelog ==

= 1.0.4 =
* Slug alignment for wordpress.org submission. The Plugin Name is `MRZ Maps Experience` and the plugin slug is now `mrz-maps-experience` — they used to be `mrz-maps-exp` which mismatched the name. The plugin folder, main file (`mrz-maps-experience.php`), text domain (`mrz-maps-experience`), POT file and GitHub repo (`m0r3z/mrz-maps-experience`) are all aligned. **Internal PHP prefixes are unchanged** (`MRZ_MAPS_EXP_*` constants, `mrz_maps_exp_*` functions and hooks, `_mrz_maps_exp_*` meta keys, `MrzMapsExp\` namespace, `mrz_maps_exp_map` CPT slug, `[mrz_maps_exp]` shortcode) — they are internal and do not affect the WordPress directory.
* `Tested up to: 7.0` (was `6.9`).

= 1.0.3 =
* Updated the readme `Contributors:` line to point at the actual wordpress.org account that owns the plugin (`mrzxp`). The GitHub repository remains `m0r3z/mrz-maps-experience` — the two accounts are intentionally distinct.

= 1.0.2 =
* Pre-submission polish for wordpress.org. No functional change.
* `uninstall.php`: rewrote the three `$wpdb->query()` calls using `$wpdb->prepare()` and `$wpdb->esc_like()` instead of hand-escaped LIKE patterns, matching the strictest Plugin Check expectations.
* `metabox-templates.php`: wrapped the inline `<code>` placeholders documentation in `wp_kses()` with an explicit allowlist instead of `echo`ing literal HTML.
* `map-wrapper.php`: minor cleanup of a `<?php echo ' / '; ?>` separator in the pagination markup (replaced with a literal character).

= 1.0.1 =
* Fix admin label : le menu sidebar affichait encore « GMaps » au lieu de « MRZ Maps ». Correction du `menu_name` du CPT.
* Fix uninstall : le script `uninstall.php` ciblait encore les anciens préfixes `_gmaps_aa_*` au lieu de `_mrz_maps_exp_*` pour le nettoyage des post_meta, term_meta et transients (les underscores échappés dans les LIKE SQL ont été manqués par le renommage en bulk de la 1.0.0).
* Fix doc : mentions résiduelles du menu « GMaps » dans `readme.txt` et `README.md` corrigées en « MRZ Maps ».

= 1.0.0 =
* First public release under the Morez.co identity, as "MRZ Maps Experience". Full rebrand from the internal "GMaps-AA" codebase: new plugin slug (`mrz-maps-experience`), new text domain, new PHP namespace (`MrzMapsExp`), new constant / function / meta / CPT prefixes (`MRZ_MAPS_EXP_*`, `mrz_maps_exp_*`, `_mrz_maps_exp_*`, `mrz_maps_exp_map`). Existing sites coming from the internal "GMaps-AA" codebase must reconfigure their maps after upgrade — there is no automatic data migration.
* Compliance with the wordpress.org plugin guidelines:
  * The admin menu icon CSS is no longer printed inline via `admin_head`; it is now enqueued via `wp_register_style` + `wp_add_inline_style` on `admin_enqueue_scripts`.
  * Removed the now-unnecessary `load_plugin_textdomain()` call (since WordPress 4.6, translations of plugins hosted on wordpress.org are loaded automatically).
  * Added a dedicated `== External services ==` section in the readme documenting the use of the Google Maps JavaScript API, what data is sent, and links to Google's Terms of Service and Privacy Policy.

= 0.5.4 =
* Conformité guidelines wordpress.org : ajout du source non-minifié de la librairie OverlappingMarkerSpiderfier (`assets/vendor/oms.js`) à côté du fichier minifié, comme l'exigent les guidelines pour toute lib externe minifiée. Les deux fichiers proviennent désormais du package npm officiel `overlapping-marker-spiderfier@1.0.1` (paire cohérente source/minifié), au lieu de l'ancien minifié solo issu du repo upstream `jawj/OverlappingMarkerSpiderfier`.
* Aucun changement de comportement : l'API utilisée (`new OverlappingMarkerSpiderfier(map, options)`, `addMarker`, `addListener`) est identique entre les deux versions.

= 0.5.3 =
* Préparation pour publication sur le répertoire WordPress.org : sections FAQ, Screenshots et Upgrade Notice ajoutées au readme. Banner, icon et captures d'écran organisés dans `.wordpress-org/` (hors zip distribué).
* Correctif libellé : « fiches » → « posts » dans la description de l'option de matching local du champ de recherche, pour cohérence avec le vocabulaire WordPress.
* Métabox Filtres : alignement vertical de la colonne « Taxonomie » avec les colonnes Libellé / Type / Logique pour un visuel cohérent avec le bloc « Filtres par champ ACF ».

= 0.5.2 =
* Mise au clair de la propriété intellectuelle : ajout d'un en-tête de copyright Morez.co explicite dans `LICENSE`, `mrz-maps-experience.php` et `readme.txt`. Le nom et le logo « MRZ Maps Experience » sont déclarés comme marques de Morez.co.
* Harmonisation de la licence sur GPLv3 ou ultérieure dans tous les en-têtes (le header PHP indiquait précédemment GPLv2). Le code reste libre et redistribuable sous GPL ; la marque, en revanche, n'est pas couverte par la GPL et reste exclusive à Morez.co.
* Correction de l'URL du repo dans le header plugin (m0r3z/mrz-maps-experience) et ajout d'un Author URI.

= 0.5.1 =
* Synchronisation des filtres avec l'URL (option opt-in dans la métabox Filtres) : à l'activation, l'URL reflète automatiquement les filtres de taxonomie et de champs ACF actifs, et les filtres présents dans l'URL au chargement sont pré-cochés. Format : `?gm_<map_id>_tax_<slug>=12,34&gm_<map_id>_acf_<field>=valeur`. Permet de partager des liens pré-filtrés. Le préfixe par ID de carte évite les conflits si plusieurs cartes coexistent sur la même page.

= 0.5.0 =
* Recherche hybride : la dropdown du champ de recherche affiche désormais les posts dont le titre correspond à la saisie, en plus des suggestions d'adresses Google. Au clic sur un post, la carte se centre dessus, applique le zoom de recherche et ouvre la tooltip.
* Nouvelle option « Suggérer aussi les posts » dans la métabox Filtres (activée par défaut), pour activer/désactiver le matching local.
* Nouvelle option « Position du champ » : permet de sortir le champ de recherche du bloc des filtres pour le placer en pleine largeur au-dessus, utile lorsque les filtres sont latéraux.
* Remplacement du widget google.maps.places.Autocomplete par AutocompleteService + une dropdown custom 100 % stylable. Navigation clavier complète (flèches / Entrée / Échap / Tab) avec attributs ARIA combobox.

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

== Upgrade Notice ==

= 1.0.4 =
Plugin slug renamed from `mrz-maps-exp` to `mrz-maps-experience` to match the Plugin Name. The shortcode, the CPT slug, the meta keys and all internal prefixes stay the same — your existing data is not touched.

= 1.0.3 =
Updated readme `Contributors:` to the actual wp.org account (`mrzxp`). Cosmetic only.

= 1.0.2 =
Pre-submission polish for wordpress.org. No functional change, safe upgrade.

= 1.0.1 =
Petits correctifs post-rebrand : libellé du menu admin (« GMaps » → « MRZ Maps »), nettoyage uninstall qui ne ciblait pas les bons préfixes. Mise à jour recommandée.

= 1.0.0 =
First public release as "MRZ Maps Experience" (Morez.co). Full rebrand from the internal "GMaps-AA" codebase — namespaces, prefixes, slug and CPT all changed. Sites previously running the internal "GMaps-AA" build must reconfigure their maps after upgrade.

= 0.5.4 =
Ajout du source non-minifié d'OverlappingMarkerSpiderfier à côté du minifié (conformité wordpress.org). Aucun changement fonctionnel.

= 0.5.3 =
Préparation pour le répertoire WordPress.org. Petits ajustements UI sans rupture. Mise à jour sans risque.

= 0.5.2 =
Clarification du copyright Morez.co et alignement de la licence sur GPLv3+. Aucun changement fonctionnel.

= 0.5.1 =
Nouvelle option pour synchroniser les filtres avec l'URL (désactivée par défaut). Compatible avec les versions précédentes.

= 0.5.0 =
Recherche hybride dans le champ de recherche : suggère les posts dont le titre correspond, en plus des adresses Google. Activée par défaut.
