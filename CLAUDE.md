# CLAUDE.md — mrz-maps-experience

Conventions et repères techniques pour Claude Code quand il opère sur ce plugin.

## Vue d'ensemble

**MRZ Maps Experience** est un plugin WordPress générique de cartographie Google Maps basé sur les champs ACF `google_map`. Multi-sites, minimaliste, configurable via l'admin, pensé pour être déployé tel quel sur plusieurs sites clients et personnalisé via le CSS du thème.

- Repo : https://github.com/m0r3z/mrz-maps-experience
- Branche principale : `main`
- Version courante : voir `define('MRZ_MAPS_EXP_VERSION', '...')` dans `mrz-maps-experience.php`

## Conventions de nommage

| Élément | Valeur |
|---|---|
| Slug plugin / text-domain | `mrz-maps-experience` |
| Nom affiché (Plugin Name) | `MRZ Maps Experience` |
| Namespace PHP | `MrzMapsExp\` |
| Préfixe options / transients | `mrz_maps_exp_` |
| Préfixe post_meta / term_meta | `_mrz_maps_exp_` |
| CPT | `mrz_maps_exp_map` |
| Handles scripts / styles | `mrz-maps-exp-*` |
| Constantes | `MRZ_MAPS_EXP_VERSION`, `MRZ_MAPS_EXP_FILE`, `MRZ_MAPS_EXP_DIR`, `MRZ_MAPS_EXP_URL`, `MRZ_MAPS_EXP_BASENAME`, `MRZ_MAPS_EXP_CPT` |

## Architecture

```
mrz-maps-experience/
├── mrz-maps-experience.php                    # Bootstrap + autoloader PSR-4 maison
├── uninstall.php                   # Cleanup wildcard sur _mrz_maps_exp_%
├── includes/
│   ├── class-plugin.php            # Singleton, instancie les modules
│   ├── class-activator.php         # Vérifie ACF, enregistre le CPT
│   ├── class-deactivator.php
│   ├── class-cpt.php               # CPT + icône admin via mask-image CSS
│   ├── class-map-config.php        # Métaboxes + save + AJAX fetch_terms
│   ├── class-taxonomy-markers.php  # Champ icône sur la page d'édition d'un term
│   ├── class-template-parser.php   # Placeholders + conditionnels
│   ├── class-data-provider.php     # Agrège les données + cache transient 5 min
│   ├── class-assets.php            # Enregistre/enqueue scripts et styles
│   ├── class-shortcode.php         # [mrz_maps_exp] + recentrage post courant
│   └── helpers.php                 # mrz_maps_exp_has_acf(), mrz_maps_exp_get_api_key()
├── admin/
│   ├── css/admin.css
│   ├── js/admin.js                 # Mini-carte centre + media pickers + AJAX terms + repeater ACF + logic toggle
│   └── views/                      # Une vue par métabox
├── public/
│   ├── css/public.css              # Layout minimal + responsive + popup
│   ├── js/mrz-maps-experience.js              # Init map, markers, filtres, search, popup, OMS, pagination
│   └── views/map-wrapper.php       # HTML du shortcode
├── assets/
│   ├── default-marker.svg
│   ├── menu-icon.svg               # Icône admin (utilisée via mask-image CSS, pas data URI)
│   └── vendor/
│       ├── oms.js                  # OverlappingMarkerSpiderfier 1.0.1 — version non-minifiée (source pour wp.org review)
│       └── oms.min.js              # OverlappingMarkerSpiderfier 1.0.1 — version minifiée (chargée en prod, paire avec oms.js). Source : npm "overlapping-marker-spiderfier" 1.0.1 (fork fritz-c, miroir du upstream jawj). Les DEUX fichiers TRACKED dans git, voir .gitignore.
├── languages/
│   └── mrz-maps-experience.pot                # Généré via wp-cli i18n make-pot
└── .wordpress-org/                 # Assets pour le répertoire wordpress.org (SVN /assets/), HORS du zip distribué
    ├── banner-1544x500.png
    ├── icon-256x256.png
    └── screenshot-1.png … screenshot-N.png
```

## Décisions architecturales (à respecter)

- **Clé API Google Maps** : jamais d'UI admin. Lue dans `helpers.php::mrz_maps_exp_get_api_key()` selon la cascade : filtre `mrz_maps_exp_api_key` → constante `MRZ_MAPS_EXP_API_KEY` → `acf_get_setting('google_api_key')`. Si absente, le shortcode renvoie un commentaire HTML admin-only et n'enqueue rien.
- **Données front** : JSON inline injecté dans un `<script type="application/json">` à la fin du wrapper. Pas de REST endpoint.
- **Pas de MarkerClusterer** : retiré en v0.3.0 (friction > bénéfice). Seul OMS gère les markers superposés.
- **Tooltip / popup** : classe `Popup` custom étendant `google.maps.OverlayView` (cf. `public/js/mrz-maps-experience.js`). 100% sous notre contrôle, surchargeable en CSS sans casser à chaque update Google.
- **Cache transient** : par carte (`mrz_maps_exp_map_<id>`), 5 min, filtrable via `mrz_maps_exp_cache_ttl`. Invalidé sur `save_post`, `edited_term`, `deleted_term`.
- **Sécurité** : nonces + caps + sanitization typée partout. `wp_kses` allowlist restreinte pour les templates HTML utilisateur (pas de `style` autorisé). Audit fait en v0.4.1.

## Hooks exposés (filtres)

- `mrz_maps_exp_api_key` — string, retourne la clé Google Maps API
- `mrz_maps_exp_cache_ttl` — int, durée du cache transient en secondes (défaut 300)
- `mrz_maps_exp_template_value` — string, transforme la valeur d'un placeholder ACF avant échappement
- `mrz_maps_exp_template_kses_allowed` — array, allowlist HTML pour les templates utilisateur
- `mrz_maps_exp_skip_gmaps_enqueue` — bool, skip l'enqueue Google Maps JS (utile en coexistence avec d'autres loaders)
- `mrz_maps_exp_spiderfier_url` — string, URL du script OMS (défaut : `assets/vendor/oms.min.js`)

## Workflow release

1. Bump `define('MRZ_MAPS_EXP_VERSION', 'X.Y.Z')` + `Version: X.Y.Z` dans le header de `mrz-maps-experience.php`
2. Bump `Stable tag: X.Y.Z` + ajouter une entrée `= X.Y.Z =` dans le changelog de `readme.txt`
3. (Si chaînes traduisibles modifiées) regénérer le POT : `php /tmp/wp-cli.phar i18n make-pot . languages/mrz-maps-experience.pot --domain=mrz-maps-experience --package-name="MRZ Maps Experience"`
4. Commit avec identité inline (pas de config git globale sur ce poste) :
   ```
   git -c user.email="hello@morez.co" -c user.name="Morez" commit -q -m "..."
   ```
5. Tag annoté : `git -c user.email="..." -c user.name="..." tag -a vX.Y.Z -m "Release vX.Y.Z"`
6. Push : `git push origin main && git push origin vX.Y.Z`
7. Zip propre : `cd wp-content/plugins && rm -f /tmp/mrz-maps-exp-X.Y.Z.zip && zip -rq /tmp/mrz-maps-exp-X.Y.Z.zip mrz-maps-experience -x "mrz-maps-experience/.git/*" "mrz-maps-experience/.gitignore" "mrz-maps-experience/.wordpress-org/*" "mrz-maps-experience/CLAUDE.md" "mrz-maps-experience/.DS_Store" "mrz-maps-experience/**/.DS_Store"`
8. Release GitHub : `gh release create vX.Y.Z /tmp/mrz-maps-exp-X.Y.Z.zip --title "vX.Y.Z" --notes "..."`

## Workflow wordpress.org (SVN, à activer après acceptation)

Le plugin sera publié sur https://wordpress.org/plugins/mrz-maps-experience/ après validation par les Plugin Reviewers.

- **Source de vérité** : Git (GitHub `m0r3z/mrz-maps-experience`). SVN n'est qu'un miroir pour la distribution wp.org.
- **Soumission initiale** : zip généré comme ci-dessus, soumis via https://wordpress.org/plugins/developers/add/ (compte wp.org `mrzxp`, email `hello@morez.co`). Le compte GitHub `m0r3z` reste indépendant du compte wp.org `mrzxp` — ne pas confondre.
- **Après acceptation**, accès SVN reçu : `https://plugins.svn.wordpress.org/mrz-maps-experience/`. Trois dossiers :
  - `trunk/` — code en développement
  - `tags/X.Y.Z/` — versions taguées (la version servie aux utilisateurs est celle du `Stable tag` de readme.txt)
  - `assets/` — icon / banner / screenshots (contenu de `.wordpress-org/` à pousser ici)
- **Process release wp.org** (à scripter plus tard) :
  1. Faire la release Git/GitHub comme d'habitude (étapes 1-8 ci-dessus).
  2. Checkout SVN : `svn co https://plugins.svn.wordpress.org/mrz-maps-experience /tmp/mrz-maps-exp-svn` (la première fois).
  3. Synchroniser `trunk/` avec le contenu du zip (rsync en excluant `.git/`, `.wordpress-org/`, `CLAUDE.md`, `.gitignore` — c'est-à-dire exactement ce que la commande `zip` étape 7 exclut déjà).
  4. Synchroniser `assets/` avec le contenu de `.wordpress-org/`.
  5. `svn cp trunk tags/X.Y.Z`.
  6. `svn ci -m "Release X.Y.Z"`.

## Pièges connus

- **`.gitignore` `/vendor/` ancré** : doit rester ancré à la racine et `/assets/vendor/` ré-activé. Sinon `oms.min.js` sort du repo et casse les zipballs / `git clone` (bug v0.4.3, fix v0.4.4).
- **L'identité git n'est pas configurée globalement** sur ce poste, toujours utiliser `git -c user.email=... -c user.name=...` pour les commits.
- **wp-cli n'est pas installé en système**. Pour les commandes WP-CLI : `php /tmp/wp-cli.phar <command>` (le phar a été téléchargé manuellement).
- **gh CLI installé via brew** et authentifié sur le compte `m0r3z`. `gh auth status` pour vérifier.
- **Compatibilité Salient** : `salient-core` charge aussi Google Maps. Le plugin détecte (`wp_script_is('nectar-gmap', 'enqueued')`) et skip son propre loader si Salient l'a déjà fait.
- **Sélecteur CSS de l'icône admin** : utiliser `#adminmenu li.menu-top[id*="mrz_maps_exp_map"]` (tolérant), pas `#toplevel_page_...` qui ne match pas tous les ID que WP peut générer pour les CPT (bug v0.4.2, fix v0.4.3).
- **Anchor par défaut d'un Google Maps Marker** : bottom-center de l'image, pas centre. La popup doit être positionnée avec `pixelOffset.y = -(markerW + 8)` pour atterrir au-dessus.

## Fichiers à NE PAS modifier sans raison forte

- `assets/vendor/oms.min.js` (lib externe, pas notre code)
- `LICENSE` (GPLv3 du repo GitHub, hérité)

## Tests rapides à faire après une modif

- `php -l <fichier>.php` sur tout fichier PHP modifié
- Sur le site dev (`/Users/morez/Documents/_Local/dev/app/public/`), recharger une page contenant le shortcode après avoir resauvegardé la carte (pour invalider le transient)
