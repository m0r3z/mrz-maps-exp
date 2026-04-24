# GMaps-AA

Plugin WordPress générique de cartographie Google Maps basé sur les champs ACF `google_map`.

Un plugin léger, configurable entièrement via l'admin WordPress, et pensé pour être déployé sur plusieurs sites sans personnalisation lourde du code. Le style front est intentionnellement minimal et se surcharge dans le CSS du thème par projet.

## Sommaire

- [Prérequis](#prérequis)
- [Fonctionnalités](#fonctionnalités)
- [Clé Google Maps API](#clé-google-maps-api)
- [Utilisation](#utilisation)
- [Configuration par métabox](#configuration-par-métabox)
- [Templates HTML](#templates-html)
- [Shortcode](#shortcode)
- [Hooks et filtres](#hooks-et-filtres)
- [Librairies embarquées](#librairies-embarquées)
- [Compatibilité](#compatibilité)
- [Architecture](#architecture)
- [Licence](#licence)

## Prérequis

- WordPress **6.3** ou supérieur (utilisation de `strategy: async` pour les scripts)
- PHP **7.4** ou supérieur
- **Advanced Custom Fields** (Pro recommandé) — vérifié à l'activation
- Clé **Google Maps JavaScript API** avec les APIs **Maps JavaScript** et **Places** activées

## Fonctionnalités

### Cartographie

- Multi-cartes indépendantes via un Custom Post Type
- Source configurable : n'importe quel post type doté d'un champ ACF de type `google_map`
- Marqueur par défaut personnalisable (image SVG/PNG) et largeur configurable
- Icône de marqueur personnalisable par terme d'une taxonomie dédiée
- Léger ombrage sous les pictos (drop-shadow)
- **OverlappingMarkerSpiderfier** intégré : les marqueurs superposés au même point se dépilent en éventail au clic
- **Tooltip 100 % personnalisée** (OverlayView custom) avec bords arrondis, ombre, bouton de fermeture
- Fermeture automatique de la tooltip au clic sur la carte (optionnel)
- Skin **Snazzy Maps** (JSON à coller depuis [snazzymaps.com](https://snazzymaps.com/))

### Contrôles de zoom

- Zoom initial, zoom minimum, zoom maximum configurables
- Zoom appliqué après recherche d'adresse (zoom rapproché sur le résultat)
- **Fit bounds automatique** après filtrage (recentre sur les marqueurs visibles)
- Option **Ctrl + molette** sur desktop (zoom coopératif sans message natif Google)
- Gestion tactile native sur mobile/tablette (un doigt pour déplacer, pinch pour zoomer)

### Filtres

- Filtres par **taxonomie** (mode *dropdown* / *radio* / *checkbox*)
- Filtres par **champ ACF** (repeater dans l'admin — valeurs auto-détectées pour select/radio/checkbox/true_false, ou collecte dynamique pour les autres types)
- Logique **OU / ET** par filtre (pour le mode cases à cocher)
- Nombre de résultats affiché à côté de chaque option (activable/désactivable globalement)
- **Recherche par adresse** avec autocomplétion Google Places + filtrage par rayon (km)
- Bouton **Réinitialiser** personnalisable (texte + visibilité)

### Liste des résultats

- Affichage en **liste** ou en **grille**
- Layout **au-dessus / à côté gauche / à côté droit / en dessous / masqué** (filtres et liste indépendants)
- **Pagination** configurable (N items par page, navigation prev/next)
- Clic sur un item : 3 comportements au choix (afficher la tooltip sur la carte / ne rien faire / ouvrir la page du post)
- Responsive : passage automatique en empilement vertical (filtres → carte → liste) en dessous de 1024 px

### Shortcode et templating

- Shortcode simple : `[gmaps_aa id="123"]`
- Filtre forcé par attribut : `filter_taxonomy="…" filter_term="…"` + option `hide_forced_filter="true"` pour masquer le filtre UI
- **Recentrage automatique sur le post courant** sur une page single du post type source (option admin)
- Templates HTML totalement personnalisables pour l'infobulle et pour chaque item de liste (placeholders + conditionnels, échappement automatique par type)

### Sécurité & performance

- Sanitization typée sur chaque champ admin (`sanitize_text_field`, `absint`, `wp_kses`, validation JSON, clamps numériques, whitelists)
- Allowlist HTML filtrable pour les templates utilisateur
- Nonces + `current_user_can` sur chaque formulaire (save post, save term, AJAX)
- Cache transient par carte (5 minutes, filtrable), invalidé à la sauvegarde d'un post source, d'un terme, ou de la carte
- JSON inline via `wp_json_encode` (échappement automatique de `<`, `>`, `&`, `'`, `"`)
- **Aucune clé API stockée en base**

## Clé Google Maps API

Le plugin n'expose **aucun champ admin pour la clé** — elle doit être fournie par le thème. Trois méthodes, essayées dans cet ordre :

### 1. Filtre (recommandé)

```php
// functions.php du thème enfant
add_filter( 'gmaps_aa_api_key', function () {
    return 'VOTRE_CLE_API';
} );
```

### 2. Constante

```php
// wp-config.php ou functions.php
define( 'GMAPS_AA_API_KEY', 'VOTRE_CLE_API' );
```

### 3. Réglage ACF

Si vous avez déjà configuré ACF pour les champs `google_map` (`acf_get_setting('google_api_key')`), GMaps-AA réutilise automatiquement cette valeur.

### Restrictions recommandées

Dans la Google Cloud Console, restreignez la clé par **HTTP Referrer** à votre domaine (`*.votre-domaine.com/*`) et activez au minimum les APIs **Maps JavaScript** et **Places**.

Une notice s'affiche dans l'admin tant que la clé n'est pas détectée.

## Utilisation

1. Créer une carte depuis le menu **GMaps** de l'admin.
2. Remplir les métaboxes (source, filtres, affichage, cosmétique, templates, style).
3. Copier le shortcode affiché dans la sidebar (`[gmaps_aa id="XX"]`) et le coller sur la page voulue.

## Configuration par métabox

### Source des données

- **Post type source** : n'importe quel post type public du site
- **Champ ACF des coordonnées** : nom du champ ACF `google_map` (défaut `location`)
- **Nombre max de posts** : limite serveur (0 = illimité)
- **Posts par page (liste)** : pagination front (0 = pas de pagination)

### Filtres

- **Recherche par adresse** : activation + rayon par défaut (km)
- **Options globales** : afficher ou non le compteur de résultats
- **Filtres par taxonomie** : mode d'affichage + logique (OU/ET)
- **Filtres par champ ACF** (repeater) : nom du champ, libellé, mode, logique

### Affichage

- Hauteur, zoom initial, zoom min/max, zoom après recherche
- Centre de la carte (mini-carte cliquable)
- Zoom desktop à la molette (Ctrl/Cmd requis)
- Fit bounds après filtrage
- Centrer sur le post courant (page single du post type source)
- Fermer la tooltip au clic sur la carte
- Bouton de réinitialisation (visibilité + texte)
- Layout filtres / liste (au-dessus / à côté gauche / à côté droit / en dessous / masqué)
- Format de la liste (liste / grille)
- Clic sur un item de la liste : tooltip / rien / lien vers le post

### Cosmétique

- Marqueur par défaut (image) + largeur (px)
- Taxonomie des marqueurs + liste des termes (upload d'icône par term via AJAX)
- **Effet Spiderfier** (dépilement des marqueurs superposés)

### Templates HTML

- Template d'infobulle (tooltip)
- Template d'item de liste

### Style de la carte

- JSON Snazzy Maps (validé à la sauvegarde)

## Templates HTML

### Placeholders disponibles

| Placeholder | Sortie | Échappement |
|---|---|---|
| `{post_title}` | Titre du post | `esc_html` |
| `{post_url}` | Permalien | `esc_url` |
| `{post_excerpt}` | Extrait | `esc_html` |
| `{post_thumbnail}` | Balise `<img>` de la thumbnail | `wp_kses_post` |
| `{post_thumbnail_url}` | URL de la thumbnail | `esc_url` |
| `{post_id}` | ID du post | — |
| `{%nom_champ_acf%}` | Valeur d'un champ ACF | adapté au type ACF |
| `{taxonomy:slug}` | Termes séparés par virgule | `esc_html` |
| `{taxonomy:slug:first}` | Premier terme uniquement | `esc_html` |

Les champs ACF de type `wysiwyg` sont rendus via `wp_kses_post` ; les types `url`/`link`/`image`/`file` via `esc_url` ; les autres via `esc_html`. Les champs à choix (select, radio, checkbox, true_false) voient leurs valeurs remplacées par les labels configurés dans ACF.

### Conditionnels

```text
{#if %annu_fonction%}<div>{%annu_fonction%}</div>{/if}
```

Le bloc est rendu uniquement si la valeur est « truthy » (non vide, non null, non `[]`, non `'0'`). Supporte l'imbrication jusqu'à 5 niveaux.

### Exemple complet

```html
<div class="gmap-tool">
  <div class="wpgmp-liste-titre"><h6>{post_title}</h6></div>
  {#if %annu_fonction%}<div class="wpgmp-liste-fonction">{%annu_fonction%}</div>{/if}
  <div class="wpgmp-liste-adresse">{%annu_adresse%}</div>
  <div class="wpgmp-liste-ville">{%annu_code_postal%} {%annu_ville%}</div>
  {#if %annu_email%}<div class="wpgmp-liste-email">{%annu_email%}</div>{/if}
  {#if %annu_telephone%}<div class="wpgmp-liste-phone">{%annu_telephone%}</div>{/if}
  {#if %annu_lien_rdv%}<div class="wpgmp-liste-rdv">
    <a href="{%annu_lien_rdv%}" target="_blank">Prendre rendez-vous</a>
  </div>{/if}
</div>
```

## Shortcode

```text
[gmaps_aa id="123"]
```

Attributs supportés :

| Attribut | Description |
|---|---|
| `id` | ID de la carte (obligatoire) |
| `filter_taxonomy` | Slug d'une taxonomie pour filtrer les points affichés |
| `filter_term` | ID d'un terme pour le filtre forcé |
| `hide_forced_filter` | `true` pour masquer l'UI du filtre forcé |

Exemple :

```text
[gmaps_aa id="123" filter_taxonomy="category" filter_term="42" hide_forced_filter="true"]
```

## Hooks et filtres

| Hook | Type | Description |
|---|---|---|
| `gmaps_aa_api_key` | filter | Retourne la clé Google Maps API |
| `gmaps_aa_cache_ttl` | filter | Durée du cache transient en secondes (défaut 300) |
| `gmaps_aa_template_value` | filter | Transforme la valeur d'un placeholder avant échappement |
| `gmaps_aa_template_kses_allowed` | filter | Allowlist HTML pour les templates utilisateur |
| `gmaps_aa_skip_gmaps_enqueue` | filter | `true` pour ne pas enqueuer Google Maps JS (si chargé par un autre plugin/thème) |
| `gmaps_aa_spiderfier_url` | filter | URL du script OverlappingMarkerSpiderfier (par défaut : fichier local) |

## Librairies embarquées

Toutes les dépendances JS sont bundlées localement dans `assets/vendor/` — aucun appel CDN en production.

| Librairie | Version | Licence | Usage |
|---|---|---|---|
| [OverlappingMarkerSpiderfier](https://github.com/jawj/OverlappingMarkerSpiderfier) | 1.0.3 | MIT | Dépilement des marqueurs superposés |

Google Maps JavaScript API est chargée à la volée via la clé fournie par le thème (pas de bundling, dépendance externe obligatoire).

## Compatibilité

- **Thème Salient** (`salient-core` / `nectar_gmap`) : les deux systèmes cohabitent sans conflit. Si Salient charge déjà Google Maps sur la même page, GMaps-AA le détecte et ne le recharge pas.

## Architecture

```
gmaps-aa/
├── gmaps-aa.php                    # Bootstrap + autoloader PSR-4
├── uninstall.php                   # Nettoyage complet à la désinstallation
├── includes/
│   ├── class-plugin.php            # Singleton, charge les modules
│   ├── class-activator.php         # Vérifie ACF, enregistre le CPT
│   ├── class-deactivator.php
│   ├── class-cpt.php               # CPT gmaps_aa_map
│   ├── class-map-config.php        # Métaboxes + save + AJAX terms
│   ├── class-taxonomy-markers.php  # Champ icône sur les termes (admin)
│   ├── class-template-parser.php   # Placeholders et conditionnels
│   ├── class-data-provider.php     # Agrège les données + cache transient
│   ├── class-assets.php            # Enregistre/enqueue scripts et styles
│   ├── class-shortcode.php         # Handler [gmaps_aa]
│   └── helpers.php                 # Fonctions utilitaires globales
├── admin/
│   ├── css/admin.css
│   ├── js/
│   │   ├── admin.js                # Mini-carte centre + media pickers + color + AJAX
│   │   └── term-icon.js            # Picker d'icône sur la page d'édition d'un term
│   └── views/                      # Templates de chaque métabox
├── public/
│   ├── css/public.css              # Layout minimal + responsive + popup
│   ├── js/gmaps-aa.js              # Init map, markers, filtres, search, popup, OMS
│   └── views/map-wrapper.php       # HTML du shortcode
├── assets/
│   ├── default-marker.svg          # Marqueur par défaut
│   └── vendor/
│       └── oms.min.js              # OverlappingMarkerSpiderfier
└── languages/
    └── gmaps-aa.pot
```

## Licence

GPLv3 or later.
